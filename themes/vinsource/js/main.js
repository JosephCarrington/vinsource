jQuery(document).ready(function($) {

	$('#already_registered').on('click', function(e)
	{
		e.preventDefault();
		$('#front_login').show();
		$('#already_registered').addClass('hidden');
		$('#registration_forms > li').css('display', 'none');
	});
	
	$('#create_account').click(function(e)
	{
		e.preventDefault();
		var destination = $('#account_type').val();
		if(destination == 0)
		{
			alert('Please select an account type.');
		}
		else
		{
			var destination_id = '';
			switch(destination)
			{
			case 'winery':
				destination_id = '#gform_widget-2';
			break;
			case 'retail':
				destination_id = '#gform_widget-3';
			break;
			}
			if(destination == 'events')
			{
				$('#front_login').show();
				$('#already_registered').addClass('hidden');
				$('#registration_forms > li').css('display', 'none');
				var fancyContent = $('#paypal_link_info').html();
				$.fancybox({
					content : fancyContent
				});
			}
			else
			{
				$('#front_login').hide();
				$('#already_registered').removeClass('hidden');
				$('#registration_forms > li').css('display', 'none');
				$(destination_id).css('display', 'block');
				$(destination_id).ScrollTo();
			}
		}
	});

	$('#browse_varietals > a, #browse_price_range > a, #browse_location > a').click(function(e)
	{
		e.preventDefault();

		$('#browse_varietals, #browse_price_range, #browse_location').removeClass('clicked');
		$(this).parent().toggleClass('clicked');
			
	});
	$('#register').hover(function() {
		$('#register img').attr('src', 'http://www.vinsourceonline.com/beta/wp-content/themes/vinsource/images/register_arrow_hover.png');	
	},
	function() {
		$('#register img').attr('src', 'http://www.vinsourceonline.com/beta/wp-content/themes/vinsource/images/register_arrow.png');	
	});

	if(supports_input_placeholder())
	{
		if(!$('body').hasClass('home'))
		{
			$('.login-username label').hide();
			$('.login-username input').attr('placeholder', 'Username');
			$('.login-password label').hide();
			$('.login-password input').attr('placeholder', 'Password');
		}
	}

	/*$('.login-submit input').addClass('hidden');
	$('.login-submit').append("<div id='submit'></div>");
	$('#submit').on('click', function() {
		$('#loginform').submit();
	});
	*/

	$('.show_discount_button').on('click', function(e)
	{
		e.preventDefault();
		$('#discount_table').toggle();
	});
});

function supports_input_placeholder() {
	var i = document.createElement('input');
	return 'placeholder' in i;
}
