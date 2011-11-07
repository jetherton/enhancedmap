<?php
/**
 * Admin helper
 * 
 */
class adminmap_helper_Core {

	// Table Prefix
	protected static $table_prefix;

	static function init()
	{
		// Set Table Prefix
		self::$table_prefix = Kohana::config('database.default.table_prefix');
	}

	/**************************************************************************************************************
      * Given all the parameters returns a list of incidents that meet the search criteria
      */
	public static function setup_adminmap($map_controller, $map_view = "adminmap/mapview", $map_css = "adminmap/css/adminmap")		
	{
	
		//set the CSS for this
		if($map_css != null)
		{
			plugin::add_stylesheet($map_css);
		}
		
		plugin::add_javascript("adminmap/js/jquery.flot");
		plugin::add_javascript("adminmap/js/excanvas.min");
		plugin::add_javascript("adminmap/js/timeline");
		plugin::add_javascript("adminmap/js/jquery.hovertip-1.0");
		
		$map_controller->template->content = new View($map_view);
		
		// Get Default Color
		$map_controller->template->content->default_map_all = Kohana::config('settings.default_map_all');
	}
	
	
	/****
	* Sets up the overlays and shares
	*/
	public static function set_overlays_shares($map_controller)
	{
				// Get all active Layers (KMZ/KML)
		$layers = array();
		$config_layers = Kohana::config('map.layers'); // use config/map layers if set
		if ($config_layers == $layers) {
			foreach (ORM::factory('layer')
					  ->where('layer_visible', 1)
					  ->find_all() as $layer)
			{
				$layers[$layer->id] = array($layer->layer_name, $layer->layer_color,
					$layer->layer_url, $layer->layer_file);
			}
		} else {
			$layers = $config_layers;
		}
		$map_controller->template->content->layers = $layers;

		// Get all active Shares
		$shares = array();
		foreach (ORM::factory('sharing')
				  ->where('sharing_active', 1)
				  ->find_all() as $share)
		{
			$shares[$share->id] = array($share->sharing_name, $share->sharing_color);
		}
		$map_controller->template->content->shares = $shares;
	}
	
	
	/*
	* this makes the map for this plugin
	*/
	public static function set_map($template, $themes, $json_url, $json_timeline_url, $javascript_view = 'adminmap/mapview_js',
							$div_map_view = 'adminmap/main_map', $div_timeline_view = 'adminmap/main_timeline')
	{
	
		////////////////////////////////////////////////////////////////Map and Slider Blocks////////////////////////////////////////////////////////////////////////////
		$div_map = new View($div_map_view);
		$div_timeline = new View($div_timeline_view);
			// Filter::map_main - Modify Main Map Block
			Event::run('ushahidi_filter.map_main', $div_map);
			// Filter::map_timeline - Modify Main Map Block
			Event::run('ushahidi_filter.map_timeline', $div_timeline);
		$template->content->div_map = $div_map;
		$template->content->div_timeline = $div_timeline;

	
		///////////////////////////////////////////////////////////////SETUP THE DATES////////////////////////////////////////////////////////////////////////////
        // Get The START, END and Incident Dates
        $startDate = "";
		$endDate = "";
		$display_startDate = 0;
		$display_endDate = 0;

		$db = new Database();
        // Next, Get the Range of Years
		$query = $db->query('SELECT DATE_FORMAT(incident_date, \'%Y-%c\') AS dates FROM '.self::$table_prefix.'incident WHERE incident_active = 1 GROUP BY DATE_FORMAT(incident_date, \'%Y-%c\') ORDER BY incident_date');

		$first_year = date('Y');
		$last_year = date('Y');
		$first_month = 1;
		$last_month = 12;
		$i = 0;

		foreach ($query as $data)
		{
			$date = explode('-',$data->dates);

			$year = $date[0];
			$month = $date[1];

			// Set first year
			if($i == 0)
			{
				$first_year = $year;
				$first_month = $month;
			}

			// Set last dates
			$last_year = $year;
			$last_month = $month;

			$i++;
		}

		$show_year = $first_year;
		$selected_start_flag = TRUE;
		while($show_year <= $last_year)
		{
			$startDate .= "<optgroup label=\"".$show_year."\">";

			$s_m = 1;
			if($show_year == $first_year)
			{
				// If we are showing the first year, the starting month may not be January
				$s_m = $first_month;
			}

			$l_m = 12;
			if($show_year == $last_year)
			{
				// If we are showing the last year, the ending month may not be December
				$l_m = $last_month;
			}

			for ( $i=$s_m; $i <= $l_m; $i++ )
			{
				if ( $i < 10 )
				{
					// All months need to be two digits
					$i = "0".$i;
				}
				$startDate .= "<option value=\"".strtotime($show_year."-".$i."-01")."\"";
				if($selected_start_flag == TRUE)
				{
					$display_startDate = strtotime($show_year."-".$i."-01");
					$startDate .= " selected=\"selected\" ";
					$selected_start_flag = FALSE;
				}
				$startDate .= ">".date('M', mktime(0,0,0,$i,1))." ".$show_year."</option>";
			}
			$startDate .= "</optgroup>";

			$endDate .= "<optgroup label=\"".$show_year."\">";
			for ( $i=$s_m; $i <= $l_m; $i++ )
			{
				if ( $i < 10 )
				{
					// All months need to be two digits
					$i = "0".$i;
				}
				$endDate .= "<option value=\"".strtotime($show_year."-".$i."-".date('t', mktime(0,0,0,$i,1))." 23:59:59")."\"";

                if($i == $l_m AND $show_year == $last_year)
				{
					$display_endDate = strtotime($show_year."-".$i."-".date('t', mktime(0,0,0,$i,1))." 23:59:59");
					$endDate .= " selected=\"selected\" ";
				}
				$endDate .= ">".date('M', mktime(0,0,0,$i,1))." ".$show_year."</option>";
			}
			$endDate .= "</optgroup>";

			// Show next year
			$show_year++;
		}

		Event::run('ushahidi_filter.active_startDate', $display_startDate);
		Event::run('ushahidi_filter.active_endDate', $display_endDate);
		Event::run('ushahidi_filter.startDate', $startDate);
		Event::run('ushahidi_filter.endDate', $endDate);
		
		$template->content->div_timeline->startDate = $startDate;
		$template->content->div_timeline->endDate = $endDate;
		///////////////////////////////////////////////////////////////MAP JAVA SCRIPT////////////////////////////////////////////////////////////////////////////
		
		//turn the map on, also turn on the timeline
		//$template->flot_enabled = TRUE; //this is done using our own custom .js files in the adminmap/js folder.
		$themes->map_enabled = true;
		
		//check if we're on the front end, if we are then the template and themese will be different
		if($themes != $template)
		{
			$themes->main_page = true;
		}
		
		$themes->js = new View($javascript_view);
		$themes->js->default_map = Kohana::config('settings.default_map');
		$themes->js->default_zoom = Kohana::config('settings.default_zoom');
		

		// Map Settings
		$clustering = Kohana::config('settings.allow_clustering');
		$marker_radius = Kohana::config('map.marker_radius');
		$marker_opacity = Kohana::config('map.marker_opacity');
		$marker_stroke_width = Kohana::config('map.marker_stroke_width');
		$marker_stroke_opacity = Kohana::config('map.marker_stroke_opacity');

		// pdestefanis - allows to restrict the number of zoomlevels available
		$numZoomLevels = Kohana::config('map.numZoomLevels');
		$minZoomLevel = Kohana::config('map.minZoomLevel');
	   	$maxZoomLevel = Kohana::config('map.maxZoomLevel');

		// pdestefanis - allows to limit the extents of the map
		$lonFrom = Kohana::config('map.lonFrom');
		$latFrom = Kohana::config('map.latFrom');
		$lonTo = Kohana::config('map.lonTo');
		$latTo = Kohana::config('map.latTo');

		
		$themes->js->json_url = $json_url;
		$themes->js->json_timeline_url  = $json_timeline_url;
		$themes->js->marker_radius =
			($marker_radius >=1 && $marker_radius <= 10 ) ? $marker_radius : 5;
		$themes->js->marker_opacity =
			($marker_opacity >=1 && $marker_opacity <= 10 )
			? $marker_opacity * 0.1  : 0.9;
		$themes->js->marker_stroke_width =
			($marker_stroke_width >=1 && $marker_stroke_width <= 5 ) ? $marker_stroke_width : 2;
		$themes->js->marker_stroke_opacity =
			($marker_stroke_opacity >=1 && $marker_stroke_opacity <= 10 )
			? $marker_stroke_opacity * 0.1  : 0.9;

		// pdestefanis - allows to restrict the number of zoomlevels available
		$themes->js->numZoomLevels = $numZoomLevels;
		$themes->js->minZoomLevel = $minZoomLevel;
		$themes->js->maxZoomLevel = $maxZoomLevel;

		// pdestefanis - allows to limit the extents of the map
		$themes->js->lonFrom = $lonFrom;
		$themes->js->latFrom = $latFrom;
		$themes->js->lonTo = $lonTo;
		$themes->js->latTo = $latTo;

		$themes->js->default_map = Kohana::config('settings.default_map');
		$themes->js->default_zoom = Kohana::config('settings.default_zoom');
		$themes->js->latitude = Kohana::config('settings.default_lat');
		$themes->js->longitude = Kohana::config('settings.default_lon');
		$themes->js->default_map_all = Kohana::config('settings.default_map_all');
		$themes->js->active_startDate = $display_startDate;
		$themes->js->active_endDate = $display_endDate;
		


	}
	
