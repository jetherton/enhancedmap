<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Reports Controller.
 * This controller will take care of adding and editing reports in the Admin section.
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     Ushahidi Team <team@ushahidi.com>
 * @package    Ushahidi - http://source.ushahididev.com
 * @module     Admin Reports Controller
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL)
 */

class Bigmap_reports_Controller extends Main_Controller
{

     var $logged_in;

    function __construct()
    {
		parent::__construct();

		$this->themes->validator_enabled = TRUE;

		// Is the Admin Logged In?

		$this->logged_in = Auth::instance()->logged_in()
			? TRUE
			: FALSE;
    }


    /**
    * Lists the reports.
    * @param int $page
    */
    function index($page = 1)
    {

		// Cacheable Controller
		$this->is_cachable = TRUE;
		
		$this->template->header->this_page = 'reports';
		$this->template->content = new View('reports');
		$this->themes->js = new View('reports_js');

		// Get locale
		$l = Kohana::config('locale.language.0');

		$db = new Database;


        if (!empty($_GET['status']))
        {
            $status = $_GET['status'];

            if (strtolower($status) == 'a')
            {
                $filter = 'incident.incident_active = 0';
            }
            elseif (strtolower($status) == 'v')
            {
                $filter = 'incident.incident_verified = 0';
            }
            else
            {
                $status = "0";
                $filter = '1=1';
            }
        }
        else
        {
            $status = "0";
            $filter = "1=1";
        }

       
        // check, has the form been submitted?
        $form_error = FALSE;
        $form_saved = FALSE;
        $form_action = "";
        
        $db = new Database;


	// Category ID
	$category_ids=array();
        if( isset($_GET['c']) AND ! empty($_GET['c']) )
	{
		$category_ids = explode(",", $_GET['c']); //get rid of that trailing ","
	}
	else
	{
		$category_ids = array("0");
	}
	
	// logical operator
	$logical_operator = "or";
        if( isset($_GET['lo']) AND ! empty($_GET['lo']) )
	{
		$logical_operator = $_GET['lo'];
	}

	$approved_text = " incident.incident_active = 1 ";
	
    // Start Date
    $start_date = (isset($_GET['s']) AND !empty($_GET['s'])) ? (int) $_GET['s'] : "0";

    // End Date
    $end_date = (isset($_GET['e']) AND !empty($_GET['e'])) ? (int) $_GET['e'] : "0";
	
	$filter .= ($start_date) ? " AND incident.incident_date >= '" . date("Y-m-d H:i:s", $start_date) . "'" : "";
    $filter .= ($end_date) ? " AND incident.incident_date <= '" . date("Y-m-d H:i:s", $end_date) . "'" : "";
	
	$location_where = "";
	// Break apart location variables, if necessary
	$southwest = array();
	if (isset($_GET['sw']))
	{
		$southwest = explode(",",$_GET['sw']);
	}

	$northeast = array();
	if (isset($_GET['ne']))
	{
		$northeast = explode(",",$_GET['ne']);
	}

	if ( count($southwest) == 2 AND count($northeast) == 2 )
	{
		$lon_min = (float) $southwest[0];
		$lon_max = (float) $northeast[0];
		$lat_min = (float) $southwest[1];
		$lat_max = (float) $northeast[1];

		$location_where = ' AND (location.latitude >='.$lat_min.' AND location.latitude <='.$lat_max.' AND location.longitude >='.$lon_min.' AND location.longitude <='.$lon_max.') ';

	}
	
	
	$reports_count = adminmap_reports::get_reports_count($category_ids, $approved_text, $location_where. " AND ". $filter, $logical_operator);

	
	// Pagination
	$pagination = new Pagination(array(
			'query_string' => 'page',
			'items_per_page' => (int) Kohana::config('settings.items_per_page'),
			'total_items' => $reports_count
			));

	$incidents = adminmap_reports::get_reports($category_ids,  $approved_text, $location_where. " AND ". $filter, $logical_operator, 
		"incident.incident_date", "asc",
		(int) Kohana::config('settings.items_per_page'), $pagination->sql_offset );
		
		
	
		//Set default as not showing pagination. Will change below if necessary.
		$this->template->content->pagination = "";

		// Pagination and Total Num of Report Stats
		if ($pagination->total_items == 1)
		{
			$plural = "";
		}
		else
		{
			$plural = "s";
		}

		if ($pagination->total_items > 0)
		{
			$current_page = ($pagination->sql_offset/ (int) Kohana::config('settings.items_per_page')) + 1;
			$total_pages = ceil($pagination->total_items/ (int) Kohana::config('settings.items_per_page'));

			if ($total_pages > 1)
			{ // If we want to show pagination
				$this->template->content->pagination_stats = Kohana::lang('ui_admin.showing_page').' '.$current_page.' '.Kohana::lang('ui_admin.of').' '.$total_pages.' '.Kohana::lang('ui_admin.pages');

				$this->template->content->pagination = $pagination;
			}
			else
			{ // If we don't want to show pagination
				$this->template->content->pagination_stats = $pagination->total_items.' '.Kohana::lang('ui_admin.reports');
			}
		}
		else
		{
			$this->template->content->pagination_stats = '('.$pagination->total_items.' report'.$plural.')';
		}


		//locations
			$location_in = array();
		foreach ($incidents as $incident)
		{
			$location_in[] = $incident->location_id;
		}

		//check if location_in is not empty
		if( count($location_in ) > 0 )
		{
			    // Get location names
			    $query = 'SELECT id, location_name FROM '.$this->table_prefix.'location WHERE id IN ('.implode(',',$location_in).')';
			    $locations_query = $db->query($query);

			    $locations = array();
			    foreach ($locations_query as $loc)
			    {
				    $locations[$loc->id] = $loc->location_name;
			    }
		}
		else
		{
		    $locations = array();
		}
		
		$this->template->content->locations = $locations;

		
		//categories
		$localized_categories = array();
		foreach ($incidents as $incident)
		{
			foreach ($incident->category AS $category)
			{
				$ct = (string)$category->category_title;
				if( ! isset($localized_categories[$ct]))
				{
					$translated_title = Category_Lang_Model::category_title($category->id,$l);
					$localized_categories[$ct] = $category->category_title;
					if($translated_title)
					{
						$localized_categories[$ct] = $translated_title;
					}
				}
			}
		}

		$this->template->content->localized_categories = $localized_categories;



	// Category Title, if Category ID available
	$category_title = "All Categories";
	if(count($category_ids) > 0 && $category_ids[0] != "0")
	{
		$category_title = "";
	}
	$count = 0;
	foreach($category_ids as $cat_id)
	{
		$category = ORM::factory('category')
			->find($cat_id);
		if($category->loaded)
		{
			$count++;
			if($count > 1)
			{
				$category_title = $category_title . " ". strtoupper($logical_operator). " ";
			}
			$category_title = $category_title . $category->category_title;
		}
	}
	$this->template->content->category_title = $category_title . ": ";



        //GET countries
        $countries = array();
        foreach (ORM::factory('country')->orderby('country')->find_all() as $country)
        {
            // Create a list of all categories
            $this_country = $country->country;
            if (strlen($this_country) > 35)
            {
                $this_country = substr($this_country, 0, 35) . "...";
            }
            $countries[$country->id] = $this_country;
        }

        $this->template->content->countries = $countries;
        $this->template->content->incidents = $incidents;
        $this->template->content->pagination = $pagination;
        $this->template->content->form_error = $form_error;
        $this->template->content->form_saved = $form_saved;
        $this->template->content->form_action = $form_action;

        // Total Reports
        $this->template->content->total_items = $pagination->total_items;

        // Status Tab
        $this->template->content->status = $status;

	$this->template->header->header_block = $this->themes->header_block();
    }//end of index()


    
    /* private functions */

