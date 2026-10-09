// Page editor of the Home, About, Booking and Contact pages: most of the page's words are in the
// "Page text" box, which newer WordPress versions fold into a "Meta Boxes" bar at the bottom of
// the screen. Open that bar and say where the box is.
( function ( wp, text ) {
	if ( ! wp || ! wp.data || ! text ) {
		return;
	}
	function openBar() {
		try {
			var prefs = wp.data.dispatch( 'core/preferences' );
			// On Home and About the FAQ and the story stay in view above the box, unless a size was already chosen
			if ( text.half && undefined === wp.data.select( 'core/preferences' ).get( 'core/edit-post', 'metaBoxesMainOpenHeight' ) ) {
				prefs.set( 'core/edit-post', 'metaBoxesMainOpenHeight', Math.round( window.innerHeight * 0.45 ) );
			}
			prefs.set( 'core/edit-post', 'metaBoxesMainIsOpen', true );
		} catch ( e ) {} // older WordPress shows the box below the text anyway
	}
	function showBox() {
		openBar();
		setTimeout( function () {
			var box = document.getElementById( 'adventure-park-text' );
			if ( ! box ) {
				return;
			}
			box.scrollIntoView( { behavior: 'smooth', block: 'start' } );
			var field = box.querySelector( 'input, textarea' );
			if ( field ) {
				field.focus( { preventScroll: true } );
			}
		}, 300 );
	}
	wp.domReady( function () {
		openBar();
		// Someone who has never changed an editor setting gets their settings from the server a moment
		// after the editor starts, replacing what was set before: open the bar again if that happens
		var unsubscribe = wp.data.subscribe( function () {
			if ( ! wp.data.select( 'core/preferences' ).get( 'core/edit-post', 'metaBoxesMainIsOpen' ) ) {
				unsubscribe();
				openBar();
			}
		}, 'core/preferences' );
		setTimeout( unsubscribe, 4000 );
		wp.data.dispatch( 'core/notices' ).createNotice( 'info', text.notice, {
			id: 'adventure-park-page-text',
			isDismissible: true,
			actions: [ { label: text.button, onClick: showBox } ],
		} );
	} );
} )( window.wp, window.adventureParkEditor );
