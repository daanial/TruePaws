<?php
/**
 * PDF generation for pedigrees and handover packets
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * TruePaws PDF Generator Class
 */
class TruePaws_PDF_Generator {

    /**
     * Constructor
     */
    public function __construct() {
        // Check if TCPDF is available
        if (!class_exists('TCPDF')) {
            add_action('admin_notices', array($this, 'tcpdf_missing_notice'));
        }
    }

    /**
     * Generate pedigree PDF
     *
     * @param int $animal_id
     * @param int $generations
     * @return string|false PDF file path or false on error
     */
    public function generate_pedigree_pdf($animal_id, $generations = 3) {
        // TODO: Implement in Phase 5
        return false;
    }

    /**
     * Generate handover packet PDF
     *
     * @param int $animal_id
     * @return string|false PDF file path or false on error
     */
    public function generate_handover_pdf($animal_id) {
        // TODO: Implement in Phase 8
        return false;
    }

    /**
     * TCPDF missing notice
     */
    public function tcpdf_missing_notice() {
        echo '<div class="notice notice-warning"><p>';
        echo __('TruePaws PDF generation requires TCPDF library. Please install TCPDF to enable PDF features.', TRUEPAWS_TEXT_DOMAIN);
        echo '</p></div>';
    }
}