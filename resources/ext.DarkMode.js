$( function () {
	var darkMode = document.documentElement.classList.contains( 'client-dark-mode' );

	function updatetext() {
		$( '#darkmode-link' ).text( mw.msg( darkMode ? 'darkmode-default-link' : 'darkmode-link' ) );
	}
	$( updatetext() );
	$( '#darkmode-link' ).on( 'click', function ( e ) {
		e.preventDefault();
		darkMode = !darkMode;

		$( document.documentElement ).toggleClass( 'client-dark-mode', darkMode );
		updatetext();
		new mw.Api().saveOption( 'darkmode', darkMode ? 1 : 0 );
	} );
} );
