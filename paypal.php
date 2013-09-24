<?php

$debug_log = "ipn_handle_debug.log"; // Debug log file name

class paypal_ipn_handler {

   var $last_error;                 // holds the last error encountered
   var $ipn_log;                    // bool: log IPN results to text file?
   var $ipn_log_file;               // filename of the IPN log
   var $ipn_response;               // holds the IPN response from paypal
   var $ipn_data = array();         // array contains the POST values for IPN
   var $fields = array();           // array holds the fields to submit to paypal
   var $sandbox_mode = false;

   	function paypal_ipn_handler()
   	{
        $this->paypal_url = 'https://www.paypal.com/cgi-bin/webscr';
      	$this->last_error = '';
      	$this->ipn_log_file = WP_CART_PATH.'ipn_handle_debug.log';
      	$this->ipn_response = '';
    }

    function validate_and_dispatch_product()
    {
        // Check Product Name , Price , Currency , Receivers email ,
        global $products,$currency,$paypal_email;
        $txn_id = $this->ipn_data['txn_id'];
        $transaction_type = $this->ipn_data['txn_type'];
        $payment_status = $this->ipn_data['payment_status'];
        $transaction_subject = $this->ipn_data['transaction_subject'];
        $custom_value_str = $this->ipn_data['custom'];
        //$this->debug_log('custom values from paypal: '.$custom_value_str,true);
        $first_name = $this->ipn_data['first_name'];
        $last_name = $this->ipn_data['last_name'];
        $buyer_email = $this->ipn_data['payer_email'];
        $street_address = $this->ipn_data['address_street'];
        $city = $this->ipn_data['address_city'];
        $state = $this->ipn_data['address_state'];
        $zip = $this->ipn_data['address_zip'];
        $country = $this->ipn_data['address_country'];
        $address = $street_address.", ".$city.", ".$state.", ".$zip.", ".$country;
        $custom_values = wp_cart_get_custom_var_array($custom_value_str);
        $this->debug_log('Payment Status: '.$payment_status,true);
        if($payment_status == "Completed" || $payment_status == "Processed" ){
            //We will process this notification
        }
        else{
            $this->debug_log('This is not a payment complete notification. This IPN will not be processed.',true);
            return true;
        }
        if ($transaction_type == "cart")
        {
            $this->debug_log('Transaction Type: Shopping Cart',true);
            // Cart Items
            $num_cart_items = $this->ipn_data['num_cart_items'];
            $this->debug_log('Number of Cart Items: '.$num_cart_items,true);

            $i = 1;
            $cart_items = array();
            while($i < $num_cart_items+1)
            {
                $item_number = $this->ipn_data['item_number' . $i];
                $item_name = $this->ipn_data['item_name' . $i];
                $quantity = $this->ipn_data['quantity' . $i];
                $mc_gross = $this->ipn_data['mc_gross_' . $i];
                $mc_currency = $this->ipn_data['mc_currency'];

                $current_item = array(
                    'item_number' => $item_number,
                    'item_name' => $item_name,
                    'quantity' => $quantity,
                    'mc_gross' => $mc_gross,
                    'mc_currency' => $mc_currency,
                );

                array_push($cart_items, $current_item);
                $i++;
            }
        }
        else
        {
            $cart_items = array();
            $this->debug_log('Transaction Type: Buy Now',true);
            $item_number = $this->ipn_data['item_number'];
            $item_name = $this->ipn_data['item_name'];
            $quantity = $this->ipn_data['quantity'];
            $mc_gross = $this->ipn_data['mc_gross'];
            $mc_currency = $this->ipn_data['mc_currency'];

            $current_item = array(
                'item_number' => $item_number,
                'item_name' => $item_name,
                'quantity' => $quantity,
                'mc_gross' => $mc_gross,
                'mc_currency' => $mc_currency,
            );
            array_push($cart_items, $current_item);
        }

        $product_id_array = Array();
        $product_name_array = Array();
        $product_price_array = Array();
        $attachments_array = Array();
        $download_link_array = Array();

        $payment_currency = get_option('cart_payment_currency');

        foreach ($cart_items as $current_cart_item)
        {
            $cart_item_data_num = $current_cart_item['item_number'];
            $cart_item_data_name = $current_cart_item['item_name'];
            $cart_item_data_quantity = $current_cart_item['quantity'];
            $cart_item_data_total = $current_cart_item['mc_gross'];
            $cart_item_data_currency = $current_cart_item['mc_currency'];

            $this->debug_log('Item Number: '.$cart_item_data_num,true);
            $this->debug_log('Item Name: '.$cart_item_data_name,true);
            $this->debug_log('Item Quantity: '.$cart_item_data_quantity,true);
            $this->debug_log('Item Total: '.$cart_item_data_total,true);
            $this->debug_log('Item Currency: '.$cart_item_data_currency,true);

            // Compare the values
            if ($payment_currency != $cart_item_data_currency)
            {
            $this->debug_log('Invalid Product Currency : '.$payment_currency,false);
            return false;
            }
        }

        /*** Send notification email ***/
        //TODO
        $post_id = $custom_values['wp_cart_id'];
        $ip_address = $custom_values['ip'];
        $currency_symbol = get_option('cart_currency_symbol');
        $this->debug_log('custom values',true);
        $this->debug_log_array($custom_values,true);
        //$this->debug_log('post id: '.$post_id,true);
        if($post_id){
            
            $updated_wpsc_order = array(
                'ID'             => $post_id,
                'post_status'    => 'publish',
                'post_type'     => 'wpsc_cart_orders',
            );
            wp_update_post($updated_wpsc_order);
            
            update_post_meta( $post_id, 'wpsc_first_name', $first_name );
            update_post_meta( $post_id, 'wpsc_last_name', $last_name );
            update_post_meta( $post_id, 'wpsc_email_address', $buyer_email );
            update_post_meta( $post_id, 'wpsc_txn_id', $txn_id );
            $mc_gross = $this->ipn_data['mc_gross'];
            update_post_meta( $post_id, 'wpsc_total_amount', $mc_gross);
            update_post_meta( $post_id, 'wpsc_ipaddress', $ip_address );
            update_post_meta( $post_id, 'wpsc_address', $address );
            $status = "Paid";
            update_post_meta( $post_id, 'wpsc_order_status', $status );
            $cart_items = get_post_meta( $post_id, 'wpsc_cart_items', true );
            $product_details = "";
            $item_counter = 1;
            if($cart_items){
                foreach ($cart_items as $item){
                    if($item_counter != 1){
                        $product_details .= "\n";
                    }
                    $product_details .= $item['name']." x ".$item['quantity']." - ".$currency_symbol.$item['price']."\n";
                    if($item['file_url']){
                        $file_url = base64_decode($item['file_url']);
                        $product_details .= "Download Link: ".$file_url."\n";
                    }
                    $item_counter++;
                }
            }
            $args = array();
            $args['product_details'] = $product_details;
            update_post_meta($post_id, 'wpspsc_items_ordered', $product_details);
            $from_email = get_option('wpspc_buyer_from_email');
            $subject = get_option('wpspc_buyer_email_subj');
            $body = get_option('wpspc_buyer_email_body');
            $args['email_body'] = $body;
            $body = wpspc_apply_dynamic_tags_on_email_body($this->ipn_data, $args);
            if($buyer_email){
                if(get_option('wpspc_send_buyer_email'))
        	{
                    $headers = 'From: '.$from_email . "\r\n";
                    wp_mail($buyer_email, $subject, $body, $headers);
                    $this->debug_log('Product Email successfully sent to '.$buyer_email,true);
                    update_post_meta( $post_id, 'wpsc_buyer_email_sent', 'Email sent to: '.$buyer_email);
                }
            }
        }

        /**** Affiliate plugin integratin ****/
        $this->debug_log('Updating Affiliate Database Table with Sales Data if Using the WP Affiliate Platform Plugin.',true);       
        if (function_exists('wp_aff_platform_install'))
        {
            $this->debug_log('WP Affiliate Platform is installed, registering sale...',true);
            $referrer = $custom_values['ap_id'];
            $sale_amount = $this->ipn_data['mc_gross'];
            if (!empty($referrer))
            {	
                do_action('wp_affiliate_process_cart_commission', array("referrer" => $referrer, "sale_amt" => $sale_amount, "txn_id" => $txn_id, "buyer_email" => $buyer_email));

                $message = 'The sale has been registered in the WP Affiliates Platform Database for referrer: '.$referrer.' for sale amount: '.$sale_amount;
                $this->debug_log($message,true);				
            }
            else
            {
                $this->debug_log('No Referrer Found. This is not an affiliate sale',true);
            }			
        }
        else
        {
            $this->debug_log('Not Using the WP Affiliate Platform Plugin.',true);
        }
        return true;
    }
	
