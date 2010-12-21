<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Admin Map - Administrative Controller
 *
 * @author	   John Etherton
 * @package	   Admin Map
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
		
	}
	
	public function index()
	{
		
		adminmap_helper::setup_adminmap($this);
		
		//get the categories
		adminmap_helper::set_categories($this);
		
		//setup the map
		$clustering = Kohana::config('settings.allow_clustering');
		$json_url = ($clustering == 1) ? "json/cluster" : "json";
		adminmap_helper::set_map($this, $json_url);
		
		//setup the overlays and shares
		adminmap_helper::set_overlays_shares($this);
		
	}//end index method

	
	
	
	
	
}