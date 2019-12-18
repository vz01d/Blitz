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
namespace blitz;

use blitz\core;

/**
 * Class Blitz - main class for the theme
 * 
 * @category Theme
 * @package  Blitz
 * @author   Sebo <sebo@42geeks.gg>
 * @license  GPLv2 https://opensource.org/licenses/gpl-2.0.php
 * @link     https://42geeks.gg/
 */
class Blitz
{
    public static $themeRootPath  = null;
    public static $themeRootUri   = null;
    public static $themeAssetUri  = null;
    public static $themeAssetPath = null;
    public static $themeClasses   = [];
    public static $siteUrl        = '';

    public static $themeRef              = 0x0000;
    public static $themeAssets           = 0x0001;
    public static $themeSettings         = 0x0002;
    public static $themeFrontend         = 0x0003;
    public static $themeBackend          = 0x0004;
    public static $themeApi              = 0x0005;
    public static $themeBlitzRemoteApi = 0x0006;

    /**
     * Initialize theme with root path and uri provided
     * 
     * @param string $rootPath - the abs path to the theme directory
     * @param string $rootUri  - the uri to the theme directory
     */
    public function __construct($rootPath = '/', $rootUri = '/')
    {
        if (null === self::$themeRootPath) {
            self::$themeRootPath  = $rootPath;
            self::$themeAssetPath = $rootPath . '/assets';
        }

        if (null === self::$themeRootUri) {
            self::$themeRootUri  = $rootUri;
            self::$themeAssetUri = $rootUri . '/assets';
        }

        // load objects, inject dependencies
        if (! isset(self::$themeClasses[self::$themeRef])) {
            self::$themeClasses[self::$themeApi]    = new core\BlitzApi;
            self::$themeClasses[self::$themeAssets] = new core\BlitzAssets;

			$blitzObjects = new core\BlitzObjects(
				[
					'blitz\objects\Settings',
					'blitz\objects\Pages',
					'blitz\objects\Posts',
					'blitz\objects\Sliders',
                ]
			);
        }
        
        // load backend or frontend
        if (is_admin()) {
            if (! isset(self::$themeClasses[self::$themeBackend])) {
                self::$themeClasses[self::$themeBackend] = new core\BlitzBackend(
                    self::$themeClasses[self::$themeAssets]
                );
            }
        } else {
            if (! isset(self::$themeClasses[self::$themeFrontend])) {
                self::$themeClasses[self::$themeFrontend] = new core\BlitzFrontend(
                    self::$themeClasses[self::$themeAssets]
                );
            }
        }

        // add hooks
        add_action('init', [$this, 'loadCore']);
        add_action('after_setup_theme', [$this, 'afterSetup']);

        // TODO: move this somewhere else
        add_action('admin_init', [$this, 'disableRevisions']);
        // add_filter('allowed_block_types', [$this, 'cleanUpBlocks'], 10, 2);
    }

    /**
     * Remove most of the default wp blocks as
     * most blocks don't work using the current asset pipeline
     * 
     * @return array
     */
    public function cleanUpBlocks($allowed_blocks, $post): array
    {
        $allowed_blocks = [
            'core/columns',
            'core/image'
        ];

        return $allowed_blocks;
    }

    /**
     * Disable post revisions
     * @todo - create a setting for this
     * 
     * @return void
     */
    public function disableRevisions(): void
    {
        remove_post_type_support('page', 'revisions');
        remove_post_type_support('post', 'revisions');
        remove_post_type_support('attachment', 'revisions');
    }

    /**
     * Clean up default wp stuff
     * 
     * @return void
     */
    private function _cleanUp(): void
    {
        remove_action('wp_head', 'wp_generator');
        remove_action('wp_head', 'rsd_link');
        remove_action('wp_head', 'wlwmanifest_link');
        remove_action('wp_head', 'index_rel_link');
        remove_action('wp_head', 'feed_links', 2);
        remove_action('wp_head', 'feed_links_extra', 3);
        remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);
        remove_action('wp_head', 'wp_shortlink_wp_head', 10);
        
        $disableEmoji = get_field('disable_emoji', 'option');
        if (true === $disableEmoji) {
            remove_action('wp_head', 'print_emoji_detection_script', 7);
            remove_action('admin_print_scripts', 'print_emoji_detection_script');
            remove_action('wp_print_styles', 'print_emoji_styles');
            remove_action('admin_print_styles', 'print_emoji_styles'); 
            remove_filter('the_content_feed', 'wp_staticize_emoji');
            remove_filter('comment_text_rss', 'wp_staticize_emoji'); 
            remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
            add_filter('tiny_mce_themes', [$this, 'disable_emojis_tinymce']);
            add_filter('wp_resource_hints', [$this, 'disable_emojis_remove_dns_prefetch'], 10, 2);
        }
    
