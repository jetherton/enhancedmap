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

