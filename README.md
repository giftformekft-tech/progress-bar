# Slide Cart Progress Bar

This plugin adds a milestone-based progress bar to a slide-out mini cart (commonly used in WooCommerce stores) and ensures the component has enough vertical breathing room so that the milestone icons no longer overlap the descriptive text.

## Features

- Injects a milestone progress bar into the mini cart before the checkout buttons.
- Ships with spacious, responsive styling tuned for slide-out layouts.
- Provides accessible markup ready for dynamic progress values via JavaScript or PHP.

## Installation

1. Copy the plugin folder into your WordPress installation's `wp-content/plugins` directory.
2. Activate **Slide Cart Progress Bar** from the WordPress admin.
3. Open the slide mini cart to confirm the progress bar renders with the improved spacing.

## Customisation

- Update the `$milestones` array inside `progress-bar.php` to change the milestone labels or order.
- Modify `assets/css/progress-bar.css` to adapt the layout and palette to your storefront.
- Adjust the `--scpb-progress` inline style to match the shopper's current cart status.

The CSS included in this repository intentionally increases the padding and layout gaps inside the component so the progress bar fits without forcing the mini cart to scroll.
