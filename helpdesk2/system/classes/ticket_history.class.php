<?php
/**
 * 	Files to Tickets
 *	Copyright Dalegroup Pty Ltd 2012
 *	support@dalegroup.net
 *
 *
 * @package     sts
 * @author      Michael Dale <mdale@dalegroup.net>
 */

namespace sts;

class ticket_history extends table_access {

	private $table_name 		= NULL;
	private $allowed_columns 	= NULL;

	function __construct() {
	
		$this->set_table('ticket_history');

		$this->allowed_columns(
				array(
					'ticket_id',
					'type',
					'ip_address',
					'date_added',
					'by_user_id',
					'history_description',
					'reply',
					'subject',
					'description',
					'user_id',
					'priority_id',
					'state_id',
					'assigned_user_id',
					'name',
					'email',
					'merge_ticket_id',
					'submitted_user_id',
					'department_id',
					'company_id',
					'project_id',
					'cc',
					'private',
					'pop_account_id',
					'date_due'
				)
			);
		$this->table_name = $this->get_table();
		$this->allowed_columns	= $this->get_allowed_columns();
		
		$plugins 		= &singleton::get(__NAMESPACE__ . '\plugins');	
		
		$plugins->add(
			array(
				'plugin_name'	=> __CLASS__,
				'task_name'		=> __CLASS__ . '_query_table_access_ticket_history_get_other_data_columns',
				'section'		=> 'query_table_access_ticket_history_get_other_data_columns',
				'method'		=> array($this, 'query_table_access_ticket_history_get_other_data_columns')
			)
		);	
		
		$plugins->add(
			array(
				'plugin_name'	=> __CLASS__,
				'task_name'		=> __CLASS__ . '_query_table_access_ticket_history_get_other_data_join',
				'section'		=> 'query_table_access_ticket_history_get_other_data_join',
				'method'		=> array($this, 'query_table_access_ticket_history_get_other_data_join')
			)
		);	

	}
	
	public function query_table_access_ticket_history_get_other_data_columns(&$query) {
		
		$query .= ', ticket_history_u.name as `ticket_history_user_name`';
		
		$query .= ", u2.name AS `assigned_name`";
		$query .= ", tp.name AS `priority_name`";
		$query .= ", td.name AS `department_name`";
		$query .= ", ts.name AS `status_name`, ts.colour  AS `status_colour`, ts.active AS `active`";
		$query .= ", pa.name AS `pop_account_name`";
				
	}
	
	public function query_table_access_ticket_history_get_other_data_join(&$query) {
		$tables 		= &singleton::get(__NAMESPACE__ . '\tables');

		$query .= " LEFT JOIN $tables->users ticket_history_u ON tan.by_user_id = ticket_history_u.id";
	
		$query .= " LEFT JOIN $tables->users u2 ON u2.id = tan.assigned_user_id";
		$query .= " LEFT JOIN $tables->ticket_priorities tp ON tp.id = tan.priority_id";
		$query .= " LEFT JOIN $tables->ticket_departments td ON td.id = tan.department_id";
		$query .= " LEFT JOIN $tables->ticket_status ts ON ts.id = tan.state_id";
		$query .= " LEFT JOIN $tables->pop_accounts pa ON pa.id = tan.pop_account_id";
	
	}
	
	public function day_users($array = NULL) {
		global $db;
		
		$tables 		= &singleton::get(__NAMESPACE__ . '\tables');
		$error 			= &singleton::get(__NAMESPACE__ . '\error');
		$site_id		= SITE_ID;
		
		$query = "SELECT count(th.id) AS `count`, u.name as `full_name`";
		
		$query .= "FROM $tables->ticket_history th, $tables->users u WHERE 1 = 1 AND th.site_id = :site_id AND th.by_user_id = u.id";
		
		if (isset($array['get_all']) && ($array['get_all'] == true)) {
		
		}
		else {
			$query .= ' AND th.date_added >= SUBDATE(DATE_FORMAT(NOW(), "%Y-%m-%d"), INTERVAL :days DAY)';
		}
		
		$query .= " GROUP BY th.by_user_id";
		
		$query .= " ORDER BY `count` DESC";
		
		if (isset($array['get_all']) && ($array['get_all'] == true)) {
		
		}
		else {
			if (isset($array['limit'])) {
				$query .= " LIMIT " . (int) $array['limit'];
			}
		}
		
		//echo $query . '<br />';

		try {
			$stmt = $db->prepare($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);
		if (isset($array['get_all']) && ($array['get_all'] == true)) {
		
		}
		else {
			$stmt->bindParam(':days', $array['days'], database::PARAM_INT);
		}
		
		try {
			$stmt->execute();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$stats = $stmt->fetchAll(database::FETCH_ASSOC);
				
		return $stats;
	}
	
	public function month_users($array = NULL) {
		global $db;
		
		$tables 		= &singleton::get(__NAMESPACE__ . '\tables');
		$error 			= &singleton::get(__NAMESPACE__ . '\error');
		$site_id		= SITE_ID;
		
		$query = "SELECT count(th.id) AS `count`, u.name as `full_name`";
		
		$query .= "FROM $tables->ticket_history th, $tables->users u WHERE 1 = 1 AND th.site_id = :site_id AND th.by_user_id = u.id";
		
		if (isset($array['get_all']) && ($array['get_all'] == true)) {
		
		}
		else {
			$query .= ' AND th.date_added >= SUBDATE(DATE_FORMAT(NOW(), "%Y-%m-01"), INTERVAL :months MONTH)';
		}
		
		$query .= " GROUP BY th.by_user_id";
		
		$query .= " ORDER BY `count` DESC";
		
		if (isset($array['get_all']) && ($array['get_all'] == true)) {
		
		}
		else {
			if (isset($array['limit'])) {
				$query .= " LIMIT " . (int) $array['limit'];
			}
		}
		
		//echo $query . '<br />';

		try {
			$stmt = $db->prepare($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);
		if (isset($array['get_all']) && ($array['get_all'] == true)) {
		
		}
		else {
			$stmt->bindParam(':months', $array['months'], database::PARAM_INT);
		}
		
		try {
			$stmt->execute();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$stats = $stmt->fetchAll(database::FETCH_ASSOC);
				
		return $stats;
	}

}


?>