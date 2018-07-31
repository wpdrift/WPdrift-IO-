var URL = require('url-parse');

(function($) {
    'use strict';

    // console.log(wpdrift_io);

    $("body").on("click", "a", function(event) {
		event.preventDefault();

		var $link = $( this );
		var url = $link.attr( 'href' );
		var parsed_url = new URL( url );

		if ( parsed_url.host == wpdrift_io.hit.domain ) {
			window.open( url, '_self' );
		} else {
			window.open( url, '_blank' );

			var data = {
				'action': 'record_click',
				'hit': wpdrift_io.hit,
				'host': parsed_url.host,
				'url': parsed_url.href
			};

			$.post( wpdrift_io.ajaxurl, data, function( response ) {
				console.log( response );
			});
		}

		// console.log( parsed_url.host );
		// console.log( wpdrift_io.hit.domain );
    });

})(jQuery);
