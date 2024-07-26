<?php

// List of all public cpts to be exluded. These include common libraries like elementor, jetengine, etc. Can be extended to more popular plugins
$tmaupd_public_libs_cpt = [
    'e-landing-page',
    'jet-form-builder',
    'elementor_library',
    'ct_template'
];

// Function to display the settings page
if( !function_exists('tmaupd_render_page') ){
	function tmaupd_render_page() {
        global $plugin_page;
	    ?>
	    <div id="aupd-container" class="wrap">
            <h1><?php echo esc_html(get_admin_page_title());?></h1>
            <form method="post" action="<?php echo esc_url(admin_url('tools.php?page='.$plugin_page));?>">
                <?php
	                wp_nonce_field('tmaupd_plugin_nonce', 'tmaupd_plugin_nonce_field');
                	settings_fields('tmaupd_plugin_settings_group');
                	do_settings_sections('tmaupd_plugin_settings');
                	submit_button('Save Settings');
                ?>
            </form>
        </div>
	    <?php
	}
}

function tmaupd_plugin_settings_init() {
    add_settings_section('tmaupd_plugin_section', 'Plugin Settings', '__return_empty_string', 'tmaupd_plugin_settings');

    add_settings_field('tmaupd_plugin_mode_radio', 'Select plugin mode', 'tmaupd_plugin_mode_radio_callback', 'tmaupd_plugin_settings', 'tmaupd_plugin_section');
    add_settings_field('tmaupd_post_types_check', 'Select post types and taxonomies', 'tmaupd_post_types_check_callback', 'tmaupd_plugin_settings', 'tmaupd_plugin_section');
    add_settings_field('tmaupd_post_dates_update', 'Select date(s) to be updated', 'tmaupd_post_dates_update_callback', 'tmaupd_plugin_settings', 'tmaupd_plugin_section');
    add_settings_field('tmaupd_manual_date', 'Select date', 'tmaupd_manual_date_callback', 'tmaupd_plugin_settings', 'tmaupd_plugin_section');
    add_settings_field('tmaupd_auto_mode_period', 'Select frequency', 'tmaupd_auto_mode_period_callback', 'tmaupd_plugin_settings', 'tmaupd_plugin_section');
    add_settings_field('tmaupd_keep_log', 'Keep log', 'tmaupd_keep_log_callback', 'tmaupd_plugin_settings', 'tmaupd_plugin_section');

    register_setting('tmaupd_plugin_settings_group', 'tmaupd_plugin_mode_radio', 'sanitize_text_field');
    register_setting('tmaupd_plugin_settings_group', 'tmaupd_post_types_check', 'sanitize_text_field');
    register_setting('tmaupd_plugin_settings_group', 'tmaupd_post_dates_update', 'sanitize_text_field');
    register_setting('tmaupd_plugin_settings_group', 'tmaupd_manual_date', 'sanitize_text_field');
    register_setting('tmaupd_plugin_settings_group', 'tmaupd_auto_mode_period', 'sanitize_text_field');
    register_setting('tmaupd_plugin_settings_group', 'tmaupd_keep_log', 'sanitize_text_field');
}
add_action('admin_init', 'tmaupd_plugin_settings_init');

function tmaupd_plugin_mode_radio_callback() {
    $value = get_option('tmaupd_plugin_mode_radio');
    ?>
    <p>Set how you want to update post dates; manually or let the plugin automatically update post dates periodically.</p>
    <input id="tmaupd_plugin_mode_manual_radio" type="radio" name="tmaupd_plugin_mode_radio" value="manual_mode" <?php checked('manual_mode', $value); ?> required />
    <label for="tmaupd_plugin_mode_manual_radio">Manual</label>
    <br>
    <input id="tmaupd_plugin_mode_auto_radio" type="radio" name="tmaupd_plugin_mode_radio" value="auto_mode" <?php checked('auto_mode', $value); ?> />
    <label for="tmaupd_plugin_mode_auto_radio">Auto</label>
    <?php
}

