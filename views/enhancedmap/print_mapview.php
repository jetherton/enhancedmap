<?php defined('SYSPATH') or die('No direct script access.');
/**
 * View for the print map
 * 
 *
 * @author     John Etherton <john@ethertontech.com>
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
 */
?>


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
		
			<?php echo $div_status_filter; ?>
			
			<?php echo $div_boolean_filter; ?>
			
			<?php echo $div_dotsize_selector; ?>
			
			<?php echo $div_clustering_selector;?>
		
			<?php echo $div_categories_filter; ?>
		
			<?php echo $div_layers_filter; ?>
			
			<?php echo $div_shares_filter; ?>
			
			
			
			
			
			

		




		
		
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
					<input type="radio" id="orientation_portrait" name="orientation" value="portrait" checked onclick="this.blur();" onchange="changeOrientation('portrait'); return false;" /> Portrait<br />
					<input type="radio" id="orientation_landscape" name="orientation" value="landscape"  onclick="this.blur();" onchange="changeOrientation('landscape'); return false;"/> Landscape
				</form>
			</div>
			<!-- /Orientation chooser -->
			
			
			<!-- Key Options -->
			<strong>KEY OPTIONS</strong>
			<div id="keyoptions" class="menuItem">					
				<form>
					Show Key: <input type="checkbox" id="showKeyCheckbox" value="showKeyCheckbox" checked onclick="this.blur();" onchange="showHideKey(); return false;" />
					<br/>
					<br/>
					<div id="keyPlacement"> Key Placement:<br/>
						<input type="radio" name="keyLeftRight" value="left"  id="leftPlacement" onclick="this.blur();" onchange="changeLeftRight('left'); return false;" /> Left 
						<input type="radio" name="keyLeftRight" value="right" id="rightPlacement"  onclick="this.blur();" checked  onchange="changeLeftRight('right'); return false;" /> Right
						<br/>
						<input type="radio" name="keyUpDown" value="up"  id="topPlacement" onclick="this.blur();" onchange="changeTopBottom('top'); return false;" /> Up 
						<input type="radio" name="keyUpDown" value="down" id="bottomePlacement" onclick="this.blur();" checked onchange="changeTopBottom('bottom'); return false;" /> Down
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