<?php
setlocale(LC_MONETARY, 'en_US');

// Init

add_action('wp', 'vs_security_check');
function vs_security_check()
{
	if(!is_front_page() && !is_page() && !is_category('blog') && !is_singular('post'))
	{
		if(!is_user_logged_in())
		{
			auth_redirect();
		}
	}

	// Redirect away from home page if user is logged in
	if(is_user_logged_in() && is_front_page())
	{
		if(current_user_can('seller'))
		{
			wp_redirect(vs_get_dash());
			exit;
		}
		elseif(current_user_can('purchase_products'))
		{
			wp_redirect(get_post_type_archive_link('vs_product'));
			exit;
		}
		elseif(current_user_can('administrator'))
		{
			wp_redirect(get_post_type_archive_link('vs_product'));
			exit;
		}

	}
}

add_action('init', 'vs_init');
function vs_init()
{
	// Redirect away from wp-admin pages
	if(is_admin() && (!current_user_can('administrator') && !current_user_can('editor')))
	{
		if(defined('DOING_AJAX') && DOING_AJAX)
		{
			// WE are doing AJAX, ignore redirection
		}
		else
			wp_redirect(get_bloginfo('url'));
	}

	add_theme_support('post-thumbnails');
	add_image_size('winery_logo', 147, 130);
	add_image_size('wine_logo', 120, 255);

	register_nav_menu('header', 'Site Header');
	register_nav_menu('buyer', 'Logged In Buyers');

	register_sidebar(array(
		'name' => 'Site Footer',
		'id' => 'site_footer',
		'description' => 'On the bottom of every page'
	));

	register_sidebar(array(
		'name' => 'Registration Sidebar',
		'id' => 'registration_sidebar',
		'description' => 'On the left of the registration page'
	));

	register_sidebar(array(
		'name' => 'Single Wine Top',
		'id' => 'single_wine_top',
		'description' => 'Above the name of the wine on the individual wine page'
	));

	register_sidebar(array(
		'name' => 'Blog Archive',
		'id' => 'blog_archive_sidebar',
		'description' => 'On the blog listing, not the individual blog post'
	));

	register_sidebar(array(
		'name' => 'Single Blog Sidebar',
		'id' => 'single_blog',
		'description' => 'Sidebar for single blog posts'
	));

	register_sidebar(array(
		'name' => 'Below Bid Button',
		'id' => 'below_bid_button',
		'description' => 'Typically, at the vey bottom of the content area on the browse page',
	));

	register_sidebar(array(
		'name' => 'Front Page : Under Logo',
		'id' => 'front_page_under_logo',
		'description' => 'On the front page, under the logo, before the account creation buttons'
	));

	register_sidebar(array(
		'name' => 'Front Page : Under Account Creation',
		'id' => 'front_page_under_account_creation',
		'description' => 'On the front page, under the account creation buttons'
	));

	register_sidebar(array(
		'name' => 'Registration Forms',
		'id' => 'registration_forms',
		'description' => 'The forms for new users to register'
	));

	// Redirect users to different pages on login if a redirect has not been set
	add_action('wp_login', 'vs_login', 10, 2);

	// Adding our custom query args
	add_filter('query_vars', 'vs_query_vars');
	function vs_query_vars($vars)
	{
		$vars[] = 'action';

		$vars[] = 'max_cases';
		$vars[] = 'min_cases';
		$vars[] = 'max_price';
		$vars[] = 'min_price';
		$vars[] = 'varietal';

		return $vars;
	}


	// And shortcodes...
	add_shortcode('one_third', 'one_third');
	add_shortcode('two_thirds', 'two_thirds');
	add_shortcode('one_third_last', 'one_third_last');
	add_shortcode('two_thirds_last', 'two_thirds_last');
}

// For reidrecting the user on login
function vs_login($user_login, $user)
{
	if(isset($_REQUEST['redirect_to']) && $_REQUEST['redirect_to'] == get_bloginfo('url')  . '/')
	{
		if(user_can($user->ID, 'seller'))
		{
			wp_redirect(vs_get_dash());
			exit;
		}
		elseif(user_can($user->ID, 'buyer'))
		{
			die('test');
			wp_redirect(get_post_type_archive_link('vs_product'));
			exit;
		}
		else
		{

		}
	}
}

