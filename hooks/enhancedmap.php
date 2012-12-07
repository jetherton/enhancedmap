<?php defined('SYSPATH') or die('No direct script access.');

/**
 * @author     John Etherton <john@ethertontech.com>
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
 * @license	   GNU Lesser GPL (LGPL) Rights pursuant to Version 3, June 2007
 * @copyright  2012 Etherton Technologies Ltd. <http://ethertontech.com>
 * @Date	   2012-06-06
 * Purpose:	   This file hooks into Ushahidi so the admin map plugin can do its thing.
 * Inputs:     Internal calls from modules
 * Outputs:    Depends on the function called
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
 * 2012-06-06:  Etherton - Initial release
 *
 * Developed by Etherton Technologies Ltd.
 */
class enhancedmap {
	
	
	/**
	 * Function: __construct
	 *
	 * Description: Registers the main event add method
	 *
	 * Views:
	 *
	 * Results: The Enhanced Map plugin is plugged in to the plugin system
	 */
	public function __construct()
	{
	
		// Hook into routing
		Event::add('system.pre_controller', array($this, 'add'));
		
	}
	

	/**
	 * Function: add
	 *
	 * Description: Adds all the events to the main Ushahidi application
	 *
	 * Views:
	 *
	 * Results: This plugin has registered for all the events that it wants to know about
	 */
	public function add()
	{
		//Just in case we need this
		Event::add('ushahidi_action.nav_main_top', array($this, '_add_big_map_tab'));	 //adds the big map  tab		
		Event::add('ushahidi_action.nav_admin_main_top', array($this, '_admin_nav_tab'));	 //adds the admin map  tab
		if(Router::$controller == "main")
		{
			Event::add('ushahidi_action.map_main_filters', array($this, '_add_big_map_main_button'));	 //adds the big map  tab
			//use sneaky JS
			if (ORM::factory('enhancedmap_settings')->where('key', 'enable_iframemap')->find()->value == "true")
			{
				plugin::add_javascript("enhancedmap/js/embedd_setup");
				plugin::add_stylesheet("enhancedmap/css/embedd_setup");
				Event::add('ushahidi_action.main_sidebar', array($this, '_add_embedd'));
			}
			plugin::add_stylesheet("enhancedmap/css/printmap_link");
			Event::add('ushahidi_filter.map_main', array($this, '_add_printmap'));
		}
		//if dealing with the
		if(Router::$controller == "reports")
		{
			Event::add('ushahidi_action.report_filters_ui', array($this,'_add_report_filter_ui'));
			
			Event::add('ushahidi_action.header_scripts', array($this, '_add_report_filter_js'));
		}

		//always filter the fetch incidents params, well don't use it if the high performance version is at play
		if(Router::$controller != "hpbigmap_json" AND Router::$controller != "hpiframemap_json"
				AND Router::$controller != "hpadminmap_json")
		{
			plugin::add_javascript("enhancedmap/js/LoadingPanel");
			Event::add('ushahidi_filter.fetch_incidents_set_params', array($this,'_add_logical_operator_filter'));
		}

		
		if(Router::$controller == "adminmap")
		{
			plugin::add_javascript("enhancedmap/js/LoadingPanel");
			//hide the content div
			Event::add('ushahidi_action.header_scripts_admin',array($this,'_hide_content_for_adminmap'));
		}
		
		//adds the ability to see all approved and unapproved reports
		Event::add('ushahidi_filter.fetch_incidents_set_params', array($this,'_add_all_reports_filter'));
		
		
	}
	
	
	
	

