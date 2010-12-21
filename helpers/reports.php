<?php
/**
 * Reports helper for the Admin Map plugin
 * 
 * @package    Admin Map
 * @author     John Etherton
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
			    
			
		}//end if there are no category filters
		else
		{
		
			// or up all the categories we're interested in
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
			} //end of if OR
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
			}//end of  else AND
		}//end of else we are using categories
		
		
		//run a filter just in case someone wants to mess with this:
		$data = array(
					"incidents" => $incidents,
					"category_ids" => $category_ids, 
					"approved_text" => $approved_text, 
					"where_text" => $where_text, 
					"logical_operator" => $logical_operator, 
					"order_by" => $order_by,
					"order_by_direction" => $order_by_direction,
					"limit" => $limit, 
					"offset" => $offset
					);
		Event::run('ushahidi_filter.admin_map_get_reports', $data);
		
		//in case the filter changed the data, make sure it gets passed in
		$incidents = $data["incidents"];
		return $incidents;

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
			    
			
		}
		else //we are using category IDs, double the fun
		{
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
				
			}
		}//end else we are using category IDs

		//run a filter just in case someone wants to mess with this:
		$data = array(
					"incidents_count" => $incidents_count,
					"category_ids" => $category_ids, 
					"approved_text" => $approved_text, 
					"where_text" => $where_text, 
					"logical_operator" => $logical_operator
					);
		Event::run('ushahidi_filter.admin_map_get_reports_count', $data);
		
		//in case the filter changed the data, make sure it gets passed in
		$incidents_count = $data["incidents_count"];
		return $incidents_count;
		
	}//end method	
	
	
}//end class reports_core


	reports_Core::init();

