<?php
/**
 * The theme's functions.php file 
 * which is only used to enqueue the smelte app
 *
 * @category Theme
 * @package  Blitz
 * @author   Sebo <sebo@42geeks.gg>
 * @license  GPLv2 https://opensource.org/licenses/gpl-2.0.php
 * @link     https://42geeks.gg/
 */

// If this file is accessed directory, then abort.
if (! defined('WPINC')) {
    die;
}

// composer psr-4
if (file_exists(dirname(__FILE__) . '/vendor/autoload.php')) {
    include_once dirname(__FILE__) . '/vendor/autoload.php';
}

if (class_exists('blitz\\Blitz')) {
    define('BLITZ_VERSION', '1.3');

    // ensure path is only set once
    $themeRootPath = get_template_directory();
    $themeRootUri  = get_template_directory_uri();

    // init acf
    if (! function_exists('acf_add_options_page')) {
        include_once dirname(__FILE__) . '/helper/acfinit.php';
        add_filter('acf/settings/show_admin', '__return_false');
    }

    // run theme
    $theme = new \blitz\Blitz($themeRootPath, $themeRootUri);
    
    register_activation_hook(__FILE__, [$theme, 'activateTheme']);
    register_deactivation_hook(__FILE__, [$theme, 'deactivateTheme']);
}
