$( function () {
	var darkMode = false;

	$( '#pt-darkmode-link a' ).on( 'click', function ( e ) {
		e.preventDefault();
		darkMode = !darkMode;

		$( document.documentElement ).toggleClass( 'client-dark-mode', darkMode );
		$( e.target ).text( mw.msg( darkMode ? 'darkmode-default-link' : 'darkmode-link' ) );
	} );
} );
