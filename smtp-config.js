/**
 * Adds mail configuration to WordPress in a simple, standardised plugin.
 *
 * @package sb-simple-smtp
 * @author soup-bowl <code@soupbowl.io>
 * @license MIT
 */

function wpss_loadin() {
	jQuery.getJSON(
		"https://www.soupbowl.io/wp-json/wprass/v1/sources",
		function( data ) {
			console.log( data );
			wpss_load_quicksettings( data );
			document.getElementById( 'wpss-quickset' ).onchange = function( stuff ) {
				wpss_input_selection( data, stuff.target.value );
			};
		}
	);
}

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

	var datacount = data.configurations.length;
	options      += '<option>Select</option>';
	for (i = 0; i < datacount; i++) {
		options += '<option>' + data.configurations[i].name + '</option>';
	}
	selector.innerHTML = options;

	selector_c1.outerHTML = "<th scope=\"row\">Quick Config</th>";
	selector_c2.appendChild( selector );
}

function wpss_input_selection( data, name ) {
	var s = null;
	var c = data.configurations.length;
	for (i = 0; i < c; i++) {
		if ( data.configurations[i].name == name ) {
			s = data.configurations[i];
			break;
		}
	}

	if ( s != null ) {
		document.getElementById( 'wpss_host' ).value          = ( s.server != null ? s.server : '' );
		document.getElementById( 'wpss_port' ).value          = ( s.port != null ? s.port : '' );
		document.getElementById( 'wpss_auth' ).checked        = ( s.authentication != null ? s.authentication : false );
		document.getElementById( 'wpss_user' ).value          = ( s.user != null ? s.user : '' );
		document.getElementById( 'wpss_pass' ).value          = '';
		document.getElementById( 'wpss_noverifyssl' ).checked = false;
	}
}

wpss_loadin();
