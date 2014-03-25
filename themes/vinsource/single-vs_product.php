<?php get_header(); ?>
<?php if(have_posts()) while(have_posts()) : the_post(); ?>
	<div id='left_wrapper'>
		<header>
			<h1><em>Purchase</em> Direct</em></h1>
		</header>
		<h2 class='wine_title'>
			<?php echo get_post_meta(get_the_id(), 'vs_wine_info_wine_year', true) . ' ' . get_the_title(); ?>
		</h2><!-- .wine_title -->
		<?php do_action('peer_marketplace_before_product_info', $post); ?>
		<?php print_wine_data($post); ?>
		<?php do_action('peer_marketplace_after_product_info', $post); ?>
	</div><!-- #left_wrapper -->
	<?php if(has_post_thumbnail()) the_post_thumbnail('wine_bottle'); ?>
<?php endwhile; ?>
<?php get_footer(); ?>
