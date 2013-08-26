<?php

/* TODO
- add email menu
- Simple discount coupons option (see the user submitted coupon code version and incorporate that in here)
- Mention the available languages
*/

/* this function gets called when init is fired */
function wp_cart_init_handler()
{
    //Add any common init hook handing code
    if(is_admin())//Init hook handing code for wp-admin
    {
        wpc_create_orders_page();
    }
    else//Init hook handing code for front end
    {    
		if(isset($_REQUEST['simple_cart_ipn']))
		{
	            include_once('paypal.php');
	            wpc_handle_paypal_ipn();
	            exit;
		}
    }
}

function wp_cart_admin_init_handler()
{
    wpsc_add_meta_boxes();
}

function wpc_append_values_to_custom_field($name,$value)
{
    $custom_field_val = $_SESSION['wp_cart_custom_values'];
    $new_val = $name.'='.$value;
    if (empty($custom_field_val)){
        $custom_field_val = $new_val;
    }
    else{
        $custom_field_val = $custom_field_val.'&'.$new_val;
    }
    $_SESSION['wp_cart_custom_values'] = $custom_field_val;
    return $custom_field_val;
}

function wp_cart_get_custom_var_array($custom_val_string)
{
    $delimiter = "&";
    $customvariables = array();
    $namevaluecombos = explode($delimiter, $custom_val_string);
    foreach ($namevaluecombos as $keyval_unparsed)
    {
            $equalsignposition = strpos($keyval_unparsed, '=');
            if ($equalsignposition === false)
            {
                $customvariables[$keyval_unparsed] = '';
                continue;
            }
            $key = substr($keyval_unparsed, 0, $equalsignposition);
            $value = substr($keyval_unparsed, $equalsignposition + 1);
            $customvariables[$key] = $value;
    }
    return $customvariables;
}

function wspsc_reset_logfile()
{
	$log_reset = true;
	$logfile = dirname(__FILE__).'/ipn_handle_debug.log';
	$text = '['.date('m/d/Y g:i A').'] - SUCCESS : Log file reset';
    $text .= "\n------------------------------------------------------------------\n\n";
	$fp = fopen($logfile, 'w');
	if($fp != FALSE) {
		@fwrite($fp, $text);
		@fclose($fp);
	}
	else{
		$log_reset = false;	
	}
	return $log_reset;
}