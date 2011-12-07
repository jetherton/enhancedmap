<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Header for the print map
 * 
 * This file is adapted from the file Ushahidi_Web/themes/default/views/header.php
 * Originally written by the Ushahidi Team
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     John Etherton <john@ethertontech.com>
 * @package    Admin Map, Ushahidi Plugin - https://github.com/jetherton/adminmap
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
	<title><?php echo $site_name; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<?php echo $header_block; ?>
	<?php
	// Action::header_scripts - Additional Inline Scripts from Plugins
	Event::run('ushahidi_action.header_scripts');
	?>
	
	<?php 
		if(isset($_GET["pdf"])){$media="all";} else{$media="print";}
	?>
	<link rel="stylesheet" type="text/css" href="<?php echo url::site(); ?>plugins/adminmap/css/print_adminmap_media.css" media="<?php echo $media; ?>" />
</head>

<body id="page">


				<!-- mainmenu -->
				<div id="mainmenu" class="clearingfix">
					<ul>
						<?php nav::main_tabs($this_page); ?>
					</ul>

				</div>
				<!-- / mainmenu -->
