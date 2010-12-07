<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Time Span - Administrative Controller
 *
 * @author	   John Etherton
 * @package	   Time Span
 */

class adminmap_Controller extends Admin_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->template->this_page = 'adminmap';

		// If this is not a super-user account, redirect to dashboard
		if(!$this->auth->logged_in('admin') && !$this->auth->logged_in('superadmin'))
		{
			url::redirect('admin/dashboard');
		}
	}
	
	public function index()
	{
		//set the CSS for this
		plugin::add_stylesheet("adminmap/css/adminmap");
		
		plugin::add_javascript("adminmap/js/jquery.flot");
		plugin::add_javascript("adminmap/js/excanvas.min");
		plugin::add_javascript("adminmap/js/timeline");
		
		$this->template->content = new View('adminmap/mapview');
		// Get Default Color
		$this->template->content->default_map_all = Kohana::config('settings.default_map_all');
		
		//get the categories
		$this->set_categories();
		
		//setup the map
		$this->set_map();
		
		//setup the overlays and shares
		$this->set_overlays_shares();
		
	}//end index method

	
	
	/****
	* Sets up the overlays and shares
	*/
	private function set_overlays_shares()
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
		$this->template->content->layers = $layers;

		// Get all active Shares
		$shares = array();
		foreach (ORM::factory('sharing')
				  ->where('sharing_active', 1)
				  ->find_all() as $share)
		{
			$shares[$share->id] = array($share->sharing_name, $share->sharing_color);
		}
		$this->template->content->shares = $shares;
	}
	
	
	/*
	* this makes the map for this plugin
	*/
	private function set_map()
	{
	
		////////////////////////////////////////////////////////////////Map and Slider Blocks////////////////////////////////////////////////////////////////////////////
		$div_map = new View('main_map');
		$div_timeline = new View('main_timeline');
			// Filter::map_main - Modify Main Map Block
			Event::run('ushahidi_filter.map_main', $div_map);
			// Filter::map_timeline - Modify Main Map Block
			Event::run('ushahidi_filter.map_timeline', $div_timeline);
		$this->template->content->div_map = $div_map;
		$this->template->content->div_timeline = $div_timeline;

	
		///////////////////////////////////////////////////////////////SETUP THE DATES////////////////////////////////////////////////////////////////////////////
	        // Get The START, END and most ACTIVE Incident Dates
		$startDate = "";
		$endDate = "";
		$active_month = 0;
		$active_startDate = 0;
		$active_endDate = 0;

		$db = new Database();
		// First Get The Most Active Month
		$query = $db->query('SELECT incident_date, count(*) AS incident_count FROM '.$this->table_prefix.'incident WHERE incident_active = 1 GROUP BY DATE_FORMAT(incident_date, \'%Y-%m\') ORDER BY incident_count DESC LIMIT 1');
		foreach ($query as $query_active)
		{
			$active_month = date('n', strtotime($query_active->incident_date));
			$active_year = date('Y', strtotime($query_active->incident_date));
			$active_startDate = strtotime($active_year . "-" . $active_month . "-01");
			$active_endDate = strtotime($active_year . "-" . $active_month .
				"-" . date('t', mktime(0,0,0,$active_month,1))." 23:59:59");
		}
		
		//run some custom events for the timeline plugin
		Event::run('ushahidi_filter.active_startDate', $active_startDate);
		Event::run('ushahidi_filter.active_endDate', $active_endDate);
		Event::run('ushahidi_filter.active_month', $active_month);

		// Next, Get the Range of Years
		$query = $db->query('SELECT DATE_FORMAT(incident_date, \'%Y\') AS incident_date FROM '.$this->table_prefix.'incident WHERE incident_active = 1 GROUP BY DATE_FORMAT(incident_date, \'%Y\') ORDER BY incident_date');
		foreach ($query as $slider_date)
		{
			$years = $slider_date->incident_date;
			$startDate .= "<optgroup label=\"" . $years . "\">";
			for ( $i=1; $i <= 12; $i++ ) {
				if ( $i < 10 )
				{
					$i = "0" . $i;
				}
				$startDate .= "<option value=\"" . strtotime($years . "-" . $i . "-01") . "\"";
				if ( $active_month &&
						( (int) $i == ( $active_month - 1)) )
				{
					$startDate .= " selected=\"selected\" ";
				}
				$startDate .= ">" . date('M', mktime(0,0,0,$i,1)) . " " . $years . "</option>";
			}
			$startDate .= "</optgroup>";

			$endDate .= "<optgroup label=\"" . $years . "\">";
			for ( $i=1; $i <= 12; $i++ )
			{
				if ( $i < 10 )
				{
					$i = "0" . $i;
				}
				$endDate .= "<option value=\"" . strtotime($years . "-" . $i . "-" . date('t', mktime(0,0,0,$i,1))." 23:59:59") . "\"";
                // Focus on the most active month or set December as month of endDate
				if ( $active_month &&
						( ( (int) $i == ( $active_month + 1)) )
						 	|| ($i == 12 && preg_match('/selected/', $endDate) == 0))
				{
					$endDate .= " selected=\"selected\" ";
				}
				$endDate .= ">" . date('M', mktime(0,0,0,$i,1)) . " " . $years . "</option>";
			}
			$endDate .= "</optgroup>";
		}

		
		//run more custom events for the timeline plugin
		Event::run('ushahidi_filter.startDate', $startDate);
		Event::run('ushahidi_filter.endDate', $endDate);	
		
		$this->template->content->div_timeline->startDate = $startDate;
		$this->template->content->div_timeline->endDate = $endDate;

		///////////////////////////////////////////////////////////////MAP JAVA SCRIPT////////////////////////////////////////////////////////////////////////////
		
		//turn the map on, also turn on the timeline
		//$this->template->flot_enabled = TRUE; //this is done using our own custom .js files in the adminmap/js folder.
		$this->template->map_enabled = TRUE;
		$this->template->js->default_map = Kohana::config('settings.default_map');
		$this->template->js->default_zoom = Kohana::config('settings.default_zoom');

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

		$this->template->js = new View('adminmap/mapview_js');
		$this->template->js->json_url = ($clustering == 1) ?
			"json/cluster" : "json";
		$this->template->js->marker_radius =
			($marker_radius >=1 && $marker_radius <= 10 ) ? $marker_radius : 5;
		$this->template->js->marker_opacity =
			($marker_opacity >=1 && $marker_opacity <= 10 )
			? $marker_opacity * 0.1  : 0.9;
		$this->template->js->marker_stroke_width =
			($marker_stroke_width >=1 && $marker_stroke_width <= 5 ) ? $marker_stroke_width : 2;
		$this->template->js->marker_stroke_opacity =
			($marker_stroke_opacity >=1 && $marker_stroke_opacity <= 10 )
			? $marker_stroke_opacity * 0.1  : 0.9;

		// pdestefanis - allows to restrict the number of zoomlevels available
		$this->template->js->numZoomLevels = $numZoomLevels;
		$this->template->js->minZoomLevel = $minZoomLevel;
		$this->template->js->maxZoomLevel = $maxZoomLevel;

		// pdestefanis - allows to limit the extents of the map
		$this->template->js->lonFrom = $lonFrom;
		$this->template->js->latFrom = $latFrom;
		$this->template->js->lonTo = $lonTo;
		$this->template->js->latTo = $latTo;

		$this->template->js->default_map = Kohana::config('settings.default_map');
		$this->template->js->default_zoom = Kohana::config('settings.default_zoom');
		$this->template->js->latitude = Kohana::config('settings.default_lat');
		$this->template->js->longitude = Kohana::config('settings.default_lon');
		$this->template->js->default_map_all = Kohana::config('settings.default_map_all');
		$this->template->js->active_startDate = $active_startDate;
		$this->template->js->active_endDate = $active_endDate;
		


	}
	
	private function set_categories()
	{
		
        // Get all active top level categories
		$parent_categories = array();
		foreach (ORM::factory('category')
				->where('category_visible', '1')
				->where('parent_id', '0')
				->find_all() as $category)
		{
			// Get The Children
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

			// Check for localization of parent category
			// Get locale
			$l = Kohana::config('locale.language.0');

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
		$this->template->content->categories = $parent_categories;
	}//end method
	
	
	
}