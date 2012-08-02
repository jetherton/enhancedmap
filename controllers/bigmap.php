<?php defined('SYSPATH') or die('No direct script access.');
/**
 * This is main map controller for the enhanced map plugin. Most all of the map
 * rendering reququest go through this controller
 *
 * 
 *
 * @author     John Etherton <john@ethertontech.com>
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
 */
class Bigmap_Controller extends Template_Controller {

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
		$this->template->header  = new View('enhancedmap/big_map_header');
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
    	//set the title of the page
    	$this->template->header->this_page = 'bigmap';
    	//javascript for the big map special features
		plugin::add_javascript("enhancedmap/js/bigmap");
    	
    	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// Setup the map
		enhancedmap_helper::setup_enhancedmap($this, "enhancedmap/big_mapview", "enhancedmap/css/big_enhancedmap");

		
		//ARE WE CLUSTERING?
		$clustering = Kohana::config('settings.allow_clustering');
		
		/////////////////////////////////////////////////////////////////////////////////////////////////////////		
		//get the CATEGORIES
		/////////////////////////////////////////////////////////////////////////////////////////////////////////
		
		
		//status filter
		$this->template->content->div_status_filter = enhancedmap_helper::get_status_filter();
		
		//boolean filter
		$this->template->content->div_boolean_filter = enhancedmap_helper::get_boolean_filter();
		
		//category filter
		$this->template->content->div_category_filter = enhancedmap_helper::set_categories(false);
		
		//dot size selector
		$this->template->content->div_dotsize_selector = enhancedmap_helper::get_dotsize_selector();

		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//setup the overlays and shares
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		
		
		//layers
		$this->template->content->div_layers_filter = enhancedmap_helper::set_layers();
		
		//shares
		$this->template->content->div_shares_filter = enhancedmap_helper::set_shares(false, false);
		
		
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//setup the map
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		$json_url = ($clustering == 1) ? "bigmap_json/cluster" : "bigmap_json";
		$json_timeline_url = "bigmap_json/timeline/";
		enhancedmap_helper::set_map($this->template, $this->themes, $json_url, $json_timeline_url, 'enhancedmap/adminmap_js',
								'enhancedmap/big_main_map', 'enhancedmap/big_main_timeline');
		

		plugin::add_stylesheet("enhancedmap/css/jquery.hovertip-1.0");
		plugin::add_javascript("enhancedmap/js/jquery.hovertip-1.0");
		
		
		// Rebuild Header Block
		$this->template->header->header_block = $this->themes->header_block();
    	
	}//end 

	
	/**
	 * Used to set how the category list will be displayed
	 */
	public function fitler_cats()
	{
		$alphabetize = isset($_GET['alphabetize']);
		
		$fitler = enhancedmap_helper::set_categories(false, false, "enhancedmap/categories_filter",
			"category_switch", $alphabetize );

		$filter->render(true);
	}
	
	
	
} // End Main
