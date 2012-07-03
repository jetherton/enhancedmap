/**
 * Java Script used to add embedd link for the map
 *
 *
 * @author     John Etherton <john@ethertontech.com>
 * @package    Enhanced Map, Ushahidi Plugin - https://github.com/jetherton/enhancedmap
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

