<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Java Script for rendering and controlling a print map
 * 
 *
 * @author     John Etherton <john@ethertontech.com>
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
 */
?>


                     
            var canRedrawMapKey = false;



    		
/**
*
* From http://osgeo-org.1803224.n2.nabble.com/How-to-print-map-area-with-openlayers-td4901023.html
*
*/
var print_wait_win = null;
 function stitchImage() {

			     //-- post a wait message
			    print_wait_win = window.open("<?php url::site();?>stitch/wait", "print_wait_win", "scrollbars=no, status=0, height=300, width=500, resizable=1");
			    
			    
					
 
				var print_url =  '<?php url::site();?>stitch/';
                var size = this.map.getSize(); 
                var tiles = []; 
                
                var nb_layers = this.map.layers.length; 
                var layeri = 0; 
                var nb_vector_layers = 0; 
                var features = []; 
                for (layeri = 0; layeri < nb_layers; layeri++) { 
                        // if the layer isn't visible at this range, or is turned off, skip it 
                        var layer = this.map.layers[layeri]; 
                        if (!layer.getVisibility()) continue; 
                        if (!layer.calculateInRange()) continue; 
                        if (layer.grid) { 
                                // iterate through their grid's tiles, collecting each tile's extent and pixel location at this moment 
                                var grid_length = layer.grid.length; 
                                var row_length = 0; 
                                var tilerow = 0; 
                                var tilei = 0; 
                                for (tilerow = 0; tilerow < grid_length; tilerow++) { 
                                        row_length = layer.grid[tilerow].length; 
                                        tilei = 0; 
                                        for (tilei = 0; tilei < row_length; tilei++) { 
                                                var tile     = layer.grid[tilerow][tilei]; 
                                                var url      = layer.getURL(tile.bounds); 
                                                var position = tile.position; 
                                                var opacity  = layer.opacity ? parseInt(100*layer.opacity) : 100; 
                                                var bounds = tile.bounds; 
                                                tiles[tiles.length] = {url:url, x:position.x, y:position.y, opacity:opacity, bounds:{left:bounds.left, right: bounds.right, top: bounds.top, bottom: bounds.bottom}}; 
                                        } 
                                } 
                        } else { 
                                // get the features of the layer 
                                var olFeatures = layer.features; 
                                var features_temp = []; 
                                var styles = {}; 
                                var nb_features = layer.features.length; 
                                var featuresi = 0; 
                                var nextId = 1; 
                                for (var i = 0; i < nb_features; i++) { 
                                        var feature = olFeatures[i]; 
                                        var style = feature.style || layer.style || layer.styleMap.createSymbolizer(feature, feature.renderIntent); 
                                        if(feature.geometry) { 
                                                if (feature.geometry.CLASS_NAME.search(/point$/i) >= 0) { 
                                                        var fpos = this.map.getLayerPxFromLonLat(new OpenLayers.LonLat(feature.geometry.x, feature.geometry.y)); 
                                                        if(fpos != null) { 
                                                                features_temp[featuresi] = {type: 'point', x: fpos.x, y:fpos.y, style: style}; 
                                                                featuresi++; 
                                                        } 
                                                }
                                                //checks for polygons
                                                if (feature.geometry.CLASS_NAME.search(/polygon$/i) >= 0) { 
                                                		var polyComponentArray = [];
                                                		var polyItemCount = 0;
                                                		//////////////////////////////////////////////////////////////
                                                		//loops over components of a polygon, most likely linear rings
                                                		//////////////////////////////////////////////////////////////
                                                        for(var polyComponentName in feature.geometry.components)
                                                        {
                                                        	var polygonComponent = feature.geometry.components[polyComponentName];
                                                        	//checks if it's a linear ring
                                                        	if(polygonComponent.CLASS_NAME.search(/LinearRing$/) >=0)
                                                        	{
                                                        		var linearRingComponentArray = [];
                                                        		var linRingItemCount = 0;
                                                        		
                                                        		//////////////////////////////////////////
                                                        		//loops over the points in a linear ring
                                                        		/////////////////////////////////////////
                                                        		for(var linearRingComponentName in polygonComponent.components)
                                                        		{
                                                        			var linearRingComponent = polygonComponent.components[linearRingComponentName];
                                                        			if (linearRingComponent.CLASS_NAME.search(/point$/i) >= 0) 
                                                        			{ 
	                                                        			var lrpos = this.map.getLayerPxFromLonLat(new OpenLayers.LonLat(linearRingComponent.x, linearRingComponent.y)); 
	                                                        			if(lrpos != null) 
	                                                        			{ 
	                                                                		linearRingComponentArray[linRingItemCount] = {type: 'point', x: lrpos.x, y:lrpos.y}; 
	                                                                		linRingItemCount++; 
	                                                        			}
	                                                        		}
                                                        		}
                                                        		
                                                        		polyComponentArray[polyItemCount] = {type: 'linearRing', components: linearRingComponentArray};
                                                        		polyItemCount++;
                                                        	}
                                                        }
                                                        
                                                        features_temp[featuresi] = {type: 'polygon', components: polyComponentArray, style: style}; 
                                                        featuresi++;
                                                }  
                                        } 
                                } 
                                features[nb_vector_layers] = features_temp; 
                                nb_vector_layers++; 
                        } 
                                        
                } 

                // hand off the list to our server-side script, which will do the heavy lifting 
                var tiles_json = JSON.stringify(tiles); 
                var features_json = JSON.stringify(features); 
                var viewport_left = parseInt(this.map.layerContainerDiv.style.left); 
                var viewport_top = parseInt(this.map.layerContainerDiv.style.top); 
                var viewport = {top: viewport_top, left: viewport_left}; 
                var viewport_json = JSON.stringify(viewport); 
                var scale = Math.round(this.map.getScale()); 
                OpenLayers.Request.POST( 
                  { url:print_url, 
                        data:OpenLayers.Util.getParameterString({width:size.w,height:size.h,scale:scale,viewport: viewport_json,tiles:tiles_json,features:features_json}), 
                        headers:{'Content-Type':'application/x-www-form-urlencoded'}, 
                        callback: function(request) {
         							print_wait_win.close();
           							window.open(request.responseText);
        							} 
                  } 
                ); 
        }            
            


            
            
            
            



