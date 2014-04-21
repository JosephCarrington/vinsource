<!DOCTYPE html>
<!--[if IE 6]>
<html id="ie6" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 7]>
<html id="ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html id="ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 6) | !(IE 7) | !(IE 8)  ]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<title><?php
	/*
	 * Print the <title> tag based on what is being viewed.
	 */
	global $page, $paged;

	wp_title( '|', true, 'right' );

	// Add the blog name.
	bloginfo( 'name' );

	// Add the blog description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		echo " | $site_description";

	// Add a page number if necessary:
	if ( $paged >= 2 || $page >= 2 )
		echo ' | ' . sprintf( __( 'Page %s', 'twentyeleven' ), max( $paged, $page ) );

	?></title>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo( 'stylesheet_url' ); ?>" />
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
<link rel="shortcut icon" href="<?php echo get_stylesheet_directory_uri(); ?>/favicon.ico" />
<!--[if IE]><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<div id='site_header_wrapper'>
	<header id='site_header'>
	<?php if(is_home())
	{
		?>
		<a id='home_login_link' href='<?php echo wp_login_url(); ?>' title='Already registered? Log in.'>Already registered? Log in here.</a>
		<?php

	}
	else
	{
		?>
		<a id='logo' href='<?php bloginfo('url'); ?>' title='Vinsource home'><img src='<?php bloginfo('stylesheet_directory'); ?>/images/logo.png' alt='Logo' /></a>
		<div id='menu_wrapper'>
			<?php 
			wp_nav_menu(array('theme_location' => 'header'));
			if(current_user_can('add_bid'))
			{
				get_template_part('winenav');
			}
			// For wineries
			elseif(current_user_can('seller'))
			{
				get_template_part('winenav', 'winery');
			}
			if(is_user_logged_in())
				get_template_part('accountnav');
			?>
		</div><!-- #menu_wrapper -->
		<?php
	}
	?>
	</header><!-- #site_header -->
</div><!-- #site_header_wrapper -->
<?php
	if(is_post_type_archive('wine') || is_tax()) get_template_part('browse_banner');
	else if(is_singular(array('vs_product', 'bid')) || is_post_type_archive('bid') || is_archive() || is_singular('post')) get_template_part('wine_banner');
	else get_template_part('banner');
?>
<div id='content'>
