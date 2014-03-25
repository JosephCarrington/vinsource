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
		'name' => 'How It Works',
		'id' => 'how_it_works',
		'description' => 'On the front page, under the latest blog post',
		'before_title' => '<span>',
		'after_title' => '</span>',
		'before_widget' => '<div>',
		'after_widget' => '</div>'
	));

	register_sidebar(array(
		'name' => 'Front Page Sidebar',
		'id' => 'front_sidebar',
		'description' => 'To the right of the front page blog post'
	));

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

	register_post_type('slide', array(
		'label' => 'Slides',
		'show_ui' => true,
		'supports' => array(
			'title',
			'editor',
			'thumbnail',
			'custom-fields',
			'page-attributes'
		)
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

		return $vars;
	}

	// Adding custom logic for ordering bids by winery, wine, or offer
	add_action('wp', 'vs_bid_sorting');

	// And shortcodes...
	add_shortcode('one_third', 'one_third');
	add_shortcode('two_thirds', 'two_thirds');
	add_shortcode('one_third_last', 'one_third_last');
	add_shortcode('two_thirds_last', 'two_thirds_last');

	// Register our post types...
	vinsource_post_types();
}

function vinsource_post_types()
{
	register_post_type('wine', array(
		'label' => 'wines',
		'labels' => array(
			'name' => 'Wines',
			'singular_name' => 'Wine',
			'add_new_item' => 'Add New Wine',
			'edit_item' => 'Edit Wine',
			'new_item' => 'New Wine',
			'view_item' => 'View Wine',
			'search_items' => 'Search Wines',
			'not_found' => 'No wines found',
			'not_found_in_trash' => 'No wines found in trash'
		),
		'public' => true,
		'menu_icon' => get_bloginfo('template_directory') . '/images/wine.png',
		'hierarchical' => true,
		'parent_item_colon' => 'Winery',
		'has_archive' => true,
		'supports' => array(
			'title',
			'editor',
			'thumbnail',
			'excerpt',
			'revisions',
			'page-attributes'
		)
	));

	register_taxonomy('varietal', 'wine', array(
		'label' => 'Varietals',
		'labels' => array(
			'name' => 'Varietals',
			'singular_name' => 'Varietal',
			'all_items' => 'All Varietals',
			'edit_item' => 'Edit Varietal',
			'update_item' => 'Update Varietal',
			'add_new_item' => 'Add New Varietal',
			'new_item_name' => 'New Varietal Name',
			'search_items' => 'Search Varietals',
			'popular_items' => 'Popular Varietals',
			'separate_items_with_commas' => 'Separate varietals with commas',
			'add_or_remove_items' => 'Add or remove varietals',
			'choose_from_most_used' => 'Choose from the most used varietals'
		),
		'sort' => true
	));

	register_taxonomy('wine_country', 'wine', array(
		'label' => 'Country',
		'labels' => array(
			'name' => 'Countries',
			'singular_name' => 'Country',
			'all_items' => 'All Countries',
			'edit_item' => 'Edit Country',
			'update_item' => 'Update Country',
			'add_new_item' => 'Add New Country',
			'new_item_name' => 'New Country Name',
			'search_items' => 'Search Countries',
			'popular_items' => 'Popular Countries',
			'add_or_remove_items' => 'Add or remove countries',
			'choose_from_most_used' => 'Choose from the mose used countries'
		),
		'hierarchical' => true
	));

	register_taxonomy('wine_state', 'wine', array(
		'label' => 'State',
		'labels' => array(
			'name' => 'States',
			'singular_name' => 'State',
			'all_items' => 'All States',
			'edit_item' => 'Edit State',
			'update_item' => 'Update State',
			'add_new_item' => 'Add New State',
			'new_item_name' => 'New State Name',
			'search_items' => 'Search States',
			'popular_items' => 'Popular States',
			'add_or_remove_items' => 'Add or remove states',
			'choose_from_most_used' => 'Choose from the mose used states'
		),
		'hierarchical' => true
	));

	register_taxonomy('wine_region', 'wine', array(
		'label' => 'Region',
		'labels' => array(
			'name' => 'Regions',
			'singular_name' => 'Region',
			'all_items' => 'All Regions',
			'edit_item' => 'Edit Region',
			'update_item' => 'Update Region',
			'add_new_item' => 'Add New Region',
			'new_item_name' => 'New Region Name',
			'search_items' => 'Search Regions',
			'popular_items' => 'Popular Regions',
			'add_or_remove_items' => 'Add or remove regions',
			'choose_from_most_used' => 'Choose from the mose used regions'
		),
		'hierarchical' => true
	));

	register_taxonomy('wine_appelation', 'wine', array(
		'label' => 'Appelation',
		'labels' => array(
			'name' => 'Appelations',
			'singular_name' => 'Appelation',
			'all_items' => 'All Appelations',
			'edit_item' => 'Edit Appelation',
			'update_item' => 'Update Appelation',
			'add_new_item' => 'Add New Appelation',
			'new_item_name' => 'New Appelation Name',
			'search_items' => 'Search Appelations',
			'popular_items' => 'Popular Appelations',
			'add_or_remove_items' => 'Add or remove appelations',
			'choose_from_most_used' => 'Choose from the mose used appelations'
		),
		'hierarchical' => true
	));

	register_post_type('bid', array(
		'label' => 'Bids',
		'labels' => array(
			'name' => 'Bids',
			'singular_name' => 'Bid',
			'all_items' => 'All Bids',
			'add_new' => 'Add New',
			'add_new_item' => 'Add New Bid',
			'edit_item' => 'Edit Bid',
			'new_item' => 'New Bid',
			'view_item' => 'View Bid',
			'items_archive' => 'Bid Archive',
			'search_items' => 'Search Bids',
			'not_found' => 'No bids found',
			'not_found_in_trash' => 'No bids found in trash'
		),
		'public' => true,
		'show_in_nav_menus' => false,
		'show_in_admin_bar' => true,
		'menu_icon' => get_bloginfo('template_directory') . '/images/bids_icon.png',
		'supports' => array(
			'author',
			'title'
		),
		'has_archive' => true,
		'capability_type' => 'bid',
		'capabilities' => array(
			'publish_posts' => 'add_bids',
			'edit_posts' => 'edit_bids',
			'edit_other_posts' => 'edit_other_bids',
			'delete_posts' => 'delete_bids',
			'delete_other_posts' => 'delete_other_bids',
			'read_private_posts' => 'read_private_bids',
			'edit_post' => 'edit_bid',
			'delete_post' => 'delete_bid',
			'read_post' => 'read_bid'
		)
	));

	// Our newset post type, payments! We use these to keep track of payments through PayPal, since they are not always instantaneous. This also allows us to do fun stuff with seeing how much money is flowing through the system

	register_post_type('payment', array(
		'label' => 'Payments',
		'labels' => array(
			'name' => 'Payments',
			'singular_name' => 'Payment',
			'all_items' => 'All Payments',
			'add_new' => 'Manually Record New',
			'add_new_item' => 'Manually Record New Payment',
			'edit_item' => 'Edit Payment',
			'new_item' => 'New Payment',
			'view_item' => 'View Payment',
			'items_archive' => 'Payment Archive',
			'search_items' => 'Search Payments',
			'not_found' => 'No payments found',
			'not_found_in_trash' => 'No payments found in trash'
		),
		'public' => false,
		'show_ui' => true,
		'supports' => false
	));
}

