<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Time Span - sets up the hooks
 *
 * @author	   John Etherton
 * @package	   Admin Map
 */

class adminmap {
	
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
			plugin::add_javascript("adminmap/js/embedd_setup");
			plugin::add_stylesheet("adminmap/css/embedd_setup");
			Event::add('ushahidi_action.main_sidebar', array($this, '_add_embedd'));
		}
		//if dealing with the
		if(Router::$controller == "reports")
		{
			Event::add('ushahidi_filter.fetch_incidents_set_params', array($this,'_add_logical_operator_filter'));
			
			Event::add('ushahidi_action.report_filters_ui', array($this,'_add_report_filter_ui'));
			
			Event::add('ushahidi_action.header_scripts', array($this, '_add_report_filter_js'));
		}		
	}
	
	/**
	 * This little guy will add the JS to the /reports page so we can switch between AND and Or
	 */
	public function _add_report_filter_js()
	{
			$view = new View('adminmap/report_filter_js');
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
		$view = new View('adminmap/report_filter_ui');
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
				if($params[$i] == $category_sql)
				{
					$found_it = true;
					break;					
				}
				$i++;
			}
			if($found_it)
			{
				unset($params[$i]);
				$after_base = substr(url::current(), strlen(url::base()));
				$only_public = (strpos($after_base, "admin") === 0) ? "" : " AND amc.category_visible = 1 "; 
				
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
					if (count($category_ids) > 0)
					{
						foreach($category_ids as $c)
						{
							array_push($params,
							'i.id IN (SELECT DISTINCT incident_id FROM '.$table_prefix.'incident_category amic '.
							'INNER JOIN '.$table_prefix.'category amc ON (amc.id = amic.category_id) '.
							'WHERE ((amc.id = '. $c . ') OR amc.parent_id = (' . $c . '))'.$only_public.' ) ');
						}
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
		$tabs = Event::$data;
		$tabs['adminmap'] = Kohana::lang('adminmap.admin_map_main_menu_tab');
		Event::$data = $tabs;
	}
	
	//adds the "Full Screen Map" button on the main page
	public function _add_big_map_main_button()
	{
		echo '<div ><a class="bigmapbutton" style="border:2px solid grey; padding: 2px;" href="'.url::site().'bigmap"> VIEW FULL MAP </a></div>';
	}
	
	//adds a tab for the big map on the front end
	public function _add_big_map_tab()
	{
		$this_page = Event::$data;
		
		$menu = "";
		$menu .= "<li><a href=\"".url::site()."bigmap\" ";
		$menu .= ($this_page == 'bigmap') ? " class=\"active\"" : "";
		$menu .= ">". Kohana::lang('adminmap.big_map_main_menu_tab')."</a></li>";
		echo $menu;
	}
	

	
}//end class

new adminmap;