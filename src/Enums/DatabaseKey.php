<?php

namespace KXC\ContactForm\Enums;

enum DatabaseKey: string {
    case ID           = 'id';
    case USER_NAME    = 'user_name';
    case USER_PHONE   = 'user_phone';
    case USER_EMAIL   = 'user_email';
    case USER_MESSAGE = 'user_message';
    case SOURCE       = 'source';
    case META         = 'meta';
    case STATUS       = 'status';
    case CREATED_AT   = 'created_at';
}