jQuery(document).ready(function($) {
	// Cycle stuff
	if($('.winery').length > 5)
	{
		$('#winery_browser').cycle({
			timeout: 0,
			speed: 300,
			fx: 'scrollHorz',
			next: '#next_wineries',
			prev: '#previous_wineries'
		});
		$('#previous_wineries, #next_wineries').show();

	}
	$('.winery_link').on('click', function(e) {
		e.preventDefault();
		$('.winery').removeClass('active_winery');
		$(this).parent().addClass('active_winery');

		$('#wine_browser').html(loadingGif);
		$('#wine_info').html('');

		var wineryID = $(this).attr('href');
		wineryID = wineryID.replace('#winery_', '');
		var data = {
			'action' : 'browse',
			'wineryID' : wineryID
		};
		if('searchBy' in window)
		{
			data['searchKey'] = searchBy['key'];
			data['searchValue'] = searchBy['value'];
		}


		$.get(ajaxurl, data, function(response) {
			$('#wine_browser').html(response);

			// More cycle stuff
			if($('.wine').length > 4)
			{
				$('#wine_browser').cycle({
					timeout: 0,
					speed: 300,
					fx: 'scrollHorz',
					next: '#next_wines',
					prev: '#previous_wines'
				});
				$('#previous_wines, #next_wines').show();
			}
			else
			{
				$('#previous_wines, #next_wines').hide();
			}

			$('.wine_link').on('click', function(e)
			{
				e.preventDefault();
				$('.wine').removeClass('active_wine');
				$(this).parent().addClass('active_wine');

				$('#wine_info').html(loadingGif);

				var wineID = $(this).attr('href');
				wineID = wineID.replace('#wine_', '');

				var wineData = {
					'action' : 'wine_info',
					'wineID' : wineID
				}
				$.get(ajaxurl, wineData, function(wineResponse)
				{
					$('#wine_info').html(wineResponse);
				});
			});

		});
	});

	$('.wine_link').on('click', function(e)
	{
		e.preventDefault();

		$('.wine').removeClass('active_wine');
		$(this).parent().addClass('active_wine');

		$('#wine_info').html(loadingGif);

		var wineID = $(this).attr('href');
		wineID = wineID.replace('#wine_', '');

		var wineData = {
			'action' : 'wine_info',
			'wineID' : wineID
		}
		$.get(ajaxurl, wineData, function(wineResponse)
		{
			$('#wine_info').html(wineResponse);
			$('#bid_button').bind('hover', function() {
				$('#bid_button img').attr('src', 'http://www.vinsourceonline.com/beta/wp-content/themes/vinsource/images/register_arrow_hover.png');	
			},
			function() {
				$('#bid_button img').attr('src', 'http://www.vinsourceonline.com/beta/wp-content/themes/vinsource/images/register_arrow.png');	
			});
		});
	});

	$('.winery_link').first().click();

});
