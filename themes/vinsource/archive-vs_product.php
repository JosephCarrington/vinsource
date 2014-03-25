<?php get_header(); ?>
<div id='winery_browser_wrapper'>
	<a href='' id='previous_wineries'>
		<img alt='previous' src='<?php bloginfo('template_directory'); ?>/images/browse_left_arrow.png' />
	</a>
	<a href='' id='next_wineries'>
		<img alt='next' src='<?php bloginfo('template_directory'); ?>/images/browse_right_arrow.png' />
	</a>
	<ul id='winery_browser'>
	<?php
	$winery_ids = array();
	foreach($posts as $post)
	{
		$winery_ids[] = get_post_meta($post->ID, 'vs_wine_winery', true);
	}
	$winery_ids = array_unique($winery_ids);

	$wineries = new WP_Query(array(
		'post__in' => $winery_ids,
		'post_type' => 'vs_store',
		'orderby' => 'menu_order',
		'order' => 'ASC',
		'posts_per_page' => -1
	));


	$i = 1;
	if($wineries->have_posts()) while($wineries->have_posts()) : $wineries->the_post();

		if($i == 1 || ($i -1 ) % 5 == 0)
		{
			echo "<li class='winery_group'><ul>";
		}
		?>
		
		<li class='winery'>
			<a class='winery_link' href='#winery_<?php echo $post->ID; ?>' title='<?php the_title_attribute();?>'>
				<?php
				if(has_post_thumbnail()) the_post_thumbnail('winery_logo', array('class' => 'winery_logo'));	
				?>
			</a><!-- .winery_link -->
		</li>
		
		<?php
		if($i % 5 == 0 || $i == $wp_query->post_count)
		{
			echo "</ul></li><!-- .winery_group -->";
		}
		$i ++;
	endwhile;
	?>
	</ul><!-- #winery_browser -->
</div><!-- #winery_browser_wrapper -->
<div id='wine_browser_wrapper'>
	<a href='' id='previous_wines'>
		<img alt='previous' src='<?php bloginfo('template_directory'); ?>/images/browse_left_arrow.png' />
	</a>
	<a href='' id='next_wines'>
		<img alt='next' src='<?php bloginfo('template_directory'); ?>/images/browse_right_arrow.png' />
	</a>
	<ul id='wine_browser'>
	</ul><!-- #wine_browser -->
</div><!-- #wine_browser_Wrapper -->
<div id='wine_info'>
</div>
<?php get_footer(); ?>