	public static function set_categories($map_controller, $on_backend = false, $group = false)
	{
	
		// Check for localization of parent category
		// Get locale
		$l = Kohana::config('locale.language.0');
	
		$parent_categories = array();
	
		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//Check to see if we're dealing with a group, and thus
		//should show group specific categories
		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		if($group != false)
		{
			//check and make sure the simpel groups category is installed
			$plugin = ORM::factory('plugin')
				->where('plugin_name', 'simplegroups')
				->where('plugin_active', '1')
				->find();
			if(!$plugin)
			{
				throw new Exception("A group was set in adminmap_helper::set_categories() when the SimpleGroupl plugin is not installed");
			}
		
			$cats = ORM::factory('simplegroups_category');
			if(!$on_backend)
			{	
				$cats = $cats->where('category_visible', '1');
			}
			$cats = $cats->where('parent_id', '0');
			$cats = $cats->where('applies_to_report', 1);
			$cats = $cats->where('simplegroups_groups_id', $group->id)
				->find_all() ;
			foreach ($cats as $category)
			{				
				/////////////////////////////////////////////////////////////////////////////////////////////
				// Get the children
				/////////////////////////////////////////////////////////////////////////////////////////////
				$children = array();
				foreach ($category->children as $child)
				{
					// Check for localization of child category

					$translated_title = Simplegroups_category_lang_Model::simplegroups_category_title($child->id,$l);

					if($translated_title)
					{
						$display_title = $translated_title;
					}
					else
					{
						$display_title = $child->category_title;
					}

					$children["sg_".$child->id] = array(
						$display_title,
						$child->category_color,
						$child->category_image
					);
					
				}

				

				$translated_title = Simplegroups_category_lang_Model::simplegroups_category_title($category->id,$l);

				if($translated_title)
				{
					$display_title = $translated_title;
				}else{
					$display_title = $category->category_title;
				}

				// Put it all together				
				$parent_categories["sg_".$category->id] = array(
					$display_title,
					$category->category_color,
					$category->category_image,
					$children
				);				
			}
		}

		/////////////////////////////////////////////////////////////////////////////////////////////
        // Get all active top level categories
		/////////////////////////////////////////////////////////////////////////////////////////////
		
		$cats = ORM::factory('category');
		if(!$on_backend)
		{	
			$cats = $cats->where('category_visible', '1');
		}
		$cats = $cats->where('parent_id', '0')
			->orderby('category_position', 'asc')		
			->find_all() ;
		foreach ($cats as $category)
		{
			/////////////////////////////////////////////////////////////////////////////////////////////
			// Get the children
			/////////////////////////////////////////////////////////////////////////////////////////////
			$children = array();
			foreach ($category->children as $child)
			{
				// Check for localization of child category

				$translated_title = Category_Lang_Model::category_title($child->id,$l);

				if($translated_title)
				{
					$display_title = $translated_title;
				}
				else
				{
					$display_title = $child->category_title;
				}

				$children[$child->id] = array(
					$display_title,
					$child->category_color,
					$child->category_image
				);

				if ($child->category_trusted)
				{ // Get Trusted Category Count
					$trusted = ORM::factory("incident")
						->join("incident_category","incident.id","incident_category.incident_id")
						->where("category_id",$child->id);
					if ( ! $trusted->count_all())
					{
						unset($children[$child->id]);
					}
				}
			}

			

			$translated_title = Category_Lang_Model::category_title($category->id,$l);

			if($translated_title)
			{
				$display_title = $translated_title;
			}else{
				$display_title = $category->category_title;
			}

			// Put it all together
			$parent_categories[$category->id] = array(
				$display_title,
				$category->category_color,
				$category->category_image,
				$children
			);

			if ($category->category_trusted)
			{ // Get Trusted Category Count
				$trusted = ORM::factory("incident")
					->join("incident_category","incident.id","incident_category.incident_id")
					->where("category_id",$category->id);
				if ( ! $trusted->count_all())
				{
					unset($parent_categories[$category->id]);
				}
			}
		}
		
		
		
		
		$map_controller->template->content->categories = $parent_categories;
	}//end method
	
	
	



