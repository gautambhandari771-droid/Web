<?php
/**
 * Fills the page templates (parts/*.html) with the settings and page text.
 *
 * The templates are the website's own HTML with {{tokens}} where a setting or a text field goes;
 * every value is escaped here before it is printed.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Link to one of the theme's pages: home, about, booking, contact. */
function adventure_park_url( $key ) {
	if ( 'home' === $key ) {
		return home_url( '/' );
	}
	$pages = adventure_park_pages();
	if ( ! empty( $pages[ $key ] ) && 'publish' === get_post_status( $pages[ $key ] ) ) {
		return get_permalink( $pages[ $key ] );
	}
	return home_url( '/' . $key . '/' );
}

/** Base address of the theme's assets folder (no trailing slash). */
function adventure_park_assets_url() {
	return untrailingslashit( get_theme_file_uri( 'assets' ) );
}

/** Attributes for a gallery photo: the chosen image from the Media Library, or the photo that comes with the theme. */
function adventure_park_photo_attrs( $key, $deferred ) {
	$group = strtok( $key, '_' );
	$sizes = adventure_park_defaults()['gallery_sizes'];
	$sizes = ! empty( $sizes[ $group ] ) ? $sizes[ $group ] : $sizes['bungee'];
	$id = absint( adventure_park_setting( 'photo_' . $key ) );
	$src = '';
	$srcset = '';
	if ( $id && wp_attachment_is_image( $id ) ) {
		$src = wp_get_attachment_image_url( $id, 'large' );
		$srcset = (string) wp_get_attachment_image_srcset( $id, 'full' );
	} else {
		$base = adventure_park_assets_url() . '/photos/';
		if ( 'zipline' === $group ) {
			$src = $base . ( 'zipline_1' === $key ? 'zipline-1-640.webp' : 'zipline-2-629.webp' );
		} else {
			$name = str_replace( '_', '-', $key );
			$src = $base . $name . '-960.webp';
			$srcset = $base . $name . '-640.webp 640w, ' . $base . $name . '-960.webp 960w, ' . $base . $name . '-1440.webp 1440w';
		}
	}
	$prefix = $deferred ? 'data-' : '';
	$out = $prefix . 'src="' . esc_url( $src ) . '"';
	if ( $srcset ) {
		$out .= ' ' . $prefix . 'srcset="' . esc_attr( $srcset ) . '" ' . $prefix . 'sizes="' . esc_attr( $sizes ) . '"';
	}
	return $out;
}

/** Description of a gallery photo: the Media Library's alt text when a photo was chosen. */
function adventure_park_photo_alt( $key ) {
	$id = absint( adventure_park_setting( 'photo_' . $key ) );
	if ( $id && wp_attachment_is_image( $id ) ) {
		$alt = trim( (string) get_post_meta( $id, '_wp_attachment_image_alt', true ) );
		return '' !== $alt ? $alt : get_the_title( $id );
	}
	$alts = adventure_park_defaults()['photo_alts'];
	return isset( $alts[ $key ] ) ? $alts[ $key ] : '';
}

/** The main menu: WordPress menu "Main menu" if set, otherwise the website's usual links. */
function adventure_park_menu_items() {
	$items = array();
	$locations = get_nav_menu_locations();
	if ( ! empty( $locations['primary'] ) ) {
		foreach ( (array) wp_get_nav_menu_items( $locations['primary'] ) as $item ) {
			if ( empty( $item->menu_item_parent ) ) {
				$items[] = array( $item->title, $item->url, 'post_type' === $item->type ? absint( $item->object_id ) : 0 );
			}
		}
	}
	if ( ! $items ) {
		foreach ( adventure_park_defaults()['nav']['items'] as $item ) {
			list( $label, $key, $hash ) = $item;
			$items[] = array( $label, adventure_park_url( $key ) . $hash, 'home' === $key && $hash ? -1 : 0 );
		}
	}
	return $items;
}

/** Is this menu link the page being shown? (Links to a part of a page, like #activities, never are.) */
function adventure_park_is_current( $url, $object_id ) {
	if ( false !== strpos( $url, '#' ) || -1 === $object_id ) {
		return false;
	}
	if ( $object_id ) {
		return is_singular() && get_queried_object_id() === $object_id;
	}
	$here = trailingslashit( strtok( home_url( add_query_arg( array() ) ), '?' ) );
	return trailingslashit( strtok( $url, '?' ) ) === $here;
}

