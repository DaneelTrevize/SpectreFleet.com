'use strict';

var sf_class_name_map = new Map();
var eve_type_name_map = new Map();
var eve_group_name_map = new Map();

var combined_sf_class_eve_types_map = new Map();
var combined_groups_count_map = new Map();
var simplified_groups_count_map = new Map();

var ongrid_ships_count = 0;
var offgrid_ships_count = 0;
var subcaps_count = 0;
var logistics_count = 0;
var capitals_count = 0;
var other_count = 0;

var gridsSortersInitialised = false;
var combinedSortersInitialised = false;
var simplifiedSortersInitialised = false;
const commonSorterSettings = {
	/*debug: "core",*/
	theme: "bootstrap",
	widgets : [ "uitheme" ],
	headerTemplate: "{content} {icon}",
	headers: {
		0: { sorter: "groupParser" },
		1: { sorter: "digit" },
		2: { sorter: "typeParser", sortInitialOrder: "asc" },
		3: { sorter: "digit" },
		4: { sorter: "digit" }	/* indexing still not fixed */
	},
	sortInitialOrder: "desc",
	sortRestart: true,
	sortList: [[1,1]]
};
const ongridSorterSettings = Object.assign( {}, commonSorterSettings );
ongridSorterSettings.headers = {
	0: { sorter: "groupParser" },
	1: { sorter: "digit" },
	2: { sorter: "typeParser", sortInitialOrder: "asc" },
	3: { sorter: "digit" },
	4: { sorter: "distanceParser", sortInitialOrder: "asc" },	/* indexing still not fixed */
	5: { sorter: "distanceParser", sortInitialOrder: "asc" },
	6: { sorter: "distanceParser", sortInitialOrder: "asc" },
	7: { sorter: "distanceParser", sortInitialOrder: "asc" }
};


function mapClassName( sf_class_id, sf_class_name ) {
	if( !sf_class_name_map.has( sf_class_id ) ) {
		sf_class_name_map.set( sf_class_id, sf_class_name );
	}
}// end mapClassName()

function mapTypeName( eve_type_id, eve_type_name ) {
	if( !eve_type_name_map.has( eve_type_id ) ) {
		eve_type_name_map.set( eve_type_id, eve_type_name );
	}
}// end mapTypeName()

function mapGroupName( eve_group_id, eve_group_name ) {
	if( !eve_group_name_map.has( eve_group_id ) ) {
		eve_group_name_map.set( eve_group_id, eve_group_name );
	}
}// end mapGroupName()

function combineType( current_eve_types_map, eve_type_id, eve_type_volume, eve_group_id, eve_category_id, row_group_count ) {
	if( !current_eve_types_map.has( eve_type_id ) ) {
		current_eve_types_map.set( eve_type_id, {
			eve_type_id: eve_type_id,
			eve_type_volume: eve_type_volume,
			eve_group_id: eve_group_id,
			eve_category_id: eve_category_id,
			count: row_group_count
			/*closest: ,
			median: ,
			furthest: */
			} );
	} else {
		let eve_type_obj = current_eve_types_map.get( eve_type_id );
		eve_type_obj.count += row_group_count;
		//current_eve_types_map.set( eve_type_id, eve_type_obj );
	}
}// end combineType()

function combinedGroupCount( sf_class_id, eve_group_id, row_group_count ) {
	// First for counts split by sf_class
	let groups_counts_map = new Map();
	let group_count = row_group_count;
	if( combined_groups_count_map.has( sf_class_id ) ) {
		groups_counts_map = combined_groups_count_map.get( sf_class_id );
		if( groups_counts_map.has( eve_group_id ) ) {
			group_count += groups_counts_map.get( eve_group_id );
		}
	}
	groups_counts_map.set( eve_group_id, group_count );
	combined_groups_count_map.set( sf_class_id, groups_counts_map );
	
	// And now for simplified counts
	group_count = row_group_count;
	if( simplified_groups_count_map.has( eve_group_id ) ) {
		group_count += simplified_groups_count_map.get( eve_group_id );
	}
	simplified_groups_count_map.set( eve_group_id, group_count );
}// end combinedGroupCount()

