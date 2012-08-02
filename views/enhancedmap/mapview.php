<?php defined('SYSPATH') or die('No direct script access.');
/**
 * View for the admin map
 * 
 * This file is adapted from the file Ushahidi_Web/themes/default/views/main.php
 * Originally written by the Ushahidi Team
 *
 *
 * @author     John Etherton <john@ethertontech.com>
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
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
