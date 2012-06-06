<?php defined('SYSPATH') or die('No direct script access.');
/**
 * map view for the admin map
 * 
 * This file is adapted from the file Ushahidi_Web/themes/default/views/main_map.php
 * Originally written by the Ushahidi Team
 *
 * @author     John Etherton <john@ethertontech.com>
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
 */
?>
<!-- map -->
<div class="map" id="map" <?php 
//setup if we're overriding the width or not
$width = ORM::factory('enhancedmap_settings')->where('key', 'adminmap_width')->find()->value;
$height =  ORM::factory('enhancedmap_settings')->where('key', 'adminmap_height')->find()->value;

if(strtolower($width) !=  'other' OR strtolower($height) !=  'other')
{
	$retVal = 'style="';
	if(strtolower($width) !=  'other')
	{
		$retVal .= 'width:'.$width.'; ';
	}
	if(strtolower($height) !=  'other')
	{
		$retVal .= 'height:'.$height.'; ';
	}
	
	$retVal .= '"';
	echo $retVal;
} 

?>></div>
<div id="mapStatus">
	<div id="mapScale" style="border-right: solid 1px #999"></div>
	<div id="mapMousePosition" style="min-width: 135px;border-right: solid 1px #999;text-align: center"></div>
	<div id="mapProjection" style="border-right: solid 1px #999"></div>
	<div id="mapOutput"></div>
</div>
<!-- / map -->