<?php
/**
 * Admin-specific functionality for TruePaws
 */

if (!defined('ABSPATH')) {
    exit;
}

class TruePaws_Admin {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_init', array($this, 'init_settings_sections'));
    }

    /**
     * Initialize settings sections and fields
     */
    public function init_settings_sections() {
        // #region agent log
        $log_path = '/Users/daanial/Desktop/apps/TruePaws/.cursor/debug.log';
        $log_data = json_encode(['sessionId'=>'debug-session','runId'=>'settings','hypothesisId'=>'B,D','location'=>'class-admin.php:init_settings_sections:entry','message'=>'Registering settings sections in admin','data'=>['current_filter'=>current_filter(),'did_action_admin_init'=>did_action('admin_init')],'timestamp'=>time()*1000]);
        @file_put_contents($log_path, $log_data."\n", FILE_APPEND);
        // #endregion
        
        add_settings_section(
            'truepaws_general',
            __('General Settings', 'truepaws'),
            array($this, 'settings_section_callback'),
            'truepaws_settings'
        );

        add_settings_field(
            'truepaws_default_species',
            __('Default Species', 'truepaws'),
            array($this, 'default_species_callback'),
            'truepaws_settings',
            'truepaws_general'
        );

        add_settings_field(
            'truepaws_breeder_prefix',
            __('Breeder Prefix', 'truepaws'),
            array($this, 'breeder_prefix_callback'),
            'truepaws_settings',
            'truepaws_general'
        );

        add_settings_field(
            'truepaws_pregnancy_days',
            __('Pregnancy Duration (Days)', 'truepaws'),
            array($this, 'pregnancy_days_callback'),
            'truepaws_settings',
            'truepaws_general'
        );

        add_settings_field(
            'truepaws_feeding_instructions',
            __('Feeding Instructions', 'truepaws'),
            array($this, 'feeding_instructions_callback'),
            'truepaws_settings',
            'truepaws_general'
        );

        add_settings_field(
            'truepaws_gemini_api_key',
            __('Gemini API Key', 'truepaws'),
            array($this, 'gemini_api_key_callback'),
            'truepaws_settings',
            'truepaws_general'
        );
    }

    /**
     * Settings section callback
     */
    public function settings_section_callback() {
        echo '<p>' . __('Configure general settings for TruePaws.', 'truepaws') . '</p>';
    }

    /**
     * Breeder prefix field
     */
    public function breeder_prefix_callback() {
        $value = get_option('truepaws_breeder_prefix', 'TP');
        echo '<input type="text" name="truepaws_breeder_prefix" value="' . esc_attr($value) . '" maxlength="5" />';
        echo '<p class="description">' . __('Prefix used for litter and registration numbers (e.g., TP for TruePaws).', 'truepaws') . '</p>';
    }

    /**
     * Default species field - main type of animal user is breeding
     */
    public function default_species_callback() {
        $value = get_option('truepaws_default_species', 'dog');
        ?>
        <select name="truepaws_default_species">
            <option value="dog" <?php selected($value, 'dog'); ?>><?php _e('Dog', 'truepaws'); ?></option>
            <option value="cat" <?php selected($value, 'cat'); ?>><?php _e('Cat', 'truepaws'); ?></option>
            <option value="horse" <?php selected($value, 'horse'); ?>><?php _e('Horse', 'truepaws'); ?></option>
            <option value="rabbit" <?php selected($value, 'rabbit'); ?>><?php _e('Rabbit', 'truepaws'); ?></option>
            <option value="guinea_pig" <?php selected($value, 'guinea_pig'); ?>><?php _e('Guinea Pig', 'truepaws'); ?></option>
            <option value="ferret" <?php selected($value, 'ferret'); ?>><?php _e('Ferret', 'truepaws'); ?></option>
            <option value="bird" <?php selected($value, 'bird'); ?>><?php _e('Bird', 'truepaws'); ?></option>
        </select>
        <p class="description"><?php _e('The main type of animal you are breeding. This applies to all animals, litters, and AI care advice.', 'truepaws'); ?></p>
        <?php
    }

    /**
     * Pregnancy days fields
     */
    public function pregnancy_days_callback() {
        $dog_days = get_option('truepaws_pregnancy_days_dog', 63);
        $cat_days = get_option('truepaws_pregnancy_days_cat', 65);
        ?>
        <label><?php _e('Dogs:', 'truepaws'); ?> <input type="number" name="truepaws_pregnancy_days_dog" value="<?php echo esc_attr($dog_days); ?>" min="50" max="80" /></label><br>
        <label><?php _e('Cats:', 'truepaws'); ?> <input type="number" name="truepaws_pregnancy_days_cat" value="<?php echo esc_attr($cat_days); ?>" min="50" max="80" /></label>
        <p class="description"><?php _e('Average gestation period in days.', 'truepaws'); ?></p>
        <?php
    }

    /**
     * Feeding instructions field
     */
    public function feeding_instructions_callback() {
        $value = get_option('truepaws_feeding_instructions', '');
        wp_editor(
            $value,
            'truepaws_feeding_instructions',
            array(
                'textarea_name' => 'truepaws_feeding_instructions',
                'textarea_rows' => 5,
                'media_buttons' => false,
                'tinymce' => array(
                    'toolbar1' => 'bold,italic,underline,|,bullist,numlist,|,link,unlink',
                    'toolbar2' => '',
                ),
            )
        );
        echo '<p class="description">' . __('Default feeding instructions included in handover packets.', 'truepaws') . '</p>';
    }

    /**
     * Gemini API key field
     */
    public function gemini_api_key_callback() {
        $value = get_option('truepaws_gemini_api_key', '');
        echo '<input type="password" name="truepaws_gemini_api_key" value="' . esc_attr($value) . '" class="regular-text" autocomplete="off" />';
        echo '<p class="description">' . __('API key for Gemini AI care advice on animal profiles. Get one at <a href="https://aistudio.google.com/apikey" target="_blank" rel="noopener">Google AI Studio</a>.', 'truepaws') . '</p>';
    }
}