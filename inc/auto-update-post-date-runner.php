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
    <p>Set how you want to update post dates; manually or let the plugin automatically update post dates periodically.</p>
    <input id="aupd_plugin_mode_manual_radio" type="radio" name="aupd_plugin_mode_radio" value="manual_mode" <?php checked('manual_mode', $value); ?> />
    <label for="aupd_plugin_mode_manual_radio">Manual</label>
    <br>
    <input id="aupd_plugin_mode_auto_radio" type="radio" name="aupd_plugin_mode_radio" value="auto_mode" <?php checked('auto_mode', $value); ?> />
    <label for="aupd_plugin_mode_auto_radio">Auto</label>
    <?php
}

function aupd_post_types_check_callback() {
    global $public_libs_cpt;
    $filter_mode = get_option('aupd_post_filter_mode', true);
    $filtered_pids = get_option('aupd_filter_ind_pid');   // array of all selected individual posts IDs
    $filtered_pids = $filtered_pids ? $filtered_pids : [];

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
    <p>Select an option below to filter specific posts to be updated. Choose to filter by taxonomy or individual posts.</p>
    <sub>Please note that only the selected posts or posts that belong to the selected taxonomies will be updated.
        <br><i><strong>If want to update all posts belonging to a post type, untick this filter option and choose the relevant post type(s) above.</strong></i>
    </sub>
    <br>
    <br>
    <input id="aupd_post_filter_mode_taxes" type="radio" name="aupd_post_filter_mode" value="taxonomy_mode" <?php checked('taxonomy_mode', $filter_mode); ?> />
    <label for="aupd_post_filter_mode_taxes">Taxonomies (e.g. categories, tags, etc.)</label>
    <br>
    <input id="aupd_post_filter_mode_ind_posts" type="radio" name="aupd_post_filter_mode" value="individual_post_mode" <?php checked('individual_post_mode', $filter_mode); ?> />
    <label for="aupd_post_filter_mode_ind_posts">Specific posts</label>
    <br>
    <br>
    <p>Filter by taxonomy: select posts to be updated from specific taxonomies such as categories.</p>
    <?php
    if ( $available_taxonomies ) {
        foreach($available_taxonomies as $taxonomy){
            $ctt_name = $taxonomy->name;
            $ctt_value = get_option('aupd_ctt_' . $ctt_name, true);
            $ctt_checked = (is_string($ctt_value)) ? 'checked' : '';
            
            echo '<input type="checkbox" id="ctt_' . $ctt_name . '" name="ctt_' . $ctt_name . '" value="ctt_' . $ctt_name . '"' . $ctt_checked .' />';
            echo '<label for="ctt_' . $ctt_name. '">' . $taxonomy->labels->name . '</label><br>';
        }
    };
    ?>
    <br>
    <p>Select specific posts</p>
    <sub>Please note that this list shows all published posts from all registered posts types on the site.</strong></sub>
    <br>
    <br>
    <div class="aupd-all-posts-list">
    <?php
    $all_posts = get_posts(
        array(
          'numberposts' => -1,
          'post_status' => 'publish',
          'post_type'   => $postTypes
        )
    );
    if ( $all_posts ) {
        foreach($all_posts as $post){
            $post_title = $post->post_title;
            $post_id = $post->ID;

            $is_present = in_array($post_id, $filtered_pids) ? 'checked' : '';
            
            echo '<input type="checkbox" class="aupd-posts-checkbox" id="aupd_post_' . $post_id . '" name="aupd_ind_post_' . $post_id . '" value="' . $post_id . '"' . $is_present .' />';
            ?>

            <label class="aupd-posts-cb-label" for="aupd_post_<?=$post_id;?>">
                <span><svg width="12px" height="10px" viewbox="0 0 12 10"><polyline points="1.5 6 4.5 9 10.5 1"></polyline></svg></span>
                <span><?=$post_title;?></span>
            </label><br>
            <?php
        }
    }
    echo '</div>';
}