	/////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////METHODS FOR the JSON CONTROLLER
	///////////////////////////////////////////////////////////////////////////////////////////////////
	
	
	/**
	* Generate JSON in NON-CLUSTER mode
	* $edit_report_path is used to set where the link to edit/view a report should be set to
	* $on_the_back_end sets whether or not this user is viewing this data from the backend
	*/
	public static function json_index($json_controller, $edit_report_path = 'admin/reports/edit/', $on_the_back_end = true,
		$extra_where_text = "",
		$joins = array(),
		$custom_category_to_table_mapping = array(),
		$link_target = "_self")
	{
		$json = "";
		$json_item = "";
		$json_array = array();
		$cat_array = array();
		$color = Kohana::config('settings.default_map_all');
		$default_color = Kohana::config('settings.default_map_all');
		$icon = "";

		$category_ids = array();
		$incident_id = "";
		$neighboring = "";
		$media_type = "";
		$show_unapproved="3"; //1 show only approved, 2 show only unapproved, 3 show all
		$logical_operator = "or";

		if( isset($_GET['c']) AND ! empty($_GET['c']) )
		{
			//check if there are any ',' in the category
			if((strpos($_GET['c'], ",")===false) && is_numeric($_GET['c']))
			{
				$category_ids = array($_GET['c']);	
			}
			else
			{
				$category_ids = explode(",", $_GET['c'],-1); //get rid of that trailing ";"
			}
		}
		else
		{
			$category_ids = array("0");
		}
		$is_all_categories = false;
		If(count($category_ids) == 0 || $category_ids[0] == '0')
		{
			$is_all_categories = true;
		}

		
		$approved_text = "";
		if( $on_the_back_end)
		{
			//figure out if we're showing unapproved stuff or what.
			if (isset($_GET['u']) AND !empty($_GET['u']))
			{
			    $show_unapproved = (int) $_GET['u'];
			}		
			if($show_unapproved == 1)
			{
				$approved_text = "incident.incident_active = 1 ";
			}
			else if ($show_unapproved == 2)
			{
				$approved_text = "incident.incident_active = 0 ";
			}
			else if ($show_unapproved == 3)
			{
				$approved_text = " (incident.incident_active = 0 OR incident.incident_active = 1) ";
			}
		}
		else
		{
			$approved_text = "incident.incident_active = 1 ";
		}
		

		
		
		//should we color unapproved reports a different color?
		$color_unapproved = 2;
		if (isset($_GET['uc']) AND !empty($_GET['uc']))
		{
		    $color_unapproved = (int) $_GET['uc'];
		}
		
		if (isset($_GET['lo']) AND !empty($_GET['lo']))
		{
		    $logical_operator =  $_GET['lo'];
		}
		
		
		
		

		if (isset($_GET['i']) AND !empty($_GET['i']))
		{
		    $incident_id = (int) $_GET['i'];
		}

		if (isset($_GET['n']) AND !empty($_GET['n']))
		{
		    $neighboring = (int) $_GET['n'];
		}

		$where_text = '';
		// Do we have a media id to filter by?
		if (isset($_GET['m']) AND !empty($_GET['m']) AND $_GET['m'] != '0')
		{
		    $media_type = (int) $_GET['m'];
		    $where_text .= " AND ".self::$table_prefix."media.media_type = " . $media_type;
		}

		if (isset($_GET['s']) AND !empty($_GET['s']))
		{
		    $start_date = (int) $_GET['s'];
		    $where_text .= " AND UNIX_TIMESTAMP(".self::$table_prefix."incident.incident_date) >= '" . $start_date . "'";
		}

		if (isset($_GET['e']) AND !empty($_GET['e']))
		{
		    $end_date = (int) $_GET['e'];
		    $where_text .= " AND UNIX_TIMESTAMP(".self::$table_prefix."incident.incident_date) <= '" . $end_date . "'";
		}

		
		//get our new custom color based on the categories we're working with
		$color = self::merge_colors($category_ids, $custom_category_to_table_mapping);

		$incidents = adminmap_reports::get_reports_list_by_cat($category_ids, 
			$approved_text, 
			$where_text. " ". $extra_where_text, 
			$logical_operator,
			"incident.id",
			"asc",
			$joins,
			$custom_category_to_table_mapping);

		$curr_id = "not a number";
		$cat_names = array();
		$colors = array();
		$last_marker = null;
		$isnt_first = false;
		    
		$json_item_first = "";  // Variable to store individual item for report detail page
		foreach ($incidents as $marker)
		{
			//if the is a new incident, or the same incident we looked at last time, then skip all of this
			if($isnt_first && ($curr_id != $marker->id))
			{
			

				$json_item = "{";
				$json_item .= "\"type\":\"Feature\",";
				$json_item .= "\"properties\": {";
				$json_item .= "\"id\": \"".$last_marker->id."\", \n";
				$cat_names_txt = "";
				$count = 0;
				If(!$is_all_categories && $logical_operator != "and")
				{
					$cat_names_txt = "<br/><br/>Falls under categories:<br/> ";
					foreach($cat_names as $cat_name)
					{
						$count++;
						if($count>1)
						{
							$cat_names_txt .= ", ";
						}
						$cat_names_txt .= $cat_name;
						
					}
				}
				
				$json_item .= "\"name\":\"" .date("n/j/Y", strtotime($marker->incident_date)).":<br/>". str_replace(chr(10), ' ', str_replace(chr(13), ' ', "<a target='".$link_target."' href='" . url::base() . $edit_report_path . $last_marker->id . "'>" . htmlentities($last_marker->incident_title) . "</a>".$cat_names_txt)) . "\",";
				//for compatiblity with the InfoWindows plugin
				$json_item .= "\"link\":\"" .url::base(). "$edit_report_path{$last_marker->id}\",";

				if (isset($category)) 
				{
					$json_item .= "\"category\":[" . $category_id . "], ";
				} 
				else 
				{
					$json_item .= "\"category\":[0], ";
				}

				//check if it's a unapproved/unactive report
				if($last_marker->incident_active == 0 && $color_unapproved==2)
				{
					$item_color = "000000";
					$json_item .= "\"color\": \"000000\", \n";
					$json_item .= "\"icon\": \"".$icon."\", \n";
				}
				//check if we're looking at all categories
				elseif(count($category_ids) == 0 || $category_ids[0] == '0')
				{	
					$item_color = $default_color;
					$json_item .= "\"color\": \"".$default_color."\", \n";
					$json_item .= "\"icon\": \"".$icon."\", \n";
				}
				//check if we're using AND
				elseif($logical_operator=="and")
				{					
					$item_color = $color;
					$json_item .= "\"color\": \"".$color."\", \n";
					$json_item .= "\"icon\": \"".$icon."\", \n";
				}
				//else we're using OR to combine categories
				else
				{
					$color = self::merge_colors_for_dots($colors);
					$item_color = $color;
					$json_item .= "\"color\": \"".$color."\", \n";
					$json_item .= "\"icon\": \"".$icon."\", \n";
				}

				$json_item .= "\"timestamp\": \"" . strtotime($last_marker->incident_date) . "\"";
				$json_item .= "},";
				$json_item .= "\"geometry\": {";
				$json_item .= "\"type\":\"Point\", ";
				$json_item .= "\"coordinates\":[" . $last_marker->location->longitude . ", " . $last_marker->location->latitude . "]";
				$json_item .= "}";
				$json_item .= "}";

				
								// Get Incident Geometries				
				$geometry = self::_get_geometry($last_marker->id, $last_marker->incident_title, $last_marker->incident_date, $on_the_back_end, $item_color, $link_target);
				if (count($geometry))
				{
					$json_item = implode(",", $geometry);
					array_push($json_array, $json_item);
				}
				else //if there are markers then don't draw the central dot, it's just an aproximation
				{
					if ($last_marker->id == $incident_id)
					{
						$json_item_first = $json_item;
					}
					else
					{
						array_push($json_array, $json_item);
					}
				}
				$cat_array = array();
				
				
				
				//reset the variables
				$cat_names = array();
				$colors = array();
			}//end if
			
			//keep track how many categories we've found for this report
			$last_marker = $marker;
			$curr_id = $marker->id;
			$isnt_first = true;
			
			$incident_array = $marker->as_array();
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			//Now we try and figure out which category this report was matched to
			//////////////////////////////////////////////////////////////////////////////////////////////////////////////
			
			if(isset($incident_array["is_parent"]) && $incident_array["is_parent"] != 0) 
			{
				//echo $incident->incident_title." parent matched\r\n";
				$cat_names[$incident_array["parent_id"]] = $incident_array["parent_title"];
				$colors[$incident_array["parent_id"]] = $incident_array["parent_color"];
			}
			elseif(isset($incident_array["is_child"]) && $incident_array["is_child"] != 0) 
			{
				//echo $incident->incident_title." kid matched\r\n";			
				$cat_names[$incident_array["cat_id"]] = $incident_array["category_title"];
				$colors[$incident_array["cat_id"]] = $incident_array["color"];
			}
			//now the fun part, loop through all the custom categories
			foreach($custom_category_to_table_mapping as $name=>$custom_cat)
			{
				if(isset($incident_array["is_".$name."_parent"]) && $incident_array["is_".$name."_parent"] != 0) 
				{
					//echo $incident->incident_title." $name parent matched\r\n";
					$cat_names[$name."_".$incident_array[$name."_parent_cat_id"]] = $incident_array[$name."_parent_title"];
					$colors[$name."_".$incident_array[$name."_parent_cat_id"]] = $incident_array[$name."_parent_color"];
				}
				elseif(isset($incident_array["is_".$name."_child"]) && $incident_array["is_".$name."_child"] != 0) 
				{
					//echo $incident->incident_title." $name kid matched. With color: ".$incident_array[$name."_color"]."\r\n";			
					$cat_names[$name."_".$incident_array[$name."_cat_id"]] = $incident_array[$name."_title"];
					$colors[$name."_".$incident_array[$name."_cat_id"]] = $incident_array[$name."_color"];
				}
			}
			
		}//end loop
		
		//catch the last one
		if($last_marker != null)
		{
				$json_item = "{";
				$json_item .= "\"type\":\"Feature\",";
				$json_item .= "\"properties\": {";
				$json_item .= "\"id\": \"".$last_marker->id."\", \n";
				$cat_names_txt = "";
				$count = 0;
				If(!$is_all_categories && $logical_operator != "and")
				{
					$cat_names_txt = "<br/><br/>Falls under categories:<br/> ";
					foreach($cat_names as $cat_name)
					{
						$count++;
						if($count>1)
						{
							$cat_names_txt .= ", ";
						}
						$cat_names_txt .= $cat_name;
						
					}
				}
				$json_item .= "\"name\":\"" . str_replace(chr(10), ' ', str_replace(chr(13), ' ', "<a target='".$link_target."' href='" . url::base() . $edit_report_path . $last_marker->id . "'>" . htmlentities($last_marker->incident_title) . "</a>".$cat_names_txt)) . "\",";
				//compatibility with the InfoWindow Plugin
				$json_item .= "\"link\":\"" .url::base(). "$edit_report_path{$last_marker->id}\",";


				if (isset($category)) 
				{
					$json_item .= "\"category\":[" . $category_id . "], ";
				} 
				else 
				{
					$json_item .= "\"category\":[0], ";
				}

				//check if it's a unapproved/unactive report
				if($last_marker->incident_active == 0 && $color_unapproved==2)
				{
					$item_color = "000000";
					$json_item .= "\"color\": \"000000\", \n";
					$json_item .= "\"icon\": \"".$icon."\", \n";
				}
				//check if we're looking at all categories
				elseif(count($category_ids) == 0 || $category_ids[0] == '0')
				{	
					$item_color = $default_color;
					$json_item .= "\"color\": \"".$default_color."\", \n";
					$json_item .= "\"icon\": \"".$icon."\", \n";
				}
								//check if we're using AND
				elseif($logical_operator=="and")
				{					
					$item_color = $color;
					$json_item .= "\"color\": \"".$color."\", \n";
					$json_item .= "\"icon\": \"".$icon."\", \n";
				}
				//else we're using OR to combine categories
				else
				{
					$color = self::merge_colors_for_dots($colors);
					$item_color = $color;
					$json_item .= "\"color\": \"".$color."\", \n";
					$json_item .= "\"icon\": \"".$icon."\", \n";
				}

				$json_item .= "\"timestamp\": \"" . strtotime($last_marker->incident_date) . "\"";
				$json_item .= "},";
				$json_item .= "\"geometry\": {";
				$json_item .= "\"type\":\"Point\", ";
				$json_item .= "\"coordinates\":[" . $last_marker->location->longitude . ", " . $last_marker->location->latitude . "]";
				$json_item .= "}";
				$json_item .= "}";
				
				// Get Incident Geometries				
				$geometry = self::_get_geometry($last_marker->id, $last_marker->incident_title, $last_marker->incident_date, $on_the_back_end, $item_color, $link_target);
				if (count($geometry))
				{
					$json_item = implode(",", $geometry);
					array_push($json_array, $json_item);
				}
				else //if there are markers then don't draw the central dot, it's just an aproximation 
				{
					if ($last_marker->id == $incident_id)
					{
						$json_item_first = $json_item;
					}
					else
					{
						array_push($json_array, $json_item);
				}
				}
				$cat_array = array();
				
				
		}//end catching the last one
		
		if ($json_item_first)
		{ // Push individual marker in last so that it is layered on top when pulled into map
		    array_push($json_array, $json_item_first);
		}
		$json = implode(",", $json_array);

		header('Content-type: application/json');
		$json_controller->template->json = $json;
	}
	
	
	
	
	
	
	
