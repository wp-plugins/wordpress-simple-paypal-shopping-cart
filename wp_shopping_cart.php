<?php
/*
Plugin Name: WP Simple Paypal Shopping cart
Version: v1.1
Plugin URI: http://www.tipsandtricks-hq.com/?p=768
Author: Ruhul Amin
Author URI: http://www.tipsandtricks-hq.com/
Description: Simple Paypal Shopping Cart Plugin, very easy to use and great for selling digital products or service from your blog!
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

session_start();

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

if ($_POST['addcart'])
{
    $count = 1;    
    $products = $_SESSION['simpleCart'];
    
    if (is_array($products))
    {
        foreach ($products as $key => $item)
        {
            if ($item['name'] == $_POST['product'])
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
        if (!empty($_POST[$_POST['product']]))
            $price = $_POST[$_POST['product']];
        else
            $price = $_POST['price'];
        
        $product = array('name' => stripslashes($_POST['product']), 'price' => $price, 'quantity' => $count, 'cartLink' => $_POST['cartLink'], 'item_number' => $_POST['item_number']);
        array_push($products, $product);
    }
    
    sort($products);
    $_SESSION['simpleCart'] = $products;
}
else if ($_POST['cquantity'])
{
    $products = $_SESSION['simpleCart'];
    foreach ($products as $key => $item)
    {
        if (($item['name'] == $_POST['product']) && $_POST['quantity'])
        {
            $item['quantity'] = $_POST['quantity'];
            unset($products[$key]);
            array_push($products, $item);
        }
        else if (($item['name'] == $_POST['product']) && !$_POST['quantity'])
            unset($products[$key]);
    }
    sort($products);
    $_SESSION['simpleCart'] = $products;
}
else if ($_POST['delcart'])
{
    $products = $_SESSION['simpleCart'];
    foreach ($products as $key => $item)
    {
        if ($item['name'] == $_POST['product'])
            unset($products[$key]);
    }
    $_SESSION['simpleCart'] = $products;
}

function print_wp_shopping_cart()
{
    $email = get_bloginfo('admin_email');
       
    $defaultCurrency = get_option('cart_payment_currency');
    $defaultSymbol = get_option('cart_currency_symbol');
    $defaultEmail = get_option('cart_paypal_email');
    if (!empty($defaultCurrency))
        $paypal_currency = $defaultCurrency;
    else
        $paypal_currency = 'USD';
    if (!empty($defaultSymbol))
        $paypal_symbol = $defaultSymbol;
    else
        $paypal_symbol = '$';

    if (!empty($defaultEmail))
        $email = $defaultEmail;
     
    $decimal = '.';  
	$urls = '';
        
    $return = get_option('cart_return_from_paypal_url');
            
    if (!empty($return))
        $urls .= '<input type="hidden" name="return" value="'.$return.'" />';
	  
	$title = get_option('wp_cart_title');
	if (empty($title)) $title = 'Your Shopping Cart';
    
    $output .= '<div class="shopping_cart" style=" padding: 5px;">';
    $output .= "<input type='image' src='".get_bloginfo('wpurl')."/wp-content/plugins/wordpress-paypal-shopping-cart/images/shopping_cart_icon.gif' value='Cart' title='Cart' />";
    $output .= "<strong>";
    $output .= $title;  
    $output .= "</strong>";  
        
    $output .= '<br /><span id="pinfo" style="display: none; font-weight: bold; color: red;">Hit enter to submit new Quantity.</span>';
	$output .= '<table style="width: 100%;">';    
    
    $count = 1;
    $total_items = 0;
    $total = 0;
    $form = '';
    if ($_SESSION['simpleCart'] && is_array($_SESSION['simpleCart']))
    {   
        $output .= '
        <tr>
        <th>Item Name</th><th>Quantity</th><th>Price</th>
        </tr>';
    
    foreach ($_SESSION['simpleCart'] as $item)
    {
        $total += $item['price'] * $item['quantity'];
        
        $total_items +=  $item['quantity'];
    }
    
    foreach ($_SESSION['simpleCart'] as $item)
    {
        $output .= "                 
        <tr><td style='overflow: hidden;'><a href='".$item['cartLink']."'>".$item['name']."</a></td>
        <td style='text-align: center'><form method=\"post\"  action=\"\" name='pcquantity' style='display: inline'>
        <input type='hidden' name='product' value='".$item['name']."' />
        
        <input type='hidden' name='cquantity' value='1' /><input type='text' name='quantity' value='".$item['quantity']."' size='1' onchange='document.pcquantity.submit();' onkeypress='document.getElementById(\"pinfo\").style.display = \"\";' /></form></td>
        <td style='text-align: center'>".print_payment_currency(($item['price'] * $item['quantity']), $paypal_symbol, $decimal)."</td>
        <td><form method=\"post\"  action=\"\">
        <input type='hidden' name='product' value='".$item['name']."' />
        <input type='hidden' name='delcart' value='1' />
        <input type='image' src='".get_bloginfo('wpurl')."/wp-content/plugins/wordpress-paypal-shopping-cart/images/Shoppingcart_delete.gif' value='Remove' title='Remove' /></form></td></tr>
        
        ";
        
        $form .= "
            <input type=\"hidden\" name=\"item_name_$count\" value=\"".$item['name']."\" />
            <input type=\"hidden\" name=\"amount_$count\" value='".$item['price']."' />
            <input type=\"hidden\" name=\"quantity_$count\" value=\"".$item['quantity']."\" />
            <input type='hidden' name='item_number' value='".$item['item_number']."' />
        ";
        $form .= "<input type=\"hidden\" name=\"shipping_$count\" value=\"0\" />";
        $count++;
    }
    }
    
       	$count--;
       	
       	if ($count)
       	{
       		$output .= '<tr><td></td><td></td><td></td></tr>';       
       		$output .= "
       		<tr><td colspan='2' style='font-weight: bold; text-align: right;'>Total: </td><td style='text-align: center'>".print_payment_currency(($total), $paypal_symbol, $decimal)."</td><td></td></tr>
       		<tr><td colspan='4'>";
       
              	$output .= "<form action=\"https://www.paypal.com/us/cgi-bin/webscr\" method=\"post\">$form";
    			if ($count)
            		$output .= '<input type="image" src="'.get_bloginfo('wpurl').'/wp-content/plugins/wordpress-paypal-shopping-cart/images/paypal_checkout.png" name="submit" alt="Make payments with PayPal - it\'s fast, free and secure!" />';
       
    			$output .= $urls.'
			    <input type="hidden" name="business" value="'.$email.'" />
			    <input type="hidden" name="currency_code" value="'.$paypal_currency.'" />
			    <input type="hidden" name="cmd" value="_cart" />
			    <input type="hidden" name="upload" value="1" />
			    </form>';          
       	}       
       	$output .= "
       
       	</td></tr>
    	</table></div>
    	";
    
    return $output;
}

function print_wp_cart_button($content)
{          
        $addcart = get_option('addToCartButtonName');
    
        if (!$addcart || ($addcart == '') )
            $addcart = 'Add to Cart';
        
        $pattern = '#\[wp_cart:.+:price:#';
        preg_match_all ($pattern, $content, $matches);
        
        foreach ($matches[0] as $match)
        {            
            $pattern = '[wp_cart:';
            $m = str_replace ($pattern, '', $match);
            $pattern = ':price:';
            $m = str_replace ($pattern, '', $m);
            
            $pieces = explode('|',$m);         
            
            if (sizeof($pieces) == 1)
            {      
                $replacement = '<object><form method="post"  action=""  style="display:inline">
                <input type="submit" value="'.$addcart.'" />
                <input type="hidden" name="product" value="'.$pieces['0'].
                '" /><input type="hidden" name="price" value="';
                
                $content = str_replace ($match, $replacement, $content);
            }   
        }
    
        $forms = str_replace(':item_num:',    
        '" /><input type="hidden" name="shipping" value="',    
        $content);  
               
        $forms = str_replace(':end]',    
        '" /><input type="hidden" name="addcart" value="1" /><input type="hidden" name="cartLink" value="'.cart_current_page_url().'" />
        </form></object>',    
        $forms);
    
    if (empty($forms))
        $forms = $content;
       
    return $forms;
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
 if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
 $pageURL .= "://";
 if ($_SERVER["SERVER_PORT"] != "80") {
  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
 } else {
  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
 }
 return $pageURL;
}

function show_wp_cart_options_page () {
	
	$wp_simple_paypal_shopping_cart_version = 1.1;
	
    $defaultCurrency = get_option('cart_payment_currency');    
    if (empty($defaultCurrency)) $defaultCurrency = 'USD';
    
    $defaultSymbol = get_option('cart_currency_symbol');
    if (empty($defaultSymbol)) $defaultSymbol = '$';

    $defaultEmail = get_option('cart_paypal_email');
    if (empty($defaultEmail)) $defaultEmail = get_bloginfo('admin_email');
    
    $return_url =  get_option('cart_return_from_paypal_url');

    $addcart = get_option('addToCartButtonName');
    if (empty($addcart)) $addcart = 'Add to Cart';           

	$title = get_option('wp_cart_title');
	if (empty($title)) $title = 'Your Shopping Cart';
      
	?>
 	<h2>Simple Paypal Shopping Cart Settings v <?php echo $wp_simple_paypal_shopping_cart_version; ?></h2>
 	
 	<p>For information and updates, please visit:<br />
    <a href="http://www.tipsandtricks-hq.com/?p=768">http://www.tipsandtricks-hq.com/?p=768</a></p>
    
     <fieldset class="options">
    <legend>Usage:</legend>

    <p>1. To add the 'Add to Cart' button simply add the trigger text <strong>[wp_cart:PRODUCT-NAME:price:PRODUCT-PRICE:end]</strong> to a post or page next to the product. Replace PRODUCT-NAME and PRODUCT-PRICE with the actual name and price.</p>
	<p>2. To add the shopping cart to a post or checkout page or sidebar simply add the trigger text <strong>&lt;!--show-wp-shopping-cart--&gt;</strong> to a post or page or sidebar. The shopping cart will only be visible when a customer adds a product. 
    </fieldset>
    
 	<?php
 
    echo '
 <form method="post" action="options.php">';
 wp_nonce_field('update-options');
 echo '
<table class="form-table">
<tr valign="top">
<th scope="row">Paypal Email Address</th>
<td><input type="text" name="cart_paypal_email" value="'.$defaultEmail.'" /></td>
</tr>
<tr valign="top">
<th scope="row">Shopping Cart title</th>
<td><input type="text" name="wp_cart_title" value="'.$title.'"  /></td>
</tr>
<tr valign="top">
<th scope="row">Currency</th>
<td><input type="text" name="cart_payment_currency" value="'.$defaultCurrency.'" size="6" /> (e.g. USD, EUR, GBP, AUD)</td>
</tr>
<tr valign="top">
<th scope="row">Currency Sybmol</th>
<td><input type="text" name="cart_currency_symbol" value="'.$defaultSymbol.'" size="2" style="width: 1.5em;" /> (e.g. $, &#163;) 
</td>
</tr>

<tr valign="top">
<th scope="row">Add to Cart button text</th>
<td><input type="text" name="addToCartButtonName" value="'.$addcart.'" /></td>
</tr>

<tr valign="top">
<th scope="row">Return URL</th>
<td><input type="text" name="cart_return_from_paypal_url" value="'.$return_url.'" size="70" /><br />This is the URL the customer will be redirected to after a successful payment</td>
</tr>
</table>

<p class="submit">
<input type="submit" name="Submit" value="Update Options &raquo;" />
<input type="hidden" name="action" value="update" />
<input type="hidden" name="page_options" value="cart_payment_currency,cart_currency_symbol,cart_paypal_email,addToCartButtonName,wp_cart_title,cart_return_from_paypal_url" />
</p>

 </form>
 ';
}

function wp_cart_options()
{
     echo '<div class="wrap"><h2>WP Paypal Shopping Cart Options</h2>';
     show_wp_cart_options_page();
     echo '</div>';
}

// Display The Options Page
function wp_cart_options_page () 
{
     add_options_page('WP Paypal Shopping Cart', 'WP Shopping Cart', 'manage_options', __FILE__, 'wp_cart_options');  
}

// Insert the options page to the admin menu
add_action('admin_menu','wp_cart_options_page');

add_filter('the_content', 'print_wp_cart_button');

add_filter('the_content', 'shopping_cart_show');

?>