// Add custom VinSource logo instead of WP logo
add_action('login_head', 'vs_login_head');
function vs_login_head()
{
	echo '<style type="text/css">
	h1 a { background-image:url('.get_bloginfo('template_directory').'/images/logo.png) !important; width: auto !important; margin: 0 !important; background-size: auto !important; }

	</style>';
}

add_filter('login_headerurl', 'vs_login_headerurl');
function vs_login_headerurl($url)
{
	return get_bloginfo('url');
}

add_filter('login_headertitle', 'vs_login_headertitle');
function vs_login_headertitle()
{
	return get_bloginfo('name');
}

// Pre_get_posts logic
add_action('pre_get_posts', 'vs_logic');
global $vs_search_criteria;
$vs_search_criteria = false;

function vs_logic($query)
{

	if(!is_admin() && !is_front_page() &&!is_page() && $query->is_main_query())
	{
		// Not backend

		if(!isset($query->query_vars['post_type'])) return;
		$post_type = $query->query_vars['post_type'];
		switch($post_type)
		{
		case 'vs_transaction' :
			// User is not an admin and not an editor
			// We do different things based on whether this is an archive or a single bid
			$current_user = wp_get_current_user();

			if(current_user_can('seller'))
			{
				$winery_id = get_user_meta($current_user->ID, 'attached_winery', true);
				$meta_query = array(
					'key' => 'pm_transaction_seller_id',
					'value' => $winery_id
				);
				$query->set('meta_query', array($meta_query));
			}
			elseif(current_user_can('buyer'))
			{
				$query->set('author', $current_user->ID);
			}
		break;
		case 'vs_product' :
			if(!current_user_can('administrator') && !current_user_can('editor') && !current_user_can('buyer') && !current_user_can('seller') && !current_user_can('retail') && !current_user_can('events'))
			{
				wp_die("You do not have permission to view this page. <a href='" . get_bloginfo('url') . "' title='return home'>Return home.</a>" );
			}
		break;
		}
	}

	if(!is_admin() && $query->is_post_type_archive('vs_product') && $query->is_main_query())
	{
		$query->set('posts_per_page', -1);
		if(isset($query->query_vars['max_price']) OR isset($query->query_vars['min_price']))
		{
		 global $vs_search_criteria;
			if(isset($query->query_vars['max_price']))
			{
				$vs_search_criteria = array('max_price' => $query->query_vars['max_price']);
				$query->set('meta_query', array(
					array(
						'key' => 'vs_wine_info_price',
						'compare' => '<=',
						'value' => $query->query_vars['max_price'],
						'type' => 'numeric'
					)
				));
			}
			if(isset($query->query_vars['min_price']))
			{
				$vs_search_criteria = array('min_price' => $query->query_vars['min_price']);
				$query->set('meta_query', array(
					array(
						'key' => 'vs_wine_info_price',
						'compare' => '>=',
						'value' => $query->query_vars['min_price'],
						'type' => 'numeric'
					)
				));

			}


		}
			
		if(current_user_can('seller'))
		{
			global $current_user;
			get_currentuserinfo();
			if($winery_id = get_user_meta($current_user->ID, 'attached_winery', true))
			{
				$winery = get_post($winery_id);
				if($winery->post_status != 'publish') wp_die('You are not currently able to view any winery products. Please contact support.');
				$query->set('meta_query', array(
					array(
						'key' => 'vs_wine_winery',
						'value' => $winery_id,
						'type' => 'numeric'
					)
				));
			}
		}
	}


	if(current_user_can('seller') && is_single())
	{
		if('vs_product' == $post_type)
		{
			wp_die("You do not have permission to view this page. <a href='" . get_bloginfo('url') . "' title='return home'>Return home.</a>" );
		}
	}

}

// Shortcode functions
function one_third($atts, $content = null)
{
	return "<div class='one_third'>$content</div>";
}

