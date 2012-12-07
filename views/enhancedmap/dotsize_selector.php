<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author     John Etherton <john@ethertontech.com>
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
 * @license	   GNU Lesser GPL (LGPL) Rights pursuant to Version 3, June 2007
 * @copyright  2012 Etherton Technologies Ltd. <http://ethertontech.com>
 * @Date	   2012-08-02
 * Purpose:	   View for the clustering selector
 * Inputs:     $dotsize_selector_id - HTML element id of this whole view. Great for a $("#<$dotsize_selector_id>") type function
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
 * 2012-08-02:  Etherton - Initial release
 *
 * Developed by Etherton Technologies Ltd.
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