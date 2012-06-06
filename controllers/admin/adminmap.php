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
		enhancedmap_helper::set_categories($this, true);
		
		//setup the map
		$clustering = Kohana::config('settings.allow_clustering');
		$json_url = ($clustering == 1) ? "admin/adminmap_json/cluster" : "admin/adminmap_json";
		$json_timeline_url = "admin/adminmap_json/timeline/";
		enhancedmap_helper::set_map($this->template, $this->template, $json_url, $json_timeline_url);
		
		//setup the overlays and shares
		enhancedmap_helper::set_overlays_shares($this);
		
	}//end index method

	
	
	
	
	
}