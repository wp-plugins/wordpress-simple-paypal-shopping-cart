<?php
/*
Plugin Name: WP Simple Paypal Shopping cart
Version: v3.5
Plugin URI: http://www.tipsandtricks-hq.com/?p=768
Author: Ruhul Amin
Author URI: http://www.tipsandtricks-hq.com/
Description: Simple WordPress Shopping Cart Plugin, very easy to use and great for selling products and services from your blog!
*/

/*
    This program is free software; you can redistribute it
    under the terms of the GNU General Public License version 2,
    as published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
*/
if(!isset($_SESSION)){
	session_start();
}	

define('WP_CART_FOLDER', dirname(plugin_basename(__FILE__)));
define('WP_CART_URL', plugins_url('',__FILE__));

define('WP_CART_LIVE_PAYPAL_URL', 'https://www.paypal.com/cgi-bin/webscr');
define('WP_CART_SANDBOX_PAYPAL_URL', 'https://www.sandbox.paypal.com/cgi-bin/webscr');

// loading language files
load_plugin_textdomain('WSPSC', false, WP_CART_FOLDER . '/languages');

add_option('wp_cart_title', __("Your Shopping Cart", "WSPSC"));
add_option('wp_cart_empty_text', __("Your cart is empty", "WSPSC"));
add_option('cart_return_from_paypal_url', get_bloginfo('wpurl'));

include_once('wp_shopping_cart_shortcodes.php');

function always_show_cart_handler($atts) 
{
	return print_wp_shopping_cart();
}

function show_wp_shopping_cart_handler()
{
    if (cart_not_empty())
    {
       	$output = print_wp_shopping_cart();
    }
    return $output;	
}

function shopping_cart_show($content)
{
	if (strpos($content, "<!--show-wp-shopping-cart-->") !== FALSE)
    {
    	if (cart_not_empty())
    	{
        	$content = preg_replace('/<p>\s*<!--(.*)-->\s*<\/p>/i', "<!--$1-->", $content);
        	$matchingText = '<!--show-wp-shopping-cart-->';
        	$replacementText = print_wp_shopping_cart();
        	$content = str_replace($matchingText, $replacementText, $content);
    	}
    }
    return $content;
}

// Reset the Cart as this is a returned customer from Paypal
if (isset($_GET["merchant_return_link"]) && !empty($_GET["merchant_return_link"]))
{
    reset_wp_cart();
    header('Location: ' . get_option('cart_return_from_paypal_url'));
}

if (isset($_GET["mc_gross"])&&  $_GET["mc_gross"]> 0)
{
    reset_wp_cart();
    header('Location: ' . get_option('cart_return_from_paypal_url'));
}

//Clear the cart if the customer landed on the thank you page
if (get_option('wp_shopping_cart_reset_after_redirection_to_return_page'))
{
	if(get_option('cart_return_from_paypal_url') == cart_current_page_url())
	{
		reset_wp_cart();
	}
}

function reset_wp_cart()
{
    $products = $_SESSION['simpleCart'];
    if(empty($products))
    {
    	unset($_SESSION['simpleCart']);
    	return;
    }
    foreach ($products as $key => $item)
    {
        unset($products[$key]);
    }
    $_SESSION['simpleCart'] = $products;    
}

if (isset($_POST['addcart']))
{
	$domain_url = $_SERVER['SERVER_NAME'];
	$cookie_domain = str_replace("www","",$domain_url);    	
	setcookie("cart_in_use","true",time()+21600,"/",$cookie_domain);  //useful to not serve cached page when using with a caching plugin
   
	//sanitize data
	$_POST['product'] = strip_tags($_POST['product']);//for PHP5.2 use filter_var($_POST['product'], FILTER_SANITIZE_STRING);
	$_POST['item_number'] = strip_tags($_POST['item_number']);
	if(isset($_POST['price']))$_POST['price'] = strip_tags($_POST['price']);
	isset($_POST['shipping'])?$_POST['shipping'] = strip_tags($_POST['shipping']):$_POST['shipping']='';
	isset($_POST['cartLink'])?$_POST['cartLink'] = strip_tags($_POST['cartLink']):$_POST['cartLink']='';

	$count = 1;    
    $products = $_SESSION['simpleCart'];	    
    if (is_array($products))
    {
        foreach ($products as $key => $item)
        {
            if ($item['name'] == stripslashes($_POST['product']))
            {
                $count += $item['quantity'];
                $item['quantity']++;
                unset($products[$key]);
                array_push($products, $item);
            }
        }
    }
    else
    {
        $products = array();
    }
        
    if ($count == 1)
    {
        if (!empty($_POST[$_POST['product']])){
            $price = $_POST[$_POST['product']];
        }
        else{
            $price = $_POST['price'];
        }
        
		$default_cur_symbol = get_option('cart_currency_symbol');
		$price = str_replace($default_cur_symbol,"",$price);
		
		$shipping = $_POST['shipping'];
		$shipping = str_replace($default_cur_symbol,"",$shipping);
            
        $product = array('name' => stripslashes($_POST['product']), 'price' => $price, 'quantity' => $count, 'shipping' => $shipping, 'cartLink' => $_POST['cartLink'], 'item_number' => $_POST['item_number']);
        array_push($products, $product);
    }
    
    sort($products);
    $_SESSION['simpleCart'] = $products;
    
    if (get_option('wp_shopping_cart_auto_redirect_to_checkout_page'))
    {
    	$checkout_url = get_option('cart_checkout_page_url');
    	if(empty($checkout_url))
    	{
    		echo "<br /><strong>".(__("Shopping Cart Configuration Error! You must specify a value in the 'Checkout Page URL' field for the automatic redirection feature to work!", "WSPSC"))."</strong><br />";
    	}
    	else
    	{
	        $redirection_parameter = 'Location: '.$checkout_url;
	        header($redirection_parameter);
	        exit;
    	}
    }    
}
else if (isset($_POST['cquantity']))
{
    $products = $_SESSION['simpleCart'];
    foreach ($products as $key => $item)
    {
        if ((stripslashes($item['name']) == stripslashes($_POST['product'])) && $_POST['quantity'])
        {
            $item['quantity'] = $_POST['quantity'];
            unset($products[$key]);
            array_push($products, $item);
        }
        else if (($item['name'] == stripslashes($_POST['product'])) && !$_POST['quantity'])
            unset($products[$key]);
    }
    sort($products);
    $_SESSION['simpleCart'] = $products;
}
else if (isset($_POST['delcart']))
{
    $products = $_SESSION['simpleCart'];
    foreach ($products as $key => $item)
    {
        if ($item['name'] == stripslashes($_POST['product']))
            unset($products[$key]);
    }
    $_SESSION['simpleCart'] = $products;
}

