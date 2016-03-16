function nl2br (str, is_xhtml) {   
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';    
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1'+ breakTag +'$2');
}

//this function gets the json array and updates the html
function receive_messages() {

	var current_admin = 0;
	var current_guest = 0;
	
	$.ajax({
		type: "GET",
		cache: false,
		dataType: "json",
		url: sts_base_url + "/simple/livechat_receive/",
		success: function(data){
			if (data !== null) {
				if (data.success) {
					if (data.messages.length > 0) {
				
						//html = '<ul>';
						html = '';
						
						$.each(
							data.messages,
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
						
						//html += '</ul>';
						
						$('#lcs_receive').html(html);		
						
						if (data.messages.length > lcs_current_messages) {
							$('#lcs_receive').scrollTop($('#lcs_receive').prop("scrollHeight"));
						}

						if (current_admin > lcs_current_admin_messages) {
							var audio = new Audio(sts_base_url + '/user/plugins/livechat/audio/alert1.wav');
							audio.play();
						}
						
						lcs_current_messages = data.messages.length;
						lcs_current_admin_messages = current_admin;
					}
					else {
						html = '<p>No Messages<hr /></p>';

						$('#lcs_receive').html(html);
					}
				}
				else {
					$('#lcs_receive').html(data.message);
				}
			}
		}
	});
	 
	setTimeout(function() {
		receive_messages()
	}, 3000);
};

var lcs_current_messages = 0;
var lcs_current_guest_messages = 0;
var lcs_current_admin_messages = 0;

//this gets the main HTML
function get_html() {

	$.ajax({
		type: "GET",
		cache: false,
		url: sts_base_url + "/simple/livechat_html/",
		success: function(html){
			$('#lcs_content').html(html);
			$('#lcs_receive').html('Loading...');

			if ($('#lcs_receive').length) {
				receive_messages();
			}
		}
	 });
};

function isValidEmailAddress(emailAddress) {
    var pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
    return pattern.test(emailAddress);
};

$(document).ready(function () {

	get_html();
	
	//process login form
	$('#lcs_body').on("submit", '#lcs_login_form', function (e) {
		e.preventDefault();	

		data = {};
		data.name = $('#lcs_login_name').val();
		data.email = $('#lcs_login_email').val();
		
		if (data.email != '' && !isValidEmailAddress(data.email)) {
			$('#lcs_message').html("<p>Email Invalid</p>");
		}
		else if (data.name == '') {
			$('#lcs_message').html("<p>Name Empty</p>");
		}
		else {	
			$.ajax({
				type: "POST",
				cache: false,
				data: {data:JSON.stringify(data)},
				url: sts_base_url + "/simple/livechat_html/",
				success: function(html){
					$('#lcs_message').html('');
					$('#lcs_content').html(html);
					get_html();
				}
			 });
		}
		 
		return false;
			 
	});
	
	//send a message
	$('#lcs_body').on("submit", '#lcs_chat_form', function (e) {
		e.preventDefault();	

		data = {};
		data.text = $('#lcs_chat_text').val();
				
		$.ajax({
			type: "POST",
			cache: false,
			data: {data:JSON.stringify(data)},
			url: sts_base_url + "/simple/livechat_send/",
			success: function(html){
				$('#lcs_chat_text').val('');
			}
		 });
		 		 
		 return false;
	});
	
});
