jQuery(document).ready( function( $ ) {

	$( 'input.operator-id-cb' ).change( function() {
		if ( $(this).is( ':checked' ) ) {
			$(this).next().attr( 'checked', 'checked' );
		} else {
			$(this).next().removeAttr( 'checked' );
		}
	});

	$( "input" ).bind( "keydown", function( event ) {
		// track enter key
		var keycode = ( event.keyCode ? event.keyCode : ( event.which ? event.which : event.charCode ) );
		if ( keycode == 13 ) { // keycode for enter key
			// force the 'Enter Key' to implicitly click the add or search button, depending on which input field was last clicked on
			if ( $(this).attr( 'id' ) === 'add_operators_list' ) {
				$( '#add_list' ).click();
			} else {
				$( '#search-submit' ).click();
			}
			return false;
		} else {
			return true;
		}
	});

}); 