function print_wp_shopping_cart()
{
	if (!cart_not_empty())
	{
	    $empty_cart_text = get_option('wp_cart_empty_text');
		if (!empty($empty_cart_text)) 
		{
			if (preg_match("/http/", $empty_cart_text))
			{
				$output .= '<img src="'.$empty_cart_text.'" alt="'.$empty_cart_text.'" />';
			}
			else
			{
				$output .= $empty_cart_text;
			}			
		}
		$cart_products_page_url = get_option('cart_products_page_url');
		if (!empty($cart_products_page_url))
		{
			$output .= '<br /><a rel="nofollow" href="'.$cart_products_page_url.'">'.(__("Visit The Shop", "WSPSC")).'</a>';
		}		
		return $output;
	}
    $email = get_bloginfo('admin_email');
    $use_affiliate_platform = get_option('wp_use_aff_platform');   
    $defaultCurrency = get_option('cart_payment_currency');
    $defaultSymbol = get_option('cart_currency_symbol');
    $defaultEmail = get_option('cart_paypal_email');
    if (!empty($defaultCurrency))
        $paypal_currency = $defaultCurrency;
    else
        $paypal_currency = __("USD", "WSPSC");
    if (!empty($defaultSymbol))
        $paypal_symbol = $defaultSymbol;
    else
        $paypal_symbol = __("$", "WSPSC");

    if (!empty($defaultEmail))
        $email = $defaultEmail;
     
    $decimal = '.';  
	$urls = '';
        
    $return = get_option('cart_return_from_paypal_url');
            
    if (!empty($return))
        $urls .= '<input type="hidden" name="return" value="'.$return.'" />';
	
	if ($use_affiliate_platform)  
	{
		if (function_exists('wp_aff_platform_install'))
		{
			$notify = WP_AFF_PLATFORM_URL.'/api/ipn_handler.php';
			//$notify = WP_CART_URL.'/paypal.php';
			$urls .= '<input type="hidden" name="notify_url" value="'.$notify.'" />';
		}
	}
	$title = get_option('wp_cart_title');
	//if (empty($title)) $title = __("Your Shopping Cart", "WSPSC");
    
    global $plugin_dir_name;
    $output .= '<div class="shopping_cart" style=" padding: 5px;">';
    if (!get_option('wp_shopping_cart_image_hide'))    
    {    	
    	$output .= "<img src='".WP_CART_URL."/images/shopping_cart_icon.png' value='".(__("Cart", "WSPSC"))."' title='".(__("Cart", "WSPSC"))."' />";
    }
    if(!empty($title))
    {
    	$output .= '<h2>';
    	$output .= $title;  
    	$output .= '</h2>';
    }
        
    $output .= '<br /><span id="pinfo" style="display: none; font-weight: bold; color: red;">'.(__("Hit enter to submit new Quantity.", "WSPSC")).'</span>';
	$output .= '<table style="width: 100%;">';    
    
    $count = 1;
    $total_items = 0;
    $total = 0;
    $form = '';
    if ($_SESSION['simpleCart'] && is_array($_SESSION['simpleCart']))
    {   
        $output .= '
        <tr>
        <th style="text-align: left">'.(__("Item Name", "WSPSC")).'</th><th>'.(__("Quantity", "WSPSC")).'</th><th>'.(__("Price", "WSPSC")).'</th><th></th>
        </tr>';
    
	    foreach ($_SESSION['simpleCart'] as $item)
	    {
	        $total += $item['price'] * $item['quantity'];
	        $item_total_shipping += $item['shipping'] * $item['quantity'];
	        $total_items +=  $item['quantity'];
	    }
	    if(!empty($item_total_shipping))
	    {
	    	$baseShipping = get_option('cart_base_shipping_cost');
	    	$postage_cost = $item_total_shipping + $baseShipping;
	    }
	    else
	    {
	    	$postage_cost = 0;
	    }
	    
	    $cart_free_shipping_threshold = get_option('cart_free_shipping_threshold');
	    if (!empty($cart_free_shipping_threshold) && $total > $cart_free_shipping_threshold)
	    {
	    	$postage_cost = 0;
	    }

	    foreach ($_SESSION['simpleCart'] as $item)
	    {
	        $output .= "
	        <tr><td style='overflow: hidden;'><a href='".$item['cartLink']."'>".$item['name']."</a></td>
	        <td style='text-align: center'><form method=\"post\"  action=\"\" name='pcquantity' style='display: inline'>
                <input type=\"hidden\" name=\"product\" value=\"".htmlspecialchars($item['name'])."\" />

	        <input type='hidden' name='cquantity' value='1' /><input type='text' name='quantity' value='".$item['quantity']."' size='1' onchange='document.pcquantity.submit();' onkeypress='document.getElementById(\"pinfo\").style.display = \"\";' /></form></td>
	        <td style='text-align: center'>".print_payment_currency(($item['price'] * $item['quantity']), $paypal_symbol, $decimal)."</td>
	        <td><form method=\"post\"  action=\"\">
	        <input type=\"hidden\" name=\"product\" value=\"".$item['name']."\" />
	        <input type='hidden' name='delcart' value='1' />
	        <input type='image' src='".WP_CART_URL."/images/Shoppingcart_delete.png' value='".(__("Remove", "WSPSC"))."' title='".(__("Remove", "WSPSC"))."' /></form></td></tr>
	        ";
	        
	        $form .= "
	            <input type=\"hidden\" name=\"item_name_$count\" value=\"".$item['name']."\" />
	            <input type=\"hidden\" name=\"amount_$count\" value='".number_format($item['price'],2)."' />
	            <input type=\"hidden\" name=\"quantity_$count\" value=\"".$item['quantity']."\" />
	            <input type='hidden' name='item_number' value='".$item['item_number']."' />
	        ";        
	        $count++;
	    }
	    if (!get_option('wp_shopping_cart_use_profile_shipping'))
	    {
	    	$postage_cost = number_format($postage_cost,2);
	    	$form .= "<input type=\"hidden\" name=\"shipping_1\" value='".$postage_cost."' />"; //You can also use "handling_cart" variable to use shipping and handling here 
	    }
	    if (get_option('wp_shopping_cart_collect_address'))//force address collection
	    {
	    	$form .= "<input type=\"hidden\" name=\"no_shipping\" value=\"2\" />";  
	    }	    	    
    }
    
       	$count--;
       	
       	if ($count)
       	{
       		//$output .= '<tr><td></td><td></td><td></td></tr>';  

            if ($postage_cost != 0)
            {
                $output .= "
                <tr><td colspan='2' style='font-weight: bold; text-align: right;'>".(__("Subtotal", "WSPSC")).": </td><td style='text-align: center'>".print_payment_currency($total, $paypal_symbol, $decimal)."</td><td></td></tr>
                <tr><td colspan='2' style='font-weight: bold; text-align: right;'>".(__("Shipping", "WSPSC")).": </td><td style='text-align: center'>".print_payment_currency($postage_cost, $paypal_symbol, $decimal)."</td><td></td></tr>";
            }

            $output .= "
       		<tr><td colspan='2' style='font-weight: bold; text-align: right;'>".(__("Total", "WSPSC")).": </td><td style='text-align: center'>".print_payment_currency(($total+$postage_cost), $paypal_symbol, $decimal)."</td><td></td></tr>
       		<tr><td colspan='4'>";
       
            $paypal_checkout_url = WP_CART_LIVE_PAYPAL_URL;
            if (get_option('wp_shopping_cart_enable_sandbox')){
            	$paypal_checkout_url = WP_CART_SANDBOX_PAYPAL_URL;
            }
              	$output .= '<form action="'.$paypal_checkout_url.'" method="post">'.$form;
    			if ($count)
            		$output .= '<input type="image" src="'.WP_CART_URL.'/images/'.(__("paypal_checkout_EN.png", "WSPSC")).'" name="submit" class="wp_cart_checkout_button" alt="'.(__("Make payments with PayPal - it\'s fast, free and secure!", "WSPSC")).'" />';
       
    			$output .= $urls.'
			    <input type="hidden" name="business" value="'.$email.'" />
			    <input type="hidden" name="currency_code" value="'.$paypal_currency.'" />
			    <input type="hidden" name="cmd" value="_cart" />
			    <input type="hidden" name="upload" value="1" />
			    <input type="hidden" name="rm" value="2" />
			    <input type="hidden" name="charset" value="utf-8" />
			    <input type="hidden" name="mrb" value="3FWGC6LFTMTUG" />';
    			$wp_cart_note_to_seller_text = get_option('wp_cart_note_to_seller_text');
    			if(!empty($wp_cart_note_to_seller_text)){
    				$output .= '<input type="hidden" name="no_note" value="0" /><input type="hidden" name="cn" value="'.$wp_cart_note_to_seller_text.'" />';
    			}
			    if ($use_affiliate_platform){
			    	$output .= wp_cart_add_custom_field();
			    }
			    $output .= '</form>';          
       	}       
       	$output .= "       
       	</td></tr>
    	</table></div>
    	";
    
    return $output;
}