/**
* Creates a URL for the way the map currently looks
*/
function setURL()
{

	$.address.parameter("catId", gCategoryId.join(","));
	$.address.parameter("startDate", $("#startDate").val());
	$.address.parameter("endDate", $("#endDate").val());
	$.address.parameter("z", gMap.getZoom());
	
	var center = gMap.getCenter();
	if(center != null)
	{
		$.address.parameter("lat", center.lat);
		$.address.parameter("lon", center.lon);
	}
	
	if($("#key").is(":hidden"))
	{
		$.address.parameter("hk","1");
	}
	else
	{
		$.address.parameter("hk","");
	}
	
	if($("#key").hasClass("left"))
	{
		$.address.parameter("left","1");
	}
	else
	{
			$.address.parameter("left","");
	}
	
	if($("#key").hasClass("right"))
	{
		$.address.parameter("right","1");
	}
	else
	{
		$.address.parameter("right","");
	}
	
	if($("#key").hasClass("top"))
	{
		$.address.parameter("top","1");
	}
	else
	{
		$.address.parameter("top","");
	}
	
	if($("#key").hasClass("bottom"))
	{
		$.address.parameter("bottom","1");
	}
	else
	{
			$.address.parameter("bottom","");
	}
	
	if($("#printpage").hasClass("landscape"))
	{
		$.address.parameter("orientation", "landscape");
	}
	else
	{
		$.address.parameter("orientation", "portrait");
	}
	
		
	//these don't matter since we're not on the backend	
	//$.address.parameter("currStatus", currentStatus);
	//$.address.parameter("currColorStatus", colorCurrentStatus);
	var currentLogicalOperator = urlParams['lo'];
	$.address.parameter("logic", currentLogicalOperator);
	
	//setting the KML/KMZ layers, just search for all the "layer_" ids that have class active.
	var layerStr = "";
	var i = 0;
	$("a[id^='layer_']").each(function(index){
		if($(this).hasClass("active"))
		{
			i++;
			if(i > 1)
			{
				layerStr += ",";
			}
			//get the id number
			var id = $(this).attr("id").substring(6);
			layerStr += id;
		}
	});
	if(layerStr != "")
	{
		$.address.parameter("layer", layerStr);
	}
	else
	{
		$.address.parameter("layer", "");
	}
	
	
	//get the URL and put it in our text box, plus a real URL param
	$("#urlText").val($.address.baseURL() + "?pdf=print#/?" + $.address.queryString());
	var theFullUrl = $.address.baseURL() + "?pdf=print#/?" + $.address.queryString();
	
	$("#mapUrlText").val($.address.baseURL() + "#/?" + $.address.queryString());

	
	var embedUrl = "<?php echo url::site();?>";
	
	$("#embedMapUrlText").val('<iframe src="' + embedUrl + "iframemap#/?" + $.address.queryString() + '" width="515px" height="430px"></iframe>');
	
	
}
        


