var Accessibilities = function () 
{
    // handle Servers Accessibilities
    var handleServersAccessibilities = function () 
    {
        $('#servers-users,#providers').on('change',function(e)
        {
            e.preventDefault();

            // clean previous 
            $("#available-servers").html('');
            $("#selected-servers").html('');

            var userId = $("#servers-users").val();
            var providerId = $("#providers").val();

            if(userId != undefined && userId != '' && providerId != undefined && providerId != '')
            {   
                MailTng.blockUI();
                
                $.ajax({
                    type: 'post',
                    url: MailTng.getBaseURL() + "/accessibilities/getServers/"+userId+"/"+providerId+".json",
                    data :  {},
                    dataType : 'json',
                    async: false,
                    success:function(result) 
                    {
                        if(result !== null)
                        {
                            var notAuthorised = result['notAuthorised'];
                            var authorised = result['authorised'];
                            
                            for (var i in notAuthorised)
                            {
                                var value = notAuthorised[i];
                                $("#available-servers").append('<option value="' + value['id'] + '" >' + value['name'] + '</option>');
                            }
                            
                            for (var i in authorised)
                            {
                                var value = authorised[i];
                                $("#selected-servers").append('<option value="' + value['id'] + '" >' + value['name'] + '</option>');
                            }

                            $('#available-servers').change();
                            $('#selected-servers').change();
                
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
        
        $('#selected-servers').change(function(){
            var values = '';
            
            $("#selected-servers>option").each(function(){
                values += $(this).val() + ',';
            });
            
            $('#authorized-servers').val(values);
        });
        
        $('#servers-users').change();
    };
    
    // handle Isps Accessibilities
    var handleIspsAccessibilities = function () 
    {
        $('#isps-users').on('change',function(e)
        {
            e.preventDefault();

            // clean previous 
            $("#available-isps").html('');
            $("#selected-isps").html('');

            var userId = $("#isps-users").val();

            if(userId != undefined && userId != '')
            {   
                MailTng.blockUI();
                
                $.ajax({
                    type: 'post',
                    url: MailTng.getBaseURL() + "/accessibilities/getIsps/"+userId+".json",
                    data :  {},
                    dataType : 'json',
                    async: false,
                    success:function(result) 
                    {
                        if(result !== null)
                        {
                            var notAuthorised = result['notAuthorised'];
                            var authorised = result['authorised'];
                            
                            for (var i in notAuthorised)
                            {
                                var value = notAuthorised[i];
                                $("#available-isps").append('<option value="' + value['id'] + '" >' + value['name'] + '</option>');
                            }
                            
                            for (var i in authorised)
                            {
                                var value = authorised[i];
                                $("#selected-isps").append('<option value="' + value['id'] + '" >' + value['name'] + '</option>');
                            }

                            $('#available-isps').change();
                            $('#selected-isps').change();
                
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
        
        $('#selected-isps').change(function(){
            var values = '';
            
            $("#selected-isps>option").each(function(){
                values += $(this).val() + ',';
            });
            
            $('#authorized-isps').val(values);
        });
        
        $('#isps-users').change();
    };

    // handle Servers Accessibilities
    var handleOffersAccessibilities = function () 
    {
        $('#offers-users,#sponsors').on('change',function(e)
        {
            e.preventDefault();

            // clean previous 
            $("#available-offers").html('');
            $("#selected-offers").html('');

            var userId = $("#offers-users").val();
            var sponsorId = $("#sponsors").val();

            if(userId != undefined && userId != '' && sponsorId != undefined && sponsorId != '')
            {   
                MailTng.blockUI();
                
                $.ajax({
                    type: 'post',
                    url: MailTng.getBaseURL() + "/accessibilities/getOffers/"+userId+"/"+sponsorId+".json",
                    data :  {},
                    dataType : 'json',
                    async: false,
                    success:function(result) 
                    {
                        if(result !== null)
                        {
                            var notAuthorised = result['notAuthorised'];
                            var authorised = result['authorised'];
                            
                            for (var i in notAuthorised)
                            {
                                var value = notAuthorised[i];
                                $("#available-offers").append('<option value="' + value['id'] + '" >' + value['name'] + '</option>');
                            }
                            
                            for (var i in authorised)
                            {
                                var value = authorised[i];
                                $("#selected-offers").append('<option value="' + value['id'] + '" >' + value['name'] + '</option>');
                            }

                            $('#available-offers').change();
                            $('#selected-offers').change();
                
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
        
        $('#selected-offers').change(function(){
            var values = '';
            
            $("#selected-offers>option").each(function(){
                values += $(this).val() + ',';
            });
            
            $('#authorized-offers').val(values);
        });
        
        $('#offers-users').change();
    };
    
    // handle Lists Accessibilities
    var handleListsAccessibilities = function () 
    {
        $('#lists-users,#isps').on('change',function(e)
        {
            e.preventDefault();

            // clean previous 
            $("#available-lists").html('');
            $("#selected-lists").html('');

            var userId = $("#lists-users").val();
            var ispId = $("#isps").val();

            if(userId != undefined && userId != '' && ispId != undefined && ispId != '')
            {   
                MailTng.blockUI();
                
                $.ajax({
                    type: 'post',
                    url: MailTng.getBaseURL() + "/accessibilities/getLists/"+userId+"/"+ispId+".json",
                    data :  {},
                    dataType : 'json',
                    async: false,
                    success:function(result) 
                    {
                        if(result !== null)
                        {
                            var notAuthorised = result['notAuthorised'];
                            var authorised = result['authorised'];
                            
                            for (var i in notAuthorised)
                            {
                                var value = notAuthorised[i];
                                $("#available-lists").append('<option value="' + value['id'] + '" >' + value['name'] + '</option>');
                            }
                            
                            for (var i in authorised)
                            {
                                var value = authorised[i];
                                $("#selected-lists").append('<option value="' + value['id'] + '" >' + value['name'] + '</option>');
                            }

                            $('#available-lists').change();
                            $('#selected-lists').change();
                
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
        
        $('#selected-lists').change(function(){
            var values = '';
            
            $("#selected-lists>option").each(function(){
                values += $(this).val() + ',';
            });
            
            $('#authorized-lists').val(values);
        });
        
        $('#lists-users').change();
    };
    
    var handleSelectors = function() {
        
        $('.select-all-options').click(function(e){
            e.preventDefault();
            var target = $(this).attr('data-target');
            var values = $("#"+target+">option").map(function() { return $(this).val(); });
            $("#"+target).val(values);
            $("#"+target).change();
        });
        
        $('.deselect-all-options').click(function(e){
            e.preventDefault();
            var target = $(this).attr('data-target');
            $("#"+target).val(null);
            $("#"+target).change();
        });
        
        // selecting event 
        $('.select-options').click(function(evt) 
        {    
            evt.preventDefault();  
            var from = $(this).attr('data-from');
            var target = $(this).attr('data-target');
            $("#"+from+' option:selected').remove().appendTo("#"+target);
            $("#"+from).change();
            $("#"+target).change();
        });
        
        // deselecting event
        $('.deselect-options').click(function(evt) 
        {    
            evt.preventDefault();
            var from = $(this).attr('data-from');
            var target = $(this).attr('data-target');
            $("#"+from+' option:selected').remove().appendTo("#"+target);
            $("#"+from).change();
            $("#"+target).change();
        });
    };

    return {
        init: function () 
        {
            handleServersAccessibilities();
            handleIspsAccessibilities();
            handleOffersAccessibilities();
            handleListsAccessibilities();
            handleSelectors();
        }
    };

}();

// initialize and activate the script
$(function(){ Accessibilities.init(); });