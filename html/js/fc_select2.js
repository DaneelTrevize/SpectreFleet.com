function fc_escapeMarkup( markup ) {
	return markup;
}; // end fc_escapeMarkup()

function fc_templateResult( data, container ) {
	//console.log( data );
	if( !data.id ) {
		return data.text;
	}
	
	var dataset = data.element.dataset;
	var CharacterID = dataset.eveCharacterId;
	var CharacterName = dataset.eveCharacterName;
	
	var html = '<img class="img-rounded" src="https://imageserver.eveonline.com/Character/' + CharacterID + '_32.jpg" alt="' + CharacterName + '"> ' + CharacterName;
	
	return html;
}; // end fc_templateResult()

function fc_templateSelection( data, container ) {
	return fc_templateResult( data, container );
}; // end fc_templateSelection()

$(document).ready( function() {
	$(".select2-fc-dropdown").select2( {
		escapeMarkup: fc_escapeMarkup,
		templateResult: fc_templateResult,
		templateSelection: fc_templateSelection
	} );
} );
