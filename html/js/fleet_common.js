
var csrf_hash;

var queue_timer;
var fast_loop_count = 0;
var slow_loop_count = 0;

var refresh_URL;
var updating_display;
var queue_display;

var members_space = 0;
var kick_banned = false;
var set_motd = false;
var list_fits = false;
var manual_update = false;


function initControlPanel() {
	
	// send request
	$.getJSON( refresh_URL, '', function( data ) {
		
		//console.log( data );
		
		handle_common( data );
		
		handle_init( data );
		
		$( "#control_panel_cover" ).hide();
		$( "#control_panel" ).show();
		
		restartQueue();
	} ); // end .getJSON()
}// end initControlPanel()

function handle_common( data ) {
	
	csrf_hash = data['csrf_hash'];
	
	// format and output result
	members_space = data['members_space'];
	$( "#member_count" ).text( data['member_count'] );
	$( "#members_space" ).text( members_space );
	
	if( data['hasPinged'] ) {
		$( "#ping_discord" ).prop('disabled', true)	// Only permit 1 ping attempt
		$( "#ping_discord" ).parent().hide();
		$( "#have_pinged_discord" ).parent().show();
	} else {
		$( "#ping_discord" ).parent().show();
		//$( "#have_pinged_discord" ).parent().hide();
	}
		
	if( !data['hasDoctrine'] ) {
		$( "#list_fits" ).prop('disabled', true)
		$( "#list_fits" ).parent().hide();
		$( "#no_fits" ).parent().show();
	} else {
		$( "#list_fits" ).parent().show();
		$( "#no_fits" ).parent().hide();
	}
	
}// end handle_common()

function restartQueue() {
	// Stop the existing Queue loop, call it immediately, and restart a timer so it resumes 5secs from now?
	window.clearInterval( queue_timer );
	resetCounts();
	processQueue();
	queue_timer = window.setInterval( processQueue, 5000 );
	$( "#update_speed" ).text( 'Update period: 5 seconds' );
}// end restartQueue()

function resetCounts() {
	fast_loop_count = 3;
	slow_loop_count = 0;
}// end resetCounts()

function manage_loop_frequency() {
	
	if( fast_loop_count > 0 ) {
		fast_loop_count -= 1;
		// Doing fast updates
		$( "#update_speed" ).text( 'Update period: 5 seconds' );
		refresh_summary();
	} else {
		slow_loop_count += 1;
		if( slow_loop_count == 1 ) {
			// Start to slow updates
			$( "#update_speed" ).text( 'Update period: 60 seconds' );
			refresh_summary();
		} else if( slow_loop_count >= 12 ) {
			// It's been a minute since reset
			slow_loop_count = 0;
		}
	}
	
}// end manage_loop_frequency()

function refresh_summary() {
	
	updating_display.fadeIn();
	$( "i", updating_display ).addClass( "fa-spin" );
	
	// send request
	$.getJSON( refresh_URL, '', function( data ) {
		
		manual_update = false;
		
		$( "i", updating_display ).removeClass( "fa-spin" );
		updating_display.fadeOut();
		
		//console.log( data );
		
		handle_common( data );
		
		handle_refresh( data );
		
	} ); // end .getJSON()
	
}// end refresh_summary()


function ESI_kick_banned() {
	
	$( "#last_action" ).text( 'Kicking banned members...' );
	
	$.post( 'https://' + document.location.host + "/fleets2/kick_banned_json", 'csrf_test_name=' + csrf_hash, function( data ) {
		
		//console.log( data );
		csrf_hash = data['csrf_hash'];
		
		$( "#last_action" ).text( 'Kicked banned members.' );
		$( "#kicking_banned" ).fadeToggle();
		kick_banned = false;
		$( "#kick_banned" ).prop( 'disabled', false );
		
	} ) // end .post()
	
}// end ESI_kick_banned()

function ESI_set_motd() {
	
	$( "#last_action" ).text( 'Setting MOTD...' );
	
	$.post( 'https://' + document.location.host + "/fleets2/set_fleet_motd_json", 'csrf_test_name=' + csrf_hash, function( data ) {
		
		//console.log( data );
		csrf_hash = data['csrf_hash'];
		
		$( "#last_action" ).text( 'MOTD set.' );
		$( "#setting_MOTD" ).fadeToggle();
		set_motd = false;
		$( "#set_motd" ).prop( 'disabled', false );
		
	} ) // end .post()
	
}// end ESI_set_motd()

function ESI_list_fits() {
	
	$( "#last_action" ).text( 'Listing fits...' );
	
	$.post( 'https://' + document.location.host + "/fleets2/list_fits_json", 'csrf_test_name=' + csrf_hash, function( data ) {
		
		//console.log( data );
		csrf_hash = data['csrf_hash'];
		
		$( "#last_action" ).text( 'Fits listed.' );
		$( "#listing_fits" ).fadeToggle();
		list_fits = false;
		$( "#list_fits" ).prop( 'disabled', false );
		
	} ) // end .post()
	.fail( function( jqXHR, textStatus, errorThrown ) {
		if( jqXHR.status == 403 ) {
			
			console.log( 'Error listing fits.' );
			csrf_hash = jqXHR.responseJSON['csrf_hash'];
			
			$( "#last_action" ).text( 'Fits not listed in fleet chat, possibly there was a problem finding the fits for the doctrine ID.' );
			$( "#listing_fits" ).fadeToggle();
			list_fits = false;
			$( "#listing_fits" ).prop( 'disabled', false );
			
		}
	} ) // end .fail()
	
}// end ESI_list_fits()


