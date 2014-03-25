<?php
// Can't really think of any better way to do this for now
require_once('../../../wp-load.php'); 

// CONFIG: Enable debug mode. This means we'll log requests into 'ipn.log' in the same directory.
// Especially useful if you encounter network errors or other intermittent problems with IPN (validation).
// Set this to 0 once you go live or don't require logging.
define("DEBUG", 0);

// Set to 0 once you're ready to go live
if(get_option('pm_paypal_use_sandbox') == 'use_sandbox')
	define("USE_SANDBOX", 1);
else define("USE_SANDBOX", 0);

define("LOG_FILE", "ipn.log");


// Read POST data
// reading posted data directly from $_POST causes serialization
// issues with array data in POST. Reading raw POST data from input stream instead.
$raw_post_data = file_get_contents('php://input');
$raw_post_array = explode('&', $raw_post_data);
$myPost = array();
foreach ($raw_post_array as $keyval) {
	$keyval = explode ('=', $keyval);
	if (count($keyval) == 2)
		$myPost[$keyval[0]] = urldecode($keyval[1]);
}
// read the post from PayPal system and add 'cmd'
$req = 'cmd=_notify-validate';
if(function_exists('get_magic_quotes_gpc')) {
	$get_magic_quotes_exists = true;
}
foreach ($myPost as $key => $value) {
	if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
		$value = urlencode(stripslashes($value));
	} else {
		$value = urlencode($value);
	}
	$req .= "&$key=$value";
}

// Post IPN data back to PayPal to validate the IPN data is genuine
// Without this step anyone can fake IPN data

if(USE_SANDBOX == true) {
	$paypal_url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
} else {
	$paypal_url = "https://www.paypal.com/cgi-bin/webscr";
}

$ch = curl_init($paypal_url);
if ($ch == FALSE) {
	return FALSE;
}

curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);

if(DEBUG == true) {
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
}

// CONFIG: Optional proxy configuration
//curl_setopt($ch, CURLOPT_PROXY, $proxy);
//curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);

// Set TCP timeout to 30 seconds
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

// CONFIG: Please download 'cacert.pem' from "http://curl.haxx.se/docs/caextract.html" and set the directory path
// of the certificate as shown below. Ensure the file is readable by the webserver.
// This is mandatory for some environments.

//$cert = __DIR__ . "./cacert.pem";
//curl_setopt($ch, CURLOPT_CAINFO, $cert);

$res = curl_exec($ch);
if (curl_errno($ch) != 0) // cURL error
	{
	if(DEBUG == true) {	
		error_log(date('[Y-m-d H:i e] '). "Can't connect to PayPal to validate IPN message: " . curl_error($ch) . PHP_EOL, 3, LOG_FILE);
	}
	curl_close($ch);
	exit;

} else {
		// Log the entire HTTP response if debug is switched on.
		if(DEBUG == true) {
			error_log(date('[Y-m-d H:i e] '). "HTTP request of validation request:". curl_getinfo($ch, CURLINFO_HEADER_OUT) ." for IPN payload: $req" . PHP_EOL, 3, LOG_FILE);
			error_log(date('[Y-m-d H:i e] '). "HTTP response of validation request: $res" . PHP_EOL, 3, LOG_FILE);

			// Split response headers and payload
			list($headers, $res) = explode("\r\n\r\n", $res, 2);
		}
		curl_close($ch);
}

// Inspect IPN validation result and act accordingly