    // Return thumbnail photos
    //XXX: This needs to be fixed, it's probably ok to return an empty iterable instead of "0"
    private function _get_thumbnails( $id )
    {
        $incident = ORM::factory('incident', $id);

        if ( $id )
        {
            $incident = ORM::factory('incident', $id);

            return $incident;

        }
        return "0";
    }

    private function _get_categories()
    {
        $categories = ORM::factory('category')
            ->where('category_visible', '1')
            ->where('parent_id', '0')
			->where('category_trusted != 1')
            ->orderby('category_title', 'ASC')
            ->find_all();

        return $categories;
    }

    // Dynamic categories form fields
    private function _new_categories_form_arr()
    {
        return array
        (
            'category_name' => '',
            'category_description' => '',
            'category_color' => '',
        );
    }

    // Time functions
    private function _hour_array()
    {
        for ($i=1; $i <= 12 ; $i++)
        {
            $hour_array[sprintf("%02d", $i)] = sprintf("%02d", $i);     // Add Leading Zero
        }
        return $hour_array;
    }

    private function _minute_array()
    {
        for ($j=0; $j <= 59 ; $j++)
        {
            $minute_array[sprintf("%02d", $j)] = sprintf("%02d", $j);   // Add Leading Zero
        }

        return $minute_array;
    }

