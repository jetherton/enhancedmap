<?php defined('SYSPATH') or die('No direct script access.');
/**
 * This is the controller for the main site.
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     Ushahidi Team <team@ushahidi.com>
 * @package    Ushahidi - http://source.ushahididev.com
 * @module     Main Controller
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL)
 */
class Printmapkey_Controller extends Controller {

	function getKey($catIds, $logicalOperator, $startDate, $endDate)
	{
		//Format the dates
		$keyStartDate = date("M j, Y", $startDate);
		$keyEndDate = date("M j, Y", $endDate);
		

		//handle the logic operator
		if($logicalOperator == "or")
		{
			$logicStr = "All reports on this map fall under one or more of the following categories.";		
		}
		else
		{
			$logicStr = "All reports on this map fall under all of the following categories. ";
		}
		
		
		
		//do the categories
		//check if we're dealing with all categories
		$categories = array();
		if($catIds == "" || $catIds == "0" || $catIds == "0," || $catIds == "undefined")
		{
			$cat = ORM::factory("category");
			$cat->category_title = Kohana::lang('ui_main.all_categories');
			$cat->category_color = Kohana::config('settings.default_map_all');
			$categories = array($cat); 
		}
		else 
		{
			$catIds = explode(",", $catIds, -1);
			$whereStr = "";
			$i = 0;
			foreach($catIds as $catId)
			{
				$i++;
				if($i > 1)
				{
					$whereStr .= " || ";
				}
				$whereStr .= "id = $catId";
			}
			
			$categories = ORM::factory("category")
				->where($whereStr)
				->find_all();
		}
		
		
		
		
		$view = View::factory("adminmap/printmapkey");
		$view->logic = $logicStr;
		$view->keyStartDate = $keyStartDate;
		$view->keyEndDate = $keyEndDate;
		$view->categories = $categories;
		
		$view->render(true);
		
	}

} // End Main
