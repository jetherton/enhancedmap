<?php defined('SYSPATH') or die('No direct script access.');
/**
 * This file hooks into Ushahidi so the admin map plugin can do its thing.
 *
 *
 * @author     John Etherton <john@ethertontech.com>
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
 */
class enhancedmap {
	
	/**
	 * Registers the main event add method
	 */
	public function __construct()
	{
	
		// Hook into routing
		Event::add('system.pre_controller', array($this, 'add'));
		
	}
	
	/**
	 * Adds all the events to the main Ushahidi application
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
			Event::add('ushahidi_filter.fetch_incidents_set_params', array($this,'_add_logical_operator_filter'));
		}

		
		if(Router::$controller == "adminmap")
		{
			//hide the content div
			Event::add('ushahidi_action.header_scripts_admin',array($this,'_hide_content_for_adminmap'));
		}
		
		//adds the ability to see all approved and unapproved reports
		Event::add('ushahidi_filter.fetch_incidents_set_params', array($this,'_add_all_reports_filter'));
		
		
	}
	
	/**
	 * This function adds a flag that'll cause the incident::get_incidents to show all reports
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
	
				if (strcmp($value, 'c.category_visible = 1') == 0)
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
	 * This little guy will add the JS to the /reports page so we can switch between AND and Or
	 */
	public function _add_report_filter_js()
	{
			$view = new View('enhancedmap/report_filter_js');
			$view->selected_categories = implode(",", $this->_get_categories());
			$view->render(true);
	}
	
	
	/**
	 * This little zinger does all the HTTP GET parsing to figure out what categories are in play
	 * Enter description here ...
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
	 * This little guy will add the UI to the /reports page so we can switch between AND and OR
	 */
	public function _add_report_filter_ui()
	{
			
		$operator = $this->_get_logical_operator();
		$view = new View('enhancedmap/report_filter_ui');
		$view->operator = $operator;		
		$view->render(true);
	}
	
	/**
	 * implements AND in the reports fetch helper
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
	 * figures out what the logical operator is
	 * defaults to OR
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
	 * Adds the embed code
	 */
	public function _add_embedd()
	{
		echo '<span id="base_url" style="display:none;">'.url::base().'</span>';
	}
	
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
	
	//adds the "Full Screen Map" button on the main page
	public function _add_big_map_main_button()
	{
		echo '<div ><a class="bigmapbutton" style="border:2px solid grey; padding: 2px;" href="'.url::site().'bigmap"> VIEW FULL MAP </a></div>';
	}
	
	//adds a tab for the big map on the front end
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
	
	public function _add_printmap()
	{
		$map = Event::$data;
		$map = str_replace('<div id="mapOutput"></div>','<div id="mapOutput"></div><div id="printmap-link"><a href="'.url::site('printmap').'">Print a map</a></div>', $map);
		Event::$data = $map;
	}
	
	/**
	 * Return true if we're on the backend
	 * false otherwise.
	 */
	private function _on_back_end()
	{
		return strpos(url::current(), 'admin/') === 0;
	}
	
	public function _hide_content_for_adminmap()
	{
		echo '<style type="text/css"> #content{display:none;}</style>';
	}
	
}//end class

new enhancedmap;