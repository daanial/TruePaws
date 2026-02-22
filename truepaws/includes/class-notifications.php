<?php
/**
 * Handle automated email notifications
 */

if (!defined('ABSPATH')) {
    exit;
}

class TruePaws_Notifications {

    /**
     * Initialize notifications
     */
    public function __construct() {
        // Schedule daily cron job if not already scheduled
        if (!wp_next_scheduled('truepaws_daily_notifications')) {
            wp_schedule_event(time(), 'daily', 'truepaws_daily_notifications');
        }

        add_action('truepaws_daily_notifications', array($this, 'send_daily_notifications'));
    }

    /**
     * Send daily notifications
     */
    public function send_daily_notifications() {
        $this->send_upcoming_whelping_reminders();
        $this->send_vaccination_reminders();
    }

    /**
     * Send reminders for upcoming whelping dates (7 days before)
     */
    private function send_upcoming_whelping_reminders() {
        global $wpdb;

        // #region agent log
        $log_path = '/Users/daanial/Desktop/apps/TruePaws/.cursor/debug-88b937.log';
        $log_data = json_encode(['sessionId'=>'88b937','runId'=>'debug','hypothesisId'=>'D','location'=>'class-notifications.php:35:entry','message'=>'Whelping reminders check','data'=>['current_date'=>date('Y-m-d')],'timestamp'=>time()*1000]);
        @file_put_contents($log_path, $log_data."\n", FILE_APPEND);
        // #endregion

        $breeder_email = get_option('truepaws_breeder_email', '');
        if (empty($breeder_email) || !is_email($breeder_email)) {
            // #region agent log
            $log_data = json_encode(['sessionId'=>'88b937','runId'=>'debug','hypothesisId'=>'D','location'=>'class-notifications.php:39:no_email','message'=>'Breeder email not configured','data'=>['email'=>$breeder_email],'timestamp'=>time()*1000]);
            @file_put_contents($log_path, $log_data."\n", FILE_APPEND);
            // #endregion
            return;
        }

        // Get litters with expected whelping date in 7 days
        $litters = $wpdb->get_results(
            "SELECT L.*, S.name as sire_name, D.name as dam_name
             FROM {$wpdb->prefix}bm_litters L
             LEFT JOIN {$wpdb->prefix}bm_animals S ON L.sire_id = S.id
             LEFT JOIN {$wpdb->prefix}bm_animals D ON L.dam_id = D.id
             WHERE L.actual_whelping_date IS NULL
             AND L.expected_whelping_date = DATE_ADD(CURDATE(), INTERVAL 7 DAY)",
            ARRAY_A
        );

        // #region agent log
        $log_data = json_encode(['sessionId'=>'88b937','runId'=>'debug','hypothesisId'=>'D','location'=>'class-notifications.php:52:query_result','message'=>'Whelping reminder query executed','data'=>['litters_found'=>count($litters),'query'=>$wpdb->last_query],'timestamp'=>time()*1000]);
        @file_put_contents($log_path, $log_data."\n", FILE_APPEND);
        // #endregion

        if (empty($litters)) {
            return;
        }

        foreach ($litters as $litter) {
            $subject = sprintf(__('[TruePaws] Whelping Reminder: %s', 'truepaws'), $litter['litter_name']);
            $message = sprintf(
                __("This is a reminder that the litter %s is expected to whelp in 7 days.\n\nExpected Date: %s\nSire: %s\nDam: %s\n\nPlease ensure all preparations are ready.\n\nThis is an automated notification from TruePaws.", 'truepaws'),
                $litter['litter_name'],
                $litter['expected_whelping_date'],
                $litter['sire_name'],
                $litter['dam_name']
            );

            wp_mail($breeder_email, $subject, $message, array('Content-Type: text/plain; charset=UTF-8'));
        }
    }

    /**
     * Send vaccination reminders for animals
     */
    private function send_vaccination_reminders() {
        global $wpdb;

        // #region agent log
        $log_path = '/Users/daanial/Desktop/apps/TruePaws/.cursor/debug-88b937.log';
        $log_data = json_encode(['sessionId'=>'88b937','runId'=>'debug','hypothesisId'=>'D','location'=>'class-notifications.php:75:entry','message'=>'Vaccination reminders check','data'=>['current_date'=>date('Y-m-d')],'timestamp'=>time()*1000]);
        @file_put_contents($log_path, $log_data."\n", FILE_APPEND);
        // #endregion

        $breeder_email = get_option('truepaws_breeder_email', '');
        if (empty($breeder_email) || !is_email($breeder_email)) {
            return;
        }

        // Get animals that may need vaccinations (based on age)
        // Puppies at 6, 9, 12, 16 weeks
        $animals = $wpdb->get_results(
            "SELECT id, name, breed, birth_date, 
                    DATEDIFF(CURDATE(), birth_date) as age_days
             FROM {$wpdb->prefix}bm_animals
             WHERE status = 'active'
             AND birth_date IS NOT NULL
             AND (
                 DATEDIFF(CURDATE(), birth_date) IN (42, 63, 84, 112) -- 6, 9, 12, 16 weeks
                 OR DATEDIFF(CURDATE(), birth_date) = 365 -- 1 year
                 OR DATEDIFF(CURDATE(), birth_date) = 730 -- 2 years
                 OR DATEDIFF(CURDATE(), birth_date) = 1095 -- 3 years
             )",
            ARRAY_A
        );

        // #region agent log
        $log_data = json_encode(['sessionId'=>'88b937','runId'=>'debug','hypothesisId'=>'D','location'=>'class-notifications.php:98:query_result','message'=>'Vaccination reminder query executed','data'=>['animals_found'=>count($animals),'query'=>$wpdb->last_query],'timestamp'=>time()*1000]);
        @file_put_contents($log_path, $log_data."\n", FILE_APPEND);
        // #endregion

        if (empty($animals)) {
            return;
        }

        $reminders = array();
        foreach ($animals as $animal) {
            $age_days = intval($animal['age_days']);
            $reminder_type = '';

            if ($age_days == 42) {
                $reminder_type = '6 weeks - First vaccination';
            } elseif ($age_days == 63) {
                $reminder_type = '9 weeks - Second vaccination';
            } elseif ($age_days == 84) {
                $reminder_type = '12 weeks - Third vaccination';
            } elseif ($age_days == 112) {
                $reminder_type = '16 weeks - Final puppy vaccination';
            } elseif ($age_days == 365) {
                $reminder_type = '1 year - Annual booster';
            } elseif ($age_days == 730) {
                $reminder_type = '2 years - Biennial check';
            } elseif ($age_days == 1095) {
                $reminder_type = '3 years - Triennial check';
            }

            if ($reminder_type) {
                $reminders[] = sprintf(
                    "%s (%s) - %s",
                    $animal['name'],
                    $animal['breed'],
                    $reminder_type
                );
            }
        }

        if (!empty($reminders)) {
            $subject = __('[TruePaws] Vaccination Reminders', 'truepaws');
            $message = __("The following animals may be due for vaccinations or health checks:\n\n", 'truepaws');
            $message .= implode("\n", $reminders);
            $message .= "\n\n" . __('Please consult with your veterinarian for specific vaccination schedules.\n\nThis is an automated notification from TruePaws.', 'truepaws');

            wp_mail($breeder_email, $subject, $message, array('Content-Type: text/plain; charset=UTF-8'));
        }
    }

    /**
     * Clear scheduled events on plugin deactivation
     */
    public static function clear_scheduled_events() {
        wp_clear_scheduled_hook('truepaws_daily_notifications');
    }
}
