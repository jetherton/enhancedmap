<?php defined('SYSPATH') or die('No direct script access.');
/**
 * View for the clustering selector
 * 
 
 * @author     John Etherton <john@ethertontech.com>
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
 */
?>

<!-- clustering selector -->
			
			
				<ul id="<?php echo $clustering_selector_id; ?>"  class="category-filters boolean-filters">
			
				<strong><?php echo Kohana::lang('enhancedmap.clustering');?></strong>
							<?php
									$clustering_array = array('1'=>Kohana::lang('ui_main.on'),
											'0'=>Kohana::lang('ui_main.off'));
									print form::dropdown('clustering',$clustering_array, $isClustering, 'id="clustering" onchange="setClustering(); return false;"');
									
								?> 
				

				</ul>			
		       <!-- /clustering selector-->