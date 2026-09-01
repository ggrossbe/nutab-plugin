<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://example.com
 * @since             1.0.0
 * @package           nutab
 *
 * @wordpress-plugin
 * Plugin Name:       123 NuTab Plugin
 * Plugin URI:        http://example.com/nutab-plugin/
 * Description:       Importiere Tabellen, Termine, ... aus nuliga.
 * Version:           1.0.0
 * Author:            Günter Grossberger <guenter.grossberger@gmx.at>
 * Author URI:        https://author.example.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       nutab
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'NUTAB_PLUGIN_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-nutab-activator.php
 */
function activate_nutab_plugin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-nutab-activator.php';
	NuTab_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-nutab-deactivator.php
 */
function deactivate_nutab_plugin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-nutab-deactivator.php';
	NuTab_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_nutab_plugin' );
register_deactivation_hook( __FILE__, 'deactivate_nutab_plugin' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-nutab.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_nutab_plugin() {

	$plugin = new NuTab();
	$plugin->run();

}
run_nutab_plugin();

// [hello] will return "this is the nutab wordpress plugin"
function nuliga_spielplan( $atts, $content = null ) {
    $a = shortcode_atts( array(
        'url' => '',
		'verein' => '',
    ), $atts );

	return "<div class=\"srsPlan\" srsUrl=\"" . esc_url($a['url']) . "\" srsVerein=\"" . esc_attr($a['verein']) . "\"></div>";
}

function nuliga_tabelle( $atts, $content = null ) {
    $a = shortcode_atts( array(
        'url' => '',
		'verein' => '',
    ), $atts );

	return "<div class=\"srsTable\" srsUrl=\"" . esc_url($a['url']) . "\" srsVerein=\"" . esc_attr($a['verein']) . "\"></div>";
}

add_shortcode('nuliga_spielplan', 'nuliga_spielplan');
add_shortcode('nuliga_tabelle', 'nuliga_tabelle');

function hook_nutab_script( ){
    wp_enqueue_script( 'fetch-table', plugin_dir_url( __FILE__ ) . 'js/fetch-table.js' );
    wp_localize_script( 'fetch-table', 'account_fetch_table', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'fail_message' => __('Connection to server failed. Check the credentials.', 'fetch-table'),
            'success_message' => __('Connection successful. ', 'fetch-table'),
			'loading_message' => __('Loading data from server. ', 'fetch-table')
	   )
    );
}
add_action( 'wp_enqueue_scripts', 'hook_nutab_script' );

function getNuLiga() {
	if (isset($_REQUEST)) {
		$url = $_REQUEST['url'];
		$jn = $_REQUEST['jn'] ?? 0;
		$spielplan = $_REQUEST['spielplan'] ?? 0;
		$spielplanverein = $_REQUEST['spielplanverein'] ?? 0;
		$von = $_REQUEST['von'] ?? "";
		$bis = $_REQUEST['bis'] ?? "";
		$alle = $_REQUEST['alle'] ?? 0;
		$aktuell = $_REQUEST['aktuell'] ?? 0;
		$cty = $_REQUEST['cty'] ?? "";
		$auchak = $_REQUEST['auchak'] ?? 0;
	}

	// die eigentliche Funktionalität ist in fetch_table.php
	// wir rufen diese Datei auf und geben die Parameter weiter
	$u = plugin_dir_url( __FILE__ ) . "fetch_table.php?url=".urlencode($url)."&jn=$jn&spielplan=$spielplan&spielplanverein=$spielplanverein&von=$von&bis=$bis&alle=$alle&aktuell=$aktuell&cty=$cty&auchak=$auchak";
	// echo "getNuLiga: calling $u";
	$r = file_get_contents($u);
	// echo "getNuLiga: called $u";
	echo $r;
}

add_action( 'wp_ajax_nopriv_getNuLiga_ajax', 'getNuLiga' );
add_action( 'wp_ajax_getNuLiga_ajax', 'getNuLiga' );

?>