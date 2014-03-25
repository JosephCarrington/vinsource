<?php get_header(); ?>
<div id='left_wrapper'>
	<article id='front_news'>
		<header>
			<h1><em>Vinsource</em> News</h1>
		</header>
		<?php if(have_posts()) while(have_posts()) : the_post(); ?>
			<aside><?php the_date('l, F j, o G:i'); ?></aside>
			<div class='news_content'>
				<?php the_content(); ?>
				<a class='read_more' href='<?php echo get_category_link(get_cat_ID('blog')); ?>'><img src='<?php bloginfo('stylesheet_directory'); ?>/images/read_more.png' alt='read_more' /></a>
			</div>
		<?php endwhile; ?>
			
	</article><!-- #front_news -->
	<article id='how_it_works'>
		<header>
			<h1><em>How</em> It Works</h1>
		</header>
		<div id='how_it_works_content'>
			<?php dynamic_sidebar('how_it_works'); ?>
		</div><!-- #how_it_works_content -->
	</article><!-- #how_it_works -->
</div><!-- #left_wrapper -->
<div id='log_me_in'>
	<?php dynamic_sidebar('front_sidebar'); ?>
</div><!-- #log_me_in -->
	
<?php get_footer(); ?>
