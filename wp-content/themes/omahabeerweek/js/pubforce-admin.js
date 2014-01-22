jQuery(document).ready(function($) {
	$(".obwdate").datepicker({
	    dateFormat: 'mm/dd/yy',
	    showOn: 'button',
	    buttonImage: 'images/icon-datepicker.png',
	    buttonImageOnly: true,
	    numberOfMonths: 1,
        defaultDate: new Date(2014, 02, 01)
    });

    $('#obw_events_startdate_ampm').val($('#obw_events_startampm_hidden').val());
    $('#obw_events_startdate_minute').val($('#obw_events_startminute_hidden').val());
    $('#obw_events_startdate_hour').val($('#obw_events_starthour_hidden').val());

    $('#obw_events_enddate_ampm').val($('#obw_events_endampm_hidden').val());
    $('#obw_events_enddate_minute').val($('#obw_events_endminute_hidden').val());
    $('#obw_events_enddate_hour').val($('#obw_events_endhour_hidden').val());

    $('#post').submit(function() {
        if ($.trim($('#obw_events_desc').val()).length > 500) {
            alert('Please shorten the Description to less than 500 characters.\n(Current character count is ' + $.trim($('#obw_events_desc').val()).length.toString() + ')');

            return false;
        }
    });
});