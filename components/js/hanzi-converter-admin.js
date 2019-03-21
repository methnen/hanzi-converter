var hanzi_converter_admin = {};

(function( $ ) {
	'use strict';

	// Start things up
	hanzi_converter_admin.init = function() {
		$('#hanzi-converter-convert' ).on('click', function(event) {
			event.preventDefault();

			hanzi_converter_admin.get_conversion();
		});
	};

	// Get the Hanzi value and submit it for conversion
	hanzi_converter_admin.get_conversion = function() {
		var hanzi = $( '#hanzi-converter-hanzi-value' ).val();

		var request = $.ajax({
			url: 'admin-ajax.php?action=hanzi_converter',
			type: 'POST',
			data: {
				hanzi: hanzi
			},
			cache: false,
			dataType: 'json',
		});

		request.done( function( response ) {
			if ( true !== response.success ) {
				$( '#hanzi-converter-error' ).text( response.data );
				return false;
			}

			$( '#hanzi-converter-hanzi-conversion' ).val( response.data );
		});
	};

	$( function() {
		hanzi_converter_admin.init();
	} );
})( jQuery );