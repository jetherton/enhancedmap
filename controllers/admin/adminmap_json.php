<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Json Controller
 * Generates Map GeoJSON File
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     Ushahidi Team <team@ushahidi.com>
 * @package    Ushahidi - http://source.ushahididev.com
 * @module     JSON Controller
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL)
 */

class Adminmap_json_Controller extends Admin_Controller
{
    public $auto_render = TRUE;

    // Main template
    public $template = 'json';

    // Table Prefix
    protected $table_prefix;

    public function __construct()
    {
        parent::__construct();
	
	
	// If this is not a super-user account, redirect to dashboard
	if(!$this->auth->logged_in('admin') && !$this->auth->logged_in('superadmin'))
	{
		url::redirect('admin/dashboard');
	}

        // Set Table Prefix
        $this->table_prefix = Kohana::config('database.default.table_prefix');

		// Cacheable JSON Controller
		$this->is_cachable = TRUE;
    }


    /**
     * Generate JSON in NON-CLUSTER mode
     */
    function index()
    {
        $json = "";
        $json_item = "";
        $json_array = array();
        $cat_array = array();
        $color = Kohana::config('settings.default_map_all');
        $icon = "";

        $category_ids = array();
        $incident_id = "";
        $neighboring = "";
        $media_type = "";
	$show_unapproved="3"; //1 show only approved, 2 show only unapproved, 3 show all
	$logical_operator = "or";

        if( isset($_GET['c']) AND ! empty($_GET['c']) )
	{
		$category_ids = explode(",", $_GET['c'],-1); //get rid of that trailing ";"
	}
	else
	{
		$category_ids = array("0");
	}
	
	
	//figure out if we're showing unapproved stuff or what.
        if (isset($_GET['u']) AND !empty($_GET['u']))
        {
            $show_unapproved = (int) $_GET['u'];
        }
	$approved_text = "";
	if($show_unapproved == 1)
	{
		$approved_text = "incident.incident_active = 1 ";
	}
	else if ($show_unapproved == 2)
	{
		$approved_text = "incident.incident_active = 0 ";
	}
	else if ($show_unapproved == 3)
	{
		$approved_text = " (incident.incident_active = 0 OR incident.incident_active = 1) ";
	}

	
	
	//should we color unapproved reports a different color?
	$color_unapproved = 2;
        if (isset($_GET['uc']) AND !empty($_GET['uc']))
        {
	    $color_unapproved = (int) $_GET['uc'];
        }
	
	if (isset($_GET['lo']) AND !empty($_GET['lo']))
        {
	    $logical_operator =  $_GET['lo'];
        }
	
	
	
	

        if (isset($_GET['i']) AND !empty($_GET['i']))
        {
            $incident_id = (int) $_GET['i'];
        }

        if (isset($_GET['n']) AND !empty($_GET['n']))
        {
            $neighboring = (int) $_GET['n'];
        }

        $where_text = '';
        // Do we have a media id to filter by?
        if (isset($_GET['m']) AND !empty($_GET['m']) AND $_GET['m'] != '0')
        {
            $media_type = (int) $_GET['m'];
            $where_text .= " AND ".$this->table_prefix."media.media_type = " . $media_type;
        }

        if (isset($_GET['s']) AND !empty($_GET['s']))
        {
            $start_date = (int) $_GET['s'];
            $where_text .= " AND UNIX_TIMESTAMP(".$this->table_prefix."incident.incident_date) >= '" . $start_date . "'";
        }

        if (isset($_GET['e']) AND !empty($_GET['e']))
        {
            $end_date = (int) $_GET['e'];
            $where_text .= " AND UNIX_TIMESTAMP(".$this->table_prefix."incident.incident_date) <= '" . $end_date . "'";
        }

        
	//get our new custom color based on the categories we're working with
	$color = $this->_merge_colors($category_ids);

	$incidents = $this->_get_incidents($category_ids, $approved_text, $where_text, $logical_operator);
	    
        $json_item_first = "";  // Variable to store individual item for report detail page
        foreach ($incidents as $marker)
        {
            $json_item = "{";
            $json_item .= "\"type\":\"Feature\",";
            $json_item .= "\"properties\": {";
            $json_item .= "\"id\": \"".$marker->id."\", \n";
            $json_item .= "\"name\":\"" . str_replace(chr(10), ' ', str_replace(chr(13), ' ', "<a href='" . url::base() . "admin/reports/edit/" . $marker->id . "'>" . htmlentities($marker->incident_title) . "</a>")) . "\",";

            if (isset($category)) {
                $json_item .= "\"category\":[" . $category_id . "], ";
            } else {
                $json_item .= "\"category\":[0], ";
            }
	    
	    //check if it's a unapproved/unactive report
	    if($marker->incident_active == 0 && $color_unapproved==2)
	    {
	        $json_item .= "\"color\": \"000000\", \n";
		$json_item .= "\"icon\": \"".$icon."\", \n";
	    }
	    else
	    {
		$json_item .= "\"color\": \"".$color."\", \n";
		$json_item .= "\"icon\": \"".$icon."\", \n";
	    }

            $json_item .= "\"timestamp\": \"" . strtotime($marker->incident_date) . "\"";
            $json_item .= "},";
            $json_item .= "\"geometry\": {";
            $json_item .= "\"type\":\"Point\", ";
            $json_item .= "\"coordinates\":[" . $marker->location->longitude . ", " . $marker->location->latitude . "]";
            $json_item .= "}";
            $json_item .= "}";

            if ($marker->id == $incident_id)
            {
                $json_item_first = $json_item;
            }
            else
            {
                array_push($json_array, $json_item);
            }
            $cat_array = array();
        }
        if ($json_item_first)
        { // Push individual marker in last so that it is layered on top when pulled into map
            array_push($json_array, $json_item_first);
        }
        $json = implode(",", $json_array);

        header('Content-type: application/json');
        $this->template->json = $json;
    }