if (strcmp ($res, "VERIFIED") == 0) {
	// check whether the payment_status is Completed
	// check that txn_id has not been previously processed
	// check that receiver_email is your PayPal email
	// check that payment_amount/payment_currency are correct
	// process payment and mark item as paid.

	// assign posted variables to local variables
	//$item_name = $_POST['item_name'];
	//$item_number = $_POST['item_number'];
	//$payment_status = $_POST['payment_status'];
	//$payment_amount = $_POST['mc_gross'];
	//$payment_currency = $_POST['mc_currency'];
	//$receiver_email = $_POST['receiver_email'];
	//$payer_email = $_POST['payer_email'];
	
	if(DEBUG == true) {
		error_log(date('[Y-m-d H:i e] '). "Verified IPN: $req ". PHP_EOL, 3, LOG_FILE);
	}

	
	$transaction_details = decodePayPalIPN($raw_post_data);
	$paykey = $transaction_details['pay_key'];
	$wp_transaction_query = new WP_Query(array(
		'post_type' => 'vs_transaction',
		'post_status' => 'draft',
		'posts_per_page' => 1,
		'meta_key' => 'pm_transaction_paypal_paykey',
		'meta_value' => $paykey
	));

	wp_mail('joseph.carrington@gmail.com', 'Vinsource Transaction Data, PT 2', var_export($transaction_details, true));

	if($wp_transaction_query->post_count != 1)
	{
		// Item was already set as completed.
		return;
	}

	$wp_transaction = $wp_transaction_query->posts[0];


	$buyer_id = $wp_transaction->post_author;
	if(pm_get_transaction_type($wp_transaction->ID) == 'sample')
	{
		pm_add_user_sampled(pm_get_product_id($wp_transaction->ID), $buyer_id);
		wp_mail('joseph.carrington@gmail.com', 'Sample Added', var_export(pm_get_user_sampled($buyer_id), true));
	}

	// TODO verify email address for receiver
	wp_update_post(array(
		'ID' => $wp_transaction->ID,
		'post_status' => 'publish'
	));

	$seller_id = get_post_meta($wp_transaction->ID, 'pm_transaction_seller_id', true);
	
	$users = get_users(array(
		'meta_key' => 'attached_winery',
		'meta_value' => $seller_id
	));

	$user = $users[0];
	$user_email = $user->user_email;
	$user_message = "You have received a trade order through Vinsource! To view the  order details and fulfillment information, please click the link below:\r\n";
	$user_message .= vs_get_dash() . "\r\n\r\n";
	$user_message .= "To ensure timely delivery and customer satisfaction, please ship the merchandise within one business day of receiving this order.\r\n\r\n";
	$user_message .= "Thank You,\r\n";
	$user_message .= "The Vinsource Team";
	wp_mail($user_email, "Vinsource Order #$wp_transaction->ID", $user_message);
	
	
} else if (strcmp ($res, "INVALID") == 0) {
	// log for manual investigation
	// Add business logic here which deals with invalid IPN messages
	if(DEBUG == true) {
		error_log(date('[Y-m-d H:i e] '). "Invalid IPN: $req" . PHP_EOL, 3, LOG_FILE);
	}
}

function decodePayPalIPN($raw_post) {
	if (empty($raw_post)) {
		return array();
	} // else:
	$post = array();
	$pairs = explode('&', $raw_post);
	foreach ($pairs as $pair) {
		list($key, $value) = explode('=', $pair, 2);
		$key = urldecode($key);
		$value = urldecode($value);
		// This is look for a key as simple as 'return_url' or as complex as 'somekey[x].property'
		preg_match('/(\w+)(?:\[(\d+)\])?(?:\.(\w+))?/', $key, $key_parts);
		switch (count($key_parts)) {
			case 4:
				// Original key format: somekey[x].property
				// Converting to $post[somekey][x][property]
				if (!isset($post[$key_parts[1]])) {
					$post[$key_parts[1]] = array($key_parts[2] => array($key_parts[3] => $value));
				} else if (!isset($post[$key_parts[1]][$key_parts[2]])) {
					$post[$key_parts[1]][$key_parts[2]] = array($key_parts[3] => $value);
				} else {
					$post[$key_parts[1]][$key_parts[2]][$key_parts[3]] = $value;
				}
				break;
			case 3:
				// Original key format: somekey[x]
				// Converting to $post[somkey][x] 
				if (!isset($post[$key_parts[1]])) {
					$post[$key_parts[1]] = array();
				}
				$post[$key_parts[1]][$key_parts[2]] = $value;
				break;
			default:
				// No special format
				$post[$key] = $value;
				break;
		}//switch
	}//foreach
	
	return $post;
}//decodePayPalIPN()
