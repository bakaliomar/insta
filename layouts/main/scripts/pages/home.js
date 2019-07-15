var Home = function () 
{  
    // server monitoring request handler
    var handleServersMonitor = function()
    {
        $('#show-server-monitoring').on('click',function(e){
            
            e.preventDefault();

            $('#show-server-monitoring').html("<i class='fa fa-spinner fa-spin'></i> Loading ...");
            $('#show-server-monitoring').attr('disabled','disabled');

            MailTng.blockUI({target:"#monitor-portlet-body"});
        
            $.ajax({
                type: 'post',
                url: MailTng.getBaseURL() + "/home/getServersMonitor.json",
                data :  {},
                dataType : 'JSON',
                success:function(result) 
                {
                    if(result !== null)
                    {
                        $('#show-server-monitoring').html('<i class="fa fa-desktop"></i> Update Monitoring');
                        $('#show-server-monitoring').removeAttr('disabled');
                     
                        // reinitialize table 
                        MailTng.clearTable('servers-list');
                        MailTng.updateTable('servers-list',result['data']);
                        
                        MailTng.unblockUI("#monitor-portlet-body");
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) 
                {
                    if(errorThrown != null && errorThrown != undefined && errorThrown != '') MailTng.alertBox({title:textStatus + ' : ' + errorThrown,type:"error",allowOutsideClick:"true",confirmButtonClass:"btn-danger"});
                }
            });
        });
        
        $('#show-server-monitoring').click();
    };
    
    // earnings handler
    var handleEarnings = function()
    {
        $.ajax({
            type: 'post',
            url: MailTng.getBaseURL() + "/home/getEarnings.json",
            data: {},
            dataType: 'JSON',
            success: function (result)
            {
                if (result !== null)
                {
                    $('#earnings').attr('data-value',result['earnings']);
                    $('#earnings').counterUp();
                }
            },
            error: function (jqXHR, textStatus, errorThrown)
            {
                if(errorThrown != null && errorThrown != undefined && errorThrown != '') MailTng.alertBox({title: textStatus + ' : ' + errorThrown, type: "error", allowOutsideClick: "true", confirmButtonClass: "btn-danger"});
            }
        });
    };
    
    // daily sent chart handler
    var handleDailySentReport = function()
    {
        $.ajax({
            type: 'post',
            url: MailTng.getBaseURL() + "/home/getDailySentReport.json",
            data: {},
            dataType: 'JSON',
            success: function (result)
            {
                if (result !== null)
                {
                    var lastIndex = new Date(new Date().getYear(),(new Date().getMonth() + 1), 0).getDate();
                    MailTng.createChart('daily-sent-report', [['Sent', result['sent']], ['Delivery', result['delivery']], ['Bounce', result['bounce']]], ["#8e44ad", "#32c5d2", "#e7505a"], lastIndex);
                }
            },
            error: function (jqXHR, textStatus, errorThrown)
            {
                if(errorThrown != null && errorThrown != undefined && errorThrown != '') MailTng.alertBox({title: textStatus + ' : ' + errorThrown, type: "error", allowOutsideClick: "true", confirmButtonClass: "btn-danger"});
            }
        });
    };
    
    // daily actions chart handler
    var handleDailyActionsReport = function()
    {
        $.ajax({
            type: 'post',
            url: MailTng.getBaseURL() + "/home/getDailyActionsReport.json",
            data: {},
            dataType: 'JSON',
            success: function (result)
            {
                if (result !== null)
                {
                    var lastIndex = new Date(new Date().getYear(),(new Date().getMonth() + 1), 0).getDate();
                    MailTng.createChart('daily-actions-report',[['Opens',result['opens']],['Clicks',result['clicks']],['Leads',result['leads']],['Unsubs',result['unsubs']]],["#3598dc", "#32c5d2", "#44b6ae" , "#e26a6a"],lastIndex);
                }
            },
            error: function (jqXHR, textStatus, errorThrown)
            {
                if(errorThrown != null && errorThrown != undefined && errorThrown != '') MailTng.alertBox({title: textStatus + ' : ' + errorThrown, type: "error", allowOutsideClick: "true", confirmButtonClass: "btn-danger"});
            }
        }); 
    };
    
    // monthly sent chart handler
    var handleMonthlySentReport = function()
    {
        $.ajax({
            type: 'post',
            url: MailTng.getBaseURL() + "/home/getMonthlySentReport.json",
            data: {},
            dataType: 'JSON',
            success: function (result)
            {
                if (result !== null)
                {
                    MailTng.createChart('monthly-sent-report', [['Sent', result['sent']], ['Delivery', result['delivery']], ['Bounce', result['bounce']]], ["#8e44ad", "#32c5d2", "#e7505a"], 12);
                }
            },
            error: function (jqXHR, textStatus, errorThrown)
            {
                if(errorThrown != null && errorThrown != undefined && errorThrown != '') MailTng.alertBox({title: textStatus + ' : ' + errorThrown, type: "error", allowOutsideClick: "true", confirmButtonClass: "btn-danger"});
            }
        });
    };
    
    // daily actions chart handler
    var handleMonthlyActionsReport = function()
    {
        $.ajax({
            type: 'post',
            url: MailTng.getBaseURL() + "/home/getMonthlyActionsReport.json",
            data: {},
            dataType: 'JSON',
            success: function (result)
            {
                if (result !== null)
                {
                    MailTng.createChart('monthly-actions-report',[['Opens',result['opens']],['Clicks',result['clicks']],['Leads',result['leads']],['Unsubs',result['unsubs']]],["#3598dc", "#32c5d2", "#44b6ae" , "#e26a6a"],12);
                }
            },
            error: function (jqXHR, textStatus, errorThrown)
            {
                if(errorThrown != null && errorThrown != undefined && errorThrown != '') MailTng.alertBox({title: textStatus + ' : ' + errorThrown, type: "error", allowOutsideClick: "true", confirmButtonClass: "btn-danger"});
            }
        }); 
    };

    return {
        init: function () 
        {
            handleServersMonitor();
            handleEarnings();
            handleDailySentReport();
            handleDailyActionsReport();
            handleMonthlySentReport();
            handleMonthlyActionsReport();
        },
        handleMonitoringDomainsDetails : function () 
        {
            $(".domains-check").on("click",function(evt){
                evt.preventDefault();
                var serverId = $(this).attr('data-server-id');
                $("#body-modal .modal-body").html("Loading Data ....."); 

                $.ajax({
                    type: 'post',
                    url: MailTng.getBaseURL() + "/home/getDomainsStatus/"+serverId+".json",
                    data :  {},
                    dataType : 'HTML',
                    success:function(result) 
                    {
                        if(result !== null)
                        {
                            $("#body-modal .modal-body").html(result);    
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) 
                    {
                        if(errorThrown != null && errorThrown != undefined && errorThrown != '') MailTng.alertBox({title: textStatus + ' : ' + errorThrown, type: "error", allowOutsideClick: "true", confirmButtonClass: "btn-danger"});
                    }
                });
            });
        }
    };
}();

// initialize and activate the script
$(function(){ Home.init(); });
