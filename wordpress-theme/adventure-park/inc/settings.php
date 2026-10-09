<?php
/**
 * Settings under Appearance → Customize → Adventure Park.
 *
 * Every setting starts with today's value (inc/defaults.json), so the site looks exactly the
 * same until something is changed.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Values taken from the current website: settings, page text, FAQ, menu styling. */
function adventure_park_defaults() {
	static $defaults = null;
	if ( null === $defaults ) {
		$defaults = json_decode( (string) file_get_contents( __DIR__ . '/defaults.json' ), true );
	}
	return $defaults;
}

/** One setting, or its default. */
function adventure_park_setting( $key ) {
	$defaults = adventure_park_defaults()['settings'];
	return get_theme_mod( 'ap_' . $key, isset( $defaults[ $key ] ) ? $defaults[ $key ] : '' );
}

/** Indian digit grouping: 1200 → 1,200; 125000 → 1,25,000. */
function adventure_park_inr_number( $amount ) {
	$amount = (string) absint( $amount );
	if ( strlen( $amount ) <= 3 ) {
		return $amount;
	}
	$last3 = substr( $amount, -3 );
	$rest  = substr( $amount, 0, -3 );
	$rest  = preg_replace( '/\B(?=(\d{2})+(?!\d))/', ',', $rest );
	return $rest . ',' . $last3;
}

/** A price setting as shown on the site: ₹1,200. */
function adventure_park_price( $key ) {
	return '₹' . adventure_park_inr_number( adventure_park_setting( $key ) );
}

/** Phone number for tel: links and WhatsApp: +919762388871. */
function adventure_park_tel( $phone ) {
	$digits = preg_replace( '/\D+/', '', (string) $phone );
	return '+' . $digits;
}

/** The WhatsApp chat link, with the greeting already typed. */
function adventure_park_whatsapp_link( $with_message = true ) {
	$url = 'https://wa.me/' . preg_replace( '/\D+/', '', adventure_park_setting( 'phone_wa' ) );
	$message = trim( (string) adventure_park_setting( 'wa_message' ) );
	return ( $with_message && '' !== $message ) ? $url . '?text=' . rawurlencode( $message ) : $url;
}

/** Instagram handle without @ or the instagram.com address around it. */
function adventure_park_instagram() {
	$handle = trim( (string) adventure_park_setting( 'instagram' ) );
	$handle = preg_replace( '#^(https?://)?(www\.)?instagram\.com/#i', '', $handle );
	return trim( $handle, "@/ \t" );
}

/** Lowest and highest activity prices, for search engines: ₹520–₹4,000. */
function adventure_park_price_range() {
	$keys   = array( 'rafting_12', 'rafting_16', 'rafting_26', 'rafting_36', 'bungee', 'zip_student', 'zip_adult' );
	$prices = array_filter( array_map( 'absint', array_map( 'adventure_park_setting', $keys ) ) );
	if ( ! $prices ) {
		return '';
	}
	return '₹' . adventure_park_inr_number( min( $prices ) ) . '–₹' . adventure_park_inr_number( max( $prices ) );
}

/** The UPI QR code: the uploaded image, or the one that comes with the theme. */
function adventure_park_qr( $for_download = false ) {
	$id = absint( adventure_park_setting( 'upi_qr' ) );
	if ( $id && wp_attachment_is_image( $id ) ) {
		return wp_get_attachment_url( $id );
	}
	return get_theme_file_uri( $for_download ? 'assets/upi-qr.png' : 'assets/upi-qr.svg' );
}

/* ------------------------------------------------------------------ Customizer */

