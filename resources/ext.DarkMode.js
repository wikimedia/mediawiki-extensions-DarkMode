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
		const labelSelector = [ 'vector', 'vector-2022', 'minerva' ].includes( mw.config.get( 'skin' ) ) ?
			'span:not( .mw-ui-icon, .vector-icon, .minerva-icon )' :
			'a';

		// Update the link text and tooltip.
		$darkModeLink.find( labelSelector )
			.text( mw.msg( darkMode ? 'darkmode-default-link' : 'darkmode-link' ) )
			.attr( 'title', mw.msg( darkMode ?
				'darkmode-default-link-tooltip' :
				'darkmode-link-tooltip'
			) );
	}

	$darkModeLink.on( 'click', ( e ) => {
		e.preventDefault();

		const docClassList = document.documentElement.classList;
		// NOTE: this must be on <html> element because the CSS filter creates
		// a new stacking context.
		// See comments in Hooks::onBeforePageDisplay() for more information.
		const darkMode = !docClassList.contains( 'skin-theme-clientpref-night' );

		if ( mw.user.isAnon() ) {
			// If the user is anonymous (not logged in) write a cookie
			mw.user.clientPrefs.set( 'skin-theme', darkMode ? 'night' : 'day' );
		} else {
			// If the user is logged in write with API to user settings
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
		$( 'meta[name="theme-color"]' ).attr( 'content', darkMode ? '#000000' : '#eaecf0' );
	} );

	function isDarkModeEnabled() {
		return document.documentElement.classList.contains( 'skin-theme-clientpref-night' );
	}

	if ( !mw.user.isNamed() && isDarkModeEnabled() ) {
		updateLink( true );
	}
} );
