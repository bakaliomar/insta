var Negative = function () 
{   

    // Words Event 
    var handleGetWordsEvent = function () 
    {
        $('#get-words').on('click',function(e)
        {
            e.preventDefault();

            var urls = $("#urls").val();

            if(urls != undefined && urls != null && urls != '')
            {   
                $('#get-words').html("<i class='fa fa-spinner fa-spin'></i> Loading ...");
                $('#get-words').attr('disabled','disabled');
  
                $.ajax({
                    type: 'post',
                    url: MailTng.getBaseURL() + "/negative/getWords.json",
                    data :  {
                        urls : urls
                    },
                    dataType : 'json',
                    async: false,
                    success:function(result) 
                    {
                        if(result !== null)
                        {
                            $("#results-textarea").val(result['words']);
                            
                            $('#get-words').html('<i class="fa fa-download"></i> Get Words');
                            $('#get-words').removeAttr('disabled');
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

    return {
        init: function () 
        {
            handleGetWordsEvent();
        }
    };

}();

// initialize and activate the script
$(function(){ Negative.init(); });