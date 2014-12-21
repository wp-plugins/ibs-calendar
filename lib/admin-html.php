<?PHP ?>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        var IBSCALENDAR = null;
        $("#ibs-calendar-tabs").tabs({beforeActivate: function (event, ui) {
                if (IBSCALENDAR) {
                    $('#ibs-calendar-1').remove();
                    IBSCALENDAR = null;
                }
                var tab = $(ui.newTab).find('a').text();
                switch (tab) {
                    case 'Calendar':
                        jQuery(document).ready(function ($) {

                            $('#ibs-calendar-tab-calendar').parent()
                                    .append($('<div id="ibs-calendar-1" style="width:<?PHP echo $args['width']; ?>">')
                                            .append($('<form id="fullcalendar-1" >'))
                                            .append($('<div id="ibs-loading-1" >'))
                                            .append($('<div>')
                                                    .append($('<input>').attr({'id': 'list-display-1', 'type': 'checkbox'}).css("margin", "10px"))
                                                    .append($('<span>').text(" Event List"))
                                                    .append($('<div>').attr('id', "event-list-1")
                                                            .append($('<table>').attr('id', 'event-table-1')
                                                                    ))
                                                    )
                                            );
                            IBSCALENDAR = new CalendarObj(jQuery, <?PHP echo json_encode($args); ?>, 'admin');
                        });
                        break;
                    case 'Options':
                        $('#dropdown-event-limit').removeClass('ibs-shortcode').addClass('ibs-options');

                        $('#ibs-sc-limit-help').dropdown('detach', '#dropdown-event-limit');
                        $('#ibs-event-limit-help').dropdown('attach', '#dropdown-event-limit');

                        $('#ibs-sc-dns-help').dropdown('detach', '#dropdown-dns');
                        $('#ibs-day-names-short-help').dropdown('attach', '#dropdown-dns');

                        $('#ibs-sc-left-help').dropdown('detach', '#dropdown-header-left');
                        $('#ibs-sc-right-help').dropdown('detach', '#dropdown-header-right');
                        $('#ibs-sc-center-help').dropdown('detach', '#dropdown-header-center');
                        $('#ibs-sc-height-help').dropdown('detach', '#dropdown-height');

                        $('#ibs-header-left-help').dropdown('attach', '#dropdown-header-left');
                        $('#ibs-header-right-help').dropdown('attach', '#dropdown-header-right');
                        $('#ibs-header-center-help').dropdown('attach', '#dropdown-header-center');
                        $('#ibs-hiddendays-help').dropdown('attach', '#dropdown-hiddendays');
                        $('#ibs-height-help').dropdown('attach', '#dropdown-height');
                        break;
                    case 'Shortcode':
                        $('#dropdown-event-limit').removeClass('ibs-options').addClass('ibs-shortcode');

                        $('#ibs-header-left-help').dropdown('detach', '#dropdown-header-left');
                        $('#ibs-header-right-help').dropdown('detach', '#dropdown-header-right');
                        $('#ibs-header-center-help').dropdown('detach', '#dropdown-header-center');

                        $('#ibs-hiddendays-help').dropdown('detach', '#dropdown-hiddendays');
                        $('#ibs-sc-hiddendays-help').dropdown('attach', '#dropdown-hiddendays');

                        $('#ibs-height-help').dropdown('detach', '#dropdown-height');
                        $('#ibs-sc-height-help').dropdown('attach', '#dropdown-height');

                        $('#ibs-event-limit-help').dropdown('detach', '#dropdown-event-limit');
                        $('#ibs-sc-limit-help').dropdown('attach', '#dropdown-event-limit');

                        $('#ibs-sc-left-help').dropdown('attach', '#dropdown-header-left');
                        $('#ibs-sc-right-help').dropdown('attach', '#dropdown-header-right');
                        $('#ibs-sc-center-help').dropdown('attach', '#dropdown-header-center');

                        $('#ibs-day-names-short-help').dropdown('detach', '#dropdown-dns');
                        $('#ibs-sc-dns-help').dropdown('attach', '#dropdown-dns');

                        $('#shortcode-result').val('[ibs-calendar]');
                        break;
                    case 'Site Events':
                        jQuery(document).ready(function ($) {
                            IBSCALENDAR = new CalendarObj(jQuery, <?PHP echo json_encode($args); ?>, 'events');
                        });
                    default:
                }
            }});
        $("#ibs-calendar-tabs").show();
    });</script>