function tmaupd_post_types_check_callback() {
    global $tmaupd_public_libs_cpt;
    $filter_mode = get_option('tmaupd_post_filter_mode');
    $filter_on = get_option('tmaupd_post_filter_mode_status');
    $filtered_pids = get_option('tmaupd_filter_ind_pid');   // array of all selected individual posts IDs
    $filtered_pids = $filtered_pids ? $filtered_pids : [];

    $tax_terms = get_option('tmaupd_filter_tax_terms');   // array of all selected taxonomy terms
    $tax_terms = $tax_terms ? $tax_terms : [];

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
    $postTypes = array_unique(array_diff($sitePostTypes, $tmaupd_public_libs_cpt));

    $available_taxonomies = get_object_taxonomies( $postTypes, 'object' );

    ?>
    <p>Select all the post types to be updated. <i><strong>NB: No posts will be updated if nothing is selected below.</i></strong></p>
    <?php
        foreach($postTypes as $cpt){
            $value = get_option('tmaupd_cpt_' . $cpt);
            $checked = ($value) ? 'checked' : '';
            $cpt_name = get_post_type_object($cpt)->labels->singular_name;
            
            echo '<input type="checkbox" id="cpt_' . esc_attr($cpt) . '" name="cpt_' . esc_attr($cpt) . '" value="cpt_' . esc_attr($cpt) . '"' . esc_attr($checked) .' />';
            echo '<label for="cpt_' . esc_attr($cpt) . '">' . esc_html($cpt_name) . '</label><br>';
        }

    ?>
    <br>
    <input id="tmaupd_post_filter_mode_status" type="checkbox" name="tmaupd_post_filter_mode_status" value="checked" <?php echo esc_attr($filter_on);?> />
    <label for="tmaupd_post_filter_mode_status">Filter posts</label>
    <br>
    <p>Select an option below to filter specific posts to be updated. Choose to filter by taxonomy or individual posts.</p>
    <sub>Please note that only the selected posts or posts that belong to the selected taxonomies will be updated. <i><strong>If want to update all posts belonging to a post type, untick this filter option and choose the relevant post type(s) above.</strong></i>
    </sub>
    <div id="filter-taxy-radio-group">
        <br>
        <input id="tmaupd_post_filter_mode_taxes" type="radio" name="tmaupd_post_filter_mode" value="taxonomy_mode" <?php checked('taxonomy_mode', $filter_mode); ?> />
        <label for="tmaupd_post_filter_mode_taxes">Taxonomies (e.g. categories, tags, etc.)</label>
    </div>
    <div id="filter-spost-radio-group">
        <input id="tmaupd_post_filter_mode_ind_posts" type="radio" name="tmaupd_post_filter_mode" value="individual_post_mode" <?php checked('individual_post_mode', $filter_mode); ?> />
        <label for="tmaupd_post_filter_mode_ind_posts">Specific posts</label>
    </div>
    <div id="aupd-taxonomy-posts">
    <br>
    <p>Filter by taxonomy: select posts to be updated from specific taxonomies such as categories.</p>
    <?php
    if ( $available_taxonomies ) {
        foreach($available_taxonomies as $taxonomy){
            $ctt_terms = get_terms($taxonomy->name);

            if ( wp_count_terms($taxonomy->name) != 0 ){
                echo '<p>' . esc_html($taxonomy->labels->name) . '</p>';
                foreach($ctt_terms as $term){
                    $ctt_name = $term->name;
                    $ctt_slug = $term->slug;
                    $ctt_tid = $term->term_taxonomy_id;
                    $ctt_checked = in_array($ctt_tid, $tax_terms) ? 'checked' : '';

                    echo '<input type="checkbox" id="ctt_' . esc_attr($ctt_slug) . '" name="tmaupd_ctt_term_' . esc_attr($ctt_slug) . '" value="' . esc_attr($ctt_tid) . '"' . esc_attr($ctt_checked) .' />';
                    echo '<label for="ctt_' . esc_attr($ctt_slug). '">' . ucfirst(esc_html($ctt_name)) . ' (' . absint($term->count) . ')</label><br>';
                }
                echo '<br>';
            }
        }
    } else {
        echo '<br><br>There are currently no posts assigned to any taxonomies. Add some posts to categories, tags, etc. and come back here to select the relevant taxonomies.<br><br>';
    }
    ?>
    </div>
    <div id="aupd-specific-posts-list">
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
        echo '<input type="checkbox" class="aupd-posts-checkbox aupd_post_checkall" id="aupd_post_checkall" name="tmaupd_post_checkall" />
            <label class="aupd-posts-cb-label" for="tmaupd_post_checkall">
                <span><svg width="12px" height="10px" viewbox="0 0 12 10"><polyline points="1.5 6 4.5 9 10.5 1"></polyline></svg></span>
                <span>Select/deselect all posts</span>
            </label><br>';

        foreach($all_posts as $post){
            $post_title = $post->post_title;
            $post_id = $post->ID;

            $is_present = in_array($post_id, $filtered_pids) ? 'checked' : '';
            
            echo '<input type="checkbox" class="aupd-posts-checkbox" id="aupd_post_' . absint($post_id) . '" name="tmaupd_ind_post_' . absint($post_id) . '" value="' . absint($post_id) . '"' . esc_attr($is_present) .' />';
            ?>

            <label class="aupd-posts-cb-label" for="tmaupd_post_<?php echo absint($post_id);?>">
                <span><svg width="12px" height="10px" viewbox="0 0 12 10"><polyline points="1.5 6 4.5 9 10.5 1"></polyline></svg></span>
                <span><?php echo esc_html($post_title);?></span>
            </label>
            <?php
        }
    }
    echo '</div></div>';
}

