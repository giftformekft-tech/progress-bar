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
        
        // Astra Cart Text overrides
        add_action('wp_footer', array($this, 'add_cart_text_overrides'), 100);
        
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

        // Ellenőrizzük, hogy a mini cart engedélyezve van-e
        if (get_option('gpb_enable_mini_cart', 'yes') !== 'yes') {
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
        
        // Safe encoding for JS string using json_encode
        $progress_html_js = json_encode($progress_html);
        ?>
        
        <script type="text/javascript">
        (function($) {
            if (!$ || typeof $ === 'undefined') return;
            
            var progressBarHTML = <?php echo $progress_html_js; ?>;
            var injected = false;
            var debounceTimer = null;
            var observer = null;
            
            function injectProgressBar() {
                if (injected) return;

                // Strategy: inject BEFORE the sticky bottom section (subtotal + buttons)
                // This keeps the bar in the fixed footer area — no scroll needed.
                // Astra uses different class names depending on theme version.
                var $footer = $(
                    '.ast-side-cart-summary, ' +
                    '.astra-cart-drawer-footer, ' +
                    '.woocommerce-mini-cart__total'
                ).first();

                if ($footer.length && !$footer.prev('.gpb-injected').length) {
                    $footer.before(progressBarHTML);
                    injected = true;
                    if (observer) observer.disconnect();
                    fixAstraLayout();
                    return;
                }

                // Fallback: prepend into the content area if footer not found
                var $content = $('.astra-cart-drawer-content, .widget_shopping_cart_content').first();
                if ($content.length && !$content.find('.gpb-injected').length) {
                    $content.prepend(progressBarHTML);
                    injected = true;
                    if (observer) observer.disconnect();
                }
                fixAstraLayout();
            }

            /**
             * Ensure the Astra drawer uses flex layout so the checkout
             * button is always visible without scrolling.
             * CSS handles the static case; this JS covers AJAX reloads
             * where inline styles (display:block on the widget) may
             * override our stylesheet rules.
             */
            function fixAstraLayout() {
                var $drawerContent = $('.astra-cart-drawer .astra-cart-drawer-content');
                if (!$drawerContent.length) return;

                // Override the inline display:block on the WooCommerce widget
                $drawerContent.children('.widget.widget_shopping_cart').css('display', 'flex');

                // Ensure widget_shopping_cart_content is also flex
                $drawerContent.find('.widget_shopping_cart_content').css('display', 'flex');
            }
            
            function debouncedInject() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(injectProgressBar, 150);
            }

            function startObserver() {
                if (observer) observer.disconnect();
                observer = new MutationObserver(function(mutations) {
                    if (injected) return;
                    for (var i = 0; i < mutations.length; i++) {
                        var added = mutations[i].addedNodes;
                        for (var j = 0; j < added.length; j++) {
                            var node = added[j];
                            if (node.nodeType === 1) {
                                var isCart = node.classList && (
                                    node.classList.contains('astra-cart-drawer-content') ||
                                    node.classList.contains('ast-side-cart-summary') ||
                                    node.classList.contains('astra-cart-drawer-footer')
                                );
                                var hasCart = node.querySelector && (
                                    node.querySelector('.astra-cart-drawer-content') ||
                                    node.querySelector('.ast-side-cart-summary')
                                );
                                if (isCart || hasCart) {
                                    debouncedInject();
                                    return;
                                }
                            }
                        }
                    }
                });
                observer.observe(document.body, { childList: true, subtree: true });
            }

            $(document).ready(function() {
                setTimeout(function() { injectProgressBar(); fixAstraLayout(); }, 100);
                startObserver();

                $(document).on('click', '.ast-cart-menu-wrap, .ast-header-cart, .header-cart-icon, [data-toggle-target*="cart"]', function() {
                    injected = false;
                    startObserver();
                    setTimeout(function() { injectProgressBar(); fixAstraLayout(); }, 300);
                });

                $(document.body).on('wc_fragments_refreshed wc_fragments_loaded added_to_cart removed_from_cart', function() {
                    injected = false;
                    startObserver();
                    debouncedInject();
                    setTimeout(fixAstraLayout, 200);
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
     *
     * IMPORTANT: only add the fragment key when we have real HTML.
     * Setting an empty string would cause WooCommerce to do
     * $('.gpb-astra-wrapper').replaceWith('') and permanently remove
     * the progress bar from the DOM after every page refresh / AJAX update.
     */
    public function add_astra_fragments($fragments) {
        if (!function_exists('WC') || !WC()->cart) {
            return $fragments;
        }

        if (WC()->cart->is_empty()) {
            return $fragments;
        }

        $progress_data = Gift_Progress_Bar::calculate_progress();
        if (!$progress_data) {
            return $fragments;
        }

        $frontend = GPB_Frontend::get_instance();
        if (!method_exists($frontend, 'render_mini_cart_progress')) {
            return $fragments;
        }

        ob_start();
        ?>
        <div class="gpb-astra-wrapper" style="padding: 15px; border-bottom: 1px solid #ececec; background: #f9f9f9;">
            <?php $frontend->render_mini_cart_progress($progress_data); ?>
        </div>
        <?php
        $html = ob_get_clean();

        if (!empty(trim($html))) {
            $fragments['.gpb-astra-wrapper'] = $html;
        }

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
        
        /* Progress bar wrapper when injected before the footer summary */
        .gpb-astra-wrapper.gpb-injected,
        .gpb-injected {
            padding: 12px 15px !important;
            background: #f9f9f9 !important;
            border-top: 1px solid #ececec !important;
            border-bottom: 1px solid #ececec !important;
            margin: 0 !important;
        }

        /* Progress bar konténer - kompakt a footer területen */
        .astra-cart-drawer .gpb-mini-cart-progress,
        .astra-cart-drawer .gpb-progress-bar-wrapper,
        .gpb-injected .gpb-mini-cart-progress {
            background: transparent !important;
            box-shadow: none !important;
            /* Override the 52px bottom padding that causes extra height */
            padding: 10px 0 0 0 !important;
            margin: 0 !important;
        }

        /* Milestone icon area: reduce minimum height to save space */
        .gpb-injected .gpb-mini-milestones,
        .astra-cart-drawer .gpb-mini-milestones {
            min-height: 34px !important;
            padding-top: 8px !important;
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

        /* ================================================================
         * Fix: checkout button always visible in Astra cart drawer.
         *
         * DOM layout inside .astra-cart-drawer-content:
         *   .gpb-astra-wrapper          ← progress bar (PHP-rendered)
         *   .widget.widget_shopping_cart
         *     .widget_shopping_cart_content
         *       ul.woocommerce-mini-cart   ← items (should scroll)
         *       .woocommerce-mini-cart__total
         *       .woocommerce-mini-cart__buttons  ← must always be visible
         *
         * Solution: flex column on the outer container so the progress bar
         * is pinned at the top, the widget fills remaining space, and the
         * items list scrolls while the total + buttons stay at the bottom.
         * ================================================================ */

        /* Outer drawer content: flex column, items scroll inside */
        .astra-cart-drawer .astra-cart-drawer-content {
            display: flex !important;
            flex-direction: column !important;
            overflow: hidden !important;
        }

        /* Progress bar wrapper: never shrink, always at top */
        .astra-cart-drawer .astra-cart-drawer-content > .gpb-astra-wrapper {
            flex-shrink: 0 !important;
            margin-bottom: 0 !important;
        }

        /* WooCommerce widget: takes all remaining height */
        .astra-cart-drawer .astra-cart-drawer-content > .widget.widget_shopping_cart {
            flex: 1 1 auto !important;
            min-height: 0 !important;
            overflow: hidden !important;
            display: flex !important;
            flex-direction: column !important;
        }

        /* Inner widget content: flex column, constrained to widget height */
        .astra-cart-drawer .astra-cart-drawer-content .widget_shopping_cart_content {
            flex: 1 !important;
            display: flex !important;
            flex-direction: column !important;
            min-height: 0 !important;
            overflow: hidden !important;
        }

        /* Cart items list: grows and scrolls */
        .astra-cart-drawer .astra-cart-drawer-content .widget_shopping_cart_content > ul.woocommerce-mini-cart {
            flex: 1 1 auto !important;
            overflow-y: auto !important;
            min-height: 0 !important;
        }

        /* Total + buttons: always visible at the bottom, never scrolled away */
        .astra-cart-drawer .astra-cart-drawer-content .widget_shopping_cart_content > .woocommerce-mini-cart__total,
        .astra-cart-drawer .astra-cart-drawer-content .widget_shopping_cart_content > .woocommerce-mini-cart__buttons {
            flex-shrink: 0 !important;
            background: #ffffff;
        }

        .astra-cart-drawer .astra-cart-drawer-content .widget_shopping_cart_content > .woocommerce-mini-cart__total {
            border-top: 1px solid #ececec;
            padding: 12px 15px !important;
            margin: 0 !important;
        }

        .astra-cart-drawer .astra-cart-drawer-content .widget_shopping_cart_content > .woocommerce-mini-cart__buttons {
            padding: 12px 15px !important;
            margin: 0 !important;
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
     * Astra Cart Text overrides
     * 
     * 1. Changes "Shopping Cart" to "Kosár"
     * 2. Changes "Continue Shopping" to "Vásárlás folytatása"
     * 3. Changes "No products in the cart." to "A kosarad üres."
     */
    public function add_cart_text_overrides() {
        if (is_admin()) return;
        ?>
        <script type="text/javascript">
        (function($) {
            function changeCartTexts() {
                // 1. Kosár cím
                $('.astra-cart-drawer-title').each(function() {
                    var el = $(this);
                    if (el.text().trim() !== 'Kosár') {
                        el.text('Kosár');
                    }
                });
                
                // 2. Vásárlás folytatása gomb
                $('.ast-continue-shopping').each(function() {
                    var el = $(this);
                    if (el.text().trim() !== 'Vásárlás folytatása') {
                        el.text('Vásárlás folytatása');
                    }
                });
                
                // 3. Üres kosár üzenet
                $('.woocommerce-mini-cart__empty-message').each(function() {
                    var el = $(this);
                    // Csak ha nem magyar (bár a trim miatt lehet, hogy változik)
                    if (el.text().trim() !== 'A kosarad üres.') {
                        el.text('A kosarad üres.');
                    }
                });
            }

            $(document).ready(function() {
                // Azonnali futtatás
                changeCartTexts();
                setTimeout(changeCartTexts, 500); // Késleltetve is, biztos ami biztos

                // WooCommerce eseményekre
                $(document.body).on('wc_fragments_refreshed wc_fragments_loaded added_to_cart', function() {
                    setTimeout(changeCartTexts, 100);
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
                        changeCartTexts();
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
