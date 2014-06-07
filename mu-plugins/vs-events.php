<?php
/**
* Plugin Name: Vinsource : Events Pass
* Description: Events pass allows a user to make purchases for a finite amount of time
* Version: 0.1.0
*/

// Extends VProduct to add a price and a time-limit
class VSEventPass extends PMProduct
{
	private $start_time;
	private $time_to_live;
	private $expiration;
	function __construct(WP_Post $post)
	{
		$this->post = $post;
		$this->ID = $this->post->ID;
		// TODO: add fields for event price and time limit
		$this->price = 50.00;
		$this->start_time = time();
		$this->time_to_live = (30 * 24 * 60 * 60); // 30 days, 24 hours, 60 minutes, 60 seconds
		$this->expiration = $this->start_time + $this->time_to_live;
	}
}

add_action('init', function()
{
	if(isset($_GET['action']) && $_GET['action'] == 'purchase_event_pass')
	{
		// Get event 
		$event_post = get_post(get_option('pm_event_pass_id'));
		// Create product
		$event_pass = new VSEventPass($event_post);
		// Create PayPal Handler
		$paypal = new PeerMarketplacePaypal();
		// Create Vinsource user to recieve funds
		$paypal_receiver = get_user_by('email', 'andrew@vinsourceonline.com');
		$paypal_receiver = new PMPaymentReceiver($paypal_receiver, $event_pass->price, get_bloginfo('url'));

		// Unlike with the regular transactions, we don't generate a transaction before payment to prevent DOS style attacks
		$paypal->createSimplePayment($paypal_receiver, $event_pass);
		$paypal->sendUserToPaypal();
	}
});

add_action('init', function()
{
	register_post_type('other_product', array(
		'label' => 'Other Products',
		'public' => true,
		'supports' => array(
			'editor',
			'title',
			'custom-fields'
		)
	));
});

add_action('admin_init', function()
{
	register_setting('general', 'pm_event_pass_id', function($val)
	{
		if(is_numeric($val)) return $val;
	});

	add_settings_field('pm_event_pass_id', 'Event Pass ID', function()
	{
		?>
		<input type='text' name='pm_event_pass_id' value='<?php echo get_option('pm_event_pass_id'); ?>' />
		<?php
	}, 'general', 'pm_core_settings');

});

