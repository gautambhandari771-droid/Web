<?php
/**
 * First activation: create the Home, About, Booking and Contact pages with today's text,
 * make Home the front page, build the main menu and switch on readable page addresses.
 * Nothing is created twice: activating the theme again only fills in what is missing.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** The FAQ as "Details" blocks for the Home page (questions and answers from the website). */
function adventure_park_faq_blocks() {
	$blocks = array();
	foreach ( adventure_park_defaults()['faq'] as $item ) {
		$question = esc_html( $item[0] );
		// Page links become real addresses (WordPress removes links it can't read)
		$answer = preg_replace_callback( '/\{url:([a-z]+)\}/', function ( $m ) {
			return adventure_park_url( $m[1] );
		}, $item[1] );
		$answer = wp_kses( $answer, adventure_park_allowed_html() );
		$blocks[] = "<!-- wp:details -->\n<details class=\"wp-block-details\"><summary>{$question}</summary><!-- wp:paragraph -->\n<p>{$answer}</p>\n<!-- /wp:paragraph --></details>\n<!-- /wp:details -->";
	}
	return implode( "\n\n", $blocks );
}

/** The founder's story as paragraph blocks for the About page. */
function adventure_park_story_blocks() {
	$blocks = array();
	foreach ( adventure_park_defaults()['about_story'] as $paragraph ) {
		$blocks[] = "<!-- wp:paragraph -->\n<p>" . wp_kses( $paragraph, adventure_park_allowed_html() ) . "</p>\n<!-- /wp:paragraph -->";
	}
	return implode( "\n\n", $blocks );
}

function adventure_park_setup_site() {
	$pages = adventure_park_pages();
	$plan = array(
		'home'    => array( __( 'Home', 'adventure-park' ), 'home', '', adventure_park_faq_blocks() ),
		'about'   => array( __( 'About', 'adventure-park' ), 'about', 'page-templates/about.php', adventure_park_story_blocks() ),
		'booking' => array( __( 'Booking', 'adventure-park' ), 'booking', 'page-templates/booking.php', '' ),
		'contact' => array( __( 'Contact', 'adventure-park' ), 'contact', 'page-templates/contact.php', '' ),
	);
	foreach ( $plan as $key => $page ) {
		list( $title, $slug, $template, $content ) = $page;
		if ( ! empty( $pages[ $key ] ) && get_post( $pages[ $key ] ) && 'trash' !== get_post_status( $pages[ $key ] ) ) {
			continue;
		}
		$id = wp_insert_post( array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'post_title'     => $title,
			'post_name'      => $slug,
			'post_content'   => $content,
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
			'page_template'  => $template,
		), true );
		if ( is_wp_error( $id ) ) {
			continue;
		}
		$pages[ $key ] = $id;
		// Fill the "Page text" box with today's text so it can be edited straight away
		foreach ( adventure_park_field_defs( $key ) as $def ) {
			update_post_meta( $id, '_ap_' . $def['key'], $def['default'] );
		}
	}
	update_option( 'adventure_park_pages', $pages );

	if ( ! empty( $pages['home'] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $pages['home'] );
	}

	// Main menu, only if none is set yet
	$locations = get_nav_menu_locations();
	if ( empty( $locations['primary'] ) ) {
		$menu_id = wp_create_nav_menu( __( 'Main menu', 'adventure-park' ) );
		if ( is_wp_error( $menu_id ) ) {
			$menu = wp_get_nav_menu_object( __( 'Main menu', 'adventure-park' ) );
			$menu_id = $menu ? $menu->term_id : 0;
		} else {
			$items = array(
				array( __( 'Home', 'adventure-park' ), 'home', '' ),
				array( __( 'Activities', 'adventure-park' ), 'home', '#activities' ),
				array( __( 'Stay', 'adventure-park' ), 'home', '#stay' ),
				array( __( 'About', 'adventure-park' ), 'about', '' ),
				array( __( 'Booking', 'adventure-park' ), 'booking', '' ),
				array( __( 'Contact', 'adventure-park' ), 'contact', '' ),
			);
			$position = 0;
			foreach ( $items as $item ) {
				list( $label, $key, $hash ) = $item;
				$args = array( 'menu-item-title' => $label, 'menu-item-status' => 'publish', 'menu-item-position' => ++$position );
				if ( '' === $hash && ! empty( $pages[ $key ] ) ) {
					$args += array( 'menu-item-type' => 'post_type', 'menu-item-object' => 'page', 'menu-item-object-id' => $pages[ $key ] );
				} else {
					$args += array( 'menu-item-type' => 'custom', 'menu-item-url' => home_url( '/' ) . $hash );
				}
				wp_update_nav_menu_item( $menu_id, 0, $args );
			}
		}
		if ( $menu_id ) {
			$locations['primary'] = $menu_id;
			set_theme_mod( 'nav_menu_locations', $locations );
		}
	}

	// Readable page addresses (/booking/ rather than /?page_id=12): the booking form and the offline copy need them
	if ( '' === (string) get_option( 'permalink_structure' ) ) {
		update_option( 'permalink_structure', '/%postname%/' );
	}
	flush_rewrite_rules();
	set_transient( 'adventure_park_welcome', 1, HOUR_IN_SECONDS );
}

add_action( 'after_switch_theme', 'adventure_park_setup_site' );

add_action( 'admin_notices', function () {
	if ( ! get_transient( 'adventure_park_welcome' ) || ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	delete_transient( 'adventure_park_welcome' );
	$customize = admin_url( 'customize.php?autofocus[panel]=adventure_park' );
	echo '<div class="notice notice-success is-dismissible"><p><strong>' . esc_html__( 'Adventure Park is set up.', 'adventure-park' ) . '</strong> '
		. esc_html__( 'Home, About, Booking and Contact pages and the main menu were created with your current text. Prices, contact details, payment rules and photos are under', 'adventure-park' )
		. ' <a href="' . esc_url( $customize ) . '">' . esc_html__( 'Appearance → Customize → Adventure Park', 'adventure-park' ) . '</a>.</p></div>';
} );