jQuery( document ).ready( function() {
	
	$( document ).ajaxError( function( event, jqXHR, settings, thrownError ) {
		if( jqXHR.status == 403 || jqXHR.status == 409 ) {
			if( jqXHR.status == 403 ) {
				console.log( 'Permission denied.' );
			} else if( jqXHR.status == 409 ) {
				console.log( 'Conflict. No space in fleet?' );
			}
			
			if( jqXHR.hasOwnProperty( 'responseJSON' ) ) {
				console.log( 'Able to continue.' );
				csrf_hash = jqXHR.responseJSON['csrf_hash'];
				
				// Might enable buttons early?
				$( "#set_motd" ).prop( 'disabled', false );
				$( "#send_invites" ).prop( 'disabled', false );
				
				return;
			}
		}
		
		console.log( 'Unexpected error response code:' + jqXHR.status );
		window.clearInterval( queue_timer );
		$( "#update_speed" ).text( 'Page not updating' );
		
		window.location.replace( 'https://' + document.location.host + "/portal" );
		
	} );// end .ajaxError()
	
	$( window ).bind( 'beforeunload', function () {
		$( document ).unbind( 'ajaxError' );	// Don't handle "errors" caused by navigation away from this page during AJAX
	} ); // end .bind()
	
	
	$( "#forget_url" ).click( function ( e ) {
		e.preventDefault();
		
		$.post( 'https://' + document.location.host + "/fleets2/forget_fleet_json", 'csrf_test_name=' + csrf_hash, function( data ) {
			
			//console.log( data );
			csrf_hash = data['csrf_hash'];
			
			window.location.replace( 'https://' + document.location.host + "/portal" );
			
		} ) // end .post()
	} ); // end .click()
	
	$( "#forget_details" ).click( function ( e ) {
		e.preventDefault();
		
		$.post( 'https://' + document.location.host + "/fleets2/forget_scheduledDetails_json", 'csrf_test_name=' + csrf_hash, function( data ) {
			
			//console.log( data );
			csrf_hash = data['csrf_hash'];
			
			window.location.replace( 'https://' + document.location.host + "/fleets2/summary" );
			
		} ) // end .post()
	} ); // end .click()
	
	$( "#kick_banned" ).click( function ( e ) {
		e.preventDefault();
		
		if( !kick_banned ) {
			$( this ).prop( 'disabled', true );
			kick_banned = true;
			$( "#kicking_banned" ).fadeToggle();
			$( this ).transfer( { to: queue_display } );
			resetCounts();
		} else {
			console.log( 'Already scheduled to kick banned.' );
		}
		
	} ); // end .click()
	
	$( "#set_motd" ).click( function ( e ) {
		e.preventDefault();
		
		if( !set_motd ) {
			$( "#set_motd" ).prop( 'disabled', true );
			set_motd = true;
			$( "#setting_MOTD" ).fadeToggle();
			$( this ).transfer( { to: queue_display } );
			resetCounts();
		} else {
			console.log( 'Already scheduled to set MOTD.' );
		}
		
	} ); // end .click()
	
	$( "#ping_discord" ).click( function ( e ) {
		e.preventDefault();
		
		$( this ).prop('disabled', true)	// Only permit 1 ping attempt
		$( this ).parent().hide();
		$( "#have_pinged_discord" ).parent().show();
		$( "#pinging_Ops" ).fadeToggle();
		$( "#have_pinged_discord" ).transfer( { to: queue_display } );
		
		$.post( 'https://' + document.location.host + "/fleets2/ping_discord_json", 'csrf_test_name=' + csrf_hash, function( data ) {
			
			$( "#pinging_Ops" ).fadeToggle();
			//console.log( data );
			csrf_hash = data['csrf_hash'];
			
		} ) // end .post()
		.fail( function( jqXHR, textStatus, errorThrown ) {
			if( jqXHR.status == 502 ) {
				console.log( 'There was a problem with pinging Discord.' );
				csrf_hash = jqXHR.responseJSON['csrf_hash'];
			}
		} ) // end .fail()
		
	} ); // end .click()
	
	$( "#list_fits" ).click( function ( e ) {
		e.preventDefault();
		
		if( !list_fits ) {
			$( "#list_fits" ).prop( 'disabled', true );
			list_fits = true;
			$( "#listing_fits" ).fadeToggle();
			$( this ).transfer( { to: queue_display } );
			resetCounts();
		} else {
			console.log( 'Already scheduled to list fits.' );
		}
		
	} ); // end .click()
	
	$( "#manual_update" ).click( function ( e ) {
		e.preventDefault();
		
		if( !manual_update ) {
			//$( this ).prop( 'disabled', true );
			manual_update = true;
			//$( "#manually_updating" ).fadeToggle();
			$( this ).transfer( { to: queue_display } );
			//resetCounts();
		} else {
			console.log( 'Already scheduled to manually update.' );
		}
		
	} ); // end .click()
	
	$( '#scheduled_toggle' ).parent().click( function() {
		
		$( '#scheduled_toggle' ).toggleClass( "fa-angle-double-down" );
		$( '#scheduled_toggle' ).toggleClass( "fa-angle-double-up" );
		$( '#scheduled_details' ).slideToggle( 'fast' );
		
	} ); // end .click()
	
	updating_display = $( "#updating" );
	queue_display = $( "#queue" );
	
});	// end .ready()