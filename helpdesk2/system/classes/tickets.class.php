<?php
/**
 * 	Tickets Class
 *	Copyright Dalegroup Pty Ltd 2015
 *	support@dalegroup.net
 *
 *
 * @package     dgx
 * @author      Michael Dale <support@dalegroup.net>
 */

 
namespace sts;

class tickets {

	function __construct() {

	}

	public function cron_archive() {
		$config 		= &singleton::get(__NAMESPACE__ . '\config');
		$log			= &singleton::get(__NAMESPACE__ . '\log');

		if ($config->get('auto_archive_enabled') && (int) $config->get('auto_archive_months') != 0) {
		
			$date = date('Y-m-d H:i:s', strtotime($config->get('auto_archive_months') . ' months ago', strtotime(datetime())));
			$due_date = date('Y-m-d H:i:s', strtotime('-1 week', strtotime(datetime())));
			
			$tickets_to_archive = $this->get(array('max_date_added' => $date, 'archived' => 0, 'active' => 0, 'max_date_due' => $due_date, 'get_other_data' => true));
			
			$count = count($tickets_to_archive);
				
			$log_array['event_severity'] = 'notice';
			$log_array['event_number'] = E_USER_NOTICE;
			$log_array['event_description'] = 'There are "'.count($count).'" tickets to auto archive.';
			$log_array['event_file'] = __FILE__;
			$log_array['event_file_line'] = __LINE__;
			$log_array['event_type'] = 'cron_archive';
			$log_array['event_source'] = 'tickets';	
			$log_array['event_version'] = '1';
			$log_array['log_backtrace'] = false;	
					
			$log->add($log_array);
			
			foreach($tickets_to_archive as $item) {
				$this->edit(array('id' => $item['id'], 'archived' => 1));
			}
			
			$log_array['event_severity'] = 'notice';
			$log_array['event_number'] = E_USER_NOTICE;
			$log_array['event_description'] = 'Tickets to auto archive has completed.';
			$log_array['event_file'] = __FILE__;
			$log_array['event_file_line'] = __LINE__;
			$log_array['event_type'] = 'cron_archive';
			$log_array['event_source'] = 'tickets';	
			$log_array['event_version'] = '1';
			$log_array['log_backtrace'] = false;	
					
			$log->add($log_array);
			
			
		}
		
	}
	
	public function cron_date_due() {
		$notifications 	= &singleton::get(__NAMESPACE__ . '\notifications');
	
		$tomorrow_timestamp = strtotime('+1 day', strtotime(thedate()));
			
		$array = $this->get(array('date_due' => date('Y-m-d', $tomorrow_timestamp), 'get_other_data' => true, 'active' => 1));
		
		foreach($array as $ticket) {
			$notifications->ticket_date_due(array('id' => $ticket['id']));
		}
	
	}
	