function tmaupd_post_dates_update_callback() {
    $value = get_option('tmaupd_post_dates_update');
    ?>
    <p>Select if the published date or modified date of the post should be updated, or both. Default is <i><strong>modified date</i></strong>.</p>
    <input id="tmaupd_post_dates_pub_mod_date" type="radio" name="tmaupd_post_dates_update" value="tmaupd_pub_mod_date" <?php checked('tmaupd_pub_mod_date', $value); ?> required />
    <label for="tmaupd_post_dates_pub_mod_date">Published & modified dates</label>
    <br>
    <input id="tmaupd_post_dates_pub_date" type="radio" name="tmaupd_post_dates_update" value="tmaupd_pub_date" <?php checked('tmaupd_pub_date', $value); ?> />
    <label for="tmaupd_post_dates_pub_date">Published date</label>
    <br>
    <input id="tmaupd_post_dates_mod_date" type="radio" name="tmaupd_post_dates_update" value="tmaupd_mod_date" <?php checked('tmaupd_mod_date', $value); ?> />
    <label for="tmaupd_post_dates_mod_date">Modified date</label>
    <?php
}

function tmaupd_manual_date_callback() {
    $value = get_option('tmaupd_manual_datetime');
    ?>
    <p>Set the date and time to be updated on all selected posts. Note that selecting a future date will set your post status to scheduled.</p>
    <br>
    <?php
        echo '<input id="aupd_manual_date_time" type="text" name="tmaupd_manual_datetime" ';
        if ($value) { echo 'value="' . esc_attr($value) . '"';}
        echo '/>';

        if ($value) {
            $formatDate = new DateTime($value);
            echo '<p><strong>Currently selected date/time:</strong> ' . esc_html($formatDate->format('D M d Y H:i:s')) . '</p>';
        }
}

