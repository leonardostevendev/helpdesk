function populate_sub_themes(theme_name, selected_sub_theme) {

	//quicker to replace the entire html select
	$('#default_sub_theme_area').html('<p>Default Sub Theme<br /><select name="default_sub_theme" id="default_sub_theme"><option value="">loading...</option></select></p>');

		
	$.ajax({
		type: "GET",
		cache: false,
		dataType: "json",
		url:  sts_base_url + "/data/themes/",
		success: function(data){			
			html = '';

			if (data !== null) {
				$.each(
					data,
					function (index, value) {
						if (theme_name == index) {
							if (value.sub_themes.length !== 0) {
								$.each(
									value.sub_themes,
									function (index2, value2) {
										if (selected_sub_theme == value2) {
											html += '<option value="'+ value2 + '" selected="selected">' + value2  + '</option>';
										}
										else {
											html += '<option value="'+ value2 + '">' + value2  + '</option>';									
										}
									}
								);
							}
							else {
								html += '<option value="">N/A</option>';									
							}
						}
					}
				);
			}
												
			$('#default_sub_theme').html(html);
			if ($.fn.select2) {
				$('#default_sub_theme').select2({ width: 'resolve' });
				$('#default_sub_theme').select2('val', selected_sub_theme);

			}
		}
	});
}


$(document).ready(function () {

	$('#pushover_extra').hide();
	
	if ($('#pushover_enabled').val() == 1) {
		$('#pushover_extra').slideDown();
	}
	
	$('#pushover_enabled').change(function() {
		$('#pushover_extra').slideToggle();
	});
	
	populate_sub_themes($('#default_theme').val(), $('#default_theme').attr("data-sub-theme"));

    $('#default_theme').change(function () {
		populate_sub_themes($(this).val(), $('#default_theme').attr("data-sub-theme"));
	});

	//Delete existing line item
	$('body').on('click', '#delete_existing_pushover_user', function(e){
		e.preventDefault();
			
		var pushover_user_id = $(this).closest('div').attr("id");
		var pushover_user_id_exploded = pushover_user_id.split('-');
		pushover_user_id = pushover_user_id_exploded[1];
				
		$.ajax({
			type: "POST",
			url:  sts_base_url + "/settings/deletepushoveruser/" + pushover_user_id,
			data: "delete=true",
			success: function(html){
				
			}
		 });
		 
		 $(this).parent('p').remove(); 
		
    });
	
		
});