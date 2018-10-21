
refresh_URL = 'https://' + document.location.host + "/fleets2/summary_json";

var ships_set = new Set();

function processQueue() {
	
	if( set_motd ) {
		ESI_set_motd();
		
	} else if( list_fits ) {
		ESI_list_fits();
		
	} else if( kick_banned ) {
		ESI_kick_banned();
		refresh_summary();
		
	} else {
		// No one-off ESI POST to call
		$( "#last_action" ).text( '' );
		
		if( manual_update ) {
			refresh_summary();
		}
		
		manage_loop_frequency();
		
	}// end else No ESI POST to call
	
}// end processQueue()

function handle_init( data ) {
	
	if( data['hasScheduleDetails'] ) {
		$( "#forget_details" ).parent().show();
		$( "#manage_fleet" ).show();
		$( "#choose_fleet" ).hide();
	
		$( "#third_row" ).show();
		$( "#forth_row" ).show();
	} else {
		$( "#forget_details" ).parent().hide();
		$( "#manage_fleet" ).hide();
		$( "#choose_fleet" ).show();
	}
	
	update_member_details( data['ships_to_members'], data['wing_names'], data['squad_names'], data['ship_names'], data['character_names'], data['system_names'] );
	
}// end handle_init()

function handle_refresh( data ) {
	
	update_member_details( data['ships_to_members'], data['wing_names'], data['squad_names'], data['ship_names'], data['character_names'], data['system_names'] );
	
}// end handle_refresh()

function update_member_details( ships_to_members, wing_names, squad_names, ship_names, character_names, system_names ) {
	
	var prior_ships = new Set( ships_set );	// Snapshot of prior ship types
	ships_set.clear();
	//console.log( "Prior: " + [...prior_ships] );
	
	for( var ship_id in ships_to_members ) {	// Display current shiptypes
		var fleet_members = ships_to_members[ship_id];
		
		let ship_name = ship_names[ship_id];
		
		ships_set.add( ship_id );
		
		var tbody_id = "ship_id_" + ship_id;

		// Does this ship_id already have a tbody?
		var existing_tbody = $( "#fleet_table > tbody#" + tbody_id );
		var was_hidden = false;
		if( existing_tbody.length ) {
			// Replace the existing tbody's content
			//console.log( "Replacing " + ship_id );
			
			was_hidden = $( "tr:not( .ship_id_header )", existing_tbody ).filter(":first").is(":hidden");
		}
		
		var tbody = '<tr class="ship_id_header">' +
			'<th colspan="7">' +
				'<div class="col-sm-2">' +
					'&nbsp;<i class="fa fa-angle-double-';
				tbody += (was_hidden) ? 'down' : 'up';
				tbody += ' fa-fw members_toggle"></i>&nbsp;' +
				'</div>' +
				'<div class="col-sm-1 aligncenter">' +
					'<img src="https://imageserver.eveonline.com/Type/' + ship_id + '_64.png" title="' + ship_name +
					'" class="img-rounded" style="height: 40px; width: 40px; margin-bottom: 0px;"> ' +
				'</div>' +
				'<div class="col-sm-5">' +
					'Ship:&nbsp;' + ship_name +
				'</div>' +
				'<div class="col-sm-2">' +
					'Count:&nbsp;<span style="font-size: 24px;">' + fleet_members.length + '</span>' +
				'</div>' +
				'<div class="col-sm-2 alignright">' +
					'&nbsp;<i class="fa fa-angle-double-';
				tbody += (was_hidden) ? 'down' : 'up';
				tbody += ' fa-fw members_toggle"></i>&nbsp;' +
				'</div>' +
			'</th>' +
		'</tr>';
		for( var item in fleet_members ) {
			
			var fleet_member = fleet_members[item];
			//console.log( fleet_member );
			
			let characterID = fleet_member['character_id'];
			let character_name = character_names[characterID];
			var station = fleet_member['station'];
			var wingID = fleet_member['wing_id'];
			var squadID = fleet_member['squad_id'];
			
			var hierarchy_role = '';
			if( fleet_member['role_name'] != 'Squad Member' ) {
				hierarchy_role = fleet_member['role_name'];
			}
			/*var docked_location = '';
			if( fleet_member['station_id'] != null ) {
				//docked_location = fleet_member['station']['name'];
				docked_location = fleet_member['station_id'];
			}*/
			
			tbody += '<tr';
			tbody += (was_hidden) ? ' hidden>' : '>';
			tbody += '<td>' + wing_names[wingID] + '</td>' +
			'<td>' + squad_names[squadID] + '</td>' +
			'<td>' + '<strong>' + character_name + '</strong>' + '</td>' +
			'<td>' + hierarchy_role + '</td>' +
			'<td class="aligncenter">' + system_names[fleet_member['solar_system_id']] + '</td>' +
			/*'<td class="aligncenter">' + docked_location + '</td>' +*/
			'<td class="aligncenter">' + (fleet_member['takes_fleet_warp'] ? '' : 'Yes') + '</td>' +
			'</tr>';
			
		} // end each fleet_members in ships_to_members[ship_id]
		
		if( existing_tbody.length ) {
			// Replace the existing tbody's content
			//console.log( "Replacing " + ship_id );
	
			existing_tbody.html( tbody );
		} else {
			// Add a new tbody
			//console.log( "Adding " + ship_id );
			
			$( "#fleet_table" ).append( '<tbody id="' + tbody_id + '">' + tbody + '</tbody>' );	// New ones at the bottom?
		}
		
		// Add click toggling to new or replaced tbodies
		$( "#fleet_table > tbody#" + tbody_id + " > tr.ship_id_header" ).click( function() {
			
			$( "i.members_toggle", $(this) ).toggleClass( "fa-angle-double-down" ).toggleClass( "fa-angle-double-up" );
			$( "tr:not( .ship_id_header )", $(this).parent() ).toggle();
		} ); // end .click()
		
	}// end for each ship_id in ships_to_members
	
	if( prior_ships.size > 0 )	// Check we're not during init
	{
		var remove_ships = new Set( [...prior_ships].filter( x => !ships_set.has(x) ) );
		//console.log( "Removing: " + [...remove_ships] );
		remove_ships.forEach( function( s ) {
			$( "#fleet_table > tbody#ship_id_" + s ).remove();
		} );// end forEach()
	}
	
}// end update_member_details()


jQuery( document ).ready( function() {
	
	initControlPanel();
	
	// Add click toggling for all tbodies
	$( "#members_toggle_down" ).click( function() {
		$( "#fleet_table > tbody > tr:not( .ship_id_header )" ).show();
		$( "i.members_toggle" ).removeClass( "fa-angle-double-down" ).addClass( "fa-angle-double-up" );
	} ); // end .click()
	$( "#members_toggle_up" ).click( function() {
		$( "#fleet_table > tbody > tr:not( .ship_id_header )" ).hide();
		$( "i.members_toggle" ).removeClass( "fa-angle-double-up" ).addClass( "fa-angle-double-down" );
	} ); // end .click()
	
});	// end .ready()