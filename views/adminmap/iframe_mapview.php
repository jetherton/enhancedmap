<div id="title">
<h1><?php echo $site_name;?></h1>
</div>


<!-- keep track of what status we're looking at -->
		<form action="">
			<input type = "hidden" value="1" name="currentStatus" id="currentStatus">
			<input type = "hidden" value="2" name="colorCurrentStatus" id="colorCurrentStatus">
		</form>



<table>
<tr>
<td>
<!-- Controls -->
		<div id="controls">	
		
	
		
		   <!-- logic filters -->
		   
			<div class="stat-filters clearingfix">
				<!-- keep track of what status we're looking at -->
				<form action="">
					<input type = "hidden" value="or" name="currentLogicalOperator" id="currentLogicalOperator">
				</form>
				
				<strong><?php echo Kohana::lang("adminmap.boolean_operators"); ?></strong>
				<ul id="status_switch" class="category-filters" style="height:auto; overflow:visible;">
					<li>
						<a class="active" id="logicalOperator_1" href="#">							
						<?php echo Kohana::lang("adminmap.OR"); ?>
						</a>
					</li>
					<li>
						<a  id="logicalOperator_2" href="#">
							<div class="status-title"><?php echo Kohana::lang("adminmap.AND"); ?></div>
						</a>
					</li>
				</ul>
			</div>		       
		       <!-- /logic filters -->

		
		
		
		
			<strong style="text-transform:uppercase;"><?php echo Kohana::lang("adminmap.Categories"); ?></strong>
			<ul id="category_switch" class="category-filters">
				
				<li><a class="active" id="cat_0" href="#"><div class="category-title"><?php echo Kohana::lang('ui_main.all_categories');?></div></a></li>
				<?php
					foreach ($categories as $category => $category_info)
					{
						$category_title = $category_info[0];

						//check if this category has kids
						if(count($category_info[3]) > 0)
						{
							echo '<li>';
							echo '<a style="float:right; text-align:center; width:15px; padding:2px 0px 1px 0px; border-left:none;" href="#" id="drop_cat_'.$category.'">+</a>';
							echo '<a  href="#" id="cat_'. $category .'"><div class="category-title">'.$category_title.'</div></a>';
							
						}
						else
						{
							echo '<li><a href="#" id="cat_'. $category .'"><div class="category-title">'.$category_title.'</div></a>';
						}
						// Get Children
						echo '<div class="hide" id="child_'. $category .'"><ul>';
						
						foreach ($category_info[3] as $child => $child_info)
						{
							$child_title = $child_info[0];
						
							echo '<li style="padding-left:20px;"><a href="#" id="cat_'. $child .'" cat_parent="'.$category.'"><div class="category-title">'.$child_title.'</div></a></li>';
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
				<br/>
				<strong><?php echo strtoupper(Kohana::lang('ui_main.layers_filter'));?> </strong>
				<ul id="kml_switch" class="category-filters" >
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
						onclick="switchLayer(\''.$layer.'\',\''.$layer_link.'\',\''.$layer_color.'\'); return false;">
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
				<strong><?php echo strtoupper(Kohana::lang('ui_main.other_ushahidi_instances'));?></strong>
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
	</td>
	
		<!-- /controls -->
	<td>	
		<!-- Map and time slider -->
				<!-- The map -->
				<div class="map" id="map" style="width:<?php echo $width;?>px"></div>
				<div id="mapStatus">
					<div id="mapScale" style="border-right: solid 1px #999"></div>
					<div id="mapMousePosition" style="min-width: 135px;border-right: solid 1px #999;text-align: center"></div>
					<div id="mapProjection" style="border-right: solid 1px #999"></div>
					<div id="mapOutput"></div>
				</div>
				<!-- /The map -->
				
				
				
				<div style="display:none;" id="key" class="right bottom">
				<h5>Map Key:</h5>
				This map is showing reports from <span id="keyStartDate"></span> to <span id="keyEndDate"></span>.<br/>
				<span id="keyLogic">All reports on this map fall under one or more of the following categories. </span>
				<br/>
				<ul id="keyCategories">
					<li> <div class="swatch" style="background:#cc0000;"></div> ALL CATEGORIES</li>
				</ul>   
				
				</div>
			<!-- /Map and time slider -->
	</td>
	</tr>
	</table>
	
	
	<!-- Time chooser -->
	<?php								
		echo $div_timeline;
	
	?>
	<!-- /Time chooser -->

	

