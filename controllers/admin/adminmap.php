<?php defined('SYSPATH') or die('No direct script access.');
 /**
 * @author     John Etherton <john@ethertontech.com>
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
 * @license	   GNU Lesser GPL (LGPL) Rights pursuant to Version 3, June 2007
 * @copyright  2012 Etherton Technologies Ltd. <http://ethertontech.com>
 * @Date	   2010-12-04
 * Purpose:	   Admin Map - Administrative Controller. Shows the user the administrative map
 * Inputs:     Internal calls from modules
 * Outputs:    A map for viewing by administrative users
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
class adminmap_Controller extends Admin_Controller
{

	/**
	 * Function: __construct
	 *
	 * Description: A default constructor that makes sure the user is authorized
	 * to access this controller. Also checks that the administrative map is enabled
	 * and sets instance variables
	 *
	 * Views:
	 *
	 * Results: Unauthorized users are booted and instance variables are set
	 */
	function __construct()
	{
		parent::__construct();
		
		$this->themes->map_enabled = TRUE;
		$this->themes->slider_enabled = TRUE;
		
		$this->template->this_page = 'adminmap';
		
		// If this is not a super-user account, redirect to dashboard
		if(!$this->auth->logged_in('admin') && !$this->auth->logged_in('superadmin') )
		{
			url::redirect('admin/dashboard');
		}
		
		//this page only works if it's allowed:
		if(ORM::factory('enhancedmap_settings')->where('key', 'enable_adminmap')->find()->value != "true")
		{
			url::redirect('admin/dashboard');
		}
		
	}
	
	
	/**
	 * Function: index
	 *
	 * Description: This controller calls the helper functions to create and assemble the various components
	 * needed to make a working map
	 *
	 * Views: enhancedmap/status_filter, enhancedmap/boolean_filter
	 *
	 * Results: User gets a map that they can interact with
	 */
	public function index()
	{
		enhancedmap_helper::setup_enhancedmap($this);
		
		//get the categories
		$this->template->content->div_categories_filter = enhancedmap_helper::set_categories($on_backend = true);
		
		//set the status filter
		$this->template->content->div_status_filter = enhancedmap_helper::get_status_filter($on_backend = true, 
			$status_filter_view = 'enhancedmap/status_filter', $status_filter_id = "status_filter",
			$show_unapproved = true);
		
		//set the boolean filter
		$this->template->content->div_boolean_filter = enhancedmap_helper::get_boolean_filter($on_backend = true,
				$boolean_filter_view = 'enhancedmap/boolean_filter', $status_filter_id = "boolean_filter", $show_help = false);
		
		//dot size selector
		$this->template->content->div_dotsize_selector = enhancedmap_helper::get_dotsize_selector();
		
		//clustering selector
		$this->template->content->div_clustering_selector = enhancedmap_helper::get_clustering_selector();
		
		//setup the map
		$clustering = cookie::get('clustering', Kohana::config('settings.allow_clustering'));
		$json_url = ($clustering == 1) ? "admin/adminmap_json/cluster" : "admin/adminmap_json";
		$json_timeline_url = "admin/adminmap_json/timeline/";
		enhancedmap_helper::set_map($this->template, $this->themes, $json_url, $json_timeline_url);
		
		//layers
		$this->template->content->div_layers_filter = enhancedmap_helper::set_layers(true);
		
		//shares
		$this->template->content->div_shares_filter = enhancedmap_helper::set_shares(true, false);
		
	}//end index method

	
	
	
	
	
}