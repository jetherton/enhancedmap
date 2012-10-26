<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Footer for the big map
 * 
 *
 * @author     John Etherton <john@ethertontech.com>
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
 */
?>
	
	
	<?php
	echo $footer_block;
	// Action::main_footer - Add items before the </body> tag
	Event::run('ushahidi_action.main_footer');
	?>
</body>
<?php Event::run('ushahidi_action.main_footer'); ?>
</html>