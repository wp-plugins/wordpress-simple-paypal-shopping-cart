=== WordPress Simple Paypal Shopping Cart ===
Contributors: Ruhul Amin, Tips and Tricks HQ
Donate link: http://www.tipsandtricks-hq.com
Tags: cart, shopping cart, WordPress shopping cart, Paypal shopping cart, sell products, online shop, shop, e-commerce, wordpress ecommerce, wordpress store, store, PayPal cart widget, sell digital products, digital downloads, paypal, paypal cart, e-shop, compact cart,
Requires at least: 3.0
Tested up to: 3.8.1
Stable tag: 3.9.4
License: GPLv2 or later

Very easy to use Simple WordPress Paypal Shopping Cart Plugin. Great for selling products online in one click from your WordPress site.

== Description ==

WordPress Simple Paypal Shopping Cart allows you to add an 'Add to Cart' button for your product on any posts or pages. This simple shopping cart plugin lets you sell products and services directly from your own wordpress site and turns your WP blog into an ecommerce site.

It also allows you to add/display the shopping cart on any post or page or sidebar easily. The shopping cart shows the user what they currently have in the cart and allows them to change quantity or remove the items. 

http://www.youtube.com/watch?v=tEZWfTmZ2kk

It can be easily integrated with the NextGen Photo Gallery plugin to accommodate the selling of photographs from your gallery.

WP simple Paypal Cart Plugin, interfaces with the Paypal sandbox to allow for testing.

This plugin is a lightweight solution (with minimal number of lines of code and minimal options) so it doesn't slow down your site.

