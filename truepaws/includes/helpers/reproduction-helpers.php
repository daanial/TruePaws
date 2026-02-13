<?php
/**
 * Reproduction helper functions
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Calculate expected whelping date
 *
 * @param string $mating_date
 * @param string $species
 * @return string|null
 */
function truepaws_calculate_whelping_date($mating_date, $species = 'dog') {
    if (empty($mating_date)) {
        return null;
    }

    try {
        $date = new DateTime($mating_date);

        // Get pregnancy duration based on species
        $days = truepaws_get_pregnancy_days($species);

        $date->modify("+{$days} days");

        return $date->format('Y-m-d');
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Get pregnancy duration in days by species
 *
 * @param string $species
 * @return int
 */
function truepaws_get_pregnancy_days($species = 'dog') {
    switch (strtolower($species)) {
        case 'cat':
            return get_option('truepaws_pregnancy_days_cat', 65);
        case 'horse':
            return get_option('truepaws_pregnancy_days_horse', 340);
        case 'rabbit':
            return get_option('truepaws_pregnancy_days_rabbit', 31);
        case 'guinea_pig':
            return get_option('truepaws_pregnancy_days_guinea_pig', 68);
        case 'ferret':
            return get_option('truepaws_pregnancy_days_ferret', 42);
        case 'bird':
            return get_option('truepaws_pregnancy_days_bird', 0);
        case 'dog':
        default:
            return get_option('truepaws_pregnancy_days_dog', 63);
    }
}

/**
 * Generate litter name
 *
 * @param string $sire_name
 * @param string $dam_name
 * @param string $mating_date
 * @return string
 */
function truepaws_generate_litter_name($sire_name, $dam_name, $mating_date) {
    $prefix = get_option('truepaws_breeder_prefix', 'TP');

    try {
        $date = new DateTime($mating_date);
        $year = $date->format('y');
    } catch (Exception $e) {
        $year = date('y');
    }

    // Extract first letter of each parent's name
    $sire_letter = strtoupper(substr($sire_name, 0, 1));
    $dam_letter = strtoupper(substr($dam_name, 0, 1));

    return $prefix . $year . $sire_letter . $dam_letter;
}

/**
 * Generate puppy names for a litter
 *
 * @param string $litter_name
 * @param int $male_count
 * @param int $female_count
 * @return array
 */
function truepaws_generate_puppy_names($litter_name, $male_count, $female_count) {
    $names = array();

    // Generate male puppy names
    for ($i = 1; $i <= $male_count; $i++) {
        $names['male'][] = "{$litter_name} Puppy {$i}";
    }

    // Generate female puppy names
    for ($i = 1; $i <= $female_count; $i++) {
        $names['female'][] = "{$litter_name} Puppy {$i}";
    }

    return $names;
}

/**
 * Get litter statistics
 *
 * @param int $litter_id
 * @return array
 */
function truepaws_get_litter_stats($litter_id) {
    global $wpdb;

    $litter_id = absint($litter_id);

    $stats = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT
                puppy_count_male,
                puppy_count_female,
                actual_whelping_date,
                (SELECT COUNT(*) FROM {$wpdb->prefix}bm_animals WHERE sire_id = L.sire_id AND dam_id = L.dam_id AND birth_date = L.actual_whelping_date) as puppies_created
             FROM {$wpdb->prefix}bm_litters L
             WHERE L.id = %d",
            $litter_id
        ),
        ARRAY_A
    );

    if (!$stats) {
        return null;
    }

    $stats['total_expected'] = $stats['puppy_count_male'] + $stats['puppy_count_female'];
    $stats['total_created'] = intval($stats['puppies_created']);

    return $stats;
}

/**
 * Check if whelping date is within normal range
 *
 * @param string $expected_date
 * @param string $actual_date
 * @return bool
 */
function truepaws_is_normal_whelping_date($expected_date, $actual_date) {
    if (empty($expected_date) || empty($actual_date)) {
        return false;
    }

    try {
        $expected = new DateTime($expected_date);
        $actual = new DateTime($actual_date);

        $interval = $expected->diff($actual);

        // Consider normal if within 7 days either side
        return $interval->days <= 7;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Get heat cycle predictions
 *
 * @param string $last_heat_date
 * @param int $cycle_days
 * @return array
 */
function truepaws_predict_heat_cycles($last_heat_date, $cycle_days = 180) {
    if (empty($last_heat_date)) {
        return array();
    }

    try {
        $predictions = array();
        $date = new DateTime($last_heat_date);

        // Predict next 3 heat cycles
        for ($i = 1; $i <= 3; $i++) {
            $date->modify("+{$cycle_days} days");
            $predictions[] = array(
                'date' => $date->format('Y-m-d'),
                'cycle_number' => $i
            );
        }

        return $predictions;
    } catch (Exception $e) {
        return array();
    }
}