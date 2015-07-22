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
                                    .append($('<div id="ibs-calendar-1" class="<?PHP echo $args['align']; ?>" style="max-width:<?PHP echo "1000px"; ?>">')
                                            .append($('<form id="fullcalendar-1" >'))
                                            .append($('<div id="ibs-loading-1" >'))
                                            .append($('<div>')
                                                    .append($('<input>').attr({'id': 'list-display-1', 'type': 'checkbox'}).css("margin", "10px"))
                                                    .append($('<span>').text(" Event List"))
                                                    .append($('<div>').attr('id', "event-list-1")
                                                            .append($('<table rules="all">').attr('id', 'event-table-1')
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
        <li><a href="#ibs-calendar-tab-shortcode">Shortcode</a></li>
        <li><a href="#ibs-calendar-tab-calendar">Calendar</a></li>
    </ul>

    <div style="clear:both"></div>
    <form action="options.php" method="post">
        <?php settings_fields('ibs_calendar_options'); ?>
        <div id="ibs-calendar-tab-settings">
            <?php do_settings_sections('calendar-general'); ?>

            <?php do_settings_sections('calendar-list-general'); ?>

            <?php do_settings_sections('qtip'); ?>

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
            <div><span>hideTitle</span><input type='checkbox' name='hideTitle' /></div>
            <div><span>event_list</span><select name="event_list">
                    <option value="" selected ></option>
                    <option value="none">None</option>
                    <option value="show" >Show</option>
                    <option value="hide" >Hide</option>
                </select>
            </div>
            <div><span>ibsEvents</span><input type='checkbox' name='ibsEvents' /></div>
            <div><span>width</span><input name="width" type="text" size="25" value=""/></div>
            <div><span>align</span><select name="align">
                    <option value="" selected ></option>
                    <option value="alignleft" >Left</option>
                    <option value="aligncenter" >Center</option>
                    <option value="alignright" >Right</option>
                </select>
            </div>
            <div><span>height</span><input id="ibs-sc-height" name="height" type="text" size="25" value=""/><a href="#" id="ibs-sc-height-help">help</a></div>
            <div><span>hiddenDays</span><input id="ibs-sc-hiddendays" name="hiddenDays" type="text" size="100" value=""/><a href="#" id="ibs-sc-hiddendays-help">help</a></div>
            <div><span>dayNamesShort</span><input id="ibs-sc-dns" name="dayNamesShort" type="text" size="100" value=""/><a href="#" id="ibs-sc-dns-help">help</a></div>
            <div><span>aspectRatio</span><input name="aspectRatio" min="0.1" max="5.0" step="0.1" type="number"/></div>
            <div><span>defaultDate</span><input name="defaultDate" type="text" size="25" value=""/></div>
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
</div>