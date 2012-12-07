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
</div> <!--class="bg"-->
</div> <!--content-->
</div> <!--holder-->

<div id="bar"></div>
		<!-- right column -->
		<div id="right">
		    
		    <?php echo $div_status_filter;?>
		    
		    <?php echo $div_boolean_filter;?>
		    
		    <?php echo $div_dotsize_selector;?>
		    
		    <?php echo $div_clustering_selector;?>
		    
		    <?php echo $div_categories_filter;?>

			<?php echo $div_layers_filter; ?>
		
			<?php echo $div_shares_filter; ?>
		
			
			
			<br />
		
			
			<?php
			// Action::main_sidebar - Add Items to the Entry Page Sidebar
			Event::run('ushahidi_action.main_sidebar');
			?>
	
		</div>
		<!-- / right column -->
		</div>
		<!-- / right column -->
	
		<!-- content column -->
		<div id="mapcontent">
			<?php								
				// Map and Timeline Blocks
				echo $div_map;
				echo $div_timeline;
				?>
		</div>
		<!-- / content column -->



<div> <!--class="bg"-->
<div> <!--content-->
<div> <!--holder-->
