<?php
/**
 * Helper functions for reproduction calculations
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Calculate expected whelping date
 *
 * @param string $mating_date Date in Y-m-d format
 * @param string $species 'dog' or 'cat'
 * @return string|false Expected whelping date in Y-m-d format, or false on error
 */
function truepaws_calculate_whelping_date($mating_date, $species = 'dog') {
    if (!truepaws_validate_date($mating_date)) {
        return false;
    }

    $days = truepaws_get_pregnancy_days($species);
    if (!$days) {
        return false;
    }

    $mating_timestamp = strtotime($mating_date);
    $whelping_timestamp = strtotime("+$days days", $mating_timestamp);

    return date('Y-m-d', $whelping_timestamp);
}

/**
 * Get pregnancy duration in days for species
 *
 * @param string $species
 * @return int|false
 */
function truepaws_get_pregnancy_days($species = 'dog') {
    $option_key = 'truepaws_pregnancy_' . strtolower($species) . '_days';
    $days = get_option($option_key, false);

    if ($days === false) {
        // Default values
        $defaults = array(
            'dog' => 63,
            'cat' => 65,
        );

        $days = isset($defaults[$species]) ? $defaults[$species] : false;
    }

    return $days ? absint($days) : false;
}

/**
 * Calculate expected heat cycle date
 *
 * @param string $last_heat_date Date in Y-m-d format
 * @param string $species 'dog' or 'cat'
 * @return string|false Next expected heat date in Y-m-d format, or false on error
 */
function truepaws_calculate_next_heat_date($last_heat_date, $species = 'dog') {
    if (!truepaws_validate_date($last_heat_date)) {
        return false;
    }

    $cycle_days = truepaws_get_heat_cycle_days($species);
    if (!$cycle_days) {
        return false;
    }

    $last_heat_timestamp = strtotime($last_heat_date);
    $next_heat_timestamp = strtotime("+$cycle_days days", $last_heat_timestamp);

    return date('Y-m-d', $next_heat_timestamp);
}

/**
 * Get heat cycle duration in days for species
 *
 * @param string $species
 * @return int
 */
function truepaws_get_heat_cycle_days($species = 'dog') {
    // Default heat cycle lengths
    $defaults = array(
        'dog' => 180, // 6 months
        'cat' => 21,  // 3 weeks (more frequent)
    );

    return isset($defaults[$species]) ? $defaults[$species] : 180;
}

/**
 * Generate unique litter name
 *
 * @param int $sire_id
 * @param int $dam_id
 * @param string $mating_date
 * @return string
 */
function truepaws_generate_litter_name($sire_id, $dam_id, $mating_date) {
    global $wpdb;

    $table_animals = $wpdb->prefix . 'bm_animals';
    $table_litters = $wpdb->prefix . 'bm_litters';

    // Get sire and dam names
    $parents = $wpdb->get_row($wpdb->prepare(
        "SELECT
            s.name as sire_name,
            d.name as dam_name
         FROM $table_animals s, $table_animals d
         WHERE s.id = %d AND d.id = %d",
        $sire_id, $dam_id
    ));

    if (!$parents) {
        return 'Litter-' . date('Y-m-d');
    }

    // Create base litter name
    $year = date('Y', strtotime($mating_date));
    $base_name = $parents->dam_name . '-' . $parents->sire_name . '-' . $year;

    // Check if name exists, append suffix if needed
    $existing_count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_litters WHERE litter_name LIKE %s",
        $wpdb->esc_like($base_name) . '%'
    ));

    if ($existing_count > 0) {
        $base_name .= '-' . ($existing_count + 1);
    }

    return $base_name;
}

/**
 * Generate puppy names for litter
 *
 * @param string $litter_name
 * @param int $count
 * @param string $sex 'M' or 'F'
 * @return array
 */
function truepaws_generate_puppy_names($litter_name, $count, $sex = 'M') {
    $names = array();

    for ($i = 1; $i <= $count; $i++) {
        $names[] = 'Puppy ' . $i . ' (' . $litter_name . ')';
    }

    return $names;
}

/**
 * Validate date format
 *
 * @param string $date
 * @return bool
 */
function truepaws_validate_date($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

/**
 * Format date for display
 *
 * @param string $date Date in Y-m-d format
 * @return string
 */
function truepaws_format_date($date) {
    if (empty($date)) {
        return '';
    }

    return date_i18n(get_option('date_format'), strtotime($date));
}

/**
 * Get litter statistics
 *
 * @param int $litter_id
 * @return array
 */
function truepaws_get_litter_stats($litter_id) {
    global $wpdb;

    $table_litters = $wpdb->prefix . 'bm_litters';

    $stats = $wpdb->get_row($wpdb->prepare(
        "SELECT
            litter_name,
            puppy_count_male,
            puppy_count_female,
            actual_whelping_date,
            mating_date
         FROM $table_litters
         WHERE id = %d",
        $litter_id
    ), ARRAY_A);

    if (!$stats) {
        return false;
    }

    $stats['total_puppies'] = $stats['puppy_count_male'] + $stats['puppy_count_female'];
    $stats['formatted_mating_date'] = truepaws_format_date($stats['mating_date']);
    $stats['formatted_whelping_date'] = truepaws_format_date($stats['actual_whelping_date']);

    return $stats;
}