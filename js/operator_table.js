jQuery(document).ready( function($) {
	$( 'input.operator-id-cb' ).change( function() {
		if ( $(this).is( ':checked' ) ) {
			$(this).next().attr( 'checked', 'checked' );
		} else {
			$(this).next().removeAttr( 'checked' );
		}
	});
});
