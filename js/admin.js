jQuery(document).ready(function ($) {
    $('.ibs-colorpicker').colorpicker();
    $('.ibs-colorpicker').parent().css({'width': 'auto', 'float': 'left', 'margin_left': '5px', 'margin-right': '5px'});
    $('.ibs-colorpicker').on('change.color', '', {}, function (event) {
        var selector = $(this).attr('feed');
        var css = $(this).attr('css');
        var value = $(this).val();
        $(selector).css(css, value);
    });
    $('.ibs-colorpicker').each(function (index) {
        $(this).trigger('change.color');
    });
    $('#ibs-event-limit').on('change', '', {}, function (event) {
        var value = $(this).val().toLowerCase();
        switch (value) {
            case 'yes' :
                break;
            case 'no' :
                break;
            default :
                try {
                    value = parseInt(value);
                    if (isNaN(value)) {
                        value = 'no';
                    } else {
                        if (value > 20) {
                            value = 20;
                        }
                    }
                } catch (err) {
                    value = 'no';
                }
        }
        $(this).val(value);
    });
    $('.qtip-table').on('change', 'input', {}, function (event) {
        qtip_handler();
    });
    function qtip_handler() {
        var b = $("input[name='ibs_calendar_options[qtip][style]']:checked").val();
        var c = $('#qtip-rounded').is(':checked') ? 'qtip-rounded' : '';
        var d = $('#qtip-shadow').is(':checked') ? 'qtip-shadow' : '';
        var styles = $.trim(b + ' ' + c + ' ' + d.replace('  ', ' '));
        $('#test-qtip').val(styles);
        $('#test-qtip').qtip({
            content: {'text': '<p> Current qtip classes test</p><p>' + styles + '</p>'},
            position: {
                my: 'bottom center',
                at: 'top center'
            },
            show: {ready: true
            },
            style: {
                classes: styles
            }
        });
    }
    ;
    qtip_handler();
    $('#shortcode-options').on('change', '', {}, function (event) {
        var sc = '[ibs-calendar ';
        var af = [];
        $('#shortcode-options').find('input').each(function (index, item) {
            if ($(item).attr('type') === 'checkbox') {
                if ($(item).is(':checked')) {
                    if ($(item).attr('name') === 'availableFeeds') {
                        af.push($(item).val());
                    } else {
                        sc += ' ' + $(item).attr('name') + '=' + '"true"';
                    }
                }
            } else {
                if ($(item).attr('type') === 'text' || $(item).attr('type') === 'number') {
                    if ($(item).val() !== '') {
                        sc += ' ' + $(item).attr('name') + '="' + $(item).val() + '"';
                    }
                }
            }

        });
        if (af.length) {
            sc += ' feeds="' + af.toString() + '" ';
        }
        $('#shortcode-options').find('select').each(function (index, item) {
            if ($(item).val() !== '') {
                sc += ' ' + $(item).attr('name') + '="' + $(item).val() + '"';
            }

        });
        sc += ']';
        $('#shortcode-result').val(sc);
    });
});
jQuery(document).ready(function ($) {
    $(document).find('body')
            .append(
                    '<div><div id="dropdown-header-left" class="dropdown dropdown-tip">'
                    + '<ul class="dropdown-menu dropdown-list">'
                    + '<li class="dropdown-divider"></li>'
                    + '<li><label><input class="header-button-item" value="prevYear" type="checkbox" /> Prev Year</label></li>'
                    + '<li><label><input class="header-button-item" value="prev" type="checkbox" /> Prev</label></li>'
                    + '<li><label><input class="header-button-item" value="next" type="checkbox" /> Next</label></li>'
                    + '<li><label><input class="header-button-item" value="nextYear" type="checkbox" /> Next Year</label></li>'
                    + '<li><label><input class="header-button-item" value="today" type="checkbox" /> Today</label></li>'
                    + '<li class="dropdown-divider"></li>'
                    + '<li><label><input class="header-button-item" value="month" type="checkbox" /> Month</label></li>'
                    + '<li><label><input class="header-button-item" value="basicWeek" type="checkbox" /> Week(basic)</label></li>'
                    + '<li><label><input class="header-button-item" value="basicDay" type="checkbox" /> Day(basic)</label></li>'
                    + '<li><label><input class="header-button-item" value="agendaWeek" type="checkbox" /> Week(agenda)</label></li>'
                    + '<li><label><input class="header-button-item" value="agendaDay" type="checkbox" /> Day(agenda)</label></li>'
                    + '<li><label><input class="header-button-item" value="title" type="checkbox" /> Title</label></li>'
                    + '<li class="dropdown-divider"></li>'
                    + '<li><a class="dropdown-update" rel="#dropdown-header-center" href="#">Update</label></li>'
                    + '<li class="dropdown-divider"></li>'
                    + '</ul>'
                    + '</div>'

                    + '<div id="dropdown-header-right" class="dropdown dropdown-tip">'
                    + '<ul class="dropdown-menu dropdown-list">'
                    + '<li class="dropdown-divider"></li>'
                    + '<li><label><input class="header-button-item" value="month" type="checkbox" /> Month</label></li>'
                    + '<li><label><input class="header-button-item" value="basicWeek" type="checkbox" /> Week(basic)</label></li>'
                    + '<li><label><input class="header-button-item" value="agendaWeek" type="checkbox" /> Week(agenda)</label></li>'
                    + '<li><label><input class="header-button-item" value="basicDay" type="checkbox" /> Day(basic)</label></li>'
                    + '<li><label><input class="header-button-item" value="agendaDay" type="checkbox" /> Day(agenda)</label></li>'
                    + '<li class="dropdown-divider"></li>'
                    + '<li><label><input class="header-button-item" value="title" type="checkbox" /> Title</label></li>'
                    + '<li><label><input class="header-button-item" value="prevYear" type="checkbox" /> Prev Year</label></li>'
                    + '<li><label><input class="header-button-item" value="prev" type="checkbox" /> Prev</label></li>'
                    + '<li><label><input class="header-button-item" value="next" type="checkbox" /> Next</label></li>'
                    + '<li><label><input class="header-button-item" value="nextYear" type="checkbox" /> Next Year</label></li>'
                    + '<li><label><input class="header-button-item" value="today" type="checkbox" /> Today</label></li>'
                    + '<li class="dropdown-divider"></li>'
                    + '<li><a class="dropdown-update" rel="#dropdown-header-center" href="#">Update</label></li>'
                    + '<li class="dropdown-divider"></li>'
                    + '</ul>'
                    + '</div>'

                    + '<div id="dropdown-header-center" class="dropdown dropdown-tip">'
                    + '<ul class="dropdown-menu dropdown-list">'
                    + '<li class="dropdown-divider"></li>'
                    + '<li><label><input class="header-button-item" value="title" type="checkbox" /> Title</label></li>'
                    + '<li class="dropdown-divider"></li>'
                    + '<li><label><input class="header-button-item" value="month" type="checkbox" /> Month</label></li>'
                    + '<li><label><input class="header-button-item" value="basicWeek" type="checkbox" /> Week(basic)</label></li>'
                    + '<li><label><input class="header-button-item" value="basicDay" type="checkbox" /> Day(basic)</label></li>'
                    + '<li><label><input class="header-button-item" value="agendaWeek" type="checkbox" /> Week(agenda)</label></li>'
                    + '<li><label><input class="header-button-item" value="agendaDay" type="checkbox" /> Day(agenda)</label></li>'
                    + '<li><label><input class="header-button-item" value="title" type="checkbox" /> Title</label></li>'
                    + '<li><label><input class="header-button-item" value="prevYear" type="checkbox" /> Prev Year</label></li>'
                    + '<li><label><input class="header-button-item" value="prev" type="checkbox" /> Prev</label></li>'
                    + '<li><label><input class="header-button-item" value="next" type="checkbox" /> Next</label></li>'
                    + '<li><label><input class="header-button-item" value="nextYear" type="checkbox" /> Next Year</label></li>'
                    + '<li><label><input class="header-button-item" value="today" type="checkbox" /> Today</label></li>'
                    + '<li class="dropdown-divider"></li>'
                    + '<li><a class="dropdown-update" rel="#dropdown-header-center" href="#">Update</label></li>'
                    + '<li class="dropdown-divider"></li>'
                    + '</ul>'
                    + '</div>'

                    + '<div id="dropdown-event-limit" class="dropdown dropdown-tip">'
                    + '<ul class="dropdown-menu">'
                    + '<li class="dropdown-divider"></li>'
                    + '<li><label><input class="event-limit-item" value="true" name="eventlimit" type="radio" /> Yes limit to cell capacity</label></li>'
                    + '<li><label><input class="event-limit-item" value="false" name="eventlimit" type="radio" /> No limit</li>'
                    + '<li><label><input class="event-limit-item" value="number" name="eventlimit" type="radio" /> <input id="event-limit-number" min="1" max="20" inc="1" value="" type="number" /> Event limit<label></li>'
                    + '<li class="dropdown-divider"></li>'
                    + '<li><a class="dropdown-update"  rel="#dropdown-event-limit" href="#"> Update</label></li>'
                    + '<li class="dropdown-divider"></li>'
                    + '</ul>'
                    + '</div></div>');

    $('#dropdown-header-left').find('ul').sortable();
    $('#dropdown-header-center').find('ul').sortable();
    $('#dropdown-header-right').find('ul').sortable();

    $('.header-button-item').on('click', '', {}, function (event) {
        event.stopImmediatePropagation();
    });

    //event limit

    $('#dropdown-event-limit').on('show', '', {}, function (event) {
        $('#dropdown-event-limit').find('input').prop('checked', false);
        $('#event-limit-number').prop('disabled', true);
        $('#event-limit-number').val('');
    });
    $('#dropdown-event-limit').on('hide', '', {}, function (event, dropdownData) {
        $('#dropdown-event-limit').find('input').each(function (index, item) {
            var target = $('#dropdown-event-limit').hasClass('ibs-options') ? $('#ibs-event-limit') : $('#ibs-sc-limit');
            if ($(item).attr('type') === 'radio' && $(item).is(':checked')) {
                switch ($(this).val()) {
                    case 'true' :
                        target.val('yes');
                        break;
                    case 'false' :
                        target.val('no');

                        break;
                    case 'number' :
                        target.val($('#event-limit-number').val());
                }
            }
        });
        $('#event-limit-number').prop('disabled', true);
        $('#event-limit-number').val('');
        if ($('#dropdown-event-limit').hasClass('ibs-shortcode')) {
            $('#shortcode-options').trigger('change');
        }
    });

    $('.event-limit-item').on('click', '', {}, function (event) {
        event.stopImmediatePropagation();
        switch ($(this).val()) {
            case 'true' :
            case 'false' :
                $('#event-limit-number').prop('disabled', true);
                break;
            case 'number' :
                $('#event-limit-number').prop('disabled', false);
                $('#event-limit-number').focus();

        }
    });

    //header left

    $('#dropdown-header-left').on('hide', '', {}, function (event) {
        var target = $('#dropdown-event-limit').hasClass('ibs-options') ? $('#ibs-header-left') : $('#ibs-sc-left');
        var result = [];
        $('#dropdown-header-left').find('input').each(function (index, item) {
            if ($(item).is(':checked')) {
                result.push($(item).val());
            }
        });
        if (result.length) {
            target.val(result.toString());
            if ($('#dropdown-event-limit').hasClass('ibs-shortcode')) {
                $('#shortcode-options').trigger('change');
            }
        }
    });

    // center 

    $('#dropdown-header-center').on('hide', '', {}, function (event) {
        var target = $('#dropdown-event-limit').hasClass('ibs-options') ? $('#ibs-header-center') : $('#ibs-sc-center');
        var result = [];
        $('#dropdown-header-center').find('input').each(function (index, item) {
            if ($(item).is(':checked')) {
                result.push($(item).val());
            }
        });
        if (result.length) {
            target.val(result.toString());
            if ($('#dropdown-event-limit').hasClass('ibs-shortcode')) {
                $('#shortcode-options').trigger('change');
            }
        }
    });

    //right

    $('#dropdown-header-right').on('hide', '', {}, function (event) {
        var target = $('#dropdown-event-limit').hasClass('ibs-options') ? $('#ibs-header-right') : $('#ibs-sc-right');
        $('#dropdown-event-limit').hasClass('ibs-shortcode')
        var result = [];
        $('#dropdown-header-right').find('input').each(function (index, item) {
            if ($(item).is(':checked')) {
                result.push($(item).val());
            }
        });
        if (result.length) {
            target.val(result.toString());
            if ($('#dropdown-event-limit').hasClass('ibs-shortcode')) {
                $('#shortcode-options').trigger('change');
            }
        }
    });
});