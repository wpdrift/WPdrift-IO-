var URL = require('url-parse');

(function($) {
    'use strict';

    $("body").on("click", "a", function(event) {
		event.preventDefault();

		var $link = $( this );
		var url = $link.attr( 'href' );
		var parsed_url = new URL( url );

		if ( parsed_url.host == wpdrift_worker.hit.host ) {
			window.open( url, '_self' );
		} else {
			window.open( url, '_blank' );

			var data = {
				'action': 'wpdrift_worker_record_click',
				'hit': wpdrift_worker.hit,
				'host': parsed_url.host,
				'url': parsed_url.href
			};

			$.post( wpdrift_worker.ajaxurl, data );
		}
    });

})(jQuery);
