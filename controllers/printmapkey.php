<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author     John Etherton <john@ethertontech.com>
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
 * @license	   GNU Lesser GPL (LGPL) Rights pursuant to Version 3, June 2007
 * @copyright  2012 Etherton Technologies Ltd. <http://ethertontech.com>
 * @Date	   2011-05-27
 * Purpose:	   This renders the print map key
 * Inputs:     Internal calls from modules
 * Outputs:    The key for the printmap, so when you print the map out you get a key
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
 * 2011-05-27:  Etherton - Initial release
 *
 * Developed by Etherton Technologies Ltd.
 */
class Printmapkey_Controller extends Controller {

	
	
	/**
	 * Function: getKey
	 *
	 * Description: This function creates a legend for the print map.
	 *
	 * @param string $catIds - A comma separated list of database catagory IDs
	 * @param string $logicalOperator - Either 'or' or 'and' determines what boolean operator is used when mutliple categories are selected
	 * @param int $startDate - The start date represented in number of seconds since 1970
	 * @param int $endDate - The end date represented in number of seconds since 1970
	 *
	 * Views: enhancedmap/printmapkey
	 *
	 * Results: Returns a legend for the print map
	 */
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
