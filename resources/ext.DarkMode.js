$( function () {
	var darkMode = document.documentElement.classList.contains( 'client-dark-mode' );

	function updatetext() {
		$( '#footer-places-darkmode-link a' ).text( mw.msg( darkMode ? 'darkmode-default-link' : 'darkmode-link' ) );
	}
	$( updatetext() );
	$( '#footer-places-darkmode-link a' ).on( 'click', function ( e ) {
		e.preventDefault();
		darkMode = !darkMode;

		$( document.documentElement ).toggleClass( 'client-dark-mode', darkMode );
		updatetext();
		new mw.Api().saveOption( 'darkmode', darkMode ? 1 : 0 );
	} );
} );
