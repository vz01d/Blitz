<?php
/* phpcs:disable */
/**
 * Blitz
 *  
 * @category Theme
 * @package  Blitz
 * @author   Sebo <sebo@42geeks.gg>
 * @license  GPLv2 https://opensource.org/licenses/gpl-2.0.php
 * @link     https://42geeks.gg/
 */
namespace Blitz\objects;

use StoutLogic\AcfBuilder\FieldsBuilder;
use blitz\interfaces\BlitzObject;
use blitz\interfaces\BlitzApiObject;
use blitz\core\BlitzObjects;
use blitz\core\BlitzApi;

/**
 * Class Settings - access to theme settings
 * 
 * @category Theme
 * @package  Blitz
 * @author   Sebo <sebo@42geeks.gg>
 * @license  GPLv2 https://opensource.org/licenses/gpl-2.0.php
 * @link     https://42geeks.gg/
 */
class Settings extends BlitzObjects implements BlitzObject, BlitzApiObject
{
    /**
     * Init settings
     */
    public function __construct()
    {
    }

    /**
     * Load objects custom fields
     * 
     * @return void
     */
    public function loadObjectMeta(): void
    {
        /**
         * Theme general settings
         */
        $themeSettings = new FieldsBuilder('theme_settings');
        $themeSettings
            ->addTab('design')
            ->addImage('logo', [
                'label' => 'Logo',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => [],
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'return_format' => 'array',
                'preview_size' => 'thumbnail',
                'library' => 'all',
                'min_width' => '',
                'min_height' => '',
                'min_size' => '',
                'max_width' => '',
                'max_height' => '',
                'max_size' => '',
                'mime_types' => 'png, jpg, svg, gif',
            ])
            ->addColorPicker('frontend_text_color', [
                'label' => 'Frontend text color',
                'instructions' => 'applied to p, h1, h2, h3, h4, h5, h6',
                'required' => 0,
                'conditional_logic' => [],
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'default_value' => '#000000',
            ])
            ->addColorPicker('frontend_background_color', [
                'label' => 'Frontend background color',
                'instructions' => 'applied to body, use with caution.',
                'required' => 0,
                'conditional_logic' => [],
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'default_value' => '#ffffff',
            ])
            ->addTab('backend')
            ->addTrueFalse('use_nice_dashboard', [
                'label' => 'use blitz UI?',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => [],
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'message' => '',
                'default_value' => 1,
                'ui' => 1,
                'ui_on_text' => 'Yes',
                'ui_off_text' => 'No',
            ])
            ->addTrueFalse('admin_bar_enabled', [
                'label' => 'Show admin bar?',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => [],
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'message' => '',
                'default_value' => 0,
                'ui' => 1,
                'ui_on_text' => 'Yes',
                'ui_off_text' => 'No',
            ])
            ->addTrueFalse('admin_menu_enabled', [
                'label' => 'Show admin menu?',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => [],
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'message' => '',
                'default_value' => 0,
                'ui' => 1,
                'ui_on_text' => 'Yes',
                'ui_off_text' => 'No',
            ])
            ->addColorPicker('admin_background_color', [
                'label' => 'Dashboard background color',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => [],
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'default_value' => '#e2e2e2',
            ])
            ->addColorPicker('admin_text_color', [
                'label' => 'Dashboard text color',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => [],
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'default_value' => '#333',
            ])
            ->addTab('frontend')
                ->addTrueFalse('show_header', [
                    'label' => 'Show header?',
                    'default_value' => 1,
                    'ui' => 1,
                    'ui_on_text' => 'Yes',
                    'ui_off_text' => 'No',
                ])
                ->addTrueFalse('show_navigation', [
                    'label' => 'Show Navigation?',
                    'default_value' => 1,
                    'ui' => 1,
                    'ui_on_text' => 'Yes',
                    'ui_off_text' => 'No',
                ])
                ->addTrueFalse('show_slider', [
                    'label' => 'Show slider?',
                    'default_value' => 0,
                    'ui' => 1,
                    'ui_on_text' => 'Yes',
                    'ui_off_text' => 'No',
                ])
                ->addTrueFalse('show_featured_image', [
                    'label' => 'Show featured image?',
                    'default_value' => 1,
                    'ui' => 1,
                    'ui_on_text' => 'Yes',
                    'ui_off_text' => 'No',
                ])
                ->addTrueFalse('show_title', [
                    'label' => 'Show title of post or page?',
                    'default_value' => 1,
                    'ui' => 1,
                    'ui_on_text' => 'Yes',
                    'ui_off_text' => 'No',
                ])
            ->addTab('misc')
            ->addTrueFalse('disable_jquery', [
                'label' => 'Disable jQuery?',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => [],
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'message' => '',
                'default_value' => 0,
                'ui' => 1,
                'ui_on_text' => 'Yes',
                'ui_off_text' => 'No',
            ])
            ->addTrueFalse('disable_emoji', [
                'label' => 'Disable emoji scripts?',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => [],
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'message' => '',
                'default_value' => 1,
                'ui' => 1,
                'ui_on_text' => 'Yes',
                'ui_off_text' => 'No',
            ])
            ->addTrueFalse('show_admin_bar', [
                'label' => 'Show admin bar?',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => [],
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'message' => '',
                'default_value' => 0,
                'ui' => 1,
                'ui_on_text' => 'Yes',
                'ui_off_text' => 'No',
            ])
            ->addPostObject('page_404', [
                'label' => 'Select a 404 page.',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => [],
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'post_type' => ['page'],
                'taxonomy' => [],
                'allow_null' => 1,
                'multiple' => 0,
                'return_format' => 'object',
                'ui' => 1,
            ]);

            // add remote tab if site is not yet connected to a network
            $isConnected = get_option('isConnected');
            if (true != $isConnected) {
                $themeSettings
                ->addTab('remote')
                ->addTrueFalse('use_remote', [
                    'label' => 'Attach this Site to a remote Network?',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => [],
                    'message' => '',
                    'default_value' => 0,
                    'ui' => 1,
                    'ui_on_text' => 'Yes',
                    'ui_off_text' => 'No',
                ])
                ->addText('rm_secret_code', [
                    'label' => 'Secret code',
                    'instructions' => 'enter this in your Remote Dashboard',
                    'required' => 0,
                    'condition_logic' => [],
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'readonly' => 1,
                    'maxlength' => '',
                ])->conditional('use_remote', '==', '1')
                ->addTrueFalse('refresh_secret', [
                    'label' => 'Generate a new secret upon save?',
                    'instructions' => 'use this if something goes wrong while connecting to your Remote network.',
                    'required' => 0,
                    'conditional_logic' => [],
                    'message' => '',
                    'default_value' => 0,
                    'ui' => 1,
                    'ui_on_text' => 'Yes',
                    'ui_off_text' => 'No',
                ])->conditional('use_remote', '==', '1');
            }
            $themeSettings
            ->setGroupConfig('position', 'acf_after_title')
            ->setLocation('options_page', '==', 'blitz-set');

        acf_add_local_field_group($themeSettings->build());
        add_filter('acf/load_value/name=rm_secret_code', [$this, 'generateSecret'], 10, 3);
    }

