<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author     John Etherton <john@ethertontech.com>
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
 * @license	   GNU Lesser GPL (LGPL) Rights pursuant to Version 3, June 2007
 * @copyright  2012 Etherton Technologies Ltd. <http://ethertontech.com>
 * @Date	   2012-06-08
 * Purpose:	   View for the boolean filter
 * Inputs:     $boolean_filter_id - The HTML element id of this whole view. Use this for CSS or slick JavaScript stuff
 *             $show_help - If it's set to true you'll see help ? that tell you how to use the power of AND and OR
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
 * 2012-06-08:  Etherton - Initial release
 *
 * Developed by Etherton Technologies Ltd.
 */
?>

<!-- logic filters -->
			

				<ul id="<?php echo $boolean_filter_id; ?>"  class="category-filters boolean-filters">
				
				<strong><?php echo Kohana::lang("enhancedmap.boolean_operators"); ?></strong>				
				
					<li>
						<?php if($show_help){?>
						<div style="float:right; margin-left:10px;"><span style="cursor:help;text-transform:none;color:#bb0000;" title="
							<?php echo '<h3>'.Kohana::lang("enhancedmap.ORHEADER"). '</h3>'. Kohana::lang("enhancedmap.ORBODY"); ?>
							"><?php echo Kohana::lang('enhancedmap.whats_this'); ?></span></div>
							<?php }?>
						<a class="active" id="logicalOperator_1" href="#">							
						<?php echo Kohana::lang("enhancedmap.OR"); ?>
						</a>
					</li>
					<li>
						<?php if($show_help){?>
						<div style="float:right; margin-left:10px;"><span style="cursor:help;text-transform:none;color:#bb0000;" title="
							<?php echo '<h3>'.Kohana::lang("enhancedmap.ANDHEADER").'</h3>'.Kohana::lang("enhancedmap.ANDBODY"); ?>							
						"><?php echo Kohana::lang('enhancedmap.whats_this'); ?></span></div>
						<?php }?>
						<a  id="logicalOperator_2" href="#">
							<div class="status-title"><?php echo Kohana::lang("enhancedmap.AND"); ?></div>
						</a>
					</li>
				</ul>
					       
		       <!-- /logic filters -->