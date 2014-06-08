<?php
/**
* Plugin Name: Vinsource Core
*/

/** Extends PMProduct to use the VS nomenclature **/
class VSProduct extends PMProduct
{
	public $price;
	function __construct(WP_Post $post)
	{
		$this->post = $post;
		$this->ID = $this->post->ID;

		$this->price = get_post_meta($this->ID, 'vs_wine_info_price', true);
		$this->seller_id = get_post_meta($this->ID, 'vs_wine_winery', true);
	}
}

/** Extends PMStore to include users attached to this winery as admins **/
class VSStore extends PMStore
{
	public $store_users;

	function __construct(WP_Post $post)
	{
		parent::__construct($post);
		$this->store_users = get_users(array(
			'meta_key' => 'attached_winery',
			'meta_value' => $this->ID
		));
	}
}

// Add post types
add_action('init', function() {
	register_post_type('vs_store', array(
		'label' => 'Wineries',
		// TODO: add all labels
		'labels' => array(
			'name' => 'Wineries',
			'singular_name' => 'Winery'
		),
		'public' => true,
		'supports' => array(
			'title',
			'thumbnail',
			'page-attributes'
		),
		'register_meta_box_cb' => function() {
			add_meta_box('vs_winery_info_meta_boxes', 'Winery Info', 'vs_winery_info_meta_boxes', 'vs_store', 'normal', 'high');
		},
		'has_archive' => true
	));

	register_post_type('vs_product', array(
		'label' => 'Wines',
		//TODO: add all labels
		'labels' => array(
			'name' => 'Wines',
			'singular_name' => 'Wine'
		),
		'public' => true,
		'supports' => array(
			'title',
			'thumbnail',
			'page-attributes',
			'editor'
		),
		'register_meta_box_cb' => function() {
			add_meta_box('vs_wine_seller_meta_boxes', 'Winery Selection', 'vs_wine_seller_meta_boxes', 'vs_product', 'normal', 'high');
			add_meta_box('vs_wine_info_meta_boxes', 'Wine Info', 'vs_wine_info_meta_boxes', 'vs_product', 'normal', 'high');
			add_meta_box('vs_case_meta_boxes', 'Case Prices', 'vs_case_meta_boxes', 'vs_product', 'normal', 'high');
		},
		'has_archive' => true
	));

	register_taxonomy('varietal', 'vs_product', array(
		'label' => 'Varietals',
		'labels' => array(
			'name' => 'Varietals',
			'singular_name' => 'Varietal',
			'all_items' => 'All Varietals',
			'edit_item' => 'Edit Varietal',
			'update_item' => 'Update Varietal',
			'add_new_item' => 'Add New Varietal',
			'new_item_name' => 'New Varietal Name',
			'search_items' => 'Search Varietals',
			'popular_items' => 'Popular Varietals',
			'separate_items_with_commas' => 'Separate varietals with commas',
			'add_or_remove_items' => 'Add or remove varietals',
			'choose_from_most_used' => 'Choose from the most used varietals'
		),
		'sort' => true
	));

	register_taxonomy('wine_country', 'vs_product', array(
		'label' => 'Country',
		'labels' => array(
			'name' => 'Countries',
			'singular_name' => 'Country',
			'all_items' => 'All Countries',
			'edit_item' => 'Edit Country',
			'update_item' => 'Update Country',
			'add_new_item' => 'Add New Country',
			'new_item_name' => 'New Country Name',
			'search_items' => 'Search Countries',
			'popular_items' => 'Popular Countries',
			'add_or_remove_items' => 'Add or remove countries',
			'choose_from_most_used' => 'Choose from the mose used countries'
		),
		'hierarchical' => true
	));

	register_taxonomy('wine_state', 'vs_product', array(
		'label' => 'State',
		'labels' => array(
			'name' => 'States',
			'singular_name' => 'State',
			'all_items' => 'All States',
			'edit_item' => 'Edit State',
			'update_item' => 'Update State',
			'add_new_item' => 'Add New State',
			'new_item_name' => 'New State Name',
			'search_items' => 'Search States',
			'popular_items' => 'Popular States',
			'add_or_remove_items' => 'Add or remove states',
			'choose_from_most_used' => 'Choose from the mose used states'
		),
		'hierarchical' => true
	));

	register_taxonomy('wine_region', 'vs_product', array(
		'label' => 'Region',
		'labels' => array(
			'name' => 'Regions',
			'singular_name' => 'Region',
			'all_items' => 'All Regions',
			'edit_item' => 'Edit Region',
			'update_item' => 'Update Region',
			'add_new_item' => 'Add New Region',
			'new_item_name' => 'New Region Name',
			'search_items' => 'Search Regions',
			'popular_items' => 'Popular Regions',
			'add_or_remove_items' => 'Add or remove regions',
			'choose_from_most_used' => 'Choose from the mose used regions'
		),
		'hierarchical' => true
	));

	register_taxonomy('wine_appelation', 'vs_product', array(
		'label' => 'Appelation',
		'labels' => array(
			'name' => 'Appelations',
			'singular_name' => 'Appelation',
			'all_items' => 'All Appelations',
			'edit_item' => 'Edit Appelation',
			'update_item' => 'Update Appelation',
			'add_new_item' => 'Add New Appelation',
			'new_item_name' => 'New Appelation Name',
			'search_items' => 'Search Appelations',
			'popular_items' => 'Popular Appelations',
			'add_or_remove_items' => 'Add or remove appelations',
			'choose_from_most_used' => 'Choose from the mose used appelations'
		),
		'hierarchical' => true
	));

	register_post_type('vs_transaction', array(
		'label' => 'Transactions',
		'labels' => array(
			'name' => 'Transactions',
			'singular_name' => 'Transaction',
			'all_items' => 'All Transactions',
			'add_new' => 'Manually Record New',
			'add_new_item' => 'Manually Record New Transaction',
			'edit_item' => 'Edit Transaction',
			'new_item' => 'New Transaction',
			'view_item' => 'View Transaction',
			'items_archive' => 'Transaction Archive',
			'search_items' => 'Search Transactions',
			'not_found' => 'No transactions found',
			'not_found_in_trash' => 'No transactions found in trash'
		),
		'publicly_queryable' => true,
		'show_ui' => true,
		'supports' => false,
		'has_archive' => true,
		'register_meta_box_cb' => function() {
			add_meta_box('vs_transaction_meta_boxes', 'Transaction Details', 'vs_transaction_meta_boxes', 'vs_transaction', 'normal', 'high');
		}
	));

	register_post_status('declined', array(
		'label' => 'Declined',
		'label_count' => _n_noop("Declined <span class='count'>(%s)</span>", "Declined <span class='count'>(%s)</span>")
	));

	register_post_status('cancelled', array(
		'label' => 'Cancelled',
		'label_count' => _n_noop("Cancelled <span class='count'>(%s)</span>", "Cancelled <span class='count'>(%s)</span>")
	));

});

