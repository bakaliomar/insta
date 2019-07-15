var Tools = function () 
{   
    // Form Type Switch Change
    var  handleNegativeTypeSwicth = function()
    {
        $('#negative-switch').on('switchChange.bootstrapSwitch', function(event, state) {
            if(state == true)
            {
                $('#send-form').slideUp(1000);
                $('#retrieve-form').slideDown(1000);
            }
            else
            {
                $('#send-form').slideDown(1000);
                $('#retrieve-form').slideUp(1000);
            }
        });
    };
    
    // Servers Change 
    var handleServersChangeEvent = function () 
    {
        $('#servers').on('change',function(e)
        {
            e.preventDefault();

            // clean the previous ips
            $("#ips").val(null);

            var serverId = $("#servers").val();

            if(serverId != undefined && serverId != null && parseInt(serverId) != NaN && serverId != 'null' && serverId != '')
            {   
                MailTng.blockUI();
                
                $("#ips").html('<option value="">Please Wait ...</option>');
                
                $.ajax({
                    type: 'post',
                    url: MailTng.getBaseURL() + "/tools/getIps/"+serverId+".json",
                    data :  {},
                    dataType : 'json',
                    async: false,
                    success:function(result) 
                    {
                        if(result !== null)
                        {
                            $("#ips").html('');

                            var ips = result['ips'];
                            
                            for (var i in ips)
                            {
                                var value = ips[i];
                                $("#ips").append('<option value="'+value['id']+'" >'+value['value']+' | '+value['rdns']+'</option>');
                            }

                            $("#ips").selectpicker('refresh');
                            MailTng.unblockUI();
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) 
                    {
                        MailTng.alertBox({title:textStatus + ' : ' + errorThrown,type:"error",allowOutsideClick:"true",confirmButtonClass:"btn-danger"});
                    }
                });
            }
        });
    };
 
    // Reset the header
    var handleHeaderReset = function () 
    {
        $("#reset-header").click(function(evt)
        {
            evt.preventDefault();
            
            // confirm the action
            swal({
                title: "Are you sure?",
                text: "Your will reset your current header values !",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-danger",
                confirmButtonText: "Yes, reset it!",
                closeOnConfirm: false
              },
              function(){
                  $("#header").val(atob($("#header").attr('data-original-header'))); 
                    swal("Completed!", "Your header has been reseted.", "success");
              });
        });
    };
    
    // Send Form Submit Buttons Click
    var handleRetrieveFormSubmitEvent = function () 
    {
        $("#retrieve-form").submit(function(e){
            e.preventDefault();
            return false;
        });
        
        $("#retrieve-form-button").click(function(e)
        {
            e.preventDefault();

            $('#retrieve-form-button').html("<i class='fa fa-spinner fa-spin'></i> Loading ...");
            $('#retrieve-form-button').attr('disabled','disabled');
            MailTng.blockUI({target:"#results"});
            
            // get the form data 
            var formData = new FormData($("#retrieve-form")[0]);
            var formURL = $("#retrieve-form").attr("action");

            $.ajax(
            {
                url: formURL,
                type: "POST",
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                dataType: 'JSON',
                success: function (data)
                {
                    if (data != null)
                    {
                        var words = data['words'];
                        $('#results-textarea').html(words);
                        $('#retrieve-form-button').html('<i class="fa fa-envelope"></i> Get Negative');
                        $('#retrieve-form-button').removeAttr('disabled');
                        MailTng.unblockUI("#results");
                    }
                },
                error: function (jqXHR, textStatus, errorThrown)
                {
                    MailTng.alertBox({title: textStatus + ' : ' + errorThrown, type: "error", allowOutsideClick: "true", confirmButtonClass: "btn-danger"});
                }
            });    
        });
    };
    
    // Send Form Submit Buttons Click
    var handleSendFormSubmitEvent = function () 
    {
        $("#send-form").submit(function(e){
            e.preventDefault();
            return false;
        });
        
        $("#send-form-button").click(function(e)
        {
            e.preventDefault();

            // add a confirmation to the form
            swal({
                title: "Form Confirmation",
                text: "You're about to procceed a negative send ",
                type: "info",
                showCancelButton: true,
                closeOnConfirm: false,
                showLoaderOnConfirm: true
            }, 
            function ()
            {
                // get the form data 
                 var formData = new FormData($("#send-form")[0]);
                 var formURL = $("#send-form").attr("action");

                 $.ajax(
                 {
                    url : formURL,
                    type: "POST",
                    data : formData,
                    cache: false,
                    contentType: false,
                    processData: false,
                    dataType: 'JSON',
                    success : function(data) 
                    {
                       if(data != null)
                       {
                          var button = (data['type'] == 'error') ? 'btn-danger' : 'btn-primary';
                          swal({title:data['message'],type:data['type'],allowOutsideClick:"true",confirmButtonClass:button});
                       }
                    },
                    error: function(jqXHR, textStatus, errorThrown) 
                    {
                       MailTng.alertBox({title:textStatus + ' : ' + errorThrown,type:"error",allowOutsideClick:"true",confirmButtonClass:"btn-danger"});
                    }
                }); 
            });   
        });
    };
    
    return {
        init: function () 
        {
            handleNegativeTypeSwicth();
            handleServersChangeEvent();
            handleHeaderReset();
            handleSendFormSubmitEvent();
            handleRetrieveFormSubmitEvent();
        }
    };

}();

// initialize and activate the script
$(function(){ Tools.init(); });