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
	}
	
	/**
	 * Get data for the specific chunk of the page for example the header, footer or content
	 * there maybe data available to all chunks
     * 
     * @param string $chunk - the chunk to load
     * @param int $postId (optional) - the post or page id the content is to
     * be fetched from
     * 
	 * @return array - all required page data for the requested chunk
	 */
	public function getChunkdata(string $chunk, int $postId = 0): array
	{
        $possibleChunks = [
            'header',
            'footer',
            'content'
        ];

        if (!in_array($chunk, $possibleChunks)) {
            $chunk = 'header';
        }

        $data = [];
        if (is_callable([$this, $chunk])) {
            if ('content' === $chunk){
                $data = $this->$chunk($postId);
            } else {
                $data = $this->$chunk();
            }
        }

        // apply these to all requests
        // colors
        $data['textColor'] = get_field('frontend_text_color', 'option');
        $data['backgroundColor'] = get_field('frontend_background_color', 'option');

        // base Url
        $data['baseUrl'] = Blitz::$siteUrl;

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
    public function content(int $postId): array
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
