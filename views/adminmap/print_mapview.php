<!-- Social media stuff -->
<!-- /Social Media stuff -->


<div id="title">
<h1>Map Printing</h1>
<div id="print_warning">
<h2>Please Read Before Printing:</h2>
This page is for creating maps that will be printed. Printing works best using <strong>Firefox</strong> 4 or higher. <strong>Chrome, Internet Explorer,</strong> 
and other browsers may not print this page correctly.<br/>
For best results in Firefox, go to <strong>"Page Setup"</strong> and make sure that <strong>"Print Background (Colors &amp; Images)"</strong> is turned on and 
that <strong>scaling</strong> is set to <strong>"scale to fit."</stong>
</div>
<br/><br/>
</div>


<!-- keep track of what status we're looking at -->
		<form action="">
			<input type = "hidden" value="1" name="currentStatus" id="currentStatus">
			<input type = "hidden" value="2" name="colorCurrentStatus" id="colorCurrentStatus">
		</form>



		<div id="controls">	
		
	
		<INPUT style="width: auto; padding:6px; margin:6px; font-size:18px;" TYPE="BUTTON" VALUE="Print this map" ONCLICK="window.print()"/>
		   <!-- logic filters -->
		   
			<div class="stat-filters clearingfix">
				<!-- keep track of what status we're looking at -->
				<form action="">
					<input type = "hidden" value="or" name="currentLogicalOperator" id="currentLogicalOperator">
				</form>
				
				<strong><?php echo Kohana::lang("adminmap.boolean_operators"); ?></strong>
				<ul id="status_switch" class="category-filters" style="height:auto; overflow:visible;">
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

		
		
		
		
			<strong style="text-transform:uppercase;"><?php echo Kohana::lang("adminmap.Categories"); ?></strong>
			<ul id="category_switch" class="category-filters">
				
				<li><a class="active" id="cat_0" href="#"><div class="swatch" style="background-color:#<?php echo $default_map_all;?>"></div><div class="category-title"><?php echo Kohana::lang('ui_main.all_categories');?></div></a></li>
				<?php
					$print_group = false;
					$print_site = false;
					foreach ($categories as $category => $category_info)
					{
						if(strpos($category, "sg_") !== false && !$print_group)
						{
							echo "<li><h2>".Kohana::lang("adminmap.group_categories").":</h2></li>";
							$print_group = true;
						}
						elseif(strpos($category, "sg_") === false && $print_group && !$print_site)
						{
							echo "<li><h2>".Kohana::lang("adminmap.site_categories").":</h2></li>";
							$print_site = true;
						}
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
			
			<!-- #layersAndSuch -->
			<br/>
			<div id="ShowLayersAndSuch">
				
				<strong><a href="#" onclick="$('#layersAndSuch').show(); $('#ShowLayersAndSuch').hide(); return false;">LAYERS</a></strong>
				<br/>
				<br/>
			</div>
			<div id="layersAndSuch" style="display:none;">
			
			<?php
			if ($layers)
			{
				?>

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
			<!-- /#layersAndSuch -->
			
			
			

		




		
		
			<!-- Time chooser -->
			<strong>TIME CHOOSER</strong>
			<?php								
			echo $div_timeline;
			
			?>
			<!-- /Time chooser -->
			
			
			
			<!-- Orientation chooser -->
			<strong>ORIENTATION</strong>
			<div id="orientation" class="menuItem">					
				<form>
					<input type="radio" id="orientation_portrait" name="orientation" value="portrait" checked onchange="changeOrientation('portrait'); return false;" /> Portrait<br />
					<input type="radio" id="orientation_landscape" name="orientation" value="landscape" onchange="changeOrientation('landscape'); return false;"/> Landscape
				</form>
			</div>
			<!-- /Orientation chooser -->
			
			
			<!-- Key Options -->
			<strong>KEY OPTIONS</strong>
			<div id="keyoptions" class="menuItem">					
				<form>
					Show Key: <input type="checkbox" id="showKeyCheckbox" value="showKeyCheckbox" checked onchange="showHideKey(); return false;" />
					<br/>
					<br/>
					<div id="keyPlacement"> Key Placement:<br/>
						<input type="radio" name="keyLeftRight" value="left"  id="leftPlacement" onchange="changeLeftRight('left'); return false;" /> Left 
						<input type="radio" name="keyLeftRight" value="right" id="rightPlacement" checked onchange="changeLeftRight('right'); return false;" /> Right
						<br/>
						<input type="radio" name="keyUpDown" value="up"  id="topPlacement" onchange="changeTopBottom('top'); return false;" /> Up 
						<input type="radio" name="keyUpDown" value="down" id="bottomePlacement" checked onchange="changeTopBottom('bottom'); return false;" /> Down
					</div>
				</form>
			</div>
			<!-- /Key Options -->
			
			
			<!-- Set URL -->
			<strong>Share this map</strong>
			<div id="keyoptions" class="menuItem">					
				<form>
					Generate a URL to share this map with others
					<input type="button" name="getURL" id="getURL" value="Create URL" onclick="setURL(); return false;"/>
					<br/>
					<br/>
					URL for this map: <br/><textarea id="urlText" rows="5" cols="33"></textarea><br/>
					<?php if (isset($_GET["dev"])): ?>					
					URL for this page:  <input type="text"; id="mapUrlText"/>
					URL to embed this map:  <input type="text"; id="embedMapUrlText"/>
					<?php endif; ?>
				</form>
				
				<div id="socialSharing">
					
									
				</div>				
			</div>
			<!-- /Set URL -->
			
			<?php if (isset($_GET["dev"])): ?>
			<!-- Print to image -->
			<strong>Print to Image</strong>
			<div id="keyoptions" class="menuItem">					
				<form>
					Generate an image of this map<br/>
					<input type="button" name="printImage" id="printImage" value="Print To Image" onclick="stitchImage(); return false;"/>
					<br/>
				</form>
			</div>
			<!-- /Print to imgage -->
			<?php endif; ?>
		</div>
		<!-- /controls -->
		
		

	<!--  Print Page -->
	<div id="printpage" class="portrait">

				<!-- The map -->
				<div class="map" id="map"></div>
				<div id="mapStatus">
					<div id="mapScale" style="border-right: solid 1px #999"></div>
					<div id="mapMousePosition" style="min-width: 135px;border-right: solid 1px #999;text-align: center"></div>
					<div id="mapProjection" style="border-right: solid 1px #999"></div>
					<div id="mapOutput"></div>
				</div>
				<!-- /The map -->
				
				
				<div id="key" class="right bottom">
				<h5>Map Key:</h5>
				This map is showing reports from <span id="keyStartDate"></span> to <span id="keyEndDate"></span>.<br/>
				<span id="keyLogic">All reports on this map fall under one or more of the following categories. </span>
				<br/>
				<ul id="keyCategories">
					<li> <div class="swatch" style="background:#cc0000;"></div> ALL CATEGORIES</li>
				</ul>   
				
				</div>

	</div>
	<!--  /Print Page -->