For video tutorial, screenshots, detailed documentation, support and updates, please visit:
[WP Simple Cart Details Page](http://www.tipsandtricks-hq.com/wordpress-simple-paypal-shopping-cart-plugin-768)
or
[WP Simple Cart Documentation](http://www.tipsandtricks-hq.com/ecommerce/wp-shopping-cart)

= Features =

* Easily create "add to cart" button with options if needed (price, shipping, options variations). The cart's shortcode can be displayed on posts or pages.
* Use a function to add dynamic "add to cart" button directly in your theme.
* Minimal number of configuration items to keep the plugin lightweight.
* Sell any kind of tangible products from your site.
* Sell any type of media file that you upload to your WordPress site. For example: you can sell ebooks (PDF), music (MP3), videos, photos etc.
* Your customers will automatically get an email with the media file that they paid for.
* Show a nicely formatted product display box on the fly using a simple shortcode.
* You can use Paypal sandbox to do testing if needed (before you go live).
* Collect special instructions from your customers on the PayPal checkout page.
* The orders menu will show you all the orders that you have received from your site.
* Ability to configure an email that will get sent to your buyers after they purchase your product.
* Ability to configure discount coupons.
* You can create coupons and give to your customers. When they use coupons during the checkout they will receive a discount.
* Compatible with WordPress Multi-site Installation.
* Ability to specify SKU (item number) for each of your products in the shortcode.
* Ability to customize the add to cart button image and use a custom image for your purchase buttons.
* Track coupons with the order to see which customer used which coupon code.
* Ability to add a compact shopping cart to your site using a shortcode.
* Can be translated into any language.
* and more...

= Note =

There are a few exact duplicate copies of this plugin that other people made. We have a few users who are getting confused as to which one is the original simple shopping cart plugin. This is the original simple PayPal shopping cart and you can verify it with the following information:

* Check the stats tab of the plugin and you will be able to see a history of when this plugin was first added to WordPress.
* Check the number of downloads on the sidebar. The original plugin always gets more downloads than the copycats.
* Check the number of ratings. The original plugin should have more votes.
* Check the developer's site.

== Usage ==
1. To add an 'Add to Cart' button for a product, simply add the shortcode [wp_cart_button name="PRODUCT-NAME" price="PRODUCT-PRICE"] to a post or page next to the product. Replace PRODUCT-NAME and PRODUCT-PRICE with the actual name and price.

2. To add the 'Add to Cart' button on the sidebar or from other template files use the following function:
<?php echo print_wp_cart_button_for_product('PRODUCT-NAME', PRODUCT-PRICE); ?>
Replace PRODUCT-NAME and PRODUCT-PRICE with the actual name and price.

3. To add the shopping cart to a post or page (eg. checkout page) simply add the shortcode [show_wp_shopping_cart] to a post or page or use the sidebar widget to add the shopping cart to the sidebar. The shopping cart will only be visible in a post or page when a customer adds a product.

= Using Product Display Box =

Here is an exmaple shortcode that shows you how to use a product display box.

[wp_cart_display_product name="My Awesome Product" price="25.00" thumbnail="http://www.example.com/images/product-image.jpg" description="This is a short description of the product"]

Simply replace the values with your product specific data

= Using a compact shopping cart =

Add the following shortcode where you want to show the compact shopping cart:

[wp_compact_cart]

= Using Shipping =

1. To use shipping cost for your product, use the "shipping" parameter. Here is an example shortcode usage:
[wp_cart_button name="Test Product" price="19.95" shipping="4.99"]

or use the following php function from your wordpress template files
<?php echo print_wp_cart_button_for_product('product name',price,shipping cost); ?>

= Using Variation Control =

1. To use variation control use the variation parameter in the shortcode:
[wp_cart_button name="Test Product" price="25.95" var1="VARIATION-NAME|VARIATION1|VARIATION2|VARIATION3"]

example usage: [wp_cart_button name="Test Product" price="29.95" var1="Size|small|medium|large"]

2. To use multiple variation for a product (2nd or 3rd variation), use the following:

[wp_cart_button name="Test Product" price="29.95" var1="Size|small|medium|large" var2="Color|red|green|blue"]

[wp_cart_button name="Test Product" price="29.95" var1="Size|small|medium|large" var2="Color|red|green|blue" var3="Sleeve|short|full"]

== Installation ==

1. Unzip and Upload the folder 'wordpress-paypal-shopping-cart' to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings and configure the options (for example: your email, Shopping Cart name, Return URL etc.)
4. Use the trigger text to add a product to a post or page where u want it to appear.

== Frequently Asked Questions ==
1. Can this plugin be used to accept paypal payment for a service or a product? Yes
2. Does this plugin have shopping cart? Yes.
3. Can the shopping cart be added to a checkout page? Yes.
4. Does this plugin has multiple currency support? Yes.
5. Is the 'Add to Cart' button customizable? Yes.
6. Does this plugin use a return URL to redirect customers to a specified page after Paypal has processed the payment? Yes.
7. How can I add a buy button on the sidebar widget of my site?
Check the documentation on [how to add buy buttons to the sidebar](http://www.tipsandtricks-hq.com/ecommerce/wordpress-shopping-cart-additional-resources-322#add_button_in_sidebar)
8. Can I use this plugin to sell digital downloads? 
Yes. See the [digital download usage documnentation] (http://www.tipsandtricks-hq.com/ecommerce/wp-simple-cart-sell-digital-downloads-2468)

== Screenshots ==
Visit the plugin site at http://www.tipsandtricks-hq.com/?p=768 for screenshots.

== Upgrade Notice ==

None

== Changelog ==

= 3.9.4 =
- Fixed a minor bug in the new compact cart shortcode [wp_compact_cart]

= 3.9.3 =
- Added a new feature to show a compact shopping cart. You can show the compact shopping cart anywhere on your site (example: sidebar, header etc).
- Language translation strings updated. Translation instruction here - http://www.tipsandtricks-hq.com/ecommerce/translating-the-wp-simple-shopping-cart-plugin-2627
- Added a new function for getting the total cart item quantity (wpspc_get_total_cart_qty).
- Added a new function to get the sub total amount of the cart (wpspc_get_total_cart_sub_total).

= 3.9.2 =
- Added an option to specify a custom button image for the add to cart buttons. You can use the "button_image" parameter in the shortcode to customize the add to cart button image.
- Coupon code that is used in a transaciton will be saved with the order so you can see it in the back end.

= 3.9.1 =
- WP 3.8 compatibility

= 3.9.0 and 3.8.9 =
- WP Super Cache workaround - http://www.tipsandtricks-hq.com/ecommerce/wp-shopping-cart-and-wp-super-cache-workaround-334
- Added a new shortcode argument to specify a SKU number for your product.
- Fixed a few debug warnings/notices
- Added Italian language file

= 3.8.8 =
- Added a discount coupon feature to the shopping cart. You can now configure discount coupon via the Simple cart settings -> Coupon/Discount menu
- View link now shows the order details
- fixed a bug where the shipping price wasn't properly showing for more than $1000
- WordPress 3.7 compatibility

= 3.8.7 =
- Changed a few function names and made them unique to reduce the chance of a function name conflict with another plugin.
- Added a new option in the plugin so the purchased items of a transaction will be shown under orders menu
- Payment notification will only be processed when the status is completed.

= 3.8.6 =
- Updated the broken settings menu link
- Updated the NextGen gallery integration to return $arg1 rather than $arg2

= 3.8.5 =
- Added an email settings menu where the site admin can customize the buyer email that gets sent after a transaction
- Also, added the following dynamic email tags for the email body field:

{first_name} First name of the buyer
{last_name} Last name of the buyer
{product_details} The item details of the purchased product (this will include the download link for digital items).

= 3.8.4 =
- Fixing an issue that resulted from doing a commit when wordpress.org plugin repository was undergoing maintenance

= 3.8.3 =
- Improved the settings menu interface with the new shortcode usage instruction.

Full changelog for all versions can be found at the following URL:
http://www.tipsandtricks-hq.com/ecommerce/?p=319
