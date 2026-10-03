/**
 * Some code adapted from the enwiki gadget https://w.wiki/5Ktj
 */
$( () => {
	// eslint-disable-next-line no-jquery/no-global-selector
	const $darkModeLink = $( '.ext-darkmode-link' );

	/**
	 * @param {boolean} darkMode is dark mode currently enabled?
	 */
	function updateLink( darkMode ) {
		// Update the icon.
		if ( darkMode ) {
			$darkModeLink.find( '.mw-ui-icon-moon' )
				.removeClass( 'mw-ui-icon-moon' )
				.addClass( 'mw-ui-icon-bright' );
		} else {
			$darkModeLink.find( '.mw-ui-icon-bright' )
				.removeClass( 'mw-ui-icon-bright' )
				.addClass( 'mw-ui-icon-moon' );
		}
		// Use different CSS selectors for the dark mode link based on the skin.
		const labelSelector = [ 'vector' ].includes( mw.config.get( 'skin' ) ) ?
			'span:not( .mw-ui-icon, .vector-icon )' :
			'a';

		// Update the link text and tooltip.
		const label = mw.msg( darkMode ? 'darkmode-default-link' : 'darkmode-link' );
		$darkModeLink.find( labelSelector )
			.text( label );
		$darkModeLink.find( 'a' ).addBack( 'a' ).attr( {
			title: mw.msg( darkMode ? 'darkmode-default-link-tooltip' : 'darkmode-link-tooltip' ),
			'aria-label': label
		} );
	}

	$darkModeLink.on( 'click', ( e ) => {
		e.preventDefault();

		const docClassList = document.documentElement.classList;
		const darkMode = !docClassList.contains( 'skin-theme-clientpref-night' );

		mw.storage.set( 'skin-theme', darkMode ? 'night' : 'day' );
		if ( !mw.user.isAnon() ) {
			new mw.Api().saveOption( 'darkmode', darkMode ? '1' : '0' );
		}

		if ( darkMode ) {
			docClassList.add( 'skin-theme-clientpref-night' );
			docClassList.add( 'client-darkmode' );
			docClassList.remove( 'skin-theme-clientpref-day' );
		} else {
			docClassList.add( 'skin-theme-clientpref-day' );
			docClassList.remove( 'client-darkmode' );
			docClassList.remove( 'skin-theme-clientpref-night' );
		}

		updateLink( darkMode );

		// Update the mobile theme-color
		// eslint-disable-next-line no-jquery/no-global-selector
		$( 'meta[name="theme-color"]' ).attr( 'content', darkMode ? '#151a21' : '#eaecf0' );
	} );

	function isDarkModeEnabled() {
		return document.documentElement.classList.contains( 'skin-theme-clientpref-night' );
	}

	updateLink( isDarkModeEnabled() );
} );