function vs_bid_sorting()
{
	if(!is_post_type_archive('bid')) return;

	$new_post_order = array();
	global $posts;

	switch(get_query_var('orderby'))
	{
	case 'winery' :
		foreach($posts as $post)
		{
			$wine = get_post(get_post_meta($post->ID, 'wine_id', true));
			$post->winery_name = get_the_title($wine->post_parent);
		}
		usort($posts, function($a, $b)
		{
			if(get_query_var('order') == 'ASC')
				return(strcmp($b->winery_name, $a->winery_name));
			else
				return(strcmp($a->winery_name, $b->winery_name));
		});
	
	
	break;
	case 'wine' :
		foreach($posts as $post)
		{
			$post->wine_name = get_the_title(get_post_meta($post->ID, 'wine_id', true));
		}
		usort($posts, function($a, $b)
		{
			if(get_query_var('order') == 'ASC')
				return(strcmp($b->wine_name, $a->wine_name));
			else
				return(strcmp($a->wine_name, $b->wine_name));

		});
		

	break;
	case 'offer' :
		foreach($posts as $post)
		{
			$wine_per_case = 12;
			$total_bottles = get_post_meta($post->ID, 'case_amount', true);
			$full_price = $total_bottles * get_post_meta($post->ID, 'wine_price', true);

			$percent_of_price = $full_price * get_post_meta($post->ID, 'percentage', true);

			$post->total_price = $percent_of_price;
		}
		usort($posts, function($a, $b)
		{
			if(get_query_var('order') == 'ASC')
				return($b->total_price < $a->total_price);
			else
				return($a->total_price < $b->total_price);

		});

	break;
	case 'status' :
		foreach($posts as $post)
		{
			$post->bid_status = get_post_meta($post->ID, 'status_code', true);
		}
		usort($posts, function($a, $b)
		{
			if(get_query_var('order') == 'ASC')
				return($b->bid_status < $a->bid_status);
			else
				return($a->bid_status < $b->bid_status);

		});

	break;
	}

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
			wp_redirect(get_post_type_archive_link('wine'));
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
	h1 a { background-image:url('.get_bloginfo('template_directory').'/images/logo.png) !important; }
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
		case 'bid' :
			if(!current_user_can('administrator') && !current_user_can('editor'))
			{
				// User is not an admin and not an editor
				// We do different things based on whether this is an archive or a single bid
				$current_user = wp_get_current_user();

				if(is_singular())
				{
					$bid_id = $query->query_vars['bid'];
					if(current_user_can('seller'))
					{
						// If the user is a seller, we make sure that the bid is on a wine that they are connected to the wnery of
						$attached_winery = get_user_meta($current_user->ID, 'attached_winery', true);

						$wine_id = get_post_meta($bid_id, 'wine_id', true);
						$wine = get_post($wine_id);
						$winery_id = $wine->post_parent;
						if($winery_id != $attached_winery)
						{
							wp_die("You do not have permission to view this page. <a href='" . get_bloginfo('url') . "' title='return home'>Return home.</a>" );
						}
					}
					elseif(current_user_can('buyer'))
					{
						$bid = get_post($bid_id);
						$bid_author = $bid->post_author;
						if($bid_author != $current_user->ID)
						{
							wp_die("You do not have permission to view this page. <a href='" . get_bloginfo('url') . "' title='return home'>Return home.</a>" );
						}
					}
				}
				else
				{
					if(current_user_can('seller'))
					{
						$winery_id = get_user_meta($current_user->ID, 'attached_winery', true);
						$wines = get_posts("post_type=wine&posts_per_page=-1&post_parent=$winery_id");
						$wine_ids = array();
						foreach($wines as $wine)
						{
							$wine_ids[] = $wine->ID;
						}

						if(count($wine_ids))
						{
							$meta_query = array(
								array(
									'key' => 'wine_id',
									'value' => $wine_ids,
									'compare' => 'IN',
									'type' => 'numeric'
								)
							);
							$query->set('meta_query', $meta_query);
						}
					}
					elseif(current_user_can('buyer'))
					{
						$query->set('author', $current_user->ID);
					}
				}
			}
		break;
		case 'wine' :
			if(!current_user_can('administrator') && !current_user_can('editor') && !current_user_can('buyer') && !current_user_can('seller'))
			{
				wp_die("You do not have permission to view this page. <a href='" . get_bloginfo('url') . "' title='return home'>Return home.</a>" );
			}
		break;
		}
	}

	if(!is_admin() && $query->is_home() && $query->is_main_query())
	{
		$query->set('posts_per_page', '1');
	}

	if(!is_admin() && $query->is_post_type_archive('wine') && $query->is_main_query())
	{
		if(current_user_can('add_bid'))
		{
			if(get_query_var('max_cases') || get_query_var('min_cases') || get_query_var('max_price') || get_query_var('min_price'))
			{
				global $vs_search_criteria;

				if(get_query_var('max_price'))
				{
					$vs_search_criteria = array('max_price' => get_query_var('max_price'));
					$args = array(
						'post_type' => 'wine',
						'meta_query' => array(
							array(
								'key' => 'wine_price',
								'compare' => '<=',
								'value' => intval(get_query_var('max_price')),
								'type' => 'numeric'
							)
						)
					);
					$children = new WP_Query($args);

				}

				if(get_query_var('min_price'))
				{
					$vs_search_criteria = array('min_price' => get_query_var('min_price'));
					$args = array(
						'post_type' => 'wine',
						'meta_query' => array(
							array(
								'key' => 'wine_price',
								'compare' => '>=',
								'value' => intval(get_query_var('min_price')),
								'type' => 'numeric'
							)
						)
					);
					$children = new WP_Query($args);
				}

				if(get_query_var('max_cases'))
				{
					$vs_search_criteria = array('max_cases' => get_query_var('max_cases'));
					$args = array(
						'post_type' => 'wine',
						'meta_query' => array(
							array(
								'key' => 'case_production',
								'compare' => '<=',
								'value' => intval(get_query_var('max_cases')),
								'type' => 'numeric'
							),
							array(
								'key' => 'case_production',
								'compare' => '!=',
								'value' => ''
							)
							
						)
					);
					$children = new WP_Query($args);
				}

				if(get_query_var('min_cases'))
				{
					$vs_search_criteria = array('min_cases' => get_query_var('min_cases'));
					$args = array(
						'post_type' => 'wine',
						'meta_query' => array(
							array(
								'key' => 'case_production',
								'compare' => '>=',
								'value' => intval(get_query_var('min_cases')),
								'type' => 'numeric'
							),
							array(
								'key' => 'case_production',
								'compare' => '!=',
								'value' => ''
							)
						)
					);
					$children = new WP_Query($args);
				}

				if(count($children->posts) == 0) wp_die('There are no wines in that range. Please try a different amount.');
				$parents = get_parents_from_children($children);
				$query->set('post__in', $parents);
			}
			
			$query->set('post_parent', 0);
			$query->set('posts_per_page', -1);
			$query->set('orderby', 'menu_order');
			$query->set('order', 'DESC');
		}
		elseif(current_user_can('seller'))
		{
			global $current_user;
			get_currentuserinfo();
			if($winery_id = get_user_meta($current_user->ID, 'attached_winery', true))
			{
				$query->set('post__in', array($winery_id));
			}
		}
	}


	if(!is_admin() && is_tax('varietal') && $query->is_main_query())
	{
		global $tax_value;
		$tax_value = $query->get('varietal');
		$parents = array();
		$children = new WP_Query('posts_per_page=-1&varietal=' . $query->get('varietal'));
		foreach($children->posts as $child)
		{
			if($child->post_parent != 0)
				$parents[] = $child->post_parent;
		}

		$query->set('varietal', '');
		$query->set('post__in', $parents);
	}

	if(!is_admin() && is_tax('wine_region') && $query->is_main_query())
	{
		global $tax_value;
		$tax_value = $query->get('wine_region');
		$parents = array();
		$children = new WP_Query('post_type=wine&posts_per_page=-1&wine_region=' . $query->get('wine_region'));
		foreach($children->posts as $child)
		{
			if($child->post_parent != 0)
				$parents[] = $child->post_parent;
		}

		$query->set('wine_region', '');
		$query->set('post__in', $parents);
	}
	if(current_user_can('seller') && is_single())
	{
		if('wine' == $post_type)
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


class HowItWidgets extends WP_Widget
{
	function HowItWidgets()
	{
		parent::__construct( false, 'How It Works Section' );
	}

	function widget($args, $instance)
	{
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $before_widget;
		?>
		<ul class='HIW_area'>
			<li class='HIW_image'>
				<?php if(!empty($instance['image'])) echo "<img src='" . $instance['image'] . "' />" ;?>
				<?php if ( ! empty( $title ) ) echo $before_title . $title . $after_title; ?>
			</li><!-- .HIW_image -->
			<li class='HIW_blurb'>
				<?php if(!empty($instance['description'])) echo $instance['description']; ?>
			</li><!-- .HIW_blurb -->
		</ul><!-- .HIW_area -->
		<?php
		echo $after_widget;	
	}

	function update($new_instance, $old_instance)
	{
		$instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['image'] = strip_tags( $new_instance['image'] );
		$instance['description'] = $new_instance['description'];

		return $instance;
	}

	function form($instance)
	{
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = '';
		}
		if ( isset( $instance[ 'image' ] ) ) {
			$image = $instance[ 'image' ];
		}
		else {
			$image = '';
		}
		if ( isset( $instance[ 'description' ] ) ) {
			$description = $instance[ 'description' ];
		}
		else {
			$description = '';
		}

		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'image' ); ?>"><?php _e( 'Image:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'image' ); ?>" name="<?php echo $this->get_field_name( 'image' ); ?>" type="text" value="<?php echo esc_attr( $image ); ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'description' ); ?>"><?php _e( 'Description:' ); ?></label> 
		<textarea class="widefat" rows='16' columns='20' id="<?php echo $this->get_field_id( 'description' ); ?>" name="<?php echo $this->get_field_name( 'description' ); ?>"><?php echo esc_attr( $description ); ?></textarea>
		</p>

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
				<a href='<?php bloginfo('url'); ?>/restaurant-registration'>
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
		<a href='<?php echo get_post_type_archive_link('wine'); ?>' title='Browse All Wines'>Browse</a>
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
	register_widget('HowItWidgets');
	register_widget('logMeIn');
	register_widget('browseWines');
}

