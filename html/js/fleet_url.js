
var csrf_hash;

var retry_timer;

var retry_period = 15000;	// 15 seconds, 4 times per minute
var auto_get = true;
var failure_count = 0;

function try_get_fleet_url() {
	
	$( "#no_fleet" ).hide();
	$( "#getting_fleet" ).show();
	
	// send request
	$.post( 'https://' + document.location.host + "/fleets2/get_fleet_url_json", 'csrf_test_name='+csrf_hash, function( data ) {
		csrf_hash = data['csrf_hash'];
		$( "input[type=hidden][name=csrf_test_name]" ).val( csrf_hash );
		
		if( data['fleet_id'] ) {
			// Success
			window.location.replace( 'https://' + document.location.host + "/fleets2/" );
			
		} else {
			failure_count += 1;
			
			if( failure_count >= 20 ) {	// 5 minutes, 4 times per minute, 20 attempts
				$( "#auto_get" ).bootstrapToggle('off');
			} else {
				// Try again later
				window.setTimeout( function() {
					// Display that we tried for at least 2 seconds
					if( auto_get ) {
						$( "#no_fleet" ).show();
						$( "#getting_fleet" ).hide();
					}
				}, 2000 );
			}
		}
	} ); // end .post()
	
}// end try_get_fleet_url()

jQuery( document ).ready( function() {
	
	$( document ).ajaxError( function( event, jqXHR, settings, thrownError ) {
		
		console.log( 'Unexpected error response code:' + jqXHR.status );
		window.clearInterval( retry_timer );
		$( "#getting_fleet" ).text( 'Unable to continue.' );
		
		window.location.replace( 'https://' + document.location.host + "/portal" );
		
	} );// end .ajaxError()
	
	$( window ).bind( 'beforeunload', function () {
		$( document ).unbind( 'ajaxError' );	// Don't handle "errors" caused by navigation away from this page during AJAX
	} ); // end .bind()
	
	try_get_fleet_url();
	retry_timer = window.setInterval( try_get_fleet_url, retry_period );
	
	$( "#auto_get" ).change( function() {
		
		auto_get = $( this ).prop( 'checked' );
		
		if( auto_get ) {
			failure_count = 0;
			$( "#auto_disabled" ).hide();
			$( "#getting_fleet" ).show();
			try_get_fleet_url();
			retry_timer = window.setInterval( try_get_fleet_url, retry_period );
		} else {
			window.clearInterval( retry_timer );
			$( "#no_fleet" ).hide();
			$( "#getting_fleet" ).hide();
			$( "#auto_disabled" ).show();
		}
	} ); // end .click()
	
});	// end .ready()