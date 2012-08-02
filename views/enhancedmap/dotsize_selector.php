<?php defined('SYSPATH') or die('No direct script access.');
/**
 * View for the dot size selector
 * 
 
 * @author     John Etherton <john@ethertontech.com>
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
 */
?>

<!-- Dot size selector -->
			
			
				<ul id="<?php echo $dotsize_selector_id; ?>"  class="category-filters boolean-filters">
			
				<strong><?php echo Kohana::lang('enhancedmap.size_of_dots');?></strong>
							<?php
									$size_array = array('1'=>Kohana::lang('enhancedmap.small'),
											'2'=>Kohana::lang('enhancedmap.medium'),
											'3'=>Kohana::lang('enhancedmap.large'),
											'4'=>Kohana::lang('enhancedmap.exlarge'),
											);
									print form::dropdown('dot_size',$size_array, $current_size, 'id="dot_size" onchange="setDotSize(); return false;"');
									
								?> 
				

				</ul>			
		       <!-- /Dot size selector-->