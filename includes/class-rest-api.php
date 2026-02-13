<?php
/**
 * REST API endpoints for TruePaws
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * TruePaws REST API Class
 */
class TruePaws_REST_API {

    /**
     * Namespace for REST API
     */
    const NAMESPACE = 'truepaws/v1';

    /**
     * Constructor
     */
    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    /**
     * Register REST API routes
     */
    public function register_routes() {
        // Animals endpoints
        register_rest_route(self::NAMESPACE, '/animals', array(
            array(
                'methods' => 'GET',
                'callback' => array($this, 'get_animals'),
                'permission_callback' => array($this, 'check_permissions'),
                'args' => $this->get_animals_args()
            ),
            array(
                'methods' => 'POST',
                'callback' => array($this, 'create_animal'),
                'permission_callback' => array($this, 'check_permissions'),
                'args' => $this->create_animal_args()
            )
        ));

        register_rest_route(self::NAMESPACE, '/animals/(?P<id>\d+)', array(
            array(
                'methods' => 'GET',
                'callback' => array($this, 'get_animal'),
                'permission_callback' => array($this, 'check_permissions')
            ),
            array(
                'methods' => 'PUT',
                'callback' => array($this, 'update_animal'),
                'permission_callback' => array($this, 'check_permissions'),
                'args' => $this->update_animal_args()
            ),
            array(
                'methods' => 'DELETE',
                'callback' => array($this, 'delete_animal'),
                'permission_callback' => array($this, 'check_permissions')
            )
        ));

        register_rest_route(self::NAMESPACE, '/animals/(?P<id>\d+)/timeline', array(
            array(
                'methods' => 'GET',
                'callback' => array($this, 'get_animal_timeline'),
                'permission_callback' => array($this, 'check_permissions'),
                'args' => $this->get_timeline_args()
            )
        ));

        register_rest_route(self::NAMESPACE, '/animals/(?P<id>\d+)/pedigree', array(
            array(
                'methods' => 'GET',
                'callback' => array($this, 'get_animal_pedigree'),
                'permission_callback' => array($this, 'check_permissions'),
                'args' => $this->get_pedigree_args()
            )
        ));

        // Events endpoints
        register_rest_route(self::NAMESPACE, '/animals/(?P<animal_id>\d+)/events', array(
            array(
                'methods' => 'POST',
                'callback' => array($this, 'create_event'),
                'permission_callback' => array($this, 'check_permissions'),
                'args' => $this->create_event_args()
            )
        ));

        register_rest_route(self::NAMESPACE, '/events/(?P<id>\d+)', array(
            array(
                'methods' => 'PUT',
                'callback' => array($this, 'update_event'),
                'permission_callback' => array($this, 'check_permissions'),
                'args' => $this->update_event_args()
            ),
            array(
                'methods' => 'DELETE',
                'callback' => array($this, 'delete_event'),
                'permission_callback' => array($this, 'check_permissions')
            )
        ));

        // Litters endpoints
        register_rest_route(self::NAMESPACE, '/litters', array(
            array(
                'methods' => 'GET',
                'callback' => array($this, 'get_litters'),
                'permission_callback' => array($this, 'check_permissions'),
                'args' => $this->get_litters_args()
            ),
            array(
                'methods' => 'POST',
                'callback' => array($this, 'create_litter'),
                'permission_callback' => array($this, 'check_permissions'),
                'args' => $this->create_litter_args()
            )
        ));

        register_rest_route(self::NAMESPACE, '/litters/(?P<id>\d+)', array(
            array(
                'methods' => 'GET',
                'callback' => array($this, 'get_litter'),
                'permission_callback' => array($this, 'check_permissions')
            ),
            array(
                'methods' => 'PUT',
                'callback' => array($this, 'update_litter'),
                'permission_callback' => array($this, 'check_permissions'),
                'args' => $this->update_litter_args()
            ),
            array(
                'methods' => 'DELETE',
                'callback' => array($this, 'delete_litter'),
                'permission_callback' => array($this, 'check_permissions')
            )
        ));

        register_rest_route(self::NAMESPACE, '/litters/(?P<id>\d+)/whelp', array(
            array(
                'methods' => 'POST',
                'callback' => array($this, 'whelp_litter'),
                'permission_callback' => array($this, 'check_permissions'),
                'args' => $this->whelp_litter_args()
            )
        ));

        // Contacts endpoints
        register_rest_route(self::NAMESPACE, '/contacts', array(
            array(
                'methods' => 'GET',
                'callback' => array($this, 'get_contacts'),
                'permission_callback' => array($this, 'check_permissions'),
                'args' => $this->get_contacts_args()
            ),
            array(
                'methods' => 'POST',
                'callback' => array($this, 'create_contact'),
                'permission_callback' => array($this, 'check_permissions'),
                'args' => $this->create_contact_args()
            )
        ));

        register_rest_route(self::NAMESPACE, '/contacts/(?P<id>\d+)', array(
            array(
                'methods' => 'GET',
                'callback' => array($this, 'get_contact'),
                'permission_callback' => array($this, 'check_permissions')
            ),
            array(
                'methods' => 'PUT',
                'callback' => array($this, 'update_contact'),
                'permission_callback' => array($this, 'check_permissions'),
                'args' => $this->update_contact_args()
            ),
            array(
                'methods' => 'DELETE',
                'callback' => array($this, 'delete_contact'),
                'permission_callback' => array($this, 'check_permissions')
            )
        ));

        // PDF generation endpoint
        register_rest_route(self::NAMESPACE, '/animals/(?P<id>\d+)/generate-handover', array(
            array(
                'methods' => 'POST',
                'callback' => array($this, 'generate_handover'),
                'permission_callback' => array($this, 'check_permissions')
            )
        ));
    }

    /**
     * Check if user has permission to access endpoints
     */
    public function check_permissions($request) {
        return current_user_can('manage_options');
    }

