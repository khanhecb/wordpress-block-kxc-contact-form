<?php

namespace KXC\ContactForm;

use WP_Error;
use KXC\ContactForm\Enums\FormFieldKey;

defined('ABSPATH') || exit;

final class Security {
    private function __construct() {}
    private function __clone() {}
    private function __wakeup() {}
    
    /**
     * Check Honeypot field.
     * If hidden field is not empty -> bot
     *
     * @return bool|WP_Error
     */
    public static function bot_honey(string $honeypot_value): bool|WP_Error {
        if (!empty(trim($honeypot_value))) {
            return new WP_Error('spam_detected', 'Cảm ơn bạn đã gửi thông tin.', ['status' => 200]);
        }
        return true;
    }

    /**
     * Check rate limit from user request, default is 3 request in 1  minute.
     * Anti DDoS - Distributed Denial of Service
     * @param int $max_requests
     * @param int $decay_seconds
     * @return bool|WP_Error
     */
    public static function check_rate_limit( int $max_requests = 3, int $decay_seconds = 60): bool|WP_Error {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $transient_key = 'kxc_rate_' . md5($ip);

        $hits = (int) get_transient($transient_key);

        if ($hits >= $max_requests) {
            return new WP_Error('too_many_requests', 'Bạn thao tác quá nhanh. Vui lòng thử lại sau ít phút.', ['status' => 429]);
        }

        set_transient($transient_key, $hits + 1, $decay_seconds);
        return true;
    }
}
