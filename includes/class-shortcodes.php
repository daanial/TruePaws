<?php
/**
 * Public shortcodes for displaying animals and litters
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * TruePaws Shortcodes Class
 */
class TruePaws_Shortcodes {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'register_shortcodes'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_public_styles'));
    }

    /**
     * Register shortcodes
     */
    public function register_shortcodes() {
        add_shortcode('truepaws_litter', array($this, 'render_litter_shortcode'));
        add_shortcode('truepaws_animal', array($this, 'render_animal_shortcode'));
    }

    /**
     * Render litter shortcode
     *
     * @param array $atts
     * @return string
     */
    public function render_litter_shortcode($atts) {
        // TODO: Implement in Phase 9
        $atts = shortcode_atts(array(
            'id' => 0,
        ), $atts);

        if (!$atts['id']) {
            return '<p>' . __('Invalid litter ID.', 'truepaws') . '</p>';
        }

        return '<div class="truepaws-litter" data-litter-id="' . esc_attr($atts['id']) . '">';
        // Implementation will go here
        return '</div>';
    }

    /**
     * Render animal shortcode
     *
     * @param array $atts
     * @return string
     */
    public function render_animal_shortcode($atts) {
        // TODO: Implement in Phase 9
        $atts = shortcode_atts(array(
            'id' => 0,
        ), $atts);

        if (!$atts['id']) {
            return '<p>' . __('Invalid animal ID.', 'truepaws') . '</p>';
        }

        return '<div class="truepaws-animal" data-animal-id="' . esc_attr($atts['id']) . '">';
        // Implementation will go here
        return '</div>';
    }

    /**
     * Enqueue public styles
     */
    public function enqueue_public_styles() {
        // Only enqueue on pages/posts that contain our shortcodes
        global $post;
        if (!is_a($post, 'WP_Post') ||
            (!has_shortcode($post->post_content, 'truepaws_litter') &&
             !has_shortcode($post->post_content, 'truepaws_animal'))) {
            return;
        }

        wp_enqueue_style(
            'truepaws-public',
            TRUEPAWS_PLUGIN_URL . 'assets/css/public.css',
            array(),
            TRUEPAWS_VERSION
        );
    }
}