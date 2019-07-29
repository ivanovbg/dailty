$('.access_switch').change(function() {
    var field = $(this).data('field');
    var manage_btn = $('input[name='+field+'Manage]');

    if(!$(this).prop('checked')){
        manage_btn.bootstrapToggle('off').bootstrapToggle('disable');
    }else{
        manage_btn.bootstrapToggle('off').bootstrapToggle('enable');
    }
});

$('.timepicker').timepicker({
    showInputs: false,
    showMeridian: false
});


$(".manage_day").change(function(){
    var values = ['Start', 'End'];
    var field = $(this).data("day");


    if(!$(this).prop('checked')) {
        values.forEach(function(value){
            $("input[name="+field+value+"]").attr("disabled", "true");
        });
    }else{
        values.forEach(function(value){
            $("input[name="+field+value+"]").removeAttr("disabled");
        });
    }
});