	/************************************************************************************************
	* Function, this'll merge colors. Given an array of category IDs it'll return a hex string
	* of all the colors merged together
	*/
	public static function merge_colors($category_ids_temp, $custom_category_to_table_mapping = array())
	{
		//because I might unset some of the values in the $category_ids array
		$category_ids = adminmap_reports::array_copy($category_ids_temp);
		
		//check if we're looking at category 0
		if(count($category_ids) == 0 || $category_ids[0] == '0')
		{
			return Kohana::config('settings.default_map_all');
		}
		
		$red = 0;
		$green = 0;
		$blue = 0;
		////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//Lets handle custom categories
		foreach($custom_category_to_table_mapping as $cat_name=>$custom_cats)
		{
			$where_str_color = ""; //to get the colors we're gonna use
			$i = 0;
			foreach($category_ids as $key=>$id)
			{
				//check if we have a custom cateogry ID
				$delimiter_pos = strpos($id, "_");
				if($delimiter_pos !== false)
				{
					//get the custom category name
					$custom_cat_name = substr($id, 0, $delimiter_pos);
					//get the custom category's numeric id
					$custom_cat_id = substr($id,$delimiter_pos + 1);
					
					//does the custom_cat_name match our current custom cat
					if($cat_name == $custom_cat_name)
					{
						$i++;
						if($i > 1)
						{
							$where_str_color = $where_str_color . " OR ";
						}
						$where_str_color = $where_str_color . "id = ".$custom_cat_id;
						
						unset($category_ids[$key]);			
					}
				}
			}
			if($where_str_color != "")
			{
				//get the custom categories themselves and add up their colors:
				// Retrieve all the categories with their colors
				$categories = ORM::factory($custom_cats['child'])
				    ->where($where_str_color)
				    ->find_all();

				//now for each color break it into RGB, add them up, then normalize
				
				foreach($categories as $category)
				{
					$color = $category->category_color;
					$numeric_colors = self::_hex2RGB($color);
					$red = $red + $numeric_colors['red'];
					$green = $green + $numeric_colors['green'];
					$blue = $blue + $numeric_colors['blue'];
				}
			}
		}//end loop through all custom categorie sources
		
		

		////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//Next lets handle regular categories		
		//first lets figure out the composite color that we're gonna usehere
		if(count($category_ids) > 0)
		{
			$where_str_color = ""; //to get the colors we're gonna use
			$i = 0;
			foreach($category_ids as $id)
			{
				$i++;
				if($i > 1)
				{
					$where_str_color = $where_str_color . " OR ";
				}
				$where_str_color = $where_str_color . "id = ".$id;
			}


			// Retrieve all the categories with their colors
			$categories = ORM::factory('category')
			    ->where($where_str_color)
			    ->find_all();

			//now for each color break it into RGB, add them up, then normalize
			
			foreach($categories as $category)
			{
				$color = $category->category_color;
				$numeric_colors = self::_hex2RGB($color);
				$red = $red + $numeric_colors['red'];
				$green = $green + $numeric_colors['green'];
				$blue = $blue + $numeric_colors['blue'];
			}
		}
		
		//now normalize
		$color_length = sqrt( ($red*$red) + ($green*$green) + ($blue*$blue));
	
		//make sure there's no divide by zero
		if($color_length == 0)
		{
			$color_length = 255;
		}
		$red = ($red / $color_length) * 255;
		$green = ($green / $color_length) * 255;
		$blue = ($blue / $color_length) * 255;
	
		
		//pad with zeros if there's too much space
		$red = dechex($red);
		if(strlen($red) < 2)
		{
			$red = "0".$red;
		}
		$green = dechex($green);
		if(strlen($green) < 2)
		{
			$green = "0".$green;
		}
		$blue = dechex($blue);
		if(strlen($blue) < 2)
		{
			$blue = "0".$blue;
		}
		//now put the color back together and return it
		return $red.$green.$blue;
		
	}//end method merge colors



/************************************************************************************************
	* Function, this'll merge colors. Given an array of category IDs it'll return a hex string
	* of all the colors merged together
	*/
	public static function merge_colors_for_dots($colors)
	{
		//check if we're dealing with just one color
		if(count($colors)==1)
		{
			foreach($colors as $color)
			{
				return $color;
			}
		}
		//now for each color break it into RGB, add them up, then normalize
		$red = 0;
		$green = 0;
		$blue = 0;
		foreach($colors as $color)
		{
			$numeric_colors = self::_hex2RGB($color);
			$red = $red + $numeric_colors['red'];
			$green = $green + $numeric_colors['green'];
			$blue = $blue + $numeric_colors['blue'];
		}
		//now normalize
		$color_length = sqrt( ($red*$red) + ($green*$green) + ($blue*$blue));
	
		//make sure there's no divide by zero
		if($color_length == 0)
		{
			$color_length = 255;
		}
		$red = ($red / $color_length) * 255;
		$green = ($green / $color_length) * 255;
		$blue = ($blue / $color_length) * 255;
	
		
		//pad with zeros if there's too much space
		$red = dechex($red);
		if(strlen($red) < 2)
		{
			$red = "0".$red;
		}
		$green = dechex($green);
		if(strlen($green) < 2)
		{
			$green = "0".$green;
		}
		$blue = dechex($blue);
		if(strlen($blue) < 2)
		{
			$blue = "0".$blue;
		}
		//now put the color back together and return it
		return $red.$green.$blue;
		
	}//end method merge colors



