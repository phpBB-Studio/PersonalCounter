(function($) { // Avoid conflicts with other libraries

	'use strict';

	$(function() {
		/*
		 * jQuery datepicker by fengyuanchen
		 * https://github.com/fengyuanchen/datepicker
		 */
		$('#pc_date:not([data-pc-date-disabled])').datepicker({
			// The dateformat to output
			autoShow: false,
			autoHide: true,
			autoPick: false,
			inline: true,
			format: 'dd-mm-yyyy',
			weekStart: 1,
			yearFirst: false,
			startDate: false,
			endDate: true,
			language: 'en-GB',
		});

		$('#pc_date_reset').on('click', function(event) {
			// We have to prevent the default action when clicking a <button>
			event.preventDefault();

			// Reset the datepicker
			$('#pc_date').datepicker('setDate', '').val('');
		});
	});

})(jQuery); // Avoid conflicts with other libraries
