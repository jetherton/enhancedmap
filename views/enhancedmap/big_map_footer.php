<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Footer for the big map
 * 
 *
 * @author     John Etherton <john@ethertontech.com>
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
 */
?>
	
	
	<?php //echo $ushahidi_stats; ?>
	<?php echo $google_analytics; ?>
	

	<!-- Task Scheduler --><script type="text/javascript">$(document).ready(function(){$.get("<?php echo url::base(); ?>scheduler");});</script><!-- End Task Scheduler -->
 
</body>
<?php Event::run('ushahidi_action.main_footer'); ?>
</html>