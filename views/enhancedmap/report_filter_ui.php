<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author     John Etherton <john@ethertontech.com>
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
 * @license	   GNU Lesser GPL (LGPL) Rights pursuant to Version 3, June 2007
 * @copyright  2012 Etherton Technologies Ltd. <http://ethertontech.com>
 * @Date	   2012-06-06
 * Purpose:	   View for adding filter UI to the /reports page
 * Inputs:     $operator - If 'or' then the OR radio button is selected, if 'and' the AND radio button is selected
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
<h3 id="admin_map_and_or_filter_header">
	<a class="f-title" href="#"><?php echo Kohana::lang('ui_main.category'). " ". Kohana::lang('enhancedmap.logical_operators'); ?></a>
</h3>
<div class="f-simpleGroups-box" id="admin_map_and_or_filter_body">
	<ul class="filter-list fl-logicalOperators">
		<li>
			<?php echo Kohana::lang('enhancedmap.OR') . " "; print form::radio('logical_operator', 'or', $operator=="or","onchange=\"logicalOperatorFilterToggle('or');\" id=\"logicalOperatorRadioOr\"");?>
		</li>
		<li>
			<?php echo Kohana::lang('enhancedmap.AND') . " "; print form::radio('logical_operator', 'and', $operator=="and", "onchange=\"logicalOperatorFilterToggle('and');\"  id=\"logicalOperatorRadioAnd\"");?>
		</li>
	</ul>
	
	
</div>