// Gross Helper Function
function vs_create_text_input($label, $name, $value, $required = false, $disabled = false)
{
	$return = "<label for='$name'>$label</label>";
	$return .= "<input type='text' name='$name' id='$name' value='$value'";
	$return .= $required ? ' required': '';
	$return .= $disabled ? ' disabled': '';
	$return .= ' />';

	return $return;
}

// Extra fields for transaction post types
function vs_transaction_meta_boxes()
{
	global $post;
	?>
	<input type='hidden' name='vs_transaction_info_nonce' id='vs_transaction_info_nonce' value='<?php echo wp_create_nonce(plugin_basename(__FILE__)); ?>' />
	<?php
	echo vs_create_text_input('Type', 'pm_transaction_type', get_post_meta($post->ID, 'pm_transaction_type', true), false, true);
	echo vs_create_text_input('Status', 'pm_transaction_status', get_post_status($post->ID), false, true);
	echo vs_create_text_input('Amount', 'pm_transaction_amount', get_post_meta($post->ID, 'pm_transaction_amount', true), false, true);
	echo vs_create_text_input('Opened', 'pm_transaction_open_date', $post->post_date, false, true);
	echo vs_create_text_input('Closed', 'pm_transaction_close_date', get_post_meta($post->ID, 'pm_transaction_close_date', true), false, true);
}

// Allows the admin to select which winery produces a wine
function vs_wine_seller_meta_boxes()
{
	global $post;
	?>
	<input type='hidden' name='vs_wine_info_nonce' id='vs_wine_info_nonce' value='<?php echo wp_create_nonce(plugin_basename(__FILE__)); ?>' />
	<label for='vs_wine_winery'>Which winery produces this wine?</label>
	<?php
		$all_wineries = new WP_Query(array(
			'post_type' => 'vs_store',
			'posts_per_page' => -1,
			'post_status' => 'publish'
		));

		$current_winery = get_post_meta($post->ID, 'vs_wine_winery', true);
	?>
	<select name='vs_wine_winery' id='vs_wine_winery' required>
		<option>Please Select...</option>
		<?php foreach($all_wineries->posts as $winery)
		{
			if($winery->ID == $current_winery)
			{
				?>
				<option value='<?php echo $winery->ID; ?>' selected><?php echo $winery->post_title; ?></option>
				<?php
			}
			else
			{
				?>
				<option value='<?php echo $winery->ID; ?>'><?php echo $winery->post_title; ?></option>
				<?php
			}
		}?>
	</select><!-- #vs_wine_winery -->
	<?php
}

// Extra fields for wine post types.
function vs_wine_info_meta_boxes()
{
	global $post;
	?>
	<fieldset>
	<legend>Vintage Details</legend>
		<label for='vs_wine_info_wine_year'>Year</label>
		<input type='text' required id='vs_wine_info_wine_year' name='vs_wine_info_wine_year' value='<?php echo get_post_meta($post->ID, 'vs_wine_info_wine_year', true); ?>' />
		<label for='vs_wine_info_case_production'>Case Production</label>
		<input type='number' step='1' min='1' id='vs_wine_info_case_production' name='vs_wine_info_case_production' value='<?php echo get_post_meta($post->ID, 'vs_wine_info_case_production', true); ?>' />
	</fieldset>
	<fieldset>
		<legend>Pricing</legend>
		<label for='vs_wine_info_price'>Suggested Retail Price (in US Dollars)</label>
		<input type='number' step='0.01' min='1' required id='vs_wine_info_price' name='vs_wine_info_price' value='<?php echo get_post_meta($post->ID, 'vs_wine_info_price', true); ?>'/>
		<label for='vs_wine_info_sample_price'>Sample Price (in US Dollars)</label>
		<input type='number' step='0.01' min='1' id='vs_wine_info_sample_price' name='vs_wine_info_sample_price' value='<?php echo get_post_meta($post->ID, 'vs_wine_info_sample_price', true); ?>' />
	</fieldset>
	<?php
}

