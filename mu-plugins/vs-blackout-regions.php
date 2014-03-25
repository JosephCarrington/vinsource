<?php
/**
* Plugin Name: Vinsource : Blackout Regions
* Description: Allows individual products to be hidden from users based upon a tag set per wine, and cross-referenced against a tag set for a user
* Version: 1.0
* Author: Joseph Carrington
* Author URI: http://josephcarrington.com
* License: GPL2

Copyright 2013  Joseph Carrington  (email : joseph.carrington@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

add_action('init', function() {
	register_taxonomy('wine_blackout_region', 'vs_product', array(
		'label' => 'Blackout Region',
		'labels' => array(
			'name' => 'Blackout Regions',
			'singular_name' => 'Blackout Region',
			'all_items' => 'All Blackout Regions',
			'edit_item' => 'Edit Blackout Region',
			'update_item' => 'Update Blackout Region',
			'add_new_item' => 'Add New Blackout Region',
			'new_item_name' => 'New Blackout Region Name',
			'search_items' => 'Search Blackout Regions',
			'popular_items' => 'Popular Blackout Regions',
			'add_or_remove_items' => 'Add or remove blackout regions',
			'choose_from_most_used' => 'Choose from the most used blackout regions'
		),
		'hierarchical' => false
	));
});

add_action('edit_user_profile', function($user) {
	if(current_user_can('edit_user', $user->ID) && user_can($user->ID, 'buyer'))
	{
		$all_blackout_regions = get_terms(array('wine_blackout_region'), array('hide_empty' => false));
		$current_blackout_region = get_user_meta($user->ID, 'user_blackout_region_id', true);
		?>
		<h3><?php _e('Buyer Blackout Region', 'vinsource'); ?></h3>
		<table class='form-table'>
			<tr>
				<th><label for='buyer_blackout_region'>Buyer's Region</label></th>
				<td>
					<select name='buyer_blackout_region' id='buyer_blackout_region'>
						<option value='NA'>Please select</option>
						<?php
							foreach($all_blackout_regions as $blackout_region)
							{
								if($current_blackout_region == $blackout_region->term_id)
								{
									?>
									<option selected value='<?php echo $blackout_region->term_id; ?>'><?php echo $blackout_region->name; ?></option>
									<?php
								}
								else
								{
									?>
									<option value='<?php echo $blackout_region->term_id; ?>'><?php echo $blackout_region->name; ?></option>
									<?php
								}
							}
						?>
					</select><!-- #buyer_blackout_region -->
				</td>
			</tr>
		</table>
		<?php

	}
});

add_action('edit_user_profile_update', function($user_id) {
	if(current_user_can('edit_user', $user_id))
	{
		if(user_can($user_id, 'buyer'))
		{
			update_user_meta($user_id, 'user_blackout_region_id', $_POST['buyer_blackout_region']);
		}
	}
});

// For sorting the parent wineries
add_action('pre_get_posts', function($query) {
	if(isset($query->query_vars['post_type']) && $query->query_vars['post_type'] == 'vs_product' && current_user_can('buyer'))
	{
		$buyer_region_id = get_user_meta(get_current_user_id(), 'user_blackout_region_id', true);

		if($buyer_region_id)
		{
			$query->query_vars['tax_query'][] = array(
				'taxonomy' => 'wine_blackout_region',
				'field' => 'id',
				'terms' => array($buyer_region_id),
				'operator' => 'NOT IN'
			);
		}
	}
});

