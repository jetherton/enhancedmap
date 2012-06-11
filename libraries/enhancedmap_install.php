<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Installation script for the Admin Map plugin, doesn't really do anything
 * since the Admin Map doesn't use any database tables
 *
 *
 * @author     John Etherton <john@ethertontech.com>
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
 */

class Enhancedmap_Install {

	/**
	 * Constructor to load the shared database library
	 */
	public function __construct()
	{
		$this->db = Database::instance();
	}

	/**
	 * Creates the required database tables for the actionable plugin
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
		
 
		
		
	}

	/**
	 * Deletes the database tables for the actionable module
	 */
	public function uninstall()
	{
		
	
	}
}