	private static function _hex2RGB($hexStr, $returnAsString = false, $seperator = ',') 
	{
		$hexStr = preg_replace("/[^0-9A-Fa-f]/", '', $hexStr); // Gets a proper hex string
		$rgbArray = array();
		if (strlen($hexStr) == 6) 
		{ //If a proper hex code, convert using bitwise operation. No overhead... faster
			$colorVal = hexdec($hexStr);
			$rgbArray['red'] = 0xFF & ($colorVal >> 0x10);
			$rgbArray['green'] = 0xFF & ($colorVal >> 0x8);
			$rgbArray['blue'] = 0xFF & $colorVal;
		} 
		elseif (strlen($hexStr) == 3) 
		{ //if shorthand notation, need some string manipulations
			$rgbArray['red'] = hexdec(str_repeat(substr($hexStr, 0, 1), 2));
			$rgbArray['green'] = hexdec(str_repeat(substr($hexStr, 1, 1), 2));
			$rgbArray['blue'] = hexdec(str_repeat(substr($hexStr, 2, 1), 2));
		} 
		else 
		{
			return false; //Invalid hex color code
		}
		return $returnAsString ? implode($seperator, $rgbArray) : $rgbArray; // returns the rgb string or the associative array
	}







	
	
	

/***************************************************************************************************************
     * Generate JSON in CLUSTER mode
     * $edit_report_path sets the path to the link to edit/view a report
     * $list_report_path sets the path to view a cluster of reports
     * $on_the_back_end sets whether or not this user is looking at this from the front end or back end
     */
    public static function json_cluster($controller, 
	$edit_report_path = 'admin/reports/edit/', 
	$list_reports_path = "admin/adminmap_reports/index/",
	$on_the_back_end = true,
	$extra_where_text = "",
	$joins = array(),
	$custom_category_to_table_mapping = array(),
	$link_target = "_self")
    {

    	//check to see how we're adding GET params 
    	$url_param_join_character = (strpos($list_reports_path, "?") === false) ? "?" : "&";
    	
        // Database
        $db = new Database();

        $json = "";
        $json_item = "";
        $json_array = array();
		$geometry_array = array();

        $color = Kohana::config('settings.default_map_all');
		$default_color = Kohana::config('settings.default_map_all');
        $icon = "";
		$logical_operator = "or";
	
		$show_unapproved="3"; //1 show only approved, 2 show only unapproved, 3 show all
		if($on_the_back_end)
		{
			//figure out if we're showing unapproved stuff or what.
			if (isset($_GET['u']) AND !empty($_GET['u']))
			{
			    $show_unapproved = (int) $_GET['u'];
			}
			$approved_text = "";
			if($show_unapproved == 1)
			{
				$approved_text = "incident.incident_active = 1 ";
			}
			else if ($show_unapproved == 2)
			{
				$approved_text = "incident.incident_active = 0 ";
			}
			else if ($show_unapproved == 3)
			{
				$approved_text = " (incident.incident_active = 0 OR incident.incident_active = 1) ";
			}	
		}
		else
		{
			$approved_text = "incident.incident_active = 1 ";
			$show_unapproved = 1;
		}
		
		
		//should we color unapproved reports a different color?
		$color_unapproved = 2;
	        if (isset($_GET['uc']) AND !empty($_GET['uc']))
	        {
		    $color_unapproved = (int) $_GET['uc'];
	        }
		
	
		if (isset($_GET['lo']) AND !empty($_GET['lo']))
	        {
		    $logical_operator =  $_GET['lo'];
	        }
	
	
	        // Get Zoom Level
	        $zoomLevel = (isset($_GET['z']) AND !empty($_GET['z'])) ?
	            (int) $_GET['z'] : 8;
	
	        //$distance = 60;
	        $distance = ((10000000 >> $zoomLevel) / 100000) / 2.5;
		
	
	        // Category ID
		$is_all_categories = false;
		$category_ids=array();
	    if( isset($_GET['c']) AND ! empty($_GET['c']) )
		{
			//check if there are any ',' in the category
			if((strpos($_GET['c'], ",")===false) && is_numeric($_GET['c']))
			{
				$category_ids = array($_GET['c']);	
			}
			else
			{
				$category_ids = explode(",", $_GET['c'],-1); //get rid of that trailing ";"
			}
		}
		else
		{
			$category_ids = array("0");
		}
		If(count($category_ids) == 0 || $category_ids[0] == '0')
		{
			$is_all_categories = true;
		}
	
	        // Start Date
	        $start_date = (isset($_GET['s']) AND !empty($_GET['s'])) ?
	            (int) $_GET['s'] : "0";
	
	        // End Date
	        $end_date = (isset($_GET['e']) AND !empty($_GET['e'])) ?
	            (int) $_GET['e'] : "0";
	
	        // SouthWest Bound
	        $southwest = (isset($_GET['sw']) AND !empty($_GET['sw'])) ?
	            $_GET['sw'] : "0";
	
	        $northeast = (isset($_GET['ne']) AND !empty($_GET['ne'])) ?
	            $_GET['ne'] : "0";
	
	        $filter = "";
	        $filter .= ($start_date) ?
	            " AND incident.incident_date >= '" . date("Y-m-d H:i:s", $start_date) . "'" : "";
	        $filter .= ($end_date) ?
	            " AND incident.incident_date <= '" . date("Y-m-d H:i:s", $end_date) . "'" : "";
	
	        if ($southwest AND $northeast)
	        {
	            list($latitude_min, $longitude_min) = explode(',', $southwest);
	            list($latitude_max, $longitude_max) = explode(',', $northeast);
	
	            $filter .= " AND location.latitude >=".(float) $latitude_min.
	                " AND location.latitude <=".(float) $latitude_max;
	            $filter .= " AND location.longitude >=".(float) $longitude_min.
	                " AND location.longitude <=".(float) $longitude_max;
	        }
	
		//stuff john just added
		$color = self::merge_colors($category_ids, $custom_category_to_table_mapping);
		//$incidents = reports::get_reports($category_ids, $approved_text, $filter, $logical_operator);
	
		$incidents = adminmap_reports::get_reports_list_by_cat($category_ids, 
			$approved_text, 
			$filter. " ". $extra_where_text, 
			$logical_operator,
			"incident.id",
			"asc",
			$joins,
			$custom_category_to_table_mapping);        

		/**
		 * **********************************************************************************************************************
		 * ********************************************************************************************************************** 
		 * We have the incidents, now process them
		 */

	
		// Create markers by marrying the the stuff together
	    $markers = array();
		$last_incident = null;
		$curr_id = "no idea";
		$isnt_first = false;
		$cat_names = array();
		$colors = array();
		
		foreach($incidents as $incident)
		{
			if($isnt_first && $curr_id != $incident->id)
			{
				//echo $last_incident->incident_title."\n\r".Kohana::debug($cat_names)."\r\n\r\n";
				$incident_info = array("incident" => $last_incident, 
									"colors"=>$colors, 
									"cat_names"=>$cat_names);
				//array_push($markers, $incident_info);
				$markers[] = $incident_info;
				//reset the arrays
				$cat_names = array();
				$colors = array();
			}
			//things to keep us moving forward
			$last_incident = $incident;
			$curr_id = $incident->id;
			$isnt_first = true;
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			//Now we try and figure out which category this report was matched to
			//////////////////////////////////////////////////////////////////////////////////////////////////////////////
			
			//if we're looking at all categories don't bother doing all this crazyness
			if(!$is_all_categories)
			{
				$incident_array = $incident->as_array();
				if(isset($incident_array["is_parent"]) && $incident_array["is_parent"] != 0) 
				{
					//echo $incident->incident_title." parent matched\r\n";
					$cat_names[$incident->parent_id] = $incident->parent_title;
					$colors[$incident->parent_id] = $incident->parent_color;
				}
				elseif(isset($incident_array["is_child"]) && $incident_array["is_child"] != 0) 
				{
					//echo $incident->incident_title." kid matched\r\n";			
					$cat_names[$incident->cat_id] = $incident->category_title;
					$colors[$incident->cat_id] = $incident->color;
				}
				//now the fun part, loop through all the custom categories
				foreach($custom_category_to_table_mapping as $name=>$custom_cat)
				{
					if(isset($incident_array["is_".$name."_parent"]) && $incident_array["is_".$name."_parent"] != 0) 
					{
						//echo $incident->incident_title." $name parent matched\r\n";
						$cat_names[$name."_".$incident_array[$name."_parent_cat_id"]] = $incident_array[$name."_parent_title"];
						$colors[$name."_".$incident_array[$name."_parent_cat_id"]] = $incident_array[$name."_parent_color"];
					}
					elseif(isset($incident_array["is_".$name."_child"]) && $incident_array["is_".$name."_child"] != 0) 
					{
						//echo $incident->incident_title." $name kid matched. With color: ".$incident_array[$name."_color"]."\r\n";			
						$cat_names[$name."_".$incident_array[$name."_cat_id"]] = $incident_array[$name."_title"];
						$colors[$name."_".$incident_array[$name."_cat_id"]] = $incident_array[$name."_color"];
					}
				}
			}//end if not is_all_categories
			unset($incident_array);
		}//end loop
		
	
		//catch the last report
		if($last_incident != null)
		{
			//echo "last one ".$last_incident->incident_title."\n\r".Kohana::debug($cat_names)."\r\n\r\n";
			$incident_info = array("incident" => $last_incident, "colors"=>$colors, "cat_names"=>$cat_names);
			$markers[] = $incident_info;
		}
	
		//echo "___________________________________________________\r\n";
	
	        $clusters = array();    // Clustered
	        $singles = array();     // Non Clustered
	
	        // Loop until all markers have been compared
	        while (count($markers))
	        {
	        
	        	$marker_info  = array_pop($markers);
		    
	        	$colors = $marker_info["colors"];	    
	        	$cat_names = $marker_info["cat_names"];	    
	        	$marker = $marker_info["incident"];
		    
		    
				//echo "\r\nLooking for clusters around ". $marker->incident_title. "\r\n";
				
			    $cluster = array();
		            
			    $category_colors = array();	//all the colors that were seen while making a cluster
			    $category_count = array();	//how many times we've seen a category while making this cluster
			    $category_names = array(); //all the names
			    
			    $contains_nonactive = false;
	            // Compare marker against all remaining markers.
	            foreach ($markers as $key => $target_info)
	            {
					
			        $target = $target_info["incident"];        
					$pixels = abs($marker->location->longitude - $target->location->longitude) + abs($marker->location->latitude - $target->location->latitude);
	
	                // If two markers are closer than defined distance, remove compareMarker from array and add to cluster.
	                if ($pixels < $distance)
	                {					
	                    unset($markers[$key]);
	                    
	                    $target_colors = $target_info["colors"];
						$target_cat_names = $target_info["cat_names"];
						
	                    
	                    
		                // Get Incident Geometries
		            	// are we using all categories red, or a specific blend of colors and goodness?
		            	$geo_color = $is_all_categories ||  $logical_operator=="and" ? $color : self::merge_colors_for_dots($target_colors);
						
		            	/** This is too slow
		            	/*$geometry = self::_get_geometry($target->id, 
							$target->incident_title, 
							$target->incident_date, 
							$on_the_back_end, 
							$geo_color,
							$link_target);
						$geometry = array();
						if (count($geometry))
						{
							$json_item = implode(",", $geometry);
							array_push($geometry_array, $json_item);
						}
						else // we don't want any average markers, just the orignals
						*/
						{
							$cluster[] = $target;
						}
	                    
	                    
					    //check if the colors and category names have been accounted for
					    if(!$is_all_categories)
					    {
						    foreach($target_colors as $cat_id => $target_color)
						    {
								//echo "\t\t".$target->incident_title. " has category: ".$target_cat_names[$cat_id]."\r\n";
								//colors
								$category_colors[$cat_id] = $target_color;
								//name
								$category_names[$cat_id] = $target_cat_names[$cat_id];
								//count
								if(isset($category_count[$cat_id]))
								{
									$category_count[$cat_id] = $category_count[$cat_id] + 1;
								}
								else
								{
									$category_count[$cat_id] = 1;
								}
								
						    }//end loop
					    } //end if
					    //check if this is a unapproved report
					    if($target->incident_active == 0)
					    {
							$contains_nonactive = true;
					    }
	                }//end if the the two points are close
	                //trying to minizmie memory use
	                unset($target_colors);
	                unset($target_cat_names);
	                unset($target);
	            }//end for loop
	            
	            // If a marker was added to cluster, also add the marker we were comparing to.
	            if (count($cluster) > 0)
	            {
					// Get Incident Geometries
					// are we using all categories red, or a specific blend of colors and goodness?
					
	            	/**
	            	/**This is too slow
	            	$geo_color = $is_all_categories ||  $logical_operator=="and" ? $color : self::merge_colors_for_dots($colors);
					
					/*$geometry = self::_get_geometry($marker->id, 
						$marker->incident_title, 
						$marker->incident_date,
						$on_the_back_end, 
						$geo_color,
						$link_target);
					
					$geometry = array();
					if (count($geometry))
					{
						$json_item = implode(",", $geometry);
						array_push($geometry_array, $json_item);
					}
					else*/
					{
						$cluster[] = $marker;
					}
	                
	                
	                
	                
					//check if the colors and category names have been accounted for
				    if(!$is_all_categories)
				    {
						foreach($colors as $cat_id2 => $marker_color)
					    {
							//echo "\t\t".$marker->incident_title. " has category: ".$cat_names[$cat_id2]."\r\n";
							//colors
							$category_colors[$cat_id2] = $marker_color;
							//name
							$category_names[$cat_id2] = $cat_names[$cat_id2];
							//count
							if(isset($category_count[$cat_id2]))
							{
								$category_count[$cat_id2] = $category_count[$cat_id2] + 1;
							}
							else
							{
								$category_count[$cat_id2] = 1;
							}
						}//end loop
				    }//end if   
			
			
				
					//check if this is an unapproved report
				    if($marker->incident_active == 0)
				    {
					$contains_nonactive = true;
				    }
				    
					if($contains_nonactive)
					{
					
						$clusters[] = array( 'contains_nonactive' => TRUE, 'cluster'=> $cluster, 'category_count'=>$category_count,
							'category_names'=>$category_names, 'category_colors'=>$category_colors);
					}
					else
					{			
						$clusters[] = array( 'contains_nonactive' => FALSE, 'cluster'=> $cluster, 'category_count'=>$category_count,
							'category_names'=>$category_names, 'category_colors'=>$category_colors);
					}
	            }
	            else
	            {
	            	// Get Incident Geometries
	            	// are we using all categories red, or a specific blend of colors and goodness?

	            	/** THIS IS TOO SLOW
	            	$geo_color = $is_all_categories ||  $logical_operator=="and" ? $color : self::merge_colors_for_dots($marker_info["colors"]);
					$geometry = self::_get_geometry($marker_info["incident"]->id, 
						$marker_info["incident"]->incident_title, 
						$marker_info["incident"]->incident_date, 
						$on_the_back_end, 
						$geo_color,
						$link_target);
					if (count($geometry))
					{
						$json_item = implode(",", $geometry);
						array_push($geometry_array, $json_item);
					}
					else //if there are markers we don't want any average dots, we just want original dots
					*/
					{
						$singles[] = $marker_info;
					}
	            }
	        }


		//echo "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx\r\n\r\n";
	
		$i = 0;
        // Create Json
        foreach ($clusters as $cluster_alpha)
        {
	    $cluster = $cluster_alpha['cluster'];
	    $cluster_cat_colors = $cluster_alpha['category_colors'];
	    $cluster_cat_names = $cluster_alpha['category_names'];
	    $cluster_cat_count = $cluster_alpha['category_count'];
	    
		//make category description string
		$category_str = "";
		if( (!$is_all_categories) && ($logical_operator!="and"))
		{
			foreach($cluster_cat_count as $cat_id => $cat_count)
			{
				$category_str .= "<li>".$cluster_cat_names[$cat_id]." (".$cat_count.")</li>";
			}
			$category_str = "<br/><br/> Categories in this cluster (number of reports):<ul>". $category_str."</ul>";
		}
		
		//make the categories string for the URL
		//if we're on the backend do it the old fashioned way
		$categories_str = "";
		if($on_the_back_end)
		{
			$categories_str = implode(",", $category_ids);
			$categories_str = "c=" . $categories_str;
		}
		//otherwise do it the new fangled fancy way, which is better, but it's a pain to change the way i do everything.
		else
		{
			foreach($category_ids as $c)
			{
				$categories_str .= "&c%5B%5D=" . mysql_real_escape_string($c); 
			}
		}
			    
	    	$contains_nonactive = $cluster_alpha['contains_nonactive'];
            // Calculate cluster center
            $bounds = self::_calculateCenter($cluster);
            $cluster_center = $bounds['center'];
            $southwest = $bounds['sw'];
            $northeast = $bounds['ne'];
            
        	$time_filter = "";
			if($start_date != "0" && $end_date != "0")
			{
				$time_filter = "&s=".$start_date."&e=".$end_date;
			}

            // Number of Items in Cluster
            $cluster_count = count($cluster);

            $json_item = "{\"type\":\"Feature\",\"properties\": {";	    	
            $json_item .= "\"name\":\"" . str_replace(chr(10), ' ', str_replace(chr(13), ' ', "<a target='".$link_target."' href='" . url::base() . $list_reports_path. $url_param_join_character . $categories_str."&sw=".$southwest."&ne=".$northeast."&lo=".$logical_operator."&u=".$show_unapproved.$time_filter."'>" . $cluster_count . " Reports</a> ".$category_str)) . "\",";
	    	$json_item .= "\"link\":\"" . url::base(). $list_reports_path . $url_param_join_character . $categories_str."&sw=".$southwest."&ne=".$northeast."&lo=".$logical_operator."&u=".$show_unapproved.$time_filter."\",";
            $json_item .= "\"category\":[0], ";
			if($contains_nonactive && $color_unapproved==2)
			{
				$json_item .= "\"color\": \"000000\", \n\"icon\": \"".$icon."\", \n";
			}
			//check if we're looking at all categories
			elseif($is_all_categories)
			{					
				$json_item .= "\"color\": \"".$default_color."\", \n\"icon\": \"".$icon."\", \n";
			}
			//check if we're using AND
			elseif($logical_operator=="and")
			{					
				$json_item .= "\"color\": \"".$color."\", \n\"icon\": \"".$icon."\", \n";
			}
			//else we're using OR to combine categories
			else
			{
				$dot_color = self::merge_colors_for_dots($cluster_cat_colors);
				$json_item .= "\"color\": \"".$dot_color."\", \n\"icon\": \"".$icon."\", \n";
			}            
            $json_item .= "\"timestamp\": \"0\", \"count\": \"" . $cluster_count . "\"},\"geometry\": {\"type\":\"Point\", \"coordinates\":[" . $cluster_center . "]}}";

            array_push($json_array, $json_item);
        }


		//do singles
        foreach ($singles as $single_info)
        {
		$single = $single_info["incident"];
		$single_colors = $single_info["colors"];
		$single_cat_names = $single_info["cat_names"];
		
		//echo $single->incident_title."\n\r".Kohana::debug($single_cat_names)."\r\n\r\n";
		
		$category_description = "";
		if(!$is_all_categories && $logical_operator!="and")
		{
			$count = 0;
			foreach($single_cat_names as $cat_name)
			{
				$count++;
				if($count > 1)
				{
					$category_description .= ", ";
				}
				$category_description .= $cat_name;
			}
			
			$category_description = "<br/><br/>Falls under categories:<br/>".$category_description;
		}
		
		$json_item = "{";
		$json_item .= "\"type\":\"Feature\",";
		$json_item .= "\"properties\": {";
		$json_item .= "\"name\":\"" .date("n/j/Y", strtotime($single->incident_date)).":<br/>". str_replace(chr(10), ' ', str_replace(chr(13), ' ', "<a target='".$link_target."' href='" . url::base() . $edit_report_path . $single->id . "'/>".str_replace('"','\"',$single->incident_title)."</a>".$category_description)) . "\",";   
	    $json_item .= "\"link\":\"" .url::base(). "{$edit_report_path}{$single->id}\","; 
            $json_item .= "\"category\":[0], ";
	    //check if it's a unapproved/unactive report
		if($single->incident_active == 0 && $color_unapproved==2)
		{
			$json_item .= "\"color\": \"000000\", \n";
			$json_item .= "\"icon\": \"".$icon."\", \n";
		}
		//check if we're looking at all categories
		elseif($is_all_categories)
		{					
			$json_item .= "\"color\": \"".$default_color."\", \n";
			$json_item .= "\"icon\": \"".$icon."\", \n";
		}
		//check if we're using AND
		elseif($logical_operator=="and")
		{					
			$json_item .= "\"color\": \"".$color."\", \n";
			$json_item .= "\"icon\": \"".$icon."\", \n";
		}
		//else we're using OR to combine categories
		else
		{
			$dot_color = self::merge_colors_for_dots($single_colors);
			$json_item .= "\"color\": \"".$dot_color."\", \n";
			$json_item .= "\"icon\": \"".$icon."\", \n";
		}            
	    $json_item .= "\"timestamp\": \"0\", ";
            $json_item .= "\"count\": \"" . 1 . "\"";
            $json_item .= "},";
            $json_item .= "\"geometry\": {";
            $json_item .= "\"type\":\"Point\", ";
            $json_item .= "\"coordinates\":[" . $single->location->longitude . ", " . $single->location->latitude . "]";
            $json_item .= "}";
            $json_item .= "}";

            array_push($json_array, $json_item);
        }

        $json = implode(",", $json_array);
	if (count($geometry_array))
	{
		$json = implode(",", $geometry_array).",".$json;
	}

        header('Content-type: application/json');
        $controller->template->json = $json;

    }//end cluster method
	    
    
  
  
  
  
  
  
  
  
     /**************************************************************
     * Retrieve timeline JSON
     * $on_the_back_end is used to set if the user is looking at this on the backend or not
     */
    public static function json_timeline( $controller, 
								$category_ids, 
								$on_the_back_end = true, 
								$extra_where_text = "", 
								$joins = array(),
								$custom_category_to_table_mapping = array())
    {
	$category_ids = explode(",", $category_ids,-1); //get rid of that trailing ","
	//a little flag to alert us to the presence of the "ALL CATEGORIES" category
	$is_all_categories = false;
	If(count($category_ids) == 0 || $category_ids[0] == '0')
	{
		$is_all_categories = true;
	}

        $controller->auto_render = FALSE;
        $db = new Database();
	
	
	
	$show_unapproved="3"; //1 show only approved, 2 show only unapproved, 3 show all
	$approved_text = " (1=1) ";
	if($on_the_back_end)
	{
		//figure out if we're showing unapproved stuff or what.
		if (isset($_GET['u']) AND !empty($_GET['u']))
		{
		    $show_unapproved = (int) $_GET['u'];
		}
		$approved_text = "";
		if($show_unapproved == 1)
		{
			$approved_text = "incident.incident_active = 1 ";
		}
		else if ($show_unapproved == 2)
		{
			$approved_text = "incident.incident_active = 0 ";
		}
		else if ($show_unapproved == 3)
		{
			$approved_text = " (incident.incident_active = 0 OR incident.incident_active = 1) ";
		}
	}
	else
	{
		$approved_text = "incident.incident_active = 1 ";
	}
	
	$logical_operator = "or";
	if (isset($_GET['lo']) AND !empty($_GET['lo']))
        {
	    $logical_operator =  $_GET['lo'];
        }


        $interval = (isset($_GET["i"]) AND !empty($_GET["i"])) ?
            $_GET["i"] : "month";


        // Get the Counts
        $select_date_text = "DATE_FORMAT(incident_date, '%Y-%m-01')";
        $groupby_date_text = "DATE_FORMAT(incident_date, '%Y%m')";
        if ($interval == 'day')
        {
            $select_date_text = "DATE_FORMAT(incident_date, '%Y-%m-%d')";
            $groupby_date_text = "DATE_FORMAT(incident_date, '%Y%m%d')";
        }
        elseif ($interval == 'hour')
        {
            $select_date_text = "DATE_FORMAT(incident_date, '%Y-%m-%d %H:%M')";
            $groupby_date_text = "DATE_FORMAT(incident_date, '%Y%m%d%H')";
        }
        elseif ($interval == 'week')
        {
            $select_date_text = "STR_TO_DATE(CONCAT(CAST(YEARWEEK(incident_date) AS CHAR), ' Sunday'), '%X%V %W')";
            $groupby_date_text = "YEARWEEK(incident_date)";
        }

	
	
        $graph_data = array();
        $graph_data[0] = array();
        $graph_data[0]['label'] = "Category Title"; //is this used for anything?
        $graph_data[0]['color'] = '#'. self::merge_colors($category_ids, $custom_category_to_table_mapping);
        $graph_data[0]['data'] = array();
	
	$incidents = adminmap_reports::get_reports($category_ids, 
		$approved_text, 
		" ".$extra_where_text, 
		$logical_operator,
		"incident.incident_date",
		"asc",
		-1, 
		-1,
		$joins,
		$custom_category_to_table_mapping);
	
	
	$approved_IDs_str = "('-1')";
	if(count($incidents) > 0)
	{
		$i = 0;
		$approved_IDs_str = "(";
		foreach($incidents as $incident)
		{
			$i++;
			$approved_IDs_str = ($i > 1) ? $approved_IDs_str.', ' : $approved_IDs_str;
			$approved_IDs_str = $approved_IDs_str."'".$incident->id."'";
		}
		$approved_IDs_str = $approved_IDs_str.") ";
	}

        $query = 'SELECT UNIX_TIMESTAMP('.$select_date_text.') AS time, COUNT(id) AS number FROM '.adminmap_helper::$table_prefix.'incident WHERE incident.id in'.$approved_IDs_str.' GROUP BY '.$groupby_date_text;
	$query = $db->query($query);

        foreach ( $query as $items )
        {
            array_push($graph_data[0]['data'],
                array($items->time * 1000, $items->number));
        }

        echo json_encode($graph_data);
    }


   
  
  
  
