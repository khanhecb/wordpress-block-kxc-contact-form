<?php

namespace KXC\ContactForm;

use KXC\ContactForm\Enums\FormFieldKey;

use \WP_REST_Request;
use \WP_REST_Response;
use \WP_Error;

defined('ABSPATH') || exit;

final class Controller {

    public function __construct(
        private Repository $repo,
    ) {}

    /**
     * Handle form submit
     * - Get data sent from form with flow:
     * 1. Check spam
     * 2. Honeypot
     * 3. Sanitize data
     * 4. Validate data
     * 5. Create Data DTO
     * 6. Insert data to database
     * 7. Response to user
     * 
     * @param WP_REST_Request $request
     * @return WP_Error|WP_REST_Response
     */
    public function handle_submission(WP_REST_Request $request) {
        // Get raw request
        $raw_data = $request->get_params();
        
        // Check Security
        $maybe_spam = Security::check_rate_limit();
        if (is_wp_error($maybe_spam)) {
            return $maybe_spam;
        }

        // Check Honeypot
        $honeypot_field_data = $raw_data[FormFieldKey::HONEYPOT->value] ?? '';
        $honeypot = Security::bot_honey($honeypot_field_data) ?? '';
        if (is_wp_error($honeypot)) {
            return $honeypot;
        }

        // Validate data
        $validated_result = Validate::validate_contact_form_data($raw_data);
        if (is_wp_error($validated_result)) {
            return $validated_result;
        }

        // Sanitize Form fields
        $clean_data = Sanitize::sanitize_contact_form($validated_result);
        
        // If not error -> create DTO Data
        $contact_dto = ContactDTO::from_array($clean_data);

        // Save database via Repo
        $is_sent = $this->repo->insert(
            name: $contact_dto->name,
            phone: $contact_dto->phone,
            email: $contact_dto->email,
            user_message: $contact_dto->message
        );

        if (!$is_sent) {
            return new WP_Error(
                'send_failed',
                'Không thể gửi thông tin liên hệ lúc này. Vui lòng thử lại sau.',
                ['status' => 500]
            );
        }

        // Return Respone
        return new WP_REST_Response([
            'success' => true,
            'message' => 'Cảm ơn bạn đã gửi thông tin. Chúng tôi sẽ liên hệ sớm nhất!',
        ], 200);
    }
}