<?php

// List of all public cpts to be exluded. These include common libraries like elementor, jetengine, etc. Can be extended to more popular plugins
$public_libs_cpt = [
    'e-landing-page',
    'jet-form-builder',
    'elementor_library'
];

// Function to display the settings page
if( !function_exists('render_aupd_page') ){
	function render_aupd_page() {
        global $plugin_page;
	    ?>
	    <div id="aupd-container" class="wrap">
	            <h1><?=esc_html(get_admin_page_title());?></h1>
	            <form method="post" action="<?=esc_url(admin_url('tools.php?page='.$plugin_page));?>">
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
    add_settings_field('aupd_post_types_check', 'Select post types and taxonomies', 'aupd_post_types_check_callback', 'aupd_plugin_settings', 'aupd_plugin_section');
    add_settings_field('aupd_post_dates_update', 'Select date(s) to be updated', 'aupd_post_dates_update_callback', 'aupd_plugin_settings', 'aupd_plugin_section');
    add_settings_field('aupd_manual_date', 'Select date', 'aupd_manual_date_callback', 'aupd_plugin_settings', 'aupd_plugin_section');
    add_settings_field('aupd_auto_mode_period', 'Select frequency', 'aupd_auto_mode_period_callback', 'aupd_plugin_settings', 'aupd_plugin_section');

    register_setting('aupd_plugin_settings_group', 'aupd_plugin_mode_radio', 'sanitize_text_field');
    register_setting('aupd_plugin_settings_group', 'aupd_post_types_check', 'sanitize_text_field');
    register_setting('aupd_plugin_settings_group', 'aupd_post_dates_update', 'sanitize_text_field');
    register_setting('aupd_plugin_settings_group', 'aupd_manual_date', 'sanitize_text_field');
    register_setting('aupd_plugin_settings_group', 'aupd_auto_mode_period', 'sanitize_text_field');
}
add_action('admin_init', 'aupd_plugin_settings_init');

function aupd_plugin_mode_radio_callback() {
    $value = get_option('aupd_plugin_mode_radio', true);
    ?>
    <p>Set if you want to update post dates manually or if you want the plugin to automatically update post dates periodically.</p>
    <br>
    <input id="aupd_plugin_mode_manual_radio" type="radio" name="aupd_plugin_mode_radio" value="manual_mode" <?php checked('manual_mode', $value); ?> />
    <label for="aupd_plugin_mode_manual_radio">Manual</label>
    <br>
    <input id="aupd_plugin_mode_auto_radio" type="radio" name="aupd_plugin_mode_radio" value="auto_mode" <?php checked('auto_mode', $value); ?> />
    <label for="aupd_plugin_mode_auto_radio">Auto</label>
    <?php
}

function aupd_post_types_check_callback() {
    global $public_libs_cpt;
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

    $sitePostTypes = array_unique(array_merge($defPostTypes, $cusPostTypes));
    $postTypes = array_unique(array_diff($sitePostTypes, $public_libs_cpt));

    $available_taxonomies = get_object_taxonomies( $postTypes, 'object' );

    ?>
    <p>Select all the post types to be updated.</p>
    <br>
    <?php
        foreach($postTypes as $cpt){
            $value = get_option('aupd_cpt_' . $cpt, true);
            $checked = ($value) ? 'checked' : '';
            $cpt_name = get_post_type_object($cpt)->labels->singular_name;
            
            echo '<input type="checkbox" id="cpt_' . $cpt . '" name="cpt_' . $cpt . '" value="cpt_' . $cpt . '"' . $checked .' />';
            echo '<label for="cpt_' . $cpt. '">' . $cpt_name . '</label><br>';
        }

    ?>
    <br>
    <p>Select post to be updated from specific taxonomies such as categories.</p>
    <br>
    <?php
    if ( $available_taxonomies ) {
        foreach($available_taxonomies as $taxonomy){
            $ctt_name = $taxonomy->name;
            $ctt_value = get_option('aupd_ctt_' . $ctt_name, true);
            $ctt_checked = (is_string($ctt_value)) ? 'checked' : '';
            
            echo '<input type="checkbox" id="ctt_' . $ctt_name . '" name="ctt_' . $ctt_name . '" value="ctt_' . $ctt_name . '"' . $ctt_checked .' />';
            echo '<label for="ctt_' . $ctt_name. '">' . $taxonomy->labels->name . '</label><br>';
        }
    }
}

function aupd_post_dates_update_callback() {
    $value = get_option('aupd_post_dates_update', true);
    ?>
    <p>Select if the published date or modified date of the post should be updated, or both.</p>
    <br>
    <input id="aupd_post_dates_pub_date" type="radio" name="aupd_post_dates_update" value="aupd_pub_date" <?php checked('aupd_pub_date', $value); ?> />
    <label for="aupd_post_dates_pub_date">Published date</label>
    <br>
    <input id="aupd_post_dates_mod_date" type="radio" name="aupd_post_dates_update" value="aupd_mod_date" <?php checked('aupd_mod_date', $value); ?> />
    <label for="aupd_post_dates_mod_date">Modified date</label>
    <br>
    <input id="aupd_post_dates_pub_mod_date" type="radio" name="aupd_post_dates_update" value="aupd_pub_mod_date" <?php checked('aupd_pub_mod_date', $value); ?> />
    <label for="aupd_post_dates_mod_date">Published & modified dates</label>
    <?php
}

function aupd_manual_date_callback() {
    $value = get_option('aupd_manual_datetime', true);
    ?>
    <p>Set the date and time to be updated on all selected posts. Note that selecting a future date will make your post status to be changed to scheduled.</p>
    <br>
    <?php
        echo '<input id="aupd_manual_date_time" type="text" name="aupd_manual_datetime" ';
        if ($value) { echo 'value="' . $value . '"';}
        echo '/>';
    ?>

    <?php 
        if ($value) {
            $formatDate = new DateTime($value);
            echo '<p><strong>Currently selected date/time:</strong> ' .
            $formatDate->format('D M d Y H:i:s') . '</p>';
        }
}

function aupd_auto_mode_period_callback() {
    $value = get_option('aupd_auto_mode_freq', true);
    $offset_ticked = get_option('aupd_auto_mode_offset_mode', true);
    $offset_value = get_option('aupd_auto_mode_offset_value', true);
    $offset_unit = get_option('aupd_auto_mode_offset_unit', true);
    ?>
    <p>Set how frequently the post dates should be updated.</p>
    <br>
    <input id="aupd_auto_mode_period_daily" type="radio" name="aupd_auto_mode_freq" value="daily" <?php checked('daily', $value); ?> />
    <label for="aupd_auto_mode_period_daily">Daily</label>
    <br>
    <input id="aupd_auto_mode_period_weekly" type="radio" name="aupd_auto_mode_freq" value="weekly" <?php checked('weekly', $value); ?> />
    <label for="aupd_auto_mode_period_weekly">Weekly</label>
    <br>
    <input id="aupd_auto_mode_period_monthly" type="radio" name="aupd_auto_mode_freq" value="monthly" <?php checked('monthly', $value); ?> />
    <label for="aupd_auto_mode_period_monthly">Monthly</label>
    <br><br>
    <input type="checkbox" id="aupd_auto_mode_period_offset" name="aupd_auto_mode_offset" value="checked" <?=$offset_ticked;?> />
    <label for="aupd_auto_mode_period_offset">Offset post dates?</label><br>
    <sub>Tick this option if you don't want all updated posts to have the same publish time and you would like to offset the selected posts by set time<br>e.g. 5 mins offset means that Post 2 date will be 5 mins after Post 1.</sub>
    <br>
    <div class="aupd_auto_mode_period_offset_value">
        <input type="number" name="aupd_auto_mode_period_offset_value" min="1" max="60" <?=($offset_value)? 'value="'.$offset_value.'"':''; ?> onkeyup="if(this.value > 60 || this.value < 1) this.value = 59;" />
        <select name="aupd_auto_mode_period_offset_unit">
            <option value="mins" <?=($offset_unit === 'mins') ? 'selected' : ''; ?>>Mins</option>
            <option value="hours" <?=($offset_unit === 'hours') ? 'selected' : ''; ?>>Hrs</option>
        </select>
    </div>
    <?php
}

function aupd_runner_action() {
    global $public_libs_cpt;
    // Verify nonce for security
    if (isset($_POST['aupd_plugin_nonce_field']) && wp_verify_nonce($_POST['aupd_plugin_nonce_field'], 'aupd_plugin_nonce')) {

        // Retrieve form data and perform actions
        $radio_button_value = sanitize_text_field($_POST['aupd_plugin_mode_radio']);
        $date_time_value = sanitize_text_field($_POST['aupd_manual_datetime']);
        $update_date_mode = sanitize_text_field($_POST['aupd_post_dates_update']);
        $auto_freq = sanitize_text_field($_POST['aupd_auto_mode_freq']);
        $offset_mode = sanitize_text_field($_POST['aupd_auto_mode_offset']);
        $offset_mode_val = absint($_POST['aupd_auto_mode_period_offset_value']);
        $offset_mode_unit = sanitize_text_field($_POST['aupd_auto_mode_period_offset_unit']);

        $defPostTypes = [
            'post',
            'page'
        ];

        // get CPTs
        $cusPostTypes = get_post_types([
           'public'   => true,
            '_builtin' => false
        ]);

        $sitePostTypes = array_unique(array_merge($defPostTypes, $cusPostTypes));
        $postTypes = array_unique(array_diff($sitePostTypes, $public_libs_cpt));
        $available_taxonomies = get_object_taxonomies( $postTypes, 'object' );

        // save user form values
        update_option('aupd_plugin_mode_radio', $radio_button_value);
        update_option('aupd_manual_datetime', $date_time_value);
        update_option('aupd_post_dates_update', $update_date_mode);
        update_option('aupd_auto_mode_freq', $auto_freq);
        update_option('aupd_auto_mode_offset_mode', $offset_mode);
        update_option('aupd_auto_mode_offset_value', $offset_mode_val);
        update_option('aupd_auto_mode_offset_unit', $offset_mode_unit);

        foreach($postTypes as $cpt){
            if( isset($_POST['cpt_' . $cpt]) ){
                update_option('aupd_cpt_' . $cpt, $cpt);
            }
        }

        if ( $available_taxonomies ) {
            foreach($available_taxonomies as $taxonomy){
                $ctt_name = $taxonomy->name;
                if( isset($_POST['ctt_' . $ctt_name]) ){
                    update_option('aupd_ctt_' . $ctt_name, $ctt_name);
                }
            }
        }
    }
}

// Hook to run the plugin action when the form is submitted
add_action('load-tools_page_aupd-settings', 'aupd_runner_action');