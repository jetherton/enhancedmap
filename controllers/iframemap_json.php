<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Json Controller for the iFrame map since the iFrame map needs to modify the target param
 * 
 * This file is adapted from the file Ushahidi_Web/appliction/controllers/json.php
 * Originally written by the Ushahidi Team
 *
 * @author     John Etherton <john@ethertontech.com>
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
 */

class Iframemap_json_Controller extends Template_Controller
{
    public $auto_render = TRUE;

    // Main template
    public $template = 'bigmap_json';

    // Table Prefix
    protected $table_prefix;

    public function __construct()
    {
        parent::__construct();
	
	
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
		enhancedmap_helper::json_index($this, false,  "_blank");
    }

    /***************************************************************************************************************
     * Generate JSON in CLUSTER mode
     */
    public function cluster()
    {
        enhancedmap_helper::json_cluster($this, false, "_blank");      
    }


     /**************************************************************
     * Retrieve timeline JSON
     */
    public function timeline( $category_ids = "0," )
    {
		enhancedmap_helper::json_timeline($this, $category_ids, false);
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

                        $pixels = sqrt(pow($marker['longitude']-$target['longitude'],2) + 
                            pow($marker['latitude']-$target['latitude'],2));
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
                    $json_item .= "\"link\":\"http://$sharing_url/reports/index/?c=0&sw=$southwest&ne=$northeast\",";
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
                    $json_item .="\"link\":\"http://$sharing_url/reports/view/{$single["id"]}\",";
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
                    $json_item .= "\"link\":\"http://$sharing_url/reports/view/{$marker->incident_id}\",";

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