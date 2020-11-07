/**
 * Adds mail configuration to WordPress in a simple, standardised plugin.
 *
 * @package sb-simple-smtp
 * @author soup-bowl <code@soupbowl.io>
 * @license MIT
 */

function load_settings_selector() {
	jQuery.getJSON(
		"https://www.soupbowl.io/wp-json/wprass/v1/sources",
		function( data ) {
			console.log(data);
			var wpss_conf_table = document.getElementById( 'wpss-conf' )
			.getElementsByTagName( 'table' )[0]
			.getElementsByTagName( 'tbody' )[0];

			var wpss_selector_row = wpss_conf_table.insertRow();
			var wpss_selector_c1  = wpss_selector_row.insertCell();
			var wpss_selector_c2  = wpss_selector_row.insertCell();
			var wpss_selector     = document.createElement( "select" );

			wpss_selector_c1.outerHTML = "<th scope=\"row\">Blub</th>";
		}
	);
}

load_settings_selector();
