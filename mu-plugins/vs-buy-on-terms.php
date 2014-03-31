<?php
/**
* Plugin Name: Vinsource : Buy on Terms
* Description: Allows a buyer to pay for a prodcut up to 29 days after it is ordered.
* Version: 0.1
*/
add_action('peer_marketplace_after_split_payment_form', function($product)
{
	$store = new VSStore(get_post($product->seller_id));
	$terms_users = vs_get_terms_users($store);

	if(in_array(get_current_user_id(), $terms_users))
	{
		// The user may buy on terms, print a link to allow them to do so
		$terms_nonce = wp_create_nonce('buy_on_terms');
		?>
		<a href='<?php echo add_query_arg(array(
			'action' => 'buy_on_terms',
			'nonce' => $terms_nonce
		)); ?>' title='Buy on terms'>Buy on terms</a>
		<?php
	}
});

/** Determines is a user has been selected by the winery to be able to buy on terms **/

/** Returns an array of user ids that can buy on terms from this store **/
function vs_get_terms_users(VSStore $store)
{
	$terms_users = get_post_meta($store->ID, 'vs_store_terms_users', true);
	if(!is_array($terms_users)) return false;
	return $terms_users;
}

/** Returns an array of user ids that have requested to buy from this seller on terms **/
function vs_get_terms_user_requests(VSStore $store)
{
	$terms_user_requests = get_post_meta($store->ID, 'vs_store_terms_user_requests', true);
	if(!is_array($terms_user_requests)) return false;
	return $terms_users;
}

/** Add a user_id to the terms_users and possible remove it from the terms_user_requests **/
function add_terms_user(VSStore $store, WP_User $user)
{
	$terms_users = get_post_meta($store->ID, 'vs_store_terms_users', true);
	$terms_users = array_unique($terms_users);
	$terms_users[] = $user->ID;
	update_post_meta($store->ID, 'vs_store_terms_users',$terms_users);

	$terms_user_requests = get_post_meta($store->ID, 'vs_store_terms_user_requests', true);
	if(is_array($terms_user_requests))
	{
		$terms_user_requests = array_unique($terms_user_requests);
		if($array_key = array_search($user->ID, $terms_user_requests))
		{
			unset($terms_user_requests[$array_key]);
			$terms_user_requests = array_values($terms_user_requests);

			update_post_meta($store->ID, 'vs_store_terms_user_requests', $terms_user_requests);
		}
	}
}

function remove_terms_user(VSStore $store, WP_User $user)
{
	$terms_users = get_post_meta($store->ID, 'vs_store_terms_users', true);
	if(is_array($terms_users))
	{
		$terms_users = array_unique($terms_users);
		if($array_key = array_search($user->ID, $terms_users))
		{
			unset($terms_users[$array_key]);
			$terms_users = array_values($terms_users);

			update_post_meta($store->ID, 'vs_store_terms_users', $terms_users);
		}
	}
}

function remove_terms_user_request(VSStore $store, WP_User $user)
{
	$terms_user_requests = get_post_meta($store->ID, 'vs_store_terms_user_requests', true);
	if(is_array($terms_user_requests))
	{
		$terms_user_requests = array_unique($terms_user_requests);
		if($array_key = array_search($user->ID, $terms_user_requests))
		{
			unset($terms_user_requests[$array_key]);
			$terms_user_requests = array_values($terms_user_requests);

			update_post_meta($store->ID, 'vs_store_terms_user_requests', $terms_user_requests);
		}
	}
}

/** Adds the necesary backend admin stuff **/
add_action('add_meta_boxes', function() {
	add_meta_box('vs_terms_users', __('Buying on Terms', 'vinsource'), 'vs_terms_metabox', 'vs_store');
});

function vs_terms_metabox($post)
{
	wp_nonce_field('save_terms_buyers', 'save_terms_buyers_nonce');

	$current_terms_users = get_post_meta($post->ID, 'vs_store_terms_users', true);

	$all_users = get_users('orderby=nicename');
	?>
	<label for='vs_store_terms_users'>Which users can buy from this winery on terms?</label>
	<select id='vs_store_terms_users' name='vs_store_terms_users[]' multiple='multiple' class='widefat' size='20'>
		<?php
		foreach($all_users as $user)
		{
			if(in_array($user->ID, $current_terms_users))
			{
				?>
					<option selected='selected' value='<?php echo $user->ID; ?>'><?php echo $user->user_login; ?></option>
				<?php
			}
			else
			{
				?>
					<option value='<?php echo $user->ID; ?>'><?php echo $user->user_login; ?></option>
				<?php
			}

		}?>
	</select><!-- #vs_store_terms_buyers -->
	<?php
}

add_action('save_post', function($post_id)
{
	if(!isset($_POST['save_terms_buyers_nonce']) OR !wp_verify_nonce($_POST['save_terms_buyers_nonce'], 'save_terms_buyers')) return $post_id;

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return $post_id;

	if(!current_user_can('edit_post', $post_id)) return $post_id;
	update_post_meta($post_id, 'vs_store_terms_users', $_POST['vs_store_terms_users']);

});