// The different prices for different case amounts
function vs_case_meta_boxes()
{
	global $post;
	$case_prices = get_post_meta($post->ID, 'vs_product_prices');
	$case_prices = $case_prices[0];

	$discount_percents = get_post_meta($post->ID, 'vs_product_discount_percents');
	$discount_percents = $discount_percents[0];
	?>
	<fieldset>
		<legend>Restaurant Prices</legend>
		<table class='widefat'>
			<tr>
				<th></th>
				<th>1</th>
				<th>2</th>
				<th>3+</th>
				<th>5+</th>
				<th>10+</th>
			</tr>
			<tr>
				<th scope='row'>Standard Discount Percent</th>
				<td><input name='discount_percent[restaurant][1]' type='number' required min='1' step='1' value='<?php echo $discount_percents['restaurant'][1]; ?>' /></td>
				<td><input name='discount_percent[restaurant][2]' type='number' required min='1' step='1' value='<?php echo $discount_percents['restaurant'][2]; ?>' /></td>
				<td><input name='discount_percent[restaurant][3]' type='number' required min='1' step='1' value='<?php echo $discount_percents['restaurant'][3]; ?>' /></td>
				<td><input name='discount_percent[restaurant][5]' type='number' required min='1' step='1' value='<?php echo $discount_percents['restaurant'][5]; ?>' /></td>
				<td><input name='discount_percent[restaurant][10]' type='number' required min='1' step='1' value='<?php echo $discount_percents['restaurant'][10]; ?>' /></td>
			</tr>
			<tr>
				<th scope='row'>Standard Case Price</th>
				<td><input name='case_price[regular][1]' type='number' required min='1' step='1' value='<?php echo $case_prices['regular'][1]; ?>' /></td>
				<td><input name='case_price[regular][2]' type='number' required min='1' step='1' value='<?php echo $case_prices['regular'][2]; ?>' /></td>
				<td><input name='case_price[regular][3]' type='number' required min='1' step='1' value='<?php echo $case_prices['regular'][3]; ?>' /></td>
				<td><input name='case_price[regular][5]' type='number' required min='1' step='1' value='<?php echo $case_prices['regular'][5]; ?>' /></td>
				<td><input name='case_price[regular][10]' type='number' required min='1' step='1' value='<?php echo $case_prices['regular'][10]; ?>' /></td>
			</tr>
			<tr>
				<th scope='row'>BTG Discount</th>
				<td><input name='discount_percent[btg][1]' type='number' required min='1' step='1' value='<?php echo $discount_percents['btg'][1]; ?>' /></td>
				<td><input name='discount_percent[btg][2]' type='number' required min='1' step='1' value='<?php echo $discount_percents['btg'][2]; ?>' /></td>
				<td><input name='discount_percent[btg][3]' type='number' required min='1' step='1' value='<?php echo $discount_percents['btg'][3]; ?>' /></td>
				<td><input name='discount_percent[btg][5]' type='number' required min='1' step='1' value='<?php echo $discount_percents['btg'][5]; ?>' /></td>
				<td><input name='discount_percent[btg][10]' type='number' required min='1' step='1' value='<?php echo $discount_percents['btg'][10]; ?>' /></td>
			</tr>
			<tr>
				<th scope='row'>BTG</th>
				<td><input name='case_price[btg][1]' type='number' required min='1' step='1' value='<?php echo $case_prices['btg'][1]; ?>' /></td>
				<td><input name='case_price[btg][2]' type='number' required min='1' step='1' value='<?php echo $case_prices['btg'][2]; ?>' /></td>
				<td><input name='case_price[btg][3]' type='number' required min='1' step='1' value='<?php echo $case_prices['btg'][3]; ?>' /></td>
				<td><input name='case_price[btg][5]' type='number' required min='1' step='1' value='<?php echo $case_prices['btg'][5]; ?>' /></td>
				<td><input name='case_price[btg][10]' type='number' required min='1' step='1' value='<?php echo $case_prices['btg'][10]; ?>' /></td>
			</tr>
		</table>
	</fieldset>
	<fieldset>
		<legend>Retail Prices</legend>
		<table class='widefat'>
			<tr>
				<th></th>
				<th>1</th>
				<th>2</th>
				<th>3+</th>
				<th>5+</th>
				<th>10+</th>
			</tr>
			<tr>
				<th scope='row'>Standard Discount Percent</th>
				<td><input name='discount_percent[retail][1]' type='number' required min='1' step='1' value='<?php echo $discount_percents['retail'][1]; ?>' /></td>
				<td><input name='discount_percent[retail][2]' type='number' required min='1' step='1' value='<?php echo $discount_percents['retail'][2]; ?>' /></td>
				<td><input name='discount_percent[retail][3]' type='number' required min='1' step='1' value='<?php echo $discount_percents['retail'][3]; ?>' /></td>
				<td><input name='discount_percent[retail][5]' type='number' required min='1' step='1' value='<?php echo $discount_percents['retail'][5]; ?>' /></td>
				<td><input name='discount_percent[retail][10]' type='number' required min='1' step='1' value='<?php echo $discount_percents['retail'][10]; ?>' /></td>
			</tr>
			<tr>
				<th scope='row'>Standard Case Price</th>
				<td><input name='case_price[retail][1]' type='number' required min='1' step='1' value='<?php echo $case_prices['retail'][1]; ?>' /></td>
				<td><input name='case_price[retail][2]' type='number' required min='1' step='1' value='<?php echo $case_prices['retail'][2]; ?>' /></td>
				<td><input name='case_price[retail][3]' type='number' required min='1' step='1' value='<?php echo $case_prices['retail'][3]; ?>' /></td>
				<td><input name='case_price[retail][5]' type='number' required min='1' step='1' value='<?php echo $case_prices['retail'][5]; ?>' /></td>
				<td><input name='case_price[retail][10]' type='number' required min='1' step='1' value='<?php echo $case_prices['retail'][10]; ?>' /></td>
			</tr>
		</table>
	</fieldset>
	<fieldset>
		<legend>Event Prices</legend>
		<table class='widefat'>
			<tr>
				<th>Event Prices</th>
				<th>1</th>
				<th>2</th>
				<th>3+</th>
				<th>5+</th>
				<th>10+</th>
			</tr>
			<tr>
				<th scope='row'>Event Discount Percent</th>
				<td><input name='discount_percent[event][1]' type='number' required min='1' step='1' value='<?php echo $discount_percents['event'][1]; ?>' /></td>
				<td><input name='discount_percent[event][2]' type='number' required min='1' step='1' value='<?php echo $discount_percents['event'][2]; ?>' /></td>
				<td><input name='discount_percent[event][3]' type='number' required min='1' step='1' value='<?php echo $discount_percents['event'][3]; ?>' /></td>
				<td><input name='discount_percent[event][5]' type='number' required min='1' step='1' value='<?php echo $discount_percents['event'][5]; ?>' /></td>
				<td><input name='discount_percent[event][10]' type='number' required min='1' step='1' value='<?php echo $discount_percents['event'][10]; ?>' /></td>
			</tr>
			<tr>
				<th scope='row'>Standard Event Case Price</th>
				<td><input name='case_price[event][1]' type='number' required min='1' step='1' value='<?php echo $case_prices['event'][1]; ?>' /></td>
				<td><input name='case_price[event][2]' type='number' required min='1' step='1' value='<?php echo $case_prices['event'][2]; ?>' /></td>
				<td><input name='case_price[event][3]' type='number' required min='1' step='1' value='<?php echo $case_prices['event'][3]; ?>' /></td>
				<td><input name='case_price[event][5]' type='number' required min='1' step='1' value='<?php echo $case_prices['event'][5]; ?>' /></td>
				<td><input name='case_price[event][10]' type='number' required min='1' step='1' value='<?php echo $case_prices['event'][10]; ?>' /></td>
			</tr>
			<tr>
				<th scope='row'>Standard Event Bottle Price</th>
				<td><input name='case_price[event_bottle][1]' type='number' required min='1' step='1' value='<?php echo $case_prices['event_bottle'][1]; ?>' /></td>
				<td><input name='case_price[event_bottle][2]' type='number' required min='1' step='1' value='<?php echo $case_prices['event_bottle'][2]; ?>' /></td>
				<td><input name='case_price[event_bottle][3]' type='number' required min='1' step='1' value='<?php echo $case_prices['event_bottle'][3]; ?>' /></td>
				<td><input name='case_price[event_bottle][5]' type='number' required min='1' step='1' value='<?php echo $case_prices['event_bottle'][5]; ?>' /></td>
				<td><input name='case_price[event_bottle][10]' type='number' required min='1' step='1' value='<?php echo $case_prices['event_bottle'][10]; ?>' /></td>
			</tr>
		</table>
	</fieldset>
	<?php
}

