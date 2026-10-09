<?php
/**
 * Text fields for the Home, About, Booking and Contact pages ("Page text" box below the editor).
 *
 * Each page created by the theme is remembered in the option `adventure_park_pages`
 * (key => page ID). A field that has never been saved shows the website's current text.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Page IDs by key: home, about, booking, contact. */
function adventure_park_pages() {
	$pages = get_option( 'adventure_park_pages', array() );
	return is_array( $pages ) ? array_map( 'absint', $pages ) : array();
}

/** Which of the theme's pages a post is (home/about/booking/contact), or ''. */
function adventure_park_page_key( $post_id ) {
	$key = array_search( absint( $post_id ), adventure_park_pages(), true );
	return false === $key ? '' : $key;
}

/** Field definitions for one page. */
function adventure_park_field_defs( $page_key ) {
	$fields = adventure_park_defaults()['fields'];
	return isset( $fields[ $page_key ] ) ? $fields[ $page_key ] : array();
}

/** The raw value of a page field (before placeholders are filled in). */
function adventure_park_field_raw( $page_key, $key ) {
	$pages = adventure_park_pages();
	$default = '';
	foreach ( adventure_park_field_defs( $page_key ) as $def ) {
		if ( $def['key'] === $key ) {
			$default = $def['default'];
		}
	}
	if ( ! empty( $pages[ $page_key ] ) && metadata_exists( 'post', $pages[ $page_key ], '_ap_' . $key ) ) {
		return (string) get_post_meta( $pages[ $page_key ], '_ap_' . $key, true );
	}
	return $default;
}

/** Numbers from the About page: years, people (lakh), founded. */
function adventure_park_number( $key ) {
	return absint( adventure_park_field_raw( 'about', $key ) );
}

/** HTML allowed in longer text fields and FAQ answers: links and emphasis. */
function adventure_park_allowed_html() {
	return array(
		'a'      => array( 'href' => true, 'target' => true, 'rel' => true ),
		'strong' => array(),
		'em'     => array(),
		'br'     => array(),
	);
}

/**
 * Fill in {placeholders} in text written by the site owner: {advance}, {refund_hours},
 * {remaining}, {years}, {people}, {founded}, {phone}, {whatsapp}, {email}, {upi_id},
 * {upi_name}, any price ({rafting_12}, {bungee} …) and page links ({url:booking}).
 */
function adventure_park_fill( $text ) {
	return preg_replace_callback( '/\{([a-z0-9_]+)(?::([a-z]+))?\}/', function ( $m ) {
		$key = $m[1];
		switch ( $key ) {
			case 'advance':
			case 'refund_hours':
				return (string) absint( adventure_park_setting( $key ) );
			case 'remaining':
				return (string) ( 100 - absint( adventure_park_setting( 'advance' ) ) );
			case 'years':
			case 'people':
			case 'founded':
				return (string) adventure_park_number( $key );
			case 'phone':
				return adventure_park_setting( 'phone_main' );
			case 'whatsapp':
				return adventure_park_setting( 'phone_wa' );
			case 'email':
			case 'upi_id':
			case 'upi_name':
				return adventure_park_setting( $key );
			case 'url':
				return isset( $m[2] ) ? adventure_park_url( $m[2] ) : $m[0];
			case 'home':
				return home_url( '/' );
			case 'whatsapp_link':
				return adventure_park_whatsapp_link( false );
			case 'map':
				return adventure_park_setting( 'map_url' );
			case 'instagram':
				return adventure_park_instagram();
			case 'instagram_url':
				return 'https://www.instagram.com/' . adventure_park_instagram() . '/';
			case 'upi_name_caps':
				return function_exists( 'mb_strtoupper' ) ? mb_strtoupper( adventure_park_setting( 'upi_name' ) ) : strtoupper( adventure_park_setting( 'upi_name' ) );
			case 'advance_example':
				return '₹' . adventure_park_inr_number( round( absint( adventure_park_setting( 'bungee' ) ) * absint( adventure_park_setting( 'advance' ) ) / 100 ) );
		}
		$defaults = adventure_park_defaults()['settings'];
		if ( isset( $defaults[ $key ] ) && is_int( $defaults[ $key ] ) && 'advance' !== $key ) {
			return adventure_park_price( $key );
		}
		return $m[0];
	}, (string) $text );
}

/** A page field ready to print: placeholders filled, text escaped (links allowed in longer text). */
function adventure_park_field( $page_key, $key ) {
	$type = 'text';
	foreach ( adventure_park_field_defs( $page_key ) as $def ) {
		if ( $def['key'] === $key ) {
			$type = $def['type'];
		}
	}
	$value = adventure_park_fill( adventure_park_field_raw( $page_key, $key ) );
	if ( 'textarea' === $type ) {
		return nl2br( wp_kses( $value, adventure_park_allowed_html() ), false );
	}
	return esc_html( $value );
}

/* ------------------------------------------------------------------ the "Page text" box */

add_action( 'add_meta_boxes_page', function ( $post ) {
	$page_key = adventure_park_page_key( $post->ID );
	if ( ! $page_key ) {
		return;
	}
	add_meta_box( 'adventure-park-text', __( 'Page text', 'adventure-park' ), 'adventure_park_render_meta_box', 'page', 'normal', 'high', array( 'key' => $page_key ) );
} );