<div id="ibs-calendar-tabs" style="display:none" >
    <ul id="ibs-calendar-tabs-nav">
        <li><a href="#ibs-calendar-tab-settings">Settings</a></li>
        <li><a href="#ibs-calendar-tab-fullcalendar">Options</a></li>
        <li><a href="#ibs-calendar-tab-feeds">Feeds</a></li>
        <li><a href="#ibs-calendar-tab-qtip">Tooltip</a></li>
        <li><a href="#ibs-calendar-tab-shortcode">Shortcode</a></li>
        <li><a href="#ibs-calendar-tab-calendar">Calendar</a></li>
        <?php if (self::$options['siteEvents']) echo '<li><a href="#ibs-calendar-tab-events">Site Events</a></li>'; ?>
    </ul>

    <div style="clear:both"></div>
    <form action="options.php" method="post">
        <?php settings_fields('ibs_calendar_options'); ?>
        <div id="ibs-calendar-tab-settings">
            <?php do_settings_sections('calendar-general'); ?>
            <?php submit_button(); ?>

        </div>
        <div id="ibs-calendar-tab-fullcalendar">
            <?php do_settings_sections('fullcalendar'); ?>
            <?php submit_button(); ?>

        </div>
        <div id="ibs-calendar-tab-feeds">
            <?php do_settings_sections('feeds'); ?>
            <?php submit_button(); ?>

        </div>
        <div id="ibs-calendar-tab-qtip">
            <?php do_settings_sections('qtip'); ?>
            <?php submit_button(); ?>
        </div>
    </form>
    <div id="ibs-calendar-tab-calendar">
        <div id="ibs-admin-div"></div>
    </div>
    <div id="ibs-calendar-tab-shortcode">
        <div class="ibs-admin-bar">Override options</div>
        <div id="shortcode-options">
            <div>feeds 
                <div id="available-feeds">
                    <?php
                    for ($feed = 1; $feed <= self::$options['feedCount']; $feed++) {
                        $curr_feed = "feed_" . $feed;
                        if (isset(self::$options['feeds'][$curr_feed]['name']) && self::$options['feeds'][$curr_feed]['name'] !== '') {
                            $name = self::$options['feeds'][$curr_feed]['name'];
                        } else {
                            $name = $curr_feed;
                        }
                        echo "<div><span>$name</span><input name='availableFeeds' value='$feed' type='checkbox'/></div>";
                    }
                    ?>
                </div>
            </div>
            <div><span>event_list</span><select name="eventlist">
                    <option value="" selected ></option>
                    <option value="none">None</option>
                    <option value="show" >Show</option>
                    <option value="hide" >Hide</option>
                </select>
            </div>
            <div><span>siteEvents</span><input type='checkbox' name='siteEvents' /></div>
            <div><span>width</span><input name="width" type="text" size="25" value=""/></div>
            <div><span>height</span><input id="ibs-sc-height" name="height" type="text" size="25" value=""/><a href="#" id="ibs-sc-height-help">help</a></div>
            <div><span>hiddenDays</span><input id="ibs-sc-hiddendays" name="hiddenDays" type="text" size="100" value=""/><a href="#" id="ibs-sc-hiddendays-help">help</a></div>
            <div><span>dayNamesShort</span><input id="ibs-sc-dns" name="dayNamesShort" type="text" size="100" value=""/><a href="#" id="ibs-sc-dns-help">help</a></div>
            <div><span>aspectRatio</span><input name="aspectRatio" min="0.1" max="5.0" step="0.1" type="number"/></div>
            <div><span>defaultDate</span><input name="defualtDate" type="text" size="25" value=""/></div>
            <div><span>weekends</span><input type='checkbox' name='weekends' /></div>
            <div><span>theme</span><input type='checkbox' name='theme' /></div>
            <div><span>editable</span><input type='checkbox' name='editable'/></div>
            <div><span>eventLimit</span><input id="ibs-sc-limit" type='text' name='eventLimit' /><a href="#" id="ibs-sc-limit-help">help</a></div>
            <div><span>timeZone</span><input name="timeZone" type="text" size="25" value=""/></div>
            <div><span>timeFormat</span><input name="timeformat" type="text" size="25" value=""/></div>
            <div><span>titleFormat</span><input name="titleformat" type="text" size="25" value=""/></div>
            <div><span>headerLeft</span><input id="ibs-sc-left" name="headerLeft" type="text" size="100" value=""/><a href="#" id="ibs-sc-left-help">help</a></div>
            <div><span>headerCenter</span><input id="ibs-sc-center"  name="headerCenter" type="text" size="100" value=""/><a href="#" id="ibs-sc-center-help">help</a></div>
            <div><span>headerRight</span><input id="ibs-sc-right" name="headerRight" type="text" size="100" value=""/><a href="#" id="ibs-sc-right-help">help</a></div>
            <div><span>firstDay</span><select name="firstDay">
                    <option value="" selected ></option>
                    <option value="0" >Sunday</option>
                    <option value="1" >Monday</option>
                    <option value="2" >Tuesday</option>
                    <option value="3" >Wednesday</option>
                    <option value="4" >Thursday</option>
                    <option value="5" >Friday</option>
                    <option value="6" >Saturday</option>
                </select>
            </div>
            <div>
                <span>defaultView</span>
                <select name="defaultView" >
                    <option value="" selected ></option>
                    <option value="month" >Month</option>
                    <option value="week" >Week</option>
                    <option value="day" >Day</option>
                </select>
            </div>
            <div>
                <span>eventLimitClick</span>
                <select name="eventLimitClick" >
                    <option value="" selected ></option>
                    <option value="none" >popover</option>
                    <option value="week" >week</option>
                    <option value="day" >day</option>
                </select>
            </div>
        </div>
        <div><textarea id="shortcode-result" style="width:100%; height:100px;"></textarea></div>
    </div>
    <?php if (false === self::$options['siteEvents']) echo '<!-- no site events '; ?>
    <div id="ibs-calendar-tab-events">
        <h2>Local Events</h2>
        <div id="ibs-events-div">
            <div id="ibs-events-buttons-page" class="widefat"> 
                <div>
                    <strong>
                        <button class="ibs-events-paging" title="first page"> << </button>
                        <button class="ibs-events-paging" title="previous page"> < </button>
                        <button class="ibs-events-paging" title="next page"> > </button>
                        <button class="ibs-events-paging" title="last page"> >> </button>
                    </strong>

                    <label>  Page limit <input id="ibs-events-limit" type="number" min="5" value="100" style="width:60px;"/></label>
                </div>
                <input id="ibs-events-position" type="hidden" value="0" disabled />
            </div>
        </div>
        <table rules="all" id="ibs-events-table">
            <tr class="ibs-events-header-row">
                <th class="ibs-events-td">No.</th>
                <th class="ibs-events-td event-center ibs-events-select"><button id="ibs-events-toggle">All</button></th>
                <th class="ibs-events-td">Title</th>
                <th class="ibs-events-td">Start</th>
                <th class="ibs-events-td">Duration</th>
                <th class="ibs-events-td">Location</th>
                <th class="ibs-events-td">Description</th>
                <th class="ibs-events-td"> <button id="ibs-events-add" title="add event" style="font-weight:normal;"><strong> + </strong></button></th>
            </tr>
        </table>
        <button id="ibs-events-remove" disabled>Remove</button>
    </div>
    <?php if (false === self::$options['siteEvents']) echo 'no site events -->'; ?>
