<?php defined('SYSPATH') or die('No direct script access.');
/**
 * View that provides the info for embedding the iframe map
 * 
 *
 * @author     John Etherton <john@ethertontech.com>
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
 */
?>

<div id="adminmap_map_embedd">
<?php echo Kohana::lang("enhancedmap.embedd_html")?>
<br/>
	<input type="text" value="<?php echo $html; ?>"/>
	
</div>
