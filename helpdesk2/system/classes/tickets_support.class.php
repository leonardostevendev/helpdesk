<?php
/**
 * 	Tickets Support Class
 *	Copyright Dalegroup Pty Ltd 2012
 *	support@dalegroup.net
 *
 *
 * @package     dgx
 * @author      Michael Dale <mdale@dalegroup.net>
 */

 
namespace sts;

class tickets_support {

	function __construct() {

	}
	
	/*
	 *	Check if a user can access the current ticket
	 */
	function can($array) {
	
		if (!isset($array['action'])) return false;
		
		switch($array['action']) {
			case 'view':
				$return = $this->can_view(array('id' => $array['id']));
			break;
			
			default:
				$return = false;
			break;
		}
		
		return $return;

	}
	
	private function can_view($array){
		$auth 				=	&singleton::get(__NAMESPACE__ . '\auth');	
		$tickets 			=	&singleton::get(__NAMESPACE__ . '\tickets');
		
		//admin and global mods
		if ($auth->can('manage_tickets')) {
			//all tickets
		}
		//moderator
		else if ($auth->can('tickets_view_assigned_department')) {
			$get_array['department_or_assigned_or_user_id']	= $auth->get('id');
		}
		//users and user plus
		else if ($auth->can('tickets_view_assigned')) {
			//select assigned tickets or personal tickets
			$get_array['assigned_or_user_id'] 		= $auth->get('id');
		}
		//sub
		else {
			$get_array['user_id'] 					= $auth->get('id');
		}
		
		$get_array['count']		= true;		
		$get_array['id']		= (int) $array['id'];
		$get_array['archived']	= 0;


		$result = $tickets->get($get_array);
				
		if (!empty($result) && ($result[0]['count'] != 0)) {
			return true;
		}
		else {
			return false;
		}	
	}
	
	
}


?>