    /**
     * Generate a new secret for this site. The secret will be only
     * generated once and is used to connect a site to a Remote network
     * 
     * @param string $value - the value of the field
     * @param int $post_id - the current post_id loaded
     * @param array $field - the current field
     */
    public function generateSecret($value, $post_id, $field)
    {
        // user intended refresh
        $refresh = get_field('refresh_secret', 'option');

        // site is already connected
        $isConnected = get_option('isConnected');

        if (true != $isConnected) {
            // secret has not been generated yet or should be refreshed by userintent
            if ('' === $value || true === $refresh) {
                $value = md5(mt_rand(0, PHP_INT_MAX).time());
            }
        }

        return $value;
    }

    /**
     * Load options page
     * 
     * @return void
     */
    public function loadObject(): void
    {
        $args = [
            'page_title' => 'Blitz Theme Settings',
            'menu_title' => 'Blitz',
            'menu_slug'  => 'blitz-set',
            'capability' => 'manage_options',
            'position'   => false,
            'icon_url'   => false,// 'http://127.0.0.1/wp-content/uploads/2019/10/cat.jpg',
            'redirect'   => true,
            'post_id'    => 'options',
            'autoload'   => false,
        ];

        acf_add_options_page($args);
    }

    /**
     * Register an endpoint to fetch the page general settings
     */
    public function registerEndpoints(): void
    {
        if (false === BlitzApi::registerEndpoint(
            'settings',
            [$this, 'getSettings']
        )){
            echo 'WARNING: endpoint registration failed for some reason maybe your callback is not callable.'.PHP_EOL;
        }
    }

    /**
     * Return the page settings
     * 
     * @param \WP_REST_Request $request - the request object
     * 
     * @return \WP_REST_Response - the settings for the requested chunk
     */
    public function getSettings(\WP_REST_Request $request): \WP_REST_Response
    {
        $postId = $request->get_header('B-PID');
        if (false != $postId) {
            $settings = $this->frontend()->getSettings($postId);
            $returnCode = 200;
        } else {
            $settings['is404'] = true;
            $returnCode = 404;
        }

        $response = new \WP_REST_Response($settings, $returnCode);
        return $response;
    }
}