function wp_cart_add_custom_field()
{
	if (function_exists('wp_aff_platform_install'))
	{
		$output = '';
		if (!empty($_SESSION['ap_id']))
		{
			$output = '<input type="hidden" name="custom" value="'.$_SESSION['ap_id'].'" id="wp_affiliate" />';
		}
		else if (isset($_COOKIE['ap_id']))
		{
			$output = '<input type="hidden" name="custom" value="'.$_COOKIE['ap_id'].'" id="wp_affiliate" />';
		}
		return 	$output;
	}
}

function print_wp_cart_button_new($content)
{
        $addcart = get_option('addToCartButtonName');    
        if (!$addcart || ($addcart == '') )
            $addcart = __("Add to Cart", "WSPSC");
            	
        $pattern = '#\[wp_cart:.+:price:.+:end]#';
        preg_match_all ($pattern, $content, $matches);

        foreach ($matches[0] as $match)
        {   
        	$var_output = '';
            $pos = strpos($match,":var1");
			if ($pos)
			{				
				$match_tmp = $match;
				// Variation control is used
				$pos2 = strpos($match,":var2");
				if ($pos2)
				{
					//echo '<br />'.$match_tmp.'<br />';
					$pattern = '#var2\[.*]:#';
				    preg_match_all ($pattern, $match_tmp, $matches3);
				    $match3 = $matches3[0][0];
				    //echo '<br />'.$match3.'<br />';
				    $match_tmp = str_replace ($match3, '', $match_tmp);
				    
				    $pattern = 'var2[';
				    $m3 = str_replace ($pattern, '', $match3);
				    $pattern = ']:';
				    $m3 = str_replace ($pattern, '', $m3);  
				    $pieces3 = explode('|',$m3);
			
				    $variation2_name = $pieces3[0];
				    $var_output .= $variation2_name." : ";
				    $var_output .= '<select name="variation2" onchange="ReadForm (this.form, false);">';
				    for ($i=1;$i<sizeof($pieces3); $i++)
				    {
				    	$var_output .= '<option value="'.$pieces3[$i].'">'.$pieces3[$i].'</option>';
				    }
				    $var_output .= '</select><br />';				    
				}				
			    
			    $pattern = '#var1\[.*]:#';
			    preg_match_all ($pattern, $match_tmp, $matches2);
			    $match2 = $matches2[0][0];

			    $match_tmp = str_replace ($match2, '', $match_tmp);

				    $pattern = 'var1[';
				    $m2 = str_replace ($pattern, '', $match2);
				    $pattern = ']:';
				    $m2 = str_replace ($pattern, '', $m2);  
				    $pieces2 = explode('|',$m2);
			
				    $variation_name = $pieces2[0];
				    $var_output .= $variation_name." : ";
				    $var_output .= '<select name="variation1" onchange="ReadForm (this.form, false);">';
				    for ($i=1;$i<sizeof($pieces2); $i++)
				    {
				    	$var_output .= '<option value="'.$pieces2[$i].'">'.$pieces2[$i].'</option>';
				    }
				    $var_output .= '</select><br />';				

			}

            $pattern = '[wp_cart:';
            $m = str_replace ($pattern, '', $match);
            
            $pattern = 'price:';
            $m = str_replace ($pattern, '', $m);
            $pattern = 'shipping:';
            $m = str_replace ($pattern, '', $m);
            $pattern = ':end]';
            $m = str_replace ($pattern, '', $m);

            $pieces = explode(':',$m);
    
                $replacement = '<div class="wp_cart_button_wrapper">';
                $replacement .= '<form method="post" class="wp-cart-button-form" action="" style="display:inline" onsubmit="return ReadForm(this, true);">';             
                if (!empty($var_output))
                {
                	$replacement .= $var_output;
                } 
				                
				if (preg_match("/http/", $addcart)) // Use the image as the 'add to cart' button
				{
				    $replacement .= '<input type="image" src="'.$addcart.'" class="wp_cart_button" alt="'.(__("Add to Cart", "WSPSC")).'"/>';
				} 
				else 
				{
				    $replacement .= '<input type="submit" value="'.$addcart.'" />';
				} 

                $replacement .= '<input type="hidden" name="product" value="'.$pieces['0'].'" /><input type="hidden" name="price" value="'.$pieces['1'].'" />';
                $replacement .= '<input type="hidden" name="product_tmp" value="'.$pieces['0'].'" />';
                if (sizeof($pieces) >2 )
                {
                	//we have shipping
                	$replacement .= '<input type="hidden" name="shipping" value="'.$pieces['2'].'" />';
                }
                $replacement .= '<input type="hidden" name="cartLink" value="'.cart_current_page_url().'" />';
                $replacement .= '<input type="hidden" name="addcart" value="1" /></form>';
                $replacement .= '</div>';
                $content = str_replace ($match, $replacement, $content);                
        }
        return $content;	
}

