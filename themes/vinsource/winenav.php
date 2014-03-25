<ul id='wine_nav' class='menu'>
	<li id='browse' class='menu-item'>
		<a href='<?php echo get_post_type_archive_link('vs_product'); ?>' title='Browse All Wines'>Browse</a>
	</li>
	<li id='browse_varietals' class='menu-item'>
		<a href=''>Varietals</a>
		<ul class='submenu'>
			<?php
				$terms = get_terms(array('varietal'), array('orderby' => 'count', 'order' => 'DESC'));
				foreach($terms as $term)
				{
					?>
					<li>
						<a href='<?php echo add_query_arg('varietal', $term->slug, get_post_type_archive_link('vs_product')); ?>'><?php echo $term->name; ?></a>
					</li>
					<?php
				}
			?>
		</ul><!-- .submenu -->
	</li>
	<li id='browse_price_range' class='menu-item'>
		<a href=''>Price Range</a>
		<ul class='submenu'>
			<li><a href='<?php echo add_query_arg('max_price', 20, get_post_type_archive_link('vs_product')); ?>'>&lt; $20</a></li>
			<li><a href='<?php echo add_query_arg('max_price', 30, get_post_type_archive_link('vs_product')); ?>'>&lt; $30</a></li>
			<li><a href='<?php echo add_query_arg('max_price', 50, get_post_type_archive_link('vs_product')); ?>'>&lt; $50</a></li>
			<li><a href='<?php echo add_query_arg('max_price', 100, get_post_type_archive_link('vs_product')); ?>'>&lt; $100</a></li>
		</ul><!-- .submenu -->
	</li>
	<li id='browse_location' class='menu-item'>
		<a href=''>Appellation</a>
		<ul class='submenu'>
			<?php
			$terms = get_terms(array('wine_region'), array('orderby' => 'count', 'order' => 'DESC'));
			foreach($terms as $term)
				{
					?>
					<li>
						<a href='<?php echo add_query_arg('wine_region', $term->slug, get_post_type_archive_link('vs_product')); ?>'><?php echo $term->name; ?></a>
					</li>
					<?php
				}
			?>
		</ul><!-- .submenu -->
	</li>
</ul>
