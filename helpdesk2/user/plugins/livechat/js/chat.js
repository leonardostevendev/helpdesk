function nl2br (str, is_xhtml) {   
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';    
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1'+ breakTag +'$2');
}

function receive_active_sessions() {

	$.ajax({
		type: "GET",
		cache: false,
		dataType: "json",
		url: sts_base_url + "/simple/livechat_admin_activesessions/",
		success: function(data){
			
			html = '';
			
			$.each(
				data,
				function (index, value) {	
					html += '<tr class="switch-1"><td class="centre">'+$('<div/>').text(value.id).html()+'</td><td class="centre"><a href="'+sts_base_url+'/p/livechat_view/'+$('<div/>').text(value.id).html()+'/">'+$('<div/>').text(value.name).html()+'</a></td><td class="centre">'+$('<div/>').text(value.email).html()+'</td><td class="centre">'+$('<div/>').text(value.date_added).html()+'</td><td class="centre">'+$('<div/>').text(value.last_guest_message).html()+'</td></td>';
				}
			);
			
			html += '';
			
			$('#active_sessions_table > tbody:last').html(html);
		}
	 });
	 
	setTimeout(function() {
		receive_active_sessions()
	}, 3000);
	
};

function receive_chat_messages() {

	var chat_id = $('.view_chat_messages').attr("id");
	var chat_exploded = chat_id.split('-');
	chat_id = chat_exploded[1];

	var current_admin = 0;
	var current_guest = 0;

	$.ajax({
		type: "GET",
		cache: false,
		dataType: "json",
		url: sts_base_url + "/simple/livechat_admin_receive/" + chat_id + "/",
		success: function(data){
			if (data !== null) {
				if (data.length > 0) {
					html = '';
					
					$.each(
						data,
						function (index, value) {
							if (value.guest == 1) {
								current_guest++;
								html += '<p>' + nl2br($('<div/>').text(value.message).html()) + ' <br /><div class="message-name">' + $('<div/>').text(value.name).html() + ' - ' + $('<div/>').text(value.time_ago).html() + '</div><div class="clearfix"></div><hr /></p>';
							}
							else {
								current_admin++;
								html += '<p>' + nl2br($('<div/>').text(value.message).html()) + ' <br /><div class="message-name">' + $('<div/>').text(value.user_name).html() + ' - ' + $('<div/>').text(value.time_ago).html() + '</div><div class="clearfix"></div><hr /></p>';					
							}
						}
					);
									
					$('.view_chat_messages').html(html);
					
					if (data.length > lcs_admin_current_messages) {
						$('.view_chat_messages').scrollTop($('.view_chat_messages').prop("scrollHeight"));					
					}
					
					if (current_guest > lcs_admin_current_guest_messages && lcs_admin_current_notifications) {
						var audio = new Audio(sts_base_url + '/user/plugins/livechat/audio/alert1.wav');
						audio.play();
					}
					
					lcs_admin_current_notifications		= true;
					lcs_admin_current_messages		 	= data.length;
					lcs_admin_current_guest_messages 	= current_guest;
				}
				else {
					lcs_admin_current_notifications		= true;

					html = '<p>No Messages<hr /></p>';

					$('.view_chat_messages').html(html);
				}
			}
		}
	});
	
	 
	 
	 setTimeout(function() {
		receive_chat_messages()
	}, 3000);
	
};

var lcs_admin_current_messages = 0;
var lcs_admin_current_guest_messages = 0;
var lcs_admin_current_admin_messages = 0;
var lcs_admin_current_notifications = false;

$(document).ready(function () {

	if ($('#active_sessions_table').length) {
		receive_active_sessions();
	}
	
	if ($('.view_chat_messages').length) {
		$('.view_chat_messages').html('Loading...');
		receive_chat_messages();		
	}
	
	//send a message
	$('body').on('click', '#lcs_admin_chat_submit', function(e){
		e.preventDefault();	

		var chat_id = $('#lcs_admin_chat_id').val();
		
		$.ajax({
			type: "POST",
			cache: false,
			data: "message=" + $('#lcs_admin_chat_text').val(),
			url: sts_base_url + "/simple/livechat_admin_add/" + chat_id + "/",
			success: function(html){
				$('#lcs_admin_chat_text').val('');
			}
		 });
		 		 
		 return false;
	});

});