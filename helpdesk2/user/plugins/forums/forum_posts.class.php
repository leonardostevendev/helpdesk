<?php
/**
 * 	Forum Threads
 *	Copyright Dalegroup Pty Ltd 2012
 *	support@dalegroup.net
 *
 *
 * @package     sts
 * @author      Michael Dale <mdale@dalegroup.net>
 */

namespace sts\plugins;
use sts;

class forum_posts extends sts\table_access {

	private $table_name 		= NULL;
	private $allowed_columns 	= NULL;

	function __construct() {
	
		$this->set_table('forum_posts');
		$this->allowed_columns(
				array(
					'message',
					'user_id',
					'thread_id',
					'date_added',
					'id'
				)
			);
		$this->table_name = $this->get_table();
		$this->allowed_columns	= $this->get_allowed_columns();

	}
	
	public function get($array = NULL) {
		global $db;
		
		$error 			= &sts\singleton::get('sts\error');
		$tables 		= &sts\singleton::get('sts\tables');
		$table 			= $this->get_table();

		$site_id		= sts\SITE_ID;


		$query = "SELECT fp.* ";
		
		if (isset($array['get_other_data']) && ($array['get_other_data'] == true)) {
			$query .= ", u.name AS `name`, u.email AS `email`";


			
			/*
			$query .= ", u.pushover_key AS `owner_pushover_key`, u.name AS `owner_name`, u.id AS `owner_id`, u.email AS `owner_email`, u.phone_number AS `owner_phone`, u.email_notifications AS `owner_email_notifications`";
			$query .= ", u2.pushover_key AS `assigned_pushover_key`, u2.name AS `assigned_name`, u2.id AS `assigned_id`, u2.email AS `assigned_email`, u2.email_notifications AS `assigned_email_notifications`";
			$query .= ", u3.name AS `submitted_name`, u3.id AS `submitted_id`, u3.email AS `submitted_email`, u3.email_notifications AS `submitted_email_notifications`";
			
			$query .= ", tp.name AS `priority_name`";
			$query .= ", td.name AS `department_name`";
			$query .= ", ts.name AS `status_name`, ts.colour  AS `status_colour`, ts.active AS `active`";
			$query .= ", pa.name AS `pop_account_name`";
			*/
		}
		
		$query .= " FROM $table fp";
		
		if (isset($array['get_other_data']) && ($array['get_other_data'] == true)) {
			$query .= " LEFT JOIN $tables->users u ON fp.user_id = u.id";

			/*
				$query .= " LEFT JOIN $tables->users u ON u.id = t.user_id";
				$query .= " LEFT JOIN $tables->users u2 ON u2.id = t.assigned_user_id";
				$query .= " LEFT JOIN $tables->users u3 ON u3.id = t.submitted_user_id";
				
				$query .= " LEFT JOIN $tables->ticket_priorities tp ON tp.id = t.priority_id";
				$query .= " LEFT JOIN $tables->ticket_departments td ON td.id = t.department_id";
				$query .= " LEFT JOIN $tables->ticket_status ts ON ts.id = t.state_id";
				$query .= " LEFT JOIN $tables->pop_accounts pa ON pa.id = t.pop_account_id";
			*/

		}
		
		$query .= " WHERE 1 = 1 AND fp.site_id = :site_id";
		
		if (isset($array['id'])) {
			$query .= " AND fp.id = :id";
		}
		
		if (isset($array['where'])) {
			foreach($array['where'] as $index => $value) {
				if (in_array($index, $this->allowed_columns)) {
					$query .= ' AND fp.'.$index.' = :'.$index;
					unset($index);
					unset($value);
				}
			}
		}
		
		if (isset($array['like'])) {
			$query .= ' AND (';
			foreach($array['like'] as $index => $value) {
				if (in_array($index, $this->allowed_columns)) {
					$query .= 'fp.'.$index.' LIKE :'.$index . ' OR ';
					unset($index);
					unset($value);
				}
			}
			
			if(substr($query, -4) == ' OR ') {	
				$query = substr($query, 0, strlen($query) - 4);
			}
			
			$query .= ')';
		}
		
		$query .= " GROUP BY fp.id";
		
		if (isset($array['order_by']) && in_array($array['order_by'], $this->allowed_columns)) {
			if (isset($array['order']) && $array['order'] == 'desc') {
				$query .= ' ORDER BY ' . $array['order_by'] . ' DESC';
			}
			else {
				$query .= ' ORDER BY ' . $array['order_by'];
			}			
		}
		else {
			if (isset($array['order']) && $array['order'] == 'asc') {
				$query .= ' ORDER BY fp.id';
			}
			else {
				$query .= " ORDER BY fp.id DESC";
			}	
		}
				
		if (isset($array['limit'])) {
			$query .= " LIMIT :limit";
			if (isset($array['offset'])) {
				$query .= " OFFSET :offset";
			}
		}
			
		//echo $query;
			
		try {
			$stmt = $db->prepare($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		$stmt->bindParam(':site_id', $site_id, sts\database::PARAM_INT);

	
		if (isset($array['id'])) {
			$stmt->bindParam(':id', $array['id'], sts\database::PARAM_INT);
		}
		
		if (isset($array['where'])) {
			foreach($array['where'] as $index => $value) {
				if (in_array($index, $this->allowed_columns)) {
					$stmt->bindParam(':' . $index, $value);
					unset($index);
					unset($value);
				}
			}
		}

		if (isset($array['like'])) {
			foreach($array['like'] as $index => $value) {
				if (in_array($index, $this->allowed_columns)) {
					$value = "%{$value}%";
					$stmt->bindParam(':' . $index, $value);
					unset($value);
					unset($index);
				}
			}
		}
		
		if (isset($array['limit'])) {
			$limit = (int) $array['limit'];
			if ($limit < 0) $limit = 0;
			$stmt->bindParam(':limit', $limit, sts\database::PARAM_INT);
			if (isset($array['offset'])) {
				$offset = (int) $array['offset'];
				$stmt->bindParam(':offset', $offset, sts\database::PARAM_INT);					
			}
		}	
	
		try {
			$stmt->execute();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$items = $stmt->fetchAll(sts\database::FETCH_ASSOC);
		
		return $items;	
	}



}


?>