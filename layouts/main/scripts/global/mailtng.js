
function adjustSize(){
    alert('yes');
    sleep(2);
    $("ul.dropdown-menu").css("max-height","300px");
}

function getAllips(){

    var myListIp = [];
    $("#selected-ips > option").each(function(el,id){
        myListIp.push(this.text);
    });
    return myListIp;
}

function getAllipsAvailable(){

    var myListIp = [];
    $("#available-ips > option").each(function(el,id){
        myListIp.push(this.text);
    });
    return myListIp;
}


function isValidIPV4(ipaddress) {  
  if (/^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/.test(ipaddress)) {  
    return (true)  
  } 
  return (false)  
}

function useIPV4(){
    var counter_ips = 0;
    $("#selected-ips > option").each(function(el,id){
        var line = this.text.replace(/\s/g, '');
        var res = line.split("|");
        if(isValidIPV4(res[1]) == true){
            $("#selected-ips > option:eq("+counter_ips+")").prop('selected', true);
        }
        else{
            $("#selected-ips > option:eq("+counter_ips+")").prop('selected', false);
        }
        counter_ips++;
    });
    $('#selected-ips').change();
}

function useIPV6(){
    var counter_ips = 0;
    $("#selected-ips > option").each(function(el,id){
        var line = this.text.replace(/\s/g, '');
        var res = line.split("|");
        if(isValidIPV4(res[1]) == false){
            $("#selected-ips > option:eq("+counter_ips+")").prop('selected', true);
        }
        else{
            $("#selected-ips > option:eq("+counter_ips+")").prop('selected', false);
        }
        counter_ips++;
    });
    $('#selected-ips').change();
}





function useIPV4Available(){
    var counter_ips = 0;
    $("#available-ips > option").each(function(el,id){
        var line = this.text.replace(/\s/g, '');
        var res = line.split("|");
        if(isValidIPV4(res[1]) == true){
            $("#available-ips > option:eq("+counter_ips+")").prop('selected', true);
        }
        else{
            $("#available-ips > option:eq("+counter_ips+")").prop('selected', false);
        }
        counter_ips++;
    });
    $('#available-ips').change();
}

function useIPV6Available(){
    var counter_ips = 0;
    $("#available-ips > option").each(function(el,id){
        var line = this.text.replace(/\s/g, '');
        var res = line.split("|");
        if(isValidIPV4(res[1]) == false){
            $("#available-ips > option:eq("+counter_ips+")").prop('selected', true);
        }
        else{
            $("#available-ips > option:eq("+counter_ips+")").prop('selected', false);
        }
        counter_ips++;
    });
    $('#available-ips').change();
}




// the ok button or my search 
function okSearch(){

    // get all selected Ips
    var myListIps = getAllips();
    
    var lines = $('#my_ip').val().split("\n"); // I'm looking for these
    for(var l = 0;l < lines.length;l++){

        for (var i = 0; i < myListIps.length; i++) {
            line = myListIps[i].replace(/\s/g, '');
            //console.log(line)
            var res = line.split("|");
            //  res[0] : server
            //  res[1] : ip
            //  res[2] : domain
            if(res[0] == lines[l] || res[1] == lines[l] || res[2] == lines[l] ){
                $("#selected-ips > option:eq("+i+")").prop('selected', true);
            }
        }   
    }

    $("#selected-ips").change();
    $('#search-box').modal('hide');
    
}

function okSearchAvailable(){

    // get all selected Ips
    var myListIps = getAllipsAvailable();
    
    var lines = $('#my_ip_available').val().split("\n"); // I'm looking for these
    for(var l = 0;l < lines.length;l++){

        for (var i = 0; i < myListIps.length; i++) {
            line = myListIps[i].replace(/\s/g, '');
            //console.log(line)
            var res = line.split("|");
            //  res[0] : server
            //  res[1] : ip
            //  res[2] : domain
            if(res[0] == lines[l] || res[1] == lines[l] || res[2] == lines[l] ){
                $("#available-ips > option:eq("+i+")").prop('selected', true);
            }
        }   
    }

    $("#available-ips").change();
    $('#search-box-available').modal('hide');

}


// the ok button or my search
function unselected_ips(){

    // get all selected Ips
    var myListIps = getAllips();
    
    // select all ips
    $('#selected-ips option').prop('selected', true);

    var lines = $('#my_ip').val().split("\n"); // I'm looking for these
    for(var l = 0;l < lines.length;l++){

        for (var i = 0; i < myListIps.length; i++) {
            line = myListIps[i].replace(/\s/g, '');
            //console.log(line)
            var res = line.split("|");
            //  res[0] : server
            //  res[1] : ip
            //  res[2] : domain
            if(res[0] == lines[l] || res[1] == lines[l] || res[2] == lines[l] ){
                $("#selected-ips > option:eq("+i+")").prop('selected', false);
            }
        }   
    }

    $('#selected-ips').change();
    $('#search-box').modal('hide');
}

function unselected_ips_available(){

    // get all selected Ips
    var myListIps = getAllipsAvailable();
    
    // select all ips
    $('#available-ips option').prop('selected', true);

    var lines = $('#my_ip_available').val().split("\n"); // I'm looking for these
    for(var l = 0;l < lines.length;l++){

        for (var i = 0; i < myListIps.length; i++) {
            line = myListIps[i].replace(/\s/g, '');
            //console.log(line)
            var res = line.split("|");
            //  res[0] : server
            //  res[1] : ip
            //  res[2] : domain
            if(res[0] == lines[l] || res[1] == lines[l] || res[2] == lines[l] ){
                $("#available-ips > option:eq("+i+")").prop('selected', false);
            }
        }   
    }

    $('#available-ips').change();
    $('#search-box-available').modal('hide');
}

