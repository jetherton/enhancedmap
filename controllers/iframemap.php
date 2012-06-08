<?php defined('SYSPATH') or die('No direct script access.');
/**
 * This is the map controller for the iFrame Map. Since the iFrame map has special needs
 *
 * This file is adapted from the file Ushahidi_Web/appliction/controllers/json.php
 * Originally written by the Ushahidi Team
 *
 * 
 *
 * @author     John Etherton <john@ethertontech.com>
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
 */
class Iframemap_Controller extends Template_Controller {

	public $auto_render = TRUE;

    // Main template
	public $template = 'layout';

    // Cache instance
	protected $cache;

	// Cacheable Controller
	public $is_cachable = FALSE;

	// Session instance
	protected $session;

	// Table Prefix
	protected $table_prefix;

	// Themes Helper
	protected $themes;

	public function __construct()
	{
		parent::__construct();

        // Load cache
		$this->cache = new Cache;

		// Load Session
		$this->session = Session::instance();

        // Load Header & Footer
		$this->template->header  = new View('enhancedmap/iframe_map_header');
		$this->template->footer  = new View('enhancedmap/iframe_map_footer');

		// Themes Helper
		$this->themes = new Themes();
		$this->themes->editor_enabled = false;
		$this->themes->api_url = Kohana::config('settings.api_url');
		$this->template->header->submit_btn = $this->themes->submit_btn();
		$this->template->header->languages = $this->themes->languages();
		$this->template->header->search = $this->themes->search();

		// Set Table Prefix
		$this->table_prefix = Kohana::config('database.default.table_prefix');

		// Retrieve Default Settings
		$site_name = Kohana::config('settings.site_name');
			// Prevent Site Name From Breaking up if its too long
			// by reducing the size of the font
			if (strlen($site_name) > 20)
			{
				$site_name_style = " style=\"font-size:21px;\"";
			}
			else
			{
				$site_name_style = "";
			}
		$this->template->header->site_name = $site_name;
		$this->template->header->site_name_style = $site_name_style;
		$this->template->header->site_tagline = Kohana::config('settings.site_tagline');

		$this->template->header->this_page = "";

		// Google Analytics
		$google_analytics = Kohana::config('settings.google_analytics');
		$this->template->footer->google_analytics = $this->themes->google_analytics($google_analytics);

        // Load profiler
        // $profiler = new Profiler;

        // Get tracking javascript for stats
        if(Kohana::config('settings.allow_stat_sharing') == 1){
			$this->template->footer->ushahidi_stats = Stats_Model::get_javascript();
		}else{
			$this->template->footer->ushahidi_stats = '';
		}
	}

    public function index($width=400)
    {
    	
    	
   	    //set the title of the page
    	$this->template->header->this_page = 'bigmap';
    	//javascript for the big map special features
		
    	
    	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// Setup the map
		enhancedmap_helper::setup_enhancedmap($this, "enhancedmap/iframe_mapview", "enhancedmap/css/iframe_adminmap");
		//set the site name
		$this->template->content->site_name = $this->template->header->site_name;
		//set the width of the map
		$this->template->content->width = $width;		
		
		//ARE WE CLUSTERING?
		$clustering = Kohana::config('settings.allow_clustering');
		
		/////////////////////////////////////////////////////////////////////////////////////////////////////////		
		//get the CATEGORIES
		/////////////////////////////////////////////////////////////////////////////////////////////////////////

		//get the categories
		enhancedmap_helper::set_categories($this, false);
		$json_url = ($clustering == 1) ? "iframemap_json/cluster" : "iframemap_json";
		$json_timeline_url = "bigmap_json/timeline/";
		
		
		
		//boolean filter
		$this->template->content->div_boolean_filter = enhancedmap_helper::get_boolean_filter();
		
		//boolean filter
		$this->template->content->div_categories_filter = enhancedmap_helper::set_categories();
		
		
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//setup the map
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////		
		enhancedmap_helper::set_map($this->template, $this->themes, $json_url, $json_timeline_url, 'enhancedmap/adminmap_js',
								'enhancedmap/big_main_map', 'enhancedmap/iframe_main_timeline');
		
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//setup the overlays and shares
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		enhancedmap_helper::set_overlays_shares($this);
		plugin::add_stylesheet("enhancedmap/css/jquery.hovertip-1.0");
		plugin::add_javascript("enhancedmap/js/jquery.hovertip-1.0");
		
		
		// Rebuild Header Block
		$this->template->header->header_block = $this->themes->header_block();
    	
	}
	
	public function setup()
	{
		$this->auto_render = FALSE;
		$view = View::factory("enhancedmap/iframemap_setup");
		$view->html = htmlentities('<iframe src="'. url::base().'iframemap" width="515px" height="430px"></iframe>');
		$view->render(true);
				
	}

} // End Main
