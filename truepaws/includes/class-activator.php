<?php
/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 */

if (!defined('ABSPATH')) {
    exit;
}

class TruePaws_Activator {

    /**
     * Plugin activation
     */
    public static function activate() {
        // #region agent log
        $log_path = '/Users/daanial/Desktop/apps/TruePaws/.cursor/debug.log';
        $log_data = json_encode(['sessionId'=>'debug-session','runId'=>'activation','hypothesisId'=>'E','location'=>'class-activator.php:activate:entry','message'=>'Activator started','data'=>['time'=>time()],'timestamp'=>time()*1000]);
        @file_put_contents($log_path, $log_data."\n", FILE_APPEND);
        // #endregion
        
        self::create_tables();
        self::set_default_options();
        flush_rewrite_rules();
        
        // #region agent log
        $log_data = json_encode(['sessionId'=>'debug-session','runId'=>'activation','hypothesisId'=>'E','location'=>'class-activator.php:activate:exit','message'=>'Activator completed','data'=>['db_version'=>get_option('truepaws_db_version'),'prefix'=>get_option('truepaws_breeder_prefix')],'timestamp'=>time()*1000]);
        @file_put_contents($log_path, $log_data."\n", FILE_APPEND);
        // #endregion
    }

    /**
     * Create custom database tables
     */
    private static function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $table_prefix = $wpdb->prefix;

        // Animals table
        $animals_table = $table_prefix . 'bm_animals';
        $sql_animals = "CREATE TABLE $animals_table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            call_name VARCHAR(100),
            registration_number VARCHAR(100),
            microchip_id VARCHAR(50) UNIQUE,
            breed VARCHAR(100),
            color_markings TEXT,
            sex ENUM('M', 'F') NOT NULL,
            sire_id BIGINT UNSIGNED NULL,
            dam_id BIGINT UNSIGNED NULL,
            birth_date DATE,
            status ENUM('active', 'retired', 'sold', 'deceased', 'co-owned') DEFAULT 'active',
            featured_image_id BIGINT UNSIGNED,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_microchip (microchip_id),
            INDEX idx_status (status),
            INDEX idx_parents (sire_id, dam_id)
        ) $charset_collate;";

        // Events table
        $events_table = $table_prefix . 'bm_events';
        $sql_events = "CREATE TABLE $events_table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            animal_id BIGINT UNSIGNED NOT NULL,
            event_type ENUM('birth', 'vaccine', 'heat', 'mating', 'whelping', 'weight', 'vet_visit', 'note') NOT NULL,
            event_date DATETIME NOT NULL,
            title VARCHAR(255),
            meta_data JSON,
            created_by BIGINT UNSIGNED,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_animal_date (animal_id, event_date),
            INDEX idx_event_type (event_type),
            FOREIGN KEY (animal_id) REFERENCES $animals_table(id) ON DELETE CASCADE
        ) $charset_collate;";

        // Contacts table
        $contacts_table = $table_prefix . 'bm_contacts';
        $sql_contacts = "CREATE TABLE $contacts_table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100),
            email VARCHAR(255) NOT NULL,
            phone VARCHAR(50),
            address TEXT,
            notes TEXT,
            status ENUM('waitlist', 'reserved', 'buyer', 'inactive') DEFAULT 'waitlist',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_status (status)
        ) $charset_collate;";

        // Litters table
        $litters_table = $table_prefix . 'bm_litters';
        $sql_litters = "CREATE TABLE $litters_table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            litter_name VARCHAR(100) NOT NULL,
            dam_id BIGINT UNSIGNED NOT NULL,
            sire_id BIGINT UNSIGNED NOT NULL,
            mating_date DATE,
            mating_method ENUM('natural', 'ai') DEFAULT 'natural',
            expected_whelping_date DATE,
            actual_whelping_date DATE,
            puppy_count_male INT DEFAULT 0,
            puppy_count_female INT DEFAULT 0,
            notes TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_parents (dam_id, sire_id),
            INDEX idx_whelping (expected_whelping_date),
            FOREIGN KEY (dam_id) REFERENCES $animals_table(id),
            FOREIGN KEY (sire_id) REFERENCES $animals_table(id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // Execute table creation
        dbDelta($sql_animals);
        dbDelta($sql_events);
        dbDelta($sql_contacts);
        dbDelta($sql_litters);

        // Store database version
        add_option('truepaws_db_version', '1.0.0');
    }

    /**
     * Set default plugin options
     */
    private static function set_default_options() {
        // Default settings
        add_option('truepaws_breeder_prefix', 'TP');
        add_option('truepaws_default_species', 'dog');
        add_option('truepaws_pregnancy_days_dog', 63);
        add_option('truepaws_pregnancy_days_cat', 65);
        add_option('truepaws_feeding_instructions', 'Provide high-quality puppy food according to age and breed requirements. Consult with your veterinarian for specific feeding recommendations.');
    }
}