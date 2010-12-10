<?php
/**
 * Plugins helper
 * 
 * @package    Plugin
 * @author     Ushahidi Team
 * @copyright  (c) 2008 Ushahidi Team
 * @license    http://www.ushahidi.com/license.html
 */
class reports_Core {



	// Table Prefix
	protected static $table_prefix;

	static function init()
	{
		// Set Table Prefix
		self::$table_prefix = Kohana::config('database.default.table_prefix');
	}


	/**************************************************************************************************************
      * Given all the parameters returns a list of incidents that meet the search criteria
      */
	public static function get_reports($category_ids, $approved_text, $where_text, $logical_operator, 
		$order_by = "incident.incident_date",
		$order_by_direction = "asc",
		$limit = -1, $offset = -1)
	{
		$incidents = null;
		//check if we're showing all categories, or if no category info was selected then return everything
		If(count($category_ids) == 0 || $category_ids[0] == '0')
		{
			// Retrieve all markers
			
			    if($limit != -1 && $offset != -1)
			    {
				$incidents = ORM::factory('incident')
					->select('DISTINCT incident.*')
					->with('location')
					->join('media', 'incident.id', 'media.incident_id','LEFT')
					->where($approved_text.$where_text)
					->orderby($order_by, $order_by_direction)
					->find_all($limit, $offset);
			    }
			    else
			    {
				$incidents = ORM::factory('incident')
					->select('DISTINCT incident.*')
					->with('location')
					->join('media', 'incident.id', 'media.incident_id','LEFT')
					->where($approved_text.$where_text)
					->orderby($order_by, $order_by_direction)
					->find_all();
			    }
			    
			return $incidents;
		}
		
		// or up allthe categories we're interested in
		$where_category = "";
		$i = 0;
		foreach($category_ids as $id)
		{
			$i++;
			$where_category = ($i > 1) ? $where_category . " OR " : $where_category;
			$where_category = $where_category . reports_Core::$table_prefix.'incident_category.category_id = ' . $id;
		}

		
		//if we're using OR
		if($logical_operator == "or")
		{
			
			// Retrieve incidents by category			
			if($limit != -1 && $offset != -1)
			{
				$incidents = ORM::factory('incident')
					->select('DISTINCT incident.*')
					->with('location')
					->join('incident_category', 'incident.id', 'incident_category.incident_id','LEFT')
					->join('media', 'incident.id', 'media.incident_id','LEFT')
					->where($approved_text.' AND ('.$where_category. ')' . $where_text)
					->orderby($order_by, $order_by_direction)
					->find_all($limit, $offset);
			}
			else
			{
				$incidents = ORM::factory('incident')
					->select('DISTINCT incident.*')
					->with('location')
					->join('incident_category', 'incident.id', 'incident_category.incident_id','LEFT')
					->join('media', 'incident.id', 'media.incident_id','LEFT')
					->where($approved_text.' AND ('.$where_category. ')' . $where_text)
					->orderby($order_by, $order_by_direction)
					->find_all();
			}
				
			return $incidents;
		}
		else //if we're using AND
		{
		
			if($limit != -1 && $offset != -1)
			{
				// Retrieve incidents by category			
				$incidents = ORM::factory('incident')
					->select('incident.*, COUNT(incident.id) as category_count')
					->with('location')
					->join('incident_category', 'incident.id', 'incident_category.incident_id','LEFT')
					->join('media', 'incident.id', 'media.incident_id','LEFT')
					->where($approved_text.' AND ('.$where_category. ')' . $where_text)
					->groupby('incident.id')
					->having('category_count', count($category_ids))
					->orderby($order_by, $order_by_direction)
					->find_all($limit, $offset);
			}
			else
			{
				// Retrieve incidents by category			
				$incidents = ORM::factory('incident')
					->select('incident.*, COUNT(incident.id) as category_count')
					->with('location')
					->join('incident_category', 'incident.id', 'incident_category.incident_id','LEFT')
					->join('media', 'incident.id', 'media.incident_id','LEFT')
					->where($approved_text.' AND ('.$where_category. ')' . $where_text)
					->groupby('incident.id')
					->having('category_count', count($category_ids))
					->orderby($order_by, $order_by_direction)
					->find_all();
			}
					
			return $incidents;
		}

	}//end method	
	
	
	
	
	/**************************************************************************************************************
      * Given all the parameters returns the count of incidents that meet the search criteria
      */
	public static function get_reports_count($category_ids, $approved_text, $where_text, $logical_operator)
	{
		$incidents_count = -1;
		
		//check if we're showing all categories, or if no category info was selected then return everything
		If(count($category_ids) == 0 || $category_ids[0] == '0')
		{
			// Retrieve all markers
			
			$incidents_count = ORM::factory('incident')
				->select('DISTINCT incident.*')
				->with('location')
				->join('media', 'incident.id', 'media.incident_id','LEFT')
				->where($approved_text.$where_text)
				->count_all();
			    
			return $incidents_count;
		}
		
		// or up allthe categories we're interested in
		$where_category = "";
		$i = 0;
		foreach($category_ids as $id)
		{
			$i++;
			$where_category = ($i > 1) ? $where_category . " OR " : $where_category;
			$where_category = $where_category . reports_Core::$table_prefix.'incident_category.category_id = ' . $id;
		}

		
		//if we're using OR
		if($logical_operator == "or")
		{
			$incidents_count = ORM::factory('incident')
				->select('DISTINCT incident.*')
				->with('location')
				->join('incident_category', 'incident.id', 'incident_category.incident_id','LEFT')
				->join('media', 'incident.id', 'media.incident_id','LEFT')
				->where($approved_text.' AND ('.$where_category. ')' . $where_text)
				->count_all();
			return $incidents_count;
		}
		else //if we're using AND
		{
			// Retrieve incidents by category			
			$incidents_count = ORM::factory('incident')
				->select('incident.*, COUNT(incident.id) as category_count')
				->with('location')
				->join('incident_category', 'incident.id', 'incident_category.incident_id','LEFT')
				->join('media', 'incident.id', 'media.incident_id','LEFT')
				->where($approved_text.' AND ('.$where_category. ')' . $where_text)
				->groupby('incident.id')
				->having('category_count', count($category_ids))
				->count_all();
			return $incidents_count;
		}

	}//end method	
	
	
}//end class reports_core


	reports_Core::init();

