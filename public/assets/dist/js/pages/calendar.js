var calendar = $('#calendar').fullCalendar({
    locale: 'bg',
    schedulerLicenseKey: 'GPL-My-Project-Is-Open-Source',
    header: {
        left: 'prev,next today',
        center: 'title',
        right: 'month,agendaWeek,agendaDay, listDay'
    },
    eventSources: [{
        url: '/ajax/calendar/get_events',
        method: "POST"
    }
    ],
    defaultView: 'agendaWeek',
    displayEventTime: true,
    eventRender: function (event, element, view){
        if (event.allDay === 'true') {
            event.allDay = true;
        } else {
            event.allDay = false;
        }
    },
    resources:  JSON.parse(providers),
    businessHours: JSON.parse(working_time),
    selectable: true,
    select: function (start, end, allDay) {
        createEvent(calendar, false, start, end);
    },
    height: 'auto',

    editable: true,
    eventDrop: function (event, delta) {
        var start = $.fullCalendar.formatDate(event.start, "Y-MM-DD HH:mm:ss");
        var end = $.fullCalendar.formatDate(event.end, "Y-MM-DD HH:mm:ss");
        $.ajax({
            url: '/ajax/calendar/update_event',
            data: 'id=' + event.id + '&start=' + start + '&end=' + end ,
            type: "POST",
            success: function (response) {

            }
        });
    },

    eventResize: function (event, delta) {
        var start = $.fullCalendar.formatDate(event.start, "Y-MM-DD HH:mm:ss");
        var end = $.fullCalendar.formatDate(event.end, "Y-MM-DD HH:mm:ss");
        $.ajax({
            url: '/ajax/calendar/update_event',
            data: 'id=' + event.id + '&start=' + start + '&end=' + end ,
            type: "POST",
            success: function (response) {

            }
        });
    },


    eventClick: function (event) {
        createEvent(calendar, event, false, false);
    },
    eventMouseover: function(calEvent, jsEvent) {
        var start = $.fullCalendar.formatDate(calEvent.start, "HH:mm");
        var end = $.fullCalendar.formatDate(calEvent.end, "HH:mm");
        var status  = [];
        status[0] = "Назначена";
        status[1] = "Извършва се";
        status[2] = "Приключена";
        status[3] = "Отказана от клиент";
        var tooltip = '<div class="tooltipevent" style="width:300px;border:1px solid #422ba0;border-radius:5px;background:whitesmoke;position:absolute;z-index:10001;padding:5px;color:#3c78b5;">Клиент: '+calEvent.client_name+'<br /> Услуга: '+calEvent.service_name+'<br /> Статус: '+status[calEvent.status]+'<br />Начало: '+start+' <br /> Край: '+end+'</div>';
        var $tooltip = $(tooltip).appendTo('body');

        $(this).mouseover(function(e) {
            $(this).css('z-index', 10000);
            $tooltip.fadeIn('500');
            $tooltip.fadeTo('10', 1.9);
        }).mousemove(function(e) {
            $tooltip.css('top', e.pageY + 10);
            $tooltip.css('left', e.pageX + 20);
        });
    },
    eventMouseout: function(calEvent, jsEvent) {
        $(this).css('z-index', 8);
        $('.tooltipevent').remove();
    },


});


