<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author     John Etherton <john@ethertontech.com>
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
 * @license	   GNU Lesser GPL (LGPL) Rights pursuant to Version 3, June 2007
 * @copyright  2012 Etherton Technologies Ltd. <http://ethertontech.com>
 * @Date	   2010-12-04
 * Purpose:	   Json Controller. Generates Map GeoJSON file for the admin side of things.
 *             This file is adapted from the file Ushahidi_Web/appliction/controllers/json.php
 *             Originally written by the Ushahidi Team
 * Inputs:     Internal calls from modules
 * Outputs:     GeoJSON file for the openlayer java script to display to the user
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
class Adminmap_json_Controller extends Admin_Controller
{
    public $auto_render = TRUE;

    // Main template
    public $template = 'bigmap_json';

    // Table Prefix
    protected $table_prefix;

    
    
    /**
	 * Function: __construct
	 * 	 
	 * Description: A default constructor that makes sure the user is authorized
	 * to access this controller. Also checks that the administrative map is enabled
	 * and initializes the $table_prefix instance variable
	 *
	 * Views:
	 *
	 * Results: Unauthorized users are booted and instance variables are set
	 */
    public function __construct()
    {
        parent::__construct();
	
		
		// If this is not a super-user account, redirect to dashboard
		if(!$this->auth->logged_in('admin') && !$this->auth->logged_in('superadmin'))
		{
			url::redirect('admin/dashboard');
		}
		
		//this page only works if it's allowed:
		if(ORM::factory('enhancedmap_settings')->where('key', 'enable_adminmap')->find()->value != "true")
		{
			url::redirect('admin/dashboard');
		}

        // Set Table Prefix
        $this->table_prefix = Kohana::config('database.default.table_prefix');

		// Cacheable JSON Controller
		$this->is_cachable = TRUE;
    }


	/**
	 * Function: index
	 *
	 * Description: This controller generates the non-clustered json of reports in the Ushahidi system.
	 * This controller uses the helper class to do all the work.
	 * 
	 * Views:
	 *
	 * Results: json is sent to the requesting client
	 */
    function index()
    {
		enhancedmap_helper::json_index($this);
    }


    
    /**
     * Function: cluster
     *
     * Description: This controller generates the clustered json of reports in the Ushahidi system.
     * This controller uses the helper class to do all the work.
     *
     * Views:
     *
     * Results: json is sent to the requesting client
     */
    public function cluster()
    {
        enhancedmap_helper::json_cluster($this);
    }


   
    
    
    /**
     * Function: timeline
     *
     * Description: This controller generates the timeline json of reports in the Ushahidi system.
     * This controller uses the helper class to do all the work.
     *
     * Views:
     *
     * Results: json is sent to the requesting client
     */
    public function timeline()
    {
		enhancedmap_helper::json_timeline($this);
    }
    
}