/** Settings shown in the Customizer: key => [section, label, type, help]. */
function adventure_park_customizer_settings() {
	return array(
		// Contact details
		'phone_main'      => array( 'contact', __( 'Phone number', 'adventure-park' ), 'text', __( 'Shown first wherever the site says "Call us".', 'adventure-park' ) ),
		'phone_wa'        => array( 'contact', __( 'WhatsApp and second phone number', 'adventure-park' ), 'text', __( 'Used for the WhatsApp button and links.', 'adventure-park' ) ),
		'wa_message'      => array( 'contact', __( 'WhatsApp greeting', 'adventure-park' ), 'text', __( 'The message already typed when a visitor opens WhatsApp from the site.', 'adventure-park' ) ),
		'email'           => array( 'contact', __( 'Email', 'adventure-park' ), 'email', __( 'Shown on the site and where both forms are sent (through FormSubmit).', 'adventure-park' ) ),
		'street'          => array( 'contact', __( 'Address: street', 'adventure-park' ), 'text', '' ),
		'town'            => array( 'contact', __( 'Address: town', 'adventure-park' ), 'text', '' ),
		'region'          => array( 'contact', __( 'Address: state', 'adventure-park' ), 'text', '' ),
		'address_short'   => array( 'contact', __( 'Short address', 'adventure-park' ), 'text', __( 'Used in the mobile menu.', 'adventure-park' ) ),
		'map_url'         => array( 'contact', __( 'Google Maps link', 'adventure-park' ), 'url', '' ),
		'instagram'       => array( 'contact', __( 'Instagram username', 'adventure-park' ), 'text', __( 'Without the @.', 'adventure-park' ) ),
		// Prices
		'rafting_12'          => array( 'prices', __( 'Rafting 12 km (Marine Drive → Shivpuri)', 'adventure-park' ), 'price', '' ),
		'rafting_16'          => array( 'prices', __( 'Rafting 16 km (Shivpuri → Nim Beach)', 'adventure-park' ), 'price', '' ),
		'rafting_26'          => array( 'prices', __( 'Rafting 26 km (Marine Drive → Nim Beach)', 'adventure-park' ), 'price', '' ),
		'rafting_36'          => array( 'prices', __( 'Rafting 36 km (Kaudiyala → Nim Beach)', 'adventure-park' ), 'price', '' ),
		'bungee'              => array( 'prices', __( 'Bungee jump', 'adventure-park' ), 'price', '' ),
		'zip_student'         => array( 'prices', __( 'Zip line, students', 'adventure-park' ), 'price', '' ),
		'zip_adult'           => array( 'prices', __( 'Zip line, adults', 'adventure-park' ), 'price', '' ),
		'camp_shared_min'     => array( 'prices', __( 'Camps & cottages, quad or triple sharing: from', 'adventure-park' ), 'price', __( 'Per person per night.', 'adventure-park' ) ),
		'camp_shared_max'     => array( 'prices', __( 'Camps & cottages, quad or triple sharing: up to', 'adventure-park' ), 'price', '' ),
		'camp_double_min'     => array( 'prices', __( 'Camps & cottages, double sharing: from', 'adventure-park' ), 'price', __( 'Per person per night.', 'adventure-park' ) ),
		'camp_double_max'     => array( 'prices', __( 'Camps & cottages, double sharing: up to', 'adventure-park' ), 'price', '' ),
		'room_nonac'          => array( 'prices', __( 'Hotel room, non-AC', 'adventure-park' ), 'price', __( 'Per room per night.', 'adventure-park' ) ),
		'room_nonac_busy_min' => array( 'prices', __( 'Non-AC room on busy days: from', 'adventure-park' ), 'price', '' ),
		'room_nonac_busy_max' => array( 'prices', __( 'Non-AC room on busy days: up to', 'adventure-park' ), 'price', '' ),
		'room_ac'             => array( 'prices', __( 'Hotel room, AC', 'adventure-park' ), 'price', __( 'Per room per night.', 'adventure-park' ) ),
		'room_ac_busy_min'    => array( 'prices', __( 'AC room on busy days: from', 'adventure-park' ), 'price', '' ),
		'room_ac_busy_max'    => array( 'prices', __( 'AC room on busy days: up to', 'adventure-park' ), 'price', '' ),
		// Payment and refunds
		'upi_id'          => array( 'payment', __( 'UPI ID', 'adventure-park' ), 'text', '' ),
		'upi_name'        => array( 'payment', __( 'Name shown in UPI apps', 'adventure-park' ), 'text', __( 'Visitors are asked to check that their UPI app shows this name before paying.', 'adventure-park' ) ),
		'upi_qr'          => array( 'payment', __( 'UPI QR code', 'adventure-park' ), 'image', __( 'If you change the UPI ID, upload the new QR code here too (a PNG or SVG from your UPI app).', 'adventure-park' ) ),
		'advance'         => array( 'payment', __( 'Advance to pay when booking (%)', 'adventure-park' ), 'percent', '' ),
		'refund_hours'    => array( 'payment', __( 'Refund window (hours after booking)', 'adventure-park' ), 'number', '' ),
		// Photos
		'photo_rafting_1' => array( 'photos', __( 'Rafting photo 1', 'adventure-park' ), 'image', __( 'Landscape photos look best. Leave empty to keep the current photo.', 'adventure-park' ) ),
		'photo_rafting_2' => array( 'photos', __( 'Rafting photo 2', 'adventure-park' ), 'image', '' ),
		'photo_rafting_3' => array( 'photos', __( 'Rafting photo 3', 'adventure-park' ), 'image', '' ),
		'photo_rafting_4' => array( 'photos', __( 'Rafting photo 4', 'adventure-park' ), 'image', '' ),
		'photo_bungee_1'  => array( 'photos', __( 'Bungee photo 1', 'adventure-park' ), 'image', '' ),
		'photo_bungee_2'  => array( 'photos', __( 'Bungee photo 2', 'adventure-park' ), 'image', '' ),
		'photo_zipline_1' => array( 'photos', __( 'Zip line photo 1', 'adventure-park' ), 'image', '' ),
		'photo_zipline_2' => array( 'photos', __( 'Zip line photo 2', 'adventure-park' ), 'image', '' ),
		'zip_representative' => array( 'photos', __( 'Label the zip line photos "Representative photo"', 'adventure-park' ), 'checkbox', __( 'Untick once the zip line photos are your own.', 'adventure-park' ) ),
		// Security
		'strict_csp'      => array( 'security', __( 'Strict security policy', 'adventure-park' ), 'checkbox', __( 'Only this website\'s own scripts may run, which blocks most attacks. If a plugin you add stops working for visitors (for example a chat widget or analytics), untick this.', 'adventure-park' ) ),
	);
}

