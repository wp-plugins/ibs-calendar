/*
 Author URI: http://indianbendsolutions.com
 License: GPL
 
 GPL License: http://www.opensource.org/licenses/gpl-license.php
 
 This program is distributed in the hope that it will be useful, but
 WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */
function ibs_util_19() {
    return 'AIzaSyDU0aiNYlY1sRHPuZadvnfAkIRMhEFobP4';
}
function CalendarObj($, args) {
    var cal = this;
    var id = args['id'];
    this.options = {
        'id': '1',
        'debug': 'no',
        'ui_theme_css': 'ui-lightness.css',
        'google': true,
        'feeds': {},
        'ajaxUrl': null,
        'ajaxData': null,
    };
    for (var arg in args) {
        if (typeof this.options[arg] !== 'undefined') {
            this.options[arg] = args[arg];
        }
    }
    this.qtip_params = function (event) {
        var fmt = cal.fullcalendar_options.timeFormat;
        var bg = '<p style="background-color:'
                + event.source.backgroundColor
                + '; color:'
                + event.source.textColor
                + ';" >';
        bg = '<p style="background-color:silver; color: black;" >';
        var loc = '';
        if (typeof event.location !== 'undefined') {
            loc = '<p>' + 'Location: ' + event.location + '</p>';
        }
        var desc = '';
        if (typeof event.description !== 'undefined') {
            desc = '<p>' + event.description + '</p>'
        }
        return {
            content: {'text': '<p>' + event.title + '</p>' + loc + desc + '<p>' + moment(event.start).format("ddd MMM DD " + fmt) + moment(event.end).format(' - ' + fmt) + '</p>'},
            position: {
                my: 'bottom center',
                at: 'top center'
            },
            style: {
                classes: args['qtip']['style'] + ' ' + args['qtip']['rounded'] + args['qtip']['shadow']

            }
        };
    }
    this.fullcalendar_options = {
        'height': null,
        'theme': true,
        'firstDay': '1',
        'weekends': true,
        'lang': 'en_us',
        'timeFormat': 'hh:mm a',
        'titleFormat': 'MMM YYYY',
        'dayNamesShort': ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
        'defaultView': 'month',
        'eventLimit': 6,
        'eventLimitClick': 'popover',
        'aspectRatio': 1.0,
        'editable': false
    };
    for (arg in args) {
        if (typeof this.fullcalendar_options[arg] !== 'undefined' && args[arg] !== '') {
            if (args[arg] === 'yes' || args[arg] === 'no') {
                this.fullcalendar_options[arg] = args[arg] === 'yes' ? true : false;
            } else {
                this.fullcalendar_options[arg] = args[arg];
            }
        }
    }
    this.fullcalendar_options.header = {
        left: args['headerLeft'],
        center: args['headerCenter'],
        right: args['headerRight']
    };
    this.fullcalendar_options.eventSources = [{
            url: this.options.ajaxUrl,
            data: this.options.ajaxData,
            ignoreTimezone: true,
            allDayDefault: false
        }];
    this.fullcalendar_options.loading = function (bool) {
        if (bool && typeof args['widget'] === 'undefined') {
            var position = $('#fullcalendar-' + cal.options['id']).position();
            var w = $('#fullcalendar-' + cal.options['id']).width();
            var h = $('#fullcalendar-' + cal.options['id']).height();
            $('#ibs-loading-' + cal.options['id']).css({'left': position.left, 'top': position.top, 'width': w, 'height': h}).show();
        } else {
            $('#ibs-loading-' + cal.options['id']).hide();
        }
    };
    this.fullcalendar_options.eventRender = function (event, element, view) {
        /* event object
         id	
         title	
         allDay	
         start	
         end	
         url	
         className	
         editable	
         startEditable	
         durationEditable	
         source	
         color	
         backgroundColor	
         borderColor	
         textColor	
         */
        /*
         element the event display container div
         */
        if(typeof args['widget'] !== 'undefined'){
            element.css('color', element.css('background-color'));
            
        }
        element.qtip(cal.qtip_params(event));
    };
    this.fullcalendar_options.eventAfterAllRender = function (view) {
        if (args.event_list !== 'none' && $('#list-display-' + id).is(':checked')) {

            var event_list = '#event-list-' + id;
            var event_table = '#event-table-' + id;
            var fullcalendar = "#fullcalendar-" + id;
            var events = $(fullcalendar).fullCalendar('clientEvents');
            events.sort(function (a, b) {
                return moment(a.start) - moment(b.start);
            });
            $(event_table).empty();
            if (typeof args.widget === 'undefined') {
                $(event_table).css({'border': '1px solid silver'})
                        .append($('<tbody>')
                                .append($('<tr>').addClass('ui-widget-header')
                                        .append($('<th>').text('Day').css('padding', '3px'))
                                        .append($('<th>').text('Time').css('padding', '3px'))
                                        .append($('<th>').text('Event').css('padding', '3px'))
                                        .append($('<th>').text('Location').css('padding', '3px'))
                                        ));
                for (var i = 0; i < events.length; i++) {
                    var pattern = 'ddd Do';
                    var past = moment() > moment(events[i].start) ? 'line-through' : 'none';
                    var d = moment(events[i].start).format(pattern);
                    var t = moment(events[i].start).format(cal.fullcalendar_options.timeFormat);
                    $(event_table).find('tbody')
                            .append($('<tr>').qtip(cal.qtip_params(events[i]))
                                    .css({'text-decoration': past})
                                    .append($('<td>').text(d).css('padding', '3px'))
                                    .append($('<td>').text(t).css('padding', '3px'))
                                    .append($('<td>').text(events[i].title).addClass('calendar-list-title').css('padding', '3px'))
                                    .append($('<td>').text(events[i].location).css('padding', '3px'))
                                    );
                }
            } else {
                $(event_table).css({'border': '1px solid silver'})
                        .append($('<tbody>')
                                .append($('<tr>').addClass('ui-widget-header')
                                        .append($('<th>').text('Events').css('padding', '3px'))
                                        ));
                for (var i = 0; i < events.length; i++) {
                    var past = moment() > moment(events[i].start) ? 'line-through' : 'none';
                    $(event_table).find('tbody')
                            .append($('<tr>').qtip(cal.qtip_params(events[i]))
                                    .css({'text-decoration': past})
                                    .append($('<td>').text(events[i].title).addClass('calendar-list-title').css('padding', '3px'))
                                    );
                }

            }
        }
    };
    this.renderCalendar = function () {
        if (args.event_list === 'none') {
            $('#list-display-' + id).parent().css('display', 'none');
        } else {
            $('#list-display-' + id).prop('checked', args.event_list === 'show');
        }
        $('#fullcalendar-' + id).fullCalendar(this.fullcalendar_options);
        for (var feed in this.options.feeds) {
            if (this.options.feeds[feed].url !== '' && this.options.feeds[feed].enabled === 'yes') {
                var event_source = {
                    'googleCalendarApiKey': function () {
                        if (typeof cal.options.feeds[feed]['key'] === 'string' && cal.options.feeds[feed]['key'] !== '') {
                            return cal.options.feeds[feed]['key'];
                        } else {
                            return ibs_util_19();
                        }
                    },
                    'textColor': this.options.feeds[feed]['textColor'],
                    'backgroundColor': this.options.feeds[feed]['backgroundColor'],
                    'url': this.options.feeds[feed]['url']
                };
                $('#fullcalendar-' + id).fullCalendar('addEventSource', event_source);
            }
        }
        if (args.event_list !== 'none') {
            if(args.event_list === 'show'){
                $('#event-list-' + id).show();
            }
            $('#list-display-' + id).click(function (event) {
                if ($('#list-display-' + id).is(':checked')) {
                    $('#fullcalendar-' + id).fullCalendar('rerenderEvents');
                    $('#event-list-' + id).show();
                } else {
                    $('#event-list-' + id).hide();
                }
            });
        }
    };
    try {
        this.renderCalendar();
    } catch (e) {

    }
}   