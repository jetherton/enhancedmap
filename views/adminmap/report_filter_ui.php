<?php defined('SYSPATH') or die('No direct script access.');
/**
 * View for adding filter UI to the /reports page
 * 
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     John Etherton <john@ethertontech.com>
 * @package    Admin Map, Ushahidi Plugin - https://github.com/jetherton/adminmap
 */
?>
<h3 id="admin_map_and_or_filter_header">
	<a class="f-title" href="#"><?php echo Kohana::lang('ui_main.category'). " ". Kohana::lang('adminmap.logical_operators'); ?></a>
</h3>
<div class="f-simpleGroups-box" id="admin_map_and_or_filter_body">
	<ul class="filter-list fl-logicalOperators">
		<li>
			<?php echo Kohana::lang('adminmap.OR') . " "; print form::radio('logical_operator', 'or', $operator=="or","onchange=\"logicalOperatorFilterToggle('or');\" id=\"logicalOperatorRadioOr\"");?>
		</li>
		<li>
			<?php echo Kohana::lang('adminmap.AND') . " "; print form::radio('logical_operator', 'and', $operator=="and", "onchange=\"logicalOperatorFilterToggle('and');\"  id=\"logicalOperatorRadioAnd\"");?>
		</li>
	</ul>
	
	
</div>