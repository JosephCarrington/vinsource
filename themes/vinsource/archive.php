<?php get_header(); ?>
<div id='left_wrapper'>
	<?php if(have_posts()) while(have_posts()) : the_post(); ?>
		<section class='blog_excerpt'>
			<header class='blog_exerpt_header'>
				<h1 class='blog_excerpt_title'><?php the_title(); ?></h1>
			</header><!-- .blog_excerpt_header -->
			<aside class='blog_excerpt_date'><?php the_date(); ?></aside>
			<?php the_excerpt(); ?>
		</section><!-- .blog_excerpt -->
	<?php endwhile; ?>
</div><!-- #left_wrapper -->
<ul id='blog_archive_sidebar'>
	<?php dynamic_sidebar('blog_archive_sidebar'); ?>
</ul><!-- #blog_archive_sidebar -->
<?php get_footer(); ?>