	/**
	 * Function: _add_all_reports_filter
	 *
	 * Description: This function adds a flag that'll cause the incident::get_incidents to show all reports.
	 * Called as a result of the following event(s): ushahidi_filter.fetch_incidents_set_params
	 *
	 * Views:
	 *
	 * Results: sneaks a fast one in and bypasses Ushahidi's attempt to hide unapproved reports
	 */
	public function _add_all_reports_filter()
	{
		//check if we're on the backend or not
		$on_backend = $this->_on_back_end();
		//see if the user we're dealing with can see reports
		// If user doesn't have access, redirect to dashboard
		if(isset($_SESSION['auth_user']))
		{		
			$user = new User_Model($_SESSION['auth_user']->id);
			$user_view_reports = admin::permissions($user, "reports_view");
		} 
		else
		{
			$user_view_reports = false;
		}
		
		$params = Event::$data;
		
		//also check and see if we want to show maybe, online approved, or only unapproved, you never know.
		//but check against the settings first
		if((ORM::factory('enhancedmap_settings')->where('key', 'show_unapproved_backend')->find()->value == 'true' AND $on_backend)
				OR (ORM::factory('enhancedmap_settings')->where('key', 'show_unapproved_frontend')->find()->value == 'true' AND !$on_backend AND $user_view_reports)) 
		{
			if(isset($_GET['u']) AND intval($_GET['u']) > 0)
			{
				$params["all_reports"] = TRUE;
				$show_unapproved = intval($_GET['u']);
				if($show_unapproved == '1')
				{
					array_push($params, '(i.incident_active = 1)');
				}
				else if($show_unapproved == '2')
				{
					array_push($params, '(i.incident_active = 0)');
				}
				
			}
		}

		//only show hidden cats if the user is on the backend
		if($on_backend AND ORM::factory('enhancedmap_settings')->where('key', 'show_hidden_categories_backend')->find()->value == 'true')
		{
			//also make it so you can see any categories, not just the visible ones
			$i = null;
			$found_it = false;
			
			foreach($params as $key=>$value)
			{
	
				if (! is_array($value) AND strcmp($value, 'c.category_visible = 1') == 0)
				{
					$found_it = true;
					$i = $key;
					break;
				}
			}
			if($found_it)
			{
				unset($params[$i]);
			}
		}
		
		Event::$data = $params;
	}
	
	
	
	
	
	
	
	/**
	 * Function: _add_report_filter_js
	 *
	 * Description: This little guy will add the JS to the /reports page so we can switch between AND and OR
	 * Called as a result of the following event(s): ushahidi_action.header_scripts when the controller is set to 'reports'
	 *
	 * Views:
	 *
	 * Results: Adds the JS to the /reports page so we can switch between AND and OR
	 */
	public function _add_report_filter_js()
	{
			$view = new View('enhancedmap/report_filter_js');
			$view->selected_categories = implode(",", $this->_get_categories());
			$view->render(true);
	}
	
	
	
	
	
	
	
	
	
	


