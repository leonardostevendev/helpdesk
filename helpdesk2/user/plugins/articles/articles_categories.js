$(document).ready(function () {
	
	//Delete existing category
	$('body').on('click', '#delete_existing_category_item', function(e){
		e.preventDefault();

		if (confirm("Are you sure you wish to permanently delete this category?")){
			
			var category_id = $(this).closest('div').attr("id");
			var category_exploded = category_id.split('-');
			category_id = category_exploded[1];
					
			$.ajax({
				type: "POST",
				url:  sts_base_url + "/simple/kb_category_delete/" + category_id,
				data: "delete=true",
				success: function(html){
					
				}
			 });
			 
			 $(this).parent('p').remove(); 
		}
		else {
			return false;
		}
		
    });
	
	//Delete existing file
	$('body').on('click', '#delete_existing_file', function(e){
		e.preventDefault();

		if (confirm("Are you sure you wish to delete this file?")){
			
			var file_id = $(this).closest('li').attr("id");
			var file_exploded = file_id.split('-');
			file_id = file_exploded[1];
			
			$.ajax({
				type: "POST",
				url:  sts_base_url + "/simple/kb_file_delete/" + file_id + "/",
				data: "delete=true",
				success: function(html){
				}
			 });
			 
			 $(this).parent('li').remove(); 
		}
		else {
			return false;
		}
		
    });
		
});