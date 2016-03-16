$(document).ready(function () {
	
	//Delete existing line item
	$('body').on('click', '#delete_existing_api_key_item', function(e){
		e.preventDefault();

		if (confirm("Are you sure you wish to permanently delete this key?")){
			
			var api_key_id = $(this).closest('td').attr("id");
			var api_key_exploded = api_key_id.split('-');
			api_key_id = api_key_exploded[1];
					
			$.ajax({
				type: "POST",
				url:  sts_base_url + "/settings/deleteapikey/" + api_key_id,
				data: "delete=true",
				success: function(html){
					
				}
			 });
			 
			 $(this).parent('td').parent('tr').remove(); 
		}
		else{
				return false;
		}
		
    });
		
});