jQuery(document).ready( function( $ ) {

	$( "input" ).bind( "keydown", function( event ) {
		// track enter key
		var keycode = ( event.keyCode ? event.keyCode : ( event.which ? event.which : event.charCode ) );
		if ( keycode == 13 ) { // keycode for enter key
			// force the 'Enter Key' to implicitly click the 'change operator' button, depending on which input field was last clicked on
			if ( $(this).attr( 'id' ) === 'operator_id_input' ) {
				$( '#switch_operator' ).click();
			}
			return false;
		} else {
			return true;
		}
	});

}); 