function tmaupd_auto_mode_period_callback() {
    $value = get_option('tmaupd_auto_mode_freq');
    $offset_ticked = get_option('tmaupd_auto_mode_offset_mode');
    $offset_value = get_option('tmaupd_auto_mode_offset_value');
    $offset_unit = get_option('tmaupd_auto_mode_offset_unit');
    ?>
    <p>Set how frequently the post dates should be updated. Default is <i><strong>weekly</i></strong>.</p>
    <input id="tmaupd_auto_mode_period_daily" type="radio" name="tmaupd_auto_mode_freq" value="daily" <?php checked('daily', $value); ?> />
    <label for="tmaupd_auto_mode_period_daily">Daily</label>
    <br>
    <input id="tmaupd_auto_mode_period_weekly" type="radio" name="tmaupd_auto_mode_freq" value="weekly" <?php checked('weekly', $value); ?> />
    <label for="tmaupd_auto_mode_period_weekly">Weekly</label>
    <!-- <br>
    <input id="tmaupd_auto_mode_period_monthly" type="radio" name="tmaupd_auto_mode_freq" value="monthly" <?php //checked('monthly', $value); ?> />
    <label for="tmaupd_auto_mode_period_monthly">Monthly</label> -->
    <br><br>
    <input type="checkbox" id="tmaupd_auto_mode_period_offset" name="tmaupd_auto_mode_offset" value="checked" <?php echo esc_html($offset_ticked);?> />
    <label for="tmaupd_auto_mode_period_offset">Offset post dates?</label><br>
    <sub>Tick this option if you don't want all updated posts to have the same publish time and you would like to offset the selected posts by set time<br><i><strong>e.g. 15 mins offset means that if Post 1's date is 1 Jan 2024, 09:00, Post 2's date will be 1 Jan 2024, 09:15.</i></strong></sub>
    <br>
    <div class="aupd_auto_mode_period_offset_value">
        <input type="number" name="tmaupd_auto_mode_period_offset_value" min="1" max="60" <?php echo ($offset_value)? 'value="'.absint($offset_value).'"':''; ?> onkeyup="if(this.value > 60 || this.value < 1) this.value = 59;" />
        <select name="tmaupd_auto_mode_period_offset_unit">
            <option value="mins" <?php echo ($offset_unit === 'mins') ? 'selected' : ''; ?>>Mins</option>
            <option value="hours" <?php echo ($offset_unit === 'hours') ? 'selected' : ''; ?>>Hrs</option>
        </select>
    </div>
    <?php
}

function tmaupd_keep_log_callback() {
    $value = get_option('tmaupd_keep_log');
    ?>
    <p>Tick this to keep a log of posts that are updated.</p>
    <input id="tmaupd_keep_log" type="checkbox" name="tmaupd_keep_log" value="checked" <?php echo esc_attr($value);?> />
    <label for="tmaupd_keep_log">Keep log</label>
    <br>
    <?php
        // show log file content
        $log_file = trailingslashit(dirname(plugin_dir_path(__FILE__))) . 'aupd_log.txt';
        if (file_exists($log_file) && filesize($log_file) > 0) {
            $log_content = file_get_contents($log_file);
            echo '<div id="tmaupd_view_button"><button data-viewstate="false">View Log</button></div>';
            echo '<textarea id="tmaupd_log_area" readonly rows="20" cols="100">' . esc_html($log_content) . '</textarea>';
        }
}

