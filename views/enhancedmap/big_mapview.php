<?php defined('SYSPATH') or die('No direct script access.');
/**
 * View for the big map
 * 
 * This file is adapted from the file Ushahidi_Web/themes/default/views/main.php
 * Originally written by the Ushahidi Team
 *
 *
 * @author     John Etherton <john@ethertontech.com>
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
 */
?>

<!-- keep track of what status we're looking at -->
		<form action="">
			<input type = "hidden" value="1" name="currentStatus" id="currentStatus">
			<input type = "hidden" value="2" name="colorCurrentStatus" id="colorCurrentStatus">
		</form>

		<!-- right column -->
		<div id="right" class="clearingfix" >	
		<div id="right_move" style="border:1px black solid;" onmousedown="mD(this,event)">
			<strong style="margin-left:10px;" ><?php echo Kohana::lang("enhancedmap.FILTERS"); ?></strong>
			<a id="toggleright" onclick="togglelayer('right_colapse', 'toggleright'); return false;" style="border: solid 1px black; margin: -1px; padding: 0px 10px; position:relative; float:right;" href="#" > 
				<strong>-</strong> 
			</a>
		</div>
		<div id="right_colapse">
		
			<?php echo $div_status_filter;?>
		
			<?php echo $div_boolean_filter;?>
			
			<?php echo $div_dotsize_selector;?>
			
			<?php echo $div_clustering_selector;?>

			<?php echo $div_category_filter;?>
		
			<?php echo $div_layers_filter;?>
			
			<?php echo $div_shares_filter;?>
		
						

		</div>
		</div>
		<!-- / right column -->

				<?php								
				// Map and Timeline Blocks
				echo '<div id="timeline_holder">
				<div id="timeline_drag" onmousedown="mD(this,event)"> 
				<strong style="margin-left:10px">'.Kohana::lang("enhancedmap.TIME_LINE").'</strong> 
				<a id="toggletimeline" onclick="togglelayer(\'timeline_colapse\', \'toggletimeline\'); return false;" style="border: solid 1px black; margin: -1px; padding: 0px 10px; position:relative; float:right;" href="#" > <strong>-</strong> </a>
				</div>
				<div id="timeline_colapse">';
				echo $div_timeline;
				echo '</div></div>';
				?>



				<?php								
				// Map and Timeline Blocks
				echo $div_map;
				?>

