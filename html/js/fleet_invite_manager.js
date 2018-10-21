
refresh_URL = 'https://' + document.location.host + "/fleets2/invite_manager_json";

var can_fit_all_invites = false;

var send_invites = false;
var auto_invite = false;

var new_awaiting_set = new Set();
var awaiting_invites_set = new Set();
var sent_invites_set = new Set();
var recent_cancels_set = new Set();
var tbody_map = new Map();

function processQueue() {
	
	if( set_motd ) {
		ESI_set_motd();
		
	} else if( list_fits ) {
		ESI_list_fits();
		
	} else if( kick_banned ) {
		ESI_kick_banned();
		refresh_summary();
		
	} else if( send_invites ) {
		ESI_send_invites();
		refresh_summary();
		
	} else {
		// No one-off ESI POST to call
		$( "#last_action" ).text( '' );
		
		if( auto_invite ) {
			// Not quite perfect logic for trying to invite after having recently been clicked if there wasn't capacity...
			if( fast_loop_count == 3 || slow_loop_count == 11 ) {
				//console.log( 'Would auto_invite now...' );
				$( "#sending_invites" ).fadeToggle();
				ESI_send_invites();
				refresh_summary();
			} else if( manual_update ) {
				refresh_summary();
			}
		} else if( manual_update ) {
			refresh_summary();
		}
		
		manage_loop_frequency();
		
	}// end else No ESI POST to call
	
}// end processQueue()

function handle_init( data ) {
	
	$( "#forget_details" ).parent().show();
	$( "#view_summary" ).show();
	
	$( "#third_row" ).show();
	$( "#forth_row" ).show();
	$( "#send_invites" ).parent().show();
	$( "#auto_invites_area" ).show();
	
	$( "#invites_table" ).show();
	update_invite_details( members_space, data['currentEveTime'], data['invite_requests_count'], data['awaiting_invites'] );
	
}// end handle_init()

function handle_refresh( data ) {
	
	update_invite_details( members_space, data['currentEveTime'], data['invite_requests_count'], data['awaiting_invites'] );
	
}// end handle_refresh()

function update_invite_details( members_space, currentEveTime, invite_requests_count, awaiting_invites ) {
	
	//var currentEveTime_date = new Date( currentEveTime );
	
	can_fit_all_invites = invite_requests_count <= members_space;
	$( "#invite_requests_count" ).text( invite_requests_count );
	var awaiting_invites_count = Object.keys(awaiting_invites).length;
	$( "#awaiting_invites_count" ).text( awaiting_invites_count );
	$( "#can_fit_all_invites" ).text( can_fit_all_invites ? 'Yes' : 'No' );
	
	var tbody_new_awaiting = { html: '' };
	var tbody_awaiting_invites = { html: '' };
	var tbody_sent_invites = { html: '' };
	var tbody_recent_cancels = { html: '' };
	
	for( var CharacterID in awaiting_invites ) {
		
		var tr = '';
		
		var invite_class = 'invite_awaiting';
		
		var character = awaiting_invites[CharacterID];
		//console.log( character );
		if( tbody_map.has( CharacterID ) ) {
			( tbody_map.get( CharacterID ) ).delete( CharacterID );
			awaiting_invites_set.add( CharacterID );
			tbody_map.set( CharacterID, awaiting_invites_set );
		} else {
			invite_class = 'invite_new';
			new_awaiting_set.add( CharacterID );
			tbody_map.set( CharacterID, new_awaiting_set );
		}
		
		var target_tbody = tbody_awaiting_invites;
		/*if( response != null ) {
			target_tbody = tbody_sent_invites;
		}*/
		var invite_result = '';
		var response = character['response'];
		if( response != null ) {
			/*( tbody_map.get( CharacterID ) ).delete( CharacterID );
			sent_invites_set.add( CharacterID );
			tbody_map.set( CharacterID, sent_invites_set );*/
			if( response == 204 )
			{
				invite_class = 'invite_sent';
				invite_result = 'Invite sent';
			}
			else if( response == 520 )
			{
				invite_class = 'invite_rejected';
				invite_result = 'Invite rejected';
			}
			else
			{
				invite_result = 'Unexpected: ' + response;
			}
		}
		
		tr = '<tr id="char_' + CharacterID + '">' +
		'<td>' + character['CharacterName'] + '</td>' +
		'<td class="aligncenter">' + character['invitesSent'] + '</td>' +
		'<td class="aligncenter">' + character['sinceLastInviteSent'] + '</td>' +
		'<td class="' + invite_class + '">' + invite_result + '</td>' +
		'</tr>';
		
		target_tbody.html += tr;
		
	} // end each character in awaiting_invites
	
	$( "#invites_table > tbody#new_awaiting" ).html( tbody_new_awaiting.html );
	$( "#invites_table > tbody#awaiting_invites" ).html( tbody_awaiting_invites.html );
	$( "#invites_table > tbody#sent_invites" ).html( tbody_sent_invites.html );
	$( "#invites_table > tbody#recent_cancels" ).html( tbody_recent_cancels.html );
	
	
	if( tbody_map.size == 0 ) {
		$( "#invites_table" ).hide();
		$( "#invites_none" ).show();
	} else {
		$( "#invites_table" ).show();
		$( "#invites_none" ).hide();
	}
	
}// end update_invite_details()


function ESI_send_invites() {
	
	$( "#last_action" ).text( 'Sending invites...' );
	
	$.post( 'https://' + document.location.host + "/fleets2/send_invites_json", 'csrf_test_name=' + csrf_hash, function( data ) {
		
		//console.log( data );
		csrf_hash = data['csrf_hash'];
		
		$( "#last_action" ).text( 'Invites sent.' );
		$( "#sending_invites" ).fadeToggle();
		send_invites = false;
		$( "#send_invites" ).prop( 'disabled', false );
		
	} ) // end .post()
	.fail( function( jqXHR, textStatus, errorThrown ) {
		if( jqXHR.status == 409 ) {
			
			console.log( 'Error sending invites. Potentially less empty member positions exist than our last cache update shows us.' );
			csrf_hash = jqXHR.responseJSON['csrf_hash'];
			
			$( "#last_action" ).text( 'Invites not sent, either there were no awaiting invitees or insufficient space in fleet.' );
			$( "#sending_invites" ).fadeToggle();
			send_invites = false;
			$( "#send_invites" ).prop( 'disabled', false );
			
		}
	} ) // end .fail()
	
}// end ESI_send_invites()


jQuery( document ).ready( function() {
	
	$( "#send_invites" ).click( function ( e ) {
		e.preventDefault();
		
		if( !send_invites ) {
			$( "#send_invites" ).prop( 'disabled', true );
			send_invites = true;
			$( "#sending_invites" ).fadeToggle();
			$( this ).transfer( { to: queue_display } );
			resetCounts();
		} else {
			console.log( 'Already scheduled to send invites.' );
		}
		
	} ); // end .click()
	
	$( '#auto_invites' ).change( function() {
		
		auto_invite = $( this ).prop( 'checked' );
		//console.log( 'Invite automation: ' + auto_invite );
		
		$( "#auto_inviting > i" ).toggleClass( "fa-spin" );
		if( auto_invite ) {
			$( "#auto_inviting" ).fadeToggle();
			$( '#auto_invites_area' ).transfer( { to: queue_display } );
			resetCounts();
		} else {
			$( "#auto_inviting" ).fadeToggle();
			queue_display.transfer( { to: $( '#auto_invites_area' ) } );
		}
		
    } ); // end .change()
	
	initControlPanel();
	
});	// end .ready()