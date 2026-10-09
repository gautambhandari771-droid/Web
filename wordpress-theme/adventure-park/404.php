<?php
/**
 * "Page not found".
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
echo adventure_park_render( '404' ); // phpcs:ignore WordPress.Security.EscapeOutput -- every value is escaped in inc/render.php
get_footer();