add_action('widgets_init', 'vinsource_widgets');


// Scripts
add_action('wp_enqueue_scripts', 'vinsource_scripts');
function vinsource_scripts()
{
	wp_enqueue_script('cycle', get_template_directory_uri() . '/js/cycle.js', array('jquery'));
	wp_enqueue_script('vinsource', get_template_directory_uri() . '/js/main.js', array('jquery'));

	wp_register_script('browse', get_template_directory_uri() . '/js/browse.js', array('jquery'));
	wp_localize_script('browse', 'ajaxurl', admin_url('admin-ajax.php'));
	wp_localize_script('browse', 'loadingGif', "<img id='loading_gif' src='" . get_template_directory_uri() . "/images/ajax-loader.gif' alt='loading' />");

	if(is_post_type_archive('wine'))
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
		global $tax_value;
		wp_localize_script('browse', 'searchBy', array('key' => 'varietal', 'value' => $tax_value));
	}

	if(is_tax('wine_region'))
	{
		global $tax_value;
		wp_localize_script('browse', 'searchBy', array('key' => 'wine_region', 'value' => $tax_value));
	}

	if(is_post_type_archive('wine') || is_tax()) wp_enqueue_script('browse');

	if(is_singular(array('wine', 'bid'))) wp_enqueue_script('bid', get_template_directory_uri() . '/js/bid.js', array('jquery'));
}

// Custom backend functions
add_action('admin_enqueue_scripts', 'vinsource_admin_scripts');
function vinsource_admin_scripts()
{
	wp_enqueue_script('reminder', get_template_directory_uri() . '/js/reminder.js', array('jquery'));
}

// Ajax Callbacks
add_action('wp_ajax_browse', 'browse_callback');
add_action('wp_ajax_nopriv_browse', 'browse_callback');

add_action('wp_ajax_wine_info', 'wine_info_callback');
add_action('wp_ajax_nopriv_wine_info', 'wine_info_callback');

