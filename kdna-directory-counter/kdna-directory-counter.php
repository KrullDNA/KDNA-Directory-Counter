<?php
/**
 * Plugin Name: KDNA Directory Counter
 * Plugin URI:  https://krulldna.com/
 * Description: An Elementor widget that displays a styleable stats badge (e.g. "32 Offices") with full position and styling controls. Designed to overlay on a JetEngine Map Listing or sit anywhere else on the page.
 * Version:     1.1.0
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

define( 'KDNA_DIRECTORY_COUNTER_VERSION', '1.1.0' );
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

	wp_localize_script(
		'kdna-directory-counter',
		'kdnaDirectoryCounter',
		array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'kdna_directory_counter_get_count' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'kdna_directory_counter_register_assets' );
add_action( 'elementor/frontend/after_register_scripts', 'kdna_directory_counter_register_assets' );
add_action( 'elementor/frontend/after_register_styles', 'kdna_directory_counter_register_assets' );

/**
 * AJAX handler that returns the current filtered count for a
 * JetSmartFilters query ID. Used by the front-end JS to keep the
 * Counter in sync with filter changes.
 */
function kdna_directory_counter_ajax_get_count() {
	check_ajax_referer( 'kdna_directory_counter_get_count', 'nonce' );

	$query_id = isset( $_POST['jsf_query_id'] ) ? sanitize_text_field( wp_unslash( $_POST['jsf_query_id'] ) ) : '';

	if ( '' === $query_id ) {
		wp_send_json_error( array( 'message' => 'missing_query_id' ), 400 );
	}

	$count = 0;

	if ( function_exists( 'jet_smart_filters' ) ) {
		$query_manager = jet_smart_filters()->query;

		if ( is_object( $query_manager ) ) {
			if ( method_exists( $query_manager, 'set_props' ) ) {
				$query_manager->set_props( $query_id );
			}

			if ( method_exists( $query_manager, 'get_query' ) ) {
				$query_object = $query_manager->get_query( $query_id );

				if ( is_object( $query_object ) ) {
					if ( method_exists( $query_object, 'get_items_total_count' ) ) {
						$count = (int) $query_object->get_items_total_count();
					} elseif ( property_exists( $query_object, 'items_total_count' ) ) {
						$count = (int) $query_object->items_total_count;
					}
				}
			}
		}
	}

	wp_send_json_success( array( 'count' => $count ) );
}
add_action( 'wp_ajax_kdna_directory_counter_get_count', 'kdna_directory_counter_ajax_get_count' );
add_action( 'wp_ajax_nopriv_kdna_directory_counter_get_count', 'kdna_directory_counter_ajax_get_count' );

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
