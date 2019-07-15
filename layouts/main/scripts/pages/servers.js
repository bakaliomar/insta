var Servers = function () 
{   
    var handleDatePickers = function () {

        if (jQuery().datepicker) {
            $('.date-picker').datepicker({
                rtl: MailTng.isRTL(),
                orientation: "right",
                autoclose: true
            });
        }

        $( document ).scroll(function(){
            $('#form_modal2 .date-picker').datepicker('place');
        });
    }
    
    var handleInstallingServers = function () 
    { 
        $('#instalation-form-submit').click(function(event) 
        {
            event.preventDefault();
            
            var domainsMapping = "";
            var selectedDomains = [];
            var serverId = $('#server-id').val();
            var serverVersion = $('#server-version').val();
            var installServices = $('#install-services').val();
            var updateIps = $('#update-ips').val();
            var updateRecords = $('#update-records').val();
            var installMailScripts = $('#install-mail-scripts').val();
            var installTracking = $('#install-tracking').val();
            var installWebmail = $('#install-webmail').val();
            var installPMTA = $('#install-pmta').val();
            var useSubDomains = $('#use-subdomains').val();
            
            // check if there is a server selected 
            if(serverId == null || serverId == '')
            {
                MailTng.alertBox({title: 'No Server Selected !', type: "error", allowOutsideClick: "true", confirmButtonClass: "btn-danger"});
                return false;
            }
            
            // check if at least on of the installation procceses is enabled 
            if(updateIps == 'disabled' && updateRecords == 'disabled' && installServices == 'disabled' && installMailScripts == 'disabled' 
            && installTracking == 'disabled' && installWebmail == 'disabled' && installPMTA == 'off')
            {
                MailTng.alertBox({title: 'Please Select At Least One Instalation Proccess !', type: "error", allowOutsideClick: "true", confirmButtonClass: "btn-danger"});
                return false;
            }
            
            // check if there is a domain or domains selected 
            $(".domains-mapping").each(function () 
            {
                if($(this).val() != null && $(this).val() != '')
                {
                    selectedDomains.push($(this).val());
                }  
            });
            
            if(selectedDomains.length == 0)
            {
                MailTng.alertBox({title: 'Please check your IP/Domains Mapping ( it seems that you\'ve missed some ) !', type: "error", allowOutsideClick: "true", confirmButtonClass: "btn-danger"});
                return false;
            }
            
            // collect ip/domain mapping
            $(".domains-mapping").each(function () 
            {
                var value = ($(this).val() == null || $(this).val() == '') ? 'none' : $(this).val();
                var index = $(this).attr('map-index');
                var ip = $(".ips-label[map-index='" + index + "']").attr('data-ip');
                domainsMapping += ip + "=" + value + ";";  
            });

            // showing the terminal
            $('#installation-status').html('<i class="fa fa-spinner fa-spin"></i>');
            $('#installation-wrapper').fadeIn(100);
            
            
            // start installation
            $.ajax({
                type: 'post',
                url: MailTng.getBaseURL() + "/servers/install/",
                data :  {
                    "server-id" : serverId,
                    "server-version" : serverVersion,
                    "update-ips" : updateIps,
                    "update-records" : updateRecords,
                    "install-services" : installServices,
                    "install-mail-scripts" : installMailScripts,
                    "install-tracking" : installTracking,
                    "install-webmail" : installWebmail,
                    "install-pmta" : installPMTA,
                    "domain-mapping" : btoa(domainsMapping),
                    "use-subdomains" : useSubDomains,
                },
                dataType : 'JSON',
                success:function(result) 
                {
                    if(result !== null)
                    {
                        var started = result['started'];

                        if(started == true)
                        {
                            getInstallationProccess();
                        }
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) 
                {
                    MailTng.alertBox({title: textStatus + ' : ' + errorThrown, type: "error", allowOutsideClick: "true", confirmButtonClass: "btn-danger"});
                }
            });
        });
    };
    
    var getInstallationProccess = function()
    {
        var serverId = $('#server-id').val();
        
        $.ajax({
                type: 'post',
                url: MailTng.getBaseURL() + "/servers/proccess/",
                data :  {
                    "server-id" : serverId
                },
                dataType : 'JSON',
                success:function(result) 
                {
                    if(result !== null)
                    {
                        var status = result['status'];
                        var log = result['log'];

                        $("#installation-proccess").html(log);
                        $("#installation-proccess").slimscroll({ scrollBy: '100px' });
                        
                        if(status === "completed")
                        {
                            $('#installation-status').html('completed !');
                        }
                        else
                        {
                            setTimeout(function (){
                                getInstallationProccess();
                            },2000);
                        }
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) 
                {
                    setTimeout(getInstallationProccess(),3000);
                }
        });   
    };
    
    var handleUninstallingServers = function () 
    { 
        $('#server-uninstallation').click(function(event) 
        {
            event.preventDefault();
            var serverId = $(this).attr('data-server-id');
            
            // add a confirmation to the form
            swal({
                title: "Administration Confirmation",
                text: "You're about to uninstall this server ! ",
                type: "warning",
                showCancelButton: true,
                closeOnConfirm: false,
                showLoaderOnConfirm: true
            }, 
            function ()
            {
                $.ajax({
                    type: 'post',
                    url: MailTng.getBaseURL() + "/servers/uninstall/",
                    data :  {
                        "server-id" : serverId
                    },
                    dataType : 'JSON',
                    success:function(result) 
                    {
                        if(result != null)
                        {
                           var button = (result['type'] == 'error') ? 'btn-danger' : 'btn-primary';
                           swal({title:result['message'],type:result['type'],allowOutsideClick:"true",confirmButtonClass:button});
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

    var handleDropdownChange = function ()
    {
        $(".domains-mapping").on('change', function() 
        {
            var previous = $(this).attr('data-prev');
            var current = $(this).find('option:selected').first().text();
            var mapIndex = $(this).attr('map-index');
            var value = $(this).val();
            
            $('.domains-mapping').each(function()
            { 
                if($(this).attr('map-index') != mapIndex)
                {
                    $(this).find('option').each(function()
                    {
                        if($(this).text() == previous)
                        {
                            $(this).removeAttr('disabled');
                        }
                        
                        if(value != '' && $(this).text() == current)
                        {
                            $(this).attr('disabled','disabled');
                        }
                    });
                }  
            });
                
            $(this).attr('data-prev',current);
        });
        
        $(".domains-mapping").change();
    };

    var checkIfSubdomainsEnabled = function ()
    {
        // handle not choosing the same domain twice       
        $("#use-subdomains").change(function() 
        {
            var value = $(this).val();
            var index = 0;
            
            if(value == 'enabled')
            {
                $('#select-random-domains').attr('disabled','disabled');
                
                $('.domains-mapping').each(function()
                { 
                    if(index > 0)
                    {
                        $(this).val(null);
                        $(this).attr('disabled','disabled').css('background','#eee');
                    }
                   
                    $(this).change();
                    index++;
                });  
            }
            else
            {
                $('#select-random-domains').removeAttr('disabled');
                $('.domains-mapping').each(function()
                { 
                    $(this).removeAttr('disabled').css('background','#fff');
                    $(this).change();
                });
            }
        });
        
        $("#use-subdomains").change(); 
    };
    
    var selectRandomDomains = function ()
    {
        $('#select-random-domains').on('click',function(){
            // check if this button is disabled 
            if($('#select-random-domains').attr('disabled') == 'disabled')
            {
                return false;
            }
            
            // empty all pre-selected values 
            $('.domains-mapping').each(function()
            { 
                $(this).val(null);
                $(this).change();
            });
            
            // starts filling the random values 
            var availableValues = [];
            
            $('.domains-mapping').first().find('option').each(function()
            { 
                if($(this).val() != null && $(this).val() != undefined && $(this).val() != '')
                {
                    availableValues.push($(this).val());
                } 
            });
            
            $('.domains-mapping').each(function()
            { 
                var index = Math.floor(Math.random() * availableValues.length);
                $(this).val(availableValues[index]);
                $(this).change();
                availableValues.splice(index,1);
            }); 
        });
    };
    
    var handleUpdateIpsCheck = function()
    {
        $('#update-ips').change(function(){
            if($(this).val() == 'disabled')
            {
                $('.domains-mapping').each(function()
                { 
                    $(this).attr('disabled','disabled').css('background','#eee');
                });
                
                $("#use-subdomains").attr('disabled','disabled').css('background','#eee');
                $('#select-random-domains').attr('disabled','disabled');
            }
            else
            {
                $('.domains-mapping').each(function()
                { 
                    $(this).removeAttr('disabled').css('background','#fff');
                });
                
                $("#use-subdomains").removeAttr('disabled').change().css('background','#fff');
            }
        });
        
        $('#update-ips').change();
    };
    
    var handleDropDownDisableEvent = function()
    {
        $('select').change(function(){
            
            if($(this).attr('disabled') != 'disabled' && $(this).attr('disabled') != 'true')
            {
                var value = $(this).val();
            
                if(value == 'disabled')
                {
                    $(this).css('background','#eee');
                }
                else
                {
                    $(this).css('background','#fff');
                }
            }
        });
    };
    
    return {

        init: function () 
        {
            handleDatePickers();
            handleInstallingServers();
            handleUninstallingServers();
            handleDropdownChange();
            checkIfSubdomainsEnabled();
            selectRandomDomains();
            handleUpdateIpsCheck();
            handleDropDownDisableEvent();
        }
    };
}();


// initialize and activate the script
$(function(){ Servers.init(); });
