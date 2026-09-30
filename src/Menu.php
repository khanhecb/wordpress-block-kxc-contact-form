<?php

namespace KXC\ContactForm;

use KXC\ContactForm\Enums\AdminKey;

defined('ABSPATH') || exit;

final class Menu {
    public function register(): void {
        add_action('admin_menu', [$this, 'add_menu_pages']);
        add_action('admin_init', [$this, 'handle_actions']);
    }

    /**
     * Đăng ký trang Admin Menu
     */
    public function add_menu_pages(): void {
        add_menu_page(
            AdminKey::MENU_PAGE_TITLE->value,
            AdminKey::MENU_TITLE->value,
            AdminKey::MENU_CAPABILITY->value,
            AdminKey::MENU_SLUG->value,
            [$this, 'render_leads_page'],
            AdminKey::MENU_ICON->value ?? '',
            AdminKey::MENU_POSITION->value ?? null
        );
    }

    /**
     * Handle
     */
    public function handle_actions(): void {
        if (!$this->is_current_page()) {
            return;
        }

        $this->ensure_wp_list_table();

        $admin = new Admin();
        $admin->process_actions();
    }

    /**
     * Render page
     */
    public function render_leads_page(): void {
        if (!$this->is_current_page()) {
            return;
        }

        $this->ensure_wp_list_table();

        $admin = new Admin();
        $admin->render_admin_page();
    }

    /**
     * Check is Admin page
     */
    private function is_current_page(): bool {
        return isset($_GET['page']) && $_GET['page'] === AdminKey::MENU_SLUG->value;
    }

    /**
     * Load WP_List_Table
     */
    private function ensure_wp_list_table(): void {
        if (!class_exists('WP_List_Table')) {
            require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
        }
    }
}