   function validate_ipn() 
   {
      // parse the paypal URL
      $url_parsed=parse_url($this->paypal_url);

      // generate the post string from the _POST vars aswell as load the _POST vars into an arry
      $post_string = '';
      foreach ($_POST as $field=>$value) {
         $this->ipn_data["$field"] = $value;
         $post_string .= $field.'='.urlencode(stripslashes($value)).'&';
      }

      $this->post_string = $post_string;
      $this->debug_log('Post string : '. $this->post_string,true);

      $post_string.="cmd=_notify-validate"; // append ipn command

      // open the connection to paypal
      if($this->sandbox_mode){//connect to PayPal sandbox
	      $uri = 'ssl://'.$url_parsed['host'];
	      $port = '443';         	
	      $fp = fsockopen($uri,$port,$err_num,$err_str,30);
      }
      else{//connect to live PayPal site using standard approach
      	$fp = fsockopen($url_parsed['host'],"80",$err_num,$err_str,30);
      }
      
      if(!$fp)
      {
         // could not open the connection.  If loggin is on, the error message
         // will be in the log.
         $this->debug_log('Connection to '.$url_parsed['host']." failed. fsockopen error no. $errnum: $errstr",false);
         return false;

      }
      else
      {
         // Post the data back to paypal
         fputs($fp, "POST $url_parsed[path] HTTP/1.1\r\n");
         fputs($fp, "Host: $url_parsed[host]\r\n");
         fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
         fputs($fp, "Content-length: ".strlen($post_string)."\r\n");
         fputs($fp, "Connection: close\r\n\r\n");
         fputs($fp, $post_string . "\r\n\r\n");

         // loop through the response from the server and append to variable
         while(!feof($fp)) {
            $this->ipn_response .= fgets($fp, 1024);
         }

         fclose($fp); // close connection

         $this->debug_log('Connection to '.$url_parsed['host'].' successfuly completed.',true);
      }

      //if (eregi("VERIFIED",$this->ipn_response))
      if (strpos($this->ipn_response, "VERIFIED") !== false)// Valid IPN transaction.
      {
         $this->debug_log('IPN successfully verified.',true);
         return true;
      }
      else
      {
         // Invalid IPN transaction. Check the log for details.
         $this->debug_log('IPN validation failed.',false);
         return false;
      }
   }

