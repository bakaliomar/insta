var PMTA = function () 
{
    // Refresh Servers List
    var handleServersRefresh = function () 
    {
        $('#refresh-servers').click(function(evt) 
        {    
            evt.preventDefault();

            $("#servers").html('').selectpicker('refresh');
            $("#pmta-links").html('<li><a href="javascript:;"> No Servers Selected</a></li>');
            
            MailTng.blockUI();
            
            $.ajax({
                type: 'post',
                url: MailTng.getBaseURL() + "/mail/getServers.json",
                data :  {},
                dataType : 'json',
                success:function(result) 
                {
                    if(result !== null)
                    {
                        $("#servers").html('');
                        
                        var providers = result['providers'];
                        var servers = result['servers'];

                        for (var i in providers)
                        {
                            var provider = providers[i];
                            
                            $("#servers").append('<optgroup label="'+provider['name']+'">');
                            
                            for (var j in servers)
                            {
                                var server = servers[j];
                                
                                if(server['provider_id'] == provider['id'])
                                {
                                    $("#servers").append('<option style="padding-left: 25px;" value="'+server['id']+'">'+server['name']+'</option>').selectpicker('refresh'); 
                                }
                            }
                        }

                        MailTng.unblockUI();
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) 
                {
                    MailTng.unblockUI();
                    MailTng.alertBox({title: textStatus + ' : ' + errorThrown, type: "error", allowOutsideClick: "true", confirmButtonClass: "btn-danger"});
                }
            });

        });
    };

    var handleServersChange = function()
    {
        $('#servers').change(function(){
            $("#pmta-links").html('');
            $(this).find('option:selected').each(function(){
                var id = $(this).val();
                var ip = $(this).attr('data-main-ip');
                var name = $(this).text();
                $("#pmta-links").append('<li><a href="http://' + ip + ':' + $('#pmta-port').val() + '" target="pmta_' + id + '"> ' + name + ' </a></li>');
            });
        });
    };
     
    // Refresh Servers List
    var handleFormSubmit = function () 
    {
        $('.submit-form').click(function(evt) 
        {    
            evt.preventDefault();

            var action = $(this).val();
            var servers = $('#servers').val();
            var data = $('#manage-pmta').serialize() + "&action=" + action;
            
            if(servers == '' || servers == undefined)
            {
                MailTng.alertBox({title:"Please Select at least one server !",type:"error",allowOutsideClick:"true",confirmButtonClass:"btn-danger"});
                return false;
            }

            MailTng.blockUI();
            
            $.ajax({
                type: 'post',
                url: MailTng.getBaseURL() + "/pmta/manage.json",
                data :  data,
                dataType : 'json',
                success:function(result) 
                {
                    if(result !== null)
                    {
                        var flag = result['flag'];
                        var message = result['message'];
                        var results = result['results'];
                        
                        $('#results').html(results);
                        
                        MailTng.unblockUI();
                        
                        MailTng.alertBox({title: message, type: flag, allowOutsideClick: "true", confirmButtonClass: "btn-primary"});
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) 
                {
                    MailTng.unblockUI();
                    MailTng.alertBox({title: textStatus + ' : ' + errorThrown, type: "error", allowOutsideClick: "true", confirmButtonClass: "btn-danger"});
                }
            });

        });
    };
    
    return {
        init: function () 
        {
            handleServersRefresh();
            handleServersChange();
            handleFormSubmit();
        }
    };

}();

jQuery(document).ready(function () {
    PMTA.init(); 
});