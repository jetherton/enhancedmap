<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Where all the work is done. This file handles map rendering requests and requests for Geo JSON
 * 
 * This file is adapted from the file Ushahidi_Web/appliction/controllers/json.php and
 * Ushahidi_Web/application/controllers/main.php
 * Originally written by the Ushahidi Team
 *
 *
 * @author     John Etherton <john@ethertontech.com>
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
 */
class enhancedmap_helper_Core {

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
	public static function setup_enhancedmap($map_controller, $map_view = "enhancedmap/mapview", $map_css = "enhancedmap/css/enhancedmap")		
	{
	
		//set the CSS for this
		if($map_css != null)
		{
			plugin::add_stylesheet($map_css);
		}
		
		plugin::add_javascript("enhancedmap/js/jquery.flot");
		plugin::add_javascript("enhancedmap/js/excanvas.min");
		plugin::add_javascript("enhancedmap/js/timeline");
		plugin::add_javascript("enhancedmap/js/jquery.hovertip-1.0");
		
		$map_controller->template->content = new View($map_view);
		
		// Get Default Color
		$map_controller->template->content->default_map_all = Kohana::config('settings.default_map_all');
	}
	
	
	/****
	* Sets up the overlays and shares
	*/
	public static function set_layers($on_backend = false, $show_on_load = false,
			$layers_filter_view = 'enhancedmap/layers_filter', $layers_filter_id = "layer_filter")
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
		$view = new View($layers_filter_view);
		$view->show_on_load = $show_on_load;
		$view->layer_id = $layers_filter_id;
		$view->layers = $layers;
		
