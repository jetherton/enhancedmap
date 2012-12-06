<?php defined('SYSPATH') or die('No direct script access.');
/**
 * map view for the big map
 * 
 * This file is adapted from the file Ushahidi_Web/themes/default/views/main_map.php
 * Originally written by the Ushahidi Team
 *
 *
 * @author     John Etherton <john@ethertontech.com>
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
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