// Styling the above fields
add_action('admin_head', function()
{
	?>
	<style type='text/css'>
		
		#vs_case_meta_boxes input[type='number']
		{
			max-width: 75px;
		}

		#vs_case_meta_boxes table
		{
			margin-bottom: 1em;
		}
	</style>
	<?php
});

// A bunch of extra stuff for wineries. THe current admin user for that winery, whether or not the winery has a valid PayPal address, as well as contact info required for the restaurant.
function vs_winery_info_meta_boxes()
{
	global $post;
	?>
	<input type='hidden' name='vs_winery_info_nonce' id='vs_winery_info_nonce' value='<?php echo wp_create_nonce(plugin_basename(__FILE__)); ?>' />
	<?php

	if($post != null && $post->post_status == 'publish')
	{
		$winery_users = get_users(array(
			'meta_key' => 'attached_winery',
			'meta_value' => $post->ID
		));
		if(count($winery_users) > 0)
		{
			?>
			<fieldset>
			<legend>Winery User Info</legend>
				<p>The following users are administrators of this winery:</p>
				<ul>
					<?php 
					foreach($winery_users as $winery_user)
					{
						?>
						<li>
							<a href='<?php echo get_edit_user_link($winery_user->ID); ?>' title='Edit <?php echo $winery_user->display_name; ?>'>
								<?php echo $winery_user->display_name; ?>
							</a>
						</li>
						<?php
					}
					?>
				</ul>
			</fieldset>
			<fieldset>
				<legend>PayPal Account Status</legend>
				<label for='pm_paypal_verified'>Winery has PayPal verified account attached</label>
				<input type='checkbox' name='pm_paypal_verified' id='pm_paypal_verified' value='verified' <?php if(get_post_meta($post->ID, 'pm_paypal_verified', true) == 'verified') echo 'checked'; ?>>
			</fieldset>
			
			<?php
		}
	} ?>

	<fieldset>
		<legend>Contact Details</legend>
		<label for='vs_winery_info_phone'>Phone Number</label>
		<input type='tel' name='vs_winery_info_phone' id='vs_winery_info_phone' required value='<?php echo get_post_meta($post->ID, 'vs_winery_info_phone', true); ?>' />
		<label for='vs_winery_info_accounts_rec'>Accounts Receivable</label>
		<input type='text' name='vs_winery_info_account_rec' id='vs_winery_info_account_rec' value='<?php echo get_post_meta($post->ID, 'vs_winery_info_account_rec', true); ?>' />
	</fieldset>
	<fieldset>
		<legend>Address</legend>
		<label for='vs_winery_address_1'>Address 1</label>
		<input type='text' name='vs_winery_address_1' id='vs_winery_address_1' required value='<?php echo get_post_meta($post->ID, 'vs_winery_address_1', true); ?>' />
		<label for='vs_winery_address_2'>Address 2</label>
		<input type='text' name='vs_winery_address_2' id='vs_winery_address_2' value='<?php echo get_post_meta($post->ID, 'vs_winery_address_2', true); ?>' />

		<label for='vs_winery_city'>City</label>
		<input type='text' name='vs_winery_city' id='vs_winery_city' required value='<?php echo get_post_meta($post->ID, 'vs_winery_city', true); ?>' />

		<label for='vs_winery_state'>State</label>
		<input type='text' name='vs_winery_state' id='vs_winery_state' required value='<?php echo get_post_meta($post->ID, 'vs_winery_state', true); ?>' />

		<label for='vs_winery_zip'>Zip</label>
		<input type='text' name='vs_winery_zip' id='vs_winery_zip' required value='<?php echo get_post_meta($post->ID, 'vs_winery_zip', true); ?>' />
	</fieldset>
	<?php
}


