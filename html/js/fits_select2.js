function fits_escapeMarkup( markup ) {
	return markup;
}; // end fits_escapeMarkup()

function fits_templateResult( data, container ) {
	//console.log( data );
	if( !data.id ) {
		return data.text;
	}
	
	var dataset = data.element.dataset;
	var typeID = dataset.eveTypeId;
	var typeName = dataset.eveTypeName;
	var isOfficial = dataset.isOfficial == 'true';
	var fitName = dataset.fitName;
	
	var html = 'ID:' + data.id + ' | ';
	html += '<img class="edit-doctrine-img img-rounded" src="https://imageserver.eveonline.com/Type/' + typeID + '_32.png" alt="' + typeName + '"> ' + typeName + ' | ';
	if( isOfficial ) {
		html += '<img src="/media/image/logo/favicon_purple_32px.png" width="32px" height="32px" style="margin-bottom: 0px"> Official Fit | ';
	}
	html += fitName;
	
	return html;
}; // end fits_templateResult()

function fits_templateSelection( data, container ) {
	return fits_templateResult( data, container );
}; // end fits_templateSelection()

$(document).ready( function() {
	$(".select2-fits-dropdown").select2( {
		escapeMarkup: fits_escapeMarkup,
		templateResult: fits_templateResult,
		templateSelection: fits_templateSelection
	} );
} );
