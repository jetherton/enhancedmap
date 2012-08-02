<?php defined('SYSPATH') or die('No direct script access.');
/**
 * view for the settings of the Enhanced Map plugin
 * 
 *
 * @author     John Etherton <john@ethertontech.com>
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
 */
?>
	
			<div class="bg">

				<?php print form::open(NULL,array('enctype' => 'multipart/form-data', 'id' => 'enhancedmapSettings', 'name' => 'enhancedmapSettings')); ?>
				<div class="report-form">
					<?php
					if (count($errors) > 0) {
					?>
						<!-- red-box -->
						<div class="red-box">
							<h3><?php echo Kohana::lang('ui_main.error');?></h3>
							<ul>
							<?php
							foreach ($errors as $error_item => $error_description)
							{
								print (!$error_description) ? '' : "<li>" . Kohana::lang('enhancedmap.'.$error_item.'_'. $error_description) . "</li>";
							}
							?>
							</ul>
						</div>
					<?php
					}

					if ($form_saved) {
					?>
						<!-- green-box -->
						<div class="green-box">
							<h3><?php echo Kohana::lang('ui_main.configuration_saved');?></h3>
						</div>
					<?php
					}
					?>
					<div class="head">
						<h3><?php echo Kohana::lang('enhancedmap.enhancedmap_settings');?></h3>
						<a href="<?php echo url::base();?>admin/addons/plugins" class="cancel-btn" ><img src="<?php echo url::file_loc('img'); ?>media/img/admin/btn-cancel.gif"  /> </a>
						<input val="save" id="save" type="image" src="<?php echo url::file_loc('img'); ?>media/img/admin/btn-save-settings.gif" class="save-rep-btn" />
					</div>
					<!-- column -->
					<div class="sms_holder">
					
						<div class="row">
							<h4>
								<a href="#" class="tooltip" title="<?php echo Kohana::lang("enhancedmap.enable_bigmap_description"); ?>">
									<?php echo Kohana::lang('enhancedmap.enable_bigmap');?>
								</a>
							</h4>
							<span class="sel-holder">
								<?php print form::dropdown('enable_bigmap', $yesno_array, $form['enable_bigmap']); ?>
							</span>
						</div>
						
						<div class="row">
							<h4>
								<a href="#" class="tooltip" title="<?php echo Kohana::lang("enhancedmap.enable_printmap_description"); ?>">
									<?php echo Kohana::lang('enhancedmap.enable_printmap');?>
								</a>
							</h4>
							<span class="sel-holder">
								<?php print form::dropdown('enable_printmap', $yesno_array, $form['enable_printmap']); ?>
							</span>
						</div>
						
						<div class="row">
							<h4>
								<a href="#" class="tooltip" title="<?php echo Kohana::lang("enhancedmap.enable_iframemap_description"); ?>">
									<?php echo Kohana::lang('enhancedmap.enable_iframemap');?>
								</a>
							</h4>
							<span class="sel-holder">
								<?php print form::dropdown('enable_iframemap', $yesno_array, $form['enable_iframemap']); ?>
							</span>
						</div>
						
						<div class="row">
							<h4>
								<a href="#" class="tooltip" title="<?php echo Kohana::lang("enhancedmap.enable_adminmap_description"); ?>">
									<?php echo Kohana::lang('enhancedmap.enable_adminmap');?>
								</a>
							</h4>
							<span class="sel-holder">
								<?php print form::dropdown('enable_adminmap', $yesno_array, $form['enable_adminmap']); ?>
							</span>
						</div>
						
						<div class="row">
							<h4>
								<a href="#" class="tooltip" title="<?php echo Kohana::lang("enhancedmap.adminmap_height_description"); ?>">
									<?php echo Kohana::lang('enhancedmap.adminmap_height');?>
								</a>
							</h4>
							<span class="sel-holder">
								<?php print form::input('adminmap_height', $form['adminmap_height'], ' class="text"'); ?>
							</span>
						</div>													
						
						<div class="row">
							<h4>
								<a href="#" class="tooltip" title="<?php echo Kohana::lang("enhancedmap.adminmap_width_description"); ?>">
									<?php echo Kohana::lang('enhancedmap.adminmap_width');?>
								</a>
							</h4>
							<span class="sel-holder">
								<?php print form::input('adminmap_width', $form['adminmap_width'], ' class="text"'); ?>
							</span>						
						</div>		
						
						<div class="row">
							<h4>
								<a href="#" class="tooltip" title="<?php echo Kohana::lang("enhancedmap.show_unapproved_backend_description"); ?>">
									<?php echo Kohana::lang('enhancedmap.show_unapproved_backend');?>
								</a>
							</h4>
							<span class="sel-holder">
								<?php print form::dropdown('show_unapproved_backend', $yesno_array, $form['show_unapproved_backend']); ?>
							</span>
						</div>				
						
						<div class="row">
							<h4>
								<a href="#" class="tooltip" title="<?php echo Kohana::lang("enhancedmap.show_unapproved_frontend_description"); ?>">
									<?php echo Kohana::lang('enhancedmap.show_unapproved_frontend');?>
								</a>
							</h4>
							<span class="sel-holder">
								<?php print form::dropdown('show_unapproved_frontend', $yesno_array, $form['show_unapproved_frontend']); ?>
							</span>
						</div>								
						
						<div class="row">
							<h4>
								<a href="#" class="tooltip" title="<?php echo Kohana::lang("enhancedmap.show_hidden_categories_backend_description"); ?>">
									<?php echo Kohana::lang('enhancedmap.show_hidden_categories_backend');?>
								</a>
							</h4>
							<span class="sel-holder">
								<?php print form::dropdown('show_hidden_categories_backend', $yesno_array, $form['show_hidden_categories_backend']); ?>
							</span>
						</div>		
						
						<div class="row">
							<h4>
								<a href="#" class="tooltip" title="<?php echo Kohana::lang("enhancedmap.color_options_description"); ?>">
									<?php echo Kohana::lang('enhancedmap.color_options');?>
								</a>
							</h4>
								<?php
									print form::radio('color_mode', 'merge_all', $form['color_mode'] == 'merge_all');
									print form::label('color_mode', Kohana::lang("enhancedmap.merge_all_description") ).'<br />';
									print form::radio('color_mode', 'highest_first', $form['color_mode'] == 'highest_first');
									print form::label('color_mode', Kohana::lang("enhancedmap.highest_first_description")).'<br />';

									
								?>
						</div>			
							
						<div class="row">
							<h4>
								<a href="#" class="tooltip" title="<?php echo Kohana::lang("enhancedmap.size_of_dots_description"); ?>">
									<?php echo Kohana::lang('enhancedmap.size_of_dots');?>
								</a>
							</h4>
								<?php
									$size_array = array('1'=>Kohana::lang('enhancedmap.small'),
											'2'=>Kohana::lang('enhancedmap.medium'),
											'3'=>Kohana::lang('enhancedmap.large'),
											'4'=>Kohana::lang('enhancedmap.exlarge'),
											);
									print form::dropdown('dot_size',$size_array, $form['dot_size']);
									
								?>
						</div>			
						
						
						
					<div class="simple_border"></div>

					<input type="image" src="<?php echo url::file_loc('img'); ?>media/img/admin/btn-save-settings.gif" class="save-rep-btn" />
					<a href="<?php echo url::base();?>admin/addons/plugins" class="cancel-btn" ><img src="<?php echo url::file_loc('img'); ?>media/img/admin/btn-cancel.gif"  /> </a>
				</div>
				<?php print form::close(); ?>
			</div>
