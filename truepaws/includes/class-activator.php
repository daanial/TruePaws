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
     * Run database upgrades if needed (called on plugins_loaded)
     */
    public static function maybe_upgrade() {
        $current_version = get_option('truepaws_db_version', '0');
        if (version_compare($current_version, '1.1.0', '<')) {
            self::create_tables();
        }
        if (version_compare($current_version, '1.2.0', '<')) {
            self::add_animal_description_column();
        }
    }

    /**
     * Add description column to animals table (v1.2.0)
     */
    private static function add_animal_description_column() {
        global $wpdb;
        $table = $wpdb->prefix . 'bm_animals';
        $column = $wpdb->get_var("SHOW COLUMNS FROM `{$table}` LIKE 'description'");
        if (!$column) {
            $wpdb->query("ALTER TABLE `$table` ADD COLUMN description TEXT AFTER color_markings");
        }
        update_option('truepaws_db_version', '1.2.0');
    }

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

        // Animal photos table (gallery)
        $photos_table = $table_prefix . 'bm_animal_photos';
        $sql_photos = "CREATE TABLE $photos_table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            animal_id BIGINT UNSIGNED NOT NULL,
            attachment_id BIGINT UNSIGNED NOT NULL,
            sort_order INT DEFAULT 0,
            is_featured TINYINT(1) DEFAULT 0,
            caption VARCHAR(255),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_animal (animal_id),
            FOREIGN KEY (animal_id) REFERENCES $animals_table(id) ON DELETE CASCADE
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
        dbDelta($sql_photos);
        dbDelta($sql_litters);

        // Store database version
        $current_version = get_option('truepaws_db_version', '0');
        update_option('truepaws_db_version', '1.1.0');
        if (version_compare($current_version, '1.0.0', '>=') && version_compare($current_version, '1.1.0', '<')) {
            self::migrate_featured_images_to_photos();
        }
    }

    /**
     * Migrate existing featured_image_id to animal_photos table
     */
    private static function migrate_featured_images_to_photos() {
        global $wpdb;
        $animals_table = $wpdb->prefix . 'bm_animals';
        $photos_table = $wpdb->prefix . 'bm_animal_photos';

        $animals_with_images = $wpdb->get_results(
            "SELECT id, featured_image_id FROM $animals_table WHERE featured_image_id IS NOT NULL AND featured_image_id > 0",
            ARRAY_A
        );

        foreach ($animals_with_images as $row) {
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $photos_table WHERE animal_id = %d AND attachment_id = %d",
                $row['id'],
                $row['featured_image_id']
            ));
            if (!$exists) {
                $wpdb->insert($photos_table, array(
                    'animal_id' => $row['id'],
                    'attachment_id' => $row['featured_image_id'],
                    'sort_order' => 0,
                    'is_featured' => 1,
                ));
            }
        }
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
        
        // Initialize default breeds if none exist
        self::initialize_default_breeds();
    }
    
    /**
     * Initialize default breeds for dogs and cats
     */
    private static function initialize_default_breeds() {
        $existing_breeds = get_option('truepaws_breeds', '[]');
        $breeds = json_decode($existing_breeds, true);
        
        if (!is_array($breeds)) {
            $breeds = array();
        }
        
        // Check if we've already initialized default breeds by looking for a marker breed
        $has_defaults = false;
        foreach ($breeds as $breed) {
            if (isset($breed['species']) && in_array($breed['species'], array('dog', 'cat'))) {
                $has_defaults = true;
                break;
            }
        }
        
        // Only add default breeds if they haven't been added yet
        if (!$has_defaults) {
            // Get the next available ID
            $max_id = 0;
            foreach ($breeds as $breed) {
                if (isset($breed['id']) && $breed['id'] > $max_id) {
                    $max_id = $breed['id'];
                }
            }
            
            $default_breeds = array(
                // Top 20 Dog Breeds
                array('id' => $max_id + 1, 'name' => 'Labrador Retriever', 'species' => 'dog'),
                array('id' => $max_id + 2, 'name' => 'German Shepherd', 'species' => 'dog'),
                array('id' => $max_id + 3, 'name' => 'Golden Retriever', 'species' => 'dog'),
                array('id' => $max_id + 4, 'name' => 'French Bulldog', 'species' => 'dog'),
                array('id' => $max_id + 5, 'name' => 'Bulldog', 'species' => 'dog'),
                array('id' => $max_id + 6, 'name' => 'Poodle', 'species' => 'dog'),
                array('id' => $max_id + 7, 'name' => 'Beagle', 'species' => 'dog'),
                array('id' => $max_id + 8, 'name' => 'Rottweiler', 'species' => 'dog'),
                array('id' => $max_id + 9, 'name' => 'German Shorthaired Pointer', 'species' => 'dog'),
                array('id' => $max_id + 10, 'name' => 'Dachshund', 'species' => 'dog'),
                array('id' => $max_id + 11, 'name' => 'Yorkshire Terrier', 'species' => 'dog'),
                array('id' => $max_id + 12, 'name' => 'Australian Shepherd', 'species' => 'dog'),
                array('id' => $max_id + 13, 'name' => 'Boxer', 'species' => 'dog'),
                array('id' => $max_id + 14, 'name' => 'Cavalier King Charles Spaniel', 'species' => 'dog'),
                array('id' => $max_id + 15, 'name' => 'Siberian Husky', 'species' => 'dog'),
                array('id' => $max_id + 16, 'name' => 'Great Dane', 'species' => 'dog'),
                array('id' => $max_id + 17, 'name' => 'Doberman Pinscher', 'species' => 'dog'),
                array('id' => $max_id + 18, 'name' => 'Shih Tzu', 'species' => 'dog'),
                array('id' => $max_id + 19, 'name' => 'Boston Terrier', 'species' => 'dog'),
                array('id' => $max_id + 20, 'name' => 'Bernese Mountain Dog', 'species' => 'dog'),
                
                // Top 20 Cat Breeds
                array('id' => $max_id + 21, 'name' => 'Persian', 'species' => 'cat'),
                array('id' => $max_id + 22, 'name' => 'Maine Coon', 'species' => 'cat'),
                array('id' => $max_id + 23, 'name' => 'Ragdoll', 'species' => 'cat'),
                array('id' => $max_id + 24, 'name' => 'British Shorthair', 'species' => 'cat'),
                array('id' => $max_id + 25, 'name' => 'Siamese', 'species' => 'cat'),
                array('id' => $max_id + 26, 'name' => 'Abyssinian', 'species' => 'cat'),
                array('id' => $max_id + 27, 'name' => 'Bengal', 'species' => 'cat'),
                array('id' => $max_id + 28, 'name' => 'Sphynx', 'species' => 'cat'),
                array('id' => $max_id + 29, 'name' => 'Scottish Fold', 'species' => 'cat'),
                array('id' => $max_id + 30, 'name' => 'American Shorthair', 'species' => 'cat'),
                array('id' => $max_id + 31, 'name' => 'Russian Blue', 'species' => 'cat'),
                array('id' => $max_id + 32, 'name' => 'Norwegian Forest Cat', 'species' => 'cat'),
                array('id' => $max_id + 33, 'name' => 'Exotic Shorthair', 'species' => 'cat'),
                array('id' => $max_id + 34, 'name' => 'Birman', 'species' => 'cat'),
                array('id' => $max_id + 35, 'name' => 'Oriental', 'species' => 'cat'),
                array('id' => $max_id + 36, 'name' => 'Devon Rex', 'species' => 'cat'),
                array('id' => $max_id + 37, 'name' => 'Burmese', 'species' => 'cat'),
                array('id' => $max_id + 38, 'name' => 'Cornish Rex', 'species' => 'cat'),
                array('id' => $max_id + 39, 'name' => 'Tonkinese', 'species' => 'cat'),
                array('id' => $max_id + 40, 'name' => 'Turkish Angora', 'species' => 'cat'),
            );
            
            // Merge existing custom breeds with default breeds
            $merged_breeds = array_merge($breeds, $default_breeds);
            update_option('truepaws_breeds', json_encode($merged_breeds));
        }
    }
}