<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Enhanced Map settings controller
 *
 * 
 * @author     John Etherton <john@ethertontech.com>
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
 */


class Enhancedmap_settings_Controller extends Admin_Controller
{

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
	 * This is the function that renders and stores the settings for the enhanced map
	 */
	public function index()
	{
		
		$this->template->content = new View('enhancedmap/enhancedmap_settings');
		$this->template->content->errors = array();
		$this->template->content->form_saved = false;
		$this->template->content->yesno_array = array(
				'true'=>strtoupper(Kohana::lang('ui_main.yes')),
				'false'=>strtoupper(Kohana::lang('ui_main.no')));
		
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
				//show big map tab
				$setting = ORM::factory('enhancedmap_settings')->where('key', 'enable_bigmap')->find();
				$setting->value = $post->enable_bigmap;
				$setting->save();
				
				$setting = ORM::factory('enhancedmap_settings')->where('key', 'enable_printmap')->find();
				$setting->value = $post->enable_printmap;
				$setting->save();
				
				$setting = ORM::factory('enhancedmap_settings')->where('key', 'enable_iframemap')->find();
				$setting->value = $post->enable_iframemap;
				$setting->save();
				
				$setting = ORM::factory('enhancedmap_settings')->where('key', 'enable_adminmap')->find();
				$setting->value = $post->enable_adminmap;
				$setting->save();
				
				$setting = ORM::factory('enhancedmap_settings')->where('key', 'adminmap_height')->find();
				$setting->value = $post->adminmap_height;
				$setting->save();
				
				$setting = ORM::factory('enhancedmap_settings')->where('key', 'adminmap_width')->find();
				$setting->value = $post->adminmap_width;
				$setting->save();
				
				$setting = ORM::factory('enhancedmap_settings')->where('key', 'show_unapproved_backend')->find();
				$setting->value = $post->show_unapproved_backend;
				$setting->save();
				
				$setting = ORM::factory('enhancedmap_settings')->where('key', 'show_unapproved_frontend')->find();
				$setting->value = $post->show_unapproved_frontend;
				$setting->save();
				
				$setting = ORM::factory('enhancedmap_settings')->where('key', 'show_hidden_categories_backend')->find();
				$setting->value = $post->show_hidden_categories_backend;
				$setting->save();	

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
					
			//load in the settings from the DB
			//show big map tab
			$form['enable_bigmap'] = ORM::factory('enhancedmap_settings')->where('key', 'enable_bigmap')->find()->value;
			//show print map tab
			$form['enable_printmap'] = ORM::factory('enhancedmap_settings')->where('key', 'enable_printmap')->find()->value;
			//show iframe embed code
			$form['enable_iframemap'] = ORM::factory('enhancedmap_settings')->where('key', 'enable_iframemap')->find()->value;
			//enable the admin map
			$form['enable_adminmap'] = ORM::factory('enhancedmap_settings')->where('key', 'enable_adminmap')->find()->value;
			//admin map height
			$form['adminmap_height'] = ORM::factory('enhancedmap_settings')->where('key', 'adminmap_height')->find()->value;
			//admin map width
			$form['adminmap_width'] = ORM::factory('enhancedmap_settings')->where('key', 'adminmap_width')->find()->value;
			//Show unapproved reports on the back end
			$form['show_unapproved_backend'] = ORM::factory('enhancedmap_settings')->where('key', 'show_unapproved_backend')->find()->value;
			//Show unapproved reports on the front end
			$form['show_unapproved_frontend'] = ORM::factory('enhancedmap_settings')->where('key', 'show_unapproved_frontend')->find()->value;
			//Show hidden categories on the backend
			$form['show_hidden_categories_backend'] = ORM::factory('enhancedmap_settings')->where('key', 'show_hidden_categories_backend')->find()->value;
		}		
		
		
		
		
		$this->template->content->form = $form;
		
	}//end index()
	
	
}//end class