  /**
	 * Get Geometry JSON
	 * @param int $incident_id
	 * @param string $incident_title
	 * @param int $incident_date
	 * @return array $geometry
	 */
	private static function _get_geometry($incident_id, $incident_title, $incident_date, $on_the_back_end, $color, $link_target = "_self")
	{
		$geometry = array();
		if ($incident_id)
		{
			$db = new Database();
			// Get Incident Geometries via SQL query as ORM can't handle Spatial Data
			$sql = "SELECT id, AsText(geometry) as geometry, geometry_label, 
				geometry_comment, geometry_color, geometry_strokewidth FROM ".self::$table_prefix."geometry 
				WHERE incident_id=".$incident_id;
			$query = $db->query($sql);
			$wkt = new Wkt();

			foreach ( $query as $item )
			{
				$geom = $wkt->read($item->geometry);
				$geom_array = $geom->getGeoInterface();

				$json_item = "{";
				$json_item .= "\"type\":\"Feature\",";
				$json_item .= "\"properties\": {";
				$json_item .= "\"id\": \"".$incident_id."\", ";
				$json_item .= "\"feature_id\": \"".$item->id."\", ";

				$title = ($item->geometry_label) ? 
					utf8tohtml::convert($item->geometry_label,TRUE) : 
					utf8tohtml::convert($incident_title,TRUE);
					
				$fillcolor = ($item->geometry_color) ? 
					utf8tohtml::convert($item->geometry_color,TRUE) : $color;
					
				$strokecolor = ($item->geometry_color) ? 
					utf8tohtml::convert($item->geometry_color,TRUE) : $color;
					
				$strokewidth = ($item->geometry_strokewidth) ? $item->geometry_strokewidth : "3";

				if($on_the_back_end)
				{
					$json_item .= "\"name\":\"" . str_replace(chr(10), ' ', str_replace(chr(13), ' ', "<a href='" . url::base() . "admin/reports/edit/" . $incident_id . "'>".$title."</a>")) . "\",";
				}
				else
				{
					$json_item .= "\"name\":\"" . str_replace(chr(10), ' ', str_replace(chr(13), ' ', "<a target='".$link_target."' href='" . url::base() . "reports/view/" . $incident_id . "'>".$title."</a>")) . "\",";
				}

				$json_item .= "\"description\": \"" . utf8tohtml::convert($item->geometry_comment,TRUE) . "\", ";
				$json_item .= "\"color\": \"" . $fillcolor . "\", ";
				$json_item .= "\"strokecolor\": \"" . $strokecolor . "\", ";
				$json_item .= "\"strokewidth\": \"" . $strokewidth . "\", ";
				$json_item .= "\"link\": \"".url::base()."reports/view/".$incident_id."\", ";
				$json_item .= "\"category\":[0], ";
				$json_item .= "\"timestamp\": \"" . strtotime($incident_date) . "\"";
				$json_item .= "},\"geometry\":".json_encode($geom_array)."}";
				$geometry[] = $json_item;
			}
		}
		
		return $geometry;
	}
  
  
  
  
  