		return $view;
		
		
	}
	
	
	/****
	 * Sets up the overlays and shares
	*/
	public static function set_shares($on_backend = false, $show_on_load = false,
			$shares_filter_view = 'enhancedmap/shares_filter', $shares_filter_id = "shares_filter")
	{
		
		//first of all make sure sharing is turned on.
		$db = new Database();
		
		$result = $db->query('SHOW TABLES LIKE \''.self::$table_prefix.'sharing\'');
		$table_exists = false;
		foreach($result as $r)
		{
			$table_exists = true;
		}
		
		
		if($table_exists)
		{	
			$shares = array();
			foreach (ORM::factory('sharing')
					->where('sharing_active', 1)
					->find_all() as $share)
			{
				$shares[$share->id] = array($share->sharing_name, $share->sharing_color);
			}
	
			$view = new View($shares_filter_view);
			$view->share_id = $shares_filter_id;
			$view->show_on_load = $show_on_load;
			$view->shares = $shares;
			return $view;
		}
		return "";
		
		
	}
	
	
	/*
	* this makes the map for this plugin
	*/
	public static function set_map($template, $themes, $json_url, $json_timeline_url, $javascript_view = 'enhancedmap/adminmap_js',
							$div_map_view = 'enhancedmap/main_map', $div_timeline_view = 'enhancedmap/main_timeline', 
							$urlParams = array(), $map_id = "map", $map_status_id = "mapStatus", $graph_id = "graph", $slider_holder_id = "slider-holder")
	{
		
		//are we on the backend?
		$on_back_end = false;
		if (stripos($json_url, 'admin/') === 0)
		{
			$on_back_end = true;	
		}
	
		////////////////////////////////////////////////////////////////Map and Slider Blocks////////////////////////////////////////////////////////////////////////////
		$div_map = new View($div_map_view);
		$div_map->map_id = $map_id;
		$div_map->map_status_id = $map_status_id;
		$div_timeline = new View($div_timeline_view);
		$div_timeline->slider_holder_id = $slider_holder_id;
		$div_timeline->graph_id = $graph_id;
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
		$themes->js->graph_id = $graph_id;
		$themes->js->map_id = $map_id;
		$themes->js->urlParams = $urlParams;
		$themes->js->default_map = Kohana::config('settings.default_map');
		$themes->js->default_zoom = Kohana::config('settings.default_zoom');
		if($on_back_end)
		{
			$themes->js->show_unapproved = '3';	
		}
		

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

	/**
	 * Use this to setup the status filter
	 */
	public static function get_status_filter($on_backend = false, 
			$status_filter_view = 'enhancedmap/status_filter', $status_filter_id = "status_filter",
			$show_unapproved = false)
	{
		$view = new View($status_filter_view);
		$view->on_backend = $on_backend;
		$view->status_filter_id = $status_filter_id;
		$view->show_unapproved = $show_unapproved;
		return $view;
	}
	
	/**
	 * Use this to setup the boolean filter
	 */
	public static function get_boolean_filter($on_backend = false,
			$boolean_filter_view = 'enhancedmap/boolean_filter', $boolean_filter_id = "boolean_filter",
			$show_help = true)
	{
		$view = new View($boolean_filter_view);
		$view->on_backend = $on_backend;
		$view->boolean_filter_id = $boolean_filter_id;
		$view->show_help = $show_help;
		return $view;
	}
	
	
	
	public static function set_categories($on_backend = false, $group = false, $categories_view = "enhancedmap/categories_filter",
			$categories_view_id = "category_switch")
	{
		
		$view = new View($categories_view);
	
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
				throw new Exception("A group was set in enhancedmap_helper::set_categories() when the SimpleGroupl plugin is not installed");
			}
		
			$cats = ORM::factory('simplegroups_category');
			if(!$on_backend OR ORM::factory('enhancedmap_settings')->where('key', 'show_hidden_categories_backend')->find()->value != 'true')
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
		if(!$on_backend OR ORM::factory('enhancedmap_settings')->where('key', 'show_hidden_categories_backend')->find()->value != 'true')
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
		
		
		
		
		$view->categories = $parent_categories;
		$view->categories_view_id = $categories_view_id;
		return $view;
	}//end method
	
	
	



	/////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////METHODS FOR the JSON CONTROLLER
	///////////////////////////////////////////////////////////////////////////////////////////////////
	
	
	/**
	* Generate JSON in NON-CLUSTER mode
	* $edit_report_path is used to set where the link to edit/view a report should be set to
	* $on_the_back_end sets whether or not this user is viewing this data from the backend
	*/
	public static function json_index($json_controller,  $on_the_back_end = true,
		$link_target = "_self",
		$link_path_prefix = '')
	{
		
		// Database
		$db = new Database();
		
		$json = "";
		$json_item = "";
		$json_array = array();		
		$icon = "";
		
		//get the coloring mode
		$color_mode = ORM::factory('enhancedmap_settings')->where('key', 'color_mode')->find()->value;

		$media_type = (isset($_GET['m']) AND intval($_GET['m']) > 0)? intval($_GET['m']) : 0;
		
		// Get the incident and category id
		$category_id = (isset($_GET['c']) AND is_array($_GET['c']))? $_GET['c'] : array('0');
		$incident_id = (isset($_GET['i']) AND intval($_GET['i']) > 0)? intval($_GET['i']) : 0;
		// Get the category colour
		$cat_str = "";//only used by highest first coloring mode
		$all_categories = false;
		if(count($category_id) == 1 AND intval($category_id[0]) == 0 )
		{
			$colors = array(Kohana::config('settings.default_map_all'));
			$all_categories = true;
		}
		
		else 
		{	
			//more than one color
			$colors = array();
			foreach($category_id as $cat)
			{
				$c = ORM::factory('category', $cat);
				$colors[$c->category_position] = $c->category_color;
				if($cat_str != ''){$cat_str .= ',';}
				$cat_str .= $c->id;
			}			
		}
		$color = self::merge_colors($colors);	
		
		//since we're on the back end, wana do anything special?
		$admin_path = '';
		$view_or_edit = 'view';
		if($on_the_back_end)
		{
			$admin_path = 'admin/';
			$view_or_edit = 'edit';
		}
		
		// Fetch the incidents
		$markers = (isset($_GET['page']) AND intval($_GET['page']) > 0)? reports::fetch_incidents(TRUE) : reports::fetch_incidents();
		
		//only do this if highest_first
		$position_map = array();
		if($color_mode=='highest_first'  AND !$all_categories)
		{
			$ids_str = ""; //only used in highest first coloring mode
			foreach ($markers as $incident)
			{
				if($ids_str != '')
				{
					$ids_str .= ',';
				}
				$ids_str .= $incident->incident_id;						
			}
			
			$query_str = 'SELECT incident_id, MIN( '.self::$table_prefix.'category.category_position ) AS position
			FROM  `'.self::$table_prefix.'incident_category`
			JOIN '.self::$table_prefix.'category ON '.self::$table_prefix.'incident_category.category_id = '.self::$table_prefix.'category.id
			WHERE incident_id IN ('.$ids_str.')
			AND category_id IN ('.$cat_str.')
			GROUP BY '.self::$table_prefix.'incident_category.incident_id
			ORDER BY '.self::$table_prefix.'incident_category.incident_id';
				
			$results = $db->query($query_str);
			
			//now build the map
			foreach($results as $r)
			{
				$position_map[$r->incident_id] = $r->position;
			}	
		}
		
		
		// Variable to store individual item for report detail page
		$json_item_first = "";	
		foreach ($markers as $marker)
		{
			
			$thumb = "";
			if ($media_type == 1)
			{
				$media = ORM::factory('incident', $marker->incident_id)->media;
				if ($media->count())
				{
					foreach ($media as $photo)
					{
						if ($photo->media_thumb)
						{ 
							// Get the first thumb
							$prefix = url::base().Kohana::config('upload.relative_directory');
							$thumb = $prefix."/".$photo->media_thumb;
							break;
						}
					}
				}
			}
			
			$json_item = "{";
			$json_item .= "\"type\":\"Feature\",";
			$json_item .= "\"properties\": {";
			$json_item .= "\"id\": \"".$marker->incident_id."\", \n";

			$encoded_title = utf8tohtml::convert($marker->incident_title, TRUE);
			$encoded_title = str_ireplace('"','&#34;',$encoded_title);
			$encoded_title = json_encode($encoded_title);
			$encoded_title = str_ireplace('"', '', $encoded_title);

			$json_item .= "\"name\":\"" . str_replace(chr(10), ' ', str_replace(chr(13), ' ', "<a target = '".$link_target
					. "' href='".url::base().$admin_path.$link_path_prefix."reports/".$view_or_edit."/".$marker->incident_id."'>".$encoded_title)."</a>") . "\","
					. "\"link\": \"".url::base().$admin_path.$link_path_prefix."reports/".$view_or_edit."/".$marker->incident_id."\", ";

			$json_item .= (isset($category))
				? "\"category\":[" . $category_id . "], "
				: "\"category\":[0], ";
			
			$dot_color = ($color_mode == 'highest_first' AND !$all_categories) ? $colors[$position_map[$marker->incident_id]] : $color; 

			$json_item .= "\"color\": \"".$dot_color."\", \n";
			$json_item .= "\"icon\": \"".$icon."\", \n";
			$json_item .= "\"ids\": [".$marker->incident_id."], ";
			$json_item .= "\"thumb\": \"".$thumb."\", \n";
			$json_item .= "\"timestamp\": \"" . strtotime($marker->incident_date) . "\"";
			$json_item .= "},";
			$json_item .= "\"geometry\": {";
			$json_item .= "\"type\":\"Point\", ";
			$json_item .= "\"coordinates\":[" . $marker->longitude . ", " . $marker->latitude . "]";
			$json_item .= "}";
			$json_item .= "}";

			if ($marker->incident_id == $incident_id)
			{
				$json_item_first = $json_item;
			}
			else
			{
				array_push($json_array, $json_item);
			}
			
			// Get Incident Geometries
			/* Slows things down too much
			$geometry = self::_get_geometry($marker->incident_id, $marker->incident_title, $marker->incident_date);
			if (count($geometry))
			{
				$json_item = implode(",", $geometry);
				array_push($json_array, $json_item);
			}
			*/
		}
		
		if ($json_item_first)
		{
			// Push individual marker in last so that it is layered on top when pulled into map
			array_push($json_array, $json_item_first);
		}
		
		$json = implode(",", $json_array);

		header('Content-type: application/json; charset=utf-8');
		$json_controller->template->json = $json;
	}
	
	
	
	
	
	
	