function two_thirds($atts, $content = null)
{
	return "<div class='two_thirds'>$content</div>";
}

function one_third_last($atts, $content = null)
{
	return "<div class='one_third last'>$content</div>";
}

function two_thirds_last($atts, $content = null)
{
	return "<div class='two_thirds last'>$content</div>";
}

// Widgets
class LoggedInTextWidget extends WP_Widget {
	function __construct() {
		$widget_ops = array('classname' => 'widget_text', 'description' => __('Arbitrary text or HTML'));
		$control_ops = array('width' => 400, 'height' => 350);
		parent::__construct('logged_in_text', __('Logged In Text'), $widget_ops, $control_ops);
	}

	function widget( $args, $instance ) {
		if(is_user_logged_in()) {
			extract($args);
			$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
			$text = apply_filters( 'widget_text', empty( $instance['text'] ) ? '' : $instance['text'], $instance );
			echo $before_widget;
			if ( !empty( $title ) ) { echo $before_title . $title . $after_title; } ?>
				<div class="textwidget"><?php echo !empty( $instance['filter'] ) ? wpautop( $text ) : $text; ?></div>
			<?php
			echo $after_widget;
		}
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		if ( current_user_can('unfiltered_html') )
			$instance['text'] =  $new_instance['text'];
		else
			$instance['text'] = stripslashes( wp_filter_post_kses( addslashes($new_instance['text']) ) ); // wp_filter_post_kses() expects slashed
		$instance['filter'] = isset($new_instance['filter']);
		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'text' => '' ) );
		$title = strip_tags($instance['title']);
		$text = esc_textarea($instance['text']);
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>

		<textarea class="widefat" rows="16" cols="20" id="<?php echo $this->get_field_id('text'); ?>" name="<?php echo $this->get_field_name('text'); ?>"><?php echo $text; ?></textarea>

		<p><input id="<?php echo $this->get_field_id('filter'); ?>" name="<?php echo $this->get_field_name('filter'); ?>" type="checkbox" <?php checked(isset($instance['filter']) ? $instance['filter'] : 0); ?> />&nbsp;<label for="<?php echo $this->get_field_id('filter'); ?>"><?php _e('Automatically add paragraphs'); ?></label></p>
<?php
	}
}

class logMeIn extends WP_Widget
{
	function logMeIn()
	{
		parent::__construct(false, 'Log Me In');
	}

	function widget($args, $instance)
	{
		extract($args);
		echo $before_widget;
		?>
		<?php if(!is_user_logged_in())
		{
			?>
			<div id='login'>
				<h3><em>Log</em> Me In</h3>
				<?php wp_login_form(); ?>
				<a href="<?php echo wp_lostpassword_url( get_bloginfo('url') ); ?>" title="Lost Password">Lost Your Password?</a>

			</div>
			<div id='register'>
				<a href='<?php bloginfo('url'); ?>/buyer-registration'>
					<h3>Register Now</h3>
					<img src='<?php bloginfo('stylesheet_directory'); ?>/images/register_arrow.png' title='Register' />
				</a>
			</div>
			<?php
		}
		else
		{
			?>
			<div class='logout_section'>
				<a href="<?php echo wp_logout_url( home_url() ); ?>" title="Logout"><img src='<?php bloginfo('template_directory'); ?>/images/log_out_button.png' /></a>
			</div><!-- .logout_section -->
			<?php
		}
		echo $after_widget;
	}

	function update($new_instance, $old_instance)
	{
		return $new_instance;
	}

	function form($instance)
	{

	}
}

class frontLogMeIn extends WP_Widget
{
	function frontLogMeIn()
	{
		parent::__construct(false, 'Front Page : Log Me In');
	}

	function widget($args, $instance)
	{
		extract($args);
		echo $before_widget;
		?>
			<div id='front_login'>
				<h2>Log me in:</h2>
				<?php wp_login_form(); ?>
				<a href="<?php echo wp_lostpassword_url( get_bloginfo('url') ); ?>" title="Lost Password">Lost Your Password?</a>

			</div>
		<?php
		echo $after_widget;
	}

	function update($new_instance, $old_instance)
	{
		return $new_instance;
	}

