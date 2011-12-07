<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Footer for the big map
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
	
	
	<?php //echo $ushahidi_stats; ?>
	<?php echo $google_analytics; ?>
	

	<!-- Task Scheduler --><script type="text/javascript">$(document).ready(function(){$.get("<?php echo url::base(); ?>scheduler");});</script><!-- End Task Scheduler -->
 
</body>
<?php Event::run('ushahidi_action.main_footer'); ?>
</html>