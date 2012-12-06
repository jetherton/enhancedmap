<?php defined('SYSPATH') or die('No direct script access.');
/**
 * This renders the print map key
 *
 *
 * @author     John Etherton <john@ethertontech.com>
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
 */
class Printmapkey_Controller extends Controller {

	function getKey($catIds = "0", $logicalOperator = "or", $startDate = "1", $endDate = "2")
	{
		//Format the dates
		$keyStartDate = date("M j, Y", $startDate);
		$keyEndDate = date("M j, Y", $endDate);
		

		//handle the logic operator
		if($logicalOperator == "or")
		{
			$logicStr = Kohana::lang('enhancedmap.logic_str_or');
		}
		else
		{
			$logicStr = Kohana::lang('enhancedmap.logic_str_and');
		}
		
		
		
		//do the categories
		//check if we're dealing with all categories
		$cat_data = array();
		if($catIds == "" || $catIds == "0" || $catIds == "0," || $catIds == "undefined" )
		{
			$cat_data[0] = array("color"=>Kohana::config('settings.default_map_all'), "name"=>Kohana::lang('ui_main.all_categories'));			
		}
		else 
		{
			$catIds = explode(",", $catIds);
			$groupWhereStr = "";
			$whereStr = "";
			$i = 0;
			$g = 0;
			foreach($catIds as $catId)
			{
				if(strpos($catId, "sg")=== FALSE )
				{
					$i++;
					if($i > 1)
					{
						$whereStr .= " || ";
					}
					$whereStr .= "id = $catId";
				}
				else
				{
					$g++;
					if($g > 1)
					{
						$groupWhereStr .= " || ";
					}
					$groupWhereStr .= "id = ".substr($catId, 3);
				}
			}
			
			if($whereStr != "")
			{
				$categories = ORM::factory("category")
					->where($whereStr)
					->find_all();
					
				foreach($categories as $cat)
				{
					$cat_data[$cat->id] = array("color"=>$cat->category_color, "name"=>$cat->category_title);
				}
			}
			
			if($groupWhereStr != "")
			{
				$categories = ORM::factory("simplegroups_category")
					->where($groupWhereStr)
					->find_all();
					
				foreach($categories as $cat)
				{
					$cat_data[$cat->id] = array("color"=>$cat->category_color, "name"=>$cat->category_title);
				}
			}
		}
		
		
		
		
		$view = View::factory("enhancedmap/printmapkey");
		$view->logic = $logicStr;
		$view->keyStartDate = $keyStartDate;
		$view->keyEndDate = $keyEndDate;
		$view->categories = $cat_data;
		
		$view->render(true);
		
	}

} // End Main