/************************************************************************************************
	* Function, this'll merge colors. Given an array of category IDs it'll return a hex string
	* of all the colors merged together
	*/
	public static function merge_colors($colors)
	{
		//check if we're dealing with just one color
		if(count($colors)==1)
		{
			foreach($colors as $color)
			{
				Event::run('enhancedmap_filter.features_color', $color);
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
		$color_str = $red.$green.$blue;
		//in case other plugins have something to say about this
		Event::run('enhancedmap_filter.features_color', $color_str);
		return $color_str;
		
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
	$on_the_back_end = true,
	$link_target = "_self",
	$link_path_prefix = "")
    {
		// Database
		$db = new Database();

		$json = "";
		$json_item = "";
		$json_array = array();
		$geometry_array = array();
		
		//get the coloring mode
		$color_mode = ORM::factory('enhancedmap_settings')->where('key', 'color_mode')->find()->value;

		
		$icon = "";

		// Get Zoom Level
		$zoomLevel = (isset($_GET['z']) AND !empty($_GET['z'])) ?
			(int) $_GET['z'] : 8;

		//$distance = 60;
		$distance = (10000000 >> $zoomLevel) / 100000;
		
		// Fetch the incidents using the specified parameters
		$incidents = reports::fetch_incidents();
		
		// Category ID
		$category_id = (isset($_GET['c']) AND is_array($_GET['c'])) ? $_GET['c'] : array(0);
		
		// Start date
		$start_date = (isset($_GET['s']) AND intval($_GET['s']) > 0) ? intval($_GET['s']) : NULL;
		
		// End date
		$end_date = (isset($_GET['e']) AND intval($_GET['e']) > 0) ? intval($_GET['e']) : NULL;
		
		//Logical operator
		$logical_operator = isset($_GET['lo'])  ? intval($_GET['lo']) : 'or';
		
		//get color
		$cat_str = "";//only used by highest first coloring mode
		$all_categories = false;
  		if(count($category_id) == 1 AND intval($category_id[0]) == 0 )
		{
			$colors = array(Kohana::config('settings.default_map_all'));
			$all_categories = true;
		}		
		else 
		{	
			//more than one color
			$colors = array();			
			foreach($category_id as $cat)
			{
				$c = ORM::factory('category', $cat);
				$colors[$c->category_position] = $c->category_color;
				if($cat_str != ''){$cat_str .= ',';}
				$cat_str .= $c->id;
			}
		}
		$color = self::merge_colors($colors);	
		

		// Create markers by marrying the locations and incidents
		$markers = array();
		$ids_str = ""; //only used in highest first coloring mode
		foreach ($incidents as $incident)
		{
			$markers[] = array(
				'id' => $incident->incident_id,
				'incident_title' => $incident->incident_title,
				'latitude' => $incident->latitude,
				'longitude' => $incident->longitude,
				'thumb' => ''
				);
			
			if($color_mode=='highest_first'  AND !$all_categories)
			{
				if($ids_str != '')
				{ 
					$ids_str .= ',';
				}
				$ids_str .= $incident->incident_id;
			}
			
		}
		
		//if the coloring mode is highest first
		if($color_mode == 'highest_first' AND !$all_categories)
		{
			$position_map = array();
			$query_str = 'SELECT incident_id, MIN( '.self::$table_prefix.'category.category_position ) AS position
			FROM  `'.self::$table_prefix.'incident_category`
			JOIN '.self::$table_prefix.'category ON '.self::$table_prefix.'incident_category.category_id = '.self::$table_prefix.'category.id
			WHERE incident_id IN ('.$ids_str.')
			AND category_id IN ('.$cat_str.')
			GROUP BY '.self::$table_prefix.'incident_category.incident_id
			ORDER BY '.self::$table_prefix.'incident_category.incident_id';
			
			$results = $db->query($query_str);
				
			//now build the map
			foreach($results as $r)
			{
				$position_map[$r->incident_id] = $r->position;
			}
		}
		
		

		$clusters = array();	// Clustered
		$singles = array();		// Non Clustered

		// Loop until all markers have been compared
		while (count($markers))
		{
			$marker	 = array_pop($markers);
			$cluster = array();
			$cluster_data = array();			
			$min_position = 9000000;
			$south = 0;
			$north = 0;
			$east = 0;
			$west = 0;
			$lat_sum = 0;
			$lon_sum = 0;
			$id_str = "";

			// Compare marker against all remaining markers.
			foreach ($markers as $key => $target)
			{
				$pixels = abs($marker['longitude']-$target['longitude']) +
					abs($marker['latitude']-$target['latitude']);
					
				// If two markers are closer than defined distance, remove compareMarker from array and add to cluster.
				if ($pixels < $distance)
				{
					unset($markers[$key]);
					$target['distance'] = $pixels;
					$cluster[] = $target;
					
					if($id_str != "")
					{
						$id_str .= ",";
					}
					$id_str .= $target['id'];
					
					if (!$south)
					{
						$south = $target['latitude'];
					}
					elseif ($target['latitude'] < $south)
					{
						$south = $target['latitude'];
					}
					
					if (!$west)
					{
						$west = $target['longitude'];
					}
					elseif ($target['longitude'] < $west)
					{
						$west = $target['longitude'];
					}
					
					if (!$north)
					{
						$north = $target['latitude'];
					}
					elseif ($target['latitude'] > $north)
					{
						$north = $target['latitude'];
					}
					
					if (!$east)
					{
						$east = $target['longitude'];
					}
					elseif ($target['longitude'] > $east)
					{
						$east = $target['longitude'];
					}
					
					$lat_sum += $target['latitude'];
					$lon_sum += $target['longitude'];
						
					
					//only if we're in the highes first color mode, do we keep track of the lowest position in a cluster
					if($color_mode == 'highest_first' AND !$all_categories)
					{
						if($min_position > $position_map[$target['id']])
						{
							$min_position = $position_map[$target['id']];
						}
					}
				}
			}

			// If a marker was added to cluster, also add the marker we were comparing to.
			if (count($cluster) > 0)
			{
				$cluster[] = $marker;
				
				//one last time setup everything
				if($id_str != "")
				{
					$id_str .= ",";
				}
				$id_str .= $marker['id'];
					
				if (!$south)
				{
					$south = $marker['latitude'];
				}
				elseif ($marker['latitude'] < $south)
				{
					$south = $marker['latitude'];
				}
					
				if (!$west)
				{
					$west = $marker['longitude'];
				}
				elseif ($marker['longitude'] < $west)
				{
					$west = $marker['longitude'];
				}
					
				if (!$north)
				{
					$north = $marker['latitude'];
				}
				elseif ($marker['latitude'] > $north)
				{
					$north = $marker['latitude'];
				}
					
				if (!$east)
				{
					$east = $marker['longitude'];
				}
				elseif ($marker['longitude'] > $east)
				{
					$east = $marker['longitude'];
				}
					
				$lat_sum += $marker['latitude'];
				$lon_sum += $marker['longitude'];
				
					
			
				
				$cluster_data = array('count'=>count($cluster), 
										'ids'=>$id_str,
										'center' => ($lon_sum/count($cluster)).",".($lat_sum/count($cluster)),
										'sw' => $west.",".$south,
										'ne' => $east.",".$north);									
				
				//only if we're in the highes first color mode, do we keep track of the lowest position in a cluster
				if($color_mode == 'highest_first' AND !$all_categories)
				{
					if($min_position > $position_map[$marker['id']])
					{
						$min_position = $position_map[$marker['id']];
					}
					$cluster_data['min_position'] = $min_position;
				}
				
				$clusters[] = $cluster_data;
			}
			else
			{
				$singles[] = $marker;
			}
		}
		
    	//since we're on the back end, wana do anything special?
		$admin_path = '';
		$view_or_edit = 'view';
		if($on_the_back_end)
		{
			$admin_path = 'admin/';
			$view_or_edit = 'edit';
		}

		// Create Json
		foreach ($clusters as $cluster)
		{
			// Calculate cluster center
			
			$cluster_center = $cluster['center'];
			$southwest = $cluster['sw'];
			$northeast = $cluster['ne'];

			// Number of Items in Cluster
			$cluster_count = $cluster['count'];
			//id string					
			$id_str = $cluster['ids'];
			
			$dot_color = ($color_mode == 'highest_first' AND !$all_categories) ? $colors[$cluster['min_position']] : $color;
			
			// Build out the JSON string
			$json_item = "{";
			$json_item .= "\"type\":\"Feature\",";
			$json_item .= "\"properties\": {";
			$json_item .= "\"name\":\"" . str_replace(chr(10), ' ', str_replace(chr(13), ' ', "<a target = ".$link_target." href=" . url::base().$admin_path.$link_path_prefix
				 . "reports/index/?".$_SERVER['QUERY_STRING']."&sw=".$southwest."&ne=".$northeast.">" . $cluster_count . " Reports</a>")) . "\",";
			$json_item .= "\"link\": \"".url::base().$admin_path.$link_path_prefix. "reports/index/?".$_SERVER['QUERY_STRING']."&sw=".$southwest."&ne=".$northeast."\", ";
			$json_item .= "\"category\":[0], ";
			$json_item .= "\"color\": \"".$dot_color."\", ";
			$json_item .= "\"icon\": \"".$icon."\", ";
			$json_item .= "\"ids\": [".$id_str."], ";
			$json_item .= "\"thumb\": \"\", ";
			$json_item .= "\"timestamp\": \"0\", ";
			$json_item .= "\"count\": \"" . $cluster_count . "\"";
			$json_item .= "},";
			$json_item .= "\"geometry\": {";
			$json_item .= "\"type\":\"Point\", ";
			$json_item .= "\"coordinates\":[" . $cluster_center . "]";
			$json_item .= "}";
			$json_item .= "}";

			array_push($json_array, $json_item);
		}

		foreach ($singles as $single)
		{
			$dot_color = ($color_mode == 'highest_first' AND !$all_categories) ? $colors[$position_map[$single['id']]] : $color;
			
			$json_item = "{";
			$json_item .= "\"type\":\"Feature\",";
			$json_item .= "\"properties\": {";
			$json_item .= "\"name\":\"" . str_replace(chr(10), ' ', str_replace(chr(13), ' ', "<a target = ".$link_target." href=" . url::base().$admin_path.$link_path_prefix
					. "reports/".$view_or_edit."/" . $single['id'] . "/>".str_replace('"','\"',$single['incident_title'])."</a>")) . "\",";
			$json_item .= "\"link\": \"".url::base().$admin_path.$link_path_prefix."reports/".$view_or_edit."/".$single['id']."\", ";
			$json_item .= "\"category\":[0], ";
			$json_item .= "\"color\": \"".$dot_color."\", ";
			$json_item .= "\"icon\": \"".$icon."\", ";
			// $json_item .= "\"thumb\": \"".$single['thumb']."\", ";
			$json_item .= "\"timestamp\": \"0\", ";
			$json_item .= "\"ids\": [".$single['id']."], ";
			$json_item .= "\"count\": \"" . 1 . "\"";
			$json_item .= "},";
			$json_item .= "\"geometry\": {";
			$json_item .= "\"type\":\"Point\", ";
			$json_item .= "\"coordinates\":[" . $single['longitude'] . ", " . $single['latitude'] . "]";
			$json_item .= "}";
			$json_item .= "}";

			array_push($json_array, $json_item);
		}

		$json = implode(",", $json_array);
		
		// 
		// E.Kala July 27, 2011
		// @todo Parking this geometry business for review
		// 
		
		// if (count($geometry_array))
		// {
		// 	$json = implode(",", $geometry_array).",".$json;
		// }
		
		header('Content-type: application/json; charset=utf-8');
        $controller->template->json = $json;

    }//end cluster method
	    
    
  
  
  
  
  
  
  
  
     /**************************************************************
     * Retrieve timeline JSON
     * $on_the_back_end is used to set if the user is looking at this on the backend or not
     */
    public static function json_timeline( $controller, 							 
								$on_the_back_end = true,
								$extra_where_text = "",
                                $joins = array(),
                                $custom_category_to_table_mapping = array())
    {
    	$category_ids = array('0');
    	
    	if (isset($_GET['c']) AND is_array($_GET['c']))
    	{
    		$category_ids = array();
    		//make sure we only hanlde numeric cat ids
    		foreach($_GET['c'] as $cat)
    		{
    			if(is_numeric($cat))
    			{
    				$category_ids[] = $cat;
    			}
    		}
    	}
    	
    	
		$is_all_categories = false;
		If(count($category_ids) == 0 || $category_ids[0] == '0')
		{
			$is_all_categories = true;
		}

        $controller->auto_render = FALSE;
        $db = new Database();
	
	
	    $interval = (isset($_GET["i"]) AND !empty($_GET["i"])) ? $_GET["i"] : "month";


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

	
        //more than one color
    	if(count($category_ids) == 1 AND intval($category_ids[0]) == 0 )
		{
			$colors = array(Kohana::config('settings.default_map_all'));
		}		
		else 
		{	
			//more than one color
			$colors = array();
			foreach($category_ids as $cat)
			{
				$colors[] = ORM::factory('category', $cat)->category_color;
			}
		}	
		$color = self::merge_colors($colors);
	
        $graph_data = array();
        $graph_data[0] = array();
        $graph_data[0]['label'] = "Category Title"; //is this used for anything?        
        $graph_data[0]['color'] = '#'.$color;
        $graph_data[0]['data'] = array();

		
	$incidents = reports::fetch_incidents();
	
	
	$approved_IDs_str = "('-1')";
	if(count($incidents) > 0)
	{
		$i = 0;
		$approved_IDs_str = "(";
		foreach($incidents as $incident)
		{
			$i++;
			$approved_IDs_str = ($i > 1) ? $approved_IDs_str.', ' : $approved_IDs_str;
			$approved_IDs_str = $approved_IDs_str."'".$incident->incident_id."'";
		}
		$approved_IDs_str = $approved_IDs_str.") ";
	}

        $query = 'SELECT UNIX_TIMESTAMP('.$select_date_text.') AS time, COUNT(id) AS number FROM '.enhancedmap_helper::$table_prefix.'incident WHERE incident.id in'.$approved_IDs_str.' GROUP BY '.$groupby_date_text;
	$query = $db->query($query);

        foreach ( $query as $items )
        {
            array_push($graph_data[0]['data'],
                array($items->time * 1000, $items->number));
        }

        header('Content-type: application/json; charset=utf-8');
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
				$south = $marker['latitude'];
			}
			elseif ($marker['latitude'] < $south)
			{
				$south = $marker['latitude'];
			}

			if (!$west)
			{
				$west = $marker['longitude'];
			}
			elseif ($marker['longitude'] < $west)
			{
				$west = $marker['longitude'];
			}

			if (!$north)
			{
				$north = $marker['latitude'];
			}
			elseif ($marker['latitude'] > $north)
			{
				$north = $marker['latitude'];
			}

			if (!$east)
			{
				$east = $marker['longitude'];
			}
			elseif ($marker['longitude'] > $east)
			{
				$east = $marker['longitude'];
			}

			$lat_sum += $marker['latitude'];
			$lon_sum += $marker['longitude'];
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
    
    
	


}//end class enhancedmap_core


	enhancedmap_helper_Core::init();



