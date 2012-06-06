<?php defined('SYSPATH') or die('No direct script access.');
/**
 * This is the controller print map map, since it has special needs
 *
 * @author     John Etherton <john@ethertontech.com>
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
 */
class Printmap_Controller extends Template_Controller {

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
		$this->template->header  = new View('enhancedmap/print_map_header');
		$this->template->footer  = new View('enhancedmap/big_map_footer');

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
		$this->site_name = $site_name;
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

    public function index()
    {
    	
    	////////////////////////////////////////////////////////////////////////////////////////////////
    	// custom JS that the print map needs
    	plugin::add_javascript("enhancedmap/js/jquery.address-1.4.min.js");
    	
    	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// Setup the map
		enhancedmap_helper::setup_enhancedmap($this, "enhancedmap/print_mapview", "enhancedmap/css/print_enhancedmap");

		
		//ARE WE CLUSTERING?
		$clustering = Kohana::config('settings.allow_clustering');
		
		/////////////////////////////////////////////////////////////////////////////////////////////////////////		
		//get the CATEGORIES
		/////////////////////////////////////////////////////////////////////////////////////////////////////////
		if(isset($this->group_id) && $this->group_id)
		{
			$group_id = $this->group_id;
			$group = ORM::factory('simplegroups_groups',$this->group_id)->find($this->group_id);
			//get the categories
			enhancedmap_helper::set_categories($this, false, $group);		
			$urlParams = array('sgid'=>$group_id);			
		}
		else
		{
			//get the categories
			enhancedmap_helper::set_categories($this, false);
			$urlParams = array();
		}
		
		$json_url = ($clustering == 1) ? "bigmap_json/cluster" : "bigmap_json";
		$json_timeline_url = "bigmap_json/timeline/";
			
		
		
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//setup the map
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////		
		enhancedmap_helper::set_map($this->template, $this->themes, $json_url, $json_timeline_url, 'enhancedmap/print_mapview_js',
								'enhancedmap/big_main_map', 'enhancedmap/print_main_timeline', $urlParams);
		
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//setup the overlays and shares
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		enhancedmap_helper::set_overlays_shares($this);
		plugin::add_stylesheet("enhancedmap/css/jquery.hovertip-1.0");
		plugin::add_javascript("enhancedmap/js/jquery.hovertip-1.0");
		


		$this->template->header  = new View('enhancedmap/print_map_header');
		$this->template->header->site_name = $this->site_name;
		$this->template->header->this_page = "printmap";
		// Rebuild Header Block
		$this->template->header->header_block = $this->themes->header_block();
	}
	
	
	/**
	 * For groups
	 * Enter description here ...
	 * @param unknown_type $id
	 */
	public function groups($id=false)
	{
		if(!$id)
		{
			return;
		}
		
		$this->group_id = $id;
						
		$this->index();
	}
	

} // End Main
