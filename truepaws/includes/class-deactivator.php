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
        // Clear any scheduled events if they existed
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}