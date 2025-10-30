<?php
/**
 * AJAX Handler Class
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class GPB_Ajax {
    
    private static $instance = null;
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        // AJAX actions
        add_action('wp_ajax_gpb_get_progress', array($this, 'get_progress'));
        add_action('wp_ajax_nopriv_gpb_get_progress', array($this, 'get_progress'));
    }
    
    /**
     * Get progress data via AJAX
     */
    public function get_progress() {
        check_ajax_referer('gpb_nonce', 'nonce');
        
        $progress_data = Gift_Progress_Bar::calculate_progress();
        
        if (!$progress_data) {
            wp_send_json_error(array(
                'message' => __('Nem sikerült lekérni a progress adatokat.', 'gift-progress-bar')
            ));
        }
        
        wp_send_json_success($progress_data);
    }
}
