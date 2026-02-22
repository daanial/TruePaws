<?php
/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 */

if (!defined('ABSPATH')) {
    exit;
}

class TruePaws_Deactivator {

    /**
     * Plugin deactivation
     */
    public static function deactivate() {
        // Clear scheduled events
        TruePaws_Notifications::clear_scheduled_events();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}