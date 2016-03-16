<?php
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$id = (int) $url->get_item();

if (!$auth->can('manage_tickets') && !$auth->can('tickets')) {
	header('Location: ' . $config->get('address') . '/');
	exit;
}

if ($auth->can('manage_tickets')) {
	//all tickets
}
else if ($auth->can('tickets_view_assigned_department')) {
	//mod
	$t_array['department_or_assigned_or_user_id']	= $auth->get('id');
}
else if ($auth->can('tickets_view_assigned')) {
	//select assigned tickets or personal tickets
	$t_array['assigned_or_user_id'] 		= $auth->get('id');
}
else {
	//just personal tickets
	$t_array['user_id'] 					= $auth->get('id');
}

$t_array['get_other_data']	= true;
$t_array['id']				= $id;
$t_array['archived']		= 0;

$tickets_array = $tickets->get($t_array);

if (count($tickets_array) == 1) {
	$ticket = $tickets_array[0];
	
	if (isset($_POST['add'])) {
	
		$history_array['ticket_id'] 			= (int) $ticket['id'];
		$history_array['by_user_id'] 			= $auth->get('id');
		$history_array['date_added'] 			= datetime();
		$history_array['ip_address'] 			= ip_address();

		//change ticket state
		if (isset($_POST['action_id']) && !empty($_POST['action_id'])) {
			
			if ($auth->can('manage_tickets') || $auth->can('tickets_change_status')) {
				$open_array['state_id']				= (int) $_POST['action_id'];
				$history_array['state_id'] 			= (int) $_POST['action_id'];
			}
			else {
				$open_array['state_id']				= 2;
				$history_array['state_id'] 			= 2;		
			}
			
			$open_array['id']					= (int) $ticket['id'];
			$open_array['date_state_changed']	= datetime();
			$tickets->edit($open_array);
			
			$history_array['type'] 					= 'status';
			$history_array['history_description'] 	= $language->get('Status Changed');
				
			$ticket_history->add(
				array(
					'columns' => $history_array
				)
			);
			unset($history_array['state_id']);
			
		}
		else if (($auth->get('id') == $ticket['user_id']) && !$ticket['active']) {
			$open_array['state_id']				= 1;
			$open_array['id']					= (int) $ticket['id'];
			$open_array['date_state_changed']	= datetime();
			
			$tickets->edit($open_array);	

			$history_array['state_id'] 				= 1;		
			$history_array['type'] 					= 'status';
			$history_array['history_description'] 	= $language->get('Auto Status Changed');
				
			$ticket_history->add(
				array(
					'columns' => $history_array
				)
			);	
			unset($history_array['state_id']);			
		}
		
		//assign ticket if not assigned
		if (empty($ticket['assigned_user_id']) && ($auth->get('id') != $ticket['user_id'])) {
			$ticket_array['id']					= (int) $ticket['id'];
			$ticket_array['assigned_user_id']	= $auth->get('id');
			
			$tickets->edit($ticket_array);

			$history_array['assigned_user_id']		= $auth->get('id');			
			$history_array['type'] 					= 'assigned';
			$history_array['history_description'] 	= $language->get('Auto Assigned User');
				
			$ticket_history->add(
				array(
					'columns' => $history_array
				)
			);	
			unset($history_array['assigned_user_id']);
		}	
		
		$files_upload = array();
		if ($config->get('storage_enabled')) {
			if (isset($_FILES['file']) && is_array($_FILES['file'])) {
				$files_array = rearrange($_FILES['file']);	
				foreach($files_array as $file) {
					if ($file['size'] > 0) {
						$file_array['file']			= $file;
						$file_array['name']			= $file['name'];
						$file_array['user_id']		= $auth->get('id');	
						$file_id 					= $storage->upload($file_array);		
						if ($file_id) {
							$files_upload[] 		= $file_id;
							
							/*
							$storage->add_file_to_ticket(
								array(
									'file_id' 	=> $file_id, 
									'ticket_id' 	=> (int) $ticket['id'],
									'private'		=> $_POST['private'] ? 1 : 0
								)
							);
							*/
							unset($file_id);
						}
					}
				}
			}
		}

		//add note!
		$note['private']		= $_POST['private'] ? 1 : 0;
		$note['description'] 	= $_POST['description'];
		$note['ticket_id'] 		= (int) $ticket['id'];
		$note['html']			= 0;
		
		if ($config->get('html_enabled')) {
			$note['html'] 		= 1;
		}	
		
		if ($auth->can('manage_tickets') || $auth->can('tickets_carbon_copy_reply')) {
			if (isset($_POST['cc']) && (!empty($_POST['cc']))) {
				$note['cc']	= $_POST['cc'];
			}
		}
		
		if (!empty($files_upload)) {
			$note['attach_file_ids']	= $files_upload;
		}
	
		$history_array['type'] 					= 'reply';
		$history_array['history_description'] 	= $language->get('Reply Added');
			
		$ticket_history->add(
			array(
				'columns' => $history_array
			)
		);	
			
		
		$note['for_company_id']	= $ticket['company_id'];

		$note['ticket_data']	= $ticket;
		
		$note['name']	=		$auth->get('name');
		
		$plugins->run('submit_add_reply_form_success_before_create_reply', $note);
			
		$ticket_notes->add($note);
		
		//transfer department or assign user
		
		
		if ($auth->can('manage_tickets') || $auth->can('tickets_assign_user') || $auth->can('tickets_transfer_department')) {
		
			$notifications 	= &singleton::get(__NAMESPACE__ . '\notifications');
			
			if (!empty($_POST['department_id2']) || !empty($_POST['assigned_user_id2'])) {
				$transfer_array['id']					= (int) $ticket['id'];
				
				
				if ($auth->can('manage_tickets') || $auth->can('tickets_transfer_department')) {
					if (!empty($_POST['department_id2'])) {
						$transfer_array['department_id'] 		= (int) $_POST['department_id2'];	
					}
				}
					
				if ($auth->can('manage_tickets') || $auth->can('tickets_assign_user')) {
					if (!empty($_POST['assigned_user_id2'])) {
						$transfer_array['assigned_user_id'] 	= (int) $_POST['assigned_user_id2'];	
					}
				}
				
				$tickets->edit($transfer_array);
				
				if ($auth->can('manage_tickets') || $auth->can('tickets_transfer_department')) {				
					if (isset($_POST['department_id2']) && ($_POST['department_id2'] ==! '')) {
						if ($_POST['department_id2'] !== $ticket['department_id']) {
							$notifications->new_department_ticket($ticket);
							
							$history_array['department_id']			= (int) $_POST['department_id2'];			
							$history_array['type'] 					= 'transferred';
							$history_array['history_description'] 	= $language->get('Transferred Department');
								
							$ticket_history->add(
								array(
									'columns' => $history_array
								)
							);	
							unset($history_array['department_id']);						
						}
					}
				}
				
				if ($auth->can('manage_tickets') || $auth->can('tickets_assign_user')) {
					if ($_POST['assigned_user_id2'] != $ticket['assigned_user_id']) {
						$notifications->ticket_assigned_user($ticket);
						
						$history_array['assigned_user_id']		= (int) $_POST['assigned_user_id2'];			
						$history_array['type'] 					= 'assigned';
						$history_array['history_description'] 	= $language->get('Assigned User');
							
						$ticket_history->add(
							array(
								'columns' => $history_array
							)
						);
						unset($history_array['assigned_user_id']);
					}
				}
			}
		}
	}
	
	header('Location: ' . $config->get('address') . '/tickets/view/' . $ticket['id'] . '/#addnote');
}
else {
	header('Location: ' . $config->get('address') . '/tickets/');
}




?>