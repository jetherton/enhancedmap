<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author     John Etherton <john@ethertontech.com>
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
 * @license	   GNU Lesser GPL (LGPL) Rights pursuant to Version 3, June 2007
 * @copyright  2012 Etherton Technologies Ltd. <http://ethertontech.com>
 * @Date	   2011-07-19
 * Purpose:	   Json Controller for the iFrame map since the iFrame map has special needs
 * Inputs:     Internal calls from modules
 * Outputs:    A map for viewing by users in an iframe
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
 * 2011-07-19:  Etherton - Initial release
 *
 * Developed by Etherton Technologies Ltd.
 */
class Iframemap_json_Controller extends Template_Controller
{
    public $auto_render = TRUE;

    // Main template
    public $template = 'bigmap_json';

    // Table Prefix
    protected $table_prefix;

    
    
    
    
    /**
     * Function: __construct
     *
     * Description: A default constructor that sets instance variables.
     *
     * Results: Instance variables are set
     */
    public function __construct()
    {
        parent::__construct();
	
	
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
		enhancedmap_helper::json_index($this, false,  "_blank");
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
        enhancedmap_helper::json_cluster($this, false, "_blank");      
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
    public function timeline( $category_ids = "0," )
    {
		enhancedmap_helper::json_timeline($this, $category_ids, false);
    }

    
    
    
    
    
    
    
    
    
}