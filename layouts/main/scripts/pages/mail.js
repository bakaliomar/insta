var Mail = function () 
{
    ////////////// General Section ////////////

    // Disable Form Submit by click on a text filed
    var DisableSubmitTextClickEvent = function () 
    {
        $("input:text").on('keypress',function(e){
            if (e.keyCode == 13) {return false;}
        });
    };

    // HELP : Placeholders Display
    var handlePlaceHoldersHelpDisplayEvent = function () 
    {
        $("#show-placeholders-help").click(function(evt)
        {
            evt.preventDefault();
            
            // empty old results
            $("#modal-dialog .modal-title").html('');
            $("#modal-dialog .modal-body").html('');
            $("#modal-dialog .modal-footer").html('<a data-dismiss="modal" class="btn green" href="javascript:;">Close</a>'); 
            
            // fill the modal 
            $("#modal-dialog .modal-title").html('PlaceHolders Help');
            $("#modal-dialog .modal-body").html(atob($("#place-holders-help-html").val())); 
        });
    };
    
    ////////////// Servers Section ////////////
    
    // Refresh Servers List
    var handleServersRefreshEvent = function () 
    {
        $('#refresh-servers').click(function(evt) 
        {    
            evt.preventDefault();
            
            MailTng.blockUI();
            
            $("#servers").selectpicker('val',null);
            
            // clean the previous ips
            $("#available-ips").html('');
            $("#selected-ips").html('');
            
            $("#pmta-links").html('<li><a href="javascript:;"> No Servers Selected</a></li>');
            $("#drops-links").html('<li><a href="javascript:;"> No Servers Selected</a></li>');

            $.ajax({
                type: 'post',
                url: MailTng.getBaseURL() + "/mail/getServers.json",
                data :  {},
                async: true,
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
                                    $("#servers").append('<option style="padding-left: 25px;" value="'+server['id']+'">'+server['name']+'</option>');
                                }
                            }
                        }
                        
                        $("#servers").selectpicker('refresh');
                        
                        MailTng.unblockUI();
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) 
                {
                    MailTng.alertBox({title:textStatus + ' : ' + errorThrown,type:"error",allowOutsideClick:"true",confirmButtonClass:"btn-danger"});
                }
            });

        });
    };
    
    // Servers Change 
    var handleServersChangeEvent = function () 
    {
        $('#servers').on('change',function(e)
        {
            e.preventDefault();

            // clean the previous ips
            $("#available-ips").html('');
            $("#selected-ips").html('');
            $('#available-ips-sum').html('(0 IP Selected)');
            $('#selected-ips-sum').html('(0 IP Selected)');
            
            $("#pmta-links").html('<li><a href="javascript:;"> No Servers Selected</a></li>');
            $("#drops-links").html('<li><a href="javascript:;"> No Servers Selected</a></li>');
            
            var serverId = String($("#servers").val()).replace(/\,/g,'/');

            if(serverId != undefined && serverId != null && parseInt(serverId) != NaN && serverId != 'null' && serverId != '')
            {   
                MailTng.blockUI();
                
                $("#available-ips").html('<option value="">Please Wait ...</option>');
                
                $.ajax({
                    type: 'post',
                    url: MailTng.getBaseURL() + "/mail/getIps/"+serverId+".json",
                    data :  {},
                    dataType : 'json',
                    async: true,
                    success:function(result) 
                    {
                        if(result !== null)
                        {
                            $("#available-ips").html('');
                            
                            var servers = result['servers'];
                            var ips = result['ips'];
                            
                            for (var i in ips)
                            {
                                var value = ips[i];
                                $("#available-ips").append('<option value="'+value['serverid']+'|'+value['id']+'" title="'+value['server']+' | '+value['value']+' | '+value['rdns']+'" >'+value['server']+' | '+value['value']+' | '+value['rdns']+'</option>');
                            }
                            
                            $("#pmta-links").html('');
                            $("#drops-links").html('<li><a href="' + MailTng.getBaseURL() + '/drops/lists.html" target="drops_all"> All Servers </a></li>');
                                
                            for (var i in servers)
                            {
                                var server = servers[i];
                                
                                $("#pmta-links").append('<li><a href="http://' + server['main_ip'] + ':' + $('#pmta-port').val() + '" target="pmta_' + server['id'] + '"> ' + server['name'] + ' </a></li>');
                                $("#drops-links").append('<li><a href="' + MailTng.getBaseURL() + '/drops/lists/' + server['id'] + '.html" target="drops_' + server['id'] + '"> ' + server['name'] + ' </a></li>');
                            }
                            
                            $('#available-ips').change();
                            $('#selected-ips').change();
                
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
       
    // Selecting or Deselcting IPS event
    var handleIpsChangeEvent = function () 
    {
        $('.select-all-options').click(function(e){
            e.preventDefault();
            var target = $(this).attr('data-target');
            var values = $("#"+target+">option").map(function() { return $(this).val(); });
            $("#"+target).val(values);
            $('#available-ips').change();
            $('#selected-ips').change();
            $('#'+target+'-sum').html('(' + values.length + ' IP Selected)');
        });
        
        $('.deselect-all-options').click(function(e){
            e.preventDefault();
            var target = $(this).attr('data-target');
            $("#"+target).val(null);
            $('#available-ips').change();
            $('#selected-ips').change();
            $('#'+target+'-sum').html('(0 IP Selected)');
        });
        
        // selecting event 
        $('#ips-selector').click(function(evt) 
        {    
            evt.preventDefault();  
            $('#available-ips option:selected').remove().appendTo('#selected-ips');
            $('#available-ips').change();
            $('#selected-ips').change();
            $('#available-ips-sum').html('(0 IP Selected)');
        });
        
        // deselecting event
        $('#ips-deselector').click(function(evt) 
        {    
            evt.preventDefault();
            $('#selected-ips option:selected').remove().appendTo('#available-ips');
            $('#available-ips').change();
            $('#selected-ips').change();
            $('#selected-ips-sum').html('(0 IP Selected)');
        });
        
        $('#available-ips,#selected-ips').on('change',function() 
        {
            var id = $(this).attr('id');
            var values = $("#"+id+">option:selected").map(function() { return $(this).val(); });
            $('#'+id+'-sum').html('(' + values.length + ' IP Selected)');
        });
        
        $('#ips-emails-proccess').on('change',function() 
        {
            var value = $(this).val();
            
            if(value == 'ips-rotation')
            {
                $("#number-of-emails").attr('disabled','true');
                $("#emails-period-value").attr('disabled','true');
                $("#emails-period-type").attr('disabled','true').selectpicker('refresh');
                $("#ips-rotation").removeAttr('disabled');
                $("#x-delay").removeAttr('disabled');
                $("#batch").removeAttr('disabled');
            }
            else
            {
                $("#number-of-emails").removeAttr('disabled');
                $("#emails-period-value").removeAttr('disabled');
                $("#emails-period-type").removeAttr('disabled').selectpicker('refresh');
                $("#ips-rotation").attr('disabled','true');
                $("#x-delay").attr('disabled','true');
                $("#batch").attr('disabled','true');
            }
        });
    };

    // IPs Settings Event
    var handleIpsSettingsEvent = function ()
    {
        $('#ips-settings').click(function(evt)
        {
            evt.preventDefault();
            
            // empty old results
            $("#modal-dialog .modal-title").html('');
            $("#modal-dialog .modal-body").html(''); 
            $("#modal-dialog .modal-footer").html('<a data-dismiss="modal" class="btn green" href="javascript:;">Close</a>'); 
            
            var count = $('#selected-ips option:selected').length;
            
            if(count == 0)
            {
                MailTng.alertBox({title:"No IPs Selected !",type:"error",allowOutsideClick:"true",confirmButtonClass:"btn-danger"});
                return false;
            }
            else
            {
                var index = 0;
                var html = '';
                var percentage = 100 / count;
                
                $('#selected-ips option:selected').each(function(){
                    var ip = $(this).html().split('|')[1].trim();
                    var border = index < count-1 ? 'style="border-bottom: 1px solid #E8E8E8;"' : '';
                    html += '<div class="row" ' + border + '> <div class="col-md-2"> <div class="form-group"><label class="control-label" style="padding-top:20px">' + ip + '</label></div> </div> <div class="col-md-10"> <div class="col-md-3"> <div class="form-group"> <label class="control-label">Number Of Emails</label> <input type="text" class="form-control ip-emails-number" value="1"> </div> </div> <div class="col-md-3"> <div class="form-group"> <label class="control-label">Time Period Value</label> <input type="text" class="form-control ip-emails-period-value" value="1" > </div> </div> <div class="col-md-3"> <div class="form-group"> <label class="control-label">Time Period Unit</label> <select class="form-control ip-emails-period-type"> <option value="seconds" selected="true">Seconds</option> <option value="minutes">Minutes</option> <option value="hours">Hours</option> </select> </div> </div> <div class="col-md-3"> <div class="form-group"> <label class="control-label">Emails Percentage</label> <input type="text" class="form-control ip-data-percentage" value="' + percentage + '" ></div> </div> </div> </div>';
                    index++;
                });
                
                $("#modal-dialog .modal-title").html('IPs Settings');
                $("#modal-dialog .modal-body").html(html); 
                $("#modal-dialog .modal-footer").prepend('<a data-dismiss="modal" id="save-ip-settings" class="btn green" href="javascript:;">Save Settings</a>'); 
                
                $('#save-ip-settings').on('click',function(){
                    MailTng.alertBox({title:"IPs Settings Saved Successfully !",type:"success",allowOutsideClick:"true",confirmButtonClass:"btn-primary"});
                    $('#modal-dialog').modal('dismiss');
                    $("#modal-dialog .modal-footer").html('<a data-dismiss="modal" class="btn" href="javascript:;">Close</a>');
                });
            }
        });
    }
    
    // IPs Select By Textarea Event
    var handleSelectIPsTextAreaEvent = function ()
    {
        $('#ips-selector-text').click(function(e){
            e.preventDefault();
            var ips = $("#ips-to-select").val();

            if(ips != undefined)
            {
                ips = btoa(ips.split("\n").join(","));

                $.ajax(
                {
                    url : MailTng.getBaseURL() + "/mail/getIpsText.json",
                    type: "POST",
                    data:{ ips : ips },
                    dataType: "JSON",
                    success:function(result) 
                    {
                        if(result !== null)
                        {
                            var servers = Object.keys(result['servers']).map(function(k) { return result['servers'][k]; });
                            var ips = Object.keys(result['ips']).map(function(k) { return result['ips'][k]; });
                            
                            $('#servers').val(servers).selectpicker('refresh'); 
                            
                            $('#get-ips').click();
                            
                            for(var i=0;i<ips.length;i++)
                            {         
                                $("#available-ips option").each(function() 
                                {
                                    if($(this).text().toLowerCase().indexOf(ips[i]) >= 0)
                                    {
                                        $(this).prop('selected', true);
                                    }
                                });
                            }
                            
                            $("#ips-selector").click();
                        }  
                    },
                    error: function(jqXHR, textStatus, errorThrown) 
                    {
                        MailTng.alertBox({title:textStatus + ' : ' + errorThrown,type:"error",allowOutsideClick:"true",confirmButtonClass:"btn-danger"});
                    }
                });
            
                $('#available-ips').change();
                $('#selected-ips').change();
            }
        });
    };
    
    // IPs Frequency Switch Event
    var handleIPsFrequencySwitchEvent = function ()
    {
        $("#emails-frequency-switch").on('switchChange.bootstrapSwitch', function(event, state) {
            if(state == false)
            {
                $('#number-of-emails').prop('disabled',true);
                $('#emails-period-type').prop('disabled',true);
                $('#emails-period-value').prop('disabled',true);
            }
            else
            {
                $('#number-of-emails').removeAttr('disabled');
                $('#emails-period-type').removeAttr('disabled');
                $('#emails-period-value').removeAttr('disabled');
            }
        });
    };
    
    ////////////// Content Section ( sponsors , offers , creatives ..... ) ////////////
    
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
            $("#lists").html('');
            $("#sub-lists").html('');
            $('#emails-per-seeds').val(1).prop('disabled',true);
            
            // reset data count
            reloadDataLists();
            resetDataCount();

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
        
        $('#sponsors2').change(function() 
        {
            // clean the last results
            $("#creatives2").html('').selectpicker('refresh');
            $("#offers2").html('').selectpicker('refresh');

            if($('#sponsors2').val() !== undefined && $('#sponsors2').val() !== '')
            {    
                MailTng.blockUI();

                var sponsorId = $('#sponsors2').val();
         
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
                                $("#offers2").append('<option value="'+value['id']+'">(' + value['production_id'] + ') '+ value['flag'] +' - '+value['name']+'</option>');
                            }
                            
                            // update the dropdown
                            $("#offers2").selectpicker('refresh');
                            
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
    
    // Offers Change 
    var handleOffersChangeEvent = function () 
    {
        $('#offers').change(function() 
        {
            // clean the last results
            $("#creatives").html('').selectpicker('refresh');
            $("#from-names").html('').selectpicker('refresh');
            $("#subjects").html('').selectpicker('refresh');
            $('#generate-links').attr('offer-id','0');
            $("#drop-body").val('');
            $("#lists").html('');
            $("#sub-lists").html('');
            $('#emails-per-seeds').val(1).prop('disabled',true);
            
            // reset data count
            reloadDataLists();
            resetDataCount();

            if($('#offers').val() !== undefined && $('#offers').val() !== '')
            {    
                MailTng.blockUI();

                var offerId = $('#offers').val();
                
                $.ajax({
                    type: 'post',
                    url: MailTng.getBaseURL() + "/mail/getOfferAssets/"+offerId+".json",
                    data :  {},
                    dataType : 'json',
                    success:function(result) 
                    {
                        if(result !== null)
                        {
                            var lists = result['from-names'];

                            for (var i in lists)
                            {
                                var value = lists[i];
                                
                                if(value['value'].trim().toLowerCase().includes('list name') || value['value'].trim().toLowerCase().includes('listname'))
                                {
                                    value['value'] = '[EMAIL_NAME]';
                                }
                                
                                $("#from-names").append('<option value="'+value['id']+'">' + value['value'] +'</option>');
                            }
                            
                            var lists = result['subjects'];

                            for (var i in lists)
                            {
                                var value = lists[i];
                                $("#subjects").append('<option value="'+value['id']+'">' + value['value'] +'</option>');
                            }
                            
                            var lists = result['creatives'];

                            for (var i in lists)
                            {
                                var value = lists[i];
                                $("#creatives").append('<option value="'+value['id']+'">creative_' + value['id'] +'</option>');
                            }

                            $("#creatives").selectpicker('refresh');
                            $("#from-names").selectpicker('refresh');
                            $("#subjects").selectpicker('refresh');
                            $('#generate-links').attr('offer-id',$('#offers').val());
                            
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
        
        $('#offers2').change(function() 
        {
            // clean the last results
            $("#creatives2").html('').selectpicker('refresh');

            if($('#offers2').val() !== undefined && $('#offers2').val() !== '')
            {    
                MailTng.blockUI();

                var offerId = $('#offers2').val();
                
                $.ajax({
                    type: 'post',
                    url: MailTng.getBaseURL() + "/mail/getOfferCreatives/"+offerId+".json",
                    data :  {},
                    dataType : 'json',
                    success:function(result) 
                    {
                        if(result !== null)
                        {
                            var lists = result['creatives'];

                            for (var i in lists)
                            {
                                var value = lists[i];
                                $("#creatives2").append('<option value="'+value['id']+'">creative_' + value['id'] +'</option>');
                            }

                            $("#creatives2").selectpicker('refresh');
                            
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
    
    // FromNames And Subjects Change 
    var handleFromNamesAndSubjectsChangeEvent = function () 
    {
        // subject or fromname changed
        $("#subjects,#from-names").on('change',function()
        {
            var encoder = $(".encoding[target='" + $(this).attr('id') + "']");
            var status = $('.toggle-from-subject[data-target=' + $(this).attr('id') + ']').attr('data-current-status');
            encode($(this),encoder,status);
        });
        
        // subject or fromname changed
        $("#subjects-text,#from-names-text").on('keyup',function()
        {
            var encoder = $(".encoding[target='" + $(this).attr('id').replace('-text','') + "']");
            var status = $('.toggle-from-subject[data-target=' + $(this).attr('id').replace('-text','') + ']').attr('data-current-status');
            encode($('#' + $(this).attr('id').replace('-text','')),encoder,status);
        });
        
        // encoder select changed
        $(".encoding").on('change',function(){  
            var target = $("#" + $(this).attr('target'));
            var status = $('.toggle-from-subject[data-target=' + $(this).attr('target') + ']').attr('data-current-status');
            encode($(this).attr('target'),target,$(this),status);
        });

        var encode = function(id,target,encoder,status)
        {
            var type = encoder.val();
            var value = (status == 'select') ? $("option:selected",target).html().replace(/(\r\n|\n|\r)/gm,"") : $("#" + target.attr('id') + '-text').val();
            var convertedValue = value;

            if(value != "[EMAIL_NAME]")
            {
                if(type == 'plain')
                {
                    convertedValue = "=?UTF-8?Q?" + value +  "?=";
                }
                else if(type == 'b64')
                {
                    convertedValue = convertedValue = "=?UTF-8?B?" + btoa(value) +  "?=";
                }
                else if(type == 'uni')
                {
                    convertedValue = "=?UTF-8?Q?=" + MailTng.encodeToUnicode(value).replace(" ",'=') +  "?=";
                }
            }

            if(status == 'select')
            {
                $('#' + id).siblings('.btn-group').first().css('display','none').hide();
                $('#' + id + '-text').removeClass('hide').css('display','block').show().val(convertedValue);
                $('.toggle-from-subject[data-target=' + id + ']').attr('data-current-status','text');
            }
            else
            {
                $('#' + id + '-text').val(convertedValue);
            }
        }
    };
    
    // FromNames And Subjects Switch Change 
    var handleFromNamesAndSubjectsSwitchEvent = function () 
    {
        $('.toggle-from-subject').on('click',function(){
            var target = $(this).attr('data-target');
            var status = $(this).attr('data-current-status');

            if(status == 'select')
            {
                $('#' + target).siblings('.btn-group').first().css('display','none').hide();
                var value = $('#' + target + ' option:selected').text() == $('#' + target).attr('title') ? '' : $('#' + target + ' option:selected').text();
                $('#' + target + '-text').removeClass('hide').css('display','block').show().val(value);
                $(this).attr('data-current-status','text');
            }
            else
            {
                $('#' + target + '-text').addClass('hide').css('display','none').hide().val('');
                $('#' + target).siblings('.btn-group').first().css('display','block').show();
                $(this).attr('data-current-status','select');
                $('#' + target).change();
            }
        });
    }
    
    // Creatives Change
    var handleCreativesChangeEvent = function () 
    {
        $('#creatives').change(function() 
        {
            if($('#creatives').val() !== undefined && $('#creatives').val() !== '')
            {    
                // clean the last results
                $("#drop-body").val('');
                
                var creativeId = $('#creatives').val();
                
                $.ajax({
                    type: 'post',
                    url: MailTng.getBaseURL() + "/mail/getCreative/" + creativeId + ".json",
                    data :  { },
                    dataType : 'JSON',
                    success:function(result) 
                    {
                        if(result !== null)
                        {
                            var creative = result['creative'];
                        
                            if(creative != '')
                            {
                                $("#drop-body").val(creative);
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
    
    // Message Creative HTML Display
    var handleCreativeDisplayEvent = function () 
    {
        $("#show-body-as-html").click(function(evt)
        {
            evt.preventDefault();
            var w = window.open();
            $(w.document.body).html('<center>' + $("#drop-body").val() + '</center>');
        });
    };
    
    // handle Generate Links Event
    var handleGenerateLinksEvent = function () 
    {
        $("#generate-links").click(function(evt)
        {
            evt.preventDefault();
            
            var offerId = $(this).attr('offer-id');
            
            if(offerId != undefined && offerId > 0)
            {
                $.ajax({
                    type: 'post',
                    url: MailTng.getBaseURL() + "/mail/generateLinks/" + offerId + ".json",
                    data :  {},
                    dataType : 'json',
                    success:function(result) 
                    {
                        if(result !== null)
                        {
                            var links = result['links'];

                            // empty old results
                            $("#modal-dialog .modal-title").html('');
                            $("#modal-dialog .modal-body").html(''); 
                            $("#modal-dialog .modal-footer").html('<a data-dismiss="modal" class="btn green" href="javascript:;">Close</a>'); 

                            // fill the modal 
                            $("#modal-dialog .modal-title").html('Generated Links');
                            $("#modal-dialog .modal-body").html('<center>' + links + '</center>'); 
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) 
                    {
                        MailTng.alertBox({title:textStatus + ' : ' + errorThrown,type:"error",allowOutsideClick:"true",confirmButtonClass:"btn-danger"});
                        return false;
                    }
                });
            }
            else
            {
                swal("Error!", "Please Select an Offer !", "error");
                return false;
            }
        });
    };
    
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
    
    var handleUploadHerders = function () 
    {
        $("#upload-button").click(function(evt){
            evt.preventDefault();
            $("#upload-headers").click();
        });
        $('#upload-headers').on('change', function()
        {
            var currentIndex = $('.nav-tabs').length + $('.tabdrop li').length;

            for (var i = 0; i < $(this).get(0).files.length; ++i)
            {
                var reader = new FileReader();
            
                reader.onload = function(event) 
                {
                    currentIndex++;
                    var content = event.target.result;
                    var li = $('<li/>');
                    var link = $('<a/>');
                    link.attr('href','#tab' + currentIndex).attr('data-toggle','tab').html('Header ' + currentIndex);
                    li.prepend(link);
                    li.appendTo('.tabbable .nav-tabs');
                    
                    var tabPane = $('<div/>');
                    tabPane.addClass('tab-pane');
                    tabPane.attr('id','tab' + currentIndex);
                    tabPane.html('<textarea class="form-control" style="height: 178px;" name="headers[]" data-widearea="enable" spellcheck="false" wrap="off" data-original-header="">' + content + '</textarea>');
                    tabPane.appendTo('.tabbable .tab-content');
                };
                
                reader.readAsText($(this).get(0).files[i]);  
            } 
            return false;
            MailTng.init();
        });
        return false;
    }; 
    
    var handleHeaderChange = function () 
    {
        $("#predefined-headers").change(function(evt)
        {
            evt.preventDefault();
            $('#header').val(atob($(this).val()));
        });
    };
    
    // Negative Upload Event
    var handleNegativeUploadEvent = function () 
    {
        // add click event to upload button
        $("#upload-negative").click(function(evt)
        {
            evt.preventDefault();
            $('#negative-file').click(); 
        });
        
        // add change event to file input
        $('#negative-file').on("change",function()
        {
            var size = MailTng.formatBytes(this.files[0].size,2);
            var value = $(this).val() != '' && $(this).val() != undefined ? '[ Negative File : ' + $(this).val() + ' ( ' + size + ' ) ]' : '';
            $('#negative-file-name').html(value).show();
            $('#negative-remove').show();
            $("#upload-negative").hide();
        });
        
        // add click event to remove button
        $('#negative-remove').on("click",function(evt)
        {
            evt.preventDefault();   
            
            $('#negative-file').val(null);
            $('#negative-file-name').html('').hide();
            $('#negative-remove').hide();
            $("#upload-negative").show();
        });
    };
    
    var handleVerticalsSelectDeselectAll = function()
    {
        $('.select-all-verticals').click(function(e){
            e.preventDefault();
            var values = $("#verticals>option").map(function() { return $(this).val(); });
            $("#verticals").val(values);
        });
        
        $('.deselect-all-verticals').click(function(e){
            e.preventDefault();
            $("#verticals").val(null);
        });  
    };
    
  
    ////////////// Data Lists Section ////////////
    
    // ISP and FLAG Change
    var handleISPAndCountryChangeEvent = function () 
    {
        $('#isp,#country').change(function()
        {
            // block the ui
            MailTng.blockUI({target:"#lists"});
            MailTng.blockUI({target:"#sub-lists"});
            
            $("#lists").html('');
            $("#sub-lists").html('');
            $('#emails-per-seeds').val(1).prop('disabled',true);
            
            // reset data count
            resetDataCount();
            
            // unselect every chekcbox 
            $('#data-types .list-type-checkbox').each(function(){
                if ($(this).prop("checked") == true)
                {
                    $(this).prop("checked",false).closest('span').removeClass('checked');
                }
            });
            
            // unblock the ui
            MailTng.unblockUI("#lists");
            MailTng.unblockUI("#sub-lists");
        });
    };
    
    // Data Types Click
    var handleDataTypesClickEvent = function () 
    {
        $('#data-types .list-type-checkbox').change(function() 
        {
            var type = $(this).attr('data-type');
            
            if ($(this).prop("checked") == true)
            {
                if(type == 'seeds')
                {
                     $('#emails-per-seeds').val(1).prop('disabled',false).removeAttr('disabled');
                }
                
                var ispId = $('#isp').val();
            
                if(ispId == undefined || ispId == '')
                {
                    MailTng.alertBox({title:'Please Select an ISP !',type:"error",allowOutsideClick:"true",confirmButtonClass:"btn-danger"});
                    return false;
                }

                if($(this).val() !== undefined && $(this).val() !== '')
                {    
                    // block the ui
                    MailTng.blockUI({target:"#lists"});
                    MailTng.blockUI({target:"#sub-lists"});

                    var typeId = $(this).val();
                    var country = $('#country').val();
                    var offer = $('#offers').val() !== undefined && $('#offers').val() !== '' ? $('#offers').val() : 0;

                    $.ajax({
                        type: 'post',
                        url: MailTng.getBaseURL() + "/mail/getDataLists/"+typeId+"/"+ispId+"/"+country+"/"+offer+".json",
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
                                    var color =  (value['remain'] - value['count'] < 0) ? 'class="font-red"' : '';
                                    $("#lists").append('<div class="list-row-checkboxes"><input class="list-checkbox" type="checkbox" value="' + value['id']+ '" data-list="' + value['name'] + '" /><span class="checkbox-label">' + value['name'] + ' ( Count : ' + value['count'] + ' , <span ' + color + ' >Left :  ' + value['remain'] + ' </span> ) </span></div>');
                                }

                                $("#lists input[type='checkbox']").uniform();

                                // unblock the ui
                                MailTng.unblockUI("#lists");
                                MailTng.unblockUI("#sub-lists");

                                // add lists change event
                                handleDataListClickEvent();
                            }
                        },
                        error: function (jqXHR, textStatus, errorThrown) 
                        {
                            MailTng.alertBox({title:textStatus + ' : ' + errorThrown,type:"error",allowOutsideClick:"true",confirmButtonClass:"btn-danger"});
                        }
                    });
                }
            }
            else
            {
                if(type == 'seeds')
                {
                    $('#emails-per-seeds').val(1).prop('disabled',true);
                }
                
                // block the ui
                MailTng.blockUI({target:"#lists"});
                MailTng.blockUI({target:"#sub-lists"});
                
                $("#lists .list-row-checkboxes,#sub-lists .list-row-checkboxes").each(function(){    
                    var value = $('.list-checkbox',this).attr('data-list');
       
                    if(value.trim().indexOf(type) > -1)
                    {
                        $(this).remove();
                    }
                }); 

                // unblock the ui
                MailTng.unblockUI("#sub-lists");
                MailTng.unblockUI("#lists");
            }
            
        });
    };
    
    // Data Lists Click
    var handleDataListClickEvent = function () 
    {  
        $('#lists .list-checkbox').unbind('change').on('change',function()
        {
            if ($(this).prop("checked") == true)
            {
                // block the ui
                MailTng.blockUI({target:"#sub-lists"});
                
                var listName = $(this).val();
                var offer = $('#offers').val() !== undefined && $('#offers').val() !== '' ? $('#offers').val() : 0;
                
                if(listName != '')
                {
                    $.ajax({
                        type: 'post',
                        url: MailTng.getBaseURL() + "/mail/getDataListChunks.json",
                        data :  {
                            'list' : listName,
                            'offer-id' : offer
                        },
                        dataType : 'json',
                        success:function(result) 
                        {
                            if(result !== null)
                            {
                                var lists = result['sub-lists'];

                                for (var i in lists)
                                {
                                    var subList = lists[i];
                                    
                                    if(subList['count'] != 0)
                                    {
                                        var value = subList['value'];
                                        var html = subList['name'] + '_chunk_' + subList['index'] + '  ( Count : ' + subList['count'] + ' )';
                                        $("#sub-lists").append('<div class="list-row-checkboxes"><input class="list-checkbox" type="checkbox" name="lists[]" data-count="' + subList['count'] + '" value="' + value +'" data-list="' + subList['name'] + '" /><span class="checkbox-label">' + html + '</span></div>');
                                    }
                                }

                                $("#sub-lists input[type='checkbox']").uniform();
                                
                                // attach an change event to sub-lists 
                                handleDataSublistsListClickEvent();
                                
                                // unblock the ui
                                MailTng.unblockUI("#sub-lists");
                            }
                        },
                        error: function (jqXHR, textStatus, errorThrown) 
                        {
                            MailTng.alertBox({title:textStatus + ' : ' + errorThrown,type:"error",allowOutsideClick:"true",confirmButtonClass:"btn-danger"});
                        }
                    });
                }
            }
            else
            {
                var list = $(this).val().split('.')[1];
                
                // block the ui
                MailTng.blockUI({target:"#sub-lists"});
                
                $("#sub-lists .list-row-checkboxes").each(function(){    
                     var value = $('.list-checkbox',this).attr('data-list');
                    
                    if(list.trim() == value.trim())
                    {
                        $(this).remove();
                    }
                }); 
            
                // unblock the ui
                MailTng.unblockUI("#sub-lists");
            }
        });
    };
    
    // Data SubLists Click
    var handleDataSublistsListClickEvent = function ()
    {
        $('#sub-lists .list-checkbox').unbind('change').on('change',function()
        {
        });
    };
    
    // Data Start Index Change
    var handleDataStartChangeEvent = function ()
    {
        $('#data-start').on('keyup',function()
        {
            
        });
    };
    
    // Data Start Index Change
    var handleDataSubListsSelectAll = function ()
    {
        $('#select-all-lists').on('click',function()
        {
            // select event in sublists 
            $("#sub-lists .list-checkbox").each(function()
            {
                $(this).prop("checked",false);
                $(this).click();
            });
        });
        
        $('#deselect-all-lists').on('click',function()
        {
            // select event in sublists 
            $("#sub-lists .list-checkbox").each(function()
            {
                $(this).prop("checked",true);
                $(this).click();
            });
        });
    };
    
    // Data Emails Per Seeds Change
    var handleEmailsPerSeedsChangeEvent = function()
    {
        $('#emails-per-seeds').keyup(function()
        {          
          
        });
    };
    
    // handle Auto Response Switch
    var handleAutoResponseSwitch = function()
    {
        $('#auto-response').on('change',function()
        {          
            var value = $(this).val();
            
            if(value == 'off')
            {
                $('#auto-response-frequency').val('').prop('disabled',true);
                $('#auto-response-emails').val('').prop('disabled',true);
            }
            else
            {
                $('#auto-response-frequency').val('1000').removeAttr('disabled');
                $('#auto-response-emails').val('').removeAttr('disabled');
            }
        });
    };

    // Reset Data Count
    var resetDataCount = function () 
    {
        $('#data-start').val(0);
        $('#data-count').val(0);
    };
    
    // Reset Data Count
    var reloadDataLists = function () 
    {
        $('#data-types .list-type-checkbox').each(function(){
            if ($(this).prop("checked") == true)
            {
                $(this).click();
                $(this).click();
            }
        });
    };
    
    
    ////////////// Form Submit Section ////////////
    
    // Form Submit Buttons Click
    var handleFormSubmitEvent = function () 
    {
        $(".submit-form").click(function(e) 
        {
            e.preventDefault();
            
            var submitButtonName = $(this).attr('send-type');
            
            // add a confirmation to the form
            swal({
                title: "Form Confirmation",
                text: "You're about to procceed a " + submitButtonName,
                type: "info",
                showCancelButton: true,
                closeOnConfirm: false,
                showLoaderOnConfirm: true
            }, 
            function ()
            {
                // get the form data 
                 var formData = new FormData($("#mail-form")[0]);
                 var formURL = $("#mail-form").attr("action");

                 // add submit button
                 formData.append(submitButtonName,'true');

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
    
    // Message Creative HTML Display
    var handleHeaderDisplayEvent = function () 
    {
        $(".show-header").click(function()
        {
            $("#modal-dialog .modal-body .header-value").val(atob($(this).attr('data-header'))); 
        });
    };
    
    // Message Creative HTML Display
    var handleDraggablePortlets = function () 
    {
        if (!jQuery().sortable) {
            return;
        }

        $("#draggable-container").sortable({
            connectWith: ".portlet",
            items: ".portlet", 
            opacity: 0.8,
            handle : '.portlet-title',
            coneHelperSize: true,
            placeholder: 'portlet-sortable-placeholder',
            forcePlaceholderSize: true,
            tolerance: "pointer",
            helper: "clone",
            tolerance: "pointer",
            forcePlaceholderSize: !0,
            helper: "clone",
            cancel: ".portlet-sortable-empty, .portlet-fullscreen", // cancel dragging if portlet is in fullscreen mode
            revert: 250, // animation in milliseconds
            update: function(b, c) {
                if (c.item.prev().hasClass("portlet-sortable-empty")) {
                    c.item.prev().before(c.item);
                }                    
            }
        });
    };
    
    // return call
    return { init: function () 
    {
        // general section
        DisableSubmitTextClickEvent();
        handlePlaceHoldersHelpDisplayEvent();
        handleDraggablePortlets();
        
        // servers section
        handleServersRefreshEvent();
        handleServersChangeEvent();
        handleIpsChangeEvent();
        handleIpsSettingsEvent();
        handleSelectIPsTextAreaEvent();
        handleIPsFrequencySwitchEvent();
        
        // content section ( sponsors , offers , creatives ..... )
        handleSponsorsChangeEvent();
        handleOffersChangeEvent();
        handleFromNamesAndSubjectsChangeEvent();
        handleFromNamesAndSubjectsSwitchEvent();
        handleCreativesChangeEvent();
        handleCreativeDisplayEvent();
        handleGenerateLinksEvent();
        handleHeaderReset();
        handleHeaderChange();
        handleNegativeUploadEvent();
        handleVerticalsSelectDeselectAll();
        handleAutoResponseSwitch();
        handleHeaderDisplayEvent();
        handleUploadHerders();
        
        // data lists section 
        handleISPAndCountryChangeEvent();
        handleDataTypesClickEvent();
        handleDataStartChangeEvent();
        handleDataSubListsSelectAll();
        handleEmailsPerSeedsChangeEvent();
        
        // form submit section
        handleFormSubmitEvent();
    }};
}();

// initialize and activate the script
$(function() 
{
    Mail.init(); 
    wideArea();
    $('#servers').change();
});