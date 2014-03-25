jQuery(document).ready(function($) {
	$('#bid_submit_button').addClass('hidden');
	$('#bid_submit_image').on('click', function() {
		$('#bid').submit();
	});

	$('#percentage_srp, #case_amount').change(function() {
		
		var selectedPercent = $('#percentage_srp').val();
		var percentToDisplay = parseFloat(selectedPercent);
		percentToDisplay = percentToDisplay * 100;
		percentToDisplay = percentToDisplay.toFixed(0);
		$('#manual_percentage').val(percentToDisplay + '%');
		var cases = $('#case_amount').val();
		if(selectedPercent != 'label')
		{
			
			$('#bid_submit_image').removeClass('hidden');
			var srp = $('#srp').text();
			var pricePerBottle = srp * selectedPercent;
			$('#price_per_bottle span').text(pricePerBottle.toFixed(2));

			var bottlesInCase = 12;
			var pricePerCase = (pricePerBottle * bottlesInCase);
			$('#price_per_case span').text(pricePerCase.toFixed(2));

			$('#total_cases').text(cases);
			$('#total_price_per_case').text('$' + pricePerCase.toFixed(2));

			var totalBottles = bottlesInCase * cases;
			$('#total_bottles').text(totalBottles);
			$('#total_price_per_bottle').text('$' + pricePerBottle.toFixed(2));

			var totalPrice = (pricePerBottle * bottlesInCase) * cases;
			$('#total span').text('$' + totalPrice.toFixed(2));

			$('#total_feedback').show();
		}
		else
		{
			$('#total_feedback').hide();
			$('#bid_submit_image').addClass('hidden');
			$('#price_per_bottle span').text('');
			$('#price_per_case span').text('');
		}

	});
	// This part is for using images as buttons and gaving confirmations
	$('#bid_image_buttons').show();
	$('#bid_step_1, #bid_step_2, #bid_step_3, #bid_step_4, #bid_step_5').find('input').hide();

	$('.bid_image_button').on('click', function()
	{
		switch($(this).attr('id'))
		{
		case 'accept_bid_button' :
			if(confirm('Are you sure you want to accept this offer?'))
			{
				$('#accept_bid_submit').click();
			}
		break;
		case 'decline_bid_button' :
			if(confirm('Are you sure you want to decline this offer?'))
			{
				$('#decline_bid_submit').click();
			}
		break;
		case 'payment_sent_button' :
			if(confirm('Are you sure you have sent the payment?'))
			{
				$('#payment_sent_submit').click();
			}
		break;
		case 'payment_received_button' :
			if(confirm('Are you sure you have received the payment?'))
			{
				$('#payment_received_submit').click();
			}
		break;
		case 'wine_sent_button' :
			if(confirm('Are you sure you have sent the wine?'))
			{
				$('#wine_sent_submit').click();
			}
		break;
		case 'wine_received_button' :
			if(confirm('Are you sure you have received the wine?'))
			{
				$('#wine_received_submit').click();
			}
		break;
		}
	});

});