   function log_ipn_results($success)
   {
      if (!$this->ipn_log) return;  // is logging turned off?

      // Timestamp
      $text = '['.date('m/d/Y g:i A').'] - ';

      // Success or failure being logged?
      if ($success) $text .= "SUCCESS!\n";
      else $text .= 'FAIL: '.$this->last_error."\n";

      // Log the POST variables
      $text .= "IPN POST Vars from Paypal:\n";
      foreach ($this->ipn_data as $key=>$value) {
         $text .= "$key=$value, ";
      }

      // Log the response from the paypal server
      $text .= "\nIPN Response from Paypal Server:\n ".$this->ipn_response;

      // Write to log
      $fp=fopen($this->ipn_log_file,'a');
      fwrite($fp, $text . "\n\n");

      fclose($fp);  // close file
   }

   function debug_log($message,$success,$end=false)
   {

   	  if (!$this->ipn_log) return;  // is logging turned off?

      // Timestamp
      $text = '['.date('m/d/Y g:i A').'] - '.(($success)?'SUCCESS :':'FAILURE :').$message. "\n";

      if ($end) {
      	$text .= "\n------------------------------------------------------------------\n\n";
      }

      // Write to log
      $fp=fopen($this->ipn_log_file,'a');
      fwrite($fp, $text );
      fclose($fp);  // close file
   }
   
    function debug_log_array($array_to_write,$success,$end=false)
    {
        if (!$this->ipn_log) return;  // is logging turned off?
        $text = '['.date('m/d/Y g:i A').'] - '.(($success)?'SUCCESS :':'FAILURE :'). "\n";
        ob_start(); 
        print_r($array_to_write); 
        $var = ob_get_contents(); 
        ob_end_clean();     
        $text .= $var;

        if ($end) 
        {
            $text .= "\n------------------------------------------------------------------\n\n";
        }
        // Write to log
        $fp=fopen($this->ipn_log_file,'a');
        fwrite($fp, $text );
        fclose($fp);  // close filee
    }
}

// Start of IPN handling (script execution)
function wpc_handle_paypal_ipn()
{
    $debug_log = "ipn_handle_debug.log"; // Debug log file name    
    $ipn_handler_instance = new paypal_ipn_handler();

    $debug_enabled = false;
    $debug = get_option('wp_shopping_cart_enable_debug');
    if ($debug){
        $debug_enabled = true;
    }

    if ($debug_enabled)
    {
        echo 'Debug is enabled. Check the '.$debug_log.' file for debug output.';
        $ipn_handler_instance->ipn_log = true;
        //$ipn_handler_instance->ipn_log_file = realpath(dirname(__FILE__)).'/'.$debug_log;
    }
    $sandbox = get_option('wp_shopping_cart_enable_sandbox');
    if ($sandbox) // Enable sandbox testing
    {
        $ipn_handler_instance->paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
        $ipn_handler_instance->sandbox_mode = true;
    }
    $ipn_handler_instance->debug_log('Paypal Class Initiated by '.$_SERVER['REMOTE_ADDR'],true);
    // Validate the IPN
    if ($ipn_handler_instance->validate_ipn())
    {
        $ipn_handler_instance->debug_log('Creating product Information to send.',true);
        if(!$ipn_handler_instance->validate_and_dispatch_product())
        {
            $ipn_handler_instance->debug_log('IPN product validation failed.',false);
        }
    }
    $ipn_handler_instance->debug_log('Paypal class finished.',true,true);
}