add_action('wp_ajax_remind', 'reminder_callback');
function browse_callback()
{
	//wp_mail('joseph.carrington@gmail.com', 'Browsing at VS', var_export($_GET, true));
	if(!is_numeric($_GET['wineryID'])) die('There was an error, please reload the page and try again');
	global $wpdb;

	$query_args = array(
		'post_per_page' => -1,
		'post_type' => 'wine',
		'post_parent' => $_GET['wineryID'],
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
		case 'max_cases':
			$query_args['meta_query'][] = array(
				'key' => 'case_production',
				'compare' => '<=',
				'value' => $_GET['searchValue'],
				'type' => 'numeric'
			);
			$query_args['meta_query'][] = array(
				'key' => 'case_production',
				'compare' => '!=',
				'value' => ''
			);
		break;
		case 'min_cases':
			$query_args['meta_query'][] = array(
				'key' => 'case_production',
				'compare' => '>=',
				'value' => $_GET['searchValue'],
				'type' => 'numeric'
			);
			$query_args['meta_query'][] = array(
				'key' => 'case_production',
				'compare' => '!=',
				'value' => ''
			);
		break;
		case 'max_price':
			$query_args['meta_query'][] = array(
				'key' => 'wine_price',
				'compare' => '<=',
				'value' => $_GET['searchValue'],
				'type' => 'numeric'
			);
		break;
		case 'min_price':
			$query_args['meta_query'][] = array(
				'key' => 'wine_price',
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
		$all_wines = array();
		foreach($wines->posts as $post)
		{
			if(isset($post->post_parent) && $post->post_parent == 0) continue;
			$all_wines[] = $post;
		}
		foreach($all_wines as $post)
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
						<div class='wine_year'><?php echo get_post_meta($post->ID, 'wine_year', true); ?></div>
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
			if($i == 4 || $i % 4 == 0 || $i == count($all_wines))
				echo "</ul></li><!-- .wine_group -->";

			$i ++;
		}
	}
	die();
}

