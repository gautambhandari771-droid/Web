<?php
/**
 * Titles, descriptions, link previews (WhatsApp, Facebook, X…) and structured data
 * (schema.org JSON-LD) for search engines and AI assistants, built from the settings so a
 * new price or phone number reaches Google too.
 *
 * If an SEO plugin (Yoast, Rank Math, All in One SEO, SEOPress) is active, it handles titles,
 * descriptions and link previews instead; the structured data stays.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function adventure_park_seo_plugin_active() {
	return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' ) || defined( 'SEOPRESS_VERSION' );
}

/** Which designed page is being shown: home, about, booking, contact, or ''. */
function adventure_park_current_key() {
	if ( is_front_page() ) {
		return 'home';
	}
	return is_page() ? adventure_park_page_key( get_queried_object_id() ) : '';
}

add_filter( 'pre_get_document_title', function ( $title ) {
	$key = adventure_park_current_key();
	if ( ! $key || adventure_park_seo_plugin_active() ) {
		return $title;
	}
	return adventure_park_fill( adventure_park_field_raw( $key, 'seo_title' ) );
} );

add_action( 'wp_head', function () {
	$key = adventure_park_current_key();
	if ( ! $key || adventure_park_seo_plugin_active() ) {
		return;
	}
	$title = adventure_park_fill( adventure_park_field_raw( $key, 'seo_title' ) );
	$desc  = adventure_park_fill( adventure_park_field_raw( $key, 'seo_description' ) );
	$url   = adventure_park_url( $key );
	$image = get_theme_file_uri( 'assets/og-image.png' );
	$alt   = adventure_park_defaults()['seo'][ $key ]['og_image_alt'];
	$tags = array(
		array( 'name', 'description', $desc ),
		array( 'property', 'og:type', 'website' ),
		array( 'property', 'og:site_name', get_bloginfo( 'name' ) ),
		array( 'property', 'og:locale', str_replace( '-', '_', get_bloginfo( 'language' ) ) ),
		array( 'property', 'og:title', $title ),
		array( 'property', 'og:description', $desc ),
		array( 'property', 'og:url', $url ),
		array( 'property', 'og:image', $image ),
		array( 'property', 'og:image:width', '1200' ),
		array( 'property', 'og:image:height', '630' ),
		array( 'property', 'og:image:alt', $alt ),
		array( 'name', 'twitter:card', 'summary_large_image' ),
		array( 'name', 'twitter:title', $title ),
		array( 'name', 'twitter:description', $desc ),
		array( 'name', 'twitter:image', $image ),
	);
	foreach ( $tags as $tag ) {
		echo '<meta ' . $tag[0] . '="' . esc_attr( $tag[1] ) . '" content="' . esc_attr( $tag[2] ) . '" />' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput -- attribute names are fixed above
	}
}, 2 );

// Let search engines show large image previews and full snippets
add_filter( 'wp_robots', function ( $robots ) {
	if ( adventure_park_current_key() && ! adventure_park_seo_plugin_active() ) {
		$robots['max-image-preview'] = 'large';
		$robots['max-snippet'] = '-1';
		$robots['max-video-preview'] = '-1';
	}
	return $robots;
} );

/** A value for inside a JSON string (without the surrounding quotes). */
function adventure_park_json_inner( $value ) {
	return substr( wp_json_encode( (string) $value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ), 1, -1 );
}

/** Structured data for one page, from schema/{page}.json with the settings filled in. */
function adventure_park_schema( $key ) {
	$json = (string) file_get_contents( get_theme_file_path( 'schema/' . $key . '.json' ) );
	$faq = array();
	foreach ( adventure_park_faq() as $item ) {
		$faq[] = array(
			'@type'          => 'Question',
			'name'           => wp_strip_all_tags( $item[0] ),
			'acceptedAnswer' => array( '@type' => 'Answer', 'text' => trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( str_replace( '<br>', ' ', $item[1] ) ) ) ) ),
		);
	}
	$json = str_replace( '"__FAQ__"', wp_json_encode( $faq, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ), $json );
	$json = preg_replace_callback( '/\{\{([a-z_]+)(?::([a-z0-9_]+))?\}\}/', function ( $m ) {
		$arg = isset( $m[2] ) ? $m[2] : '';
		switch ( $m[1] ) {
			case 'url':
				return adventure_park_json_inner( adventure_park_url( $arg ) );
			case 'asset':
				return adventure_park_json_inner( adventure_park_assets_url() );
			case 'n':
				return (string) absint( adventure_park_setting( $arg ) );
			case 'p':
				return adventure_park_json_inner( adventure_park_price( $arg ) );
			case 'tel_dash_main':
				return adventure_park_json_inner( str_replace( ' ', '-', trim( adventure_park_setting( 'phone_main' ) ) ) );
			case 'tel_dash_wa':
				return adventure_park_json_inner( str_replace( ' ', '-', trim( adventure_park_setting( 'phone_wa' ) ) ) );
			case 'wa_plain':
				return adventure_park_json_inner( adventure_park_whatsapp_link( false ) );
			case 'instagram_url':
				return adventure_park_json_inner( 'https://www.instagram.com/' . adventure_park_instagram() . '/' );
			case 'price_range':
				return adventure_park_json_inner( adventure_park_price_range() );
			case 'years':
			case 'people':
			case 'founded':
				return (string) adventure_park_number( $m[1] );
			case 'advance':
			case 'refund_hours':
				return (string) absint( adventure_park_setting( $m[1] ) );
			default:
				return adventure_park_json_inner( adventure_park_setting( $m[1] ) );
		}
	}, $json );
	$data = json_decode( $json, true );
	return $data ? wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) : '';
}

add_action( 'wp_head', function () {
	$key = adventure_park_current_key();
	if ( ! $key ) {
		return;
	}
	$json = adventure_park_schema( $key );
	if ( $json ) {
		// JSON-LD is data, not a script that runs, so the security policy allows it
		echo "<script type=\"application/ld+json\">\n" . str_replace( '</', '<\/', $json ) . "\n</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput -- JSON encoded above
	}
}, 3 );

// The sitemap lists pages and posts, not user accounts
add_filter( 'wp_sitemaps_add_provider', function ( $provider, $name ) {
	return 'users' === $name ? false : $provider;
}, 10, 2 );
