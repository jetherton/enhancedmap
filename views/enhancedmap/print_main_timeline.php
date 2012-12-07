<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author     John Etherton <john@ethertontech.com>
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
 * @license	   GNU Lesser GPL (LGPL) Rights pursuant to Version 3, June 2007
 * @copyright  2012 Etherton Technologies Ltd. <http://ethertontech.com>
 * @Date	   2012-06-06
 * Purpose:	   timeline view for the print map
 *             This file is adapted from the file Ushahidi_Web/themes/default/views/main_timeline.php
 *             Originally written by the Ushahidi Team
 * Inputs:     $graph_id - HTML element id of the timeline
 *             $slider_holder_id - HTML element id of the slider and drop downs for the timeline
 *             $startDate - String of HTML select options for all the dates you can choose as the start date
 *             $endDate - String of HTML select options for all the dates you can choose as the end date
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
<div class="slider-holder" id="<?php echo $slider_holder_id; ?>">
	<form action="">
		<input type="hidden" value="0," name="currentCat" id="currentCat">
		<fieldset>
			<!--<div class="play"><a href="#" id="playTimeline">PLAY</a></div> This is buggy, and not up to snub for my code, plus no one uses it that i know of and it's not worth fixing right now-->
			<label for="startDate"><?php echo Kohana::lang('ui_main.from') ?>:</label>
			<select name="startDate" id="startDate"><?php echo $startDate; ?></select>
			<label for="endDate"><?php echo Kohana::lang('ui_main.to') ?>:</label>
			<select name="endDate" id="endDate"><?php echo $endDate; ?></select>
		</fieldset>
	</form>
</div>
<div id="<?php echo $graph_id; ?>" class="graph-holder" ></div>
