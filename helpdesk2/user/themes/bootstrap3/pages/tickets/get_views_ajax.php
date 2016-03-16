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
	
	$items = $ticket_views->get_views(array('ticket_id' => $ticket['id'], 'user_id' => $auth->get('id'), 'max_age' => $config->get('ticket_views_max_age')));
		
	$output 			= array();
	$output['message'] 	= $language->get('Users viewing this ticket: ');
	$output['users']	= array();
	
	foreach($items as $item) {
		$output['users'][] = array(
			'name' 				=> safe_output($item['name']), 
			'id' 				=> (int) $item['user_id'], 
			'time_ago_in_words'	=> safe_output($language->get('~')) . safe_output(time_ago_in_words($item['date_added'])) . ' ' . safe_output($language->get('ago')) 
		);
	}
	
	echo json_encode($output);
	exit;
	
}
else {
	echo 'Error';
	exit;
}

?>