function wp_cart_add_read_form_javascript()
{
	echo '
	<script type="text/javascript">
	<!--
	//
	function ReadForm (obj1, tst) 
	{ 
	    // Read the user form
	    var i,j,pos;
	    val_total="";val_combo="";		
	
	    for (i=0; i<obj1.length; i++) 
	    {     
	        // run entire form
	        obj = obj1.elements[i];           // a form element
	
	        if (obj.type == "select-one") 
	        {   // just selects
	            if (obj.name == "quantity" ||
	                obj.name == "amount") continue;
		        pos = obj.selectedIndex;        // which option selected
		        val = obj.options[pos].value;   // selected value
		        val_combo = val_combo + "(" + val + ")";
	        }
	    }
		// Now summarize everything we have processed above
		val_total = obj1.product_tmp.value + val_combo;
		obj1.product.value = val_total;
	}
	//-->
	</script>';	
}
function print_wp_cart_button_for_product($name, $price, $shipping=0)
{
		$addcart = get_option('addToCartButtonName');
        if (!$addcart || ($addcart == '') )
            $addcart = __("Add to Cart", "WSPSC");

		$replacement = '<div class="wp_cart_button_wrapper">';
        $replacement .= '<form method="post" class="wp-cart-button-form" action="" style="display:inline">';
		if (preg_match("/http:/", $addcart)) // Use the image as the 'add to cart' button
		{
			$replacement .= '<input type="image" src="'.$addcart.'" class="wp_cart_button" alt="'.(__("Add to Cart", "WSPSC")).'"/>';
		} 
		else 
		{
		    $replacement .= '<input type="submit" value="'.$addcart.'" />';
		}
        $replacement .= '<input type="hidden" name="product" value="'.$name.'" /><input type="hidden" name="price" value="'.$price.'" /><input type="hidden" name="shipping" value="'.$shipping.'" /><input type="hidden" name="addcart" value="1" /><input type="hidden" name="cartLink" value="'.cart_current_page_url().'" />';
		$replacement .= '</form>';
        $replacement .= '</div>';
        return $replacement;
}

