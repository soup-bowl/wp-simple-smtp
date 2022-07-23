/**
 * Adds mail configuration to WordPress in a simple, standardised plugin.
 *
 * @package sb-simple-smtp
 * @author soup-bowl <code@soupbowl.io>
 * @license MIT
 */

const { __ } = wp.i18n;

/**
 * Grabs the sources file. If it loads, we continue. If not, we do not display this feature.
 */
function wpss_loadin() {
	if ( null !== document.getElementById( 'wpss-conf' ) ) {
		wpss_load_quicksettings( wpss_qc_settings );
		document.getElementById( 'wpss-quickset' ).onchange = function( stuff ) {
			wpss_input_selection( wpss_qc_settings, stuff.target.value );
		};
	}
}

/**
 * Displays the quick settings dropdown on the settings page.
 *
 * @param {object} data Data from the soupbowl.io API.
 */
function wpss_load_quicksettings( data ) {
	var conf_table = document.getElementById( 'wpss-conf' )
	.getElementsByTagName( 'table' )[0]
	.getElementsByTagName( 'tbody' )[0];

	var selector_row = conf_table.insertRow( 0 );
	var selector_c1  = selector_row.insertCell();
	var selector_c2  = selector_row.insertCell();

	var selector = document.createElement( "select" );
	var options  = '';
	selector.id  = 'wpss-quickset';

	// Description cell.
	selector_c1.outerHTML = "<th scope=\"row\">" + __( 'Quick Config', 'simple-smtp' ) + "</th>";

	// Content cell.
	var datacount = data.length;
	options      += '<option>' + __( 'Select', 'simple-smtp' ) + '</option>';
	for (i = 0; i < datacount; i++) {
		options += '<option>' + data[i].name + '</option>';
	}
	selector.innerHTML = options;
	selector_c2.appendChild( selector );

	sel_warning           = document.createElement( "p" );
	sel_warning.className = 'description';
	sel_warning.innerHTML = __( 'Automatically sets the default settings for most providers.', 'simple-smtp' );
	selector_c2.appendChild( sel_warning );
}

/**
 * Changes the input fields to match the desired data selection.
 *
 * @param {object} data Data from the soupbowl.io API.
 * @param {string} name The data segment to use.
 */
function wpss_input_selection( data, name ) {
	var s = null;
	var c = data.length;
	for (i = 0; i < c; i++) {
		if ( data[i].name == name ) {
			s = data[i];
			break;
		}
	}

	if ( s != null ) {
		if ( ! document.getElementById( 'wpss_host' ).disabled ) {
			document.getElementById( 'wpss_host' ).value = ( s.server != null ? s.server : '' );
		}
		if ( ! document.getElementById( 'wpss_port' ).disabled ) {
			document.getElementById( 'wpss_port' ).value = ( s.port != null ? s.port : '' );
		}
		if ( ! document.getElementById( 'wpss_auth' ).disabled ) {
			document.getElementById( 'wpss_auth' ).checked = ( s.authentication != null ? s.authentication : false );
		}
		if ( ! document.getElementById( 'wpss_user' ).disabled ) {
			document.getElementById( 'wpss_user' ).value = ( s.user != null ? s.user : '' );
		}
		if ( ! document.getElementById( 'wpss_pass' ).disabled ) {
			document.getElementById( 'wpss_pass' ).value = '';
		}
		if ( ! document.getElementById( 'wpss_sec' ).disabled ) {
			document.getElementById( 'wpss_sec' ).value = ( s.encryption != null ? s.encryption : 'def' );
		}
		if ( ! document.getElementById( 'wpss_noverifyssl' ).disabled ) {
			document.getElementById( 'wpss_noverifyssl' ).checked = false;
		}
	}
}

wpss_loadin();
