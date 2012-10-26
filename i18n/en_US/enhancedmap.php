<?php
/**
 *  Admin Map US English Language file
 *
 * @author     John Etherton <john@ethertontech.com>
 * @author     Carter Draper <carjimdra@gmail.com>
 * @author     Kpetermeni Siakor <tksiakor@gmail.com> 
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
 * 
 */ 

	$lang = array(
	'admin_map_main_menu_tab' => 'Admin Map',
	'big_map_main_menu_tab' => 'Big Map',
	'print_map_main_menu_tab'=>'Print Map',			
	'boolean_operators' => 'Boolean Operators:',
	'FILTERS' => 'FILTERS',
	'Categories' => 'Categories',
	'TIME_LINE' => 'TIME LINE',
	'ORHEADER'  => 'OR',
	'ORBODY' =>				'The OR operator allows you to see all the reports that fall under any one of the categories you select.
							<br/><br/>
							For example, if you had selected categories A, B, and C, then you would see all the reports that were
							labeled as falling under category A <strong>or</strong> B <strong>or</strong> C. Some of the reports
							shown may only fall under category A. Others may only fall under category C. Some may fall under both 
							category A and B.<br/><br/>
							When the OR operator is selected, dots will be colored according to the categories selected.
							For example, if you have selected categories A and B, where A is red and B is blue, then all dots will be purple
							since purple is the color you get when mixing red and blue.',
	'OR' => 'OR',
	'ANDHEADER' => 'AND',
	'ANDBODY' =>				'The AND operator allows you to see all the reports that fall under all of the categories you select.<br/><br/>
							For example, if you had selected categories A, B, and C, then you would see all the reports that were
							labeled as falling under category A <strong>and</strong> B <strong>and</strong> C. <br/><br/>
							When the AND operator is selected, dots will be colored according to the categories you have selected.
							Since every report shown will fall under all of the categories selected, all of the colors of the categories
							selected will be merged and the dots will have the merged color.',
	'AND' => 'AND',

// Kpetermeni's Entries
	'header_info' => 'This maps shows you all of the reports you are authorized to see. This includes unapproved reports.',
	'logical_operators' => 'Logical Operators',
	'unapproved_reports' => 'Show unapproved reports as black',
	'status_filters' => 'Status Filters',
	'or' => 'OR',
	'and' => 'AND',
	'or_details' => 'Show all reports that fall under at least one of the categories selected below',
	'and_details' => 'Show all reports that fall under all of the categories selected below',
	'show_all_reports' => 'Show All Reports',
	'embedd_html' => 'To embed this map in your own site use this HTML:',
	//john's entries
	'group_categories' => 'Group Categories',
	'site_categories' => 'Site Categories',
	'whats_this' => 'What\'s this?',
	'print_a_map' => 'Print a map',
	'print_this_map' => 'Print this map',
	'map_printing' => 'Map Printing',
	'read_before_printing' => 'Please Read Before Printing:',
	'printmap_info' => 'This page is for creating maps that will be printed.
	Printing works best using <strong>Firefox</strong> 4 or higher. <strong>Chrome, Internet Explorer,</strong> 
	and other browsers may not print this page correctly.<br/>
	For best results in Firefox, go to <strong>"Page Setup"</strong> and make sure that
	<strong>"Print Background (Colors &amp; Images)"</strong> is turned on and that <strong>scaling</strong> is set to <strong>"scale to fit."</stong>',
	'map_key' => 'Map Key:',
	'map_key_1' => 'Reports from %s to %s.',
	'generate_image' => 'Generate an image of this map',
	'print_to_image' => 'Print to image',
	'generate_url' => 'Generate a URL to share this map with others',
	'map_url' => 'URL for this map:',
	'page_url' => 'URL for this page:',
	'embed_url' => 'URL to embed this map:',
	'create_url' => 'Create URL',
	'share_map' => 'Share this map',
	'show_key' => 'Show key:',
	'key_placement' => 'Key Placement:',
	'key_options' => 'Key Options',
	'orientation' => 'Orientation',
	'time_chooser' => 'Time Chooser',
	'portrait' => 'Portrait',
	'landscape' => 'Landscape',
	'logic_str_or' => "All reports on this map fall under one or more of the following categories.",
	'logic_str_and' => "All reports on this map fall under all of the following categories. ",
	'left' => 'Left',
	'right' => 'Right',
	'up' => 'Up',
	'down' => 'Down',
	'site_categories' => 'Site Categories',
	
	//settings
	'enable_bigmap'=>'Enable Big Map menu item on the front end',
	'enable_bigmap_description'=>'Chosing \'Yes\' will cause a \'Big Map\' menu item to appear on the front end of this website. If you choose \'No\' the Big Map page will still be available, only the menu item will be turned off. <br/> <br/> By default the Big Map is enabled.',
	
	'enable_printmap'=>'Enable Print Map menu item on the front end',
	'enable_printmap_description'=>'Chosing \'Yes\' will cause a \'Print Map\' menu item to appear on the front end of this website. If you choose \'No\' the Print Map page will still be available, only the menu item will be turned off. <br/> <br/> By default the Print Map is disabled.',
	
	'enable_iframemap'=>'Enable embed map code on the front end',
	'enable_iframemap_description'=>'Chosing \'Yes\' will cause a text box to appear above the map on the home page that containts the needed HTML to embed the map on another website. If you choose \'No\' the iFrame Map page will still be available, only the text box with the HTML code will be turned off. <br/> <br/> By default the map embed code is enabled.',	
	
	'enable_adminmap'=>'Enable Admin Map on the back end',
	'enable_adminmap_description'=>'Chosing \'Yes\' will cause a \'Admin Map\' menu item to appear on the back end. If you choose \'No\' both the menu item and the Admin Map page will be disabled. <br/> <br/> By default the Admin Map is enabled.',
	
	'adminmap_height'=>'Height of Admin Map',
	'adminmap_height_description'=>'Sets the height of the Admin Map. Use CSS notation. If you want 3rd party CSS to determine the sizing set the value to \'other\'<br/><br/>By default the Admin Map height is set to \'other\'',
	
	'adminmap_width'=>'Width of Admin Map',
	'adminmap_width_description'=>'Sets the width of the Admin Map. Use CSS notation. If you want 3rd party CSS to determine the sizing set the value to \'other\'<br/><br/>By default the Admin Map height is set to \'other\'',
	
	'show_unapproved_backend'=>'Allow users to see unapproved reports on the back end',
	'show_unapproved_backend_description'=>'Chosing \'Yes\' will allow users on the back end to see unapproved reports on the map.<br/><br/>By default this is enabled.',
	
	'show_unapproved_frontend'=>'Allow users to see unapproved reports on the front end',
	'show_unapproved_frontend_description'=>'Chosing \'Yes\' will allow users on the front end to see unapproved reports on the map.<br/><br/>By default this is disabled.',
	
	'show_hidden_categories_backend'=>'Allow users to see hidden categories on the back end',
	'show_hidden_categories_backend_description'=>'Chosing \'Yes\' will allow users on the back end to see and filter by hidden categories.<br/><br/>By default this is enabled.',
	
	'enhancedmap_settings'=>'Enhanced Map Settings',
	
	'adminmap_width_required'=>'Width of Admin Map must not be empty',
	'adminmap_height_required'=>'Height of Admin Map must not be empty',
	
	'color_options_description'=>'Determines how the dots of the map are colored when more than one category is being used as a filter.',
	'color_options'=>'How should the dots be colored',
	
	'merge_all_description'=>' <strong>Merge all.</strong> All the colors of the selected categories will be merged together for the resultant color of all dots. Lowest database load.',
	'highest_first_description'=>' <strong>Highest takes precedence</strong>. Colors will be assigned per dot, with the higest ranking category taking precedence. Highest database load.',
	
	'size_of_dots'=>'Size of dots',
	'size_of_dots_description'=>'How large or small do you want the dots that appear on the map to be?',
	'small'=>'Small',
	'medium'=>'Medium',
	'large'=>'Large',
	'exlarge'=>'Extra-Large',
	
	'alphabetize'=>'Alphabetize',
	'clustering'=>'Clustering',
	'on'=>'On',
	'off'=>'Off',

);
	
