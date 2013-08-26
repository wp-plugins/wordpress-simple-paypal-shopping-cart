<?php

function wp_cart_button_handler($atts){
	extract(shortcode_atts(array(
		'name' => '',
		'price' => '',
		'shipping' => '0',
		'var1' => '',
		'var2' => '',
		'var3' => '',
	), $atts));

	if(empty($name)){
		return '<div style="color:red;">Error! You must specify a product name in the shortcode.</div>';
	}
	if(empty($price)){
		return '<div style="color:red;">Error! You must specify a price for your product in the shortcode.</div>';
	}
	return print_wp_cart_button_for_product($name, $price, $shipping, $var1, $var2, $var3, $atts);
}

function wp_cart_display_product_handler($atts)
{
    extract(shortcode_atts(array(
        'name' => '',
        'price' => '',
        'shipping' => '0',
		'var1' => '',
		'var2' => '',
		'var3' => '',    
        'thumbnail' => '',
        'description' => '',    
    ), $atts));

    if(empty($name)){
            return '<div style="color:red;">Error! You must specify a product name in the shortcode.</div>';
    }
    if(empty($price)){
            return '<div style="color:red;">Error! You must specify a price for your product in the shortcode.</div>';
    }
    if(empty($thumbnail)){
            return '<div style="color:red;">Error! You must specify a thumbnail image for your product in the shortcode.</div>';
    }
    $currency_symbol = get_option('cart_currency_symbol');
    $button_code = print_wp_cart_button_for_product($name, $price, $shipping, $var1, $var2, $var3, $atts);
    $display_code = <<<EOT
    <div class="wp_cart_product_display_box">
        <div class="wp_cart_product_thumbnail">
            <img src="$thumbnail" />
        </div>
        <div class="wp_cart_product_display_bottom">
	        <div class="wp_cart_product_name">
	            $name
	        </div>
	        <div class="wp_cart_product_description">
	            $description
	        </div>
			<div class="wp_cart_product_price">
	        	{$currency_symbol}{$price}
	        </div>
			<div class="wp_cart_product_button">
	        	$button_code
			</div>
		</div>
    </div>
EOT;
    return $display_code; 
}
