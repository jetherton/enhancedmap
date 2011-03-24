
<!-- keep track of what status we're looking at -->
		<form action="">
			<input type = "hidden" value="1" name="currentStatus" id="currentStatus">
			<input type = "hidden" value="2" name="colorCurrentStatus" id="colorCurrentStatus">
		</form>

		<!-- right column -->
		<div id="right" class="clearingfix" >	
		<div id="right_move" style="border:1px black solid;" onmousedown="mD(this,event)">
			<strong style="margin-left:10px;" ><?php echo Kohana::lang("adminmap.FILTERS"); ?></strong>
			<a id="toggleright" onclick="togglelayer('right_colapse', 'toggleright'); return false;" style="border: solid 1px black; marging: 1px; padding: 0px 10px; position:relative; float:right;" href="#" > 
				<strong>-</strong> 
			</a>
		</div>
		<div id="right_colapse">
		
				       <!-- logic filters -->
			<div class="stat-filters clearingfix">
				<!-- keep track of what status we're looking at -->
				<form action="">
					<input type = "hidden" value="or" name="currentLogicalOperator" id="currentLogicalOperator">
				</form>
				<ul id="status_switch" class="status-filters">
				
				<strong><?php echo Kohana::lang("adminmap.boolean_operators"); ?></strong>				
				
					<li>
						<div style="float:right; margin-left:10px;"><span style="cursor:help;text-transform:none;color:#bb0000;" title="
							<h3><?php echo Kohana::lang("adminmap.ORHEADER"); ?></h3>
							<?php echo Kohana::lang("adminmap.ORBODY"); ?>
						">What's this?</span></div>
						<a class="active" id="logicalOperator_1" href="#">							
						<?php echo Kohana::lang("adminmap.OR"); ?>
						</a>
					</li>
					<li>
						<div style="float:right; margin-left:10px;"><span style="cursor:help;text-transform:none;color:#bb0000;" title="
							<h3><?php echo Kohana::lang("adminmap.ANDHEADER"); ?></h3>
							<?php echo Kohana::lang("adminmap.ANDBODY"); ?>
						">What's this?</span></div>
						
						<a  id="logicalOperator_2" href="#">
							<div class="status-title"><?php echo Kohana::lang("adminmap.AND"); ?></div>
						</a>
					</li>
				</ul>
			</div>		       
		       <!-- /logic filters -->

		
		
		
		
	
			<ul id="category_switch" class="category-filters">
				<strong style="text-transform:uppercase;font-size:85%;"><?php echo Kohana::lang("adminmap.Categories"); ?></strong>
				<li><a class="active" id="cat_0" href="#"><div class="swatch" style="background-color:#<?php echo $default_map_all;?>"></div><div class="category-title"><?php echo Kohana::lang('ui_main.all_categories');?></div></a></li>
				<?php
					foreach ($categories as $category => $category_info)
					{
						$category_title = $category_info[0];
						$category_color = $category_info[1];
						$category_image = '';
						$color_css = 'class="swatch" style="background-color:#'.$category_color.'"';
						if($category_info[2] != NULL && file_exists(Kohana::config('upload.relative_directory').'/'.$category_info[2])) {
							$category_image = html::image(array(
								'src'=>Kohana::config('upload.relative_directory').'/'.$category_info[2],
								'style'=>'float:left;padding-right:5px;'
								));
							$color_css = '';
						}
						//check if this category has kids
						if(count($category_info[3]) > 0)
						{
							echo '<li>';
							echo '<a style="float:right; text-align:center; width:15px; padding:2px 0px 1px 0px; border-left:none;" href="#" id="drop_cat_'.$category.'">+</a>';
							echo '<a  href="#" id="cat_'. $category .'"><div '.$color_css.'>'.$category_image.'</div><div class="category-title">'.$category_title.'</div></a>';
							
						}
						else
						{
							echo '<li><a href="#" id="cat_'. $category .'"><div '.$color_css.'>'.$category_image.'</div><div class="category-title">'.$category_title.'</div></a>';
						}
						// Get Children
						echo '<div class="hide" id="child_'. $category .'"><ul>';
						foreach ($category_info[3] as $child => $child_info)
						{
							$child_title = $child_info[0];
							$child_color = $child_info[1];
							$child_image = '';
							$color_css = 'class="swatch" style="background-color:#'.$child_color.'"';
							if($child_info[2] != NULL && file_exists(Kohana::config('upload.relative_directory').'/'.$child_info[2])) {
								$child_image = html::image(array(
									'src'=>Kohana::config('upload.relative_directory').'/'.$child_info[2],
									'style'=>'float:left;padding-right:5px;'
									));
								$color_css = '';
							}
							echo '<li style="padding-left:20px;"><a href="#" id="cat_'. $child .'" cat_parent="'.$category.'"><div '.$color_css.'>'.$child_image.'</div><div class="category-title">'.$child_title.'</div></a></li>';
						}
						echo '</ul></div></li>';
					}
				?>
			</ul>
			<!-- / category filters -->
			
			<?php
			if ($layers)
			{
				?>
				<!-- Layers (KML/KMZ) -->
				<div class="cat-filters clearingfix" style="margin-top:20px;">
					<strong><?php echo Kohana::lang('ui_main.layers_filter');?> <span>[<a href="javascript:toggleLayer('kml_switch_link', 'kml_switch')" id="kml_switch_link"><?php echo Kohana::lang('ui_main.show'); ?></a>]</span></strong>
				</div>
				<ul id="kml_switch" class="category-filters" style="display:none;">
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
				<!-- /Layers -->
				<?php
			}
			?>
			
			
			<?php
			if ($shares)
			{
				?>
				<!-- Layers (Other Ushahidi Layers) -->
				<div class="cat-filters clearingfix" style="margin-top:20px;">
					<strong><?php echo Kohana::lang('ui_main.other_ushahidi_instances');?> <span>[<a href="javascript:toggleLayer('sharing_switch_link', 'sharing_switch')" id="sharing_switch_link"><?php echo Kohana::lang('ui_main.hide'); ?></a>]</span></strong>
				</div>
				<ul id="sharing_switch" class="category-filters">
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
				<!-- /Layers -->
				<?php
			}
			?>
			
			

		</div>
		</div>
		<!-- / right column -->

				<?php								
				// Map and Timeline Blocks
				echo '<div id="timeline_holder">
				<div id="timeline_drag" onmousedown="mD(this,event)"> 
				<strong style="margin-left:10px">'.Kohana::lang("adminmap.TIME_LINE").'</strong> 
				<a id="toggletimeline" onclick="togglelayer(\'timeline_colapse\', \'toggletimeline\'); return false;" style="border: solid 1px black; marging: 1px; padding: 0px 10px; position:relative; float:right;" href="#" > <strong>-</strong> </a>
				</div>
				<div id="timeline_colapse">';
				echo $div_timeline;
				echo '</div></div>';
				?>



				<?php								
				// Map and Timeline Blocks
				echo $div_map;
				?>

