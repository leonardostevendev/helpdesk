<?php
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

if ($auth->can('manage_tickets') || $auth->can('manage_users') || $auth->can('tickets_view_assigned_department')) {

	if (isset($_GET['department_id']) && !empty($_GET['department_id'])) {
			
		$allowed = false;
		
		if ($auth->can('manage_tickets') || $auth->can('manage_users')) {
			$allowed = true;
		}
		else {
			$allowed_user = $users->get(array('department_id' => (int) $_GET['department_id'], 'id' => $auth->get('id')));
			if (!empty($allowed_user)) {
				$allowed = true;
			}
		}
		
		if ($allowed) {
		
			$get_array = array();
			
			$get_array['department_id']	= (int) $_GET['department_id'];
			
			if (isset($_GET['company_id']) && ((int) $_GET['company_id'] != 0)) {
				$get_array['company_id']	= (int) $_GET['company_id'];
			}
			
			if (isset($_GET['assigned_users']) && ($_GET['assigned_users'] == true)) {
				//all users except subs
				$get_array['where_can_or']	= array('manage_tickets', 'tickets_view_assigned', 'tickets_view_assigned_department');
			}

			$users = $users->get($get_array);

			$output = array();
			foreach($users as $user) {
				$output[] = array(
							'name'	=> safe_output(ucwords($user['name'])),
							'id'	=> (int) $user['id']
						);
			}

			echo json_encode($output);
		}
		else {
			echo json_encode(array());
		}

	}
	else {
		$get_array = array();
		if ($auth->can('manage_tickets') || $auth->can('manage_users')) {

		}
		else {
			$departments 	= $ticket_departments->get(array('get_other_data' => true, 'user_id_is_member' => $auth->get('id')));
			
			$d_ids			= array();
			foreach($departments as $d_id) {
				$d_ids[]	= $d_id['id'];
			}
						
			$get_array['department_ids']	= $d_ids;
			
			if (empty($d_ids)) {
				echo json_encode(array());
				exit;
			}
		}
		
		if (isset($_GET['company_id']) && ((int) $_GET['company_id'] != 0)) {
			$get_array['company_id']	= (int) $_GET['company_id'];
		}
		
		if (isset($_GET['assigned_users']) && ($_GET['assigned_users'] == true)) {
			//all users except subs
			$get_array['where_can_or']	= array('manage_tickets', 'tickets_view_assigned', 'tickets_view_assigned_department');
		}
		
				
		$users = $users->get($get_array);

		$output = array();
		foreach($users as $user) {
			$output[] = array(
						'name'	=> safe_output(ucwords($user['name'])),
						'id'	=> (int) $user['id']
					);
		}

		echo json_encode($output);
	}
}
else {
	echo json_encode(array());
}
?>