<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author     John Etherton <john@ethertontech.com>
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
 * @license	   GNU Lesser GPL (LGPL) Rights pursuant to Version 3, June 2007
 * @copyright  2012 Etherton Technologies Ltd. <http://ethertontech.com>
 * @Date	   2012-06-06
 * Purpose:	   Enhanced Map settings controller
 * Inputs:     Internal calls from modules
 * Outputs:    Allows admins to view and modify the settings of the Enhanced Map plugin
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
class Enhancedmap_settings_Controller extends Admin_Controller
{

	
	
	/**
	 * Function: __construct
	 *
	 * Description: A default constructor that makes sure the user is authorized
	 * to access this controller. Also initializes instance variables.
	 *
	 * Views:
	 *
	 * Results: Unauthorized users are booted and instance variables are set
	 */
	function __construct()
	{
		parent::__construct();
		$this->template->this_page = 'settings';

		// If this is not a super-user account, redirect to dashboard
		if(!$this->auth->logged_in('admin') && !$this->auth->logged_in('superadmin'))
		{
			url::redirect('admin/dashboard');
		}
	}
	
	/**
	 * 
	 */
	/**
	 * Function: index
	 *
	 * Description: This is the function that renders and stores the settings for the enhanced map
	 *
	 * Params(POST)
	 * - adminmap_height - CSS specification of the height of the map
	 * - adminmap_width - CSS specification of the width of the map
	 * - enable_bigmap - Is the front end big map enabled?
	 * - enable_printmap - Is the print map enabled
	 * - enable_iframemap - Is the iframe map enabled
	 * - adminmap_height - The height of the admin map
	 * - show_unapproved_backend - Should unapproved reports be shown on the back end map
	 * - show_unapproved_frontend - Should unapproved reports be shown on the front end map
	 * - show_hidden_categories_backend - Should hidden categories be shown on the back end map
	 *
	 * Views: enhancedmap/enhancedmap_settings
	 *
	 * Results: Enhanced map settings are updated.
	 */
	public function index()
	{
		
		$this->template->content = new View('enhancedmap/enhancedmap_settings');
		$this->template->content->errors = array();
		$this->template->content->form_saved = false;
		$this->template->content->yesno_array = array(
				'true' => utf8::strtoupper(Kohana::lang('ui_main.yes')),
				'false' => utf8::strtoupper(Kohana::lang('ui_main.no')));
		
		$form = array();
		
		// check, has the form been submitted, if so, setup validation
		if ($_POST)
		{

			//print_r($_POST);
			//echo "<br><br/>";
			
			$post = new Validation($_POST);
		
			// Add some filters
			$post->pre_filter('trim', TRUE);
		
			// Add some rules, the input field, followed by a list of checks, carried out in order		
			$post->add_rules('adminmap_height', 'required', 'length[1,99]');
			$post->add_rules('adminmap_width', 'required', 'length[1,99]');
			/*
			$post->add_rules('enable_bigmap','required','in_array["true", "false"]');
			$post->add_rules('enable_printmap','required','in_array[\'true\', \'false\']');
			$post->add_rules('enable_iframemap','required','in_array[\'true\', \'false\']');
			$post->add_rules('adminmap_height','required','in_array[\'true\', \'false\']');
			$post->add_rules('show_unapproved_backend','required','in_array[\'true\', \'false\']');
			$post->add_rules('show_unapproved_frontend','required','in_array[\'true\', \'false\']');
			$post->add_rules('show_hidden_categories_backend','required','in_array[\'true\', \'false\']');
			*/
			
			if ($post->validate())
			{
				// Yes! everything is valid
				//load in the settings from the DB
				
				//load up all the settings
				$settings = ORM::factory('enhancedmap_settings')->find_all();
				foreach($settings as $setting)
				{
					$setting->value = $_POST[$setting->key];
					$setting->save();
				}
				$form = $_POST;
				
				$this->template->content->form_saved = true;
				
			}//end if post is valid
			// No! We have validation errors, we need to show the form again,
			// with the errors
			else
			{
;		
				// repopulate the form fields
				$form = $_POST;
			
				// populate the error fields, if any				
				$this->template->content->errors = $post->errors('settings');
			}
		}//end if a post happened
		else 		//there was no post
		{
			
			//load up all the settings
			$settings = ORM::factory('enhancedmap_settings')->find_all();
			foreach($settings as $setting)
			{
				$form[$setting->key] = $setting->value;
			}		
		}		
		
		
		
		
		$this->template->content->form = $form;
		
	}//end index()
	
	
}//end class
