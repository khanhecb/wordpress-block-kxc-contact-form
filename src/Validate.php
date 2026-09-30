<?php

namespace KXC\ContactForm;

use WP_Error;

use KXC\ContactForm\ContactDTO;

use KXC\ContactForm\Enums\FormFieldKey as FormFieldKey;

defined('ABSPATH') || exit;

final class Validate {

/**
     * Prevent instantiating pure static class
     */
    private function __construct() {}

    /**
     * Validate Cleaned Contact Form Data
     *
     * @param array $raw_data Data get from form.
     * @return array|WP_Error
     */
    public static function validate_contact_form_data(array $raw_data): array|WP_Error {
        
        $errors = new WP_Error();

        // Extract variables from clean data
        $name    = trim($raw_data[FormFieldKey::NAME->value] ?? '');
        $phone   = trim($raw_data[FormFieldKey::PHONE->value] ?? '');
        $email   = trim($raw_data[FormFieldKey::EMAIL->value] ?? '');
        $message = trim($raw_data[FormFieldKey::MESSAGE->value] ?? '');

        // Execute validations
        self::validate_name($name, $errors);
        self::validate_phone($phone, $errors);
        self::validate_email($email, $errors);
        self::validate_message($message, $errors);

        // Return accumulated errors if exists
        if ($errors->has_errors()) {
            return $errors;
        }

        // Return Payload Array
        return [
            FormFieldKey::NAME->value       => $name,
            FormFieldKey::PHONE->value      => $phone,
            FormFieldKey::EMAIL->value      => $email,
            FormFieldKey::MESSAGE->value    => $message
        ];
    }

    /**
     * Validate User Name:
     * - Name is not Empty
     * - String name in range 5-48 character
     * 
     * @param string $name
     * @param WP_Error $errors
     * @return bool|WP_Error
     */
    private static function validate_name(string $name, WP_Error $errors): void {

        // Trim data
        $name = trim($name);

        // Check empty name field. if Empty -> stop process
        if (empty($name)) {
            $errors->add(
                'empty_name',
                'Họ và tên không được để trống.',
                ['status' => 400]
            );

            return;
        } 

        // Check name string length
        $name_length = mb_strlen($name);
        if ($name_length < 5 || $name_length > 48) {
            $errors->add(
                'name_long_or_short',
                'Tên phải có độ dài từ 5 đến 48 ký tự.',
                ['status' => 400]
            );
        }
    }

    /**
     * Validate phone number
     * - Phone number is not empty.
     * - Phone number string allow: number and + char.
     * - Phone number start: +84 or +.
     * - Phone number length in range [10:12] character.
     * @param string $phone
     * @param WP_Error $errors
     * @return void
     */
    private static function validate_phone(string $phone, WP_Error $errors): void {
        
        $phone = trim($phone);

        // Check empty
        if (empty($phone)) {
            $errors->add(
                'empty_phone',
                'Số điện thoại không được để trống.',
                ['status' => 400]
            );
            return;
        }

        // Char is 0-9 and +
        if (!preg_match('/^\+?[0-9]+$/', $phone)) {
            $errors->add(
                'invalid_phone_characters',
                'Số điện thoại chỉ được chứa chữ số và ký tự +.',
                ['status' => 400]
            );

            return;
        }

        // Just start at +84 or 0
        if (!preg_match('/^(0|\+84)/', $phone)) {
            $errors->add(
                'invalid_phone_prefix',
                'Số điện thoại phải bắt đầu bằng 0 hoặc +84.',
                ['status' => 400]
            );
            
            return;
        }

        // Phone string is 10-12 char
        $phone_length = strlen($phone);
        if ($phone_length < 10 || $phone_length > 12) {
            $errors->add(
                'invalid_phone_length',
                'Số điện thoại phải có độ dài từ 10 đến 12 ký tự.',
                ['status' => 400]
            );
        }

    }

    /**
     * Validate email:
     * - Can empty email.
     * - If not empty, check is email.
     * 
     * @param string   $email
     * @param WP_Error $errors
     * @return bool|WP_Error
     */
    private static function validate_email(string $email, WP_Error $errors): void {

        // Trim Email
        $email = trim($email);

        // Able empty Email
        if (empty(trim($email))) {
            return;
        }

        // If not empty check is email
        if ( !is_email($email)) {
            $errors->add(
                'not_email',
                'Email không đúng định dạng, trường này có thể bỏ qua',
                ['status' => 400]
            );
            
            return;
        }
    }

    /**
     * Validate message string
     * - Message is not empty
     * - Message string in range [0-512] character
     * 
     * @param string $message
     * @param WP_Error $errors
     * @return void
     */
                
    private static function validate_message(string $message, WP_Error $errors): void {
        // Trim Mess
        $message = trim($message);

        // Validate empty message. If empty return error
        if (empty($message)) {
            $errors->add(
                'empty_message',
                'Message không được bỏ trống',
                ['status' => 400]
            );

            return;
        }

        // Validate message length: string length < 512 char
        if (mb_strlen($message) > 512) {
            $errors->add(
                'message_too_long',
                'Nội dung lời nhắn không được vượt quá 512 ký tự.',
                ['status' => 400]
            );
        }
    }
}