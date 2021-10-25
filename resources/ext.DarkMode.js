$( function () {
	$( '.darkmode-link' ).on( 'click', function ( e ) {
		e.preventDefault();

		var darkMode = document.body.classList.toggle( 'client-dark-mode' );

		$( this ).text( mw.msg( darkMode ? 'darkmode-default-link' : 'darkmode-link' ) );
		new mw.Api().saveOption( 'darkmode', darkMode ? 1 : 0 );
	} );
} );