    /**
     * Get animals collection
     */
    public function get_animals($request) {
        global $wpdb;

        $table = $wpdb->prefix . 'bm_animals';

        $page = $request->get_param('page') ?: 1;
        $per_page = $request->get_param('per_page') ?: 20;
        $search = $request->get_param('search');
        $status = $request->get_param('status');
        $breed = $request->get_param('breed');
        $sex = $request->get_param('sex');

        $offset = ($page - 1) * $per_page;

        $where = array('1=1');
        $where_values = array();

        if ($search) {
            $where[] = "(name LIKE %s OR call_name LIKE %s OR registration_number LIKE %s OR microchip_id LIKE %s)";
            $search_term = '%' . $wpdb->esc_like($search) . '%';
            $where_values = array_merge($where_values, array($search_term, $search_term, $search_term, $search_term));
        }

        if ($status) {
            $where[] = "status = %s";
            $where_values[] = $status;
        }

        if ($breed) {
            $where[] = "breed = %s";
            $where_values[] = $breed;
        }

        if ($sex) {
            $where[] = "sex = %s";
            $where_values[] = $sex;
        }

        $where_clause = implode(' AND ', $where);

        // Get total count
        $count_query = "SELECT COUNT(*) FROM $table WHERE $where_clause";
        $total = $wpdb->get_var($wpdb->prepare($count_query, $where_values));

        // Get animals
        $query = "SELECT * FROM $table WHERE $where_clause ORDER BY name ASC LIMIT %d OFFSET %d";
        $values = array_merge($where_values, array($per_page, $offset));

        $animals = $wpdb->get_results($wpdb->prepare($query, $values), ARRAY_A);

        // Add featured image URLs
        foreach ($animals as &$animal) {
            $animal['id'] = (int) $animal['id'];
            $animal['sire_id'] = $animal['sire_id'] ? (int) $animal['sire_id'] : null;
            $animal['dam_id'] = $animal['dam_id'] ? (int) $animal['dam_id'] : null;
            $animal['featured_image_id'] = $animal['featured_image_id'] ? (int) $animal['featured_image_id'] : null;

            if ($animal['featured_image_id']) {
                $animal['featured_image_url'] = wp_get_attachment_image_url($animal['featured_image_id'], 'medium');
            }
        }

        $response = array(
            'animals' => $animals,
            'total' => (int) $total,
            'page' => (int) $page,
            'per_page' => (int) $per_page,
            'total_pages' => ceil($total / $per_page)
        );

        return new WP_REST_Response($response, 200);
    }

