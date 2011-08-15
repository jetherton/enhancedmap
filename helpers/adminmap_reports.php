<?php
/**
 * Reports helper for the Admin Map plugin
 * 
 * @package    Admin Map
 * @author     John Etherton
 */
class adminmap_reports_Core {



	// Table Prefix
	public static $table_prefix;

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
		$joins = array(),
		$custom_category_to_table_mapping = array())
	{
		$incidents = null;
		
		//check if we're showing all categories, or if no category info was selected then return everything
		If(count($category_ids) == 0 || $category_ids[0] == '0')
		{
			// Retrieve all markers
			$incidents = ORM::factory('incident')
				/*->select('incident.*, category.category_color as color, category.category_title as category_title, category.id as cat_id, '.
						'parent_cat.category_title as parent_title, parent_cat.category_color as parent_color, parent_cat.id as parent_id, '.
						'(0=1) AS is_parent')*/
				->with('location');
				//->join('incident_category', 'incident.id', 'incident_category.incident_id','LEFT')
				//->join('media', 'incident.id', 'media.incident_id','LEFT')
				//->join('category', 'incident_category.category_id', 'category.id', 'LEFT')
				//->join('category as parent_cat', 'category.parent_id', 'parent_cat.id', 'LEFT');
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

			
			// OR up all the categories we're interested in
			$where_category = adminmap_reports::or_up_categories($category_ids, $custom_category_to_table_mapping);
			$test_text = adminmap_reports::create_test_for_match($category_ids, $custom_category_to_table_mapping);
			
			$custom_cat_selects = adminmap_reports::create_custom_category_selects($category_ids, $custom_category_to_table_mapping);

			
			//if we're using OR
			if($logical_operator == "or")
			{
				$incidents = ORM::factory('incident')
					->select('incident.*, category.category_color as color, category.category_title as category_title, category.id as cat_id, '.
						'parent_cat.category_title as parent_title, parent_cat.category_color as parent_color, parent_cat.id as parent_id'.
						$test_text.' '. $custom_cat_selects)
					->with('location')
					->join('incident_category', 'incident.id', 'incident_category.incident_id','RIGHT')
					//->join('media', 'incident.id', 'media.incident_id','LEFT')
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
						'parent_cat.category_title as parent_title, parent_cat.category_color as parent_color, parent_cat.id as parent_id'.
						$test_text.' '. $custom_cat_selects)
					->with('location')
					->join('incident_category', 'incident.id', 'incident_category.incident_id','LEFT')
					->join('category', 'incident_category.category_id', 'category.id')
					//->join('media', 'incident.id', 'media.incident_id','LEFT')
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
					
				$incidents = self::post_process_and($category_ids, $incidents, $custom_category_to_table_mapping);
			}//end of  else AND
		}//end of else we are using categories
		
		
		return $incidents;

	}//end method	
	
	
	
	






	/**************************************************************************************************************
      * Given all the parameters returns a list of incidents that meet the search criteria
      //custom_category_to_table_mapping --- This assumes that the custom category you're
      mapping into this has the same basic setup as the core ushahidi table "category" that is that
      you will be comparing <your category name>.id and <your category name>.parent_id. You are
      responsible for including the necesary joins. Below is the category to table mapping used for the 
      simple groups plugin as an example:
      $custom_category_to_table_mapping = array("SG"=>array(
											"child"=>"simplegroups_category", 
											"parent"=>"simplegroups_parent_cat")
										);
      /**************************************************************************************************************/
	public static function get_reports($category_ids, 
		$approved_text, 
		$where_text, 
		$logical_operator, 
		$order_by = "incident.incident_date",
		$order_by_direction = "asc",
		$limit = -1, $offset = -1,
		$joins = array(),
		$custom_category_to_table_mapping = array())
	{
	
		
	
		$incidents = null;
	
			
		
		//check if we're showing all categories, or if no category info was selected then return everything
		If(count($category_ids) == 0 || $category_ids[0] == '0')
		{
			// Retrieve all markers
			$incidents = ORM::factory('incident')
				->select('DISTINCT incident.*')
				->with('location');
				//->join('media', 'incident.id', 'media.incident_id','LEFT')
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
		
			// OR up all the categories we're interested in
			$where_category = adminmap_reports::or_up_categories($category_ids, $custom_category_to_table_mapping);
			
						
			//if we're using OR
			if($logical_operator == "or")
			{
			
				$incidents = ORM::factory('incident')
					->select('DISTINCT incident.*')
					->with('location')
					->join('incident_category', 'incident.id', 'incident_category.incident_id','LEFT')
					//->join('media', 'incident.id', 'media.incident_id','LEFT')
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
			
				//based on what's in the custom cat mappings make some fancy selects
				$custom_cat_selects = adminmap_reports::create_custom_category_selects($category_ids, $custom_category_to_table_mapping);
			
				// Retrieve incidents by category			
				$incidents = ORM::factory('incident')
					->select('incident.*,  category.category_color as color, category.category_title as category_title, category.id as cat_id, '.
						'parent_cat.category_title as parent_title, parent_cat.category_color as parent_color, parent_cat.id as parent_id'.
						$custom_cat_selects)
					->with('location')
					->join('incident_category', 'incident.id', 'incident_category.incident_id','LEFT')
					->join('category', 'incident_category.category_id', 'category.id')
					//->join('media', 'incident.id', 'media.incident_id','LEFT')
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
				
				$incidents = self::post_process_and($category_ids, $incidents, $custom_category_to_table_mapping);
				
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
      /**************************************************************************************************************/
	public static function get_reports_count($category_ids, 
		$approved_text, 
		$where_text, 
		$logical_operator,
		$joins = array(),
		$custom_category_to_table_mapping = array())
	{
		$incidents_count = -1;
		
		
		//run a filter just in case someone wants to mess with this:
		$data = array(
					"was_changed_by_plugin" => false,
					"category_ids" => $category_ids, 
					"approved_text" => $approved_text, 
					"where_text" => $where_text, 
					"logical_operator" => $logical_operator,
					"incidents_count" => $incidents_count,
					"custom_category_to_table_mapping" => $custom_category_to_table_mapping
					);
		Event::run('ushahidi_filter.admin_map_get_reports_count', $data);
		//check if someone has changed this and see what we get
		//in case the filter changed the data, make sure it gets passed in
		if($data["was_changed_by_plugin"])
		{
			return $incidents_count = $data["incidents_count"];
		}
		
		//check if we're showing all categories, or if no category info was selected then return everything
		If(count($category_ids) == 0 || $category_ids[0] == '0')
		{
			// Retrieve all markers
			
			$incidents = ORM::factory('incident')
				->select('DISTINCT incident.*')
				->with('location');
				//->join('media', 'incident.id', 'media.incident_id','LEFT');
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

			//I hate finding the count this way because it forces you to download all 
			//the incidents and not just a number, but if i use count_all() it sometimes gives 
			//erroneous numbers, but doing it this way seems to work. I imagine 
			//it has to do with the way count and distinct work together.
			$incidents_found = $incidents->where($approved_text.$where_text)->find_all();

			$incidents_count = count($incidents_found);
			
			    
			
		}
		else //we are using category IDs, double the fun
		{
			// OR up all the categories we're interested in
			$where_category = adminmap_reports::or_up_categories($category_ids, $custom_category_to_table_mapping);
			
						
			//if we're using OR
			if($logical_operator == "or")
			{
				$incidents = ORM::factory('incident')
					->select('DISTINCT incident.*, COUNT(DISTINCT '.Kohana::config('database.default.table_prefix').'incident.id) as incidents_found' )
					->with('location')
					->join('incident_category', 'incident.id', 'incident_category.incident_id','LEFT')
					//->join('media', 'incident.id', 'media.incident_id','LEFT')
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
				$incidents_count = $incidents->incidents_found;
			}
			else //if we're using AND
			{
			
				//based on what's in the custom cat mappings make some fancy selects
				$custom_cat_selects = adminmap_reports::create_custom_category_selects($category_ids, $custom_category_to_table_mapping);
				
			
			
				// Retrieve incidents by category			
				$incidents = ORM::factory('incident')
					->select('incident.*,  category.category_color as color, category.category_title as category_title, category.id as cat_id, '.
						'parent_cat.category_title as parent_title, parent_cat.category_color as parent_color, parent_cat.id as parent_id'.
						$custom_cat_selects)
					->with('location')
					->join('incident_category', 'incident.id', 'incident_category.incident_id','LEFT')
					->join('category', 'incident_category.category_id', 'category.id')
					//->join('media', 'incident.id', 'media.incident_id','LEFT')
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
	
	
	/**************************************************************************
	* Create select statements to pull colors, titles, and 
	***************************************************************************/
	private static function create_custom_category_selects($category_ids, $custom_category_to_table_mapping)
	{
	
		$found_group_cats = false;

		if(is_array($category_ids)) //sometimes if we're looking at all categories there won't be an array, but just a string of "0"
		{
			$custom_cat_selects = "";
			
			//look for our category ID marker "SG" and then if we find it make the appropriate where SQL
			foreach($category_ids as $cat_id)
			{
				
				$delimiter_pos  = strpos($cat_id, "_");
				if ($delimiter_pos !==false)
				{
					//we're gonna need some joins
					$found_group_cats = true;
					break;				
				}
			}//end for each
		}//end if is_array
	
		if($found_group_cats)
		{
			//based on what's in the custom cat mappings make some fancy selects		
			foreach($custom_category_to_table_mapping as $name => $tables)
			{
				$custom_cat_selects .= ", ".$tables["child"].".category_color as ".$name."_color";
				$custom_cat_selects .= ", ".$tables["child"].".category_title as ".$name."_title";
				$custom_cat_selects .= ", ".$tables["child"].".id as ".$name."_cat_id";
				
				$custom_cat_selects .= ", ".$tables["parent"].".category_color as ".$name."_parent_color";
				$custom_cat_selects .= ", ".$tables["parent"].".category_title as ".$name."_parent_title";
				$custom_cat_selects .= ", ".$tables["parent"].".id as ".$name."_parent_cat_id";
			}
		}
		return $custom_cat_selects;
	}
	
	
	
	
	
	/**********************************************************************************************
	* Create a big WHERE clause for all the categories we're interested in
	**********************************************************************************************/
	private static function or_up_categories($category_ids, $custom_category_to_table_mapping)
	{
		$where_category = "";
		$i = 0;
		foreach($category_ids as $id)
		{
			$i++;
			//first we wana check and see if this is a site wide category or a custom category
			$delimiter_pos = strpos($id, "_");
			if($delimiter_pos !== false)
			{
				//get the custom category name
				$custom_cat_name = substr($id, 0, $delimiter_pos);
				//get the custom category's numeric id
				$custom_cat_id = substr($id,$delimiter_pos + 1);
				
				//check to make sure an index is set in custom_category_to_table_mapping for this custom cateogry
				//if not throw an error
				if(!isset($custom_category_to_table_mapping[$custom_cat_name]))
				{
					throw new Exception("No custom category to table mapping was supplied for $custom_cat_name. Unable to determine which tables in the database to use to look up this category");
				}
				
				$child_table = $custom_category_to_table_mapping[$custom_cat_name]["child"];
				$parent_table = $custom_category_to_table_mapping[$custom_cat_name]["parent"];
				
				$where_category = ($i > 1) ? $where_category . " OR " : $where_category;
				$where_category = $where_category . "(".adminmap_reports::$table_prefix.$child_table.".id = " . $custom_cat_id. " OR ".$parent_table.".id = " . $custom_cat_id.")";
				
			}
			else
			{	//this a normal, site wide, category, so treat it normally								
				$where_category = ($i > 1) ? $where_category . " OR " : $where_category;
				$where_category = $where_category . "(".adminmap_reports::$table_prefix.'incident_category.category_id = ' . $id." OR parent_cat.id = " . $id.")";
			}

		}
		
		return $where_category;
	}
	
	
	/**********************************************************************************************
	* Create a big WHERE clause for all the categories we're interested in
	**********************************************************************************************/
	private static function create_test_for_match($category_ids, $custom_category_to_table_mapping)
	{
		////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//iniate an array to hold all of the stuff
		$test_text_array = array("default"=>array("parent"=>"", "child"=>""));		
		foreach($custom_category_to_table_mapping as $name=>$custom_cat)
		{
			$test_text_array[$name] = array("parent"=>"", "child"=>"");
		}
				
		foreach($category_ids as $id)
		{
			//first we wana check and see if this is a site wide category or a custom category
			$delimiter_pos = strpos($id, "_");
			if($delimiter_pos !== false)
			{
				//get the custom category name
				$custom_cat_name = substr($id, 0, $delimiter_pos);
				//get the custom category's numeric id
				$custom_cat_id = substr($id,$delimiter_pos + 1);
				
				//check to make sure an index is set in custom_category_to_table_mapping for this custom cateogry
				//if not throw an error
				if(!isset($custom_category_to_table_mapping[$custom_cat_name]))
				{
					throw new Exception("No custom category to table mapping was supplied for $custom_cat_name. Unable to determine which tables in the database to use to look up this category");
				}
				
				$child_table = $custom_category_to_table_mapping[$custom_cat_name]["child"];
				$parent_table = $custom_category_to_table_mapping[$custom_cat_name]["parent"];
				
				$str_len = strlen($test_text_array[$custom_cat_name]["parent"]);
				if($str_len > 0)
				{
					$test_text_array[$custom_cat_name]["parent"] .= " OR ";
					$test_text_array[$custom_cat_name]["child"] .= " OR ";
				}
				$test_text_array[$custom_cat_name]["parent"] .= "(".$parent_table.".id = " . $custom_cat_id.")";
				$test_text_array[$custom_cat_name]["child"] .=  "(".$child_table.".id = " . $custom_cat_id.")";
				
			}
			else
			{
				//this a normal, site wide, category, so treat it normally								
				$str_len = strlen($test_text_array["default"]["parent"]);
				if($str_len > 0)
				{
					$test_text_array["default"]["parent"] .= " OR ";
					$test_text_array["default"]["child"] .= " OR ";
				}
				$test_text_array["default"]["parent"] .= "(parent_cat.id = " . $id.")";
				$test_text_array["default"]["child"] .=  "(category.id = " . $id.")";
			}

		}//end big loop
		
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//Now go back over everything and put it together
		////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		$test_text = "";
		//first the default stuff
		if(strlen($test_text_array["default"]["parent"]) > 0)
		{
			$test_text = $test_text . ", (".$test_text_array["default"]["parent"].") AS is_parent";
			$test_text = $test_text . ", (".$test_text_array["default"]["child"].") AS is_child";
		}
		//now the custom categories
		foreach($custom_category_to_table_mapping as $name=>$custom_cat)
		{
			if(strlen($test_text_array[$name]["parent"]) > 0)
			{
				$test_text = $test_text . ", (".$test_text_array[$name]["parent"].") AS is_".$name."_parent";
				$test_text = $test_text . ", (".$test_text_array[$name]["child"].") AS is_".$name."_child";
			}
		}

		
		return $test_text;
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
	public static function post_process_and($category_ids, $incidents, $custom_category_to_table_mapping=array())
	{
		$new_incidents = array();		
		$cats = self::array_copy($category_ids);
		$last_incident = null;
		
		foreach($incidents as $incident)
		{
			//echo $incident->incident_title."\r\n";
			
			//end condtion
			if($last_incident!=null && $last_incident->id != $incident->id)
			{
				//have all the categories been matched?
				if(count($cats) == 0)
				{
					//then add this incident to the list of correctly ANDed incidents
					$new_incidents[] = $last_incident;
					//echo $last_incident->incident_title. " approved!!!\r\n";
				}
				$cats = self::array_copy($category_ids);
				self::array_copy($category_ids, $cats);	
			}
			$incident_array = $incident->as_array();
			
			$last_incident = $incident;
			//see which category ID this incident was matched on, parent or child
			//first check kid
			if(isset($incident_array["cat_id"]))
			{
				$child_key = array_search($incident_array["cat_id"], $cats);
				if($child_key !== false && $child_key !== null)
				{
					unset($cats[$child_key]);
				}
				else
				{
					//check parent
					if(isset($incident_array["parent_id"]))
					{
						$parent_key = array_search($incident_array["parent_id"], $cats);
						if($parent_key !== false && $parent_key !== null)
						{
							unset($cats[$parent_key]);
						}
					}
				}
			}
			
			//now check custom categories			
			foreach($custom_category_to_table_mapping as $name => $table)
			{
				if(isset($incident_array[$name."_cat_id"]))
				{
					$child_key = array_search($name."_".$incident_array[$name."_cat_id"], $cats);
					if($child_key !== false && $child_key !== null)
					{
						unset($cats[$child_key]);
					}
					else
					{
						if(isset($incident_array[$name."_parent_cat_id"]))
						{
						//echo $incident->incident_title. ": $name _parent_cat_id: ".$incident_array[$name."_parent_cat_id"]."\r\n";
						
							//check parent
							$parent_key = array_search($name."_".$incident_array[$name."_parent_cat_id"], $cats);
							if($parent_key !== false && $parent_key !== null)
							{
								unset($cats[$parent_key]);
							}
						}
					}
				}
			}//end for each custom category						
		}//end loop over all incidents
		
		//catch the last one
		if($last_incident != null)
		{
			//echo $last_incident->incident_title . " still waiting\r\n";
			//have all the categories been matched?
			//echo Kohana::debug($cats);
			if(count($cats) == 0)
			{
				//then add this incident to the list of correctly ANDed incidents
				$new_incidents[] = $last_incident;
			}
		}

		return $new_incidents;

	}
	
	
}//end class reports_core


	adminmap_reports_Core::init();

