<?php get_header(); ?>
<ul id='winery_browser'>
<?php
if(have_posts()) while(have_posts()) : the_post();
	?>
	<li class='winery'>
		<a class='winery_link' href='#winery_<?php echo $post->ID; ?>' title='<?php the_title_attribute();?>'>
			<?php
			if(has_post_thumbnail()) the_post_thumbnail('winery_logo', array('class' => 'winery_logo'));	
			?>
		</a><!-- .winery_link -->
	</li><!-- .winery -->
	<?php
endwhile;
?>
</ul><!-- #winery_browser -->
<ul id='wine_browser'>
</ul><!-- #wine_browser -->
<div id='wine_info'>
</div>
<?php get_footer(); ?>
