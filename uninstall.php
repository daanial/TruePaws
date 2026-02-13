<?php
/**
 * Fired when the plugin is uninstalled.
 */

// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Check if user wants to delete data
$delete_data = get_option('truepaws_delete_data_on_uninstall', false);

if ($delete_data) {
    // Delete custom tables
    global $wpdb;

    $tables = array(
        $wpdb->prefix . 'bm_animals',
        $wpdb->prefix . 'bm_events',
        $wpdb->prefix . 'bm_contacts',
        $wpdb->prefix . 'bm_litters',
    );

    foreach ($tables as $table) {
        $wpdb->query("DROP TABLE IF EXISTS $table");
    }

    // Delete plugin options
    $options = array(
        'truepaws_db_version',
        'truepaws_breeder_prefix',
        'truepaws_default_species',
        'truepaws_pregnancy_dog_days',
        'truepaws_pregnancy_cat_days',
        'truepaws_feeding_instructions',
        'truepaws_delete_data_on_uninstall',
    );

    foreach ($options as $option) {
        delete_option($option);
    }

    // Delete uploaded files
    $upload_dir = wp_upload_dir();
    $truepaws_dir = $upload_dir['basedir'] . '/truepaws';

    if (file_exists($truepaws_dir)) {
        // Use WordPress function to safely delete directory
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        if (function_exists('WP_Filesystem')) {
            WP_Filesystem();
            global $wp_filesystem;
            $wp_filesystem->delete($truepaws_dir, true);
        }
    }
}