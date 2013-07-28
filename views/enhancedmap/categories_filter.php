<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @author     John Etherton <john@ethertontech.com>
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
 * @license	   GNU Lesser GPL (LGPL) Rights pursuant to Version 3, June 2007
 * @copyright  2012 Etherton Technologies Ltd. <http://ethertontech.com>
 * @Date	   2012-06-08
 * Purpose:	   View for the categories filter
 *             This file is adapted from the file Ushahidi_Web/themes/default/views/main.php
 *             Originally written by the Ushahidi Team
 * Inputs:     $categories_view_id - HTML element id of this whole view. Great for a $("#<category_view_id>") type function
 *             $categories - An array of categories that will be shown to the user
 * Outputs:    HTML
 *
 * The Enhanced Map, Ushahidi Plugin is free software: you can redistribute
 * it and/or modify it under the terms of the GNU Lesser General Public License
 * as published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * The Enhanced Map, Ushahidi Plugin is distributed in the hope that it will
 * be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with the Enhanced Map, Ushahidi Plugin.  If not, see
 * <http://www.gnu.org/licenses/>.
 *
 * Changelog:
 * 2012-06-08:  Etherton - Initial release
 *
 * Developed by Etherton Technologies Ltd.
 */
?>


<!-- category filters -->
			<div id="catsortdiv"></div>
			<ul id="<?php echo $categories_view_id;?>" class="category-filters cat_switch">
				<strong ><?php echo Kohana::lang("ui_main.categories"); ?></strong>
				<a class="alphabetize_link" id="<?php echo $categories_view_id;?>_alphabetize_link" href="#" onclick="alphabetize('<?php echo $categories_view_id;?>'); return false;"> <?php echo Kohana::lang("enhancedmap.alphabetize"); ?></a>
				<li><a class="active" id="cat_0" href="#"><div class="swatch" style="background-color:#<?php echo Kohana::config('settings.default_map_all');?>"></div><div class="category-title"><?php echo Kohana::lang('ui_main.all_categories');?></div></a></li>
				<?php
					foreach ($categories as $category => $category_info)
					{
						$category_title = $category_info[0];
						$category_color = $category_info[1];
						$category_image = '';
						$color_css = 'class="swatch" style="background-color:#'.$category_color.'"';
						if($category_info[2] != NULL && file_exists(Kohana::config('upload.relative_directory').'/'.$category_info[2])) {
							$category_image = html::image(array(
								'src'=>Kohana::config('upload.relative_directory').'/'.$category_info[2],
								'style'=>'float:left;padding-right:5px;'
								));
							$color_css = '';
						}
						//check if this category has kids
						if(count($category_info[3]) > 0)
						{
							echo '<li>';
							echo '<a style="float:right; text-align:center; width:15px; padding:2px 0px 1px 0px; border-left:none;" href="#" id="drop_cat_'.$category.'">+</a>';
							echo '<a  href="#" id="cat_'. $category .'"><div '.$color_css.'>'.$category_image.'</div><div class="category-title">'.$category_title.'</div></a>';
							
						}
						else
						{
							echo '<li><a href="#" id="cat_'. $category .'"><div '.$color_css.'>'.$category_image.'</div><div class="category-title">'.$category_title.'</div></a>';
						}
						// Get Children
						echo '<div class="hide" id="child_'. $category .'"><ul>';
						foreach ($category_info[3] as $child => $child_info)
						{
							$child_title = $child_info[0];
							$child_color = $child_info[1];
							$child_image = '';
							$color_css = 'class="swatch" style="background-color:#'.$child_color.'"';
							if($child_info[2] != NULL && file_exists(Kohana::config('upload.relative_directory').'/'.$child_info[2])) {
								$child_image = html::image(array(
									'src'=>Kohana::config('upload.relative_directory').'/'.$child_info[2],
									'style'=>'float:left;padding-right:5px;'
									));
								$color_css = '';
							}
							echo '<li style="padding-left:20px;"><a href="#" id="cat_'. $child .'" cat_parent="'.$category.'"><div '.$color_css.'>'.$child_image.'</div><div class="category-title">'.$child_title.'</div></a></li>';
						}
						echo '</ul></div></li>';
					}
				?>
			</ul>
			<!-- / category filters -->
