<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author     John Etherton <john@ethertontech.com>
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
 * @license	   GNU Lesser GPL (LGPL) Rights pursuant to Version 3, June 2007
 * @copyright  2012 Etherton Technologies Ltd. <http://ethertontech.com>
 * @Date	   2012-06-06
 * Purpose:	   View for adding filter JS to the /reports page
 * Inputs:     $selected_categories - A string of a JSON array representing the categories that should be selected when the page loads     
 * Outputs:    JavaScript
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

<script type="text/javascript">

/**
 * Toggle AND or OR
 */
function logicalOperatorFilterToggle(lo)
{
	urlParameters['lo'] = lo;	
}

/**
 * Set the selected categories as selected
 */
$(document).ready(function() {

	var categories = [<?php echo $selected_categories; ?>];
	for( i in categories)
	{
		if(!$("#filter_link_cat_" + categories[i]).hasClass("selected"))
		{
			$("#filter_link_cat_" + categories[i]).trigger("click");
		}
	}

	//ride the reset all filters bandwagon
	$("#reset_all_filters").click(function(){
		$("#logicalOperatorRadioOr").attr("checked","checked");
		$("#logicalOperatorRadioAnd").removeAttr("checked");
		logicalOperatorFilterToggle('or');
	});
});




</script>