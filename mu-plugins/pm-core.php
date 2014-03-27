<?php
/*
*Plugin Name : Peer Marketplace : Core
*/
/** TODO: Why does this have a singleton? */
class PeerMarketplace
{
	private $current_transaction;
	private $payment_handler;
	public static function getInstance()
	{
		static $instance = null;
		if(null === $instance)
		{
			$instance = new static();
		}

		return $instance;
	}

	protected function __construct()
	{
	
	}

	private function __clone()
	{

	}

	private function __wakeup()
	{

	}

	function __set($name, $value)
	{
		switch($name)
		{
		case 'current_transaction':
			if(!is_a($value, 'PMTransaction'))throw new Exception("Current Transaction must be of class type PMTransaction");
			$this->name = $value;
		break;

		case 'payment_handler':
			if(!is_a($value, 'PMPaymentHandler')) throw new Exception("Payment handler must have PMPaymentHandler as parent");
			$this->name = $value;
		break;

		default:
			$this->name = $value;
		break;
		}
	}
}

class PMProduct
{
	public $post;
	public $ID;
	public $price;
	public $seller_id;

	public $meta;
	function __construct(WP_Post $post)
	{
		$this->post = $post;
		$this->ID = $this->post->ID;

		$this->meta = get_post_custom($this->ID);
		
		$this->price = $this->meta['product_price'];
	}
}

class PMStore
{
	public $post;
	public $ID;

	function __construct(WP_Post $post)
	{

		$this->post = $post;
		$this->ID = $this->post->ID;
	}
}

class PMTransaction
{
	public $post;
	public $id;
	public $status;
	public $type;
	public $buyer_id;
	public $seller_id;
	public $amount;

	public $date_opened;
	public $date_closed;

	public $product_ids;

	function __construct(WP_Post $post)
	{
		$this->post = $post;
		$this->ID = $this->post->ID;

		$this->status = get_post_status($post->ID);
		$this->type = get_post_meta($this->ID, 'pm_transaction_type', true);
		$this->buyer_id = $this->post->post_author;
		$this->seller_id = get_post_meta($this->ID, 'pm_transaction_seller_id', true);
		$this->amount = get_post_meta($this->ID, 'pm_transaction_amount', true);
		$this->date_opened = get_post_meta($this->ID, 'pm_transaction_date_opened', true);
		$this->amount = get_post_meta($this->ID, 'pm_transaction_date_closed', true);
		$this->product_ids = get_post_meta($this->ID, 'pm_transaction_product_ids');
	}
}

/**
* An array of PMPaymentReceivers for sending money to multiple people from the same sender, using Adaptive Payments Chained Payments
*/
class PMPaymentReceiverList
{
	private $receivers;
	function __construct()
	{

	}

	/* Add a receiver. SHould eventually do some kind of validation against PP requirements */
	function add_receiver(PMPaymentReceiver $receiver)
	{
		$this->receivers[] = $receiver;
	}

	/* Returns all receivers. */
	function get_receivers()
	{
		return $this->receivers;
	}
}


/**
* A container for a  WP_User and an amount of money to send to them
*/
class PMPaymentReceiver
{
	public $WP_User;
	public $amount;
	function __construct(WP_User $WP_User, $amount)
	{
		$this->WP_User = $WP_User;
		$this->amount = $amount;
	}
}

/**
* Creates the various types of payments required by Peer Marketplace.
*/
interface PMPaymentHandler
{
	/* A single payment from one sender to one reciever. */
	function createSimplePayment(PMPaymentReceiver $receiver, PMProduct $product);
	/* A single payment form one sender to two or more receivers */
	function createChainedPayment(PMPaymentReceiverList $receiver_list,  PMSplitPaymentHandler $split_handler);
}

/**
* Adds the admin setions for Peer Marketplace
*/
add_action('admin_init', function()
{
	add_settings_section('pm_core_settings', 'Peer Marketplace Settings', function()
	{
		echo "<p>Here you can control the basic options for the Peer Marketplace plugin</p>";

	}, 'general');

	register_setting('general', 'pm_secondary_payment_factor', function($val)
	{
		if(is_numeric($val)) return $val;
	});

	register_setting('general', 'pm_secondary_payment_receiver', function($val)
	{
		if(get_userdata($val)) return $val;
	});

	register_setting('general', 'pm_paypal_use_sandbox', function($val)
	{
		if($val  == '1') return 'use_sandbox';
	});

	add_settings_field('pm_paypal_use_sandbox', 'Use PayPal Sandbox', function()
	{
		?>
		<input type='checkbox' name='pm_paypal_use_sandbox' value='1' <?php if(get_option('pm_paypal_use_sandbox')) echo 'checked'; ?>>Use PayPal Sandbox</input>
		<?php

	}, 'general', 'pm_core_settings');
	add_settings_field('pm_secondary_payment_factor', 'Secondary Payment Factor', function()
	{
		?>
		<input type='number' name='pm_secondary_payment_factor' min='0' max='1' step='0.01' value='<?php echo get_option('pm_secondary_payment_factor'); ?>' /> As a number between 0 and 1
		<?php
	}, 'general', 'pm_core_settings');

	add_settings_field('pm_secondary_payment_receiver', 'Secondary Payment Receiver', function()
	{
		?>
		<select name='pm_secondary_payment_receiver'>
			<option value='na'>Please Select...</option>
			<?php
			$all_admins = get_users(array(
				'role' => 'administrator'
			));

			foreach($all_admins as $admin)
			{
				?>
				<option value='<?php echo $admin->ID; ?>' <?php if(get_option('pm_secondary_payment_receiver') == $admin->ID) echo 'selected'; ?>><?php echo $admin->user_email; ?></option>
				<?php
			}
			?>
		</select> Only admin users can be set as the secondary payment receiver.
		<?php
	}, 'general', 'pm_core_settings');

});
