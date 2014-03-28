<?php
/**
* Plugin Name : Vinsource : Split Payment and Bulk Discount
* Description : Allows an administrator to set a secondary payment receiver for certain types of transactions
* Author : Joseph Carrington
* Version : 0.1
*/
add_action('peer_marketplace_before_product_info', function($product)
{
	$product = new VSProduct($product);
	$split_payment_handler = new PMSplitPaymentHandler($product, get_current_user_id());
	$split_payment_handler->display_form();
}, 2);

class PMSplitPaymentHandler
{
	var $product;
	var $buyer_id;
	var $seller_id;
	var $buyer_paypal_address;
	var $primary_receiver_paypal_address;
	var $secondary_receiver;
	var $secondary_receiver_percentage;
	var $btg_pricing;

	private $price_per_item;
	private $items_per_case;
	private $case_quantity;
	private $case_pricing;
	private $total;

	function __construct(PMProduct $product, $buyer_id)
	{
		$this->product = $product;
		$this->buyer_id = $buyer_id;
		$this->seller_id = get_post_meta($this->product->post->ID, 'vs_wine_winery', true);
		$this->buyer_paypal_address = pm_get_buyer_paypal_address($this->buyer_id);
		$this->primary_receiver_paypal_address = pm_get_seller_paypal_address($this->seller_id);
		$this->secondary_receiver = get_userdata(get_option('pm_secondary_payment_receiver'));
		$this->secondary_receiver_percentage = get_option('pm_secondary_payment_factor');
		$this->price_per_item = $product->price;

		$this->items_per_case = 12;

		$this->case_pricing = get_post_meta($this->product->post->ID, 'vs_product_prices', true);
	}

	function __set($name, $value)
	{
		switch($name)
		{
		case 'case_quantity':
			if(!is_int($value)) throw new Exception("Quantity must be of type int");
			$this->$name = $value;
		break;
		case 'price_per_item':
			if(!is_numeric($value)) throw new Exception("Price must be numeric");
			$this->$name = $value;
		break;
		default:
			$this->$name = $value;
		break;
		}
	}

	function __get($name)
	{
		switch($name)
		{
		case 'total' :
			$all_case_amounts = $this->btg_pricing? array_keys($this->case_pricing['btg']) : array_keys($this->case_pricing['regular']);
			$case_price_to_use = $this->case_quantity;
			while($case_price_to_use > 0 && !in_array($case_price_to_use, $all_case_amounts))
			{
				$case_price_to_use--;
			}

			$this->total = $this->btg_pricing ? $this->case_quantity * $this->case_pricing['btg'][$case_price_to_use] : $this->case_quantity * $this->case_pricing['regular'][$case_price_to_use];
			return $this->total;
		break;
		default :
			return $this->$name;
		}
	}


	// Displays a form that allows a user to elect to buy now, and provides an input to select quantity
	function display_form()
	{
		$bulk_prices = get_post_meta($this->product->post->ID, 'vs_product_prices', true);
		?>
		<form class='pm_split_payment_form' action='<?echo add_query_arg('action', 'buy_cases'); ?>' method='POST'>
			<input type='hidden' name='buy_case_nonce' value='<?php echo wp_create_nonce('buy_case_nonce'); ?>' />
			<ul class='pm_split_payment_header'>
				<li class='case_amount'><em>$<?php echo number_format($bulk_prices['regular'][1], 2); ?></em> PER CASE</li>
				<li class='show_discount_wrapper'><a href='#discount_table' class='show_discount_button'><img src='<?php bloginfo('stylesheet_directory');?>/images/tag-show_discounts.png' alt='Show Discounts' /></a></li>
			</ul>
			<?php do_action('pm_under_split_payment_form_header', $this->product); ?>
			<fieldset>
				<ul class='pm_split_payment_case_select_and_buttons'>
					<li class='pm_split_payment_case_select_and_btg_toggle'>
						<ul>
							<li>
								<select name='case_amount' id='case_amount_select'>
									<?php
									for($i = 1; $i <= 20; $i ++)
									{
										$case_string = $i . ' ';
										$case_string .= $i > 1 ? 'cases' : 'case';
										echo "<option value='$i'>$case_string</option>";
									}
									?>
								</select>
							</li>
							<li>
								<input type='checkbox' name='btg_placement' id='btg_placement' /><label for='btg_placement'>BTG PLACEMENT</label>
							</li>
						</ul>
					</li>
					<li class='pm_spilt_payment_button_wrapper'>
						<input type='submit' class='split_payment_button' value='<?php echo apply_filters('pm_split_payment_button_text', 'Buy'); ?>' />
					</li>
				</ul>
			</fieldset>
		</form><!-- .pm_split_payment_form -->
		<?php do_action('peer_marketplace_after_split_payment_form', $this->product); ?>
		<?php
	}
}

