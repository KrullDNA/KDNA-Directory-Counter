<?php
/**
 * KDNA Directory Counter Widget.
 *
 * @package KDNA_Directory_Counter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KDNA_Directory_Counter_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'kdna-directory-counter';
	}

	public function get_title() {
		return esc_html__( 'KDNA Directory Counter', 'kdna-directory-counter' );
	}

	public function get_icon() {
		return 'eicon-counter';
	}

	public function get_categories() {
		return array( 'general' );
	}

	public function get_keywords() {
		return array( 'kdna', 'counter', 'directory', 'badge', 'stats', 'jetengine' );
	}

	public function get_style_depends() {
		return array( 'kdna-directory-counter' );
	}

	public function get_script_depends() {
		return array( 'kdna-directory-counter' );
	}

	/**
	 * Disable Elementor's inner wrapper when atomic markup is active.
	 * Keeps the rendered markup to a single wrapper div.
	 */
	public function has_widget_inner_wrapper() {
		return ! \Elementor\Plugin::instance()->experiments->is_feature_active( 'e_optimized_markup' );
	}

	/**
	 * Register widget controls.
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'kdna_section_counter_source',
			array(
				'label' => esc_html__( 'Counter Source', 'kdna-directory-counter' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'source',
			array(
				'label'   => esc_html__( 'Source', 'kdna-directory-counter' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'static',
				'options' => array(
					'static'    => esc_html__( 'Static number', 'kdna-directory-counter' ),
					'cpt_total' => esc_html__( 'CPT total', 'kdna-directory-counter' ),
					'jsf_query' => esc_html__( 'JetSmartFilters query', 'kdna-directory-counter' ),
				),
			)
		);

		$this->add_control(
			'static_number',
			array(
				'label'     => esc_html__( 'Static number', 'kdna-directory-counter' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'default'   => 0,
				'min'       => 0,
				'step'      => 1,
				'condition' => array(
					'source' => 'static',
				),
			)
		);

		$this->add_control(
			'cpt_slug',
			array(
				'label'       => esc_html__( 'Custom post type', 'kdna-directory-counter' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'default'     => '',
				'options'     => $this->kdna_get_public_post_type_options(),
				'description' => esc_html__( 'Select the post type whose published posts you want to count. Cached for 15 minutes.', 'kdna-directory-counter' ),
				'condition'   => array(
					'source' => 'cpt_total',
				),
			)
		);

		$this->add_control(
			'jsf_query_id',
			array(
				'label'       => esc_html__( 'JetSmartFilters query ID', 'kdna-directory-counter' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'description' => esc_html__( 'Enter the Query ID set on the JetEngine Listing Grid or Map Listing you want this counter to track. The same ID must be set on each JetSmartFilters filter widget that targets the directory. Live updates are added in Stage 2.', 'kdna-directory-counter' ),
				'condition'   => array(
					'source' => 'jsf_query',
				),
			)
		);

		$this->add_control(
			'singular_label',
			array(
				'label'       => esc_html__( 'Singular label', 'kdna-directory-counter' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => esc_html__( 'Office', 'kdna-directory-counter' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'plural_label',
			array(
				'label'       => esc_html__( 'Plural label', 'kdna-directory-counter' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => esc_html__( 'Offices', 'kdna-directory-counter' ),
				'label_block' => true,
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Build an options array of all public custom post types for the CPT slug control.
	 *
	 * @return array
	 */
	protected function kdna_get_public_post_type_options() {
		$options = array(
			'' => esc_html__( 'Select a post type', 'kdna-directory-counter' ),
		);

		$post_types = get_post_types(
			array(
				'public' => true,
			),
			'objects'
		);

		foreach ( $post_types as $post_type ) {
			if ( 'attachment' === $post_type->name ) {
				continue;
			}
			$options[ $post_type->name ] = $post_type->label;
		}

		return $options;
	}

	/**
	 * Resolve the count value from the chosen source.
	 *
	 * @param array $settings Widget settings.
	 * @return int
	 */
	protected function kdna_get_count( $settings ) {
		$source = isset( $settings['source'] ) ? $settings['source'] : 'static';

		switch ( $source ) {
			case 'cpt_total':
				return $this->kdna_get_cpt_total( $settings );

			case 'jsf_query':
				return $this->kdna_get_jsf_initial_count( $settings );

			case 'static':
			default:
				return isset( $settings['static_number'] ) ? absint( $settings['static_number'] ) : 0;
		}
	}

	/**
	 * Count published posts for the chosen CPT, cached for 15 minutes via transient.
	 *
	 * @param array $settings Widget settings.
	 * @return int
	 */
	protected function kdna_get_cpt_total( $settings ) {
		$cpt_slug = isset( $settings['cpt_slug'] ) ? sanitize_key( $settings['cpt_slug'] ) : '';

		if ( '' === $cpt_slug || ! post_type_exists( $cpt_slug ) ) {
			return 0;
		}

		$transient_key = 'kdna_directory_counter_cpt_' . $cpt_slug;
		$cached        = get_transient( $transient_key );

		if ( false !== $cached ) {
			return (int) $cached;
		}

		$query = new \WP_Query(
			array(
				'post_type'              => $cpt_slug,
				'post_status'            => 'publish',
				'posts_per_page'         => -1,
				'fields'                 => 'ids',
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);

		$count = is_array( $query->posts ) ? count( $query->posts ) : 0;

		set_transient( $transient_key, $count, 15 * MINUTE_IN_SECONDS );

		return $count;
	}

	/**
	 * Return the server-side initial count for a JetSmartFilters query ID.
	 * Live updates are added in Stage 2.
	 *
	 * @param array $settings Widget settings.
	 * @return int
	 */
	protected function kdna_get_jsf_initial_count( $settings ) {
		$query_id = isset( $settings['jsf_query_id'] ) ? sanitize_text_field( $settings['jsf_query_id'] ) : '';

		if ( '' === $query_id ) {
			return 0;
		}

		if ( function_exists( 'jet_smart_filters' ) ) {
			$query_manager = jet_smart_filters()->query;

			if ( is_object( $query_manager ) && method_exists( $query_manager, 'get_query' ) ) {
				$query_object = $query_manager->get_query( $query_id );

				if ( is_object( $query_object ) ) {
					if ( method_exists( $query_object, 'get_items_total_count' ) ) {
						return (int) $query_object->get_items_total_count();
					}
					if ( property_exists( $query_object, 'items_total_count' ) ) {
						return (int) $query_object->items_total_count;
					}
				}
			}
		}

		return 0;
	}

	/**
	 * Render the widget output on the front end.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		$count          = (int) $this->kdna_get_count( $settings );
		$singular_label = isset( $settings['singular_label'] ) ? $settings['singular_label'] : '';
		$plural_label   = isset( $settings['plural_label'] ) ? $settings['plural_label'] : '';
		$label          = ( 1 === $count ) ? $singular_label : $plural_label;

		$this->add_render_attribute(
			'wrapper',
			array(
				'class' => 'kdna-directory-counter',
			)
		);

		?>
		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
			<span class="kdna-directory-counter__number"><?php echo esc_html( number_format_i18n( $count ) ); ?></span>
			<span class="kdna-directory-counter__label"><?php echo esc_html( $label ); ?></span>
		</div>
		<?php
	}
}
