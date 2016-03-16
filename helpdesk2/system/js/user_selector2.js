function populate_users_2(department_id, selected_user_id) {

	$.ajax({
		type: "GET",
		cache: false,
		dataType: "json",
		url:  sts_base_url + "/data/users/?department_id=" + department_id,
		success: function(data){

			//quicker to replace the entire html select	
			$('#user_id2').empty();
		
			html = '<option value="">&nbsp;</option>';

			if (data !== null) {
				$.each(
					data,
					function (index, value) {
						if (selected_user_id == value.id) {
							html += '<option value="'+ value.id + '" selected="selected">' + value.name  + '</option>';
						}
						else {
							html += '<option value="'+ value.id + '">' + value.name  + '</option>';						
						}
					}
				);
			}
									
			$('#user_id2').html(html);
			if ($.fn.select2) {
				$('#user_id2').select2('val', selected_user_id);
			}
			
		}
	});
}

function populate_assigned_users_2(department_id, selected_user_id) {
	$.ajax({
		type: "GET",
		cache: false,
		dataType: "json",
		url:  sts_base_url + "/data/users/?assigned_users=true&department_id=" + department_id,
		success: function(data){
			
			//quicker to replace the entire html select	
			$('#assigned_user_id2').empty();
		
			html = '<option value="">&nbsp;</option>';

			if (data !== null) {
				$.each(
					data,
					function (index, value) {
						if (selected_user_id == value.id) {
							html += '<option value="'+ value.id + '" selected="selected">' + value.name  + '</option>';
						}
						else {
							html += '<option value="'+ value.id + '">' + value.name  + '</option>';												
						}
					}
				);
			}
									
			$('#assigned_user_id2').html(html);
			if ($.fn.select2) {
				$('#assigned_user_id2').select2('val', selected_user_id);
			}
		}
	});
}

$(document).ready(function () {

	if ($('#assigned_user_id2').length > 0) {
		if ($('#assigned_user_id2').val().length > 0) {
			if ($('#update_department_id2').length > 0 && $('#update_department_id2').val().length > 0) {
				populate_assigned_users_2($('#update_department_id2').val(), $('#assigned_user_id2').val());
			}
			else {
				populate_assigned_users_2(0, $('#assigned_user_id2').val());
			}
		}
		else {			
			if ($('#update_department_id2').length > 0 && $('#update_department_id2').val().length > 0) {
				populate_assigned_users_2($('#update_department_id2').val(), 0);
			}
			else {
				populate_assigned_users_2(0, 0);
			}			
		}
	}

	if ($('#user_id2').length > 0) {
		if ($('#user_id2').val().length > 0) {
			if ($('#update_department_id2').length > 0 && $('#update_department_id2').val().length > 0) {
				populate_users_2($('#update_department_id2').val(), $('#user_id2').val());
			}
			else {
				populate_users_2(0, $('#user_id2').val());
			}
		}
		else {
			if ($('#update_department_id2').length > 0 && $('#update_department_id2').val().length > 0) {
				populate_users_2($('#update_department_id2').val(), 0);
			}
			else {
				populate_users_2(0, 0);
			}
		}
	}

	$('body').on('change', '#update_department_id2', function(e){
		populate_users_2($(this).val());
		populate_assigned_users_2($(this).val());
	});
	
});