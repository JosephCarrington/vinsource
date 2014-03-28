<?php
/**
* Plugin Name: Vinsource : Buy on Terms
* Description: Allows a buyer to pay for a prodcut up to 29 days after it is ordered.
* Version: 0.1
*/
add_action('peer_marketplace_after_split_payment_form', function($product)
{
	if(current_user_can('buy_on_terms', $product->ID))
	{

	}
}
