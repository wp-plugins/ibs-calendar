(function ($) {
    $.datepicker.parseDate = function (format, value) {
        return moment(value, format).toDate();
    };
    $.datepicker.formatDate = function (format, value) {
        return moment(value).format(format);
    };
    CalendarObj.prototype.editEvent = function (event) {
        var cal = this;
        $('#ibs-event-title').val(event.title);
        $('#ibs-event-allday').prop('checked', event.allDay);
        $('.color-box').each(function (index, item) {
            if (rgb2hex($(item).css('background-color')) === event.color) {
                $(item).trigger('click');
            }
        });
        $('#ibs-event-color').val(event.color);
        $('#ibs-event-description').val(event.description);
        $('#ibs-event-location').val(event.location);
        $('.repeat-option').addClass('repeat-not-active').find('input').prop('disabled', true);
        if (event.repeat) {
            $('#ibs-event-repeat').val(event.repeat);
            $('#ibs-event-recurr').attr('checked', true)
        } else {
            $('#ibs-event-repeat').val('');
            $('#ibs-event-recurr').attr('checked', false)
        }
        var sstr, estr, dstart, dend;
        if(typeof event.start === 'string'){
            sstr = event.start;
            event.start = moment(sstr);
        }else{
            sstr = event.start.format();
        }
        if(typeof event.end === 'string'){
            estr = event.end;
            event.end = moment(estr);
        }else{
            estr = event.end.format();
        }
        if (event.allDay || event.end === null) {
            dstart = moment(sstr).startOf('day').toDate();
            dend = moment(sstr).endOf('day').toDate();
        } else {
            dstart = moment(sstr).toDate();
            dend = moment(estr).toDate();
        }
        $('#ibs-event-start-date').datepicker('setDate', dstart);
        $('#ibs-event-start-time').timepicker('setTime', dstart);
        $('#ibs-event-end-date').datepicker('setDate', dend);
        $('#ibs-event-end-time').timepicker('setTime', dend);
        $('#ibs-event-exception-div').empty()
        $('.event-allday').prop('disabled', event.allDay);
        var options = {
            autoOpen: true,
            height: 600,
            width: 600,
            modal: true,
            buttons: {
                'Update': function () {
                    event.wasRepeat = event.repeat ? true : false;
                    event.title = $('#ibs-event-title').val();
                    event.color = rgb2hex($('.color-box-selected').css('background-color'));
                    event.description = $('#ibs-event-description').val();
                    event.location = $('#ibs-event-location').val();
                    event.repeat = $('#ibs-event-repeat').val();
                    if (event.exceptions) {
                        var excepts = [];
                        $('input[name=repeat-exceptions]:checked').each(function (index, item) {
                            excepts.push($(item).val());
                        });
                        event.exceptions = excepts.toString();
                    }
                    var _allDay = $('#ibs-event-allday').is(':checked');
                    var _both = event.allDay ? _allDay : false;
                    if (_both === false) {
                        var sdate = moment($('#ibs-event-start-date').datepicker('getDate')).startOf('day');
                        var stime = $('#ibs-event-start-time').timepicker('getSecondsFromMidnight');
                        var edate = moment($('#ibs-event-end-date').datepicker('getDate')).startOf('day');
                        var etime = $('#ibs-event-end-time').timepicker('getSecondsFromMidnight');
                        if (edate.diff(sdate) < 0) {
                            alert('end date cannot be before start date.')
                            return;
                        }

                        if (sdate.diff(edate) === 0 && etime <= stime) {
                            alert('end time cannot be before start time.')
                            return;
                        }
                        if (_allDay) {
                            event.start = sdate.startOf('day');
                            event.end = edate.endOf('day');
                        } else {
                            event.start = sdate.add(stime, 'seconds');
                            event.end = edate.add(etime, 'seconds');
                        }
                        event.allDay = _allDay ? 1 : 0;
                    }
                    cal.putEvent(event);
                    $(this).dialog('close');
                }, 'Delete': function () {
                    if (parseInt() !== 0) {
                        if (confirm('Delete this event?')) {
                            cal.deleteEvent(event);
                        }
                    }
                    $(this).dialog('close');
                }, Cancel: function () {
                    $(this).dialog('close');
                }
            }, open: function () {
                $("#event-title").focus();
            }, close: function () {
                $(this).dialog('destroy');
            }
        };
        if (event.repeat) {
            options.buttons = {'Update': options.buttons['Update'],
                'Exception': function () {
                    if (confirm('Make  [' + $('#ibs-event-start-date').val() + '] an exception to the repeat rule?')) {
                        cal.deleteEvent(event);
                        $(this).dialog('close');
                    }
                },
                'Cancel': options.buttons['Cancel']
            };
            if (event.exceptions) {
                var excepts = event.exceptions.split(',').sort();
                var str = '';
                for (var i in excepts) {
                    var dstr = moment(excepts[i]).format('ddd MMM DD, YYYY');
                    str += '<tr><td><input type="checkbox" name="repeat-exceptions" class="repeat-exceptions" value="' + excepts[i] + '" checked/><td>' + dstr + '</td></tr>\n>';
                }
                $('#ibs-event-exception-div')
                        .append($('<div>Exceptions - uncheck to remove an exception on update</div>'))
                        .append($('<table>')
                                .append($('<tbody>')
                                        .append(str)));
            }
        }
        $('#event-dialog').dialog(options);
    };
    CalendarObj.prototype.eventHTML = function () {
        var cal = this;
        var fd = parseInt(this.fullcalendar_options.firstDay);
        $('#repeat-wkst').val(fd - 1 === -1 ? 6 : fd - 1);
        var getFormValues = function () {
            var paramObj = {};
            paramObj.freq = $('input[name=freq]:checked').val();
            paramObj.interval = $("#repeat-interval").val();
            var work = [];
            $('input[name=byweekday]').each(function (index, item) {
                if ($(this).is(':checked')) {
                    work.push($(this).val());
                }
            });
            paramObj.byweekday = work; //.toString();
            paramObj.dtstart = $("#repeat-dtstart").val();
            paramObj.count = $("#repeat-count").val();
            paramObj.until = $("#repeat-until").val();
            paramObj.wkst = $('#repeat-wkst').val();
            work = [];
            $('input[name=bymonth]').each(function (index, item) {
                if ($(this).is(':checked')) {
                    work.push($(this).val());
                }
            });
            paramObj.bymonth = work; //.toString();
            paramObj.bysetpos = $("#repeat-bysetpos").val();
            paramObj.bymonthday = $("#repeat-bymonthday").val();
            paramObj.byyearday = $("#repeat-byyearday").val();
            paramObj.byweekno = $("#repeat-byweekno").val();
            paramObj.byhour = $("#repeat-byhour").val();
            paramObj.byminute = $("#repeat-byminute").val();
            paramObj.bysecond = $("#repeat-bysecond").val();
            paramObj.easter = '';
            return paramObj;
        }
        var processChange = function () {
            if ($('#ibs-event-recurr').is(':checked')) {
                var date, days, getDay, makeRule, options, rfc, rule, v, value, values;
                values = getFormValues();
                delete values['radio_ends'];
                options = {};
                days = [RRule.MO, RRule.TU, RRule.WE, RRule.TH, RRule.FR, RRule.SA, RRule.SU];
                getDay = function (i) {
                    return days[i];
                };
                for (key in values) {
                    value = values[key];
                    if (!value) {
                        continue;
                    } else if (key === 'dtstart' || key === 'until') {
                        date = new Date(Date.parse(value));
                        value = new Date(date.getTime() + (date.getTimezoneOffset() * 60 * 1000));
                    } else if (key === 'byweekday') {
                        if (value instanceof Array) {
                            value = value.map(getDay);
                        } else {
                            value = getDay(value);
                        }
                    } else if (/^by/.test(key)) {
                        if (false === value instanceof Array) {
                            value = value.split(/[,\s]+/);
                        }
                        value = (function () {
                            var _i, _len, _results;
                            _results = [];
                            for (_i = 0, _len = value.length; _i < _len; _i++) {
                                v = value[_i];
                                if (v) {
                                    _results.push(v);
                                }
                            }
                            return _results;
                        })();
                        value = value.map(function (n) {
                            return parseInt(n, 10);
                        });
                    } else {
                        value = parseInt(value, 10);
                    }
                    if (key === 'wkst') {
                        value = getDay(value);
                    }
                    if (key === 'interval' && (value === 1 || !value)) {
                        continue;
                    }
                    options[key] = value;
                }
                makeRule = function () {
                    return new RRule(options);
                };
                try {
                    rule = makeRule();
                } catch (e) {
                    console.log(e)
                    return;
                }
                rfc = rule.toString();
                $("#ibs-event-repeat").val(rfc);
                // $("#repeat-summary").val(rfc);
            }
            return '';
        };
        $('#repeat-options').on('change', 'input', function () {
            var a = $('input[name=interval]').val() > 1 ? 's' : '';
            switch ($('input[name=freq]:checked').val()) {
                case '3' :
                    $('#repeat-interval-type').text('day' + a);
                    break;
                case '2' :
                    $('#repeat-interval-type').text('week' + a);
                    break;
                case '1' :
                    $('#repeat-interval-type').text('month' + a);
                    break;
                case '0' :
                    $('#repeat-interval-type').text('year' + a);
                    break;
            }
            processChange();
        });
        $('input[name=radio_ends]').click(function (event) {
            switch ($(this).val()) {
                case 'never':
                    $('input[name=until]').val('').attr('disabled', true);
                    $('input[name=count]').val('').attr('disabled', true);
                    break;
                case 'until' :
                    $('input[name=until]').val('').attr('disabled', false);
                    $('input[name=count]').val('').attr('disabled', true);
                    $('#repeat-until').datepicker('setDate', moment().toDate());
                    break;
                case 'count':
                    $('input[name=until]').val('').attr('disabled', true);
                    $('input[name=count]').val('30').attr('disabled', false);
                    break;
            }
            processChange(this, 'options');
        });
        $('#repeat-advanced').click(function (event) {
            $(this).is(':checked') ? $('.repeat-advanced').show() : $('.repeat-advanced').hide();
        });
        $("#ibs-event-recurr").click(function (event) {
            if ($(this).is(':checked')) {
                $('.repeat-option').removeClass('repeat-not-active').find('input').prop('disabled', false);
                $('#repeat-dtstart').datepicker('setDate', moment().toDate());
                $('#repeat-until').prop('disabled', true);
                processChange();
            } else {
                $('#ibs-event-repeat').val('');
                $('#ibs-event-exceptions').val('');
                $('.repeat-option').addClass('repeat-not-active').find('input').prop('disabled', true);
            }
        });
        var dp_options =
                {
                    dateFormat: this.fullcalendar_options.titleFormat,
                    onChange: processChange()
                }
        $('#repeat-until').datepicker(dp_options);
        $('#repeat-dtstart').datepicker(dp_options);
        $("#repeat-advanced").click(function (event) {
            $(this).is(':checked') ? $('.repeat-advanced').show() : $('.repeat-advanced').hide();
        });
        $('.color-box').click(function () {
            $('.color-box').removeClass('color-box-selected');
            $(this).addClass('color-box-selected');
        });

        $('.ibs-datepicker').datepicker(
                {
                    dateFormat: this.fullcalendar_options.titleFormat
                });

        $('.ibs-timepicker').timepicker(
                {
                    timeFormat: this.fullcalendar_options.timeFormat,
                    step: 15
                });

        $('#ibs-events-position').val(0);
        $('.ibs-events-paging').click(function (event) {
            var pos = parseInt($('#ibs-events-position').val());
            var limit = parseInt($('#ibs-events-limit').val());
            if (limit < cal.db.Events.length) {
                switch ($.trim($(this).text())) {
                    case '<<' :
                        pos = 0;
                        break;
                    case '<' :
                        if (pos - limit < 0) {
                            pos = 0;
                        } else {
                            pos -= limit;
                        }
                        break;
                    case '>' :
                        if (pos + limit > cal.db.Events.length - limit) {
                            pos = cal.db.Events.length - limit;
                        } else {
                            pos += limit;
                        }
                        break;
                    case '>>' :
                        if (pos + limit <= cal.db.Events.length - limit) {
                            pos = cal.db.Events.length - limit;
                        }
                }
                $('#ibs-events-position').val(pos);
            }
            cal.showEvents(pos, limit);
        });
        $('#ibs-events-toggle').click(function () {
            switch ($(this).text()) {
                case 'All':
                    $('.ibs-events-select').attr('checked', true);
                    $(this).text('None');
                    $('#ibs-events-remove').attr('disabled', false);
                    break;
                case 'None':
                    $('.ibs-events-select').attr('checked', false);
                    $(this).text('All');
                    $('#ibs-events-remove').attr('disabled', true);
                    break;
            }
        });

        $('#ibs-events-remove').click(function () {
            $('#ibs-events-remove').attr('disabled', true);
            if (confirm('Delete selected events?')) {
                $('.ibs-events-select').each(function (index, item) {
                    if ($(item).is(':checked')) {
                        for (var i in cal.db.Events) {
                            if (cal.db.Events[i].id === parseInt($(item).val())) {
                                cal.db.Events.splice(i, 1);
                                break;
                            }
                        }
                    }
                });
                cal.putDB();

            }
            cal.showEvents(0);
        });
        $('#ibs-events-add').click(function () {
            var event = cal.getEvent(0);
            cal.editEvent(event);
        });
        $('#ibs-event-allday').click(function () {
            if ($(this).is(':checked')) {
                $('.event-allday').attr('disabled', true);
            } else {
                $('.event-allday').attr('disabled', false);
            }
        });
    };
    CalendarObj.prototype.showEvents = function (pos, limit) {
        var cal = this;
        var cal_format = this.fullcalendar_options.titleFormat + ' - ' + this.fullcalendar_options.timeFormat;
        if (typeof pos === 'undefined') {
            pos = parseInt($('#ibs-events-position').val());
        }
        if (typeof limit === 'undefined') {
            limit = parseInt($('#ibs-events-limit').val());
        }
        function sort_events(a, b) {
            return  ((a.start < b.start) ? -1 : ((a.start > b.start) ? 1 : 0));
        }
        var events = this.db.Events.sort(sort_events);
        $('.ibs-events-data-row').remove();
        var stop = this.db.Events.length <= limit ? this.db.Events.length : limit;

        for (var i = pos; i < pos + stop; i++) {
            var start = moment(events[i].start);
            var end = moment(events[i].end);
            var dur = moment.duration(end.diff(start)).humanize();
            if (events[i].allDay) {
                dur = 'all day';
            }
            if (events[i].repeat) {
                dur = dur + ' >>';
            }
            $('#ibs-events-table')
                    .append($('<tr class="ibs-events-data-row">')
                            .append($('<td class="ibs-events-td">').text(i + 1).css('text-align', 'right'))
                            .append($('<td class="ibs-events-td event-center"><input class="ibs-events-select" type="checkbox" value="' + events[i].id + '" />'))
                            .append($('<td class="ibs-events-td">').text(events[i].title).css({'background-color': events[i].color, 'color' : events[i].textColor}))
                            .append($('<td class="ibs-events-td">').text(start.format(cal_format)))
                            .append($('<td class="ibs-events-td">').text(dur))
                            .append($('<td class="ibs-events-td">').text(events[i].location))
                            .append($('<td class="ibs-events-td">').text(events[i].description.substring(0, 50)))
                            .append($('<td class="ibs-events-td event-center">').html('<button href="#" class="event-edit" rel="' + events[i].id + '" title="edit event"><strong> ~ </strong></button>'))
                            );
        }
        $('.event-edit').on('click', '', {}, function (event) {
            var target = cal.getEvent(parseInt($(this).attr('rel')));
            target.start = moment(target.start);
            target.end = moment(target.end);
            cal.editEvent(target);
        });

    }
})(jQuery);