function cart_not_empty()
{
        $count = 0;
        if (isset($_SESSION['simpleCart']) && is_array($_SESSION['simpleCart']))
        {
            foreach ($_SESSION['simpleCart'] as $item)
                $count++;
            return $count;
        }
        else
            return 0;
}

function print_payment_currency($price, $symbol, $decimal)
{
    return $symbol.number_format($price, 2, $decimal, ',');
}

function cart_current_page_url() {
	$pageURL = 'http';
	if(!isset($_SERVER["HTTPS"])){$_SERVER["HTTPS"]="";}
	if(!isset($_SERVER["SERVER_PORT"])){$_SERVER["SERVER_PORT"]="";}

	if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
	$pageURL .= "://";
	if ($_SERVER["SERVER_PORT"] != "80") {
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	}
	else {
		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	}
	return $pageURL;
}

function show_wp_cart_options_page () {	
	$wp_simple_paypal_shopping_cart_version = "3.5";
    if (isset($_POST['info_update']))
    {
        update_option('cart_payment_currency', (string)$_POST["cart_payment_currency"]);
        update_option('cart_currency_symbol', (string)$_POST["cart_currency_symbol"]);
        update_option('cart_base_shipping_cost', (string)$_POST["cart_base_shipping_cost"]);
        update_option('cart_free_shipping_threshold', (string)$_POST["cart_free_shipping_threshold"]);   
        update_option('wp_shopping_cart_collect_address', ($_POST['wp_shopping_cart_collect_address']!='') ? 'checked="checked"':'' );    
        update_option('wp_shopping_cart_use_profile_shipping', ($_POST['wp_shopping_cart_use_profile_shipping']!='') ? 'checked="checked"':'' );
                
        update_option('cart_paypal_email', (string)$_POST["cart_paypal_email"]);
        update_option('addToCartButtonName', (string)$_POST["addToCartButtonName"]);
        update_option('wp_cart_title', (string)$_POST["wp_cart_title"]);
        update_option('wp_cart_empty_text', (string)$_POST["wp_cart_empty_text"]);
        update_option('cart_return_from_paypal_url', (string)$_POST["cart_return_from_paypal_url"]);
        update_option('cart_products_page_url', (string)$_POST["cart_products_page_url"]);
                
        update_option('wp_shopping_cart_auto_redirect_to_checkout_page', ($_POST['wp_shopping_cart_auto_redirect_to_checkout_page']!='') ? 'checked="checked"':'' );
        update_option('cart_checkout_page_url', (string)$_POST["cart_checkout_page_url"]);
        update_option('wp_shopping_cart_reset_after_redirection_to_return_page', ($_POST['wp_shopping_cart_reset_after_redirection_to_return_page']!='') ? 'checked="checked"':'' );        
                
        update_option('wp_shopping_cart_image_hide', ($_POST['wp_shopping_cart_image_hide']!='') ? 'checked="checked"':'' );
        update_option('wp_cart_note_to_seller_text', (string)$_POST["wp_cart_note_to_seller_text"]);
        update_option('wp_use_aff_platform', ($_POST['wp_use_aff_platform']!='') ? 'checked="checked"':'' );
        
        update_option('wp_shopping_cart_enable_sandbox', ($_POST['wp_shopping_cart_enable_sandbox']!='') ? 'checked="checked"':'' );
        
        echo '<div id="message" class="updated fade">';
        echo '<p><strong>'.(__("Options Updated!", "WSPSC")).'</strong></p></div>';
    }	
	
    $defaultCurrency = get_option('cart_payment_currency');    
    if (empty($defaultCurrency)) $defaultCurrency = __("USD", "WSPSC");
    
    $defaultSymbol = get_option('cart_currency_symbol');
    if (empty($defaultSymbol)) $defaultSymbol = __("$", "WSPSC");

    $baseShipping = get_option('cart_base_shipping_cost');
    if (empty($baseShipping)) $baseShipping = 0;
    
    $cart_free_shipping_threshold = get_option('cart_free_shipping_threshold');

    $defaultEmail = get_option('cart_paypal_email');
    if (empty($defaultEmail)) $defaultEmail = get_bloginfo('admin_email');
    
    $return_url =  get_option('cart_return_from_paypal_url');

    $addcart = get_option('addToCartButtonName');
    if (empty($addcart)) $addcart = __("Add to Cart", "WSPSC");           

	$title = get_option('wp_cart_title');
	//if (empty($title)) $title = __("Your Shopping Cart", "WSPSC");
	
	$emptyCartText = get_option('wp_cart_empty_text');
	$cart_products_page_url = get_option('cart_products_page_url');	  

	$cart_checkout_page_url = get_option('cart_checkout_page_url');
    if (get_option('wp_shopping_cart_auto_redirect_to_checkout_page'))
        $wp_shopping_cart_auto_redirect_to_checkout_page = 'checked="checked"';
    else
        $wp_shopping_cart_auto_redirect_to_checkout_page = '';	
        
    if (get_option('wp_shopping_cart_reset_after_redirection_to_return_page'))
        $wp_shopping_cart_reset_after_redirection_to_return_page = 'checked="checked"';
    else
        $wp_shopping_cart_reset_after_redirection_to_return_page = '';	
                	    
    if (get_option('wp_shopping_cart_collect_address'))
        $wp_shopping_cart_collect_address = 'checked="checked"';
    else
        $wp_shopping_cart_collect_address = '';
        
    if (get_option('wp_shopping_cart_use_profile_shipping'))
        $wp_shopping_cart_use_profile_shipping = 'checked="checked"';
    else
        $wp_shopping_cart_use_profile_shipping = '';
                	
    if (get_option('wp_shopping_cart_image_hide'))
        $wp_cart_image_hide = 'checked="checked"';
    else
        $wp_cart_image_hide = '';

	$wp_cart_note_to_seller_text = get_option('wp_cart_note_to_seller_text');
	
    if (get_option('wp_use_aff_platform'))
        $wp_use_aff_platform = 'checked="checked"';
    else
        $wp_use_aff_platform = '';
                              
	//$wp_shopping_cart_enable_sandbox = get_option('wp_shopping_cart_enable_sandbox');
    if (get_option('wp_shopping_cart_enable_sandbox'))
        $wp_shopping_cart_enable_sandbox = 'checked="checked"';
    else
        $wp_shopping_cart_enable_sandbox = '';	        
	?>
 	<h2><?php _e("Simple PayPal Shopping Cart Settings", "WSPSC"); ?> v <?php echo $wp_simple_paypal_shopping_cart_version; ?></h2>
 	
 	<div style="background: none repeat scroll 0 0 #FFF6D5;border: 1px solid #D1B655;color: #3F2502;margin: 10px 0;padding: 5px 5px 5px 10px;text-shadow: 1px 1px #FFFFFF;">	
 	<p><?php _e("For more information, updates, detailed documentation and video tutorial, please visit:", "WSPSC"); ?><br />
    <a href="http://www.tipsandtricks-hq.com/?p=768" target="_blank"><?php _e("WP simple cart homepage", "WSPSC"); ?></a></p>
    </div>
    
    <div class="postbox">
	<h3><label for="title"><?php _e("Quick Usage Guide", "WSPSC"); ?></label></h3>
	<div class="inside">
	
    <p><?php _e("1. To add an 'Add to Cart' button for a product simply add the shortcode", "WSPSC"); ?> <strong>[wp_cart:<?php _e("PRODUCT-NAME", "WSPSC"); ?>:price:<?php _e("PRODUCT-PRICE", "WSPSC"); ?>:end]</strong> <?php _e("to a post or page next to the product. Replace PRODUCT-NAME and PRODUCT-PRICE with the actual name and price. For example: [wp_cart:Test Product:price:15.00:end]", "WSPSC"); ?></p>
	<p><?php _e("2. To add the shopping cart to a post or page (example, checkout page) simply add the shortcode", "WSPSC"); ?><strong>[show_wp_shopping_cart]</strong> <?php _e("to a post or page or use the sidebar widget to add the shopping cart to the sidebar.", "WSPSC"); ?></p> 

	</div></div>
	
    <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
    <input type="hidden" name="info_update" id="info_update" value="true" />    
 	<?php
echo '
	<div class="postbox">
	<h3><label for="title">'.(__("PayPal and Shopping Cart Settings", "WSPSC")).'</label></h3>
	<div class="inside">';

echo '
<table class="form-table">
<tr valign="top">
<th scope="row">'.(__("Paypal Email Address", "WSPSC")).'</th>
<td><input type="text" name="cart_paypal_email" value="'.$defaultEmail.'" size="40" /></td>
</tr>
<tr valign="top">
<th scope="row">'.(__("Enable Sandbox Testing", "WSPSC")).'</th>
<td><input type="checkbox" name="wp_shopping_cart_enable_sandbox" value="1" '.$wp_shopping_cart_enable_sandbox.' />
<br />'.(__("Check this option if you want to do PayPal sandbox testing. You will need to create a PayPal sandbox account from PayPal Developer site", "WSPSC")).'</td>
</tr>
<tr valign="top">
<th scope="row">'.(__("Shopping Cart title", "WSPSC")).'</th>
<td><input type="text" name="wp_cart_title" value="'.$title.'" size="40" /></td>
</tr>
<tr valign="top">
<th scope="row">'.(__("Text/Image to Show When Cart Empty", "WSPSC")).'</th>
<td><input type="text" name="wp_cart_empty_text" value="'.$emptyCartText.'" size="60" /><br />'.(__("You can either enter plain text or the URL of an image that you want to show when the shopping cart is empty", "WSPSC")).'</td>
</tr>
<tr valign="top">
<th scope="row">'.(__("Currency", "WSPSC")).'</th>
<td><input type="text" name="cart_payment_currency" value="'.$defaultCurrency.'" size="6" /> ('.(__("e.g.", "WSPSC")).' USD, EUR, GBP, AUD)</td>
</tr>
<tr valign="top">
<th scope="row">'.(__("Currency Symbol", "WSPSC")).'</th>
<td><input type="text" name="cart_currency_symbol" value="'.$defaultSymbol.'" size="2" style="width: 1.5em;" /> ('.(__("e.g.", "WSPSC")).' $, &#163;, &#8364;) 
</td>
</tr>

<tr valign="top">
<th scope="row">'.(__("Base Shipping Cost", "WSPSC")).'</th>
<td><input type="text" name="cart_base_shipping_cost" value="'.$baseShipping.'" size="5" /> <br />'.(__("This is the base shipping cost that will be added to the total of individual products shipping cost. Put 0 if you do not want to charge shipping cost or use base shipping cost.", "WSPSC")).' <a href="http://www.tipsandtricks-hq.com/ecommerce/?p=297" target="_blank">'.(__("Learn More on Shipping Calculation", "WSPSC")).'</a></td>
</tr>

<tr valign="top">
<th scope="row">'.(__("Free Shipping for Orders Over", "WSPSC")).'</th>
<td><input type="text" name="cart_free_shipping_threshold" value="'.$cart_free_shipping_threshold.'" size="5" /> <br />'.(__("When a customer orders more than this amount he/she will get free shipping. Leave empty if you do not want to use it.", "WSPSC")).'</td>
</tr>

<tr valign="top">
<th scope="row">'.(__("Must Collect Shipping Address on PayPal", "WSPSC")).'</th>
<td><input type="checkbox" name="wp_shopping_cart_collect_address" value="1" '.$wp_shopping_cart_collect_address.' /><br />'.(__("If checked the customer will be forced to enter a shipping address on PayPal when checking out.", "WSPSC")).'</td>
</tr>

<tr valign="top">
<th scope="row">'.(__("Use PayPal Profile Based Shipping", "WSPSC")).'</th>
<td><input type="checkbox" name="wp_shopping_cart_use_profile_shipping" value="1" '.$wp_shopping_cart_use_profile_shipping.' /><br />'.(__("Check this if you want to use", "WSPSC")).' <a href="https://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_html_ProfileAndTools#id08A9EF00IQY" target="_blank">'.(__("PayPal profile based shipping", "WSPSC")).'</a>. '.(__("Using this will ignore any other shipping options that you have specified in this plugin.", "WSPSC")).'</td>
</tr>
		
<tr valign="top">
<th scope="row">'.(__("Add to Cart button text or Image", "WSPSC")).'</th>
<td><input type="text" name="addToCartButtonName" value="'.$addcart.'" size="100" /><br />'.(__("To use a customized image as the button simply enter the URL of the image file.", "WSPSC")).' '.(__("e.g.", "WSPSC")).' http://www.your-domain.com/wp-content/plugins/wordpress-paypal-shopping-cart/images/buy_now_button.png</td>
</tr>

<tr valign="top">
<th scope="row">'.(__("Return URL", "WSPSC")).'</th>
<td><input type="text" name="cart_return_from_paypal_url" value="'.$return_url.'" size="100" /><br />'.(__("This is the URL the customer will be redirected to after a successful payment", "WSPSC")).'</td>
</tr>
		
<tr valign="top">
<th scope="row">'.(__("Products Page URL", "WSPSC")).'</th>
<td><input type="text" name="cart_products_page_url" value="'.$cart_products_page_url.'" size="100" /><br />'.(__("This is the URL of your products page if you have any. If used, the shopping cart widget will display a link to this page when cart is empty", "WSPSC")).'</td>
</tr>

<tr valign="top">
<th scope="row">'.(__("Automatic redirection to checkout page", "WSPSC")).'</th>
<td><input type="checkbox" name="wp_shopping_cart_auto_redirect_to_checkout_page" value="1" '.$wp_shopping_cart_auto_redirect_to_checkout_page.' />
 '.(__("Checkout Page URL", "WSPSC")).': <input type="text" name="cart_checkout_page_url" value="'.$cart_checkout_page_url.'" size="60" />
<br />'.(__("If checked the visitor will be redirected to the Checkout page after a product is added to the cart. You must enter a URL in the Checkout Page URL field for this to work.", "WSPSC")).'</td>
</tr>

<tr valign="top">
<th scope="row">'.(__("Reset Cart After Redirection to Return Page", "WSPSC")).'</th>
<td><input type="checkbox" name="wp_shopping_cart_reset_after_redirection_to_return_page" value="1" '.$wp_shopping_cart_reset_after_redirection_to_return_page.' />
<br />'.(__("If checked the shopping cart will be reset when the customer lands on the return URL (Thank You) page.", "WSPSC")).'</td>
</tr>
</table>


<table class="form-table">
<tr valign="top">
<th scope="row">'.(__("Hide Shopping Cart Image", "WSPSC")).'</th>
<td><input type="checkbox" name="wp_shopping_cart_image_hide" value="1" '.$wp_cart_image_hide.' /><br />'.(__("If ticked the shopping cart image will not be shown.", "WSPSC")).'</td>
</tr>
</table>

<table class="form-table">
<tr valign="top">
<th scope="row">'.(__("Customize the Note to Seller Text", "WSPSC")).'</th>
<td><input type="text" name="wp_cart_note_to_seller_text" value="'.$wp_cart_note_to_seller_text.'" size="100" />
<br />'.(__("Specify the text that you want to use for the note field on PayPal checkout page to collect special instruction (leave this field empty if you don't need to customize it). The default label for the note field is \"Add special instructions to merchant\".", "WSPSC")).'</td>
</tr>
</table>

