jQuery(document).ready(function($) {

    let manualModeChecked = $('#aupd_plugin_mode_manual_radio');
    let autoModeChecked = $('#aupd_plugin_mode_auto_radio');
    let selectedDateTime = $('#aupd_manual_date_time');
    let dateTimeRow = $('#aupd-container .form-table tr:nth-child(3)');
    $('.form-table tr:nth-child(3) td').append('<p id="aupd-selected-date-time-val"></p>');  // add element to display selected date and time

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

    if (manualModeChecked[0].checked === true) {
        dateTimeRow.show();
    }

    manualModeChecked.change(function(){
        if (manualModeChecked[0].checked === true) {
            dateTimeRow.show();
        }
    });

    autoModeChecked.change(function(){
        if (autoModeChecked[0].checked === true) {
            dateTimeRow.hide();
            selectedDateTime.val('');
        }
    });

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