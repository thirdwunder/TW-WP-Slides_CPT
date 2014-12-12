<?php
/*
 * Plugin Name: Third Wunder Slides Plugin
 * Version: 1.0
 * Plugin URI: http://www.thirdwunder.com/
 * Description: Third Wunder slides CPT plugin
 * Author: Mohamed Hamad
 * Author URI: http://www.thirdwunder.com/
 * Requires at least: 4.0
 * Tested up to: 4.0
 *
 * Text Domain: tw
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Mohamed Hamad
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Load plugin class files
require_once( 'includes/class-tw-slides-plugin.php' );
require_once( 'includes/class-tw-slides-plugin-settings.php' );

// Load plugin libraries
require_once( 'includes/lib/class-tw-slides-plugin-admin-api.php' );
require_once( 'includes/lib/class-tw-slides-plugin-post-type.php' );
require_once( 'includes/lib/class-tw-slides-plugin-taxonomy.php' );

/**
 * Returns the main instance of TW_Slides_Plugin to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object TW_Slides_Plugin
 */
function TW_Slides_Plugin () {
	$instance = TW_Slides_Plugin::instance( __FILE__, '1.0.0' );

	if ( is_null( $instance->settings ) ) {
		$instance->settings = TW_Slides_Plugin_Settings::instance( $instance );
	}

	return $instance;
}

TW_Slides_Plugin();


TW_Slides_Plugin()->register_post_type( 'slide', __( 'Slides', 'tw' ), __( 'Slide', 'tw' ) );
TW_Slides_Plugin()->register_taxonomy( 'slide_category', __( 'Slide Categories', 'tw' ), __( 'Slide Category', 'tw' ), 'slide', array('hierarchical'=>true) );
TW_Slides_Plugin()->register_taxonomy( 'slide_tag', __( 'Slide Tags', 'tw' ), __( 'Slide Tag', 'tw' ), 'slide', array('hierarchical'=>false) );