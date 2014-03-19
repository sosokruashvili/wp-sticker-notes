=== Wordpress Sticky Notes ===
Contributors: sosokruashvili
Donate link: http://sticker-notes.com/
Tags: sticker, sticky, notes, plugin, wordpress
Requires at least: 3.5
Tested up to: 3.8.1
Stable tag: 2.0.1
License: GNU3
License URI: https://www.gnu.org/copyleft/gpl.html

Create and stick notes to any page on any position

== Description ==
= With this plugin you are able to: = 

*   Create and stick note to any page on any position
*   Tag users on a note with “@” and target note to specified users
*   Communicate between website administrator and developer by simple notes ( tasks )
*   See exact location of problem or some issue
*   Control issues and communicate by inline note editing
*   Set permissions to roles or users with custom capabilities: read, create, edit

This plugin is useful while website testing and tracking bugs. User (with appropriate permissions) can create and stick note anywhere on any page.
For example: if there is bug or is required to change something administrator can create note and stick on exact location where the issue is and developer (with appropriate permissions) can see the note.

= New Features =

*   Added URL Parser in note content, replace plain urls to link
*   Possibility to tag users in note with “@” and target note to specified users
*   Email notification for users who was tagged on a note
*   Added permission management structure, capabilities: read, create, edit
*   Added capability management on user edit page
*   ctrl + s - save note command

This plugin uses as less scripts and css as possible to avoid traffic load or performance problem, and is as simple as possible.
Some other features will be added in next releases :D

= Watch the demo =

[youtube https://www.youtube.com/watch?v=PSo_MQukxdM]

Hope you enjoy it.

== Installation ==

= This section describes how to install the plugin and get it working. =
1. Download and unzip Sticky Notes plugin.
2. Upload the wp-sticky-notes folder to the /wp-content/plugins/ directory.
3. Activate the plugin through the \'Plugins\' menu in WordPress.
4. You will have a new WP Sticker Notes link in your admin menu, navigate through this menu item
5. Easily set permissions, who can use this plugin ( see and create )
6. Enjoy

== Frequently Asked Questions ==
= Created note is not draggable. Why? =
This plugin uses jQuery library and jQuery UI. In some situations there are multiple jQuery libraries included in document footer and head. In most cases this problem appears when there is jQuery included after jQuery UI. Check if there is jQuery included after jQuery UI.

= Note is not in exact location where I sticked it. =
This happens when website is with responsive design and  you are viewing notes in non desktop version. This plugin is optimized on desktop resolutions.

== Screenshots ==
1. Note on front page
2. Note on front page
3. Sticky Note
4. Admin settings page

== Changelog ==

= 2.0.1 = 
* Fixed DB errors

= 2.0 = 
* Added URL Parser in note content, replace plain urls to link
* Possibility to tag users in note with “@” and target note to specified users
* Email notification for users who was tagged on a note
* Fixed bug: css verriding fontello elements

= 1.5.2 = 
* Fixed bugs related to default capabilities

= 1.5.1 = 
* Fixed some minor bugs related to new features

= 1.5.0 = 
* Changed permission management structure, added capabilities: read, create, edit
* Added capability management on user edit page
* ctrl + s - save note command
* Minor design changes: light shadow for note and white background
* Fixed bug when deleting already deleted note bug
* Fixed bug while saving unchanged note

= 1.0.8 = 
* Added "Everyone" option in permission management
* Fixed some minor bugs

= 1.0.5 = 
* Fixed positioning bugs while dragging
* Changed names in admin section

= 1.0.0 = 
* Realease

== Upgrade Notice ==

= 2.0.1 = 
Fixed DB errors

= 2.0 = 
Added URL Parser in note content, replace plain urls to link
Possibility to tag users in note with “@” and target note to specified users
Email notification for users who was tagged on a note

= 1.5.2 = 
Fixed bugs related to default capabilities

= 1.5.1 = 
Fixed minor bugs

= 1.5.0 = 
New features: extended permission management. You can set capabilities to user roles and users. Caps: read, create, edit. Also changed some minor UI. 

= 1.0.8 = 
New option to permission management. Added "Everyone" option, for using this plugin more flexible

= 1.0.5 =
This version fixes positioning bugs and jQuery UI bugs while dragging the sticky note. Highly Recommended

= 1.0.0 =
Realease