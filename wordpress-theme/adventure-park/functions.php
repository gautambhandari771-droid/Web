<?php
/**
 * Adventure Park theme.
 *
 * inc/settings.php  Customizer settings: prices, contact details, payment rules, photos
 * inc/fields.php    Text fields on the Home, About, Booking and Contact pages
 * inc/render.php    Fills the page templates (parts/*.html) with the settings and fields
 * inc/setup.php     Styles, scripts, menus and a lean <head>
 * inc/seo.php       Titles, descriptions, link previews and structured data
 * inc/routes.php    sw.js (offline copy), llms.txt, site.webmanifest, security.txt, robots.txt
 * inc/activate.php  Creates the pages, menu and front page when the theme is activated
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ADVENTURE_PARK_VERSION', '1.0.1' );

require_once __DIR__ . '/inc/settings.php';
require_once __DIR__ . '/inc/fields.php';
require_once __DIR__ . '/inc/render.php';
require_once __DIR__ . '/inc/setup.php';
require_once __DIR__ . '/inc/seo.php';
require_once __DIR__ . '/inc/routes.php';
require_once __DIR__ . '/inc/activate.php';
