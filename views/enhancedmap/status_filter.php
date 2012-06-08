<?php defined('SYSPATH') or die('No direct script access.');
/**
 * View for the status filter
 * 
 * This file is adapted from the file Ushahidi_Web/themes/default/views/main.php
 * Originally written by the Ushahidi Team
 *
 *
 * @author     John Etherton <john@ethertontech.com>
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
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
			