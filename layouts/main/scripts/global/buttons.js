var Buttons = function () 
{
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
        
    return{
        init: function () 
        {
           handleConfirmation(); 
           handleSubmitButton();
        }
    };

}();

// initialize and activate the script
$(function(){ Buttons.init(); });