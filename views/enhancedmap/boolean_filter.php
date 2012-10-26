<?php defined('SYSPATH') or die('No direct script access.');
/**
 * View for the boolean filter
 * 
 
 * @author     John Etherton <john@ethertontech.com>
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
 */
?>

<!-- logic filters -->
			

				<ul id="<?php echo $boolean_filter_id; ?>"  class="category-filters boolean-filters">
				
				<strong><?php echo Kohana::lang("enhancedmap.boolean_operators"); ?></strong>				
				
					<li>
						<?php if($show_help){?>
						<div style="float:right; margin-left:10px;"><span style="cursor:help;text-transform:none;color:#bb0000;" title="
							<?php echo '<h3>'.Kohana::lang("enhancedmap.ORHEADER"). '</h3>'. Kohana::lang("enhancedmap.ORBODY"); ?>
							"><?php echo Kohana::lang('enhancedmap.whats_this'); ?></span></div>
							<?php }?>
						<a class="active" id="logicalOperator_1" href="#">							
						<?php echo Kohana::lang("enhancedmap.OR"); ?>
						</a>
					</li>
					<li>
						<?php if($show_help){?>
						<div style="float:right; margin-left:10px;"><span style="cursor:help;text-transform:none;color:#bb0000;" title="
							<?php echo '<h3>'.Kohana::lang("enhancedmap.ANDHEADER").'</h3>'.Kohana::lang("enhancedmap.ANDBODY"); ?>							
						"><?php echo Kohana::lang('enhancedmap.whats_this'); ?></span></div>
						<?php }?>
						<a  id="logicalOperator_2" href="#">
							<div class="status-title"><?php echo Kohana::lang("enhancedmap.AND"); ?></div>
						</a>
					</li>
				</ul>
					       
		       <!-- /logic filters -->