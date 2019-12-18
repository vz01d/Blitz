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
 * Class BlitzBackend - this class will change the WordPress
 * backend layout as well as the login area
 * 
 * @category Theme
 * @package  Blitz
 * @author   Sebo <sebo@42geeks.gg>
 * @license  GPLv2 https://opensource.org/licenses/gpl-2.0.php
 * @link     https://42geeks.gg/
 * @version  1.0
 */
class BlitzBackend
{
    /**
     * Init Backend changes
     * 
     */
    public function __construct(BlitzAssets $assets)
    {
        // check if classic dashboard is desired
        $uiEnabled       = get_field('use_nice_dashboard', 'option');
        if (true === $uiEnabled) {
            add_action('login_enqueue_scripts', [$this, 'loadBlitzLoginUI']);
            add_filter('login_headerurl', [$this, 'changeLoginUrl']);
            add_filter('contextual_help', [$this, 'removeHelpTabs'], 999, 3);
            add_filter('admin_footer_text', '__return_false');
            add_filter( 'update_footer', [$this, 'setCreatorInfo'], 11);
        }

        add_action('admin_enqueue_scripts', [$assets, 'enqueueBackendAssets']);
    }

    public function setCreatorInfo()
    {
        $adminTextColor = get_field('admin_text_color', 'option');
        return '<div style="text-align:left;color:'.$adminTextColor.';">made with <span class="dashicons dashicons-heart" style="color:red;"></span> powered by <a href="https://wp.org/" target="_blank" title="WordPress" style="text-decoration:none;"><span class="dashicons dashicons-wordpress"></span></a></div>';
    }

    public function removeHelpTabs($old_help, $screen_id, $screen)
    {
        $screen->remove_help_tabs();
        return $old_help;
    }

    /**
     * Load the blitz UI which will change the way
     * the WordPress admin backend works UI/UX wise.
     * 
     * @return void
     */
    public function loadBlitzLoginUI(): void
    {
        wp_enqueue_style('blitz-backend', Blitz::$themeAssetUri . '/css/login.css');
        
        // output custom logo in wp-login if selected
        $logo = get_field('logo', 'option');
        $adminBgColor = get_field('admin_background_color', 'option');
        $adminTextColor = get_field('admin_text_color', 'option');
    
        $out = '';
        $out .= '<style type="text/css">';
            // form inputs
            $out .= 'body.login div#login form#loginform .button.wp-hide-pw {';
                $out .= 'color: '.$adminTextColor.' !important;}';
            $out .= 'body.login div#login form#loginform input:not([type="checkbox"]) {';
                $out .= 'border:none;border-radius:0px;border-bottom: 2px solid '.$adminTextColor.';color:'.$adminTextColor.';}';
            $out .= 'body.login div#login form#loginform input[type="checkbox"] {';
                $out .= 'border-radius:0px;transform:scale(1.5);border: 1px solid '.$adminTextColor.';}';
            $out .= 'body.login { background: '.$adminBgColor.'; color: '.$adminTextColor.'; }';

            // login logo
            if (in_array($GLOBALS['pagenow'], ['wp-login.php'])) {
                $out .= '#login h1 a, .login h1 a {';
                if (null !== $logo && is_array($logo)) {
                    $logoUrl = $logo['url'];
                    $out .= 'background-image: url('.$logoUrl.');';
                }
                $out .= 'height:65px;';
                $out .= 'width:320px;';
                $out .= 'background-size: 320px 65px;';
                $out .= 'background-repeat: no-repeat;';
                    $out .= 'padding-bottom: 10px;';
                $out .= '}';
            }

            // submit
            $out .= 'body.login div#login form#loginform p.submit input#wp-submit {';
                $out .= 'background:'.$adminBgColor.';color:'.$adminTextColor.';border: 2px solid '.$adminTextColor.';font-size:22px;width:100%;margin-top:20px;';
            $out.= '}';
            $out .= 'body.login div#login form#loginform p.submit input#wp-submit:hover {';
                $out .= 'background:'.$adminTextColor.';color:'.$adminBgColor.';border: 2px solid '.$adminBgColor.';transition:0.6s;';
            $out.= '}';

            // lost pw and back to blog links
            $out .= 'body.login div#login p#nav, body.login div#login p#backtoblog {';
                $out .= 'padding:0px;width:50%;background:'.$adminBgColor.';text-align:center;float:right;margin-top:5px;';    
            $out.= '}';
            $out .= 'body.login div#login p#backtoblog {';
                $out .= 'float:left;';    
            $out.= '}';
            $out .= 'body.login div#login p#nav a, body.login div#login p#backtoblog a {';
                $out .= 'color:'.$adminTextColor.';display:block;padding:6px;';
            $out.= '}';
            $out .= 'body.login div#login p#backtoblog a:hover, body.login div#login p#nav a:hover {';
                $out .= 'background:'.$adminTextColor.';color:'.$adminBgColor.';transition:0.6s;';
            $out.= '}';
                
        $out .= '</style>';

        echo $out;
    }

    /**
     * Change the url on the login page logo link
     * 
     * @return string - the new url
     */
    public function changeLoginUrl(): string
    {
        return home_url();
    }
}