function createEvent(calendar, event, start, end){
    var modal = $(".eventView");
    var modal_title = modal.find(".modal-title");
    var modal_body = modal.find(".modal-body");

    var services_div = $(".services_div");
    var services = $(".services");

    var providers_div = $(".providers_div");
    var providers = $(".providers");

    var clients_div = $(".clients_div");
    var clients = $(".clients");

    var title = event ? "Редактиране на задача" : "Добавяне на нова задача";

    var provider_id = event ? event.provider : false;
    var service_id = event? event.service : false;
    var client_id = event ? event.client : false;
    var description = event ? event.description : "";

    modal_title.html(title);
    modal.modal("show");

    if(!event){
        $(".services_div").hide();
        $(".deleteEvent").hide();
        $(".providers option:selected").removeAttr("selected");
        $(".clients option:selected").removeAttr("selected");
        $(".status option:selected").removeAttr("selected");
    }else{
        $(".providers option[value=" + provider_id + "]").prop("selected", true);
        $(".clients option[value=" + client_id + "]").prop("selected", true);
        $(".status option[value=" + event.status + "]").prop("selected", true);
        $(".deleteEvent").show();
    }



    $(".services").html("");
    $(".info").html("");
    $(".description").remove();

    if(event){
        getServices(event.provider, services_div, services, service_id);
    }

    $(".providers").off().on("change", function () {
        getServices($(this).val(), services_div, services);
    });

    $(".eventView").off().on("click", ".services", function(){
        $(".info").html("<span class=\"badge badge-secondary\">Време: "+$(".services option:selected").data('time')+"минути</span><span class=\"badge badge-secondary\">Цена: "+$(".services option:selected").data('price')+"лв.</span>");
    });

    $(".deleteEvent").off().on("click", function(){
        if(event) {
            var data = "id="+event.id ;
            $.ajax({
                url: '/ajax/calendar/delete_event',
                data: data,
                type: "POST",
                success: function (response) {
                    if (response.status) {
                        calendar.fullCalendar('removeEvents', event.id);
                        calendar.fullCalendar('unselect');
                        modal.modal('hide');
                    } else {
                        alert(response.msg);
                    }
                }
            });
        }
    });

    $(".save_event").off().on("click", function(){
        var start_date = event ? event.start : start;
        var start_new = $.fullCalendar.formatDate(start_date, "Y-MM-DD HH:mm:ss");
        end_date = event ? event.end : start_date.add(moment.duration(convertMinsToHrsMins($(".services option:selected").data("time"))));
        var end_new = $.fullCalendar.formatDate(end_date, "Y-MM-DD HH:mm:ss");

        var data = $(".event_form").serialize();
        data += "&start="+start_new+"&end="+end_new;
        if(event){
            data += "&id="+event.id
        }

        $.ajax({
            url: '/ajax/calendar/save_event',
            data: data,
            type: "POST",
            success: function (response) {
                if(response.status){
                    if(event){
                        event.title =response.event.title;
                        event.description = response.event.description;
                        event.id = response.event.id;
                        event.client=response.event.client;
                        event.service=response.event.service;
                        event.provider=response.event.provider;
                        event.start=start_new;
                        event.end=end_new;
                        event.service_name = response.event.service_name;
                        event.client_name = response.event.client_name;
                        event.status = response.event.status;
                        event.color = response.event.color;
                        calendar.fullCalendar('updateEvent', event);
                        calendar.fullCalendar('unselect');
                    }else{
                        calendar.fullCalendar('renderEvent', {
                            title: response.event.title,
                            start: start_new,
                            end: end_new,
                            id: response.event.id,
                            allDay: false,
                            description: response.event.description,
                            client: response.event.client,
                            service: response.event.service,
                            provider: response.event.provider,
                            resourceId: response.event.provider,
                            client_name: response.event.client_name,
                            service_name: response.event.service_name,
                            status: response.event.status,
                            color: response.event.color
                        }, false);
                        calendar.fullCalendar('unselect');
                    }

                    modal.modal('hide');
                    modal_body.find(".alerts").hide().find(".message").html("");
                }else{
                    modal_body.find(".alerts").show().find(".message").html(response.msg);

                }
            }
        });


    });


    $(".modal-body").find(".description_div").show().html("<textarea class='form-control description' name='description' rows='5'>"+description+"</textarea>");


}

function getServices(provider_id, services_div, services, service_id = false){
    services.html("");
    $.ajax({
        type: "POST",
        url: "/ajax/calendar/get_provider_services",
        data: "provider_id=" + provider_id,
        success: function (response) {
            if (response.status && response.services.length > 0) {
                services.append("<option value='0'>Избери услуга</option>");
                response.services.forEach(function (service) {
                    var selected = service_id && service_id == service.id ? "selected" : "";
                    services.append("<option value='" + service.id + "' data-time='" + service.time + "' data-price='" + service.cost + "' "+selected+" >" + service.name + "</option>");
                    if(selected != ""){
                        $(".info").html("<span class=\"badge badge-secondary\">Време: "+$(".services option:selected").data('time')+"минути</span><span class=\"badge badge-secondary\">Цена: "+$(".services option:selected").data('price')+"лв.</span>");
                    }
                });
                services_div.show();
            }
        }
    });
}


$(".close").on("click", function(){
    $(".eventView").modal("hide");
});



function convertMinsToHrsMins(mins) {
    let h = Math.floor(mins / 60);
    let m = mins % 60;
    h = h < 10 ? '0' + h : h;
    m = m < 10 ? '0' + m : m;
    return `${h}:${m}:00`;
}