<?php 
if(isset($_POST['bid_step']) && $_POST['bid_step'] != '' && isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], "change_bid_" . get_queried_object_id()))
{
	$bid_id = get_queried_object_id();
	$current_step = get_post_meta($bid_id, 'status_code', true);
	if($_POST['bid_step'] < $current_step)
	{
		wp_die('You have already changed the status of this bid. If you have any questions or issues, please contact <a href="mailto:info@vinsourceonline.com">info@vinsourceonline.com</a>');
	}

	switch($_POST['bid_step'])
	{
	case 1:
		if(isset($_POST['winery_choice']) && $_POST['winery_choice'] != '')
		{
			switch($_POST['winery_choice'])
			{
			case 'Accept':
				update_post_meta($bid_id, 'status_code', 2);
			break;
			case 'Decline':
				update_post_meta($bid_id, 'status_code', 7);
			break;
			}
		}
	break;
	case 2:
		update_post_meta($bid_id, 'status_code', 3);
	break;
	case 3:
		update_post_meta($bid_id, 'status_code', 4);
	break;
	case 4:
		update_post_meta($bid_id, 'status_code', 5);
	break;
	case 5:
		update_post_meta($bid_id, 'status_code', 6);
	break;
	}
}
?>
<?php get_header(); ?>
<?php if(have_posts()) while(have_posts()) : the_post();
	$wine_id = get_post_meta($post->ID, 'wine_id', true);
	$wine = get_post($wine_id);

	$bid_status_code = get_post_meta($post->ID, 'status_code', true);

	$price_at_time = get_post_meta($post->ID, 'wine_price', true);
	$case_amount = get_post_meta($post->ID, 'case_amount', true);
	$percentage = get_post_meta($post->ID, 'percentage', true);
	$wine_per_case = 12;
	$total_bottles = $case_amount * $wine_per_case;
	$full_price = $price_at_time * $total_bottles;
	$percent_of_price = $full_price * $percentage;
	$display_price = money_format('%.2n', $percent_of_price);

	?>
	<section id='bid_info'>
		<header>
			<h1><em>Transaction:</em> <?php echo the_ID(); ?></h1>
		</header>
		<ul id='bid_data'>
			<li id='wine_data_title'><?php echo get_post_meta($wine->ID, 'wine_year', true); ?> <?php echo $wine->post_title; ?></li>
			<li id='total_feedback'>
				<ul id='bid_cost'>
					<li id='total'>Total offer: <?php echo $display_price; ?></li>
					<li id='total_by_case'>
						<ul>
							<li id='total_cases'><?php echo $case_amount; ?></li>
							<li> cases at </li>
							<li id='total_price_per_case'><?php echo money_format('%.2n', ($price_at_time * $wine_per_case) * $percentage); ?></li>
							<li> per case</li>
						</ul>
					</li><!-- #total_by_case -->
					<li id='total_by_bottle'>
						<ul>
							<li id='total_bottles'><?php echo $total_bottles; ?></li>
							<li> bottles at </li>
							<li id='total_price_per_bottle'><?php echo money_format('%.2n', $price_at_time * $percentage); ?></li>
							<li> per bottle</li>
						</ul>
					</li><!-- #total_by_bottle -->
				</ul>
			</li><!-- #total_feedback -->
			<li id='bid_status'>
				<ul id='bid_status_header'>
					<li id='bid_status_label'>Status</li>
					<li id='bid_last_updated'>Last updated: <?php echo date('M d o', strtotime($post->post_modified)); ?></li>
				</ul><!-- #bid_status_header -->
				<?php echo vs_get_bid_status_info($post, true); ?>
			</li><!-- #bid_status -->
		</ul><!-- #bid_data -->
	</section><!-- #bid_info -->
	<?php print_wine_data($wine); ?>
	<div id='bid_wine_bottle_wrapper'>
		<?php if(has_post_Thumbnail($wine->ID)) echo get_the_post_thumbnail($wine->ID); ?>
	</div>
<?php endwhile; ?>
<?php get_footer(); ?>
