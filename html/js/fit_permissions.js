'use strict';

var csrf_name;
var csrf_hash;

var fit_id;

const fit_json_URL = 'https://' + document.location.host + '/doctrine/fit_json/';

jQuery( document ).ready( function() {
	
	// send request
	$.getJSON( fit_json_URL+fit_id, '', function( data ) {
		
		//console.log( data );
		
		csrf_name = data['csrf_name'];
		csrf_hash = data['csrf_hash'];
		$( "input[type=hidden][name="+csrf_name+"]" ).val( csrf_hash );
		
		if( data['can_have_fits'] ) {
			$( "#fc_options" ).show();
			$( "#can_have_fits" ).show();
		}
		if( data['can_modify_fit'] ) {
			$( "#can_modify_fit" ).show();
		}
		if( data['can_modify_status'] ) {
			if( data['status'] == 'Public' ) {
				$( "#status_public" ).show();
			} else if( data['status'] == 'Official' ) {
				$( "#status_official" ).show();
			}
		}
		
	} ); // end .getJSON()
	
});	// end .ready()