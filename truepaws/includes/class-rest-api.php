<?php
/**
 * REST API endpoints for TruePaws
 */

if (!defined('ABSPATH')) {
    exit;
}

class TruePaws_REST_API {

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
        register_rest_route('truepaws/v1', '/animals', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_animals'),
                'permission_callback' => array($this, 'check_admin_permissions'),
                'args' => array(
                    'page' => array(
                        'default' => 1,
                        'sanitize_callback' => 'absint',
                    ),
                    'per_page' => array(
                        'default' => 20,
                        'sanitize_callback' => 'absint',
                    ),
                    'search' => array(
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'status' => array(
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'breed' => array(
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                ),
            ),
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'create_animal'),
                'permission_callback' => array($this, 'check_admin_permissions'),
                'args' => $this->get_animal_args(),
            ),
        ));

        register_rest_route('truepaws/v1', '/animals/(?P<id>\d+)', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_animal'),
                'permission_callback' => array($this, 'check_admin_permissions'),
            ),
            array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array($this, 'update_animal'),
                'permission_callback' => array($this, 'check_admin_permissions'),
                'args' => $this->get_animal_args(false),
            ),
            array(
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => array($this, 'delete_animal'),
                'permission_callback' => array($this, 'check_admin_permissions'),
            ),
        ));

        // Animal photos endpoints
        register_rest_route('truepaws/v1', '/animals/(?P<id>\d+)/photos', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_animal_photos'),
                'permission_callback' => array($this, 'check_admin_permissions'),
            ),
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'add_animal_photos'),
                'permission_callback' => array($this, 'check_admin_permissions'),
                'args' => array(
                    'attachment_ids' => array(
                        'required' => true,
                        'type' => 'array',
                        'items' => array('type' => 'integer'),
                    ),
                ),
            ),
            array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array($this, 'reorder_animal_photos'),
                'permission_callback' => array($this, 'check_admin_permissions'),
                'args' => array(
                    'photos' => array(
                        'required' => true,
                        'type' => 'array',
                        'items' => array(
                            'type' => 'object',
                            'properties' => array(
                                'id' => array('type' => 'integer'),
                                'sort_order' => array('type' => 'integer'),
                                'is_featured' => array('type' => 'integer'),
                            ),
                        ),
                    ),
                ),
            ),
        ));

        register_rest_route('truepaws/v1', '/animals/(?P<id>\d+)/photos/(?P<photo_id>\d+)', array(
            'methods' => WP_REST_Server::DELETABLE,
            'callback' => array($this, 'delete_animal_photo'),
            'permission_callback' => array($this, 'check_admin_permissions'),
        ));

        // Timeline endpoint
        register_rest_route('truepaws/v1', '/animals/(?P<id>\d+)/timeline', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'get_animal_timeline'),
            'permission_callback' => array($this, 'check_admin_permissions'),
        ));

        // Pedigree endpoint
        register_rest_route('truepaws/v1', '/animals/(?P<id>\d+)/pedigree', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'get_animal_pedigree'),
            'permission_callback' => array($this, 'check_admin_permissions'),
            'args' => array(
                'generations' => array(
                    'default' => 3,
                    'sanitize_callback' => 'absint',
                ),
            ),
        ));

        // AI care advice endpoint
        register_rest_route('truepaws/v1', '/animals/(?P<id>\d+)/ai-care-advice', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'get_animal_ai_care_advice'),
            'permission_callback' => array($this, 'check_admin_permissions'),
        ));

        // AI health alerts endpoint
        register_rest_route('truepaws/v1', '/animals/(?P<id>\d+)/ai-health-alerts', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'get_animal_health_alerts'),
            'permission_callback' => array($this, 'check_admin_permissions'),
        ));

        // AI marketing bio endpoint
        register_rest_route('truepaws/v1', '/animals/(?P<id>\d+)/ai-marketing-bio', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'get_animal_marketing_bio'),
            'permission_callback' => array($this, 'check_admin_permissions'),
        ));

        // Litters endpoints
        register_rest_route('truepaws/v1', '/litters', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_litters'),
                'permission_callback' => array($this, 'check_admin_permissions'),
            ),
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'create_litter'),
                'permission_callback' => array($this, 'check_admin_permissions'),
            ),
        ));

        register_rest_route('truepaws/v1', '/litters/(?P<id>\d+)', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_litter'),
                'permission_callback' => array($this, 'check_admin_permissions'),
            ),
            array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array($this, 'update_litter'),
                'permission_callback' => array($this, 'check_admin_permissions'),
            ),
            array(
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => array($this, 'delete_litter'),
                'permission_callback' => array($this, 'check_admin_permissions'),
            ),
        ));

        // Whelping endpoint
        register_rest_route('truepaws/v1', '/litters/(?P<id>\d+)/whelp', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array($this, 'whelp_litter'),
            'permission_callback' => array($this, 'check_admin_permissions'),
        ));

        // AI litter name suggestions endpoint
        register_rest_route('truepaws/v1', '/litters/(?P<id>\d+)/suggest-names', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'suggest_litter_names'),
            'permission_callback' => array($this, 'check_admin_permissions'),
        ));

        // Contacts endpoints
        register_rest_route('truepaws/v1', '/contacts', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_contacts'),
                'permission_callback' => array($this, 'check_admin_permissions'),
                'args' => array(
                    'status' => array(
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                ),
            ),
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'create_contact'),
                'permission_callback' => array($this, 'check_admin_permissions'),
            ),
        ));

        register_rest_route('truepaws/v1', '/contacts/(?P<id>\d+)/purchases', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'get_contact_purchases'),
            'permission_callback' => array($this, 'check_admin_permissions'),
        ));

        register_rest_route('truepaws/v1', '/contacts/(?P<id>\d+)', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_contact'),
                'permission_callback' => array($this, 'check_admin_permissions'),
            ),
            array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array($this, 'update_contact'),
                'permission_callback' => array($this, 'check_admin_permissions'),
            ),
            array(
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => array($this, 'delete_contact'),
                'permission_callback' => array($this, 'check_admin_permissions'),
            ),
        ));

        // Dashboard stats endpoint
        register_rest_route('truepaws/v1', '/dashboard/stats', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'get_dashboard_stats'),
            'permission_callback' => array($this, 'check_admin_permissions'),
        ));

        // Dashboard latest events endpoint
        register_rest_route('truepaws/v1', '/dashboard/latest-events', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'get_latest_events'),
            'permission_callback' => array($this, 'check_admin_permissions'),
        ));

        // Dashboard sales reports endpoint
        register_rest_route('truepaws/v1', '/dashboard/sales', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'get_sales_report'),
            'permission_callback' => array($this, 'check_admin_permissions'),
        ));

        // Dashboard activity heatmap endpoint
        register_rest_route('truepaws/v1', '/dashboard/activity-heatmap', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'get_activity_heatmap'),
            'permission_callback' => array($this, 'check_admin_permissions'),
        ));

        // Settings endpoints
        register_rest_route('truepaws/v1', '/settings', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_settings'),
                'permission_callback' => array($this, 'check_admin_permissions'),
            ),
            array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array($this, 'update_settings'),
                'permission_callback' => array($this, 'check_admin_permissions'),
            ),
        ));

        register_rest_route('truepaws/v1', '/settings/breeds', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_breeds'),
                'permission_callback' => array($this, 'check_admin_permissions'),
            ),
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'add_breed'),
                'permission_callback' => array($this, 'check_admin_permissions'),
            ),
        ));

        register_rest_route('truepaws/v1', '/settings/breeds/(?P<id>\d+)', array(
            'methods' => WP_REST_Server::DELETABLE,
            'callback' => array($this, 'delete_breed'),
            'permission_callback' => array($this, 'check_admin_permissions'),
        ));

        // Events endpoints
        register_rest_route('truepaws/v1', '/animals/(?P<id>\d+)/events', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array($this, 'create_event'),
            'permission_callback' => array($this, 'check_admin_permissions'),
        ));

        register_rest_route('truepaws/v1', '/events/(?P<id>\d+)', array(
            array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array($this, 'update_event'),
                'permission_callback' => array($this, 'check_admin_permissions'),
            ),
            array(
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => array($this, 'delete_event'),
                'permission_callback' => array($this, 'check_admin_permissions'),
            ),
        ));

        // PDF generation endpoints
        register_rest_route('truepaws/v1', '/animals/(?P<id>\d+)/generate-handover', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array($this, 'generate_handover_pdf'),
            'permission_callback' => array($this, 'check_admin_permissions'),
        ));

        register_rest_route('truepaws/v1', '/animals/(?P<id>\d+)/generate-pedigree-pdf', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array($this, 'generate_pedigree_pdf'),
            'permission_callback' => array($this, 'check_admin_permissions'),
        ));

        // Public inquiry endpoint (no auth required)
        register_rest_route('truepaws/v1', '/inquiries', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array($this, 'submit_inquiry'),
            'permission_callback' => '__return_true',
            'args' => array(
                'first_name' => array(
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'last_name' => array(
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'email' => array(
                    'required' => true,
                    'sanitize_callback' => 'sanitize_email',
                ),
                'phone' => array(
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'message' => array(
                    'required' => true,
                    'sanitize_callback' => 'sanitize_textarea_field',
                ),
                'animal_id' => array(
                    'sanitize_callback' => 'absint',
                ),
            ),
        ));
    }

    /**
     * Check admin permissions
     */
    public function check_admin_permissions() {
        return current_user_can('manage_options');
    }

    /**
     * Get validation args for animal fields
     */
    private function get_animal_args($required = true) {
        $args = array(
            'name' => array(
                'required' => $required,
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'call_name' => array(
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'registration_number' => array(
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'microchip_id' => array(
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'breed' => array(
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'color_markings' => array(
                'sanitize_callback' => 'wp_kses_post',
            ),
            'description' => array(
                'sanitize_callback' => 'wp_kses_post',
            ),
            'sex' => array(
                'required' => $required,
                'validate_callback' => function($value) {
                    return in_array($value, array('M', 'F'));
                },
            ),
            'sire_id' => array(
                'sanitize_callback' => 'absint',
            ),
            'dam_id' => array(
                'sanitize_callback' => 'absint',
            ),
            'birth_date' => array(
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'status' => array(
                'validate_callback' => function($value) {
                    return in_array($value, array('active', 'retired', 'sold', 'deceased', 'co-owned'));
                },
            ),
            'featured_image_id' => array(
                'sanitize_callback' => 'absint',
            ),
        );

        return $args;
    }

    // Animal endpoints implementation
    public function get_animals($request) {
        global $wpdb;

        $page = $request->get_param('page') ?: 1;
        $per_page = $request->get_param('per_page') ?: 20;
        $search = $request->get_param('search');
        $status = $request->get_param('status');
        $breed = $request->get_param('breed');

        $offset = ($page - 1) * $per_page;

        $where = array();
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

        $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $query = $wpdb->prepare(
            "SELECT SQL_CALC_FOUND_ROWS 
                a.id, a.name, a.call_name, a.registration_number, a.microchip_id, 
                a.breed, a.sex, a.birth_date, a.status, a.sire_id, a.dam_id, 
                a.featured_image_id, a.created_at,
                sire.name as sire_name,
                dam.name as dam_name
             FROM {$wpdb->prefix}bm_animals a
             LEFT JOIN {$wpdb->prefix}bm_animals sire ON a.sire_id = sire.id
             LEFT JOIN {$wpdb->prefix}bm_animals dam ON a.dam_id = dam.id
             $where_clause
             ORDER BY a.created_at DESC
             LIMIT %d OFFSET %d",
            array_merge($where_values, array($per_page, $offset))
        );

        $animals = $wpdb->get_results($query, ARRAY_A);
        $total = $wpdb->get_var("SELECT FOUND_ROWS()");

        // Add featured image URLs
        foreach ($animals as &$animal) {
            if (!empty($animal['featured_image_id'])) {
                $image_url = wp_get_attachment_image_url($animal['featured_image_id'], 'medium');
                if (!$image_url) {
                    $image_url = wp_get_attachment_image_url($animal['featured_image_id'], 'full');
                }
                if (!$image_url) {
                    $image_url = wp_get_attachment_url($animal['featured_image_id']);
                }
                if ($image_url) {
                    $animal['featured_image_url'] = $image_url;
                }
            }
        }

        return new WP_REST_Response(array(
            'animals' => $animals,
            'pagination' => array(
                'page' => $page,
                'per_page' => $per_page,
                'total' => intval($total),
                'total_pages' => ceil($total / $per_page)
            )
        ));
    }

    public function create_animal($request) {
        global $wpdb;

        $microchip = sanitize_text_field($request->get_param('microchip_id'));
        
        $data = array(
            'name' => sanitize_text_field($request->get_param('name')),
            'call_name' => sanitize_text_field($request->get_param('call_name')),
            'registration_number' => sanitize_text_field($request->get_param('registration_number')),
            'microchip_id' => !empty($microchip) ? $microchip : null,
            'breed' => sanitize_text_field($request->get_param('breed')),
            'color_markings' => wp_kses_post($request->get_param('color_markings')),
            'description' => wp_kses_post($request->get_param('description')),
            'sex' => $request->get_param('sex'),
            'sire_id' => absint($request->get_param('sire_id')),
            'dam_id' => absint($request->get_param('dam_id')),
            'birth_date' => sanitize_text_field($request->get_param('birth_date')),
            'status' => $request->get_param('status') ?: 'active',
            'featured_image_id' => absint($request->get_param('featured_image_id')),
        );

        // Remove empty values (but keep null for microchip_id)
        $data = array_filter($data, function($value, $key) {
            if ($key === 'microchip_id') {
                return true; // Keep null values for microchip_id
            }
            return $value !== '' && $value !== null;
        }, ARRAY_FILTER_USE_BOTH);

        $result = $wpdb->insert("{$wpdb->prefix}bm_animals", $data);

        if ($result === false) {
            // Check if it's a duplicate microchip_id error
            if (strpos($wpdb->last_error, 'Duplicate entry') !== false && strpos($wpdb->last_error, 'microchip_id') !== false) {
                $existing = $wpdb->get_row($wpdb->prepare(
                    "SELECT id, name FROM {$wpdb->prefix}bm_animals WHERE microchip_id = %s",
                    $data['microchip_id']
                ));
                if ($existing) {
                    return new WP_Error('duplicate_microchip', 
                        sprintf(__('This microchip ID is already used by "%s" (ID: %d)', 'truepaws'), $existing->name, $existing->id), 
                        array('status' => 400));
                }
            }
            return new WP_Error('db_error', __('Failed to create animal: ' . $wpdb->last_error, 'truepaws'), array('status' => 500));
        }

        $animal_id = $wpdb->insert_id;

        // Create birth event if birth date is provided
        if (!empty($data['birth_date'])) {
            $wpdb->insert("{$wpdb->prefix}bm_events", array(
                'animal_id' => $animal_id,
                'event_type' => 'birth',
                'event_date' => $data['birth_date'],
                'title' => __('Birth', 'truepaws'),
                'created_by' => get_current_user_id()
            ));
        }

        // Clear dashboard cache
        delete_transient('truepaws_dashboard_stats');

        return new WP_REST_Response(array(
            'id' => $animal_id,
            'message' => __('Animal created successfully', 'truepaws')
        ), 201);
    }

    public function get_animal($request) {
        global $wpdb;

        $id = absint($request->get_param('id'));

        $animal = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}bm_animals WHERE id = %d",
                $id
            ),
            ARRAY_A
        );

        if (!$animal) {
            return new WP_Error('not_found', __('Animal not found', 'truepaws'), array('status' => 404));
        }

        // Add parent names
        if ($animal['sire_id']) {
            $animal['sire_name'] = $wpdb->get_var($wpdb->prepare(
                "SELECT name FROM {$wpdb->prefix}bm_animals WHERE id = %d",
                $animal['sire_id']
            ));
        }
        if ($animal['dam_id']) {
            $animal['dam_name'] = $wpdb->get_var($wpdb->prepare(
                "SELECT name FROM {$wpdb->prefix}bm_animals WHERE id = %d",
                $animal['dam_id']
            ));
        }

        if (!empty($animal['featured_image_id'])) {
            $image_url = wp_get_attachment_image_url($animal['featured_image_id'], 'large');
            if (!$image_url) {
                $image_url = wp_get_attachment_image_url($animal['featured_image_id'], 'full');
            }
            if (!$image_url) {
                $image_url = wp_get_attachment_url($animal['featured_image_id']);
            }
            if ($image_url) {
                $animal['featured_image_url'] = $image_url;
            }
        }

        // Add photos array
        $animal['photos'] = $this->get_animal_photos_data($id);

        return new WP_REST_Response($animal);
    }

    /**
     * Get photos for an animal (internal helper)
     */
    private function get_animal_photos_data($animal_id) {
        global $wpdb;
        $photos = $wpdb->get_results($wpdb->prepare(
            "SELECT id, attachment_id, sort_order, is_featured, caption
             FROM {$wpdb->prefix}bm_animal_photos
             WHERE animal_id = %d
             ORDER BY sort_order ASC, id ASC",
            $animal_id
        ), ARRAY_A);

        foreach ($photos as &$p) {
            $url = wp_get_attachment_image_url($p['attachment_id'], 'medium');
            if (!$url) {
                $url = wp_get_attachment_image_url($p['attachment_id'], 'full');
            }
            if (!$url) {
                $url = wp_get_attachment_url($p['attachment_id']);
            }
            $p['url'] = $url;
            $p['url_large'] = wp_get_attachment_image_url($p['attachment_id'], 'large') ?: wp_get_attachment_url($p['attachment_id']);
        }
        return $photos;
    }

    public function get_animal_photos($request) {
        $id = absint($request->get_param('id'));
        $animal = $this->verify_animal_exists($id);
        if (is_wp_error($animal)) {
            return $animal;
        }
        return new WP_REST_Response(array('photos' => $this->get_animal_photos_data($id)));
    }

    public function add_animal_photos($request) {
        global $wpdb;

        $id = absint($request->get_param('id'));
        $animal = $this->verify_animal_exists($id);
        if (is_wp_error($animal)) {
            return $animal;
        }

        $attachment_ids = $request->get_param('attachment_ids');
        if (!is_array($attachment_ids)) {
            $attachment_ids = array();
        }
        $attachment_ids = array_map('absint', array_filter($attachment_ids));

        $max_order = $wpdb->get_var($wpdb->prepare(
            "SELECT COALESCE(MAX(sort_order), -1) FROM {$wpdb->prefix}bm_animal_photos WHERE animal_id = %d",
            $id
        ));
        $sort_order = intval($max_order) + 1;

        $inserted = array();
        foreach ($attachment_ids as $att_id) {
            if ($att_id <= 0) {
                continue;
            }
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}bm_animal_photos WHERE animal_id = %d AND attachment_id = %d",
                $id,
                $att_id
            ));
            if ($exists) {
                continue;
            }
            $is_featured = 0;
            if (empty($animal['featured_image_id'])) {
                $is_featured = 1;
                $wpdb->update("{$wpdb->prefix}bm_animals", array('featured_image_id' => $att_id), array('id' => $id));
            }
            $wpdb->insert("{$wpdb->prefix}bm_animal_photos", array(
                'animal_id' => $id,
                'attachment_id' => $att_id,
                'sort_order' => $sort_order++,
                'is_featured' => $is_featured,
            ));
            $inserted[] = array(
                'id' => $wpdb->insert_id,
                'attachment_id' => $att_id,
                'sort_order' => $sort_order - 1,
                'is_featured' => $is_featured,
            );
        }

        return new WP_REST_Response(array(
            'message' => __('Photos added successfully', 'truepaws'),
            'photos' => $this->get_animal_photos_data($id),
        ), 201);
    }

    public function reorder_animal_photos($request) {
        global $wpdb;

        $id = absint($request->get_param('id'));
        $animal = $this->verify_animal_exists($id);
        if (is_wp_error($animal)) {
            return $animal;
        }

        $photos = $request->get_param('photos');
        if (!is_array($photos)) {
            return new WP_Error('invalid_data', __('Invalid photos data', 'truepaws'), array('status' => 400));
        }

        $featured_attachment_id = null;
        foreach ($photos as $p) {
            $photo_id = absint($p['id'] ?? 0);
            $sort_order = isset($p['sort_order']) ? absint($p['sort_order']) : 0;
            $is_featured = !empty($p['is_featured']);

            if ($photo_id > 0) {
                $wpdb->update(
                    "{$wpdb->prefix}bm_animal_photos",
                    array('sort_order' => $sort_order, 'is_featured' => $is_featured ? 1 : 0),
                    array('id' => $photo_id, 'animal_id' => $id)
                );
                if ($is_featured) {
                    $featured_attachment_id = $wpdb->get_var($wpdb->prepare(
                        "SELECT attachment_id FROM {$wpdb->prefix}bm_animal_photos WHERE id = %d",
                        $photo_id
                    ));
                }
            }
        }

        if ($featured_attachment_id) {
            $wpdb->update("{$wpdb->prefix}bm_animal_photos", array('is_featured' => 0), array('animal_id' => $id));
            $wpdb->update("{$wpdb->prefix}bm_animal_photos", array('is_featured' => 1), array('animal_id' => $id, 'attachment_id' => $featured_attachment_id));
            $wpdb->update("{$wpdb->prefix}bm_animals", array('featured_image_id' => $featured_attachment_id), array('id' => $id));
        }

        return new WP_REST_Response(array(
            'message' => __('Photos reordered successfully', 'truepaws'),
            'photos' => $this->get_animal_photos_data($id),
        ));
    }

    public function delete_animal_photo($request) {
        global $wpdb;

        $id = absint($request->get_param('id'));
        $photo_id = absint($request->get_param('photo_id'));

        $photo = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}bm_animal_photos WHERE id = %d AND animal_id = %d",
            $photo_id,
            $id
        ), ARRAY_A);

        if (!$photo) {
            return new WP_Error('not_found', __('Photo not found', 'truepaws'), array('status' => 404));
        }

        $wpdb->delete("{$wpdb->prefix}bm_animal_photos", array('id' => $photo_id));

        if (!empty($photo['is_featured'])) {
            $next = $wpdb->get_row($wpdb->prepare(
                "SELECT attachment_id FROM {$wpdb->prefix}bm_animal_photos WHERE animal_id = %d ORDER BY sort_order ASC LIMIT 1",
                $id
            ), ARRAY_A);
            $new_featured = $next ? $next['attachment_id'] : null;
            $wpdb->update("{$wpdb->prefix}bm_animals", array('featured_image_id' => $new_featured), array('id' => $id));
            if ($new_featured) {
                $wpdb->update("{$wpdb->prefix}bm_animal_photos", array('is_featured' => 1), array('animal_id' => $id, 'attachment_id' => $new_featured));
            }
        }

        return new WP_REST_Response(array(
            'message' => __('Photo removed successfully', 'truepaws'),
            'photos' => $this->get_animal_photos_data($id),
        ));
    }

    private function verify_animal_exists($id) {
        global $wpdb;
        $animal = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}bm_animals WHERE id = %d",
            $id
        ), ARRAY_A);
        if (!$animal) {
            return new WP_Error('not_found', __('Animal not found', 'truepaws'), array('status' => 404));
        }
        return $animal;
    }

    public function update_animal($request) {
        global $wpdb;

        $id = absint($request->get_param('id'));
        $params = $request->get_json_params();
        if (empty($params) && $request->get_body()) {
            $params = json_decode($request->get_body(), true) ?: array();
        }
        if (empty($params)) {
            $params = $request->get_params();
        }

        $updatable = array(
            'name' => 'sanitize_text_field',
            'call_name' => 'sanitize_text_field',
            'registration_number' => 'sanitize_text_field',
            'microchip_id' => 'sanitize_text_field',
            'breed' => 'sanitize_text_field',
            'color_markings' => 'wp_kses_post',
            'description' => 'wp_kses_post',
            'sex' => null,
            'sire_id' => 'absint',
            'dam_id' => 'absint',
            'birth_date' => 'sanitize_text_field',
            'status' => null,
            'featured_image_id' => 'absint',
        );

        $data = array('updated_at' => current_time('mysql'));

        foreach ($updatable as $field => $sanitize) {
            if (!array_key_exists($field, $params)) {
                continue;
            }
            $value = $params[$field];
            if ($sanitize === 'absint') {
                $data[$field] = absint($value);
            } elseif ($sanitize === 'sanitize_text_field') {
                $sanitized = sanitize_text_field($value);
                // Convert empty microchip_id to NULL to avoid UNIQUE constraint issues
                if ($field === 'microchip_id' && empty($sanitized)) {
                    $data[$field] = null;
                } else {
                    $data[$field] = $sanitized;
                }
            } elseif ($sanitize === 'wp_kses_post') {
                $data[$field] = wp_kses_post($value);
            } else {
                if ($field === 'status' && !in_array($value, array('active', 'retired', 'sold', 'deceased', 'co-owned'), true)) {
                    continue;
                }
                if ($field === 'sex' && !in_array($value, array('M', 'F'), true)) {
                    continue;
                }
                $data[$field] = $value;
            }
        }

        if (count($data) <= 1) {
            return new WP_REST_Response(array('message' => __('Nothing to update', 'truepaws')));
        }

        $result = $wpdb->update("{$wpdb->prefix}bm_animals", $data, array('id' => $id));

        if ($result === false) {
            // Check if it's a duplicate microchip_id error
            if (strpos($wpdb->last_error, 'Duplicate entry') !== false && strpos($wpdb->last_error, 'microchip_id') !== false) {
                if (isset($data['microchip_id'])) {
                    $existing = $wpdb->get_row($wpdb->prepare(
                        "SELECT id, name FROM {$wpdb->prefix}bm_animals WHERE microchip_id = %s AND id != %d",
                        $data['microchip_id'], $id
                    ));
                    if ($existing) {
                        return new WP_Error('duplicate_microchip', 
                            sprintf(__('This microchip ID is already used by "%s" (ID: %d)', 'truepaws'), $existing->name, $existing->id), 
                            array('status' => 400));
                    }
                }
            }
            return new WP_Error('db_error', __('Failed to update animal: ' . $wpdb->last_error, 'truepaws'), array('status' => 500));
        }

        // Clear dashboard cache
        delete_transient('truepaws_dashboard_stats');

        return new WP_REST_Response(array(
            'message' => __('Animal updated successfully', 'truepaws')
        ));
    }

    public function delete_animal($request) {
        global $wpdb;

        $id = absint($request->get_param('id'));

        $result = $wpdb->delete("{$wpdb->prefix}bm_animals", array('id' => $id));

        if ($result === false) {
            return new WP_Error('db_error', __('Failed to delete animal', 'truepaws'), array('status' => 500));
        }

        // Clear dashboard cache
        delete_transient('truepaws_dashboard_stats');

        return new WP_REST_Response(array(
            'message' => __('Animal deleted successfully', 'truepaws')
        ));
    }

    public function get_animal_timeline($request) {
        global $wpdb;

        $id = absint($request->get_param('id'));

        $events = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}bm_events
                 WHERE animal_id = %d
                 ORDER BY event_date DESC, created_at DESC",
                $id
            ),
            ARRAY_A
        );

        // Decode meta_data JSON for each event
        foreach ($events as &$event) {
            if (!empty($event['meta_data'])) {
                $decoded = is_string($event['meta_data'])
                    ? json_decode($event['meta_data'], true)
                    : $event['meta_data'];
                $event['meta_data'] = is_array($decoded) ? $decoded : array();
            }
        }

        return new WP_REST_Response(array('events' => $events));
    }

    public function get_animal_pedigree($request) {
        $id = absint($request->get_param('id'));
        $generations = absint($request->get_param('generations')) ?: 3;

        $pedigree = truepaws_get_simple_pedigree($id, $generations);

        if (!$pedigree) {
            return new WP_Error('not_found', __('Animal not found', 'truepaws'), array('status' => 404));
        }

        return new WP_REST_Response($pedigree);
    }

    /**
     * Get AI care advice for an animal via Gemini API (cached 30 days)
     */
    public function get_animal_ai_care_advice($request) {
        global $wpdb;

        $id = absint($request->get_param('id'));
        $api_key = get_option('truepaws_gemini_api_key', '');

        if (empty($api_key)) {
            return new WP_REST_Response(array(
                'enabled' => false,
                'message' => __('Configure Gemini API key in Settings to enable AI care advice.', 'truepaws')
            ));
        }

        $cache_key = 'truepaws_ai_advice_' . $id;
        $cached = get_transient($cache_key);
        if ($cached !== false) {
            $cached['cached'] = true;
            return new WP_REST_Response($cached);
        }

        $animal = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT breed, sex, birth_date FROM {$wpdb->prefix}bm_animals WHERE id = %d",
                $id
            ),
            ARRAY_A
        );

        if (!$animal) {
            return new WP_Error('not_found', __('Animal not found', 'truepaws'), array('status' => 404));
        }

        $species = get_option('truepaws_default_species', 'dog');
        $species_labels = array(
            'dog' => 'dog',
            'cat' => 'cat',
            'horse' => 'horse',
            'rabbit' => 'rabbit',
            'guinea_pig' => 'guinea pig',
            'ferret' => 'ferret',
            'bird' => 'bird'
        );
        $species_label = isset($species_labels[$species]) ? $species_labels[$species] : $species;
        $breed = !empty($animal['breed']) ? $animal['breed'] : 'Unknown breed';
        $sex = ($animal['sex'] === 'M') ? 'Male' : 'Female';
        $birth_date = $animal['birth_date'] ?? '';

        // Compute age for prompt
        $age_str = 'exact age unknown';
        if (!empty($birth_date)) {
            $birth = new DateTime($birth_date);
            $now = new DateTime();
            $diff = $now->diff($birth);
            if ($diff->days < 30) {
                $age_str = $diff->days . ' days old';
            } elseif ($diff->days < 365) {
                $weeks = (int) ($diff->days / 7);
                $age_str = $weeks . ' weeks old';
            } else {
                $age_str = $diff->y . ' year(s) old';
            }
        }

        $prompt = sprintf(
            "You are a veterinarian advisor. IMPORTANT: The user breeds %ss only. Provide care and health advice ONLY for %ss. Do NOT include information about other species (cats, dogs, horses, rabbits, etc.).\n\nProvide care and health advice for a %s of breed: %s, %s, age: %s (birth date: %s).\n\nInclude:\n1) Care and health advice for this age and breed\n2) Common care tips\n3) Vaccination schedule and important vaccinations\n4) Other important points\n\nFormat as clear sections with bullet points. Be concise. IMPORTANT: Do NOT start with any introductory or conversational sentence (e.g. \"Okay, here's...\", \"Here is...\", \"Sure, ...\"). Begin directly with the first section heading.",
            $species_label,
            $species_label,
            $species_label,
            $breed,
            $sex,
            $age_str,
            $birth_date ?: 'unknown'
        );

        $url = add_query_arg('key', $api_key, 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-lite:generateContent');

        $response = wp_remote_post($url, array(
            'timeout' => 30,
            'headers' => array('Content-Type' => 'application/json'),
            'body' => wp_json_encode(array(
                'contents' => array(
                    array(
                        'parts' => array(
                            array('text' => $prompt)
                        )
                    )
                )
            ))
        ));

        if (is_wp_error($response)) {
            return new WP_REST_Response(array(
                'enabled' => true,
                'content' => null,
                'error' => $response->get_error_message()
            ));
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        if ($code !== 200 || empty($body)) {
            $error_msg = isset($body['error']['message']) ? $body['error']['message'] : __('Failed to get AI response', 'truepaws');
            return new WP_REST_Response(array(
                'enabled' => true,
                'content' => null,
                'error' => $error_msg
            ));
        }

        $text = '';
        if (isset($body['candidates'][0]['content']['parts'][0]['text'])) {
            $text = $this->clean_ai_response($body['candidates'][0]['content']['parts'][0]['text']);
        }

        $result = array(
            'enabled' => true,
            'content' => $text,
            'cached' => false
        );
        set_transient($cache_key, $result, 30 * DAY_IN_SECONDS);

        return new WP_REST_Response($result);
    }

    /**
     * Get AI-powered health alerts for an animal based on event history
     */
    public function get_animal_health_alerts($request) {
        global $wpdb;

        $id = absint($request->get_param('id'));
        $api_key = get_option('truepaws_gemini_api_key', '');

        // #region agent log
        $log_path = '/Users/daanial/Desktop/apps/TruePaws/.cursor/debug-88b937.log';
        $log_data = json_encode(['sessionId'=>'88b937','runId'=>'debug','hypothesisId'=>'B','location'=>'class-rest-api.php:1048:entry','message'=>'AI health alerts called','data'=>['animal_id'=>$id,'api_key_set'=>!empty($api_key)],'timestamp'=>time()*1000]);
        @file_put_contents($log_path, $log_data."\n", FILE_APPEND);
        // #endregion

        if (empty($api_key)) {
            // #region agent log
            $log_data = json_encode(['sessionId'=>'88b937','runId'=>'debug','hypothesisId'=>'B','location'=>'class-rest-api.php:1054:no_api_key','message'=>'API key not configured','data'=>['enabled'=>false],'timestamp'=>time()*1000]);
            @file_put_contents($log_path, $log_data."\n", FILE_APPEND);
            // #endregion
            return new WP_REST_Response(array(
                'enabled' => false,
                'alerts' => array()
            ));
        }

        // Check cache first
        $cache_key = 'truepaws_health_alerts_' . $id;
        $cached = get_transient($cache_key);
        if ($cached !== false) {
            return new WP_REST_Response($cached);
        }

        // Get animal info
        $animal = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT id, name, breed, sex, birth_date FROM {$wpdb->prefix}bm_animals WHERE id = %d",
                $id
            ),
            ARRAY_A
        );

        if (!$animal) {
            return new WP_Error('not_found', __('Animal not found', 'truepaws'), array('status' => 404));
        }

        // Get recent events (last 6 months)
        $events = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT event_type, event_date, title, meta_data 
                 FROM {$wpdb->prefix}bm_events 
                 WHERE animal_id = %d 
                 AND event_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                 ORDER BY event_date DESC
                 LIMIT 50",
                $id
            ),
            ARRAY_A
        );

        // Build event summary for AI
        $event_summary = array();
        foreach ($events as $event) {
            $event_summary[] = sprintf(
                "%s on %s: %s",
                ucfirst($event['event_type']),
                $event['event_date'],
                $event['title']
            );
        }

        $species = get_option('truepaws_default_species', 'dog');
        $age_str = 'unknown age';
        if (!empty($animal['birth_date'])) {
            $birth = new DateTime($animal['birth_date']);
            $now = new DateTime();
            $diff = $now->diff($birth);
            $age_str = $diff->y . ' years, ' . $diff->m . ' months old';
        }

        $prompt = sprintf(
            "You are a veterinary health advisor. Analyze the following %s's health history and identify any important health alerts or concerns.\n\nAnimal: %s (%s, %s)\nAge: %s\n\nRecent health events:\n%s\n\nProvide:\n1) Any overdue vaccinations or health checks\n2) Patterns or concerns from the event history\n3) Recommended actions\n\nFormat as a JSON array of alert objects with 'type' (warning/info/urgent), 'title', and 'message' fields. If no alerts, return empty array. IMPORTANT: Return ONLY the JSON array. Do NOT include any introductory text, explanation, or conversational preamble before or after the JSON.",
            $species,
            $animal['name'],
            $animal['breed'],
            $animal['sex'] === 'M' ? 'Male' : 'Female',
            $age_str,
            !empty($event_summary) ? implode("\n", $event_summary) : 'No recent events recorded'
        );

        $url = add_query_arg('key', $api_key, 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-lite:generateContent');

        $response = wp_remote_post($url, array(
            'timeout' => 30,
            'headers' => array('Content-Type' => 'application/json'),
            'body' => wp_json_encode(array(
                'contents' => array(
                    array(
                        'parts' => array(
                            array('text' => $prompt)
                        )
                    )
                )
            ))
        ));

        if (is_wp_error($response)) {
            // #region agent log
            $log_path = '/Users/daanial/Desktop/apps/TruePaws/.cursor/debug-88b937.log';
            $log_data = json_encode(['sessionId'=>'88b937','runId'=>'debug','hypothesisId'=>'B','location'=>'class-rest-api.php:1141:wp_error','message'=>'API request failed','data'=>['error'=>$response->get_error_message()],'timestamp'=>time()*1000]);
            @file_put_contents($log_path, $log_data."\n", FILE_APPEND);
            // #endregion
            return new WP_REST_Response(array(
                'enabled' => true,
                'alerts' => array(),
                'error' => $response->get_error_message()
            ));
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        // #region agent log
        $log_path = '/Users/daanial/Desktop/apps/TruePaws/.cursor/debug-88b937.log';
        $log_data = json_encode(['sessionId'=>'88b937','runId'=>'debug','hypothesisId'=>'B','location'=>'class-rest-api.php:1150:api_response','message'=>'API response received','data'=>['status_code'=>$code,'body_empty'=>empty($body),'has_candidates'=>isset($body['candidates'])],'timestamp'=>time()*1000]);
        @file_put_contents($log_path, $log_data."\n", FILE_APPEND);
        // #endregion

        if ($code !== 200 || empty($body)) {
            return new WP_REST_Response(array(
                'enabled' => true,
                'alerts' => array(),
                'error' => __('Failed to get AI response', 'truepaws')
            ));
        }

        $text = '';
        if (isset($body['candidates'][0]['content']['parts'][0]['text'])) {
            $text = $body['candidates'][0]['content']['parts'][0]['text'];
        }

        // #region agent log
        $log_data = json_encode(['sessionId'=>'88b937','runId'=>'debug','hypothesisId'=>'B','location'=>'class-rest-api.php:1165:parsing','message'=>'Parsing AI response','data'=>['text_length'=>strlen($text),'text_preview'=>substr($text,0,200)],'timestamp'=>time()*1000]);
        @file_put_contents($log_path, $log_data."\n", FILE_APPEND);
        // #endregion

        // Try to parse JSON from response
        $alerts = array();
        if (preg_match('/\[.*\]/s', $text, $matches)) {
            $parsed = json_decode($matches[0], true);
            if (is_array($parsed)) {
                $alerts = $parsed;
            }
            // #region agent log
            $log_data = json_encode(['sessionId'=>'88b937','runId'=>'debug','hypothesisId'=>'B','location'=>'class-rest-api.php:1172:parsed','message'=>'JSON parsed from AI response','data'=>['alerts_count'=>count($alerts),'json_valid'=>is_array($parsed)],'timestamp'=>time()*1000]);
            @file_put_contents($log_path, $log_data."\n", FILE_APPEND);
            // #endregion
        } else {
            // #region agent log
            $log_data = json_encode(['sessionId'=>'88b937','runId'=>'debug','hypothesisId'=>'B','location'=>'class-rest-api.php:1172:no_json','message'=>'No JSON found in AI response','data'=>['text'=>$text],'timestamp'=>time()*1000]);
            @file_put_contents($log_path, $log_data."\n", FILE_APPEND);
            // #endregion
        }

        $result = array(
            'enabled' => true,
            'alerts' => $alerts,
            'cached' => false
        );

        // Cache for 1 day
        set_transient($cache_key, $result, DAY_IN_SECONDS);

        return new WP_REST_Response($result);
    }

    // Litter endpoints implementation
    public function get_litters($request) {
        global $wpdb;

        $page = $request->get_param('page') ?: 1;
        $per_page = $request->get_param('per_page') ?: 20;
        $offset = ($page - 1) * $per_page;

        $litters = $wpdb->get_results($wpdb->prepare(
            "SELECT SQL_CALC_FOUND_ROWS L.*, S.name as sire_name, D.name as dam_name
             FROM {$wpdb->prefix}bm_litters L
             LEFT JOIN {$wpdb->prefix}bm_animals S ON L.sire_id = S.id
             LEFT JOIN {$wpdb->prefix}bm_animals D ON L.dam_id = D.id
             ORDER BY L.created_at DESC
             LIMIT %d OFFSET %d",
            $per_page, $offset
        ), ARRAY_A);
        
        $total = $wpdb->get_var("SELECT FOUND_ROWS()");

        return new WP_REST_Response(array(
            'litters' => $litters,
            'pagination' => array(
                'page' => $page,
                'per_page' => $per_page,
                'total' => intval($total),
                'total_pages' => ceil($total / $per_page)
            )
        ));
    }

    public function create_litter($request) {
        global $wpdb;

        $sire_id = absint($request->get_param('sire_id'));
        $dam_id = absint($request->get_param('dam_id'));
        $mating_date = sanitize_text_field($request->get_param('mating_date'));

        // Generate litter name
        $sire = $wpdb->get_row($wpdb->prepare("SELECT name FROM {$wpdb->prefix}bm_animals WHERE id = %d", $sire_id), ARRAY_A);
        $dam = $wpdb->get_row($wpdb->prepare("SELECT name FROM {$wpdb->prefix}bm_animals WHERE id = %d", $dam_id), ARRAY_A);

        if (!$sire || !$dam) {
            return new WP_Error('invalid_parents', __('Invalid parent animals', 'truepaws'), array('status' => 400));
        }

        $litter_name = truepaws_generate_litter_name($sire['name'], $dam['name'], $mating_date);
        $expected_date = truepaws_calculate_whelping_date($mating_date, get_option('truepaws_default_species', 'dog'));

        $data = array(
            'litter_name' => $litter_name,
            'sire_id' => $sire_id,
            'dam_id' => $dam_id,
            'mating_date' => $mating_date,
            'mating_method' => sanitize_text_field($request->get_param('mating_method')) ?: 'natural',
            'expected_whelping_date' => $expected_date,
            'notes' => wp_kses_post($request->get_param('notes'))
        );

        $result = $wpdb->insert("{$wpdb->prefix}bm_litters", $data);

        if ($result === false) {
            return new WP_Error('db_error', __('Failed to create litter', 'truepaws'), array('status' => 500));
        }

        $litter_id = $wpdb->insert_id;

        return new WP_REST_Response(array(
            'id' => $litter_id,
            'litter_name' => $litter_name,
            'expected_whelping_date' => $expected_date,
            'message' => __('Litter created successfully', 'truepaws')
        ), 201);
    }

    public function get_litter($request) {
        global $wpdb;

        $id = absint($request->get_param('id'));

        $litter = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT L.*, S.name as sire_name, D.name as dam_name
                 FROM {$wpdb->prefix}bm_litters L
                 LEFT JOIN {$wpdb->prefix}bm_animals S ON L.sire_id = S.id
                 LEFT JOIN {$wpdb->prefix}bm_animals D ON L.dam_id = D.id
                 WHERE L.id = %d",
                $id
            ),
            ARRAY_A
        );

        if (!$litter) {
            return new WP_Error('not_found', __('Litter not found', 'truepaws'), array('status' => 404));
        }

        return new WP_REST_Response($litter);
    }

    public function update_litter($request) {
        global $wpdb;

        $id = absint($request->get_param('id'));

        $data = array(
            'mating_date' => sanitize_text_field($request->get_param('mating_date')),
            'mating_method' => sanitize_text_field($request->get_param('mating_method')),
            'actual_whelping_date' => sanitize_text_field($request->get_param('actual_whelping_date')),
            'puppy_count_male' => absint($request->get_param('puppy_count_male')),
            'puppy_count_female' => absint($request->get_param('puppy_count_female')),
            'notes' => wp_kses_post($request->get_param('notes'))
        );

        // Recalculate expected date if mating date changed
        if (!empty($data['mating_date'])) {
            $data['expected_whelping_date'] = truepaws_calculate_whelping_date($data['mating_date'], get_option('truepaws_default_species', 'dog'));
        }

        $result = $wpdb->update("{$wpdb->prefix}bm_litters", $data, array('id' => $id));

        if ($result === false) {
            return new WP_Error('db_error', __('Failed to update litter', 'truepaws'), array('status' => 500));
        }

        return new WP_REST_Response(array(
            'message' => __('Litter updated successfully', 'truepaws')
        ));
    }

    public function delete_litter($request) {
        global $wpdb;

        $id = absint($request->get_param('id'));

        $result = $wpdb->delete("{$wpdb->prefix}bm_litters", array('id' => $id));

        if ($result === false) {
            return new WP_Error('db_error', __('Failed to delete litter', 'truepaws'), array('status' => 500));
        }

        return new WP_REST_Response(array(
            'message' => __('Litter deleted successfully', 'truepaws')
        ));
    }

    public function whelp_litter($request) {
        global $wpdb;

        $id = absint($request->get_param('id'));
        $actual_date = sanitize_text_field($request->get_param('actual_date'));
        $male_count = absint($request->get_param('male_count'));
        $female_count = absint($request->get_param('female_count'));

        // Get litter info
        $litter = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}bm_litters WHERE id = %d",
                $id
            ),
            ARRAY_A
        );

        if (!$litter) {
            return new WP_Error('not_found', __('Litter not found', 'truepaws'), array('status' => 404));
        }

        // Start transaction
        $wpdb->query('START TRANSACTION');

        try {
            // Update litter with whelping data
            $update_result = $wpdb->update(
                "{$wpdb->prefix}bm_litters",
                array(
                    'actual_whelping_date' => $actual_date,
                    'puppy_count_male' => $male_count,
                    'puppy_count_female' => $female_count
                ),
                array('id' => $id)
            );

            if ($update_result === false) {
                throw new Exception(__('Failed to update litter', 'truepaws'));
            }

            // Generate puppy names
            $puppy_names = truepaws_generate_puppy_names($litter['litter_name'], $male_count, $female_count);

            $created_animals = array();

            // Create male puppies
            for ($i = 1; $i <= $male_count; $i++) {
                $animal_result = $wpdb->insert("{$wpdb->prefix}bm_animals", array(
                    'name' => $puppy_names['male'][$i - 1],
                    'sex' => 'M',
                    'sire_id' => $litter['sire_id'],
                    'dam_id' => $litter['dam_id'],
                    'birth_date' => $actual_date,
                    'status' => 'active'
                ));

                if ($animal_result === false) {
                    throw new Exception(__('Failed to create puppy', 'truepaws'));
                }

                $animal_id = $wpdb->insert_id;
                $created_animals[] = $animal_id;

                // Create birth event
                $event_result = $wpdb->insert("{$wpdb->prefix}bm_events", array(
                    'animal_id' => $animal_id,
                    'event_type' => 'birth',
                    'event_date' => $actual_date,
                    'title' => __('Birth', 'truepaws'),
                    'created_by' => get_current_user_id()
                ));

                if ($event_result === false) {
                    throw new Exception(__('Failed to create birth event', 'truepaws'));
                }
            }

            // Create female puppies
            for ($i = 1; $i <= $female_count; $i++) {
                $animal_result = $wpdb->insert("{$wpdb->prefix}bm_animals", array(
                    'name' => $puppy_names['female'][$i - 1],
                    'sex' => 'F',
                    'sire_id' => $litter['sire_id'],
                    'dam_id' => $litter['dam_id'],
                    'birth_date' => $actual_date,
                    'status' => 'active'
                ));

                if ($animal_result === false) {
                    throw new Exception(__('Failed to create puppy', 'truepaws'));
                }

                $animal_id = $wpdb->insert_id;
                $created_animals[] = $animal_id;

                // Create birth event
                $event_result = $wpdb->insert("{$wpdb->prefix}bm_events", array(
                    'animal_id' => $animal_id,
                    'event_type' => 'birth',
                    'event_date' => $actual_date,
                    'title' => __('Birth', 'truepaws'),
                    'created_by' => get_current_user_id()
                ));

                if ($event_result === false) {
                    throw new Exception(__('Failed to create birth event', 'truepaws'));
                }
            }

            // Commit transaction
            $wpdb->query('COMMIT');

            // Clear dashboard cache
            delete_transient('truepaws_dashboard_stats');

            $this->fire_webhook('litter_whelped', array(
                'litter_id' => $id,
                'litter_name' => $litter['litter_name'],
                'actual_whelping_date' => $actual_date,
                'male_count' => $male_count,
                'female_count' => $female_count,
                'created_animal_ids' => $created_animals,
            ));

            return new WP_REST_Response(array(
                'message' => sprintf(__('Created %d puppies successfully', 'truepaws'), count($created_animals)),
                'created_animals' => $created_animals,
                'total_created' => count($created_animals)
            ));
        } catch (Exception $e) {
            // Rollback transaction on error
            $wpdb->query('ROLLBACK');
            return new WP_Error('db_error', $e->getMessage(), array('status' => 500));
        }
    }

    /**
     * AI-powered litter name suggestions
     */
    public function suggest_litter_names($request) {
        global $wpdb;

        $id = absint($request->get_param('id'));
        $api_key = get_option('truepaws_gemini_api_key', '');

        if (empty($api_key)) {
            return new WP_REST_Response(array(
                'enabled' => false,
                'suggestions' => array()
            ));
        }

        // Get litter info
        $litter = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT L.*, S.name as sire_name, D.name as dam_name, S.breed as breed
                 FROM {$wpdb->prefix}bm_litters L
                 LEFT JOIN {$wpdb->prefix}bm_animals S ON L.sire_id = S.id
                 LEFT JOIN {$wpdb->prefix}bm_animals D ON L.dam_id = D.id
                 WHERE L.id = %d",
                $id
            ),
            ARRAY_A
        );

        if (!$litter) {
            return new WP_Error('not_found', __('Litter not found', 'truepaws'), array('status' => 404));
        }

        $species = get_option('truepaws_default_species', 'dog');
        $total_puppies = ($litter['puppy_count_male'] ?: 0) + ($litter['puppy_count_female'] ?: 0);

        $prompt = sprintf(
            "Generate %d creative and themed puppy names for a %s litter.\n\nBreed: %s\nSire: %s\nDam: %s\nLitter name: %s\n\nProvide a JSON array with %d name suggestions. Each should be unique, appropriate for the breed, and follow a cohesive theme. Return ONLY the JSON array, no other text.\n\nFormat: [\"Name1\", \"Name2\", ...]",
            max($total_puppies, 6),
            $species,
            $litter['breed'] ?: 'Mixed',
            $litter['sire_name'],
            $litter['dam_name'],
            $litter['litter_name'],
            max($total_puppies, 6)
        );

        $url = add_query_arg('key', $api_key, 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-lite:generateContent');

        $response = wp_remote_post($url, array(
            'timeout' => 30,
            'headers' => array('Content-Type' => 'application/json'),
            'body' => wp_json_encode(array(
                'contents' => array(
                    array(
                        'parts' => array(
                            array('text' => $prompt)
                        )
                    )
                )
            ))
        ));

        if (is_wp_error($response)) {
            return new WP_REST_Response(array(
                'enabled' => true,
                'suggestions' => array(),
                'error' => $response->get_error_message()
            ));
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        if ($code !== 200 || empty($body)) {
            return new WP_REST_Response(array(
                'enabled' => true,
                'suggestions' => array(),
                'error' => __('Failed to get AI response', 'truepaws')
            ));
        }

        $text = '';
        if (isset($body['candidates'][0]['content']['parts'][0]['text'])) {
            $text = $body['candidates'][0]['content']['parts'][0]['text'];
        }

        // Parse JSON from response
        $suggestions = array();
        if (preg_match('/\[.*\]/s', $text, $matches)) {
            $parsed = json_decode($matches[0], true);
            if (is_array($parsed)) {
                $suggestions = $parsed;
            }
        }

        return new WP_REST_Response(array(
            'enabled' => true,
            'suggestions' => $suggestions
        ));
    }

    // Contact endpoints implementation
    public function get_contacts($request) {
        global $wpdb;

        $status = $request->get_param('status');

        $where = array();
        $where_values = array();

        if ($status) {
            $where[] = 'status = %s';
            $where_values[] = $status;
        }

        $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        if (!empty($where_values)) {
            $contacts = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}bm_contacts $where_clause ORDER BY created_at DESC",
                    $where_values
                ),
                ARRAY_A
            );
        } else {
            $contacts = $wpdb->get_results(
                "SELECT * FROM {$wpdb->prefix}bm_contacts ORDER BY created_at DESC",
                ARRAY_A
            );
        }

        // Attach inquiry animals (animals this contact inquired about via shortcode)
        $contacts = $this->attach_inquiry_animals_to_contacts($contacts);

        return new WP_REST_Response(array('contacts' => $contacts));
    }

    /**
     * Attach inquiry_animals array to each contact (animals they inquired about)
     */
    private function attach_inquiry_animals_to_contacts($contacts) {
        global $wpdb;

        if (empty($contacts)) {
            return $contacts;
        }

        $contact_ids = array_map(function ($c) {
            return $c['id'];
        }, $contacts);

        $placeholders = implode(',', array_fill(0, count($contact_ids), '%d'));

        $inquiry_events = $wpdb->get_results($wpdb->prepare(
            "SELECT e.animal_id, a.name as animal_name, a.breed,
                    CAST(JSON_UNQUOTE(COALESCE(JSON_EXTRACT(e.meta_data, '$.contact_id'), '0')) AS UNSIGNED) as contact_id
             FROM {$wpdb->prefix}bm_events e
             LEFT JOIN {$wpdb->prefix}bm_animals a ON e.animal_id = a.id
             WHERE e.event_type = 'note'
             AND JSON_EXTRACT(e.meta_data, '$.inquiry') IS NOT NULL
             AND CAST(JSON_UNQUOTE(COALESCE(JSON_EXTRACT(e.meta_data, '$.contact_id'), '0')) AS UNSIGNED) IN ($placeholders)
             AND e.animal_id IS NOT NULL AND e.animal_id > 0",
            $contact_ids
        ), ARRAY_A);

        $by_contact = array();
        foreach ($inquiry_events as $row) {
            $cid = (int) $row['contact_id'];
            if (!isset($by_contact[$cid])) {
                $by_contact[$cid] = array();
            }
            $by_contact[$cid][] = array(
                'id' => (int) $row['animal_id'],
                'name' => $row['animal_name'],
                'breed' => $row['breed'],
            );
        }

        foreach ($contacts as &$contact) {
            $contact['inquiry_animals'] = isset($by_contact[$contact['id']]) ? $by_contact[$contact['id']] : array();
        }

        return $contacts;
    }

    public function create_contact($request) {
        global $wpdb;

        $data = array(
            'first_name' => sanitize_text_field($request->get_param('first_name')),
            'last_name' => sanitize_text_field($request->get_param('last_name')),
            'email' => sanitize_email($request->get_param('email')),
            'phone' => sanitize_text_field($request->get_param('phone')),
            'address' => sanitize_textarea_field($request->get_param('address')),
            'notes' => wp_kses_post($request->get_param('notes')),
            'status' => $request->get_param('status') ?: 'waitlist'
        );

        $result = $wpdb->insert("{$wpdb->prefix}bm_contacts", $data);

        if ($result === false) {
            return new WP_Error('db_error', __('Failed to create contact', 'truepaws'), array('status' => 500));
        }

        return new WP_REST_Response(array(
            'id' => $wpdb->insert_id,
            'message' => __('Contact created successfully', 'truepaws')
        ), 201);
    }

    /**
     * Submit public inquiry (creates contact, logs event, sends email)
     */
    public function submit_inquiry($request) {
        global $wpdb;

        // Rate limiting: 3 submissions per IP per hour
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $rate_limit_key = 'truepaws_inquiry_rate_' . md5($ip_address);
        $submissions = get_transient($rate_limit_key);
        
        if ($submissions === false) {
            $submissions = 0;
        }
        
        if ($submissions >= 3) {
            return new WP_Error(
                'rate_limit',
                __('Too many submissions. Please try again later.', 'truepaws'),
                array('status' => 429)
            );
        }

        $first_name = sanitize_text_field($request->get_param('first_name'));
        $last_name = sanitize_text_field($request->get_param('last_name'));
        $email = sanitize_email($request->get_param('email'));
        $phone = sanitize_text_field($request->get_param('phone'));
        $message = sanitize_textarea_field($request->get_param('message'));
        $animal_id = absint($request->get_param('animal_id'));

        if (empty($first_name) || empty($email) || empty($message)) {
            return new WP_Error('validation', __('Please fill in all required fields.', 'truepaws'), array('status' => 400));
        }

        if (!is_email($email)) {
            return new WP_Error('validation', __('Please enter a valid email address.', 'truepaws'), array('status' => 400));
        }
        
        // Increment rate limit counter
        set_transient($rate_limit_key, $submissions + 1, HOUR_IN_SECONDS);

        // Create contact
        $contact_data = array(
            'first_name' => $first_name,
            'last_name' => $last_name ?: '',
            'email' => $email,
            'phone' => $phone ?: '',
            'address' => '',
            'notes' => $message,
            'status' => 'waitlist'
        );

        $result = $wpdb->insert("{$wpdb->prefix}bm_contacts", $contact_data);
        if ($result === false) {
            return new WP_Error('db_error', __('Failed to submit inquiry. Please try again.', 'truepaws'), array('status' => 500));
        }

        $contact_id = $wpdb->insert_id;
        $animal = null;

        // If animal_id provided, add inquiry event to that animal
        if ($animal_id > 0) {
            $animal = $wpdb->get_row($wpdb->prepare(
                "SELECT id, name FROM {$wpdb->prefix}bm_animals WHERE id = %d",
                $animal_id
            ), ARRAY_A);

            if ($animal) {
                $meta_data = array(
                    'inquiry' => true,
                    'contact_id' => $contact_id,
                    'message' => $message
                );
                $wpdb->insert("{$wpdb->prefix}bm_events", array(
                    'animal_id' => $animal_id,
                    'event_type' => 'note',
                    'event_date' => current_time('mysql'),
                    'title' => sprintf(__('Inquiry from %s', 'truepaws'), $first_name . ($last_name ? ' ' . $last_name : '')),
                    'meta_data' => wp_json_encode($meta_data),
                    'created_by' => 0
                ));
            }
        }

        // Send email notification to breeder
        $breeder_email = get_option('truepaws_breeder_email', '');
        if (!empty($breeder_email) && is_email($breeder_email)) {
            $animal_name = ($animal && !empty($animal['name'])) ? $animal['name'] : '';
            $subject = sprintf(__('[TruePaws] New inquiry from %s', 'truepaws'), $first_name . ($last_name ? ' ' . $last_name : ''));
            $body = sprintf(
                __("A new inquiry has been submitted:\n\nName: %s %s\nEmail: %s\nPhone: %s\n\nMessage:\n%s\n\n", 'truepaws'),
                $first_name,
                $last_name,
                $email,
                $phone ?: __('(not provided)', 'truepaws'),
                $message
            );
            if ($animal_name) {
                $body .= sprintf(__("Regarding: %s\n", 'truepaws'), $animal_name);
            }
            $body .= "\n" . __('View this contact in TruePaws admin.', 'truepaws');

            wp_mail($breeder_email, $subject, $body, array('Content-Type: text/plain; charset=UTF-8'));
        }

        $this->fire_webhook('inquiry', array(
            'contact_id' => $contact_id,
            'animal_id' => $animal_id > 0 ? $animal_id : null,
            'first_name' => $first_name,
            'last_name' => $last_name,
        ));

        // Smart Inquiry Auto-Responder: generate AI draft for breeder to review
        $draft = $this->generate_inquiry_response_draft($first_name, $last_name, $message, $animal, $animal_id);
        if (!empty($draft)) {
            $draft_section = "\n\n--- " . __('AI Response Draft (review before sending)', 'truepaws') . " ---\n" . $draft;
            $wpdb->update(
                "{$wpdb->prefix}bm_contacts",
                array('notes' => $message . $draft_section),
                array('id' => $contact_id)
            );
        }

        return new WP_REST_Response(array(
            'success' => true,
            'message' => __('Thank you! Your inquiry has been submitted. We will get back to you soon.', 'truepaws')
        ), 201);
    }

    /**
     * Generate AI response draft for inquiry using Gemini
     *
     * @param string $first_name
     * @param string $last_name
     * @param string $message
     * @param array|null $animal Animal data if inquiry was about specific animal
     * @param int $animal_id
     * @return string|null Draft text or null on failure
     */
    private function generate_inquiry_response_draft($first_name, $last_name, $message, $animal, $animal_id) {
        $api_key = get_option('truepaws_gemini_api_key', '');
        if (empty($api_key)) {
            return null;
        }

        global $wpdb;
        $breeder_name = get_option('truepaws_breeder_name', '');
        $business_name = get_option('truepaws_business_name', '');
        $species = get_option('truepaws_default_species', 'dog');
        $species_label = $species === 'dog' ? 'dog' : ($species === 'cat' ? 'cat' : $species);

        // Get count of available animals (active, not sold)
        $available_count = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}bm_animals WHERE status = 'active'"
        );
        $available_info = $available_count > 0
            ? sprintf(__('We currently have %d %s(s) available.', 'truepaws'), $available_count, $species_label)
            : __('We do not have any available at the moment, but we maintain a waitlist.', 'truepaws');

        $animal_context = '';
        if ($animal && !empty($animal['name'])) {
            $animal_context = sprintf(
                __("The inquiry is regarding our %s named %s.", 'truepaws'),
                $species_label,
                $animal['name']
            );
        }

        $prompt = sprintf(
            "You are a professional %s breeder writing a response to a potential customer inquiry. " .
            "Write a friendly, professional, and personalized email response. " .
            "Address the customer by their first name (%s). " .
            "Acknowledge their message and respond helpfully. " .
            "Do NOT include specific pricing - say you would be happy to discuss details. " .
            "Do NOT make promises about availability. " .
            "Keep it warm but professional. " .
            "Sign off as the breeder. " .
            "Write only the email body, no subject line.\n\n" .
            "Breeder/Kennel: %s\n" .
            "Business: %s\n" .
            "Availability: %s\n" .
            "%s\n\n" .
            "Customer's message:\n%s",
            $species_label,
            $first_name,
            $breeder_name ?: __('The Breeder', 'truepaws'),
            $business_name ?: __('Our Kennel', 'truepaws'),
            $available_info,
            $animal_context,
            $message
        );

        $url = add_query_arg('key', $api_key, 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-lite:generateContent');

        $response = wp_remote_post($url, array(
            'timeout' => 15,
            'headers' => array('Content-Type' => 'application/json'),
            'body' => wp_json_encode(array(
                'contents' => array(
                    array(
                        'parts' => array(
                            array('text' => $prompt)
                        )
                    )
                )
            ))
        ));

        if (is_wp_error($response)) {
            return null;
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        if ($code !== 200 || empty($body)) {
            return null;
        }

        if (isset($body['candidates'][0]['content']['parts'][0]['text'])) {
            return $this->clean_ai_response($body['candidates'][0]['content']['parts'][0]['text']);
        }

        return null;
    }

    public function get_contact($request) {
        global $wpdb;

        $id = absint($request->get_param('id'));

        $contact = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}bm_contacts WHERE id = %d",
                $id
            ),
            ARRAY_A
        );

        if (!$contact) {
            return new WP_Error('not_found', __('Contact not found', 'truepaws'), array('status' => 404));
        }

        // Attach inquiry animals (animals this contact inquired about)
        $contact['inquiry_animals'] = $this->get_contact_inquiry_animals($id);

        return new WP_REST_Response($contact);
    }

    /**
     * Get animals a contact inquired about (from inquiry events)
     */
    private function get_contact_inquiry_animals($contact_id) {
        global $wpdb;

        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT e.animal_id, a.name as animal_name, a.breed
             FROM {$wpdb->prefix}bm_events e
             LEFT JOIN {$wpdb->prefix}bm_animals a ON e.animal_id = a.id
             WHERE e.event_type = 'note'
             AND JSON_EXTRACT(e.meta_data, '$.inquiry') IS NOT NULL
             AND CAST(JSON_UNQUOTE(COALESCE(JSON_EXTRACT(e.meta_data, '$.contact_id'), '0')) AS UNSIGNED) = %d
             AND e.animal_id IS NOT NULL AND e.animal_id > 0
             ORDER BY e.event_date DESC",
            $contact_id
        ), ARRAY_A);

        return array_map(function ($row) {
            return array(
                'id' => (int) $row['animal_id'],
                'name' => $row['animal_name'],
                'breed' => $row['breed'],
            );
        }, $rows ?: array());
    }

    /**
     * Get animals purchased by a contact (from sale events)
     */
    public function get_contact_purchases($request) {
        global $wpdb;

        $contact_id = absint($request->get_param('id'));
        $events_table = $wpdb->prefix . 'bm_events';
        $animals_table = $wpdb->prefix . 'bm_animals';

        // Find sale events (event_type=note with sale_type in meta_data) for this contact
        $sale_events = $wpdb->get_results($wpdb->prepare("
            SELECT e.animal_id, e.event_date, e.meta_data
            FROM $events_table e
            WHERE e.event_type = 'note'
            AND JSON_UNQUOTE(COALESCE(JSON_EXTRACT(e.meta_data, '$.sale_type'), '\"\"')) = 'sale'
            AND CAST(JSON_UNQUOTE(COALESCE(JSON_EXTRACT(e.meta_data, '$.contact_id'), '0')) AS UNSIGNED) = %d
            ORDER BY e.event_date DESC
        ", $contact_id), ARRAY_A);

        $purchases = array();
        foreach ($sale_events as $event) {
            $meta = !empty($event['meta_data']) ? json_decode($event['meta_data'], true) : array();
            $animal = $wpdb->get_row($wpdb->prepare(
                "SELECT id, name, call_name, breed, registration_number, birth_date
                FROM $animals_table WHERE id = %d",
                $event['animal_id']
            ), ARRAY_A);
            if ($animal) {
                $animal['sale_date'] = $event['event_date'];
                $animal['sale_price'] = isset($meta['price']) ? $meta['price'] : null;
                $animal['sale_notes'] = isset($meta['notes']) ? $meta['notes'] : null;
                $purchases[] = $animal;
            }
        }

        return new WP_REST_Response(array('purchases' => $purchases));
    }

    public function update_contact($request) {
        global $wpdb;

        $id = absint($request->get_param('id'));

        $data = array(
            'first_name' => sanitize_text_field($request->get_param('first_name')),
            'last_name' => sanitize_text_field($request->get_param('last_name')),
            'email' => sanitize_email($request->get_param('email')),
            'phone' => sanitize_text_field($request->get_param('phone')),
            'address' => sanitize_textarea_field($request->get_param('address')),
            'notes' => wp_kses_post($request->get_param('notes')),
            'status' => $request->get_param('status'),
            'updated_at' => current_time('mysql')
        );

        $result = $wpdb->update("{$wpdb->prefix}bm_contacts", $data, array('id' => $id));

        if ($result === false) {
            return new WP_Error('db_error', __('Failed to update contact', 'truepaws'), array('status' => 500));
        }

        return new WP_REST_Response(array(
            'message' => __('Contact updated successfully', 'truepaws')
        ));
    }

    public function delete_contact($request) {
        global $wpdb;

        $id = absint($request->get_param('id'));

        $result = $wpdb->delete("{$wpdb->prefix}bm_contacts", array('id' => $id));

        if ($result === false) {
            return new WP_Error('db_error', __('Failed to delete contact', 'truepaws'), array('status' => 500));
        }

        return new WP_REST_Response(array(
            'message' => __('Contact deleted successfully', 'truepaws')
        ));
    }

    // Event endpoints implementation
    public function create_event($request) {
        global $wpdb;

        $animal_id = absint($request->get_param('id'));

        $data = array(
            'animal_id' => $animal_id,
            'event_type' => sanitize_text_field($request->get_param('event_type')),
            'event_date' => sanitize_text_field($request->get_param('event_date')),
            'title' => sanitize_text_field($request->get_param('title')),
            'meta_data' => wp_json_encode($request->get_param('meta_data') ?: array()),
            'created_by' => get_current_user_id()
        );

        $result = $wpdb->insert("{$wpdb->prefix}bm_events", $data);

        if ($result === false) {
            return new WP_Error('db_error', __('Failed to create event', 'truepaws'), array('status' => 500));
        }

        $meta_data = $request->get_param('meta_data') ?: array();
        if (!empty($meta_data['sale_type']) && $meta_data['sale_type'] === 'sale') {
            $this->fire_webhook('sale', array(
                'event_id' => $wpdb->insert_id,
                'animal_id' => $animal_id,
                'contact_id' => isset($meta_data['contact_id']) ? absint($meta_data['contact_id']) : null,
                'sale_date' => $data['event_date'],
                'price' => isset($meta_data['price']) ? $meta_data['price'] : null,
            ));
        }

        return new WP_REST_Response(array(
            'id' => $wpdb->insert_id,
            'message' => __('Event created successfully', 'truepaws')
        ), 201);
    }

    public function update_event($request) {
        global $wpdb;

        $id = absint($request->get_param('id'));

        $data = array(
            'event_type' => sanitize_text_field($request->get_param('event_type')),
            'event_date' => sanitize_text_field($request->get_param('event_date')),
            'title' => sanitize_text_field($request->get_param('title')),
            'meta_data' => wp_json_encode($request->get_param('meta_data') ?: array())
        );

        $result = $wpdb->update("{$wpdb->prefix}bm_events", $data, array('id' => $id));

        if ($result === false) {
            return new WP_Error('db_error', __('Failed to update event', 'truepaws'), array('status' => 500));
        }

        return new WP_REST_Response(array(
            'message' => __('Event updated successfully', 'truepaws')
        ));
    }

    public function delete_event($request) {
        global $wpdb;

        $id = absint($request->get_param('id'));

        $result = $wpdb->delete("{$wpdb->prefix}bm_events", array('id' => $id));

        if ($result === false) {
            return new WP_Error('db_error', __('Failed to delete event', 'truepaws'), array('status' => 500));
        }

        return new WP_REST_Response(array(
            'message' => __('Event deleted successfully', 'truepaws')
        ));
    }

    // PDF generation endpoint (returns HTML handover document for download)
    public function generate_handover_pdf($request) {
        $animal_id = absint($request->get_param('id'));

        try {
            $result = TruePaws_PDF_Generator::generate_handover_pdf($animal_id);

            if ($result && (!empty($result['pdf']) || !empty($result['html']))) {
                $response = array(
                    'message' => 'Handover document generated successfully',
                    'filename' => $result['filename']
                );
                if (!empty($result['pdf'])) {
                    $response['pdf'] = $result['pdf'];
                }
                if (!empty($result['html'])) {
                    $response['html'] = $result['html'];
                }
                return new WP_REST_Response($response);
            } else {
                return new WP_Error('pdf_error', __('Failed to generate handover document', 'truepaws'), array('status' => 500));
            }
        } catch (Exception $e) {
            return new WP_Error('pdf_error', __('Error generating handover: ' . $e->getMessage(), 'truepaws'), array('status' => 500));
        }
    }

    /**
     * Generate pedigree certificate PDF
     */
    public function generate_pedigree_pdf($request) {
        $animal_id = absint($request->get_param('id'));

        try {
            $result = TruePaws_PDF_Generator::generate_pedigree_certificate($animal_id);

            if ($result && (!empty($result['pdf']) || !empty($result['html']))) {
                $response = array(
                    'message' => __('Pedigree certificate generated successfully', 'truepaws'),
                    'filename' => $result['filename']
                );
                if (!empty($result['pdf'])) {
                    $response['pdf'] = $result['pdf'];
                }
                if (!empty($result['html'])) {
                    $response['html'] = $result['html'];
                }
                return new WP_REST_Response($response);
            } else {
                return new WP_Error('pdf_error', __('Failed to generate pedigree certificate', 'truepaws'), array('status' => 500));
            }
        } catch (Exception $e) {
            return new WP_Error('pdf_error', __('Error generating pedigree: ' . $e->getMessage(), 'truepaws'), array('status' => 500));
        }
    }

    /**
     * Get dashboard statistics
     */
    public function get_dashboard_stats($request) {
        global $wpdb;
        
        // Check cache first
        $cache_key = 'truepaws_dashboard_stats';
        $cached_stats = get_transient($cache_key);
        
        if ($cached_stats !== false) {
            return rest_ensure_response($cached_stats);
        }
        
        $animals_table = $wpdb->prefix . 'bm_animals';
        $litters_table = $wpdb->prefix . 'bm_litters';
        $contacts_table = $wpdb->prefix . 'bm_contacts';
        $events_table = $wpdb->prefix . 'bm_events';
        
        // Count total animals
        $total_animals = $wpdb->get_var("SELECT COUNT(*) FROM $animals_table");
        
        // Count active litters (not whelped or whelped within last 60 days)
        $active_litters = $wpdb->get_var("
            SELECT COUNT(*) FROM $litters_table
            WHERE actual_whelping_date IS NULL 
            OR actual_whelping_date >= DATE_SUB(NOW(), INTERVAL 60 DAY)
        ");
        
        // Count total contacts
        $total_contacts = $wpdb->get_var("SELECT COUNT(*) FROM $contacts_table");
        
        // Count upcoming events (next 30 days)
        $upcoming_events = $wpdb->get_var("
            SELECT COUNT(*) FROM $events_table 
            WHERE event_date >= CURDATE() AND event_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
        ");

        // Breed distribution (animals by breed)
        $breeds_by_count = $wpdb->get_results("
            SELECT breed, COUNT(*) as count 
            FROM $animals_table 
            WHERE breed IS NOT NULL AND breed != ''
            GROUP BY breed 
            ORDER BY count DESC
            LIMIT 10
        ", ARRAY_A);
        
        $stats = array(
            'success' => true,
            'stats' => array(
                'totalAnimals' => (int) $total_animals,
                'activeLitters' => (int) $active_litters,
                'totalContacts' => (int) $total_contacts,
                'upcomingEvents' => (int) $upcoming_events,
                'breedsByCount' => $breeds_by_count ?: array(),
            )
        );
        
        // Cache for 5 minutes
        set_transient($cache_key, $stats, 5 * MINUTE_IN_SECONDS);
        
        return rest_ensure_response($stats);
    }

    /**
     * Get latest events for dashboard
     */
    public function get_latest_events($request) {
        global $wpdb;
        
        $events_table = $wpdb->prefix . 'bm_events';
        $animals_table = $wpdb->prefix . 'bm_animals';
        $litters_table = $wpdb->prefix . 'bm_litters';
        
        // Get latest 10 events with animal names
        $events = $wpdb->get_results("
            SELECT 
                e.*,
                a.name as animal_name,
                a.id as animal_id,
                a.call_name as animal_call_name
            FROM $events_table e
            LEFT JOIN $animals_table a ON e.animal_id = a.id
            ORDER BY e.event_date DESC, e.created_at DESC
            LIMIT 10
        ", ARRAY_A);
        
        // Also get recent litters (whelping events)
        $recent_litters = $wpdb->get_results("
            SELECT 
                l.*,
                s.name as sire_name,
                d.name as dam_name
            FROM $litters_table l
            LEFT JOIN $animals_table s ON l.sire_id = s.id
            LEFT JOIN $animals_table d ON l.dam_id = d.id
            WHERE l.actual_whelping_date IS NOT NULL AND l.actual_whelping_date != ''
            ORDER BY l.actual_whelping_date DESC
            LIMIT 5
        ", ARRAY_A);
        
        // Format events
        $formatted_events = array();
        
        // Add regular events
        foreach ($events as $event) {
            $formatted_events[] = array(
                'id' => (int) $event['id'],
                'type' => 'event',
                'event_type' => $event['event_type'],
                'title' => $event['title'],
                'date' => $event['event_date'],
                'animal_id' => (int) $event['animal_id'],
                'animal_name' => $event['animal_name'],
                'animal_call_name' => $event['animal_call_name'],
                'meta_data' => json_decode($event['meta_data'], true) ?: array(),
            );
        }
        
        // Add whelping events from litters
        foreach ($recent_litters as $litter) {
            $formatted_events[] = array(
                'id' => 'litter_' . $litter['id'],
                'type' => 'whelping',
                'event_type' => 'whelping',
                'title' => 'Litter Whelped: ' . $litter['litter_name'],
                'date' => $litter['actual_whelping_date'],
                'animal_id' => null,
                'animal_name' => $litter['sire_name'] . ' × ' . $litter['dam_name'],
                'animal_call_name' => null,
                'meta_data' => array(
                    'litter_id' => (int) $litter['id'],
                    'puppy_count' => (int) (($litter['puppy_count_male'] ?? 0) + ($litter['puppy_count_female'] ?? 0)),
                ),
            );
        }
        
        // Sort all events by date (most recent first) and take top 10
        usort($formatted_events, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        
        $formatted_events = array_slice($formatted_events, 0, 10);
        
        return rest_ensure_response(array(
            'success' => true,
            'events' => $formatted_events
        ));
    }

    /**
     * Get sales report (revenue, avg price, sales by month, top buyers)
     */
    public function get_sales_report($request) {
        global $wpdb;

        $events_table = $wpdb->prefix . 'bm_events';
        $animals_table = $wpdb->prefix . 'bm_animals';
        $contacts_table = $wpdb->prefix . 'bm_contacts';

        // Get all sale events with price
        $sales = $wpdb->get_results("
            SELECT 
                e.id, e.animal_id, e.event_date, e.meta_data,
                a.name as animal_name, a.breed
            FROM $events_table e
            LEFT JOIN $animals_table a ON e.animal_id = a.id
            WHERE e.event_type = 'note'
            AND JSON_UNQUOTE(COALESCE(JSON_EXTRACT(e.meta_data, '$.sale_type'), '\"\"')) = 'sale'
            ORDER BY e.event_date DESC
        ", ARRAY_A);

        $total_revenue = 0;
        $sales_with_price = 0;
        $by_month = array();
        $by_contact = array();

        foreach ($sales as $sale) {
            $meta = !empty($sale['meta_data']) ? json_decode($sale['meta_data'], true) : array();
            $price = isset($meta['price']) ? floatval($meta['price']) : 0;
            $contact_id = isset($meta['contact_id']) ? absint($meta['contact_id']) : 0;

            if ($price > 0) {
                $total_revenue += $price;
                $sales_with_price++;

                $month_key = date('Y-m', strtotime($sale['event_date']));
                if (!isset($by_month[$month_key])) {
                    $by_month[$month_key] = array('count' => 0, 'revenue' => 0);
                }
                $by_month[$month_key]['count']++;
                $by_month[$month_key]['revenue'] += $price;

                if ($contact_id > 0) {
                    if (!isset($by_contact[$contact_id])) {
                        $by_contact[$contact_id] = array('count' => 0, 'revenue' => 0);
                    }
                    $by_contact[$contact_id]['count']++;
                    $by_contact[$contact_id]['revenue'] += $price;
                }
            }
        }

        // Build top buyers (contacts with most revenue)
        $top_buyers = array();
        foreach ($by_contact as $cid => $data) {
            $contact = $wpdb->get_row($wpdb->prepare(
                "SELECT first_name, last_name, email FROM $contacts_table WHERE id = %d",
                $cid
            ), ARRAY_A);
            if ($contact) {
                $top_buyers[] = array(
                    'contact_id' => $cid,
                    'name' => trim($contact['first_name'] . ' ' . $contact['last_name']),
                    'email' => $contact['email'],
                    'purchases' => $data['count'],
                    'revenue' => round($data['revenue'], 2)
                );
            }
        }
        usort($top_buyers, function($a, $b) {
            return $b['revenue'] - $a['revenue'];
        });
        $top_buyers = array_slice($top_buyers, 0, 10);

        // Sort by month descending
        krsort($by_month);
        $sales_by_month = array();
        foreach ($by_month as $month => $data) {
            $sales_by_month[] = array(
                'month' => $month,
                'count' => $data['count'],
                'revenue' => round($data['revenue'], 2)
            );
        }
        $sales_by_month = array_slice($sales_by_month, 0, 12);

        return rest_ensure_response(array(
            'success' => true,
            'report' => array(
                'totalSales' => count($sales),
                'totalRevenue' => round($total_revenue, 2),
                'averagePrice' => $sales_with_price > 0 ? round($total_revenue / $sales_with_price, 2) : 0,
                'salesByMonth' => $sales_by_month,
                'topBuyers' => $top_buyers
            )
        ));
    }

    /**
     * Get all settings
     */
    public function get_settings($request) {
        $settings = array(
            'breeder_prefix' => get_option('truepaws_breeder_prefix', 'TP'),
            'default_species' => get_option('truepaws_default_species', 'dog'),
            'pregnancy_days_dog' => get_option('truepaws_pregnancy_days_dog', 63),
            'pregnancy_days_cat' => get_option('truepaws_pregnancy_days_cat', 65),
            'feeding_instructions' => get_option('truepaws_feeding_instructions', ''),
            'breeder_name' => get_option('truepaws_breeder_name', ''),
            'business_name' => get_option('truepaws_business_name', ''),
            'license_number' => get_option('truepaws_license_number', ''),
            'breeder_phone' => get_option('truepaws_breeder_phone', ''),
            'breeder_email' => get_option('truepaws_breeder_email', ''),
            'address_street' => get_option('truepaws_address_street', ''),
            'address_city' => get_option('truepaws_address_city', ''),
            'address_state' => get_option('truepaws_address_state', ''),
            'address_zip' => get_option('truepaws_address_zip', ''),
            'address_country' => get_option('truepaws_address_country', ''),
            'contact_url' => get_option('truepaws_contact_url', '#contact'),
            'webhook_url' => get_option('truepaws_webhook_url', ''),
            'breeds' => $this->get_breeds_array(),
            'gemini_api_key' => $this->get_masked_gemini_api_key(),
        );

        return rest_ensure_response(array(
            'success' => true,
            'settings' => $settings
        ));
    }

    /**
     * Update settings
     */
    public function update_settings($request) {
        $params = $request->get_json_params();

        // Update general settings
        if (isset($params['breeder_prefix'])) {
            update_option('truepaws_breeder_prefix', sanitize_text_field($params['breeder_prefix']));
        }
        if (isset($params['default_species'])) {
            update_option('truepaws_default_species', sanitize_text_field($params['default_species']));
        }
        if (isset($params['pregnancy_days_dog'])) {
            update_option('truepaws_pregnancy_days_dog', absint($params['pregnancy_days_dog']));
        }
        if (isset($params['pregnancy_days_cat'])) {
            update_option('truepaws_pregnancy_days_cat', absint($params['pregnancy_days_cat']));
        }
        if (isset($params['feeding_instructions'])) {
            update_option('truepaws_feeding_instructions', wp_kses_post($params['feeding_instructions']));
        }
        if (isset($params['gemini_api_key']) && $params['gemini_api_key'] !== '' && $params['gemini_api_key'] !== '********') {
            update_option('truepaws_gemini_api_key', sanitize_text_field($params['gemini_api_key']));
        }

        // Update breeder info
        if (isset($params['breeder_name'])) {
            update_option('truepaws_breeder_name', sanitize_text_field($params['breeder_name']));
        }
        if (isset($params['business_name'])) {
            update_option('truepaws_business_name', sanitize_text_field($params['business_name']));
        }
        if (isset($params['license_number'])) {
            update_option('truepaws_license_number', sanitize_text_field($params['license_number']));
        }
        if (isset($params['breeder_phone'])) {
            update_option('truepaws_breeder_phone', sanitize_text_field($params['breeder_phone']));
        }
        if (isset($params['breeder_email'])) {
            update_option('truepaws_breeder_email', sanitize_email($params['breeder_email']));
        }

        // Update address
        if (isset($params['address_street'])) {
            update_option('truepaws_address_street', sanitize_text_field($params['address_street']));
        }
        if (isset($params['address_city'])) {
            update_option('truepaws_address_city', sanitize_text_field($params['address_city']));
        }
        if (isset($params['address_state'])) {
            update_option('truepaws_address_state', sanitize_text_field($params['address_state']));
        }
        if (isset($params['address_zip'])) {
            update_option('truepaws_address_zip', sanitize_text_field($params['address_zip']));
        }
        if (isset($params['address_country'])) {
            update_option('truepaws_address_country', sanitize_text_field($params['address_country']));
        }
        if (isset($params['contact_url'])) {
            update_option('truepaws_contact_url', esc_url_raw($params['contact_url']));
        }
        if (isset($params['webhook_url'])) {
            update_option('truepaws_webhook_url', esc_url_raw($params['webhook_url']));
        }

        return rest_ensure_response(array(
            'success' => true,
            'message' => __('Settings updated successfully', 'truepaws')
        ));
    }

    /**
     * Fire webhook to configured URL (Zapier, Make, etc.)
     *
     * @param string $event Event type: inquiry, sale, litter_whelped
     * @param array  $payload Event data (no sensitive secrets)
     */
    private function fire_webhook($event, $payload = array()) {
        $url = get_option('truepaws_webhook_url', '');
        if (empty($url) || !wp_http_validate_url($url)) {
            return;
        }
        $body = array_merge(
            array(
                'event' => $event,
                'timestamp' => current_time('c'),
                'source' => 'truepaws',
            ),
            $payload
        );
        wp_remote_post($url, array(
            'timeout' => 15,
            'blocking' => false,
            'headers' => array('Content-Type' => 'application/json'),
            'body' => wp_json_encode($body),
        ));
    }

    /**
     * Strip conversational preamble from Gemini AI responses so the output
     * is clean, presentation-ready text (no "Okay, here's…" openers).
     */
    private function clean_ai_response($text) {
        if (empty($text)) {
            return $text;
        }

        $text = trim($text);

        $preamble_patterns = array(
            '/^(?:Okay|Ok|Sure|Certainly|Of course|Absolutely|Great|Alright|Right)[,!.]?\s*/i',
            '/^(?:Here(?:\'s| is| are)|I\'ve (?:prepared|compiled|put together|created|generated))\s+[^.:\n]{0,120}[.:]\s*/i',
            '/^(?:Below (?:is|are)|The following (?:is|are)|Let me (?:provide|share|give))\s+[^.:\n]{0,120}[.:]\s*/i',
            '/^(?:This is|These are)\s+[^.:\n]{0,80}[.:]\s*/i',
        );

        $max_passes = 3;
        for ($i = 0; $i < $max_passes; $i++) {
            $changed = false;
            foreach ($preamble_patterns as $pattern) {
                $cleaned = preg_replace($pattern, '', $text);
                if ($cleaned !== $text) {
                    $text = ltrim($cleaned);
                    $changed = true;
                    break;
                }
            }
            if (!$changed) {
                break;
            }
        }

        return $text;
    }

    /**
     * Get masked Gemini API key for display (never expose real key)
     */
    private function get_masked_gemini_api_key() {
        $key = get_option('truepaws_gemini_api_key', '');
        return $key !== '' ? '********' : '';
    }

    /**
     * Get breeds array from option
     */
    private function get_breeds_array() {
        $breeds_json = get_option('truepaws_breeds', '[]');
        $breeds = json_decode($breeds_json, true);
        return is_array($breeds) ? $breeds : array();
    }

    /**
     * Get breeds list
     */
    public function get_breeds($request) {
        $breeds = $this->get_breeds_array();
        return rest_ensure_response(array(
            'success' => true,
            'breeds' => $breeds
        ));
    }

    /**
     * Add new breed
     */
    public function add_breed($request) {
        $params = $request->get_json_params();
        $breed_name = isset($params['name']) ? sanitize_text_field($params['name']) : '';

        if (empty($breed_name)) {
            return new WP_Error('invalid_breed', __('Breed name is required', 'truepaws'), array('status' => 400));
        }

        $breeds = $this->get_breeds_array();

        // Check for duplicates
        foreach ($breeds as $breed) {
            if (strtolower($breed['name']) === strtolower($breed_name)) {
                return new WP_Error('duplicate_breed', __('Breed already exists', 'truepaws'), array('status' => 400));
            }
        }

        // Add new breed
        $new_id = !empty($breeds) ? max(array_column($breeds, 'id')) + 1 : 1;
        $breeds[] = array(
            'id' => $new_id,
            'name' => $breed_name
        );

        update_option('truepaws_breeds', json_encode($breeds));

        return rest_ensure_response(array(
            'success' => true,
            'breed' => array('id' => $new_id, 'name' => $breed_name),
            'message' => __('Breed added successfully', 'truepaws')
        ));
    }

    /**
     * Delete breed
     */
    public function delete_breed($request) {
        $id = absint($request->get_param('id'));

        if (!$id) {
            return new WP_Error('invalid_id', __('Invalid breed ID', 'truepaws'), array('status' => 400));
        }

        $breeds = $this->get_breeds_array();
        $breeds = array_filter($breeds, function($breed) use ($id) {
            return (int) $breed['id'] !== $id;
        });

        // Re-index array
        $breeds = array_values($breeds);

        update_option('truepaws_breeds', json_encode($breeds));

        return rest_ensure_response(array(
            'success' => true,
            'message' => __('Breed deleted successfully', 'truepaws')
        ));
    }

    /**
     * Get activity heatmap data (event counts per day for the past year)
     */
    public function get_activity_heatmap($request) {
        global $wpdb;

        $events_table = $wpdb->prefix . 'bm_events';
        $one_year_ago = date('Y-m-d', strtotime('-365 days'));

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT DATE(event_date) as event_day, COUNT(*) as event_count
             FROM $events_table
             WHERE event_date >= %s
             GROUP BY DATE(event_date)
             ORDER BY event_day ASC",
            $one_year_ago
        ), ARRAY_A);

        $activity = array();
        if ($results) {
            foreach ($results as $row) {
                $activity[$row['event_day']] = (int) $row['event_count'];
            }
        }

        return rest_ensure_response(array(
            'success' => true,
            'activity' => $activity,
        ));
    }

    /**
     * Generate AI marketing bio for an animal via Gemini API
     */
    public function get_animal_marketing_bio($request) {
        global $wpdb;

        $id = absint($request->get_param('id'));
        $api_key = get_option('truepaws_gemini_api_key', '');

        if (empty($api_key)) {
            return new WP_REST_Response(array(
                'enabled' => false,
                'message' => __('Configure Gemini API key in Settings to enable AI features.', 'truepaws')
            ));
        }

        $animal = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT a.*, s.name as sire_name, d.name as dam_name
                 FROM {$wpdb->prefix}bm_animals a
                 LEFT JOIN {$wpdb->prefix}bm_animals s ON a.sire_id = s.id
                 LEFT JOIN {$wpdb->prefix}bm_animals d ON a.dam_id = d.id
                 WHERE a.id = %d",
                $id
            ),
            ARRAY_A
        );

        if (!$animal) {
            return new WP_Error('not_found', __('Animal not found', 'truepaws'), array('status' => 404));
        }

        $species = get_option('truepaws_default_species', 'dog');
        $species_labels = array(
            'dog' => 'dog', 'cat' => 'cat', 'horse' => 'horse',
            'rabbit' => 'rabbit', 'guinea_pig' => 'guinea pig',
            'ferret' => 'ferret', 'bird' => 'bird'
        );
        $species_label = isset($species_labels[$species]) ? $species_labels[$species] : $species;
        $breed = !empty($animal['breed']) ? $animal['breed'] : 'Unknown breed';
        $sex = ($animal['sex'] === 'M') ? 'Male' : 'Female';
        $name = $animal['name'];
        $color = !empty($animal['color_markings']) ? $animal['color_markings'] : '';
        $sire = !empty($animal['sire_name']) ? $animal['sire_name'] : '';
        $dam = !empty($animal['dam_name']) ? $animal['dam_name'] : '';
        $description = !empty($animal['description']) ? $animal['description'] : '';

        $age_str = '';
        if (!empty($animal['birth_date'])) {
            $birth = new DateTime($animal['birth_date']);
            $now = new DateTime();
            $diff = $now->diff($birth);
            if ($diff->days < 30) {
                $age_str = $diff->days . ' days old';
            } elseif ($diff->days < 365) {
                $weeks = (int) ($diff->days / 7);
                $age_str = $weeks . ' weeks old';
            } else {
                $age_str = $diff->y . ' year(s) old';
            }
        }

        $prompt = sprintf(
            "Write a warm, professional, and appealing marketing description for a %s available from a reputable breeder. This will be used on the breeder's website.\n\nDetails:\n- Name: %s\n- Breed: %s\n- Sex: %s\n- Age: %s\n- Color/Markings: %s\n- Sire (father): %s\n- Dam (mother): %s\n- Description/notes: %s\n\nWrite 2-3 short paragraphs (150-200 words total). Be warm and inviting but professional. Highlight the breed's qualities, the animal's lineage if known, and make potential buyers excited. Do NOT use markdown formatting. Write plain text only. IMPORTANT: Do NOT start with any introductory or conversational sentence (e.g. \"Okay, here's...\", \"Here is...\", \"Sure, ...\"). Begin directly with the marketing description.",
            $species_label, $name, $breed, $sex,
            $age_str ?: 'not specified',
            $color ?: 'not specified',
            $sire ?: 'not specified',
            $dam ?: 'not specified',
            $description ?: 'none'
        );

        $url = add_query_arg('key', $api_key, 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-lite:generateContent');

        $response = wp_remote_post($url, array(
            'timeout' => 30,
            'headers' => array('Content-Type' => 'application/json'),
            'body' => wp_json_encode(array(
                'contents' => array(
                    array(
                        'parts' => array(
                            array('text' => $prompt)
                        )
                    )
                )
            ))
        ));

        if (is_wp_error($response)) {
            return new WP_REST_Response(array(
                'enabled' => true,
                'content' => null,
                'error' => $response->get_error_message()
            ));
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        if ($code !== 200 || empty($body)) {
            $error_msg = isset($body['error']['message']) ? $body['error']['message'] : __('Failed to get AI response', 'truepaws');
            return new WP_REST_Response(array(
                'enabled' => true,
                'content' => null,
                'error' => $error_msg
            ));
        }

        $text = '';
        if (isset($body['candidates'][0]['content']['parts'][0]['text'])) {
            $text = $this->clean_ai_response($body['candidates'][0]['content']['parts'][0]['text']);
        }

        return new WP_REST_Response(array(
            'enabled' => true,
            'content' => $text,
        ));
    }
}