/**
* Pass in a string of either "landscape" or something else, and this will
* change the orientation of the map to eithe landscape or portrait
*
*/
function changeOrientation(orientation)
{
	
	var width;
	var height;
	if(orientation == "landscape")
	{
		$("#printpage").removeClass("portrait");
		$("#printpage").addClass("landscape");
	}
	else
	{	
		$("#printpage").removeClass("landscape");	
		$("#printpage").addClass("portrait");
	}
	

	map.updateSize();
	map.pan(1,0);
}

/**
* Shows or hides the map key according to the check state of the #showKeyCheckBox
*
*/
function showHideKey()
{
	if($("#showKeyCheckbox:checked").val())
	{
		$("#key").show();
		$("#keyPlacement").show();
	}
	else
	{
		$("#key").hide();
		$("#keyPlacement").hide();
	}
	
}




/**
* Takes a string as input and from that determines what class to assign to the map key
* and thus what positioning the key will have over the map.
*/
function changeLeftRight(direction)
{
	if(direction == "left")
	{
		$("#key").removeClass("right");
		$("#key").addClass("left");
	}
	else
	{
		$("#key").removeClass("left");
		$("#key").addClass("right");
	}
	return false;
}

/**
* Takes a string as input, and from that determines what class to assign to the map key
* and thus what positioning the key will have over the map.
*/
function changeTopBottom(direction)
{
	if(direction == "top")
	{
		$("#key").removeClass("bottom");
		$("#key").addClass("top");
	}
	else
	{
		$("#key").removeClass("top");
		$("#key").addClass("bottom");
	}
	return false;
}


/**************************************************** END print map specific code **/
		
		
		<?php
			$view = new View('enhancedmap/adminmap_js');  
			$view->map_id = $map_id;
			$view->json_url = $json_url;
			$view->longitude = $longitude;
			$view->latitude = $latitude;
			$view->default_zoom = $default_zoom;
			$view->marker_radius = $marker_radius;
			$view->marker_opacity = $marker_opacity;
			$view->active_startDate = $active_startDate;
			$view->active_endDate = $active_endDate;
			$view->graph_id = $graph_id;
			if(isset($urlParams))
			{
					$view->urlParams = $urlParams;
			}
			$view->render(TRUE);
		?>

							
