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
 * Text Domain: tw-slides-plugin
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
require_once( 'includes/class-tw-slides-plugin-widgets.php' );

// Load plugin libraries
require_once( 'includes/lib/class-tw-slides-plugin-admin-api.php' );
require_once( 'includes/lib/class-tw-slides-plugin-post-type.php' );
require_once( 'includes/lib/class-tw-slides-plugin-taxonomy.php' );

if(!class_exists('AT_Meta_Box')){
  require_once("includes/My-Meta-Box/meta-box-class/my-meta-box-class.php");
}

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
$prefix = 'tw_';

$slides_category = get_option('wpt_tw_slide_category') ? get_option('wpt_tw_slide_category') : "off";
$slides_tag      = get_option('wpt_tw_slide_tag') ? get_option('wpt_tw_slide_tag') : "off";

$slides_enable_video = get_option('wpt_tw_slide_enable_video') ? get_option('wpt_tw_slide_enable_video') : "off";
$slides_enable_expiration = get_option('wpt_tw_slide_enable_expiration') ? get_option('wpt_tw_slide_enable_expiration') : "off";

TW_Slides_Plugin()->register_post_type(
                        'tw_slide',
                        __( 'Slides',     'tw-slides-plugin' ),
                        __( 'Slide',      'tw-slides-plugin' ),
                        __( 'Slides CPT', 'tw-slides-plugin'),
                        array(
                          'menu_icon'=>plugins_url( 'assets/img/cpt-icon-slide.png', __FILE__ ),
                          'rewrite' => array('slug' => 'slide'),
                          'exclude_from_search' => true,
                          'has_archive'     => false,
                        )
                    );

if($slides_category=='on'){
  TW_Slides_Plugin()->register_taxonomy( 'tw_slide_category', __( 'Slide Categories', 'tw-slides-plugin' ), __( 'Slide Category', 'tw' ), 'tw_slide', array('hierarchical'=>true) );
}

if($slides_tag=='on'){
 TW_Slides_Plugin()->register_taxonomy( 'tw_slide_tag', __( 'Slide Tags', 'tw-slides-plugin' ), __( 'Slide Tag', 'tw-slides-plugin' ), 'tw_slide', array('hierarchical'=>false) );
}

if (is_admin()){
  $slide_config = array(
    'id'             => 'tw_slide_cpt_metabox',
    'title'          => 'Slide Details',
    'pages'          => array('tw_slide'),
    'context'        => 'normal',
    'priority'       => 'high',
    'fields'         => array(),
    'local_images'   => true,
    'use_with_theme' => false
  );

  $slide_meta =  new AT_Meta_Box($slide_config);

  $slide_meta->addText($prefix.'slide_cta_title',array('name'=> 'CTA Title','desc'=>'CTA button text', 'group' => 'start'));
  $slide_meta->addText($prefix.'slide_cta_url',array('name'=> 'CTA URL', 'desc'=>'CTA button destination url. External links must include http://', 'group' => 'end'));

  if($slides_enable_video=='on'){
    $slide_meta->addCheckbox($prefix.'slide_video_enable',array('name'=> 'Enable Slide Video', 'group' => 'start'));
    $slide_meta->addText($prefix.'slide_video_url',array('name'=> 'Video URL', 'desc'=>'Full url of the video including http:// (supports youtube and vimeo only)'));
    $slide_meta->addImage($prefix.'slide_video_poster',array('name'=> 'Video Poster Image','desc'=>'Poster image to overlay over the video.','group' => 'end'));
  }

  if($slides_enable_expiration=='on'){
    $exp_date = Date('Y-m-d', strtotime("+5 years"));
    $slide_meta->addDate($prefix.'slide_expiry_date',array('name'=> 'Expiration Date','desc'=>'Date to stop displaying the slide.<br/> <strong>By default, set to 5 years in the future.</strong>','std'=>$exp_date, 'group' => 'start'));
    $slide_meta->addTime($prefix.'slide_expiry_time',array('name'=> 'Expiration Time','desc'=>'Time to stop displaying the slide', 'std'=>'12:00','group' => 'end'));
  }

  $slide_meta->Finish();
}