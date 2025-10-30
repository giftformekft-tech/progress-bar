/**
 * Gift Progress Bar - Admin JavaScript
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        GPB_Admin.init();
    });
    
    var GPB_Admin = {
        
        thresholdIndex: 0,
        
        /**
         * Initialize
         */
        init: function() {
            this.initColorPickers();
            this.initSortable();
            this.bindEvents();
            this.updateThresholdIndex();
        },
        
        /**
         * Initialize color pickers
         */
        initColorPickers: function() {
            $('.gpb-color-picker').wpColorPicker();
        },
        
        /**
         * Initialize sortable
         */
        initSortable: function() {
            $('#gpb-thresholds-list').sortable({
                handle: '.gpb-drag-handle',
                placeholder: 'gpb-threshold-item ui-sortable-placeholder',
                start: function(e, ui) {
                    ui.placeholder.height(ui.item.height());
                },
                update: function() {
                    // Update indexes after sorting
                    GPB_Admin.updateThresholdIndexes();
                }
            });
        },
        
        /**
         * Bind events
         */
        bindEvents: function() {
            var self = this;
            
            // Add threshold
            $('#gpb-add-threshold').on('click', function(e) {
                e.preventDefault();
                self.addThreshold();
            });
            
            // Remove threshold
            $(document).on('click', '.gpb-remove-threshold', function(e) {
                e.preventDefault();
                self.removeThreshold($(this));
            });
            
            // Update icon preview
            $(document).on('input', 'input[name*="[icon]"]', function() {
                self.updateIconPreview($(this));
            });
        },
        
        /**
         * Add threshold
         */
        addThreshold: function() {
            var template = this.getThresholdTemplate(this.thresholdIndex);
            $('#gpb-thresholds-list').append(template);
            this.thresholdIndex++;
            
            // Animate the new item
            var $newItem = $('#gpb-thresholds-list .gpb-threshold-item:last');
            $newItem.hide().slideDown(300);
        },
        
        /**
         * Remove threshold
         */
        removeThreshold: function($button) {
            var $item = $button.closest('.gpb-threshold-item');
            
            if ($('#gpb-thresholds-list .gpb-threshold-item').length <= 1) {
                alert('Legalább egy küszöbértéket meg kell tartani!');
                return;
            }
            
            if (confirm('Biztosan törölni szeretnéd ezt a küszöbértéket?')) {
                $item.slideUp(300, function() {
                    $(this).remove();
                    GPB_Admin.updateThresholdIndexes();
                });
            }
        },
        
        /**
         * Get threshold template
         */
        getThresholdTemplate: function(index) {
            return `
                <div class="gpb-threshold-item" data-index="${index}">
                    <span class="gpb-drag-handle dashicons dashicons-move"></span>
                    
                    <div class="gpb-threshold-fields">
                        <div class="gpb-field">
                            <label>Összeg (Ft)</label>
                            <input type="number" name="gpb_thresholds[${index}][amount]" 
                                   value="" min="0" step="1" required>
                        </div>
                        
                        <div class="gpb-field">
                            <label>Jutalom leírás</label>
                            <input type="text" name="gpb_thresholds[${index}][reward]" 
                                   value="" placeholder="pl. Ingyenes szállítás" required>
                        </div>
                        
                        <div class="gpb-field">
                            <label>Ikon (Dashicon osztály)</label>
                            <input type="text" name="gpb_thresholds[${index}][icon]" 
                                   value="dashicons-cart" placeholder="dashicons-cart">
                            <span class="dashicons dashicons-cart gpb-icon-preview"></span>
                        </div>
                    </div>
                    
                    <button type="button" class="gpb-remove-threshold button" title="Törlés">
                        <span class="dashicons dashicons-trash"></span>
                    </button>
                </div>
            `;
        },
        
        /**
         * Update threshold indexes after sorting/removal
         */
        updateThresholdIndexes: function() {
            $('#gpb-thresholds-list .gpb-threshold-item').each(function(index) {
                $(this).attr('data-index', index);
                $(this).find('input').each(function() {
                    var name = $(this).attr('name');
                    if (name) {
                        var newName = name.replace(/\[\d+\]/, '[' + index + ']');
                        $(this).attr('name', newName);
                    }
                });
            });
        },
        
        /**
         * Update threshold index counter
         */
        updateThresholdIndex: function() {
            var maxIndex = 0;
            $('#gpb-thresholds-list .gpb-threshold-item').each(function() {
                var index = parseInt($(this).attr('data-index'));
                if (index > maxIndex) {
                    maxIndex = index;
                }
            });
            this.thresholdIndex = maxIndex + 1;
        },
        
        /**
         * Update icon preview
         */
        updateIconPreview: function($input) {
            var iconClass = $input.val();
            var $preview = $input.siblings('.gpb-icon-preview');
            
            // Remove all dashicons classes
            $preview.removeClass(function(index, className) {
                return (className.match(/(^|\s)dashicons-\S+/g) || []).join(' ');
            });
            
            // Add new icon class
            if (iconClass) {
                $preview.addClass(iconClass);
            } else {
                $preview.addClass('dashicons-cart');
            }
        }
    };
    
    // Make available globally
    window.GPB_Admin = GPB_Admin;
    
})(jQuery);
