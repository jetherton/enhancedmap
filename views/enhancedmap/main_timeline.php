<?php defined('SYSPATH') or die('No direct script access.');
/**
 * timeline view for the admin map
 * 
 * This file is adapted from the file Ushahidi_Web/themes/default/views/main_timeline.php
 * Originally written by the Ushahidi Team
 *
 *
 * @author     John Etherton <john@ethertontech.com>
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
 */
?>
<br/>
<div class="slider-holder" id="<?php echo $slider_holder_id; ?>">
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
<div id="<?php echo $graph_id; ?>" class="graph-holder"></div>