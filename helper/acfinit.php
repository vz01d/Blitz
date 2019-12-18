<?php

// Define path and URL to the ACF plugin.
define('BLITZ_ACF_PATH', $themeRootPath . '/vendor/acf/');
define('BLITZ_ACF_URL', $themeRootUri . '/vendor/acf/');

// Include the ACF plugin.
include_once BLITZ_ACF_PATH . 'acf.php';

// Customize the url setting to fix incorrect asset URLs.
add_filter('acf/settings/url', 'blitzAcfSettingsUrl');
function blitzAcfSettingsUrl($url)
{
    return BLITZ_ACF_URL;
}
