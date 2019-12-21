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
 * Class BlitzFrontend - any frontend changes will reside here
 * which will mostly be hooks or action filters
 * 
 * @category Theme
 * @package  Blitz
 * @author   Sebo <sebo@42geeks.gg>
 * @license  GPLv2 https://opensource.org/licenses/gpl-2.0.php
 * @link     https://42geeks.gg/
 * @version  1.0
 */
class BlitzFrontend
{
    /**
     * Init Blitz Frontend
     * 
     */
    public function __construct(BlitzAssets $assets)
    {
		$showAdminBar = get_field('show_admin_bar', 'option');
		show_admin_bar($showAdminBar);
        add_action('wp_enqueue_scripts', [$assets, 'enqueueFrontendAssets']);
        add_action('get_footer', [$assets, 'enqueueFooterCss']);

        BlitzApi::registerEndpoint(
            'content',
            [$this, 'getContent']
        );
    }
    
    /**
     * Return the page content
     * 
     * @param \WP_REST_Request $request - the request object
     * 
     * @return \WP_REST_Response - the page content object which may
     * includes navigation, slider images or post content rendered
     */
    public function getContent(\WP_REST_Request $request): \WP_REST_Response
    {
        $parts = [
            'header',
            'content',
            'footer'
        ];

        $postId     = $request->get_header('B-PID');
        $part       = $request->get_param('load');
        if (false === in_array($part, $parts)) {
            $part = 'header';
        }

        $returnCode = 200;
        
        if ('header' === $part) {
            $content['navigation'] = $this->_getNavigation(['main', 'top']);
            $content['baseUrl']    = Blitz::$siteUrl;
        }

        $response = new \WP_REST_Response($content, $returnCode);
        return $response;
    }
	
	/**
	 * Get frontend settings (colors, hidden items etc.)
     * 
     * @param int $postId (optional) - the post or page id the content is to
     * be fetched for - posts and pages can have their header hidden, no navigation etc.
     * 
	 * @return array - all settings to built the page
	 */
	public function getSettings(int $postId): array
	{
        $data['textColor']       = get_field('frontend_text_color', 'option');
        $data['backgroundColor'] = get_field('frontend_background_color', 'option');
        $data['showHeader']      = get_field('show_header', 'option');
        if (true === $data['showHeader']) {
            $data['showNavigation'] = get_field('show_navigation', 'option');
        }

        /* 
            we include the logo url here since we
            do not want to have the logo in header
            for some layouts
        */
        $logo = get_field('logo', 'option');
        $data['logoUrl'] = $logo['url'];

        return $data;
    }
    
    /**
     * Get the frontend header settings
     * 
     * @return array - the settings including top and main navigation
     */
    public function header(): array
    {
        $logo = get_field('logo', 'option');
        $showHead = get_field('show_header', 'option');
        $showNav = false;
        if (false != $showHead) {
            $showNav  = get_field('show_navigation', 'option');
        }

        $data = [
            'navigation' => $this->_getNavigation(['main', 'top']),
            'logo'       => $logo['url'],
            'showHead'   => $showHead,
            'showNav'    => $showNav
        ];

        return $data;
    }

    /**
     * Get the frontend content settings
     * 
     * @param int $postId - the id of the current post requested
     * 
     * @return array - the settings for the content
     */
    private function content(int $postId): array
    {
		/* 
			TODO: currentId is false when no static page is 
			selected to act as home, this would mean no content is sent
			to svelte frontend
        **/
        // TODO: create theme "Post" object
        $content = [];
		$currentId = $postId;
		$post = new \stdClass;
        $post->post_content = '';
        $post->post_type = 'page';
		if ($currentId !== false){
			$post = get_post($currentId);
        }
        
        if (null === $post->post_content) {
            $p = get_field('page_404', 'option');
            $post = $p !== false ? $p : $post;
        }

        $sliderData = [];
        $postThumbnail = '';

        // check if slider has been selected
        $slider = get_field('page_slider', $post->ID);
        if (false != $slider) {
            // slider is a WP_Post object containing acf repeater subfields
            $slides = get_field('slides', $slider->ID);
            if (false != $slides) {
                foreach ($slides as $slide) {
                    $s = new \stdClass;
                    $imgSrc = wp_get_attachment_image_src($slide['slider_image']['ID'], 'full');
                    $s->imageUrl = isset($imgSrc[0]) ? $imgSrc[0] : '';
                    $s->text = $slide['slide_text'];
    
                    if (false !== $slide['show_button']) {
                        $s->btnLink = $slide['button_link'];
                    }
    
                    $sliderData[] = $s;
                }
            }
        } else {
            // header options (featured image or slider)
            $showFeaturedImage = get_field('show_featured_image', 'option');
            if (false === $slider && false !== $showFeaturedImage) {
                $postThumbnail = get_the_post_thumbnail($post->ID);
            }
        }

        // title global setting
        $showTitle = false != get_field('show_title', 'option');

        // we probably need do_shortcode as well here as some plugins still rely on shortcodes
        $content = [
            'postTitle'	    => $post->post_title,
            'postThumbnail' => $postThumbnail,
            'sliderData'    => $sliderData,
            'is404'         => isset($p),
            'showTitle'     => $showTitle,
            'pt'            => $post->post_type
        ];

        return $content;
    }

    /**
     * Get the frontend footer settings
     * 
     * @return array - the settings including footer navigation
     */
    public function footer(): array
    {
        $data = [
            'navigation' => $this->_getNavigation(['footer'])
        ];

        return $data;
    }

	/**
	 * Get all menus with their items
     * 
     * @param array $slugs - the slugs to get the navigation items for
	 * 
	 * @return array - the entire navigation set 
	 */
    private function _getNavigation(array $slugs): array
	{
		$navigation = [];
		foreach($slugs as $slug) {
            $navItems = wp_get_nav_menu_items($slug);
            $currentMenu = [];
            if (false != $navItems) {
                foreach($navItems as $navItem) {
                    $item = [
                        'ID'      => $navItem->ID,
                        'url'     => $navItem->url,
                        'title'   => $navItem->title,
                        'classes' => $navItem->classes,
                        'parent'  => $navItem->menu_item_parent,
                    ];

                    $currentMenu[] = $item;
                }
            }
            $navigation[$slug] = $currentMenu;
		}

		return $navigation;
	}
}
