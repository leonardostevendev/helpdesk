<?php
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$id = (int) $url->get_item();

$access_key 	= '';
if (isset($_GET['access_key'])) {
	$access_key	= $_GET['access_key'];
}

if ($id == 0 && !empty($access_key)) {
	header('Location: ' . $config->get('address') . '/guest/');
	exit;
}

$t_array['id']				= $id;
$t_array['limit']			= 1;
$t_array['get_other_data']	= true;
$t_array['access_key']		= $access_key;

$tickets_array = $tickets->get($t_array);

$close_ticket = false;
if (isset($_POST['close_ticket']) && ($_POST['close_ticket'] == 1)) {
	$close_ticket = true;
}

if (count($tickets_array) == 1) {
	$ticket = $tickets_array[0];
	
	$history_array['ticket_id'] 			= (int) $ticket['id'];
	$history_array['by_user_id'] 			= $auth->get('id');
	$history_array['date_added'] 			= datetime();
	$history_array['ip_address'] 			= ip_address();

			
	//reopen ticket if is not already active
	if (!$close_ticket && !$ticket['active']) {
		$open_array['state_id']				= 1;
		$open_array['id']					= (int) $ticket['id'];
		$open_array['date_state_changed']	= datetime();
		
		$tickets->edit($open_array);
				
		$history_array['state_id'] 				= 1;		
		$history_array['type'] 					= 'status';
		$history_array['history_description'] 	= $language->get('Auto Status Changed (via Guest Portal)');
			
		$ticket_history->add(
			array(
				'columns' => $history_array
			)
		);	
		unset($history_array['state_id']);		
		
	}
	
	//close ticket
	if ($close_ticket) {
		$open_array['state_id']				= 2;
		$open_array['id']					= (int) $ticket['id'];
		$open_array['date_state_changed']	= datetime();
		$tickets->edit($open_array);
		
		$history_array['state_id'] 				= 2;		
		$history_array['type'] 					= 'status';
		$history_array['history_description'] 	= $language->get('Status Changed (via Guest Portal)');
			
		$ticket_history->add(
			array(
				'columns' => $history_array
			)
		);	
		unset($history_array['state_id']);		
		
	}
	
	$files_upload = array();
	if ($config->get('storage_enabled')) {
		if (isset($_FILES['file']) && is_array($_FILES['file'])) {
			$files_array = rearrange($_FILES['file']);	
			foreach($files_array as $file) {
				if ($file['size'] > 0) {
					$file_array['file']			= $file;
					$file_array['name']			= $file['name'];		
					$file_id 					= $storage->upload($file_array);		
					if ($file_id) {
						$files_upload[] 		= $file_id;
						$storage->add_file_to_ticket(array('file_id' => $file_id, 'ticket_id' => (int) $ticket['id']));
						unset($file_id);
					}
				}
			}
		}
	}

	$note['description'] 	= $_POST['description'];
	$note['ticket_id'] 		= (int) $ticket['id'];
	$note['html']			= 0;
	$note['private']		= 0;

	//$note['user_id']		= $ticket['user_id'];
	
	if ($config->get('html_enabled')) {
		$note['html'] 		= 1;
	}	
	
	if (!empty($files_upload)) {
		$note['attached_file_ids']	= $files_upload;
	}
	
	$history_array['type'] 					= 'reply';
	$history_array['history_description'] 	= $language->get('Reply Added (via Guest Portal)');
		
	$ticket_history->add(
		array(
			'columns' => $history_array
		)
	);	
	
	$ticket_notes->add($note);
	
	
	header('Location: ' . $config->get('address') . '/guest/ticket_view/' . $ticket['id'] . '/?access_key='. safe_output($ticket['access_key']).'#addnote');
}
else {
	header('Location: ' . $config->get('address') . '/guest/');
}




?>