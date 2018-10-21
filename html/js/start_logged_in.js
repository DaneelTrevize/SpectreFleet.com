'use strict';

const logged_in_URL = 'https://' + document.location.host + "/portal/logged_in_json";

var logged_in = false;
var dynamic_login = null;


function handle_logged_in( data ) {
	
	logged_in = data['is_logged_in'];
	
	dynamic_login.after( data['portal_dropdown_menu'] );
	dynamic_login.remove();
	dynamic_login = null;
	
	$( "#portal_dropdown_menu [data-submenu]" ).submenupicker();
	
}// handle_logged_in()


jQuery( document ).ready( function() {
	
	dynamic_login = $( "#navbar-dynamic-login" );
	
	dynamic_login.html( '<i class="fa fa-spinner fa-fw fa-spin" id="dynamic_login_spinner"></i>' );
	
	// send request
	$.getJSON( logged_in_URL, '', function( data ) {
		
		dynamic_login.html( '' );
		
		//console.log( data );
		
		handle_logged_in( data );
		
	} ); // end .getJSON()
	
});	// end .ready()