<?php

namespace KXC\ContactForm;

use KXC\ContactForm\Enums\FormFieldKey as Field;

defined('ABSPATH') || exit;

readonly final class ContactDTO {
    public function __construct(
        public string $name,
        public string $email,
        public string $phone,
        public string $message
    ){}

    /**
     * Create DTO Instance from array input
     *
     * @param array $data
     * @return self
     */
    public static function from_array(array $data): self {
        return new self(
            name:    (string) ($data[Field::NAME->value]    ?? ''),
            email:   (string) ($data[Field::EMAIL->value]   ?? ''),
            phone:   (string) ($data[Field::PHONE->value]   ?? ''),
            message: (string) ($data[Field::MESSAGE->value] ?? '')
        );
    }

    /**
     * Convert Object to Array
     * 
     * @return string[]
     */
    public function to_array(): array {
        return [
            Field::NAME->value    => $this->name,
            Field::EMAIL->value   => $this->email,
            Field::PHONE->value   => $this->phone,
            Field::MESSAGE->value => $this->message
        ];
    }
}