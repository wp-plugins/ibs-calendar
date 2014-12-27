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
function CalendarObj($, args, mode) {
    this.init(args, mode)
}
(function ($) {
    CalendarObj.prototype.init = function (args, mode) {
        var cal = this;
        for (arg in args) {
            var data = args[arg];
            if (typeof data === 'string') {
                data = data.toLowerCase();
                if (data === 'yes' || data === 'no') {
                    args[arg] = data === 'yes' ? true : false;
                } else {
                    if (data === 'true' || data === 'false') {
                        args[arg] = data === 'true' ? true : false;
                    }
                }
            }
        }
        this.args = args;
        this.mode = mode;
        var id = args['id'];
        this.ibs_events;
        this.calendar = $('#fullcalendar-' + id);
        this.options = {
            'id': '1',
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
                    + event.color
                    + '; color:'
                    + event.textColor
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

                },
                show: {
                    event: 'mouseover'
                },
                hide: {
                    event: 'mouseout mouseleave'
                }
            };
        }
        this.fullcalendar_options = {
            'timezone': 'local',
            'height': null,
            'theme': true,
            'firstDay': '1',
            'weekends': true,
            'lang': 'en_us',
            'timeFormat': 'hh:mm a',
            'titleFormat': 'YYYY MMM DD',
            'dayNamesShort': ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
            'defaultView': 'month',
            'eventLimit': 6,
            'eventLimitClick': 'popover',
            'aspectRatio': 1.0,
            'editable': false,
            'hiddenDays': '',
            'fixedWeekCount': true,
            'weekNumbers': false,
            'defaultDate': moment()
        };
        for (arg in args) {
            if (typeof this.fullcalendar_options[arg] !== 'undefined' && args[arg] !== '') {
                this.fullcalendar_options[arg] = args[arg];
            }
        }
        this.fullcalendar_options.header = {
            left: args['headerLeft'],
            center: args['headerCenter'],
            right: args['headerRight']
        };
        this.fullcalendar_options.loading = function (bool) {
            if (bool && mode !== 'widget') {
                var position = $('#fullcalendar-' + cal.options['id']).position();
                var w = $('#fullcalendar-' + cal.options['id']).width();
                var h = $('#fullcalendar-' + cal.options['id']).height();
                $('#ibs-loading-' + cal.options['id']).css({'left': position.left, 'top': position.top, 'width': w, 'height': h}).show();
            } else {
                $('#ibs-loading-' + cal.options['id']).hide();
            }
        };
        this.fullcalendar_options.eventRender = function (event, element, view) {
            if (mode === 'widget' || args.hideTitle) {
                element.css('color', element.css('background-color'));
            }
            element.css('cursor', 'pointer');
            if (mode !== 'admin' || view.name === 'month') {
                element.qtip(cal.qtip_params(event));
            }
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
                if (mode !== 'widget') {
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
                                        .append($('<td>')
                                                .append($('<a>').attr({href: events[i].url}).text(events[i].title).addClass('calendar-list-title').css('padding', '3px')))
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
                                        .append($('<td>')
                                                .append($('<a>').attr({href: events[i].url}).text(events[i].title).addClass('calendar-list-title').css('padding', '3px')))
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
            this.calendar.fullCalendar(this.fullcalendar_options);
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
                    this.calendar.fullCalendar('addEventSource', event_source);
                }
            }
            if (args.ibsEvents) {
                this.calendar.fullCalendar('addEventSource',
                        {events: function (start, end, timezone, callback) {
                                var result = [];
                                for (var ex in cal.ibs_events) {
                                    var event = cal.ibs_events[ex];
                                    if (false == event.recurr) {
                                        if (moment(event.start).unix() >= start.unix() && moment(event.end).unix() <= end.unix()) {
                                            result.push(event);
                                        }
                                    } else {
                                        var exceptions = [];
                                        if (event.exceptions) {
                                            exceptions = event.exceptions.split(',');
                                            for (var i in exceptions) {
                                                exceptions[i] = moment(exceptions[i]).startOf('day');
                                            }
                                        }
                                        var rule = new RRule(RRule.parseString(event.repeat));
                                        var dates = rule.between(start.toDate(), end.toDate());
                                        for (i in dates) {
                                            dates[i] = moment(dates[i]).startOf('day');
                                        }
                                        var isException = function (index) {
                                            for (var i in exceptions) {
                                                if (exceptions[i].diff(dates[index]) === 0) {
                                                    return true;
                                                }
                                            }
                                            return false;
                                        }
                                        var duration = moment(event.end).diff(moment(event.start), 'seconds');
                                        var start_time = moment(event.start).unix() - moment(event.start).startOf('day').unix();

                                        for (var i in dates) {
                                            if (isException(i)) {
                                                continue;
                                            }
                                            var theDate = dates[i].startOf('day');
                                            var current = {
                                                start: theDate.add(start_time, 'seconds').format(),
                                                end: theDate.add(duration, 'seconds').format(),
                                                id: event.id,
                                                title: event.title,
                                                allDay: event.allDay,
                                                color: event.color,
                                                textColor: event.textColor,
                                                description: event.description,
                                                url: event.url,
                                                repeat: event.repeat,
                                                exceptions: event.exceptions
                                            }
                                            result.push(current);
                                        }
                                    }
                                    for (var i in result) {
                                        result[i].textColor = '#ffffff';
                                        result[i].editable = false;
                                    }

                                }
                                callback(result);
                            }
                        }
                );

            }
            if (args.event_list !== 'none') {
                if (args.event_list === 'show') {
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
        if (args.ibsEvents) {
            $.get(cal.options.ajaxUrl, {
                action: 'ibs_calendar_get_events',
                cache: false,
                dataType: 'json'
            }).then(
                    function (data) {
                        if (data !== "") {
                            data = decodeURIComponent(data);
                            cal.ibs_events = JSON.parse(data);
                            for (var i in cal.ibs_events) {
                                cal.ibs_events[i].editable = false;
                                cal.ibs_events[i].start = moment.unix(parseInt(cal.ibs_events[i].start)).format();
                                cal.ibs_events[i].end = moment.unix(parseInt(cal.ibs_events[i].end)).format();
                            }
                            console.log("IBS Events loaded.");
                        } else {
                            cal.ibs_events = [];
                        }

                        //----------------------------------------------------------
                        cal.renderCalendar();
                        //----------------------------------------------------------

                    },
                    function () {
                        console.log("Get IBS Events failed.");
                    });
        } else {
            cal.renderCalendar();
        }

    };
})(jQuery);