	/**
	 * Function: _get_categories
	 *
	 * Description: This little zinger does all the HTTP GET parsing to figure out what categories are in play
	 * Called as a result of the following event(s): none, this is a helper method
	 * 
	 * @return array - Category IDs
	 *
	 * Views:
	 *
	 * Results: $_GET is parsed like you wouldn't believe
	 */
	private function _get_categories()
	{
		$category_ids = array();
			
			if ( isset($_GET['c']) AND !is_array($_GET['c']) AND intval($_GET['c']) > 0)
			{
				// Get the category ID
				$category_ids[] = intval($_GET['c']);			
			}
			elseif (isset($_GET['c']) AND is_array($_GET['c']))
			{
				// Sanitize each of the category ids
				
				foreach ($_GET['c'] as $c_id)
				{
					if (intval($c_id) > 0)
					{
						$category_ids[] = intval($c_id);
					}
				}
			}
			
			return $category_ids;
	}
	

	
	
	
	/**
	 * Function: _add_report_filter_js
	 *
	 * Description: This little guy will add the UI to the /reports page so we can switch between AND and OR
	 * Called as a result of the following event(s): ushahidi_action.report_filters_ui when the controller is set to 'reports'
	 *
	 * Views:
	 *
	 * Results: Adds the UI to the /reports page so we can switch between AND and OR
	 */
	public function _add_report_filter_ui()
	{
			
		$operator = $this->_get_logical_operator();
		$view = new View('enhancedmap/report_filter_ui');
		$view->operator = $operator;		
		$view->render(true);
	}
	
	
	
	
	
	
	
	
	/**
	 * Function: _add_logical_operator_filter
	 *
	 * Description: Implements AND in the reports fetch helper
	 * Called as a result of the following event(s): ushahidi_filter.fetch_incidents_set_params
	 *
	 * Views:
	 *
	 * Results: the fetch parameters are altered
	 */
	public function _add_logical_operator_filter()
	{		
		//are we dealing with AND, cause if we're not we don't have to do anything?
		if($this->_get_logical_operator() == "and")
		{
			//get the table prefix
			$table_prefix = Kohana::config('database.default.table_prefix');
			
			//first create a duplicate bit of SQL like the reports helper would
			$category_sql = $this->_create_default_category_sql();
			if($category_sql == "")
			{
				return; //seems they're looking at everything, so ignore this
			}
			
			//there is something there, so lets find it and remove it.
			$params = Event::$data;
			$i = 0;
			$found_it = false;
			while($i < count($params))
			{
				if(isset($params[$i]))
				{
					if($params[$i] == $category_sql)
					{
						$found_it = true;
						break;					
					}
				}
				$i++;
			}
			if($found_it)
			{
				unset($params[$i]);
				
				$only_public = (strpos(url::current(), "admin/") === 0) ? "" : " AND amc.category_visible = 1 "; 
				
				//now replace it
				$category_sql = "";
				if ( isset($_GET['c']) AND !is_array($_GET['c']) AND intval($_GET['c']) > 0)
				{
					// Get the category ID
					$category_id = intval($_GET['c']);
					
					// Add category parameter to the parameter list
					array_push($params, '(c.id = '.$category_id.' OR c.parent_id = '.$category_id.')');
				}
				elseif (isset($_GET['c']) AND is_array($_GET['c']))
				{
					
					// Sanitize each of the category ids
					$category_ids = array();
					foreach ($_GET['c'] as $c_id)
					{
						if (intval($c_id) > 0)
						{
							$category_ids[] = intval($c_id);
						}
					}
					
					// Check if there are any category ids
					$cat_count = count($category_ids);
					if ($cat_count > 0)
					{
						$sql = 'i.id IN (SELECT DISTINCT incident_id FROM '.$table_prefix.'incident_category amic '.
							'INNER JOIN '.$table_prefix.'category amc ON (amc.id = amic.category_id) '.
							'WHERE ';
						
						
						$category_ids = implode(",", $category_ids);
							
						$sql .=	'(amc.id IN ('.$category_ids.') OR amc.parent_id IN ('.$category_ids.'))';
						$sql .= $only_public; 
						$sql .= ' GROUP BY incident_id HAVING COUNT(*) = '. $cat_count. ')';
						
						array_push($params, $sql);
					}
				}//end it's an array
				Event::$data = $params;				
			}//end found it
		}//end it's == and		
	}//end method
	
	
	
	
	
	/**
	 * Function: _create_default_category_sql
	 *
	 * Description: Mimics the way Ushahidi creates the fetch params array, so we can undo it, then override what it does
	 * Called as a result of the following event(s): none, this is a helper method
	 *
	 * Views:
	 *
	 * Results: Duplicates what Ushahidi does, so we can undo it
	 */
	private function _create_default_category_sql()
	{
		// 
		// Check for the category parameter
		//

		$category_sql = "";
		if ( isset($_GET['c']) AND !is_array($_GET['c']) AND intval($_GET['c']) > 0)
		{
			//just one category, so AND has no effect, so just return an empty string
			return "";
		}
		elseif (isset($_GET['c']) AND is_array($_GET['c']))
		{
			// Sanitize each of the category ids
			$category_ids = array();
			foreach ($_GET['c'] as $c_id)
			{
				if (intval($c_id) > 0)
				{
					$category_ids[] = intval($c_id);
				}
			}
			// Check if there are any category ids
			if (count($category_ids) > 0)
			{
				$category_ids = implode(",", $category_ids);
			
				$category_sql = '(c.id IN ('.$category_ids.') OR c.parent_id IN ('.$category_ids.'))';
			}
		}
		return $category_sql;
	}
	
	

	
	

	
	/**
	 * Function: _get_logical_operator
	 *
	 * Description: figures out what the logical operator is. Defaults to OR.
	 * Called as a result of the following event(s): none, this is a helper method
	 *
	 * Views:
	 *
	 * Results: Duplicates what Ushahidi does, so we can undo it
	 */
	private function _get_logical_operator()
	{
		$lo = "or";
		if ( isset($_GET['lo']) AND !is_array($_GET['lo']) AND strtolower($_GET['lo']) == "and" )
		{
			$lo = "and";
		}
		return $lo;
	}
	
	
	
	

	
	
	
	/**
	 * Function: _add_embedd
	 *
	 * Description: Hides the url base for the iframe embedding code in a hidden span. 
	 * Now that Ushahidi has events in the JS headers this isn't really needed.
	 * Triggered by: ushahidi_action.main_sidebar
	 *
	 * Views:
	 *
	 * Results: things are hidden in spans that JS needs for use later
	 */
	public function _add_embedd()
	{
		echo '<span id="base_url" style="display:none;">'.url::base().'</span>';
	}
	
	
	
