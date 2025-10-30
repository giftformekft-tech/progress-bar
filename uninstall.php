<?php
/**
 * Uninstall script
 * Fired when the plugin is uninstalled
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete plugin options
delete_option('gpb_thresholds');
delete_option('gpb_enable_cart');
delete_option('gpb_enable_checkout');
delete_option('gpb_enable_mini_cart');
delete_option('gpb_bar_color');
delete_option('gpb_bg_color');
delete_option('gpb_text_color');

// For multisite
if (is_multisite()) {
    global $wpdb;
    $blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
    
    foreach ($blog_ids as $blog_id) {
        switch_to_blog($blog_id);
        
        delete_option('gpb_thresholds');
        delete_option('gpb_enable_cart');
        delete_option('gpb_enable_checkout');
        delete_option('gpb_enable_mini_cart');
        delete_option('gpb_bar_color');
        delete_option('gpb_bg_color');
        delete_option('gpb_text_color');
        
        restore_current_blog();
    }
}