function adventure_park_sanitize_price( $value ) {
	return absint( preg_replace( '/[^\d]/', '', (string) $value ) );
}

function adventure_park_sanitize_percent( $value ) {
	return min( 100, absint( $value ) );
}

function adventure_park_sanitize_checkbox( $value ) {
	return (bool) $value;
}

add_action( 'customize_register', function ( WP_Customize_Manager $wp_customize ) {
	$wp_customize->add_panel( 'adventure_park', array(
		'title'       => __( 'Adventure Park', 'adventure-park' ),
		'description' => __( 'Prices, contact details, payment rules and photos. A change here updates every page that shows it, and what search engines read.', 'adventure-park' ),
		'priority'    => 20,
	) );
	$sections = array(
		'contact'  => __( 'Contact details', 'adventure-park' ),
		'prices'   => __( 'Prices (₹)', 'adventure-park' ),
		'payment'  => __( 'Payment and refunds', 'adventure-park' ),
		'photos'   => __( 'Photos', 'adventure-park' ),
		'security' => __( 'Security', 'adventure-park' ),
	);
	$priority = 10;
	foreach ( $sections as $id => $title ) {
		$wp_customize->add_section( 'ap_' . $id, array( 'title' => $title, 'panel' => 'adventure_park', 'priority' => $priority += 10 ) );
	}
	$defaults = adventure_park_defaults()['settings'];
	foreach ( adventure_park_customizer_settings() as $key => $def ) {
		list( $section, $label, $type, $help ) = $def;
		$sanitize = array(
			'text'     => 'sanitize_text_field',
			'email'    => 'sanitize_email',
			'url'      => 'esc_url_raw',
			'price'    => 'adventure_park_sanitize_price',
			'number'   => 'absint',
			'percent'  => 'adventure_park_sanitize_percent',
			'image'    => 'absint',
			'checkbox' => 'adventure_park_sanitize_checkbox',
		)[ $type ];
		$wp_customize->add_setting( 'ap_' . $key, array(
			'default'           => isset( $defaults[ $key ] ) ? $defaults[ $key ] : '',
			'sanitize_callback' => $sanitize,
			'capability'        => 'edit_theme_options',
		) );
		$args = array( 'label' => $label, 'description' => $help, 'section' => 'ap_' . $section );
		if ( 'image' === $type ) {
			$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'ap_' . $key, $args + array( 'mime_type' => 'image' ) ) );
			continue;
		}
		$args['type'] = array( 'price' => 'number', 'percent' => 'number', 'number' => 'number' )[ $type ] ?? $type;
		if ( in_array( $type, array( 'price', 'number', 'percent' ), true ) ) {
			$args['input_attrs'] = array( 'min' => 0, 'step' => 1 ) + ( 'percent' === $type ? array( 'max' => 100 ) : array() );
		}
		$wp_customize->add_control( 'ap_' . $key, $args );
	}
} );
