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
	public static function get_reports_list_by_cat($category_ids, $approved_text, $where_text, $logical_operator, 
		$order_by = "incident.incident_date",
		$order_by_direction = "asc",
		$joins = array())
	{
		$incidents = null;
		
		//check if we're showing all categories, or if no category info was selected then return everything
		If(count($category_ids) == 0 || $category_ids[0] == '0')
		{
			// Retrieve all markers
			$incidents = ORM::factory('incident')
				->select('incident.*, category.category_color as color, category.category_title as category_title, category.id as cat_id, '.
						'parent_cat.category_title as parent_title, parent_cat.category_color as parent_color, parent_cat.id as parent_id, '.
						'(0=1) AS is_parent')
				->with('location')
				->join('incident_category', 'incident.id', 'incident_category.incident_id','LEFT')
				->join('media', 'incident.id', 'media.incident_id','LEFT')
				->join('category', 'incident_category.category_id', 'category.id', 'LEFT')
				->join('category as parent_cat', 'category.parent_id', 'parent_cat.id', 'LEFT');
			//run code to add in extra joins
			foreach($joins as $join)
			{
				if(count($join) < 4)
				{
					$incidents = $incidents->join($join[0], $join[1], $join[2]);
				}
				else
				{
					$incidents = $incidents->join($join[0], $join[1], $join[2], $join[3]);	
				}
					
			}
			
			$incidents = $incidents->where($approved_text.$where_text)
				->orderby($order_by, $order_by_direction)
				->find_all();			    
			
		}//end if there are no category filters
		else
		{  //there are category filters to be concerned with
			// or up all the categories we're interested in
			$where_category = "";
			$test_for_parent = "";
			$i = 0;
			foreach($category_ids as $id)
			{
				$i++;
				$where_category = ($i > 1) ? $where_category . " OR " : $where_category;
				$where_category = $where_category . "(".reports_Core::$table_prefix.'incident_category.category_id = ' . $id." OR parent_cat.id = " . $id.")";

				$test_for_parent = ($i > 1) ? $test_for_parent . " OR " : $test_for_parent;
				$test_for_parent .= "(parent_cat.id =  ". $id.")";
			}

			
			//if we're using OR
			if($logical_operator == "or")
			{
				$incidents = ORM::factory('incident')
					->select('incident.*, category.category_color as color, category.category_title as category_title, category.id as cat_id, '.
						'parent_cat.category_title as parent_title, parent_cat.category_color as parent_color, parent_cat.id as parent_id, '.
						'('.$test_for_parent.') AS is_parent')
					->with('location')
					->join('incident_category', 'incident.id', 'incident_category.incident_id','RIGHT')
					->join('media', 'incident.id', 'media.incident_id','LEFT')
					->join('category', 'incident_category.category_id', 'category.id', 'LEFT')
					->join('category as parent_cat', 'category.parent_id', 'parent_cat.id', 'LEFT');
				//run code to add in extra joins
				foreach($joins as $join)
				{
					if(count($join) < 4)
					{
						$incidents = $incidents->join($join[0], $join[1], $join[2]);
					}
					else
					{
						$incidents = $incidents->join($join[0], $join[1], $join[2], $join[3]);	
					}
						
				}
				$incidents = $incidents->where($approved_text.' AND ('.$where_category. ')' . $where_text)
					->orderby($order_by, $order_by_direction)
					->orderby('incident.id')
					->find_all();
			} //end of if OR
			else //if we're using AND
			{
				// Retrieve incidents by category			
				$incidents = ORM::factory('incident')
					->select('incident.*,  category.category_color as color, category.category_title as category_title, category.id as cat_id, '.
						'parent_cat.category_title as parent_title, parent_cat.category_color as parent_color, parent_cat.id as parent_id, '.
						'('.$test_for_parent.') AS is_parent')
					->with('location')
					->join('incident_category', 'incident.id', 'incident_category.incident_id','LEFT')
					->join('category', 'incident_category.category_id', 'category.id')
					->join('media', 'incident.id', 'media.incident_id','LEFT')
					->join('category as parent_cat', 'category.parent_id', 'parent_cat.id', 'LEFT');
				//run code to add in extra joins
				foreach($joins as $join)
				{
					if(count($join) < 4)
					{
						$incidents = $incidents->join($join[0], $join[1], $join[2]);
					}
					else
					{
						$incidents = $incidents->join($join[0], $join[1], $join[2], $join[3]);	
					}
						
				}
				$incidents = $incidents->where($approved_text.' AND ('.$where_category. ')' . $where_text)
					->orderby($order_by, $order_by_direction)
					->orderby('incident.id')
					->find_all();
					
				$incidents = self::post_process_and($category_ids, $incidents);
			}//end of  else AND
		}//end of else we are using categories
		
		
		return $incidents;

	}//end method	
	
	
	
	






	/**************************************************************************************************************
      * Given all the parameters returns a list of incidents that meet the search criteria
      */
	public static function get_reports($category_ids, $approved_text, $where_text, $logical_operator, 
		$order_by = "incident.incident_date",
		$order_by_direction = "asc",
		$limit = -1, $offset = -1,
		$joins = array())
	{
	
		$incidents = null;
	
			
		
		//check if we're showing all categories, or if no category info was selected then return everything
		If(count($category_ids) == 0 || $category_ids[0] == '0')
		{
			// Retrieve all markers
			$incidents = ORM::factory('incident')
				->select('DISTINCT incident.*')
				->with('location')
				->join('media', 'incident.id', 'media.incident_id','LEFT');
			//run code to add in extra joins
			foreach($joins as $join)
			{
				if(count($join) < 4)
				{
					$incidents = $incidents->join($join[0], $join[1], $join[2]);
				}
				else
				{
					$incidents = $incidents->join($join[0], $join[1], $join[2], $join[3]);	
				}
					
			}
			$incidents = $incidents->where($approved_text.$where_text)
				->orderby($order_by, $order_by_direction);
			//are we gonna do offsets?
			if($limit != -1 && $offset != -1)
			{
				$incidents = $incidents->find_all($limit, $offset);
			}
			else
			{
				$incidents = $incidents->find_all();
			}			    
		}//end if there are no category filters
		else
		{ //we're gonna use category filters
		
			// or up all the categories we're interested in
			$where_category = "";
			$i = 0;
			foreach($category_ids as $id)
			{
				$i++;
				$where_category = ($i > 1) ? $where_category . " OR " : $where_category;
				$where_category = $where_category . "(".reports_Core::$table_prefix.'incident_category.category_id = ' . $id." OR parent_cat.id = " . $id.")";

			}

			
			//if we're using OR
			if($logical_operator == "or")
			{
			
				$incidents = ORM::factory('incident')
					->select('DISTINCT incident.*')
					->with('location')
					->join('incident_category', 'incident.id', 'incident_category.incident_id','LEFT')
					->join('media', 'incident.id', 'media.incident_id','LEFT')
					->join('category', 'incident_category.category_id', 'category.id', 'LEFT')
					->join('category as parent_cat', 'category.parent_id', 'parent_cat.id', 'LEFT');
				//run code to add in extra joins
				foreach($joins as $join)
				{
					if(count($join) < 4)
					{
						$incidents = $incidents->join($join[0], $join[1], $join[2]);
					}
					else
					{
						$incidents = $incidents->join($join[0], $join[1], $join[2], $join[3]);	
					}
						
				}
				$incidents = $incidents->where($approved_text.' AND ('.$where_category. ')' . $where_text)
					->orderby($order_by, $order_by_direction)
					->orderby('incident.id');
				//are we gonna do offsets?
				if($limit != -1 && $offset != -1)
				{
					$incidents = $incidents->find_all($limit, $offset);
				}
				else
				{
					$incidents = $incidents->find_all();
				}
			}
			else //if we're using AND
			{
			
				// Retrieve incidents by category			
				$incidents = ORM::factory('incident')
					->select('incident.*,  category.category_color as color, category.category_title as category_title, category.id as cat_id, '.
						'parent_cat.category_title as parent_title, parent_cat.category_color as parent_color, parent_cat.id as parent_id')
					->with('location')
					->join('incident_category', 'incident.id', 'incident_category.incident_id','LEFT')
					->join('category', 'incident_category.category_id', 'category.id')
					->join('media', 'incident.id', 'media.incident_id','LEFT')
					->join('category as parent_cat', 'category.parent_id', 'parent_cat.id', 'LEFT');
					//run code to add in extra joins
				foreach($joins as $join)
				{
					if(count($join) < 4)
					{
						$incidents = $incidents->join($join[0], $join[1], $join[2]);
					}
					else
					{
						$incidents = $incidents->join($join[0], $join[1], $join[2], $join[3]);	
					}
						
				}

				$incidents = $incidents->where($approved_text.' AND ('.$where_category. ')' . $where_text)
					->orderby($order_by, $order_by_direction)
					->orderby('incident.id')
					->find_all();	
				$incidents = self::post_process_and($category_ids, $incidents);
				
				if($limit != -1 && $offset != -1)
				{
					$incidents = array_slice($incidents, $offset, $limit);
				}
			}//end of  else AND
		}//end of else we are using categories
		
		
		return $incidents;

	}//end method	
	
	
	
	
	/**************************************************************************************************************
      * Given all the parameters returns the count of incidents that meet the search criteria
      */
	public static function get_reports_count($category_ids, $approved_text, $where_text, $logical_operator,
		$joins = array())
	{
		$incidents_count = -1;
		
		
		//run a filter just in case someone wants to mess with this:
		$data = array(
					"was_changed_by_plugin" => false,
					"category_ids" => $category_ids, 
					"approved_text" => $approved_text, 
					"where_text" => $where_text, 
					"logical_operator" => $logical_operator
					);
		Event::run('ushahidi_filter.admin_map_get_reports_count', $data);
		//check if someone has changed this and see what we get
		//in case the filter changed the data, make sure it gets passed in
		$incidents_count = $data["incidents_count"];
		
		//check if we're showing all categories, or if no category info was selected then return everything
		If(count($category_ids) == 0 || $category_ids[0] == '0')
		{
			// Retrieve all markers
			
			$incidents_count = ORM::factory('incident')
				->select('DISTINCT incident.*')
				->with('location')
				->join('media', 'incident.id', 'media.incident_id','LEFT');
			//run code to add in extra joins
			foreach($joins as $join)
			{
				if(count($join) < 4)
				{
					$incidents = $incidents->join($join[0], $join[1], $join[2]);
				}
				else
				{
					$incidents = $incidents->join($join[0], $join[1], $join[2], $join[3]);	
				}
					
			}

			$incidents = $incidents->where($approved_text.$where_text)
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
				$where_category = $where_category . "(".reports_Core::$table_prefix.'incident_category.category_id = ' . $id." OR parent_cat.id = " . $id.")";

			}

			
			//if we're using OR
			if($logical_operator == "or")
			{
				$incidents_count = ORM::factory('incident')
					->select('DISTINCT incident.*, COUNT(DISTINCT '.Kohana::config('database.default.table_prefix').'incident.id) as incidents_found' )
					->with('location')
					->join('incident_category', 'incident.id', 'incident_category.incident_id','LEFT')
					->join('media', 'incident.id', 'media.incident_id','LEFT')
					->join('category', 'incident_category.category_id', 'category.id', 'LEFT')
					->join('category as parent_cat', 'category.parent_id', 'parent_cat.id', 'LEFT');
				//run code to add in extra joins
				foreach($joins as $join)
				{
					if(count($join) < 4)
					{
						$incidents = $incidents->join($join[0], $join[1], $join[2]);
					}
					else
					{
						$incidents = $incidents->join($join[0], $join[1], $join[2], $join[3]);	
					}
						
				}

				$incidents = $incidents->where($approved_text.' AND ('.$where_category. ')' . $where_text)
					->find();
				$incidents_count = $incidents_count->incidents_found;
			}
			else //if we're using AND
			{
				// Retrieve incidents by category			
				$incidents = ORM::factory('incident')
					->select('incident.*,  category.category_color as color, category.category_title as category_title, category.id as cat_id, '.
						'parent_cat.category_title as parent_title, parent_cat.category_color as parent_color, parent_cat.id as parent_id')
					->with('location')
					->join('incident_category', 'incident.id', 'incident_category.incident_id','LEFT')
					->join('category', 'incident_category.category_id', 'category.id')
					->join('media', 'incident.id', 'media.incident_id','LEFT')
					->join('category as parent_cat', 'category.parent_id', 'parent_cat.id', 'LEFT');
				//run code to add in extra joins
				foreach($joins as $join)
				{
					if(count($join) < 4)
					{
						$incidents = $incidents->join($join[0], $join[1], $join[2]);
					}
					else
					{
						$incidents = $incidents->join($join[0], $join[1], $join[2], $join[3]);	
					}
						
				}

				$incidents = $incidents->where($approved_text.' AND ('.$where_category. ')' . $where_text)
					->find_all();
					
				$incidents = self::post_process_and($category_ids, $incidents);
				
				$incidents_count = count($incidents);
				
			}
			
		}//end else we are using category IDs

		
		return $incidents_count;
		
	}//end method	
	
	
	/**********************************************************
	 * Does a shallow copy of an array
	 * Both arrays need to be initialized before calling this
	 *********************************************************/
	public static function array_copy($source)
	{
		$destination = array();
		foreach($source as $key => $value)
		{
			$destination[$key] = $value;
		}
		
		return $destination;
	}
	

	/********************************************************************
	 * Given the input from the database when looking for AND reports
	 * This runs the post processing on them. I tried really hard to do it
	 * all in SQL, but doing AND with matches on both parent categories 
	 * and child categories was to involved, and probably lost some of  its
	 * SQL effeciency with the massively complex queries I was writing
	 **********************************************************************/
	public static function post_process_and($category_ids, $incidents)
	{
		$new_incidents = array();		
		$cats = self::array_copy($category_ids);
		$last_incident = null;
		
		foreach($incidents as $incident)
		{
			if($last_incident!=null && $last_incident->id != $incident->id)
			{
				//have all the categories been matched?
				if(count($cats) == 0)
				{
					//then add this incident to the list of correctly ANDed incidents
					$new_incidents[] = $last_incident;
				}
				$cats = self::array_copy($category_ids);
				self::array_copy($category_ids, $cats);	
			}
			
			$last_incident = $incident;
			//see which category ID this incident was matched on, parent or child
			//first check kid
			$child_key = array_search($incident->cat_id, $cats);
			if($child_key !== false && $child_key !== null)
			{
				unset($cats[$child_key]);
			}
			else
			{
				//check kids
				$parent_key = array_search($incident->parent_id, $cats);
				if($parent_key !== false && $parent_key !== null)
				{
					unset($cats[$parent_key]);
				}
			}
		}//end loop
		
		//catch the last one
		if($last_incident != null)
		{
			//have all the categories been matched?
			if(count($cats) == 0)
			{
				//then add this incident to the list of correctly ANDed incidents
				$new_incidents[] = $last_incident;
			}
		}

		return $new_incidents;

	}
	
	
}//end class reports_core


	reports_Core::init();

