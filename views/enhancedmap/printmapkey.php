<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author     John Etherton <john@ethertontech.com>
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
 * @license	   GNU Lesser GPL (LGPL) Rights pursuant to Version 3, June 2007
 * @copyright  2012 Etherton Technologies Ltd. <http://ethertontech.com>
 * @Date	   2012-06-06
 * Purpose:	   View for the Key for the print map
 * Inputs:     $keyStartDate - A string representing the start date of the timeline
 *             $keyEndDate - A string representing the end date of the timeline
 *             $logic - A string representing the current state of the boolean filter
 *             $categories - An array of the currently selected categories on the map
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

<h5><?php echo Kohana::lang('enhancedmap.map_key'); ?></h5>

<?php echo Kohana::lang('enhancedmap.map_key_1', array("<strong>$keyStartDate</strong>","<strong>$keyEndDate</strong>")); ?><br/>
<?php echo $logic; ?>
<br/>
<ul id="keyCategories">
	<?php 
		foreach ($categories as $cat)
		{
		?>
			<li> 
				<div class="swatch" style="background:#<?php echo $cat["color"]; ?>;"></div> 
				<?php echo $cat["name"];?>
			</li>
		<?php 
		}
	?>
	
</ul>