    private function _ampm_array()
    {
        return $ampm_array = array('pm'=>Kohana::lang('ui_admin.pm'),'am'=>Kohana::lang('ui_admin.am'));
    }

    // Javascript functions
     private function _color_picker_js()
    {
     return "<script type=\"text/javascript\">
                $(document).ready(function() {
                $('#category_color').ColorPicker({
                        onSubmit: function(hsb, hex, rgb) {
                            $('#category_color').val(hex);
                        },
                        onChange: function(hsb, hex, rgb) {
                            $('#category_color').val(hex);
                        },
                        onBeforeShow: function () {
                            $(this).ColorPickerSetColor(this.value);
                        }
                    })
                .bind('keyup', function(){
                    $(this).ColorPickerSetColor(this.value);
                });
                });
            </script>";
    }

    private function _date_picker_js()
    {
        return "<script type=\"text/javascript\">
                $(document).ready(function() {
                $(\"#incident_date\").datepicker({
                showOn: \"both\",
                buttonImage: \"" . url::base() . "media/img/icon-calendar.gif\",
                buttonImageOnly: true
                });
                });
            </script>";
    }


    private function _new_category_toggle_js()
    {
        return "<script type=\"text/javascript\">
                $(document).ready(function() {
                $('a#category_toggle').click(function() {
                $('#category_add').toggle(400);
                return false;
                });
                });
            </script>";
    }


    /**
     * Checks if translation for this report & locale exists
     * @param Validation $post $_POST variable with validation rules
     * @param int $iid The unique incident_id of the original report
     */
    public function translate_exists_chk(Validation $post)
    {
        // If add->rules validation found any errors, get me out of here!
        if (array_key_exists('locale', $post->errors()))
            return;

        $iid = $_GET['iid'];
        if (empty($iid)) {
            $iid = 0;
        }
        $translate = ORM::factory('incident_lang')->where('incident_id',$iid)->where('locale',$post->locale)->find();
        if ($translate->loaded == true) {
            $post->add_error( 'locale', 'exists');
        // Not found
        } else {
            return;
        }
    }


    /**
     * Retrieve Custom Form Fields
     * @param bool|int $incident_id The unique incident_id of the original report
     * @param int $form_id The unique form_id. Uses default form (1), if none selected
     * @param bool $field_names_only Whether or not to include just fields names, or field names + data
     * @param bool $data_only Whether or not to include just data
     */
    private function _get_custom_form_fields($incident_id = false, $form_id = 1, $data_only = false)
    {
        $fields_array = array();

        if (!$form_id)
        {
            $form_id = 1;
        }
        $custom_form = ORM::factory('form', $form_id)->orderby('field_position','asc');
        foreach ($custom_form->form_field as $custom_formfield)
        {
            if ($data_only)
            { // Return Data Only
                $fields_array[$custom_formfield->id] = '';

                foreach ($custom_formfield->form_response as $form_response)
                {
                    if ($form_response->incident_id == $incident_id)
                    {
                        $fields_array[$custom_formfield->id] = $form_response->form_response;
                    }
                }
            }
            else
            { // Return Field Structure
                $fields_array[$custom_formfield->id] = array(
                    'field_id' => $custom_formfield->id,
                    'field_name' => $custom_formfield->field_name,
                    'field_type' => $custom_formfield->field_type,
                    'field_required' => $custom_formfield->field_required,
                    'field_maxlength' => $custom_formfield->field_maxlength,
                    'field_height' => $custom_formfield->field_height,
                    'field_width' => $custom_formfield->field_width,
                    'field_isdate' => $custom_formfield->field_isdate,
                    'field_response' => ''
                    );
            }
        }

        return $fields_array;
    }


    /**
     * Validate Custom Form Fields
     * @param array $custom_fields Array
     */
    private function _validate_custom_form_fields($custom_fields = array())
    {
        $custom_fields_error = "";

        foreach ($custom_fields as $field_id => $field_response)
        {
            // Get the parameters for this field
            $field_param = ORM::factory('form_field', $field_id);
            if ($field_param->loaded == true)
            {
                // Validate for required
                if ($field_param->field_required == 1 && $field_response == "")
                {
                    return false;
                }

                // Validate for date
                if ($field_param->field_isdate == 1 && $field_response != "")
                {
                    $myvalid = new Valid();
                    return $myvalid->date_mmddyyyy($field_response);
                }
            }
        }
        return true;
    }


    /**
     * Ajax call to update Incident Reporting Form
     */
    public function switch_form()
    {
        $this->template = "";
        $this->auto_render = FALSE;

        isset($_POST['form_id']) ? $form_id = $_POST['form_id'] : $form_id = "1";
        isset($_POST['incident_id']) ? $incident_id = $_POST['incident_id'] : $incident_id = "";

        $html = "";
        $fields_array = array();
        $custom_form = ORM::factory('form', $form_id)->orderby('field_position','asc');

        foreach ($custom_form->form_field as $custom_formfield)
        {
            $fields_array[$custom_formfield->id] = array(
                'field_id' => $custom_formfield->id,
                'field_name' => $custom_formfield->field_name,
                'field_type' => $custom_formfield->field_type,
                'field_required' => $custom_formfield->field_required,
                'field_maxlength' => $custom_formfield->field_maxlength,
                'field_height' => $custom_formfield->field_height,
                'field_width' => $custom_formfield->field_width,
                'field_isdate' => $custom_formfield->field_isdate,
                'field_response' => ''
                );

            // Load Data, if Any
            foreach ($custom_formfield->form_response as $form_response)
            {
                if ($form_response->incident_id = $incident_id)
                {
                    $fields_array[$custom_formfield->id]['field_response'] = $form_response->form_response;
                }
            }
        }

        foreach ($fields_array as $field_property)
        {
            $html .= "<div class=\"row\">";
            $html .= "<h4>" . $field_property['field_name'] . "</h4>";
            if ($field_property['field_type'] == 1)
            { // Text Field
                // Is this a date field?
                if ($field_property['field_isdate'] == 1)
                {
                    $html .= form::input('custom_field['.$field_property['field_id'].']', $field_property['field_response'],
                        ' id="custom_field_'.$field_property['field_id'].'" class="text"');
                    $html .= "<script type=\"text/javascript\">
                            $(document).ready(function() {
                            $(\"#custom_field_".$field_property['field_id']."\").datepicker({
                            showOn: \"both\",
                            buttonImage: \"" . url::base() . "media/img/icon-calendar.gif\",
                            buttonImageOnly: true
                            });
                            });
                        </script>";
                }
                else
                {
                    $html .= form::input('custom_field['.$field_property['field_id'].']', $field_property['field_response'],
                        ' id="custom_field_'.$field_property['field_id'].'" class="text custom_text"');
                }
            }
            elseif ($field_property['field_type'] == 2)
            { // TextArea Field
                $html .= form::textarea('custom_field['.$field_property['field_id'].']',
                    $field_property['field_response'], ' class="custom_text" rows="3"');
            }
            $html .= "</div>";
        }

        echo json_encode(array("status"=>"success", "response"=>$html));
    }

    /**
     * Creates a SQL string from search keywords
     */
    private function _get_searchstring($keyword_raw)
    {
        $or = '';
        $where_string = '';


        // Stop words that we won't search for
        // Add words as needed!!
        $stop_words = array('the', 'and', 'a', 'to', 'of', 'in', 'i', 'is', 'that', 'it',
        'on', 'you', 'this', 'for', 'but', 'with', 'are', 'have', 'be',
        'at', 'or', 'as', 'was', 'so', 'if', 'out', 'not');

        $keywords = explode(' ', $keyword_raw);
        
        if (is_array($keywords) && !empty($keywords))
        {
            array_change_key_case($keywords, CASE_LOWER);
            $i = 0;
            
            foreach($keywords as $value)
            {
                if (!in_array($value,$stop_words) && !empty($value))
                {
                    $chunk = mysql_real_escape_string($value);
                    if ($i > 0) {
                        $or = ' OR ';
                    }
                    $where_string = $where_string.$or."incident_title LIKE '%$chunk%' OR incident_description LIKE '%$chunk%'  OR location_name LIKE '%$chunk%'";
                    $i++;
                }
            }
        }

        if ($where_string)
        {
            return $where_string;
        }
        else
        {
            return "1=1";
        }
    }

    private function _csv_text($text)
    {
        $text = stripslashes(htmlspecialchars($text));
        return $text;
    }
}