// Saves all the extra fields we just added
add_action('save_post', 'vs_save_custom_meta');
function vs_save_custom_meta($post_id)
{
	if(!isset($_POST['post_type']) OR (($_POST['post_type'] != 'vs_product' && $_POST['post_type'] != 'vs_store'))) return;
	if(!current_user_can('edit_post', $post_id)) return $post_id;
	$meta = array();

	switch($_POST['post_type'])
	{
	case 'vs_product' :
		if(!isset($_POST['vs_wine_info_nonce']) OR !wp_verify_nonce($_POST['vs_wine_info_nonce'], plugin_basename(__FILE__))) return $post_id;
		// Verified!


		$meta['vs_wine_winery'] = $_POST['vs_wine_winery'];

		$meta['vs_wine_info_wine_year'] = $_POST['vs_wine_info_wine_year'];
		$meta['vs_wine_info_case_production'] = $_POST['vs_wine_info_case_production'];
		$meta['vs_wine_info_price'] = $_POST['vs_wine_info_price'];
		$meta['vs_wine_info_sample_price'] = $_POST['vs_wine_info_sample_price'];
		$meta['vs_product_prices'] = $_POST['case_price'];
		$meta['vs_product_discount_percents'] = $_POST['discount_percent'];
	break;
	
	 case 'vs_store' :
		if(!isset($_POST['vs_winery_info_nonce']) OR !wp_verify_nonce($_POST['vs_winery_info_nonce'], plugin_basename(__FILE__))) return $post_id;

		$meta['pm_paypal_verified'] = isset($_POST['pm_paypal_verified']) ? 'verified' : false;
		$meta['vs_winery_info_phone'] = $_POST['vs_winery_info_phone'];
		$meta['vs_winery_info_account_rec'] = $_POST['vs_winery_info_account_rec'];
		$meta['vs_winery_address_1'] = $_POST['vs_winery_address_1'];
		$meta['vs_winery_address_2'] = $_POST['vs_winery_address_2'];
		$meta['vs_winery_city'] = $_POST['vs_winery_city'];
		$meta['vs_winery_state'] = $_POST['vs_winery_state'];
		$meta['vs_winery_zip'] = $_POST['vs_winery_zip'];
	break;
	}

	foreach($meta as $key => $value)
	{
		update_post_meta($post_id, $key, $value);
	}
}

// Remove all those silly extra contact methods, jeez
add_filter('user_contactmethods', function($user_contact)
{
	unset($user_contact['aim']);
	unset($user_contact['yim']);
	unset($user_contact['jabber']);
	return $user_contact;
});

