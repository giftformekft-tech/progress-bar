<?php
/**
 * Plugin Activator
 * Runs on plugin activation
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class GPB_Activator {
    
    /**
     * Activation hook
     */
    public static function activate() {
        // Set default options
        self::set_default_options();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Set default options
     */
    private static function set_default_options() {
        // Default thresholds
        if (!get_option('gpb_thresholds')) {
            $default_thresholds = array(
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
            update_option('gpb_thresholds', $default_thresholds);
        }
        
        // Default display settings
        if (!get_option('gpb_enable_cart')) {
            update_option('gpb_enable_cart', 'yes');
        }
        
        if (!get_option('gpb_enable_checkout')) {
            update_option('gpb_enable_checkout', 'yes');
        }
        
        if (!get_option('gpb_enable_mini_cart')) {
            update_option('gpb_enable_mini_cart', 'yes');
        }
        
        // Default colors
        if (!get_option('gpb_bar_color')) {
            update_option('gpb_bar_color', '#4CAF50');
        }
        
        if (!get_option('gpb_bg_color')) {
            update_option('gpb_bg_color', '#e0e0e0');
        }
        
        if (!get_option('gpb_text_color')) {
            update_option('gpb_text_color', '#333333');
        }
    }
}
