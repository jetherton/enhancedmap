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

class Adminmap_reports_Controller extends Admin_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->template->this_page = 'reports';
    }


    /**
    * Lists the reports.
    * @param int $page
    */
    function index($page = 1)
    {
        // If user doesn't have access, redirect to dashboard
        if ( ! admin::permissions($this->user, "reports_view"))
        {
            url::redirect(url::site().'admin/dashboard');
        }

        $this->template->content = new View('adminmap/adminmap_reports');
        $this->template->content->title = Kohana::lang('ui_admin.reports');


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
        
        if ($_POST)
        {
            $post = Validation::factory($_POST);

             //  Add some filters
            $post->pre_filter('trim', TRUE);

            // Add some rules, the input field, followed by a list of checks, carried out in order
            $post->add_rules('action','required', 'alpha', 'length[1,1]');
            $post->add_rules('incident_id.*','required','numeric');

            if ($post->validate())
            {
                if ($post->action == 'a')       // Approve Action
                {
                    foreach($post->incident_id as $item)
                    {
                        $update = new Incident_Model($item);
                        if ($update->loaded == true) 
                        {
                            if( $update->incident_active == 0 ) 
                            {
                                $update->incident_active = '1';
                            } 
                            else {
                                $update->incident_active = '0';
                            }

                            // Tag this as a report that needs to be sent out as an alert
                            if ($update->incident_alert_status != '2')
                            { // 2 = report that has had an alert sent
                                $update->incident_alert_status = '1';
                            }

                            $update->save();

                            $verify = new Verify_Model();
                            $verify->incident_id = $item;
                            $verify->verified_status = '1';
                            $verify->user_id = $_SESSION['auth_user']->id;          // Record 'Verified By' Action
                            $verify->verified_date = date("Y-m-d H:i:s",time());
                            $verify->save();

                            // Action::report_approve - Approve a Report
                            Event::run('ushahidi_action.report_approve', $update);
                        }
                    }
                    $form_action = strtoupper(Kohana::lang('ui_admin.approved'));
                }
                elseif ($post->action == 'u')   // Unapprove Action
                {
                    foreach($post->incident_id as $item)
                    {
                        $update = new Incident_Model($item);
                        if ($update->loaded == true) {
                            $update->incident_active = '0';

                            // If Alert hasn't been sent yet, disable it
                            if ($update->incident_alert_status == '1')
                            {
                                $update->incident_alert_status = '0';
                            }

                            $update->save();

                            $verify = new Verify_Model();
                            $verify->incident_id = $item;
                            $verify->verified_status = '0';
                            $verify->user_id = $_SESSION['auth_user']->id;          // Record 'Verified By' Action
                            $verify->verified_date = date("Y-m-d H:i:s",time());
                            $verify->save();

                            // Action::report_unapprove - Unapprove a Report
                            Event::run('ushahidi_action.report_unapprove', $update);
                        }
                    }
                    $form_action = strtoupper(Kohana::lang('ui_admin.unapproved'));
                }
                elseif ($post->action == 'v')   // Verify Action
                {
                    foreach($post->incident_id as $item)
                    {
                        $update = new Incident_Model($item);
                        $verify = new Verify_Model();
                        if ($update->loaded == true) {
                            if ($update->incident_verified == '1')
                            {
                                $update->incident_verified = '0';
                                $verify->verified_status = '0';
                            }
                            else {
                                $update->incident_verified = '1';
                                $verify->verified_status = '2';
                            }
                            $update->save();

                            $verify->incident_id = $item;
                            $verify->user_id = $_SESSION['auth_user']->id;          // Record 'Verified By' Action
                            $verify->verified_date = date("Y-m-d H:i:s",time());
                            $verify->save();
                        }
                    }
                    $form_action = "VERIFIED";
                }
                elseif ($post->action == 'd')   //Delete Action
                {
                    foreach($post->incident_id as $item)
                    {
                        $update = new Incident_Model($item);
                        if ($update->loaded == true)
                        {
                            $incident_id = $update->id;
                            $location_id = $update->location_id;
                            $update->delete();

                            // Delete Location
                            ORM::factory('location')->where('id',$location_id)->delete_all();

                            // Delete Categories
                            ORM::factory('incident_category')->where('incident_id',$incident_id)->delete_all();

                            // Delete Translations
                            ORM::factory('incident_lang')->where('incident_id',$incident_id)->delete_all();

                            // Delete Photos From Directory
                            foreach (ORM::factory('media')->where('incident_id',$incident_id)->where('media_type', 1) as $photo) {
                                deletePhoto($photo->id);
                            }

                            // Delete Media
                            ORM::factory('media')->where('incident_id',$incident_id)->delete_all();

                            // Delete Sender
                            ORM::factory('incident_person')->where('incident_id',$incident_id)->delete_all();

                            // Delete relationship to SMS message
                            $updatemessage = ORM::factory('message')->where('incident_id',$incident_id)->find();
                            if ($updatemessage->loaded == true) {
                                $updatemessage->incident_id = 0;
                                $updatemessage->save();
                            }

                            // Delete Comments
                            ORM::factory('comment')->where('incident_id',$incident_id)->delete_all();

                            // Action::report_delete - Deleted a Report
                            Event::run('ushahidi_action.report_delete', $update);
                        }
                    }
                    $form_action = strtoupper(Kohana::lang('ui_admin.deleted'));
                }
                $form_saved = TRUE;
            }
            else
            {
                $form_error = TRUE;
            }

        }

        
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
		(int) Kohana::config('settings.items_per_page_admin'), $pagination->sql_offset );



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

        // Javascript Header
        $this->template->js = new View('admin/reports_js');
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
