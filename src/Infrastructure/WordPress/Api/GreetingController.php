<?php

declare(strict_types=1);

namespace WP\Skeleton\Infrastructure\WordPress\Api;

use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use WP\Skeleton\Application\GreetingApplication;

/**
 * REST API controller for greeting functionality
 */
class GreetingController extends WP_REST_Controller
{
    /**
     * @var string The namespace for this controller
     */
    protected $namespace = 'wp-skeleton/v1';

    /**
     * @var string The base for this controller's routes
     */
    protected $rest_base = 'greeting';

    /**
     * @var GreetingApplication
     */
    private GreetingApplication $greetingApp;

    public function __construct(GreetingApplication $greetingApp)
    {
        $this->greetingApp = $greetingApp;
    }

    /**
     * Register the routes for this controller
     */
    public function register_routes(): void
    {
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            [
                [
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => [$this, 'get_greeting'],
                    'permission_callback' => [$this, 'get_item_permissions_check'],
                    'args' => $this->get_collection_params(),
                ],
                [
                    'methods' => WP_REST_Server::CREATABLE,
                    'callback' => [$this, 'create_greeting'],
                    'permission_callback' => [$this, 'create_item_permissions_check'],
                    'args' => $this->get_endpoint_args_for_item_schema(WP_REST_Server::CREATABLE),
                ],
                'schema' => [$this, 'get_public_item_schema'],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<name>[a-zA-Z0-9-]+)',
            [
                [
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => [$this, 'get_greeting_by_name'],
                    'permission_callback' => [$this, 'get_item_permissions_check'],
                    'args' => [
                        'name' => [
                            'description' => __('The name to generate a greeting for.', 'wp-skeleton'),
                            'type' => 'string',
                            'required' => true,
                            'validate_callback' => [$this, 'validate_name'],
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * Get a greeting
     */
    public function get_greeting(WP_REST_Request $request): WP_REST_Response
    {
        $name = $request->get_param('name') ?: 'World';
        
        try {
            $greeting = $this->greetingApp->greet($name);
            
            return new WP_REST_Response([
                'greeting' => $greeting,
                'name' => $name,
                'timestamp' => current_time('mysql'),
            ], 200);
            
        } catch (InvalidArgumentException $e) {
            return new WP_REST_Response([
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get greeting by specific name
     */
    public function get_greeting_by_name(WP_REST_Request $request): WP_REST_Response
    {
        $name = $request->get_param('name');
        
        try {
            $greeting = $this->greetingApp->greet($name);
            
            return new WP_REST_Response([
                'greeting' => $greeting,
                'name' => $name,
                'timestamp' => current_time('mysql'),
            ], 200);
            
        } catch (InvalidArgumentException $e) {
            return new WP_REST_Response([
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Create a new greeting
     */
    public function create_greeting(WP_REST_Request $request): WP_REST_Response
    {
        $name = $request->get_param('name');
        
        try {
            if (!$this->greetingApp->isValidName($name)) {
                return new WP_REST_Response([
                    'error' => __('Invalid name provided.', 'wp-skeleton'),
                ], 400);
            }
            
            $greeting = $this->greetingApp->greet($name);
            
            return new WP_REST_Response([
                'greeting' => $greeting,
                'name' => $name,
                'id' => uniqid(),
                'timestamp' => current_time('mysql'),
            ], 201);
            
        } catch (InvalidArgumentException $e) {
            return new WP_REST_Response([
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Check if a given request has access to get items
     */
    public function get_item_permissions_check($request): bool
    {
        return true; // Publicly accessible
    }

    /**
     * Check if a given request has access to create items
     */
    public function create_item_permissions_check($request): bool
    {
        return current_user_can('edit_posts');
    }

    /**
     * Validate name parameter
     */
    public function validate_name($value, $request, $param): bool
    {
        return $this->greetingApp->isValidName($value);
    }

    /**
     * Get the query params for collections
     */
    public function get_collection_params(): array
    {
        return [
            'name' => [
                'description' => __('The name to generate a greeting for.', 'wp-skeleton'),
                'type' => 'string',
                'default' => 'World',
                'validate_callback' => [$this, 'validate_name'],
            ],
        ];
    }
}