// Each winery must have at least one user
// TODO: add winery main user picker
add_action('edit_user_profile', 'vs_extra_profile_fields');
function vs_extra_profile_fields($user)
{
	// If this is a seller profile, the admins can associate a winery
	if(current_user_can('edit_user', $user->ID) && user_can($user->ID, 'seller'))
	{
		$all_wineries = get_posts('posts_per_page=-1&post_type=vs_store');
		$current_winery = get_user_meta($user->ID, 'attached_winery', true);
		?>
		<h3><?php _e('Seller Winery Info', 'vinsource'); ?></h3>
		<table class='form-table'>
			<tr>
				<th><label for='seller_winery'><?php _e('Winery', 'vinsource'); ?></label></th>
				<td>
					<select name='seller_winery' id='seller_winery'>
						<option>Please select</option>"; ?>
						<?php foreach($all_wineries as $winery)
						{
							if($current_winery == $winery->ID)
								echo "<option value='$winery->ID' selected>$winery->post_title</option>";
					
							else
								echo "<option value='$winery->ID'>$winery->post_title</option>";
						}
						?>
					</select><!-- #seller_winery -->
					<span class='description'>Which winery should this user be able to accept bids for?</span>
				</td>
			</tr>
		</table>
		<?php
	}

	// If this is a buyer field, the admins can associate an address
	elseif(current_user_can('edit_user', $user->ID) && user_can($user->ID, 'buyer'))
	{
		// Get all the user meta
		?>
		<h3><?php _e('Buyer Info', 'vinsource'); ?></h3>
		<table class='form-table'>
			<tr>
				<th><label for='buyer_title'>Title</label></th>
				<td>
					<input type='text' name='buyer_title' id='buyer_title' value='<?php echo get_user_meta($user->ID, 'buyer_title', true); ?>' />
				</td>
			</tr>
			<tr>
				<th><label for='buyer_phone'>Phone</label></th>
				<td>
					<input type='text' name='buyer_phone' id='buyer_phone' value='<?php echo get_user_meta($user->ID, 'buyer_phone', true); ?>' />
				</td>
			</tr>
			<tr>
				<th><label for='buyer_establishment'>Establishment Name</label></th>
				<td>
					<input type='text' name='buyer_establishment' id='buyer_establishment' value='<?php echo get_user_meta($user->ID, 'buyer_establishment', true); ?>' />
				</td>
			</tr>
			<tr>
				<th><label for='buyer_address_1'>Address</label></th>
				<td>
					<input type='text' name='buyer_address_1' id='buyer_address_1' value='<?php echo get_user_meta($user->ID, 'buyer_address_1', true); ?>' />
				</td>
			</tr>
			<tr>
				<th><label for='buyer_address_2'>Address 2</label></th>
				<td>
					<input type='text' name='buyer_address_2' id='buyer_address_2' value='<?php echo get_user_meta($user->ID, 'buyer_address_2', true); ?>' />
				</td>
			</tr>
			<tr>
				<th><label for='buyer_city'>City</label></th>
				<td>
					<input type='text' name='buyer_city' id='buyer_city' value='<?php echo get_user_meta($user->ID, 'buyer_city', true); ?>' />
				</td>
			</tr>
			<tr>
				<th><label for='buyer_state'>State</label></th>
				<td>
					<input type='text' name='buyer_state' id='buyer_state' value='<?php echo get_user_meta($user->ID, 'buyer_state', true); ?>' />
				</td>
			</tr>
			<tr>
				<th><label for='buyer_zip'>Zip</label></th>
				<td>
					<input type='text' name='buyer_zip' id='buyer_zip' value='<?php echo get_user_meta($user->ID, 'buyer_zip', true); ?>' />
				</td>
			</tr>
			<tr>
				<th><label for='buyer_liquor_license'>Liquor License</label></th>
				<td>
					<input type='text' name='buyer_liquor_license' id='buyer_liquor_license' value='<?php echo get_user_meta($user->ID, 'buyer_liquor_license', true); ?>' />
				</td>
			</tr>
			<tr>
				<th><label for='buyer_details'>Additional Details</label></th>
				<td>
					<textarea name='buyer_details' id='buyer_details'><?php echo get_user_meta($user->ID, 'buyer_details', true); ?></textarea>
				</td>
			</tr>
		</table><!-- .form-table -->
		<?php
	}
}

// Save the extra info required for each buyer
add_action('edit_user_profile_update', 'vs_save_extra_profile_fields');
function vs_save_extra_profile_fields($user_id)
{
	if(current_user_can('edit_user', $user_id))
	{
		if(user_can($user_id, 'seller'))
		{
			update_user_meta($user_id, 'attached_winery', $_POST['seller_winery']);
		}
		elseif(user_can($user_id, 'buyer'))
		{
			
			update_user_meta($user_id, 'buyer_title', $_POST['buyer_title']);
			update_user_meta($user_id, 'buyer_phone', $_POST['buyer_phone']);
			update_user_meta($user_id, 'buyer_establishment', $_POST['buyer_establishment']);
			update_user_meta($user_id, 'buyer_address_1', $_POST['buyer_address_1']);
			update_user_meta($user_id, 'buyer_address_2', $_POST['buyer_address_2']);
			update_user_meta($user_id, 'buyer_city', $_POST['buyer_city']);
			update_user_meta($user_id, 'buyer_state', $_POST['buyer_state']);
			update_user_meta($user_id, 'buyer_zip', $_POST['buyer_zip']);
			update_user_meta($user_id, 'buyer_liquor_license', $_POST['buyer_liquor_license']);
			update_user_meta($user_id, 'buyer_details', $_POST['buyer_details']);
		}
	}
}


// At A Glance Stuff
add_action('dashboard_glance_items', function() {
	if(!post_type_exists('vs_product')) return;

	$num_products = wp_count_posts('vs_product');
	$num_products_display = number_format_i18n($num_products->publish);
	$products_text = _n('Wine', 'Wines', intval($num_products->publish));

	$num_stores = wp_count_posts('vs_store');
	$num_stores_display = number_format_i18n($num_stores->publish);
	$stores_text = _n('Winery', 'Wineries', intval($num_stores->publish));

	$num_transactions = wp_count_posts('vs_transaction');
	$num_closed_transactions_display = number_format_i18n($num_transactions->publish);
	$num_open_transactions_display = number_format_i18n($num_transactions->draft);
	$num_cancelled_transactions_display = number_format_i18n($num_transactions->cancelled);
	$closed_transactions_text = 'Closed ' . _n('Transaction', 'Transactions', intval($num_transactions->publish));
	$open_transactions_text = 'Open ' . _n('Transaction', 'Transactions', intval($num_transactions->draft));
	$cancelled_transactions_text = 'Cancelled ' . _n('Transaction', 'Transactions', intval($num_transactions->cancelled));

	if(current_user_can('edit_posts'))
	{
		?>
		<li class='vs_store-count'>
			<a href='edit.php?post_type=vs_store'><?php echo $num_stores_display . ' ' . $stores_text; ?></a>
		</li>
		<li class='vs_product-count'>
			<a href='edit.php?post_type=vs_product'><?php echo $num_products_display . ' ' . $products_text; ?></a>
		</li>
		<li class='vs_closed_transaction-count'>
			<a href='edit.php?post_type=vs_transaction&post_status=publish'><?php echo $num_closed_transactions_display . ' ' . $closed_transactions_text; ?></a>
		</li>
		<li class='vs_open_transaction-count'>
			<a href='edit.php?post_type=vs_transaction&post_status=draft'><?php echo $num_open_transactions_display . ' ' . $open_transactions_text; ?></a>
		</li>
		<li class='vs_cancelled_transaction-count'>
			<a href='edit.php?post_type=vs_transaction&post_status=cancelled'><?php echo $num_cancelled_transactions_display . ' ' . $cancelled_transactions_text; ?></a>
		</li>

	<?php
	}
});

