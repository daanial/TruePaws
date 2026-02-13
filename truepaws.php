<?php
/**
 * Plugin Name: TruePaws
 * Plugin URI: https://truepaws.com
 * Description: Self-hosted Kennel Management System for professional breeders. Manage animals, lineage, reproduction, and sales with automated PDF generation.
 * Version: 1.0.2
 * Author: TruePaws Team
 * Author URI: https://truepaws.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: truepaws
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('TRUEPAWS_VERSION', '1.0.0');
define('TRUEPAWS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TRUEPAWS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('TRUEPAWS_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('TRUEPAWS_TEXT_DOMAIN', 'truepaws');

// Autoloader for classes
spl_autoload_register(function ($class_name) {
    if (strpos($class_name, 'TruePaws_') === 0) {
        $class_name = str_replace('TruePaws_', '', $class_name);
        $class_name = str_replace('_', '-', $class_name);
        $class_name = strtolower($class_name);

        $file_path = TRUEPAWS_PLUGIN_DIR . 'includes/class-' . $class_name . '.php';
        if (file_exists($file_path)) {
            require_once $file_path;
        }
    }
});

/**
 * Main TruePaws Class
 */
class TruePaws {

    /**
     * Single instance of the plugin
     */
    private static $instance = null;

    /**
     * Get single instance of the plugin
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        add_action('plugins_loaded', array($this, 'load_textdomain'));
        add_action('init', array($this, 'init'));
    }

    /**
     * Plugin activation
     */
    public function activate() {
        TruePaws_Activator::activate();
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        TruePaws_Deactivator::deactivate();
    }

    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            TRUEPAWS_TEXT_DOMAIN,
            false,
            dirname(TRUEPAWS_PLUGIN_BASENAME) . '/languages/'
        );
    }

    /**
     * Initialize the plugin
     */
    public function init() {
        // Initialize core classes
        new TruePaws_Admin_Menu();
        new TruePaws_REST_API();

        // Load includes
        $this->load_includes();
    }

    /**
     * Load include files
     */
    private function load_includes() {
        // Helper functions
        require_once TRUEPAWS_PLUGIN_DIR . 'includes/helpers/pedigree-helpers.php';
        require_once TRUEPAWS_PLUGIN_DIR . 'includes/helpers/reproduction-helpers.php';

        // Core classes
        require_once TRUEPAWS_PLUGIN_DIR . 'includes/class-activator.php';
        require_once TRUEPAWS_PLUGIN_DIR . 'includes/class-deactivator.php';
        require_once TRUEPAWS_PLUGIN_DIR . 'includes/class-admin-menu.php';
        require_once TRUEPAWS_PLUGIN_DIR . 'includes/class-rest-api.php';
        require_once TRUEPAWS_PLUGIN_DIR . 'includes/class-pdf-generator.php';
        require_once TRUEPAWS_PLUGIN_DIR . 'includes/class-shortcodes.php';
    }
}

/**
 * Initialize the plugin
 */
function truepaws() {
    return TruePaws::get_instance();
}

// Start the plugin
truepaws();