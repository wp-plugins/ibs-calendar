=== IBS Calendar ===
Contributors: hmoore71
Donate link: https://indianbendsolutions.net/donate/
Plugin URI: https://indianbendsolutions.net/documentation/ibs-Calendar/
Author URI: https://indianbendsolutions.net/
Tags: google calendar, calendar, 
Requires at least: 4.0
Tested up to: 4.1
Stable tag: 0.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Implementation of Adam Shaw's Full Calendar for public Google Calendar feeds.

== Description ==
* IBS Calendar is a comprehensive implementation of the jQuery Full Calendar Version 2 from Adam Shaw. See http://fullcalendar.io/. 
* Define any practical number of Google Calendar feeds each having a different color scheme.
* Style tooltips using Qtip2 predefined styles.
* Shortcode generator to help customize individual shortcodes. 
* IBS Calendar Widget showing calendar and optionally an event list.
* Displays IBS Events plugin post-type ibs_event.
* See more at https://indianbendsolutions.net/documentation/ibs-Calendar/


Presently IBS Calendar is in its Beta phase of development and all testing and reporting of issues is appreciated.

== Installation ==
1. Download ibs-Calendar and unzip.
2. Upload `ibs-Calendar` folder to the Wordpress plugin directory
3. Activate the plugin through the ‘Plugins’ menu in WordPress
4 Admin | Settings menu | IBS Calendar and configure the plugin.

== Frequently Asked Questions ==
How do I get the Google Calendar feed address? 
1. Open your Google Calendar and on the left side bar click "My calendars" which should list all of your calendars.
2. To the right of your calendar name is a dropdown indicator; click it and a dialog will display.
3. Click "Calendar settings" and that will open a page with all of your calendar settings on it.
4. Towards the bottom are a set of three buttons XML(orange) ICAL(green) HTML(blue) and a Calendar ID. 
5. Click the XML(orange) and a dialog will open displaying the link to the calendar. Copy and paste this link into the IBS Calendar feed address field.
6. Alternatively you may also copy and paste the Calendar ID into the IBS Calendar Feed.

What is "Google API Key" ? Google requires every user of the Google Calendar feeds to have their own Google Calendar API Key. IBS Calendar has its own key that is shared with all users of the plugin.
If the use of the key gets too high (500,000 requests per day) the plugin may be denied access. At that point you may want to obtain another key.

Can I display event sources other than Google Calendar? Perhaps. If the event information conforms to Full Calendar expectations. As of Version 0.4 if IBS Events plugin is installed those events may be displayed.

How can I hide the event titles? Because calendars can be configured such that the event titles do not display properly an option, hideTitle, has be added. When this option is set the event will be displayed on the Full Calendar using only the background color.
This yields a solid bar as the event title.

== Screenshots ==
1. Settings tab.
2. Options for Full Calendar.
3. Feeds for Google Calendars
4. Tooltip styling tab.
5. Shortcode generator.
6. Widget
7. Widget admin

== Changelog ==

(initial release)
2014-11-27 Added required Google Calendar API Key
2014-12-02 Version 0.3 adds a IBS calendar widget using Full Calendar.
2014-12-27 Version 0.4 adds several more Full Calendar options and support for IBS Events calendar feed.

== Upgrade Notice ==