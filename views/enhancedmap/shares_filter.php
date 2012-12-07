<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author     John Etherton <john@ethertontech.com>
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
 * @license	   GNU Lesser GPL (LGPL) Rights pursuant to Version 3, June 2007
 * @copyright  2012 Etherton Technologies Ltd. <http://ethertontech.com>
 * @Date	   2012-06-08
 * Purpose:	   View for the shares filter
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
 * 2012-06-08:  Etherton - Initial release
 *
 * Developed by Etherton Technologies Ltd.
 */
?>



		<?php
			if ($shares)
			{
				?>
				<!-- Layers (Other Ushahidi Layers) -->
				<div id="<?php echo $share_id;?>">
					<div class="cat-filters clearingfix shares_filters_header" >
						<strong>
							<?php echo Kohana::lang('ui_main.other_ushahidi_instances');?> 
							<span>[
								<a href="javascript:toggleLayer('sharing_switch_link', 'sharing_switch')" id="sharing_switch_link">
									<?php echo $show_on_load ? Kohana::lang('ui_main.hide'): Kohana::lang('ui_main.show'); ?>
								</a>]
							</span>
						</strong>
					</div>
					<ul id="sharing_switch" class="category-filters" <?php if(!$show_on_load){echo 'style="display:none;"';}?>>
						<?php
						foreach ($shares as $share => $share_info)
						{
							$sharing_name = $share_info[0];
							$sharing_color = $share_info[1];
							echo '<li><a href="#" id="share_'. $share .'"><div class="swatch" style="background-color:#'.$sharing_color.'"></div>
							<div>'.$sharing_name.'</div></a></li>';
						}
						?>
					</ul>
				</div>
				<!-- /Layers -->
				<?php
			}
			?>