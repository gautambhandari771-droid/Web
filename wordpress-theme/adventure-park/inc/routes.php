<?php
/**
 * Files the site serves at fixed addresses, built from the settings:
 * /sw.js (offline copy), /llms.txt (summary for AI assistants), /site.webmanifest,
 * /.well-known/security.txt, and robots.txt (through WordPress's own robots.txt).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Version of the offline copy: changes whenever a page, a setting or a theme file changes. */
function adventure_park_sw_version() {
	$parts = array( ADVENTURE_PARK_VERSION, wp_json_encode( get_theme_mods() ) );
	foreach ( array( 'styles.css', 'theme.css', 'head.js', 'site.js', 'hero-fx.js' ) as $file ) {
		$parts[] = adventure_park_asset_version( $file );
	}
	foreach ( adventure_park_pages() as $id ) {
		$parts[] = get_post_modified_time( 'U', true, $id );
	}
	return substr( md5( implode( '|', $parts ) ), 0, 10 );
}

function adventure_park_sw() {
	$assets = array();
	foreach ( array( 'theme.css', 'styles.css', 'head.js', 'hero-fx.js', 'site.js' ) as $file ) {
		$assets[] = add_query_arg( 'ver', adventure_park_asset_version( $file ), get_theme_file_uri( 'assets/' . $file ) );
	}
	$assets[] = get_theme_file_uri( 'assets/fonts/plus-jakarta-sans.woff2' );
	$assets[] = get_theme_file_uri( 'assets/fonts/instrument-serif-italic-latin.woff2' );
	$pages = array_map( 'adventure_park_url', array( 'home', 'about', 'booking', 'contact' ) );
	$flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
	return strtr( (string) file_get_contents( __DIR__ . '/sw.js' ), array(
		'__VERSION__'    => adventure_park_sw_version(),
		'__ASSETS__'     => wp_json_encode( $assets, $flags ),
		'__PAGES__'      => wp_json_encode( $pages, $flags ),
		'__ASSET_PATH__' => wp_parse_url( trailingslashit( adventure_park_assets_url() ), PHP_URL_PATH ),
		'__LOGO__'       => get_theme_file_uri( 'assets/logo.png' ),
	) );
}

function adventure_park_llms() {
	$faq = array();
	foreach ( adventure_park_faq() as $item ) {
		$answer = preg_replace( '#<a [^>]*href="([^"]*)"[^>]*>(.*?)</a>#', '[$2]($1)', $item[1] );
		$faq[] = '- **' . $item[0] . '** ' . trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( str_replace( '<br>', ' ', $answer ) ) ) );
	}
	$text = str_replace( '{faq}', implode( "\n", $faq ), (string) file_get_contents( __DIR__ . '/llms.txt' ) );
	return adventure_park_fill( $text );
}

function adventure_park_manifest() {
	return wp_json_encode( array(
		'name'             => get_bloginfo( 'name' ),
		'short_name'       => 'Adventure Park',
		'description'      => 'River rafting, bungee jumping, zip line, luxury camps & cottages in Shivpuri, Rishikesh.',
		'lang'             => get_bloginfo( 'language' ),
		'start_url'        => wp_parse_url( home_url( '/' ), PHP_URL_PATH ) ?: '/',
		'scope'            => wp_parse_url( home_url( '/' ), PHP_URL_PATH ) ?: '/',
		'display'          => 'standalone',
		'background_color' => '#f7fbfc',
		'theme_color'      => '#f28a2e',
		'icons'            => array(
			array( 'src' => get_theme_file_uri( 'assets/icon-192.png' ), 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any' ),
			array( 'src' => get_theme_file_uri( 'assets/apple-touch-icon.png' ), 'sizes' => '180x180', 'type' => 'image/png', 'purpose' => 'any' ),
		),
	), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
}

function adventure_park_security_txt() {
	$lines = array(
		'Contact: mailto:' . adventure_park_setting( 'email' ),
		'Contact: tel:' . str_replace( ' ', '-', trim( adventure_park_setting( 'phone_main' ) ) ),
		'Expires: ' . gmdate( 'Y-m-d\T00:00:00.000\Z', strtotime( '+1 year' ) ),
		'Preferred-Languages: en, hi',
		'Canonical: ' . home_url( '/.well-known/security.txt' ),
	);
	return implode( "\n", $lines ) . "\n";
}

/** Serve the files above before WordPress looks for a page at that address. */
add_action( 'parse_request', function () {
	$path = (string) wp_parse_url( isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '', PHP_URL_PATH ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- compared against fixed names only
	$base = (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH );
	if ( '' !== $base && 0 === strpos( $path, $base ) ) {
		$path = substr( $path, strlen( $base ) );
	}
	$path = ltrim( $path, '/' );
	$routes = array(
		'sw.js'                     => array( 'application/javascript; charset=utf-8', 'adventure_park_sw', array( 'Cache-Control: no-cache', 'Service-Worker-Allowed: ' . ( $base ? $base : '/' ) ) ),
		'llms.txt'                  => array( 'text/plain; charset=utf-8', 'adventure_park_llms', array( 'Cache-Control: public, max-age=3600' ) ),
		'site.webmanifest'          => array( 'application/manifest+json; charset=utf-8', 'adventure_park_manifest', array( 'Cache-Control: public, max-age=86400' ) ),
		'.well-known/security.txt'  => array( 'text/plain; charset=utf-8', 'adventure_park_security_txt', array( 'Cache-Control: public, max-age=86400' ) ),
	);
	if ( ! isset( $routes[ $path ] ) ) {
		return;
	}
	list( $type, $callback, $headers ) = $routes[ $path ];
	status_header( 200 );
	header( 'Content-Type: ' . $type );
	header( 'X-Content-Type-Options: nosniff' );
	foreach ( $headers as $header ) {
		header( $header );
	}
	echo call_user_func( $callback ); // phpcs:ignore WordPress.Security.EscapeOutput -- plain text, JSON and JavaScript built from sanitized settings
	exit;
}, 0 );

/** robots.txt: search engines and AI assistants are welcome (WordPress serves it when there is no robots.txt file). */
add_filter( 'robots_txt', function ( $output, $public ) {
	if ( ! $public ) {
		return $output;
	}
	return adventure_park_fill( (string) file_get_contents( __DIR__ . '/robots.txt' ) );
}, 10, 2 );
