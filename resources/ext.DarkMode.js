$( function () {
	var darkMode = document.documentElement.classList.contains( 'client-dark-mode' ),
		$link = $( '.darkmode-link' );

	function updateText() {
		$link.text( mw.msg( darkMode ? 'darkmode-default-link' : 'darkmode-link' ) );
	}

	updateText();

	$link.on( 'click', function ( e ) {
		e.preventDefault();
		darkMode = !darkMode;

		$( document.documentElement ).toggleClass( 'client-dark-mode', darkMode );
		updateText();
		new mw.Api().saveOption( 'darkmode', darkMode ? 1 : 0 );
	} );
} );