<table class="form-table">
<tr valign="top">
<th scope="row">'.(__("Use WP Affiliate Platform", "WSPSC")).'</th>
<td><input type="checkbox" name="wp_use_aff_platform" value="1" '.$wp_use_aff_platform.' />
<br />'.(__("Check this if using with the", "WSPSC")).' <a href="http://www.tipsandtricks-hq.com/?p=1474" target="_blank">WP Affiliate Platform plugin</a>. '.(__("This plugin lets you run your own affiliate campaign/program and allows you to reward (pay commission) your affiliates for referred sales", "WSPSC")).'</td>
</tr>
</table>
</div></div>
    <div class="submit">
        <input type="submit" name="info_update" value="'.(__("Update Options &raquo;", "WSPSC")).'" />
    </div>						
 </form>
 ';
    echo (__("Like the Simple WordPress Shopping Cart Plugin?", "WSPSC")).' <a href="http://wordpress.org/extend/plugins/wordpress-simple-paypal-shopping-cart" target="_blank">'.(__("Give it a good rating", "WSPSC")).'</a>';
	?>
 	<div style="background: none repeat scroll 0 0 #FFF6D5;border: 1px solid #D1B655;color: #3F2502;margin: 10px 0;padding: 5px 5px 5px 10px;text-shadow: 1px 1px #FFFFFF;">	
 	<p><?php _e("Need a shopping cart plugin with more features? Checkout my ", "WSPSC"); ?>
    <a href="http://www.tipsandtricks-hq.com/?p=1059" target="_blank"><?php _e("WP eStore Plugin", "WSPSC"); ?></a></p>
    </div>
    <?php 
}

