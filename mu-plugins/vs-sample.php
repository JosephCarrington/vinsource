<?php
/**
* Plugin Name: Vinsource : Buy Sample
* Description : A vinsource extension that allows a user to buy a sample of a product at a different price.
*/

add_action('peer_marketplace_after_product_info', 'vinsource_display_sample_form', 10, 1);

function vinsource_display_sample_form($product)
{
	$sample_form = new VinsourceSampleHandler($product, get_current_user_id());
	// Todo, add 'alrady sampled' language
	$sample_form->display_sample_form();
}

class VSSample extends VSProduct
{
	function __construct($post)
	{
		parent::__construct($post);
		$this->price = get_post_meta($this->ID, 'vs_wine_info_sample_price', true);
	}
}

class VinsourceSampleHandler
{
	var $product;
	var $buyer_id;
	var $seller_id;
	var $all_sampled_product_ids;
	var $seller_paypal_address;
	var $buyer_paypal_address;
	var $sample_price;

	function __construct($product, $buyer_id)
	{
		$this->product = $product;
		$this->buyer_id = $buyer_id;
		$this->seller_id = get_post_meta($this->product->ID, 'vs_wine_winery', true);
		$this->all_sampled_product_ids = get_user_meta($this->buyer_id, 'sampled_product_ids');
		$this->seller_paypal_address = pm_get_seller_paypal_address($this->seller_id);
		$this->buyer_paypal_address = pm_get_buyer_paypal_address($this->buyer_id);
		$this->sample_price = get_post_meta($this->product->ID, 'vs_wine_info_sample_price', true);
	}


	function __set($name, $value)
	{
		switch($name)
		{
		case 'buyer_id' :
		case 'seller_id' :
			if(!is_int($value)) throw new Exception($name . " is not an int");
			$this->$name = $value;
		break;
		default :
			$this->$name = $value;
		break;
		}
	}

	function can_be_sampled()
	{
		// We must have a buyer ID and a seller ID
		if(!$this->buyer_id) throw new Exception("buyer_id is not set");
		if(!$this->seller_id) throw new Exception("seller_id is not set");

		// Product can not be sampled more than once
		if($this->has_product_been_sampled()) throw new Exception("product has already been sampled by this user");

		// Both seller and buyer must have PayPal addresses
		if(!$this->buyer_paypal_address) throw new Exception("buyer has no PayPal address set");
		if(!$this->seller_paypal_address) throw new Exception("seller has no PayPal address set");

		// We must have a sample price
		if(!$this->sample_price) throw new Exception("product has no sample price");

		return true;
	}

	function has_product_been_sampled()
	{
		$all_sampled = pm_get_user_sampled($this->buyer_id);
		if(!is_array($all_sampled)) return false;
		return in_array($this->product->ID, $all_sampled);
	}

	function display_sample_form()
	{
		?>
		<ul id='sample_order_section'>
			<li class='sample_header_and_blurb'>
				<ul>
					<li id='sample_order_header'><em>$<?php echo $this->sample_price; ?></em> PER SAMPLE</li>
					<li id='sample_order_blurb'>Full bottle sample - limit one per account</li>
				</ul>
			</li>
			<li class='sample_button'>
				<div id='sample_order_button_wrapper'>
					<?php if($this->has_product_been_sampled())
					{
						?>
						<div class='sample_already_purchased'>Sample purchased</div>
						<?php
					}
					else
					{
						?>
						<a id='sample_order_button' href='<?php echo add_query_arg(array('action' => 'buy_sample', 'buy_nonce' => wp_create_nonce('buy_sample')), get_permalink($this->product->ID)); ?>' title='Buy sample of <?php echo $this->product->post_title; ?>'>Buy Sample</a>
						<?php
					}
					?>
				</div>
			</li>
		</ul><!-- #sample_order_section -->
		<?php
	}
}

add_action('wp', function() {
	if(get_query_var('action') == 'buy_sample' && is_singular('vs_product'))
	{
		if(!isset($_GET['buy_nonce']) OR !wp_verify_nonce($_GET['buy_nonce'], 'buy_sample')) throw new Exception('Oh no, something went wrong! Plese try again.');
		
		// Make sure we can sample the product
		$sample_handler = new VinsourceSampleHandler(get_queried_object(), get_current_user_id());
		try
		{
			$sample_handler->can_be_sampled();
		}
		catch (Exception $e)
		{
			//db($e->getMessage());
		}
		$product = new VSProduct(get_queried_object());
		$receiver_store_id = $product->seller_id;
		$receiver_store = new VSStore(get_post($receiver_store_id));

		$all_store_receivers = $receiver_store->store_users;
		// TODO: Do something other than just getting the first user
		$sample = new VSSample(get_queried_object());
		$receiver = new PMPaymentReceiver($all_store_receivers[0], $sample->price);

		// Make the transaction post
		$transaction_post_args = array(
			'post_author' => get_current_user_id(),
			'post_status' => 'draft',
			'post_type' => 'vs_transaction',
			'post_title' => $sample->post->post_title
		);

		$transaction_post_id = wp_insert_post($transaction_post_args);
		update_post_meta($transaction_post_id, 'pm_transaction_seller_id', $receiver_store_id);
		update_post_meta($transaction_post_id, 'pm_transaction_product_ids', array(array($sample->ID => 'sample')));
		update_post_meta($transaction_post_id, 'pm_transaction_amount', $sample->price);
		update_post_meta($transaction_post_id, 'pm_transaction_type', 'sample');
		
		$paypal = new PeerMarketplacePayPal();
		$paypal->createSimplePayment($receiver, $sample);

		update_post_meta($transaction_post_id, 'pm_transaction_paypal_paykey', $paypal->paykey);
		update_post_meta($transaction_post_id, 'pm_transaction_cancel_nonce', $paypal->cancel_nonce);
		
		$paypal->sendUserToPayPal();
	}
});

function pm_get_product_id($transaction_id)
{
	$product_ids = get_post_meta($transaction_id, 'pm_transaction_product_ids', true);
	$product_id = $product_ids[0];
	return array_keys($product_id)[0];
}

function pm_get_transaction_type($transaction_id)
{
	$product_ids = get_post_meta($transaction_id, 'pm_transaction_product_ids', true);
	$product_id = $product_ids[0];
	return array_values($product_id)[0];
}

function pm_get_user_sampled($user_id)
{
	$all_sampled = get_user_meta($user_id, 'sampled_product_ids', true);
	return $all_sampled;
}

function pm_add_user_sampled($product_id, $user_id)
{
	$all_sampled = pm_get_user_sampled($user_id);
	$all_sampled[] = $product_id;
	update_user_meta($user_id, 'sampled_product_ids', $all_sampled);

	return $all_sampled;
}
