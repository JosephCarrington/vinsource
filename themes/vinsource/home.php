<?php get_header(); ?>
<img src='<?php echo get_stylesheet_directory_uri(); ?>/images/vs_logo_front.png' id='home_vinsource_logo' title='Vinsource : Direct-To-Trade' />
<ul id='front_page_under_sidebar'>
	<?php dynamic_sidebar('front_page_under_logo'); ?>
</ul>
<ul id='create_accounts'>
	<li>
		<select id='account_type'>
			<option value='0'>Account Type</option>
			<option value='winery'>Winery</option>
			<option value='retail'>Restaurant/Retailer</option>
			<option value='events'>Events</option>
		</select>
	</li>
	<li>
		<a href='#' id='create_account'>Create Account</a>
	</li>
</ul><!-- #create_accounts -->
<ul id='registration_forms'>
	<?php dynamic_sidebar('registration_forms'); ?>
</ul><!-- #registration_forms -->
<ul id='front_page_under_account_creation'>
	<?php dynamic_sidebar('front_page_under_account_creation'); ?>
</ul><!-- #front_page_under_account_creation -->
<div class='hidden' id='paypal_link_info'>
	<h2>Event Registration</h2>
	<p>A one-time fee of $50 gets you access to our fine selection of wines, wine advice from our sommeliers, and prices way below retail.</p>
	<p><a id='paypal_event_link'>Sounds great, sign me up!</a></p>
	<p><a id='cancel_event_link' href='<?php bloginfo('url'); ?>'>Cancel</a></p>
</div><!-- #paypal_link_info -->
<?php get_footer(); ?>