    /**
     * Get single animal
     */
    public function get_animal($request) {
        global $wpdb;

        $id = $request->get_param('id');
        $table = $wpdb->prefix . 'bm_animals';

        $animal = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $id
        ), ARRAY_A);

        if (!$animal) {
            return new WP_Error('animal_not_found', __('Animal not found.', TRUEPAWS_TEXT_DOMAIN), array('status' => 404));
        }

        // Format data
        $animal['id'] = (int) $animal['id'];
        $animal['sire_id'] = $animal['sire_id'] ? (int) $animal['sire_id'] : null;
        $animal['dam_id'] = $animal['dam_id'] ? (int) $animal['dam_id'] : null;
        $animal['featured_image_id'] = $animal['featured_image_id'] ? (int) $animal['featured_image_id'] : null;

        // Add parent names
        if ($animal['sire_id']) {
            $animal['sire_name'] = $wpdb->get_var($wpdb->prepare(
                "SELECT name FROM $table WHERE id = %d",
                $animal['sire_id']
            ));
        }

        if ($animal['dam_id']) {
            $animal['dam_name'] = $wpdb->get_var($wpdb->prepare(
                "SELECT name FROM $table WHERE id = %d",
                $animal['dam_id']
            ));
        }

        // Add featured image URL
        if ($animal['featured_image_id']) {
            $animal['featured_image_url'] = wp_get_attachment_image_url($animal['featured_image_id'], 'large');
        }

        return new WP_REST_Response($animal, 200);
    }

    /**
     * Create animal
     */
    public function create_animal($request) {
        global $wpdb;

        $table = $wpdb->prefix . 'bm_animals';

        $data = array(
            'name' => sanitize_text_field($request->get_param('name')),
            'call_name' => sanitize_text_field($request->get_param('call_name')),
            'registration_number' => sanitize_text_field($request->get_param('registration_number')),
            'microchip_id' => sanitize_text_field($request->get_param('microchip_id')),
            'breed' => sanitize_text_field($request->get_param('breed')),
            'color_markings' => sanitize_textarea_field($request->get_param('color_markings')),
            'sex' => $request->get_param('sex'),
            'sire_id' => $request->get_param('sire_id') ? absint($request->get_param('sire_id')) : null,
            'dam_id' => $request->get_param('dam_id') ? absint($request->get_param('dam_id')) : null,
            'birth_date' => $request->get_param('birth_date'),
            'featured_image_id' => $request->get_param('featured_image_id') ? absint($request->get_param('featured_image_id')) : null,
            'status' => $request->get_param('status') ?: 'active'
        );

        // Validate required fields
        if (empty($data['name']) || empty($data['sex'])) {
            return new WP_Error('missing_required_fields', __('Name and sex are required.', TRUEPAWS_TEXT_DOMAIN), array('status' => 400));
        }

        // Validate sex
        if (!in_array($data['sex'], array('M', 'F'))) {
            return new WP_Error('invalid_sex', __('Sex must be M or F.', TRUEPAWS_TEXT_DOMAIN), array('status' => 400));
        }

        // Check for duplicate microchip
        if (!empty($data['microchip_id'])) {
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $table WHERE microchip_id = %s",
                $data['microchip_id']
            ));
            if ($existing) {
                return new WP_Error('duplicate_microchip', __('Microchip ID already exists.', TRUEPAWS_TEXT_DOMAIN), array('status' => 400));
            }
        }

        $result = $wpdb->insert($table, $data);

        if ($result === false) {
            return new WP_Error('db_error', __('Failed to create animal.', TRUEPAWS_TEXT_DOMAIN), array('status' => 500));
        }

        $animal_id = $wpdb->insert_id;

        // Create birth event if birth date is provided
        if (!empty($data['birth_date'])) {
            $this->create_birth_event($animal_id, $data['birth_date']);
        }

        $data['id'] = $animal_id;
        return new WP_REST_Response($data, 201);
    }

    /**
     * Update animal
     */
    public function update_animal($request) {
        global $wpdb;

        $id = $request->get_param('id');
        $table = $wpdb->prefix . 'bm_animals';

        // Check if animal exists
        $existing = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE id = %d", $id));
        if (!$existing) {
            return new WP_Error('animal_not_found', __('Animal not found.', TRUEPAWS_TEXT_DOMAIN), array('status' => 404));
        }

        $data = array();

        // Only include fields that were provided
        $fields = array('name', 'call_name', 'registration_number', 'microchip_id', 'breed', 'color_markings', 'sex', 'sire_id', 'dam_id', 'birth_date', 'status', 'featured_image_id');

        foreach ($fields as $field) {
            $value = $request->get_param($field);
            if ($value !== null) {
                if (in_array($field, array('sire_id', 'dam_id', 'featured_image_id'))) {
                    $data[$field] = $value ? absint($value) : null;
                } elseif ($field === 'microchip_id') {
                    $data[$field] = sanitize_text_field($value);
                    // Check for duplicate microchip (excluding current animal)
                    if (!empty($data[$field])) {
                        $duplicate = $wpdb->get_var($wpdb->prepare(
                            "SELECT id FROM $table WHERE microchip_id = %s AND id != %d",
                            $data[$field], $id
                        ));
                        if ($duplicate) {
                            return new WP_Error('duplicate_microchip', __('Microchip ID already exists.', TRUEPAWS_TEXT_DOMAIN), array('status' => 400));
                        }
                    }
                } else {
                    $data[$field] = sanitize_text_field($value);
                }
            }
        }

        // Validate sex if provided
        if (isset($data['sex']) && !in_array($data['sex'], array('M', 'F'))) {
            return new WP_Error('invalid_sex', __('Sex must be M or F.', TRUEPAWS_TEXT_DOMAIN), array('status' => 400));
        }

        if (empty($data)) {
            return new WP_Error('no_data', __('No data provided for update.', TRUEPAWS_TEXT_DOMAIN), array('status' => 400));
        }

        $data['updated_at'] = current_time('mysql');

        $result = $wpdb->update($table, $data, array('id' => $id));

        if ($result === false) {
            return new WP_Error('db_error', __('Failed to update animal.', TRUEPAWS_TEXT_DOMAIN), array('status' => 500));
        }

        return new WP_REST_Response(array('id' => $id, 'updated' => true), 200);
    }

    /**
     * Delete animal
     */
    public function delete_animal($request) {
        global $wpdb;

        $id = $request->get_param('id');
        $table = $wpdb->prefix . 'bm_animals';

        // Check if animal exists
        $existing = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE id = %d", $id));
        if (!$existing) {
            return new WP_Error('animal_not_found', __('Animal not found.', TRUEPAWS_TEXT_DOMAIN), array('status' => 404));
        }

        // Check if animal is referenced as parent
        $is_parent = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE sire_id = %d OR dam_id = %d",
            $id, $id
        ));

        if ($is_parent > 0) {
            return new WP_Error('animal_has_offspring', __('Cannot delete animal that has offspring.', TRUEPAWS_TEXT_DOMAIN), array('status' => 400));
        }

        $result = $wpdb->delete($table, array('id' => $id));

        if ($result === false) {
            return new WP_Error('db_error', __('Failed to delete animal.', TRUEPAWS_TEXT_DOMAIN), array('status' => 500));
        }

        return new WP_REST_Response(array('deleted' => true), 200);
    }

    /**
     * Get arguments for animals collection
     */
    private function get_animals_args() {
        return array(
            'page' => array(
                'description' => __('Current page of the collection.', TRUEPAWS_TEXT_DOMAIN),
                'type' => 'integer',
                'default' => 1,
                'sanitize_callback' => 'absint',
            ),
            'per_page' => array(
                'description' => __('Maximum number of items to be returned in result set.', TRUEPAWS_TEXT_DOMAIN),
                'type' => 'integer',
                'default' => 20,
                'sanitize_callback' => 'absint',
            ),
            'search' => array(
                'description' => __('Limit results to those matching a string.', TRUEPAWS_TEXT_DOMAIN),
                'type' => 'string',
            ),
            'status' => array(
                'description' => __('Limit results to animals with specific status.', TRUEPAWS_TEXT_DOMAIN),
                'type' => 'string',
                'enum' => array('active', 'retired', 'sold', 'deceased', 'co-owned'),
            ),
        );
    }

    /**
     * Get arguments for creating animal
     */
    private function create_animal_args() {
        return array(
            'name' => array(
                'required' => true,
                'description' => __('Animal name.', TRUEPAWS_TEXT_DOMAIN),
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'sex' => array(
                'required' => true,
                'description' => __('Animal sex.', TRUEPAWS_TEXT_DOMAIN),
                'type' => 'string',
                'enum' => array('M', 'F'),
            ),
            // Additional args to be added in Phase 2
        );
    }

    /**
     * Get animal timeline
     */
    public function get_animal_timeline($request) {
        global $wpdb;

        $animal_id = $request->get_param('id');
        $table_events = $wpdb->prefix . 'bm_events';

        $events = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_events WHERE animal_id = %d ORDER BY event_date DESC, created_at DESC",
            $animal_id
        ), ARRAY_A);

        // Format events
        foreach ($events as &$event) {
            $event['id'] = (int) $event['id'];
            $event['animal_id'] = (int) $event['animal_id'];
            $event['created_by'] = (int) $event['created_by'];
            $event['meta_data'] = json_decode($event['meta_data'], true);
        }

        return new WP_REST_Response(array('events' => $events), 200);
    }

    /**
     * Get animal pedigree
     */
    public function get_animal_pedigree($request) {
        $animal_id = $request->get_param('id');
        $generations = $request->get_param('generations') ?: 3;

        $pedigree = truepaws_get_pedigree($animal_id, $generations);

        if (!$pedigree) {
            return new WP_Error('pedigree_not_found', __('Pedigree not found.', TRUEPAWS_TEXT_DOMAIN), array('status' => 404));
        }

        return new WP_REST_Response($pedigree, 200);
    }

    /**
     * Create event
     */
    public function create_event($request) {
        global $wpdb;

        $animal_id = $request->get_param('animal_id');
        $table_animals = $wpdb->prefix . 'bm_animals';
        $table_events = $wpdb->prefix . 'bm_events';

        // Check if animal exists
        $animal_exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_animals WHERE id = %d", $animal_id));
        if (!$animal_exists) {
            return new WP_Error('animal_not_found', __('Animal not found.', TRUEPAWS_TEXT_DOMAIN), array('status' => 404));
        }

        $data = array(
            'animal_id' => $animal_id,
            'event_type' => $request->get_param('event_type'),
            'event_date' => $request->get_param('event_date'),
            'title' => sanitize_text_field($request->get_param('title')),
            'meta_data' => wp_json_encode($request->get_param('meta_data') ?: array()),
            'created_by' => get_current_user_id()
        );

        // Validate required fields
        if (empty($data['event_type']) || empty($data['event_date'])) {
            return new WP_Error('missing_required_fields', __('Event type and date are required.', TRUEPAWS_TEXT_DOMAIN), array('status' => 400));
        }

        // Validate event type
        $valid_types = array('birth', 'vaccine', 'heat', 'mating', 'whelping', 'weight', 'vet_visit', 'note');
        if (!in_array($data['event_type'], $valid_types)) {
            return new WP_Error('invalid_event_type', __('Invalid event type.', TRUEPAWS_TEXT_DOMAIN), array('status' => 400));
        }

        $result = $wpdb->insert($table_events, $data);

        if ($result === false) {
            return new WP_Error('db_error', __('Failed to create event.', TRUEPAWS_TEXT_DOMAIN), array('status' => 500));
        }

        $event_id = $wpdb->insert_id;
        $data['id'] = $event_id;

        return new WP_REST_Response($data, 201);
    }

    /**
     * Update event
     */
    public function update_event($request) {
        global $wpdb;

        $id = $request->get_param('id');
        $table_events = $wpdb->prefix . 'bm_events';

        // Check if event exists
        $existing = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_events WHERE id = %d", $id));
        if (!$existing) {
            return new WP_Error('event_not_found', __('Event not found.', TRUEPAWS_TEXT_DOMAIN), array('status' => 404));
        }

        $data = array();

        $fields = array('event_type', 'event_date', 'title', 'meta_data');
        foreach ($fields as $field) {
            $value = $request->get_param($field);
            if ($value !== null) {
                if ($field === 'meta_data') {
                    $data[$field] = wp_json_encode($value);
                } else {
                    $data[$field] = sanitize_text_field($value);
                }
            }
        }

        if (empty($data)) {
            return new WP_Error('no_data', __('No data provided for update.', TRUEPAWS_TEXT_DOMAIN), array('status' => 400));
        }

        $result = $wpdb->update($table_events, $data, array('id' => $id));

        if ($result === false) {
            return new WP_Error('db_error', __('Failed to update event.', TRUEPAWS_TEXT_DOMAIN), array('status' => 500));
        }

        return new WP_REST_Response(array('id' => $id, 'updated' => true), 200);
    }

    /**
     * Delete event
     */
    public function delete_event($request) {
        global $wpdb;

        $id = $request->get_param('id');
        $table_events = $wpdb->prefix . 'bm_events';

        // Check if event exists
        $existing = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_events WHERE id = %d", $id));
        if (!$existing) {
            return new WP_Error('event_not_found', __('Event not found.', TRUEPAWS_TEXT_DOMAIN), array('status' => 404));
        }

        $result = $wpdb->delete($table_events, array('id' => $id));

        if ($result === false) {
            return new WP_Error('db_error', __('Failed to delete event.', TRUEPAWS_TEXT_DOMAIN), array('status' => 500));
        }

        return new WP_REST_Response(array('deleted' => true), 200);
    }

    /**
     * Get litters collection
     */
    public function get_litters($request) {
        global $wpdb;

        $table = $wpdb->prefix . 'bm_litters';
        $table_animals = $wpdb->prefix . 'bm_animals';

        $page = $request->get_param('page') ?: 1;
        $per_page = $request->get_param('per_page') ?: 20;
        $offset = ($page - 1) * $per_page;

        // Get total count
        $total = $wpdb->get_var("SELECT COUNT(*) FROM $table");

        // Get litters with parent names
        $query = "SELECT l.*, 
                         d.name as dam_name, s.name as sire_name
                  FROM $table l
                  LEFT JOIN $table_animals d ON l.dam_id = d.id
                  LEFT JOIN $table_animals s ON l.sire_id = s.id
                  ORDER BY l.created_at DESC
                  LIMIT %d OFFSET %d";

        $litters = $wpdb->get_results($wpdb->prepare($query, array($per_page, $offset)), ARRAY_A);

        // Format data
        foreach ($litters as &$litter) {
            $litter['id'] = (int) $litter['id'];
            $litter['dam_id'] = (int) $litter['dam_id'];
            $litter['sire_id'] = (int) $litter['sire_id'];
            $litter['puppy_count_male'] = (int) $litter['puppy_count_male'];
            $litter['puppy_count_female'] = (int) $litter['puppy_count_female'];
        }

        $response = array(
            'litters' => $litters,
            'total' => (int) $total,
            'page' => (int) $page,
            'per_page' => (int) $per_page,
            'total_pages' => ceil($total / $per_page)
        );

        return new WP_REST_Response($response, 200);
    }

    /**
     * Get single litter
     */
    public function get_litter($request) {
        global $wpdb;

        $id = $request->get_param('id');
        $table = $wpdb->prefix . 'bm_litters';
        $table_animals = $wpdb->prefix . 'bm_animals';

        $litter = $wpdb->get_row($wpdb->prepare(
            "SELECT l.*, d.name as dam_name, s.name as sire_name
             FROM $table l
             LEFT JOIN $table_animals d ON l.dam_id = d.id
             LEFT JOIN $table_animals s ON l.sire_id = s.id
             WHERE l.id = %d",
            $id
        ), ARRAY_A);

        if (!$litter) {
            return new WP_Error('litter_not_found', __('Litter not found.', TRUEPAWS_TEXT_DOMAIN), array('status' => 404));
        }

        // Format data
        $litter['id'] = (int) $litter['id'];
        $litter['dam_id'] = (int) $litter['dam_id'];
        $litter['sire_id'] = (int) $litter['sire_id'];
        $litter['puppy_count_male'] = (int) $litter['puppy_count_male'];
        $litter['puppy_count_female'] = (int) $litter['puppy_count_female'];

        return new WP_REST_Response($litter, 200);
    }

    /**
     * Create litter
     */
    public function create_litter($request) {
        global $wpdb;

        $table = $wpdb->prefix . 'bm_litters';
        $table_animals = $wpdb->prefix . 'bm_animals';

        $dam_id = absint($request->get_param('dam_id'));
        $sire_id = absint($request->get_param('sire_id'));

        // Validate parents exist and are correct sex
        $dam = $wpdb->get_row($wpdb->prepare("SELECT sex FROM $table_animals WHERE id = %d", $dam_id), ARRAY_A);
        $sire = $wpdb->get_row($wpdb->prepare("SELECT sex FROM $table_animals WHERE id = %d", $sire_id), ARRAY_A);

        if (!$dam || $dam['sex'] !== 'F') {
            return new WP_Error('invalid_dam', __('Dam must be a female animal.', TRUEPAWS_TEXT_DOMAIN), array('status' => 400));
        }

        if (!$sire || $sire['sex'] !== 'M') {
            return new WP_Error('invalid_sire', __('Sire must be a male animal.', TRUEPAWS_TEXT_DOMAIN), array('status' => 400));
        }

        $data = array(
            'litter_name' => sanitize_text_field($request->get_param('litter_name')) ?: truepaws_generate_litter_name($sire_id, $dam_id, $request->get_param('mating_date')),
            'dam_id' => $dam_id,
            'sire_id' => $sire_id,
            'mating_date' => $request->get_param('mating_date'),
            'mating_method' => $request->get_param('mating_method') ?: 'natural',
            'expected_whelping_date' => truepaws_calculate_whelping_date($request->get_param('mating_date')),
            'notes' => sanitize_textarea_field($request->get_param('notes'))
        );

        $result = $wpdb->insert($table, $data);

        if ($result === false) {
            return new WP_Error('db_error', __('Failed to create litter.', TRUEPAWS_TEXT_DOMAIN), array('status' => 500));
        }

        $litter_id = $wpdb->insert_id;
        $data['id'] = $litter_id;

        return new WP_REST_Response($data, 201);
    }

    /**
     * Update litter
     */
    public function update_litter($request) {
        global $wpdb;

        $id = $request->get_param('id');
        $table = $wpdb->prefix . 'bm_litters';

        // Check if litter exists
        $existing = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE id = %d", $id));
        if (!$existing) {
            return new WP_Error('litter_not_found', __('Litter not found.', TRUEPAWS_TEXT_DOMAIN), array('status' => 404));
        }

        $data = array();

        $fields = array('litter_name', 'mating_date', 'mating_method', 'actual_whelping_date', 'puppy_count_male', 'puppy_count_female', 'notes');
        foreach ($fields as $field) {
            $value = $request->get_param($field);
            if ($value !== null) {
                if (in_array($field, array('puppy_count_male', 'puppy_count_female'))) {
                    $data[$field] = absint($value);
                } elseif ($field === 'mating_method') {
                    $data[$field] = in_array($value, array('natural', 'ai')) ? $value : 'natural';
                } else {
                    $data[$field] = sanitize_text_field($value);
                }
            }
        }

        // Recalculate expected whelping date if mating date changed
        if (isset($data['mating_date'])) {
            $data['expected_whelping_date'] = truepaws_calculate_whelping_date($data['mating_date']);
        }

        if (empty($data)) {
            return new WP_Error('no_data', __('No data provided for update.', TRUEPAWS_TEXT_DOMAIN), array('status' => 400));
        }

        $result = $wpdb->update($table, $data, array('id' => $id));

        if ($result === false) {
            return new WP_Error('db_error', __('Failed to update litter.', TRUEPAWS_TEXT_DOMAIN), array('status' => 500));
        }

        return new WP_REST_Response(array('id' => $id, 'updated' => true), 200);
    }

    /**
     * Delete litter
     */
    public function delete_litter($request) {
        global $wpdb;

        $id = $request->get_param('id');
        $table = $wpdb->prefix . 'bm_litters';

        // Check if litter exists
        $existing = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE id = %d", $id));
        if (!$existing) {
            return new WP_Error('litter_not_found', __('Litter not found.', TRUEPAWS_TEXT_DOMAIN), array('status' => 404));
        }

        $result = $wpdb->delete($table, array('id' => $id));

        if ($result === false) {
            return new WP_Error('db_error', __('Failed to delete litter.', TRUEPAWS_TEXT_DOMAIN), array('status' => 500));
        }

        return new WP_REST_Response(array('deleted' => true), 200);
    }

    /**
     * Whelp litter (create puppies)
     */
    public function whelp_litter($request) {
        global $wpdb;

        $litter_id = $request->get_param('id');
        $table_litters = $wpdb->prefix . 'bm_litters';
        $table_animals = $wpdb->prefix . 'bm_animals';
        $table_events = $wpdb->prefix . 'bm_events';

        // Get litter data
        $litter = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_litters WHERE id = %d", $litter_id), ARRAY_A);
        if (!$litter) {
            return new WP_Error('litter_not_found', __('Litter not found.', TRUEPAWS_TEXT_DOMAIN), array('status' => 404));
        }

        $actual_date = $request->get_param('actual_date');
        $male_count = absint($request->get_param('male_count'));
        $female_count = absint($request->get_param('female_count'));

        if (empty($actual_date)) {
            return new WP_Error('missing_date', __('Actual whelping date is required.', TRUEPAWS_TEXT_DOMAIN), array('status' => 400));
        }

        // Update litter record
        $litter_update = array(
            'actual_whelping_date' => $actual_date,
            'puppy_count_male' => $male_count,
            'puppy_count_female' => $female_count
        );

        $wpdb->update($table_litters, $litter_update, array('id' => $litter_id));

        $created_animals = array();

        // Create male puppies
        for ($i = 1; $i <= $male_count; $i++) {
            $puppy_name = 'Puppy ' . $i . ' (' . $litter['litter_name'] . ')';
            $animal_data = array(
                'name' => $puppy_name,
                'sex' => 'M',
                'sire_id' => $litter['sire_id'],
                'dam_id' => $litter['dam_id'],
                'birth_date' => $actual_date,
                'status' => 'active'
            );

            $wpdb->insert($table_animals, $animal_data);
            $animal_id = $wpdb->insert_id;
            $created_animals[] = $animal_id;

            // Create birth event
            $this->create_birth_event($animal_id, $actual_date);
        }

        // Create female puppies
        for ($i = 1; $i <= $female_count; $i++) {
            $puppy_number = $male_count + $i;
            $puppy_name = 'Puppy ' . $puppy_number . ' (' . $litter['litter_name'] . ')';
            $animal_data = array(
                'name' => $puppy_name,
                'sex' => 'F',
                'sire_id' => $litter['sire_id'],
                'dam_id' => $litter['dam_id'],
                'birth_date' => $actual_date,
                'status' => 'active'
            );

            $wpdb->insert($table_animals, $animal_data);
            $animal_id = $wpdb->insert_id;
            $created_animals[] = $animal_id;

            // Create birth event
            $this->create_birth_event($animal_id, $actual_date);
        }

        return new WP_REST_Response(array(
            'litter_id' => $litter_id,
            'created_animals' => $created_animals,
            'total_created' => count($created_animals)
        ), 201);
    }

    /**
     * Get contacts collection
     */
    public function get_contacts($request) {
        global $wpdb;

        $table = $wpdb->prefix . 'bm_contacts';

        $page = $request->get_param('page') ?: 1;
        $per_page = $request->get_param('per_page') ?: 20;
        $search = $request->get_param('search');
        $status = $request->get_param('status');

        $offset = ($page - 1) * $per_page;

        $where = array('1=1');
        $where_values = array();

        if ($search) {
            $where[] = "(first_name LIKE %s OR last_name LIKE %s OR email LIKE %s)";
            $search_term = '%' . $wpdb->esc_like($search) . '%';
            $where_values = array_merge($where_values, array($search_term, $search_term, $search_term));
        }

        if ($status) {
            $where[] = "status = %s";
            $where_values[] = $status;
        }

        $where_clause = implode(' AND ', $where);

        // Get total count
        $count_query = "SELECT COUNT(*) FROM $table WHERE $where_clause";
        $total = $wpdb->get_var($wpdb->prepare($count_query, $where_values));

        // Get contacts
        $query = "SELECT * FROM $table WHERE $where_clause ORDER BY last_name ASC, first_name ASC LIMIT %d OFFSET %d";
        $values = array_merge($where_values, array($per_page, $offset));

        $contacts = $wpdb->get_results($wpdb->prepare($query, $values), ARRAY_A);

        // Format data
        foreach ($contacts as &$contact) {
            $contact['id'] = (int) $contact['id'];
        }

        $response = array(
            'contacts' => $contacts,
            'total' => (int) $total,
            'page' => (int) $page,
            'per_page' => (int) $per_page,
            'total_pages' => ceil($total / $per_page)
        );

        return new WP_REST_Response($response, 200);
    }

    /**
     * Get single contact
     */
    public function get_contact($request) {
        global $wpdb;

        $id = $request->get_param('id');
        $table = $wpdb->prefix . 'bm_contacts';

        $contact = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $id
        ), ARRAY_A);

        if (!$contact) {
            return new WP_Error('contact_not_found', __('Contact not found.', TRUEPAWS_TEXT_DOMAIN), array('status' => 404));
        }

        $contact['id'] = (int) $contact['id'];

        return new WP_REST_Response($contact, 200);
    }

    /**
     * Create contact
     */
    public function create_contact($request) {
        global $wpdb;

        $table = $wpdb->prefix . 'bm_contacts';

        $data = array(
            'first_name' => sanitize_text_field($request->get_param('first_name')),
            'last_name' => sanitize_text_field($request->get_param('last_name')),
            'email' => sanitize_email($request->get_param('email')),
            'phone' => sanitize_text_field($request->get_param('phone')),
            'address' => sanitize_textarea_field($request->get_param('address')),
            'notes' => sanitize_textarea_field($request->get_param('notes')),
            'status' => $request->get_param('status') ?: 'waitlist'
        );

        // Validate required fields
        if (empty($data['first_name']) || empty($data['email'])) {
            return new WP_Error('missing_required_fields', __('First name and email are required.', TRUEPAWS_TEXT_DOMAIN), array('status' => 400));
        }

        // Validate email
        if (!is_email($data['email'])) {
            return new WP_Error('invalid_email', __('Invalid email address.', TRUEPAWS_TEXT_DOMAIN), array('status' => 400));
        }

        // Check for duplicate email
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table WHERE email = %s",
            $data['email']
        ));
        if ($existing) {
            return new WP_Error('duplicate_email', __('Email address already exists.', TRUEPAWS_TEXT_DOMAIN), array('status' => 400));
        }

        $result = $wpdb->insert($table, $data);

        if ($result === false) {
            return new WP_Error('db_error', __('Failed to create contact.', TRUEPAWS_TEXT_DOMAIN), array('status' => 500));
        }

        $contact_id = $wpdb->insert_id;
        $data['id'] = $contact_id;

        return new WP_REST_Response($data, 201);
    }

    /**
     * Update contact
     */
    public function update_contact($request) {
        global $wpdb;

        $id = $request->get_param('id');
        $table = $wpdb->prefix . 'bm_contacts';

        // Check if contact exists
        $existing = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE id = %d", $id));
        if (!$existing) {
            return new WP_Error('contact_not_found', __('Contact not found.', TRUEPAWS_TEXT_DOMAIN), array('status' => 404));
        }

        $data = array();

        $fields = array('first_name', 'last_name', 'email', 'phone', 'address', 'notes', 'status');
        foreach ($fields as $field) {
            $value = $request->get_param($field);
            if ($value !== null) {
                if ($field === 'email') {
                    $data[$field] = sanitize_email($value);
                    // Check for duplicate email (excluding current contact)
                    if (!empty($data[$field])) {
                        $duplicate = $wpdb->get_var($wpdb->prepare(
                            "SELECT id FROM $table WHERE email = %s AND id != %d",
                            $data[$field], $id
                        ));
                        if ($duplicate) {
                            return new WP_Error('duplicate_email', __('Email address already exists.', TRUEPAWS_TEXT_DOMAIN), array('status' => 400));
                        }
                    }
                } else {
                    $data[$field] = sanitize_text_field($value);
                }
            }
        }

        // Validate email if provided
        if (isset($data['email']) && !is_email($data['email'])) {
            return new WP_Error('invalid_email', __('Invalid email address.', TRUEPAWS_TEXT_DOMAIN), array('status' => 400));
        }

        if (empty($data)) {
            return new WP_Error('no_data', __('No data provided for update.', TRUEPAWS_TEXT_DOMAIN), array('status' => 400));
        }

        $data['updated_at'] = current_time('mysql');

        $result = $wpdb->update($table, $data, array('id' => $id));

        if ($result === false) {
            return new WP_Error('db_error', __('Failed to update contact.', TRUEPAWS_TEXT_DOMAIN), array('status' => 500));
        }

        return new WP_REST_Response(array('id' => $id, 'updated' => true), 200);
    }

    /**
     * Delete contact
     */
    public function delete_contact($request) {
        global $wpdb;

        $id = $request->get_param('id');
        $table = $wpdb->prefix . 'bm_contacts';

        // Check if contact exists
        $existing = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE id = %d", $id));
        if (!$existing) {
            return new WP_Error('contact_not_found', __('Contact not found.', TRUEPAWS_TEXT_DOMAIN), array('status' => 404));
        }

        $result = $wpdb->delete($table, array('id' => $id));

        if ($result === false) {
            return new WP_Error('db_error', __('Failed to delete contact.', TRUEPAWS_TEXT_DOMAIN), array('status' => 500));
        }

        return new WP_REST_Response(array('deleted' => true), 200);
    }

    /**
     * Generate handover PDF
     */
    public function generate_handover($request) {
        $animal_id = $request->get_param('id');

        $pdf_generator = new TruePaws_PDF_Generator();
        $pdf_path = $pdf_generator->generate_handover_pdf($animal_id);

        if (!$pdf_path) {
            return new WP_Error('pdf_generation_failed', __('Failed to generate handover PDF.', TRUEPAWS_TEXT_DOMAIN), array('status' => 500));
        }

        $pdf_url = str_replace(WP_CONTENT_DIR, content_url(), $pdf_path);

        return new WP_REST_Response(array(
            'pdf_url' => $pdf_url,
            'filename' => basename($pdf_path)
        ), 200);
    }

    /**
     * Create birth event helper
     */
    private function create_birth_event($animal_id, $birth_date) {
        global $wpdb;

        $table_events = $wpdb->prefix . 'bm_events';

        $event_data = array(
            'animal_id' => $animal_id,
            'event_type' => 'birth',
            'event_date' => $birth_date,
            'title' => __('Birth', TRUEPAWS_TEXT_DOMAIN),
            'meta_data' => wp_json_encode(array()),
            'created_by' => get_current_user_id()
        );

        $wpdb->insert($table_events, $event_data);
    }

    /**
     * Get arguments for updating animal
     */
    private function update_animal_args() {
        return array_merge($this->create_animal_args(), array(
            'status' => array(
                'description' => __('Animal status.', TRUEPAWS_TEXT_DOMAIN),
                'type' => 'string',
                'enum' => array('active', 'retired', 'sold', 'deceased', 'co-owned'),
            ),
        ));
    }

    /**
     * Get arguments for timeline
     */
    private function get_timeline_args() {
        return array(
            'limit' => array(
                'description' => __('Maximum number of events to return.', TRUEPAWS_TEXT_DOMAIN),
                'type' => 'integer',
                'default' => 50,
                'sanitize_callback' => 'absint',
            ),
        );
    }

    /**
     * Get arguments for pedigree
     */
    private function get_pedigree_args() {
        return array(
            'generations' => array(
                'description' => __('Number of generations to include.', TRUEPAWS_TEXT_DOMAIN),
                'type' => 'integer',
                'default' => 3,
                'minimum' => 1,
                'maximum' => 5,
                'sanitize_callback' => 'absint',
            ),
        );
    }

    /**
     * Get arguments for litters
     */
    private function get_litters_args() {
        return array(
            'page' => array(
                'description' => __('Current page of the collection.', TRUEPAWS_TEXT_DOMAIN),
                'type' => 'integer',
                'default' => 1,
                'sanitize_callback' => 'absint',
            ),
            'per_page' => array(
                'description' => __('Maximum number of items to be returned in result set.', TRUEPAWS_TEXT_DOMAIN),
                'type' => 'integer',
                'default' => 20,
                'sanitize_callback' => 'absint',
            ),
        );
    }

    /**
     * Get arguments for creating litter
     */
    private function create_litter_args() {
        return array(
            'litter_name' => array(
                'description' => __('Litter name.', TRUEPAWS_TEXT_DOMAIN),
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'dam_id' => array(
                'required' => true,
                'description' => __('Dam (mother) animal ID.', TRUEPAWS_TEXT_DOMAIN),
                'type' => 'integer',
                'sanitize_callback' => 'absint',
            ),
            'sire_id' => array(
                'required' => true,
                'description' => __('Sire (father) animal ID.', TRUEPAWS_TEXT_DOMAIN),
                'type' => 'integer',
                'sanitize_callback' => 'absint',
            ),
            'mating_date' => array(
                'required' => true,
                'description' => __('Mating date.', TRUEPAWS_TEXT_DOMAIN),
                'type' => 'string',
                'format' => 'date',
            ),
            'mating_method' => array(
                'description' => __('Mating method.', TRUEPAWS_TEXT_DOMAIN),
                'type' => 'string',
                'enum' => array('natural', 'ai'),
                'default' => 'natural',
            ),
            'notes' => array(
                'description' => __('Additional notes.', TRUEPAWS_TEXT_DOMAIN),
                'type' => 'string',
                'sanitize_callback' => 'sanitize_textarea_field',
            ),
        );
    }

    /**
     * Get arguments for updating litter
     */
    private function update_litter_args() {
        return $this->create_litter_args();
    }

    /**
     * Get arguments for whelping litter
     */
    private function whelp_litter_args() {
        return array(
            'actual_date' => array(
                'required' => true,
                'description' => __('Actual whelping date.', TRUEPAWS_TEXT_DOMAIN),
                'type' => 'string',
                'format' => 'date',
            ),
            'male_count' => array(
                'required' => true,
                'description' => __('Number of male puppies.', TRUEPAWS_TEXT_DOMAIN),
                'type' => 'integer',
                'default' => 0,
                'sanitize_callback' => 'absint',
            ),
            'female_count' => array(
                'required' => true,
                'description' => __('Number of female puppies.', TRUEPAWS_TEXT_DOMAIN),
                'type' => 'integer',
                'default' => 0,
                'sanitize_callback' => 'absint',
            ),
        );
    }

    /**
     * Get arguments for contacts
     */
    private function get_contacts_args() {
        return array(
            'page' => array(
                'description' => __('Current page of the collection.', TRUEPAWS_TEXT_DOMAIN),
                'type' => 'integer',
                'default' => 1,
                'sanitize_callback' => 'absint',
            ),
            'per_page' => array(
                'description' => __('Maximum number of items to be returned in result set.', TRUEPAWS_TEXT_DOMAIN),
                'type' => 'integer',
                'default' => 20,
                'sanitize_callback' => 'absint',
            ),
            'search' => array(
                'description' => __('Limit results to those matching a string.', TRUEPAWS_TEXT_DOMAIN),
                'type' => 'string',
            ),
            'status' => array(
                'description' => __('Limit results to contacts with specific status.', TRUEPAWS_TEXT_DOMAIN),
                'type' => 'string',
                'enum' => array('waitlist', 'reserved', 'buyer', 'inactive'),
            ),
        );
    }

    /**
     * Get arguments for creating contact
     */
    private function create_contact_args() {
        return array(
            'first_name' => array(
                'required' => true,
                'description' => __('First name.', TRUEPAWS_TEXT_DOMAIN),
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'last_name' => array(
                'description' => __('Last name.', TRUEPAWS_TEXT_DOMAIN),
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'email' => array(
                'required' => true,
                'description' => __('Email address.', TRUEPAWS_TEXT_DOMAIN),
                'type' => 'string',
                'format' => 'email',
                'sanitize_callback' => 'sanitize_email',
            ),
            'phone' => array(
                'description' => __('Phone number.', TRUEPAWS_TEXT_DOMAIN),
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'address' => array(
                'description' => __('Address.', TRUEPAWS_TEXT_DOMAIN),
                'type' => 'string',
                'sanitize_callback' => 'sanitize_textarea_field',
            ),
            'notes' => array(
                'description' => __('Additional notes.', TRUEPAWS_TEXT_DOMAIN),
                'type' => 'string',
                'sanitize_callback' => 'sanitize_textarea_field',
            ),
            'status' => array(
                'description' => __('Contact status.', TRUEPAWS_TEXT_DOMAIN),
                'type' => 'string',
                'enum' => array('waitlist', 'reserved', 'buyer', 'inactive'),
                'default' => 'waitlist',
            ),
        );
    }

    /**
     * Get arguments for updating contact
     */
    private function update_contact_args() {
        return $this->create_contact_args();
    }

    /**
     * Get arguments for creating event
     */
    private function create_event_args() {
        return array(
            'event_type' => array(
                'required' => true,
                'description' => __('Event type.', TRUEPAWS_TEXT_DOMAIN),
                'type' => 'string',
                'enum' => array('birth', 'vaccine', 'heat', 'mating', 'whelping', 'weight', 'vet_visit', 'note'),
            ),
            'event_date' => array(
                'required' => true,
                'description' => __('Event date.', TRUEPAWS_TEXT_DOMAIN),
                'type' => 'string',
                'format' => 'date-time',
            ),
            'title' => array(
                'description' => __('Event title.', TRUEPAWS_TEXT_DOMAIN),
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'meta_data' => array(
                'description' => __('Additional event data.', TRUEPAWS_TEXT_DOMAIN),
                'type' => 'object',
            ),
        );
    }

    /**
     * Get arguments for updating event
     */
    private function update_event_args() {
        return $this->create_event_args();
    }
}