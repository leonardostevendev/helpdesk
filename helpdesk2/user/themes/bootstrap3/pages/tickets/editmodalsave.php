<?php
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;


$id = (int) $url->get_item();

if ($id == 0) {
	echo 'Error';
	exit;
}

if ($auth->can('manage_tickets') || $auth->can('tickets_view_assigned_department')) {

}
else {
	echo 'Error';
	exit;
}

//admin and global mods
if ($auth->can('manage_tickets')) {
	//all tickets
}
//moderator
else if ($auth->can('tickets_view_assigned_department')) {
	$t_array['department_or_assigned_or_user_id']	= $auth->get('id');
}
//users and user plus
else if ($auth->can('tickets_view_assigned')) {
	//select assigned tickets or personal tickets
	$t_array['assigned_or_user_id'] 		= $auth->get('id');
}
//sub
else {
	//just personal tickets
	$t_array['user_id'] 					= $auth->get('id');
}

$t_array['id']				= $id;
$t_array['get_other_data'] 	= true;
$t_array['limit']			= 1;
$t_array['archived']		= 0;

$tickets_array = $tickets->get($t_array);

if (count($tickets_array) == 1) {
	$ticket = $tickets_array[0];
}
else {
	echo 'Error';
	exit;
}

if (isset($_POST['save'])) {

	$history_array = array(
		'ticket_id'				=> $ticket['id'],
		'by_user_id'			=> $auth->get('id'),
		'date_added'			=> datetime(),
		'type'					=> 'modified',
		'history_description'	=> $language->get('Edited'),
		'ip_address'			=> ip_address()
	);

	$ticket_history->add(
		array(
			'columns' => $history_array
		)
	);
	unset($history_array);
	
	if ($auth->can('manage_tickets') || $auth->can('tickets_transfer_department')) {
		$ticket_edit['department_id'] 		= (int) $_POST['department_id'];	
	}
	
	if ($auth->can('manage_tickets') || $auth->can('tickets_assign_user')) {
		$ticket_edit['assigned_user_id'] 	= (int) $_POST['assigned_user_id'];	
	}
	
	if ($auth->can('manage_tickets') || $auth->can('tickets_change_status')) {
		$ticket_edit['state_id'] 			= (int) $_POST['state_id'];	
		$ticket_edit['priority_id'] 		= (int) $_POST['priority_id'];	
	}

	$ticket_edit['id'] 					= $ticket['id'];	
	
	$tickets->edit($ticket_edit);
	
	$notifications 	= &singleton::get(__NAMESPACE__ . '\notifications');
	
	if ($auth->can('manage_tickets') || $auth->can('tickets_transfer_department')) {
		if ($_POST['department_id'] != $ticket['department_id']) {
			$history_array = array(
				'ticket_id'				=> $ticket['id'],
				'by_user_id'			=> $auth->get('id'),
				'date_added'			=> datetime(),
				'type'					=> 'transferred',
				'history_description'	=> $language->get('Transferred Department'),
				'ip_address'			=> ip_address(),
				'department_id'			=> (int) $_POST['department_id']
			);

			$ticket_history->add(
				array(
					'columns' => $history_array
				)
			);
			unset($history_array);
		
		
			$notifications->new_department_ticket($ticket);
		}
	}
	
	if ($auth->can('manage_tickets') || $auth->can('tickets_assign_user')) {
		if ($_POST['assigned_user_id'] != $ticket['assigned_user_id']) {
		
			if (!empty($_POST['assigned_user_id'])) {
				$history_array = array(
					'ticket_id'				=> $ticket['id'],
					'by_user_id'			=> $auth->get('id'),
					'date_added'			=> datetime(),
					'type'					=> 'assigned',
					'history_description'	=> $language->get('Assigned User'),
					'ip_address'			=> ip_address(),
					'assigned_user_id'		=> (int) $_POST['assigned_user_id']
				);

				$ticket_history->add(
					array(
						'columns' => $history_array
					)
				);
				unset($history_array);
			
				$notifications->ticket_assigned_user($ticket);
			}
		}
	}
		

	
}