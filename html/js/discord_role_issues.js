'use strict';

jQuery( document ).ready( function() {
	
	//$( "#show_all_users" ).prop( 'disabled', true );
	
	// Add click toggling for identified users
	$( "#show_all_users" ).click( function() {
		$( "tr.identified_user" ).show();
		/*$( "#show_all_users" ).prop( 'disabled', true );
		$( "#show_only_issues" ).prop( 'disabled', false );*/
	} ); // end .click()
	$( "#show_only_issues" ).click( function() {
		$( "tr.identified_user:not( .role_issues )" ).hide();
		/*$( "#show_only_issues" ).prop( 'disabled', true );
		$( "#show_all_users" ).prop( 'disabled', false );*/
	} ); // end .click()
	
});	// end .ready()