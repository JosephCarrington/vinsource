<?php
/**
* Handles everything required for creating a payment, saving it to WP, and sending the payer to PayPal to complete the transaction
*/
class PeerMarketplacePayPal implements PMPaymentHandler{
	var $apiUrl;
	var $payPalUrl;
	var $paypal_config;


	var $requestEnvelope = array(
		'errorLanguage' => 'en_US',
		'detailLevel' => 'ReturnAll'
	);

	public $paykey;
	public $transactionId;

	public $cancel_nonce;

	function __construct()
	{
		/**
		* If we are using the sandbox, make sure we use the sandbox credentials
		TODO: add senttings page to add these credentials
		*/
		if(get_option('pm_paypal_use_sandbox'))
		{
			$this->apiUrl = 'https://svcs.sandbox.paypal.com/AdaptivePayments/';
			$this->payPalUrl = 'https://www.sandbox.paypal.com/webscr?cmd=_ap-payment&paykey=';
			$this->paypal_config = array(
				'mode' => 'sandbox',
				'api_username' => 'joe_api1.vinsourceonline.com',
				'api_password' => '1386196412',
				'api_signature' => 'AMrrXcTNzBdED44nEtAn8ZAoEyAqA70LUvwYDfdj2E1DLxsfILdV.pWc',
				'api_app_id' => 'APP-80W284485P519543T'
			);

		}
		else
		{
			$this->apiUrl = 'https://svcs.paypal.com/AdaptivePayments/';
			$this->payPalUrl = 'https://www.paypal.com/webscr?cmd=_ap-payment&paykey=';
			$this->paypal_config = array(
				'mode' => 'live',
				'api_username' => 'andrew_api1.vinsourceonline.com',
				'api_password' => '36AHC63HZHQM7CGH',
				'api_signature' => 'AoMbFTZjKYXM8pKp9fqDsF0bVSbTAGh4ugFT0F0.KiC7FmddxckHKWMk',
				'api_app_id' => 'APP-8HM47177SA392642D'
			);

		}
		$this->headers = array(
			"X-PAYPAL-SECURITY-USERID: " . $this->paypal_config['api_username'],
			"X-PAYPAL-SECURITY-PASSWORD: " . $this->paypal_config['api_password'],
			"X-PAYPAL-SECURITY-SIGNATURE: " . $this->paypal_config['api_signature'],
			"X-PAYPAL-APPLICATION-ID: " . $this->paypal_config['api_app_id'], 
			"X-PAYPAL-REQUEST-DATA-FORMAT: JSON",
			"X-PAYPAL-RESPONSE-DATA-FORMAT: JSON"
		);
	}

	/**
	* TODO: What does this do?
	*/
	function getPaymentOptions($paykey)
	{
		$packet = array(
			'requestEnvelope' => $this->requestEnvelope,
			'payKey' => $paykey
		);

		return $this->_paypalSend($packet, 'GetPaymentOptions');
	}