	/************************************************************************************************
	* Function, this'll merge colors. Given an array of category IDs it'll return a hex string
	* of all the colors merged together
	*/
	private function _merge_colors($category_ids)
	{
		//check if we're looking at category 0
		if(count($category_ids) == 0 || $category_ids[0] == '0')
		{
			return Kohana::config('settings.default_map_all');
		}
		//first lets figure out the composite color that we're gonna usehere
		$where_str_color = ""; //to get the colors we're gonna use
		$i = 0;
		foreach($category_ids as $id)
		{
			$i++;
			if($i > 1)
			{
				$where_str_color = $where_str_color . " OR ";
			}
			$where_str_color = $where_str_color . "id = ".$id;
		}


		// Retrieve all the categories with their colors
		$categories = ORM::factory('category')
		    ->where($where_str_color)
		    ->find_all();

		//now for each color break it into RGB, add them up, then normalize
		$red = 0;
		$green = 0;
		$blue = 0;
		foreach($categories as $category)
		{
			$color = $category->category_color;
			$numeric_colors = $this->_hex2RGB($color);
			$red = $red + $numeric_colors['red'];
			$green = $green + $numeric_colors['green'];
			$blue = $blue + $numeric_colors['blue'];
		}
		//now normalize
		$color_length = sqrt( ($red*$red) + ($green*$green) + ($blue*$blue));
	
		$red = ($red / $color_length) * 255;
		$green = ($green / $color_length) * 255;
		$blue = ($blue / $color_length) * 255;
	
		
		//pad with zeros if there's too much space
		$red = dechex($red);
		if(strlen($red) < 2)
		{
			$red = "0".$red;
		}
		$green = dechex($green);
		if(strlen($green) < 2)
		{
			$green = "0".$green;
		}
		$blue = dechex($blue);
		if(strlen($blue) < 2)
		{
			$blue = "0".$blue;
		}
		//now put the color back together and return it
		return $red.$green.$blue;
		
	}



