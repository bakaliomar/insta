var Stats = function () 
{
    var handleDatePickers = function () 
    {
        if (jQuery().datepicker) 
        {
            $('.date-picker').datepicker({
                rtl: MailTng.isRTL(),
                orientation: "left",
                format: 'yyyy-mm-dd',
                autoclose: true
            });
            $('body').removeClass("modal-open");
        }
    };
    
    var handleCsvExport = function () 
    {
        $("#export-csv").click(function(evt){
            evt.preventDefault();
            var table = atob($(this).attr('data-table'));
            var a = document.createElement('a');
            a.href = 'data:attachment/csv,' +  encodeURIComponent(table);
            a.target = '_blank';
            a.download = 'ipstats.csv';
            document.body.appendChild(a);
            a.click();
        });
    };

    return {
        init: function () 
        {
            handleDatePickers();
            handleCsvExport();
        },
        handleStatsDetails : function () 
        {
            $(".stats-details").click(function(evt){
                evt.preventDefault();

                // empty the previous
                $("#sub-rows-modal .modal-title").html(""); 
                $("#sub-rows-modal .modal-body").html("");

                var detailsEncoded = $(this).attr('data-details');

                var server = $(this).attr('data-server');
                var ip = $(this).attr('data-ip');
                var details = atob(detailsEncoded);

                if(details != undefined)
                {
                    $("#sub-rows-modal .modal-title").html("Statistics Details For " + server + " : " + ip); 
                    $("#sub-rows-modal .modal-body").html(details); 
                }
            });
        }
    };
}();

// initialize and activate the script
$(function(){ Stats.init(); });
