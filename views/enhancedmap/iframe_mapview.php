<?php defined('SYSPATH') or die('No direct script access.');
/**
 * View for the iFrame Map
 * 
 * This file is adapted from the file Ushahidi_Web/themes/default/views/main.php
 * Originally written by the Ushahidi Team
 *
 *
 * @author     John Etherton <john@ethertontech.com>
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
 */
?>
<div id="title">
<h1><a href="<?php echo url::base(); ?>"><?php echo $site_name;?></a></h1>
</div>


<!-- keep track of what status we're looking at -->
		<form action="">
			<input type = "hidden" value="1" name="currentStatus" id="currentStatus">
			<input type = "hidden" value="2" name="colorCurrentStatus" id="colorCurrentStatus">
		</form>



<table>
<tr>
<td>
<!-- Controls -->
		<div id="controls">	
		
	
		
		<?php echo $div_boolean_filter;?>
				 
		<?php echo $div_categories_filter;?>
		
		<?php echo $div_layers_filter; ?>
		
		<?php echo $div_shares_filter; ?>
		
	</div>
	</td>
	
		<!-- /controls -->
	<td>	
		<!-- Map and time slider -->
				<!-- The map -->
				<div class="map" id="map" style="width:<?php echo $width;?>px"></div>
				<div id="mapStatus">
					<div id="mapScale" style="border-right: solid 1px #999"></div>
					<div id="mapMousePosition" style="min-width: 135px;border-right: solid 1px #999;text-align: center"></div>
					<div id="mapProjection" style="border-right: solid 1px #999"></div>
					<div id="mapOutput"></div>
				</div>
				<!-- /The map -->
				
				
				
				<div style="display:none;" id="key" class="right bottom">
				<h5>Map Key:</h5>
				This map is showing reports from <span id="keyStartDate"></span> to <span id="keyEndDate"></span>.<br/>
				<span id="keyLogic">All reports on this map fall under one or more of the following categories. </span>
				<br/>
				<ul id="keyCategories">
					<li> <div class="swatch" style="background:#cc0000;"></div> ALL CATEGORIES</li>
				</ul>   
				
				</div>
			<!-- /Map and time slider -->
	</td>
	</tr>
	</table>
	
	
	<!-- Time chooser -->
	<?php								
		echo $div_timeline;
	
	?>
	<!-- /Time chooser -->

	

