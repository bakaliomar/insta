var Drops = function () 
{
    ////////////// General Section ////////////
    
    // Bootstrap DataPickers Event 
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

    return {
        init: function () 
        {
            // general section 
            handleDatePickers();
        },
        dropSettings : function()
        {
            // recalculate sent progress
            $(".recalculate-sent").on('click',function(evt)
            {
                evt.preventDefault();

                var dropId = $(this).attr('data-drop-id');

                if(dropId !== undefined && dropId !== '')
                {
                    $("#drop-progress-" + dropId).html('<center><i class="fa fa-spinner fa-spin"></i></center>');

                    $.ajax({
                        type: 'post',
                        url: MailTng.getBaseURL() + "/drops/recalculate/"+dropId+".json",
                        data :  {},
                        dataType : 'json',
                        success:function(result) 
                        {
                            if(result !== null)
                            {
                                $("#drop-progress-" + dropId).html('<center>' + result['sentProgress'] + '</center>');
                            }
                        },
                        error: function (jqXHR, textStatus, errorThrown) 
                        {
                            MailTng.alertBox({title: textStatus + ' : ' + errorThrown, type: "error", allowOutsideClick: "true", confirmButtonClass: "btn-danger"});
                        }
                    });
                } 
            });
            
            // display sent details
            $(".drop-details").on('click',function(evt)
            {
                evt.preventDefault();

                // empty the old details 
                $("#details-modal .modal-body").html('<img src="' + MailTng.getLayoutURL() + '/images/icons/loading.gif" alt="loading" />');

                var dropId = $(this).attr('data-drop-id');

                if(dropId !== undefined && dropId !== '')
                {
                    $.ajax({
                        type: 'post',
                        url: MailTng.getBaseURL() + "/drops/getDrop/"+dropId+".json",
                        data :  {},
                        dataType : 'json',
                        success:function(result) 
                        {
                            if(result !== null)
                            {
                                var drop = result['drop'];

                                if(drop != null)
                                {
                                    $("#details-modal .modal-body").html(drop);
                                }
                            }
                        },
                        error: function (jqXHR, textStatus, errorThrown) 
                        {
                            MailTng.alertBox({title: textStatus + ' : ' + errorThrown, type: "error", allowOutsideClick: "true", confirmButtonClass: "btn-danger"});
                        }
                    });
                } 
            });
        }
    };
}();

jQuery(document).ready(function() {
    Drops.init();
});
