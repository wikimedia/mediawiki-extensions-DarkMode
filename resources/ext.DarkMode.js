( function () {
	var darkMode = false;

	$( '#pt-darkmode-link a' ).on( 'click', function ( e ) {
		e.preventDefault();
		darkMode = !darkMode;

		$( 'html' ).toggleClass( 'darkmode', darkMode );
		$( e.target ).text( mw.msg( darkMode ? 'darkmode-default-link' : 'darkmode-link' ) );
	} );
}() );