function categoryIsShip( eve_category_id ) {
	return (eve_category_id == 6 || eve_category_id == 18 );
}// end categoryIsShip()


function highlightGroup( eve_group_id, table, highlight ) {
	let row = $( "tbody > tr[data-eve-group-id='"+eve_group_id+"']", table );
	if( highlight ) {
		row.addClass( "highlight_group" );
	} else {
		row.removeClass( "highlight_group" );
	}
}// end highlightGroup()

function addGroupHighlighting( groups_map, counts_or_names, table ) {
	for( let [eve_group_id, group_value] of groups_map ) {
		//console.log( eve_group_id + ": " + group_value );
		$( "tbody > tr[data-eve-group-id='"+eve_group_id+"']", table ).each( function( index, row ) {
			
			if( counts_or_names ) {
				let group_count = group_value;
				// Display the summed count
				let group_count_td = $( "td:nth-child(2)", row );	// 2 for Group count column
				group_count_td.text( group_count );
				let type_count = parseInt( $( "td:nth-child(5)", row ).text(), 10 );	// 5 for Type count column
				if( type_count == group_count ) {
					group_count_td.addClass( 'text-muted' );
				}
			}
			
			// Set up highlighting on grouped rows' mouseover
			$( row ).on( "mouseenter", function() {
				highlightGroup( eve_group_id, table, true )
			} );
			$( row ).on( "mouseleave", function() {
				highlightGroup( eve_group_id, table, false )
			} );
		} );// end .each()
		
	}// end for()
}// end addGroupHighlighting()


function generateCombinedHTML() {
	let combined_html = '<div class="row">';
	let combined_ships_html = '';
	let combined_nonships_html = '';
	let table_html = '';
	
	for( let [sf_class_id, current_eve_types_map] of combined_sf_class_eve_types_map ) {
		//console.log( 'sf_class_id: ' + sf_class_id );
		let class_count = 0;
		let class_is_ships = null;	// Ships or drones actually
		
		table_html = '<table class="table table-striped table_valign_m table_combined"';
		table_html += ' data-sf-class-id="'+sf_class_id+'"><thead><tr>';
		table_html += '<th class="aligncenter">Group</th>';
		table_html += '<th class="aligncenter">Σ</th>';
		table_html += '<th class="aligncenter" colspan="2">Type</th>';
		table_html += '<th class="aligncenter">Σ</th>';
		table_html += '</tr></thead><tbody>';
		
		for( let [eve_type_id, eve_type_obj] of current_eve_types_map ) {
			//console.log( eve_type_id );
			let group_id = eve_type_obj.eve_group_id;
			let count = eve_type_obj.count;
			
			table_html += '<tr data-eve-type-id="' +eve_type_id+ '" data-eve-type-volume="' +eve_type_obj.eve_type_volume+ '" data-eve-group-id="' +group_id+ '" data-eve-category-id="' +eve_type_obj.eve_category_id+ '">';
			
			class_count += count;
			if( class_is_ships == null ) {
				class_is_ships = categoryIsShip( eve_type_obj.eve_category_id );
			}
			
			table_html += '<td class="aligncenter"><img src="/dscan/image/' +sf_class_id+ '/' +group_id+ '" title="'+eve_group_name_map.get( group_id )+'"></td>';
			
			let groups_counts_map = combined_groups_count_map.get( sf_class_id );
			let group_count = groups_counts_map.get( group_id );
			// Mute group count where this row is the only one in the group
			table_html += '<td class="aligncenter'+(count==group_count?' text-muted':'')+'">'+group_count+'</td>';
			
			table_html += '<td class="aligncenter"><img class="img-rounded" src="https://imageserver.eveonline.com/Type/' +eve_type_id+ '_32.png" title="'+'"></td>';
			table_html += '<td class="type-name">'+eve_type_name_map.get( eve_type_id )+'</td>';
			table_html += '<td class="aligncenter">'+count+'</td>';
			
			table_html += '</tr>';
		}// end for()
		
		table_html += '</tbody></table></div>';
		
		let h4_html = '<div class="col-lg-3 col-md-4 col-sm-6"><h4>'+sf_class_name_map.get( sf_class_id )+': <span id="combined_sf_class_'+sf_class_id+'_count">'+class_count+'</span></h4>';
		if( class_is_ships ) {
			combined_ships_html += h4_html;
			combined_ships_html += table_html;
		} else {
			combined_nonships_html += h4_html;
			combined_nonships_html += table_html;
		}
	}// end for()
	
	if( combined_ships_html == '' ) {
		combined_html += '<div class="col-sm-offset-1"><p>No results found for combat ship categories.</p></div>';
	} else {
		combined_html += combined_ships_html;
	}
	combined_html += '<div class="row"><div class="col-xs-8 col-xs-offset-2"><hr><br></div></div>';
	combined_html += combined_nonships_html;
	combined_html += '</div>';
	
	return combined_html;
}// end generateCombinedHTML()

