<?php
/**
 * Fired during plugin deactivation
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * TruePaws Deactivator Class
 */
class TruePaws_Deactivator {

    /**
     * Plugin deactivation
     */
    public static function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();

        // Clear any scheduled events if they exist
        // wp_clear_scheduled_hook('truepaws_daily_cleanup');
    }
}