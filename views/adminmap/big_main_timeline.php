<?php defined('SYSPATH') or die('No direct script access.');
/**
 * timeline view for the big map
 * 
 * This file is adapted from the file Ushahidi_Web/themes/default/views/main_timeline.php
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

<div class="slider-holder">
	<form action="">
		<input type="hidden" value="0," name="currentCat" id="currentCat">
		<fieldset>
			<!--<div class="play"><a href="#" id="playTimeline">PLAY</a></div> This is buggy, and not up to snub for my code, plus no one uses it that i know of and it's not worth fixing right now-->
			<label for="startDate">From:</label>
			<select name="startDate" id="startDate"><?php echo $startDate; ?></select>
			<label for="endDate">To:</label>
			<select name="endDate" id="endDate"><?php echo $endDate; ?></select>
		</fieldset>
	</form>
</div>
<div id="graph" class="graph-holder"></div>