function generateCondensedTable( name, count, rows_html ) {
	let table_html = '<div class="col-lg-3 col-md-4 col-sm-6"><h4>'+name+': <span id="simplified_'+name+'_count">'+count+'</span></h4>';
	if( count == 0 ) {
		table_html += '<div class="col-sm-offset-1"><p>No results found for '+name+'.</p></div>';
	} else {
		table_html += '<table class="table table-striped table_valign_m table_simplified"><thead><tr>';
		table_html += '<th class="aligncenter">Group</th>';
		table_html += '<th class="aligncenter">Σ</th>';
		table_html += '<th class="aligncenter" colspan="2">Type</th>';
		table_html += '<th class="aligncenter">Σ</th>';
		table_html += '</tr></thead><tbody>';
		table_html += rows_html;
		table_html += '</tbody></table>';
	}
	table_html += '</div>';
	return table_html;
}// end generateCondensedTable()

function generateCondensedHTML() {
	let simplified_subcaps_html = '';
	let simplified_logistics_html = '';
	let simplified_capitals_html = '';
	let simplified_other_html = '';
	
	for( let [sf_class_id, current_eve_types_map] of combined_sf_class_eve_types_map ) {
		//console.log( 'sf_class_id: ' + sf_class_id );
		
		for( let [eve_type_id, eve_type_obj] of current_eve_types_map ) {
			//console.log( eve_type_id );
			let group_id = eve_type_obj.eve_group_id;
			let count = eve_type_obj.count;
			
			let rows_html = '<tr data-eve-type-id="' +eve_type_id+ '" data-eve-type-volume="' +eve_type_obj.eve_type_volume+ '" data-eve-group-id="' +group_id+ '" data-eve-category-id="' +eve_type_obj.eve_category_id+ '">';
			
			rows_html += '<td class="aligncenter"><img src="/dscan/image/' +sf_class_id+ '/' +group_id+ '" title="'+eve_group_name_map.get( group_id )+'"></td>';
			
			let group_count = simplified_groups_count_map.get( group_id );
			// Mute group count where this row is the only one in the group
			rows_html += '<td class="aligncenter'+(count==group_count?' text-muted':'')+'">'+group_count+'</td>';
			
			rows_html += '<td class="aligncenter"><img class="img-rounded" src="https://imageserver.eveonline.com/Type/' +eve_type_id+ '_32.png" title="'+'"></td>';
			rows_html += '<td class="type-name">'+eve_type_name_map.get( eve_type_id )+'</td>';
			rows_html += '<td class="aligncenter">'+count+'</td>';
			
			rows_html += '</tr>';
		
			switch( sf_class_id ) {
				case 2:		// DPS
				case 8:		// Tackle
				case 13:	// EWar & Support
					subcaps_count += count;
					simplified_subcaps_html += rows_html;
					break;
				case 7:		// Logistics
					logistics_count += count;
					simplified_logistics_html += rows_html;
					break;
				case 11:	// Supers
				case 12:	// Capitals
					capitals_count += count;
					simplified_capitals_html += rows_html;
					break;
				default:
					other_count += count;
					simplified_other_html += rows_html;
					break;
			}
		}// end for()
	}// end for()
	
	simplified_subcaps_html = generateCondensedTable( 'Subcaps', subcaps_count, simplified_subcaps_html );
	simplified_logistics_html = generateCondensedTable( 'Logistics', logistics_count, simplified_logistics_html );
	simplified_capitals_html = generateCondensedTable( 'Capitals', capitals_count, simplified_capitals_html );
	simplified_other_html = generateCondensedTable( 'Other', other_count, simplified_other_html );
	
	let simplified_html = '<div class="row">';
	simplified_html += simplified_subcaps_html;
	simplified_html += simplified_logistics_html;
	simplified_html += simplified_capitals_html;
	simplified_html += simplified_other_html;
	simplified_html += '</div>';
	
	return simplified_html;
}// end generateCondensedHTML()


