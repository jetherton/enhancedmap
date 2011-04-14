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
		Event::add('ushahidi_action.map_main_filters', array($this, '_add_big_map_main_button'));	 //adds the big map  tab
		Event::add('ushahidi_action.nav_admin_main_top', array($this, '_admin_nav_tab'));	 //adds the admin map  tab
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