<?php
/**
 * Frontend Display Class
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class GPB_Frontend {
    
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
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // Add progress bar to cart page
        if (get_option('gpb_enable_cart', 'yes') === 'yes') {
            add_action('woocommerce_before_cart', array($this, 'display_progress_bar'), 10);
        }
        
        // Add progress bar to checkout page
        if (get_option('gpb_enable_checkout', 'yes') === 'yes') {
            add_action('woocommerce_before_checkout_form', array($this, 'display_progress_bar'), 10);
        }
        
        // Add progress bar to mini cart (widget)
        if (get_option('gpb_enable_mini_cart', 'yes') === 'yes') {
            // Use only ONE hook to prevent duplication
            // woocommerce_before_mini_cart is the most reliable and widely supported
            add_action('woocommerce_before_mini_cart', array($this, 'display_mini_cart_progress'), 10);
        }
        
        // Add to cart fragments for AJAX updates
        // Use only woocommerce_add_to_cart_fragments to prevent duplication
        add_filter('woocommerce_add_to_cart_fragments', array($this, 'cart_fragments'), 10);
        
        // Register shortcode
        add_shortcode('gift_progress_bar', array($this, 'shortcode_handler'));
    }
    
    /**
     * Shortcode handler
     */
    public function shortcode_handler($atts) {
        // Only show if WooCommerce cart exists
        if (!function_exists('WC') || !WC()->cart) {
            return '';
        }
        
        ob_start();
        $this->display_progress_bar();
        return ob_get_clean();
    }
    
    /**
     * Get cached option value (avoids repeated DB reads per request)
     */
    private static function get_cached_option($key, $default = '') {
        static $cache = array();
        if (!isset($cache[$key])) {
            $cache[$key] = get_option($key, $default);
        }
        return $cache[$key];
    }

    /**
     * Check if the progress bar should be shown on the current page
     */
    private function should_enqueue() {
        // Always load on cart and checkout pages
        if (is_cart() || is_checkout()) {
            return true;
        }
        // Load if the current page has the shortcode
        global $post;
        if ($post && has_shortcode($post->post_content, 'gift_progress_bar')) {
            return true;
        }
        // Load if mini cart is enabled (widget can appear on any page)
        if (self::get_cached_option('gpb_enable_mini_cart', 'yes') === 'yes') {
            return true;
        }
        return false;
    }

    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        // Only load assets on pages where we actually show the progress bar
        if (!$this->should_enqueue()) {
            return;
        }

        // Enqueue Dashicons (needed for milestone icons)
        wp_enqueue_style('dashicons');
        
        // Enqueue CSS
        wp_enqueue_style(
            'gpb-frontend-css',
            GPB_PLUGIN_URL . 'assets/css/frontend.css',
            array('dashicons'),
            GPB_VERSION
        );
        
        // Add dynamic CSS for colors - cache the brightness calculation
        $bar_color  = self::get_cached_option('gpb_bar_color', '#4CAF50');
        $bg_color   = self::get_cached_option('gpb_bg_color', '#e0e0e0');
        $text_color = self::get_cached_option('gpb_text_color', '#333333');
        $bar_color_light = $this->adjust_brightness($bar_color, 20); // calc once, reuse
        
        $custom_css = "
            .gpb-progress-bar-container { color: {$text_color}; }
            .gpb-progress-bar-bg { background-color: {$bg_color}; }
            .gpb-progress-bar-fill { background: linear-gradient(90deg, {$bar_color} 0%, {$bar_color_light} 100%); }
            .gpb-milestone.completed .gpb-milestone-icon { background-color: {$bar_color}; border-color: {$bar_color}; }
            .gpb-milestone.active .gpb-milestone-icon { border-color: {$bar_color}; color: {$bar_color}; }
            .gpb-mini-cart-container { color: {$text_color}; }
            .gpb-mini-progress-bg { background-color: {$bg_color}; }
            .gpb-mini-progress-fill { background: linear-gradient(90deg, {$bar_color} 0%, {$bar_color_light} 100%); }
            .gpb-mini-milestone.completed .gpb-mini-milestone-icon { background-color: {$bar_color}; border-color: {$bar_color}; }
            .gpb-mini-milestone.active .gpb-mini-milestone-icon { border-color: {$bar_color}; color: {$bar_color}; }
        ";
        
        wp_add_inline_style('gpb-frontend-css', $custom_css);
        
        // Enqueue JS
        wp_enqueue_script(
            'gpb-frontend-js',
            GPB_PLUGIN_URL . 'assets/js/frontend.js',
            array('jquery'),
            GPB_VERSION,
            true
        );
        
        // Localize script (nonce only generated when script is actually needed)
        wp_localize_script('gpb-frontend-js', 'gpbData', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('gpb_nonce')
        ));
    }
    
    /**
     * Display progress bar
     */
    public function display_progress_bar() {
        // Prevent multiple displays with global flag
        global $gpb_main_progress_displayed;
        
        if ($gpb_main_progress_displayed) {
            return;
        }
        
        // Check if WooCommerce cart exists
        if (!function_exists('WC') || !WC()->cart) {
            return;
        }
        
        $progress_data = Gift_Progress_Bar::calculate_progress();
        
        if (!$progress_data) {
            return;
        }
        
        // Mark as displayed globally
        $gpb_main_progress_displayed = true;
        
        $this->render_progress_bar($progress_data);
    }
    
    /**
     * Display mini cart progress (compact version)
     */
    public function display_mini_cart_progress() {
        // Prevent multiple displays with global flag
        global $gpb_mini_cart_displayed;
        
        if ($gpb_mini_cart_displayed) {
            return;
        }
        
        // Check if WooCommerce cart exists
        if (!function_exists('WC') || !WC()->cart) {
            return;
        }
        
        $progress_data = Gift_Progress_Bar::calculate_progress();
        
        if (!$progress_data) {
            return;
        }
        
        // Mark as displayed globally
        $gpb_mini_cart_displayed = true;
        
        $this->render_mini_cart_progress($progress_data);
    }
    
    /**
     * Render progress bar HTML
     */
    public function render_progress_bar($data) {
        ?>
        <div class="gpb-progress-bar-wrapper" id="gpb-progress-bar-wrapper">
            <div class="gpb-progress-bar-container">
                
                <!-- Message -->
                <div class="gpb-message">
                    <?php echo $this->get_progress_message($data); ?>
                </div>
                
                <!-- Progress Bar -->
                <div class="gpb-progress-bar-outer">
                    <div class="gpb-progress-bar-bg">
                        <div class="gpb-progress-bar-fill" style="width: <?php echo esc_attr($data['progress_percent']); ?>%;">
                            <span class="gpb-progress-bar-shine"></span>
                        </div>
                    </div>
                </div>
                
                <!-- Milestones -->
                <div class="gpb-milestones">
                    <?php foreach ($data['thresholds'] as $index => $threshold): ?>
                        <?php
                        $is_completed = in_array($index, $data['completed_levels']);
                        $is_active = (!$is_completed && $data['next_level'] && $threshold['amount'] === $data['next_level']['amount']);
                        // Use manual bar_position if set, otherwise auto-calculate
                        if (isset($threshold['bar_position']) && $threshold['bar_position'] !== '' && $threshold['bar_position'] !== null) {
                            $position = floatval($threshold['bar_position']);
                        } else {
                            $position = ($threshold['amount'] / $data['highest_threshold']) * 100;
                        }
                        
                        $classes = array('gpb-milestone');
                        if ($is_completed) {
                            $classes[] = 'completed';
                        }
                        if ($is_active) {
                            $classes[] = 'active';
                        }
                        ?>
                        
                        <div class="<?php echo esc_attr(implode(' ', $classes)); ?>" 
                             style="position: absolute !important; left: <?php echo esc_attr($position); ?>%; display: block !important; transform: translateX(-50%) !important; top: 12px !important;"
                             data-amount="<?php echo esc_attr($threshold['amount']); ?>">
                            
                            <div class="gpb-milestone-icon">
                                <?php if ($is_completed): ?>
                                    <span class="dashicons dashicons-yes"></span>
                                <?php else: ?>
                                    <span class="dashicons <?php echo esc_attr($threshold['icon']); ?>"></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="gpb-milestone-tooltip">
                                    <div class="gpb-milestone-amount">
                                        <?php
                                        $ms_type = isset($threshold['type']) ? $threshold['type'] : 'amount';
                                        if ($ms_type === 'quantity') {
                                            echo esc_html(intval($threshold['amount']) . ' db');
                                        } else {
                                            echo wc_price($threshold['amount']);
                                        }
                                        ?>
                                    </div>
                                    <div class="gpb-milestone-reward">
                                        <?php echo esc_html($threshold['reward']); ?>
                                    </div>
                                </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
            </div>
        </div>
        <?php
    }
    
    /**
     * Render mini cart progress bar (full version with icons)
     */
    public function render_mini_cart_progress($data) {
        ?>
        <div class="gpb-mini-cart-progress" id="gpb-mini-cart-progress">
            <div class="gpb-mini-cart-container">
                
                <!-- Message -->
                <div class="gpb-mini-message">
                    <?php echo $this->get_progress_message($data); ?>
                </div>
                
                <!-- Progress Bar -->
                <div class="gpb-mini-progress-outer">
                    <div class="gpb-mini-progress-bg">
                        <div class="gpb-mini-progress-fill" style="width: <?php echo esc_attr($data['progress_percent']); ?>%;">
                            <span class="gpb-mini-progress-shine"></span>
                        </div>
                    </div>
                </div>
                
                <!-- Milestones -->
                <div class="gpb-mini-milestones">
                    <?php foreach ($data['thresholds'] as $index => $threshold): ?>
                        <?php
                        $is_completed = in_array($index, $data['completed_levels']);
                        $is_active = (!$is_completed && $data['next_level'] && $threshold['amount'] === $data['next_level']['amount']);
                        // Use manual bar_position if set, otherwise auto-calculate
                        if (isset($threshold['bar_position']) && $threshold['bar_position'] !== '' && $threshold['bar_position'] !== null) {
                            $position = floatval($threshold['bar_position']);
                        } else {
                            $position = ($threshold['amount'] / $data['highest_threshold']) * 100;
                        }
                        
                        $classes = array('gpb-mini-milestone');
                        if ($is_completed) {
                            $classes[] = 'completed';
                        }
                        if ($is_active) {
                            $classes[] = 'active';
                        }
                        ?>
                        
                        <div class="<?php echo esc_attr(implode(' ', $classes)); ?>" 
                             style="position: absolute !important; left: <?php echo esc_attr($position); ?>%; display: block !important; transform: translateX(-50%) !important; top: 0 !important;"
                             data-amount="<?php echo esc_attr($threshold['amount']); ?>">
                            
                            <div class="gpb-mini-milestone-icon">
                                <?php if ($is_completed): ?>
                                    <span class="dashicons dashicons-yes"></span>
                                <?php else: ?>
                                    <span class="dashicons <?php echo esc_attr($threshold['icon']); ?>"></span>
                                <?php endif; ?>
                            </div>
                            
                        </div>
                    <?php endforeach; ?>
                </div>
                
            </div>
        </div>
        <?php
    }
    
    /**
     * Get progress message
     */
    private function get_progress_message($data) {
        if ($data['all_completed']) {
            return '<span class="gpb-message-icon">🥳</span> <strong>' . 
                   esc_html__('Gratulálunk! Maximális ajándékcsomagot értél el!', 'gift-progress-bar') . 
                   '</strong>';
        }
        
        // Helper: format a threshold value for display
        $format_needed = function($level, $amount_needed) {
            $type = isset($level['type']) ? $level['type'] : 'amount';
            if ($type === 'quantity') {
                return '<strong>' . intval($amount_needed) . ' db</strong>';
            }
            return '<strong>' . wc_price($amount_needed) . '</strong>';
        };
        
        if ($data['current_level']) {
            $message = '<span class="gpb-message-icon">🎉</span> <strong>' . 
                      esc_html__('Gratulálunk!', 'gift-progress-bar') . 
                      '</strong> ' . 
                      sprintf(
                          esc_html__('Már jogosult vagy a következőre: %s', 'gift-progress-bar'),
                          '<strong>' . esc_html($data['current_level']['reward']) . '</strong>'
                      );
            
            if ($data['next_level']) {
                $message .= ' ' . sprintf(
                    esc_html__('Már csak %s kell a következő ajándékhoz: %s', 'gift-progress-bar'),
                    $format_needed($data['next_level'], $data['amount_needed']),
                    '<strong>' . esc_html($data['next_level']['reward']) . '</strong>'
                );
            }
            
            return $message;
        }
        
        // Sort by amount - use spaceship operator for type-safe comparison (avoids PHP 8 float→int warnings)
        usort($thresholds, function($a, $b) {
            return $a['amount'] <=> $b['amount'];
        });
        
        if ($data['next_level']) {
            return sprintf(
                esc_html__('Már csak %s kell az ajándékhoz: %s', 'gift-progress-bar'),
                $format_needed($data['next_level'], $data['amount_needed']),
                '<strong>' . esc_html($data['next_level']['reward']) . '</strong>'
            );
        }
        
        return '';
    }
    
    /**
     * Add cart fragments for AJAX updates (classic WooCommerce only)
     */
    public function cart_fragments($fragments) {
        // Skip if WooCommerce cart is not available
        if (!function_exists('WC') || !WC()->cart) {
            return $fragments;
        }
        
        // Skip if WooCommerce Blocks is handling the cart (Store API context)
        // Blocks use their own REST endpoints, not the classic fragment system
        if (defined('REST_REQUEST') && REST_REQUEST) {
            return $fragments;
        }
        
        try {
            $progress_data = Gift_Progress_Bar::calculate_progress();
            
            // Main progress bar fragment
            if (get_option('gpb_enable_cart', 'yes') === 'yes') {
                ob_start();
                if ($progress_data) {
                    $this->render_progress_bar($progress_data);
                }
                $fragments['#gpb-progress-bar-wrapper'] = ob_get_clean();
            }
            
            // Mini cart progress bar fragment
            if (get_option('gpb_enable_mini_cart', 'yes') === 'yes') {
                ob_start();
                if ($progress_data) {
                    $this->render_mini_cart_progress($progress_data);
                }
                $fragments['#gpb-mini-cart-progress'] = ob_get_clean();
            }
            
        } catch (Exception $e) {
            // Clean up any open output buffers to avoid corrupting the AJAX response
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
        }
        
        return $fragments;
    }
    
    /**
     * Adjust color brightness
     */
    private function adjust_brightness($hex, $percent) {
        $hex = str_replace('#', '', $hex);
        
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        $r = max(0, min(255, $r + ($r * $percent / 100)));
        $g = max(0, min(255, $g + ($g * $percent / 100)));
        $b = max(0, min(255, $b + ($b * $percent / 100)));
        
        return '#' . str_pad(dechex($r), 2, '0', STR_PAD_LEFT) .
                     str_pad(dechex($g), 2, '0', STR_PAD_LEFT) .
                     str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
    }
}