function tmaupd_plugin_settings_action() {
    global $tmaupd_public_libs_cpt;
    // Verify nonce for security
    if (isset($_POST['tmaupd_plugin_nonce_field']) && wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['tmaupd_plugin_nonce_field']) ), 'tmaupd_plugin_nonce')) {
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
        $postTypes = array_unique(array_diff($sitePostTypes, $tmaupd_public_libs_cpt));
        $available_taxonomies = get_object_taxonomies( $postTypes, 'object' );

        // Retrieve form data and perform actions
        $plugin_mode_status = sanitize_text_field($_POST['tmaupd_plugin_mode_radio']);
        $post_filter_mode_status = sanitize_text_field($_POST['tmaupd_post_filter_mode_status']);
        $post_filter_mode = sanitize_text_field($_POST['tmaupd_post_filter_mode']);
        $aupd_post_filter_mode_ind_posts = [];  // array of all selected individual posts IDs
        $aupd_post_filter_mode_tax_terms = [];  // array of all selected taxonomy term IDs
        $update_date_mode = sanitize_text_field($_POST['tmaupd_post_dates_update']);
        $date_time_value = sanitize_text_field($_POST['tmaupd_manual_datetime']);
        $auto_freq = sanitize_text_field($_POST['tmaupd_auto_mode_freq']);
        $offset_mode = sanitize_text_field($_POST['tmaupd_auto_mode_offset']);
        $offset_mode_val = absint($_POST['tmaupd_auto_mode_period_offset_value']);
        $offset_mode_unit = sanitize_text_field($_POST['tmaupd_auto_mode_period_offset_unit']);
        $keep_logs = sanitize_text_field($_POST['tmaupd_keep_log']);
        $plugin_post_types = [];    // store selected post types

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
                if( isset($_POST['tmaupd_ind_post_' . $post_id]) ){
                    $aupd_post_filter_mode_ind_posts[] = $post_id;
                }
            }
        }

        if ( $available_taxonomies ) {
            foreach($available_taxonomies as $key => $value){
                $ctt_terms = get_terms($key);

                foreach($ctt_terms as $term){
                    $ctt_slug = $term->slug;
                    if( isset($_POST['tmaupd_ctt_term_' . $ctt_slug]) ){
                        $aupd_post_filter_mode_tax_terms[] = absint($_POST['tmaupd_ctt_term_' . $ctt_slug]);
                    }
                }
            }
        }

        // save all options as an array so options can be deleted easily on plugin uninstall
        $aupd_settings_all_options = [
            'tmaupd_plugin_mode_radio',
            'tmaupd_post_filter_mode_status',
            'tmaupd_post_filter_mode',
            'tmaupd_filter_ind_pid',
            'tmaupd_filter_tax_terms',
            'tmaupd_post_dates_update',
            'tmaupd_manual_datetime',
            'tmaupd_auto_mode_freq',
            'tmaupd_auto_mode_offset_mode',
            'tmaupd_auto_mode_offset_value',
            'tmaupd_auto_mode_offset_unit',
            'tmaupd_keep_log'
        ];

        // save user settings
        update_option('tmaupd_plugin_mode_radio', $plugin_mode_status);
        update_option('tmaupd_post_filter_mode_status', $post_filter_mode_status);
        update_option('tmaupd_post_filter_mode', $post_filter_mode);
        update_option('tmaupd_filter_ind_pid', $aupd_post_filter_mode_ind_posts);
        update_option('tmaupd_filter_tax_terms', $aupd_post_filter_mode_tax_terms);
        update_option('tmaupd_post_dates_update', $update_date_mode);
        update_option('tmaupd_manual_datetime', $date_time_value);
        update_option('tmaupd_auto_mode_freq', $auto_freq);
        update_option('tmaupd_auto_mode_offset_mode', $offset_mode);
        update_option('tmaupd_auto_mode_offset_value', $offset_mode_val);
        update_option('tmaupd_auto_mode_offset_unit', $offset_mode_unit);
        update_option('tmaupd_keep_log', $keep_logs);

        foreach($postTypes as $cpt){
            if( isset($_POST['cpt_' . $cpt]) ){
                update_option('tmaupd_cpt_' . $cpt, $cpt);
                $plugin_post_types[] = 'tmaupd_cpt_' . $cpt;
                $aupd_settings_all_options[] = 'tmaupd_cpt_' . $cpt;
            } else {
                delete_option('tmaupd_cpt_' . $cpt);  // clear all previously ticked cpts if any
            }
        }

        update_option('tmaupd_settings_all_options', $aupd_settings_all_options);

        // run function to update the dates based on plugin settings - only if required fields are not missing
        if (!empty($plugin_mode_status) && !empty($update_date_mode) && (!empty($plugin_post_types) || !empty($post_filter_mode))){
            tmaupd_runner_action();
        }
    }
}

