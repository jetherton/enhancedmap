/**
 * Java Script used to add embedd link for the map
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     John Etherton <john@ethertontech.com>
 * @package    Admin Map, Ushahidi Plugin - https://github.com/jetherton/adminmap
 */


var adminmap_embed_count = 0;
$(document).ready(function(){
	adminmap_embed_count++;
	if( adminmap_embed_count == 1)
	{
		$.get(baseUrl + 'iframemap/setup', 
				function(data){			
					$("#map").before(data);				
				});
	}
	
});

