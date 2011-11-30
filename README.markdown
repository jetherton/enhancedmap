Admin Map Ushahidi Plugin
=================
Info
-----
website: https://github.com/jetherton/adminmap
description: Shows a map on the backend that shows unapproved reports as well as approved reports.
version: 2.0
requires: 2.1 - The bleeding edge version from the repo https://github.com/ushahidi/Ushahidi_Web, not form http://download.ushahidi.com/
author: John Etherton
author website: http://ethertontech.com

Description
-----------------
* Lets you see all reports (approved and unapproved) on a map on the backend of the website.

* Lets you print maps, using CSS black magic, and if you're using a map layer that allows you 
to manipulate the tiles on the server, you can print via the server. you can access this via
/printmap. It's still under development, so please give feed back and keep checking in

* Lets you embedd your map. It'll create a text box on the home page that has the embedd HTML.


Installation
----------------
1. Copy the entire /adminmap/ directory into your /plugins/ directory.
2. Activate the plugin.

Changelog
----------------
* 2.0 -- 2011/11/30 -- Refactored the plugin to work with Ushahidi's new reports::fetch_incidents() helper method. This allows much tigher intergration with Ushahidi