     /**************************************************************************************************************
      * Given all the parameters returns a list of incidents that meet the search criteria
      */
	private function _get_incidents($category_ids, $approved_text, $where_text, $logical_operator)
	{
	
		//check if we're showing all categories, or if no category info was selected then return everything
		If(count($category_ids) == 0 || $category_ids[0] == '0')
		{
			// Retrieve all markers
			$incidents = ORM::factory('incident')
			    ->select('DISTINCT incident.*')
			    ->with('location')
			    ->join('media', 'incident.id', 'media.incident_id','LEFT')
			    ->where($approved_text.$where_text)
			    ->find_all();
			    
			return $incidents;
		}
		
		// or up allthe categories we're interested in
		$where_category = "";
		$i = 0;
		foreach($category_ids as $id)
		{
			$i++;
			$where_category = ($i > 1) ? $where_category . " OR " : $where_category;
			$where_category = $where_category . $this->table_prefix.'incident_category.category_id = ' . $id;
		}

		
		//if we're using OR
		if($logical_operator == "or")
		{
		
			// Retrieve incidents by category			
			$incidents = ORM::factory('incident')
				->select('DISTINCT incident.*')
				->with('location')
				->join('incident_category', 'incident.id', 'incident_category.incident_id','LEFT')
				->join('media', 'incident.id', 'media.incident_id','LEFT')
				->where($approved_text.' AND ('.$where_category. ')' . $where_text)
				->find_all();
				
			return $incidents;
		}
		else //if we're using AND
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
				->find_all();
				
			return $incidents;
		}

	}//end method
    
    /***************************************************************************************************************
     * Generate JSON in CLUSTER mode
     */
    public function cluster()
    {
        //$profiler = new Profiler;

        // Database
        $db = new Database();

        $json = "";
        $json_item = "";
        $json_array = array();

        $color = Kohana::config('settings.default_map_all');
        $icon = "";
	$logical_operator = "or";
	
	$show_unapproved="3"; //1 show only approved, 2 show only unapproved, 3 show all
	//figure out if we're showing unapproved stuff or what.
        if (isset($_GET['u']) AND !empty($_GET['u']))
        {
            $show_unapproved = (int) $_GET['u'];
        }
	$approved_text = "";
	if($show_unapproved == 1)
	{
		$approved_text = "incident.incident_active = 1 ";
	}
	else if ($show_unapproved == 2)
	{
		$approved_text = "incident.incident_active = 0 ";
	}
	else if ($show_unapproved == 3)
	{
		$approved_text = " (incident.incident_active = 0 OR incident.incident_active = 1) ";
	}	
	
	
	//should we color unapproved reports a different color?
	$color_unapproved = 2;
        if (isset($_GET['uc']) AND !empty($_GET['uc']))
        {
	    $color_unapproved = (int) $_GET['uc'];
        }
	

	if (isset($_GET['lo']) AND !empty($_GET['lo']))
        {
	    $logical_operator =  $_GET['lo'];
        }


        // Get Zoom Level
        $zoomLevel = (isset($_GET['z']) AND !empty($_GET['z'])) ?
            (int) $_GET['z'] : 8;

        //$distance = 60;
        $distance = (10000000 >> $zoomLevel) / 100000;

        // Category ID
	$category_ids=array();
        if( isset($_GET['c']) AND ! empty($_GET['c']) )
	{
		$category_ids = explode(",", $_GET['c'],-1); //get rid of that trailing ";"
	}
	else
	{
		$category_ids = array("0");
	}

        // Start Date
        $start_date = (isset($_GET['s']) AND !empty($_GET['s'])) ?
            (int) $_GET['s'] : "0";

        // End Date
        $end_date = (isset($_GET['e']) AND !empty($_GET['e'])) ?
            (int) $_GET['e'] : "0";

        // SouthWest Bound
        $southwest = (isset($_GET['sw']) AND !empty($_GET['sw'])) ?
            $_GET['sw'] : "0";

        $northeast = (isset($_GET['ne']) AND !empty($_GET['ne'])) ?
            $_GET['ne'] : "0";

        $filter = "";
        $filter .= ($start_date) ?
            " AND incident.incident_date >= '" . date("Y-m-d H:i:s", $start_date) . "'" : "";
        $filter .= ($end_date) ?
            " AND incident.incident_date <= '" . date("Y-m-d H:i:s", $end_date) . "'" : "";

        if ($southwest AND $northeast)
        {
            list($latitude_min, $longitude_min) = explode(',', $southwest);
            list($latitude_max, $longitude_max) = explode(',', $northeast);

            $filter .= " AND location.latitude >=".(float) $latitude_min.
                " AND location.latitude <=".(float) $latitude_max;
            $filter .= " AND location.longitude >=".(float) $longitude_min.
                " AND location.longitude <=".(float) $longitude_max;
        }

	//stuff john just added
	$color = $this->_merge_colors($category_ids);
	$incidents = $this->_get_incidents($category_ids, $approved_text, $filter, $logical_operator);
        
	
	// Create markers by marrying the locations and incidents
        $markers = array();
	foreach($incidents as $incident)
	{
		array_push($markers, $incident);
	}

        $clusters = array();    // Clustered
        $singles = array();     // Non Clustered

        // Loop until all markers have been compared
        while (count($markers))
        {
            $marker  = array_pop($markers);
            $cluster = array();
	    $contains_nonactive = false;
            // Compare marker against all remaining markers.
            foreach ($markers as $key => $target)
            {
                // This function returns the distance between two markers, at a defined zoom level.
                // $pixels = $this->_pixelDistance($marker['latitude'], $marker['longitude'],
                // $target['latitude'], $target['longitude'], $zoomLevel);
		
                //$pixels = abs($marker['longitude']-$target['longitude']) + abs($marker['latitude']-$target['latitude']);
		$pixels = abs($marker->location->longitude - $target->location->longitude) + abs($marker->location->latitude - $target->location->latitude);
                // echo $pixels."<BR>";
                // If two markers are closer than defined distance, remove compareMarker from array and add to cluster.
                if ($pixels < $distance)
                {
                    unset($markers[$key]);
                    $cluster[] = $target;
		    //check if this is a unapproved report
		    if($target->incident_active == 0)
		    {
			$contains_nonactive = true;
		    }
                }
            }

            // If a marker was added to cluster, also add the marker we were comparing to.
            if (count($cluster) > 0)
            {
                $cluster[] = $marker;
                //$clusters[] = $cluster;
		//check if this is a unapproved report
		    if($marker->incident_active == 0)
		    {
			$contains_nonactive = true;
		    }
		    
		if($contains_nonactive)
		{
			$clusters[] = array( 'contains_nonactive' => TRUE, 'cluster'=> $cluster);
		}
		else
		{
			$clusters[] = array( 'contains_nonactive' => FALSE, 'cluster'=> $cluster);
		}
            }
            else
            {
                $singles[] = $marker;
            }
        }

	$i = 0;
        // Create Json
        foreach ($clusters as $cluster_alpha)
        {
	    $cluster = $cluster_alpha['cluster'];
	    $contains_nonactive = $cluster_alpha['contains_nonactive'];
            // Calculate cluster center
            $bounds = $this->_calculateCenter($cluster);
            $cluster_center = $bounds['center'];
            $southwest = $bounds['sw'];
            $northeast = $bounds['ne'];

            // Number of Items in Cluster
            $cluster_count = count($cluster);

            $json_item = "{";
            $json_item .= "\"type\":\"Feature\",";
            $json_item .= "\"properties\": {";
	    $categories_str = implode(",", $category_ids);
            $json_item .= "\"name\":\"" . str_replace(chr(10), ' ', str_replace(chr(13), ' ', "<a href=" . url::base() . "admin/adminmap_reports/index/?c=".$categories_str."&sw=".$southwest."&ne=".$northeast.">" . $cluster_count . " Reports</a>")) . "\",";
            $json_item .= "\"category\":[0], ";
	    if($contains_nonactive && $color_unapproved==2)
	    {
	        $json_item .= "\"color\": \"000000\", \n";
		$json_item .= "\"icon\": \"".$icon."\", \n";
	    }
	    else
	    {
		$json_item .= "\"color\": \"".$color."\", \n";
		$json_item .= "\"icon\": \"".$icon."\", \n";
	    }            
            $json_item .= "\"timestamp\": \"0\", ";
            $json_item .= "\"count\": \"" . $cluster_count . "\"";
            $json_item .= "},";
            $json_item .= "\"geometry\": {";
            $json_item .= "\"type\":\"Point\", ";
            $json_item .= "\"coordinates\":[" . $cluster_center . "]";
            $json_item .= "}";
            $json_item .= "}";

            array_push($json_array, $json_item);
        }

        foreach ($singles as $single)
        {
            $json_item = "{";
            $json_item .= "\"type\":\"Feature\",";
            $json_item .= "\"properties\": {";
            $json_item .= "\"name\":\"" . str_replace(chr(10), ' ', str_replace(chr(13), ' ', "<a href=" . url::base() . "admin/reports/edit/" . $single->id . "/>".str_replace('"','\"',$single->incident_title)."</a>")) . "\",";   
            $json_item .= "\"category\":[0], ";
	    //check if it's a unapproved/unactive report
	    if($single->incident_active == 0 && $color_unapproved==2)
	    {
	        $json_item .= "\"color\": \"000000\", \n";
		$json_item .= "\"icon\": \"".$icon."\", \n";
	    }
	    else
	    {
		$json_item .= "\"color\": \"".$color."\", \n";
		$json_item .= "\"icon\": \"".$icon."\", \n";
	    }            
	    $json_item .= "\"timestamp\": \"0\", ";
            $json_item .= "\"count\": \"" . 1 . "\"";
            $json_item .= "},";
            $json_item .= "\"geometry\": {";
            $json_item .= "\"type\":\"Point\", ";
            $json_item .= "\"coordinates\":[" . $single->location->longitude . ", " . $single->location->latitude . "]";
            $json_item .= "}";
            $json_item .= "}";

            array_push($json_array, $json_item);
        }

        $json = implode(",", $json_array);

        header('Content-type: application/json');
        $this->template->json = $json;

    }


     /**************************************************************
     * Retrieve timeline JSON
     */
    public function timeline( $category_ids = "0," )
    {
	$category_ids = explode(",", $category_ids,-1); //get rid of that trailing ","

        $this->auto_render = FALSE;
        $db = new Database();
	
	
	
	$show_unapproved="3"; //1 show only approved, 2 show only unapproved, 3 show all
	//figure out if we're showing unapproved stuff or what.
        if (isset($_GET['u']) AND !empty($_GET['u']))
        {
            $show_unapproved = (int) $_GET['u'];
        }
	$approved_text = "";
	if($show_unapproved == 1)
	{
		$approved_text = "incident.incident_active = 1 ";
	}
	else if ($show_unapproved == 2)
	{
		$approved_text = "incident.incident_active = 0 ";
	}
	else if ($show_unapproved == 3)
	{
		$approved_text = " (incident.incident_active = 0 OR incident.incident_active = 1) ";
	}
	
	$logical_operator = "or";
	if (isset($_GET['lo']) AND !empty($_GET['lo']))
        {
	    $logical_operator =  $_GET['lo'];
        }


        $interval = (isset($_GET["i"]) AND !empty($_GET["i"])) ?
            $_GET["i"] : "month";


        // Get the Counts
        $select_date_text = "DATE_FORMAT(incident_date, '%Y-%m-01')";
        $groupby_date_text = "DATE_FORMAT(incident_date, '%Y%m')";
        if ($interval == 'day')
        {
            $select_date_text = "DATE_FORMAT(incident_date, '%Y-%m-%d')";
            $groupby_date_text = "DATE_FORMAT(incident_date, '%Y%m%d')";
        }
        elseif ($interval == 'hour')
        {
            $select_date_text = "DATE_FORMAT(incident_date, '%Y-%m-%d %H:%M')";
            $groupby_date_text = "DATE_FORMAT(incident_date, '%Y%m%d%H')";
        }
        elseif ($interval == 'week')
        {
            $select_date_text = "STR_TO_DATE(CONCAT(CAST(YEARWEEK(incident_date) AS CHAR), ' Sunday'), '%X%V %W')";
            $groupby_date_text = "YEARWEEK(incident_date)";
        }

        $graph_data = array();
        $graph_data[0] = array();
        $graph_data[0]['label'] = "Category Title"; //is this used for anything?
        $graph_data[0]['color'] = '#'. $this->_merge_colors($category_ids);
        $graph_data[0]['data'] = array();
	
	$incidents = $this->_get_incidents($category_ids, $approved_text, "", $logical_operator);
	
	$approved_IDs_str = "('-1')";
	if(count($incidents) > 0)
	{
		$i = 0;
		$approved_IDs_str = "(";
		foreach($incidents as $incident)
		{
			$i++;
			$approved_IDs_str = ($i > 1) ? $approved_IDs_str.', ' : $approved_IDs_str;
			$approved_IDs_str = $approved_IDs_str."'".$incident->id."'";
		}
		$approved_IDs_str = $approved_IDs_str.") ";
	}

        $query = 'SELECT UNIX_TIMESTAMP('.$select_date_text.') AS time, COUNT(id) AS number FROM '.$this->table_prefix.'incident WHERE incident.id in'.$approved_IDs_str.' GROUP BY '.$groupby_date_text;
	//echo $query;
	$query = $db->query($query);

        foreach ( $query as $items )
        {
            array_push($graph_data[0]['data'],
                array($items->time * 1000, $items->number));
        }

        echo json_encode($graph_data);
    }


    /**
     * Read in new layer KML via file_get_contents
     * @param int $layer_id - ID of the new KML Layer
     */
    public function layer($layer_id = 0)
    {
        $this->template = "";
        $this->auto_render = FALSE;

        $layer = ORM::factory('layer')
            ->where('layer_visible', 1)
            ->find($layer_id);

        if ($layer->loaded)
        {
            $layer_url = $layer->layer_url;
            $layer_file = $layer->layer_file;

            $layer_link = (!$layer_url) ?
                url::base().Kohana::config('upload.relative_directory').'/'.$layer_file :
                $layer_url;

            $content = file_get_contents($layer_link);

            if ($content !== false)
            {
                echo $content;
            }
            else
            {
                echo "";
            }
        }
        else
        {
            echo "";
        }
    }


    /**
     * Read in new layer JSON from shared connection
     * @param int $sharing_id - ID of the new Share Layer
     */
    public function share( $sharing_id = false )
    {   
        $json = "";
        $json_item = "";
        $json_array = array();
        $sharing_data = "";
        $clustering = Kohana::config('settings.allow_clustering');
        
        if ($sharing_id)
        {
            // Get This Sharing ID Color
            $sharing = ORM::factory('sharing')
                ->find($sharing_id);
            
            if( ! $sharing->loaded )
                return;
            
            $sharing_url = $sharing->sharing_url;
            $sharing_color = $sharing->sharing_color;
            
            if ($clustering)
            {
                // Database
                $db = new Database();
                
                // Start Date
                $start_date = (isset($_GET['s']) && !empty($_GET['s'])) ?
                    (int) $_GET['s'] : "0";

                // End Date
                $end_date = (isset($_GET['e']) && !empty($_GET['e'])) ?
                    (int) $_GET['e'] : "0";

                // SouthWest Bound
                $southwest = (isset($_GET['sw']) && !empty($_GET['sw'])) ?
                    $_GET['sw'] : "0";

                $northeast = (isset($_GET['ne']) && !empty($_GET['ne'])) ?
                    $_GET['ne'] : "0";
                
                // Get Zoom Level
                $zoomLevel = (isset($_GET['z']) && !empty($_GET['z'])) ?
                    (int) $_GET['z'] : 8;

                //$distance = 60;
                $distance = (10000000 >> $zoomLevel) / 100000;
                
                $filter = "";
                $filter .= ($start_date) ? 
                    " AND incident_date >= '" . date("Y-m-d H:i:s", $start_date) . "'" : "";
                $filter .= ($end_date) ? 
                    " AND incident_date <= '" . date("Y-m-d H:i:s", $end_date) . "'" : "";

                if ($southwest && $northeast)
                {
                    list($latitude_min, $longitude_min) = explode(',', $southwest);
                    list($latitude_max, $longitude_max) = explode(',', $northeast);

                    $filter .= " AND latitude >=".(float) $latitude_min.
                        " AND latitude <=".(float) $latitude_max;
                    $filter .= " AND longitude >=".(float) $longitude_min.
                        " AND longitude <=".(float) $longitude_max;
                }
                
                $query = $db->query("SELECT * FROM `".$this->table_prefix."sharing_incident` WHERE 1=1 $filter ORDER BY incident_id ASC "); 

                $markers = $query->result_array(FALSE);

                $clusters = array();    // Clustered
                $singles = array();     // Non Clustered

                // Loop until all markers have been compared
                while (count($markers))
                {
                    $marker  = array_pop($markers);
                    $cluster = array();

                    // Compare marker against all remaining markers.
                    foreach ($markers as $key => $target)
                    {
                        // This function returns the distance between two markers, at a defined zoom level.
                        // $pixels = $this->_pixelDistance($marker['latitude'], $marker['longitude'], 
                        // $target['latitude'], $target['longitude'], $zoomLevel);

                        $pixels = abs($marker['longitude']-$target['longitude']) + 
                            abs($marker['latitude']-$target['latitude']);
                        // echo $pixels."<BR>";
                        // If two markers are closer than defined distance, remove compareMarker from array and add to cluster.
                        if ($pixels < $distance)
                        {
                            unset($markers[$key]);
                            $target['distance'] = $pixels;
                            $cluster[] = $target;
                        }
                    }

                    // If a marker was added to cluster, also add the marker we were comparing to.
                    if (count($cluster) > 0)
                    {
                        $cluster[] = $marker;
                        $clusters[] = $cluster;
                    }
                    else
                    {
                        $singles[] = $marker;
                    }
                }

                // Create Json
                foreach ($clusters as $cluster)
                {
                    // Calculate cluster center
                    $bounds = $this->_calculateCenter($cluster);
                    $cluster_center = $bounds['center'];
                    $southwest = $bounds['sw'];
                    $northeast = $bounds['ne'];

                    // Number of Items in Cluster
                    $cluster_count = count($cluster);

                    $json_item = "{";
                    $json_item .= "\"type\":\"Feature\",";
                    $json_item .= "\"properties\": {";
                    $json_item .= "\"name\":\"" . str_replace(chr(10), ' ', str_replace(chr(13), ' ', "<a href='http://" . $sharing_url . "/reports/index/?c=0&sw=".$southwest."&ne=".$northeast."'>" . $cluster_count . " Reports</a>")) . "\",";          
                    $json_item .= "\"category\":[0], ";
                    $json_item .= "\"icon\": \"\", ";
                    $json_item .= "\"color\": \"".$sharing_color."\", ";
                    $json_item .= "\"timestamp\": \"0\", ";
                    $json_item .= "\"count\": \"" . $cluster_count . "\"";
                    $json_item .= "},";
                    $json_item .= "\"geometry\": {";
                    $json_item .= "\"type\":\"Point\", ";
                    $json_item .= "\"coordinates\":[" . $cluster_center . "]";
                    $json_item .= "}";
                    $json_item .= "}";

                    array_push($json_array, $json_item);
                }

                foreach ($singles as $single)
                {
                    $json_item = "{";
                    $json_item .= "\"type\":\"Feature\",";
                    $json_item .= "\"properties\": {";
                    $json_item .= "\"name\":\"" . str_replace(chr(10), ' ', str_replace(chr(13), ' ', "<a href='http://" . $sharing_url . "/reports/view/" . $single['id'] . "'>".$single['incident_title']."</a>")) . "\",";   
                    $json_item .= "\"category\":[0], ";
                    $json_item .= "\"icon\": \"\", ";
                    $json_item .= "\"color\": \"".$sharing_color."\", ";
                    $json_item .= "\"timestamp\": \"0\", ";
                    $json_item .= "\"count\": \"" . 1 . "\"";
                    $json_item .= "},";
                    $json_item .= "\"geometry\": {";
                    $json_item .= "\"type\":\"Point\", ";
                    $json_item .= "\"coordinates\":[" . $single['longitude'] . ", " . $single['latitude'] . "]";
                    $json_item .= "}";
                    $json_item .= "}";

                    array_push($json_array, $json_item);
                }

                $json = implode(",", $json_array);
                
            }
            else
            {
                // Retrieve all markers
                $markers = ORM::factory('sharing_incident')
                                        ->where('sharing_id', $sharing_id)
                                        ->find_all();

                foreach ($markers as $marker)
                {   
                    $json_item = "{";
                    $json_item .= "\"type\":\"Feature\",";
                    $json_item .= "\"properties\": {";
                    $json_item .= "\"id\": \"".$marker->incident_id."\", \n";
                    $json_item .= "\"name\":\"" . str_replace(chr(10), ' ', str_replace(chr(13), ' ', "<a href='http://" . $sharing_url . "/reports/view/" . $marker->incident_id . "'>" . htmlentities($marker->incident_title) . "</a>")) . "\",";

                    $json_item .= "\"icon\": \"\", ";
                    $json_item .= "\"color\": \"".$sharing_color ."\", \n";
                    $json_item .= "\"timestamp\": \"" . strtotime($marker->incident_date) . "\"";
                    $json_item .= "},";
                    $json_item .= "\"geometry\": {";
                    $json_item .= "\"type\":\"Point\", ";
                    $json_item .= "\"coordinates\":[" . $marker->longitude . ", " . $marker->latitude . "]";
                    $json_item .= "}";
                    $json_item .= "}";

                    array_push($json_array, $json_item);
                }

                $json = implode(",", $json_array);
            }
        }
        
        header('Content-type: application/json');
        $this->template->json = $json;
    }


    /**
     * Convert Longitude to Cartesian (Pixels) value
     * @param double $lon - Longitude
     * @return int
     */
    private function _lonToX($lon)
    {
        return round(OFFSET + RADIUS * $lon * pi() / 180);
    }

    /**
     * Convert Latitude to Cartesian (Pixels) value
     * @param double $lat - Latitude
     * @return int
     */
    private function _latToY($lat)
    {
        return round(OFFSET - RADIUS *
                    log((1 + sin($lat * pi() / 180)) /
                    (1 - sin($lat * pi() / 180))) / 2);
    }

    /**
     * Calculate distance using Cartesian (pixel) coordinates
     * @param int $lat1 - Latitude for point 1
     * @param int $lon1 - Longitude for point 1
     * @param int $lon2 - Latitude for point 2
     * @param int $lon2 - Longitude for point 2
     * @return int
     */
    private function _pixelDistance($lat1, $lon1, $lat2, $lon2, $zoom)
    {
        $x1 = $this->_lonToX($lon1);
        $y1 = $this->_latToY($lat1);

        $x2 = $this->_lonToX($lon2);
        $y2 = $this->_latToY($lat2);

        return sqrt(pow(($x1-$x2),2) + pow(($y1-$y2),2)) >> (21 - $zoom);
    }

    /**
     * Calculate the center of a cluster of markers
     * @param array $cluster
     * @return array - (center, southwest bound, northeast bound)
     */
    private function _calculateCenter($cluster)
    {
        // Calculate average lat and lon of clustered items
        $south = 0;
        $west = 0;
        $north = 0;
        $east = 0;

        $lat_sum = $lon_sum = 0;
        foreach ($cluster as $marker)
        {
            if (!$south)
            {
                $south = $marker->location->latitude;
            }
            elseif ($marker->location->latitude < $south)
            {
                $south = $marker->location->latitude;
            }

            if (!$west)
            {
                $west = $marker->location->longitude;
            }
            elseif ($marker->location->longitude < $west)
            {
                $west = $marker->location->longitude;
            }

            if (!$north)
            {
                $north = $marker->location->latitude;
            }
            elseif ($marker->location->latitude > $north)
            {
                $north = $marker->location->latitude;
            }

            if (!$east)
            {
                $east = $marker->location->longitude;
            }
            elseif ($marker->location->longitude > $east)
            {
                $east = $marker->location->longitude;
            }

            $lat_sum += $marker->location->latitude;
            $lon_sum += $marker->location->longitude;
        }
        $lat_avg = $lat_sum / count($cluster);
        $lon_avg = $lon_sum / count($cluster);

        $center = $lon_avg.",".$lat_avg;
        $sw = $west.",".$south;
        $ne = $east.",".$north;

        return array(
            "center"=>$center,
            "sw"=>$sw,
            "ne"=>$ne
        );
    }//end function
    
    
	private function _hex2RGB($hexStr, $returnAsString = false, $seperator = ',') 
	{
		$hexStr = preg_replace("/[^0-9A-Fa-f]/", '', $hexStr); // Gets a proper hex string
		$rgbArray = array();
		if (strlen($hexStr) == 6) 
		{ //If a proper hex code, convert using bitwise operation. No overhead... faster
			$colorVal = hexdec($hexStr);
			$rgbArray['red'] = 0xFF & ($colorVal >> 0x10);
			$rgbArray['green'] = 0xFF & ($colorVal >> 0x8);
			$rgbArray['blue'] = 0xFF & $colorVal;
		} 
		elseif (strlen($hexStr) == 3) 
		{ //if shorthand notation, need some string manipulations
			$rgbArray['red'] = hexdec(str_repeat(substr($hexStr, 0, 1), 2));
			$rgbArray['green'] = hexdec(str_repeat(substr($hexStr, 1, 1), 2));
			$rgbArray['blue'] = hexdec(str_repeat(substr($hexStr, 2, 1), 2));
		} 
		else 
		{
			return false; //Invalid hex color code
		}
		return $returnAsString ? implode($seperator, $rgbArray) : $rgbArray; // returns the rgb string or the associative array
	}
    
    
    
}