
            $(function() {
                $('span[title]').hovertip();
            });
            
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
}
