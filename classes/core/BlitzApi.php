<?php
/*phpcs:disable*/
/**
 * Blitz
 *  
 * @category Theme
 * @package  Blitz
 * @author   Sebo <sebo@42geeks.gg>
 * @license  GPLv2 https://opensource.org/licenses/gpl-2.0.php
 * @link     https://42geeks.gg/
 */
namespace blitz\core;

use blitz\Blitz;

/**
 * Class BlitzApi - Blitz Api
 * 
 * @category Theme
 * @package  BlitzApi
 * @author   Sebo <sebo@42geeks.gg>
 * @license  GPLv2 https://opensource.org/licenses/gpl-2.0.php
 * @link     https://42geeks.gg/
 * @version  2.0
 */
class BlitzApi
{
    /**
     * API Version
     */
    const API_VERSION = 'v2';

    /**
     * Registered endpoints
     */
    private static $registeredEndpoints = [];

    /**
     * Empty
     */
    public function __construct()
    {
    }

    /**
     * Determines wether the given endpoint is valid or not
     * 
     * @param string $endpoint - the endpoint to verify
     * 
     * @return bool - wether the endpoint is valid or not
     */
    public static function isValidEndpoint(string $endpoint): bool
    {
        return in_array($endpoint, self::$registeredEndpoints);
    }

    /**
     * Register a custom endpoint to the application
     * this function is used by BlitzApiObjects to register their endpoints
     * 
     * @param string $name - the name of the endpoint
     * @param array $callback - an array containing an object and the function on it to callback
     * the object, can also be a namespace. The callback provided should accept \WP_REST_Request as a parameter
     * and return \WP_REST_Response
     * @param string $allowedMethod - the HTTP method allowed to call this route
     * @param array $permissions (optional) - the permissions required to access the endpoint
     * it checks permissions based on wp's current_user_can but you can handover multiple strings
     * to allow public access leave empty
     * 
     * @return bool
     */
    public static function registerEndpoint(string $name, array $callback, string $allowedMethod = \WP_REST_Server::READABLE, array $permissions = []): bool
    {
        // TODO: throw exception instead
        if (false === is_callable($callback)) return false;

        add_action(
            'rest_api_init', 
            function () use ($name, $allowedMethod, $callback, $permissions) {
                register_rest_route(
                    'blitz/'.self::API_VERSION,
                    '/'.$name,
                    [
                        'methods' => $allowedMethod,
                        'callback' => $callback,
                        'permission_callback' => function (\WP_REST_Request $request) {
                            return true;
                            /* TODO: we need another nonce solution to work with the API based svelte frontend
                            $nonce = $request->get_header('X-WP-Nonce');
                            $valid = boolval(wp_verify_nonce($nonce, 'wp_rest'));
                            $referer = wp_get_referer();
                            $valid = $this->_checkReferer($referer);
                            if (0 === count($permissions)) {
                                return $valid && true;
                            } else {
                                return $valid && current_user_can('edit_others_posts');
                            }
                            */
                        }
                    ]
                );
            }
        );

        self::$registeredEndpoints[] = $name;

        return true;
    }

    /**
     * Check the call referer
     * 
     * @param string $referer - the url to check
     * 
     * @return bool - wether the referer is valid or not
     */
    private function _checkReferer(string $referer): bool
    {
        return rtrim(Blitz::$siteUrl, '/') === rtrim($referer, '/');
    }
}
