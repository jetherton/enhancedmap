<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Key for the print map
 * 
 *
 * @author     John Etherton <john@ethertontech.com>
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
 */
?>

<h5><?php echo Kohana::lang('enhancedmap.map_key'); ?></h5>

<?php echo Kohana::lang('enhancedmap.map_key_1', array("<strong>$keyStartDate</strong>","<strong>$keyEndDate</strong>")); ?><br/>
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