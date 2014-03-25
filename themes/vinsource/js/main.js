jQuery(document).ready(function($) {

	$('#browse_varietals > a, #browse_price_range > a, #browse_location > a').click(function(e)
	{
		e.preventDefault();

		$('#browse_varietals, #browse_price_range, #browse_location').removeClass('clicked');
		$(this).parent().toggleClass('clicked');
			
	});
	$('#slides').cycle();
	$('#register').hover(function() {
		$('#register img').attr('src', 'http://www.vinsourceonline.com/beta/wp-content/themes/vinsource/images/register_arrow_hover.png');	
	},
	function() {
		$('#register img').attr('src', 'http://www.vinsourceonline.com/beta/wp-content/themes/vinsource/images/register_arrow.png');	
	});

	if(supports_input_placeholder())
	{
		$('.login-username label').hide();
		$('.login-username input').attr('placeholder', 'Username');
		$('.login-password label').hide();
		$('.login-password input').attr('placeholder', 'Password');
	}

	$('.login-submit input').addClass('hidden');
	$('.login-submit').append("<div id='submit'></div>");
	$('#submit').on('click', function() {
		$('#loginform').submit();
	});

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
