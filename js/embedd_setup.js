/**
 * @author     John Etherton <john@ethertontech.com>
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
 * @license	   GNU Lesser GPL (LGPL) Rights pursuant to Version 3, June 2007
 * @copyright  2012 Etherton Technologies Ltd. <http://ethertontech.com>
 * @Date	   2011-08-14
 * Purpose:	   Java Script used to add embedd link for the map
 * Inputs:     N/A
 * Outputs:    Java Script
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
 * 2011-08-14:  Etherton - Initial release
 *
 * Developed by Etherton Technologies Ltd.
 */

var adminmap_embed_count = 0;
$(document).ready(function(){
	adminmap_embed_count++;
	if( adminmap_embed_count == 1)
	{
		var baseUrl = $("#base_url").text();
		$.get(baseUrl + 'iframemap/setup', 
				function(data){			
					$("#map").before(data);				
				});
	}
	
});

