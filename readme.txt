=== About ===
name: Enhanced Map
website: https://github.com/jetherton/enhancedmap
description: Adds a full screen map on the front end and a map on the back end that shows non-approved reports. Also allows for more complex boolean AND and OR filtering operations. Formerly Admin Map.
version: 2.1
requires: 2.1 or higher
author: John Etherton
author website: http://ethertontech.com

== Description ==
* Lets you see all reports (approved and unapproved) on a map on the backend of the website.

* Lets you print maps, using CSS black magic, and if you're using a map layer that allows you 
to manipulate the tiles on the server, you can print via the server. you can access this via
/printmap. It's still under development, so please give feed back and keep checking in

* Lets you embedd your map. It'll create a text box on the home page that has the embedd HTML.


== Installation ==
1. Copy the entire /enhancedmap/ directory into your /plugins/ directory.
      Note, that you must name the plugin folder exactly "enhancedmap"
2. Activate the plugin.

== Changelog ==
* 2.0 -- 2011/11/30 -- Refactored the plugin to work with Ushahidi's new reports::fetch_incidents() helper method. This allows much tigher intergration with Ushahidi. Though this will get slow when there are more than 3 or 4 thousand reports.
* 2.1 -- 2012/06/06 -- Renamed to Enhanced Map to reflect all that this plugin has become.