<?php

namespace KXC\ContactForm; 

use KXC\ContactForm\Enums\AdminKey;

defined('ABSPATH') || exit;

if (!function_exists('convert_to_screen')) {
    require_once ABSPATH . 'wp-admin/includes/template.php';
}

class Admin extends \WP_List_Table {

    /**
     * @var Repository
     */
    private $repository;

    public function __construct(?Repository $repository = null) {
        $this->repository = $repository ?: new Repository();

        parent::__construct([
            'singular' => 'lead',
            'plural'   => 'leads',
            'ajax'     => false,
        ]);
    }

    /**
     * Feature: Add/Remove Lead
     */
    public function process_actions() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $page = isset($_REQUEST['page']) ? sanitize_text_field($_REQUEST['page']) : '';
        
        if ($page !== AdminKey::MENU_SLUG->value ) {
            return;
        }

        // Add new record
        if (isset($_POST['kxc_action']) && $_POST['kxc_action'] === 'add_lead') {
            if (isset($_POST['kxc_add_lead_nonce']) && wp_verify_nonce($_POST['kxc_add_lead_nonce'], 'kxc_add_lead_action')) {
                $name    = sanitize_text_field($_POST['user_name'] ?? '');
                $phone   = sanitize_text_field($_POST['user_phone'] ?? '');
                $email   = sanitize_email($_POST['user_email'] ?? '');
                $message = sanitize_textarea_field($_POST['user_message'] ?? '');
                $source  = sanitize_text_field($_POST['source'] ?? 'Thêm thủ công');

                if (!empty($name) && !empty($phone)) {
                    $result = $this->repository->insert($name, $phone, $message, $email, $source);
                    if ($result !== false) {
                        wp_redirect(add_query_arg(['page' => $page, 'message' => 'added'], admin_url('admin.php')));
                        exit;
                    }
                }
                wp_redirect(add_query_arg(['page' => $page, 'message' => 'error'], admin_url('admin.php')));
                exit;
            }
        }

