<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author     John Etherton <john@ethertontech.com>
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
 * @license	   GNU Lesser GPL (LGPL) Rights pursuant to Version 3, June 2007
 * @copyright  2012 Etherton Technologies Ltd. <http://ethertontech.com>
 * @Date	   2012-06-06
 * Purpose:	   View for the iFrame Map
 *             This file is adapted from the file Ushahidi_Web/themes/default/views/main.php
 *             Originally written by the Ushahidi Team
 * Inputs:     $div_status_filter - The HTML that creates the status(approved/unapproved) filter
 *             $div_boolean_filter  - The HTML that creates the boolean(AND/OR) filter
 *             $div_category_filter - The HTML that creates the category filter
 *             $div_layers_filter - The HTML that creates the layers filter
 *             $div_shares_filter - The HTML that creates the shares filter
 *             $div_timeline - The HTML that creates the timeline
 *             $width - The width of the map in pixels
 * Outputs:    HTML
 *
 * The Enhanced Map, Ushahidi Plugin is free software: you can redistribute
 * it and/or modify it under the terms of the GNU Lesser General Public License
 * as published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * The Enhanced Map, Ushahidi Plugin is distributed in the hope that it will
 * be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with the Enhanced Map, Ushahidi Plugin.  If not, see
 * <http://www.gnu.org/licenses/>.
 *
 * Changelog:
 * 2012-06-06:  Etherton - Initial release
 *
 * Developed by Etherton Technologies Ltd.
 */
?>
<!-- The map -->
<style type="text/css">
	html, body, #map {
		margin: 0;
		padding:0;
		border:0;
		overflow:hidden;
		width: 100%;
		height: 100%;
	}
</style>
<div class="map" id="map" style="width:100%;height:100%;"></div>
<div id="mapStatus">
	<div id="mapScale" style="border-right: solid 1px #999"></div>
	<div id="mapMousePosition" style="min-width: 135px;border-right: solid 1px #999;text-align: center"></div>
	<div id="mapProjection" style="border-right: solid 1px #999"></div>
	<div id="mapOutput"></div>
</div>
<!-- /The map -->
<div style="display:none;">
	<?php echo $div_timeline; ?>
</div>