function adventure_park_nav( $mobile ) {
	$nav = adventure_park_defaults()['nav'];
	$out = array();
	foreach ( adventure_park_menu_items() as $i => $item ) {
		list( $label, $url, $object_id ) = $item;
		$current = adventure_park_is_current( $url, $object_id );
		if ( $mobile ) {
			$class = str_replace( '[--i:__I__]', $i <= 5 ? '[--i:' . $i . ']' : '', $current ? $nav['mobile_class'] : $nav['mobile_class_off'] );
			$style = $i > 5 ? ' style="--i:' . $i . '"' : '';
			$out[] = '<li><a href="' . esc_url( $url ) . '"' . ( $current ? ' aria-current="page"' : '' ) . ' class="' . esc_attr( $class ) . '"' . $style . '>' . esc_html( $label ) . ' ' . $nav['mobile_icon'] . '</a></li>';
		} else {
			$class = $current ? $nav['desktop_class'] : $nav['desktop_class_off'];
			$out[] = '<a href="' . esc_url( $url ) . '"' . ( $current ? ' aria-current="page"' : '' ) . ' class="' . esc_attr( $class ) . '">' . esc_html( $label ) . '</a>';
		}
	}
	return implode( $mobile ? "\n          " : "\n        ", $out );
}

/** The FAQ: the "Details" blocks of the Home page, or the website's questions if there are none. */
function adventure_park_faq() {
	$faq = array();
	$pages = adventure_park_pages();
	$home = ! empty( $pages['home'] ) ? get_post( $pages['home'] ) : null;
	if ( $home && has_blocks( $home->post_content ) ) {
		foreach ( parse_blocks( $home->post_content ) as $block ) {
			if ( 'core/details' !== $block['blockName'] ) {
				continue;
			}
			$question = preg_match( '#<summary[^>]*>(.*?)</summary>#s', $block['innerHTML'], $m ) ? wp_strip_all_tags( $m[1] ) : '';
			$answer = '';
			foreach ( $block['innerBlocks'] as $inner ) {
				$answer .= ( $answer ? '<br><br>' : '' ) . trim( preg_replace( '#^\s*<p[^>]*>|</p>\s*$#', '', render_block( $inner ) ) );
			}
			if ( '' !== trim( $question ) ) {
				$faq[] = array( trim( $question ), $answer );
			}
		}
	}
	if ( ! $faq ) {
		foreach ( adventure_park_defaults()['faq'] as $item ) {
			$faq[] = array( $item[0], $item[1] );
		}
	}
	return array_map( function ( $item ) {
		return array( adventure_park_fill( $item[0] ), wp_kses( adventure_park_fill( $item[1] ), adventure_park_allowed_html() ) );
	}, $faq );
}

function adventure_park_faq_html() {
	$markup = adventure_park_defaults()['faq_markup'];
	$out = array();
	foreach ( adventure_park_faq() as $i => $item ) {
		$delay = $i ? ' [--d:' . ( 50 * $i ) . 'ms]' : '';
		$answer_class = $markup['answer_class'] . ' [&_a]:font-semibold [&_a]:text-primary-ink [&_a]:underline-offset-4 hover:[&_a]:underline';
		$out[] = '<details name="faq" class="' . esc_attr( $markup['details_class'] . $delay ) . '">'
			. "\n            " . '<summary class="' . esc_attr( $markup['summary_class'] ) . '">'
			. "\n              " . esc_html( $item[0] )
			. "\n              " . $markup['icon']
			. "\n            </summary>"
			. "\n            " . '<p class="' . esc_attr( $answer_class ) . '">' . $item[1] . '</p>'
			. "\n          </details>";
	}
	return implode( "\n          ", $out );
}

/** The founder's story on the About page: the About page's own text, or the website's. */
function adventure_park_about_story() {
	$pages = adventure_park_pages();
	$post = ! empty( $pages['about'] ) ? get_post( $pages['about'] ) : null;
	if ( $post && '' !== trim( $post->post_content ) ) {
		$html = apply_filters( 'the_content', $post->post_content );
	} else {
		$html = '<p>' . implode( "</p>\n<p>", adventure_park_defaults()['about_story'] ) . '</p>';
	}
	return adventure_park_fill( $html );
}

