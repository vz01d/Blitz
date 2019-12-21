<?php
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

use blitz\interfaces\BlitzAsset;
use blitz\core\BlitzObjects;
use blitz\Blitz;

/**
 * Class BlitzAssets - Load all required assets
 * 
 * @category Theme
 * @package  Blitz
 * @author   Sebo <sebo@42geeks.gg>
 * @license  GPLv2 https://opensource.org/licenses/gpl-2.0.php
 * @link     https://42geeks.gg/
 */
class BlitzAssets extends BlitzObjects implements BlitzAsset
{
    public static $assets = [];

    /**
     * Empty
     */
    public function __construct()
    {
        add_filter('upload_mimes', [$this, 'setAllowedMimes']);
    }

    /**
     * Set additional allowed mimes for svg && webp
     * 
     * @param array $mimes - the  current mimes array
     * 
     * @return array - the updated mimes array
     * @todo - add options to configure mimes allowed
     */
    public function setAllowedMimes($mimes)
    {
        $mimes['svg'] = 'image/svg+xml';
        $mimes['webp'] = 'image/webp';
        return $mimes;
    }

    /**
     * Enqueue all theme backend assets
     * 
     * @return void
     */
    public function enqueueBackendAssets(): void
    {
        $uiEnabled        = get_field('use_nice_dashboard', 'option');
        $adminBarEnabled  = get_field('admin_bar_enabled', 'option');
        $adminMenuEnabled = get_field('admin_menu_enabled', 'option');
        $postTypes        = get_post_types();

        unset($postTypes['revision']);
        unset($postTypes['nav_menu_item']);
        unset($postTypes['custom_css']);
        unset($postTypes['customize_changeset']);
        unset($postTypes['user_request']);
        unset($postTypes['wp_block']);
		unset($postTypes['acf-field']);

		// current post or page permalink
		$permalink = get_the_permalink();
		
        $data = [
            'adminBarEnabled' => $adminBarEnabled,
            'adminMenuEnabled' => $adminMenuEnabled,
            'uiEnabled' => $uiEnabled,
			'postTypes' => array_values($postTypes),
			'postUrl' => $permalink === false ? '/' : $permalink,
            'siteUrl' => get_site_url()
        ];

        if (true != $adminBarEnabled) {
            wp_enqueue_style(
                'blitz-hideadminbar',
                Blitz::$themeAssetUri . '/css/hideadminbar.css',
                [],
                false
            );
        }

        if (true != $adminMenuEnabled) {
            wp_enqueue_style(
                'blitz-hideadminmenu',
                Blitz::$themeAssetUri . '/css/hideadminmenu.css',
                [],
                false
            );
		}
		
		if (true === $uiEnabled) {
            $data['adminTextColor'] = get_field('admin_text_color', 'option');
            $data['adminBgColor'] = get_field('admin_background_color', 'option');

            // use blitz menu for backend instead
            wp_enqueue_style(
                'blitz-menu',
                Blitz::$themeAssetUri . '/css/menu.css',
                [],
                false
            );

			wp_enqueue_style(
				'blitz-backend',
				Blitz::$themeAssetUri . '/css/admin.css',
				[],
				false
			);

            wp_register_script(
                'blitz-backend-menu',
                Blitz::$themeAssetUri . '/js/menu.js',
                ['blitz-backend'],
                false,
                true
            );

            wp_localize_script(
                'blitz-backend-menu',
                'BLITZ',
                $data
            );

            wp_enqueue_script('blitz-backend-menu');
		}

        wp_register_script(
            'blitz-backend',
            Blitz::$themeAssetUri . '/js/admin.js',
            [],
            false,
            true
        );

        wp_localize_script(
            'blitz-backend',
            'BLITZ',
            $data
        );
        
        wp_enqueue_script('blitz-backend');
    }

    /**
     * Load css in footer - since we have svelte-transitions in
     * frontend FOUC wouldn't be an issue
     * 
     * @return void
     */
    public function enqueueFooterCss(): void
    {
        wp_enqueue_style(
            'blitz-frontend',
            Blitz::$themeRootUri . '/frontend/public/components.css',
            [],
            false
        );

        // tailwind
        wp_enqueue_style(
            'blitz-frontend-utils',
            Blitz::$themeRootUri . '/frontend/public/utils.css',
            [],
            false
        );
    }

    /**
     * Enqueue all theme frontend assets and clean up jquery
     * as well as other unused stuff
     * 
     * @return void
     */
    public function enqueueFrontendAssets(): void
    {
        /**
         * Load theme assets
         */
        // remove jquery, emoji etc.
        wp_deregister_script('wp-embed');
        wp_deregister_script('wp-block-library');
        wp_dequeue_style('wp-block-library');
        wp_dequeue_style('wp-block-library-theme');
        
		$disablejQuery  = get_field('disable_jquery', 'option');
        if (true === $disablejQuery) {
            wp_deregister_script('jquery');
		}

		$disableEmoji  = get_field('disable_emoji', 'option');
        if (true === $disableEmoji) {
			wp_deregister_script('wp-emoji');
		}

        wp_register_script(
            'blitz-frontend',
            Blitz::$themeRootUri . '/frontend/public/bundle.js',
            [],
            false,
            true
        );

        $id = get_the_ID();
        $pt = get_post_type($id);
        wp_localize_script(
            'blitz-frontend',
            'BLITZ',
            [
                'nonce'       => wp_create_nonce('wp_rest'),
                'restBase'    => esc_url_raw(rest_url()),
                'settingsUrl' => esc_url_raw(rest_url().'blitz/' . \blitz\core\BlitzApi::API_VERSION.'/settings'),
                'contentUrl'  => esc_url_raw(rest_url().'blitz/' . \blitz\core\BlitzApi::API_VERSION.'/content'),
                'slidesUrl'   => esc_url_raw(rest_url().'blitz/' . \blitz\core\BlitzApi::API_VERSION.'/slides'),
                'currentID'   => $id,
                'pt'          => $pt != false ? $pt.'s' : false,
                'siteBase'    => Blitz::$siteUrl
            ]
        );
        
        wp_enqueue_script('blitz-frontend');
    }

    /**
     * Load objects custom fields
     * 
     * @return void
     */
    public function registerAsset(): void
    {
        /**
         * Plugin general settings
         */
    }

    /**
     * Load options page
     * 
     * @return void
     */
    public function localizeAsset(): void
    {
        
    }

    /**
     * Load options page
     * 
     * @return void
     */
    public function enqueueAsset(): void
    {
        
    }
}
