<?php
/**
 * Plugin Name: KDNA Directory Counter
 * Plugin URI:  https://krulldna.com/
 * Description: An Elementor widget that displays a styleable stats badge (e.g. "32 Offices") with full position and styling controls. Designed to overlay on a JetEngine Map Listing or sit anywhere else on the page.
 * Version:     1.0.0
 * Author:      Krull Design & Advertising
 * Author URI:  https://krulldna.com/
 * Text Domain: kdna-directory-counter
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Elementor tested up to: 3.21.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'KDNA_DIRECTORY_COUNTER_VERSION', '1.0.0' );
define( 'KDNA_DIRECTORY_COUNTER_FILE', __FILE__ );
define( 'KDNA_DIRECTORY_COUNTER_PATH', plugin_dir_path( __FILE__ ) );
define( 'KDNA_DIRECTORY_COUNTER_URL', plugin_dir_url( __FILE__ ) );

/**
 * Register the widget with Elementor.
 * Hooked at file load time per KDNA conventions.
 */
function kdna_directory_counter_register_widget( $widgets_manager ) {
	require_once KDNA_DIRECTORY_COUNTER_PATH . 'includes/class-kdna-directory-counter-widget.php';
	$widgets_manager->register( new \KDNA_Directory_Counter_Widget() );
}
add_action( 'elementor/widgets/register', 'kdna_directory_counter_register_widget' );

/**
 * Register front-end assets. Assets are registered here, then enqueued
 * conditionally from the widget render method so they only load on pages
 * that actually use the widget.
 */
function kdna_directory_counter_register_assets() {
	wp_register_style(
		'kdna-directory-counter',
		KDNA_DIRECTORY_COUNTER_URL . 'assets/css/kdna-directory-counter.css',
		array(),
		KDNA_DIRECTORY_COUNTER_VERSION
	);

	wp_register_script(
		'kdna-directory-counter',
		KDNA_DIRECTORY_COUNTER_URL . 'assets/js/kdna-directory-counter.js',
		array(),
		KDNA_DIRECTORY_COUNTER_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'kdna_directory_counter_register_assets' );
add_action( 'elementor/frontend/after_register_scripts', 'kdna_directory_counter_register_assets' );
add_action( 'elementor/frontend/after_register_styles', 'kdna_directory_counter_register_assets' );

/**
 * Admin notice when Elementor is not active.
 */
function kdna_directory_counter_elementor_missing_notice() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	if ( did_action( 'elementor/loaded' ) ) {
		return;
	}

	echo '<div class="notice notice-warning is-dismissible"><p>';
	echo esc_html__( 'KDNA Directory Counter requires Elementor to be installed and active.', 'kdna-directory-counter' );
	echo '</p></div>';
}
add_action( 'admin_notices', 'kdna_directory_counter_elementor_missing_notice' );
