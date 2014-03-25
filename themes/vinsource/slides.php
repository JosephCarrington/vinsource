<div id='slides_wrapper'>
<ul id='slides'>

<?php
	$slides = get_posts('post_type=slide&numberposts=-1');
	foreach($slides as $slide)
	{
		?>
		<li class='slide'>
			<ul>
				<li class='slide_bg' style="background: white url('<?php echo get_post_meta($slide->ID, 'bg_url', true); ?>') top center no-repeat"></li>
				<li class='slide_image_wrapper'>
					<div class='slide_image'>
						<?php if(has_post_thumbnail($slide->ID)) echo get_the_post_thumbnail($slide->ID, 'full'); ?>
					</div><!-- .slide_image -->
				</li><!-- .slide_image_wrapper -->
				<li class='headline_container'>
					<div class='headline_subcontainer'>
						<h2 class='slide_headline'><?php echo $slide->post_title; ?></h2>
					</div><!-- .h2_container -->
				</li><!-- ,headline_subcontainer -->
				<li class='blurb_container'>
					<div class='blurb'>
						<?php echo $slide->post_content; ?>
					</div><!-- .blurb -->
				</li><!-- .blurb_container -->
			</ul>
		<?php
	}
?>
</ul><!-- #slides -->
</div><!-- #slides_Wrapper -->
