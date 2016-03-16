$(document).ready(function () {

	var forum_thread_id = $('.forums_thread_subscription').attr("id");
	var forum_thread_exploded = forum_thread_id.split('-');
	forum_thread_id = forum_thread_exploded[1];

	$.ajax({
		type: "GET",
		cache: false,
		dataType: "json",
		url:  sts_base_url + "/simple/forum_get_subscription/" + forum_thread_id + "/",
		success: function(data){
			if (data !== null) {
				if (data.subscribed) {
					$('.forums_thread_subscription').html('Unsubscribe');
				}
				else {
					$('.forums_thread_subscription').html('Subscribe');				
				}
			}
		}
	 });		

	$('body').on('click', '.forums_thread_subscription', function(e){
		e.preventDefault();

		$.ajax({
			type: "POST",
			cache: false,
			dataType: "json",
			url:  sts_base_url + "/simple/forum_get_subscription/" + forum_thread_id + "/",
			data: "save=true",
			success: function(data){
				if (data !== null) {
					if (data.subscribed) {
						$('.forums_thread_subscription').html('Unsubscribe');
					}
					else {
						$('.forums_thread_subscription').html('Subscribe');				
					}
				}
			}
		 });

		
    });
		
});