function ensureGridsSortersInitialised() {
	if( !gridsSortersInitialised ) {
		$( ".table_ongrid" ).tablesorter( ongridSorterSettings );
		$( ".table_offgrid" ).tablesorter( commonSorterSettings );
		
		gridsSortersInitialised = true;
	}
}// end ensureGridsSortersInitialised()

function ensureCombinedSortersInitialised() {
	if( !combinedSortersInitialised ) {
		$( ".table_combined" ).tablesorter( commonSorterSettings );
		
		combinedSortersInitialised = true;
	}
}// end ensureCombinedSortersInitialised()

function ensureCondensedSortersInitialised() {
	if( !simplifiedSortersInitialised ) {
		$( ".table_simplified" ).tablesorter( commonSorterSettings );
		
		simplifiedSortersInitialised = true;
	}
}// end ensureCondensedSortersInitialised()

function commonImagesToggle() {
	$( "#dscan_images_hide" ).toggle();
	$( "#dscan_images_show" ).toggle();
	
	$( ".table_ongrid > thead > tr > th:nth-child(2), \
	.table_offgrid > thead > tr > th:nth-child(2), \
	.table_combined > thead > tr > th:nth-child(2), \
	.table_simplified > thead > tr > th:nth-child(2), \
	.table_ongrid > tbody > tr > td:nth-child(2), \
	.table_offgrid > tbody > tr > td:nth-child(2), \
	.table_combined > tbody > tr > td:nth-child(2), \
	.table_simplified > tbody > tr > td:nth-child(2) " ).toggle();
	
	$( ".table_ongrid > tbody > tr > td:nth-child(3), \
	.table_offgrid > tbody > tr > td:nth-child(3), \
	.table_combined > tbody > tr > td:nth-child(3), \
	.table_simplified > tbody > tr > td:nth-child(3) " ).toggle();
}// end commonImagesToggle()