function aupd_post_dates_update_callback() {
    $value = get_option('aupd_post_dates_update', true);
    ?>
    <p>Select if the published date or modified date of the post should be updated, or both.</p>
    <br>
    <input id="aupd_post_dates_pub_mod_date" type="radio" name="aupd_post_dates_update" value="aupd_pub_mod_date" <?php checked('aupd_pub_mod_date', $value); ?> />
    <label for="aupd_post_dates_pub_mod_date">Published & modified dates</label>
    <br>
    <input id="aupd_post_dates_pub_date" type="radio" name="aupd_post_dates_update" value="aupd_pub_date" <?php checked('aupd_pub_date', $value); ?> />
    <label for="aupd_post_dates_pub_date">Published date</label>
    <br>
    <input id="aupd_post_dates_mod_date" type="radio" name="aupd_post_dates_update" value="aupd_mod_date" <?php checked('aupd_mod_date', $value); ?> />
    <label for="aupd_post_dates_mod_date">Modified date</label>
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

function aupd_plugin_settings_action() {
    global $public_libs_cpt;
    // Verify nonce for security
    if (isset($_POST['aupd_plugin_nonce_field']) && wp_verify_nonce($_POST['aupd_plugin_nonce_field'], 'aupd_plugin_nonce')) {

        // Retrieve form data and perform actions
        $radio_button_value = sanitize_text_field($_POST['aupd_plugin_mode_radio']);
        $post_filter_mode = sanitize_text_field($_POST['aupd_post_filter_mode']);
        $aupd_post_filter_mode_ind_posts = [];  // array of all selected individual posts IDs
        $update_date_mode = sanitize_text_field($_POST['aupd_post_dates_update']);
        $date_time_value = sanitize_text_field($_POST['aupd_manual_datetime']);
        $auto_freq = sanitize_text_field($_POST['aupd_auto_mode_freq']);
        $offset_mode = sanitize_text_field($_POST['aupd_auto_mode_offset']);
        $offset_mode_val = absint($_POST['aupd_auto_mode_period_offset_value']);
        $offset_mode_unit = sanitize_text_field($_POST['aupd_auto_mode_period_offset_unit']);

        $all_posts = get_posts(
            array(
              'numberposts' => -1,
              'post_status' => 'publish',
              'post_type'   => $postTypes
            )
        );

        if ( $all_posts ) {
            foreach($all_posts as $post){
                $post_id = $post->ID;
                if( isset($_POST['aupd_ind_post_' . $post_id]) ){
                    $aupd_post_filter_mode_ind_posts[] = $post_id;
                }
            }
        }

        if ( $available_taxonomies ) {
            foreach($available_taxonomies as $taxonomy){
                $ctt_name = $taxonomy->name;
                if( isset($_POST['aupd_ind_post_' . $ctt_name]) ){
                    update_option('aupd_ctt_' . $ctt_name, $ctt_name);
                }
            }
        }

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

        // save all options as an array so options can be deleted easily on delete
        $aupd_settings_all_options = [
            'aupd_plugin_mode_radio',
            'aupd_post_filter_mode',
            'aupd_filter_ind_pid',
            'aupd_post_dates_update',
            'aupd_manual_datetime',
            'aupd_auto_mode_freq',
            'aupd_auto_mode_offset_mode',
            'aupd_auto_mode_offset_value',
            'aupd_auto_mode_offset_unit'
        ];

        // save user form values
        update_option('aupd_plugin_mode_radio', $radio_button_value);
        update_option('aupd_post_filter_mode', $post_filter_mode);
        update_option('aupd_filter_ind_pid', $aupd_post_filter_mode_ind_posts);
        update_option('aupd_post_dates_update', $update_date_mode);
        update_option('aupd_manual_datetime', $date_time_value);
        update_option('aupd_auto_mode_freq', $auto_freq);
        update_option('aupd_auto_mode_offset_mode', $offset_mode);
        update_option('aupd_auto_mode_offset_value', $offset_mode_val);
        update_option('aupd_auto_mode_offset_unit', $offset_mode_unit);

        foreach($postTypes as $cpt){
            if( isset($_POST['cpt_' . $cpt]) ){
                update_option('aupd_cpt_' . $cpt, $cpt);
                $aupd_settings_all_options[] = 'aupd_cpt_' . $cpt;
            }
        }

        if ( $available_taxonomies ) {
            foreach($available_taxonomies as $taxonomy){
                $ctt_name = $taxonomy->name;
                if( isset($_POST['ctt_' . $ctt_name]) ){
                    update_option('aupd_ctt_' . $ctt_name, $ctt_name);
                    $aupd_settings_all_options[] = 'aupd_ctt_' . $ctt_name;
                }
            }
        }

        update_option('aupd_settings_all_options', $aupd_settings_all_options);

        // run function to update the dates based on plugin settings
        // aupd_runner_action();
    }
}

// Hook to run the plugin action when the form is submitted
add_action('load-tools_page_aupd-settings', 'aupd_plugin_settings_action');

function aupd_runner_action(){
    global $public_libs_cpt;

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

    $aupd_cpt_to_be_updated = [];   // array for all cpts to be updated
    $aupd_ctt_to_be_updated = [];   // array for all taxonomies to be updated

    foreach($postTypes as $cpt){
        $value = get_option('aupd_cpt_' . $cpt, true);
        $aupd_cpt_to_be_updated[] = $value;
    }

    // retrieve plugin options
    $aupd_plugin_mode_radio = get_option('aupd_plugin_mode_radio', true);
    $aupd_post_filter_mode = get_option('aupd_post_filter_mode', true);
    $aupd_filter_ind_pid = get_option('aupd_filter_ind_pid', true);
    $aupd_post_dates_update = get_option('aupd_post_dates_update', true);
    $aupd_manual_datetime = ($aupd_plugin_mode_radio == 'manual_mode') ? get_option('aupd_manual_datetime', true) : null;
    $aupd_auto_mode_freq = get_option('aupd_auto_mode_freq', true);
    $aupd_auto_mode_offset_mode = get_option('aupd_auto_mode_offset_mode', true);
    $aupd_auto_mode_offset_value = get_option('aupd_auto_mode_offset_value', true);
    $aupd_auto_mode_offset_unit = get_option('aupd_auto_mode_offset_unit', true);

    // set dates to be updated based on selected option
    switch ($aupd_post_dates_update) {
        case 'aupd_pub_date':
            $dates = [
                'post_date'         =>  $aupd_manual_datetime,
                'post_date_gmt'     =>  get_gmt_from_date( $aupd_manual_datetime ),
            ];
            break;
        case 'aupd_mod_date':
            $dates = [
                'post_modified'     =>  $aupd_manual_datetime,
                'post_modified_gmt' =>  get_gmt_from_date( $aupd_manual_datetime ),
            ];
            break;
        case 'aupd_pub_mod_date':
            $dates = [
                'post_date'         =>  $aupd_manual_datetime,
                'post_date_gmt'     =>  get_gmt_from_date( $aupd_manual_datetime ),
                'post_modified'     =>  $aupd_manual_datetime,
                'post_modified_gmt' =>  get_gmt_from_date( $aupd_manual_datetime ),
            ];
            break;
        default:
            $dates = [
                'post_modified'     =>  $aupd_manual_datetime,
                'post_modified_gmt' =>  get_gmt_from_date( $aupd_manual_datetime ),
            ];
            break;
    }

    // query arguments
    $args = [
        'post_type' => $aupd_cpt_to_be_updated,
        'posts_per_page' => -1,
    ];

    // check through the available categories when the selected mode is taxonomies
    if ($aupd_post_filter_mode == 'taxonomy_mode'){
        $available_taxonomies = get_object_taxonomies( $postTypes );

        foreach($available_taxonomies as $ctt){
            $value = get_option('aupd_ctt_' . $ctt, true);
            $aupd_ctt_to_be_updated[] = $value;
        }

        if (!empty($aupd_ctt_to_be_updated)) {
            $args['tax_query'] = [
                'relation' => 'OR',
            ];

            foreach ($aupd_ctt_to_be_updated as $ctt) {
                $taxonomy = $ctt;
                $terms    = get_terms($taxonomy);

                $args['tax_query'][] = [
                    'taxonomy' => $ctt,
                    'field'    => 'slug',
                    'terms'    => wp_list_pluck($terms, 'slug'),
                ];
            }
        }
    }

    // specific posts mode - run the updates directly on selected posts and date options
    if ($aupd_post_filter_mode == 'individual_post_mode'){
        if (!empty($aupd_filter_ind_pid)) {
            foreach ($aupd_filter_ind_pid as $pid) {
                $update_post_date = [
                    'ID'    =>  $pid,
                ];

                $update_post_date = array_merge($update_post_date, $dates);

                wp_update_post($update_post_date);
            }
        }
    }
    
    // if plugin is running in manual mode
    if ($aupd_plugin_mode_radio == 'manual_mode'){
        $postsQuery = new WP_Query($args);

        if ($postsQuery->have_posts()) {
            while ($postsQuery->have_posts()) {
                $postsQuery->the_post();

                $update_post_date = [
                    'ID'    =>  get_the_ID(),
                ];

                $update_post_date = array_merge($update_post_date, $dates);

                wp_update_post($update_post_date);
            }
        }

        wp_reset_postdata();

    }

    // if plugin is running in auto mode
    if ($aupd_plugin_mode_radio == 'auto_mode'){
        $upd_date_format = 'Y-m-d H:i:s';
        $current_date = date($upd_date_format);

        // set date to current date
        switch ($aupd_post_dates_update) {
            case 'aupd_pub_date':
                $dates = [
                    'post_date'         =>  $current_date,
                    'post_date_gmt'     =>  get_gmt_from_date( $current_date ),
                ];
                break;
            case 'aupd_mod_date':
                $dates = [
                    'post_modified'     =>  $current_date,
                    'post_modified_gmt' =>  get_gmt_from_date( $current_date ),
                ];
                break;
            case 'aupd_pub_mod_date':
                $dates = [
                    'post_date'         =>  $current_date,
                    'post_date_gmt'     =>  get_gmt_from_date( $current_date ),
                    'post_modified'     =>  $current_date,
                    'post_modified_gmt' =>  get_gmt_from_date( $current_date ),
                ];
                break;
            default:
                $dates = [
                    'post_modified'     =>  $current_date,
                    'post_modified_gmt' =>  get_gmt_from_date( $current_date ),
                ];
                break;
        }

        $postsQuery = new WP_Query($args);

        if ($postsQuery->have_posts()) {             
            while ($postsQuery->have_posts()) {
                $postsQuery->the_post();

                $update_post_date = [
                    'ID'    =>  get_the_ID(),
                ];

                if ($aupd_auto_mode_offset_mode == 'checked') {
                    $current_date = date($upd_date_format, strtotime($current_date . ' +' . $aupd_auto_mode_offset_value . $aupd_auto_mode_offset_unit));
                }

                $update_post_date = array_merge($update_post_date, $dates);
                wp_update_post($update_post_date);
            }
        }

        wp_reset_postdata();

    }

}
add_action('cron_update_aarp_posts_date', 'aupd_runner_action');

// cron job
function auto_update_aarp_posts_date(){
    $aupd_cron_freq = get_option('aupd_auto_mode_freq', true);
    $aupd_plugin_mode = get_option('aupd_plugin_mode_radio', true);

    if ($aupd_plugin_mode == 'auto_mode'){
        if (!wp_next_scheduled('cron_update_aarp_posts_date')){
            wp_schedule_event(time(), $aupd_cron_freq, 'cron_update_aarp_posts_date');
        }
    } else {
        if (wp_next_scheduled('cron_update_aarp_posts_date')){
            wp_clear_scheduled_hook('cron_update_aarp_posts_date');
        }
    }
}
add_action('wp_loaded', 'auto_update_aarp_posts_date');