<!-- keep track of what status we're looking at -->
		<form action="">
			<input type = "hidden" value="1" name="currentStatus" id="currentStatus">
			<input type = "hidden" value="2" name="colorCurrentStatus" id="colorCurrentStatus">
		</form>

		<!-- right column -->
		<div id="right" class="clearingfix" >	
		<div id="right_move" style="border:1px black solid;" onmousedown="mD(this,event)">
			<strong style="margin-left:10px;" >FILTERS:</strong>
			<a id="toggleright" onclick="togglelayer('right_colapse', 'toggleright'); return false;" style="border: solid 1px black; marging: 1px; padding: 0px 10px; position:relative; left:160px;" href="#" > 
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
				
				<strong>Boolean Operators: </strong>
				<!--<div>
					<span style="text-align:right;cursor:help; text-transform:none;color:#bb0000;" title="
						<h3> Boolean Operators </h3>
						Boolean operators are a way to filter the data you want to see. The one or more of the categories listed below can be selected
						and then the reports you see on the map will be filtered either using the OR operator or the AND operator. You can use the OR
						operator to see all the reports that fall under one category <strong>or</strong> another. You can use the AND operator to see all 
						the reports that fall under one category <strong>and </strong>another.
						<br/><br/>
						If you want the map to work like the map on the home page you will need to make sure to select only one category at a time.
						<br/><br/>
						A category is selected by clicking on it. Click on the the category again to unselect it.
						">
						What's this?
					</span>
				</div>-->
				
				
					<li>
						<div style="float:right; margin-left:10px;"><span style="cursor:help;text-transform:none;color:#bb0000;" title="
							<h3>OR</h3>
							The OR operator allows you to see all the reports that fall under any one of the categories you select.
							<br/><br/>
							For example, if you had selected categories A, B, and C, then you would see all the reports that were
							labeled as falling under category A <strong>or</strong> B <strong>or</strong> C. Some of the reports
							shown may only fall under category A. Others may only fall under category C. Some may fall under both 
							category A and B.
							<br/><br/>
							When the OR operator is selected dots will be colored by the categories they fall under out of the categories you
							have selected. For example, if you have selected categories A and B, where A is red and B is blue, then those reports 
							that are only categorized as A will have red dots, and those reports that are only categorized as B will have 
							blue dots. If a report falls under A and B the two colors, red and blue, will be merged, and that report will
							be shown with a purple dot.
						">What's this?</span></div>
						<a class="active" id="logicalOperator_1" href="#">							
							OR
						</a>
					</li>
					<li>
						<div style="float:right; margin-left:10px;"><span style="cursor:help;text-transform:none;color:#bb0000;" title="
							<h3>AND</h3>
							The AND operator allows you to see all the reports that fall under all of the categories you select.
							<br/><br/>
							For example, if you had selected categories A, B, and C, then you would see all the reports that were
							labeled as falling under category A <strong>and</strong> B <strong>and</strong> C. 
							<br/><br/>
							When the AND operator is selected dots will be colored according to the categories you have selected.
							Since every report shown will fall under all of the categorizes selected, all of the colors of the categories
							selected will be merged and the dots will have the merged color.
						">What's this?</span></div>
						
						<a  id="logicalOperator_2" href="#">
							<div class="status-title">AND</div>
						</a>
					</li>
				</ul>
			</div>		       
		       <!-- /logic filters -->

		
		
		
		
	
			<ul id="category_switch" class="category-filters">
				<strong style="text-transform:uppercase;font-size:85%;">Categories: </strong>
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
					<strong><?php echo Kohana::lang('ui_main.layers_filter');?> <span>[<a href="javascript:toggleLayer('kml_switch_link', 'kml_switch')" id="kml_switch_link"><?php echo Kohana::lang('ui_main.hide'); ?></a>]</span></strong>
				</div>
				<ul id="kml_switch" class="category-filters">
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
				<strong style="margin-left:10px">TIME LINE</strong> 
				<a id="toggletimeline" onclick="togglelayer(\'timeline_colapse\', \'toggletimeline\'); return false;" style="border: solid 1px black; marging: 1px; padding: 0px 10px; position:relative; left:525px;" href="#" > <strong>-</strong> </a>
				</div>
				<div id="timeline_colapse">';
				echo $div_timeline;
				echo '</div></div>';
				?>



				<?php								
				// Map and Timeline Blocks
				echo $div_map;
				?>

