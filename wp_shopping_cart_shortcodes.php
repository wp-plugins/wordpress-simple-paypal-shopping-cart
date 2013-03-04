<?php

function wp_cart_button_handler($atts){
	extract(shortcode_atts(array(
		'name' => '',
		'price' => '',
		'shipping' => '0',
	), $atts));

	if(empty($name)){
		return '<div style="color:red;">Error! You must specify a product name in the shortcode.</div>';
	}
	if(empty($price)){
		return '<div style="color:red;">Error! You must specify a price for your product in the shortcode.</div>';
	}
	return print_wp_cart_button_for_product($name, $price, $shipping);
}