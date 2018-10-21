
jQuery( document ).ready( function() {
	
	$( '#hide_portal_menu' ).click( function() {
		$( '#portal_menu' ).hide().removeClass( "col-sm-3" );
		$( '#portal_content' ).removeClass( "col-sm-9" );
		$( '#portal_content' ).addClass( "col-sm-12" );
		$( this ).hide();
		$( '#show_portal_menu' ).show();
	} ); // end .click()
	
	$( '#show_portal_menu' ).click( function() {
		$( '#portal_content' ).removeClass( "col-sm-12" );
		$( '#portal_content' ).addClass( "col-sm-9" );
		$( '#portal_menu' ).addClass( "col-sm-3" ).show();
		$( this ).hide();
		$( '#hide_portal_menu' ).show();
	} ); // end .click()
	
});	// end .ready()
