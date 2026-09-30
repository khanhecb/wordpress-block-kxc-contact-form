<?php

/**
 * Plugin Name:         Kha Xuyen Computer Contact Form
 * Description:         Gutenbern block form,
 * Author:              Nguyen Huu Khanh
 * Version:             1.0.0
 * Date:                26/09/08
 * Text Domain:         plugin-text-domain
 * Requires PHP:        8.1
 * Requires at lesats:  8.1
 * 
 */

use KXC\ContactForm\Repository;
use KXC\ContactForm\Controller;
use KXC\ContactForm\Api;
use KXC\ContactForm\Menu;
use KXC\ContactForm\Database;

defined("ABSPATH") || exit;


if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

register_activation_hook(__FILE__, [new Database(), 'create_table']);

/**
 * Main Plugin Bootstrapper Class
 */
final class KXC_Contact_Form_Block {

    private Repository $repo;
    private Controller $controller;

    public function __construct() {
        $this->init_dependencies();
        $this->init_hooks();
    }

    /**
     * Khởi tạo các Dependencies core
     */
    private function init_dependencies(): void {
        $this->repo       = new Repository();
        $this->controller = new Controller($this->repo);
    }

    /**
     * Đăng ký toàn bộ các Hooks của WordPress
     */
    private function init_hooks(): void {
        add_action('init', [$this, 'register_block']);
        add_action('rest_api_init', [$this, 'register_api']);
        (new Menu())->register();
    }

    public function register_block(): void {
        register_block_type(__DIR__);
    }

    public function register_api(): void {
        $api = new Api($this->repo, $this->controller);
        $api->register_rest_route();
    }
}

new KXC_Contact_Form_Block();