function simple_cart_total()
{
	$grand_total = 0;
	foreach ((array) $_SESSION['simpleCart'] as $item)
	{
		$total += $item['price'] * $item['quantity'];
		$item_total_shipping += $item['shipping'] * $item['quantity'];
	}
	$grand_total = $total + $item_total_shipping;
	return number_format($grand_total,2);
}

function wp_cart_options()
{
     echo '<div class="wrap"><h2>'.(__("WP Paypal Shopping Cart Options", "WSPSC")).'</h2>';
     echo '<div id="poststuff"><div id="post-body">';
     show_wp_cart_options_page();
     echo '</div></div>';
     echo '</div>';
}

// Display The Options Page
function wp_cart_options_page () 
{
     add_options_page(__("WP Paypal Shopping Cart", "WSPSC"), __("WP Shopping Cart", "WSPSC"), 'manage_options', __FILE__, 'wp_cart_options');  
}

function wp_paypal_shopping_cart_load_widgets()
{
	register_widget('WP_PayPal_Cart_Widget');
}

class WP_PayPal_Cart_Widget extends WP_Widget {
	function WP_PayPal_Cart_Widget() {
		parent::WP_Widget('wp_paypal_shopping_cart_widgets', 'WP Paypal Shopping Cart', array('description' => 'WP Paypal Shopping Cart Widget') );
	}
	function form($instance) {
		// outputs the options form on admin
	}
	function update($new_instance, $old_instance) {
		// processes widget options to be saved
	}
	function widget($args, $instance) {
		// outputs the content of the widget
		extract( $args );
		
		$cart_title = get_option('wp_cart_title');
		if (empty($cart_title)) $cart_title = __("Shopping Cart", "WSPSC");
		
		echo $before_widget;
		echo $before_title . $cart_title . $after_title;
	    echo print_wp_shopping_cart();
	    echo $after_widget;	
	}
}

