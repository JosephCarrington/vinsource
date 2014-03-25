<?php
/*
Plugin Name: Send Different Registration Emails to DIfferent Roles
Description: Allows you to send different emails to different roles upon their registration, mostly useful when the admin is adding new users by hand
Version: 0.1
Author: Joseph Carrington
Author URI: http://josephcarrington.com
License: GPL2
*/
function wp_new_user_notification($user_id, $plaintext_pass = '') {
	$user = get_userdata( $user_id );

	$user_login = $user->user_login;
	$user_email = $user->user_email;

	// The blogname option is escaped with esc_html on the way into the database in sanitize_option
	// we want to reverse this for the plain text arena of emails.
	$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

	$message  = sprintf(__('New user registration on your site %s:'), $blogname) . "\r\n\r\n";
	$message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
	$message .= sprintf(__('E-mail: %s'), $user_email) . "\r\n";

	@wp_mail(get_option('admin_email'), sprintf(__('[%s] New User Registration'), $blogname), $message);

	if ( empty($plaintext_pass) )
		return;

	if(user_can($user_id, 'seller'))
	{
		$subject = "Welcome to Vinsource Online";
		$message = "A new winery account has been setup on your behalf in the Vinsource system.\r\n";
		$message .= sprintf(__('Username: %s'), $user_login) . "\r\n";
		$message .= sprintf(__('Password: %s'), $plaintext_pass) . "\r\n";
		$message .= "Please login below to view and manage your account.\r\n";
		$message .= bloginfo('url') . "\r\n\r\n";
		
	}

	elseif(user_can($user_id, 'subscriber'))
	{
		$subject = sprintf(__('[%s] Your username and password'), $blogname);
		$message = "Hello, you've recently asked to become a member of Vinsource.\r\n\r\n";
		$message .= "We just need to validate your liquor license and then you'll be all set up to browse our wines. You can expect another email from us in a few days once we've validated your license number.\r\n\r\n";
		$message .="Until then, please keep this email for your records. Thanks!\r\n\r\n";
		$message .= sprintf(__('Username: %s'), $user_login) . "\r\n";
		$message .= sprintf(__('Password: %s'), $plaintext_pass) . "\r\n\r\n";
		$message .= bloginfo('url') . "\r\n\r\n";

	}
	else
	{
		wp_mail('joseph.carrington@gmail.com', 'Registration Error', "User was not of seller or subscriber class");
		return;
	}

	$message .= "Sincerely,\r\n";
	$message .= "The Vinsource Team";

	wp_mail($user_email, $subject, $message);

}
