<?php

namespace KXC\ContactForm\Enums;

defined('ABSPATH') || exit;

enum RestRouteKey: string {
    case NAMESPACE = 'v2/kxc';
    case PATH = '/contact';
}