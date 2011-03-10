
	
	
	<?php //echo $ushahidi_stats; ?>
	<?php echo $google_analytics; ?>
	
	<!-- Task Scheduler -->
	<img src="<?php echo url::site().'scheduler'; ?>" height="1" width="1" border="0" />
 
</body>
<?php Event::run('ushahidi_action.main_footer'); ?>
</html>