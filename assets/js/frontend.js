/**
 * Gift Progress Bar - Frontend JavaScript
 */

(function($) {
    'use strict';
    
    // Initialize on document ready
    $(document).ready(function() {
        GPB_Frontend.init();
        GPB_Frontend.removeDuplicates(); // Remove any duplicates on load
    });
    
    var GPB_Frontend = {
        
        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
            this.initAnimations();
        },
        
        /**
         * Remove duplicate progress bars from DOM
         */
        removeDuplicates: function() {
            // Remove duplicate main progress bars (keep only first)
            $('.gpb-progress-bar-wrapper').each(function(index) {
                if (index > 0) {
                    $(this).remove();
                }
            });
            
            // Remove duplicate mini cart progress bars (keep only first)
            $('.gpb-mini-cart-progress').each(function(index) {
                if (index > 0) {
                    $(this).remove();
                }
            });
            
            // Fix positioning after cleanup
            this.fixMilestonePositioning();
        },
        
        /**
         * Bind events
         */
        bindEvents: function() {
            var self = this;
            
            // Update on cart updates
            $(document.body).on('updated_cart_totals updated_checkout', function() {
                self.updateProgressBar();
                // Remove duplicates after update
                setTimeout(function() {
                    self.removeDuplicates();
                }, 100);
            });
            
            // Listen for WooCommerce fragments update
            $(document.body).on('wc_fragments_refreshed wc_fragments_loaded', function() {
                self.initAnimations();
                // Remove duplicates after fragments refresh
                setTimeout(function() {
                    self.removeDuplicates();
                }, 100);
            });
            
            // Listen for mini cart opening
            $(document).on('click', '.cart-contents, [href*="cart"]', function() {
                setTimeout(function() {
                    self.removeDuplicates();
                }, 200);
            });
        },
        
        /**
         * Initialize animations
         */
        initAnimations: function() {
            var $wrapper = $('#gpb-progress-bar-wrapper');
            
            if ($wrapper.length === 0) {
                return;
            }
            
            // Animate progress bar on load
            var $fill = $wrapper.find('.gpb-progress-bar-fill');
            var targetWidth = $fill.css('width');
            
            $fill.css('width', '0');
            
            setTimeout(function() {
                $fill.css('width', targetWidth);
            }, 100);
            
            // Force milestone horizontal positioning
            this.fixMilestonePositioning();
            
            // Stagger milestone animations
            var $milestones = $wrapper.find('.gpb-milestone');
            $milestones.each(function(index) {
                var $milestone = $(this);
                $milestone.css('opacity', '0');
                
                setTimeout(function() {
                    $milestone.css({
                        'opacity': '1',
                        'transition': 'opacity 0.3s ease'
                    });
                }, 200 + (index * 100));
            });
        },
        
        /**
         * Fix milestone positioning - ensure horizontal layout
         */
        fixMilestonePositioning: function() {
            $('.gpb-mini-milestone, .gpb-milestone').each(function() {
                var $milestone = $(this);
                
                // Force absolute positioning
                $milestone.css({
                    'position': 'absolute',
                    'display': 'block',
                    'transform': 'translateX(-50%)'
                });
            });
        },
        
        /**
         * Update progress bar via AJAX
         */
        updateProgressBar: function() {
            var self = this;
            var $wrapper = $('#gpb-progress-bar-wrapper');
            
            if ($wrapper.length === 0) {
                return;
            }
            
            $wrapper.addClass('loading');
            
            $.ajax({
                url: gpbData.ajax_url,
                type: 'POST',
                data: {
                    action: 'gpb_get_progress',
                    nonce: gpbData.nonce
                },
                success: function(response) {
                    if (response.success) {
                        self.updateDisplay(response.data);
                        $wrapper.addClass('success');
                        
                        setTimeout(function() {
                            $wrapper.removeClass('success');
                        }, 600);
                    }
                },
                error: function() {
                    console.error('Failed to update progress bar');
                },
                complete: function() {
                    $wrapper.removeClass('loading');
                }
            });
        },
        
        /**
         * Update display with new data
         */
        updateDisplay: function(data) {
            var $wrapper = $('#gpb-progress-bar-wrapper');
            
            // Update progress bar
            var $fill = $wrapper.find('.gpb-progress-bar-fill');
            $fill.css('width', data.progress_percent + '%');
            
            // Update message
            var message = this.getProgressMessage(data);
            $wrapper.find('.gpb-message').html(message);
            
            // Update milestones
            this.updateMilestones(data);
        },
        
        /**
         * Get progress message
         */
        getProgressMessage: function(data) {
            if (data.all_completed) {
                return '<span class="gpb-message-icon">🥳</span> <strong>Gratulálunk! Maximális ajándékcsomagot értél el!</strong>';
            }
            
            var message = '';
            
            if (data.current_level) {
                message = '<span class="gpb-message-icon">🎉</span> <strong>Gratulálunk!</strong> ' +
                         'Már jogosult vagy a következőre: <strong>' + data.current_level.reward + '</strong>';
                
                if (data.next_level) {
                    message += ' Már csak <strong>' + this.formatPrice(data.amount_needed) + '</strong> ' +
                              'kell a következő ajándékhoz: <strong>' + data.next_level.reward + '</strong>';
                }
            } else if (data.next_level) {
                message = 'Már csak <strong>' + this.formatPrice(data.amount_needed) + '</strong> ' +
                         'kell az ajándékhoz: <strong>' + data.next_level.reward + '</strong>';
            }
            
            return message;
        },
        
        /**
         * Update milestones
         */
        updateMilestones: function(data) {
            var $wrapper = $('#gpb-progress-bar-wrapper');
            var $milestones = $wrapper.find('.gpb-milestone');
            
            $milestones.each(function(index) {
                var $milestone = $(this);
                var isCompleted = data.completed_levels.indexOf(index) !== -1;
                var isActive = !isCompleted && data.next_level && 
                              parseFloat($milestone.data('amount')) === parseFloat(data.next_level.amount);
                
                $milestone.removeClass('completed active');
                
                if (isCompleted) {
                    $milestone.addClass('completed');
                } else if (isActive) {
                    $milestone.addClass('active');
                }
            });
        },
        
        /**
         * Format price
         */
        formatPrice: function(amount) {
            return new Intl.NumberFormat('hu-HU', {
                style: 'currency',
                currency: 'HUF',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(amount);
        }
    };
    
    // Make available globally
    window.GPB_Frontend = GPB_Frontend;
    
})(jQuery);
