<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Shares filter
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