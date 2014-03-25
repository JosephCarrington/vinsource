<?php get_header(); ?>
<div id='left_wrapper' class='single_blog'>
	<?php if(have_posts()) while(have_posts()) : the_post(); ?>
	<header id='single_blog_header'>
		<h1><?php the_title(); ?></h1>
	</header><!-- #single_blog_header -->
	<aside id='single_blog_date'><?php the_date(); ?></aside>
	<?php the_content(); ?>

	<?php endwhile; ?>
</div><!-- #left_wrapper -->
<ul id='single_blog_sidebar'>
	<?php dynamic_sidebar('single_blog'); ?>
</ul><!-- #single_blog_sidebar -->
<?php get_footer(); ?>
