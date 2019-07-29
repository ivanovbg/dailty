$(document).ready(function() {
    $(function () {
        //Add text editor
        $(".message").wysihtml5();
    });


    $(".js-send-email-to-client").off().on("click", function(){
        var client_id = $(this).data("client");
        var client_name = $(this).data("name");
        var modal = $(".sendClientEmail");
        var form = $(".send_mail_form");
        var success = $(".send_mail_success");
        var submit_button = $(".send_message");
        var loader = $(".send_loader");


        form.show();
        success.hide();
        submit_button.show();
        loader.hide();

        modal.find(".modal-title").html("Изпращане на емейл до " + client_name);

        submit_button.off().on("click", function(){
           var message = modal.find(".message");
           var subject = modal.find("input[name=subject]");


           $(".send_mail_error").hide();

           if(!message.val() || !subject.val()){
               $(".send_mail_error").show();
               $(".send_mail_error_message").html("Не сте въвели съобщение или тема");
           }else {
               loader.show();
               $.post("/ajax/send_email_to_client", {
                   account: account,
                   company: company,
                   message: message.val(),
                   client_id: client_id,
                   subject: subject.val()
               }, function (response) {
                    if(response.status){
                        subject.val("");
                        $('.message').data("wysihtml5").editor.clear();
                        form.hide();
                        success.show();
                        submit_button.hide();

                    }else{
                        $(".send_mail_error").show();
                        $(".send_mail_error_message").html(response.msg);
                    }
               }, 'json');
           }
        });
    });

});