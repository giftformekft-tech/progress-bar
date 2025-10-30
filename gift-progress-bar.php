<?php
/**
 * Plugin Name: Gift Progress Bar for WooCommerce
 * Plugin URI: https://github.com/yourusername/gift-progress-bar
 * Description: Vizuális progress bar a kosárban, ami ösztönzi a vásárlókat a kosárérték növelésére küszöbértékek és ajándékok megjelenítésével.
 * Version: 1.0.9-production
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: gift-progress-bar
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.5
 * Requires Plugins: woocommerce
 */

// Declare HPOS compatibility
add_action('before_woocommerce_init', function() {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('GPB_VERSION', '1.0.9-production');
define('GPB_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('GPB_PLUGIN_URL', plugin_dir_url(__FILE__));
define('GPB_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main Plugin Class
 */
class Gift_Progress_Bar {
    
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
        // Check if WooCommerce is active
        add_action('plugins_loaded', array($this, 'check_woocommerce'));
        
        // Initialize plugin
        add_action('init', array($this, 'init'));
        
        // Load admin
        if (is_admin()) {
            require_once GPB_PLUGIN_DIR . 'includes/class-gpb-admin.php';
            GPB_Admin::get_instance();
        }
        
        // Load frontend
        require_once GPB_PLUGIN_DIR . 'includes/class-gpb-frontend.php';
        GPB_Frontend::get_instance();
        
        // Load AJAX handler
        require_once GPB_PLUGIN_DIR . 'includes/class-gpb-ajax.php';
        GPB_Ajax::get_instance();
        
        // Load theme compatibility
        require_once GPB_PLUGIN_DIR . 'includes/class-gpb-astra-compat.php';
    }
    
    /**
     * Check if WooCommerce is active
     */
    public function check_woocommerce() {
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
            deactivate_plugins(GPB_PLUGIN_BASENAME);
        }
    }
    
    /**
     * WooCommerce missing notice
     */
    public function woocommerce_missing_notice() {
        ?>
        <div class="notice notice-error">
            <p><?php esc_html_e('A Gift Progress Bar plugin működéséhez szükséges a WooCommerce plugin telepítése és aktiválása.', 'gift-progress-bar'); ?></p>
        </div>
        <?php
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Load text domain
        load_plugin_textdomain('gift-progress-bar', false, dirname(GPB_PLUGIN_BASENAME) . '/languages');
    }
    
    /**
     * Get thresholds
     */
    public static function get_thresholds() {
        $thresholds = get_option('gpb_thresholds', array());
        
        // Default thresholds if empty
        if (empty($thresholds)) {
            $thresholds = array(
                array(
                    'amount' => 15000,
                    'reward' => 'Ingyenes szállítás',
                    'icon' => 'dashicons-cart'
                ),
                array(
                    'amount' => 15990,
                    'reward' => 'Ajándék Filmes póló',
                    'icon' => 'dashicons-products'
                ),
                array(
                    'amount' => 19990,
                    'reward' => 'Ajándék Filmes póló és bögre',
                    'icon' => 'dashicons-awards'
                ),
                array(
                    'amount' => 24990,
                    'reward' => 'Ajándék 2 db Filmes póló',
                    'icon' => 'dashicons-star-filled'
                )
            );
        }
        
        // Sort by amount
        usort($thresholds, function($a, $b) {
            return $a['amount'] - $b['amount'];
        });
        
        return $thresholds;
    }
    
    /**
     * Calculate progress data
     */
    public static function calculate_progress() {
        if (!function_exists('WC') || !WC()->cart) {
            return null;
        }
        
        $cart_total = floatval(WC()->cart->get_subtotal());
        $thresholds = self::get_thresholds();
        
        if (empty($thresholds)) {
            return null;
        }
        
        $highest_threshold = end($thresholds)['amount'];
        $current_level = null;
        $next_level = null;
        $completed_levels = array();
        
        // Find current and next levels
        foreach ($thresholds as $index => $threshold) {
            if ($cart_total >= $threshold['amount']) {
                $completed_levels[] = $index;
                $current_level = $threshold;
            } else {
                if ($next_level === null) {
                    $next_level = $threshold;
                }
            }
        }
        
        // Calculate progress percentage
        $progress_percent = min(100, ($cart_total / $highest_threshold) * 100);
        
        // Calculate amount needed for next level
        $amount_needed = 0;
        if ($next_level) {
            $amount_needed = $next_level['amount'] - $cart_total;
        }
        
        // Check if all levels completed
        $all_completed = (count($completed_levels) === count($thresholds));
        
        return array(
            'cart_total' => $cart_total,
            'progress_percent' => $progress_percent,
            'current_level' => $current_level,
            'next_level' => $next_level,
            'amount_needed' => $amount_needed,
            'completed_levels' => $completed_levels,
            'all_completed' => $all_completed,
            'thresholds' => $thresholds,
            'highest_threshold' => $highest_threshold
        );
    }
}

/**
 * Activation hook
 */
function activate_gift_progress_bar() {
    require_once GPB_PLUGIN_DIR . 'includes/class-gpb-activator.php';
    GPB_Activator::activate();
}
register_activation_hook(__FILE__, 'activate_gift_progress_bar');

/**
 * Initialize the plugin
 */
function gift_progress_bar_init() {
    return Gift_Progress_Bar::get_instance();
}

// Start the plugin
gift_progress_bar_init();
