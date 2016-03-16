<?php
/**
 * 	Ticket Views
 *	Copyright Dalegroup Pty Ltd 2012
 *	support@dalegroup.net
 *
 *
 * @package     sts
 * @author      Michael Dale <mdale@dalegroup.net>
 */

namespace sts;

class ticket_views extends table_access {

	private $table_name 		= NULL;
	private $allowed_columns 	= NULL;

	function __construct() {
	
		$this->set_table('ticket_views');
		$this->allowed_columns(
				array(
					'ticket_id',
					'user_id',
					'date_added'
				)
			);
		$this->table_name = $this->get_table();
		$this->allowed_columns	= $this->get_allowed_columns();

	}
	
	public function get_views($array) {
		global $db;
				
		$error 			= &singleton::get(__NAMESPACE__ . '\error');
		$log			= &singleton::get(__NAMESPACE__ . '\log');
		$config 		= &singleton::get(__NAMESPACE__ . '\config');
		$tables 		= &singleton::get(__NAMESPACE__ . '\tables');

		$site_id		= SITE_ID;

		
		$query = "
			SELECT 
				u.name AS `name`, u.id AS `user_id`, MAX(tan.date_added) AS `date_added`
			FROM 
				$this->table_name tan 
			LEFT JOIN
				$tables->users u ON tan.user_id = u.id 
			WHERE 
				tan.ticket_id = :ticket_id 
				AND tan.date_added >= :date_added
				AND tan.site_id = :site_id 
				AND tan.user_id != :user_id
			GROUP BY tan.user_id
		";
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));		
		}
		
		$max_age = 15;
		if (isset($array['max_age'])) {
			$max_age = (int) $array['max_age'];
		}
		
		$date_added = datetime(-$max_age);
		
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);
		$stmt->bindParam(':ticket_id', $array['ticket_id'], database::PARAM_INT);
		$stmt->bindParam(':user_id', $array['user_id'], database::PARAM_INT);
		$stmt->bindParam(':date_added', $date_added, database::PARAM_STR);

		try {
			$stmt->execute();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$items = $stmt->fetchAll(database::FETCH_ASSOC);
		
		return $items;	
		
	
	}
	
	public function prune() {
		global $db;
		
		$tables 		= &singleton::get(__NAMESPACE__ . '\tables');
		$error 			= &singleton::get(__NAMESPACE__ . '\error');
		$config 		= &singleton::get(__NAMESPACE__ . '\config');
		$log 			= &singleton::get(__NAMESPACE__ . '\log');

		$site_id		= SITE_ID;
					
		$query = "DELETE FROM $this->table_name WHERE date_added < :date_added AND site_id = :site_id";
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		//2 days ago
		$date_added = datetime(-172800);
		
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);
		$stmt->bindParam(':date_added', $date_added, database::PARAM_STR);

		try {
			$stmt->execute();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
	
		$log_array['event_severity'] = 'notice';
		$log_array['event_number'] = E_USER_NOTICE;
		$log_array['event_description'] = 'Ticket Views auto prune has finished.';
		$log_array['event_file'] = __FILE__;
		$log_array['event_file_line'] = __LINE__;
		$log_array['event_type'] = 'prune';
		$log_array['event_source'] = 'ticket_views';	
		$log_array['event_version'] = '1';
		$log_array['log_backtrace'] = false;	
				
		$log->add($log_array);
	
	}

}


?>