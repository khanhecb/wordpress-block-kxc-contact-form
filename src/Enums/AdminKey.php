<?php

namespace KXC\ContactForm\Enums;

defined('ABSPATH') || exit;

enum AdminKey: string {
    case MENU_PAGE_TITLE = 'KXC Contact Leads';
    case MENU_TITLE      = 'Contact lead';
    case MENU_CAPABILITY = 'manage_options';
    case MENU_SLUG       = 'kxc-contact-leads';
    case MENU_ICON       = 'dashicons-email-alt';
    case MENU_POSITION   = '20';
}