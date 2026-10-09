<?php
/**
 * Styles, scripts, menu location, a lean <head> and security headers.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'responsive-embeds' );
	register_nav_menus( array( 'primary' => __( 'Main menu', 'adventure-park' ) ) );
} );

/** Version of an asset file: changes when the file changes, so browsers can keep it for a year. */
function adventure_park_asset_version( $file ) {
	$path = get_theme_file_path( 'assets/' . $file );
	return file_exists( $path ) ? substr( md5( (string) filemtime( $path ) . filesize( $path ) . ADVENTURE_PARK_VERSION ), 0, 10 ) : ADVENTURE_PARK_VERSION;
}

/** Pages designed by the theme (everything except ordinary pages and posts you add yourself). */
function adventure_park_is_designed_page() {
	if ( is_404() || is_front_page() ) {
		return true;
	}
	return is_page() && '' !== adventure_park_page_key( get_queried_object_id() );
}

add_action( 'wp_enqueue_scripts', function () {
	$assets = get_theme_file_uri( 'assets/' );
	wp_enqueue_style( 'adventure-park-theme', $assets . 'theme.css', array(), adventure_park_asset_version( 'theme.css' ) );
	wp_enqueue_style( 'adventure-park', $assets . 'styles.css', array( 'adventure-park-theme' ), adventure_park_asset_version( 'styles.css' ) );
	// Light/dark mode must be known before the page paints, so this one stays in the <head>
	wp_enqueue_script( 'adventure-park-head', $assets . 'head.js', array(), adventure_park_asset_version( 'head.js' ), false );
	wp_enqueue_script( 'adventure-park-hero-fx', $assets . 'hero-fx.js', array(), adventure_park_asset_version( 'hero-fx.js' ), array( 'in_footer' => true, 'strategy' => 'defer' ) );
	wp_enqueue_script( 'adventure-park-site', $assets . 'site.js', array(), adventure_park_asset_version( 'site.js' ), true );

	// The theme's own pages don't use WordPress block styles; pages you add yourself do
	if ( ! adventure_park_is_designed_page() ) {
		wp_enqueue_style( 'adventure-park-content', $assets . 'content.css', array( 'adventure-park' ), adventure_park_asset_version( 'content.css' ) );
	} else {
		foreach ( array( 'wp-block-library', 'wp-block-library-theme', 'classic-theme-styles', 'global-styles' ) as $handle ) {
			wp_dequeue_style( $handle );
		}
	}
}, 20 );

// Only load the CSS of blocks a page actually uses
add_filter( 'should_load_separate_core_block_assets', '__return_true' );

// Both fonts are used on the first screen of every page: fetch them first thing
add_action( 'wp_head', function () {
	$fonts = get_theme_file_uri( 'assets/fonts/' );
	echo '<link rel="preload" href="' . esc_url( $fonts . 'plus-jakarta-sans.woff2' ) . '" as="font" type="font/woff2" crossorigin />' . "\n";
	echo '<link rel="preload" href="' . esc_url( $fonts . 'instrument-serif-italic-latin.woff2' ) . '" as="font" type="font/woff2" crossorigin />' . "\n";
	echo '<meta name="theme-color" content="#f7fbfc" media="(prefers-color-scheme: light)" />' . "\n";
	echo '<meta name="theme-color" content="#0b151c" media="(prefers-color-scheme: dark)" />' . "\n";
	echo '<link rel="manifest" href="' . esc_url( home_url( '/site.webmanifest' ) ) . '" />' . "\n";
	if ( ! has_site_icon() ) {
		echo '<link rel="icon" href="' . esc_url( get_theme_file_uri( 'assets/favicon.ico' ) ) . '" sizes="any" />' . "\n";
		echo '<link rel="icon" href="' . esc_url( get_theme_file_uri( 'assets/icon-192.png' ) ) . '" type="image/png" sizes="192x192" />' . "\n";
		echo '<link rel="apple-touch-icon" href="' . esc_url( get_theme_file_uri( 'assets/apple-touch-icon.png' ) ) . '" />' . "\n";
	}
}, 1 );

// A lean <head>: no emoji script, generator tag or discovery links the site doesn't need
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
remove_action( 'wp_head', 'feed_links_extra', 3 );
add_filter( 'emoji_svg_url', '__return_false' );

/** Body attributes: the theme's classes, plus where the offline copy (sw.js) lives. */
function adventure_park_body_attributes() {
	echo ' data-sw="' . esc_url( home_url( '/sw.js' ) ) . '"';
}

// The offline copy is only for visitors: in the dashboard, remove it from this browser
add_action( 'admin_enqueue_scripts', function () {
	wp_enqueue_script( 'adventure-park-sw-off', get_theme_file_uri( 'assets/sw-off.js' ), array(), adventure_park_asset_version( 'sw-off.js' ), true );
} );

/** Content-Security-Policy for visitors: only this site's scripts may run (see Customize → Adventure Park → Security). */
function adventure_park_csp() {
	return "default-src 'self'; script-src 'self' 'inline-speculation-rules'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'; connect-src 'self' https://formsubmit.co; form-action 'self' https://formsubmit.co; frame-src 'self'; frame-ancestors 'self'; base-uri 'self'; object-src 'none'";
}

add_action( 'send_headers', function () {
	if ( is_admin() || headers_sent() ) {
		return;
	}
	header( 'X-Content-Type-Options: nosniff' );
	header( 'X-Frame-Options: SAMEORIGIN' );
	header( 'Referrer-Policy: strict-origin-when-cross-origin' );
	header( 'Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=(), usb=()' );
	// Pages opened from this site (WhatsApp, Instagram, Maps) can't reach back into it
	header( 'Cross-Origin-Opener-Policy: same-origin' );
	if ( is_ssl() ) {
		header( 'Strict-Transport-Security: max-age=31536000' );
	}
	// Logged-in users (admin bar), the Customizer preview and plugins that ask for it get no script policy
	$previewing = isset( $_GET['customize_changeset_uuid'] ) || isset( $_GET['preview'] ); // phpcs:ignore WordPress.Security.NonceVerification
	if ( adventure_park_setting( 'strict_csp' ) && ! is_user_logged_in() && ! $previewing ) {
		header( 'Content-Security-Policy: ' . adventure_park_csp() );
	}
} );

// Pages created by the theme don't take comments
add_filter( 'comments_open', function ( $open, $post_id ) {
	return adventure_park_page_key( $post_id ) ? false : $open;
}, 10, 2 );
