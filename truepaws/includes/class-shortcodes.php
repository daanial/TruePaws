<?php
/**
 * Public shortcodes for TruePaws
 */

if (!defined('ABSPATH')) {
    exit;
}

class TruePaws_Shortcodes {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_public_scripts'));
        add_shortcode('truepaws_litter', array($this, 'litter_shortcode'));
        add_shortcode('truepaws_animal', array($this, 'animal_shortcode'));
        add_shortcode('truepaws_available_puppies', array($this, 'available_puppies_shortcode'));
        add_shortcode('truepaws_inquiry_form', array($this, 'inquiry_form_shortcode'));
    }

    /**
     * Enqueue public scripts and styles
     */
    public function enqueue_public_scripts() {
        // Only enqueue on pages with shortcodes
        global $post;
        if (!is_a($post, 'WP_Post') || (!has_shortcode($post->post_content, 'truepaws_litter') && !has_shortcode($post->post_content, 'truepaws_animal') && !has_shortcode($post->post_content, 'truepaws_available_puppies') && !has_shortcode($post->post_content, 'truepaws_inquiry_form'))) {
            return;
        }

        wp_enqueue_style(
            'truepaws-public',
            TRUEPAWS_PLUGIN_URL . 'assets/src/styles/public.css',
            array(),
            TRUEPAWS_VERSION
        );
    }

    /**
     * Litter shortcode
     * Usage: [truepaws_litter id="123"]
     */
    public function litter_shortcode($atts) {
        $atts = shortcode_atts(array(
            'id' => 0,
        ), $atts, 'truepaws_litter');

        $litter_id = absint($atts['id']);

        if (!$litter_id) {
            return '<div class="truepaws-error">' . __('Invalid litter ID.', 'truepaws') . '</div>';
        }

        global $wpdb;

        // Get litter information
        $litter = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT L.*, S.name as sire_name, D.name as dam_name
                 FROM {$wpdb->prefix}bm_litters L
                 LEFT JOIN {$wpdb->prefix}bm_animals S ON L.sire_id = S.id
                 LEFT JOIN {$wpdb->prefix}bm_animals D ON L.dam_id = D.id
                 WHERE L.id = %d",
                $litter_id
            ),
            ARRAY_A
        );

        if (!$litter) {
            return '<div class="truepaws-error">' . __('Litter not found.', 'truepaws') . '</div>';
        }

        // Get available puppies
        $puppies = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT A.id, A.name, A.sex, A.birth_date, A.featured_image_id, A.breed, A.color_markings
                 FROM {$wpdb->prefix}bm_animals A
                 WHERE A.sire_id = %d AND A.dam_id = %d AND A.birth_date = %s AND A.status = 'active'",
                $litter['sire_id'],
                $litter['dam_id'],
                $litter['actual_whelping_date']
            ),
            ARRAY_A
        );

        ob_start();
        ?>
        <div class="truepaws-litter">
            <div class="litter-header">
                <h3><?php printf(__('Litter: %s', 'truepaws'), esc_html($litter['litter_name'])); ?></h3>
                <div class="litter-meta">
                    <span class="litter-parents">
                        <?php echo esc_html($litter['sire_name'] . ' × ' . $litter['dam_name']); ?>
                    </span>
                    <span class="litter-born">
                        <?php printf(__('Born: %s', 'truepaws'), esc_html(date_i18n(get_option('date_format'), strtotime($litter['actual_whelping_date'])))); ?>
                    </span>
                </div>
            </div>

            <?php if (!empty($puppies)): ?>
                <div class="puppies-grid">
                    <?php foreach ($puppies as $puppy): ?>
                        <div class="puppy-card">
                            <?php if ($puppy['featured_image_id']): ?>
                                <div class="puppy-image">
                                    <?php echo wp_get_attachment_image($puppy['featured_image_id'], 'medium', false, array('alt' => esc_attr($puppy['name']))); ?>
                                </div>
                            <?php else: ?>
                                <div class="puppy-no-image">
                                    <span><?php _e('No Photo', 'truepaws'); ?></span>
                                </div>
                            <?php endif; ?>

                            <div class="puppy-info">
                                <h4><?php echo esc_html($puppy['name']); ?></h4>
                                <div class="puppy-details">
                                    <span class="puppy-sex"><?php echo $puppy['sex'] === 'M' ? __('Male', 'truepaws') : __('Female', 'truepaws'); ?></span>
                                    <span class="puppy-breed"><?php echo esc_html($puppy['breed'] ?: __('Mixed Breed', 'truepaws')); ?></span>
                                </div>
                                <?php if (!empty($puppy['color_markings'])): ?>
                                    <p class="puppy-description"><?php echo esc_html(wp_trim_words($puppy['color_markings'], 10)); ?></p>
                                <?php endif; ?>
                                <a href="#contact" class="contact-button"><?php _e('Contact Us', 'truepaws'); ?></a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-puppies">
                    <p><?php _e('No puppies available from this litter at this time.', 'truepaws'); ?></p>
                </div>
            <?php endif; ?>
        </div>
        <?php

        return ob_get_clean();
    }

    /**
     * Format AI content for HTML output (sections, bold, lists)
     */
    private function format_ai_content($text) {
        if (empty($text)) {
            return '';
        }
        $escape = function ($s) {
            return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
        };
        $apply_bold = function ($s) {
            return preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $s);
        };
        $lines = explode("\n", $text);
        $sections = array();
        $current = array('title' => null, 'bullets' => array(), 'paragraphs' => array());

        $flush = function () use (&$current, &$sections) {
            if ($current['title'] || !empty($current['bullets']) || !empty($current['paragraphs'])) {
                $sections[] = $current;
                $current = array('title' => null, 'bullets' => array(), 'paragraphs' => array());
            }
        };

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                $flush();
                continue;
            }
            if (preg_match('/^\*\*(.+?)\*\*:?\s*$/', $trimmed, $m)) {
                $flush();
                $current['title'] = $apply_bold($escape($m[1]));
            } elseif (preg_match('/^(\d+)[\.\)]\s*(.+)/', $trimmed, $m)) {
                $flush();
                $current['title'] = $apply_bold($escape(trim($m[2])));
            } elseif (preg_match('/^[-•*]\s*(.+)/', $trimmed, $m)) {
                $current['bullets'][] = $apply_bold($escape($m[1]));
            } elseif (empty($current['bullets']) && !$current['title']) {
                $current['title'] = $apply_bold($escape($trimmed));
            } else {
                $current['paragraphs'][] = $apply_bold($escape($trimmed));
            }
        }
        $flush();

        $html = '';
        foreach ($sections as $sec) {
            $html .= '<section class="ai-section">';
            if ($sec['title']) {
                $html .= '<h5 class="ai-section-title">' . $sec['title'] . '</h5>';
            }
            if (!empty($sec['paragraphs'])) {
                foreach ($sec['paragraphs'] as $p) {
                    $html .= '<p class="ai-section-p">' . $p . '</p>';
                }
            }
            if (!empty($sec['bullets'])) {
                $html .= '<ul class="ai-section-list">';
                foreach ($sec['bullets'] as $li) {
                    $html .= '<li>' . $li . '</li>';
                }
                $html .= '</ul>';
            }
            $html .= '</section>';
        }
        return $html;
    }

    /**
     * Animal detail shortcode (ADP-like for public sales pages)
     * Usage: [truepaws_animal id="456"] or [truepaws_animal id="456" contact_url="/contact" show_ai="true" show_pedigree="true"]
     */
    public function animal_shortcode($atts) {
        $atts = shortcode_atts(array(
            'id' => 0,
            'contact_url' => '',
            'show_ai' => 'true',
            'show_pedigree' => 'true',
            'show_inquiry_form' => 'false',
        ), $atts, 'truepaws_animal');

        $animal_id = absint($atts['id']);

        if (!$animal_id) {
            return '<div class="truepaws-animal-detail truepaws-error">' . __('Invalid animal ID.', 'truepaws') . '</div>';
        }

        global $wpdb;

        $animal = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT A.*, S.name as sire_name, D.name as dam_name
                 FROM {$wpdb->prefix}bm_animals A
                 LEFT JOIN {$wpdb->prefix}bm_animals S ON A.sire_id = S.id
                 LEFT JOIN {$wpdb->prefix}bm_animals D ON A.dam_id = D.id
                 WHERE A.id = %d",
                $animal_id
            ),
            ARRAY_A
        );

        if (!$animal) {
            return '<div class="truepaws-animal-detail truepaws-error">' . __('Animal not found.', 'truepaws') . '</div>';
        }

        $contact_url = !empty($atts['contact_url']) ? esc_url($atts['contact_url']) : esc_url(get_option('truepaws_contact_url', '#contact'));
        $show_ai = $atts['show_ai'] === 'true';
        $show_pedigree = $atts['show_pedigree'] === 'true';
        $show_inquiry_form = $atts['show_inquiry_form'] === 'true';

        $ai_content = '';
        if ($show_ai) {
            $cached = get_transient('truepaws_ai_advice_' . $animal_id);
            if ($cached && !empty($cached['content'])) {
                $ai_content = $this->format_ai_content($cached['content']);
            }
        }

        ob_start();
        ?>
        <div class="truepaws-animal-detail truepaws-animal">
            <div class="animal-detail-header">
                <div class="animal-detail-image">
                    <?php if (!empty($animal['featured_image_id'])): ?>
                        <?php echo wp_get_attachment_image($animal['featured_image_id'], 'large', false, array('alt' => esc_attr($animal['name']))); ?>
                    <?php else: ?>
                        <div class="animal-no-image">
                            <span><?php _e('No Photo', 'truepaws'); ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="animal-detail-info">
                    <h2 class="animal-detail-name"><?php echo esc_html($animal['name']); ?></h2>
                    <?php if (!empty($animal['call_name'])): ?>
                        <p class="animal-call-name"><?php echo esc_html('"' . $animal['call_name'] . '"'); ?></p>
                    <?php endif; ?>
                    <div class="animal-detail-meta">
                        <p><strong><?php _e('Breed:', 'truepaws'); ?></strong> <?php echo esc_html($animal['breed'] ?: __('Not specified', 'truepaws')); ?></p>
                        <p><strong><?php _e('Sex:', 'truepaws'); ?></strong> <?php echo $animal['sex'] === 'M' ? __('Male', 'truepaws') : __('Female', 'truepaws'); ?></p>
                        <?php if (!empty($animal['birth_date'])): ?>
                            <p><strong><?php _e('Born:', 'truepaws'); ?></strong> <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($animal['birth_date']))); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($animal['registration_number'])): ?>
                            <p><strong><?php _e('Registration:', 'truepaws'); ?></strong> <?php echo esc_html($animal['registration_number']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($animal['microchip_id'])): ?>
                            <p><strong><?php _e('Microchip:', 'truepaws'); ?></strong> <?php echo esc_html($animal['microchip_id']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if (!empty($animal['sire_name']) || !empty($animal['dam_name'])): ?>
                <div class="animal-detail-parents">
                    <h4><?php _e('Parents', 'truepaws'); ?></h4>
                    <div class="parents-grid">
                        <div class="parent"><strong><?php _e('Sire:', 'truepaws'); ?></strong> <?php echo esc_html($animal['sire_name'] ?: __('Unknown', 'truepaws')); ?></div>
                        <div class="parent"><strong><?php _e('Dam:', 'truepaws'); ?></strong> <?php echo esc_html($animal['dam_name'] ?: __('Unknown', 'truepaws')); ?></div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($animal['color_markings'])): ?>
                <div class="animal-detail-description">
                    <h4><?php _e('Description', 'truepaws'); ?></h4>
                    <p><?php echo esc_html($animal['color_markings']); ?></p>
                </div>
            <?php endif; ?>

            <?php if ($ai_content): ?>
                <div class="animal-detail-ai">
                    <h4><?php _e('AI Information', 'truepaws'); ?></h4>
                    <div class="ai-content"><?php echo $ai_content; ?></div>
                </div>
            <?php endif; ?>

            <?php if ($show_pedigree && (function_exists('truepaws_get_simple_pedigree'))): ?>
                <?php
                $pedigree = truepaws_get_simple_pedigree($animal_id, 2);
                if ($pedigree): ?>
                    <div class="animal-detail-pedigree">
                        <h4><?php _e('Pedigree', 'truepaws'); ?></h4>
                        <div class="pedigree-preview">
                            <p><strong><?php echo esc_html($pedigree['animal']['name']); ?></strong></p>
                            <?php if (!empty($pedigree['sire'])): ?>
                                <p><?php _e('Sire:', 'truepaws'); ?> <?php echo esc_html($pedigree['sire']['name']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($pedigree['dam'])): ?>
                                <p><?php _e('Dam:', 'truepaws'); ?> <?php echo esc_html($pedigree['dam']['name']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="animal-detail-cta">
                <?php if ($show_inquiry_form): ?>
                    <?php echo $this->inquiry_form_shortcode(array('animal_id' => $animal_id, 'title' => sprintf(__('Inquire about %s', 'truepaws'), esc_html($animal['name'])))); ?>
                <?php else: ?>
                    <a href="<?php echo $contact_url; ?>" class="contact-button"><?php _e('Inquire / Contact', 'truepaws'); ?></a>
                <?php endif; ?>
            </div>
        </div>
        <?php

        return ob_get_clean();
    }

    /**
     * Available puppies shortcode
     * Usage: [truepaws_available_puppies]
     */
    public function available_puppies_shortcode($atts) {
        $atts = shortcode_atts(array(
            'limit' => 12,
            'breed' => '',
            'show_images' => 'true'
        ), $atts, 'truepaws_available_puppies');

        $limit = absint($atts['limit']);
        $breed = sanitize_text_field($atts['breed']);
        $show_images = $atts['show_images'] === 'true';

        global $wpdb;

        $where = array("A.status = 'active'");
        $where_values = array();

        if ($breed) {
            $where[] = "A.breed LIKE %s";
            $where_values[] = '%' . $wpdb->esc_like($breed) . '%';
        }

        $where_clause = implode(' AND ', $where);

        $puppies = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT A.id, A.name, A.sex, A.breed, A.birth_date, A.featured_image_id, A.color_markings,
                        L.litter_name, S.name as sire_name, D.name as dam_name
                 FROM {$wpdb->prefix}bm_animals A
                 LEFT JOIN {$wpdb->prefix}bm_litters L ON A.sire_id = L.sire_id AND A.dam_id = L.dam_id AND A.birth_date = L.actual_whelping_date
                 LEFT JOIN {$wpdb->prefix}bm_animals S ON A.sire_id = S.id
                 LEFT JOIN {$wpdb->prefix}bm_animals D ON A.dam_id = D.id
                 WHERE $where_clause
                 ORDER BY A.birth_date DESC, A.name ASC
                 LIMIT %d",
                array_merge($where_values, array($limit))
            ),
            ARRAY_A
        );

        if (empty($puppies)) {
            return '<div class="no-puppies-available">' . __('No puppies available at this time.', 'truepaws') . '</div>';
        }

        ob_start();
        ?>
        <div class="truepaws-available-puppies">
            <div class="puppies-grid">
                <?php foreach ($puppies as $puppy): ?>
                    <div class="puppy-card">
                        <?php if ($show_images && $puppy['featured_image_id']): ?>
                            <div class="puppy-image">
                                <?php echo wp_get_attachment_image($puppy['featured_image_id'], 'medium', false, array('alt' => esc_attr($puppy['name']))); ?>
                            </div>
                        <?php elseif ($show_images): ?>
                            <div class="puppy-no-image">
                                <span><?php _e('No Photo', 'truepaws'); ?></span>
                            </div>
                        <?php endif; ?>

                        <div class="puppy-info">
                            <h4><?php echo esc_html($puppy['name']); ?></h4>
                            <div class="puppy-details">
                                <span class="puppy-sex"><?php echo $puppy['sex'] === 'M' ? __('Male', 'truepaws') : __('Female', 'truepaws'); ?></span>
                                <span class="puppy-breed"><?php echo esc_html($puppy['breed'] ?: __('Mixed Breed', 'truepaws')); ?></span>
                            </div>
                            <?php if (!empty($puppy['litter_name'])): ?>
                                <p class="puppy-litter"><?php printf(__('From litter: %s', 'truepaws'), esc_html($puppy['litter_name'])); ?></p>
                            <?php endif; ?>
                            <a href="#contact" class="contact-button"><?php _e('Contact Us', 'truepaws'); ?></a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php

        return ob_get_clean();
    }

    /**
     * Public inquiry form shortcode
     * Usage: [truepaws_inquiry_form] or [truepaws_inquiry_form animal_id="456"]
     */
    public function inquiry_form_shortcode($atts) {
        $atts = shortcode_atts(array(
            'animal_id' => 0,
            'title' => __('Send an Inquiry', 'truepaws'),
        ), $atts, 'truepaws_inquiry_form');

        $animal_id = absint($atts['animal_id']);
        $title = sanitize_text_field($atts['title']);
        $api_url = rest_url('truepaws/v1/inquiries');
        $nonce = wp_create_nonce('wp_rest');

        ob_start();
        ?>
        <div class="truepaws-inquiry-form-wrapper" id="truepaws-inquiry-form-<?php echo esc_attr($animal_id ?: 'general'); ?>">
            <form class="truepaws-inquiry-form" method="post" data-api-url="<?php echo esc_url($api_url); ?>" data-nonce="<?php echo esc_attr($nonce); ?>">
                <?php if ($title): ?>
                    <h3 class="inquiry-form-title"><?php echo esc_html($title); ?></h3>
                <?php endif; ?>

                <?php if ($animal_id > 0): ?>
                    <input type="hidden" name="animal_id" value="<?php echo esc_attr($animal_id); ?>">
                <?php endif; ?>

                <div class="inquiry-form-row">
                    <div class="inquiry-form-group">
                        <label for="tp-inq-first"><?php _e('First Name', 'truepaws'); ?> <span class="required">*</span></label>
                        <input type="text" id="tp-inq-first" name="first_name" required>
                    </div>
                    <div class="inquiry-form-group">
                        <label for="tp-inq-last"><?php _e('Last Name', 'truepaws'); ?></label>
                        <input type="text" id="tp-inq-last" name="last_name">
                    </div>
                </div>

                <div class="inquiry-form-group">
                    <label for="tp-inq-email"><?php _e('Email', 'truepaws'); ?> <span class="required">*</span></label>
                    <input type="email" id="tp-inq-email" name="email" required>
                </div>

                <div class="inquiry-form-group">
                    <label for="tp-inq-phone"><?php _e('Phone', 'truepaws'); ?></label>
                    <input type="tel" id="tp-inq-phone" name="phone">
                </div>

                <div class="inquiry-form-group">
                    <label for="tp-inq-message"><?php _e('Message', 'truepaws'); ?> <span class="required">*</span></label>
                    <textarea id="tp-inq-message" name="message" rows="5" required></textarea>
                </div>

                <div class="inquiry-form-actions">
                    <button type="submit" class="inquiry-submit-btn">
                        <span class="btn-text"><?php _e('Send Inquiry', 'truepaws'); ?></span>
                        <span class="btn-loading" style="display:none;"><?php _e('Sending...', 'truepaws'); ?></span>
                    </button>
                </div>

                <div class="inquiry-form-message" style="display:none;"></div>
            </form>
        </div>

        <script>
        (function() {
            var form = document.querySelector('.truepaws-inquiry-form');
            if (!form) return;
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                var btn = form.querySelector('.inquiry-submit-btn');
                var msgEl = form.querySelector('.inquiry-form-message');
                var btnText = btn.querySelector('.btn-text');
                var btnLoading = btn.querySelector('.btn-loading');

                btn.disabled = true;
                btnText.style.display = 'none';
                btnLoading.style.display = 'inline';
                msgEl.style.display = 'none';
                msgEl.className = 'inquiry-form-message';

                var formData = new FormData(form);
                var data = {
                    first_name: formData.get('first_name'),
                    last_name: formData.get('last_name'),
                    email: formData.get('email'),
                    phone: formData.get('phone') || '',
                    message: formData.get('message'),
                    animal_id: formData.get('animal_id') ? parseInt(formData.get('animal_id'), 10) : 0
                };

                fetch(form.dataset.apiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': form.dataset.nonce || ''
                    },
                    body: JSON.stringify(data)
                })
                .then(function(r) { return r.json().then(function(j) { return { ok: r.ok, json: j }; }); })
                .then(function(res) {
                    if (res.ok && res.json.success) {
                        msgEl.textContent = res.json.message || '<?php echo esc_js(__('Thank you! Your inquiry has been submitted.', 'truepaws')); ?>';
                        msgEl.className = 'inquiry-form-message success';
                        form.reset();
                    } else {
                        msgEl.textContent = (res.json.message || res.json.code || '<?php echo esc_js(__('Something went wrong. Please try again.', 'truepaws')); ?>');
                        msgEl.className = 'inquiry-form-message error';
                    }
                    msgEl.style.display = 'block';
                })
                .catch(function() {
                    msgEl.textContent = '<?php echo esc_js(__('Something went wrong. Please try again.', 'truepaws')); ?>';
                    msgEl.className = 'inquiry-form-message error';
                    msgEl.style.display = 'block';
                })
                .finally(function() {
                    btn.disabled = false;
                    btnText.style.display = 'inline';
                    btnLoading.style.display = 'none';
                });
            });
        })();
        </script>
        <?php

        return ob_get_clean();
    }
}