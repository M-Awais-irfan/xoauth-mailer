/* XOAuth Mailer admin scripts */
(function () {
	'use strict';

	// Auth method toggle
	function toggleAuthFields() {
		var radios = document.querySelectorAll( 'input[name$="[auth_method]"]' );
		if ( ! radios.length ) return;

		var selected = document.querySelector( 'input[name$="[auth_method]"]:checked' );
		if ( ! selected ) return;

		var isOauth = selected.value === 'oauth2';

		var rowApp    = document.getElementById( 'xoam-row-app-password' );
		var rowId     = document.getElementById( 'xoam-row-oauth-id' );
		var rowSecret = document.getElementById( 'xoam-row-oauth-secret' );

		if ( rowApp )    rowApp.style.display    = isOauth ? 'none' : '';
		if ( rowId )     rowId.style.display     = isOauth ? '' : 'none';
		if ( rowSecret ) rowSecret.style.display = isOauth ? '' : 'none';
	}

	document.querySelectorAll( 'input[name$="[auth_method]"]' ).forEach( function ( el ) {
		el.addEventListener( 'change', toggleAuthFields );
	} );
	toggleAuthFields();

	// Confirm before destructive actions
	// Text comes from a data-confirm attribute (escaped with esc_attr in PHP),
	// so translations with quotes can't break the script.
	document.querySelectorAll( '[data-confirm]' ).forEach( function ( btn ) {
		btn.addEventListener( 'click', function ( e ) {
			if ( ! window.confirm( btn.getAttribute( 'data-confirm' ) ) ) {
				e.preventDefault();
			}
		} );
	} );

	// Copy to clipboard
	var __ = wp.i18n.__; // Loaded via the 'wp-i18n' script dependency

	document.querySelectorAll( '[data-copy]' ).forEach( function ( btn ) {
		// Remember the (already translated, PHP-rendered) label to restore later
		var label = btn.textContent.trim();

		function showCopied() {
			btn.textContent = __( 'Copied!', 'xoauth-mailer' );
			setTimeout( function () { btn.textContent = label; }, 2000 );
		}

		btn.addEventListener( 'click', function () {
			var targetId = btn.getAttribute( 'data-copy' );
			var input    = document.getElementById( targetId );
			if ( ! input ) return;

			input.select();
			input.setSelectionRange( 0, 99999 );

			if ( navigator.clipboard ) {
				navigator.clipboard.writeText( input.value ).then( showCopied );
			} else {
				document.execCommand( 'copy' );
				showCopied();
			}
		} );
	} );
}());
