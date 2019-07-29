$(function () {
    $(function () {
        $('input').iCheck({
            checkboxClass: 'icheckbox_square-blue',
            radioClass: 'iradio_square-blue',
            increaseArea: '20%' // optional
        });
    });


    var login_form = $("#login_form");
    var forgot_password_form = $("#forgot_password");
    var new_password_form = $("#new_password");

    $(login_form).validate({
        errorClass: 'error',
        rules: {
            'email': {
                required: true,
                email: true
            },
            'password':{
                required: true,
                minlength: 8
            }
        },
        submitHandler: function(form){
            $.post("/ajax/login", $("#login_form").serializeJSON(), function (response) {
                if(!response.status){
                    $(".alert-danger").show();
                    $(".error_message").html(response.msg);
                } else{
                    window.location.replace("/");
                }
            }, 'json');
        }
    });


    $(forgot_password_form).validate({
       errorClass: 'error',
       rules: {
           'email': {
               required: true,
               email: true
           }
       },
        submitHandler: function(form){
            $.post("/ajax/forgot_password", $("#forgot_password").serializeJSON(), function(response){
                var messages_class = response.status ? "alert-success" : "alert-danger";
                $(".messages").removeClass("alert-danger").removeClass("alert-success");
                $(".messages").addClass(messages_class);
                $(".messages").show();
                $(".message_text").html(response.msg);

                if(response.status){
                    $("#forgot_password").hide();
                }

            }, 'json');
        }
    });


    $(new_password_form).validate({
       errorClass: 'error',
       rules: {
           'password':{
               required: true,
               minlength: 8
           },
           'password_repeat':{
               required: true,
               minlength: 8
           }
       },
        submitHandler: function(form){
            $.post("/ajax/new_password", $("#new_password").serializeJSON(), function(response){
                var messages_class = response.status ? "alert-success" : "alert-danger";
                $(".messages").removeClass("alert-danger").removeClass("alert-success");
                $(".messages").addClass(messages_class);
                $(".messages").show();
                $(".message_text").html(response.msg);

                if(response.status){
                    $("#new_password").hide();
                    setTimeout(function(){
                        window.location.replace("/account/login");
                    },5000);

                }


            }, 'json');
        }
    });
});