// toggel the search box
function showBoxSearchAvailable(){
    $('#search-box-available').modal('show');
}

function showBoxSearch(){
    $('#search-box').modal('show');
}

var MailTng = function() 
{
    var isRTL = false;
    var isIE8 = false;
    var isIE9 = false;
    var isIE10 = false;
    var lastPopedPopover = null;
    var resizeHandlers = [];
    var brandColors = {
        'blue': '#89C4F4',
        'red': '#F3565D',
        'green': '#1bbc9b',
        'purple': '#9b59b6',
        'grey': '#95a5a6',
        'yellow': '#F8CB00'
    };

    var handleInit = function ()
    {
        isRTL = $('body').css('direction') === 'rtl';
        isIE8 = !!navigator.userAgent.match(/MSIE 8.0/);
        isIE9 = !!navigator.userAgent.match(/MSIE 9.0/);
        isIE10 = !!navigator.userAgent.match(/MSIE 10.0/);

        if (isIE10)
        {
            $('html').addClass('ie10');
        }

        if (isIE10 || isIE9 || isIE8)
        {
            $('html').addClass('ie');
        }
    };

    var runResizeHandlers = function ()
    {
        for (var i = 0; i < resizeHandlers.length; i++)
        {
            var each = resizeHandlers[i];
            each.call();
        }
    };

    var handleOnResize = function ()
    {
        var resize;

        if (isIE8)
        {
            var currheight;

            $(window).resize(function ()
            {
                if (currheight === document.documentElement.clientHeight)
                {
                    return;
                }

                if (resize)
                {
                    clearTimeout(resize);
                }

                resize = setTimeout(function () {
                    runResizeHandlers();
                }, 50);

                currheight = document.documentElement.clientHeight;
            });
        } else
        {
            $(window).resize(function ()
            {
                if (resize)
                {
                    clearTimeout(resize);
                }

                resize = setTimeout(function () {
                    runResizeHandlers();
                }, 50);
            });
        }
    };

    var handleUniform = function ()
    {
        if (!$().uniform)
        {
            return;
        }

        var test = $("input[type=checkbox]:not(.toggle, .md-check, .md-radiobtn, .make-switch, .icheck), input[type=radio]:not(.toggle, .md-check, .md-radiobtn, .star, .make-switch, .icheck)");

        if (test.size() > 0)
        {
            test.each(function ()
            {
                if ($(this).parents(".checker").size() === 0)
                {
                    $(this).show();
                    $(this).uniform();
                }
            });
        }
    };

    var handleiCheck = function () 
    {
        if (!$().iCheck) 
        {
            return;
        }

        $('.icheck').each(function () 
        {
            var checkboxClass = $(this).attr('data-checkbox') ? $(this).attr('data-checkbox') : 'icheckbox_minimal-grey';
            var radioClass = $(this).attr('data-radio') ? $(this).attr('data-radio') : 'iradio_minimal-grey';

            if (checkboxClass.indexOf('_line') > -1 || radioClass.indexOf('_line') > -1) 
            {
                $(this).iCheck({
                    checkboxClass: checkboxClass,
                    radioClass: radioClass,
                    insert: '<div class="icheck_line-icon"></div>' + $(this).attr("data-label")
                });
            } 
            else 
            {
                $(this).iCheck({
                    checkboxClass: checkboxClass,
                    radioClass: radioClass
                });
            }
        });
    };

    var handleBootstrapSwitch = function ()
    {
        if (!$().bootstrapSwitch)
        {
            return;
        }

        $('.make-switch').bootstrapSwitch();
    };

    var handleBootstrapConfirmation = function ()
    {
        if (!$().confirmation)
        {
            return;
        }

        $('[data-toggle=confirmation]').confirmation({container: 'body', btnOkClass: 'btn btn-sm btn-success', btnCancelClass: 'btn btn-sm btn-danger'});
    };

    var handleAccordions = function ()
    {
        $('body').on('shown.bs.collapse', '.accordion.scrollable', function (e)
        {
            MailTng.scrollTo($(e.target));
        });
    };

    var handleTabs = function ()
    {
        if (location.hash)
        {
            var tabid = encodeURI(location.hash.substr(1));

            $('a[href="#' + tabid + '"]').parents('.tab-pane:hidden').each(function () {
                var tabid = $(this).attr("id");
                $('a[href="#' + tabid + '"]').click();
            });

            $('a[href="#' + tabid + '"]').click();
        }

        if ($().tabdrop)
        {
            $('.tabbable-tabdrop .nav-pills, .tabbable-tabdrop .nav-tabs').tabdrop({
                text: '<i class="fa fa-ellipsis-v"></i>&nbsp;<i class="fa fa-angle-down"></i>'
            });
        }
    };

    var handleModals = function ()
    {
        $('body').on('hide.bs.modal', function ()
        {
            if ($('.modal:visible').size() > 1 && $('html').hasClass('modal-open') === false)
            {
                $('html').addClass('modal-open');
            } else if ($('.modal:visible').size() <= 1)
            {
                $('html').removeClass('modal-open');
            }
        });

        $('body').on('show.bs.modal', '.modal', function ()
        {
            if ($(this).hasClass("modal-scroll"))
            {
                $('body').addClass("modal-open-noscroll");
            }
        });

        $('body').on('hide.bs.modal', '.modal', function ()
        {
            $('body').removeClass("modal-open-noscroll");
        });

        $('body').on('hidden.bs.modal', '.modal:not(.modal-cached)', function ()
        {
            $(this).removeData('bs.modal');
        });
    };

    var handleDropdowns = function ()
    {
        $('body').on('click', '.dropdown-menu.hold-on-click', function (e)
        {
            e.stopPropagation();
        });
        
        $(".bs-select").selectpicker({iconBase:"fa",tickIcon:"fa-check",dropupAuto:false});
    };

    var handleAlerts = function () {
        $('body').on('click', '[data-close="alert"]', function (e) {
            $(this).parent('.alert').hide();
            $(this).closest('.note').hide();
            e.preventDefault();
        });

        $('body').on('click', '[data-close="note"]', function (e) {
            $(this).closest('.note').hide();
            e.preventDefault();
        });

        $('body').on('click', '[data-remove="note"]', function (e) {
            $(this).closest('.note').remove();
            e.preventDefault();
        });
    };

    var handleDropdownHover = function ()
    {
        $('[data-hover="dropdown"]').not('.hover-initialized').each(function ()
        {
            $(this).dropdownHover();
            $(this).addClass('hover-initialized');
        });
    };

    var handlePopovers = function ()
    {
        $('.popovers').popover();

        $(document).on('click.bs.popover.data-api', function ()
        {
            if (lastPopedPopover)
            {
                lastPopedPopover.popover('hide');
            }
        });
    };

    var handleScrollers = function ()
    {
        MailTng.initSlimScroll('.scroller');
    };

    var handleFancybox = function ()
    {
        if (!$.fancybox)
        {
            return;
        }

        if ($(".fancybox-button").size() > 0)
        {
            $(".fancybox-button").fancybox({
                groupAttr: 'data-rel',
                prevEffect: 'none',
                nextEffect: 'none',
                closeBtn: true,
                helpers: {
                    title: {
                        type: 'inside'
                    }
                }
            });
        }
    };

    var handleCounterup = function ()
    {
        if (!$().counterUp)
        {
            return;
        }

        $("[data-counter='counterup']").counterUp({
            delay: 10,
            time: 1000
        });
    };

    var handleFixInputPlaceholderForIE = function ()
    {
        if (isIE8 || isIE9)
        {
            $('input[placeholder]:not(.placeholder-no-fix), textarea[placeholder]:not(.placeholder-no-fix)').each(function ()
            {
                var input = $(this);

                if (input.val() === '' && input.attr("placeholder") !== '')
                {
                    input.addClass("placeholder").val(input.attr('placeholder'));
                }

                input.focus(function ()
                {
                    if (input.val() === input.attr('placeholder'))
                    {
                        input.val('');
                    }
                });

                input.blur(function ()
                {
                    if (input.val() === '' || input.val() === input.attr('placeholder'))
                    {
                        input.val(input.attr('placeholder'));
                    }
                });
            });
        }
    };

    var handleSelect2 = function ()
    {
        if ($().select2)
        {
            $.fn.select2.defaults.set("theme", "bootstrap");

            $('.select2me').select2({
                placeholder: "Select",
                width: 'auto',
                allowClear: true
            });
        }
    };

    var handleHeight = function ()
    {
        $('[data-auto-height]').each(function ()
        {
            var parent = $(this);
            var items = $('[data-height]', parent);
            var height = 0;
            var mode = parent.attr('data-mode');
            var offset = parseInt(parent.attr('data-offset') ? parent.attr('data-offset') : 0);

            items.each(function ()
            {
                if ($(this).attr('data-height') === "height")
                {
                    $(this).css('height', '');
                } else
                {
                    $(this).css('min-height', '');
                }

                var height_ = (mode === 'base-height' ? $(this).outerHeight() : $(this).outerHeight(true));

                if (height_ > height)
                {
                    height = height_;
                }
            });

            height = height + offset;

            items.each(function ()
            {
                if ($(this).attr('data-height') === "height")
                {
                    $(this).css('height', height);
                } else
                {
                    $(this).css('min-height', height);
                }
            });

            if (parent.attr('data-related'))
            {
                $(parent.attr('data-related')).css('height', parent.height());
            }
        });
    };
    
    var handleConfirmation = function () 
    {    
        $('.confirmation-button').click(function (evt) 
        {
            evt.preventDefault();
            var location = $(this).attr('href');

            swal({
                title: "Are you sure?",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-danger",
                confirmButtonText: "Yes",
                closeOnConfirm: false
              },
              function(){
                  window.location.href = location;
              });
        });
    }

    var handleSubmitButton = function()
    {
        $(".submit-loading").click(function(){
           $(this).html("<i class='fa fa-spinner fa-spin'></i> Loading ...");
           $(this).attr('disabled','disabled');
           $(this).closest('form').submit();
        });
    };

    var handleDatePickers = function () {

        if ($().datepicker) 
        {
            $('.date-picker').datepicker({
                rtl: MailTng.isRTL(),
                orientation: "right",
                autoclose: true
            });
        }
    };
    
    var handleInitTables = function () 
    { 
        $('.data-list').each(function() 
        {
            var id = $(this).attr('id');
            var table = $('#' + id);
            var page = $(this).attr('page');
            var order = $(this).attr('order');
            var method = $(this).attr('callbackMethod');
            MailTng.initDataTable(table,order,page,method);
        });  
    };
    
    var handleSummerNote = function () 
    { 
        $('.summernote').each(function() 
        {
            var element = $(this);
            var height = element.attr('data-height');
            var textarea = element.attr('data-textarea-id');
            
            element.summernote({
                height: height,
                callbacks: 
                {
                    onChange: function(contents, $editable) 
                    {
                        $('#' + textarea).val(contents);
                    },
                    onblur: function(e) 
                    {
                        $('#' + textarea).val(element.code());
                    },
                    onpaste: function(e) 
                    {
                        $('#' + textarea).val(element.code());
                    },
                    onImageUpload: function(files, editor, $editable) 
                    {
                        MailTng.blockUI();
                        sendFile(files[0],element);
                    }  
                }
            });
            
            function sendFile(file,element) 
            {
                data = new FormData();
                data.append('file', file);
                data.append('method','uploadImage');
                
                $.ajax({
                    url: MailTng.getBaseURL() + "/services.json",
                    data: data,
                    cache: false,
                    contentType: false,
                    processData: false,
                    type: 'POST',
                    dataType : 'JSON',
                    success: function(result)
                    {
                        if(result != false)
                        {
                            var status = result['status'];

                            if(status == 200)
                            {
                                element.summernote("insertImage",result['data']['url'],result['data']['name']);
                                MailTng.alertBox({title: result['data']['message'], type: 'success', allowOutsideClick: "true", confirmButtonClass: 'btn-primary'});
                            }
                            else
                            {
                                MailTng.alertBox({title: result['message'], type: "error", allowOutsideClick: "true", confirmButtonClass: "btn-danger"});
                            }
                            
                            MailTng.unblockUI();
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) 
                    {
                        MailTng.alertBox({title: textStatus + ' : ' + errorThrown, type: "error", allowOutsideClick: "true", confirmButtonClass: "btn-danger"});
                        MailTng.unblockUI();
                    }
                });
            }
            
            $('.note-codable').on('blur',function(){
                $('.btn-codeview').click();
            });
        });  
    };
    
    var handleQuickNavs = function ()
    {
        if ($('.quick-nav').length > 0) 
        {
            var stretchyNavs = $('.quick-nav');
            
            stretchyNavs.each(function () 
            {
                var stretchyNav = $(this), stretchyNavTrigger = stretchyNav.find('.quick-nav-trigger');

                stretchyNavTrigger.on('click', function (event) {
                    event.preventDefault();
                    stretchyNav.toggleClass('nav-is-visible');
                });
            });

            $(document).on('click', function (event) 
            {
                (!$(event.target).is('.quick-nav-trigger') && !$(event.target).is('.quick-nav-trigger span')) && stretchyNavs.removeClass('nav-is-visible');
            });
        }
    };
    
    var handleGo2Top = function () 
    {
        var Go2TopOperation = function () 
        {
            var CurrentWindowPosition = $(window).scrollTop();
            
            if (CurrentWindowPosition > 100) 
            {
                $(".go2top").show();
            } 
            else 
            {
                $(".go2top").hide();
            }
        };

        Go2TopOperation();
        
        if (navigator.userAgent.match(/iPhone|iPad|iPod/i)) 
        {
            $(window).bind("touchend touchcancel touchleave", function (e) 
            {
                Go2TopOperation();
            });
        } 
        else 
        {
            $(window).scroll(function () 
            {
                Go2TopOperation();
            });
        }

        $(".go2top").click(function (e) 
        {
            e.preventDefault();
            $("html, body").animate({scrollTop: 0}, 600);
        });
    };
    
    var handleSidebarMenu = function () 
    {
        var resBreakpointMd = MailTng.getResponsiveBreakpoint('md');
        
        $('.page-sidebar').on('click', 'li > a', function (e) 
        {    
            if (MailTng.getViewPort().width >= resBreakpointMd && $(this).parents('.page-sidebar-menu-hover-submenu').size() === 1) 
            {
                return;
            }

            if ($(this).next().hasClass('sub-menu') === false) 
            {
                if (MailTng.getViewPort().width < resBreakpointMd && $('.page-sidebar').hasClass("in")) 
                {
                    $('.page-header .responsive-toggler').click();
                }
                
                return;
            }

            if ($(this).next().hasClass('sub-menu always-open')) 
            {
                return;
            }

            var parent = $(this).parent().parent();
            var the = $(this);
            var menu = $('.page-sidebar-menu');
            var sub = $(this).next();

            var autoScroll = menu.data("auto-scroll");
            var slideSpeed = parseInt(menu.data("slide-speed"));
            var keepExpand = menu.data("keep-expanded");

            if (keepExpand !== true) 
            {
                parent.children('li.open').children('a').children('.arrow').removeClass('open');
                parent.children('li.open').children('.sub-menu:not(.always-open)').slideUp(slideSpeed);
                parent.children('li.open').removeClass('open');
            }

            var slideOffeset = -200;

            if (sub.is(":visible")) 
            {
                $('.arrow', $(this)).removeClass("open");
                $(this).parent().removeClass("open");
                
                sub.slideUp(slideSpeed, function () 
                {
                    if (autoScroll === true && $('body').hasClass('page-sidebar-closed') === false) 
                    {
                        if ($('body').hasClass('page-sidebar-fixed')) 
                        {
                            menu.slimScroll({
                                'scrollTo': (the.position()).top
                            });
                        } 
                        else 
                        {
                            MailTng.scrollTo(the, slideOffeset);
                        }
                    }
                });
            } 
            else 
            {
                $('.arrow', $(this)).addClass("open");
                $(this).parent().addClass("open");
                
                sub.slideDown(slideSpeed, function () 
                {
                    if (autoScroll === true && $('body').hasClass('page-sidebar-closed') === false) 
                    {
                        if ($('body').hasClass('page-sidebar-fixed')) 
                        {
                            menu.slimScroll({
                                'scrollTo': (the.position()).top
                            });
                        } 
                        else 
                        {
                            MailTng.scrollTo(the, slideOffeset);
                        }
                    }
                });
            }
            
            e.preventDefault();
        });

        $(document).on('click', '.page-header-fixed-mobile .responsive-toggler', function () 
        {
            MailTng.scrollTop();
        });
    };
    
    var handleFormValidations = function ()
    {
        $('form.validate :submit').on('click',function(e)
        {
            e.preventDefault();
            
            var isValid = true;
            var button = $(this);
            var html = button.html();
            button.html("<i class='fa fa-spinner fa-spin'></i> Loading ...");
            button.attr('disabled','disabled');
           
            $(":input",$(this).closest('form.validate')).each(function()
            {
                var required = $(this).attr('data-required') != undefined && $(this).attr('data-required') == 'true';
                var filled = $(this).val() != undefined && $(this).val() != null && $(this).val() != '';
                
                if(required == true && filled == false)
                {
                    MailTng.alertBox({title:$(this).attr('data-validation-message'),type:"error",allowOutsideClick:"true",confirmButtonClass:"btn-danger"});
                    button.html(html);
                    button.removeAttr('disabled');
                    isValid = false;
                    return false;
                }
            });
            
            if(isValid == true)
            {
                $(this).closest('form.validate').submit();
            }
        });
    };
    
     var handlesessionTimeout = function () 
     {
        if ($.sessionTimeout) 
        {
            $.sessionTimeout({
                title: 'Session Timeout Notification',
                message: 'Your session is about to expire.',
                redirUrl: MailTng.getBaseURL() + '/authentication/logout.html',
                logoutUrl: MailTng.getBaseURL() + '/authentication/logout.html',
                keepAliveUrl : MailTng.getBaseURL(),
                warnAfter: 1790000,
                redirAfter: 1800000, //redirect after 30 minutes,
                ignoreUserActivity: true,
                countdownMessage: 'Redirecting in {timer} seconds.',
                countdownBar: true
            });
        }
    }
    
    var handleFormRepeter = function () 
     {
        $('.mt-repeater').each(function()
        {
            $(this).repeater({
                show: function () 
                {
                    $(this).slideDown();
                    $('.date-picker').datepicker({
                        rtl: MailTng.isRTL(),
                        orientation: "left",
                        autoclose: true
                    });
                    $(this).find('select').each(function(){
                        $(this).val($("option:first",$(this)).val()).change();
                    });
                },
                hide: function (deleteElement) 
                {
                    var element = $(this);

                    swal({
                        title: "Are you sure you want to delete this element?",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonClass: "btn-danger",
                        confirmButtonText: "Yes",
                        closeOnConfirm: true
                    },
                    function () {
                        element.slideUp(deleteElement);
                    });
                },
                ready: function (setIndexes) {}
            });
        });
    };
    
    return {
        init: function ()
        {
            //Core handlers
            handleInit(); // initialize core variables
            handleOnResize(); // set and handle responsive    

            //UI Component handlers          
            handleUniform(); // hanfle custom radio & checkboxes
            handleiCheck(); // handles custom icheck radio and checkboxes
            handleBootstrapSwitch(); // handle bootstrap switch plugin
            handleDropdownHover();
            handleScrollers(); // handles slim scrolling contents 
            handleFancybox(); // handle fancy box
            handleSelect2(); // handle custom Select2 dropdowns
            handleAlerts(); //handle closabled alerts
            handleDropdowns(); // handle dropdowns
            handleTabs(); // handle tabs
            handlePopovers(); // handles bootstrap popovers
            handleAccordions(); //handles accordions 
            handleModals(); // handle modals
            handleBootstrapConfirmation(); // handle bootstrap confirmations
            handleCounterup(); // handle counterup instances
            handleConfirmation(); // handle buttons confirmation
            handleSubmitButton(); // handle submit loading 
            handleDatePickers(); // handle datepickers
            handleInitTables(); // handle datatables
            handleQuickNavs(); // handle quicknavs 
            handleGo2Top(); // handle go to top 
            handleSidebarMenu(); // handle sidebar menu
            handleFormValidations(); // handle validations
            handlesessionTimeout(); // handles session Timeout
            handleFormRepeter(); // handles form repeter
            handleSummerNote(); // handle summernote 
            
            //Handle group element heights
            this.addResizeHandler(handleHeight); // handle auto calculating height on window resize

            // Hacks
            handleFixInputPlaceholderForIE(); //IE8 & IE9 input placeholder issue fix
        },
        addResizeHandler: function (func)
        {
            resizeHandlers.push(func);
        },
        scrollTo: function(el, offeset) 
        {
            var pos = (el && el.size() > 0) ? el.offset().top : 0;

            if (el) 
            {
                if ($('body').hasClass('page-header-fixed')) 
                {
                    pos = pos - $('.page-header').height();
                } 
                else if ($('body').hasClass('page-header-top-fixed')) 
                {
                    pos = pos - $('.page-header-top').height();
                } 
                else if ($('body').hasClass('page-header-menu-fixed')) 
                {
                    pos = pos - $('.page-header-menu').height();
                }
                
                pos = pos + (offeset ? offeset : -1 * el.height());
            }

            $('html,body').animate({
                scrollTop: pos
            }, 'slow');
        },
        initSlimScroll: function(el) 
        {
            $(el).each(function() 
            {
                if ($(this).attr("data-initialized")) 
                {
                    return;
                }

                var height;

                if ($(this).attr("data-height")) 
                {
                    height = $(this).attr("data-height");
                } 
                else 
                {
                    height = $(this).css('height');
                }

                $(this).slimScroll({
                    allowPageScroll: true,
                    size: '7px',
                    color: ($(this).attr("data-handle-color") ? $(this).attr("data-handle-color") : '#bbb'),
                    wrapperClass: ($(this).attr("data-wrapper-class") ? $(this).attr("data-wrapper-class") : 'slimScrollDiv'),
                    railColor: ($(this).attr("data-rail-color") ? $(this).attr("data-rail-color") : '#eaeaea'),
                    position: isRTL ? 'left' : 'right',
                    height: height,
                    alwaysVisible: ($(this).attr("data-always-visible") === "1" ? true : false),
                    railVisible: ($(this).attr("data-rail-visible") === "1" ? true : false),
                    disableFadeOut: true
                });

                $(this).attr("data-initialized", "1");
            });
        },
        destroySlimScroll: function(el) 
        {
            $(el).each(function() 
            {
                if ($(this).attr("data-initialized") === "1") 
                { 
                    $(this).removeAttr("data-initialized");
                    $(this).removeAttr("style");

                    var attrList = {};

                    if ($(this).attr("data-handle-color")) 
                    {
                        attrList["data-handle-color"] = $(this).attr("data-handle-color");
                    }
                    
                    if ($(this).attr("data-wrapper-class")) 
                    {
                        attrList["data-wrapper-class"] = $(this).attr("data-wrapper-class");
                    }
                    
                    if ($(this).attr("data-rail-color")) 
                    {
                        attrList["data-rail-color"] = $(this).attr("data-rail-color");
                    }
                    
                    if ($(this).attr("data-always-visible")) 
                    {
                        attrList["data-always-visible"] = $(this).attr("data-always-visible");
                    }
                    
                    if ($(this).attr("data-rail-visible")) 
                    {
                        attrList["data-rail-visible"] = $(this).attr("data-rail-visible");
                    }

                    $(this).slimScroll({
                        wrapperClass: ($(this).attr("data-wrapper-class") ? $(this).attr("data-wrapper-class") : 'slimScrollDiv'),
                        destroy: true
                    });

                    var the = $(this);
                    
                    $.each(attrList, function(key, value) {
                        the.attr(key, value);
                    });

                }
            });
        },
        scrollTop: function() 
        {
            MailTng.scrollTo();
        },
        blockUI: function(options) 
        {
            options = $.extend(true, {}, options);
            
            var html = '';
            if (options.animate) 
            {
                html = '<div class="loading-message ' + (options.boxed ? 'loading-message-boxed' : '') + '">' + '<div class="block-spinner-bar"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div>' + '</div>';
            } 
            else if (options.iconOnly) 
            {
                html = '<div class="loading-message ' + (options.boxed ? 'loading-message-boxed' : '') + '"><img src="' + MailTng.getLayoutURL() + '/images/icons/loading-spinner-grey.gif" align=""></div>';
            } 
            else if (options.textOnly) 
            {
                html = '<div class="loading-message ' + (options.boxed ? 'loading-message-boxed' : '') + '"><span>&nbsp;&nbsp;' + (options.message ? options.message : 'LOADING...') + '</span></div>';
            } 
            else 
            {
                html = '<div class="loading-message ' + (options.boxed ? 'loading-message-boxed' : '') + '"><img src="' + MailTng.getLayoutURL() + '/images/icons/loading-spinner-grey.gif" align=""><span>&nbsp;&nbsp;' + (options.message ? options.message : 'LOADING...') + '</span></div>';
            }

            if (options.target) 
            {
                var el = $(options.target);
                
                if (el.height() <= ($(window).height())) 
                {
                    options.cenrerY = true;
                }
                
                el.block({
                    message: html,
                    baseZ: options.zIndex ? options.zIndex : 1000,
                    centerY: options.cenrerY !== undefined ? options.cenrerY : false,
                    css: {
                        top: '10%',
                        border: '0',
                        padding: '0',
                        backgroundColor: 'none'
                    },
                    overlayCSS: {
                        backgroundColor: options.overlayColor ? options.overlayColor : '#555',
                        opacity: options.boxed ? 0.05 : 0.1,
                        cursor: 'wait'
                    }
                });
            }
            else 
            { 
                $.blockUI({
                    message: html,
                    baseZ: options.zIndex ? options.zIndex : 1000,
                    css: {
                        border: '0',
                        padding: '0',
                        backgroundColor: 'none'
                    },
                    overlayCSS: {
                        backgroundColor: options.overlayColor ? options.overlayColor : '#555',
                        opacity: options.boxed ? 0.05 : 0.1,
                        cursor: 'wait'
                    }
                });
            }
        },
        unblockUI: function(target) 
        {
            if (target) 
            {
                $(target).unblock({
                    onUnblock: function() {
                        $(target).css('position', '');
                        $(target).css('zoom', '');
                    }
                });
            } 
            else 
            {
                $.unblockUI();
            }
        },
        initUniform: function(els) 
        {
            if (els) 
            {
                $(els).each(function() 
                {
                    if ($(this).parents(".checker").size() === 0) 
                    {
                        $(this).show();
                        $(this).uniform();
                    }
                });
            } 
            else 
            {
                handleUniform();
            }
        },
        updateUniform: function(els) 
        {
            $.uniform.update(els);
        },
        initFancybox: function() 
        {
            handleFancybox();
        },
        getViewPort: function() 
        {
            var e = window,
                a = 'inner';
        
            if (!('innerWidth' in window)) 
            {
                a = 'client';
                e = document.documentElement || document.body;
            }

            return {
                width: e[a + 'Width'],
                height: e[a + 'Height']
            };
        },
        getUniqueID: function(prefix) 
        {
            return 'prefix_' + Math.floor(Math.random() * (new Date()).getTime());
        },
        isIE8: function() 
        {
            return isIE8;
        },
        isIE9: function() 
        {
            return isIE9;
        },
        isRTL: function() 
        {
            return isRTL;
        },
        getCurrentURL : function() 
        {
            return window.location.href;
        }, 
        getBaseURL : function() 
        {
            return window.location.protocol+"//"+window.location.host;
        }, 
        getLayoutURL: function () 
        {
            return MailTng.getBaseURL() + '/layouts/main';
        },
        getBrandColor: function(name) 
        {
            if (brandColors[name]) 
            {
                return brandColors[name];
            } 
            else 
            {
                return '';
            }
        },
        getResponsiveBreakpoint: function(size) 
        {
            var sizes = {
                'xs' : 480,     // extra small
                'sm' : 768,     // small
                'md' : 992,     // medium
                'lg' : 1200     // large
            };

            return sizes[size] ? sizes[size] : 0; 
        },
        formatBytes : function (bytes,decimals) {
            if(bytes == 0) return '0 Byte';
            var k = 1000;
            var dm = decimals + 1 || 3;
            var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
            var i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
        },
        alertBox: function (settings) 
        {
            swal(settings);
        },
        alertConfirmationBox: function (settings,successTitle,successMessage,cancelTitle,cancelMessage) 
        {
            swal(settings,
            function (isConfirm) 
            {
                if (isConfirm) 
                {
                    swal(successTitle, successMessage, "success");
                } 
                else 
                {
                    swal(cancelTitle, cancelMessage, "error");
                }
            });
        },
        executeFunctionByName : function(functionName, context) 
        {
            var namespaces = functionName.split(".");
            var func = namespaces.pop();
            
            for(var i = 0; i < namespaces.length; i++) 
            {
              context = context[namespaces[i]];
            }
            
            return context[func].apply(context,null);
        },
        createChart: function (id, data, colors,lastIndex)
        {
            var chart = $('#' + id);
            
            if (chart.size() != 0)
            {
                $('#' + id + '-loading').hide();
                $('#' + id + '-content').show();

                var chartData = [];

                for (var index in data)
                {
                    var tmpData = [];
                    
                    
                    for (var i = 1;i <= lastIndex;i++)
                    {
                        var found = false;

                        for (var j = 0; j < data[index][1].length;j++)
                        {
                            if(data[index][1][j][0] == i)
                            {
                                tmpData.push([i,data[index][1][j][1]]);
                                found = true;
                            }
                        }

                        if(found == false)
                        {
                            tmpData.push([i,0]);
                        }
                    }
                    
                    chartData.push({
                        label: data[index][0],
                        data: tmpData,
                        lines: {lineWidth: 1},
                        shadowSize: 0
                    });
                }

                $.plot(chart, chartData ,{
                    series: {
                        lines: {
                            show: true,
                            lineWidth: 2,
                            fill: true,
                            fillColor: {
                                colors: [{
                                    opacity: 0.05
                                }, {
                                    opacity: 0.01
                                }]
                            }
                        },
                        points: {
                            show: true,
                            radius: 3,
                            lineWidth: 1
                        },
                        shadowSize: 2
                    },
                    grid: {
                        hoverable: true,
                        clickable: true,
                        tickColor: "#eee",
                        borderColor: "#eee",
                        borderWidth: 1
                    },
                    colors: colors,
                    xaxis: {
                        ticks: 11,
                        tickDecimals: 0,
                        tickColor: "#eee",
                    },
                    yaxis: {
                        ticks: 11,
                        tickDecimals: 0,
                        tickColor: "#eee",
                    }
                });

                var previousPoint = null;

                chart.bind("plothover", function (event, pos, item)
                {
                    event.preventDefault();
                    
                    $("#x").text(pos.x.toFixed(2));
                    $("#y").text(pos.y.toFixed(2));

                    if (item)
                    {
                        if (previousPoint != item.dataIndex)
                        {
                            previousPoint = item.dataIndex;
                            $("#tooltip").remove();
                            MailTng.showChartTooltip(item.pageX, item.pageY, item.datapoint[1] + " " + item.series.label);
                        }
                    } else
                    {
                        $("#tooltip").remove();
                        previousPoint = null;
                    }
                });
            }
        },
        showChartTooltip : function(x, y,yValue) 
        {
            $('<div id="tooltip" class="chart-tooltip">' + yValue + '<\/div>').css({
                position: 'absolute',
                display: 'none',
                top: y - 40,
                left: x - 40,
                border: '0px solid #ccc',
                padding: '2px 6px',
                'background-color': '#fff'
            }).appendTo("body").fadeIn(200);
        },
        convertFromDecToHex: function(text)
        {
            var hexequiv = new Array ("0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "A", "B", "C", "D", "E", "F");
            return hexequiv[(text >> 4) & 0xF] + hexequiv[text & 0xF]; 
        },
        encodeToUnicode: function (text) 
        {
            var highsurrogate = 0;
            var suppCP;
            var outputString = '';
            for (var i = 0; i < text.length; i++) 
            {
                    var cc = text.charCodeAt(i); 
                    
                    if (cc < 0 || cc > 0xFFFF) 
                    {
                        alert('Unexpected charCodeAt result, cc=' + cc + '!');
                    }
                    
                    if (highsurrogate != 0) 
                    {  
                        if (0xDC00 <= cc && cc <= 0xDFFF) 
                        {
                            suppCP = 0x10000 + ((highsurrogate - 0xD800) << 10) + (cc - 0xDC00); 
                            outputString += ' ' + MailTng.convertFromDecToHex(0xF0 | ((suppCP>>18) & 0x07)) + ' ' + MailTng.convertFromDecToHex(0x80 | ((suppCP>>12) & 0x3F)) + ' ' + MailTng.convertFromDecToHex(0x80 | ((suppCP>>6) & 0x3F)) + ' ' + MailTng.convertFromDecToHex(0x80 | (suppCP & 0x3F));
                            highsurrogate = 0;
                            continue;
                        }
                        else 
                        {
                            outputString += 'Error in convertCharStr2UTF8: low surrogate expected, cc=' + cc + '!';
                            highsurrogate = 0;
                        }
                    }
                    if (0xD800 <= cc && cc <= 0xDBFF) 
                    { 
                        highsurrogate = cc;
                    }
                    else 
                    {
                        if (cc <= 0x7F) { outputString += ' ' + MailTng.convertFromDecToHex(cc); }
                        else if (cc <= 0x7FF) { outputString += ' ' + MailTng.convertFromDecToHex(0xC0 | ((cc>>6) & 0x1F)) + ' ' + MailTng.convertFromDecToHex(0x80 | (cc & 0x3F)); } 
                        else if (cc <= 0xFFFF) { outputString += ' ' + MailTng.convertFromDecToHex(0xE0 | ((cc>>12) & 0x0F)) + ' ' + MailTng.convertFromDecToHex(0x80 | ((cc>>6) & 0x3F)) + ' ' + MailTng.convertFromDecToHex(0x80 | (cc & 0x3F)); } 
                    }
            }
            
            return outputString.substring(1);
        },
        initDataTable : function(table,orderColumns,pages,method)
        {
            var order = (orderColumns != undefined && orderColumns != '') ? orderColumns : 'asc';
            var page = (pages != undefined && pages != '') ? pages : 10;
            var callbackMethod = (method != undefined && method != '') ? method : '';

            var oTable = table.dataTable({
                "language": 
                {
                    "aria": 
                    {
                        "sortAscending": ": activate to sort column ascending",
                        "sortDescending": ": activate to sort column descending"
                    },
                    "emptyTable": "No data available in table",
                    "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                    "infoEmpty": "No entries found",
                    "infoFiltered": "(filtered1 from _MAX_ total entries)",
                    "lengthMenu": "_MENU_ entries",
                    "search": "Search:",
                    "zeroRecords": "No matching records found"
                },
                buttons: [
                    { extend: 'print', className: 'btn dark btn-outline' },
                    { extend: 'copy', className: 'btn red btn-outline' },
                    { extend: 'pdf', className: 'btn green btn-outline' },
                    { extend: 'excel', className: 'btn yellow btn-outline ' },
                    { extend: 'csv', className: 'btn purple btn-outline ' },
                    { extend: 'colvis', className: 'btn dark btn-outline', text: 'Columns'}
                ],
                responsive: false,
                "order": [
                    [0, order]
                ],

                "lengthMenu": [
                    [5, 10, 15, 20, -1],
                    [5, 10, 15, 20, "All"]
                ],
                "pageLength": page,
                "fnDrawCallback": function(oSettings) 
                {
                    if(callbackMethod != '')
                    {
                        MailTng.executeFunctionByName(callbackMethod,window);
                    }
                }
            });

            // handle datatable custom tools
            $('#data-list-tools > li > a.tool-action').on('click', function() {
                var action = $(this).attr('data-action');
                oTable.DataTable().button(action).trigger();
            });
        },
        updateTable : function(id,data)
        {
            var datatable = $('#' + id).dataTable().api();
            datatable.rows.add(data); 
            datatable.draw();
        },
        clearTable : function(id)
        {
            var datatable = $('#' + id).dataTable().api();
            datatable.clear(); 
        },
        createHTMLTable : function(columns,data)
        {
            var html = "<table class='table table-bordered table-striped table-condensed'><thead><tr>";
            
            for (var i in columns) 
            {
                html += "<th>" + columns[i].replace('_',' ').toUpperCase() + "</th>";
            }
            
            html += "</tr></thead><tbody>";
            
            for (var i in data) 
            {
                html += "<tr>";
                
                for (var j in columns) 
                {
                    html += "<td>" + data[i][columns[j]] + "</td>";
                }
                
                html += "</tr>";
            }
            
            html += "</tbody></table>";
            
            return html;
        }
    };
}();

// Run The Class
$(function() {  MailTng.init(); });