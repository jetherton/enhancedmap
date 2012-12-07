<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author     John Etherton <john@ethertontech.com>
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
 * @license	   GNU Lesser GPL (LGPL) Rights pursuant to Version 3, June 2007
 * @copyright  2012 Etherton Technologies Ltd. <http://ethertontech.com>
 * @Date	   2012-06-06
 * Purpose:	   Installation script for the Enhanced Map plugin
 * Inputs:     Internal calls from modules
 * Outputs:    Adds or removes the database elements that Enhanced Map needs
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


class Enhancedmap_Install {

    /**
	 * Function: __construct
	 * 	 
	 * Description: A default constructor that initializes instance variables.
	 *
	 * Views:
	 *
	 * Results: Instance variables are set
	 */
	public function __construct()
	{
		$this->db = Database::instance();
	}

	
	
	
	
	
	/**
	 * Function: run_install
	 *
	 * Description: Creates the required database tables for the actionable plugin
	 *
	 * Views:
	 *
	 * Results: Database is initialized
	 */
	public function run_install()
	{
	
		// Create the database tables.
		// Also include table_prefix in name
		$this->db->query('CREATE TABLE IF NOT EXISTS `'.Kohana::config('database.default.table_prefix').'enhancedmap_settings` (
				`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`key` char(100) NOT NULL,
				`value` char(100) NOT NULL,
				PRIMARY KEY (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1');
		
		//if there are no entries, then add some entries

		//configure showing the big map on the front end
		if(!ORM::factory('enhancedmap_settings')->where('key', 'enable_bigmap')->find()->loaded)
		{
			$frontend = ORM::factory('enhancedmap_settings');
			$frontend->key = 'enable_bigmap';
			$frontend->value = 'true';
			$frontend->save();
		}
		
		//configure showing the print map on the front end
		if(!ORM::factory('enhancedmap_settings')->where('key', 'enable_printmap')->find()->loaded)
		{
			$frontend = ORM::factory('enhancedmap_settings');
			$frontend->key = 'enable_printmap';
			$frontend->value = 'false';
			$frontend->save();
		}
		
		//configure showing the iframe map on the front end
		if(!ORM::factory('enhancedmap_settings')->where('key', 'enable_iframemap')->find()->loaded)
		{
			$frontend = ORM::factory('enhancedmap_settings');
			$frontend->key = 'enable_iframemap';
			$frontend->value = 'true';
			$frontend->save();
		}
		
		//configure showing the admin map on the back end
		if(!ORM::factory('enhancedmap_settings')->where('key', 'enable_adminmap')->find()->loaded)
		{
			$frontend = ORM::factory('enhancedmap_settings');
			$frontend->key = 'enable_adminmap';
			$frontend->value = 'true';
			$frontend->save();
		}
		
		//configure the height of the admin map
		if(!ORM::factory('enhancedmap_settings')->where('key', 'adminmap_height')->find()->loaded)
		{
			$frontend = ORM::factory('enhancedmap_settings');
			$frontend->key = 'adminmap_height';
			$frontend->value = 'other';
			$frontend->save();
		}
		
		//configure the width of the admin map
		if(!ORM::factory('enhancedmap_settings')->where('key', 'adminmap_width')->find()->loaded)
		{
			$frontend = ORM::factory('enhancedmap_settings');
			$frontend->key = 'adminmap_width';
			$frontend->value = 'other';
			$frontend->save();
		}
		
		//configure if you can see unapproved reports on the back end
		if(!ORM::factory('enhancedmap_settings')->where('key', 'show_unapproved_backend')->find()->loaded)
		{
			$frontend = ORM::factory('enhancedmap_settings');
			$frontend->key = 'show_unapproved_backend';
			$frontend->value = 'true';
			$frontend->save();
		}
		
		//configure if you can see unapproved reports on the front end
		if(!ORM::factory('enhancedmap_settings')->where('key', 'show_unapproved_frontend')->find()->loaded)
		{
			$frontend = ORM::factory('enhancedmap_settings');
			$frontend->key = 'show_unapproved_frontend';
			$frontend->value = 'false';
			$frontend->save();
		}
		
		//configure if you can see hidden categories on the back end
		if(!ORM::factory('enhancedmap_settings')->where('key', 'show_hidden_categories_backend')->find()->loaded)
		{
			$frontend = ORM::factory('enhancedmap_settings');
			$frontend->key = 'show_hidden_categories_backend';
			$frontend->value = 'true';
			$frontend->save();
		}
		
		//configure how the colors are rendered when workingg from two or more categories
		if(!ORM::factory('enhancedmap_settings')->where('key', 'color_mode')->find()->loaded)
		{
			$frontend = ORM::factory('enhancedmap_settings');
			$frontend->key = 'color_mode';
			$frontend->value = 'merge_all';
			$frontend->save();
		}
		
		//dot sizes
		if(!ORM::factory('enhancedmap_settings')->where('key', 'dot_size')->find()->loaded)
		{
			$frontend = ORM::factory('enhancedmap_settings');
			$frontend->key = 'dot_size';
			$frontend->value = '2';
			$frontend->save();
		}
		
 
		
		
	}


	
	/**
	 * Function: uninstall
	 *
	 * Description: Should uninstall the settings from the DB, but I hate the idea of careless admin
	 * not knowing that DB deletes are permanent, so right now this does nothing.
	 *
	 * Views:
	 *
	 * Results: Nothing
	 */
	public function uninstall()
	{
		
	
	}
}