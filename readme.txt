=== WordPress Simple Paypal Shopping Cart ===
Contributors: Ruhul Amin
Donate link: http://www.tipsandtricks-hq.com
Tags: WordPress shopping cart, Paypal shopping cart, online shop, shopping cart, wordperss ecommerce, sell digital products
Requires at least: 2.6
Tested up to: 3.3
Stable tag:3.2.7

Very easy to use Simple WordPress Paypal Shopping Cart Plugin. Great for selling products online in one click from your WordPress site.

== Description ==

WordPress Simple Paypal Shopping Cart allows you to add an 'Add to Cart' button on any posts or pages. It also allows you to add/display the shopping cart on any post or page or sidebar easily. The shopping cart shows the user what they currently have in the cart and allows them to remove the items. It can be easily integrated with the NextGen Photo Gallery plugin too.

For video tutorial, screenshots, detailed documentation, support and updates, please visit:
http://www.tipsandtricks-hq.com/?p=768
or
http://www.tipsandtricks-hq.com/ecommerce/wp-shopping-cart

== Usage ==
1. To add the 'Add to Cart' button simply add the trigger text [wp_cart:PRODUCT-NAME:price:PRODUCT-PRICE:end] to a post or page next to the product. Replace PRODUCT-NAME and PRODUCT-PRICE with the actual name and price.
2. To add the 'Add to Cart' button on the sidebar or from other template files use the following function:
<?php echo print_wp_cart_button_for_product('PRODUCT-NAME', PRODUCT-PRICE); ?>
Replace PRODUCT-NAME and PRODUCT-PRICE with the actual name and price.
3. To add the shopping cart to a post or page (eg. checkout page) simply add the shortcode [show_wp_shopping_cart] to a post or page or use the sidebar widget to add the shopping cart to the sidebar. The shopping cart will only be visible in a post or page when a customer adds a product.

Using Shipping
1. To use shipping cost use the following trigger text
[wp_cart:PRODUCT-NAME:price:PRODUCT-PRICE:shipping:SHIPPING-COST:end]

or use the following php function from your wordpress template files
<?php echo print_wp_cart_button_for_product('product name',price,shipping cost); ?>

Using Variation Control
1. To use variation control use the following trigger text
[wp_cart:PRODUCT-NAME:price:PRODUCT-PRICE:var1[VARIATION-NAME|VARIATION1|VARIATION2|VARIATION3]:end]

eg. [wp_cart:Demo Product 1:price:15:var1[Size|Small|Medium|Large]:end]

2. To use variation control with shipping use the following trigger text:
[wp_cart:PRODUCT-NAME:price:PRODUCT-PRICE:shipping:SHIPPING-COST:var1[VARIATION-NAME|VARIATION1|VARIATION2|VARIATION3]:end]

eg. [wp_cart:Demo Product 1:price:15:shipping:2:var1[Size|Small|Medium|Large]:end]

3. To use multiple variation option use the following trigger text:
[wp_cart:PRODUCT-NAME:price:PRODUCT-PRICE:var1[VARIATION-NAME|VARIATION1|VARIATION2|VARIATION3]:var2[VARIATION-NAME|VARIATION1|VARIATION2]:end]

eg. [wp_cart:Demo Product 1:price:15:shipping:2:var1[Size|Small|Medium|Large]:var2[Color|Red|Green]:end]

== Installation ==

1. Unzip and Upload the folder 'wordpress-paypal-shopping-cart' to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings and configure the options eg. your email, Shopping Cart name, Return URL etc.
4. Use the trigger text to add a product to a post or page where u want it to appear.

== Frequently Asked Questions ==
1. Can this plugin be used to accept paypal payment for a service or a product? Yes
2. Does this plugin have shopping cart? Yes.
3. Can the shopping cart be added to a checkout page? Yes.
4. Does this plugin has multiple currency support? Yes.
5. Is the 'Add to Cart' button customizable? Yes.
6. Does this plugin use a return URL to redirect customers to a specified page after Paypal has processed the payment? Yes.


== Screenshots ==
Visit the plugin site at http://www.tipsandtricks-hq.com/?p=768 for screenshots.

== Changelog ==
Changelog can be found at the following URL
http://www.tipsandtricks-hq.com/ecommerce/?p=319