	function form($instance)
	{

	}

}

class browseWines extends WP_Widget
{
	function browseWines()
	{
		parent::__construct(false, 'Browse Wines');
	}

	function widget($args, $instance)
	{
		extract($args);
		echo $before_widget;
		?>
		<a href='<?php echo get_post_type_archive_link('vs_product'); ?>' title='Browse All Wines'>Browse</a>
		<?php
		echo $after_widget;

	}

	function update($new_instance, $old_instance)
	{
		return $new_instance;
	}

	function form($instance)
	{

	}
}

function vinsource_widgets()
{
	register_widget('LoggedInTextWidget');
	register_widget('logMeIn');
	register_widget('frontLogMeIn');
	register_widget('browseWines');
}

add_action('widgets_init', 'vinsource_widgets');


// Scripts
add_action('wp_enqueue_scripts', 'vinsource_scripts');
function vinsource_scripts()
{
	if(is_home()) 
	{
		wp_enqueue_script('scrollTo', get_template_directory_uri() . '/js/scrollTo.js', array('jquery'));
		wp_enqueue_script('vinsource', get_template_directory_uri() . '/js/main.js', array('jquery', 'scrollTo', 'fancybox'));
		wp_enqueue_script('fancybox', get_template_directory_uri() . '/lib/fancybox/source/jquery.fancybox.js?v=2.1.5', array('jquery'));
		wp_enqueue_style('fancybox_style', get_template_directory_uri() . '/lib/fancybox/source/jquery.fancybox.css?v=2.1.5');
	}
	else 
		wp_enqueue_script('vinsource', get_template_directory_uri() . '/js/main.js', array('jquery'));

	if(is_post_type_archive('vs_product'))
		wp_enqueue_script('cycle', get_template_Directory_uri() . '/js/cycle.js', array('jquery'));

	wp_register_script('browse', get_template_directory_uri() . '/js/browse.js', array('jquery'));
	wp_localize_script('browse', 'ajaxurl', admin_url('admin-ajax.php'));
	wp_localize_script('browse', 'loadingGif', "<img id='loading_gif' src='" . get_template_directory_uri() . "/images/ajax-loader.gif' alt='loading' />");

	if(is_post_type_archive('vs_product'))
	{
		global $vs_search_criteria;
		if($vs_search_criteria)
		{
			reset($vs_search_criteria);
			wp_localize_script('browse', 'searchBy', array('key' => key($vs_search_criteria), 'value' => reset($vs_search_criteria)));
		}
	}

	if(is_tax('varietal'))
	{
		wp_localize_script('browse', 'searchBy', array('key' => 'varietal', 'value' => get_query_var('term')));
	}

	if(is_tax('wine_region'))
	{
		wp_localize_script('browse', 'searchBy', array('key' => 'wine_region', 'value' => get_query_var('term')));
	}

	if(is_post_type_archive('vs_product') || is_tax()) wp_enqueue_script('browse');

	if(is_singular(array('vs_product', 'bid'))) wp_enqueue_script('bid', get_template_directory_uri() . '/js/bid.js', array('jquery'));

}

// Ajax Callbacks
add_action('wp_ajax_browse', 'browse_callback');
add_action('wp_ajax_nopriv_browse', 'browse_callback');

