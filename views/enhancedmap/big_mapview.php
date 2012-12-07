<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author     John Etherton <john@ethertontech.com>
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
 * @license	   GNU Lesser GPL (LGPL) Rights pursuant to Version 3, June 2007
 * @copyright  2012 Etherton Technologies Ltd. <http://ethertontech.com>
 * @Date	   2012-06-06
 * Purpose:	   View for the big map
 *             This file is adapted from the file Ushahidi_Web/themes/default/views/main.php
 *             Originally written by the Ushahidi Team
 * Inputs:     $div_status_filter - The HTML that creates the status(approved/unapproved) filter
 *             $div_boolean_filter  - The HTML that creates the boolean(AND/OR) filter
 *             $div_dotsize_selector - The HTML that creates the dot size selector
 *             $div_clustering_selector - The HTML that creates the clustering selector
 *             $div_category_filter - The HTML that creates the category filter
 *             $div_layers_filter - The HTML that creates the layers filter
 *             $div_shares_filter - The HTML that creates the shares filter
 *             $div_timeline - The HTML that creates the timeline
 *             $div_map - The HTML that creates the map
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