	/**
	 * Function: _admin_nav_tab
	 *
	 * Description: Creates the navigation tab for the admin map
	 * Triggered by: ushahidi_action.nav_admin_main_top
	 *
	 * Views:
	 *
	 * Results: Navigation tabs for admin map are created
	 */
	public function _admin_nav_tab()
	{
		//only show this if the settings allow it
		if(ORM::factory('enhancedmap_settings')->where('key', 'enable_adminmap')->find()->value == "true")
		{
			$tabs = Event::$data;
			$tabs['adminmap'] = Kohana::lang('enhancedmap.admin_map_main_menu_tab');
			Event::$data = $tabs;
		}
	}
	
	
	
	/**
	 * Function: _add_big_map_main_button
	 *
	 * Description: adds the "Full Screen Map" button on the main page
	 * Triggered by: ushahidi_action.map_main_filters
	 *
	 * Views:
	 *
	 * Results: Big button for a big map
	 */
	public function _add_big_map_main_button()
	{
		echo '<div ><a class="bigmapbutton" style="border:2px solid grey; padding: 2px;" href="'.url::site().'bigmap"> VIEW FULL MAP </a></div>';
	}
	
	
	
    /**
	 * Function: _add_big_map_tab
	 *
	 * Description: adds tabs for the various enahanced map pages on thefront end
	 * Triggered by: ushahidi_action.map_main_filters
	 *
	 * Views:
	 *
	 * Results: Big button for a big map
	 */
	public function _add_big_map_tab()
	{
		//only do this if the settings allow it
		if(ORM::factory('enhancedmap_settings')->where('key', 'enable_bigmap')->find()->value == "true")
		{
			$this_page = Event::$data;
			
			$menu = "";
			$menu .= "<li><a href=\"".url::site()."bigmap\" ";
			$menu .= ($this_page == 'bigmap') ? " class=\"active\"" : "";
			$menu .= ">". Kohana::lang('enhancedmap.big_map_main_menu_tab')."</a></li>";
			echo $menu;
		}
		//now add a print map button, but only if the settings allow it
		if(ORM::factory('enhancedmap_settings')->where('key', 'enable_printmap')->find()->value == "true")
		{
			$this_page = Event::$data;
				
			$menu = "";
			$menu .= "<li><a href=\"".url::site()."printmap\" ";
			$menu .= ($this_page == 'printmap') ? " class=\"active\"" : "";
			$menu .= ">". Kohana::lang('enhancedmap.print_map_main_menu_tab')."</a></li>";
			echo $menu;
		}
		
	}
	
	/**
	 * Function: _add_printmap
	 *
	 * Description: Adds a link to the print map
	 * Triggered by: ushahidi_filter.map_main
	 *
	 * Views:
	 *
	 * Results: A link to the print map is added to the main map
	 */
	public function _add_printmap()
	{
		$map = Event::$data;
		$map = str_replace('<div id="mapOutput"></div>', '<div id="mapOutput"></div><div id="printmap-link"><a href="' . url::site('printmap') . '">'.Kohana::lang('enhancedmap.print_a_map').'</a></div>', $map);
		Event::$data = $map;
	}
	

	
	
	
	
	/**
	 * Function: _on_back_end
	 *
	 * Description: Looks at the URL and figures out if we're on the backend end or not
	 * 
	 * @return bool - True if we're on the backend, otherwise, false.
	 *
	 * Views:
	 *
	 * Results: Returns true if we're on the backend, otherwise, false.
	 */
	private function _on_back_end()
	{
		return strpos(url::current(), 'admin/') === 0;
	}
	
	
	
	
	
	/**
	 * Function: _hide_content_for_adminmap
	 *
	 * Description: Adds some CSS that hides content of the adminmap. Used when rendering the iframe map
	 * Triggered by: ushahidi_action.header_scripts_admin
	 *
	 * Views:
	 *
	 * Results: CSS is echoed out
	 */
	public function _hide_content_for_adminmap()
	{
		echo '<style type="text/css"> #content{display:none;}</style>';
	}
	
}//end class

new enhancedmap;
