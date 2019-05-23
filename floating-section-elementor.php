<?php

/**
 *
 * @link              https://www.fiqhidayat.com
 * @since             1.0.0
 * @package           floating-section-elementor
 *
 * @wordpress-plugin
 * Plugin Name:       Floating Section Elementor
 * Plugin URI:        https://github.com/fiqhidayat/Floating-Section-Elementor
 * Description:       Make section in elementor floating used for floating bar
 * Version:           1.0.0
 * Author:            Fiq Hidayat
 * Author URI:        https://www.fiqhidayat.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       elementor-floating-section
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

add_action('plugins_loaded', 'efs_plugins_loaded');
function efs_plugins_loaded(){

    if ( did_action( 'elementor/loaded' ) ):
        add_action( 'elementor/element/after_section_end','efs_register_elementor_controls', 10, 2 );
        add_action( 'elementor/element/parse_css', 'efs_add_post_css', 10, 2 );
    endif;
}

function efs_register_elementor_controls( Elementor\Controls_Stack $controls_stack, $section_id ){

    if ( 'section_advanced' !== $section_id ) {
        return;
    }

    $controls_stack->start_controls_section(
        'section_floating_bar',
        [
            'label' => __( 'Floating Bar', 'elementor' ),
            'tab' => 'advanced',
        ]
    );

    $controls_stack->add_control(
        'floating_bar_on',
        [
            'label' => __( 'Floating Bar On?', 'elementor' ),
            'type' => Elementor\Controls_Manager::SWITCHER,
            'label_on' => __( 'Yes', 'your-plugin' ),
            'label_off' => __( 'No', 'your-plugin' ),
            'return_value' => 'yes',
            'default' => '',
        ]
    );

    $controls_stack->add_control(
        'floating_bar_on_position',
        [
            'label' => __( 'Position', 'elementor' ),
            'type' => Elementor\Controls_Manager::SELECT,
            'default' => 'top',
            'options' => [
                'top' => 'Top',
                'bottom' => 'Bottom'
            ],
            'condition' => [
                'floating_bar_on' => 'yes',
            ],
        ]
    );

    $controls_stack->end_controls_section();

}

function efs_add_post_css( $post_css, $element ) {
    if ( $post_css instanceof Dynamic_CSS ) {
        return;
    }

    $element_settings = $element->get_settings();

    if( isset($element_settings['floating_bar_on']) && $element_settings['floating_bar_on'] == 'yes' ) {

        $position = isset($element_settings['floating_bar_on_position']) && $element_settings['floating_bar_on_position'] ? sanitize_text_field($element_settings['floating_bar_on_position']) : 'top';

        ob_start();
        ?>
        selector{
            position: fixed;
            z-index: 9999999;
            width: 100%;
            left: 0;
            <?php echo $position; ?>: 0;
        }
        <?php
        $css = ob_get_contents();
        ob_end_clean();

        $css = trim( $css );

        if ( empty( $css ) ) {
            return;
        }
        $css = str_replace( 'selector', $post_css->get_element_unique_selector( $element ), $css );

        // Add a css comment
        $css = sprintf( '/* Start custom CSS for %s, class: %s */', $element->get_name(), $element->get_unique_selector() ) . $css . '/* End custom CSS */';

        $post_css->get_stylesheet()->add_raw_css( $css );
    }

    return;
}