        // disable noise and throttle brutforce
        add_filter('xmlrpc_enabled', '__return_false');
    }

    /**
     * WP Theme after setup hook
     * 
     * @return void
     */
    public function afterSetup(): void
    {
        register_nav_menus(
            [
                'main' => __('Main Menu', 'blitz'),
                'top' => __('Top Menu', 'blitz'),
                'footer' => __('Footer Menu', 'blitz')
            ]
        );

        // TODO: create settings for this
        // Add default posts and comments RSS feed links to head.
		// add_theme_support('automatic-feed-links');

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support('title-tag');

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 */
		add_theme_support('post-thumbnails');
		set_post_thumbnail_size(1920, 1280);

        /*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support(
			'html5',
			[
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
			]
		);

        // Add theme support for selective refresh for widgets.
		add_theme_support( 'customize-selective-refresh-widgets' );

		// Add support for Block Styles.
		add_theme_support( 'wp-block-styles' );

		// Add support for full and wide align images.
		add_theme_support( 'align-wide' );

		// Add support for editor styles.
		add_theme_support( 'editor-styles' );

		// Enqueue editor styles.
		add_editor_style( 'style-editor.css' );

		// Add support for responsive embeds.
		add_theme_support('responsive-embeds');
    }

	// ### thx to kinsta https://kinsta.com/knowledgebase/disable-emojis-wordpress/
	// updated to PSR-4
    /**
     * Filter function used to remove the tinymce emoji theme.
     * 
     * @param array $themes 
	 * 
     * @return array Difference betwen the two arrays
     */
	public function disable_emojis_tinymce(array $themes): array
	{
        if (is_array($themes)) {
            return array_diff($themes, ['wpemoji']);
        } else {
            return array();
        }
    }
    
    /**
     * Remove emoji CDN hostname from DNS prefetching hints.
     *
     * @param array $urls URLs to print for resource hints.
     * @param string $relation_type The relation type the URLs are printed for.
	 * 
     * @return array Difference betwen the two arrays.
     **/
	public function disable_emojis_remove_dns_prefetch(array $urls, string $relation_type ): array
	{
        if ('dns-prefetch' === $relation_type) {
            // This filter is documented in wp-includes/formatting.php
            $emoji_svg_url = apply_filters('emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/');
    
            $urls = array_diff($urls, [$emoji_svg_url]);
        }
    
        return $urls;
    }
    

    /**
     * Init the cpt once and flush rewrites
     * 
     * @return void
     */
    public function activateTheme(): void
    {
        flush_rewrite_rules();
    }

    /**
     * Deactivate theme should flush rewrites
     * 
     * @return void
     */
    public function deactivateTheme(): void
    {
        flush_rewrite_rules();
    }

    /**
     * Load the themes core
     * 
     * @return void
     */
    public function loadCore(): void
    {
        $this->_cleanUp();
        self::$siteUrl = get_site_url();
        
        $useRemote = get_field('use_remote', 'option');
        if (true == $useRemote) {
            self::$themeClasses[self::$themeBlitzRemoteApi] = new core\BlitzRemoteApi;
        }
        
        self::$themeClasses[self::$themeRef] = $this;
    }

    /**
     * Reference to the Frontend object
     * 
     * @return core\BlitzFrontend - return reference to theme frontend
     */
    protected function frontend(): core\BlitzFrontend
    {
        return self::$themeClasses[self::$themeFrontend];
    }
    
    /**
     * Reference to the Backend object
     * 
     * @return BlitzBackend - return reference to theme backend
     */
    protected function backend(): BlitzBackend
    {
        return self::$themeClasses[self::$themeBackend];
	}
    
    /**
     * Reference to the api object
     * 
     * @return mixed - return reference to theme api
     */
    protected function api()
    {
        return self::$themeClasses[self::$themeApi];
	}
	
	/**
     * Reference to the assets object
     * 
     * @return mixed - return reference to theme assets
     */
    protected static function assets()
    {
        return self::$themeClasses[self::$themeAssets];
	}

    /**
     * Reference to the app object
     * 
     * @return mixed - return theme reference for global namespaced access
     */
    protected static function app()
    {
        return self::$themeClasses[self::$themeRef];
    }
}