// Adds all the extra columns in the various post type admin screens
add_filter('manage_vs_product_posts_columns', function($column)
{
	$column['winery'] = 'Winery';
	return $column;
});

add_filter('manage_vs_transaction_posts_columns', function($column)
{
	$column['seller'] = 'Winery';
	$column['buyer'] = 'Buyer';
	$column['total'] = 'Total';
	$column['type'] = 'Type';
	$column['paykey'] = 'PayPal Paykey';
	return $column;
});

add_filter('manage_vs_product_posts_custom_column', function($column_name, $post_id)
{
	switch($column_name)
	{
	case 'winery' :
		$winery_id = get_post_meta($post_id, 'vs_wine_winery', true);
		$winery_edit_link = get_edit_post_link($winery_id);
		$winery = get_post($winery_id);
		echo "<a href='$winery_edit_link' title='Edit $winery->post_title'>$winery->post_title</a>";
	break;
	}
}, 10, 2);

add_filter('manage_vs_transaction_posts_custom_column', function($column_name, $post_id)
{
	switch($column_name)
	{
	case 'paykey' :
		$paykey = get_post_meta($post_id, 'pm_transaction_paypal_paykey', true);
		echo $paykey;
	break;
	case 'seller' :
		$winery_id = get_post_meta($post_id, 'pm_transaction_seller_id', true);
		$winery_edit_link = get_edit_post_link($winery_id);
		$winery = get_post($winery_id);
		echo "<a href='$winery_edit_link' title='Edit $winery->post_title'>$winery->post_title</a>";
	break;
	case 'buyer' :
		$transaction = get_post($post_id);
		$buyer_id = $transaction->post_author;
		$buyer = get_userdata($buyer_id);
		$buyer_edit_link = get_edit_user_link($buyer_id);
		echo "<a href='$buyer_edit_link' title='Edit $buyer->user_login'>$buyer->user_login</a>";
	break;
	case 'total' :
		$amount = get_post_meta($post_id, 'pm_transaction_amount', true);
		echo "$" . number_format($amount, 2);
	break;
	case 'type' :
		$type = get_post_meta($post_id, 'pm_transaction_type', true);
		echo $type;
	break;
	}
}, 10, 2);

add_filter('manage_edit-vs_transaction_sortable_columns', function($columns)
{
	$columns['seller'] = 'seller_id';
	$columns['buyer'] = 'buyer_id';
	$columns['total'] = 'pm_transaction_amount';
	$columns['type'] = 'pm_transaction_type';
	return $columns;
});

add_filter('manage_edit-vs_product_sortable_columns', function($columns)
{
	$columns['winery'] = 'winery';
	return $columns;
});

add_filter('request', function($vars)
{
	if(isset($vars['orderby']))
	{
		switch($vars['orderby'])
		{
		case 'winery' :
			$vars = array_merge($vars, array(
				'meta_key' => 'vs_wine_winery',
				'orderby' => 'meta_value_num'
			));
		break;
		case 'seller_id' :
			$vars = array_merge($vars, array(
				'meta_key' => 'pm_transaction_seller_id',
				'orderby' => 'meta_value_num'
			));
		break;
		case 'buyer_id' :
			$vars = array_merge($vars, array(
				'orderby' => 'author'
			));
		break;
		case 'pm_transaction_amount' :
			$vars = array_merge($vars, array(
				'meta_key' => 'pm_transaction_amount',
				'orderby' => 'meta_value_num'
			));
		break;
		case 'pm_transaction_type' :
			$vars = array_merge($vars, array(
				'meta_key' => 'pm_transaction_type',
				'orderby' => 'meta_value'
			));
		break;

		// This is for front end sorting
		case 'seller' :
			$vars = array_merge($vars, array(
				'meta_key' => 'pm_transaction_seller_id',
				'orderby' => 'meta_value_num'
			));
		break;

		case 'amount' :
			$vars = array_merge($vars, array(
				'meta_key' => 'pm_transaction_amount',
				'orderby' => 'meta_value_num'
			));
		break;

		}
	}

	return $vars;
});

// End the extra columns in the admin screens

