<?php
/**
 * Fired during plugin activation
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * TruePaws Activator Class
 */
class TruePaws_Activator {

    /**
     * Plugin activation
     */
    public static function activate() {
        self::create_tables();
        self::set_default_options();
        self::create_upload_directory();

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Create custom database tables
     */
    private static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // Animals table
        $table_animals = $wpdb->prefix . 'bm_animals';
        $sql_animals = "CREATE TABLE $table_animals (
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
            INDEX idx_parents (sire_id, dam_id),
            INDEX idx_breed (breed),
            INDEX idx_sex (sex)
        ) $charset_collate;";

        // Events table
        $table_events = $wpdb->prefix . 'bm_events';
        $sql_events = "CREATE TABLE $table_events (
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
            FOREIGN KEY (animal_id) REFERENCES $table_animals(id) ON DELETE CASCADE
        ) $charset_collate;";

        // Contacts table
        $table_contacts = $wpdb->prefix . 'bm_contacts';
        $sql_contacts = "CREATE TABLE $table_contacts (
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
        $table_litters = $wpdb->prefix . 'bm_litters';
        $sql_litters = "CREATE TABLE $table_litters (
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
            FOREIGN KEY (dam_id) REFERENCES $table_animals(id),
            FOREIGN KEY (sire_id) REFERENCES $table_animals(id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // Execute table creation
        dbDelta($sql_animals);
        dbDelta($sql_events);
        dbDelta($sql_contacts);
        dbDelta($sql_litters);

        // Store current database version
        add_option('truepaws_db_version', TRUEPAWS_VERSION);
    }

    /**
     * Set default plugin options
     */
    private static function set_default_options() {
        add_option('truepaws_breeder_prefix', '');
        add_option('truepaws_default_species', 'dog');
        add_option('truepaws_pregnancy_dog_days', 63);
        add_option('truepaws_pregnancy_cat_days', 65);
        add_option('truepaws_feeding_instructions', __('Please consult with your veterinarian for specific feeding recommendations based on your puppy\'s breed, age, and health needs.', 'truepaws'));
    }

    /**
     * Create upload directory for PDFs
     */
    private static function create_upload_directory() {
        $upload_dir = wp_upload_dir();
        $truepaws_dir = $upload_dir['basedir'] . '/truepaws';

        if (!file_exists($truepaws_dir)) {
            wp_mkdir_p($truepaws_dir);
        }

        // Create .htaccess to protect PDFs
        $htaccess_file = $truepaws_dir . '/.htaccess';
        if (!file_exists($htaccess_file)) {
            $htaccess_content = "# Protect TruePaws files\n<FilesMatch \"\\.(pdf)$\">\nOrder deny,allow\nAllow from all\n</FilesMatch>\n";
            file_put_contents($htaccess_file, $htaccess_content);
        }
    }
}