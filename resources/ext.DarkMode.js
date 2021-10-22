$( function () {
	var $link = $( '.darkmode-link' );

	function updateText( darkMode ) {
		$link.text( mw.msg( darkMode ? 'darkmode-default-link' : 'darkmode-link' ) );
	}

	updateText( document.documentElement.classList.contains( 'client-dark-mode' ) );

	$link.on( 'click', function ( e ) {
		e.preventDefault();

		var darkMode = document.documentElement.classList.toggle( 'client-dark-mode' );
		updateText( darkMode );
		new mw.Api().saveOption( 'darkmode', darkMode ? 1 : 0 );
	} );
} );
