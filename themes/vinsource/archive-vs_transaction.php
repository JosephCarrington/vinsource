<?php get_header(); ?>
<?php
	if(current_user_can('buyer'))
	{
		$establishment_name = get_user_meta(get_current_user_id(), 'buyer_establishment', true);
	}
	else
	{
		$establishment_id = get_user_meta(get_current_user_id(), 'attached_winery', true);
		if(!$establishment_id)
		{
			// User is an admin
			$establishment_name = 'All Transactions';
		}
		else
		{
			$establishment = get_post($establishment_id);
			$establishment_name = $establishment->post_title;
		}
	}
	if($establishment_name == '') $establishment_name = 'Transaction Administration';
	
	if(current_user_can('seller'))
	{
		$all_transactions = new WP_Query(array(
			'post_type' => 'vs_transaction',
			'posts_per_page' => -1,
			'meta_key' => 'pm_transaction_seller_id',
			'meta_value' => $establishment_id
		));
		$grand_total = 0.0;
		foreach($all_transactions->posts as $transaction)
		{
			$grand_total += get_post_meta($transaction->ID, 'pm_transaction_amount', true);
		}
	}
?>
<header>
	<hgroup class='my_account_headers'>
		<h1><?php echo $establishment_name; ?></h1>
		<?php
			if(current_user_can('seller'))
				echo "<h3>Total Transactions: $" . number_format($grand_total, 2) . "</h3>";
		?>

	</hgroup>
</header>
<?php if(have_posts()) { ?>
	<table id='bid_table'>
	<tr id='bid_table_headers'>
		<?php echo vs_sortable_header('No.', array('orderby' => 'ID')); ?>
		<?php echo vs_sortable_header('Date', array('orderby' => 'modified')); ?>
		<?php if(current_user_can('buyer') OR current_user_can('administrator')) 
		{
			echo vs_sortable_header('Winery', array('orderby' => 'seller'));
		}
		?>
		<th>Wine</th>
		<?php echo vs_sortable_header('Volume', array()); ?>
		<?php echo vs_sortable_header('Amount', array('orderby' => 'amount')); ?>
		<?php if(current_user_can('buyer')) echo "<th></th>"; ?>
		<?php if(current_user_can('seller')) echo "<th>Buyer Information</th>"; ?>
	</tr><!-- #bid_table_headers -->
	<?php $reorder_nonce = wp_create_nonce('buy_case_nonce'); ?>
	<?php  while(have_posts()) : the_post(); ?>
		<?php
		$product_ids = get_post_meta(get_the_ID(), 'pm_transaction_product_ids', true);
		$wine = get_post(key($product_ids[0]));
		$quantity_values = array_values($product_ids[0]);
		$quantity = $quantity_values[0];
		if(is_numeric($quantity))
		{
			$quantity .= $quantity > 1 ? ' cases' : ' case';
		}
		$winery_id = get_post_meta(get_the_id(), 'pm_transaction_seller_id', true);
		$winery = get_post($winery_id);

		$transaction_type = get_post_meta(get_the_ID(), 'pm_transaction_type', true);

		// Calculating total
		$price = get_post_meta(get_the_ID(), 'pm_transaction_amount', true);
		$display_price = money_format('%.2n', $price);
		?>
			<tr class='bid_table_row'>
				<td><?php echo get_the_ID(); ?></td>
				<td><?php echo date('M d o', strtotime($post->post_modified)); ?></td>
				<?php if(current_user_can('buyer') OR current_user_can('administrator'))
				{
					?>
					<td><?php echo $winery->post_title; ?></td>
					<?php
				}
				?>
				<td><a href='<?php echo get_permalink($wine->ID); ?>' title='View wine'><?php echo get_post_meta($wine->ID, 'vs_wine_info_wine_year', true) . ' ' . $wine->post_title; ?></a></td>
				<td><?php echo $quantity; ?></td>
				<td><?php echo $display_price; ?></td>
				<td>
					<?php
					if('buy now' == $transaction_type && current_user_can('buyer'))
					{
						?>
						<form class='reorder_form' action='<?php echo add_query_arg('action', 'buy_cases', get_permalink($wine->ID)); ?>' method='post'>
							<input type='hidden' name='buy_case_nonce' value='<?php echo $reorder_nonce; ?>' />
							<input type='hidden' name='case_amount' value='<?php echo $quantity; ?>' />
							<?php if(get_post_meta(get_the_ID(), 'vs_btg_placement', true)) {
							?>
								<input type='hidden' name='btg_placement' value='true' />
							<?php
							}
							?>
							<input type='submit' class='submit' value='Re-order' />
						</form>
						<?php
					}
					elseif(current_user_can('seller'))
					{
						echo vs_format_restaurant_address($post->post_author);
					}
					?>
				</td>
			</tr>
	<?php endwhile; ?>
	</table><!-- #bid_table -->
<?php 
} 
else {
	?>
	<div id='no_bid_notifier'>You have no transactions to display.</div>
	<?php
} ?>
<?php get_footer(); ?>
