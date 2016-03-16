function lcs_start_chat() {
	$('div#lcs').css("height", "380px");
	$('div#lcs').css("width", "275px");

	$('#lcs').html(''
	+ '<iframe frameBorder="0" id="lcs_frame" src="' + sts_base_url + '/simple/livechat/' + '"></iframe>'
	+ '<div id="lcs_chat_links"><a href="#" id="lcs_hide_chat">Hide Chat</a></div>'
	+ '');

	$('div#lcs a').css("color", "#000");

};

$(document).ready(function () {

	$('#lcs').html('<div id="lcs_chat_links"><a href="#" id="lcs_start_chat">Start Chat</a></div>');


	if ($.cookie("lcs_on") == 1) {
		lcs_start_chat();
	}

	$('body').on('click', '#lcs_start_chat', function(e){
		e.preventDefault();
		$.cookie("lcs_on", 1);
		lcs_start_chat();
	});

	$('body').on('click', '#lcs_full_chat', function(e){
		e.preventDefault();
		$('div#lcs').css("width", "100%");
		$('div#lcs').css("height", "100%");
		$('#lcs_frame').css("height", "95%");
		$('#lcs_frame').css("width", "95%");
		$('#lcs_frame').css("float", "left");
		$('#lcs_frame').css("padding", "10px");
	});
	
	$('body').on('click', '#lcs_hide_chat', function(e){
		e.preventDefault();
		$.cookie("lcs_on", 0);
		$('div#lcs').css("height", "30px");
		$('div#lcs').css("width", "275px");
		$('#lcs').html('<div id="lcs_chat_links"><a href="#" id="lcs_start_chat">Show Chat</a></div>');
	});
	
});