// Hook to run the plugin action when the form is submitted
add_action('load-tools_page_tmaupd-settings', 'tmaupd_plugin_settings_action');

function tmaupd_runner_action(){
    global $wpdb;
    global $tmaupd_public_libs_cpt;

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
    $postTypes = array_unique(array_diff($sitePostTypes, $tmaupd_public_libs_cpt));

    $aupd_cpt_to_be_updated = [];   // array for all cpts to be updated

    foreach($postTypes as $cpt){
        $value = get_option('tmaupd_cpt_' . $cpt);
        if ($value) {
            $aupd_cpt_to_be_updated[] = $value;
        }
    }

    // retrieve plugin options
    $aupd_plugin_mode_radio = get_option('tmaupd_plugin_mode_radio');
    $aupd_post_filter_mode_status = get_option('tmaupd_post_filter_mode_status');
    $aupd_post_filter_mode = get_option('tmaupd_post_filter_mode');
    $aupd_filter_ind_pid = get_option('tmaupd_filter_ind_pid');
    $aupd_filter_tax_terms = get_option('tmaupd_filter_tax_terms');
    $aupd_post_dates_update = get_option('tmaupd_post_dates_update');
    $aupd_manual_datetime = ($aupd_plugin_mode_radio == 'manual_mode') ? get_option('tmaupd_manual_datetime') : null;
    $aupd_auto_mode_offset_mode = get_option('tmaupd_auto_mode_offset_mode');
    $aupd_auto_mode_offset_value = get_option('tmaupd_auto_mode_offset_value');
    $aupd_auto_mode_offset_unit = get_option('tmaupd_auto_mode_offset_unit');
    $keep_logs = get_option('tmaupd_keep_log') ?: false;

    // set dates to be updated based on selected option
    switch ($aupd_post_dates_update) {
        case 'tmaupd_pub_date':
            $dates = [
                'post_date'         =>  $aupd_manual_datetime,
                'post_date_gmt'     =>  get_gmt_from_date( $aupd_manual_datetime ),
            ];
            break;
        case 'tmaupd_mod_date':
            $dates = [
                'post_modified'     =>  $aupd_manual_datetime,
                'post_modified_gmt' =>  get_gmt_from_date( $aupd_manual_datetime ),
            ];
            break;
        case 'tmaupd_pub_mod_date':
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

    // check through the available taxonomies when the selected mode is taxonomies
    if ($aupd_plugin_mode_radio && $aupd_post_filter_mode == 'taxonomy_mode' && $aupd_post_filter_mode_status == 'checked'){
        if (!empty($aupd_filter_tax_terms)) {
            $args['tax_query'] = [
                'relation' => 'OR',
            ];

            foreach ($aupd_filter_tax_terms as $ctt) {
                $term = get_term_by('term', $ctt);

                $args['tax_query'][] = [
                    'taxonomy' => $term->taxonomy,
                    'field'    => 'term_taxonomy_id',
                    'terms'    => $ctt,
                ];
            }

            $postsTaxQuery = new WP_Query($args);

            if ($postsTaxQuery->have_posts()) {
                while ($postsTaxQuery->have_posts()) {
                    $postsTaxQuery->the_post();

                    $updated = $wpdb->update(
                        $wpdb->posts,
                        $dates,
                        ['ID' => get_the_ID()],
                        '%s',
                        '%d'
                    );

                    if ($keep_logs){
                        tmaupd_log_updates('Post: ' . get_the_title() . ' updated successfully');
                    }
                }
            }

            wp_reset_postdata();
        }
    }

    // specific posts mode - run the updates directly on selected posts and date options
    if ($aupd_plugin_mode_radio && $aupd_post_filter_mode == 'individual_post_mode' && $aupd_post_filter_mode_status == 'checked'){
        if (!empty($aupd_filter_ind_pid)) {
            foreach ($aupd_filter_ind_pid as $pid) {
                $updated = $wpdb->update(
                    $wpdb->posts,
                    $dates,
                    ['ID' => $pid],
                    '%s',
                    '%d'
                );
            }
        }
    }
    
    // if plugin is running in manual mode
    if ($aupd_plugin_mode_radio == 'manual_mode' && $aupd_post_filter_mode_status != 'checked'){
        $postsQuery = new WP_Query($args);

        if ($postsQuery->have_posts()) {
            while ($postsQuery->have_posts()) {
                $postsQuery->the_post();

                $updated = $wpdb->update(
                    $wpdb->posts,
                    $dates,
                    ['ID' => get_the_ID()],
                    '%s',
                    '%d'
                );
            }
        }

        wp_reset_postdata();
    }

    // if plugin is running in auto mode
    if ($aupd_plugin_mode_radio == 'auto_mode'){
        $upd_date_format = 'Y-m-d H:i:s';
        $current_date = current_time($upd_date_format);

        $postsQuery = new WP_Query($args);

        if ($postsQuery->have_posts()) {             
            while ($postsQuery->have_posts()) {
                $postsQuery->the_post();

                // set date to current date
                if ($aupd_post_dates_update == 'tmaupd_pub_date'){
                    $dates = [
                        'post_date'         =>  $current_date,
                        'post_date_gmt'     =>  get_gmt_from_date( $current_date ),
                    ];
                } elseif ($aupd_post_dates_update == 'tmaupd_pub_mod_date') {
                    $dates = [
                        'post_date'         =>  $current_date,
                        'post_date_gmt'     =>  get_gmt_from_date( $current_date ),
                        'post_modified'     =>  $current_date,
                        'post_modified_gmt' =>  get_gmt_from_date( $current_date ),
                    ];
                } else {
                    $dates = [
                        'post_modified'     =>  $current_date,
                        'post_modified_gmt' =>  get_gmt_from_date( $current_date ),
                    ];
                }

                $updated = $wpdb->update(
                    $wpdb->posts,
                    $dates,
                    ['ID' => get_the_ID()],
                    '%s',
                    '%d'
                );

                if ($aupd_auto_mode_offset_mode == 'checked') {
                    $offset = strtotime('+' . $aupd_auto_mode_offset_value . $aupd_auto_mode_offset_unit, strtotime($current_date));
                    $current_date = gmdate($upd_date_format, $offset);
                }
            }
        }

        wp_reset_postdata();
    }
}
add_action('tmaupd_cron_job_action', 'tmaupd_runner_action');

// cron job
function tmaupd_cron_job_runner(){
    $aupd_cron_freq = get_option('tmaupd_auto_mode_freq') ?: 'weekly';
    $aupd_plugin_mode = get_option('tmaupd_plugin_mode_radio');
    $aupd_cron_exists = wp_next_scheduled('tmaupd_cron_job_action');

    if (isset($aupd_plugin_mode)) {
        if ($aupd_plugin_mode == 'auto_mode'){
            if ($aupd_cron_exists){
                // only change the freq if the user changed it
                $aupd_event = wp_get_scheduled_event('tmaupd_cron_job_action');
                if ($aupd_event->schedule !== $aupd_cron_freq) {
                    wp_clear_scheduled_hook('tmaupd_cron_job_action');
                    wp_reschedule_event($aupd_cron_exists, $aupd_cron_freq, 'tmaupd_cron_job_action');
                }
            } else {
                wp_schedule_event(time(), $aupd_cron_freq, 'tmaupd_cron_job_action');
            }
        } else {
            if ($aupd_cron_exists){
                wp_clear_scheduled_hook('tmaupd_cron_job_action');
            }
        }
    }
}
add_action('wp_loaded', 'tmaupd_cron_job_runner');