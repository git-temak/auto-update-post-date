<?php

// Function to display the settings page
if( !function_exists('render_aupd_page') ){
	function render_aupd_page() {
	    ?>
	    <div class="wrap">
	            <h1><?=esc_html(get_admin_page_title());?></h1>
	            <form method="post" action="<?=esc_url(admin_url('tools.php?page=aupd-settings'));?>">
	                <?php
		                wp_nonce_field('aupd_plugin_nonce', 'aupd_plugin_nonce_field');
	                	settings_fields('aupd_plugin_settings_group');
	                	do_settings_sections('aupd_plugin_settings');
	                	submit_button('Save Settings');
	                ?>
	            </form>
	        </div>
	    <?php
	}
}

function aupd_plugin_settings_init() {
    add_settings_section('aupd_plugin_section', 'Plugin Settings', '__return_empty_string', 'aupd_plugin_settings');

    add_settings_field('aupd_plugin_mode_radio', 'Select plugin mode', 'aupd_plugin_mode_radio_callback', 'aupd_plugin_settings', 'aupd_plugin_section');
    add_settings_field('aupd_post_types_check', 'Select post types', 'aupd_post_types_check_callback', 'aupd_plugin_settings', 'aupd_plugin_section');
    add_settings_field('aupd_manual_date', 'Select date', 'aupd_manual_date_callback', 'aupd_plugin_settings', 'aupd_plugin_section');

    register_setting('aupd_plugin_settings_group', 'aupd_plugin_mode_radio', 'sanitize_text_field');
    register_setting('aupd_plugin_settings_group', 'aupd_post_types_check', 'sanitize_text_field');
    register_setting('aupd_plugin_settings_group', 'aupd_manual_date', 'sanitize_text_field');
}
add_action('admin_init', 'aupd_plugin_settings_init');

function aupd_plugin_mode_radio_callback() {
    $value = get_option('aupd_plugin_mode_radio');
    ?>
    <p>Set if you want to update post dates manually or if you want the plugin to automatically update post dates.</p>
    <br>
    <input id="aupd_plugin_mode_manual_radio" type="radio" name="aupd_plugin_mode_radio" value="manual_mode" <?php checked('manual_mode', $value); ?> />
    <label for="aupd_plugin_mode_manual_radio">Manual</label>
    <br>
    <input id="aupd_plugin_mode_auto_radio" type="radio" name="aupd_plugin_mode_radio" value="auto_mode" <?php checked('auto_mode', $value); ?> />
    <label for="aupd_plugin_mode_auto_radio">Auto</label>
    <?php
}

function aupd_post_types_check_callback() {
    $value = get_option('aupd_post_types_check');

    // default WP post types
    $defPostTypes = [
        'post',
        'page'
    ];

    // get CPTs
    $cusPostTypes = get_post_types([
        'public' => true,
        '_builtin' => false
    ]);

    $postTypes = array_unique(array_merge($defPostTypes, $cusPostTypes));

    ?>
    <p>Select all the post types to be updated.</p>
    <br>
    <?php
        foreach($postTypes as $cpt){
            $value = get_option('aupd_cpt_' . $cpt);
            $checked = ($value) ? 'checked' : '';
            
            echo '<input type="checkbox" id="cpt_' . $cpt . '" name="cpt_' . $cpt . '" value="cpt_' . $cpt . '"' . $checked .' />';
            echo '<label for="cpt_' . $cpt. '">' . $cpt . '</label><br>';
        }
}

function aupd_manual_date_callback() {
    $value = get_option('aupd_manual_datetime');
    ?>
    <p>Set the date and time to be updated on all selected posts. Note that selecting a future date will make your post status to be changed to scheduled.</p>
    <br>
    <input id="aupd_manual_date_time" type="text" name="aupd_manual_datetime" />
    <?php
}


function run_plugin_action() {
    // Verify nonce for security
    if (isset($_POST['aupd_plugin_nonce_field']) && wp_verify_nonce($_POST['aupd_plugin_nonce_field'], 'aupd_plugin_nonce')) {

        // Retrieve form data and perform actions
        $radio_button_value = sanitize_text_field($_POST['aupd_plugin_mode_radio']);
        $date_time_value = sanitize_text_field($_POST['aupd_manual_datetime']);

        $defPostTypes = [
            'post',
            'page'
        ];

        // get CPTs
        $cusPostTypes = get_post_types([
           'public'   => true,
            '_builtin' => false
        ]);

        $postTypes = array_unique(array_merge($defPostTypes, $cusPostTypes));

        update_option('aupd_plugin_mode_radio', $radio_button_value);

        foreach($postTypes as $cpt){
            $cpt = sanitize_text_field($cpt);
            if(isset($_POST['cpt_' . $cpt])){
                update_option('aupd_cpt_' . $cpt, $cpt);
            }
        }

        update_option('aupd_manual_datetime', $date_time_value);

    }
}

// Hook to run the plugin action when the form is submitted
add_action('load-tools_page_aupd-settings', 'run_plugin_action');