$(document).ready(function(){
	$.get(baseUrl + 'iframemap/setup', 
			function(data){			
				$("#map").before(data);				
			});
	
});