<?php
/**
 * Admin menu and page setup for TruePaws
 */

if (!defined('ABSPATH')) {
    exit;
}

class TruePaws_Admin_Menu {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));

        // Register settings early
        add_action('admin_init', array($this, 'register_plugin_settings'));
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

        // Add settings submenu
        add_submenu_page(
            'truepaws',
            __('Settings', 'truepaws'),
            __('Settings', 'truepaws'),
            'manage_options',
            'truepaws_settings',
            array($this, 'settings_page')
        );

        // Add License submenu (redirects to Freemius account page)
        add_submenu_page(
            'truepaws',
            __('License', 'truepaws'),
            __('License', 'truepaws'),
            'manage_options',
            'truepaws_license',
            array($this, 'license_page')
        );
    }

    /**
     * License page - redirects to Freemius account/license UI
     * Uses JavaScript redirect to avoid "headers already sent" errors
     */
    public function license_page() {
        if (function_exists('tru_fs')) {
            $account_url = tru_fs()->get_account_url();
            ?>
            <div class="wrap">
                <p><?php esc_html_e('Redirecting to license page...', 'truepaws'); ?></p>
                <script>window.location.replace(<?php echo wp_json_encode(esc_url($account_url)); ?>);</script>
                <p><a href="<?php echo esc_url($account_url); ?>"><?php esc_html_e('Click here if you are not redirected.', 'truepaws'); ?></a></p>
            </div>
            <?php
            return;
        }
        echo '<div class="wrap"><h1>' . esc_html__('License', 'truepaws') . '</h1><p>' . esc_html__('Freemius is not loaded.', 'truepaws') . '</p></div>';
    }

    /**
     * Register plugin settings
     */
    public function register_plugin_settings() {
        // #region agent log
        $log_path = '/Users/daanial/Desktop/apps/TruePaws/.cursor/debug.log';
        $log_data = json_encode(['sessionId'=>'debug-session','runId'=>'settings','hypothesisId'=>'B,D','location'=>'class-admin-menu.php:register_plugin_settings:entry','message'=>'Registering settings in admin-menu','data'=>['current_filter'=>current_filter(),'did_action_admin_init'=>did_action('admin_init')],'timestamp'=>time()*1000]);
        @file_put_contents($log_path, $log_data."\n", FILE_APPEND);
        // #endregion
        
        // Register settings with proper sanitization
        register_setting(
            'truepaws_settings_group',
            'truepaws_breeder_prefix',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => 'TP'
            )
        );
        register_setting(
            'truepaws_settings_group',
            'truepaws_default_species',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => 'dog'
            )
        );
        register_setting(
            'truepaws_settings_group',
            'truepaws_pregnancy_days_dog',
            array(
                'type' => 'integer',
                'sanitize_callback' => 'absint',
                'default' => 63
            )
        );
        register_setting(
            'truepaws_settings_group',
            'truepaws_pregnancy_days_cat',
            array(
                'type' => 'integer',
                'sanitize_callback' => 'absint',
                'default' => 65
            )
        );
        register_setting(
            'truepaws_settings_group',
            'truepaws_feeding_instructions',
            array(
                'type' => 'string',
                'sanitize_callback' => 'wp_kses_post',
                'default' => 'Provide high-quality puppy food according to age and breed requirements. Consult with your veterinarian for specific feeding recommendations.'
            )
        );
        register_setting(
            'truepaws_settings_group',
            'truepaws_gemini_api_key',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => ''
            )
        );

        // Breeder information settings
        register_setting(
            'truepaws_settings_group',
            'truepaws_breeder_name',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => ''
            )
        );
        register_setting(
            'truepaws_settings_group',
            'truepaws_business_name',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => ''
            )
        );
        register_setting(
            'truepaws_settings_group',
            'truepaws_license_number',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => ''
            )
        );
        register_setting(
            'truepaws_settings_group',
            'truepaws_breeder_phone',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => ''
            )
        );
        register_setting(
            'truepaws_settings_group',
            'truepaws_breeder_email',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_email',
                'default' => ''
            )
        );

        // Address settings
        register_setting(
            'truepaws_settings_group',
            'truepaws_address_street',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => ''
            )
        );
        register_setting(
            'truepaws_settings_group',
            'truepaws_address_city',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => ''
            )
        );
        register_setting(
            'truepaws_settings_group',
            'truepaws_address_state',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => ''
            )
        );
        register_setting(
            'truepaws_settings_group',
            'truepaws_address_zip',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => ''
            )
        );
        register_setting(
            'truepaws_settings_group',
            'truepaws_address_country',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => ''
            )
        );
        register_setting(
            'truepaws_settings_group',
            'truepaws_contact_url',
            array(
                'type' => 'string',
                'sanitize_callback' => 'esc_url_raw',
                'default' => '#contact'
            )
        );

        // Breeds management (stored as JSON)
        register_setting(
            'truepaws_settings_group',
            'truepaws_breeds',
            array(
                'type' => 'string',
                'sanitize_callback' => array($this, 'sanitize_breeds_json'),
                'default' => '[]'
            )
        );
    }

    /**
     * Sanitize breeds JSON data
     */
    public function sanitize_breeds_json($value) {
        if (empty($value)) {
            return '[]';
        }
        
        // If it's already a JSON string, validate it
        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            // Re-encode to ensure clean JSON
            return json_encode($decoded);
        }
        
        return '[]';
    }

    /**
     * Main admin page (loads React app)
     */
    public function admin_page() {
        // #region agent log
        $log_path = '/Users/daanial/Desktop/apps/TruePaws/.cursor/debug.log';
        $log_data = json_encode(['sessionId'=>'debug-session','runId'=>'page-load','hypothesisId'=>'C','location'=>'class-admin-menu.php:admin_page:entry','message'=>'Admin page loading','data'=>['TRUEPAWS_PLUGIN_DIR'=>TRUEPAWS_PLUGIN_DIR,'script_check'=>TRUEPAWS_PLUGIN_DIR.'assets/build/main.js'],'timestamp'=>time()*1000]);
        @file_put_contents($log_path, $log_data."\n", FILE_APPEND);
        // #endregion
        
        $script_path = TRUEPAWS_PLUGIN_DIR . 'assets/build/main.js';

        // #region agent log
        $log_data = json_encode(['sessionId'=>'debug-session','runId'=>'page-load','hypothesisId'=>'C','location'=>'class-admin-menu.php:admin_page:check','message'=>'Script path check','data'=>['script_path'=>$script_path,'file_exists'=>file_exists($script_path),'is_readable'=>is_readable($script_path)],'timestamp'=>time()*1000]);
        @file_put_contents($log_path, $log_data."\n", FILE_APPEND);
        // #endregion
        
        if (!file_exists($script_path)) {
            ?>
            <div class="wrap">
                <h1><?php _e('TruePaws - Kennel Management System', 'truepaws'); ?></h1>
                <div class="notice notice-error">
                    <p><strong><?php _e('TruePaws React application not found.', 'truepaws'); ?></strong></p>
                    <p><?php _e('The frontend assets need to be built. Please run these commands in your terminal:', 'truepaws'); ?></p>
                    <pre style="background: #f1f1f1; padding: 10px; border-radius: 4px; font-family: monospace;">
cd <?php echo esc_html(TRUEPAWS_PLUGIN_DIR); ?>
npm install
npm run build</pre>
                    <p><?php _e('After building, refresh this page.', 'truepaws'); ?></p>
                </div>
            </div>
            <?php
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php _e('TruePaws - Kennel Management System', 'truepaws'); ?></h1>
            <div id="truepaws-app"></div>
        </div>
        <?php
    }

    /**
     * Settings page
     */
    public function settings_page() {
        $script_path = TRUEPAWS_PLUGIN_DIR . 'assets/build/main.js';

        if (!file_exists($script_path)) {
            ?>
            <div class="wrap">
                <h1><?php _e('TruePaws Settings', 'truepaws'); ?></h1>
                <div class="notice notice-error">
                    <p><strong><?php _e('TruePaws React application not found.', 'truepaws'); ?></strong></p>
                    <p><?php _e('The frontend assets need to be built. Please run these commands in your terminal:', 'truepaws'); ?></p>
                    <pre style="background: #f1f1f1; padding: 10px; border-radius: 4px; font-family: monospace;">
cd <?php echo esc_html(TRUEPAWS_PLUGIN_DIR); ?>
npm install
npm run build</pre>
                    <p><?php _e('After building, refresh this page.', 'truepaws'); ?></p>
                </div>
                <hr>
                <h2><?php _e('Fallback Settings Form', 'truepaws'); ?></h2>
                <form method="post" action="options.php">
                    <?php
                    settings_fields('truepaws_settings_group');
                    do_settings_sections('truepaws_settings');
                    submit_button();
                    ?>
                </form>
            </div>
            <?php
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php _e('TruePaws Settings', 'truepaws'); ?></h1>
            <div id="truepaws-app"></div>
        </div>
        <script>
            // Navigate to settings route when settings page loads
            if (window.location.hash !== '#/settings') {
                window.location.hash = '#/settings';
            }
        </script>
        <?php
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        // Only load on TruePaws pages
        if (!in_array($hook, array('toplevel_page_truepaws', 'truepaws_page_truepaws_settings'))) {
            return;
        }

        // Enqueue WordPress media uploader
        wp_enqueue_media();
        
        // Enqueue React app
        $script_path = TRUEPAWS_PLUGIN_DIR . 'assets/build/main.js';
        $script_url = TRUEPAWS_PLUGIN_URL . 'assets/build/main.js';

        if (file_exists($script_path)) {
            wp_enqueue_script(
                'truepaws-app',
                $script_url,
                array('wp-api', 'wp-i18n', 'jquery'),
                TRUEPAWS_VERSION,
                true
            );

            // Localize script with necessary data
            wp_localize_script('truepaws-app', 'truepawsData', array(
                'apiUrl' => rest_url('truepaws/v1/'),
                'pluginUrl' => TRUEPAWS_PLUGIN_URL,
                'nonce' => wp_create_nonce('wp_rest'),
                'strings' => array(
                    'loading' => __('Loading...', 'truepaws'),
                    'error' => __('Error', 'truepaws'),
                    'success' => __('Success', 'truepaws'),
                )
            ));

            // Enqueue styles
            wp_enqueue_style(
                'truepaws-admin',
                TRUEPAWS_PLUGIN_URL . 'assets/src/styles/main.css',
                array(),
                TRUEPAWS_VERSION
            );
        }
    }
}