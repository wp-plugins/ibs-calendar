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
        this.args = args;
        this.mode = mode;
        this.db = null;
        this.db_id = 0;
        var id = args['id'];
        this.calendar = $('#fullcalendar-' + id);
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
            'defaultDate' : moment()
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
            if (mode === 'widget') {
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
        if (args.siteEvents && mode === 'admin') {
            this.fullcalendar_options.eventClick = $.proxy(function (event, jsEvent, view) {
                if (typeof event.repeat !== 'undefined') {
                    cal.editEvent(event);
                }
            }, this);
            this.fullcalendar_options.selectable = true;
            this.fullcalendar_options.selectHelper = true;
            this.fullcalendar_options.select = function (start, end, jsevent, view) {
                switch (view.name) {
                    case 'month':
                        break;
                    case 'basicWeek':
                        break;
                    case 'basicDay':
                        break;
                    case 'agendaWeek':
                    case 'agendaDay':
                        var event = cal.getEvent(0);
                        event.start = start;
                        event.end = end;
                        cal.editEvent(event);
                }
                cal.calendar.fullCalendar('unselect');
            };
            this.fullcalendar_options.eventResize = function (event, delta) {
                cal.putEvent(event, false);
            };
            this.fullcalendar_options.eventDrop = function (event, delta) {
                if (event.repeat && delta.days() !== 0) {
                    cal.calendar.fullCalendar('refetchEvents');
                } else {
                    cal.putEvent(event);
                }
            };
            this.fullcalendar_options.dayClick = function (date, jsEvent, view) {
                if (view.name === 'month') {
                    cal.calendar.fullCalendar('changeView', 'agendaDay');
                    cal.calendar.fullCalendar('gotoDate', date);
                }
            };
        }
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
            if (args.siteEvents) {
                this.calendar.fullCalendar('addEventSource',
                        {events: function (start, end, timezone, callback) {
                                var result = [];
                                for (var ex in cal.db.Events) {
                                    var event = cal.db.Events[ex];
                                    if (event.repeat === null) {
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
                                        for (var i in dates) {
                                            if (isException(i)) {
                                                continue;
                                            }
                                            var current = cal.getEvent(0);
                                            current.start = moment((dates[i].unix() + (moment(event.start).unix() - moment(event.start).startOf('day').unix())) * 1000).format();
                                            current.end = moment((dates[i].unix() + (moment(event.end).unix() - moment(event.end).startOf('day').unix())) * 1000).format();
                                            current.id = event.id;
                                            current.title = event.title;
                                            current.allDay = event.allDay;
                                            current.color = event.color;
                                            current.textColor = event.textColor;
                                            current.description = event.description;
                                            current.location = event.location;
                                            current.repeat = event.repeat;
                                            current.exceptions = event.exceptions;
                                            result.push(current);
                                        }
                                    }
                                }
                                for (var i in result) {
                                    result[i].textColor = '#ffffff';
                                    result[i].editable = mode === 'admin';
                                }
                                callback(result);
                            }
                        });
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
        if (args.siteEvents) {
            if (mode === 'admin' || mode === 'events') {
                this.eventHTML();
            }
            this.getDB();
        } else {
            this.renderCalendar();
        }
    };
    CalendarObj.prototype.getDB = function () {
        var cal = this;
        if (this.db) {
            return;
        }
        $.get(this.options.ajaxUrl, {
            action: 'ibs_calendar_get_db',
            cache: false,
            dataType: 'json'
        }).then(
                function (data) {
                    if (data !== "") {
                        data = decodeURIComponent(data);
                        cal.db = JSON.parse(data);
                        cal.db_id = 0;
                        for (var i in cal.db.Events) {
                            cal.db.Events[i].id = ++cal.db_id;
                            if (cal.db.Events[i].repeat === '') {
                                cal.db.Events[i].repeat = null;
                            }
                        }
                        this.db_id = i;
                        console.log("Database loaded.");
                    } else {
                        cal.db = {Events: []};
                        this.db_id = 0;
                    }
                    //----------------------------------------------------------
                    if (cal.mode === 'events') {
                        cal.showEvents();
                    } else {
                        cal.renderCalendar();
                    }
                    //----------------------------------------------------------
                },
                function () {
                    console.log("Get Database failed.");
                    if (cal.mode === 'events') {
                        cal.showEvents();
                    } else {
                        cal.renderCalendar();
                    }
                });
    };
    CalendarObj.prototype.getEvent = function (id) {
        if (id) {
            for (var i in this.db.Events) {
                if (this.db.Events[i].id === id) {
                    return this.db.Events[i];
                }
            }
            return null;
        } else {
            return {
                id: 0,
                title: '',
                allDay: 0,
                start: moment().format(),
                end: moment().add(15, 'minute').format(),
                color: '',
                textColor: '',
                description: '',
                location: '',
                repeat: null,
                exceptions: null
            };
        }
    };
    CalendarObj.prototype.closeDB = function () {
        if (this.db) {
            this.putDB();
            delete this.db;
            this.db = null;
        }
    };
    CalendarObj.prototype.putEvent = function (event) {
        if (event.repeat && event.repeat === '') {
            event.repeat === null;
        }
        if (event.description && event.repeat === null) {
            event.description = null;
        }
        if (event.id === 0) {
            if (typeof event.start === 'object') {
                event.start = event.start.format();
            }
            if (typeof event.end === 'object') {
                event.end = event.end.format();
            }
            event.id = ++this.db_id;
            this.db.Events.push(event);
            if (this.mode === 'admin') {
                if (event.repeat || event.wasRepeat) {
                    this.calendar.fullCalendar('refetchEvents');
                } else {
                    this.calendar.fullCalendar('renderEvent', event);
                }
            } else {
                if (this.mode === 'events')
                    this.showEvents();
            }
        } else {
            var current = this.getEvent(event.id);
            current.title = event.title;
            current.allDay = event.allDay;
            current.start = event.start.format();
            try {
                current.end = event.end.format();
            } catch (e) {
                current.end = event.start.endOf('day');
            }
            current.color = event.color;
            current.textColor = event.textColor;
            current.description = event.description;
            current.location = event.location;
            current.repeat = event.repeat;
            current.exceptions = event.exceptions;
            if (this.mode === 'admin') {
                if (event.repeat) {
                    this.calendar.fullCalendar('refetchEvents');
                } else {
                    this.calendar.fullCalendar('updateEvent', event);
                }
            } else {
                if (this.mode === 'events') {
                    this.showEvents();
                }
            }
        }
        this.putDB();
    };
    CalendarObj.prototype.deleteEvent = function (event) {
        if (event.repeat) {
            var exceptions = [];
            if (event.exceptions) {
                exceptions = event.exceptions.split(',');
            }
            exceptions.push(event.start.format());
            event.exceptions = exceptions.toString();
            this.putEvent(event);
        } else {
            for (var i in this.db.Events) {
                if (this.db.Events[i].id === event.id) {
                    this.db.Events.splice(i, 1);
                    this.putDB();
                    break;
                }
            }
        }
        if (this.mode === 'admin') {
            this.calendar.fullCalendar('refetchEvents');
        } else {
            if (this.mode === 'events') {
                this.showEvents();
            }
        }
    };
    CalendarObj.prototype.putDB = function () {
        if (this.db) {
            var data = [];
            for (var i in this.db.Events) {
                if (this.db.Events[i].id > 0) {
                    data.push(this.db.Events[i]);
                }
            }
            ;
            var payload = JSON.stringify({Events: data});
            $.ajax({
                type: "POST",
                url: this.options.ajaxUrl,
                data: {'action': 'ibs_calendar_put_db', 'data': payload},
                dataType: "json",
                cache: false
            }).then(
                    function (data) {
                        console.log('db put completed');
                    },
                    function () {
                        console.log('db put failed');
                    }
            );
        }
    };
})(jQuery);