add_action('wp_ajax_wine_info', 'wine_info_callback');
add_action('wp_ajax_nopriv_wine_info', 'wine_info_callback');
function browse_callback()
{
	if(!is_numeric($_GET['wineryID'])) die('There was an error, please reload the page and try again');
	global $wpdb;

	$query_args = array(
		'post_per_page' => -1,
		'post_type' => 'vs_product',
		'meta_key' => 'vs_wine_winery',
		'meta_value' => $_GET['wineryID'],
		'orderby' => 'menu_order',
		'order' => 'ASC'
	);

	if(isset($_GET['searchKey']) && $_GET['searchKey'] != '' && isset($_GET['searchValue']) && $_GET['searchValue'] != '')
	{
		switch($_GET['searchKey'])
		{
		case 'varietal' :
			$query_args['varietal'] = $_GET['searchValue'];
		break;

		case 'wine_region' :
			$query_args['wine_region'] = $_GET['searchValue'];
		break;
		case 'max_price':
			$query_args['meta_query'][] = array(
				'key' => 'vs_wine_info_price',
				'compare' => '<=',
				'value' => $_GET['searchValue'],
				'type' => 'numeric'
			);
		break;
		case 'min_price':
			$query_args['meta_query'][] = array(
				'key' => 'vs_wine_info_price',
				'compare' => '>=',
				'value' => $_GET['searchValue'],
				'type' => 'numeric'
			);
		break;

		}
	}

	$wines = new WP_Query($query_args);

	if(count($wines->posts) == 0)
	{
		die('No wines found matching your search criteria.');
	}
	else
	{
		$i = 1;
		foreach($wines->posts as $post)
		{
			if($i == 1 || ($i - 1) % 4 == 0)
				echo "<li class='wine_group'><ul>";
			?><li class='wine'>
				<a class='wine_link' href='#wine_<?php echo $post->ID; ?>' title='<?php echo esc_attr(strip_tags(get_the_title($post->ID))); ?>'>
					<?php
					if(has_post_thumbnail($post->ID)) echo get_the_post_thumbnail($post->ID, 'wine_logo', array('class' => 'wine_logo'));	
					?>
					<div class='wine_info'>
						<div class='wine_name'><?php echo $post->post_title; ?></div>
						<div class='wine_year'><?php echo get_post_meta($post->ID, 'vs_wine_info_wine_year', true); ?></div>
						<div class='wine_varietals'>
							<?php
								$varietals = get_the_terms($post->ID, 'varietal');
								if($varietals)
								{
									if(count($varietals) == 1)
									{
										$varietal = reset($varietals);
										echo $varietal->name;
									}
									else echo 'Blend';
								}
							?>
						</div><!-- .wine_varietals -->
					</div><!-- .wine_info -->
				</a><!-- .wine_link -->
			</li>
			<?php
			if($i == 4 || $i % 4 == 0 || $i == count($wines->posts))
				echo "</ul></li><!-- .wine_group -->";

			$i ++;
		}
	}
	die();
}

function wine_info_callback()
{
	global $wpdb;

	if(!is_numeric($_GET['wineID'])) die('There was an error fetching the wine. Please reload and try again.');
	else
	{
		$wine_query = new WP_Query(array(
			'post_type' => 'vs_product',
			'p' => $_GET['wineID'],
			'posts_per_page' => 1
		));

		$wine = $wine_query->posts[0];

		if(is_null($wine)) die('There was an error fetching the wine. Please reload and try again.');
		print_wine_data($wine);
		
		if(current_user_can('seller'))
		{

		}
		elseif(current_user_can('add_bid', array('wine_id', $wine->ID)))
		{
			?>
			<a id='bid_button' href='<?php echo get_permalink($wine->ID); ?>' title='Buy now'><h3>Buy Now</h3><img src='<?php bloginfo('stylesheet_directory'); ?>/images/blue-arrow.png' alt='Purchase Direct' /></a>
			<?php
		}
		else
		{
			// Current user has already bid
			// We need the bid ID
			$current_user = wp_get_current_user();
			$bid = new WP_Query(array(
				'post_type' => 'bid',
				'author' => $current_user->ID,
				'meta_key' => 'wine_id',
				'meta_value' => $wine->ID,
				'posts_per_page' => 1
			));
			if(count($bid->posts) != 1)
			{
				wp_die('There was an error fetching your bid. Please check your account.');
			}
			$bid = $bid->posts[0];
				
			?>
			<a id='view_bid_button' href='<?php echo post_permalink($bid->ID); ?>'><h3>View my offer</h3><img src='<?php bloginfo('stylesheet_directory'); ?>/images/register_arrow.png' alt='View bid' /></a>
			<?php
		}
		?>
		<ul id='below_bid_button'>
			<?php dynamic_sidebar('below_bid_button'); ?>
		</ul><!-- #below_bid_button -->
		<?php
		if(has_post_thumbnail($wine->ID)) echo get_the_post_thumbnail($wine->ID, 'browse_bottle', array('class' => 'wine_bottle'));
	}

	die();
}

