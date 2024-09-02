jQuery(document).ready(function($) {

    let manualModeChecked = $('#tmaupd_plugin_mode_manual_radio');
    let autoModeChecked = $('#tmaupd_plugin_mode_auto_radio');
    let taxonomyModeChecked = $('#tmaupd_post_filter_mode_taxes');
    let singPostModeChecked = $('#tmaupd_post_filter_mode_ind_posts');
    let taxonomyModeGroup = $('#filter-taxy-radio-group');
    let singPostModeGroup = $('#filter-spost-radio-group');
    let filterModeChecked = $('#tmaupd_post_filter_mode_status');
    let selectedDateTime = $('#aupd_manual_date_time');
    let dateTimeRow = $('#aupd-container .form-table tr:nth-child(4)');
    let autoFreqRow = $('#aupd-container .form-table tr:nth-child(5)');
    let taxPostsRow = $('#aupd-container #aupd-taxonomy-posts');
    let singPostPostsRow = $('#aupd-container #aupd-specific-posts-list');
    let autoOffsetChecked = $('#tmaupd_auto_mode_period_offset');
    let autoOffsetOptions = $('#aupd-container .aupd_auto_mode_period_offset_value');

    // add element to display selected date and time
    $('.form-table tr:nth-child(4) td').append('<p id="aupd-selected-date-time-val"></p>');

    // select or deselect all posts based on toggle checkbox
    $("#aupd_post_checkall").change(function () {
        $(".aupd-all-posts-list input[type='checkbox']:not(.aupd_post_checkall)").prop('checked', $(this).prop("checked"));
    });

    // format datetime picker to wp post format Y-m-d H:i:s
    function formatSelectedDateTime(date) {
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const day = String(date.getDate()).padStart(2, '0');
      const hours = String(date.getHours()).padStart(2, '0');
      const minutes = String(date.getMinutes()).padStart(2, '0');
      const seconds = String(date.getSeconds()).padStart(2, '0');

      const formattedDateTime = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
      return formattedDateTime;
    }

    // toggle datetime row visibility based on plugin mode
    function updateOptionRowVisibility() {
        if (manualModeChecked[0].checked) {
            dateTimeRow.show();
            autoFreqRow.hide();
            $('#aupd-container #tmaupd_auto_mode_period_daily').prop('required', false);
            $('#aupd-container .aupd_auto_mode_period_offset_value > input').val('');
        }
        if (autoModeChecked[0].checked) {
            dateTimeRow.hide();
            autoFreqRow.show();
            selectedDateTime.val('');
            $('#aupd-container #tmaupd_auto_mode_period_daily').prop('required', true);
        }
    }

    // toggle offset options visibility based on if checked/not
    function updateOffsetOptionVisibility() {
        if (autoOffsetChecked[0].checked) {
            autoOffsetOptions.show();
        } else {
            autoOffsetOptions.hide();
            $('#aupd-container .aupd_auto_mode_period_offset_value > input').val('');
        }
    }

    // toggle posts/taxonomies row visibility based on selected mode
    function togglePostsRowVisibility() {
        if (taxonomyModeChecked[0].checked) {
            taxPostsRow.show();
            singPostPostsRow.hide();
        }
        if (singPostModeChecked[0].checked) {
            taxPostsRow.hide();
            singPostPostsRow.show();
        }
    }

    // toggle offset options visibility based on if checked/not
    function toggleFilterModeRowVisibility() {
        if (filterModeChecked[0].checked) {
            taxonomyModeGroup.show();
            singPostModeGroup.show();
            togglePostsRowVisibility();
        } else {
            taxonomyModeGroup.hide();
            singPostModeGroup.hide();
            taxPostsRow.hide();
            singPostPostsRow.hide();
        }
    }

    manualModeChecked.add(autoModeChecked).change(updateOptionRowVisibility);
    taxonomyModeChecked.add(singPostModeChecked).change(togglePostsRowVisibility);
    filterModeChecked.change(toggleFilterModeRowVisibility);
    autoOffsetChecked.change(updateOffsetOptionVisibility);

    updateOptionRowVisibility();
    updateOffsetOptionVisibility();
    toggleFilterModeRowVisibility();

    curDate = selectedDateTime.val() ? selectedDateTime.val() : new Date();

    selectedDateTime.datetimepicker({
        value: curDate,
        // minDate: 0,
        // monthStart: new Date().getMonth(),
        yearStart: new Date().getFullYear(),
        format: 'Y-m-d H:i:s',
        inline: true,
    });

    selectedDateTime.change(function(){
        let selectedDateTimeVal = selectedDateTime.datetimepicker('getValue');
        selectedDateTime.val(formatSelectedDateTime(selectedDateTimeVal));
        $('#aupd-selected-date-time-val').html('<strong>Selected date/time:</strong> ' + selectedDateTimeVal);
    });

    // toggle visibility of log area
    $('#tmaupd_view_button').click(function() {
        $('#tmaupd_log_area').toggle();
    });

});