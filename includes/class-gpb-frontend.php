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
        
        // Add progress bar to cart page - multiple hooks for compatibility
        if (get_option('gpb_enable_cart', 'yes') === 'yes') {
            add_action('woocommerce_before_cart', array($this, 'display_progress_bar'), 10);
            add_action('woocommerce_before_cart_table', array($this, 'display_progress_bar'), 5);
        }
        
        // Add progress bar to checkout page - multiple hooks for compatibility
        if (get_option('gpb_enable_checkout', 'yes') === 'yes') {
            add_action('woocommerce_before_checkout_form', array($this, 'display_progress_bar'), 10);
            add_action('woocommerce_checkout_before_customer_details', array($this, 'display_progress_bar'), 5);
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
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        // Always enqueue CSS for mini cart (it can appear anywhere via widget)
        // Enqueue Dashicons (needed for milestone icons)
        wp_enqueue_style('dashicons');
        
        // Enqueue CSS
        wp_enqueue_style(
            'gpb-frontend-css',
            GPB_PLUGIN_URL . 'assets/css/frontend.css',
            array('dashicons'),
            GPB_VERSION
        );
        
        // Add dynamic CSS for colors
        $bar_color = get_option('gpb_bar_color', '#4CAF50');
        $bg_color = get_option('gpb_bg_color', '#e0e0e0');
        $text_color = get_option('gpb_text_color', '#333333');
        
        $custom_css = "
            .gpb-progress-bar-container {
                color: {$text_color};
            }
            .gpb-progress-bar-bg {
                background-color: {$bg_color};
            }
            .gpb-progress-bar-fill {
                background: linear-gradient(90deg, {$bar_color} 0%, " . $this->adjust_brightness($bar_color, 20) . " 100%);
            }
            .gpb-milestone.completed .gpb-milestone-icon {
                background-color: {$bar_color};
                border-color: {$bar_color};
            }
            .gpb-milestone.active .gpb-milestone-icon {
                border-color: {$bar_color};
                color: {$bar_color};
            }
            
            /* Mini cart colors */
            .gpb-mini-cart-container {
                color: {$text_color};
            }
            .gpb-mini-progress-bg {
                background-color: {$bg_color};
            }
            .gpb-mini-progress-fill {
                background: linear-gradient(90deg, {$bar_color} 0%, " . $this->adjust_brightness($bar_color, 20) . " 100%);
            }
            .gpb-mini-milestone.completed .gpb-mini-milestone-icon {
                background-color: {$bar_color};
                border-color: {$bar_color};
            }
            .gpb-mini-milestone.active .gpb-mini-milestone-icon {
                border-color: {$bar_color};
                color: {$bar_color};
            }
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
        
        // Localize script
        wp_localize_script('gpb-frontend-js', 'gpbData', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('gpb_nonce')
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
                        $position = ($threshold['amount'] / $data['highest_threshold']) * 100;
                        
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
                                    <?php echo wc_price($threshold['amount']); ?>
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
        <script type="text/javascript">
        (function() {
            // Immediate deduplication - remove duplicates right after render
            if (typeof jQuery !== 'undefined') {
                jQuery(function($) {
                    $('.gpb-progress-bar-wrapper').each(function(i) {
                        if (i > 0) $(this).remove();
                    });
                });
            }
        })();
        </script>
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
                        $position = ($threshold['amount'] / $data['highest_threshold']) * 100;
                        
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
        <script type="text/javascript">
        (function() {
            // Immediate deduplication - remove duplicates right after render
            if (typeof jQuery !== 'undefined') {
                jQuery(function($) {
                    $('.gpb-mini-cart-progress').each(function(i) {
                        if (i > 0) $(this).remove();
                    });
                    
                    // Debug: Check milestone positioning
                    if (window.console && window.console.log) {
                        console.log('GPB Debug: Mini cart milestones check');
                        $('.gpb-mini-milestone').each(function(i) {
                            var $m = $(this);
                            console.log('Milestone ' + i + ':', {
                                position: $m.css('position'),
                                left: $m.css('left'),
                                display: $m.css('display'),
                                transform: $m.css('transform')
                            });
                        });
                    }
                    
                    // Force absolute positioning
                    $('.gpb-mini-milestone').css({
                        'position': 'absolute',
                        'display': 'block',
                        'transform': 'translateX(-50%)'
                    });
                });
            }
        })();
        </script>
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
                    '<strong>' . wc_price($data['amount_needed']) . '</strong>',
                    '<strong>' . esc_html($data['next_level']['reward']) . '</strong>'
                );
            }
            
            return $message;
        }
        
        if ($data['next_level']) {
            return sprintf(
                esc_html__('Már csak %s kell az ajándékhoz: %s', 'gift-progress-bar'),
                '<strong>' . wc_price($data['amount_needed']) . '</strong>',
                '<strong>' . esc_html($data['next_level']['reward']) . '</strong>'
            );
        }
        
        return '';
    }
    
    /**
     * Add cart fragments for AJAX updates
     */
    public function cart_fragments($fragments) {
        // Reset global flags for AJAX updates
        global $gpb_main_progress_displayed, $gpb_mini_cart_displayed;
        $gpb_main_progress_displayed = false;
        $gpb_mini_cart_displayed = false;
        
        // Main progress bar fragment
        ob_start();
        
        $progress_data = Gift_Progress_Bar::calculate_progress();
        if ($progress_data) {
            $this->render_progress_bar($progress_data);
        }
        
        $fragments['#gpb-progress-bar-wrapper'] = ob_get_clean();
        
        // Mini cart progress bar fragment
        ob_start();
        
        if ($progress_data) {
            $this->render_mini_cart_progress($progress_data);
        }
        
        $fragments['#gpb-mini-cart-progress'] = ob_get_clean();
        
        // Reset flags again after rendering
        $gpb_main_progress_displayed = false;
        $gpb_mini_cart_displayed = false;
        
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