  /**
     * Calculate the center of a cluster of markers
     * @param array $cluster
     * @return array - (center, southwest bound, northeast bound)
     */
    private static function _calculateCenter($cluster)
    {
        // Calculate average lat and lon of clustered items
        $south = 0;
        $west = 0;
        $north = 0;
        $east = 0;

        $lat_sum = $lon_sum = 0;
        foreach ($cluster as $marker)
        {
            if (!$south)
            {
                $south = $marker->location->latitude;
            }
            elseif ($marker->location->latitude < $south)
            {
                $south = $marker->location->latitude;
            }

            if (!$west)
            {
                $west = $marker->location->longitude;
            }
            elseif ($marker->location->longitude < $west)
            {
                $west = $marker->location->longitude;
            }

            if (!$north)
            {
                $north = $marker->location->latitude;
            }
            elseif ($marker->location->latitude > $north)
            {
                $north = $marker->location->latitude;
            }

            if (!$east)
            {
                $east = $marker->location->longitude;
            }
            elseif ($marker->location->longitude > $east)
            {
                $east = $marker->location->longitude;
            }

            $lat_sum += $marker->location->latitude;
            $lon_sum += $marker->location->longitude;
        }
        $lat_avg = $lat_sum / count($cluster);
        $lon_avg = $lon_sum / count($cluster);

        $center = $lon_avg.",".$lat_avg;
        $sw = $west.",".$south;
        $ne = $east.",".$north;

        return array(
            "center"=>$center,
            "sw"=>$sw,
            "ne"=>$ne
        );
    }//end function
    
    
	


}//end class adminmap_core


	adminmap_helper_Core::init();



