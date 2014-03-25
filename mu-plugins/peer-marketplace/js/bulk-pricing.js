jQuery('document').ready(function($)
{
	$('#case_amount_select, #btg_placement').change(function()
	{
		var caseAmount = $('#case_amount_select').val();
		var BTGPricing = $('#btg_placement').is(':checked') ? true : false;
		var priceDisplay = $('.case_amount em');
		// Hacky!
		while(caseAmount > 0 && caseAmount != 1 && caseAmount != 2 && caseAmount != 3 && caseAmount != 5 && caseAmount != 10)
		{
			caseAmount--;
		}
		
		var priceToDisplay = BTGPricing ? bulk_pricing_data.btg[caseAmount] : bulk_pricing_data.regular[caseAmount];
		priceToDisplay = parseFloat(priceToDisplay);
		priceDisplay.text('$' + priceToDisplay.toFixed(2));
	});
});
