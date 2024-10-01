<?php
/*
Plugin Name: Auto Update Post Date
Description: Keep your WordPress content evergreen with Auto Update Post Date – a simple WP plugin designed to effortlessly update your posts and boost SEO. Bid farewell to outdated information as this plugin takes care of the heavy lifting for you!
Version:     	1.0.2
Author:      	Temak
Author URI:  	https://temak.dev
Plugin URI:  	https://github.com/git-temak/auto-update-post-date
License:     	GPL3 or later
License URI: 	https://www.gnu.org/licenses/gpl-3.0.html
Text Domain: 	auto-update-post-date
Domain Path:    /languages

*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require __DIR__ . '/inc/auto-update-post-date-runner.php';

// Add menu page
if( !function_exists('tmaupd_menu_page') ){
	function tmaupd_menu_page() {
	    add_submenu_page(
	        'tools.php',
	        'Auto Update Post Date Settings',
	        'Auto Update Post Date',
	        'manage_options',
	        'tmaupd-settings',
	        'tmaupd_render_page'
	    );
	}
}

add_action('admin_menu', 'tmaupd_menu_page');

// log events to file
function tmaupd_log_updates($log, $date = true){
    $filename = plugin_dir_path(__FILE__). 'aupd_log.txt';
	if ($date){
		file_put_contents($filename, $log . ' - ' . date('Y-m-d H:i:s') . "\n\n", FILE_APPEND);
	} else {
		file_put_contents($filename, $log . "\n\n", FILE_APPEND);
	}
}

// Load script and styles
function tmaupd_load_scripts_styles()
{
    wp_enqueue_script('cs-jsDatetimePicker', plugin_dir_url(__FILE__) . 'inc/jquery.datetimepicker.full.min.js', array('jquery'), '1.0.0', true);
    wp_enqueue_style('cs-jsDatetimePickerStyle', plugin_dir_url(__FILE__) . 'inc/jquery.datetimepicker.min.css', array(), '1.0.0', 'all');
    wp_enqueue_script('tmaupdscript', plugin_dir_url(__FILE__) . 'inc/aupd.js', array('jquery'), '1.0.1', true);
    wp_enqueue_style('tmaupdstyles', plugin_dir_url(__FILE__) . 'inc/aupd.css', array(), '1.0.1', 'all');
}

add_action('admin_enqueue_scripts', 'tmaupd_load_scripts_styles');

// plugin uninstallation
register_uninstall_hook( __FILE__, 'tmaupd_plugin_uninstall' );
function tmaupd_plugin_uninstall() {
	$aupd_plugin_settings = get_option('tmaupd_settings_all_options');

	// delete all saved plugin options on uninstall
	foreach ($aupd_plugin_settings as $aupd_setting){
	    delete_option( $aupd_setting );
	}

    delete_option('tmaupd_settings_all_options');
}

?>