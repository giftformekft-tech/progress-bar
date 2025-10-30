<?php
/**
 * Admin Settings Class
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class GPB_Admin {
    
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
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            __('Ajándék Progress Bar', 'gift-progress-bar'),
            __('Ajándék Progress Bar', 'gift-progress-bar'),
            'manage_woocommerce',
            'gift-progress-bar',
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('gpb_settings', 'gpb_thresholds');
        register_setting('gpb_settings', 'gpb_enable_cart');
        register_setting('gpb_settings', 'gpb_enable_checkout');
        register_setting('gpb_settings', 'gpb_bar_color');
        register_setting('gpb_settings', 'gpb_bg_color');
        register_setting('gpb_settings', 'gpb_text_color');
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if ('woocommerce_page_gift-progress-bar' !== $hook) {
            return;
        }
        
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        
        wp_enqueue_style(
            'gpb-admin-css',
            GPB_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            GPB_VERSION
        );
        
        wp_enqueue_script(
            'gpb-admin-js',
            GPB_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery', 'wp-color-picker', 'jquery-ui-sortable'),
            GPB_VERSION,
            true
        );
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_woocommerce')) {
            return;
        }
        
        // Handle form submission
        if (isset($_POST['gpb_save_settings']) && check_admin_referer('gpb_settings_action', 'gpb_settings_nonce')) {
            $this->save_settings();
        }
        
        $thresholds = Gift_Progress_Bar::get_thresholds();
        $enable_cart = get_option('gpb_enable_cart', 'yes');
        $enable_checkout = get_option('gpb_enable_checkout', 'yes');
        $bar_color = get_option('gpb_bar_color', '#4CAF50');
        $bg_color = get_option('gpb_bg_color', '#e0e0e0');
        $text_color = get_option('gpb_text_color', '#333333');
        
        ?>
        <div class="wrap gpb-admin-wrap">
            <h1><?php esc_html_e('Ajándék Progress Bar Beállítások', 'gift-progress-bar'); ?></h1>
            
            <form method="post" action="">
                <?php wp_nonce_field('gpb_settings_action', 'gpb_settings_nonce'); ?>
                
                <div class="gpb-settings-container">
                    
                    <!-- Display Settings -->
                    <div class="gpb-settings-section">
                        <h2><?php esc_html_e('Megjelenítési beállítások', 'gift-progress-bar'); ?></h2>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php esc_html_e('Megjelenítés a kosár oldalon', 'gift-progress-bar'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="gpb_enable_cart" value="yes" <?php checked($enable_cart, 'yes'); ?>>
                                        <?php esc_html_e('Engedélyezve', 'gift-progress-bar'); ?>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('Megjelenítés a pénztár oldalon', 'gift-progress-bar'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="gpb_enable_checkout" value="yes" <?php checked($enable_checkout, 'yes'); ?>>
                                        <?php esc_html_e('Engedélyezve', 'gift-progress-bar'); ?>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('Megjelenítés a mini cart-ban', 'gift-progress-bar'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="gpb_enable_mini_cart" value="yes" <?php checked(get_option('gpb_enable_mini_cart', 'yes'), 'yes'); ?>>
                                        <?php esc_html_e('Engedélyezve', 'gift-progress-bar'); ?>
                                    </label>
                                    <p class="description">
                                        <?php esc_html_e('Kompakt verzió jelenik meg a WooCommerce mini cart widget-ben (oldalsáv, menü, AJAX cart)', 'gift-progress-bar'); ?>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Color Settings -->
                    <div class="gpb-settings-section">
                        <h2><?php esc_html_e('Színek', 'gift-progress-bar'); ?></h2>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php esc_html_e('Progress bar színe', 'gift-progress-bar'); ?></th>
                                <td>
                                    <input type="text" name="gpb_bar_color" value="<?php echo esc_attr($bar_color); ?>" class="gpb-color-picker">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('Háttér színe', 'gift-progress-bar'); ?></th>
                                <td>
                                    <input type="text" name="gpb_bg_color" value="<?php echo esc_attr($bg_color); ?>" class="gpb-color-picker">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('Szöveg színe', 'gift-progress-bar'); ?></th>
                                <td>
                                    <input type="text" name="gpb_text_color" value="<?php echo esc_attr($text_color); ?>" class="gpb-color-picker">
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Thresholds -->
                    <div class="gpb-settings-section">
                        <h2><?php esc_html_e('Küszöbértékek és Ajándékok', 'gift-progress-bar'); ?></h2>
                        <p class="description"><?php esc_html_e('Húzd az elemeket a sorrendezéshez.', 'gift-progress-bar'); ?></p>
                        
                        <div id="gpb-thresholds-list" class="gpb-thresholds-list">
                            <?php foreach ($thresholds as $index => $threshold): ?>
                                <div class="gpb-threshold-item" data-index="<?php echo esc_attr($index); ?>">
                                    <span class="gpb-drag-handle dashicons dashicons-move"></span>
                                    
                                    <div class="gpb-threshold-fields">
                                        <div class="gpb-field">
                                            <label><?php esc_html_e('Összeg (Ft)', 'gift-progress-bar'); ?></label>
                                            <input type="number" name="gpb_thresholds[<?php echo esc_attr($index); ?>][amount]" 
                                                   value="<?php echo esc_attr($threshold['amount']); ?>" 
                                                   min="0" step="1" required>
                                        </div>
                                        
                                        <div class="gpb-field">
                                            <label><?php esc_html_e('Jutalom leírás', 'gift-progress-bar'); ?></label>
                                            <input type="text" name="gpb_thresholds[<?php echo esc_attr($index); ?>][reward]" 
                                                   value="<?php echo esc_attr($threshold['reward']); ?>" 
                                                   placeholder="pl. Ingyenes szállítás" required>
                                        </div>
                                        
                                        <div class="gpb-field">
                                            <label><?php esc_html_e('Ikon (Dashicon osztály)', 'gift-progress-bar'); ?></label>
                                            <input type="text" name="gpb_thresholds[<?php echo esc_attr($index); ?>][icon]" 
                                                   value="<?php echo esc_attr($threshold['icon']); ?>" 
                                                   placeholder="dashicons-cart">
                                            <span class="dashicons <?php echo esc_attr($threshold['icon']); ?> gpb-icon-preview"></span>
                                        </div>
                                    </div>
                                    
                                    <button type="button" class="gpb-remove-threshold button" title="<?php esc_attr_e('Törlés', 'gift-progress-bar'); ?>">
                                        <span class="dashicons dashicons-trash"></span>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <button type="button" id="gpb-add-threshold" class="button button-secondary">
                            <span class="dashicons dashicons-plus-alt"></span>
                            <?php esc_html_e('Új küszöbérték hozzáadása', 'gift-progress-bar'); ?>
                        </button>
                    </div>
                    
                    <p class="submit">
                        <input type="submit" name="gpb_save_settings" class="button button-primary" value="<?php esc_attr_e('Beállítások mentése', 'gift-progress-bar'); ?>">
                    </p>
                </div>
            </form>
        </div>
        <?php
    }
    
    /**
     * Save settings
     */
    private function save_settings() {
        // Save thresholds
        $thresholds = array();
        if (isset($_POST['gpb_thresholds']) && is_array($_POST['gpb_thresholds'])) {
            foreach ($_POST['gpb_thresholds'] as $threshold) {
                if (!empty($threshold['amount']) && !empty($threshold['reward'])) {
                    $thresholds[] = array(
                        'amount' => floatval($threshold['amount']),
                        'reward' => sanitize_text_field($threshold['reward']),
                        'icon' => sanitize_text_field($threshold['icon'])
                    );
                }
            }
        }
        update_option('gpb_thresholds', $thresholds);
        
        // Save display settings
        $enable_cart = isset($_POST['gpb_enable_cart']) ? 'yes' : 'no';
        $enable_checkout = isset($_POST['gpb_enable_checkout']) ? 'yes' : 'no';
        $enable_mini_cart = isset($_POST['gpb_enable_mini_cart']) ? 'yes' : 'no';
        
        update_option('gpb_enable_cart', $enable_cart);
        update_option('gpb_enable_checkout', $enable_checkout);
        update_option('gpb_enable_mini_cart', $enable_mini_cart);
        
        // Save color settings
        update_option('gpb_bar_color', sanitize_hex_color($_POST['gpb_bar_color']));
        update_option('gpb_bg_color', sanitize_hex_color($_POST['gpb_bg_color']));
        update_option('gpb_text_color', sanitize_hex_color($_POST['gpb_text_color']));
        
        // Show detailed success message
        $messages = array();
        $messages[] = 'Beállítások sikeresen mentve!';
        $messages[] = 'Kosár oldal: ' . ($enable_cart === 'yes' ? 'BEKAPCSOLVA' : 'KIKAPCSOLVA');
        $messages[] = 'Pénztár oldal: ' . ($enable_checkout === 'yes' ? 'BEKAPCSOLVA' : 'KIKAPCSOLVA');
        $messages[] = 'Mini cart: ' . ($enable_mini_cart === 'yes' ? 'BEKAPCSOLVA' : 'KIKAPCSOLVA');
        $messages[] = 'Küszöbértékek száma: ' . count($thresholds);
        
        echo '<div class="notice notice-success"><p><strong>' . implode('</strong><br>', $messages) . '</strong></p></div>';
        
        // Add test notice
        if ($enable_cart === 'yes' || $enable_checkout === 'yes' || $enable_mini_cart === 'yes') {
            echo '<div class="notice notice-info"><p>';
            echo '<strong>🧪 Tesztelés:</strong><br>';
            if ($enable_cart === 'yes') {
                echo '1. Látogasd meg a kosár oldalt: <a href="' . wc_get_cart_url() . '" target="_blank">Kosár megtekintése</a><br>';
            }
            if ($enable_mini_cart === 'yes') {
                echo '2. Nézd meg a mini cart widget-et (oldalsáv vagy menü)<br>';
            }
            echo '3. Adj hozzá terméket a kosárhoz<br>';
            echo '4. A progress bar automatikusan megjelenik<br>';
            echo '5. Ha nem látod, használd a shortcode-ot: <code>[gift_progress_bar]</code>';
            echo '</p></div>';
        }
    }
}
