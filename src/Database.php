<?php
/**
 * This claff extra create lead database, public method: insert new record, delele record, creat new record
 * @depends: WordPres Core
 * @author : KhanhECB
 * @date   : 26/9/2026
 * @packgae: KXC Contact Form
 */

namespace KXC\ContactForm;

use KXC\ContactForm\Enums\DatabaseKey;

defined("ABSPATH") || exit;

final class Database {
    private $table_name;
    private $charset_collate;
    private $db;
    
    // Define database fields public for use in difference layer (Admin get field render)
    public const FIELD_ID           = DatabaseKey::ID->value;
    public const FIELD_USER_NAME    = DatabaseKey::USER_NAME->value;
    public const FIELD_USER_PHONE   = DatabaseKey::USER_PHONE->value;
    public const FIELD_USER_EMAIL   = DatabaseKey::USER_EMAIL->value;
    public const FIELD_USER_MESSAGE = DatabaseKey::USER_MESSAGE->value;
    public const FIELD_SOURCE       = DatabaseKey::SOURCE->value;
    public const FIELD_META         = DatabaseKey::META->value;
    public const FIELD_STATUS       = DatabaseKey::STATUS->value;
    public const FIELD_CREATED_AT   = DatabaseKey::CREATED_AT->value;

    public function __construct() {
        global $wpdb;
        $this->db              = $wpdb;
        $this->table_name      = $wpdb->prefix . 'contact_lead';
        $this->charset_collate = $wpdb->get_charset_collate();
    }

    /**
     * Create Table wp_leads
     * @since: 1.0.0
     * @return void
     */
    public function create_table() {
        
        $sql = "CREATE TABLE {table_name} (
            {id} bigint(20) NOT NULL AUTO_INCREMENT,
            {user_name} varchar(255) NOT NULL DEFAULT '',
            {user_email} varchar(100) NOT NULL DEFAULT '',
            {user_phone} varchar(20) NOT NULL DEFAULT '',
            {user_message} varchar(512) NOT NULL DEFAULT '',
            {source} varchar(100) NOT NULL DEFAULT '',
            {meta} longtext,
            {status} varchar(20) NOT NULL DEFAULT '',
            {created_at} datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  ({id}),
            KEY {user_email} ({user_email}),
            KEY {user_phone} ({user_phone}),
            KEY {source} ({source}),
            KEY {created_at} ({created_at})
        ) {charset_collate};";

        $sql = strtr($sql, [
            '{table_name}'      => $this->table_name,
            '{id}'              => self::FIELD_ID,
            '{user_name}'       => self::FIELD_USER_NAME,
            '{user_email}'      => self::FIELD_USER_EMAIL,
            '{user_phone}'      => self::FIELD_USER_PHONE,
            '{user_message}'    => self::FIELD_USER_MESSAGE,
            '{source}'          => self::FIELD_SOURCE,
            '{meta}'            => self::FIELD_META,
            '{status}'          => self::FIELD_STATUS,
            '{created_at}'      => self::FIELD_CREATED_AT,
            '{charset_collate}' => $this->charset_collate,
        ]);

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    // Getter
    public function get_table_name() {
        return $this->table_name;
    }

    public function get_charset_collate() {
        return $this->charset_collate;
    }
}