function vs_mail_from_name($name)
{
	return 'Vinsource Alerts';
}

function print_wine_data($wine)
{
	?>		
	<ul id='wine_data'>
		<li class='wine_title'><?php echo get_post_meta($wine->ID, 'vs_wine_info_wine_year', true) . ' ' . $wine->post_title; ?></li>
		<li class='wine_case_price'>
			<ul class='wine_case_price_discount_and_no_discount'>
				<li class='wine_case_price_no_discount'>
					<?php
					$prices = get_post_meta($wine->ID, 'vs_product_prices');
					$case_price = $prices[0]['regular'][1];
					$display_price = '$' . number_format($case_price, 2);
					$bottle_price = $case_price / 12;
					$display_price .= '/' . number_format($bottle_price, 2);
					?>
					<p class='price_breakdown'><em><?php echo $display_price; ?></em>(MSRP)</p>
					<p class='price_info'>PER CASE/BOTTLE</p>
				</li>
				<li class='wine_case_price_discount_tag'>
					<?php
					$reg_1_case = $prices[0]['regular'][1];
					$reg_10_case = $prices[0]['regular'][10];
					$btg_10_case = $prices[0]['btg'][10];
					if(($reg_1_case != $reg_10_case) OR ($reg_1_case != $btg_10_case))
						echo "<img src='" . get_bloginfo('stylesheet_directory') . "/images/tag-discounts-available.png' alt='Discounts available' />";
					?>
				</li>
			</ul>
		</li>
		<li class='label' id='wine_data_description_label'>Product Description</li>
		<li id='wine_data_description'>
			<?php echo apply_filters('the_content', $wine->post_content); ?>
		</li><!-- .wine_info_description -->
		<li class='label' id='wine_data_varietal_label'>
			<?php
			// First we count varietals here
			$varietals = get_the_terms($wine->ID, 'varietal');
			if(count($varietals) == 1) echo 'Varietal';
			else echo 'Varietal Blend';
			?>
		</li><!-- #wine_data_varietal_label -->
		<li id='wine_data_varietal'>
			<?php
			if(count($varietals) == 1)
			{
				$varietal = reset($varietals);
				echo $varietal->name;
			}
			else
			{
				$i = 1;
				foreach($varietals as $varietal)
				{
					echo $varietal->name;
					if($i < count($varietals)) echo ', ';
					$i ++;
				}
			}
			?>
		</li><!-- #wine_data_varietal -->
		<?php
			if(get_post_meta($wine->ID, 'vs_wine_info_case_production', true)) {
			?>
			<li class='label' id='wine_data_case_label'>Case Production</li>
			<li id='wine_data_case'>Approximately <?php echo number_format(get_post_meta($wine->ID, 'vs_wine_info_case_production', true)); ?> cases produced</li>
			<?php
			}
		?>
		<li id='wine_data_country'>
		<?php $countries = get_the_terms($wine->ID, 'wine_country'); ?>
		<?php $country = reset($countries); ?>
			<ul>
				<li class='label'>Country: </li>
				<li class='value'><?php echo $country->name; ?></li>
			</ul>
		</li><!-- #wine_data_country -->
		<li id='wine_data_state'>
		<?php $states = get_the_terms($wine->ID, 'wine_state'); ?>
		<?php $state = reset($states); ?>
			<ul>
				<li class='label'>State: </li>
				<li class='value'><?php echo $state->name; ?></li>
			</ul>
		</li><!-- #wine_data_state -->
		<li id='wine_data_region'>
		<?php $regions = get_the_terms($wine->ID, 'wine_region'); ?>
		<?php $region = reset($regions); ?>
			<ul>
				<li class='label'>Region: </li>
				<li class='value'><?php echo $region->name; ?></li>
			</ul>
		</li><!-- #wine_data_region -->
		<li id='wine_data_appelation'>
		<?php $appelations = get_the_terms($wine->ID, 'wine_appelation'); ?>
		<?php $appelation = reset($appelations); ?>
			<ul>
				<li class='label'>Appellation: </li>
				<li class='value'><?php echo $appelation->name; ?></li>
			</ul>
		</li><!-- #wine_data_appelation -->
		</li><!-- #wine_data_srp -->
	</ul><!-- #wine_data -->
	<?php
}

