<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author     John Etherton <john@ethertontech.com>
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
 * @license	   GNU Lesser GPL (LGPL) Rights pursuant to Version 3, June 2007
 * @copyright  2012 Etherton Technologies Ltd. <http://ethertontech.com>
 * @Date	   2012-06-06
 * Purpose:	   map view for the big map
 *             This file is adapted from the file Ushahidi_Web/themes/default/views/main_map.php
 *             Originally written by the Ushahidi Team
 * Inputs:     $map_id - HTML element id of the map
 *             $map_status_id - HTML element id of the status 
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

<div class="map" id="<?php echo $map_id; ?>"></div>

<div id="<?php echo $map_status_id; ?>" class="mapStatus">
	<div id="mapScale" class="mapScale"></div>
	<div id="mapMousePosition" class="mapMousePosition" ></div>
	<div id="mapProjection" class="mapProjection"></div>
	<div id="mapOutput" class="mapOutput" ></div>
	<div id="printmap-link"><a href="<?php echo url::site('printmap'); ?>"><?php echo Kohana::lang('enhancedmap.print_a_map'); ?></a></div>
</div>
<!-- / map -->
