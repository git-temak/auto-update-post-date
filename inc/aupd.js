jQuery(document).ready(function($) {

    let manualModeChecked = $('#aupd_plugin_mode_manual_radio');
    let autoModeChecked = $('#aupd_plugin_mode_auto_radio');
    let selectedDateTime = $('#aupd_manual_date_time');
    let dateTimeRow = $('#aupd-container .form-table tr:nth-child(3)');
    let autoFreqRow = $('#aupd-container .form-table tr:nth-child(4)');
    let autoOffsetChecked = $('#aupd_auto_mode_period_offset');
    let autoOffsetOptions = $('#aupd-container .aupd_auto_mode_period_offset_value');

    // add element to display selected date and time
    $('.form-table tr:nth-child(3) td').append('<p id="aupd-selected-date-time-val"></p>');

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
            $('#aupd-container .aupd_auto_mode_period_offset_value > input').val('');
        } else {
            dateTimeRow.hide();
            autoFreqRow.show();
            selectedDateTime.val('');
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

    manualModeChecked.add(autoModeChecked).change(updateOptionRowVisibility);
    autoOffsetChecked.change(updateOffsetOptionVisibility);

    updateOptionRowVisibility();
    updateOffsetOptionVisibility();

    selectedDateTime.datetimepicker({
        format: 'Y-m-d H:i:s',
        inline: true
    });

    selectedDateTime.change(function(){
        let selectedDateTimeVal = selectedDateTime.datetimepicker('getValue');
        selectedDateTime.val(formatSelectedDateTime(selectedDateTimeVal));
        $('#aupd-selected-date-time-val').html('<strong>Selected date/time:</strong> ' + selectedDateTimeVal);
    });

});