</div>
<?php if (false === self::$options['siteEvents']) echo '<!-- no site events '; ?>
<div id="event-dialog" title="Event" style="display:none">
    <div class="widefat" > '
        <label for="ibs-event-title">Title</label>
        <input class="widefat" id="ibs-event-title" placeholder="event title" value=""/>
    </div>
    <div class="widefat ibs-event-date">
        <label for="ibs-event-start-date">Start</label>
        <input class="ibs-datepicker event-allday" id="ibs-event-start-date" type="text" placeholder="start date" value="" />
        <input class="ibs-timepicker event-allday" id="ibs-event-start-time" type="text" placeholder="start time" value="" />
        <label for="ibs-event-allday" style="width:45px;margin-left:10px;">All day</label>
        <input id="ibs-event-allday" type="checkbox"  class="cb" name="ibs_event_allday" />
    </div>
    <div class="widefat ibs-event-date">
        <label for="ibs-event-end-date">End</label>
        <input class="ibs-datepicker event-allday" id="ibs-event-end-date" type="text" placeholder="end date" value="" />
        <input class="ibs-timepicker event-allday" id="ibs-event-end-time" type="text" placeholder="end time" value="" />
    </div>
    <div class="widefat" > 
        <label>Location</label>
        <input class="widefat" id="ibs-event-location" placeholder="event title" value=""/>
    </div>
    <div class="widefat" ><label class="color-label" >Event color </label>
        <div class="color-box color-box-selected" style = "background-color: #5484ed;"></div>
        <div class="color-box" style = "background-color: #a4bdfc;"></div>
        <div class="color-box" style = "background-color: #46d6db;"></div>
        <div class="color-box" style = "background-color: #7ae7bf;"></div>
        <div class="color-box" style = "background-color: #51b749;"></div>
        <div class="color-box" style = "background-color: #fbd75b;"></div>
        <div class="color-box" style = "background-color: #ffb878;"></div>
        <div class="color-box" style = "background-color: #ff887c;"></div>
        <div class="color-box" style = "background-color: #dc2127;"></div>
        <div class="color-box" style = "background-color: #dbadff;"></div>
        <div class="color-box" style = "background-color: #e1e1e1;"></div>
    </div>
    <div></div>
    <div class="widefat">
        <label for="ibs-event-description">Description</label>
    </div>
    <textarea class="widefat" id="ibs-event-description" placeholder="event descrption" ></textarea>
    <div class="widefat">
        <label class="option-name" for="ibs-event-recurr">Repeats
            <input id="ibs-event-recurr" type="checkbox" style="margin-top:1px;" /></label>
    </div>  
    <div id="repeat-options">
        <div class="repeat-option">
            <input name="freq" type="radio" value="3" class="cb" /><label>Daily</label>
            <input name="freq" type="radio" value="2" class="cb" checked /><label>Weekly</label>
            <input name="freq" type="radio" value="1" class="cb" /><label>Monthly</label>
            <input name="freq" type="radio" value="0" class="cb" /><label>Yearly</label>
            <label style="margin-left:10px;" for="ibs-event-frequency">Every</label>
            <input id="repeat-interval" style="width:50px;" type="number" value="1" min="1" name="interval"/><label id="repeat-interval-type">week</label>
        </div>
        <div class="repeat-option widefat">
            <label class="option-name" for="ibs-event-repeats-on">Repeats on</label>
            <input type="checkbox" name="byweekday" class="cb" title="Sunday" value="6"><label>Sun</label>
            <input type="checkbox" name="byweekday" class="cb" title="Monday"  value="0"><label>Mon</label>
            <input type="checkbox" name="byweekday" class="cb" title="Tuesday" value="1"><label>Tue</label>
            <input type="checkbox" name="byweekday" class="cb" title="Wednesday" value="2"><label>Wed</label>
            <input type="checkbox" name="byweekday" class="cb" title="Thursday" value="3"><label>Thu</label>
            <input type="checkbox" name="byweekday" class="cb" title="Friday" value="4"><label>Fri</label>
            <input type="checkbox" name="byweekday" class="cb" title="Saturday" value="5"><label>Sat</label>
        </div>
        <div class="repeat-option widefat">
            <label class="option-name">Starting</label>
            <input id="repeat-dtstart" name="dtstart" placeholder="rrule.dtstart (first date)" type="text"/>
        </div>
        <div class="repeat-option widefat">
            <label class="option-name" for="ibs-event-ends">Ending</label>
            <input name="radio_ends" value="never" type="radio" class="cb" checked /><label>Never</label>
            <input name="radio_ends" type="radio" value="until" class="cb" /><label>Until</label>
            <input class="option-ends" id="repeat-until" type="text" name="until"  placeholder="rrule.until (last date)" disabled />
            <input name="radio_ends" type="radio" value="count" class="cb" /><label>Count</label>
            <input class="option-ends" id="repeat-count" type="number" max="1000" min="1" value="" name="count" disabled/>
        </div>
        <div style="display:none">
            <div class="widefat">
                <label class="option-name">Week starts</label>
                <input id="repeat-wkst" type="hidden" name="wkst" value="0" />
            </div>
            <div class="widefat">
                <label class="option-name">Month</label>
                <input name="bymonth" type="checkbox" value="1" class="cb" /><label>Jan</label>
                <input name="bymonth" type="checkbox" value="2" class="cb" /><label>Feb</label>
                <input name="bymonth" type="checkbox" value="3" class="cb" /><label>Mar</label>
                <input name="bymonth" type="checkbox" value="4" class="cb" /><label>Apr</label>
                <input name="bymonth" type="checkbox" value="5" class="cb" /><label>May</label>
                <input name="bymonth" type="checkbox" value="6" class="cb" /><label>Jun</label>
            </div>
            <div class="widefat">
                <label class="option-name"></label>
                <input name="bymonth" type="checkbox" value="7" class="cb" /><label>Jul</label>
                <input name="bymonth" type="checkbox" value="8" class="cb" /><label>Aug</label>
                <input name="bymonth" type="checkbox" value="9" class="cb" /><label>Sep</label>
                <input name="bymonth" type="checkbox" value="10" class="cb" /><label>Oct</label>
                <input name="bymonth" type="checkbox" value="11" class="cb" /><label>Nov</label>
                <input name="bymonth" type="checkbox" value="12" class="cb" /><label>Dec</label>
            </div>
            <div class="widefat">
                <label class="option-name" >Position</label>
                <input id="repeat-bysetpos"  placeholder="rrule.bysetpos" name="bysetpos"/>  
            </div>
            <div class="widefat">
                <label class="option-name" >Day of mo.</label>
                <input id="repeat-bymonthday" placeholder="rrule.bymonthday" name="bymonthday"/>
            </div>
            <div class="widefat">
                <label class="option-name" >Day of yr.</label>
                <input id="repeat-byyearday"  placeholder="rrule.byyearday" name="byyearday" type="text" value=""/>
            </div>
            <div class="widefat">
                <label class="option-name" >Week no.</label>
                <input id="repeat-byweekno"  placeholder="rrule.byweekno" name="byweekno">
            </div> 
            <div class="widefat">
                <label class="option-name" >Hour</label>
                <input id="repeat-byhour"  placeholder="rrule.byhour" name="byhour">
            </div> 
            <div class="widefat">
                <label class="option-name" >Minute</label>
                <input id="repeat-byminute" placeholder="rrule.byminute" name="byminute"/>
            </div> 
            <div class="widefat">
                <label class="option-name" >Second</label>
                <input id="repeat-bysecond" placeholder="rrule.bysecond" name="bysecond">
            </div> 
        </div>
    </div>
    <div class="repeat-option widefat"> <label for="ibs-event-repeat"></label></div>
    <textarea class="repeat-option widefat" id="ibs-event-repeat" readonly></textarea>
    <div id="ibs-event-exception-div" class="repeat-option">
    </div>
</div>
<?php
if (false === self::$options['siteEvents'])
    echo '  no site events -->';?>