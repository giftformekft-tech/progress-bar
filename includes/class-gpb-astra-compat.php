<?php
/**
 * Astra Theme Compatibility Class
 * 
 * Automatikus integráció az Astra Side Cart-tal
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class GPB_Astra_Compat {
    
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
        // Debug mód admin felhasználóknak
        add_action('wp_footer', array($this, 'debug_info'));
        
        // Csak akkor aktiválódik, ha Astra téma aktív
        if (!$this->is_astra_active()) {
            return;
        }
        
        // Progress bar hozzáadása az Astra Side Cart-hoz - MINDEN lehetséges hook
        add_action('astra_woo_mini_cart_before_content', array($this, 'add_progress_bar'), 1);
        add_action('astra_woo_cart_drawer_before_content', array($this, 'add_progress_bar'), 1);
        add_action('astra_cart_drawer_before_items', array($this, 'add_progress_bar'), 1);
        add_action('woocommerce_before_mini_cart', array($this, 'add_progress_bar'), 1);
        add_action('woocommerce_widget_shopping_cart_before_buttons', array($this, 'add_progress_bar_after'), 1);
        
        // CSS stílusok hozzáadása
        add_action('wp_head', array($this, 'add_custom_styles'), 99);
        add_action('wp_footer', array($this, 'add_custom_styles_footer'), 99);
        
        // JavaScript DOM injection (ha a hookok nem működnek)
        add_action('wp_footer', array($this, 'add_dom_injection_script'), 100);
        
        // Astra Cart Title override
        add_action('wp_footer', array($this, 'add_cart_title_override'), 100);
        
        // Admin értesítés
        add_action('admin_notices', array($this, 'astra_integration_notice'));
        
        // Force fragment refresh
        add_filter('woocommerce_add_to_cart_fragments', array($this, 'add_astra_fragments'), 99);
    }
    
    /**
     * DOM Injection JavaScript - Ha a hookok nem működnek
     */
    public function add_dom_injection_script() {
        if (is_admin()) {
            return;
        }
        
        // Csak akkor, ha van termék a kosárban
        if (!function_exists('WC') || !WC()->cart || WC()->cart->is_empty()) {
            return;
        }
        
        // Progress data lekérése
        $progress_data = Gift_Progress_Bar::calculate_progress();
        if (!$progress_data) {
            return;
        }
        
        // Progress bar HTML generálása
        ob_start();
        ?>
        <div class="gpb-astra-wrapper gpb-injected" data-injected="true">
            <?php
            $frontend = GPB_Frontend::get_instance();
            if (method_exists($frontend, 'render_mini_cart_progress')) {
                $frontend->render_mini_cart_progress($progress_data);
            }
            ?>
        </div>
        <?php
        $progress_html = ob_get_clean();
        
        // Proper escaping for JavaScript string
        $progress_html_escaped = str_replace(array("\r", "\n", "\t"), '', $progress_html);
        $progress_html_escaped = addslashes($progress_html_escaped);
        $progress_html_escaped = str_replace("'", "\\'", $progress_html_escaped);
        $progress_html_escaped = str_replace('<script', '<\\/script', $progress_html_escaped);
        $progress_html_escaped = str_replace('</script>', '<\\/script>', $progress_html_escaped);
        ?>
        
        <script type="text/javascript">
        (function($) {
            if (!$ || typeof $ === 'undefined') return;
            
            var progressBarHTML = '<?php echo $progress_html_escaped; ?>';
            var injected = false;
            var attempts = 0;
            var maxAttempts = 10;
            
            // Injection funkció
            function injectProgressBar() {
                if (injected || attempts >= maxAttempts) {
                    return;
                }
                
                attempts++;
                
                var found = false;
                
                // Helyes selector: .astra-cart-drawer-content
                var $target = $('.astra-cart-drawer-content, .widget_shopping_cart_content').first();
                
                if ($target.length && !$target.find('.gpb-injected').length) {
                    // Prepend - legelső elemként
                    $target.prepend(progressBarHTML);
                    injected = true;
                    found = true;
                } 
                
                // Ha még nem sikerült, próbáljuk újra
                if (!found && attempts < maxAttempts) {
                    setTimeout(injectProgressBar, 300);
                }
            }
            
            // Injection indítása
            $(document).ready(function() {
                // Azonnali próbálkozás
                setTimeout(injectProgressBar, 100);
                
                // Cart icon kattintás
                $(document).on('click', '.ast-cart-menu-wrap, .ast-header-cart, .header-cart-icon, [data-toggle-target*="cart"]', function() {
                    injected = false;
                    attempts = 0;
                    setTimeout(injectProgressBar, 300);
                });
                
                // WooCommerce events
                $(document.body).on('wc_fragments_refreshed wc_fragments_loaded added_to_cart', function() {
                    injected = false;
                    attempts = 0;
                    setTimeout(injectProgressBar, 200);
                });
                
                // MutationObserver
                var observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.addedNodes.length) {
                            $(mutation.addedNodes).each(function() {
                                if (this.nodeType === 1) {
                                    var $node = $(this);
                                    if ($node.is('.astra-cart-drawer-content') ||
                                        $node.find('.astra-cart-drawer-content').length) {
                                        setTimeout(injectProgressBar, 100);
                                    }
                                }
                            });
                        }
                    });
                });
                
                observer.observe(document.body, {
                    childList: true,
                    subtree: true
                });
            });
            
        })(jQuery);
        </script>
        <?php
    }
    
    /**
     * Debug információ megjelenítése (csak admin felhasználóknak ha szükséges)
     */
    public function debug_info() {
        // Debug mód kikapcsolva production verzióban
        // Ha szükséges, kapcsold be ezt a kommentet kivéve:
        /*
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $is_astra = $this->is_astra_active();
        // ... debug kód ...
        */
    }
    
    /**
     * Ellenőrzi, hogy Astra téma aktív-e
     */
    private function is_astra_active() {
        $theme = wp_get_theme();
        
        // Astra téma vagy child theme
        if ('Astra' === $theme->name || 'Astra' === $theme->parent_theme) {
            return true;
        }
        
        // Template név alapján
        if ('astra' === get_template()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Progress bar hozzáadása az Astra Side Cart-hoz
     */
    public function add_progress_bar() {
        // Megakadályozzuk a többszöri megjelenítést
        static $displayed = false;
        if ($displayed) {
            return;
        }
        
        // Csak akkor jelenjen meg, ha van termék a kosárban
        if (!function_exists('WC') || !WC()->cart || WC()->cart->is_empty()) {
            return;
        }
        
        // Ellenőrizzük, hogy a mini cart engedélyezve van-e
        if (get_option('gpb_enable_mini_cart', 'yes') !== 'yes') {
            return;
        }
        
        $displayed = true;
        
        // Mobil detektálás
        $is_mobile = wp_is_mobile();
        $padding = $is_mobile ? '10px' : '15px';
        
        // Progress bar megjelenítése
        ?>
        <div class="gpb-astra-wrapper" data-hook="<?php echo esc_attr(current_action()); ?>" style="padding: <?php echo esc_attr($padding); ?>; border-bottom: 1px solid #ececec; background: #f9f9f9;">
            <?php
            $progress_data = Gift_Progress_Bar::calculate_progress();
            if ($progress_data) {
                // Kompakt verzió használata
                $frontend = GPB_Frontend::get_instance();
                if (method_exists($frontend, 'render_mini_cart_progress')) {
                    $frontend->render_mini_cart_progress($progress_data);
                } else {
                    // Fallback: shortcode
                    echo do_shortcode('[gift_progress_bar]');
                }
            }
            ?>
        </div>
        <?php
    }
    
    /**
     * Progress bar hozzáadása gombok után (alternatív pozíció)
     */
    public function add_progress_bar_after() {
        // Ha már megjelent fent, ne jelenjen meg újra
        static $displayed_after = false;
        if ($displayed_after) {
            return;
        }
        
        // Ha az első már megjelent, ne kelljen ez
        if (did_action('astra_woo_mini_cart_before_content')) {
            return;
        }
        
        $displayed_after = true;
        $this->add_progress_bar();
    }
    
    /**
     * Astra fragments hozzáadása
     */
    public function add_astra_fragments($fragments) {
        ob_start();
        
        if (!WC()->cart->is_empty()) {
            $progress_data = Gift_Progress_Bar::calculate_progress();
            if ($progress_data) {
                $frontend = GPB_Frontend::get_instance();
                if (method_exists($frontend, 'render_mini_cart_progress')) {
                    ?>
                    <div class="gpb-astra-wrapper" style="padding: 15px; border-bottom: 1px solid #ececec; background: #f9f9f9;">
                        <?php $frontend->render_mini_cart_progress($progress_data); ?>
                    </div>
                    <?php
                }
            }
        }
        
        $fragments['.gpb-astra-wrapper'] = ob_get_clean();
        
        return $fragments;
    }
    
    /**
     * Astra specifikus CSS stílusok
     */
    public function add_custom_styles() {
        if (!$this->is_astra_active() || is_admin()) {
            return;
        }
        ?>
        <style id="gpb-astra-styles">
        /* ========================================
           Astra Side Cart - Gift Progress Bar
           ======================================== */
        
        /* Fő wrapper */
        .gpb-astra-wrapper {
            padding: 15px;
            background: #f9f9f9;
            border-bottom: 1px solid #ececec;
            margin-bottom: 15px;
        }
        
        /* Progress bar konténer */
        .astra-cart-drawer .gpb-mini-cart-progress,
        .astra-cart-drawer .gpb-progress-bar-wrapper {
            background: transparent !important;
            box-shadow: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        
        .astra-cart-drawer .gpb-progress-bar-container {
            background: transparent;
            box-shadow: none;
            padding: 0;
        }
        
        /* Progress bar magassága */
        .astra-cart-drawer .gpb-progress-bar-bg,
        .astra-cart-drawer .gpb-mini-progress-bg {
            height: 10px !important;
        }
        
        /* Mérföldkövek kisebbek */
        .astra-cart-drawer .gpb-milestone-icon {
            width: 30px !important;
            height: 30px !important;
        }
        
        .astra-cart-drawer .gpb-milestone-icon .dashicons {
            font-size: 14px !important;
            width: 14px !important;
            height: 14px !important;
            line-height: 30px !important;
        }
        
        /* Tooltip kisebb */
        .astra-cart-drawer .gpb-milestone-tooltip {
            font-size: 11px !important;
            padding: 8px 10px !important;
        }
        
        .astra-cart-drawer .gpb-milestone-amount {
            font-size: 12px !important;
        }
        
        .astra-cart-drawer .gpb-milestone-reward {
            font-size: 10px !important;
        }
        
        /* Üzenet kisebb */
        .astra-cart-drawer .gpb-message,
        .astra-cart-drawer .gpb-mini-message {
            font-size: 13px !important;
            margin-bottom: 10px !important;
        }
        
        /* Mini cart milestone lista */
        .astra-cart-drawer .gpb-mini-milestones-list {
            gap: 6px !important;
        }
        
        .astra-cart-drawer .gpb-mini-milestone {
            font-size: 11px !important;
            padding: 5px 8px !important;
        }
        
        .astra-cart-drawer .gpb-mini-milestone-text strong {
            font-size: 12px !important;
        }
        
        .astra-cart-drawer .gpb-mini-milestone-text small {
            font-size: 10px !important;
        }
        
        /* Sötét háttér esetén világos szöveg */
        .astra-cart-drawer.dark-bg .gpb-message,
        .astra-cart-drawer.dark-bg .gpb-mini-message {
            color: #ffffff !important;
        }
        
        .astra-cart-drawer.dark-bg .gpb-astra-wrapper {
            background: rgba(255, 255, 255, 0.05) !important;
            border-bottom-color: rgba(255, 255, 255, 0.1) !important;
        }
        
        /* Astra drawer specifikus */
        .astra-cart-drawer-wrapper .gpb-astra-wrapper {
            border-radius: 0;
        }
        
        /* Overflow kezelés */
        .astra-cart-drawer .gpb-progress-bar-wrapper,
        .astra-cart-drawer .gpb-mini-cart-progress {
            max-width: 100%;
            overflow-x: hidden;
        }
        
        /* Milestones vízszintes scroll ha túl hosszú */
        .astra-cart-drawer .gpb-milestones {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        /* Scrollbar styling */
        .astra-cart-drawer .gpb-milestones::-webkit-scrollbar {
            height: 4px;
        }
        
        .astra-cart-drawer .gpb-milestones::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 2px;
        }
        
        /* Mobil még kompaktabb */
        @media (max-width: 480px) {
            .astra-cart-drawer .gpb-message,
            .astra-cart-drawer .gpb-mini-message {
                font-size: 12px !important;
            }
            
            .astra-cart-drawer .gpb-progress-bar-bg,
            .astra-cart-drawer .gpb-mini-progress-bg {
                height: 8px !important;
            }
            
            .astra-cart-drawer .gpb-milestone-icon {
                width: 26px !important;
                height: 26px !important;
            }
            
            .astra-cart-drawer .gpb-milestone-icon .dashicons {
                font-size: 12px !important;
                width: 12px !important;
                height: 12px !important;
                line-height: 26px !important;
            }
            
            .astra-cart-drawer .gpb-mini-milestone {
                font-size: 10px !important;
                padding: 4px 6px !important;
            }
            
            .astra-cart-drawer .gpb-mini-icon {
                font-size: 14px !important;
            }
            
            .astra-cart-drawer .gpb-astra-wrapper {
                padding: 8px !important;
            }
        }
        
        /* Tablet méret */
        @media (min-width: 481px) and (max-width: 768px) {
            .astra-cart-drawer .gpb-message,
            .astra-cart-drawer .gpb-mini-message {
                font-size: 12px !important;
            }
        }
        
        /* Animation smooth */
        .astra-cart-drawer .gpb-progress-bar-fill,
        .astra-cart-drawer .gpb-mini-progress-fill {
            transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        /* Z-index biztosítása */
        .astra-cart-drawer .gpb-astra-wrapper {
            position: relative;
            z-index: 1;
        }
        </style>
        <?php
    }
    
    /**
     * Footer stílusok is, biztonsági okokból
     */
    public function add_custom_styles_footer() {
        if (!$this->is_astra_active() || is_admin()) {
            return;
        }
        
        // Ha a head stílusok nem töltődtek be, próbáljuk footerben
        if (!has_action('wp_head', array($this, 'add_custom_styles'))) {
            $this->add_custom_styles();
        }
    }
    
    /**
     * Admin értesítés az Astra integrációról
     */
    public function astra_integration_notice() {
        // Csak akkor jelenjen meg, ha az admin oldalon vagyunk és Astra aktív
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Csak a plugin beállítások oldalán
        $screen = get_current_screen();
        if (!$screen || strpos($screen->id, 'gift-progress-bar') === false) {
            return;
        }
        
        // Ellenőrizzük, hogy a mini cart engedélyezve van-e
        if (get_option('gpb_enable_mini_cart', 'yes') !== 'yes') {
            return;
        }
        
        ?>
        <div class="notice notice-success">
            <p>
                <strong>🎨 Astra Téma Észlelve!</strong><br>
                A Gift Progress Bar automatikusan integrálódott az Astra Side Cart-tal! 🎉<br>
                <small>
                    A progress bar megjelenik az Astra slide-in kosárban amikor termékeket adsz hozzá.
                    <a href="<?php echo esc_url(wc_get_cart_url()); ?>" target="_blank">Teszteld a kosárban →</a>
                </small>
            </p>
        </div>
        <?php
    }

    /**
     * Astra Cart Title override
     * Changes "Shopping Cart" to "Kosár"
     */
    public function add_cart_title_override() {
        if (is_admin()) return;
        ?>
        <script type="text/javascript">
        (function($) {
            function changeCartTitle() {
                $('.astra-cart-drawer-title').each(function() {
                    var el = $(this);
                    // Csak akkor írjuk felül, ha nem "Kosár" (hogy ne villogjon feleslegesen, bár a text() gyors)
                    if (el.text().trim() !== 'Kosár') {
                        el.text('Kosár');
                    }
                });
            }

            $(document).ready(function() {
                // Azonnali futtatás
                changeCartTitle();
                setTimeout(changeCartTitle, 500); // Késleltetve is, biztos ami biztos

                // WooCommerce eseményekre
                $(document.body).on('wc_fragments_refreshed wc_fragments_loaded added_to_cart', function() {
                    setTimeout(changeCartTitle, 100);
                });
                
                // MutationObserver a dinamikus változásokhoz (pl. Astra side cart megnyitása)
                var observer = new MutationObserver(function(mutations) {
                     var shouldUpdate = false;
                     mutations.forEach(function(mutation) {
                        if (mutation.addedNodes.length || mutation.type === 'attributes') {
                            shouldUpdate = true;
                        }
                     });
                     if (shouldUpdate) {
                        changeCartTitle();
                     }
                });
                
                // Figyeljük a body-t, mert az Astra side cart oda kerülhet be
                observer.observe(document.body, { childList: true, subtree: true });
            });
        })(jQuery);
        </script>
        <?php
    }
}

// Inicializálás
add_action('init', function() {
    GPB_Astra_Compat::get_instance();
});
