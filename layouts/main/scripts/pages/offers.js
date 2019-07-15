var Offers = function () 
{
    // display Creative details
    var handleCreativeDetails = function () 
    {
        $(".show-creative-as-html").click(function(evt)
        {
            evt.preventDefault();
            var creativeId = $(this).attr('data-creative-id');
            
            $.ajax({
                type: 'post',
                url: MailTng.getBaseURL() + "/offers/getCreative.json",
                data :  { "creative-id" : creativeId },
                dataType : 'JSON',
                success:function(result) 
                {
                    if(result !== null)
                    {
                        var creative = result['creative'];

                        if(creative != '')
                        {
                            var windowObject = window.open();
                            $(windowObject.document.body).html(creative);
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
    
    // display Creative links
    var handleCreativeLinks = function () 
    {
        $(".show-creative-links").click(function(evt)
        {
            evt.preventDefault();
            
            // empty the old details 
            $("#details-modal .modal-body").html('');
            $("#details-modal .modal-title").html('');
            
            var links = atob($(this).attr('data-links'));

            if(links != undefined)
            {
                $("#details-modal .modal-title").html('Creative Links');
                var html = '<table class="table table-bordered table-striped table-condensed"><thead><tr><th>Type</th><th>Link</th></tr></thead><tbody>';
                $("#details-modal .modal-body").html(html + links + '</tbody></table>'); 
            }
        });
    };

    return {
        init: function () 
        {
            handleCreativeDetails();
            handleCreativeLinks();
        }
    };
}();

// initialize and activate the script
$(function(){ Offers.init(); });
