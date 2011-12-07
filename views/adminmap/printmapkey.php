<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Key for the print map
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

<h5>Map Key:</h5>

Reports from <strong><?php echo $keyStartDate; ?></strong> to <strong><?php echo $keyEndDate; ?></strong>.<br/>
<?php echo $logic; ?>
<br/>
<ul id="keyCategories">
	<?php 
		foreach ($categories as $cat)
		{
		?>
			<li> 
				<div class="swatch" style="background:#<?php echo $cat["color"]; ?>;"></div> 
				<?php echo $cat["name"];?>
			</li>
		<?php 
		}
	?>
	
</ul>