function wp_cart_css()
{
    echo '<link type="text/css" rel="stylesheet" href="'.WP_CART_URL.'/wp_shopping_cart_style.css" />'."\n";
}

// Add the settings link
function wp_simple_cart_add_settings_link($links, $file) 
{
	if ($file == plugin_basename(__FILE__)){
		$settings_link = '<a href="options-general.php?page='.dirname(plugin_basename(__FILE__)).'/wp_shopping_cart.php">'.(__("Settings", "WSPSC")).'</a>';
		array_unshift($links, $settings_link);
	}
	return $links;
}
add_filter('plugin_action_links', 'wp_simple_cart_add_settings_link', 10, 2 );

// Insert the options page to the admin menu
add_action('admin_menu','wp_cart_options_page');
add_action('widgets_init','wp_paypal_shopping_cart_load_widgets');

//add_filter('the_content', 'print_wp_cart_button',11);
add_filter('the_content', 'print_wp_cart_button_new',11);
add_filter('the_content', 'shopping_cart_show');

if (!is_admin())
{add_filter('widget_text', 'do_shortcode');}

add_shortcode('show_wp_shopping_cart', 'show_wp_shopping_cart_handler');
add_shortcode('always_show_wp_shopping_cart', 'always_show_cart_handler');
add_shortcode('wp_cart_button', 'wp_cart_button_handler');

add_action('wp_head', 'wp_cart_css');
add_action('wp_head', 'wp_cart_add_read_form_javascript');
?>