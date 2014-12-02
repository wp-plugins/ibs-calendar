<?php
/*
  Plugin Name: IBS Calendar
  Plugin URI: http://wordpress.org/extend/plugins/
  Description: implements FullCalendar for Wordpress Adimin and shortcode.
  Author: Harry Moore
  Version: 0.3
  Author URI: http://indianbendsolutions.com
  License: none
  License URI: none
 */

/*
  This program is distributed in the hope that it will be useful, but
  WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

define('IBS_CALENDAR_VERSION', '0.1');
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
    static $debug = false;
    static $ui_theme = "cupertino";
    static $options = array();

    static function init() {
        //self::defaults();  //development add new options
        self::$options = get_option('ibs_calendar_options');
        self::$debug = self::$options['debug'] === 'yes';
        self::$ui_theme = self::$options['ui_theme'];
        self::$options['debug'] = self::$options['debug'] === 'yes';
        self::$options['weekends'] = isset(self::$options['weekends']) ? true : false;
        self::$options['theme'] = isset(self::$options['theme']) ? true : false;
        self::$options['editable'] = isset(self::$options['editable']) ? true : false;
        add_action('admin_init', array(__CLASS__, 'admin_options_init'));
        add_action('admin_menu', array(__CLASS__, 'admin_add_page'));
        add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'admin_enqueue_scripts'));
        add_shortcode('ibs-calendar', array(__CLASS__, 'handle_shortcode'));
        add_action('init', array(__CLASS__, 'register_script'));
        add_action('wp_head', array(__CLASS__, 'print_script_header'));
        add_action('wp_footer', array(__CLASS__, 'print_script_footer'));
        add_action('admin_print_scripts', array(__CLASS__, 'print_admin_scripts'));
        add_action('wp_ajax_ibs_calendar', array('__CLASS__, ', 'ajax'));
        add_action('wp_ajax_nopriv_ibs_calendar', array(__CLASS__, 'ajax'));
    }

    static function defaults() { //jason_encode requires double quotes
        $options = (array) get_option('ibs_calendar_options');
        $arr = array(
            "debug" => "no",
            "ui_theme" => "cupertino",
            "event_list" => "none",
            "feedCount" => 3,
            "theme" => "yes",
            "width" => "100%",
            "firstDay" => "1",
            "weekends" => "yes",
            "lang" => "en_us",
            "ui_theme_css" => "cupertino",
            "timeFormat" => "HH:mm",
            "defaultView" => "month",
            "eventLimit" => "yes",
            "eventLimitClick" => "popover",
            "aspectRatio" => 1.0,
            "editable" => "no",
            "feeds" => array(
                "feed_1" => array('name' => 'google holidays', 'enabled' => 'yes', 'url' => 'https://www.google.com/calendar/feeds/en.usa%23holiday%40group.v.calendar.google.com/public/basic', 'text_color' => 'white', 'background_color' => 'blue'),
                "feed_2" => array('name' => '', 'enabled' => 'no', 'url' => '', 'text_color' => 'white', 'background_color' => 'blue'),
                "feed_3" => array('name' => '', 'enabled' => 'no', 'url' => '', 'text_color' => 'white', 'background_color' => 'blue')),
            "headerLeft" => 'prevYear,prev,next,nextYear today',
            "headerCenter" => 'title',
            "headerRight" => 'month agendaWeek agendaDay',
            "qtip" => array('style' => "qtip-bootstrap", 'rounded' => 'qtip-rounded', 'shadow' => 'qtip-shadow')
        );
        foreach ($arr as $key => $value) {
            if (!isset($options[$key])) {
                $options[$key] = $value;
            }
        }
        self::$options = $options;
        update_option('ibs_calendar_options', $options);
    }

    static function admin_options_init() {
        register_setting('ibs_calendar_options', 'ibs_calendar_options');
        add_settings_section('calendar-section-general', '', array(__CLASS__, 'admin_general_header'), 'calendar-general');
        add_settings_field('debug', 'debug', array(__CLASS__, 'field_debug'), 'calendar-general', 'calendar-section-general');
        add_settings_field('ui_theme', 'ui theme', array(__CLASS__, 'field_ui_theme'), 'calendar-general', 'calendar-section-general');
        add_settings_field('width', 'calendar width', array(__CLASS__, 'field_width'), 'calendar-general', 'calendar-section-general');
        add_settings_field('event_list', 'event list', array(__CLASS__, 'field_event_list'), 'calendar-general', 'calendar-section-general');

        add_settings_section('section_fullcalendar', '', array(__CLASS__, 'admin_options_header'), 'fullcalendar');
        add_settings_field('aspectRatio', 'aspectRatio', array(__CLASS__, 'field_aspectRatio'), 'fullcalendar', 'section_fullcalendar');
        add_settings_field('defaultView', 'defaultView', array(__CLASS__, 'field_defaultView'), 'fullcalendar', 'section_fullcalendar');
        add_settings_field('editable', 'editable', array(__CLASS__, 'field_editable'), 'fullcalendar', 'section_fullcalendar');
        add_settings_field('eventLimit', 'eventLimit', array(__CLASS__, 'field_eventLimit'), 'fullcalendar', 'section_fullcalendar');
        add_settings_field('eventLimitClick', 'eventLimitClick', array(__CLASS__, 'field_eventLimitClick'), 'fullcalendar', 'section_fullcalendar');
        add_settings_field('firstDay', 'firstDay', array(__CLASS__, 'field_firstDay'), 'fullcalendar', 'section_fullcalendar');
        add_settings_field('headerCenter', 'headerCenter', array(__CLASS__, 'field_headerCenter'), 'fullcalendar', 'section_fullcalendar');
        add_settings_field('headerLeft', 'headerLeft', array(__CLASS__, 'field_headerLeft'), 'fullcalendar', 'section_fullcalendar');
        add_settings_field('headerRight', 'headerRight', array(__CLASS__, 'field_headerRight'), 'fullcalendar', 'section_fullcalendar');
        add_settings_field('theme', 'use ui theme', array(__CLASS__, 'field_theme'), 'fullcalendar', 'section_fullcalendar');
        add_settings_field('timeFormat', 'timeFormat', array(__CLASS__, 'field_timeFormat'), 'fullcalendar', 'section_fullcalendar');
        add_settings_field('weekends', 'weekends', array(__CLASS__, 'field_weekends'), 'fullcalendar', 'section_fullcalendar');


        add_settings_section('section_feeds', '', array(__CLASS__, 'admin_feeds_header'), 'feeds');
        add_settings_field('feedCount', 'event feed count', array(__CLASS__, 'field_feedCount'), 'feeds', 'section_feeds');
        add_settings_field('feeds', 'event feeds', array(__CLASS__, 'field_feeds'), 'feeds', 'section_feeds');

        add_settings_section('section_qtip', '', array(__CLASS__, 'admin_qtip_header'), 'qtip');
        add_settings_field('classes', '', array(__CLASS__, 'field_qtip_classes'), 'qtip', 'section_qtip');
    }

    static function admin_general_header() {
        echo '<div class="ibs-admin-bar">General settings</div>';
    }

    static function admin_options_header() {
        echo '<div class="ibs-admin-bar" >FullCalendar options</div>';
    }

    static function admin_qtip_header() {
        echo '<div class="ibs-admin-bar" >Tooltip options</div>';
    }

    static function admin_feeds_header() {
        echo '<div class="ibs-admin-bar" >Calendar feeds</div>';
    }

    static function field_feedCount() {
        $value = self::$options['feedCount'];
        echo '<input name="ibs_calendar_options[feedCount]" min="1" max="10" step="1" placeholder="number of feeds" type="number" value="' . $value . '" />';
    }

    static function field_debug() {
        $checked = self::$debug ? "checked" : '';
        echo '<p>determines whether to use minimized javascript</p>';
        echo '<input type="radio" name="ibs_calendar_options[debug]" value="yes"' . $checked . '/>&nbspYes&nbsp&nbsp';
        $checked = self::$debug ? '' : "checked";
        echo '<input type="radio" name="ibs_calendar_options[debug]" value="no"' . $checked . '/>&nbspNo';
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

    static function field_weekends() {
        $checked = self::$options['weekends'] ? "checked" : '';
        echo "<input type='checkbox' name='ibs_calendar_options[weekends]' value=1 $checked '/>";
    }

    static function field_theme() {
        $checked = self::$options['theme'] ? "checked" : '';
        echo "<input type='checkbox' name='ibs_calendar_options[theme]' value=1 $checked />";
    }

    static function field_editable() {
        $checked = self::$options['editable'] ? "checked" : '';
        echo "<input type='checkbox' name='ibs_calendar_options[editable]'  value=1 $checked />";
    }

    static function field_eventLimit() {
        $value = self::$options['eventLimit'];
        echo "<input id='ibs-event-limit'  type='text' name='ibs_calendar_options[eventLimit]'  value='$value'  /><a href='#' id='ibs-event-limit-help'>help</a>";
    }

    static function field_width() {
        $value = self::$options['width'];
        echo '<input name="ibs_calendar_options[width]" type="text" size="25" value="' . $value . '"/>';
    }

    static function field_timeFormat() {
        $value = self::$options['timeFormat'];
        echo '<input name="ibs_calendar_options[timeFormat]" type="text" size="25" value="' . $value . '"/>';
    }

    static function field_headerLeft() {
        $value = self::$options['headerLeft'];
        echo '<input id="ibs-header-left" name="ibs_calendar_options[headerLeft]" type="text" size="100" value="' . $value . '"/><a href="#" id="ibs-header-left-help">help</a>';
    }

    static function field_headerCenter() {
        $value = self::$options['headerCenter'];
        echo '<input id="ibs-header-center" name="ibs_calendar_options[headerCenter]" type="text" size="100" value="' . $value . '"/><a href="#" id="ibs-header-center-help">help</a>';
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

    static function field_feeds() {
        for ($feed = 1; $feed <= self::$options['feedCount']; $feed++) {
            $curr_feed = "feed_" . $feed;
            $value = isset(self::$options['feeds'][$curr_feed]['name']) ? self::$options['feeds'][$curr_feed]['name'] : '';
            echo "<div class='ibs-admin-bar' ><span>&nbsp;Feed $feed</span></div>";
            echo "<div class='feed-div'><span>Name</span><input id='ibs-feed-name-$feed' name='ibs_calendar_options[feeds][$curr_feed][name]' type='text' placeholder='feed name' size='25' value='$value' /></div>";
            $checked = isset(self::$options['feeds'][$curr_feed]['enabled']) && self::$options['feeds'][$curr_feed]['enabled'] == 'yes' ? 'checked' : '';
            echo "<div class='feed-div'><span>Enabled</span><input name='ibs_calendar_options[feeds][$curr_feed][enabled]' value='yes' $checked type='checkbox'/></div>";
            $value = isset(self::$options['feeds'][$curr_feed]['url']) ? self::$options['feeds'][$curr_feed]['url'] : '';
            echo "<div class='feed-div' ><span>Address</span><input id='ibs-feed-url-$feed'name='ibs_calendar_options[feeds][$curr_feed][url]' type='text' placeholder='Google Calendar Address (XML or Calendar ID)' size='100' value='$value' /></div>";
            $value = isset(self::$options['feeds'][$curr_feed]['key']) ? self::$options['feeds'][$curr_feed]['key'] : '';
            echo "<div class='feed-div' ><span>Key</span><input id='ibs-feed-key-$feed'name='ibs_calendar_options[feeds][$curr_feed][key]' type='text' placeholder='Google API Key' size='100' value='$value' /></div>";
            $value = isset(self::$options['feeds'][$curr_feed]['textColor']) ? self::$options['feeds'][$curr_feed]['textColor'] : 'black';
            echo '<div class="feed-div" ></div>';
            echo "<span class='feed-letter'>Letters</span><input id='colorpicker-fg-$feed' class='ibs-colorpicker' feed='#ibs-feed-url-$feed' css='color' name='ibs_calendar_options[feeds][$curr_feed][textColor]' value='$value' />";
            $value = isset(self::$options['feeds'][$curr_feed]['backgroundColor']) ? self::$options['feeds'][$curr_feed]['backgroundColor'] : 'white';
            echo "<span class='feed-background'>Background</span><input id='colorpicker-bg-$feed' class='ibs-colorpicker' feed='#ibs-feed-url-$feed' css='background-color' name='ibs_calendar_options[feeds][$curr_feed][backgroundColor]' value='$value' />";
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

//======================================================================================================================================
    static function field_qtip_classes() {
        $html = "<div></div>"
                . "<table class='qtip-table'><tbody>"
                . "<tr><td style='font-weight:bold;'>CSS3 classes </td><td></td></tr>"
                . "<tr><td> <input id='qtip-rounded' type='checkbox' value='qtip-rounded' name='ibs_calendar_options[qtip][rounded]'/><i>qtip-rounded</i></td><td>CSS3 border-radius class for applying rounded corners to your tooltips </td></tr>"
                . "<tr><td> <input id='qtip-shadow' type='checkbox' value='qtip-shadow' name='ibs_calendar_options[qtip][shadow]'/><i>qtip-shadow</i></td><td>CSS3 box-shadow class for applying shadows to your tooltips </td></tr>"
                . "<tr><td style='font-weight:bold;'>Styles </td><td></td></tr>"
                . "<tr><td> <input id='qtip-none' type='radio' value='' name='ibs_calendar_options[qtip][style]' checked /><i>none</i></td><td></td></tr>"
                . "<tr><td> <input id='qtip-light' type='radio' value='qtip-light' name='ibs_calendar_options[qtip][style]'/><i>qtip-light</i></td><td> light coloured style</td></tr>"
                . "<tr><td> <input id='qtip-dark'  type='radio' value='qtip-dark' name='ibs_calendar_options[qtip][style]'/><i>qtip-dark</i></td><td>dark style</td></tr>"
                . "<tr><td> <input id='qtip-cream' type='radio' value='qtip-cream' name='ibs_calendar_options[qtip][style]'/><i>qtip-cream</i></td><td>cream</td></tr>"
                . "<tr><td> <input id='qtip-red' type='radio' value='qtip-red' name='ibs_calendar_options[qtip][style]'/><i>qtip-red</i></td><td>Alert-ful red style </td></tr>"
                . "<tr><td> <input id='qtip-greent' type='radio' value='qtip-green' name='ibs_calendar_options[qtip][style]'/><i>qtip-green</i></td><td>Positive green style </td></tr>"
                . "<tr><td> <input id='qtip-blue' type='radio' value='qtip-blue' name='ibs_calendar_options[qtip][style]'/><i>qtip-blue</i></td><td>Informative blue style </td></tr>"
                . "<tr><td> <input id='qtip-bootstrap' type='radio' value='qtip-bootstrap' name='ibs_calendar_options[qtip][style]'/><i>qtip-bootstrap</i></td><td>Twitter Bootstrap style </td></tr>"
                . "<tr><td> <input id='qtip-youtube' type='radio' value='qtip-youtube' name='ibs_calendar_options[qtip][style]'/><i>qtip-youtube</i></td><td>Google's new YouTube style</td></tr>"
                . "<tr><td> <input id='qtip-tipsy' type='radio' value='qtip-tipsy' name='ibs_calendar_options[qtip][style]'/><i>qtip-tipsy</i></td><td>Minimalist Tipsy style </td></tr>"
                . "<tr><td> <input id='qtip-tipped' type='radio' value='qtip-tipped' name='ibs_calendar_options[qtip][style]'/><i>qtip-tipped</i></td><td>Tipped libraries</td></tr>"
                . "<tr><td> <input id='qtip-jtools' type='radio' value='qtip-jtools' name='ibs_calendar_options[qtip][style]'/><i>qtip-jtools</i></td><td>jTools tooltip style </td></tr>"
                . "<tr><td> <input id='qtip-cluetip' type='radio' value='qtip-cluetip' name='ibs_calendar_options[qtip][style]'/><i>qtip-cluetip</i></td><td>Good ole' ClueTip style </td></tr>"
                . "</tbody></table><br/>";
        if (isset(self::$options['qtip']['style'])) {
            $value = str_replace('_', '-', self::$options['qtip']['style']);
            $html = str_replace("id='$value'", "id='$value' checked ", $html);
        } else {
            $html = str_replace("id='none'", "id='none' checked ", $html);
        }
        if (isset(self::$options['qtip']['rounded'])) {
            $value = 'qtip-rounded';
            $html = str_replace("id='$value'", "id='$value' checked ", $html);
        }
        if (isset(self::$options['qtip']['shadow'])) {
            $value = 'qtip-shadow';
            $html = str_replace("id='$value'", "id='$value' checked ", $html);
        }
        echo $html;
        echo "<div><input id='test-qtip' type='text' style='width:600px' value='$value'/></div>";
    }

    //=====================================================================================================================================    
    static function admin_add_page() {
        add_options_page('IBS Calendar', 'IBS Calendar', 'manage_options', 'ibs_calendar', array(__CLASS__, 'admin_options_page'));
    }

    static function admin_options_page() {
        $args = self::$options;
        $args['id'] = '1';
        $args['ajaxData'] = array("action" => "ibs_calendar_ajax", "type" => "event");
        $args['ajaxUrl'] = admin_url("admin-ajax.php");
        ?>
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
                                    IBSCALENDAR = new CalendarObj(jQuery, <?PHP echo json_encode($args); ?>);
                                });
                                break;
                            case 'Options':
                                $('#dropdown-event-limit').removeClass('ibs-shortcode').addClass('ibs-options');

                                $('#ibs-sc-limit-help').dropdown('detach', '#dropdown-event-limit');
                                $('#ibs-event-limit-help').dropdown('attach', '#dropdown-event-limit');

                                $('#ibs-sc-left-help').dropdown('detach', '#dropdown-header-left');
                                $('#ibs-sc-right-help').dropdown('detach', '#dropdown-header-right');
                                $('#ibs-sc-center-help').dropdown('detach', '#dropdown-header-center');

                                $('#ibs-header-left-help').dropdown('attach', '#dropdown-header-left');
                                $('#ibs-header-right-help').dropdown('attach', '#dropdown-header-right');
                                $('#ibs-header-center-help').dropdown('attach', '#dropdown-header-center');
                                break;
                            case 'Shortcode':
                                $('#dropdown-event-limit').removeClass('ibs-options').addClass('ibs-shortcode');

                                $('#ibs-header-left-help').dropdown('detach', '#dropdown-header-left');
                                $('#ibs-header-right-help').dropdown('detach', '#dropdown-header-right');
                                $('#ibs-header-center-help').dropdown('detach', '#dropdown-header-center');

                                $('#ibs-event-limit-help').dropdown('detach', '#dropdown-event-limit');
                                $('#ibs-sc-limit-help').dropdown('attach', '#dropdown-event-limit');

                                $('#ibs-sc-left-help').dropdown('attach', '#dropdown-header-left');
                                $('#ibs-sc-right-help').dropdown('attach', '#dropdown-header-right');
                                $('#ibs-sc-center-help').dropdown('attach', '#dropdown-header-center');
                                $('#shortcode-result').val('[ibs-calendar]')
                                break;
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
                    <div><span>width</span><input name="width" type="text" size="25" value=""/></div>
                    <div><span>aspectRatio</span><input name="aspectRatio" min="0.1" max="5.0" step="0.1" type="number"/></div>
                    <div><span>weekends</span><input type='checkbox' name='weekends' /></div>
                    <div><span>theme</span><input type='checkbox' name='theme' /></div>
                    <div><span>editable</span><input type='checkbox' name='editable'/></div>
                    <div><span>eventLimit</span><input id="ibs-sc-limit" type='text' name='eventLimit' /><a href="#" id="ibs-sc-limit-help">help</a></div>
                    <div><span>timeFormat</span><input name="timeformat" type="text" size="25" value=""/></div>
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
        <?php
    }

    static function handle_shortcode($atts, $content = null) {
        self::$add_script += 1;
        $args = self::$options;
        if (is_array($atts)) {
            foreach ($atts as $key => $value) {
                switch ($key) {
                    case 'width' : $args['width'] = $value;
                        break;
                    case 'weekends' : $args['weekends'] = $value;
                        break;
                    case 'theme' : $args['theme'] = $value;
                        break;
                    case 'editable' : $args['editable'] = $value;
                        break;
                    case 'eventlist': $args['event_list'] = $value;
                        break;
                    case 'timeformat' : $args['timeFormat'] = $value;
                        break;
                    case 'defaultview' : $args['defaultView'] = $value;
                        break;
                    case 'firstday' : $args['firstDay'] = $value;
                        break;
                    case 'headerleft' : $args['headerLeft'] = $value;
                        break;
                    case 'headercenter' : 'title';//$args['headerCenter'] = $value;
                        break;
                    case 'headerright' : $args['headerRight'] = $value;
                        break;
                    case 'eventlimit' : $args['eventLimit'] = $value;
                        break;
                    case 'eventlimitclick' : $args['eventLimitClick'] = $value;
                        break;
                    case 'aspectratio' : $args['aspectRatio'] = $value;
                        break;
                    case 'feeds' :
                        $keep = explode(',', $value);
                        for ($i = 1; self::$options['feedCount'] >= $i; $i++) {
                            $index = (string) $i;
                            if (false == in_array($index, $keep)) {
                                $args['feeds']['feed_' . $i]['url'] = '';
                            }
                        }
                        break;

                    default:
                        if (isset($args[$key])) {
                            $args[$key] = $value;
                        }
                }
            }
        }
        $args['ajaxData'] = array("action" => "ibs_calendar_ajax", "type" => "event");
        $args['ajaxUrl'] = admin_url("admin-ajax.php");
        $args['id'] = self::$add_script;
        $id = self::$add_script;
        $width = $args['width'];


        $html = '<div id="ibs-calendar-id" class="aligncenter" style="width:650px;" >
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
        $html = str_replace('width:650px;', 'width :' . $width . ';', $html);
        ob_start();
        echo $html;
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                new CalendarObj(jQuery, <?PHP echo json_encode($args); ?>);
            });
        </script> 
        <?PHP
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }

    static function register_script() {
        $min = self::$debug ? '' : '.min';
        $theme = self::$ui_theme;
        wp_register_style('ibs-calendar-ui-theme-style', plugins_url("css/jquery-ui-themes-1.11.1/themes/$theme/jquery-ui.min.css", __FILE__));

        wp_register_script('ibs-calendar-script', plugins_url("js/calendar$min.js", __FILE__), self::$core_handles);
        wp_register_script('ibs-moment-script', plugins_url("js/moment$min.js", __FILE__));

        wp_register_style('ibs-fullcalendar-style', plugins_url("js/fullcalendar-2.1.1/fullcalendar.min.css", __FILE__));
        wp_register_style('ibs-fullcalendar-print-style', plugins_url("js/fullcalendar-2.1.1/fullcalendar.print.min.css", __FILE__));
        wp_register_script('ibs-fullcalendar-script', plugins_url("js/fullcalendar-2.1.1/fullcalendar.min.js", __FILE__));
        wp_register_script('ibs-fullcalendar-gcal-script', plugins_url("js/fullcalendar-2.1.1/gcal.js", __FILE__));
        wp_register_script('ibs-fullcalendar-lang-all-script', plugins_url("js/fullcalendar-2.1.1/lang-all.js", __FILE__));

        wp_register_style('ibs-colorpicker-style', plugins_url('js/colorpicker/css/evol.colorpicker.min.css', __FILE__));
        wp_register_script('ibs-colorpicker-script', plugins_url('js/colorpicker/js/evol.colorpicker.min.js', __FILE__));

        wp_register_style('ibs-qtip_style', plugins_url("js/jquery.qtip.2.1.1/jquery.qtip.css", __FILE__));
        wp_register_script('ibs-qtip-script', plugins_url("js/jquery.qtip.2.1.1/jquery.qtip.min.js", __FILE__));

        wp_register_style('ibs-admin-style', plugins_url("css/admin.css", __FILE__));
        wp_register_script('ibs-admin-script', plugins_url("js/admin.js", __FILE__));

        wp_register_style("ibs-dropdown-style", plugins_url("js/jquery.dropdown/jquery.dropdown.css", __FILE__));
        wp_register_script("ibs-dropdown-script", plugins_url("js/jquery.dropdown/jquery.dropdown.min.js", __FILE__));
    }

    static $core_handles = array(
        'jquery',
        'json2',
        'jquery-ui-core',
        'jquery-ui-widget',
        'jquery-ui-sortable',
        'jquery-ui-draggable',
        'jquery-ui-droppable',
        'jquery-ui-selectable',
        'jquery-ui-position',
        'jquery-ui-tabs',
    );
    static $script_handles = array(
        'ibs-calendar-script',
        'ibs-moment-script',
        'ibs-fullcalendar-script',
        'ibs-fullcalendar-gcal-script',
        'ibs-fullcalendar-lang-all-script',
        'ibs-colorpicker-script',
        'ibs-qtip-script'
    );
    static $style_handles = array(
        'ibs-calendar-ui-theme-style',
        'ibs-fullcalendar-style',
        'ibs-colorpicker-style',
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
        }
    }

    static function print_admin_scripts() {
        ?>
        <?PHP
    }

    static function print_script_header() {
        
    }

    static function print_script_footer() {
        //load our stuff only if needed
        if (self::$add_script > 0) {
            self::print_admin_scripts();
            wp_print_styles(self::$style_handles);
            wp_print_scripts(self::$script_handles);
        }
    }

    static function ajax() {
        //add json event source logic here
        $events = array();
        echo json_encode($events);
    }

}

IBS_CALENDAR::init();
include( 'lib/widget-ibs-calendar.php' );