function wine_info_callback()
{
	global $wpdb;

	//wp_mail('joseph.carrington@gmail.com', 'Browsing at VS', var_export($_GET, true));
	if(!is_numeric($_GET['wineID'])) die('There was an error fetching the wine. Please reload and try again.');
	else
	{
		$wine = get_post($_GET['wineID']);
		if(is_null($wine)) die('There was an error fetching the wine. Please reload and try again.');
		print_wine_data($wine);
		
		if(current_user_can('seller'))
		{

		}
		elseif(current_user_can('add_bid', array('wine_id', $wine->ID)))
		{
			?>
			<a id='bid_button' href='<?php echo get_permalink($wine->ID); ?>' title='Place a bid'><h3>Make an offer</h3><img src='<?php bloginfo('stylesheet_directory'); ?>/images/register_arrow.png' alt='Place a bid' /></a>
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

function reminder_callback()
{
	$nonce = $_GET['nonce'];
	$bid_id = $_GET['bidID'];
	
	if(check_ajax_referer("reminder_$bid_id", 'nonce', false))
	{
		$wine_id = get_post_meta($bid_id, 'wine_id', true);
		$wine = get_post($wine_id);

		$bid = get_post($bid_id);
		$bid_status = intval(get_post_meta($bid_id, 'status_code', true));
		switch($bid_status)
		{
		case 1:
		case 3:
		case 4:
			$email = array();
			$winery_users = vs_get_winery_users_by_bid($bid_id);
			foreach($winery_users as $user)
			{
				$email[] = $user->user_email;
			}
		break;

		case 2:
		case 5:
			$author_id = $bid->post_author;
			$email = get_the_author_meta('user_email', $author_id);
		break;
		default:
			die(json_encode(array('status' => 'unusable status code')));
		break;
		}


		$reminder_subject = 'Reminder: You have action in your Vinsource account!';
		$reminder_body = 'This action is related to the offer on ';
		$reminder_body .= get_post_meta($wine->ID, 'wine_year', true) . ' ' . $wine->post_title . ' (Transaction #' . $bid_id . ')';
		$reminder_body .= "\r\n\r\n";
		$reminder_body .= 'Please log in at ' . get_permalink($bid_id) . ' to view this transaction.';

		if(wp_mail($email, $reminder_subject, $reminder_body))
		{
			if(update_post_meta($bid_id, 'last_reminded', time())) {
				die(json_encode(array('status' => 'success')));
			}
			else die(json_encode(array('status' => 'could not update post_meta')));
		}
		else die(json_encode(array('status' => 'could not send mail')));
	}
	else die(json_encode(array('status' => 'incorrect nonce')));
}

function vs_mail_from_name($name)
{
	return 'Vinsource Alerts';
}

function print_wine_data($wine)
{
	?>		
	<ul id='wine_data'>
		<li id='wine_data_title'><?php echo get_post_meta($wine->ID, 'wine_year', true); ?> <?php echo $wine->post_title; ?></li>
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
			if(get_post_meta($wine->ID, 'case_production', true)) {
			?>
			<li class='label' id='wine_data_case_label'>Case Prodution</li>
			<li id='wine_data_case'>Approximately <?php echo number_format(get_post_meta($wine->ID, 'case_production', true)); ?> cases produced</li>
			<?php
			}
		?>
		<li id='wine_data_country'>
		<?php $country = reset(get_the_terms($wine->ID, 'wine_country')); ?>
			<ul>
				<li class='label'>Country: </li>
				<li class='value'><?php echo $country->name; ?></li>
			</ul>
		</li><!-- #wine_data_country -->
		<li id='wine_data_state'>
		<?php $state = reset(get_the_terms($wine->ID, 'wine_state')); ?>
			<ul>
				<li class='label'>State: </li>
				<li class='value'><?php echo $state->name; ?></li>
			</ul>
		</li><!-- #wine_data_state -->
		<li id='wine_data_region'>
		<?php $region = reset(get_the_terms($wine->ID, 'wine_region')); ?>
			<ul>
				<li class='label'>Region: </li>
				<li class='value'><?php echo $region->name; ?></li>
			</ul>
		</li><!-- #wine_data_region -->
		<li id='wine_data_appelation'>
		<?php $appelation = reset(get_the_terms($wine->ID, 'wine_appelation')); ?>
			<ul>
				<li class='label'>Appelation: </li>
				<li class='value'><?php echo $appelation->name; ?></li>
			</ul>
		</li><!-- #wine_data_appelation -->
		<li id='wine_data_srp'>
			Suggested Retail Price: <?php echo money_format('%.0n', get_post_meta($wine->ID, 'wine_price', true)); ?>
		</li><!-- #wine_data_srp -->
	</ul><!-- #wine_data -->
	<?php
}

function print_buy_sample_form($wine)
{
	if(!get_post_meta($wine->ID, 'wine_purchase_sample', true)) return;

	// Get our winery ID
	$winery_id = $wine->post_parent;
	if($winery_id)
	{
		// First make sure we can receive payments in the first place
		if(vs_can_winery_receive_payments($winery_id))
		{
			// Get the address to which we are sending the payment
			$winery_paypal_address = get_post_meta($winery_id, 'winery_paypal_address', true);
			// Get the address from which we are receiving the funds
			$buyer_paypal_address = vs_get_buyer_paypal_address(get_current_user_id());
			$sample_price = get_post_meta($wine->ID, 'wine_sample_price', true);
			if($winery_paypal_address && $buyer_paypal_address && $sample_price)
			{
				// We have everything we need to make a sale, let's make the button
				?>
					<div id='sample_order_section'>
						<p id='sample_order_header'>Sample Price: $<?php echo $sample_price; ?></p>
						<p id='sample_order_blurb'>Try before you buy! Sometimes you just need to get up close.</p>
						<p id='sample_order_button_wrapper'>
							<a id='sample_order_button' href='<?php echo add_query_arg('action', 'buy_sample', get_permalink($wine->ID)); ?>' title='Buy sample of <?php echo $wine->post_title; ?>'>Buy Sample</a>
						</p>
					</div><!-- #sample_order_section -->
				<?php

			}

		}
	}
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
add_filter('manage_bid_posts_columns', 'vs_bid_column');
function vs_bid_column($column)
{
	unset($column['date']);
	$column['wine'] = 'Wine';
	$column['winery'] = 'Winery';
	$column['percentage'] = '% Offered';
	$column['cases'] = 'Cases Requested';
	$column['total_offered'] = 'Total Offered';
	$column['author'] = 'User';
	$column['status_code'] = 'Status';
	$column['last_action'] = 'Last Action';
	$column['reminder'] = 'Send Reminder';

	return $column;
}

add_filter('manage_bid_posts_custom_column', 'vs_bid_row', 10, 2);
function vs_bid_row($column_name, $post_id)
{
	$cf = get_post_custom($post_id);
	$wine = get_post($cf['wine_id'][0]);
	$bid = get_post($post_id);
	switch($column_name)
	{
	case 'date':
		echo 'test';
	break;

	case 'status_code':
		echo $cf['status_code'][0];
	break;
	case 'wine' : 
		echo $wine->post_title;
	break;
	case 'winery' :
		echo get_the_title($wine->post_parent);
	break;
	case 'percentage' :
		echo ($cf['percentage'][0] * 100 . '%');
	break;
	case 'cases' :
		echo $cf['case_amount'][0];
	break;
	case 'total_offered' :
		$bottles_per_case = 12;
		$total = $cf['wine_price'][0] * $bottles_per_case * $cf['case_amount'][0] * $cf['percentage'][0];

		echo money_format('%.2n', $total);
	break;
	case 'last_action' :
		$last_modified_date = $bid->post_modified;
		$last_modified_time = strtotime($last_modified_date);

		$current_time = time();
		$difference = $current_time - $last_modified_time;
		$day_difference = intval(floor($difference / (3600 * 24)));

		if($day_difference == 0)
		{
			echo 'Today';
		}
		elseif($day_difference == 1)
		{
			echo $day_difference . ' day ago';
		}
		else
		{
			echo $day_difference . ' days ago';
		}

	break;
	case 'reminder' :
		if(isset($cf['last_reminded']))
		{
			$last_reminded = $cf['last_reminded'][0];
			$current_time = time();
			$difference = $current_time - $last_reminded;
			$day_difference = intval(floor($difference / (3600 * 24)));

			if($day_difference == 0)
			{
				$last_reminded_message = 'Today';
			}
			elseif($day_difference == 1)
			{
				$last_reminded_message = $day_difference . ' day ago.';
			}
			else
			{
				$last_reminded_message = $day_difference . ' days ago.';
			}
		}

		else
			$last_reminded_message = 'Never';
	
		?>
		<img src='<?php bloginfo('template_directory'); ?>/images/ajax-loader.gif' title='loading' alt='loading' class='hidden' />
		<input type='button' value='Send reminder' id='reminder_<?php echo $post_id; ?>' />
		<input type='hidden' value='<?php echo wp_create_nonce("reminder_$post_id"); ?>' class='reminder_nonce' />
		<br />
		Last reminder sent : 
		<?
		echo "<span class='last_reminder'>$last_reminded_message</span>";
	break;
	}
}

add_filter('manage_wine_posts_columns', 'vs_wine_column');
function vs_wine_column($column)
{
	$column['winery'] = 'Winery';
	$column['varietals'] = 'Varietals';
	$column['year'] = 'Year';
	$column['case'] = 'Case Production';
	$column['country'] = 'Country';
	$column['state'] = 'State';
	$column['region'] = 'Region';
	$column['appelation'] = 'Appelation';
	$column['srp'] = 'SRP';

	return $column;
}

add_filter('manage_wine_posts_custom_column', 'vs_wine_row', 10, 2);
function vs_wine_row($column_name, $post_id)
{
	$cf = get_post_custom($post_id);
	$wine = get_post($post_id);
	if($wine->post_parent == 0) return;
	switch($column_name)
	{
	case 'winery' :
		echo get_the_title($wine->post_parent);
	break;
	case 'varietals' :
			$varietals = get_the_terms($post_id, 'varietal');
			$i = 1;
			foreach($varietals as $varietal)
			{
				echo $varietal->name;
				if($i < count($varietals)) echo ', ';
				$i ++;
			}
	break;
	case 'year' :
		if(isset($cf['wine_year']))
			echo $cf['wine_year'][0];
	break;
	case 'case' :
		if(isset($cf['case_production']))
			echo $cf['case_production'][0];
	break;
	case 'country' :
		if(isset($cf['wine_country']))
			echo $cf['wine_country'][0];
	break;
	case 'state' :
		if(isset($cf['wine_state']))
			echo $cf['wine_state'][0];
	break;
	case 'region' :
		if(isset($cf['wine_region']))
			echo $cf['wine_region'][0];
	break;
	case 'appelation' :
		if(isset($cf['wine_appelation']))
			echo $cf['wine_appelation'][0];
	break;
	case 'srp' :
		echo $cf['wine_price'][0];
	break;
	}
}
		
// Add custom field to user profiles to add additional info based on the user's class
add_action('edit_user_profile', 'vs_extra_profile_fields');
function vs_extra_profile_fields($user)
{
	// If this is a seller profile, the admins can associate a winery
	if(current_user_can('edit_user', $user->ID) && user_can($user->ID, 'seller'))
	{
		$all_wineries = get_posts('posts_per_page=-1&post_parent=0&post_type=wine');
		$current_winery = get_user_meta($user->ID, 'attached_winery', true);
		?>
		<h3><?php _e('Seller Winery Info', 'vinsource'); ?></h3>
		<table class='form-table'>
			<tr>
				<th><label for='seller_winery'><?php _e('Winery', 'vinsource'); ?></label></th>
				<td>
					<select name='seller_winery' id='seller_winery'>
						<?php if(!$current_winery)
							echo "<option>Please select</option>"; ?>

						<?php foreach($all_wineries as $winery)
						{
							if($current_winery == $winery->ID)
								echo "<option value='$winery->ID' selected>$winery->post_title</option>";
					
							else
								echo "<option value='$winery->ID'>$winery->post_title</option>";
						}
						?>
					</select><!-- #seller_winery -->
					<span class='description'>Which winery should this user be able to accept bids for?</span>
				</td>
			</tr>
		</table>
		<?php
	}

	// If this is a buyer field, the admins can associate an address
	elseif(current_user_can('edit_user', $user->ID) && user_can($user->ID, 'buyer'))
	{
		// Get all the user meta
		?>
		<h3><?php _e('Buyer Info', 'vinsource'); ?></h3>
		<table class='form-table'>
			<tr>
				<th><label for='buyer_title'>Title</label></th>
				<td>
					<input type='text' name='buyer_title' id='buyer_title' value='<?php echo get_user_meta($user->ID, 'buyer_title', true); ?>' />
				</td>
			</tr>
			<tr>
				<th><label for='buyer_phone'>Phone</label></th>
				<td>
					<input type='text' name='buyer_phone' id='buyer_phone' value='<?php echo get_user_meta($user->ID, 'buyer_phone', true); ?>' />
				</td>
			</tr>
			<tr>
				<th><label for='buyer_establishment'>Establishment Name</label></th>
				<td>
					<input type='text' name='buyer_establishment' id='buyer_establishment' value='<?php echo get_user_meta($user->ID, 'buyer_establishment', true); ?>' />
				</td>
			</tr>
			<tr>
				<th><label for='buyer_address_1'>Address</label></th>
				<td>
					<input type='text' name='buyer_address_1' id='buyer_address_1' value='<?php echo get_user_meta($user->ID, 'buyer_address_1', true); ?>' />
				</td>
			</tr>
			<tr>
				<th><label for='buyer_address_2'>Address 2</label></th>
				<td>
					<input type='text' name='buyer_address_2' id='buyer_address_2' value='<?php echo get_user_meta($user->ID, 'buyer_address_2', true); ?>' />
				</td>
			</tr>
			<tr>
				<th><label for='buyer_city'>City</label></th>
				<td>
					<input type='text' name='buyer_city' id='buyer_city' value='<?php echo get_user_meta($user->ID, 'buyer_city', true); ?>' />
				</td>
			</tr>
			<tr>
				<th><label for='buyer_state'>State</label></th>
				<td>
					<input type='text' name='buyer_state' id='buyer_state' value='<?php echo get_user_meta($user->ID, 'buyer_state', true); ?>' />
				</td>
			</tr>
			<tr>
				<th><label for='buyer_zip'>Zip</label></th>
				<td>
					<input type='text' name='buyer_zip' id='buyer_zip' value='<?php echo get_user_meta($user->ID, 'buyer_zip', true); ?>' />
				</td>
			</tr>
			<tr>
				<th><label for='buyer_liquor_license'>Liquor License</label></th>
				<td>
					<input type='text' name='buyer_liquor_license' id='buyer_liquor_license' value='<?php echo get_user_meta($user->ID, 'buyer_liquor_license', true); ?>' />
				</td>
			</tr>
			<tr>
				<th><label for='buyer_details'>Additional Details</label></th>
				<td>
					<textarea name='buyer_details' id='buyer_details'><?php echo get_user_meta($user->ID, 'buyer_details', true); ?></textarea>
				</td>
			</tr>
		</table><!-- .form-table -->
		<?php
	}
}

add_action('edit_user_profile_update', 'vs_save_extra_profile_fields');
function vs_save_extra_profile_fields($user_id)
{
	if(current_user_can('edit_user', $user_id))
	{
		if(user_can($user_id, 'seller'))
		{
			update_user_meta($user_id, 'attached_winery', $_POST['seller_winery']);
		}
		elseif(user_can($user_id, 'buyer'))
		{
			
			update_user_meta($user_id, 'buyer_title', $_POST['buyer_title']);
			update_user_meta($user_id, 'buyer_phone', $_POST['buyer_phone']);
			update_user_meta($user_id, 'buyer_establishment', $_POST['buyer_establishment']);
			update_user_meta($user_id, 'buyer_address_1', $_POST['buyer_address_1']);
			update_user_meta($user_id, 'buyer_address_2', $_POST['buyer_address_2']);
			update_user_meta($user_id, 'buyer_city', $_POST['buyer_city']);
			update_user_meta($user_id, 'buyer_state', $_POST['buyer_state']);
			update_user_meta($user_id, 'buyer_zip', $_POST['buyer_zip']);
			update_user_meta($user_id, 'buyer_liquor_license', $_POST['buyer_liquor_license']);
			update_user_meta($user_id, 'buyer_details', $_POST['buyer_details']);
		}
	}
}

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

function vs_get_bid_status_info($bid, $verbose = false)
{
	$status_code = get_post_meta($bid->ID, 'status_code', true);
	$wine_id = get_post_meta($bid->ID, 'wine_id', true);
	$wine = get_post($wine_id);
	$winery_id = get_post($wine->post_parent);
	$winery = get_post($winery_id);
	
	if(current_user_can('add_bid'))
	{
		$short_message = "<div class='status_short for_buyer status_code_$status_code'>";
		$short_message .= "<div class='status_badge'></div>";
		$short_message .= "<a href='" . get_permalink($bid->ID) . "' title='$bid->ID'>";

		$verbose_message = "<div class='status_verbose for_buyer status_code_$status_code'>";
	}
	elseif(current_user_can('seller'))
	{

		$short_message = "<div class='status_short for_seller status_code_$status_code'>";
		$short_message .= "<div class='status_badge'></div>";

		$verbose_message = "<div class='status_verbose for_seller status_code_$status_code'>";
	}

	switch($status_code)
	{
	case 1:
		// First status of all bids. When a buyer first places a bid
		
		if(current_user_can('add_bid'))
		{
			$short_message .= 'Waiting for response to offer';
			
			$verbose_message .= "<strong>Offer Sent:</strong> <span class='bid_blurb'>You will receive a notification email when then winery responds.</span>";
			if(isset($_GET['bid_sent']) && $_GET['bid_sent'] == true)
			{
				$verbose_message .= " <a href='" . get_post_type_archive_link('wine') . "' title='Continue to browse'>Continue to browse</a> or view the status of your offers on your <a href='" . vs_get_dash() . "' title='Accoutn dashboard'>account dashboard</a>.";
			}
		}
		elseif(current_user_can('seller'))
		{
			$short_message .= 'Accept or decline this offer';

			$verbose_message .= "<ul class='accept_bid_steps'>";
				$verbose_message .= "<li class='accept_bid_header'><strong>Offer received.</strong> <span class='bid_blurb'>Please accept or decline this offer.</span></li>";
				$verbose_message .= "<li class='accept_bid_button'>" . vs_get_step_button($bid) . "</li>";
			$verbose_message .= '</ul><!-- .accept_bid_steps -->';
		}
	break;
	case 2:
		// When a winery has accepted an offer
		$case_amount = get_post_meta($bid->ID, 'case_amount', true);
		$bottles_per_case = 12;
		$bid_total = get_post_meta($bid->ID, 'wine_price', true)
			* $bottles_per_case
			* $case_amount
			* get_post_meta($bid->ID, 'percentage', true);
			
		if(current_user_can('add_bid'))
		{
			$short_message .= 'Offer accepted: Send check';

			$verbose_message .= "<ul class='bid_accepted_steps'>";
				$verbose_message .= "<li class='bid_accepted_header'>Offer accepted!</li>";
				$verbose_message .= "<li class='bid_accepted_content'>";
					$verbose_message .= "<ul>";
						$verbose_message .= "<li class='bid_accepted_payment_note'><span class='bid_blurb'>Please send a check for " . money_format('%.2n', $bid_total) . " to:</span></li>";
						$verbose_message .= "<li class='bid_accepted_address'>" . vs_format_winery_address($winery) . "</li>";
						$verbose_message .= "<li class='bid_accepted_payment_sent_button'>" . vs_get_step_button($bid) . "</li>";
					$verbose_message .= '</ul>';
				$verbose_message .= "</li><!-- .bid_accepted_content -->";
			$verbose_message .= "</ul><!-- .bid_accepted_steps -->";
		}
		elseif(current_user_can('seller'))
		{
			$short_message .= 'Offer accepted: Awaiting payment';

			$verbose_message .= '<strong>Offer accepted:</strong> <span class="bid_blurb">You will be notified when the buyer has sent you a check for '. money_format('%.2n', $bid_total) . '. Please do not send the wine until the check has been received.</span>';
		}
	break;
	case 3: 
		// When a buyer says they've sent the check
		if(current_user_can('add_bid'))
		{
			$short_message .= 'Check sent to winery';

			$verbose_message .= '<strong>Payment sent: </strong> <span class="bid_blurb">You will receive notification when payment is received.</span>';
			$verbose_message .= vs_format_winery_address($winery);
		}
		elseif(current_user_can('seller'))
		{
			$short_message .= 'Have you recieved the check?';

			$verbose_message .= "<ul class='receive_payment_steps'>";
				$verbose_message .= "<li class='receive_payment_header'><strong>Payment is on the way!</strong> <span class='bid_blurb'>Please let us know when you have received it.</span></li>";
				$verbose_message .= "<li class='receive_payment_button'>" . vs_get_step_button($bid) . "</li>";
			$verbose_message .= '</ul><!-- .receive_payment_steps -->';
		}
		
	break;
	case 4:
		// When winery says they've recieved the check
		if(current_user_can('add_bid'))
		{
			$short_message .= 'Check received by winery';

			$verbose_message .= '<strong>Payment received: </strong> <span class="bid_blurb">You will be notified when the wine is on the way.</span><br />';
			$verbose_message .= vs_format_winery_address($winery);
		}
		elseif(current_user_can('seller'))
		{
			$short_message .= 'Have you sent the wine?';

			$verbose_message .= "<ul id='send_wine_steps'>";
				$verbose_message .= "<li id='send_wine_header'><strong>Payment received:</strong> <span class='bid_blurb'>it's great to be making money.</span></li>";
				$verbose_message .= "<li id='send_wine_address_wrapper'>";
					$verbose_message .= "<ul id='send_wine_address_and_button'>";
						$verbose_message .= "<li id='send_wine_address_and_address_header'>";
							$verbose_message .= "<ul>";
								$verbose_message .= "<li id='send_wine_address_header'>";
									$verbose_message .= "<strong>Please send the wine:</strong> <span class='bid_blurb'>";
									$cases = get_post_meta($bid->ID, 'case_amount', true);
									if($cases == 1)
										$verbose_message .= $cases . ' case of ';
									else
										$verbose_message .= $cases . ' cases of ';

									$verbose_message .= get_post_meta($wine_id, 'wine_year', true) . ' ';
									$verbose_message .= get_the_title(get_post_meta($bid->ID, 'wine_id', true)) . ' to:';
								$verbose_message .= '</span></li><!-- #send_wine_address_header -->';
								$verbose_message .= "<li id='send_wine_address'>" . vs_format_restaurant_address($bid->post_author) . "</li>";
							$verbose_message .= "</ul>";
						$verbose_message .= "</li><!-- #send_wine_address_and_address_header -->";
						$verbose_message .= "<li id='send_wine_button'>" . vs_get_step_button($bid) . "</li>";
					$verbose_message .= "</ul>";
				$verbose_message .= "</li><!-- send_wine_address_wrapper -->";
			$verbose_message .= "</ul><!-- #send_wine_steps -->";
		}
	break;
	case 5:
		// When winery says they've sent the wine
		if(current_user_can('add_bid'))
		{
			$short_message .= 'Have you recieved the wine?';

			$verbose_message .= '<strong>Your wine is on the way! </strong><span class="bid_blurb">Please let us know once it has arrived.</span>';
			$verbose_message .= vs_get_step_button($bid);
		}
		elseif(current_user_can('seller'))
		{
			$short_message .= 'Wine sent';

			$verbose_message .= "<ul id='finish_bid_steps'>";
				$verbose_message .= "<li id='finish_bid_info'>";
					$verbose_message .= "<strong>Wine sent:</strong> <span class='bid_blurb'>The buyer will be notified their wine is on the way.</span>";
					$verbose_message .= vs_format_restaurant_address($bid->post_author);
				$verbose_message .= "</li><!-- #finish_bid_info -->";
			$verbose_message .="</ul><!-- #finish_bid_steps -->";
		}
	break;
	case 6:
		// When buyer says they've received the wine
		$short_message .= 'Transaction completed';

		$verbose_message .= '<strong>This transaction is complete!</strong>';
	break;
	case 7:
		// When winery declines the bid
		if(current_user_can('add_bid'))
		{
			$short_message .= 'Offer declined';

			$verbose_message .= "<ul class='bid_declined'>";
				$verbose_message .= "<li class='bid_declined_header'><strong>Offer declined.</strong> <span class='bid_blurb'>This transaction is closed.</span></li>";
				$verbose_message .= "<li><span class='bid_blurb'>It’s not you - there are a lot of reasons an offer may be declined. Why not try again later or <a href='" . get_post_type_archive_link('wine') . "' title='browse wines'>start browsing</a> for another wine?</span></li>";
			$verbose_message .= '</ul><!-- .bid_declined -->';
		}
		elseif(current_user_can('seller'))
		{
			$short_message .= 'Offer declined';
			$verbose_message .= "<ul class='bid_declined'>";
					$verbose_message .= "<li class='bid_declined_header'><strong>Offer declined.</strong> <span class='bid_blurb'>This transaction is closed.</span></li>";
			$verbose_message .= '</ul><!-- .bid_declined -->';
		}
	break;
	default:
		// An error of some kind
		$short_message .= 'Unknown';

		$verbose_message .= 'Unknown';
	break;
	}

	$short_message .= "</a></div>";
	$verbose_message .= "</div>";

	if($verbose)
		return $verbose_message;
	else
		return $short_message;
}

function vs_get_step_button($bid)
{
	$status_code = get_post_meta($bid->ID, 'status_code', true);
	$form = "<form id='bid_step_$status_code' action='' method='post'>";
	$form .= "<input type='hidden' name='bid_step' value='$status_code' />";
	switch($status_code)
	{
	case 1:
		$nonce = wp_create_nonce('change_bid_' . $bid->ID);
		$form .= "<input type='hidden' name='_wpnonce' value='$nonce' />";
		$form .= "<div id='bid_image_buttons'>";
			$form .= "<div id='accept_bid_button' class='bid_image_button'>Accept</div>";
			$form .= "<div id='decline_bid_button' class='bid_image_button'>Decline</div>";
		$form .= "</div><!-- #bid_image_buttons -->";
		$form .= "<input type='submit' name='winery_choice' id='accept_bid_submit' value='Accept' />";
		$form .= "<input type='submit' name='winery_choice' id='decline_bid_submit' value='Decline' />";
	break;
	case 2:
		$nonce = wp_create_nonce('change_bid_' . $bid->ID);
		$form .= "<div id='bid_image_buttons'>";
			$form .= "<div id='payment_sent_button' class='bid_image_button'>Payment sent</div>";
		$form .= "</div><!-- #bid_image_buttons -->";
		$form .= "<input type='hidden' name='_wpnonce' value='$nonce' />";
		$form .= "<input type='submit' id='payment_sent_submit' value='Payment sent' />";
	break;
	case 3:
		$nonce = wp_create_nonce('change_bid_' . $bid->ID);
		$form .= "<div id='bid_image_buttons'>";
			$form .= "<div id='payment_received_button' class='bid_image_button'>Payment received</div>";
		$form .= "</div><!-- #bid_image_buttons -->";
		$form .= "<input type='hidden' name='_wpnonce' value='$nonce' />";
		$form .= "<input type='submit' id='payment_received_submit' value='Payment received' />";
	break;
	case 4:
		$nonce = wp_create_nonce('change_bid_' . $bid->ID);
		$form .= "<div id='bid_image_buttons'>";
			$form .= "<div id='wine_sent_button' class='bid_image_button'>Wine sent</div>";
		$form .= "</div><!-- #bid_image_buttons -->";
		$form .= "<input type='hidden' name='_wpnonce' value='$nonce' />";
		$form .= "<input type='submit' id='wine_sent_submit' value='Wine sent' />";
	break;
	case 5:
		$nonce = wp_create_nonce('change_bid_' . $bid->ID);
		$form .= "<div id='bid_image_buttons'>";
			$form .= "<div id='wine_received_button' class='bid_image_button'>Wine received</div>";
		$form .= "</div><!-- #bid_image_buttons -->";
		$form .= "<input type='hidden' name='_wpnonce' value='$nonce' />";
		$form .= "<input type='submit' id='wine_received_submit' value='Wine received' />";
	break;
	}
	$form .= "</form><!-- #bid_step_$status_code -->";
	return $form;
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
	return get_post_type_archive_link('bid');
}

function vs_sortable_header($title, $query_args)
{
	// First find out if this is the current sorting header
	global $wp_query;
	
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

// Send emails to users when status codes change
add_action('added_post_meta', 'vs_bid_status_change_notifier', 10, 4);
add_action('updated_post_meta', 'vs_bid_status_change_notifier', 10, 4);
function vs_bid_status_change_notifier($meta_id, $object_id, $meta_key, $meta_value)
{
	if(get_post_type($object_id) == 'bid')
	{
		if($meta_key == 'status_code')
		{

			$winery_users = vs_get_winery_users_by_bid($object_id);
			$restaurant_user = vs_get_restaurant_user_by_bid($object_id);

			$bid = get_post($object_id);
			$wine = get_post(get_post_meta($object_id, 'wine_id', true));

			$subject = "You have action in your Vinsource account!";
			$message = "This action is related to the offer on " . get_post_meta($wine->ID, 'wine_year', true) . ' ' . $wine->post_title;
			$message .= " (Transaction #$object_id)\r\n\r\n";
			$message .= "Please log in at "  . get_permalink($object_id) . " to view this transaction.\r\n";
			$message .= "Sincerely,\r\n";
			$message .= "The Vinsource Team";


			switch($meta_value)
			{
			// Bid sent
			case 1:
				foreach($winery_users as $user)
				{
					wp_mail($user->user_email, $subject, $message);
				}

			break;

			// Bid accepted
			case 2:
				wp_mail($restaurant_user->user_email, $subject, $message);

			break;

			// Check sent
			case 3:
				foreach($winery_users as $user)
				{
					wp_mail($user->user_email, $subject, $message);
				}


			break;

			// Check received
			case 4:
				wp_mail($restaurant_user->user_email, $subject, $message);

			break;

			// Wine sent
			case 5:
				wp_mail($restaurant_user->user_email, $subject, $message);

			break;

			// Wine received
			case 6:
				foreach($winery_users as $user)
				{
					wp_mail($user->user_email, $subject, $message);
				}
			break;
			}
		}
	}
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

function vs_can_winery_receive_payments($winery_id)
{
	// THis could get more complex if we need it to, but for now it just makes sure that there is a paypal email associated with the winery
	$paypal_email = get_post_meta($winery_id, 'winery_paypal_address', true);
	if($paypal_email) return true;
}

function vs_get_buyer_paypal_address($user_id)
{
	// THis could get more complex, but for now we just return the user's email address
	$user = get_userdata($user_id);
	return $user->user_email;
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


