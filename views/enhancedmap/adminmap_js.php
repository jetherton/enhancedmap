<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Java Script for rendering and controlling a map
 * 
 * This file is adapted from the file Ushahidi_Web/appliction/views/main_js.php
 * Originally written by the Ushahidi Team
 *
 *
 * @author     John Etherton <john@ethertontech.com>
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
 */
?>


		$(function() {
		    $('span[title]').hovertip();
		});


		var urlParams = new Object();

		<?php
			if(isset($urlParams))
			{
				foreach($urlParams as $key=>$param)
				{
					echo 'urlParams["'.$key.'"] = "'.$param.'";';
				}
			}
		?>

		//initialize the defaults
		urlParams['lo'] = 'or'; //logical operator
		urlParams['uc'] = '1'; //color status
		urlParams['u'] = '<?php echo isset($show_unapproved) ? $show_unapproved : '1'; ?>';
		
		/**
		* Creates a url string of all the parameters
		* in the urlParams variable
		*/
		function getUrlStringFromParams()
		{
			var returnStr = "";
			var n = 0;
			for(var i in urlParams)
			{
				n++;
				if(n>1){returnStr += "&";}
				if(typeof urlParams[i] == "object")
				{
					for(var j in urlParams[i])
					{
						n++;
						if(n>1){returnStr += "&";}
						returnStr += i + "%5B%5D=" + encodeURIComponent(urlParams[i][j]);	
					}
				}
				else
				{
					returnStr += i + "=" + encodeURIComponent(urlParams[i]);
				}
				
			}
			return returnStr;
		}//end getUrlStringFromParams

		/**
		 * Adds the url params in urlParams to your existing URL
		 */
		function addParamsToUrl(url)
		{
			if(url.indexOf("?")==-1)
			{
				url += "?";
			}
			else
			{
				url += "&";
			}
			return url + getUrlStringFromParams();
		}//end addParamsToUrl


		
		// Map JS

		//number of categories selcted
		var numOfCategoriesSelected = 0;
		//Max number of categories to show at once, if you have more than 1000 reports with lots of categories you might want to turn this down
		var maxCategories = 14;
		// Map Object
		var map;		
		// Selected Category
		var gCategoryId = ['0']; //default to all
		// color the reports who's status is unapproved black?
		var colorCurrentStatus = '1';
		// Selected Layer
		var thisLayer;
		// WGS84 Datum
		var proj_4326 = new OpenLayers.Projection('EPSG:4326');
		// Spherical Mercator
		var proj_900913 = new OpenLayers.Projection('EPSG:900913');
		// Change to 1 after map loads
		var mapLoad = 0;
		// /json or /json/cluster depending on if clustering is on
		var default_json_url = "<?php echo $json_url;?>";
		// Current json_url, if map is switched dynamically between json and json_cluster
		var json_url = default_json_url;

		var time_line_url = "<?php echo isset($json_timeline_url) ? $json_timeline_url : 'bigmap_json/timeline/'; ?>";
		
		// Global list for current KML overlays in display
		var kmlOverlays = [];
		
		var baseUrl = "<?php echo url::base(); ?>";
		var longitude = <?php echo $longitude; ?>;
		var latitude = <?php echo $latitude; ?>;
		var defaultZoom = <?php echo $default_zoom; ?>;
		var markerRadius = <?php echo $marker_radius; ?>;
		var markerOpacity = "<?php echo $marker_opacity; ?>";
		var selectedFeature;

		var gMarkerOptions = {baseUrl: baseUrl, longitude: longitude,
		                     latitude: latitude, defaultZoom: defaultZoom,
							 markerRadius: markerRadius,
							 markerOpacity: markerOpacity,
							 protocolFormat: OpenLayers.Format.GeoJSON};
							
		/*
		Create the Markers Layer
		*/
		function addMarkers(catID,startDate,endDate, currZoom, currCenter,
			mediaType, thisLayerID, thisLayerType, thisLayerUrl, thisLayerColor)
		{

			var extraParams = getUrlStringFromParams();
				
			var retVal = $.timeline("<?php echo $graph_id;?>", {categoryId: catID,
			                   startTime: new Date(startDate * 1000),
			                   endTime: new Date(endDate * 1000),
							   mediaType: mediaType
							  }).addMarkers(
								startDate, endDate, gMap.getZoom(),
								gMap.getCenter(), thisLayerID, thisLayerType, 
								thisLayerUrl, thisLayerColor, json_url, extraParams);
								

			
			return retVal;								
		}



		/******
		* Removes the category ID from the string currentCat
		*******/
		function removeCategoryFilter(idToRemove)
		{
			for(i = 0; i < gCategoryId.length; i++)
			{
				if (gCategoryId[i] == idToRemove)
				{
					//deactivate
					$("#cat_"+idToRemove).removeClass("active");
					gCategoryId.splice(i,1);
					break;
				}
			}
		}//end removeCategoryFilter()


		/*
		Display loader as Map Loads
		*/
		function onMapStartLoad(event)
		{
			if ($("#loader"))
			{
				$("#loader").show();
			}

			if ($("#OpenLayers\\.Control\\.LoadingPanel_4"))
			{
				$("#OpenLayers\\.Control\\.LoadingPanel_4").show();
			}
		}

		/*
		Hide Loader
		*/
		function onMapEndLoad(event)
		{
			if ($("#loader"))
			{
				$("#loader").hide();
			}

			if ($("#OpenLayers\\.Control\\.LoadingPanel_4"))
			{
				$("#OpenLayers\\.Control\\.LoadingPanel_4").hide();
			}
		}

		/*
		Close Popup
		*/
		function onPopupClose(evt)
		{			
			if(selectedFeature != null)
			{
				selectControl.unselect(selectedFeature); //this seemed to change things.
				selectedFeature = null;
			}
			
			
		}

		/*
		Display popup when feature selected
		*/
		function onFeatureSelect(event)
		{
					
			
			selectedFeature = event.feature;
			var feature = event.feature;
			// Since KML is user-generated, do naive protection against
			// Javascript.

			zoom_point = event.feature.geometry.getBounds().getCenterLonLat();
			lon = zoom_point.lon;
			lat = zoom_point.lat;

			var content = "<div class=\"infowindow\"><div class=\"infowindow_list\">"+event.feature.attributes.name + "<div style=\"clear:both;\"></div></div>";
			content = content + "\n<div class=\"infowindow_meta\"><a href='javascript:zoomToSelectedFeature("+ lon + ","+ lat +", 1)'><?php echo Kohana::lang('ui_main.zoom_in'); ?></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='javascript:zoomToSelectedFeature("+ lon + ","+ lat +", -1)'><?php echo Kohana::lang('ui_main.zoom_out'); ?></a></div>";
			content = content + "</div>";			

			if (content.search("<script") != -1)
			{
				content = "Content contained Javascript! Escaped content below.<br />" + content.replace(/</g, "&lt;");
			}
			popup = new OpenLayers.Popup.FramedCloud("chicken", 
					event.feature.geometry.getBounds().getCenterLonLat(),
					new OpenLayers.Size(100,100),
					content,
					null, true, function(evt){ map.removePopup(feature.popup);
				            feature.popup.destroy();
				            feature.popup = null;
				            
				            
							for(i in map.featureUnSelectionEventRegistrants)
							{
								var func = map.featureUnSelectionEventRegistrants[i];
								func(feature);
							}});
			event.feature.popup = popup;
			map.addPopup(popup);
			
			//let other JS items know that there was a feature select
			
			for(i in map.featureSelectionEventRegistrants)
			{
				var func = map.featureSelectionEventRegistrants[i];
				func(selectedFeature);
			}
		}

		/*
		Destroy Popup Layer
		*/
        function onFeatureUnselect(event)
		{
			/*
            map.removePopup(event.feature.popup);
            event.feature.popup.destroy();
            event.feature.popup = null;
            
            
			for(i in map.featureUnSelectionEventRegistrants)
			{
				var func = map.featureUnSelectionEventRegistrants[i];
				func(event.feature);
			}
			*/
        }

		// Refactor Clusters On Zoom
		// *** Causes the map to load json twice on the first go
		// *** Need to fix this!
		function mapZoom(event)
		{
			// Prevent this event from running on the first load
			if (mapLoad > 0)
			{
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
				
				
			}
		}

		function mapMove(event)
		{
			// Prevent this event from running on the first load
			if (mapLoad > 0)
			{
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
			}
		}


		/*
		Refresh Graph on Slider Change
		*/
		function refreshGraph(startDate, endDate)
		{
			
			var startTime = new Date(startDate * 1000);
			var endTime = new Date(endDate * 1000);

			// daily
			var graphData = "";

			var iValue = "";
			if ((endTime - startTime) / (1000 * 60 * 60 * 24) <= 3)
			{
				iValue = "&i=hour";
			}
			else if ((endTime - startTime) / (1000 * 60 * 60 * 24) <= 124)
			{
				iValue = "&i=day";
			}

			var categoriesStr = "";
			for(i = 0; i < gCategoryId.length; i++)
			{
				if(categoriesStr.length != 0)
				{
					categoriesStr = categoriesStr + "&";
				}
				categoriesStr += "c%5B%5D=" + gCategoryId[i];
			}

			var fullUrl = "<?php echo url::site(); ?>" + time_line_url + "?"+categoriesStr+iValue;

			fullUrl = addParamsToUrl(fullUrl); 

			$.getJSON(fullUrl, function(data) {
				graphData = data[0];

				gTimeline = $.timeline("<?php echo $graph_id;?>",{categoryId: gCategoryId,
					startTime: new Date(startDate * 1000),
				    endTime: new Date(endDate * 1000), mediaType: gMediaType,
					markerOptions: gMarkerOptions,
					graphData: graphData
				});
				gTimeline.plot();
			});
		

		}

		/*
		Zoom to Selected Feature from within Popup
		*/
		function zoomToSelectedFeature(lon, lat, zoomfactor)
		{
			var lonlat = new OpenLayers.LonLat(lon,lat);

			// Get Current Zoom
			currZoom = map.getZoom();
			// New Zoom
			newZoom = currZoom + zoomfactor;
			// Center and Zoom
			map.setCenter(lonlat, newZoom);
			// Remove Popups
			for (var i=0; i<map.popups.length; ++i)
			{
				map.removePopup(map.popups[i]);
			}
		}

		/*
		Add KML/KMZ Layers
		*/
		function switchLayer(layerID, layerURL, layerColor)
		{
			if ( $("#layer_" + layerID).hasClass("active") )
			{
				new_layer = map.getLayersByName("Layer_"+layerID);
				if (new_layer)
				{
					for (var i = 0; i <?php echo '<'; ?> new_layer.length; i++)
					{
						map.removeLayer(new_layer[i]);
					}
					
					// Part of #2168 fix
					// Added by E.Kala <emmanuel(at)ushahidi.com>
					// Remove the layer from the list of KML overlays - kmlOverlays
					if (kmlOverlays.length == 1)
					{
						kmlOverlays.pop();
					}
					else if (kmlOverlays.length > 1)
					{
						// Temporarily store the current list of overlays
						tempKmlOverlays = kmlOverlays;
						
						// Re-initialize the list of overlays
						kmlOverlays = [];
						
						// Search for the overlay that has just been removed from display
						for (var i = 0; i < tempKmlOverlays.length; i ++)
						{
							if (tempKmlOverlays[i].name != "Layer_"+layerID)
							{
								kmlOverlays.push(tempKmlOverlays[i]);
							}
						}
						// Unset the working list
						tempKmlOverlays = null;
					}
				}
				$("#layer_" + layerID).removeClass("active");

			}
			else
			{
				$("#layer_" + layerID).addClass("active");

				// Get Current Zoom
				currZoom = map.getZoom();

				// Get Current Center
				currCenter = map.getCenter();
				
				// Add New Layer
				addMarkers('', '', '', currZoom, currCenter, '', layerID, 'layers', layerURL, layerColor);
			}
		}

		/*
		Toggle Layer Switchers
		*/
		function toggleLayer(link, layer){
			if ($("#"+link).text() == "<?php echo Kohana::lang('ui_main.show'); ?>")
			{
				$("#"+link).text("<?php echo Kohana::lang('ui_main.hide'); ?>");
			}
			else
			{
				$("#"+link).text("<?php echo Kohana::lang('ui_main.show'); ?>");
			}
			$('#'+layer).toggle(500);
		}							

		jQuery(function() {
			var map_layer;
			markers = null;
			var catID = '';
			OpenLayers.Strategy.Fixed.prototype.preload=true;
			
			/*
			- Initialize Map
			- Uses Spherical Mercator Projection
			- Units in Metres instead of Degrees					
			*/
			var options = {
				units: "mi",
				numZoomLevels: 21,
				controls:[],
				projection: proj_900913,
				'displayProjection': proj_4326,
				eventListeners: {
						"zoomend": mapMove
				    },
				'theme': null
				};
			map = new OpenLayers.Map('<?php echo $map_id; ?>', options);
			//the Enhanced map event system
			map.featureSelectionEventRegistrants = new Array();
			map.featureUnSelectionEventRegistrants = new Array();
			map.categoryChangeEventRegistrants = new Array();
			map.addControl( new OpenLayers.Control.LoadingPanel({minSize: new OpenLayers.Size(573, 366)}) );
			
			<?php echo map::layers_js(FALSE); ?>
			map.addLayers(<?php echo map::layers_array(FALSE); ?>);
			
			
			// Add Controls
			map.addControl(new OpenLayers.Control.Navigation());
			map.addControl(new OpenLayers.Control.Attribution());
			map.addControl(new OpenLayers.Control.PanZoomBar());
			map.addControl(new OpenLayers.Control.MousePosition(
				{
					div: document.getElementById('mapMousePosition'),
					numdigits: 5
				}));    
			map.addControl(new OpenLayers.Control.Scale('mapScale'));
            map.addControl(new OpenLayers.Control.ScaleLine());
			map.addControl(new OpenLayers.Control.LayerSwitcher());
			
			// display the map projection
			document.getElementById('mapProjection').innerHTML = map.projection;
				
			gMap = map;



		
			//////////////////////////////////////////////////////////////////////////////////////
			// Parent Category opener
			$("a[id^='drop_cat_']").click(function()
			{
				//get the ID of the category we're dealing with
				var catID = this.id.substring(9);

				//if the kids aren't currenlty shown, show them
				if( !$("#child_"+catID).is(":visible"))
				{
					$("#child_"+catID).show();
					$(this).html("-");
					//since all we're doing is showing things we don't need to update the map
					// so just bounce
					
					$("a[id^='cat_']").addClass("forceRefresh"); //have to do this because IE sucks
					$("a[id^='cat_']").removeClass("forceRefresh"); //have to do this because IE sucks
					
					return false;
				}
				else //kids are shown, deactivate them.
				{
					var kids = $("#child_"+catID).find('a');
					kids.each(function(){
						if($(this).hasClass("active"))
						{
							//remove this category ID from the list of IDs to show
							var idNum = $(this).attr("id").substring(4);
							//removeCategoryFilter(idNum);
						}
					});
					$("#child_"+catID).hide();
					$(this).html("+");
					return false;
				}
			});

			
			//////////////////////////////////////////////////////////////////////////////////////
			// Category Switch Action
			$("a[id^='cat_']").click(function()
			{
				//the id of the category that just changed
				var catID = this.id.substring(4);
				
				//the list of categories we're currently showing
				numOfCategoriesSelected = gCategoryId.length;
				 
				 
				//First we check if the "All Categories" button was pressed. If so unselect everything else
				if( catID == 0)
				{
					if( !$("#cat_0").hasClass("active")) //it's being activated so unselect everything else
					{
						//unselect all other selected categories
						while (gCategoryId.length > 0)
						{
							removeCategoryFilter(gCategoryId[0]);
						}				
					}
				}
				else
				{ //we're dealing wtih single categories or parents
				
				
					//first check and see if we're dealing with a parent category
					if( $("#child_"+catID).find('a').length > 0)
					{
						//are we turning the parent on or off?
						var parentActive = $("#cat_"+catID).hasClass("active");
						
						//we want to turn on/off kid categories						
						var kids = $("#child_"+catID).find('a');
						kids.each(function(){
							
							var kidNum = $(this).attr("id").substring(4);
							if(!parentActive)
							{
								$(this).addClass("active");

							}
							else
							{
								removeCategoryFilter(kidNum);
								$(this).removeClass("active");
							}
						});
															
					}//end of if for dealing with parents
					
					//check if we're dealing with a child
					if($(this).attr("cat_parent"))
					{
						//get the parent ID
						parentID = $(this).attr("cat_parent");
						//if it's active deactivate it
						//first check and see if we're adding or removing this category
						if($("#cat_"+parentID).hasClass("active")) //it is active so make it unactive and remove this category from the list of categories we're looking at.
						{ 
							removeCategoryFilter(parentID);
							//also deactiveate any siblings
							var kids = $("#child_"+parentID).find('a');
							kids.each(function(){
								var kidNum = $(this).attr("id").substring(4);
								removeCategoryFilter(kidNum);
								$(this).removeClass("active");
							});
						}
						
					}//end of dealing with kids
					
					//first check and see if we're adding or removing this category
					if($("#cat_"+catID).hasClass("active")) //it is active so make it unactive and remove this category from the list of categories we're looking at.
					{ 
						removeCategoryFilter(catID);
					}
					else //it isn't active so make it active
					{ 
						//seems on really big maps with lots of reports we can't do more than 4 categories at a time.
						if(numOfCategoriesSelected < (maxCategories+1))
						{
							$("#cat_"+catID).addClass("active");
							
							//make sure the "all categories" button isn't active
							removeCategoryFilter("0");
							
							gCategoryId.push(catID);
						}
						else
						{
							alert("Sorry, do to the size and complexity of the information on this site we cannot display more than "+maxCategories+" categories at once");
						}
					}
				}
				
				
				//check to make sure something is selected. If nothing is selected then select "all gategories"
				if( gCategoryId.length == 0)
				{
					$("#cat_0").addClass("active");
					gCategoryId.push("0");
				}

				// Destroy any open popups
				onPopupClose();
				
				// Get Current Zoom
				currZoom = map.getZoom();
				
				// Get Current Center
				currCenter = map.getCenter();

				var startDate = $("#startDate").val();
				var endDate = $("#endDate").val();
				refreshGraph(startDate, endDate);

				var startTime = new Date($("#startDate").val() * 1000);
				var endTime = new Date($("#endDate").val() * 1000);
				
				//notify those who are registered
				for(i in map.categoryChangeEventRegistrants)
				{
					var func = map.categoryChangeEventRegistrants[i];
					func(gCategoryId);
				}
				
				
				addMarkers(gCategoryId, $("#startDate").val(), $("#endDate").val(), currZoom, currCenter, gMediaType);
				
				
				
				
				return false;
			});
			
			
			
			//////////////////////////////////////////////////////
			//status switcher
			//////////////////////////////////////////////////////
			$("a[id^='status_']").click(function()
			{
				
				var statID = this.id.substring(7);
				//check and see if the just clicked element should have "active" class or not
				if( $("#status_" + statID).hasClass("active"))
				{
					//we have it so remove it
					$("#status_" + statID).removeClass("active"); // Remove All active
				}
				else
				{
					//we don't have it so add it
					$("#status_" + statID).addClass("active"); // Add Highlight
				}
			

				//both are active
				if($("#status_1").hasClass("active") && $("#status_2").hasClass("active"))
				{
					urlParams['u'] = 3;
				}
				else if($("#status_1").hasClass("active") && !($("#status_2").hasClass("active")))
				{
					urlParams['u'] = 2;
				}
				else if(!($("#status_1").hasClass("active")) && $("#status_2").hasClass("active"))
				{
					urlParams['u'] = 1;
				}
				else //this shouldn't happen, so undo what was done above, can't have no reports showing. That's just silly
				{
					if( $("#status_" + statID).hasClass("active"))
					{
						//we have it so remove it
						$("#status_" + statID).removeClass("active"); // Remove All active
					}
					else
					{
						//we don't have it so add it
						$("#status_" + statID).addClass("active"); // Add Highlight
					}
					return false;
				}
				
			
				// Destroy any open popups
				onPopupClose();
				
				// Get Current Zoom
				currZoom = map.getZoom();
				
				// Get Current Center
				currCenter = map.getCenter();

				var startDate = $("#startDate").val();
				var endDate = $("#endDate").val();
				refreshGraph(startDate, endDate);
				
				var startTime = new Date($("#startDate").val() * 1000);
				var endTime = new Date($("#endDate").val() * 1000);
				addMarkers(gCategoryId, $("#startDate").val(), $("#endDate").val(), currZoom, currCenter, gMediaType);
								
					
				
				return false;
			});
			






			//////////////////////////////////////////////////////
			//Color status switcher
			//////////////////////////////////////////////////////
			$("#color_status_1").click(function()
			{
				//switch the status
				if( $("#color_status_1").hasClass("active"))
				{
					//we have it so remove it
					$("#color_status_1").removeClass("active"); // make it not active
					urlParams['uc'] = 1;					
				}
				else
				{
					$("#color_status_1").addClass("active"); // make it active
					urlParams['uc'] = 2;
					colorCurrentStatus = 2;
				}
				
				// Destroy any open popups
				onPopupClose();
				
				// Get Current Zoom
				currZoom = map.getZoom();
				
				// Get Current Center
				currCenter = map.getCenter();

				var startDate = $("#startDate").val();
				var endDate = $("#endDate").val();
				refreshGraph(startDate, endDate);
				
				var startTime = new Date($("#startDate").val() * 1000);
				var endTime = new Date($("#endDate").val() * 1000);
				addMarkers(gCategoryId, $("#startDate").val(), $("#endDate").val(), currZoom, currCenter, gMediaType);
								
					

				return false;
			});
			
			
			
		
			//////////////////////////////////////////////////////
			//Logical Operator switcher
			//////////////////////////////////////////////////////
			$("a[id^='logicalOperator_']").click(function()
			{
				
				//switch whatever the current setting is. 
				if( $("#logicalOperator_1").hasClass("active")) //was OR, now make it AND
				{
					$("#logicalOperator_1").removeClass("active"); // not OR
					$("#logicalOperator_2").addClass("active"); // is AND					
					urlParams['lo'] = "and";
				}
				else //was AND, now make it OR
				{
					$("#logicalOperator_2").removeClass("active"); // not AND
					$("#logicalOperator_1").addClass("active"); // is OR
					urlParams['lo'] = "or";
				}

				// Destroy any open popups
				onPopupClose();
				
				// Get Current Zoom
				currZoom = map.getZoom();
				
				// Get Current Center
				currCenter = map.getCenter();

				var startDate = $("#startDate").val();
				var endDate = $("#endDate").val();
				refreshGraph(startDate, endDate);	
				
				var startTime = new Date($("#startDate").val() * 1000);
				var endTime = new Date($("#endDate").val() * 1000);
				addMarkers(gCategoryId, $("#startDate").val(), $("#endDate").val(), currZoom, currCenter, gMediaType);
								
				
				
				return false;
			});

			
			// Sharing Layer[s] Switch Action
			$("a[id^='share_']").click(function()
			{
				var shareID = this.id.substring(6);
				
				if ( $("#share_" + shareID).hasClass("active") )
				{
					share_layer = map.getLayersByName("Share_"+shareID);
					if (share_layer)
					{
						for (var i = 0; i < share_layer.length; i++)
						{
							map.removeLayer(share_layer[i]);
						}
					}
					$("#share_" + shareID).removeClass("active");
					
				} 
				else
				{
					$("#share_" + shareID).addClass("active");
					
					// Get Current Zoom
					currZoom = map.getZoom();

					// Get Current Center
					currCenter = map.getCenter();
					
					// Add New Layer
					addMarkers('', '', '', currZoom, currCenter, '', shareID, 'shares');
				}
			});

			// Exit if we don't have any incidents
			if (!$("#startDate").val())
			{
				map.setCenter(new OpenLayers.LonLat(<?php echo $longitude ?>, <?php echo $latitude ?>), 5);
				return;
			}

			//not sure how this happened, but we now have to check if the selectTuUISlider change event
			//has been fired twice. 
			var lastSliderChangeTimeStamp = 0;			
			//Accessible Slider/Select Switch
			$("select#startDate, select#endDate").selectToUISlider({
				labels: 4,
				labelSrc: 'text',
				sliderOptions: {
					change: function(e, ui)
					{
						
						//it seems that this event gets fired twice some times,
						//so to keep that from happening, we check if the event
						//has been fired in the last 100 miliseconds, if it has
						//ignore it.
						if((e.timeStamp - lastSliderChangeTimeStamp) < 100)
						{
							return;
						}
						lastSliderChangeTimeStamp = e.timeStamp;
						var startDate = $("#startDate").val();
						var endDate = $("#endDate").val();
						
						// Get Current Zoom
						currZoom = map.getZoom();
						
						// Get Current Center
						currCenter = map.getCenter();
						
						
												
						refreshGraph(startDate, endDate);
						// Refresh Map
						
						addMarkers(gCategoryId, startDate, endDate, currZoom, currCenter, gMediaType);
						
					}
				}
			});
			
			
			var allGraphData = "";
			var dailyGraphData = "";
			
			var startTime = <?php echo $active_startDate ?>;	// Or in human readable format <?php echo date("F j, Y, g:i:a", $active_startDate); ?>
			
			var endTime = <?php echo $active_endDate ?>;	// Or in human readable format <?php echo date("F j, Y, g:i:a", $active_endDate); ?>
					
			// get the closest existing dates in the selection options
			options = $('#startDate > optgroup > option').map(function()
			{
				return $(this).val(); 
			});
			//figure out what time is the greatest time, that is less than the startTime
			var foundStartTime = false;
			for(i = 1; i < options.length; i++)
			{
				var time = parseInt(options[i]);
				if(time == startTime)
				{
					foundStartTime = true;
					break;
				}
				else if(time > startTime)
				{
					startTime = options[i-1];
					foundStartTime = true;
					break;
				}
				
			}
			//if the original start time is after the last option date
			if(!foundStartTime)
			{
				startTime = options[options.length-1];
			}
			
			options = $('#endDate > optgroup > option').map(function()
			{
				return $(this).val(); 
			});
			endTime = $.grep(options, function(n,i)
			{
			  var newVal = parseInt(n);
			  return newVal >= endTime;
			})[0];
			

			gMediaType = 0;
			//$("#startDate").val(startTime);
			//$("#endDate").val(endTime);
			
			// Initialize Map
			refreshGraph(startTime, endTime);
			addMarkers(gCategoryId, startTime, endTime, '', '', gMediaType);
			
			
			// Media Filter Action
			$('.filters li a').click(function()
			{
				var startTimestamp = $("#startDate").val();
				var endTimestamp = $("#endDate").val();
				var startTime = new Date(startTimestamp * 1000);
				var endTime = new Date(endTimestamp * 1000);
				gMediaType = parseFloat(this.id.replace('media_', '')) || 0;
				
				// Get Current Zoom
				currZoom = map.getZoom();
					
				// Get Current Center
				currCenter = map.getCenter();
				
				// Refresh Map
				addMarkers(gCategoryId, startTimestamp, endTimestamp, 
				           currZoom, currCenter, gMediaType);
				
				$('.filters li a').attr('class', '');
				$(this).addClass('active');

				var fullUrl = "<?php echo url::site(); ?>" + time_line_url + "?"+categoriesStr+iValue;
				fullUrl = addParamsToUrl(fullUrl);
				
				gTimeline = $.timeline({categoryId: gCategoryId, startTime: startTime, 
				    endTime: endTime, mediaType: gMediaType,
					url: fullUrl
				});
				gTimeline.plot();
			});
			
			$('#playTimeline').click(function()
			{
			    gTimelineMarkers = gTimeline.addMarkers(gStartTime.getTime()/1000,
					$.dayEndDateTime(gEndTime.getTime()/1000), gMap.getZoom(),
					gMap.getCenter(),null,null,null,null,"json");
				gTimeline.playOrPause('raindrops');
			});
			
			
						
			
		
		/*This function is used to register event handlers for feature selection*/
		map.registerMapFeatureSelectionHandler = function(func)
		{
			this.featureSelectionEventRegistrants.push(func);
		}
		
		/*This is used to unregister event handlers for feature selection*/
		map.unregisterMapFeatureSelectionHandler = function (func)
		{
			var tempArray = new Array();
			for(i in this.featureSelectionEventRegistrants)
			{
				var t = this.featureSelectionEventRegistrants[i];
				if(t != func)
				{
					tempArray.push(t);
				}
			}
			this.featureSelectionEventRegistrants = tempArray;
		}
		
		/*This function is used to register event handlers for feature unselection*/
		map.registerMapFeatureUnSelectionHandler = function (func)
		{
			this.featureUnSelectionEventRegistrants.push(func);
		}
		
		/*This is used to unregister event handlers for feature unselection*/
		map.unregisterMapFeatureUnSelectionHandler = function (func)
		{
			var tempArray = new Array();
			for(i in this.featureUnSelectionEventRegistrants)
			{
				var t = this.featureUnSelectionEventRegistrants[i];
				if(t != func)
				{
					tempArray.push(t);
				}
			}
			this.featureUnSelectionEventRegistrants = tempArray;
		}
		
		
		
		/*This function is used to register event handlers for category changes events*/
		map.registerCategoryChangeHandler = function (func)
		{
			this.categoryChangeEventRegistrants.push(func);
		}
		
		/*This is used to unregister event handlers for category change events*/
		map.unregisterCategoryChangeHandler = function (func)
		{
			var tempArray = new Array();
			for(i in this.categoryChangeEventRegistrants)
			{
				var t = this.categoryChangeEventRegistrants[i];
				if(t != func)
				{
					tempArray.push(t);
				}
			}
			this.categoryChangeEventRegistrants = tempArray;
		}
		
		
		

		/**
		 * creates an array that maps incident ids to markers
		 */
		map.mapIncidentsToMarkers = function (markers)
		{
			//zero out the old array
			var incidentsToMarkers = new Array();
			
			//get the features
			var features = markers[0].features;
			//loop over the features
			for(i in features)
			{
				var feature = features[i];
				//loop over the ids
				for(j in feature.attributes.ids)
				{
					var id = feature.attributes.ids[j];
					incidentsToMarkers[id] = feature;		
				}
			}
			
			return incidentsToMarkers;
			
		}		
			
			
		});


		
		function setDotSize()
		{
			var radiusModifier = $("#dot_size").val();
			var newRadius = 0;
			switch(radiusModifier)
			{
				case '1':
				newRadius = '2';
				break;
				
				case '2':
				newRadius = '4';
				break;
				
				case '3':
				newRadius = '6';
				break;
				
				case '4':
				newRadius = '8';
				break;
			}
			//set the marker radius
			markerRadius = newRadius;
			//get some meta data ready to go
			var startTime = new Date($("#startDate").val() * 1000);
			var endTime = new Date($("#endDate").val() * 1000);
			// Get Current Zoom
			currZoom = map.getZoom();
			// Get Current Center
			currCenter = map.getCenter();
			
			//redraw the map
			addMarkers(gCategoryId, $("#startDate").val(), $("#endDate").val(), currZoom, currCenter, gMediaType);
			
			//set the preferences for this user
			setCookie('dot_size', radiusModifier, 20);
		}	
		
		
		function setCookie(c_name,value,exdays)
		{
			var exdate=new Date();
			exdate.setDate(exdate.getDate() + exdays);
			var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
			document.cookie=c_name + "=" + c_value + "; path=/";
		}
		
		var originalListOrder = new Array();
		var originalListOrderKids = new Array();
		function alphabetize(filterId)
		{
			var sortAlphabetically = !$("#"+filterId+"_alphabetize_link").hasClass('active');
			if(sortAlphabetically)
			{
				$("#"+filterId+"_alphabetize_link").addClass('active');
				
				var titleArray = new Array();
				var elementArray = new Array();
				var count = 0;
				var parentsArray = new Array();
				//gather all the elements up
				$("ul#"+filterId+" a[id^='cat_']").each(function(index){
					var element = $(this).parent();
					count++;
					//skip this first one, since it's all cats
					if(count > 1 && $(this).attr("cat_parent") == undefined)
					{
						var text = $("div.category-title", this).text() + "_" + $(this).attr("id");
						titleArray.push(text);
						originalListOrder.push(text);
						elementArray[text] = element;
					}
					else
					{
						parentsArray[$(this).attr("cat_parent")] = $(this).attr("cat_parent");
					}				
				});
				
				//now sort
				titleArray.sort();
				
				//now go through the elements again
				for(i in titleArray)
				{
					var text = titleArray[i];
					var element = elementArray[text];
					//move the element
					$(element).detach().appendTo("#" + filterId);
		
				}
				
				//do it all over again for the kids
				for(i in parentsArray)
				{
					var parentId = parentsArray[i];
					if(parentId == undefined)
					{
						continue;
					}
					originalListOrderKids[parentId] = new Array();
					var titleArray = new Array();
					var elementArray = new Array();
					//gather all the elements up
					$("ul#"+filterId+" li div#child_"+parentId+" a[id^='cat_']").each(function(index){
						var element = $(this).parent();
						var text = $("div.category-title", this).text() + "_" + $(this).attr("id");
						titleArray.push(text);
						elementArray[text] = element;
						originalListOrderKids[parentId].push(text);
					});
					
					//now sort
					titleArray.sort();
					
					//now go through the elements again
					for(i in titleArray)
					{
						var text = titleArray[i];
						var element = elementArray[text];
						//move the element
						$(element).detach().appendTo("#" + filterId+" div#child_"+parentId+" ul");
			
					}
				} //end loop over parentsArray
				
			} //end if alphabetical
			else
			{
				$("#"+filterId+"_alphabetize_link").removeClass('active');
				
				//init the array
				var elementArray = new Array();
				var parentsArray = new Array();
				var count = 0;				
				//gather all the elements up
				$("ul#"+filterId+" a[id^='cat_']").each(function(index){
					var element = $(this).parent();
					count++;
					//skip this first one, since it's all cats
					if(count>1 && $(this).attr("cat_parent") == undefined)
					{
						var text = $("div.category-title", this).text() + "_" + $(this).attr("id");
						elementArray[text] = element;
					}
					else
					{
						parentsArray[$(this).attr("cat_parent")] = $(this).attr("cat_parent");
					}							
				});

				//put things back the way they were
				for(i in originalListOrder)
				{
					var text = originalListOrder[i];
					var element = elementArray[text];
					//move the element
					$(element).detach().appendTo("#"+filterId);
		
				}//end putting things into the new order
				
				
				//do it all over again for the kids
				for(i in parentsArray)
				{
					var parentId = parentsArray[i];
					if(parentId == undefined)
					{
						continue;
					}
				
					var elementArray = new Array();
					//gather all the elements up
					$("ul#"+filterId+" li div#child_"+parentId+" a[id^='cat_']").each(function(index){
						var element = $(this).parent();
						var text = $("div.category-title", this).text() + "_" + $(this).attr("id");
						elementArray[text] = element;
					});
					
					
					//now go through the elements again
					for(i in originalListOrderKids[parentId])
					{
						var text = originalListOrderKids[parentId][i];
						var element = elementArray[text];
						//move the element
						$(element).detach().appendTo("#" + filterId+" div#child_"+parentId+" ul");
			
					}
				} //end loop over parentsArray
			}//end if default ordering			
		}//end of alphabetize
		
		
		
	function setClustering()
	{
		var isClustering = $("#clustering").val();
		if(isClustering == '1')
		{
			//we are clustering
			json_url = json_url+'/cluster';
		}
		else
		{
			//we are not clustering
			json_url = json_url.substr(0, json_url.indexOf('/cluster'));
		}
		
		//get some meta data ready to go
		var startTime = new Date($("#startDate").val() * 1000);
		var endTime = new Date($("#endDate").val() * 1000);
		// Get Current Zoom
		currZoom = map.getZoom();
		// Get Current Center
		currCenter = map.getCenter();
		
		//redraw the map
		addMarkers(gCategoryId, $("#startDate").val(), $("#endDate").val(), currZoom, currCenter, gMediaType);
		
		//set the preferences for this user
		setCookie('clustering', isClustering, 20);
	}
