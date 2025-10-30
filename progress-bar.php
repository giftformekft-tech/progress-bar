<?php
/**
 * Plugin Name:       Slide Cart Progress Bar
 * Description:       Adds a milestone based progress bar to the slide-out mini cart with improved spacing.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            AI Assistant
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       slide-cart-progress-bar
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Slide_Cart_Progress_Bar' ) ) {
    /**
     * Main plugin class responsible for rendering the progress bar inside the mini cart.
     */
    final class Slide_Cart_Progress_Bar {
        /**
         * Singleton instance.
         *
         * @var Slide_Cart_Progress_Bar|null
         */
        private static ?Slide_Cart_Progress_Bar $instance = null;

        /**
         * Ensures that the plugin boots only once.
         */
        public static function instance(): Slide_Cart_Progress_Bar {
            if ( null === self::$instance ) {
                self::$instance = new self();
            }

            return self::$instance;
        }

        /**
         * Slide_Cart_Progress_Bar constructor.
         */
        private function __construct() {
            add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
            add_action( 'woocommerce_widget_shopping_cart_before_buttons', [ $this, 'render_progress_bar' ], 15 );
        }

        /**
         * Registers the plugin's assets.
         */
        public function enqueue_assets(): void {
            $suffix    = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
            $css_path  = plugin_dir_url( __FILE__ ) . "assets/css/progress-bar{$suffix}.css";
            $version   = defined( 'WP_DEBUG' ) && WP_DEBUG ? time() : '1.0.0';

            wp_register_style( 'slide-cart-progress-bar', $css_path, [], $version );
            wp_enqueue_style( 'slide-cart-progress-bar' );
        }

        /**
         * Renders the progress bar markup inside the mini cart drawer.
         */
        public function render_progress_bar(): void {
            $milestones = [
                [
                    'label'   => __( 'Cart Created', 'slide-cart-progress-bar' ),
                    'message' => __( 'Start your journey', 'slide-cart-progress-bar' ),
                    'status'  => 'complete',
                ],
                [
                    'label'   => __( 'Free Shipping', 'slide-cart-progress-bar' ),
                    'message' => __( 'Add items worth $20', 'slide-cart-progress-bar' ),
                    'status'  => 'current',
                ],
                [
                    'label'   => __( 'Checkout', 'slide-cart-progress-bar' ),
                    'message' => __( 'Proceed to payment', 'slide-cart-progress-bar' ),
                    'status'  => 'upcoming',
                ],
            ];

            ?>
            <section class="scpb" aria-labelledby="scpb__title">
                <header class="scpb__header">
                    <p id="scpb__eyebrow" class="scpb__eyebrow"><?php esc_html_e( 'Order progress', 'slide-cart-progress-bar' ); ?></p>
                    <h3 id="scpb__title" class="scpb__title"><?php esc_html_e( 'You are close to free shipping!', 'slide-cart-progress-bar' ); ?></h3>
                </header>

                <div class="scpb__track" role="presentation">
                    <div class="scpb__track-bar" style="--scpb-progress: 50%;"></div>
                </div>

                <ol class="scpb__milestones" role="list">
                    <?php foreach ( $milestones as $milestone ) : ?>
                        <li class="scpb__milestone scpb__milestone--<?php echo esc_attr( $milestone['status'] ); ?>">
                            <span class="scpb__milestone-icon" aria-hidden="true"></span>
                            <span class="scpb__milestone-label"><?php echo esc_html( $milestone['label'] ); ?></span>
                            <span class="scpb__milestone-message"><?php echo esc_html( $milestone['message'] ); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </section>
            <?php
        }
    }
}

Slide_Cart_Progress_Bar::instance();
