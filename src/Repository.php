<?php

namespace KXC\ContactForm;

use WP_Error;

defined('ABSPATH') || exit;

final class Repository {
    private \wpdb $wpdb;
    private Database $db_config;

    public function __construct(?Database $db_config = null) {
        global $wpdb;
        $this->wpdb      = $wpdb;
        $this->db_config = $db_config ?: new Database();
    }

     /**
     * Create new record into customer contact lead
     * @param string $name          full name of user    (front-end)
     * @param string $phone         phone number of user (front-end)
     * @param string $user_message  message of user      (front-end)    
     * @param string $email         email of user        (front-end)
     * @param string $source        user from for Marketing
     * @param string $status        tracking status
     * @param string $meta          expand data
     * @return bool|int
     */
    public function insert(
        string $name,
        string $phone,
        string $user_message,
        string $email  = '',
        string $source = '',
        string $status = '',
        string $meta   = ''
    )
    {   
        $table_name = $this->db_config->get_table_name();
        return $this->wpdb->insert(
            $table_name,
            [
                $this->db_config::FIELD_USER_NAME    => $name         ?? '',
                $this->db_config::FIELD_USER_PHONE   => $phone        ?? '',
                $this->db_config::FIELD_USER_MESSAGE => $user_message ?? '',
                $this->db_config::FIELD_USER_EMAIL   => $email        ?? '',
                $this->db_config::FIELD_SOURCE       => $source       ?? '',
                $this->db_config::FIELD_META         => $meta         ?? '',
                $this->db_config::FIELD_STATUS       => $status       ?? '',
            ]
        );
    }

    /**
     * Delelte 1 item from lead
     * @param int $id
     * @return bool|int
     */
    public function delete(int $id) {
        return $this->wpdb->delete(
            $this->db_config->get_table_name(),
            [ $this->db_config::FIELD_ID => $id ],
            [ '%d' ]
        );
    }

    public function bulk_delete(array $ids): int|WP_Error {

        // Check empty array
        if (empty($ids)) {
            return new WP_Error(
                'empty_ids',
                'Dữ liệu truyền vào danh sách xóa là một array rỗng'
            );
        }

        // Validate data from array
        $sanitized_ids = array_map('absint', array_filter($ids));

        // Check empty after validate
        if (empty($sanitized_ids)) {
            return new WP_Error(
                'empty_ids',
                'Danh sách ID truyền vào bị trống'
            );
        }

        // Delele data from safe array
        $table        = $this->db_config->get_table_name();
        $f_id         = $this->db_config::FIELD_ID;
        $ids_imploded = implode(',', $sanitized_ids);

        $deleted = $this->wpdb->query("DELETE FROM {$table} WHERE {$f_id} IN ({$ids_imploded})");

        // Check success and return or show error
        if (false === $deleted) {
            error_log('[KXC_Lead_Repository] Bulk Delete Fail: ' . $this->wpdb->last_error);
            return new WP_Error(
                'db_delete_error',
                'Lỗi CSDL khi xóa bản ghi.'
            );
        }

        return $deleted;
    }

    /**
     * Query database 
     * @param string $sql
     * @param mixed $params
     * @return int|WP_Error
     */
    public function query( string $sql, $params = [] ): int|WP_Error {
        
        // Prepare SQL
        if ( ! empty( $params ) ) {
            $prepared_sql = $this->wpdb->prepare( $sql, $params );
        } else {
            $prepared_sql = $sql;
        }

        // Excute SQL
        $result = $this->wpdb->query( $prepared_sql );

        // Error
        if ( false === $result ) {
            return new WP_Error(
                'db_query_error',
                'KXC Repository: query thực thi không thành công'
            );
        }

        return (int) $result;
    }
}