add_action('wp_enqueue_scripts', function()
{
	if(is_singular('vs_product'))
	{
		$bulk_prices = get_post_meta(get_queried_object_id(), 'vs_product_prices', true);
		wp_register_script('bulk-pricing', plugin_dir_url(__FILE__) . 'peer-marketplace/js/bulk-pricing.js', array('jquery'));
		wp_localize_script('bulk-pricing', 'bulk_pricing_data', $bulk_prices);
		wp_enqueue_script('bulk-pricing');
	}
});

add_action('wp', function() {
	if(get_query_var('action') == 'buy_cases' && is_singular('vs_product'))
	{
		if(!isset($_POST['buy_case_nonce']) OR !wp_verify_nonce($_POST['buy_case_nonce'], 'buy_case_nonce') OR !isset($_POST['case_amount'])) throw new Exception('There was an error. Please try again. Error code #NP01');
		$case_quantity = $_POST['case_amount'];
		$btg_pricing = isset($_POST['btg_placement']);

		$product = new VSProduct(get_queried_object());
		$split_handler = new PMSplitPaymentHandler($product, get_current_user_id());
		$split_handler->case_quantity = intval($case_quantity);
		$split_handler->btg_pricing = $btg_pricing;

		$secondary_receiver_amount = round($split_handler->total * $split_handler->secondary_receiver_percentage, 2);
		$primary_receiver_amount = $split_handler->total - $secondary_receiver_amount;
		$receiver_store_id = $product->seller_id;
		$receiver_store = new VSStore(get_post($receiver_store_id));
		
		$all_store_receivers =  $receiver_store->store_users;

		$receiver_list = new PMPaymentReceiverList();

		$primary_receiver = new PMPaymentReceiver($all_store_receivers[0], $primary_receiver_amount);
		$secondary_receiver = new PMPaymentReceiver($split_handler->secondary_receiver, $secondary_receiver_amount);
		$receiver_list->add_receiver($primary_receiver);
		$receiver_list->add_receiver($secondary_receiver);

		$paypal = new PeerMarketplacePayPal();
		// If the winery's paypal address is not verified yet, it will throw an error if we try to use a chained payment
		if(get_post_meta($receiver_store_id, 'pm_paypal_verified', true) == 'verified')
		{
			$paypal->createChainedPayment($receiver_list, $split_handler);
		}
		else
		{
			$simple_product = new VSProduct(get_queried_object());
			$simple_product->price = $split_handler->total;
			$paypal->createSimplePayment($primary_receiver, $simple_product);
		}

		// Make the transaction post
		$transaction_post_args = array(
			'post_author' => get_current_user_id(),
			'post_status' => 'draft',
			'post_type' => 'vs_transaction',
			'post_title' => $split_handler->product->post->post_title
		);

		$transaction_post_id = wp_insert_post($transaction_post_args);
		update_post_meta($transaction_post_id, 'pm_transaction_seller_id', $receiver_store_id);
		update_post_meta($transaction_post_id, 'pm_transaction_product_ids', array(array($split_handler->product->post->ID => $split_handler->case_quantity)));
		update_post_meta($transaction_post_id, 'pm_transaction_amount', $split_handler->total);
		update_post_meta($transaction_post_id, 'pm_transaction_type', 'buy now');
		update_post_meta($transaction_post_id, 'vs_btg_placement', $split_handler->btg_pricing);
		update_post_meta($transaction_post_id, 'pm_transaction_paypal_paykey', $paypal->paykey);
		update_post_meta($transaction_post_id, 'pm_transaction_cancel_nonce', $paypal->cancel_nonce);
	
		$paypal->sendUserToPayPal();
	}
});

