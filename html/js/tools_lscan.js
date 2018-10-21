'use strict';

var toggled_corporation_id = null;
var toggled_alliance_id = null;
var toggled_faction_id = null;
var CLICK_REPLACES_HIGHLIGHTED = true;

const commonSorterSettings = {
	/*debug: true,*/
	theme: "bootstrap",
	widgets : [ "uitheme" ],
	headerTemplate: "{content} {icon}",
	headers: {
		0: { parser: false, sorter: false },
		1: { sorter: "digit" },
		2: { sorter: "text", sortInitialOrder: "asc" }
	},
	sortInitialOrder: "desc",
	sortRestart: true,
	sortList: [[1,1]]
};

function alreadyHighlightingToggled() {
	return ( toggled_corporation_id != null || toggled_alliance_id != null || toggled_faction_id != null );
}// end alreadyHighlightingToggled()

function highlightAssociates( mouse_not_click, highlight, eve_corporation_id = null, eve_alliance_id= null, eve_faction_id= null ) {
	if( !mouse_not_click && !highlight ) {
		// A click of a highlightable element, toggle off current highlights
		toggled_corporation_id = null;
		toggled_alliance_id = null;
		toggled_faction_id = null;
	}
	else if( alreadyHighlightingToggled() ) {
		return;
	}
	
	if( !highlight ) {
		$( ".highlight_associates" ).removeClass( "highlight_associates" );
	} else {
		$( "#lscan_corps" ).each( function( index, table ) {
			let row = $( "tbody > tr[data-eve-corporation-id='"+eve_corporation_id+"']", table );
			//let row = $( "tbody > tr[data-eve-alliance-id='"+eve_alliance_id+"']", table );
			row.addClass( "highlight_associates" );
		} );// end .each()
		if( eve_alliance_id != null ) {
			$( "#lscan_alliances" ).each( function( index, table ) {
				let row = $( "tbody > tr[data-eve-alliance-id='"+eve_alliance_id+"']", table );
				row.addClass( "highlight_associates" );
			} );// end .each()
		} else {
			let row = $( "#no_alliance" );
			row.addClass( "highlight_associates" );
			$( "#lscan_corps" ).each( function( index, table ) {
				let row = $( "tbody > tr:not([data-eve-alliance-id])", table );
				row.addClass( "highlight_associates" );
			} );// end .each()
		}
		if( eve_faction_id != null ) {
			$( "#lscan_factions" ).each( function( index, table ) {
				let row = $( "tbody > tr[data-eve-faction-id='"+eve_faction_id+"']", table );
				row.addClass( "highlight_associates" );
			} );// end .each()
		}
		
		if( !mouse_not_click && highlight ) {
			// A click of a now-highlighted element, set as toggled
			toggled_corporation_id = eve_corporation_id;
			toggled_alliance_id = eve_alliance_id;
			toggled_faction_id = eve_faction_id;
		}
	}
}// end highlightAssociates()

function toggleHighlighting( eve_corporation_id, eve_alliance_id, eve_faction_id ) {
	// Only called by clicking, not mouseover/mouseleave
	if( alreadyHighlightingToggled() ) {
		// Check if we're single-click-swapping toggled highlighting to different alliances/factions
		if( CLICK_REPLACES_HIGHLIGHTED && (toggled_alliance_id != eve_alliance_id || toggled_faction_id != eve_faction_id) ) {
			// Swap from old to new set
			highlightAssociates( false, false );
			highlightAssociates( false, true, eve_corporation_id, eve_alliance_id, eve_faction_id );
		} else {
			// Turn off highlighting for this entity and unset it as the toggled one
			highlightAssociates( false, false );
		}
	} else {
		// Toggle any previous highlighting off, then enable it for this entity
		highlightAssociates( false, false );
		highlightAssociates( false, true, eve_corporation_id, eve_alliance_id, eve_faction_id );
	}
}// end toggleHighlighting()


jQuery( document ).ready( function() {
	
	// Count & enrich the tables
	let alliance_corps_id_map = new Map();
	let faction_corps_id_map = new Map();
	
	$( "#lscan_corps > tbody > tr" ).each( function( index, row ) {
		let eve_corporation_id = $( row ).data( "eve-corporation-id" );
		// Check this isn't the high local count pseudo corp row
		if( eve_corporation_id !== undefined ) {
			// Check corp has an alliance
			let eve_alliance_id = $( row ).data( "eve-alliance-id" );
			if( eve_alliance_id !== undefined ) {
				let corps_set = new Set();
				// Update the alliance's set with this corp
				if( alliance_corps_id_map.has( eve_alliance_id ) ) {
					corps_set = alliance_corps_id_map.get( eve_alliance_id );
				}
				corps_set.add( eve_corporation_id );
				alliance_corps_id_map.set( eve_alliance_id, corps_set );
			} else {
				eve_alliance_id = null;
			}
			// Check corp has a faction
			let eve_faction_id = $( row ).data( "eve-faction-id" );
			if( eve_faction_id !== undefined ) {
				let corps_set = new Set();
				// Update the faction's set with this corp
				if( faction_corps_id_map.has( eve_faction_id ) ) {
					corps_set = faction_corps_id_map.get( eve_faction_id );
				}
				corps_set.add( eve_corporation_id );
				faction_corps_id_map.set( eve_faction_id, corps_set );
			} else {
				eve_faction_id = null;
			}
			
			// Set up highlighting on associates rows' mouseover
			$( row ).on( "mouseenter", function() {
				highlightAssociates( true, true, eve_corporation_id, eve_alliance_id, eve_faction_id );
			} );
			$( row ).on( "mouseleave", function() {
				highlightAssociates( true, false );
			} );
			$( row ).click( function() {
				toggleHighlighting( eve_corporation_id, eve_alliance_id, eve_faction_id );
			} );// end .click()
		}
		// else skip pseudo corp row
		
	} );// end .each()
	
	//console.log( faction_corps_id_map );
	
	for( let [eve_alliance_id, corps_set] of alliance_corps_id_map ) {
		$( "#lscan_alliances > tbody > tr[data-eve-alliance-id='"+eve_alliance_id+"']" ).each( function( index, row ) {
			
			// Display the summed count
			let corp_count_td = $( "td:nth-child(4)", row );	// 4 for Corps count column
			corp_count_td.text( corps_set.size );
			
		} );// end .each()
	}// end for()
	for( let [eve_faction_id, corps_set] of faction_corps_id_map ) {
		$( "#lscan_factions > tbody > tr[data-eve-faction-id='"+eve_faction_id+"']" ).each( function( index, row ) {
			
			// Display the summed count
			let corp_count_td = $( "td:nth-child(3)", row );	// 3 for Corps count column
			corp_count_td.text( corps_set.size );
			
		} );// end .each()
	}// end for()
	
	
	// Initialise TableSorter 2.0
	$.tablesorter.themes.bootstrap = {
		iconSortNone: 'fa fa-sort',
		iconSortAsc: 'fa fa-sort-asc',
		iconSortDesc: 'fa fa-sort-desc'
	};
	$( "#lscan_corps, #lscan_alliances" ).tablesorter( commonSorterSettings );
	
});	// end .ready()