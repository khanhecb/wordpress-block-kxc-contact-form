<?php

namespace KXC\ContactForm;

use KXC\ContactForm\Enums\RestRouteKey;
use \WP_REST_Server;
use \WP_REST_Request;
use \WP_REST_Response;
use \WP_Error;

use KXC\Contactform\Repository as Repository;
use KXC\ContactForm\Controller as Controller;
use KXC\ContactForm\Validate   as Validate;

defined('ABSPATH') || exit;

class Api {
    private $repo;
    private $rest_namespace;
    private $route;
    private $method;
    private $controller;

    public function __construct( 
        Repository $repo,
        Controller $controller
    ){
        $this->repo = $repo;
        $this->rest_namespace = 'v2/kxc';
        $this->route          = '/contact';
        $this->method         = WP_REST_Server::CREATABLE;
        $this->controller     = new Controller($repo);

        add_action( 'rest_api_init', [ $this, 'register_rest_route']);
    }

    /**
     * Get URL of form. This is POST method.
     * URL: https://domain.com/wp-json/v2/kxc/contact
     * 
     * @return string
     */
    public static function get_rest_url() {
        
        $rest_url = '/wp-json/' . RestRouteKey::NAMESPACE->value . RestRouteKey::PATH->value;
        
        $home = get_home_url( $rest_url );

        return $rest_url;
    }

    public function register_rest_route() {
        register_rest_route(
            $this->rest_namespace,
            $this->route,
            [
                'methods'             => $this->method,
                'callback'            => [ $this, 'submit'],
                'permission_callback' => '__return_true'
            ]
        );
    }

    /**
     * Handle submit from form
     * 
     * @param  WP_REST_Request $request  Request from front-end
     * @return WP_Error|WP_REST_Response Response form backend
     */
    public function submit(WP_REST_Request $request) {
        return $this->controller->handle_submission($request);
    }
}
