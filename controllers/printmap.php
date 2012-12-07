<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author     John Etherton <john@ethertontech.com>
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
 * @license	   GNU Lesser GPL (LGPL) Rights pursuant to Version 3, June 2007
 * @copyright  2012 Etherton Technologies Ltd. <http://ethertontech.com>
 * @Date	   2011-05-27
 * Purpose:	   This is the controller print map map, since it has special needs
 * Inputs:     Internal calls from modules
 * Outputs:    A map for viewing by users
 *
 * The Enhanced Map, Ushahidi Plugin is free software: you can redistribute
 * it and/or modify it under the terms of the GNU Lesser General Public License
 * as published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * The Enhanced Map, Ushahidi Plugin is distributed in the hope that it will
 * be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with the Enhanced Map, Ushahidi Plugin.  If not, see
 * <http://www.gnu.org/licenses/>.
 *
 * Changelog:
 * 2011-05-27:  Etherton - Initial release
 *
 * Developed by Etherton Technologies Ltd.
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

	
	
	
	
	
	
	/**
	 * Function: __construct
	 *
	 * Description: A default constructor that sets instance variables.
	 *
	 * Views:enhancedmap/print_map_header, enhancedmap/big_map_footer
	 *
	 * Results: Instance variables are set
	 */
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

		$this->template->footer->footer_block = $this->themes->footer_block();
	}
	
	
	
	
	
	
	
	
	
	/**
	 * Function: index
	 *
	 * Description: This controller calls the helper functions to create and assemble the various components
	 * needed to make a working map
	 *
	 * Views: enhancedmap/print_mapview, enhancedmap/print_map_header
	 *
	 * Results: User gets a map that they can interact with
	 */
    public function index()
    {
    	
    	////////////////////////////////////////////////////////////////////////////////////////////////
    	// custom JS that the print map needs
    	plugin::add_javascript("enhancedmap/js/jquery.address-1.4.min.js");
    	
    	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// Setup the map
		enhancedmap_helper::setup_enhancedmap($this, "enhancedmap/print_mapview", "enhancedmap/css/print_enhancedmap");

		
		//ARE WE CLUSTERING?
		$clustering = cookie::get('clustering', Kohana::config('settings.allow_clustering'));
		
		/////////////////////////////////////////////////////////////////////////////////////////////////////////		
		//get the CATEGORIES
		/////////////////////////////////////////////////////////////////////////////////////////////////////////
		
		if(isset($this->group_id) && $this->group_id)
		{
			$group_id = $this->group_id;
			$group = ORM::factory('simplegroups_groups',$this->group_id)->find($this->group_id);
			$this->template->content->div_categories_filter = enhancedmap_helper::set_categories(false, $group);
			$urlParams = array('sgid'=>$group_id);			
		}
		else
		{
			//get the categories
			$this->template->content->div_categories_filter = enhancedmap_helper::set_categories();
			$urlParams = array();
		}
		
		
			
		
		//status filter
		$this->template->content->div_status_filter = enhancedmap_helper::get_status_filter();
		
		//boolean filter
		$this->template->content->div_boolean_filter = enhancedmap_helper::get_boolean_filter();
		
		//dot size selector
		$this->template->content->div_dotsize_selector = enhancedmap_helper::get_dotsize_selector();
		
		//clustering selector
		$this->template->content->div_clustering_selector = enhancedmap_helper::get_clustering_selector();
		

		
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//setup the map
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		$json_url = ($clustering == 1) ? "bigmap_json/cluster" : "bigmap_json";
		$json_timeline_url = "bigmap_json/timeline/";
		enhancedmap_helper::set_map($this->template, $this->themes, $json_url, $json_timeline_url, 'enhancedmap/print_mapview_js',
								'enhancedmap/big_main_map', 'enhancedmap/print_main_timeline', $urlParams);
		
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//setup the overlays and shares
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		
		//layers
		$this->template->content->div_layers_filter = enhancedmap_helper::set_layers();

		//shares
		$this->template->content->div_shares_filter = enhancedmap_helper::set_shares(false, false);
		

		
		plugin::add_stylesheet("enhancedmap/css/jquery.hovertip-1.0");
		plugin::add_javascript("enhancedmap/js/jquery.hovertip-1.0");
		


		$this->template->header  = new View('enhancedmap/print_map_header');
		$this->template->header->site_name = $this->site_name;
		$this->template->header->this_page = "printmap";
		// Rebuild Header Block
		$this->template->header->header_block = $this->themes->header_block();
	}
	
	
	
	
	
	
	
	/**
	 * Function: groups
	 *
	 * Description: This controller sets an instance variable and calls the index 
	 * function to show a map specific to a group, if the Simple Group plugin is enabled.
	 * 
	 * @param int $id - The database id of the group to render the map for
	 * 
	 * Results: User gets a map of reports specific to the given group that they can interact with
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
