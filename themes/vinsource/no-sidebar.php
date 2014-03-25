<?php
/*
Template Name: No Sidebar
*/
?>
<?php get_header(); ?>
<div>
	<?php if(have_posts()) while(have_posts()) : the_post(); ?>
		<?php 
		$title = get_the_title();
		$title_parts = explode(' ', $title);
		$title_string = "<em>";
		for($w = 0; $w < count($title_parts); $w ++)
		{
			if($w == 0)
			{
				$title_string .= $title_parts[$w] . "</em> ";
			}
			elseif($w == count($title_parts) - 1)
			{
				$title_string .= $title_parts[$w];
			}
			else
			{
				$title_string .= $title_parts[$w] . " ";
			}
		}
		?>
			
		<header>
			<h1><?php echo $title_string; ?></h1>
		</header>
		<div id='page_content'>
			<?php the_content(); ?>
		</div><!-- #page_content -->
	<?php endwhile; ?>
</div>
<?php get_footer(); ?>
