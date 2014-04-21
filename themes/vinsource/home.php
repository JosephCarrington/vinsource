<?php get_header(); ?>
<img src='<?php echo get_stylesheet_directory_uri(); ?>/images/vs_logo_front.png' id='home_vinsource_logo' title='Vinsource : Direct-To-Trade' />
<ul id='front_page_under_sidebar'>
	<?php dynamic_sidebar('front_page_under_logo'); ?>
</ul>
<ul id='create_accounts'>
	<li><a id='create_winery_account' href='#gform_widget-2' title='Create a WINERY account'>Create a WINERY Account</a></li>
	<li><a id='create_buyer_account' href='#gform_widget-3' title='Create a BUYER account'>Create a BUYER Account</a></li>
</ul><!-- #create_accounts -->
<ul id='front_page_under_account_creation'>
	<?php dynamic_sidebar('front_page_under_account_creation'); ?>
</ul><!-- #front_page_under_account_creation -->
<ul id='registration_forms'>
	<?php dynamic_sidebar('registration_forms'); ?>
</ul><!-- #registration_forms -->
<div id='site_description_wrapper'>
	<div id='site_description'>
		<?php bloginfo('description'); ?>
	</div><!-- #site_description -->
</div><!-- #site_description_wrapper -->
<?php get_footer(); ?>