function adventure_park_render_meta_box( $post, $box ) {
	$page_key = $box['args']['key'];
	wp_nonce_field( 'adventure_park_fields', 'adventure_park_fields_nonce' );
	$intro = array(
		'home'    => __( 'The questions and answers in the editor above are the FAQ on the Home page. Each is a "Details" block: the summary is the question, the text inside is the answer.', 'adventure-park' ),
		'about'   => __( 'The text in the editor above is the founder\'s story on the About page.', 'adventure-park' ),
		'booking' => __( 'The booking form, the activities and the UPI payment section come from Appearance → Customize → Adventure Park.', 'adventure-park' ),
		'contact' => __( 'Phone numbers, email, address, map and Instagram come from Appearance → Customize → Adventure Park.', 'adventure-park' ),
	);
	echo '<p>' . esc_html( $intro[ $page_key ] ) . '</p>';
	echo '<p class="description">' . esc_html__( 'In any text you can write {advance} for the advance percentage, {remaining} for the rest, {refund_hours}, {years}, {people}, {founded}, {phone}, {whatsapp}, {email}, {upi_id} or a price such as {rafting_12} or {bungee}: the current value is shown in its place. Longer texts can contain links (<a href="…">) and <strong>bold</strong> words.', 'adventure-park' ) . '</p>';
	$group = '';
	echo '<table class="form-table" role="presentation"><tbody>';
	foreach ( adventure_park_field_defs( $page_key ) as $def ) {
		$this_group = isset( $def['group'] ) ? $def['group'] : '';
		if ( $this_group !== $group && 'search' === $this_group ) {
			echo '<tr><th colspan="2"><h3 style="margin:1em 0 0">' . esc_html__( 'Search engines and link previews', 'adventure-park' ) . '</h3></th></tr>';
		}
		$group = $this_group;
		$id = 'ap-' . $def['key'];
		$value = adventure_park_field_raw( $page_key, $def['key'] );
		echo '<tr><th scope="row"><label for="' . esc_attr( $id ) . '">' . esc_html( $def['label'] ) . '</label></th><td>';
		if ( 'textarea' === $def['type'] ) {
			echo '<textarea class="large-text" rows="3" id="' . esc_attr( $id ) . '" name="ap_fields[' . esc_attr( $def['key'] ) . ']">' . esc_textarea( $value ) . '</textarea>';
		} else {
			$type = 'number' === $def['type'] ? 'number' : 'text';
			echo '<input class="' . ( 'number' === $type ? 'small-text' : 'large-text' ) . '" type="' . esc_attr( $type ) . '" id="' . esc_attr( $id ) . '" name="ap_fields[' . esc_attr( $def['key'] ) . ']" value="' . esc_attr( $value ) . '"' . ( 'number' === $type ? ' min="0" step="1"' : '' ) . ' />';
		}
		if ( ! empty( $def['help'] ) ) {
			echo '<p class="description">' . esc_html( $def['help'] ) . '</p>';
		}
		echo '</td></tr>';
	}
	echo '</tbody></table>';
}

// Newer WordPress versions fold the box into a "Meta Boxes" bar at the bottom of the editor: open it and say so
add_action( 'enqueue_block_editor_assets', function () {
	$post = get_post();
	if ( ! $post || 'page' !== $post->post_type || ! adventure_park_page_key( $post->ID ) ) {
		return;
	}
	wp_enqueue_script( 'adventure-park-editor', get_theme_file_uri( 'admin/editor.js' ), array( 'wp-data', 'wp-dom-ready', 'wp-edit-post', 'wp-notices' ), ADVENTURE_PARK_VERSION, true );
	wp_add_inline_script( 'adventure-park-editor', 'window.adventureParkEditor = ' . wp_json_encode( array(
		'notice' => __( 'This page\'s headings and other text are in the "Page text" box at the bottom of the screen.', 'adventure-park' ),
		'button' => __( 'Show Page text', 'adventure-park' ),
		'half'   => in_array( adventure_park_page_key( $post->ID ), array( 'home', 'about' ), true ),
	) ) . ';', 'before' );
} );

add_action( 'save_post_page', function ( $post_id ) {
	if ( ! isset( $_POST['adventure_park_fields_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['adventure_park_fields_nonce'] ) ), 'adventure_park_fields' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_page', $post_id ) ) {
		return;
	}
	$page_key = adventure_park_page_key( $post_id );
	if ( ! $page_key || ! isset( $_POST['ap_fields'] ) || ! is_array( $_POST['ap_fields'] ) ) {
		return;
	}
	$input = wp_unslash( $_POST['ap_fields'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- sanitized per field below
	foreach ( adventure_park_field_defs( $page_key ) as $def ) {
		if ( ! isset( $input[ $def['key'] ] ) ) {
			continue;
		}
		$value = $input[ $def['key'] ];
		if ( 'textarea' === $def['type'] ) {
			$value = wp_kses( (string) $value, adventure_park_allowed_html() );
		} elseif ( 'number' === $def['type'] ) {
			$value = (string) absint( $value );
		} else {
			$value = sanitize_text_field( (string) $value );
		}
		update_post_meta( $post_id, '_ap_' . $def['key'], $value );
	}
} );
