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

			update_post_meta($store->ID, 'vs_store_terms_user_requests',$terms_user_requests);
		}
	}
}