/**************************************************** START print map specific code AGAIN**/
 
 
 
   		/*
         * Create the Markers Layer
         * Over write the default addMarkers method
         */
        function addMarkers(catID,startDate,endDate, currZoom, currCenter,
            mediaType, thisLayerID, thisLayerType, thisLayerUrl, thisLayerColor)
        {
       
	       
         
            if(startDate == "")
            {startDate = "1";}
            if(endDate == "")
            {endDate = "1";}
            //a poor man's attempt at thread safety
            if(canRedrawMapKey)
            {
            	var currentLogicalOperator = urlParams['lo'];
                $.get("<?php echo url::site(); ?>printmapkey/getKey/" + catID + "/" + currentLogicalOperator + "/" + startDate + "/" + endDate,
                    function(data){
                        $("#key").html(data);                    
                                           
                });
            }
            else
            {
                return;
            }

           var extraParams = getUrlStringFromParams();
			var retVal = $.timeline("<?php echo $graph_id;?>", {categoryId: catID,
			                   startTime: new Date(startDate * 1000),
			                   endTime: new Date(endDate * 1000),
							   mediaType: mediaType
							  }).addMarkers(
								startDate, endDate, gMap.getZoom(),
								gMap.getCenter(), thisLayerID, thisLayerType, 
								thisLayerUrl, thisLayerColor, json_url, extraParams);
           
            map.updateSize();
            return retVal;                                                            
           
        } 
        
        	





 
 
		jQuery(function() {
			
			
			///////////////////////////////////////////////////////////////////////////
			//check and see if we should update the map given the URL
			/////////////////////////////////////////////////////////////////////////
			
			//re center the map
			if($.address.parameter("lon") != null && $.address.parameter("lat"))
			{
				var lon = $.address.parameter("lon");
				var lat  = $.address.parameter("lat");
				var lonlat = new OpenLayers.LonLat(lon,lat);
				gMap.setCenter(lonlat);				
			}
			
			if($.address.parameter("z") != null)
			{
				gMap.zoomTo($.address.parameter("z"));
			}
			
			
			if($.address.parameter("startDate") != null)
			{
				startDate = $.address.parameter("startDate");
				$("#startDate").val(startDate);
				$("#startDate").trigger("change");
				
			}
			
			if($.address.parameter("endDate") != null)
			{
				endDate = $.address.parameter("endDate");
				$("#endDate").val(endDate);
				$("#endDate").trigger("change");
				
			}
			
			
			if($.address.parameter("currColorStatus") != null)
			{
				$("#color_status_1").trigger("click");
			}
			
			if($.address.parameter("logic") != null)
			{
				var logic = $.address.parameter("logic");
				if(logic == "and")
				{
					$("#logicalOperator_2").trigger("click");
				}
				else if (logic == "or")
				{
					//because OR is the default
					//$("#logicalOperator_1").trigger("click");
				}
			}
			
			if($.address.parameter("catId") != null)
			{
				var cats = $.address.parameter("catId");
				//first check if there are "," involved, if not just click and go
				if(cats.search(",") == -1)
				{
					$("#cat_"+cats).trigger("click");
				}
				else
				{
				
					//var catsarray = cats.split(",",-1);
					var catsarray = cats.split(",");
					for(i in catsarray)
					{
						
						//check if the given category is hidden under a parent
						if($("#cat_"+catsarray[i]).is(":hidden"))
						{
							//it's hidden so we need to show it
							//find it's drop_cat and go from there
							$("#cat_"+catsarray[i]).parents("div[id^='child_']").siblings("a[id^='drop_cat_']").trigger("click");
						}
						$("#cat_"+catsarray[i]).trigger("click");						
					}
				}				
			}
			
			//handle the key
			if($.address.parameter("hk") != null)
			{
				$("#key").hide();
				$("#showKeyCheckbox").removeAttr("checked");
			}
			
			if($.address.parameter("left") != null)
			{
				$("#key").removeClass("right");
				$("#key").addClass("left");
				$("#leftPlacement").attr("checked","checked");
			}
			if($.address.parameter("right") != null)
			{
				$("#key").removeClass("left");
				$("#key").addClass("right");
				$("#rightPlacement").attr("checked","checked");
			}
			if($.address.parameter("top") != null)
			{
				$("#key").removeClass("bottom");
				$("#key").addClass("top");
				$("#topPlacement").attr("checked","checked");
			}
			if($.address.parameter("bottom") != null)
			{
				$("#key").removeClass("top");
				$("#key").addClass("bottom");
				$("#bottomPlacement").attr("checked","checked");
			}
			
			if($.address.parameter("orientation") != null)
			{
				var orientation = $.address.parameter("orientation");
				if(orientation == "landscape")
				{
					$("#orientation_landscape").trigger("change");
					$("#orientation_landscape").attr("checked", "checked");
				}
				else
				{
					$("#orientation_portrait").trigger("change");
					$("#orientation_portrait").attr("checked", "checked");
				}
			}
			
			
			//handle layers
			if($.address.parameter("layer") != null)
			{
				var layersArray = $.address.parameter("layer").split(",");
				for(i in layersArray)
				{
					$("#layer_" + layersArray[i]).trigger("click");
				}
			}
			
			$.get("<?php echo url::site(); ?>printmapkey/getKey/" + 
				gCategoryId.join(",") + "/" + 
				$("#currentLogicalOperator").val() + "/" + 
				$("#startDate").val() + "/" + 
				$("#endDate").val(),
				
				function(data){
					$("#key").html(data);					
					canRedrawMapKey = true;
					
					

					
					// Get Current Start Date
					currStartDate = $("#startDate").val();
		
					// Get Current End Date
					currEndDate = $("#endDate").val();
		
					// Get Current Zoom
					currZoom = map.getZoom();
		
					// Get Current Center
					currCenter = map.getCenter();
		
					// Refresh Map
					addMarkers(gCategoryId, currStartDate, currEndDate, currZoom, currCenter);
					
										
			});
			
			
			
		});
		
	
