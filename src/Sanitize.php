<?php

namespace KXC\ContactForm;

use KXC\ContactForm\Enums\FormFieldKey;

defined('ABSPATH') || exit;

final class Sanitize {

    /**
     * Sanitize contact form raw inputs
     *
     * @param array $raw_data
     * @return array
     */
    public static function sanitize_contact_form(array $raw_data): array {
        return [
            FormFieldKey::NAME->value => sanitize_text_field(
                trim($raw_data[FormFieldKey::NAME->value] ?? '')
            ),
            FormFieldKey::PHONE->value => sanitize_text_field(
                trim($raw_data[FormFieldKey::PHONE->value] ?? '')
            ),
            FormFieldKey::EMAIL->value => sanitize_email(
                trim($raw_data[FormFieldKey::EMAIL->value] ?? '')
            ),
            FormFieldKey::MESSAGE->value => sanitize_textarea_field(
                trim($raw_data[FormFieldKey::MESSAGE->value] ?? '')
            )
        ];
    }
}