// Prints the discount table
add_action('pm_under_split_payment_form_header', function(VSProduct $product)
{
	$prices = get_post_meta($product->ID, 'vs_product_prices', true);
	$discount_percents = get_post_meta($product->ID, 'vs_product_discount_percents', true);
	?>
	<table id='discount_table' class='vs_bulk_prices'>
		<tr class='vs_bulk_price_headers_header'>
			<td rowspan='2'></td>
			<th colspan='5'>Cases</th>
		</tr>
		<tr class='vs_bulk_price_headers'>
			<th>1</th>
			<th>2</th>
			<th>3+</th>
			<th>5+</th>
			<th>10+</th>
		</tr>
		<?php
		if(current_user_can('buyer'))
		{
			?>
			<tr>
				<th scope='row'>Regular</th>

				<?php foreach($prices['regular'] as $case_amount => $price_per_case) {
					echo "<td>$" . number_format($price_per_case, 2) . "</td>";
				}
				?>
			</tr>
			<tr>
				<th scope='row'><i class='discount_icon'></i>DISCOUNT</th>
				<?php foreach($discount_percents['restaurant'] as $discount_percent)
				{
					echo "<td>" . $discount_percent . '%' . "</td>";
				}
				?>
			</tr>
			<tr>
				<th scope='row'>BTG</th>
				<?php foreach($prices['btg'] as $case_amount => $price_per_case) {
					echo "<td>$" . number_format($price_per_case, 2) . "</td>";
				}
				?>
			</tr>
			<tr>
				<th scope='row'><i class='discount_icon'></i>DISCOUNT</th>
				<?php foreach($discount_percents['btg'] as $discount_percent)
				{
					echo "<td>" . $discount_percent . '%' . "</td>";
				}
				?>
			</tr>
		<?php
		}
		elseif(current_user_can('events'))
		{
			?>
			<tr>
				<th scope='row'>Per Case</th>

				<?php foreach($prices['events'] as $case_amount => $price_per_case) {
					echo "<td>$" . number_format($price_per_case, 2) . "</td>";
				}
				?>
			</tr>
			<tr>
				<th scope='row'>Per Bottle</th>

				<?php foreach($prices['event_bottle'] as $case_amount => $price_per_case) {
					echo "<td>$" . number_format($price_per_case, 2) . "</td>";
				}
				?>
			</tr>
			<tr>
				<th scope='row'><i class='discount_icon'></i>DISCOUNT</th>
				<?php foreach($discount_percents['event'] as $discount_percent)
				{
					echo "<td>" . $discount_percent . '%' . "</td>";
				}
				?>
			</tr>
			<?php
		}
		elseif(current_user_can('retail'))
		{
			?>
			<tr>
				<th scope='row'>Per Case</th>

				<?php foreach($prices['retail'] as $case_amount => $price_per_case) {
					echo "<td>$" . number_format($price_per_case, 2) . "</td>";
				}
				?>
			</tr>
			<tr>
				<th scope='row'><i class='discount_icon'></i>DISCOUNT</th>
				<?php foreach($discount_percents['retail'] as $discount_percent)
				{
					echo "<td>" . $discount_percent . '%' . "</td>";
				}
				?>
			</tr>
			<?php
		}
		elseif(current_user_can('seller') OR current_user_can('editor') OR current_user_can('administrator'))
		{
			?>
			<tr>
				<th scope='row'>Restaurant Regular</th>

				<?php foreach($prices['regular'] as $case_amount => $price_per_case) {
					echo "<td>$" . number_format($price_per_case, 2) . "</td>";
				}
				?>
			</tr>
			<tr>
				<th scope='row'><i class='discount_icon'></i>Restaurant DISCOUNT</th>
				<?php foreach($discount_percents['restaurant'] as $discount_percent)
				{
					echo "<td>" . $discount_percent . '%' . "</td>";
				}
				?>
			</tr>
			<tr>
				<th scope='row'>BTG</th>
				<?php foreach($prices['btg'] as $case_amount => $price_per_case) {
					echo "<td>$" . number_format($price_per_case, 2) . "</td>";
				}
				?>
			</tr>
			<tr>
				<th scope='row'><i class='discount_icon'></i>BTG DISCOUNT</th>
				<?php foreach($discount_percents['btg'] as $discount_percent)
				{
					echo "<td>" . $discount_percent . '%' . "</td>";
				}
				?>
			</tr>
			<tr>
				<th scope='row'>Events Per Case</th>

				<?php foreach($prices['event'] as $case_amount => $price_per_case) {
					echo "<td>$" . number_format($price_per_case, 2) . "</td>";
				}
				?>
			</tr>
			<tr>
				<th scope='row'>Events Per Bottle</th>

				<?php foreach($prices['event_bottle'] as $case_amount => $price_per_case) {
					echo "<td>$" . number_format($price_per_case, 2) . "</td>";
				}
				?>
			</tr>
			<tr>
				<th scope='row'><i class='discount_icon'></i>Events DISCOUNT</th>
				<?php foreach($discount_percents['event'] as $discount_percent)
				{
					echo "<td>" . $discount_percent . '%' . "</td>";
				}
				?>
			</tr>
			<tr>
				<th scope='row'>Retail Per Case</th>

				<?php foreach($prices['retail'] as $case_amount => $price_per_case) {
					echo "<td>$" . number_format($price_per_case, 2) . "</td>";
				}
				?>
			</tr>
			<tr>
				<th scope='row'><i class='discount_icon'></i>Retail DISCOUNT</th>
				<?php foreach($discount_percents['retail'] as $discount_percent)
				{
					echo "<td>" . $discount_percent . '%' . "</td>";
				}
				?>
			</tr>
			<?php
		}
		?>
	</table>
	<?php
});

// Makes the browe pages show all the wines.
// TODO: Paginate this
add_action('pre_get_posts', function($query) {
	if(!is_admin() && $query->is_main_query() && is_post_type_archive('vs_transaction'))
	{
		$query->set('posts_per_page', -1);
	}
});
