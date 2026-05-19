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

		$this->start_controls_section(
			'kdna_section_position',
			array(
				'label' => esc_html__( 'Position', 'kdna-directory-counter' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'target_element_id',
			array(
				'label'       => esc_html__( 'Target element ID', 'kdna-directory-counter' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'label_block' => true,
				'description' => esc_html__( 'Enter the CSS ID of the element you want the Counter to overlay, without the # prefix. Leave empty to render the Counter inline where it sits in the page builder. To set a CSS ID on the target widget, edit it and look under Advanced > CSS ID.', 'kdna-directory-counter' ),
			)
		);

		$this->add_control(
			'enable_absolute',
			array(
				'label'        => esc_html__( 'Overlay on target', 'kdna-directory-counter' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'On', 'kdna-directory-counter' ),
				'label_off'    => esc_html__( 'Off', 'kdna-directory-counter' ),
				'return_value' => 'yes',
				'default'      => '',
				'description'  => esc_html__( 'Inject this Counter into the target element and overlay it. The target element will automatically be set to position: relative.', 'kdna-directory-counter' ),
				'condition'    => array(
					'target_element_id!' => '',
				),
			)
		);

		$this->add_control(
			'position_preset',
			array(
				'label'   => esc_html__( 'Position preset', 'kdna-directory-counter' ),
				'type'    => \Elementor\Controls_Manager::CHOOSE,
				'default' => 'top-right',
				'options' => array(
					'top-left'     => array(
						'title' => esc_html__( 'Top left', 'kdna-directory-counter' ),
						'icon'  => 'eicon-h-align-left',
					),
					'top-right'    => array(
						'title' => esc_html__( 'Top right', 'kdna-directory-counter' ),
						'icon'  => 'eicon-h-align-right',
					),
					'bottom-left'  => array(
						'title' => esc_html__( 'Bottom left', 'kdna-directory-counter' ),
						'icon'  => 'eicon-h-align-left',
					),
					'bottom-right' => array(
						'title' => esc_html__( 'Bottom right', 'kdna-directory-counter' ),
						'icon'  => 'eicon-h-align-right',
					),
					'custom'       => array(
						'title' => esc_html__( 'Custom', 'kdna-directory-counter' ),
						'icon'  => 'eicon-edit',
					),
				),
				'toggle'    => false,
				'condition' => array(
					'target_element_id!' => '',
					'enable_absolute'    => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'vertical_offset',
			array(
				'label'      => esc_html__( 'Vertical offset', 'kdna-directory-counter' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'rem' ),
				'range'      => array(
					'px'  => array(
						'min' => 0,
						'max' => 500,
					),
					'%'   => array(
						'min' => 0,
						'max' => 100,
					),
					'rem' => array(
						'min' => 0,
						'max' => 30,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 20,
				),
				'condition'  => array(
					'target_element_id!' => '',
					'enable_absolute'    => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'horizontal_offset',
			array(
				'label'      => esc_html__( 'Horizontal offset', 'kdna-directory-counter' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'rem' ),
				'range'      => array(
					'px'  => array(
						'min' => 0,
						'max' => 500,
					),
					'%'   => array(
						'min' => 0,
						'max' => 100,
					),
					'rem' => array(
						'min' => 0,
						'max' => 30,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 20,
				),
				'condition'  => array(
					'target_element_id!' => '',
					'enable_absolute'    => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'custom_top_offset',
			array(
				'label'      => esc_html__( 'Top offset', 'kdna-directory-counter' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'rem' ),
				'range'      => array(
					'px'  => array(
						'min' => 0,
						'max' => 500,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 20,
				),
				'condition'  => array(
					'target_element_id!' => '',
					'enable_absolute'    => 'yes',
					'position_preset'    => 'custom',
				),
			)
		);

		$this->add_responsive_control(
			'custom_right_offset',
			array(
				'label'      => esc_html__( 'Right offset', 'kdna-directory-counter' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'rem' ),
				'range'      => array(
					'px'  => array(
						'min' => 0,
						'max' => 500,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 20,
				),
				'condition'  => array(
					'target_element_id!' => '',
					'enable_absolute'    => 'yes',
					'position_preset'    => 'custom',
				),
			)
		);

		$this->add_responsive_control(
			'custom_bottom_offset',
			array(
				'label'      => esc_html__( 'Bottom offset', 'kdna-directory-counter' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'rem' ),
				'range'      => array(
					'px'  => array(
						'min' => 0,
						'max' => 500,
					),
				),
				'condition'  => array(
					'target_element_id!' => '',
					'enable_absolute'    => 'yes',
					'position_preset'    => 'custom',
				),
			)
		);

		$this->add_responsive_control(
			'custom_left_offset',
			array(
				'label'      => esc_html__( 'Left offset', 'kdna-directory-counter' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'rem' ),
				'range'      => array(
					'px'  => array(
						'min' => 0,
						'max' => 500,
					),
				),
				'condition'  => array(
					'target_element_id!' => '',
					'enable_absolute'    => 'yes',
					'position_preset'    => 'custom',
				),
			)
		);

		$this->add_control(
			'z_index',
			array(
				'label'     => esc_html__( 'Z-index', 'kdna-directory-counter' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'default'   => 10,
				'min'       => 0,
				'step'      => 1,
				'condition' => array(
					'target_element_id!' => '',
					'enable_absolute'    => 'yes',
				),
			)
		);

		$this->end_controls_section();

		$this->kdna_register_animation_section();
		$this->kdna_register_icon_section();
		$this->kdna_register_style_sections();
	}

	/**
	 * Animation controls (Content tab).
	 */
	protected function kdna_register_animation_section() {
		$this->start_controls_section(
			'kdna_section_animation',
			array(
				'label' => esc_html__( 'Animation', 'kdna-directory-counter' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'enable_animation',
			array(
				'label'        => esc_html__( 'Animate count', 'kdna-directory-counter' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'On', 'kdna-directory-counter' ),
				'label_off'    => esc_html__( 'Off', 'kdna-directory-counter' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'animation_duration',
			array(
				'label'      => esc_html__( 'Duration (seconds)', 'kdna-directory-counter' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 's' ),
				'range'      => array(
					's' => array(
						'min'  => 0.5,
						'max'  => 5,
						'step' => 0.1,
					),
				),
				'default'    => array(
					'unit' => 's',
					'size' => 2,
				),
				'condition'  => array(
					'enable_animation' => 'yes',
				),
			)
		);

		$this->add_control(
			'animation_easing',
			array(
				'label'     => esc_html__( 'Easing', 'kdna-directory-counter' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'easeOutExpo',
				'options'   => array(
					'linear'        => esc_html__( 'Linear', 'kdna-directory-counter' ),
					'easeOutExpo'   => esc_html__( 'Ease out expo', 'kdna-directory-counter' ),
					'easeInOutQuad' => esc_html__( 'Ease in out quad', 'kdna-directory-counter' ),
					'easeOutCubic'  => esc_html__( 'Ease out cubic', 'kdna-directory-counter' ),
				),
				'condition' => array(
					'enable_animation' => 'yes',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Icon controls (Content tab).
	 */
	protected function kdna_register_icon_section() {
		$this->start_controls_section(
			'kdna_section_icon',
			array(
				'label' => esc_html__( 'Icon', 'kdna-directory-counter' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'icon',
			array(
				'label'       => esc_html__( 'Icon', 'kdna-directory-counter' ),
				'type'        => \Elementor\Controls_Manager::ICONS,
				'label_block' => true,
				'default'     => array(),
			)
		);

		$this->add_control(
			'icon_position',
			array(
				'label'   => esc_html__( 'Icon position', 'kdna-directory-counter' ),
				'type'    => \Elementor\Controls_Manager::CHOOSE,
				'default' => 'before',
				'options' => array(
					'before' => array(
						'title' => esc_html__( 'Before number', 'kdna-directory-counter' ),
						'icon'  => 'eicon-h-align-left',
					),
					'after'  => array(
						'title' => esc_html__( 'After number', 'kdna-directory-counter' ),
						'icon'  => 'eicon-h-align-right',
					),
					'above'  => array(
						'title' => esc_html__( 'Above number', 'kdna-directory-counter' ),
						'icon'  => 'eicon-v-align-top',
					),
				),
				'toggle'    => false,
				'condition' => array(
					'icon[value]!' => '',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style tab sections.
	 */
	protected function kdna_register_style_sections() {
		$wrapper = '{{WRAPPER}} .kdna-directory-counter';
		$number  = '{{WRAPPER}} .kdna-directory-counter__number';
		$label   = '{{WRAPPER}} .kdna-directory-counter__label';
		$icon    = '{{WRAPPER}} .kdna-directory-counter__icon';

		/* Container */
		$this->start_controls_section(
			'kdna_section_style_container',
			array(
				'label' => esc_html__( 'Container', 'kdna-directory-counter' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->start_controls_tabs( 'kdna_container_tabs' );

		$this->start_controls_tab(
			'kdna_container_tab_normal',
			array( 'label' => esc_html__( 'Normal', 'kdna-directory-counter' ) )
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			array(
				'name'     => 'container_background',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => $wrapper,
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'container_shadow',
				'selector' => $wrapper,
			)
		);

		$this->add_control(
			'container_border_colour',
			array(
				'label'     => esc_html__( 'Border colour', 'kdna-directory-counter' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					$wrapper => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'kdna_container_tab_hover',
			array( 'label' => esc_html__( 'Hover', 'kdna-directory-counter' ) )
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			array(
				'name'     => 'container_background_hover',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => $wrapper . ':hover',
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'container_shadow_hover',
				'selector' => $wrapper . ':hover',
			)
		);

		$this->add_control(
			'container_border_colour_hover',
			array(
				'label'     => esc_html__( 'Border colour', 'kdna-directory-counter' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					$wrapper . ':hover' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'container_padding',
			array(
				'label'      => esc_html__( 'Padding', 'kdna-directory-counter' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'default'    => array(
					'top'      => 16,
					'right'    => 20,
					'bottom'   => 16,
					'left'     => 20,
					'unit'     => 'px',
					'isLinked' => false,
				),
				'selectors'  => array(
					$wrapper => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'container_border_style',
			array(
				'label'     => esc_html__( 'Border type', 'kdna-directory-counter' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'none',
				'options'   => array(
					'none'   => esc_html__( 'None', 'kdna-directory-counter' ),
					'solid'  => esc_html__( 'Solid', 'kdna-directory-counter' ),
					'dashed' => esc_html__( 'Dashed', 'kdna-directory-counter' ),
					'dotted' => esc_html__( 'Dotted', 'kdna-directory-counter' ),
					'double' => esc_html__( 'Double', 'kdna-directory-counter' ),
				),
				'selectors' => array(
					$wrapper => 'border-style: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'container_border_width',
			array(
				'label'      => esc_html__( 'Border width', 'kdna-directory-counter' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px' ),
				'selectors'  => array(
					$wrapper => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'container_border_style!' => 'none',
				),
			)
		);

		$this->add_responsive_control(
			'container_border_radius',
			array(
				'label'      => esc_html__( 'Border radius', 'kdna-directory-counter' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'default'    => array(
					'top'      => 8,
					'right'    => 8,
					'bottom'   => 8,
					'left'     => 8,
					'unit'     => 'px',
					'isLinked' => true,
				),
				'selectors'  => array(
					$wrapper => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'container_transition',
			array(
				'label'      => esc_html__( 'Hover transition (seconds)', 'kdna-directory-counter' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 's' ),
				'range'      => array(
					's' => array(
						'min'  => 0,
						'max'  => 2,
						'step' => 0.05,
					),
				),
				'default'    => array(
					'unit' => 's',
					'size' => 0.3,
				),
				'selectors'  => array(
					$wrapper          => 'transition: background {{SIZE}}{{UNIT}} ease, border-color {{SIZE}}{{UNIT}} ease, box-shadow {{SIZE}}{{UNIT}} ease, color {{SIZE}}{{UNIT}} ease;',
					$number           => 'transition: color {{SIZE}}{{UNIT}} ease, text-shadow {{SIZE}}{{UNIT}} ease;',
					$label            => 'transition: color {{SIZE}}{{UNIT}} ease;',
					$icon . ' svg, ' . $icon . ' i' => 'transition: color {{SIZE}}{{UNIT}} ease, fill {{SIZE}}{{UNIT}} ease;',
				),
			)
		);

		$this->end_controls_section();

		/* Number */
		$this->start_controls_section(
			'kdna_section_style_number',
			array(
				'label' => esc_html__( 'Number', 'kdna-directory-counter' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'number_typography',
				'selector' => $number,
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'number_text_shadow',
				'selector' => $number,
			)
		);

		$this->start_controls_tabs( 'kdna_number_tabs' );

		$this->start_controls_tab(
			'kdna_number_tab_normal',
			array( 'label' => esc_html__( 'Normal', 'kdna-directory-counter' ) )
		);

		$this->add_control(
			'number_colour',
			array(
				'label'     => esc_html__( 'Colour', 'kdna-directory-counter' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					$number => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'kdna_number_tab_hover',
			array( 'label' => esc_html__( 'Hover', 'kdna-directory-counter' ) )
		);

		$this->add_control(
			'number_colour_hover',
			array(
				'label'     => esc_html__( 'Colour', 'kdna-directory-counter' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					$wrapper . ':hover ' . '.kdna-directory-counter__number' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		/* Label */
		$this->start_controls_section(
			'kdna_section_style_label',
			array(
				'label' => esc_html__( 'Label', 'kdna-directory-counter' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'label_typography',
				'selector' => $label,
			)
		);

		$this->start_controls_tabs( 'kdna_label_tabs' );

		$this->start_controls_tab(
			'kdna_label_tab_normal',
			array( 'label' => esc_html__( 'Normal', 'kdna-directory-counter' ) )
		);

		$this->add_control(
			'label_colour',
			array(
				'label'     => esc_html__( 'Colour', 'kdna-directory-counter' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					$label => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'kdna_label_tab_hover',
			array( 'label' => esc_html__( 'Hover', 'kdna-directory-counter' ) )
		);

		$this->add_control(
			'label_colour_hover',
			array(
				'label'     => esc_html__( 'Colour', 'kdna-directory-counter' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					$wrapper . ':hover ' . '.kdna-directory-counter__label' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'label_spacing',
			array(
				'label'      => esc_html__( 'Spacing from number', 'kdna-directory-counter' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 80,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 4,
				),
				'selectors'  => array(
					$label => 'margin-top: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		/* Icon */
		$this->start_controls_section(
			'kdna_section_style_icon',
			array(
				'label'     => esc_html__( 'Icon', 'kdna-directory-counter' ),
				'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => array(
					'icon[value]!' => '',
				),
			)
		);

		$this->add_responsive_control(
			'icon_size',
			array(
				'label'      => esc_html__( 'Size', 'kdna-directory-counter' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array(
						'min' => 8,
						'max' => 200,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 24,
				),
				'selectors'  => array(
					$icon . ' svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
					$icon . ' i'   => 'font-size: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->start_controls_tabs( 'kdna_icon_tabs' );

		$this->start_controls_tab(
			'kdna_icon_tab_normal',
			array( 'label' => esc_html__( 'Normal', 'kdna-directory-counter' ) )
		);

		$this->add_control(
			'icon_colour',
			array(
				'label'     => esc_html__( 'Colour', 'kdna-directory-counter' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					$icon . ' svg' => 'fill: {{VALUE}};',
					$icon . ' i'   => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'kdna_icon_tab_hover',
			array( 'label' => esc_html__( 'Hover', 'kdna-directory-counter' ) )
		);

		$this->add_control(
			'icon_colour_hover',
			array(
				'label'     => esc_html__( 'Colour', 'kdna-directory-counter' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					$wrapper . ':hover ' . '.kdna-directory-counter__icon svg' => 'fill: {{VALUE}};',
					$wrapper . ':hover ' . '.kdna-directory-counter__icon i'   => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'icon_spacing',
			array(
				'label'      => esc_html__( 'Spacing from text', 'kdna-directory-counter' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 80,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 8,
				),
				'selectors'  => array(
					$wrapper . '--icon-before ' . '.kdna-directory-counter__icon' => 'margin-right: {{SIZE}}{{UNIT}};',
					$wrapper . '--icon-after ' . '.kdna-directory-counter__icon'  => 'margin-left: {{SIZE}}{{UNIT}};',
					$wrapper . '--icon-above ' . '.kdna-directory-counter__icon'  => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		/* Alignment */
		$this->start_controls_section(
			'kdna_section_style_alignment',
			array(
				'label' => esc_html__( 'Alignment', 'kdna-directory-counter' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'text_alignment',
			array(
				'label'     => esc_html__( 'Text alignment', 'kdna-directory-counter' ),
				'type'      => \Elementor\Controls_Manager::CHOOSE,
				'default'   => 'center',
				'options'   => array(
					'left'   => array(
						'title' => esc_html__( 'Left', 'kdna-directory-counter' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center' => array(
						'title' => esc_html__( 'Centre', 'kdna-directory-counter' ),
						'icon'  => 'eicon-text-align-center',
					),
					'right'  => array(
						'title' => esc_html__( 'Right', 'kdna-directory-counter' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'toggle'    => false,
				'selectors' => array(
					$wrapper => 'text-align: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'layout',
			array(
				'label'   => esc_html__( 'Layout', 'kdna-directory-counter' ),
				'type'    => \Elementor\Controls_Manager::CHOOSE,
				'default' => 'stacked',
				'options' => array(
					'stacked' => array(
						'title' => esc_html__( 'Number above label', 'kdna-directory-counter' ),
						'icon'  => 'eicon-v-align-top',
					),
					'inline'  => array(
						'title' => esc_html__( 'Number beside label', 'kdna-directory-counter' ),
						'icon'  => 'eicon-h-align-left',
					),
				),
				'toggle'    => false,
				'selectors_dictionary' => array(
					'stacked' => 'flex-direction: column; align-items: center;',
					'inline'  => 'flex-direction: row; align-items: baseline; gap: 8px;',
				),
				'selectors' => array(
					$wrapper . '__text' => '{{VALUE}}',
				),
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
	 * Build the data-config payload consumed by the front-end JS.
	 *
	 * @param array $settings Widget settings.
	 * @return array
	 */
	protected function kdna_get_js_config( $settings, $final_count ) {
		$target = isset( $settings['target_element_id'] ) ? sanitize_html_class( $settings['target_element_id'] ) : '';
		$enable = ! empty( $settings['enable_absolute'] ) && 'yes' === $settings['enable_absolute'] && '' !== $target;

		$animation_duration = isset( $settings['animation_duration']['size'] ) ? (float) $settings['animation_duration']['size'] : 2;
		if ( $animation_duration <= 0 ) {
			$animation_duration = 2;
		}

		$config = array(
			'source'            => isset( $settings['source'] ) ? $settings['source'] : 'static',
			'jsfQueryId'        => isset( $settings['jsf_query_id'] ) ? sanitize_text_field( $settings['jsf_query_id'] ) : '',
			'singularLabel'     => isset( $settings['singular_label'] ) ? $settings['singular_label'] : '',
			'pluralLabel'       => isset( $settings['plural_label'] ) ? $settings['plural_label'] : '',
			'finalCount'        => (int) $final_count,
			'targetElementId'   => $target,
			'enableAbsolute'    => $enable,
			'positionPreset'    => isset( $settings['position_preset'] ) ? $settings['position_preset'] : 'top-right',
			'zIndex'            => isset( $settings['z_index'] ) ? (int) $settings['z_index'] : 10,
			'enableAnimation'   => ! empty( $settings['enable_animation'] ) && 'yes' === $settings['enable_animation'],
			'animationDuration' => $animation_duration,
			'animationEasing'   => isset( $settings['animation_easing'] ) ? $settings['animation_easing'] : 'easeOutExpo',
			'offsets'           => array(
				'vertical'   => $this->kdna_get_slider_css_value( $settings, 'vertical_offset' ),
				'horizontal' => $this->kdna_get_slider_css_value( $settings, 'horizontal_offset' ),
				'top'        => $this->kdna_get_slider_css_value( $settings, 'custom_top_offset' ),
				'right'      => $this->kdna_get_slider_css_value( $settings, 'custom_right_offset' ),
				'bottom'     => $this->kdna_get_slider_css_value( $settings, 'custom_bottom_offset' ),
				'left'       => $this->kdna_get_slider_css_value( $settings, 'custom_left_offset' ),
			),
		);

		return $config;
	}

	/**
	 * Convert an Elementor slider control value into a CSS value string.
	 *
	 * @param array  $settings Widget settings.
	 * @param string $key      Control key.
	 * @return string
	 */
	protected function kdna_get_slider_css_value( $settings, $key ) {
		if ( empty( $settings[ $key ] ) || ! is_array( $settings[ $key ] ) ) {
			return '';
		}

		$size = isset( $settings[ $key ]['size'] ) ? $settings[ $key ]['size'] : '';
		$unit = isset( $settings[ $key ]['unit'] ) ? $settings[ $key ]['unit'] : 'px';

		if ( '' === $size || null === $size ) {
			return '';
		}

		return $size . $unit;
	}

	/**
	 * Render the widget output on the front end.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		if ( function_exists( 'kdna_directory_counter_rendered_flag' ) ) {
			kdna_directory_counter_rendered_flag( true );
		}

		$count          = (int) $this->kdna_get_count( $settings );
		$singular_label = isset( $settings['singular_label'] ) ? $settings['singular_label'] : '';
		$plural_label   = isset( $settings['plural_label'] ) ? $settings['plural_label'] : '';
		$label          = ( 1 === $count ) ? $singular_label : $plural_label;

		$config      = $this->kdna_get_js_config( $settings, $count );
		$config_json = wp_json_encode( $config );

		$icon_position = isset( $settings['icon_position'] ) ? $settings['icon_position'] : 'before';
		$has_icon      = ! empty( $settings['icon'] ) && ! empty( $settings['icon']['value'] );

		$classes = array( 'kdna-directory-counter' );
		if ( $has_icon ) {
			$classes[] = 'kdna-directory-counter--has-icon';
			$classes[] = 'kdna-directory-counter--icon-' . sanitize_html_class( $icon_position );
		}

		$this->add_render_attribute(
			'wrapper',
			array(
				'class'            => $classes,
				'data-kdna-config' => $config_json,
				'style'            => 'display:none;',
			)
		);

		?>
		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
			<?php if ( $has_icon && 'before' === $icon_position ) : ?>
				<span class="kdna-directory-counter__icon"><?php \Elementor\Icons_Manager::render_icon( $settings['icon'], array( 'aria-hidden' => 'true' ) ); ?></span>
			<?php endif; ?>
			<?php if ( $has_icon && 'above' === $icon_position ) : ?>
				<span class="kdna-directory-counter__icon"><?php \Elementor\Icons_Manager::render_icon( $settings['icon'], array( 'aria-hidden' => 'true' ) ); ?></span>
			<?php endif; ?>
			<span class="kdna-directory-counter__text">
				<span class="kdna-directory-counter__number" data-kdna-final="<?php echo esc_attr( $count ); ?>"><?php echo esc_html( number_format_i18n( $count ) ); ?></span>
				<span class="kdna-directory-counter__label"><?php echo esc_html( $label ); ?></span>
			</span>
			<?php if ( $has_icon && 'after' === $icon_position ) : ?>
				<span class="kdna-directory-counter__icon"><?php \Elementor\Icons_Manager::render_icon( $settings['icon'], array( 'aria-hidden' => 'true' ) ); ?></span>
			<?php endif; ?>
		</div>
		<?php
	}
}
