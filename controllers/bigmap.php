<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author     John Etherton <john@ethertontech.com>
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
 * @license	   GNU Lesser GPL (LGPL) Rights pursuant to Version 3, June 2007
 * @copyright  2012 Etherton Technologies Ltd. <http://ethertontech.com>
 * @Date	   2010-12-04
 * Purpose:	   This is main map controller for the enhanced map plugin.
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
 * 2010-12-04:  Etherton - Initial release
 *
 * Developed by Etherton Technologies Ltd.
 */
class Bigmap_Controller extends Main_Controller {

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
	 * Views:enhancedmap/big_map_header, enhancedmap/big_map_footer
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
		
		$this->themes->map_enabled = TRUE;
		$this->themes->slider_enabled = TRUE;

        // Load Header & Footer
		$this->template->header  = new View('enhancedmap/big_map_header');
		$this->template->footer  = new View('enhancedmap/big_map_footer');

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

		$this->template->footer->footer_block = $this->themes->footer_block();
	}

	
	
	
	
	
	
	/**
	 * Function: index
	 *
	 * Description: This controller calls the helper functions to create and assemble the various components
	 * needed to make a working map
	 *
	 * Views: enhancedmap/big_mapview, 
	 *
	 * Results: User gets a map that they can interact with
	 */
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
		$clustering = cookie::get('clustering', Kohana::config('settings.allow_clustering'));
		
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
		
		//clustering selector
		$this->template->content->div_clustering_selector = enhancedmap_helper::get_clustering_selector();

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
	 * Function: fitler_cats
	 *
	 * Description: Used to set how the category list will be displayed, alphabetized or not
	 *
	 * Views: enhancedmap/categories_filter
	 *
	 * Results: renders the cateogry filter as indicated by the settings
	 */
	public function fitler_cats()
	{
		$alphabetize = isset($_GET['alphabetize']);
		
		$fitler = enhancedmap_helper::set_categories(false, false, "enhancedmap/categories_filter",
			"category_switch", $alphabetize );

		$filter->render(true);
	}
	
	
	
} // End Main
