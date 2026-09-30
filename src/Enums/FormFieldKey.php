<?php

namespace KXC\ContactForm\Enums;

defined('ABSPATH') || exit;

enum FormFieldKey: string {
    case NAME      = 'name';
    case PHONE     = 'phone';
    case EMAIL     = 'email';
    case MESSAGE   = 'message';
    case HONEYPOT = 'website_kxc';
}