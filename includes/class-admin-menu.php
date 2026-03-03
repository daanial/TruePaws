<?php
/**
 * Register admin menu and enqueue scripts/styles
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * TruePaws Admin Menu Class
 */
class TruePaws_Admin_Menu {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('TruePaws', 'truepaws'),
            __('TruePaws', 'truepaws'),
            'manage_options',
            'truepaws',
            array($this, 'admin_page'),
            'dashicons-pets',
            30
        );

        // Add submenu for settings
        add_submenu_page(
            'truepaws',
            __('Settings', 'truepaws'),
            __('Settings', 'truepaws'),
            'manage_options',
            'truepaws-settings',
            array($this, 'settings_page')
        );
    }

    /**
     * Main admin page (React SPA container)
     */
    public function admin_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        echo '<div class="wrap">';
        echo '<div id="truepaws-app"></div>';
        echo '</div>';
    }

    /**
     * Settings page
     */
    public function settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        echo '<div class="wrap">';
        echo '<h1>' . __('TruePaws Settings', 'truepaws') . '</h1>';
        echo '<form method="post" action="options.php">';

        settings_fields('truepaws_settings');
        do_settings_sections('truepaws-settings');

        submit_button();

        echo '</form>';
        echo '</div>';
    }

    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts($hook) {
        // Only load on our admin pages
        if ($hook !== 'toplevel_page_truepaws' && $hook !== 'truepaws_page_truepaws-settings') {
            return;
        }

        // Enqueue WordPress media scripts for image uploads
        if ($hook === 'toplevel_page_truepaws') {
            wp_enqueue_media();
        }

        // Enqueue our React app
        if ($hook === 'toplevel_page_truepaws') {
            $this->enqueue_react_app();
        }

        // Enqueue settings page styles
        if ($hook === 'truepaws_page_truepaws-settings') {
            wp_enqueue_style(
                'truepaws-admin',
                TRUEPAWS_PLUGIN_URL . 'assets/css/admin.css',
                array(),
                TRUEPAWS_VERSION
            );
        }
    }

    /**
     * Enqueue React application
     */
    private function enqueue_react_app() {
        // Check if build exists
        $build_file = TRUEPAWS_PLUGIN_DIR . 'assets/build/main.js';
        if (!file_exists($build_file)) {
            echo '<div class="notice notice-warning"><p>';
            echo __('TruePaws React application not found. Please build the frontend assets.', 'truepaws');
            echo '</p></div>';
            return;
        }

        // Enqueue React app script
        wp_enqueue_script(
            'truepaws-app',
            TRUEPAWS_PLUGIN_URL . 'assets/build/main.js',
            array('wp-api', 'wp-i18n'),
            TRUEPAWS_VERSION,
            true
        );

        wp_set_script_translations('truepaws-app', 'truepaws', TRUEPAWS_PLUGIN_DIR . 'languages');

        // Localize script with necessary data
        wp_localize_script('truepaws-app', 'truepawsConfig', array(
            'apiUrl' => esc_url_raw(rest_url('truepaws/v1/')),
            'nonce' => wp_create_nonce('wp_rest'),
            'userId' => get_current_user_id(),
            'locale' => get_locale(),
            'strings' => array(
                'loading' => __('Loading...', 'truepaws'),
                'error' => __('An error occurred', 'truepaws'),
                'save' => __('Save', 'truepaws'),
                'cancel' => __('Cancel', 'truepaws'),
            )
        ));

        // CSS is injected by webpack style-loader, no separate CSS file needed
    }
}