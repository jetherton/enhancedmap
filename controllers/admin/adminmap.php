<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Admin Map - Administrative Controller
 *
 * This file is adapted from the file Ushahidi_Web/appliction/controllers/main.php
 * Originally written by the Ushahidi Team
 * 
 * @author     John Etherton <john@ethertontech.com>
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
 */

class adminmap_Controller extends Admin_Controller
{

	function __construct()
	{
		parent::__construct();
		
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
		enhancedmap_helper::set_map($this->template, $this->template, $json_url, $json_timeline_url);
		
		//layers
		$this->template->content->div_layers_filter = enhancedmap_helper::set_layers(true);
		
		//shares
		$this->template->content->div_shares_filter = enhancedmap_helper::set_shares(true, false);
		
	}//end index method

	
	
	
	
	
}