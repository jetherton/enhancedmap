<?php defined('SYSPATH') or die('No direct script access.');
/**
 * View that provides the info for embedding the iframe map
 * 
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     John Etherton <john@ethertontech.com>
 * @package    Admin Map, Ushahidi Plugin - https://github.com/jetherton/adminmap
 */
?>

<div id="adminmap_map_embedd">
<?php echo Kohana::lang("adminmap.embedd_html")?>
<br/>
	<input type="text" value="<?php echo $html; ?>"/>
	
</div>