	public function add($array) {
		global $db;
				
		$error 			= &singleton::get(__NAMESPACE__ . '\error');
		$log			= &singleton::get(__NAMESPACE__ . '\log');
		$tables 		= &singleton::get(__NAMESPACE__ . '\tables');
		$auth 			= &singleton::get(__NAMESPACE__ . '\auth');
		$config 		= &singleton::get(__NAMESPACE__ . '\config');
		$notifications 	= &singleton::get(__NAMESPACE__ . '\notifications');
		$history 		= &singleton::get(__NAMESPACE__ . '\ticket_history');
		$storage 		= &singleton::get(__NAMESPACE__ . '\storage');
		$language 		= &singleton::get(__NAMESPACE__ . '\language');


		$site_id		= SITE_ID;
		$date_added 	= datetime();
		$last_modified	= datetime();
		$key 			= rand_str(8);
		$access_key 	= rand_str(32);

		$query = "INSERT INTO $tables->tickets (user_id, site_id, date_added, last_modified, `key`, submitted_user_id, date_state_changed, access_key";

		//used for import
		if (isset($array['id'])) {
			$query .= ", id";
		}
		
		if (isset($array['subject'])) {
			$query .= ", subject";
		}		
		if (isset($array['description'])) {
			$query .= ", description";
		}
		if (isset($array['priority_id'])) {
			$query .= ", priority_id";
		}
		if (isset($array['department_id'])) {
			$query .= ", department_id";
		}
		if (isset($array['name'])) {
			$query .= ", name";
		}
		if (isset($array['email'])) {
			$query .= ", email";
		}
		if (isset($array['html'])) {
			$query .= ", html";
		}
		if (isset($array['assigned_user_id'])) {
			$query .= ", assigned_user_id";
		}
		if (isset($array['pop_account_id'])) {
			$query .= ", pop_account_id";
		}
		if (isset($array['state_id'])) {
			$query .= ", state_id";
		}
		if (isset($array['cc'])) {
			$query .= ", cc";
		}
		if (isset($array['email_data'])) {
			$query .= ", email_data";
		}
		if (isset($array['company_id'])) {
			$query .= ", company_id";
		}
		if (isset($array['project_id'])) {
			$query .= ", project_id";
		}
		if (isset($array['date_due'])) {
			$query .= ", date_due";
		}	
		
		$query .= ") VALUES (:user_id, :site_id, :date_added, :last_modified, :key, :submitted_user_id, :date_state_changed, :access_key";
		
		//used for import
		if (isset($array['id'])) {
			$query .= ", :id";
		}
		
		if (isset($array['subject'])) {
			$query .= ", :subject";
		}
		if (isset($array['description'])) {
			$query .= ", :description";
		}
		if (isset($array['priority_id'])) {
			$query .= ", :priority_id";
		}
		if (isset($array['department_id'])) {
			$query .= ", :department_id";
		}
		if (isset($array['name'])) {
			$query .= ", :name";
		}
		if (isset($array['email'])) {
			$query .= ", :email";
		}
		if (isset($array['html'])) {
			$query .= ", :html";
		}	
		if (isset($array['assigned_user_id'])) {
			$query .= ", :assigned_user_id";
		}
		if (isset($array['pop_account_id'])) {
			$query .= ", :pop_account_id";
		}		
		if (isset($array['state_id'])) {
			$query .= ", :state_id";
		}
		if (isset($array['cc'])) {
			$query .= ", :cc";
		}
		if (isset($array['email_data'])) {
			$query .= ", :email_data";
		}
		if (isset($array['company_id'])) {
			$query .= ", :company_id";
		}
		if (isset($array['project_id'])) {
			$query .= ", :project_id";
		}
		if (isset($array['date_due'])) {
			$query .= ", :date_due";
		}	
		
		$query .= ")";
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));		
		}
		
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);
		
		if (isset($array['date_added'])) {
			$stmt->bindParam(':date_added', $array['date_added'], database::PARAM_STR);		
		}
		else {
			$stmt->bindParam(':date_added', $date_added, database::PARAM_STR);
		}
		
		if (isset($array['last_modified'])) {
			$stmt->bindParam(':last_modified', $array['last_modified'], database::PARAM_STR);		
		}
		else {
			$stmt->bindParam(':last_modified', $date_added, database::PARAM_STR);
		}
		
		$stmt->bindParam(':date_state_changed', $date_added, database::PARAM_STR);
		
		if (!isset($array['access_key'])) {
			$array['access_key'] = $access_key;
		}

		$stmt->bindParam(':access_key', $array['access_key'], database::PARAM_STR);
		$stmt->bindParam(':key', $key, database::PARAM_STR);
		
		$submitted_user_id = $auth->get('id');
		$stmt->bindParam(':submitted_user_id', $submitted_user_id, database::PARAM_INT);
		$history_array['submitted_user_id'] = $submitted_user_id;
		
		$array['key']	= $key;
		
		if (isset($array['description'])) {
			$description = $array['description'];
			$stmt->bindParam(':description', $description, database::PARAM_STR);
			$history_array['description'] = $description;
		}
		
		//used for import
		if (isset($array['id'])) {
			$stmt->bindParam(':id', $array['id'], database::PARAM_INT);
		}
		
		if (isset($array['subject'])) {
			$subject = $array['subject'];
			$stmt->bindParam(':subject', $subject, database::PARAM_STR);
			$history_array['subject'] = $subject;
		}
		
		if (isset($array['user_id'])) {
			$user_id = (int) $array['user_id'];
		}
		else {
			$user_id = 0;
		}
		
		$array['user_id']	= $user_id;
		
		$stmt->bindParam(':user_id', $user_id, database::PARAM_INT);
		$history_array['user_id'] = $user_id;
	
		if (isset($array['priority_id'])) {
			$priority_id	= $array['priority_id'];
			$stmt->bindParam(':priority_id', $priority_id, database::PARAM_INT);
			$history_array['priority_id'] = $priority_id;

		}
		if (isset($array['department_id'])) {
			$department_id	= $array['department_id'];
			$stmt->bindParam(':department_id', $department_id, database::PARAM_INT);
			$history_array['department_id'] = $department_id;
		}
		
		if (isset($array['name'])) {
			$name = $array['name'];
			$stmt->bindParam(':name', $name, database::PARAM_STR);
			$history_array['name'] = $name;
		}
		if (isset($array['email'])) {
			$email = $array['email'];
			$stmt->bindParam(':email', $email, database::PARAM_STR);
			$history_array['email'] = $email;
		}
		if (isset($array['html'])) {
			$html = $array['html'];
			$stmt->bindParam(':html', $html, database::PARAM_INT);
		}
		if (isset($array['assigned_user_id'])) {
			$assigned_user_id	= $array['assigned_user_id'];
			$stmt->bindParam(':assigned_user_id', $assigned_user_id, database::PARAM_INT);	
			$history_array['assigned_user_id'] = $assigned_user_id;
		}	
		if (isset($array['pop_account_id'])) {
			$pop_account_id	= $array['pop_account_id'];
			$stmt->bindParam(':pop_account_id', $pop_account_id, database::PARAM_INT);	
			$history_array['pop_account_id'] = $pop_account_id;
		}	
		if (isset($array['state_id'])) {
			$state_id	= $array['state_id'];
			$stmt->bindParam(':state_id', $state_id, database::PARAM_INT);
			$history_array['state_id'] = $state_id;			
		}		
		if (isset($array['cc'])) {
			$cc	= $array['cc'];
			
			$cc = explode(',', $cc);
			$cc = serialize($cc);
			
			$stmt->bindParam(':cc', $cc, database::PARAM_STR);	
			$history_array['cc'] = $cc;			
		}	
		if (isset($array['email_data'])) {
			$email_data	= $array['email_data'];
			
			$email_data = base64_encode(serialize($email_data));
			
			$stmt->bindParam(':email_data', $email_data, database::PARAM_STR);	
		}	
		if (isset($array['company_id'])) {
			$company_id	= $array['company_id'];
			$stmt->bindParam(':company_id', $company_id, database::PARAM_INT);	
			$history_array['company_id'] = $company_id;			
		}
		if (isset($array['project_id'])) {
			$project_id	= $array['project_id'];
			$stmt->bindParam(':project_id', $project_id, database::PARAM_INT);	
			$history_array['project_id'] = $project_id;			
		}

		if (isset($array['date_due'])) {
			$date_due	= $array['date_due'];
			$stmt->bindParam(':date_due', $date_due, database::PARAM_STR);	
			$history_array['date_due'] = $date_due;			
		}	
		
		try {
			$stmt->execute();
			$id = $db->lastInsertId();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$array['id']	= (int) $id;
				
		$log_array['event_severity'] = 'notice';
		$log_array['event_number'] = E_USER_NOTICE;
		$log_array['event_description'] = 'Ticket Added "<a href="'. $config->get('address') .'/tickets/view/'.(int)$id.'/">' . safe_output($array['subject']) . '</a>"';
		$log_array['event_file'] = __FILE__;
		$log_array['event_file_line'] = __LINE__;
		$log_array['event_type'] = 'add';
		$log_array['event_source'] = 'tickets';
		$log_array['event_version'] = '1';
		$log_array['log_backtrace'] = false;	
				
		$log->add($log_array);
		
		if (isset($array['attach_file_ids']) && !empty($array['attach_file_ids'])) {
			foreach($array['attach_file_ids'] as $file_id) {
				if ($file_id !== false) {
					$storage->add_file_to_ticket(array('file_id' => (int) $file_id, 'ticket_id' => $id));
				}
			}
		}
		
		$history_array['ticket_id'] 			= $id;
		
		$history_array['by_user_id']			= $user_id;
		if ($auth->get('id') != 0) {
			$history_array['by_user_id'] 		= $auth->get('id');
		}
		$history_array['date_added'] 			= datetime();
		$history_array['type'] 					= 'created';
		$history_array['history_description'] 	= $language->get('Ticket Created');
		$history_array['ip_address'] 			= ip_address();
			
		$history->add(
			array(
				'columns' => $history_array
			)
		);
		
		$notifications->new_ticket($array);
		$notifications->new_department_ticket($array);
				
		return $id;
		
	}
	
	public function count($array = NULL) {
		global $db;
		
		$tables =	&singleton::get(__NAMESPACE__ . '\tables');
		$error =	&singleton::get(__NAMESPACE__ . '\error');
		$site_id	= SITE_ID;
		$order_array 	= array('date_added', 'state_id', 'priority_id', 'subject', 'user_id', 'company_id', 'assigned_user_id', 'last_modified', 'id', 'date_due');

				
		$query = "SELECT count(*) AS `count`";
		
		if (isset($array['get_other_data']) && ($array['get_other_data'] == true)) {
			$query .= ', t.*';		
		}
		else if (isset($array['group_by']) && in_array($array['group_by'], $order_array)) {
			$query .= ', t.' . $array['group_by'];		
		}			
			
		if (isset($array['get_other_data']) && ($array['get_other_data'] == true)) {
			$query .= ", u.name AS `owner_name`";
			$query .= ", u2.name AS `assigned_name`";
			$query .= ", u3.name AS `submitted_name`";
			$query .= ", tp.name AS `priority_name`";
			$query .= ", td.name AS `department_name`";
			$query .= ", ts.name AS `status_name`, ts.colour  AS `status_colour`, ts.active AS `active`";
			$query .= ", pa.name AS `pop_account_name`";
		}
		
		$query .= " FROM $tables->tickets t";
		
				
		if (isset($array['get_other_data']) && ($array['get_other_data'] == true)) {			
			$query .= " LEFT JOIN $tables->users u ON u.id = t.user_id";
			$query .= " LEFT JOIN $tables->users u2 ON u2.id = t.assigned_user_id";
			$query .= " LEFT JOIN $tables->users u3 ON u3.id = t.submitted_user_id";
			
			$query .= " LEFT JOIN $tables->ticket_priorities tp ON tp.id = t.priority_id";
			$query .= " LEFT JOIN $tables->ticket_departments td ON td.id = t.department_id";
			$query .= " LEFT JOIN $tables->ticket_status ts ON ts.id = t.state_id";
			$query .= " LEFT JOIN $tables->pop_accounts pa ON pa.id = t.pop_account_id";
		}
		
				
		$query .= " WHERE t.site_id = :site_id";
		
		
		
		if (isset($array['id'])) {
			$query .= " AND t.id = :id";
		}
		if (isset($array['user_id'])) {
			$query .= " AND t.user_id = :user_id";
		}
		if (isset($array['assigned_user_id'])) {
			$query .= " AND t.assigned_user_id = :assigned_user_id";
		}
		if (isset($array['state_id'])) {
			$query .= " AND t.state_id = :state_id";
		}
		if (isset($array['priority_id'])) {
			$query .= " AND t.priority_id = :priority_id";
		}
		if (isset($array['department_id'])) {
			$query .= " AND t.department_id = :department_id";
		}
		if (isset($array['company_id'])) {
			$query .= " AND t.company_id = :company_id";
		}
		
		if (isset($array['min_date_added'])) {
			$query .= " AND t.date_added >= :min_date_added";
		}
		if (isset($array['max_date_added'])) {
			$query .= " AND t.date_added <= :max_date_added";		
		}

		if (isset($array['min_date_due'])) {
			$query .= " AND t.date_due >= :min_date_due";		
		}
		if (isset($array['max_date_due'])) {
			$query .= " AND (t.date_due <= :max_date_due OR t.date_due IS NULL)";		
		}		
		
		if (isset($array['archived'])) {
			$query .= " AND t.archived = :archived";		
		}
		else {
			$query .= " AND t.archived = 0";				
		}
		
		
		if (isset($array['get_other_data']) && ($array['get_other_data'] == true) && isset($array['active'])) {
			$query .= " AND ts.active = :active ";
		}
		
		if (isset($array['group_by']) && in_array($array['group_by'], $order_array)) {
			$query .= ' GROUP BY t.' . $array['group_by'];		
		}
		
		//echo $query; 
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);

		if (isset($array['id'])) {
			$id = $array['id'];
			$stmt->bindParam(':id', $id, database::PARAM_INT);
		}
		if (isset($array['get_other_data']) && ($array['get_other_data'] == true) && isset($array['active'])) {
			$stmt->bindParam(':active', $array['active'], database::PARAM_INT);
		}			
		if (isset($array['user_id'])) {
			$user_id = $array['user_id'];
			$stmt->bindParam(':user_id', $user_id, database::PARAM_INT);
		}
		if (isset($array['assigned_user_id'])) {
			$assigned_user_id = $array['assigned_user_id'];
			$stmt->bindParam(':assigned_user_id', $assigned_user_id, database::PARAM_INT);
		}
		if (isset($array['state_id'])) {
			$stmt->bindParam(':state_id', $array['state_id'], database::PARAM_INT);
		}
		
		if (isset($array['priority_id'])) {
			$stmt->bindParam(':priority_id', $array['priority_id'], database::PARAM_INT);
		}
		if (isset($array['department_id'])) {
			$stmt->bindParam(':department_id', $array['department_id'], database::PARAM_INT);
		}
		if (isset($array['company_id'])) {
			$stmt->bindParam(':company_id', $array['company_id'], database::PARAM_INT);
		}	
		if (isset($array['min_date_added'])) {
			$stmt->bindParam(':min_date_added', $array['min_date_added'], database::PARAM_STR);
		}
		if (isset($array['max_date_added'])) {
			$stmt->bindParam(':max_date_added', $array['max_date_added'], database::PARAM_STR);
		}
		if (isset($array['archived'])) {
			$stmt->bindParam(':archived', $array['archived'], database::PARAM_INT);
		}
		
		try {
			$stmt->execute();
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}

		if (isset($array['fetch_all']) && ($array['fetch_all'] == true)) {
			$count = $stmt->fetchAll(database::FETCH_ASSOC);
			
			return $count;		
		}
		else {
			$count = $stmt->fetch(database::FETCH_ASSOC);
			
			return (int) $count['count'];
		}
	}
	
	public function edit($array) {
		global $db;
		
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$log		= &singleton::get(__NAMESPACE__ . '\log');
		$config		= &singleton::get(__NAMESPACE__ . '\config');

		$site_id			= SITE_ID;
		$last_modified 		= datetime();	
		
		$query = "UPDATE $tables->tickets SET last_modified = :last_modified";

		if (isset($array['subject'])) {
			$query .= ", subject = :subject";
		}
		if (isset($array['description'])) {
			$query .= ", description = :description";
		}
		if (isset($array['priority_id'])) {
			$query .= ", priority_id = :priority_id";
		}
		if (isset($array['state_id'])) {
			$query .= ", state_id = :state_id";
		}
		if (isset($array['assigned_user_id'])) {
			$query .= ", assigned_user_id = :assigned_user_id";
		}
		if (isset($array['department_id'])) {
			$query .= ", department_id = :department_id";
		}
		if (isset($array['key'])) {
			$query .= ", key = :key";
		}
		if (isset($array['html'])) {
			$query .= ", html = :html";
		}
		if (isset($array['date_state_changed'])) {
			$query .= ", date_state_changed = :date_state_changed";
		}
		if (isset($array['merge_ticket_id'])) {
			$query .= ", `merge_ticket_id` = :merge_ticket_id";
		}
		if (isset($array['company_id'])) {
			$query .= ", `company_id` = :company_id";
		}
		if (isset($array['user_id'])) {
			$query .= ", `user_id` = :user_id";
		}		
		if (isset($array['date_due'])) {
			$query .= ", `date_due` = :date_due";
		}	
		if (isset($array['archived'])) {
			$query .= ", `archived` = :archived";
		}	
		$query .= " WHERE id = :id AND site_id = :site_id";
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		$stmt->bindParam(':id', $array['id'], database::PARAM_INT);
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);
		$stmt->bindParam(':last_modified', $last_modified, database::PARAM_STR);


		if (isset($array['subject'])) {
			$stmt->bindParam(':subject', $array['subject'], database::PARAM_STR);
		}	
		if (isset($array['description'])) {
			$stmt->bindParam(':description', $array['description'], database::PARAM_STR);
		}	
		if (isset($array['priority_id'])) {
			$stmt->bindParam(':priority_id', $array['priority_id'], database::PARAM_INT);
		}
		if (isset($array['state_id'])) {
			$stmt->bindParam(':state_id', $array['state_id'], database::PARAM_INT);
		}
		if (isset($array['assigned_user_id'])) {
			$stmt->bindParam(':assigned_user_id', $array['assigned_user_id'], database::PARAM_INT);
		}
		if (isset($array['department_id'])) {
			$stmt->bindParam(':department_id', $array['department_id'], database::PARAM_INT);
		}
		if (isset($array['key'])) {
			$stmt->bindParam(':key', $array['key'], database::PARAM_INT);
		}
		if (isset($array['html'])) {
			$stmt->bindParam(':html', $array['html'], database::PARAM_INT);
		}		
		if (isset($array['date_state_changed'])) {
			$stmt->bindParam(':date_state_changed', $array['date_state_changed'], database::PARAM_STR);
		}
		if (isset($array['merge_ticket_id'])) {
			$stmt->bindParam(':merge_ticket_id', $array['merge_ticket_id'], database::PARAM_INT);
		}
		if (isset($array['company_id'])) {
			$stmt->bindParam(':company_id', $array['company_id'], database::PARAM_INT);
		}
		if (isset($array['user_id'])) {
			$stmt->bindParam(':user_id', $array['user_id'], database::PARAM_INT);
		}	
		if (isset($array['date_due'])) {
			$stmt->bindParam(':date_due', $array['date_due'], database::PARAM_STR);
		}	
		if (isset($array['archived'])) {
			$stmt->bindParam(':archived', $array['archived'], database::PARAM_INT);
		}	
		
		try {
			$stmt->execute();
		}
		catch (Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$log_array['event_severity'] = 'notice';
		$log_array['event_number'] = E_USER_NOTICE;
		$log_array['event_description'] = 'Ticket Edited ID <a href="'. $config->get('address') .'/tickets/view/'.(int)$array['id'] . '/">' . safe_output($array['id']) . '</a>';
		if (isset($array['subject'])) {
			$log_array['event_description'] = 'Ticket Edited "<a href="'. $config->get('address') .'/tickets/view/'.(int)$array['id'] . '/">' . safe_output($array['subject']) . '</a>"';
		}
		$log_array['event_file'] = __FILE__;
		$log_array['event_file_line'] = __LINE__;
		$log_array['event_type'] = 'edit';
		$log_array['event_source'] = 'tickets';
		$log_array['event_version'] = '1';
		$log_array['log_backtrace'] = false;	
				
		$log->add($log_array);
				
		
		return true;
	
	}
	public function get($array = NULL) {
		global $db;
		
		$error 			=	&singleton::get(__NAMESPACE__ . '\error');
		$tables 		=	&singleton::get(__NAMESPACE__ . '\tables');
		$plugins 		=	&singleton::get(__NAMESPACE__ . '\plugins');

		$site_id		= SITE_ID;
		$order_array 	= array('date_added', 'state_id', 'priority_id', 'subject', 'user_id', 'assigned_user_id', 'last_modified', 'id', 'date_due');


		if (isset($array['count']) && ($array['count'] == true)) {
			$query = "SELECT count(t.id) AS `count`";
		}
		else {
			$query = "SELECT t.* ";
		}
		
		if (isset($array['get_other_data']) && ($array['get_other_data'] == true)) {
			$query .= ", u.pushover_key AS `owner_pushover_key`, u.name AS `owner_name`, u.id AS `owner_id`, u.email AS `owner_email`, u.phone_number AS `owner_phone`, u.email_notifications AS `owner_email_notifications`";
			$query .= ", u2.pushover_key AS `assigned_pushover_key`, u2.name AS `assigned_name`, u2.id AS `assigned_id`, u2.email AS `assigned_email`, u2.email_notifications AS `assigned_email_notifications`";
			$query .= ", u3.name AS `submitted_name`, u3.id AS `submitted_id`, u3.email AS `submitted_email`, u3.email_notifications AS `submitted_email_notifications`";
			
			$query .= ", tp.name AS `priority_name`, tp.colour AS `priority_colour`";
			$query .= ", td.name AS `department_name`";
			$query .= ", ts.name AS `status_name`, ts.colour AS `status_colour`, ts.active AS `active`";
			
			$query .= ", pa.name AS `pop_account_name`";
			//$query .= ", GROUP_CONCAT(IFNULL(CONCAT('{value:\"', IFNULL(tfv.value, NULL), '\", group_id:\"',IFNULL(tfv.ticket_field_group_id, NULL),'\"}'), NULL)) custom_values";
			//$query .= ", CONCAT('{value:\"', IFNULL(tfv.value, NULL), '\", group_id:\"',IFNULL(tfv.ticket_field_group_id, NULL),'\"}') custom_values";
			
			if (isset($array['get_last_replier']) && $array['get_last_replier'] == false) {
			
			}
			else {
				$query .= ", IFNULL(u4.name, IFNULL(tn.name, tn.email)) AS `last_replier` ";
			}
			
			//$query .= ", tfg.name AS `custom_field_name`";

						
		}
		
		$query .= " FROM $tables->tickets t";
		
		if (isset($array['get_other_data']) && ($array['get_other_data'] == true)) {
			$query .= " LEFT JOIN $tables->users u ON u.id = t.user_id";
			$query .= " LEFT JOIN $tables->users u2 ON u2.id = t.assigned_user_id";
			$query .= " LEFT JOIN $tables->users u3 ON u3.id = t.submitted_user_id";
			
			$query .= " LEFT JOIN $tables->ticket_priorities tp ON tp.id = t.priority_id";
			$query .= " LEFT JOIN $tables->ticket_departments td ON td.id = t.department_id";
			$query .= " LEFT JOIN $tables->ticket_status ts ON ts.id = t.state_id";
			$query .= " LEFT JOIN $tables->pop_accounts pa ON pa.id = t.pop_account_id";
			//$query .= " LEFT JOIN $tables->ticket_field_values tfv ON tfv.ticket_id = t.id";

			
			if (isset($array['get_last_replier']) && $array['get_last_replier'] == false) {
			
			}
			else {
				//this code is currently very slow :(
				
				$query .= " LEFT JOIN 
					(
						SELECT
							tn.user_id, 
							tn.name, 
							tn.email, 
							tn.id, 
							tn.ticket_id 
						FROM 
							$tables->ticket_notes AS tn 					
						INNER JOIN (
							SELECT 
								MAX(tn_temp.id) AS id, 
								tn_temp.ticket_id
							FROM
								$tables->ticket_notes AS tn_temp
							GROUP BY 
								tn_temp.ticket_id
						) AS tn2 ON (tn.id = tn2.id AND tn.ticket_id = tn2.ticket_id)
					) AS tn ON t.id = tn.ticket_id OR t.id IS NULL				
				";
				
				$query .= " LEFT JOIN $tables->users u4 ON tn.user_id = u4.id";

			}
						
			
			//$query .= " LEFT JOIN $tables->ticket_field_values tfv ON tfv.ticket_id = t.id";
			//$query .= " LEFT JOIN $tables->ticket_field_group tfg ON tfg.id = tfv.ticket_field_group_id";

		}
		
		$query .= " WHERE 1 = 1 AND t.site_id = :site_id";
		
		if (isset($array['id'])) {
			$query .= " AND t.id = :id";
		}
		if (isset($array['ids'])) {				
			$return = ' AND t.id IN (';
			
			foreach ($array['ids'] as $index => $value) {
				$return .= ':ids' . (int) $index . ',';
			}
			
			if(substr($return, -1) == ',') {	
				$return = substr($return, 0, strlen($return) - 1);
			}
			
			$return .= ')';
			
			$query .= $return;
		}
		
		if (isset($array['user_id'])) {
			$query .= " AND t.user_id = :user_id";
		}
		if (isset($array['assigned_user_id'])) {
			$query .= " AND t.assigned_user_id = :assigned_user_id";
		}	
		if (isset($array['assigned_or_user_id'])) {
			$query .= " AND (t.assigned_user_id = :assigned_or_user_id OR t.user_id = :assigned_or_user_id)";
		}	
		if (isset($array['unassigned_or_assigned_or_user_id'])) {
			$query .= " AND (t.assigned_user_id = 0 OR t.assigned_user_id IS NULL OR t.assigned_user_id = :assigned_or_user_id OR t.user_id = :assigned_or_user_id)";
		}	
		if (isset($array['state_id'])) {
			$query .= " AND t.state_id = :state_id";
		}
		if (isset($array['priority_id'])) {
			$query .= " AND t.priority_id = :priority_id";
		}
		if (isset($array['department_id'])) {
			$query .= " AND t.department_id = :department_id";
		}
		if (isset($array['company_id'])) {
			$query .= " AND t.company_id = :company_id";
		}
		if (isset($array['project_id'])) {
			$query .= " AND t.project_id = :project_id";
		}
		if (isset($array['date_due'])) {
			$query .= " AND t.date_due = :date_due";
		}
		if (isset($array['not_projects']) && ($array['not_projects'] == true || $array['not_projects'] == 1)) {
			$query .= " AND t.project_id IS NULL OR t.project_id = 0";
		}
		
		if (isset($array['like_search'])) {
			$query .= " AND 
			(
				t.id LIKE :like_search 
				OR t.subject LIKE :like_search 
				OR t.description LIKE :like_search 
				OR t.name LIKE :like_search 
				OR t.email LIKE :like_search 
				OR t.id 
					IN (
						SELECT ticket_id 
						FROM $tables->ticket_notes tn2 
						WHERE tn2.site_id = :site_id 
						AND t.id = tn2.ticket_id 
						AND tn2.private = 0 
						AND (
							tn2.description LIKE :like_search OR tn2.name LIKE :like_search OR tn2.email LIKE :like_search
						)
					)
				OR t.id
					IN (
						SELECT ticket_id 
						FROM $tables->files_to_tickets ftf, $tables->storage s
						WHERE ftf.site_id = :site_id 
						AND t.id = ftf.ticket_id 
						AND s.id = ftf.file_id
						AND ftf.private = 0 
						AND s.name LIKE :like_search				
					)
				OR t.id
					IN (
						SELECT ticket_id 
						FROM $tables->files_to_ticket_notes fttn, $tables->storage s2
						WHERE fttn.site_id = :site_id 
						AND t.id = fttn.ticket_id 
						AND s2.id = fttn.file_id
						AND fttn.private = 0 
						AND s2.name LIKE :like_search				
					)
					
				OR t.id
					IN (
						SELECT ticket_id 
						FROM $tables->ticket_field_values tfv, $tables->ticket_field_group tfg
						WHERE tfv.site_id = :site_id 
						AND tfv.ticket_field_group_id = tfg.id
						AND tfg.client_modify = 1
						AND t.id = tfv.ticket_id 
						AND tfv.value LIKE :like_search					
					)
			)";
		}
		if (isset($array['access_key'])) {
			$query .= " AND t.access_key = :access_key";
		}
		//used for moderators
		if (isset($array['department_or_assigned_or_user_id'])) {
			$query .= " AND (";
				//departments
				$query .= " t.department_id IN (SELECT utd.department_id FROM $tables->users_to_departments utd WHERE utd.user_id = :department_or_assigned_or_user_id AND utd.site_id = :site_id)";
			$query .= " OR ";
				//assigned or user
				$query .= " (t.assigned_user_id = :department_or_assigned_or_user_id OR t.user_id = :department_or_assigned_or_user_id)";
			$query .= ")";
		}
		
		if (isset($array['min_date_added'])) {
			$query .= " AND t.date_added >= :min_date_added";
		}
		if (isset($array['max_date_added'])) {
			$query .= " AND t.date_added <= :max_date_added";		
		}
		
		if (isset($array['min_date_due'])) {
			$query .= " AND t.date_due >= :min_date_due";		
		}
		if (isset($array['max_date_due'])) {
			$query .= " AND t.date_due <= :max_date_due";		
		}	
		
		if (isset($array['archived'])) {
			$query .= " AND t.archived = :archived";		
		}
		else {
			$query .= " AND t.archived = 0";				
		}
		
		if (isset($array['order_by']) && ($array['order_by'] == 'date_due')) {
			$query .= " AND t.date_due <> ''";
		}
		
		//this is causing issues!!!
		//$query .= ' GROUP BY t.id';

		if (isset($array['get_other_data']) && ($array['get_other_data'] == true) && isset($array['active'])) {
			$query .= " HAVING active = :active ";
		}
		
		if (isset($array['order_by']) && in_array($array['order_by'], $order_array)) {
			if (isset($array['order']) && $array['order'] == 'desc') {
				$query .= ' ORDER BY t.' . $array['order_by'] . ' DESC';
			}
			else {
				$query .= ' ORDER BY t.' . $array['order_by'];
			}			
		}
		else {
			if (isset($array['order']) && $array['order'] == 'asc') {
				$query .= ' ORDER BY t.last_modified';
			}
			else {
				$query .= " ORDER BY t.last_modified DESC";
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
		
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);

		if (isset($array['get_other_data']) && ($array['get_other_data'] == true) && isset($array['active'])) {
			$stmt->bindParam(':active', $array['active'], database::PARAM_INT);
		}		
		if (isset($array['id'])) {
			$stmt->bindParam(':id', $array['id'], database::PARAM_INT);
		}
		if (isset($array['ids'])) {	
			foreach ($array['ids'] as $index => $value) {
				$d_id = (int) $value;
				$stmt->bindParam(':ids' . (int) $index, $d_id, database::PARAM_INT);
				unset($d_id);
			}
		}
			
		if (isset($array['user_id'])) {
			$stmt->bindParam(':user_id', $array['user_id'], database::PARAM_INT);
		}
		if (isset($array['assigned_user_id'])) {
			$stmt->bindParam(':assigned_user_id', $array['assigned_user_id'], database::PARAM_INT);
		}
		if (isset($array['assigned_or_user_id'])) {
			$stmt->bindParam(':assigned_or_user_id', $array['assigned_or_user_id'], database::PARAM_INT);
		}
		if (isset($array['state_id'])) {
			$stmt->bindParam(':state_id', $array['state_id'], database::PARAM_INT);
		}
		if (isset($array['priority_id'])) {
			$stmt->bindParam(':priority_id', $array['priority_id'], database::PARAM_INT);
		}
		if (isset($array['department_id'])) {
			$stmt->bindParam(':department_id', $array['department_id'], database::PARAM_INT);
		}
		if (isset($array['company_id'])) {
			$stmt->bindParam(':company_id', $array['company_id'], database::PARAM_INT);
		}
		if (isset($array['project_id'])) {
			$stmt->bindParam(':project_id', $array['project_id'], database::PARAM_INT);
		}
		if (isset($array['date_due'])) {
			$stmt->bindParam(':date_due', $array['date_due'], database::PARAM_STR);
		}
		if (isset($array['access_key'])) {
			$stmt->bindParam(':access_key', $array['access_key'], database::PARAM_STR);
		}
		if (isset($array['like_search'])) {
			$value = $array['like_search'];
			$value = "%{$value}%";
			$stmt->bindParam(':like_search', $value, database::PARAM_STR);
			unset($value);
		}
		if (isset($array['department_or_assigned_or_user_id'])) {
			$stmt->bindParam(':department_or_assigned_or_user_id', $array['department_or_assigned_or_user_id'], database::PARAM_INT);
		}
		
		if (isset($array['min_date_added'])) {
			$stmt->bindParam(':min_date_added', $array['min_date_added'], database::PARAM_STR);
		}
		if (isset($array['max_date_added'])) {
			$stmt->bindParam(':max_date_added', $array['max_date_added'], database::PARAM_STR);
		}
		if (isset($array['min_date_due'])) {
			$stmt->bindParam(':min_date_due', $array['min_date_due'], database::PARAM_STR);
		}
		if (isset($array['max_date_due'])) {
			$stmt->bindParam(':max_date_due', $array['max_date_due'], database::PARAM_STR);
		}			
		if (isset($array['archived'])) {
			$stmt->bindParam(':archived', $array['archived'], database::PARAM_INT);
		}

		if (isset($array['limit'])) {
			$limit = (int) $array['limit'];
			if ($limit < 0) $limit = 0;
			$stmt->bindParam(':limit', $limit, database::PARAM_INT);
			if (isset($array['offset'])) {
				$offset = (int) $array['offset'];
				$stmt->bindParam(':offset', $offset, database::PARAM_INT);					
			}
		}	
	
		try {
			$stmt->execute();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$tickets = $stmt->fetchAll(database::FETCH_ASSOC);
		
		//print_r($tickets);
		
		return $tickets;
	}
	
	function delete($array) {
		global $db;
		
		$tables =	&singleton::get(__NAMESPACE__ . '\tables');
		$error 	=	&singleton::get(__NAMESPACE__ . '\error');
		$log 	=	&singleton::get(__NAMESPACE__ . '\log');

		$site_id	= SITE_ID;
		
		if (!isset($array['id'])) return false;
		
		//delete file links
		$query 	= "DELETE FROM $tables->files_to_tickets WHERE site_id = :site_id AND ticket_id = :id";
				
		try {
			$stmt = $db->prepare($query);
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);
		$stmt->bindParam(':id', $array['id'], database::PARAM_INT);
		
		try {
			$stmt->execute();
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}

		//delete notes
		$query 	= "DELETE FROM $tables->ticket_notes WHERE site_id = :site_id AND ticket_id = :id";
				
		try {
			$stmt = $db->prepare($query);
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);
		$stmt->bindParam(':id', $array['id'], database::PARAM_INT);
		
		try {
			$stmt->execute();
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		//delete ticket
		$query 	= "DELETE FROM $tables->tickets WHERE site_id = :site_id";
		
		if (isset($array['id'])) {
			$query .= " AND id = :id";
		}
		if (isset($array['user_id'])) {
			$query .= " AND user_id = :user_id";
		}
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);

		if (isset($array['id'])) {
			$stmt->bindParam(':id', $array['id'], database::PARAM_INT);
		}
		if (isset($array['user_id'])) {
			$stmt->bindParam(':user_id', $array['user_id'], database::PARAM_INT);
		}
		
		try {
			$stmt->execute();
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$log_array['event_severity'] = 'notice';
		$log_array['event_number'] = E_USER_NOTICE;
		$log_array['event_description'] = 'Ticket Deleted ID ' . safe_output($array['id']);
		$log_array['event_file'] = __FILE__;
		$log_array['event_file_line'] = __LINE__;
		$log_array['event_type'] = 'delete';
		$log_array['event_source'] = 'tickets';
		$log_array['event_version'] = '1';
		$log_array['log_backtrace'] = false;	
				
		$log->add($log_array);
		
	}
	
	public function get_files($array) {
		global $db;
		
		$tables =	&singleton::get(__NAMESPACE__ . '\tables');
		$error 	=	&singleton::get(__NAMESPACE__ . '\error');
		$log 	=	&singleton::get(__NAMESPACE__ . '\log');

		$site_id	= SITE_ID;
		
		$query = "
		SELECT
			$tables->storage.*,
			u.name AS `user_fullname`
		FROM
			$tables->files_to_tickets 
		LEFT JOIN
			$tables->storage ON $tables->files_to_tickets.file_id = $tables->storage.id
		LEFT JOIN
			$tables->users u ON $tables->storage.user_id = u.id
		WHERE 
			1 = 1 AND $tables->storage.site_id = :site_id
		";
		
		if (isset($array['id'])) {
			$query .= " AND $tables->files_to_tickets.ticket_id = :id";
		}
		
		if (isset($array['file_id'])) {
			$query .= " AND $tables->files_to_tickets.file_id = :file_id";
		}

		if (isset($array['private'])) {
			$query .= " AND $tables->files_to_tickets.private = :private";
		}
		
		if (isset($array['ticket_ids'])) {
							
			$return = " AND $tables->files_to_tickets.ticket_id IN (";
			
			foreach ($array['ticket_ids'] as $index => $value) {
				$return .= ':ticket_ids' . (int) $index . ',';
			}
			
			if(substr($return, -1) == ',') {	
				$return = substr($return, 0, strlen($return) - 1);
			}
			
			$return .= ')';
			
			$query .= $return;

		}
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);

			
		if (isset($array['id'])) {
			$stmt->bindParam(':id', $array['id'], database::PARAM_INT);
		}
		if (isset($array['file_id'])) {
			$stmt->bindParam(':file_id', $array['file_id'], database::PARAM_INT);
		}
		if (isset($array['private'])) {
			$stmt->bindParam(':private', $array['private'], database::PARAM_INT);
		}
		if (isset($array['ticket_ids'])) {	
			foreach ($array['ticket_ids'] as $index => $value) {
				$t_id = (int) $value;
				$stmt->bindParam(':ticket_ids' . (int) $index, $t_id, database::PARAM_INT);
				unset($t_id);
			}
		}	
	
		try {
			$stmt->execute();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$files = $stmt->fetchAll(database::FETCH_ASSOC);
		
		return $files;
		
	}
	
	public function day_stats($array = NULL) {
		global $db;
		
		$tables 		= &singleton::get(__NAMESPACE__ . '\tables');
		$error 			= &singleton::get(__NAMESPACE__ . '\error');
		$site_id		= SITE_ID;
		
		if ($array['date_select'] == 'date_state_changed') {
			$query = "SELECT count(t.id) AS `count`, DAY(t.date_state_changed) AS `day`, MONTH(t.date_state_changed) AS `month`, YEAR(t.date_state_changed) AS `year`";
		}
		else {
			$query = "SELECT count(t.id) AS `count`, DAY(t.date_added) AS `day`, MONTH(t.date_added) AS `month`, YEAR(t.date_added) AS `year`";
		}
		
		$query .= "FROM $tables->tickets t";
		
		$query .= " LEFT JOIN $tables->ticket_status ts ON state_id = ts.id";
		
		$query .= " WHERE 1 = 1 AND t.site_id = :site_id";
		
		if (isset($array['state_id'])) {
			$query .= " AND t.state_id = :state_id";
		}

		if (isset($array['active'])) {
			$query .= " AND ts.active = :active";
		}
		
		if (isset($array['get_all']) && ($array['get_all'] == true)) {
		
		}
		else {
			if ($array['date_select'] == 'date_state_changed') {
				$query .= ' AND t.date_state_changed >= SUBDATE(DATE_FORMAT(NOW(), "%Y-%m-%d"), INTERVAL :days DAY)';
			}
			else {
				$query .= ' AND t.date_added >= SUBDATE(DATE_FORMAT(NOW(), "%Y-%m-%d"), INTERVAL :days DAY)';
			}
		}
		
		$query .= " GROUP BY `year` DESC, `month` DESC, `day` DESC";
		
		$query .= " ORDER BY `year` DESC, `month` DESC, `day` DESC";
		
		if (isset($array['get_all']) && ($array['get_all'] == true)) {
		
		}
		else {
			$query .= " LIMIT " . (int) $array['days'];
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
				
		if (isset($array['state_id'])) {
			$stmt->bindParam(':state_id', $array['state_id'], database::PARAM_INT);
		}
		if (isset($array['active'])) {
			$stmt->bindParam(':active', $array['active'], database::PARAM_INT);
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
	
	public function month_stats($array = NULL) {
		global $db;
		
		$tables 		= &singleton::get(__NAMESPACE__ . '\tables');
		$error 			= &singleton::get(__NAMESPACE__ . '\error');
		$site_id		= SITE_ID;
		
		if ($array['date_select'] == 'date_state_changed') {
			$query = "SELECT count(t.id) AS `count`, MONTH(t.date_state_changed) AS `month`, YEAR(t.date_state_changed) AS `year`";
		}
		else {
			$query = "SELECT count(t.id) AS `count`, MONTH(t.date_added) AS `month`, YEAR(t.date_added) AS `year`";
		}
		
		$query .= "FROM $tables->tickets t";

		$query .= " LEFT JOIN $tables->ticket_status ts ON state_id = ts.id";
		
		$query .= " WHERE 1 = 1 AND t.site_id = :site_id";
		
		if (isset($array['state_id'])) {
			$query .= " AND t.state_id = :state_id";
		}
		
		if (isset($array['active'])) {
			$query .= " AND ts.active = :active";
		}
		
		if (isset($array['get_all']) && ($array['get_all'] == true)) {
		
		}
		else {
			if ($array['date_select'] == 'date_state_changed') {
				$query .= ' AND t.date_state_changed >= SUBDATE(DATE_FORMAT(NOW(), "%Y-%m-01"), INTERVAL :months MONTH)';
			}
			else {
				$query .= ' AND t.date_added >= SUBDATE(DATE_FORMAT(NOW(), "%Y-%m-01"), INTERVAL :months MONTH)';
			}
		}
		
		$query .= " GROUP BY `year` DESC, `month` DESC";
		
		$query .= " ORDER BY `year` DESC, `month` DESC";
		
		if (isset($array['get_all']) && ($array['get_all'] == true)) {
		
		}
		else {
			$query .= " LIMIT " . (int) $array['months'];
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
				
		if (isset($array['state_id'])) {
			$stmt->bindParam(':state_id', $array['state_id'], database::PARAM_INT);
		}
		if (isset($array['active'])) {
			$stmt->bindParam(':active', $array['active'], database::PARAM_INT);
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
	
		
	public function day_users($array = NULL) {
		global $db;
		
		$tables 		= &singleton::get(__NAMESPACE__ . '\tables');
		$error 			= &singleton::get(__NAMESPACE__ . '\error');
		$site_id		= SITE_ID;
		
		$query = "SELECT count(t.id) AS `count`, u.name as `full_name`";
		
		$query .= "FROM $tables->tickets t, $tables->users u WHERE 1 = 1 AND t.site_id = :site_id AND t.user_id = u.id";
		
		if (isset($array['get_all']) && ($array['get_all'] == true)) {
		
		}
		else {
			$query .= ' AND t.date_added >= SUBDATE(DATE_FORMAT(NOW(), "%Y-%m-%d"), INTERVAL :days DAY)';
		}
		
		$query .= " GROUP BY t.user_id";
		
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
		
		$query = "SELECT count(t.id) AS `count`, u.name as `full_name`";
		
		$query .= "FROM $tables->tickets t, $tables->users u WHERE 1 = 1 AND t.site_id = :site_id AND t.user_id = u.id";
		
		if (isset($array['get_all']) && ($array['get_all'] == true)) {
		
		}
		else {
			$query .= ' AND t.date_added >= SUBDATE(DATE_FORMAT(NOW(), "%Y-%m-01"), INTERVAL :months MONTH)';
		}
		
		$query .= " GROUP BY t.user_id";
		
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
	
	function active_tickets_by_department() {
		global $db;
		
		$tables 	=	&singleton::get(__NAMESPACE__ . '\tables');
		$error 		=	&singleton::get(__NAMESPACE__ . '\error');
		$site_id	= SITE_ID;
					
		$query = "
			SELECT count(t.id) AS `count`, td.name FROM $tables->tickets t
			LEFT JOIN $tables->ticket_status ts ON t.state_id = ts.id
			LEFT JOIN $tables->ticket_departments td ON t.department_id = td.id 
			WHERE ts.active = 1 AND t.site_id = :site_id
			GROUP BY td.id
		";
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);

		try {
			$stmt->execute();
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}

		$count = $stmt->fetchAll(database::FETCH_ASSOC);
		
		return $count;	
	
	}
	
	function active_tickets_by_status() {
		global $db;
		
		$tables 	=	&singleton::get(__NAMESPACE__ . '\tables');
		$error 		=	&singleton::get(__NAMESPACE__ . '\error');
		$site_id	= SITE_ID;
					
		$query = "
			SELECT count(t.id) AS `count`, ts.name FROM $tables->tickets t
			LEFT JOIN $tables->ticket_status ts ON t.state_id = ts.id
			WHERE ts.active = 1 AND t.site_id = :site_id
			GROUP BY ts.id
		";
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);

		try {
			$stmt->execute();
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}

		$count = $stmt->fetchAll(database::FETCH_ASSOC);
		
		return $count;	
	
	}
	
	function status_count($array = NULL) {
		global $db;
		
		$tables =	&singleton::get(__NAMESPACE__ . '\tables');
		$error =	&singleton::get(__NAMESPACE__ . '\error');
		$site_id	= SITE_ID;
				
		$query = "SELECT count(*) AS `count`";

				
		$query .= ", ts.name AS `status_name`, ts.active AS `active`";		
		
		$query .= " FROM $tables->tickets t";
		
				
		$query .= " LEFT JOIN $tables->ticket_status ts ON ts.id = t.state_id";
				
		$query .= " WHERE t.site_id = :site_id";
		
		
		
		if (isset($array['id'])) {
			$query .= " AND t.id = :id";
		}
		if (isset($array['user_id'])) {
			$query .= " AND t.user_id = :user_id";
		}
		if (isset($array['assigned_user_id'])) {
			$query .= " AND t.assigned_user_id = :assigned_user_id";
		}
		if (isset($array['state_id'])) {
			$query .= " AND t.state_id = :state_id";
		}
		if (isset($array['archived'])) {
			$query .= " AND t.archived = :archived";		
		}
		else {
			$query .= " AND t.archived = 0";				
		}
		
		
		
		if (isset($array['get_other_data']) && ($array['get_other_data'] == true) && isset($array['active'])) {
			$query .= " AND ts.active = :active ";
		}
		
		$query .= " GROUP BY t.state_id";
		
		//echo $query; 
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);

		if (isset($array['id'])) {
			$id = $array['id'];
			$stmt->bindParam(':id', $id, database::PARAM_INT);
		}
		if (isset($array['get_other_data']) && ($array['get_other_data'] == true) && isset($array['active'])) {
			$stmt->bindParam(':active', $array['active'], database::PARAM_INT);
		}			
		if (isset($array['user_id'])) {
			$user_id = $array['user_id'];
			$stmt->bindParam(':user_id', $user_id, database::PARAM_INT);
		}
		if (isset($array['assigned_user_id'])) {
			$assigned_user_id = $array['assigned_user_id'];
			$stmt->bindParam(':assigned_user_id', $assigned_user_id, database::PARAM_INT);
		}
		if (isset($array['state_id'])) {
			$stmt->bindParam(':state_id', $array['state_id'], database::PARAM_INT);
		}
		if (isset($array['archived'])) {
			$stmt->bindParam(':archived', $array['archived'], database::PARAM_INT);
		}
		try {
			$stmt->execute();
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}

		$count = $stmt->fetchAll(database::FETCH_ASSOC);
		
		return $count;	
	
	}
	
	public function merge($array) {
	
		$auth 			=	&singleton::get(__NAMESPACE__ . '\auth');
		$ticket_notes	=	&singleton::get(__NAMESPACE__ . '\ticket_notes');
		$storage		=	&singleton::get(__NAMESPACE__ . '\storage');

		//see if primary ticket is within merge ticket array and remove
		foreach($array['ids'] as $index => $value) {
			if ($value == $array['primary_id']) {
				unset($array['ids'][$index]);
			}
		}
		$array['ids'] = array_values($array['ids']);

		//get primary ticket
		$primary_tick = $this->get(array('id' => (int) $array['primary_id']));
		
		if (!empty($primary_tick)) {
	
			//create new ticket
			$primary_ticket 			= $primary_tick[0];	
			
			//copy data to new ticket
			$ticket_add_array = 
			array(
				'subject'				=> $primary_ticket['subject'],
				'description'			=> $primary_ticket['description'],
				'name'					=> $primary_ticket['name'],
				'email'					=> $primary_ticket['email'],
				'priority_id'			=> (int) $primary_ticket['priority_id'],
				'html'					=> $primary_ticket['html'],
				'user_id'				=> (int) $primary_ticket['user_id'],
				'department_id'			=> (int) $primary_ticket['department_id'],
				'assigned_user_id'		=> (int) $primary_ticket['assigned_user_id'],
				//fix cc
				//'cc'					=> $primary_ticket['cc'],
				'company_id'			=> (int) $primary_ticket['company_id'],
				'state_id'				=> (int) $primary_ticket['state_id'],
				'pop_account_id'				=> (int) $primary_ticket['pop_account_id']
			);

			$id = $this->add($ticket_add_array);
			unset($ticket_add_array);
			
			//copy description
			$to_copy = $this->get(array('ids' => $array['ids']));
			
			foreach ($to_copy as $ticket) {
				$ticket_note['description']		= '<strong>' . $ticket['subject'] . '</strong><br />' . $ticket['description'];
				$ticket_note['user_id']			= $ticket['user_id'];
				$ticket_note['company_id']		= $ticket['company_id'];
				$ticket_note['html']			= 1;
				//fix cc
				//$ticket_note['cc']				= $ticket['cc'];

				$ticket_note['ticket_id']		= $id;
				
				
				$ticket_notes->add($ticket_note);
				unset($ticket_note);
			}
			
			//copy primary ticket notes
			$notes_to_copy = $ticket_notes->get(array('ticket_id' => (int) $array['primary_id']));

			foreach ($notes_to_copy as $nticket) {
				$ticket_note['description']		= $nticket['description'];
				$ticket_note['user_id']			= $nticket['user_id'];
				$ticket_note['company_id']		= $nticket['company_id'];
				$ticket_note['html']			= $nticket['html'];
				$ticket_note['private']			= $nticket['private'];
				$ticket_note['name']			= $nticket['name'];
				$ticket_note['email']			= $nticket['email'];
				//$ticket_note['cc']				= $nticket['cc'];
				$ticket_note['subject']			= $nticket['subject'];
				$ticket_note['ticket_id']		= $id;
								
				$ticket_notes->add($ticket_note);
				unset($ticket_note);
			}		
			
			unset($notes_to_copy);
			unset($nticket);
			
			//copy notes
			$notes_to_copy = $ticket_notes->get(array('ticket_ids' => $array['ids']));

			foreach ($notes_to_copy as $nticket) {
				$ticket_note['description']		= $nticket['description'];
				$ticket_note['user_id']			= $nticket['user_id'];
				$ticket_note['company_id']		= $nticket['company_id'];
				$ticket_note['html']			= $nticket['html'];
				$ticket_note['private']			= $nticket['private'];
				$ticket_note['name']			= $nticket['name'];
				$ticket_note['email']			= $nticket['email'];
				//$ticket_note['cc']				= $nticket['cc'];
				$ticket_note['subject']			= $nticket['subject'];
				$ticket_note['ticket_id']		= $id;
								
				$ticket_notes->add($ticket_note);
				unset($ticket_note);
			}
			
			//copy files from primary ticket
			$primary_files = $this->get_files(array('id' => (int) $array['primary_id']));
			
			foreach($primary_files as $file) {
				$storage->add_file_to_ticket(array('ticket_id' => $id, 'file_id' => $file['id'], 'private' => $file['private']));
			}
			unset($file);
			
			//get all the files from the other tickets
			$other_files = $this->get_files(array('ticket_ids' => $array['ids']));
			
			foreach($other_files as $file) {
				$storage->add_file_to_ticket(array('ticket_id' => $id, 'file_id' => $file['id'], 'private' => $file['private']));
			}
			
			
			//close old tickets
			foreach($array['ids'] as $ticket_id) {
				$mod_ticket_array['id']					= $ticket_id;
				$mod_ticket_array['state_id']			= 2;
				$mod_ticket_array['merge_ticket_id']	= $id;
				
				/*
					$ticket_note['description']		= $auth->get('name') . ' Merged Ticket (new ticket ID: '.(int)$id.').';
					$ticket_note['user_id']			= $auth->get('id');
					$ticket_note['company_id']		= $auth->get('company_id');
					$ticket_note['ticket_id']		= $ticket_id;
				*/
				
				$this->edit($mod_ticket_array);
				
				//Adds note to old ticket
				//$this->add_note($ticket_note);
				//unset($ticket_note);
				
				unset($mod_ticket_array);
			}
			
			//close old primary ticket
			$mod_ticket_array['id']					= (int) $array['primary_id'];
			$mod_ticket_array['state_id']			= 2;
			$mod_ticket_array['merge_ticket_id']	= $id;
			
			/*
				$ticket_note['description']		= $auth->get('name') . ' Merged Ticket (new ticket ID: '.(int)$id.').';
				$ticket_note['client_id']		= $auth->get('id');
				$ticket_note['company_id']		= $auth->get('company_id');
				$ticket_note['ticket_id']		= (int) $array['primary_id'];
			*/
			
			$this->edit($mod_ticket_array);
			//$ticket_notes->add($ticket_note);
			
			unset($ticket_note);
			unset($mod_ticket_array);
			
			return $id;
		}
		
	}
	
	public function add_ticket($array) {
		$config 				= &singleton::get(__NAMESPACE__ . '\config');
		$storage 				= &singleton::get(__NAMESPACE__ . '\storage');
		$language 				= &singleton::get(__NAMESPACE__ . '\language');
		$auth 					= &singleton::get(__NAMESPACE__ . '\auth');
		$ticket_custom_fields 	= &singleton::get(__NAMESPACE__ . '\ticket_custom_fields');
		
		$created_ticket 		= false;
	
		if (!empty($array['subject'])) {		
			
			$upload_file 	= false;
			$files_upload 	= array();
						
			if ($config->get('storage_enabled')) {
				if (isset($array['files']) && is_array($array['files'])) {
					$files_array = rearrange($array['files']);	
					foreach($files_array as $file) {
						if ($file['size'] > 0) {
							$upload_file = true;
							$file_array['file']			= $file;
							$file_array['name']			= $file['name'];		
							$file_array['user_id']		= $auth->get('id');		
							$files_upload[] 			= $storage->upload($file_array);
						}
					}
				}
			}
					
			if ($upload_file && empty($files_upload)) {
				$message = $language->get('File Upload Failed. Ticket Not Submitted.');
			}
			else {
				$add_array = 
					array(
						'subject'			=> $array['subject'],
						'description'		=> $array['description'],
						'priority_id'		=> (int) $array['priority_id'],
						'html'				=> 0
					);
				if ($config->get('html_enabled')) {
					$add_array['html'] = 1;
				}
				//$add_array['user_id'] 		= $auth->get('id');
				//$add_array['company_id'] 		= $auth->get('company_id');

				$add_array['department_id'] = 1;
				if (isset($array['department_id']) && ($array['department_id'] != '')) {
					$add_array['department_id']	= (int) $array['department_id'];
				}

				//if ($auth->can('manage_tickets') || $auth->can('tickets_view_assigned_department')) {
					if (isset($array['user_id']) && ($array['user_id'] != '')) {
						$add_array['user_id']	= (int) $array['user_id'];
					}
					if (isset($array['assigned_user_id']) && ($array['assigned_user_id'] != '')) {
						$add_array['assigned_user_id']	= (int) $array['assigned_user_id'];
					}
					if (isset($array['cc']) && (!empty($array['cc']))) {
						$add_array['cc']	= $array['cc'];
					}
					if (isset($array['company_id']) && ($array['company_id'] != '')) {
						$add_array['company_id']	= (int) $array['company_id'];
					}
					if (isset($array['project_id']) && ($array['project_id'] != '')) {
						$add_array['project_id']	= (int) $array['project_id'];
					}
				//}
				
				if (!empty($files_upload)) {
					$add_array['attach_file_ids']	= $files_upload;
				}
				
				$id = $this->add($add_array);
						
				foreach($array as $index => $value){
					if(strncasecmp($index, 'field-', 6) === 0) {
					
						$group_index = explode('-', $index);
						$group_id = (int) $group_index[1];
						if ($group_id !== 0) {
							
							$edit_array['ticket_field_group_id']	= $group_id;
							$edit_array['ticket_id']				= $id;
						
							if (is_array($value)) {
								$values = array();
								foreach($value as $check_index => $check_item) {
									$values[] = $check_item;
								}
								$edit_array['value']					= json_encode($value);								
							}
							else {
								$edit_array['value']					= $value;
							}
						
							$ticket_custom_fields->add_value($edit_array);
							unset($edit_array);
						}
						
					}			
				}
								
				$created_ticket		= true;
				$return['id'] 		= $id;
				$message 			= $language->get('Ticket Created');
				
			}
		}
		else {
			$message = $language->get('Subject Empty');
		}
		
				
		$return['success'] 	= $created_ticket;
		$return['message']	= $message;
		
		return $return;
		
	}	
	
}


?>