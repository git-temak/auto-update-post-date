<?php
/*
Plugin Name: Auto Update Post Date
Description: Keep your WordPress content evergreen with Auto Update Post Date – a simple WP plugin designed to effortlessly update your posts and boost SEO. Bid farewell to outdated information as this plugin takes care of the heavy lifting for you!
Version:     	1.0.0
Author:      	Temak
Author URI:  	https://temak.dev
Plugin URI:  	https://github.com/git-temak/auto-update-post-date
License:     	GPL3
License URI: 	http://www.gnu.org/licenses/gpl-3.0.html
Text Domain: 	auto-update-post-date
Domain Path:    /languages

*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require __DIR__ . '/inc/auto-update-post-date-runner.php';

// Add menu page
if( !function_exists('add_aupd_menu_page') ){
	function add_aupd_menu_page() {
	    add_submenu_page(
	        'tools.php',
	        'Auto Update Post Date',
	        'Auto Update Post Date',
	        'manage_options',
	        'aupd-settings',
	        'render_aupd_page'
	    );
	}
}

add_action('admin_menu', 'add_aupd_menu_page');

// Load script and styles
function aupd_load_scripts_styles()
{
    wp_enqueue_script('aupdscript', plugin_dir_url(__FILE__) . 'inc/aupd.js');
    wp_enqueue_style('aupdstyles', plugin_dir_url(__FILE__) . 'inc/aupd.css');
}

// add_action('admin_enqueue_scripts', 'aupd_load_scripts_styles');

?>