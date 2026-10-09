<?php
/**
 * Home page: hero, activities and prices, stay, food, safety, how it works, FAQ.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
echo adventure_park_render( 'home', 'home' ); // phpcs:ignore WordPress.Security.EscapeOutput -- every value is escaped in inc/render.php
get_footer();