	/**
	* Handles sending any kind of data to PayPal
	*/
	function _paypalSend($data, $call)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->apiUrl.$call);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);

		$reply = curl_exec($ch);
		curl_close($ch);
		return(json_decode($reply, TRUE));
	}

	function createSimplePayment(PMPaymentReceiver $receiver, PMProduct $product)
	{
		// Create paypal specific receiver List
		$receiver_data = array(
			'amount' => $product->price,
			'email' => $receiver->WP_User->user_email
		);
		
		// Create pay request
		if(is_user_logged_in())
		{
			$this->cancel_nonce = wp_create_nonce('cancel_transaction');
			$cancel_args = array(
				'action' => 'cancel_transaction',
				'cancel_nonce' => $this->cancel_nonce
			);
		}

		else
			$cancel_args = array(
				'action' => 'cancel_transaction'
			);

		$cancel_url = add_query_arg($cancel_args, get_permalink($product->ID));
		$createPacket = array(
			'actionType' => 'PAY',
			'currencyCode' => 'USD',
			'receiverList' => array(
				'receiver' => $receiver_data
			),
			'returnUrl' => vs_get_dash(),
			'cancelUrl' => $cancel_url,
			'ipnNotificationUrl' => plugins_url('', __FILE__) . '/peer-marketplace/paypal-ipn-listener.php',
			'requestEnvelope' => $this->requestEnvelope
		);

		$response = $this->_paypalSend($createPacket, 'Pay');
		$debug_data['response'] = $response;
		$debug_data['receiver_data'] = $receiver_data;
		$debug_data['sender'] = wp_get_current_user();
		wp_mail('joseph.carrington@gmail.com', 'Vinsource Transaction Data, PT 1', var_export($debug_data, true));
		// TODO: add error handling here
		if($response['paymentExecStatus'] == 'CREATED')
		{
			$this->paykey = $response['payKey'];

			// Get product info
			$product_title = $product->post->post_title;
			$product_price = $product->price; 
			// Set payment details
			$detailsPacket = array(
				'requestEnvelope' => $this->requestEnvelope,
				'payKey' => $this->paykey,
				'receiverOptions' => array(
					array(
						'receiver' => array(
							'email' => $receiver->WP_User->user_email,
						),
						'invoiceData' => array(
							'item' => array(
								array(
									'name' => $product_title,
									'price' => $product_price,
									'identifier' => $product->ID 
								)
							)
						)
					)
				)
			);

			$response = $this->_paypalSend($detailsPacket, 'SetPaymentOptions');
		} // End $response['paymentExecStatus'] == 'Created'
	} // END createPayment

	function createChainedPayment(PMPaymentReceiverList $receiver_list, PMSplitPaymentHandler $split_handler)
	{
		$receiver_data = array();
		foreach($receiver_list->get_receivers() as $receiver)
		{
			$receiver_data[] = array(
				'amount' => $receiver->amount,
				'email' => $receiver->WP_User->user_email
			);
		}

		// When there is a primary receiver, tehy get the full amount first
		$receiver_data[0]['primary'] = true;
		$receiver_data[0]['amount'] = $split_handler->total;

		// Pay request
		$this->cancel_nonce = wp_create_nonce('cancel_transaction');
		$cancel_args = array(
			'action' => 'cancel_transaction',
			'cancel_nonce' => $this->cancel_nonce
		);

		$cancel_url = add_query_arg($cancel_args, get_permalink($split_handler->product->post->ID));
		$createPacket = array(
			'actionType' => 'PAY',
			'currencyCode' => 'USD',
			'receiverList' => array(
				'receiver' => $receiver_data
			),
			'returnUrl' => vs_get_dash(),
			'cancelUrl' => $cancel_url,
			'ipnNotificationUrl' => plugins_url('', __FILE__) . '/peer-marketplace/paypal-ipn-listener.php',
			'requestEnvelope' => $this->requestEnvelope
		);
		$response = $this->_paypalSend($createPacket, 'Pay');
		$debug_data['response'] = $response;
		$debug_data['receiver_data'] = $receiver_data;
		$debug_data['sender'] = wp_get_current_user();
		wp_mail('joseph.carrington@gmail.com', 'Vinsource Transaction Data, PT 1', var_export($debug_data, true));
		if($response['paymentExecStatus'] == 'CREATED')
		{
			$this->paykey = $response['payKey'];
			
			// Set product info
			$product_title = $split_handler->product->post->post_title;
			$product_price = $split_handler->total;

			$secondary_receiver_amount = round($split_handler->total * $split_handler->secondary_receiver_percentage, 2);
			$primary_receiver_amount = $split_handler->total - $secondary_receiver_amount;

			// Set payment details
			$detailsPacket = array(
				'requestEnvelope' => $this->requestEnvelope,
				'payKey' => $this->paykey,
				'receiverOptions' => array(
					array(
						'receiver' => array(
							'email' => $split_handler->primary_receiver_paypal_address
						),
						'invoiceData' => array(
							'item' => array(
								array(
									'name' => $product_title,
									'price' => $primary_receiver_amount,
									'identifier' => $split_handler->product->post->ID
								)
							)
						)
					),
					array(
						'receiver' => array(
							'email' => $split_handler->secondary_receiver->user_email
						),
						'invoiceData' => array(
							'item' => array(
								array(
									'name' => 'Fee for the sale of ' . $product_title,
									'price' => $secondary_receiver_amount,
									'identifier' => $split_handler->product->post->ID
								)
							)
						)
					)
				)
			);

			$response = $this->_paypalSend($detailsPacket, 'SetPaymentOptions');
		}
	} // End createChainedPayment

	/**
	* Handles sending the user to PayPal to complete a payment
	*/

	function sendUserToPayPal()
	{
		wp_redirect($this->payPalUrl . $this->paykey);
	}
} // END PayPal class

// Handlers the transaction canceling
add_action('init', function()
{
	if(isset($_GET['action']) && isset($_GET['cancel_nonce']) && $_GET['action'] == 'cancel_transaction')
	{
		if(wp_verify_nonce($_GET['cancel_nonce'], 'cancel_transaction'))
		{
			$transaction_post = new WP_Query(array(
				'posts_per_page' => 1,
				'post_type' => 'vs_transaction',
				'post_status' => 'draft',
				'meta_key' => 'pm_transaction_cancel_nonce',
				'meta_value' => $_GET['cancel_nonce']
			));


			if($transaction_post->post_count == 1)
			{
				$transaction_post = $transaction_post->posts[0];
				wp_update_post(array(
					'ID' => $transaction_post->ID,
					'post_status' => 'cancelled'
				));
			}
		}
	}
});

/**
* Extends PMTransaction to also have a PayPal Paykey
*/
class PMPayPalTransaction extends PMTransaction
{
	public $paykey;
	function __construct(WP_Post $post)
	{
		parent::__construct($post);
		$this->paykey = get_post_meta($post->ID, 'pm_transaction_paypal_paykey', true);
	}
}

function pm_get_buyer_paypal_address($buyer_id)
{
	return pm_get_paypal_address($buyer_id);
}

// Right now just gets the first user. TODO: Get paypal user
function pm_get_seller_paypal_address($seller_id)
{
	$winery_users = get_users(array(
		'meta_key' => 'attached_winery',
		'meta_value' => $seller_id
	));

	if(count($winery_users) == 0)
	{
		throw new Exception("Winery has no users associated with it.");
	}
	return pm_get_paypal_address($winery_users[0]->ID);
}

/** 
* Gets the user's email from WP
*/
function pm_get_paypal_address($user_id)
{
	$user = get_userdata($user_id);
	return $user->user_email;
}
