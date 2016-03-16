function sts_update_ticket_views(ticket_id) {
	$.ajax({
		type: "POST",
		cache: false,
		data: "update=true",
		url: sts_base_url + "/tickets/add_views_ajax/" + ticket_id + "/"
	});

	setTimeout(function() {
		sts_update_ticket_views(ticket_id)
	}, 10000);
	//10000 = 10 second refresh
};

function sts_get_ticket_views(ticket_id) {
	$.ajax({
		type: "GET",
		cache: false,
		dataType: "json",
		url: sts_base_url + "/tickets/get_views_ajax/" + ticket_id + "/",
		success: function(data){
			if (data !== null) {
				var array = [];
				
				if (data.users.length > 0) {
					$.each(
						data.users,
						function (index, value) {
							array.push(value.name + ' (' + value.time_ago_in_words + ')');
						}
					);
					
					var html_temp = array.join(', ');

					html = '<div class="alert alert-success">' + data.message;
					html += html_temp;
					html += '</div>';
				}
				else {
					html = '';
				}
				
				$('#ajax_ticket_views_header').html(html);
			}
		}
	 });

	setTimeout(function () {
		sts_get_ticket_views(ticket_id)
	}, 10000);
	//10000 = 10 second refresh
};