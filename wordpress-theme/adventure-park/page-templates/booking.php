<?php
/**
 * Template Name: Adventure Park – Booking page
 *
 * The Booking page with its own design. Its text is edited in the "Page text" box below the editor.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
echo adventure_park_render( 'booking', 'booking' ); // phpcs:ignore WordPress.Security.EscapeOutput -- every value is escaped in inc/render.php
get_footer();