// Mapping meta caps
add_filter('map_meta_cap', 'vinsource_map_meta_caps', 10, 4);
function vinsource_map_meta_caps($caps, $cap, $user_id, $args)
{
	if('add_bid' == $cap && count($args) > 0)
	{
		$wine_id = $args[0];
		$bids = new WP_Query(array(
			'author' => get_current_user_id(),
			'post_status' => 'publish',
			'post_type' => 'bid',
			'meta_key' => 'wine_id',
			'meta_value' => $wine_id
		));


		if(count($bids->posts) > 0)
		{
			foreach($bids->posts as $bid)
			{
				// Since a user can of course place another bid after the old bid is closed or declined
				if(get_post_meta($bid->ID, 'status_code', true) > 5)
					continue;
				else
				{
					$caps[] = 'add_multiple_bids';
					break;
				}
			}
		}
	}

	if('edit_bid' == $cap || 'delete_bid' == $cap || 'read_bid' == $cap)
	{
		$post = get_post($args[0]);
		$post_type = get_post_type_object($post->post_type);

		$caps = array();
	}

	if('edit_bid' == $cap)
	{
		if($user_id == $post->post_author) $caps[] = $post_type->cap->edit_posts;
		else $caps[] = $post_type->cap->edit_other_posts;
	}

	elseif('delete_bid' == $cap)
	{
		if($user_id == $post->post_author) $caps[] = $post_type->cap->delete_posts;
		else $caps[] = $post_type->cap->delete_other_posts;
	}

	elseif('read_bid' == $cap)
	{
		if('private' != $post->post_status) $caps[] = 'read';
		elseif($user_id == $post->post_author) $caps[] = 'read';
		else $caps = $post_type->cap->read_private_posts;
	}

	return $caps;
}

// Template functions
function vs_message($type, $message)
{
	switch($type)
	{
	case 'success' :
	case 'alert' :
		echo "<div class='$type'><p>$message</p></div>";
	break;
	case 'error' :
		echo "<div class='$type'><p>$message</p></div>";
		//TODO email admin
	break;
	}
}

// Admin columns

// Send email to user when their role changes from Subscriber to Buyer
add_action('set_user_role', 'vs_role_change_email', 10, 2);
function vs_role_change_email($user_id, $new_role)
{
	if($new_role == 'buyer')
	{
		$site_url = site_url();
		$user_info = get_userdata($user_id);
		$to = $user_info->user_email;
		$subject = 'Congratulations, you have been approved to use Vinsourceonline.com';
		$message = 'Hello ' . $user_info->display_name . ', your liquor license has been validated and you are now ready to use ' . $site_url . "\r\n\r\n";
		$message .= "Sincerely,\r\n";
		$message .= "The Vinsource Team";

		wp_mail($to, $subject, $message);
	}
}
// Helper Functions
function get_parents_from_children($wp_query)
{
	$parents = array();
	foreach($wp_query->posts as $post)
	{
		if($post->post_parent != 0)
			$parents[] = $post->post_parent;
	}
	return $parents;
}
function vs_format_winery_address($winery)
{
	$winery_id = $winery->ID;
	$address = "<div class='address'>";
		$address .= $winery->post_title . '<br />';
		$address .= "ATTN: " . get_post_meta($winery_id, 'winery_accounts_receivable', true) . '<br />';
		$address .= get_post_meta($winery_id, 'winery_address_1', true) . '<br />';
		$address .= get_post_meta($winery_id, 'winery_address_2', true) ? get_post_meta($winery_id, 'winery_address_2', true) . '<br />' : '';
		$address .= get_post_meta($winery_id, 'winery_city', true) . ', ' . get_post_meta($winery_id, 'winery_state', true) . ' ' . get_post_meta($winery_id, 'winery_zip', true);
	$address .= '</div><!-- .address -->';

	return $address;
}

