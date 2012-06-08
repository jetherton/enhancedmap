<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Layers filter
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
if ($layers)
{
	?>
				<!-- Layers (KML/KMZ) -->
				<div id="<?php echo $layer_id;?>">
					<div  class="cat-filters clearingfix layer-filters_header">
						<strong><?php echo Kohana::lang('ui_main.layers_filter');?> 
							<span>
								[<a href="javascript:toggleLayer('kml_switch_link', 'kml_switch')" id="kml_switch_link">
									<?php echo $show_on_load ? Kohana::lang('ui_main.hide') : Kohana::lang('ui_main.show'); ?>
								</a>]
							</span>
						</strong>
					</div>
					
					<div>
						<ul id="kml_switch" class="category-filters layer-filters" <?php if(!$show_on_load){echo 'style="display:none;"'; }?>>
							<?php
							foreach ($layers as $layer => $layer_info)
							{
								$layer_name = $layer_info[0];
								$layer_color = $layer_info[1];
								$layer_url = $layer_info[2];
								$layer_file = $layer_info[3];
								$layer_link = (!$layer_url) ?
									url::base().Kohana::config('upload.relative_directory').'/'.$layer_file :
									$layer_url;
								echo '<li><a href="#" id="layer_'. $layer .'"
								onclick="switchLayer(\''.$layer.'\',\''.$layer_link.'\',\''.$layer_color.'\'); return false;"><div class="swatch" style="background-color:#'.$layer_color.'"></div>
								<div>'.$layer_name.'</div></a></li>';
							}
							?>
						</ul>
					</div>
				</div>
				<!-- /Layers -->
				<?php
			}
			?>
			
			
			