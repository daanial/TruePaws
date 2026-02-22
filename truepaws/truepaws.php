<?php
/**
 * Plugin Name: TruePaws
 * Plugin URI: https://truepaws.com
 * Description: Self-hosted Kennel Management System for professional breeders. Manage animals, lineage, reproduction, and sales with automated PDF generation.
 * Version: 1.1.0
 * Author: TruePaws Team
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

// Freemius SDK - Development mode (remove for production)
define('WP_FS__DEV_MODE', true);
define('WP_FS__SKIP_EMAIL_ACTIVATION', true);

// Secret key should be defined in wp-config.php for security:
// define('WP_FS__truepaws_SECRET_KEY', 'your-secret-key-here');
if (!defined('WP_FS__truepaws_SECRET_KEY')) {
    define('WP_FS__truepaws_SECRET_KEY', '');
}

if ( ! function_exists( 'tru_fs' ) ) {
    function tru_fs() {
        global $tru_fs;

        if ( ! isset( $tru_fs ) ) {
            require_once dirname( __FILE__ ) . '/freemius/start.php';

            $tru_fs = fs_dynamic_init( array(
                'id'                  => '24370',
                'slug'                => 'truepaws',
                'type'                => 'plugin',
                'public_key'          => 'pk_3fdb297cdcfba64f2820ab28a9a63',
                'is_premium'          => true,
                'is_premium_only'     => true,
                'has_addons'          => false,
                'has_paid_plans'      => true,
                'wp_org_gatekeeper'   => 'OA7#BoRiBNqdf52FvzEf!!074aRLPs8fspif$7K1#4u4Csys1fQlCecVcUTOs2mcpeVHi#C2j9d09fOTvbC0HloPT7fFee5WdS3G',
                'trial'               => array(
                    'days'               => 3,
                    'is_require_payment' => true,
                ),
                'menu'                => array(
                    'slug'           => 'truepaws',
                    'parent'         => array( 'slug' => 'truepaws' ),
                    'support'        => false,
                    'account'        => true,
                    'contact'        => false,
                    'pricing'        => false,
                    'addons'         => false,
                ),
            ) );
        }

        return $tru_fs;
    }

    tru_fs();
    do_action( 'tru_fs_loaded' );

    // Hook uninstall cleanup to Freemius (runs after uninstall event is reported)
    tru_fs()->add_action( 'after_uninstall', 'tru_fs_uninstall_cleanup' );

    // Hide Freemius "Account" submenu - we use our own "License" menu instead
    add_filter( 'fs_is_submenu_visible_truepaws', function( $visible, $id ) {
        return ( $id === 'account' ) ? false : $visible;
    }, 10, 2 );
}

/**
 * Cleanup on uninstall - runs after Freemius sends uninstall data
 */
function tru_fs_uninstall_cleanup() {
    global $wpdb;

    // Drop custom tables
    $tables = array(
        $wpdb->prefix . 'bm_animal_photos',
        $wpdb->prefix . 'bm_animals',
        $wpdb->prefix . 'bm_events',
        $wpdb->prefix . 'bm_contacts',
        $wpdb->prefix . 'bm_litters'
    );

    foreach ( $tables as $table ) {
        $wpdb->query( "DROP TABLE IF EXISTS `{$table}`" );
    }

    // Delete plugin options
    $options = array(
        'truepaws_db_version',
        'truepaws_breeder_prefix',
        'truepaws_default_species',
        'truepaws_pregnancy_days_dog',
        'truepaws_pregnancy_days_cat',
        'truepaws_feeding_instructions',
        'truepaws_gemini_api_key',
        'truepaws_contact_url',
        'truepaws_webhook_url',
        'truepaws_breeder_name',
        'truepaws_business_name',
        'truepaws_license_number',
        'truepaws_breeder_phone',
        'truepaws_breeder_email',
        'truepaws_address_street',
        'truepaws_address_city',
        'truepaws_address_state',
        'truepaws_address_zip',
        'truepaws_address_country',
        'truepaws_breeds',
    );

    foreach ( $options as $option ) {
        delete_option( $option );
    }

    wp_cache_flush();
}

// Define plugin constants
define('TRUEPAWS_VERSION', '1.1.0');
define('TRUEPAWS_PLUGIN_FILE', __FILE__);
define('TRUEPAWS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TRUEPAWS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('TRUEPAWS_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Autoloader for classes
spl_autoload_register(function ($class_name) {
    if (strpos($class_name, 'TruePaws_') === 0) {
        $class_file = str_replace('_', '-', strtolower($class_name));
        $class_file = str_replace('truepaws-', '', $class_file);
        $file_path = TRUEPAWS_PLUGIN_DIR . 'includes/class-' . $class_file . '.php';

        if (file_exists($file_path)) {
            require_once $file_path;
        }
    }
});

/**
 * Main plugin class
 */
class TruePaws {

    /**
     * Single instance of the plugin
     */
    private static $instance = null;

    /**
     * Get single instance
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
        add_action('plugins_loaded', array('TruePaws_Activator', 'maybe_upgrade'), 5);
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
     * Load plugin text domain
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'truepaws',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages/'
        );
    }

    /**
     * Initialize plugin
     */
    public function init() {
        // Initialize core classes
        new TruePaws_Admin_Menu();
        new TruePaws_REST_API();
        new TruePaws_Notifications();

        // Load on admin pages only
        if (is_admin()) {
            new TruePaws_Admin();
        }

        // Load public shortcodes
        if (!is_admin()) {
            new TruePaws_Shortcodes();
        }
    }
}

// Initialize the plugin
TruePaws::get_instance();

// Include helper functions
require_once TRUEPAWS_PLUGIN_DIR . 'includes/helpers/pedigree-helpers.php';
require_once TRUEPAWS_PLUGIN_DIR . 'includes/helpers/reproduction-helpers.php';