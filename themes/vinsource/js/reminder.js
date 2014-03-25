jQuery(document).ready(function($) {
	$('.reminder input').on('click', function(e) {
		e.preventDefault();
		var statusCode = $(this).parent().parent().find('.status_code').text();
		switch(statusCode)
		{
		// Cases to alert the winery
		case '1':
		case '3':
		case '4':
			var name = $(this).parent().parent().find('.winery').text();
		break;
		// Cases to alert the restaurant
		case '2':
		case '5':
			var name = $(this).parent().parent().find('.author a').text();

		break;
		}
		if(confirm('Are you sure you want to send a reminder to ' + name + '?'))
		{
			// Show the loader and hide the input
			$(this).parent().find('img').removeClass('hidden');
			$(this).addClass('hidden');
			var postID = $(this).attr('id');
			postID = postID.substring(9);
			reminderNonce = $(this).parent().find('.reminder_nonce').val();
			var input = $(this);

			// Get to the AJAX
			var data = {
				'action' : 'remind',
				'bidID' : postID,
				'nonce' : reminderNonce
			}

			$.get(ajaxurl, data, function(response) {
				if(response.status == 'success')
				{
					input.parent().find('img').addClass('hidden');
					input.removeClass('hidden');
					input.attr('disabled', 'disabled');
					input.parent().find('.last_reminder').text('Just now!');
				}
				else
				{
					input.parent().find('img').addClass('hidden');
					input.removeClass('hidden');
					alert('There was a problem communicating to the server. Please try again');
				}
			}, 'json');
		}

	});
});
