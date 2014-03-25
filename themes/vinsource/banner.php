<div id='banner_wrapper'>
	<?php
	// We need to get the page id before the loop.
	global $wp_query;
	$page_id = $wp_query->get_queried_object_id();

	if(has_post_thumbnail($page_id)) echo get_the_post_thumbnail($page_id, 'full');
	?>
</div><!-- #banner_wrapper -->
