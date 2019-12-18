<?php
/**
 * Header template.
 *
 * @package Blitz
 * @subpackage Templates
 * @since 1.0
 * @version 1.0
 *
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'geek protected.' );
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js no-svg">
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <style>
        body {
            margin: 0;
            max-width: 100%;
            overflow-x: hidden;
        }
    </style>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>