jQuery( document ).ready( function() {
	
	// Hide the tables before we modify them
	$( "#dscan_offgrid_skip >" ).hide();
	$( "#ongrid" ).hide();
	$( "#offgrid" ).hide();
	
	// Count & enrich the on-grid tables
	$( ".table_ongrid" ).each( function( index, table ) {
		let ongrid_groups_count_map = new Map();
		
		let sf_class_id = $( table ).data( "sf-class-id" );
		//console.log( sf_class_id );
		mapClassName( sf_class_id, $( table ).data( "sf-class-name" ) );
		
		if( !combined_sf_class_eve_types_map.has( sf_class_id ) ) {
			combined_sf_class_eve_types_map.set( sf_class_id, new Map() );
		}
		let current_eve_types_map = combined_sf_class_eve_types_map.get( sf_class_id );
		
		$( "tbody > tr", table ).each( function( index, row ) {
			
			let eve_type_id = $( row ).data( "eve-type-id" );
			let eve_type_volume = $( row ).data( "eve-type-volume" );
			let eve_group_id = $( row ).data( "eve-group-id" );
			let eve_category_id = $( row ).data( "eve-category-id" );
			
			mapTypeName( eve_type_id, $( "td:nth-child(4)", row ).text() );		// 4 for Type name column
			let eve_group_name = $( "td:first-child > img", row ).attr( 'title' );
			mapGroupName( eve_group_id, eve_group_name );
			
			let row_group_count = parseInt( $( "td:nth-child(5)", row ).text(), 10 );	// 5 for Type count column
			// Add to ongrid counts
			let group_count = row_group_count;
			if( ongrid_groups_count_map.has( eve_group_id ) ) {
				group_count += ongrid_groups_count_map.get( eve_group_id );
			}
			ongrid_groups_count_map.set( eve_group_id, group_count );
			
			if( eve_category_id == 6 ) {
				ongrid_ships_count += row_group_count;
			}
			combinedGroupCount( sf_class_id, eve_group_id, row_group_count );
			
			// Add to combined map
			combineType( current_eve_types_map, eve_type_id, eve_type_volume, eve_group_id, eve_category_id, row_group_count );
			
		} );// end .each()
		
		addGroupHighlighting( ongrid_groups_count_map, true, table );
		
	} );// end .each()
	
	$( "#ongrid_ships_count" ).text( ongrid_ships_count );
	
	// Count & enrich the off-grid tables
	$( ".table_offgrid" ).each( function( index, table ) {
		let offgrid_groups_count_map = new Map();
		
		let sf_class_id = $( table ).data( "sf-class-id" );
		//console.log( sf_class_id );
		mapClassName( sf_class_id, $( table ).data( "sf-class-name" ) );
		
		if( !combined_sf_class_eve_types_map.has( sf_class_id ) ) {
			combined_sf_class_eve_types_map.set( sf_class_id, new Map() );
		}
		let current_eve_types_map = combined_sf_class_eve_types_map.get( sf_class_id );
		
		$( "tbody > tr", table ).each( function( index, row ) {
			
			let eve_type_id = $( row ).data( "eve-type-id" );
			let eve_type_volume = $( row ).data( "eve-type-volume" );
			let eve_group_id = $( row ).data( "eve-group-id" );
			let eve_category_id = $( row ).data( "eve-category-id" );
			
			mapTypeName( eve_type_id, $( "td:nth-child(4)", row ).text() );		// 4 for Type name column
			let eve_group_name = $( "td:first-child > img", row ).attr( 'title' );
			mapGroupName( eve_group_id, eve_group_name );
			
			let row_group_count = parseInt( $( "td:nth-child(5)", row ).text(), 10 );	// 5 for Type count column
			// Add to offgrid counts
			let group_count = row_group_count;
			if( offgrid_groups_count_map.has( eve_group_id ) ) {
				group_count += offgrid_groups_count_map.get( eve_group_id );
			}
			offgrid_groups_count_map.set( eve_group_id, group_count );
			
			if( eve_category_id == 6 ) {
				offgrid_ships_count += row_group_count;
			}
			combinedGroupCount( sf_class_id, eve_group_id, row_group_count );
			
			// Add to combined map
			combineType( current_eve_types_map, eve_type_id, eve_type_volume, eve_group_id, eve_category_id, row_group_count );
			
		} );// end .each()
		
		addGroupHighlighting( offgrid_groups_count_map, true, table );
		
	} );// end .each()
	
	$( "#offgrid_ships_count" ).text( offgrid_ships_count );
	
	// Use the combined on- and off-grid tables data
	$( "#combined_ships_count" ).text( ongrid_ships_count + offgrid_ships_count );
	$( "#combined" ).append( generateCombinedHTML() );
	$( ".table_combined" ).each( function( index, table ) {
		addGroupHighlighting( eve_group_name_map, false, table );
	} );// end .each()
	
	$( "#simplified_ships_count" ).text( ongrid_ships_count + offgrid_ships_count );
	$( "#simplified" ).append( generateCondensedHTML() );
	$( ".table_simplified" ).each( function( index, table ) {
		addGroupHighlighting( eve_group_name_map, false, table );
	} );// end .each()
	
	// Free used maps
	sf_class_name_map = null;
	eve_type_name_map = null;
	eve_group_name_map = null;
	combined_sf_class_eve_types_map = null;
	combined_groups_count_map = null;
	simplified_groups_count_map = null;
	
	// Initialise TableSorter 2.0
	$.tablesorter.addParser( {
		id: 'groupParser',
		format: function( str, table, cell, cellIndex ) {
			let row = $( cell ).closest( 'tr' );
			let eve_category_id = row.data( "eve-category-id" );
			if( categoryIsShip( eve_category_id ) ) {
				let eve_type_volume = row.data( "eve-type-volume" );
				//console.log( eve_type_volume );
				return eve_type_volume;
			} else {
				let eve_group_id = row.data( "eve-group-id" );
				//console.log( eve_group_id );
				return -1 * eve_group_id;	// *-1 To separate them from ships in Simplified Other table
			}
		},
		parsed: true,
		type: 'numeric'
	} );
	/*$.tablesorter.addParser( {
		id: 'groupIDParser',
		format: function( str, table, cell, cellIndex ) {
			let row = $( cell ).closest( 'tr' );
			let eve_group_id = row.data( "eve-group-id" );
			//console.log( eve_group_id );
			return eve_group_id;
		},
		parsed: true,
		type: 'numeric'
	} );*/
	$.tablesorter.addParser( {
		id: 'typeParser',
		format: function( str, table, cell, cellIndex ) {
			let row = $( cell ).closest( 'tr' );
			let type_name = $( 'td.type-name', row ).text();
			//console.log( type_name );
			return type_name;
		},
		parsed: true,
		type: 'text'
	} );
	$.tablesorter.addParser( {
		id: 'distanceParser',
		format: function( str, table, cell, cellIndex ) {
			//console.log( str );
			let distance = 0;
			if( str[str.length-1] == 'k' ) {
				distance = 1000 * parseInt( str.slice( 0, str.length-1 ), 10 );
			} else {
				distance = parseInt( str, 10 );
			}
			//console.log( distance );
			return distance;
		},
		parsed: true,
		type: 'numeric'
	} );
	$.tablesorter.themes.bootstrap = {
		iconSortNone: 'fa fa-sort',
		iconSortAsc: 'fa fa-sort-asc',
		iconSortDesc: 'fa fa-sort-desc'
	};
	// Lazy invoke TableSorters later...
	
	// Add functionality to toggle buttons & links
	$( "#dscan_grid_toggle" ).click( function ( e ) {
		ensureGridsSortersInitialised();
		$( "#dscan_grid_toggle" ).hide();
		$( "#dscan_offgrid_skip >" ).show();
		$( "#ongrid" ).show();
		$( "#offgrid" ).show();
		$( "#dscan_combined_toggle" ).show();
		$( "#combined" ).hide();
		$( "#dscan_simplified_toggle" ).show();
		$( "#simplified" ).hide();
	} ); // end .click()
	$( "#dscan_combined_toggle" ).click( function ( e ) {
		ensureCombinedSortersInitialised();
		$( "#dscan_grid_toggle" ).show();
		$( "#dscan_offgrid_skip >" ).hide();
		$( "#ongrid" ).hide();
		$( "#offgrid" ).hide();
		$( "#dscan_combined_toggle" ).hide();
		$( "#combined" ).show();
		$( "#dscan_simplified_toggle" ).show();
		$( "#simplified" ).hide();
	} ); // end .click()
	$( "#dscan_simplified_toggle" ).click( function ( e ) {
		ensureCondensedSortersInitialised();
		$( "#dscan_grid_toggle" ).show();
		$( "#dscan_offgrid_skip >" ).hide();
		$( "#ongrid" ).hide();
		$( "#offgrid" ).hide();
		$( "#dscan_combined_toggle" ).show();
		$( "#combined" ).hide();
		$( "#dscan_simplified_toggle" ).hide();
		$( "#simplified" ).show();
	} ); // end .click()
	
	$( "#dscan_images_hide a" ).click( function ( e ) {
		commonImagesToggle();
		$( ".table_ongrid > thead > tr > th:nth-child(3), \
		.table_offgrid > thead > tr > th:nth-child(3), \
		.table_combined > thead > tr > th:nth-child(3), \
		.table_simplified > thead > tr > th:nth-child(3) " ).attr( 'colspan', 1 );
	} ); // end .click()
	
	$( "#dscan_images_show a" ).click( function ( e ) {
		commonImagesToggle();
		$( ".table_ongrid > thead > tr > th:nth-child(3), \
		.table_offgrid > thead > tr > th:nth-child(3), \
		.table_combined > thead > tr > th:nth-child(3), \
		.table_simplified > thead > tr > th:nth-child(3) " ).attr( 'colspan', 2 );
	} ); // end .click()
	
	// Choose default view
	$( "#dscan_combined_toggle" ).click();
	
});	// end .ready()