function vs_format_restaurant_address($user_id)
{
	$address = "<div class='address'>";
		$address .= get_user_meta($user_id, 'buyer_establishment', true) . '<br />';
		$address .= 'ATTN: ' . get_user_meta($user_id, 'first_name', true) . ' ' . get_user_meta($user_id, 'last_name', true) . '<br />';
		$address .= get_user_meta($user_id, 'buyer_address_1', true) . '<br />';
		$address .= get_user_meta($user_id, 'buyer_address_2', true) ? get_user_meta($user_id, 'buyer_address_2', true) . '<br />' : '';
		$address .= get_user_meta($user_id, 'buyer_city', true) . ', ' . get_user_meta($user_id, 'buyer_state', true) . ' ' . get_user_meta($user_id, 'buyer_zip', true);

	$address .= '</div><!-- .address -->';
	return $address;
}

function vs_get_dash()
{
	return get_post_type_archive_link('vs_transaction');
}

function vs_sortable_header($title, $query_args)
{
	// First find out if this is the current sorting header
	global $wp_query;
	apply_filters('request', $query_args);
	$similar_vars = array_intersect_assoc($query_args, $wp_query->query_vars);
	if(count($similar_vars) == count($query_args))
	{
		$current_sort = true;
		$sorting = strtolower(get_query_var('order'));
	}

	else $current_sort = false;
	
	$output = $current_sort ? "<th class='bid_header current_sort $sorting'>" : "<th class='bid_header'>";
	$output .= "<a title='Sort by $title' href='";
	// If this is after we are already sorting by this field, we add the option to sort DESC
	if($current_sort &&  $sorting == 'desc')
		$query_args[] = array('order' => 'ASC');

	$output .= add_query_arg($query_args, get_post_type_archive_link('bid'));
	$output .= "'>$title";
	$output .= "<div class='spacer_dot_gif'></div>";
	$output .= "<img class='sort_asc' src='" . get_bloginfo('stylesheet_directory') . "/images/sort_asc.png' title='ascending' />";
	$output .= "<img class='sort_desc' src='" . get_bloginfo('stylesheet_directory') . "/images/sort_desc.png' title='descending' />";
	$output .= "</a>";
	$output .= "</th>";

	return $output;

}
/*
* Returns an array of users attached to the winery. Accepts a bid object or a bid ID
*/
function vs_get_winery_users_by_bid($bid)
{
	if(is_object($bid))
		$bid_id = $bid->ID;
	
	else
		$bid_id = $bid;

	$wine_id = get_post_meta($bid_id, 'wine_id', true);
	$wine = get_post($wine_id);
	$winery_id = $wine->post_parent;
	$attached_users = get_users(array(
		'meta_key' => 'attached_winery',
		'meta_value' => $winery_id
	));

	return $attached_users;
}

function vs_get_restaurant_user_by_bid($bid)
{
	if(is_object($bid))
		$bid_author = $bid->post_author;
	
	else
	{
		$bid = get_post($bid);
		$bid_author = $bid->post_author;
	}

	$users = get_users(array(
		'include' => $bid_author
	));

	return $users[0];
}

function vs_get_winery_name_from_wine_id($wine_id)
{
	$winery_id = vs_get_winery_id_from_wine_id($wine_id);
	if($winery_id)
	{
		$winery = get_post($winery_id);
		$winery_name = $winery->post_title;
		return $winery_name;
	}
}

function vs_get_winery_id_from_wine_id($wine_id)
{
	$wine = get_post($wine_id);
	$winery_id = $wine->post_parent;
	if($winery_id)
	{
		return $winery_id;
	}
	else wp_die('Sorry, something went wrong. Error code NWU_01');
}

function new_excerpt_more( $more ) {
	return ' <a class="read-more" href="'. get_permalink( get_the_ID() ) . '">Read More</a>';
}
add_filter( 'excerpt_more', 'new_excerpt_more' );
// Debug
function db($data, $color = '#ffff99')
{
	echo "<pre style='background-color: $color'>";
	var_dump($data);
	echo "</pre>";
}

