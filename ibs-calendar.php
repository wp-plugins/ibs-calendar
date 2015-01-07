<?php
/*
  Plugin Name: IBS Calendar
  Plugin URI: http://wordpress.org/extend/plugins/
  Description: implements FullCalendar for Wordpress Adimin and shortcode.
  Author: HMoore71
  Version: 1.0
  Author URI: http://indianbendsolutions.net
  License: GPL2
  License URI: none
 */

/*
  This program is distributed in the hope that it will be useful, but
  WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

define('IBS_CALENDAR_VERSION', '1.0');
register_activation_hook(__FILE__, 'ibs_calendar_defaults');

function ibs_calendar_defaults() {
    IBS_CALENDAR::defaults();
}

register_deactivation_hook(__FILE__, 'ibs_calendar_deactivate');

function ibs_calendar_deactivate() {
    delete_option('ibs_calendar_options');
}

class IBS_CALENDAR {

    static $add_script = 0;
    static $add_script_gcal_list = 0;
    static $options = array();
    static $options_defaults = array(
        "version" => IBS_CALENDAR_VERSION,
        "debug" => false,
        "ibsEvents" => false,
        "ui_theme" => "cupertino",
        "event_list" => "none",
        "list_past" => false,
        "list_max" => 100,
        "list_repeat" => false,
        "feedCount" => 3,
        "theme" => false,
        "width" => "100%",
        "align" => "alignleft",
        "height" => null,
        "firstDay" => "1",
        "weekends" => false,
        "lang" => "en_us",
        "titleFormat" => "MMM DD, YYYY",
        "timeFormat" => "HH:mm",
        "defaultView" => "month",
        "eventLimit" => 'yes',
        "eventLimitClick" => "popover",
        "aspectRatio" => 1.0,
        "editable" => false,
        "feeds" => array(
            "feed_1" => array('name' => 'Google Holidays', 'enabled' => false, 'url' => 'en.usa#holiday@group.v.calendar.google.com', 'key' => '', 'text_color' => 'white', 'background_color' => '#5484ed'),
            "feed_2" => array('name' => '', 'enabled' => false, 'url' => '', 'key' => '', 'text_color' => 'white', 'background_color' => '#5484ed'),
            "feed_3" => array('name' => '', 'enabled' => false, 'url' => '', 'key' => '', 'text_color' => 'white', 'background_color' => '#5484ed')),
        "headerLeft" => 'prevYear,prev,next,nextYear today',
        "headerCenter" => 'title',
        "headerRight" => 'month agendaWeek agendaDay',
        "hiddenDays" => '',
        "dayNamesShort" => '',
        "fixedWeekCount" => false,
        "weekNumbers" => false,
        "weekNumberCalculation" => 'local',
        "weekNumberTitle" => 'W',
        "timeZone" => "local",
        "qtip" => array('style' => "qtip-bootstrap", 'rounded' => false, 'shadow' => false),
        "hideTitle" => false,
        "defaultDate" => ''
    );

    static function extendA($a, &$b) {
        foreach ($a as $key => $value) {
            if (!isset($b[$key])) {
                $b[$key] = $value;
            }
            if (is_array($value)) {
                self::extendA($value, $b[$key]);
            }
        }
    }

    static function fixBool(&$item, $key) {
        if ($key === 'eventLimit') {
            return;
        }
        switch (strtolower($item)) {
            case "null" : $item = null;
                break;
            case "true" :
            case "yes" : $item = true;
                break;
            case "false" :
            case "no" : $item = false;
                break;
            default :
        }
    }

    static function init() {
        self::$options = get_option('ibs_calendar_options');
        if (isset(self::$options['version']) === false || self::$options['version'] !== IBS_CALENDAR_VERSION) {
            self::defaults();  //development set new options
        } else {
            self::extendA(self::$options_defaults, self::$options);
            array_walk_recursive(self::$options, array(__CLASS__, 'fixBool'));
        }
        add_action('admin_init', array(__CLASS__, 'admin_options_init'));
        add_action('admin_menu', array(__CLASS__, 'admin_add_page'));
        add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'admin_enqueue_scripts'));
        add_shortcode('ibs-calendar', array(__CLASS__, 'handle_shortcode'));
        add_action('init', array(__CLASS__, 'register_script'));
        add_action('wp_head', array(__CLASS__, 'print_script_header'));
        add_action('wp_footer', array(__CLASS__, 'print_script_footer'));
        add_action('admin_print_scripts', array(__CLASS__, 'print_admin_scripts'));
        add_action('wp_ajax_ibs_calendar_get_events', array(__CLASS__, 'get_ibs_events'));
        add_action('wp_ajax_nopriv_ibs_calendar_get_events', array(__CLASS__, 'get_ibs_events'));
    }

    static function defaults() { //jason_encode requires double quotes
        $options = (array) get_option('ibs_calendar_options');
        self::extendA($arr, $options);
        array_walk_recursive($options, array(__CLASS__, 'fixBool'));
        $options['version'] = IBS_CALENDAR_VERSION;
        self::$options = $options;
        update_option('ibs_calendar_options', $options);
    }

    static function admin_options_init() {
        register_setting('ibs_calendar_options', 'ibs_calendar_options');
        add_settings_section('calendar-section-general', '', array(__CLASS__, 'admin_general_header'), 'calendar-general');
        add_settings_field('debug', 'debug', array(__CLASS__, 'field_debug'), 'calendar-general', 'calendar-section-general');
        add_settings_field('ui_theme', 'ui theme', array(__CLASS__, 'field_ui_theme'), 'calendar-general', 'calendar-section-general');
        add_settings_field('align', 'calendar align', array(__CLASS__, 'field_align'), 'calendar-general', 'calendar-section-general');
        add_settings_field('width', 'calendar width', array(__CLASS__, 'field_width'), 'calendar-general', 'calendar-section-general');
        add_settings_field('ibsEvents', 'Show IBS Events', array(__CLASS__, 'field_ibsEvents'), 'calendar-general', 'calendar-section-general');

        add_settings_section('calendar-list-section-general', '', array(__CLASS__, 'admin_general_list_header'), 'calendar-list-general');
        add_settings_field('event_list', 'event list', array(__CLASS__, 'field_event_list'), 'calendar-list-general', 'calendar-list-section-general');
        add_settings_field('list_max', 'List max events', array(__CLASS__, 'field_list_max'), 'calendar-list-general', 'calendar-list-section-general');
        add_settings_field('list_past', 'List past events', array(__CLASS__, 'field_list_past'), 'calendar-list-general', 'calendar-list-section-general');
        add_settings_field('list_repeat', 'List repeat events', array(__CLASS__, 'field_list_repeat'), 'calendar-list-general', 'calendar-list-section-general');

        add_settings_section('section_fullcalendar', '', array(__CLASS__, 'admin_options_header'), 'fullcalendar');
        add_settings_field('aspectRatio', 'aspectRatio', array(__CLASS__, 'field_aspectRatio'), 'fullcalendar', 'section_fullcalendar');
        add_settings_field('dayNamesShort', 'dayNamesShort', array(__CLASS__, 'field_dayNamesShort'), 'fullcalendar', 'section_fullcalendar');
        add_settings_field('defaultView', 'defaultView', array(__CLASS__, 'field_defaultView'), 'fullcalendar', 'section_fullcalendar');
        add_settings_field('editable', 'editable', array(__CLASS__, 'field_editable'), 'fullcalendar', 'section_fullcalendar');
        add_settings_field('eventLimit', 'eventLimit', array(__CLASS__, 'field_eventLimit'), 'fullcalendar', 'section_fullcalendar');
        add_settings_field('eventLimitClick', 'eventLimitClick', array(__CLASS__, 'field_eventLimitClick'), 'fullcalendar', 'section_fullcalendar');
        add_settings_field('firstDay', 'firstDay', array(__CLASS__, 'field_firstDay'), 'fullcalendar', 'section_fullcalendar');
        add_settings_field('fixedWeekCount', 'fixedWeekCount', array(__CLASS__, 'field_fixedWeekCount'), 'fullcalendar', 'section_fullcalendar');
        add_settings_field('headerCenter', 'headerCenter', array(__CLASS__, 'field_headerCenter'), 'fullcalendar', 'section_fullcalendar');
        add_settings_field('headerLeft', 'headerLeft', array(__CLASS__, 'field_headerLeft'), 'fullcalendar', 'section_fullcalendar');
        add_settings_field('headerRight', 'headerRight', array(__CLASS__, 'field_headerRight'), 'fullcalendar', 'section_fullcalendar');
        add_settings_field('height', 'height', array(__CLASS__, 'field_height'), 'fullcalendar', 'section_fullcalendar');
        add_settings_field('hiddenDays', 'hiddenDays', array(__CLASS__, 'field_hiddenDays'), 'fullcalendar', 'section_fullcalendar');
        add_settings_field('theme', 'theme (ui)', array(__CLASS__, 'field_theme'), 'fullcalendar', 'section_fullcalendar');
        add_settings_field('timeFormat', 'timeFormat', array(__CLASS__, 'field_timeFormat'), 'fullcalendar', 'section_fullcalendar');
        add_settings_field('timeZone', 'timeZone', array(__CLASS__, 'field_timeZone'), 'fullcalendar', 'section_fullcalendar');
        add_settings_field('titleFormat', 'titleFormat', array(__CLASS__, 'field_titleFormat'), 'fullcalendar', 'section_fullcalendar');
        add_settings_field('weekends', 'weekends', array(__CLASS__, 'field_weekends'), 'fullcalendar', 'section_fullcalendar');
        add_settings_field('weekNumbers', 'weekNumbers', array(__CLASS__, 'field_weekNumbers'), 'fullcalendar', 'section_fullcalendar');


        add_settings_section('section_feeds', '', array(__CLASS__, 'admin_feeds_header'), 'feeds');
        add_settings_field('feedCount', 'event feed count', array(__CLASS__, 'field_feedCount'), 'feeds', 'section_feeds');
        add_settings_field('feeds', 'event feeds', array(__CLASS__, 'field_feeds'), 'feeds', 'section_feeds');

        add_settings_section('section_qtip', '', array(__CLASS__, 'admin_qtip_header'), 'qtip');
        add_settings_field('rounded', 'Rounded', array(__CLASS__, 'field_qtip_rounded'), 'qtip', 'section_qtip');
        add_settings_field('shadow', 'Shadow', array(__CLASS__, 'field_qtip_shadow'), 'qtip', 'section_qtip');
        add_settings_field('style', 'Style', array(__CLASS__, 'field_qtip_style'), 'qtip', 'section_qtip');
    }

    static function admin_general_header() {
        echo '<div class="ibs-admin-bar">General settings</div>';
    }

    static function admin_general_list_header() {
        echo '<div class="ibs-admin-bar">Event list default settings</div>';
    }

    static function admin_options_header() {
        echo '<div class="ibs-admin-bar" >FullCalendar default settings   (<a href="http://fullcalendar.io/docs/" target="_blank" >please see the Full Calendar documentation for these options.)</a> </div>';
    }

    static function admin_qtip_header() {
        echo '<div class="ibs-admin-bar" >Event list Qtip settings</div>';
    }

    static function admin_feeds_header() {
        echo '<div class="ibs-admin-bar" >Google Calendar feeds</div>';
    }

    static function field_feedCount() {
        $value = self::$options['feedCount'];
        echo '<input name="ibs_calendar_options[feedCount]" min="1" max="10" step="1" placeholder="number of feeds" type="number" value="' . $value . '" />';
    }

    static function field_debug() {
        $checked = self::$options['debug'] ? "checked" : '';
        echo '<input type="checkbox" name="ibs_calendar_options[debug]" value="true"' . $checked . '/>';

        $version = self::$options['version'];
        echo "<input type='hidden' name='ibs_calendar_options[version]' value='$version'/>";
    }

    static function field_ibsEvents() {
        $checked = self::$options['ibsEvents'] ? "checked" : '';
        echo '<input type="checkbox" name="ibs_calendar_options[ibsEvents]" value="true"' . $checked . '/>';
    }

    static function field_fixedWeekCount() {
        $checked = self::$options['fixedWeekCount'] ? "checked" : '';
        echo '<input type="checkbox" name="ibs_calendar_options[fixedWeekCount]" value="true"' . $checked . '/> [fixed number of weeks shown.]';
    }

    static function field_weekNumbers() {
        $checked = self::$options['weekNumbers'] ? "checked" : '';
        echo '<input type="checkbox" name="ibs_calendar_options[weekNumbers]" value="true"' . $checked . '/>';
    }

    static function field_ui_theme() {
        $result = array();
        $dir = get_home_path() . 'wp-content/plugins/ibs-calendar/css/jquery-ui-themes-1.11.1/themes/';
        if (file_exists($dir)) {
            $files = scandir($dir);
            natcasesort($files);
            if (count($files) > 2) { /* The 2 accounts for . and .. */
                foreach ($files as $file) {
                    if (file_exists($dir . $file) && $file != '.' && $file != '..' && is_dir($dir . $file)) {
                        $result[] = $file;
                    }
                }
            }
        }
        foreach ($result as &$line) {
            $line = "<option selected value='$line' >$line</option>";
        }
        echo "<select name='ibs_calendar_options[ui_theme]'>";
        foreach ($result as $option) {
            if (strpos($option, self::$options['ui_theme']) == false) {
                $option = str_replace('selected', '', $option);
            }
            echo $option;
        }
        echo "</select>";
    }

    static function field_event_list() {
        echo '<select name="ibs_calendar_options[event_list]"  />';
        $selected = self::$options['event_list'] == "none" ? 'selected' : '';
        echo '<option value="none" ' . $selected . '>None</option>';
        $selected = self::$options['event_list'] == "show" ? 'selected' : '';
        echo '<option value="show" ' . $selected . '>Show</option>';
        $selected = self::$options['event_list'] == "hide" ? 'selected' : '';
        echo '<option value="hide" ' . $selected . '>Hide</option>';
        echo '</select>';
    }

    static function field_list_past() {
        $checked = self::$options['list_past'] ? "checked" : '';
        echo '<input type="checkbox" name="ibs_calendar_options[list_past]" value="true"' . $checked . '/>';
    }

    static function field_list_repeat() {
        $checked = self::$options['list_repeat'] ? "checked" : '';
        echo '<input type="checkbox" name="ibs_calendar_options[list_repeat]" value="true"' . $checked . '/>';
    }

    static function field_list_max() {
        $value = self::$options['list_max'];
        echo '<input name="ibs_calendar_options[list_max]" min="1" max="1000" step="1" placeholder="number of feeds" type="number" value="' . $value . '" />';
    }

    static function field_align() {
        echo '<select name="ibs_calendar_options[align]"  />';
        $selected = self::$options['align'] == "alignleft" ? 'selected' : '';
        echo '<option value="alignleft" ' . $selected . '>left</option>';
        $selected = self::$options['align'] == "aligncenter" ? 'selected' : '';
        echo '<option value="aligncenter" ' . $selected . '>center</option>';
        $selected = self::$options['align'] == "alignright" ? 'selected' : '';
        echo '<option value="alignright" ' . $selected . '>right</option>';
        echo '</select>';
    }

    static function field_weekends() {
        $checked = self::$options['weekends'] ? "checked" : '';
        echo '<input type="checkbox" name="ibs_calendar_options[weekends]" value="true" ' . $checked . '/>';
    }

    static function field_theme() {
        $checked = self::$options['theme'] ? "checked" : '';
        echo '<input type="checkbox" name="ibs_calendar_options[theme]" value="true"' . $checked . '/>';
    }

    static function field_editable() {
        $checked = self::$options['editable'] ? "checked" : '';
        echo '<input type="checkbox" name="ibs_calendar_options[editable]" value="true"' . $checked . '/>';
    }

    static function field_eventLimit() {
        $value = self::$options['eventLimit'];
        echo "<input id='ibs-event-limit'  type='text' name='ibs_calendar_options[eventLimit]'  value='$value'  /><a href='#' id='ibs-event-limit-help'>help</a>";
    }

    static function field_width() {
        $value = self::$options['width'];
        echo '<input name="ibs_calendar_options[width]" type="text" size="25" value="' . $value . '"/>';
    }

    static function field_height() {
        $value = self::$options['height'];
        echo '<input id="ibs-height" name="ibs_calendar_options[height]" type="text" size="25" value="' . $value . '"/><a href="#" id="ibs-height-help">help</a>';
    }

    static function field_titleFormat() {
        $value = self::$options['titleFormat'];
        echo '<input name="ibs_calendar_options[titleFormat]" type="text" size="25" value="' . $value . '"/>';
    }

    static function field_timeFormat() {
        $value = self::$options['timeFormat'];
        echo '<input name="ibs_calendar_options[timeFormat]" type="text" size="25" value="' . $value . '"/>';
    }

    static function field_timeZone() {
        $value = self::$options['timeZone'];
        echo '<input name="ibs_calendar_options[timeZone]" type="text" size="25" value="' . $value . '"/>';
    }

    static function field_headerLeft() {
        $value = self::$options['headerLeft'];
        echo '<input id="ibs-header-left" name="ibs_calendar_options[headerLeft]" type="text" size="100" value="' . $value . '"/><a href="#" id="ibs-header-left-help">help</a>';
    }

    static function field_headerCenter() {
        $value = self::$options['headerCenter'];
        echo '<input id="ibs-header-center" name="ibs_calendar_options[headerCenter]" type="text" size="100" value="' . $value . '"/><a href="#" id="ibs-header-center-help">help</a>';
    }

    static function field_dayNamesShort() {
        $value = self::$options['dayNamesShort'];
        echo '<input id="ibs-day-names-short" name="ibs_calendar_options[dayNamesShort]" type="text" size="100" value="' . $value . '"/><a href="#" id="ibs-day-names-short-help">help</a>';
    }

    static function field_hiddenDays() {
        $value = self::$options['hiddenDays'];
        echo '<input id="ibs-hiddendays" name="ibs_calendar_options[hiddenDays]" type="text" size="100" value="' . $value . '"/><a href="#" id="ibs-hiddendays-help">help</a>';
    }

    static function field_headerRight() {
        $value = self::$options['headerRight'];
        echo '<input id="ibs-header-right" name="ibs_calendar_options[headerRight]" type="text" size="100" value="' . $value . '"/><a href="#" id="ibs-header-right-help">help</a>';
    }

    static function field_firstDay() {
        $value = self::$options['firstDay'];
        echo '<select name="ibs_calendar_options[firstDay]" value="' . $value . '" />';
        $selected = self::$options['firstDay'] == "0" ? 'selected' : '';
        echo '<option value="0" ' . $selected . '>Sunday</option>';
        $selected = self::$options['firstDay'] == "1" ? 'selected' : '';
        echo '<option value="1" ' . $selected . '>Monday</option>';
        $selected = self::$options['firstDay'] == "2" ? 'selected' : '';
        echo '<option value="2" ' . $selected . '>Tuesday</option>';
        $selected = self::$options['firstDay'] == "3" ? 'selected' : '';
        echo '<option value="3" ' . self::$options['firstDay'] == "3" ? 'checked' : '' . '>Wednesday</option>' . "\n";
        $selected = self::$options['firstDay'] == "4" ? 'selected' : '';
        echo '<option value="4" ' . $selected . '>Thursday</option>';
        $selected = self::$options['firstDay'] == "5" ? 'selected' : '';
        echo '<option value="5" ' . $selected . '>Friday</option>';
        $selected = self::$options['firstDay'] == "6" ? 'selected' : '';
        echo '<option value="6" ' . $selected . '>Saturday</option>';
        echo '</select>';
    }

    static function colors($feed, $color) {
        $template = array(
            '<div class="feed-color-box %c" rel="%f" style="background-color:#5484ed;"></div>',
            '<div class="feed-color-box %c" rel="%f" style="background-color:#a4bdfc;"></div>',
            '<div class="feed-color-box %c" rel="%f" style="background-color:#46d6db;"></div>',
            '<div class="feed-color-box %c" rel="%f" style="background-color:#7ae7bf;"></div>',
            '<div class="feed-color-box %c" rel="%f" style="background-color:#51b749;"></div>',
            '<div class="feed-color-box %c" rel="%f" style="background-color:#fbd75b;"></div>',
            '<div class="feed-color-box %c" rel="%f" style="background-color:#ffb878;"></div>',
            '<div class="feed-color-box %c" rel="%f" style="background-color:#ff887c;"></div>',
            '<div class="feed-color-box %c" rel="%f" style="background-color:#dc2127;"></div>',
            '<div class="feed-color-box %c" rel="%f" style="background-color:#dbadff;"></div>',
            '<div class="feed-color-box %c" rel="%f" style="background-color:#e1e1e1;"></div>'
        );
        $cstr = $template;
        for ($str = 0; $str < count($cstr); $str++) {
            $cstr[$str] = str_replace('%f', $feed, $cstr[$str]);
            if (strpos($cstr[$str], $color) > 0) {
                $cstr[$str] = str_replace('%c', 'feed-color-box-selected', $cstr[$str]);
            } else {
                $cstr[$str] = str_replace('%c', '', $cstr[$str]);
            }
        }
        return implode('', $cstr);
    }

    static function field_feeds() {
        for ($feed = 1; $feed <= self::$options['feedCount']; $feed++) {
            $curr_feed = "feed_" . $feed;
            $bg = isset(self::$options['feeds'][$curr_feed]['backgroundColor']) ? self::$options['feeds'][$curr_feed]['backgroundColor'] : '#5484ed';
            $fg = isset(self::$options['feeds'][$curr_feed]['textColor']) ? self::$options['feeds'][$curr_feed]['textColor'] : '#ffffff';
            $color = "style='background-color:$bg; color:$fg;'";
            $value = isset(self::$options['feeds'][$curr_feed]['name']) ? self::$options['feeds'][$curr_feed]['name'] : '';
            echo "<div class='ibs-admin-bar' ><span>&nbsp;Feed $feed</span></div>";
            echo "<div class='feed-div'><span>Name</span><input id='ibs-feed-name-$feed' name='ibs_calendar_options[feeds][$curr_feed][name]' type='text' placeholder='feed name' size='25' " . $color . " value='$value' />" . self::colors($feed, $bg) . "</div>";
            $checked = isset(self::$options['feeds'][$curr_feed]['enabled']) && self::$options['feeds'][$curr_feed]['enabled'] == 'yes' ? 'checked' : '';
            echo "<div class='feed-div'><span>Enabled</span><input name='ibs_calendar_options[feeds][$curr_feed][enabled]' value='yes' $checked type='checkbox'/></div>";
            $value = isset(self::$options['feeds'][$curr_feed]['url']) ? self::$options['feeds'][$curr_feed]['url'] : '';
            echo "<div class='feed-div' ><span>ID</span><input id='ibs-feed-url-$feed'name='ibs_calendar_options[feeds][$curr_feed][url]' type='text' placeholder='Google Calendar Address (XML or Calendar ID)' size='100' value='$value' /></div>";
            $value = isset(self::$options['feeds'][$curr_feed]['key']) ? self::$options['feeds'][$curr_feed]['key'] : '';
            echo "<div class='feed-div' ><span>Key</span><input id='ibs-feed-key-$feed'name='ibs_calendar_options[feeds][$curr_feed][key]' type='text' placeholder='Optional Google API Key' size='100' value='$value' /></div>";
            $value = isset(self::$options['feeds'][$curr_feed]['color']) ? self::$options['feeds'][$curr_feed]['textColor'] : '#ffffff';
            echo "<input id='colorpicker-fg-$feed' type='hidden' feed='#ibs-feed-url-$feed' css='color' name='ibs_calendar_options[feeds][$curr_feed][textColor]' value='" . $value . "' />";
            $value = isset(self::$options['feeds'][$curr_feed]['backgroundColor']) ? self::$options['feeds'][$curr_feed]['backgroundColor'] : '#5484ed';
            echo "<input id='colorpicker-bg-$feed'type='hidden' class='ibs-colorpicker' feed='#ibs-feed-url-$feed' css='background-color' name='ibs_calendar_options[feeds][$curr_feed][backgroundColor]' value='$value' />";
            echo "<div style='width:100%; height:20px; margin-bottom:30px';> </div>";
        }
    }

    static function field_defaultView() {
        $value = self::$options['defaultView'];
        echo '<select name="ibs_calendar_options[defaultView]" value="' . $value . '" />';
        $selected = self::$options['defaultView'] == "month" ? 'selected' : '';
        echo '<option value="month" ' . $selected . '>Month</option>';
        $selected = self::$options['defaultView'] == "week" ? 'selected' : '';
        echo '<option value="week" ' . $selected . '>Week</option>';
        $selected = self::$options['defaultView'] == "day" ? 'selected' : '';
        echo '<option value="day" ' . $selected . '>Day</option>';
        echo '</select>';
    }

    static function field_eventLimitClick() {
        $value = self::$options['eventLimitClick'];
        echo '<select name="ibs_calendar_options[eventLimitClick]" value="' . $value . '" />';
        $selected = self::$options['eventLimitClick'] == "popover" ? 'selected' : '';
        echo '<option value="none" ' . $selected . '>popover</option>';
        $selected = self::$options['eventLimitClick'] == "week" ? 'selected' : '';
        echo '<option value="week" ' . $selected . '>week</option>';
        $selected = self::$options['eventLimitClick'] == "day" ? 'selected' : '';
        echo '<option value="day" ' . $selected . '>day</option>';
        echo '</select>';
    }

    static function field_aspectRatio() {
        $value = self::$options['aspectRatio'];
        echo '<input name="ibs_calendar_options[aspectRatio]" min="0.1" max="5.0" step="0.1" type="number" value="' . $value . '" />';
    }

    //==================================================================================================================================

    static function field_qtip_rounded() {
        $checked = self::$options['qtip']['shadow'] ? "checked" : '';
        echo '<input type="checkbox" name="ibs_calendar_options[qtip][shadow]" value="qtip-rounded"' . $checked . '/>';
    }

    static function field_qtip_shadow() {
        $checked = self::$options['qtip']['rounded'] ? "checked" : '';
        echo '<input type="checkbox" name="ibs_calendar_options[qtip][rounded]" value="qtip-shadow"' . $checked . '/>';
    }

    static function field_qtip_style() {
        echo "<select name='ibs_calendar_options[qtip][style]'> ";
        $value = self::$options['qtip']['style'];
        $selected = $value === '' ? "selected" : '';
        echo "<option id='qtip-none'     $selected  value=''  selected >none</option>";
        $selected = $value === 'qtip-light' ? "selected" : '';
        echo "<option id='qtip-light'    $selected value='qtip-light' >light coloured style</option>";
        $selected = $value === 'qtip-dark' ? "selected" : '';
        echo "<option id='qtip-dark'     $selected value='qtip-dark' >dark style</option>";
        $selected = $value === 'qtip-cream' ? "selected" : '';
        echo "<option id='qtip-cream'    $selected value='qtip-cream' >cream</option>";
        $selected = $value === 'qtip-red' ? "selected" : '';
        echo "<option id='qtip-red'      $selected value='qtip-red' >Alert-ful red style </option>";
        $selected = $value === 'qtip-green' ? "selected" : '';
        echo "<option id='qtip-green'   $selected value='qtip-green' >Positive green style </option>";
        $selected = $value === 'qtip-blue' ? "selected" : '';
        echo "<option id='qtip-blue'     $selected value='qtip-blue' >Informative blue style </option>";
        $selected = $value === 'qtip-bootstrap' ? "selected" : '';
        echo "<option id='qtip-bootstrap'$selected value='qtip-bootstrap' >Twitter Bootstrap style </option>";
        $selected = $value === 'qtip-youtube' ? "selected" : '';
        echo "<option id='qtip-youtube'  $selected value='qtip-youtube' >Google's new YouTube style</option>";
        $selected = $value === 'qtip-tipsy' ? "selected" : '';
        echo "<option id='qtip-tipsy'    $selected value='qtip-tipsy' >Minimalist Tipsy style </option>";
        $selected = $value === 'qtip-tipped' ? "selected" : '';
        echo "<option id='qtip-tipped'   $selected value='qtip-tipped' >Tipped libraries</option>";
        $selected = $value === 'qtip-jtools' ? "selected" : '';
        echo "<option id='qtip-jtools'   $selected value='qtip-jtools' >Tools tooltip style </option>";
        $selected = $value === 'qtip-cluetip' ? "selected" : '';
        echo "<option id='qtip-cluetip'  $selected value='qtip-cluetip' >Good ole'' ClueTip style </option>";
        echo "</select>";
    }

    static function admin_add_page() {
        add_options_page('IBS Calendar', 'IBS Calendar', 'manage_options', 'ibs_calendar', array(__CLASS__, 'admin_options_page'));
    }

    static function fix_args(&$args) {
        if (isset($args['hiddenDays'])) {
            if ($args['hiddenDays'] === '') {
                $args['hiddenDays'] = null;
            } else {
                $args['hiddenDays'] = explode(',', $args['hiddenDays']);
                for ($i = 0; $i < count($args['hiddenDays']); $i++) {
                    $args['hiddenDays'][$i] = (int) $args['hiddenDays'][$i];
                }
            }
        }
        if (isset($args['dayNamesShort'])) {
            if ($args['dayNamesShort'] === '') {
                $args['dayNamesShort'] = null;
            } else {
                $args['dayNamesShort'] = explode(',', $args['dayNamesShort']);
            }
        }
    }

    static function admin_options_page() {
        $args = self::$options;
        self::fix_args($args);
        $args['id'] = '1';
        $args['ajaxData'] = array("action" => "ibs_calendar_ajax", "type" => "event");
        $args['ajaxUrl'] = admin_url("admin-ajax.php");
        include( 'lib/admin-html.php');
    }

    static function handle_shortcode($atts, $content = null) {
        self::$add_script += 1;
        $args = self::$options;
        if (is_array($atts)) {
            foreach ($args as $key => $value) {
                if (isset($atts[strtolower($key)])) {
                    if ($key === 'feeds') {
                        $keep = explode(',', $atts['feeds']);
                        for ($i = 1; self::$options['feedCount'] >= $i; $i++) {
                            $index = (string) $i;
                            if (false == in_array($index, $keep)) {
                                $args['feeds']['feed_' . $i]['enabled'] = false;
                            }
                        }
                    } else {
                        $args[$key] = $atts[strtolower($key)];
                    }
                }
            }
        }

        if (isset($atts['repeats'])) {
            $args['list_repeat'] = strtolower($atts['repeats']) === 'yes' ? true : false;
        }
        if (isset($atts['past'])) {
            $args['list_past'] = strtolower($atts['past']) === 'yes' ? true : false;
        }
        if (isset($atts['max'])) {
            $args['list_max'] = strtolower($atts['max']) === 'yes' ? true : false;
        }

        self::fix_args($args);
        $args['ajaxData'] = array("action" => "ibs_calendar_ajax", "type" => "event");
        $args['ajaxUrl'] = admin_url("admin-ajax.php");
        $args['id'] = self::$add_script;
        $id = self::$add_script;

        $html = '<div id="ibs-calendar-id" class="' . $args['align'] . '" style="width:%w;" >
            <form id="fullcalendar-id" >
                <div id="ibs-loading-id" ></div>
            </form>
            <div>
                <input id="list-display-id" type="checkbox" style="margin : 10px;" />
                <span> &nbsp;Event List</span>
                <div id="event-list-id" >
                    <table id="event-table-id" style="width:100%;" >
                        <tbody> 
                        </tbody>
                    </table>
                </div>       
            </div>
        </div>';
        $html = str_replace('-id', '-' . $id, $html);
        $html = str_replace('%w', $args['width'], $html);
        ob_start();
        echo $html;
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                new CalendarObj(jQuery, <?PHP echo json_encode($args); ?>, 'shortcode');
            });
        </script> 
        <?PHP
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }

    static function register_script() {
        $min = self::$options['debug'] ? '' : '.min';
        $theme = self::$options['ui_theme'];
        wp_register_style('ibs-calendar-ui-theme-style', plugins_url("css/jquery-ui-themes-1.11.1/themes/$theme/jquery-ui.min.css", __FILE__));

        wp_register_script('ibs-calendar-script', plugins_url("js/calendar$min.js", __FILE__), self::$core_handles);
        wp_register_script('ibs-moment-script', plugins_url("js/moment$min.js", __FILE__));

        wp_register_style('ibs-fullcalendar-style', plugins_url("js/fullcalendar-2.1.1/fullcalendar.min.css", __FILE__));
        wp_register_style('ibs-fullcalendar-print-style', plugins_url("js/fullcalendar-2.1.1/fullcalendar.print.min.css", __FILE__));
        wp_register_script('ibs-fullcalendar-script', plugins_url("js/fullcalendar-2.1.1/fullcalendar.min.js", __FILE__));
        wp_register_script('ibs-fullcalendar-gcal-script', plugins_url("js/fullcalendar-2.1.1/gcal.js", __FILE__));
        wp_register_script('ibs-fullcalendar-lang-all-script', plugins_url("js/fullcalendar-2.1.1/lang-all.js", __FILE__));

        wp_register_style('ibs-qtip_style', plugins_url("css/jquery.qtip.css", __FILE__));
        wp_register_script('ibs-qtip-script', plugins_url("js/jquery.qtip.min.js", __FILE__));

        wp_register_style('ibs-admin-style', plugins_url("css/admin.css", __FILE__));
        wp_register_script('ibs-admin-script', plugins_url("js/admin$min.js", __FILE__));

        wp_register_style("ibs-dropdown-style", plugins_url("js/jquery.dropdown/jquery.dropdown.css", __FILE__));
        wp_register_script("ibs-dropdown-script", plugins_url("js/jquery.dropdown/jquery.dropdown.min.js", __FILE__));

        wp_register_script('rrule-rrule-script', plugins_url("js/rrule$min.js", __FILE__));
    }

    static $core_handles = array(
        'jquery',
        'json2',
        'jquery-ui-core',
        'jquery-ui-dialog',
        'jquery-ui-datepicker',
        'jquery-ui-widget',
        'jquery-ui-sortable',
        'jquery-ui-draggable',
        'jquery-ui-droppable',
        'jquery-ui-selectable',
        'jquery-ui-position',
        'jquery-ui-tabs',
    );
    static $script_handles = array(
        'rrule-rrule-script',
        'ibs-calendar-script',
        'ibs-moment-script',
        'ibs-fullcalendar-script',
        'ibs-fullcalendar-gcal-script',
        'ibs-fullcalendar-lang-all-script',
        'ibs-qtip-script'
    );
    static $style_handles = array(
        'ibs-calendar-ui-theme-style',
        'ibs-fullcalendar-style',
        'ibs-qtip_style'
    );

    static function enqueue_scripts() {
        foreach (self::$core_handles as $handle) {
            wp_enqueue_script($handle);
        }
        if (is_active_widget('', '', 'ibs_wcalendar', true)) {
            self::print_admin_scripts();
            wp_enqueue_style(self::$style_handles);
            wp_enqueue_script(self::$script_handles);
        }
    }

    static function admin_enqueue_scripts($page) {
        if ($page === 'settings_page_ibs_calendar') {

            wp_enqueue_style(self::$style_handles);
            wp_enqueue_script(self::$script_handles);
            wp_enqueue_style("ibs-dropdown-style");
            wp_enqueue_script("ibs-dropdown-script");

            wp_enqueue_style('ibs-admin-style');
            wp_enqueue_script('ibs-admin-script');

            wp_enqueue_style('ibs-calendar-event-style');
            wp_enqueue_script('ibs-calendar-event-script');

            wp_enqueue_style('ibs-timepicker-style');
            wp_enqueue_script('ibs-timepicker-script');
        }
    }

    static function print_admin_scripts() {
        ?>
        <?PHP
    }

    static function print_script_header() {
        
    }

    static $script_handles_gcal_list = array(
        'ibs-gcal-events-script',
        'ibs-moment-script'
    );
    static $style_handles_gcal_list = array(
        'ibs-gcal-events-style'
    );

    static function print_script_footer() {
        if (self::$add_script > 0) {
            self::print_admin_scripts();
            wp_print_styles(self::$style_handles);
            wp_print_scripts(self::$script_handles);
        }
        if (self::$add_script_gcal_list > 0) {
            wp_print_styles(self::$style_handles_gcal_list);
            wp_print_scripts(self::$script_handles_gcal_list);
        }
    }

    static function get_ibs_events() {
        Global $post;
        $query_args = array(
            'post_type' => 'ibs_event',
            'posts_per_page' => 9999,
            'post_status' => 'publish',
            'ignore_sticky_posts' => true,
            'meta_query' => ''
        );
        $result = array();
        $events = new WP_Query($query_args);
        while ($events->have_posts()) {
            $events->the_post();
            $item = array(
                'id' => 'IBS-' . $post->ID,
                'title' => get_the_title($post->ID),
                'start' => get_post_meta($post->ID, 'ibs-event-start', true),
                'end' => get_post_meta($post->ID, 'ibs-event-end', true),
                'allDay' => get_post_meta($post->ID, 'ibs-event-allday', true),
                'color' => get_post_meta($post->ID, 'ibs-event-color', true),
                'repeat' => get_post_meta($post->ID, 'ibs-event-repeat', true),
                'recurr' => get_post_meta($post->ID, 'ibs-event-recurr', true),
                'exceptions' => get_post_meta($post->ID, 'ibs-event-exceptions', true),
                'url' => get_the_permalink($post->ID),
                'description' => get_the_excerpt()
            );
            if (false === $item['recurr']) {
                $item['repeat'] = null;
                $item['exceptions'] = null;
            }
            $result[] = $item;
        }
        echo json_encode($result);
        exit;
    }

}

IBS_CALENDAR::init();
include( 'lib/widget-ibs-calendar.php' );
