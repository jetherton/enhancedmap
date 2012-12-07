<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author     Robbie MacKay <rm@robbiemackay.com>
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
 * @license	   GNU Lesser GPL (LGPL) Rights pursuant to Version 3, June 2007
 * @copyright  2012 Etherton Technologies Ltd. <http://ethertontech.com>
 * @Date	   2012-04-27
 * Purpose:	   View for the status (approved/unapproved)filter
 *             This file is adapted from the file Ushahidi_Web/themes/default/views/main.php
 *             Originally written by the Ushahidi Team
 * Inputs:     $share_id - HTML element id of this whole view. Great for a $("#<share_id>") type function
 *             $shares - An array of shares that will be shown to the user
 *             $show_on_load - If true this view will be shown in its full glory, otherwise it's minimized
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
 * 2012-04-27:  MacKay - Initial release
 *
 * Developed by Etherton Technologies Ltd.
 */
?>



			<?php 
				//see if the user we're dealing with can see reports
				// If user doesn't have access, redirect to dashboard
				if(isset($_SESSION['auth_user']))
				{
				$user = new User_Model($_SESSION['auth_user']->id);
				$user_view_reports = admin::permissions($user, "reports_view");
				if(ORM::factory('enhancedmap_settings')->where('key', 'show_unapproved_frontend')->find()->value == 'true' AND $user_view_reports) 
				{
			?>
			<!-- Show unapproved -->
	
				<ul id="<?php echo $status_filter_id;?>" class="category-filters status-filters">
					<strong><?php echo Kohana::lang('enhancedmap.status_filters') ?>:</strong>
					<li>
						<a <?php if($show_unapproved){echo 'class="active"';}?> id="status_1" href="#">
							<div class="swatch" style="background-color:#000000"></div>
							<div class="status-title">Unapproved Reports</div>
						</a>
					</li>
					<li>
						<a class="active" id="status_2" href="#">
							<div class="swatch" style="background-color:#<?php echo Kohana::config('settings.default_map_all');?>"></div>
							<div class="status-title">Approved Reports</div>
						</a>
					</li>								
				</ul>
				<!-- /Show unapproved -->
				<?php }} //end if show_unapproved_backend?>
			