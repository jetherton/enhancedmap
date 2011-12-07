<?php defined('SYSPATH') or die('No direct script access.');
/**
 * map view for the admin map
 * 
 * This file is adapted from the file Ushahidi_Web/themes/default/views/main_map.php
 * Originally written by the Ushahidi Team
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     John Etherton <john@ethertontech.com>
 * @package    Admin Map, Ushahidi Plugin - https://github.com/jetherton/adminmap
 */
?>
<!-- map -->
<div class="map" id="map"></div>
<div id="mapStatus">
	<div id="mapScale" style="border-right: solid 1px #999"></div>
	<div id="mapMousePosition" style="min-width: 135px;border-right: solid 1px #999;text-align: center"></div>
	<div id="mapProjection" style="border-right: solid 1px #999"></div>
	<div id="mapOutput"></div>
</div>
<!-- / map -->