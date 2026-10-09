<?php
/**
 * Top of every page: <head>, skip link, header bar and the mobile menu.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!doctype html>
<html <?php language_attributes(); ?> class="scroll-smooth scroll-pt-20">
<head>
  <meta charset="<?php bloginfo( 'charset' ); ?>" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
  <?php wp_head(); ?>
</head>
<body <?php body_class( 'min-h-screen overflow-x-clip antialiased' ); ?><?php adventure_park_body_attributes(); ?>>
<?php wp_body_open(); ?>

  <?php echo adventure_park_render( 'header' ); // phpcs:ignore WordPress.Security.EscapeOutput -- every value is escaped in inc/render.php ?>