        // Delete 1 record
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
            $id = absint($_GET['id']);
            if (wp_verify_nonce($_GET['_wpnonce'] ?? '', 'kxc_delete_lead_' . $id)) {
                $this->repository->delete($id);
                wp_redirect(add_query_arg(['page' => $page, 'message' => 'deleted'], admin_url('admin.php')));
                exit;
            }
        }

        // Bulk delete multi record
        $action = $this->current_action();
        if ($action === 'bulk-delete' && !empty($_GET['bulk-delete'])) {
            if (check_admin_referer('bulk-' . $this->_args['plural'])) {
                $delete_ids = array_map('absint', (array) $_GET['bulk-delete']);
                $delete_ids = array_filter($delete_ids);

                if (!empty($delete_ids)) {
                    $this->repository->bulk_delete($delete_ids);
                }

                wp_redirect(add_query_arg(['page' => $page, 'message' => 'bulk_deleted'], admin_url('admin.php')));
                exit;
            }
        }
    }

    /**
     * Render Admin page
     * @return void
     */
    public function render_admin_page() {
        $this->prepare_items();
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Quản Lý Contact Leads</h1>
            <hr class="wp-header-end">

            <?php $this->display_admin_notices(); ?>

            <!-- Form Thêm Mới Lead -->
            <div class="card" style="max-width: 100%; margin-top: 15px; margin-bottom: 20px; padding: 15px 20px;">
                <h2>Thêm Lead Mới</h2>
                <form method="post" action="">
                    <?php wp_nonce_field('kxc_add_lead_action', 'kxc_add_lead_nonce'); ?>
                    <input type="hidden" name="kxc_action" value="add_lead" />
                    
                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row"><label for="user_name">Họ và Tên <span class="description">(bắt buộc)</span></label></th>
                                <td><input name="user_name" type="text" id="user_name" class="regular-text" required /></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="user_phone">Số Điện Thoại <span class="description">(bắt buộc)</span></label></th>
                                <td><input name="user_phone" type="text" id="user_phone" class="regular-text" required /></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="user_email">Email</label></th>
                                <td><input name="user_email" type="email" id="user_email" class="regular-text" /></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="user_message">Tin nhắn / Lời nhắn</label></th>
                                <td><textarea name="user_message" id="user_message" rows="3" class="regular-text"></textarea></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="source">Nguồn (Source)</label></th>
                                <td><input name="source" type="text" id="source" value="Admin Manual" class="regular-text" /></td>
                            </tr>
                        </tbody>
                    </table>
                    <?php submit_button('Thêm Lead', 'primary', 'submit_add_lead', false); ?>
                </form>
            </div>

            
            <form method="get">
                <input type="hidden" name="page" value="<?php echo esc_attr($_REQUEST['page'] ?? ''); ?>" />
                <?php
                $this->search_box('Tìm kiếm', 'search_id');
                $this->display();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register columns in table data.
     * @return array{cb: string}
     */
    public function get_columns() {
        return [
            'cb'                                    => '<input type="checkbox" />',
            Database::FIELD_USER_NAME    => 'Họ và Tên',
            Database::FIELD_USER_PHONE   => 'Số Điện Thoại',
            Database::FIELD_USER_EMAIL   => 'Email',
            Database::FIELD_USER_MESSAGE => 'Nội dung',
            Database::FIELD_SOURCE       => 'Nguồn',
            Database::FIELD_CREATED_AT   => 'Ngày Tạo',
        ];
    }

    /**
     * Register Support rows
     */
    public function get_sortable_columns() {
        return [
            Database::FIELD_USER_NAME  => [Database::FIELD_USER_NAME, false],
            Database::FIELD_CREATED_AT => [Database::FIELD_CREATED_AT, true],
        ];
    }

    /**
     * Render bulk action checkbox
     */
    protected function column_cb($item) {
        $id_field = Database::FIELD_ID;
        return sprintf('<input type="checkbox" name="bulk-delete[]" value="%d" />', absint($item[$id_field]));
    }

    /**
     * Render Col: Name & Delete
     */
    protected function column_user_name($item) {
        $id_field   = Database::FIELD_ID;
        $name_field = Database::FIELD_USER_NAME;

        $delete_url = wp_nonce_url(
            add_query_arg([
                'page'   => $_REQUEST['page'] ?? '',
                'action' => 'delete',
                'id'     => $item[$id_field]
            ], admin_url('admin.php')),
            'kxc_delete_lead_' . $item[$id_field]
        );

        $actions = [
            'delete' => sprintf(
                '<a href="%s" style="color: #a00;" onclick="return confirm(\'Bạn có chắc muốn xóa lead này?\')">Xóa</a>',
                esc_url($delete_url)
            ),
        ];

        return sprintf('<strong>%s</strong> %s', esc_html($item[$name_field]), $this->row_actions($actions));
    }

    /**
     * Render data default
     * @param mixed $item
     * @param mixed $column_name
     * @return string
     */
    protected function column_default($item, $column_name) {
        return esc_html($item[$column_name] ?? '');
    }

    /**
     * Bulk action delete
     */
    public function get_bulk_actions() {
        return [
            'bulk-delete' => 'Xóa Hàng Loạt',
        ];
    }

    /**
     * Prepare data for pagenigation
     */
    public function prepare_items() {
        global $wpdb;

        $columns               = $this->get_columns();
        $hidden                = [];
        $sortable              = $this->get_sortable_columns();
        $this->_column_headers = [$columns, $hidden, $sortable];

        $per_page     = 15;
        $current_page = $this->get_pagenum();
        $db_config    = new Database();
        $table_name   = $db_config->get_table_name();

        // Field name
        $f_name  = $db_config::FIELD_USER_NAME;
        $f_phone = $db_config::FIELD_USER_PHONE;
        $f_email = $db_config::FIELD_USER_EMAIL;
        $f_meta  = $db_config::FIELD_META;
        $f_date  = $db_config::FIELD_CREATED_AT;

        // Sort
        $allowed_sort = [$f_name, $f_date];
        $requested_orderby = $_REQUEST['orderby'] ?? $f_date;
        $orderby = in_array($requested_orderby, $allowed_sort, true) ? $requested_orderby : $f_date;
        $order   = isset($_REQUEST['order']) && strtoupper($_REQUEST['order']) === 'ASC' ? 'ASC' : 'DESC';

        // Search
        $search = isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : '';
        $where  = 'WHERE 1=1';

        if (!empty($search)) {
            $like  = '%' . $wpdb->esc_like($search) . '%';
            $where .= $wpdb->prepare(
                " AND ({$f_name} LIKE %s OR {$f_phone} LIKE %s OR {$f_email} LIKE %s OR {$f_meta} LIKE %s)",
                $like, $like, $like, $like
            );
        }

        // Count record
        $total_items = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table_name} {$where}");

        // Get list pagenigation
        $offset = ($current_page - 1) * $per_page;
        $this->items = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table_name} {$where} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d",
                $per_page,
                $offset
            ),
            ARRAY_A
        );

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items / $per_page),
        ]);
    }

    /**
     * Admin notice
     * 
     * @return void
     */
    private function display_admin_notices() {
        if (!isset($_GET['message'])) {
            return;
        }

        $message = sanitize_text_field($_GET['message']);

        if ($message === 'added') {
            echo '<div class="notice notice-success is-dismissible"><p>Thêm Lead thành công!</p></div>';
        } elseif ($message === 'deleted') {
            echo '<div class="notice notice-success is-dismissible"><p>Đã xóa Lead thành công!</p></div>';
        } elseif ($message === 'bulk_deleted') {
            echo '<div class="notice notice-success is-dismissible"><p>Đã xóa các Lead đã chọn!</p></div>';
        } elseif ($message === 'error') {
            echo '<div class="notice notice-error is-dismissible"><p>Có lỗi xảy ra, vui lòng kiểm tra lại dữ liệu!</p></div>';
        }
    }
}