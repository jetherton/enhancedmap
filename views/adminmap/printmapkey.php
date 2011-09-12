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