/** Opening hours on the Contact page, shown only when filled in. */
function adventure_park_opening_hours_card() {
	$hours = trim( adventure_park_field_raw( 'contact', 'opening_hours' ) );
	if ( '' === $hours ) {
		return '';
	}
	$card = adventure_park_defaults()['contact_card'];
	$clock = '<svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-5"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>';
	return "\n          " . '<div class="' . esc_attr( $card['card'] ) . '">'
		. "\n            " . '<span class="' . esc_attr( $card['icon_wrap'] ) . '">' . $clock . '</span>'
		. "\n            <div>\n              <h2 class=\"font-semibold\">" . esc_html__( 'Opening hours', 'adventure-park' ) . '</h2>'
		. "\n              " . '<p class="mt-1 text-muted-foreground">' . adventure_park_field( 'contact', 'opening_hours' ) . '</p>'
		. "\n            </div>\n          </div>";
}

/** Value of one {{token}} in a template, ready to print. */
function adventure_park_token( $token, $page_key ) {
	list( $kind, $arg ) = array_pad( explode( ':', $token, 2 ), 2, '' );
	switch ( $kind ) {
		case 'asset':
			return esc_url( adventure_park_assets_url() );
		case 'url':
			return esc_url( adventure_park_url( $arg ) );
		case 'p':
			return esc_html( adventure_park_price( $arg ) );
		case 'f':
			return adventure_park_field( $page_key, $arg );
		case 'photo':
			return adventure_park_photo_attrs( $arg, false );
		case 'photo_deferred':
			return adventure_park_photo_attrs( $arg, true );
		case 'photo_alt':
			return esc_attr( adventure_park_photo_alt( $arg ) );
		case 'nav_desktop':
			return adventure_park_nav( false );
		case 'nav_mobile':
			return adventure_park_nav( true );
		case 'faq_items':
			return adventure_park_faq_html();
		case 'about_story':
			return adventure_park_about_story();
		case 'opening_hours_card':
			return adventure_park_opening_hours_card();
		case 'rep_chip':
			return adventure_park_setting( 'zip_representative' ) ? adventure_park_defaults()['rep_chip'] : '';
		case 'wa_link':
			return esc_url( adventure_park_whatsapp_link() );
		case 'formsubmit':
			return esc_url( 'https://formsubmit.co/' . adventure_park_setting( 'email' ) );
		case 'tel_main':
			return esc_attr( adventure_park_tel( adventure_park_setting( 'phone_main' ) ) );
		case 'tel_wa':
			return esc_attr( adventure_park_tel( adventure_park_setting( 'phone_wa' ) ) );
		case 'map_url':
			return esc_url( adventure_park_setting( 'map_url' ) );
		case 'instagram':
			return esc_html( adventure_park_instagram() );
		case 'instagram_url':
			return esc_url( 'https://www.instagram.com/' . adventure_park_instagram() . '/' );
		case 'address_full':
			return esc_html( implode( ', ', array_filter( array( adventure_park_setting( 'street' ), adventure_park_setting( 'town' ), adventure_park_setting( 'region' ) ) ) ) );
		case 'upi_name_caps':
			return esc_html( function_exists( 'mb_strtoupper' ) ? mb_strtoupper( adventure_park_setting( 'upi_name' ) ) : strtoupper( adventure_park_setting( 'upi_name' ) ) );
		case 'qr_image':
			return esc_url( adventure_park_qr() );
		case 'qr_download':
			return esc_url( adventure_park_qr( true ) );
		case 'advance':
		case 'refund_hours':
			return esc_html( (string) absint( adventure_park_setting( $kind ) ) );
		case 'remaining':
			return esc_html( (string) ( 100 - absint( adventure_park_setting( 'advance' ) ) ) );
		case 'advance_example':
			return esc_html( '₹' . adventure_park_inr_number( round( absint( adventure_park_setting( 'bungee' ) ) * absint( adventure_park_setting( 'advance' ) ) / 100 ) ) );
		case 'years':
		case 'people':
		case 'founded':
			return esc_html( (string) adventure_park_number( $kind ) );
		case 'year':
			return esc_html( wp_date( 'Y' ) );
		case 'phone_main':
		case 'phone_wa':
		case 'email':
		case 'street':
		case 'town':
		case 'region':
		case 'address_short':
		case 'upi_id':
		case 'upi_name':
			return esc_html( adventure_park_setting( $kind ) );
	}
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		trigger_error( esc_html( 'Adventure Park: unknown template token ' . $token ), E_USER_WARNING ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
	}
	return '';
}

/** A filled-in template from parts/. */
function adventure_park_render( $part, $page_key = '' ) {
	$html = (string) file_get_contents( get_theme_file_path( 'parts/' . $part . '.html' ) );
	return preg_replace_callback( '/\{\{([a-z_]+(?::[a-z0-9_]+)?)\}\}/', function ( $m ) use ( $page_key ) {
		return adventure_park_token( $m[1], $page_key );
	}, $html );
}
