<?php
/**
 * Pedigree helper functions
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get pedigree tree for an animal
 *
 * @param int $animal_id
 * @param int $depth
 * @return array|null
 */
function truepaws_get_pedigree($animal_id, $depth = 3) {
    global $wpdb;

    $animal_id = absint($animal_id);
    $depth = absint($depth);

    if (!$animal_id || $depth < 1) {
        return null;
    }

    // Check cache first
    $cache_key = 'truepaws_pedigree_' . $animal_id . '_' . $depth;
    $cached = get_transient($cache_key);
    
    if ($cached !== false) {
        return $cached;
    }

    // Get the animal data
    $animal = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT id, name, registration_number, sire_id, dam_id, sex, birth_date
             FROM {$wpdb->prefix}bm_animals
             WHERE id = %d",
            $animal_id
        ),
        ARRAY_A
    );

    if (!$animal) {
        return null;
    }

    $pedigree = array(
        'animal' => $animal,
        'generations' => array()
    );

    // Build generations recursively
    $pedigree['generations'] = truepaws_build_pedigree_generations($animal, $depth, 1);

    // Cache for 1 day (pedigree doesn't change often)
    set_transient($cache_key, $pedigree, DAY_IN_SECONDS);

    return $pedigree;
}

/**
 * Recursively build pedigree generations
 *
 * @param array $animal
 * @param int $max_depth
 * @param int $current_depth
 * @return array
 */
function truepaws_build_pedigree_generations($animal, $max_depth, $current_depth) {
    global $wpdb;

    if ($current_depth > $max_depth) {
        return array();
    }

    $generations = array();

    // Get sire
    if (!empty($animal['sire_id'])) {
        $sire = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT id, name, registration_number, sire_id, dam_id, sex, birth_date
                 FROM {$wpdb->prefix}bm_animals
                 WHERE id = %d",
                $animal['sire_id']
            ),
            ARRAY_A
        );

        if ($sire) {
            $generations['sire'] = $sire;
            $generations['sire']['descendants'] = truepaws_build_pedigree_generations($sire, $max_depth, $current_depth + 1);
        }
    }

    // Get dam
    if (!empty($animal['dam_id'])) {
        $dam = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT id, name, registration_number, sire_id, dam_id, sex, birth_date
                 FROM {$wpdb->prefix}bm_animals
                 WHERE id = %d",
                $animal['dam_id']
            ),
            ARRAY_A
        );

        if ($dam) {
            $generations['dam'] = $dam;
            $generations['dam']['descendants'] = truepaws_build_pedigree_generations($dam, $max_depth, $current_depth + 1);
        }
    }

    return $generations;
}

/**
 * Get simplified pedigree for display (name and registration only)
 *
 * @param int $animal_id
 * @param int $depth
 * @return array|null
 */
function truepaws_get_simple_pedigree($animal_id, $depth = 3) {
    $pedigree = truepaws_get_pedigree($animal_id, $depth);

    if (!$pedigree) {
        return null;
    }

    return truepaws_simplify_pedigree($pedigree);
}

/**
 * Simplify pedigree data for frontend display
 *
 * @param array $pedigree
 * @return array
 */
function truepaws_simplify_pedigree($pedigree) {
    $simplified = array(
        'animal' => array(
            'id' => $pedigree['animal']['id'],
            'name' => $pedigree['animal']['name'],
            'registration_number' => $pedigree['animal']['registration_number']
        ),
        'sire' => null,
        'dam' => null
    );

    if (!empty($pedigree['generations']['sire'])) {
        $simplified['sire'] = truepaws_simplify_pedigree_animal($pedigree['generations']['sire']);
    }

    if (!empty($pedigree['generations']['dam'])) {
        $simplified['dam'] = truepaws_simplify_pedigree_animal($pedigree['generations']['dam']);
    }

    return $simplified;
}

/**
 * Simplify individual animal data for pedigree
 *
 * @param array $animal
 * @return array
 */
function truepaws_simplify_pedigree_animal($animal) {
    return array(
        'id' => $animal['id'],
        'name' => $animal['name'],
        'registration_number' => $animal['registration_number'],
        'sire' => !empty($animal['descendants']['sire']) ? array(
            'id' => $animal['descendants']['sire']['id'],
            'name' => $animal['descendants']['sire']['name'],
            'registration_number' => $animal['descendants']['sire']['registration_number']
        ) : null,
        'dam' => !empty($animal['descendants']['dam']) ? array(
            'id' => $animal['descendants']['dam']['id'],
            'name' => $animal['descendants']['dam']['name'],
            'registration_number' => $animal['descendants']['dam']['registration_number']
        ) : null
    );
}