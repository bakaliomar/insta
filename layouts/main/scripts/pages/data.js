var Data = function () 
{
    // data retreiving change 
    var handleDataRetreivingChange = function () 
    {
        $('#isp,#data-flag').change(function() 
        {
            // clean the last results
            $("#data-count-help").html("Data Count : 0");
            $("#lists").html('<option value="">Select List ....</option>');
                
            var isp = $('#isp').val();
            var flag = $('#data-flag').val();

            if(isp !== undefined && isp !== '' && flag !== undefined && flag !== '')
            {    
                // show loading
                $("#lists").html('<option value="">Please Wait ....</option>');

                $.ajax({
                    type: 'post',
                    url: MailTng.getBaseURL() + "/data/getDataLists/" + isp + "/" + flag + ".json",
                    data :  {},
                    dataType : 'json',
                    success:function(result) 
                    {
                        if(result !== null)
                        {
                            $("#lists").html('');
                            
                            var lists = result['lists'];

                            for (var i in lists)
                            {
                                var value = lists[i];
                                $("#lists").append('<option value="'+value['id']+'">'+value['name']+'</option>').selectpicker('refresh');
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
    };
    
    // data seeds retreiving change 
    var handleDataSeedsRetreivingChange = function () 
    {
        $('#seeds-isp').change(function() 
        {
            // clean the last results
            $("#data-count-help").html("Data Count : 0");
            $("#lists").html('');
                
            var isp = $('#seeds-isp').val();

            if(isp !== undefined && isp !== '')
            {    
                // show loading
                $("#lists").html('');

                $.ajax({
                    type: 'post',
                    url: MailTng.getBaseURL() + "/data/getDataSeedsLists/" + isp + ".json",
                    data :  {},
                    dataType : 'json',
                    success:function(result) 
                    {
                        if(result !== null)
                        {
                            var lists = result['lists'];

                            for (var i in lists)
                            {
                                var value = lists[i];
                                $("#lists").append('<option value="'+value['id']+'">'+value['name']+'</option>').selectpicker('refresh'); 
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
        
        // trigger it at the begining 
        $('#seeds-isp').change();
    };
    
    // get the data list count
    var handleDataCount = function () 
    {
        $('#lists').change(function() 
        {
            if($(this).val() !== undefined && $(this).val() !== '')
            {
                $("#data-count-help").html("Please Wait ....");
                
                var listId = btoa($(this).val()).replace(/=/g,'_');
                
                $.ajax({
                    type: 'post',
                    url: MailTng.getBaseURL() + "/data/getDataListCount/"+listId+".json",
                    data :  {},
                    dataType : 'json',
                    success:function(result) 
                    {
                        if(result !== null)
                        {
                            $("#data-count-help").html("Data Count : " + result['count']);
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) 
                    {
                        MailTng.alertBox({title: textStatus + ' : ' + errorThrown, type: "error", allowOutsideClick: "true", confirmButtonClass: "btn-danger"});
                    }
                });
            }
        });
    };
 
    // get the data seeds emails
    var handleDataSeedsEmails = function () 
    {
        $('.seeds-lists').change(function() 
        {
            if($(this).val() !== undefined && $(this).val() !== '')
            { 
                var listId = btoa($(this).val()).replaceAll('=','_');
                
                $.ajax({
                    type: 'post',
                    url: MailTng.getBaseURL() + "/data/getDataListSeedsEmails/"+listId+".json",
                    data :  {},
                    dataType : 'json',
                    success:function(result) 
                    {
                        if(result !== null)
                        {
                            $('#emails').val(result['emails']);
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) 
                    {
                        MailTng.alertBox({title: textStatus + ' : ' + errorThrown, type: "error", allowOutsideClick: "true", confirmButtonClass: "btn-danger"});
                    }
                });
            }
        });
    };
    
    // get the data seeds emails
    var handleDownloadEmails = function () 
    {
        $('.download-data').click(function(e) 
        {
            e.preventDefault();
            var button = $(this);
            var html = $(this).html();
            $(this).html("<i class='fa fa-spinner fa-spin'></i> Loading ...");
            $(this).attr('disabled','disabled');
            var listname = $('#lists').val();
            
            if(listname !== undefined && listname !== '')
            { 
                $.ajax({
                    type: 'post',
                    url: MailTng.getBaseURL() + "/data/downloadData.json",
                    data :  {
                        'data-list' : listname
                    },
                    dataType : 'json',
                    success:function(result) 
                    {
                        if(result !== null)
                        {
                            var name = result['name'];
                            var content = result['content'];
                            var a         = document.createElement('a');
                            a.href        = 'data:attachment/csv,' +  encodeURIComponent(content);
                            a.target      = '_blank';
                            a.download    = name + '.csv';

                            document.body.appendChild(a);
                            a.click();
                            button.html(html);
                            button.removeAttr('disabled');
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) 
                    {
                        MailTng.alertBox({title: textStatus + ' : ' + errorThrown, type: "error", allowOutsideClick: "true", confirmButtonClass: "btn-danger"});
                    }
                });
            }
        });
    };
    
    
    // handle data switch
    var handleDataTypeSwitch = function()
    {
        $('#data-type-add').change(function(){
            var value = $(this).val();

            if(value == 'seeds')
            {
                $('#flag').prop('disabled',true)
            }
            else
            {
                $('#flag').prop('disabled',false) 
            }
        });
    };
    
    // update bounce progress
    var handleBounceProccessProgress = function () 
    {
        $(".update-bounce-progress").click(function(evt)
        {
            evt.preventDefault();
            
            var proccessId = $(this).attr('data-proccess-id');
            
            if(proccessId !== undefined && proccessId !== '')
            {
                $("#proccess-progress-" + proccessId).html('<i class="fa fa-spinner fa-spin"></i>');
                $("#proccess-emails-" + proccessId).html('<i class="fa fa-spinner fa-spin"></i>');
                
                $.ajax({
                    type: 'post',
                    url: MailTng.getBaseURL()+"data/updateBounceProgress/"+proccessId+".json",
                    data :  {},
                    dataType : 'json',
                    success:function(result) 
                    {
                        if(result !== null)
                        {
                            $("#proccess-progress-" + proccessId).html(result['progress']);
                            $("#proccess-emails-" + proccessId).html(result['emails']);
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) 
                    {
                        alert(errorThrown);
                    }
                });
            } 
        });
    };
    
    // Sponsors Change 
    var handleSponsorsChangeEvent = function () 
    {
        $('#sponsors').change(function() 
        {
            // clean the last results
            $("#creatives").html('').selectpicker('refresh');
            $("#from-names").html('').selectpicker('refresh');
            $("#subjects").html('').selectpicker('refresh');
            $('#generate-links').attr('offer-id','0');
            $("#offers").html('').selectpicker('refresh');
            $("#drop-body").val('');
            
            if($('#sponsors').val() !== undefined && $('#sponsors').val() !== '')
            {    
                MailTng.blockUI();

                var sponsorId = $('#sponsors').val();
         
                $.ajax({
                    type: 'post',
                    url: MailTng.getBaseURL() + "/mail/getOffers/"+sponsorId+".json",
                    data :  {},
                    dataType : 'json',
                    success:function(result) 
                    {
                        if(result !== null)
                        {
                            var lists = result['offers'];

                            for (var i in lists)
                            {
                                var value = lists[i];
                                $("#offers").append('<option value="'+value['id']+'">(' + value['production_id'] + ') '+ value['flag'] +' - '+value['name']+'</option>');
                            }
                            
                            // update the dropdown
                            $("#offers").selectpicker('refresh');
                            
                            MailTng.unblockUI();
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) 
                    {
                        MailTng.unblockUI();
                        MailTng.alertBox({title:textStatus + ' : ' + errorThrown,type:"error",allowOutsideClick:"true",confirmButtonClass:"btn-danger"});
                    }
                });
            }
        });
    };
    
    // update suppression progress
    var handleSuppressionProccessProgress = function () 
    {
        $(".update-suppression-progress").click(function(evt)
        {
            evt.preventDefault();
            
            var proccessId = $(this).attr('data-proccess-id');
            
            if(proccessId !== undefined && proccessId !== '')
            {
                $("#proccess-progress-" + proccessId).html('<i class="fa fa-spinner fa-spin"></i>');
                $("#proccess-emails-" + proccessId).html('<i class="fa fa-spinner fa-spin"></i>');
                
                $.ajax({
                    type: 'post',
                    url: MailTng.getBaseURL()+"data/updateSuppressionProgress/"+proccessId+".json",
                    data :  {},
                    dataType : 'json',
                    success:function(result) 
                    {
                        if(result !== null)
                        {
                            $("#proccess-progress-" + proccessId).html(result['progress']);
                            $("#proccess-emails-" + proccessId).html(result['emails']);
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) 
                    {
                        alert(errorThrown);
                    }
                });
            } 
        });
    };
    
    return {
        init: function () 
        {
            handleDataRetreivingChange();
            handleDataSeedsRetreivingChange();
            handleDataCount();
            handleDataSeedsEmails();
            handleDataTypeSwitch();
            handleBounceProccessProgress();
            handleSuppressionProccessProgress();
            handleSponsorsChangeEvent();
            handleDownloadEmails();
        }
    };

}();

// initialize and activate the script
$(function(){ Data.init(); });