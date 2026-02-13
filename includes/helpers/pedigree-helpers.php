<?php
/**
 * Helper functions for pedigree calculations
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get pedigree data for an animal
 *
 * @param int $animal_id
 * @param int $generations
 * @return array|false
 */
function truepaws_get_pedigree($animal_id, $generations = 3) {
    global $wpdb;

    $animal_id = absint($animal_id);
    $generations = min(absint($generations), 5); // Max 5 generations for performance

    if (!$animal_id) {
        return false;
    }

    return _truepaws_build_pedigree_tree($animal_id, $generations);
}

/**
 * Recursively build pedigree tree
 *
 * @param int $animal_id
 * @param int $max_generations
 * @param int $current_generation
 * @return array
 */
function _truepaws_build_pedigree_tree($animal_id, $max_generations, $current_generation = 0) {
    global $wpdb;

    if ($current_generation >= $max_generations) {
        return null;
    }

    $table_animals = $wpdb->prefix . 'bm_animals';

    $animal = $wpdb->get_row($wpdb->prepare(
        "SELECT id, name, call_name, registration_number, breed, sex, sire_id, dam_id, birth_date
         FROM $table_animals
         WHERE id = %d",
        $animal_id
    ), ARRAY_A);

    if (!$animal) {
        return null;
    }

    $pedigree = array(
        'id' => (int) $animal['id'],
        'name' => $animal['name'],
        'call_name' => $animal['call_name'],
        'registration_number' => $animal['registration_number'],
        'breed' => $animal['breed'],
        'sex' => $animal['sex'],
        'birth_date' => $animal['birth_date'],
        'sire' => null,
        'dam' => null,
    );

    // Get sire pedigree
    if (!empty($animal['sire_id'])) {
        $pedigree['sire'] = _truepaws_build_pedigree_tree($animal['sire_id'], $max_generations, $current_generation + 1);
    }

    // Get dam pedigree
    if (!empty($animal['dam_id'])) {
        $pedigree['dam'] = _truepaws_build_pedigree_tree($animal['dam_id'], $max_generations, $current_generation + 1);
    }

    return $pedigree;
}

/**
 * Get ancestors for an animal (flat list)
 *
 * @param int $animal_id
 * @param int $generations
 * @return array
 */
function truepaws_get_ancestors($animal_id, $generations = 3) {
    $pedigree = truepaws_get_pedigree($animal_id, $generations);
    if (!$pedigree) {
        return array();
    }

    $ancestors = array();
    _truepaws_collect_ancestors($pedigree, $ancestors);

    return $ancestors;
}

/**
 * Recursively collect ancestors from pedigree tree
 *
 * @param array $pedigree
 * @param array &$ancestors
 */
function _truepaws_collect_ancestors($pedigree, &$ancestors) {
    if (!$pedigree) {
        return;
    }

    if ($pedigree['sire']) {
        $ancestors[] = $pedigree['sire'];
        _truepaws_collect_ancestors($pedigree['sire'], $ancestors);
    }

    if ($pedigree['dam']) {
        $ancestors[] = $pedigree['dam'];
        _truepaws_collect_ancestors($pedigree['dam'], $ancestors);
    }
}

/**
 * Check if two animals are related
 *
 * @param int $animal1_id
 * @param int $animal2_id
 * @param int $generations
 * @return bool
 */
function truepaws_animals_are_related($animal1_id, $animal2_id, $generations = 3) {
    $ancestors1 = truepaws_get_ancestors($animal1_id, $generations);
    $ancestors2 = truepaws_get_ancestors($animal2_id, $generations);

    $ancestor_ids1 = wp_list_pluck($ancestors1, 'id');
    $ancestor_ids2 = wp_list_pluck($ancestors2, 'id');

    return !empty(array_intersect($ancestor_ids1, $ancestor_ids2));
}