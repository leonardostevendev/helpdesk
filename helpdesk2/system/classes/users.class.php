<?php
/**
 * 	Users Class
 *	Copyright Dalegroup Pty Ltd 2014
 *	support@dalegroup.net
 *
 *
 * @package     dgx
 * @author      Michael Dale <support@dalegroup.net>
 */
 
namespace sts;

class users {

	var $meta = NULL;

	function __construct() {

	}
		
	/**
	 * Gets an array of users from the system.
	 *
	 * Form the array like this (all optional):
	 * <code>
	 * $array = array(
	 *		'id'	=> 1,		//The id of the user you want to get (for a single user)
	 *		'limit'	=> 10		//Limit the number of returned users
	 *
	 * );
	 * </code>
	 *
	 * @param   array   $array 		The array explained above
	 * @return  array				The array of users.
	 */
	public function get($array = NULL) {
		global $db;
		
		$error 		=	&singleton::get(__NAMESPACE__ . '\error');
		$tables 	=	&singleton::get(__NAMESPACE__ . '\tables');
		$plugins 	=	&singleton::get(__NAMESPACE__ . '\plugins');
		$config 	=	&singleton::get(__NAMESPACE__ . '\config');

		$site_id	= SITE_ID;
		
		$query = "SELECT u.* ";
		
		if (isset($array['get_other_data']) && ($array['get_other_data'] == true)) {
			$plugins->run('query_users_get_other_data_columns', $query);
			$query .= ", pg.name AS `permission_group_name`";
			
			if ($config->get('database_version') > 101) {
				$query .= ", pg.global_message AS `permission_global_message`";
			}
		}
		
		//echo $query;
		
		$query .= " FROM $tables->users u";
		
		if (isset($array['get_other_data']) && ($array['get_other_data'] == true)) {
			$plugins->run('query_users_get_other_data_join', $query);
			$query .= " LEFT JOIN $tables->permission_groups pg ON pg.id = u.group_id";
		}
	
		if (isset($array['department_id']) || isset($array['department_ids'])) {
			$query .= " LEFT JOIN $tables->users_to_departments utd";
			
			$query .= " ON u.id = utd.user_id";
			
		}
		
		$query .= " WHERE 1 = 1 AND u.site_id = :site_id";
		
		if (isset($array['id'])) {
			$query .= " AND u.id = :id";
		}
		if (isset($array['group_id'])) {
			$query .= " AND u.group_id = :group_id";
		}
		if (isset($array['allow_login'])) {
			$query .= " AND u.allow_login = :allow_login";
		}
		if (isset($array['email'])) {
			$query .= " AND u.email = :email";
		}
		if (isset($array['username'])) {
			$query .= " AND u.username = :username";
		}
		if (isset($array['authentication_id'])) {
			$query .= " AND u.authentication_id = :authentication_id";
		}

		if (isset($array['user_level'])) {
			$query .= " AND u.user_level = :user_level";
		}

		if (isset($array['company_id'])) {
			$query .= " AND u.company_id = :company_id";
		}
		
		if (isset($array['department_id'])) {
			$query .= " AND utd.site_id = :site_id AND utd.department_id = :department_id";
		}

		if (isset($array['facebook_id'])) {
			$query .= " AND u.facebook_id = :facebook_id";
		}
		
		if (isset($array['department_ids'])) {				
			$return = ' AND utd.site_id = :site_id AND utd.department_id IN (';
			
			foreach ($array['department_ids'] as $index => $value) {
				$return .= ':department_id' . (int) $index . ',';
			}
			
			if(substr($return, -1) == ',') {	
				$return = substr($return, 0, strlen($return) - 1);
			}
			
			$return .= ')';
			
			$query .= $return;
		}
		
		if (isset($array['user_levels'])) {
				
			$return = ' AND u.user_level IN ( ';
			
			foreach ($array['user_levels'] as $index => $value) {
				$return .= ' :user_level' . (int) $index . ',' ;
			}
			
			if(substr($return, -1) == ',') {	
				$return = substr($return, 0, strlen($return) - 1);
			}
			
			$return .= ')';
			
			$query .= $return;
		}
		
		if (isset($array['group_ids'])) {
				
			$return = ' AND u.group_ids IN ( ';
			
			foreach ($array['group_ids'] as $index => $value) {
				$return .= ' :group_ids' . (int) $index . ',' ;
			}
			
			if(substr($return, -1) == ',') {	
				$return = substr($return, 0, strlen($return) - 1);
			}
			
			$return .= ')';
			
			$query .= $return;
		}
		
		if (isset($array['ids'])) {
				
			$return = ' AND (u.id = ';
			
			foreach ($array['ids'] as $index => $value) {
				$return .= ' :ids' . (int) $index . ' OR u.id = ' ;
			}
			
			if(substr($return, -11) == ' OR u.id = ') {	
				$return = substr($return, 0, strlen($return) - 11);
			}
			
			$return .= ')';
			
			$query .= $return;
		}
		
		if (isset($array['notification_department']['type']) && isset($array['notification_department']['id'])) {
			$query .= " AND (u.group_id IN (SELECT group_id FROM $tables->permission_groups_to_department_notifications WHERE type = :notification_department_type AND department_id = :notification_department_id)";
			$query .= " AND u.id IN (SELECT user_id FROM $tables->users_to_departments WHERE department_id = :notification_department_id))";
		}
		
		if (isset($array['where_can_or'])) {		
			$query .="
			AND u.group_id 
			IN (
				SELECT wcopg.id FROM $tables->permission_groups wcopg WHERE wcopg.id 
					IN (
						SELECT wcottg.group_id FROM $tables->tasks_to_groups wcottg WHERE wcottg.task_id 
						IN (
							SELECT wcopt.id FROM $tables->permission_tasks wcopt WHERE wcopt.name IN 
							(
							";
								foreach($array['where_can_or'] as $index => $value) {
									$query .= ' :wco'.$index . ' , ';
									unset($index);
									unset($value);
								}
								
								if(substr($query, -3) == ' , ') {	
									$query = substr($query, 0, strlen($query) - 3);
								}
								
							$query .= "
							)
						)
					)
				)
			";
		}
		
		if (isset($array['like_search'])) {
			$query .= " AND (u.name LIKE :like_search OR u.username LIKE :like_search OR u.email LIKE :like_search)";
		}

		if (isset($array['like_email_domain'])) {
			$query .= " AND u.email LIKE :like_email_domain";
		}
		
		if (isset($array['name_search']) && !empty($array['name_search'])) {
			$query .= " AND u.name LIKE :name_search";
		}
	
		$query .= " GROUP BY u.id";
		
		if (isset($array['order_by'])) {
			if ($array['order_by'] == 'id') {
				$query .= " ORDER BY u.id DESC";			
			}
		}
		else {
			$query .= " ORDER BY u.name";
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
		
		if (isset($array['id'])) {
			$stmt->bindParam(':id', $array['id'], database::PARAM_INT);
		}
		if (isset($array['group_id'])) {
			$stmt->bindParam(':group_id', $array['group_id'], database::PARAM_INT);
		}
		if (isset($array['allow_login'])) {
			$stmt->bindParam(':allow_login', $array['allow_login'], database::PARAM_INT);
		}
		if (isset($array['email'])) {
			$email = strtolower($array['email']);
			$stmt->bindParam(':email', $email, database::PARAM_STR);
		}
		if (isset($array['username'])) {
			$username = strtolower($array['username']);
			$stmt->bindParam(':username', $username, database::PARAM_STR);
		}
		if (isset($array['authentication_id'])) {
			$stmt->bindParam(':authentication_id', $array['authentication_id'], database::PARAM_INT);
		}
		if (isset($array['department_id'])) {
			$stmt->bindParam(':department_id', $array['department_id'], database::PARAM_INT);
		}
		if (isset($array['facebook_id'])) {
			$stmt->bindParam(':facebook_id', $array['facebook_id'], database::PARAM_INT);
		}
		if (isset($array['user_level'])) {
			$stmt->bindParam(':user_level', $array['user_level'], database::PARAM_INT);
		}
		if (isset($array['company_id'])) {
			$stmt->bindParam(':company_id', $array['company_id'], database::PARAM_INT);
		}

		if (isset($array['department_ids'])) {	
			foreach ($array['department_ids'] as $index => $value) {
				$d_id = (int) $value;
				$stmt->bindParam(':department_id' . (int) $index, $d_id, database::PARAM_INT);
				unset($d_id);
			}
		}
			
		if (isset($array['user_levels'])) {	
			foreach ($array['user_levels'] as $index => $value) {
				$t_id = (int) $value;
				$stmt->bindParam(':user_level' . (int) $index, $t_id, database::PARAM_INT);
				unset($t_id);
			}
		}

		if (isset($array['group_ids'])) {	
			foreach ($array['group_ids'] as $index => $value) {
				$gids_id = (int) $value;
				$stmt->bindParam(':group_ids' . (int) $index, $gids_id, database::PARAM_INT);
				unset($gids_id);
			}
		}
		
		if (isset($array['ids'])) {	
			foreach ($array['ids'] as $index => $value) {
				$id_s = (int) $value;
				$stmt->bindParam(':ids' . (int) $index, $id_s, database::PARAM_INT);
				unset($id_s);
			}
		}
		if (isset($array['notification_department']['type']) && isset($array['notification_department']['id'])) {
			$notification_department_id = $array['notification_department']['id'];
			$notification_department_type = $array['notification_department']['type'];
			$stmt->bindParam(':notification_department_type', $notification_department_type, database::PARAM_STR);
			$stmt->bindParam(':notification_department_id', $notification_department_id, database::PARAM_INT);

		}

		if (isset($array['where_can_or'])) {
			foreach($array['where_can_or'] as $index => $value) {
				$id_s = $value;
				$stmt->bindParam(':wco' . (int) $index, $id_s, database::PARAM_STR);
				unset($id_s);
			}
		}
		
		if (isset($array['like_search'])) {
			$value = $array['like_search'];
			$value = "%{$value}%";
			$stmt->bindParam(':like_search', $value, database::PARAM_STR);
			unset($value);
		}
		if (isset($array['like_email_domain'])) {
			$value = $array['like_email_domain'];
			$value = "%@{$value}";
			$stmt->bindParam(':like_email_domain', $value, database::PARAM_STR);
			unset($value);
		}
		if (isset($array['name_search']) && !empty($array['name_search'])) {
			$value1 = $array['name_search'];
			$value1 = "%{$value1}%";
			$stmt->bindParam(':name_search', $value1);
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
		
		$users = $stmt->fetchAll(database::FETCH_ASSOC);
		
		return $users;
	}

	/**
	 * Adds a user into the system.
	 *
	 * Form the array like this:
	 * <code>
	 * $array = array(
	 *			'name' 				=> 'Test User', 		// The users Full Name 	(required)
	 *			'email' 			=> 'user@example.com',	// The email address	(optional)
	 *			'authentication_id' => 1,					// 1 (local database) or 2 (active directory)
	 *			'allow_login'		=> 1,					// Should be 1 to allow login
	 *			'username'			=> 'test',				// Username all lowercase
	 *			'password'			=> '1234',				// Plain text password (not required for external auth)
	 *			'group_id'			=> 1,					// 1,2,3,4,5 (or something custom!)
	 * );
	 * </code>
	 *
	 * @param   array   $array 		The array explained above
	 * @return  int					The id of the created user.
	 */
	public function add($array) {
		global $db;
		
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
		$log		= &singleton::get(__NAMESPACE__ . '\log');
		$config		= &singleton::get(__NAMESPACE__ . '\config');
		$auth		= &singleton::get(__NAMESPACE__ . '\auth');
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$notifications 	= &singleton::get(__NAMESPACE__ . '\notifications');

		$site_id	= SITE_ID;
		$salt 		= $auth->generate_user_salt();

		$query = "INSERT INTO $tables->users (name, site_id, salt, date_added";

		//used for import
		if (isset($array['id'])) {
			$query .= ", id";
		}

		if (isset($array['email'])) {
			$query .= ", email";
		}
		if (isset($array['authentication_id'])) {
			$query .= ", authentication_id";
		}
		if (isset($array['group_id'])) {
			$query .= ", group_id";
		}
		if (isset($array['allow_login'])) {
			$query .= ", allow_login";
		}
		if (isset($array['username'])) {
			$query .= ", username";
		}
		if (isset($array['password'])) {
			$query .= ", password";
		}
		if (isset($array['user_level'])) {
			$query .= ", user_level";
		}
		if (isset($array['address'])) {
			$query .= ", address";
		}
		if (isset($array['phone_number'])) {
			$query .= ", phone_number";
		}
		if (isset($array['pushover_key'])) {
			$query .= ", pushover_key";
		}
		if (isset($array['email_notifications'])) {
			$query .= ", email_notifications";
		}		
		if (isset($array['company_id'])) {
			$query .= ", company_id";
		}
		if (isset($array['view_ticket_reverse'])) {
			$query .= ", view_ticket_reverse";
		}		
		if (isset($array['view_ticket_filter_projects'])) {
			$query .= ", view_ticket_filter_projects";
		}			
		if (isset($array['facebook_id'])) {
			$query .= ", facebook_id";
		}	
		if (isset($array['timesheets_hourly_rate'])) {
			$query .= ", timesheets_hourly_rate";
		}			
		$query .= ") VALUES (:name, :site_id, :salt, :date_added";
		
		//used for import
		if (isset($array['id'])) {
			$query .= ", :id";
		}
		
		if (isset($array['email'])) {
			$query .= ", :email";
		}
		if (isset($array['authentication_id'])) {
			$query .= ", :authentication_id";
		}
		if (isset($array['group_id'])) {
			$query .= ", :group_id";
		}
		if (isset($array['allow_login'])) {
			$query .= ", :allow_login";
		}
		if (isset($array['username'])) {
			$query .= ", :username";
		}
		if (isset($array['password'])) {
			$query .= ", :password";
		}
		if (isset($array['user_level'])) {
			$query .= ", :user_level";
		}
		if (isset($array['address'])) {
			$query .= ", :address";
		}
		if (isset($array['phone_number'])) {
			$query .= ", :phone_number";
		}
		if (isset($array['pushover_key'])) {
			$query .= ", :pushover_key";
		}
		if (isset($array['email_notifications'])) {
			$query .= ", :email_notifications";
		}
		if (isset($array['company_id'])) {
			$query .= ", :company_id";
		}
		if (isset($array['view_ticket_reverse'])) {
			$query .= ", :view_ticket_reverse";
		}
		if (isset($array['view_ticket_filter_projects'])) {
			$query .= ", :view_ticket_filter_projects";
		}	
		if (isset($array['facebook_id'])) {
			$query .= ", :facebook_id";
		}	
		if (isset($array['timesheets_hourly_rate'])) {
			$query .= ", :timesheets_hourly_rate";
		}		
		$query .= ")";
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));		
		}
		
		$date_added = datetime();
		
		$stmt->bindParam(':salt', $salt, database::PARAM_STR);
		$stmt->bindParam(':name', $array['name'], database::PARAM_STR);
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);
		$stmt->bindParam(':date_added', $date_added, database::PARAM_STR);

		
		//used for import
		if (isset($array['id'])) {
			$stmt->bindParam(':id', $array['id'], database::PARAM_INT);
		}

		if (isset($array['email'])) {
			$email = strtolower($array['email']);
			$stmt->bindParam(':email', $email, database::PARAM_STR);
		}
		if (isset($array['authentication_id'])) {
			$stmt->bindParam(':authentication_id', $array['authentication_id'], database::PARAM_INT);
		}
		if (isset($array['group_id'])) {
			$stmt->bindParam(':group_id', $array['group_id'], database::PARAM_INT);
		}
		if (isset($array['allow_login'])) {
			$stmt->bindParam(':allow_login', $array['allow_login'], database::PARAM_INT);
		}
		if (isset($array['username'])) {
			$stmt->bindParam(':username', $array['username'], database::PARAM_STR);
		}
		if (isset($array['password'])) {
			$password	= $auth->hash_password($array['password'], $salt);
			$stmt->bindParam(':password', $password, database::PARAM_STR);
		}
		if (isset($array['user_level'])) {
			$stmt->bindParam(':user_level', $array['user_level'], database::PARAM_INT);
		}
		if (isset($array['address'])) {
			$stmt->bindParam(':address', $array['address'], database::PARAM_STR);
		}
		if (isset($array['phone_number'])) {
			$stmt->bindParam(':phone_number', $array['phone_number'], database::PARAM_STR);
		}
		if (isset($array['pushover_key'])) {
			$stmt->bindParam(':pushover_key', $array['pushover_key'], database::PARAM_STR);
		}
		if (isset($array['email_notifications'])) {
			$stmt->bindParam(':email_notifications', $array['email_notifications'], database::PARAM_INT);
		}
		if (isset($array['company_id'])) {
			$stmt->bindParam(':company_id', $array['company_id'], database::PARAM_INT);
		}
		if (isset($array['view_ticket_reverse'])) {
			$stmt->bindParam(':view_ticket_reverse', $array['view_ticket_reverse'], database::PARAM_INT);
		}
		if (isset($array['view_ticket_filter_projects'])) {
			$stmt->bindParam(':view_ticket_filter_projects', $array['view_ticket_filter_projects'], database::PARAM_INT);
		}	
		if (isset($array['facebook_id'])) {
			$stmt->bindParam(':facebook_id', $array['facebook_id'], database::PARAM_INT);
		}
		if (isset($array['timesheets_hourly_rate'])) {
			$stmt->bindParam(':timesheets_hourly_rate', $array['timesheets_hourly_rate'], database::PARAM_INT);
		}
		
		try {
			$stmt->execute();
			$id = $db->lastInsertId();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}

		$array['id']	= $id;
		
		if (isset($array['welcome_email']) && ($array['welcome_email'] == 1) && isset($array['email']) && !empty($array['email'])) {
			$notifications->new_user($array);
		}

		if (isset($array['match_tickets']) && ($array['match_tickets'] == true) && isset($array['email']) && !empty($array['email'])) {
			$this->match_tickets($array);
		}
			
		$log_array['event_severity'] = 'notice';
		$log_array['event_number'] = E_USER_NOTICE;
		$log_array['event_description'] = 'User Added "<a href="' . $config->get('address') . '/users/view/' . (int)$id . '/">' . safe_output($array['name']) . '</a>"';
		$log_array['event_file'] = __FILE__;
		$log_array['event_file_line'] = __LINE__;
		$log_array['event_type'] = 'add';
		$log_array['event_source'] = 'users';
		$log_array['event_version'] = '1';
		$log_array['log_backtrace'] = false;	
				
		$log->add($log_array);
		
	
		return $id;
	}
	
	public function edit($array) {
		global $db;
		
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$auth		= &singleton::get(__NAMESPACE__ . '\auth');
		$log		= &singleton::get(__NAMESPACE__ . '\log');
		$config		= &singleton::get(__NAMESPACE__ . '\config');
		$error		= &singleton::get(__NAMESPACE__ . '\error');

		$site_id	= SITE_ID;
		$salt 		= $auth->generate_user_salt();

		
		$query = "UPDATE $tables->users SET site_id = :site_id";

		if (isset($array['name'])) {
			$query .= ", name = :name";
		}
		if (isset($array['group_id'])) {
			$query .= ", group_id = :group_id";
		}
		if (isset($array['authentication_id'])) {
			$query .= ", authentication_id = :authentication_id";
		}		
		if (isset($array['email'])) {
			$query .= ", email = :email";
		}
		if (isset($array['allow_login'])) {
			$query .= ", allow_login = :allow_login";
		}
		if (isset($array['username'])) {
			$query .= ", username = :username";
		}
		if (isset($array['password'])) {
			$query .= ", salt = :salt";
			$query .= ", password = :password";
		}
		if (isset($array['user_level'])) {
			$query .= ", user_level = :user_level";
		}
		if (isset($array['fail_expires'])) {
			$query .= ", fail_expires = :fail_expires";
		}
		if (isset($array['failed_logins'])) {
			$query .= ", failed_logins = :failed_logins";
		}		
		if (isset($array['email_notifications'])) {
			$query .= ", email_notifications = :email_notifications";
		}
		if (isset($array['reset_key'])) {
			$query .= ", reset_key = :reset_key";
		}
		if (isset($array['reset_expiry'])) {
			$query .= ", reset_expiry = :reset_expiry";
		}			
		if (isset($array['address'])) {
			$query .= ", address = :address";
		}
		if (isset($array['phone_number'])) {
			$query .= ", phone_number = :phone_number";
		}
		if (isset($array['pushover_key'])) {
			$query .= ", pushover_key = :pushover_key";
		}
		if (isset($array['date_added'])) {
			$query .= ", date_added = :date_added";
		}
		if (isset($array['company_id'])) {
			$query .= ", company_id = :company_id";
		}
		if (isset($array['view_ticket_reverse'])) {
			$query .= ", view_ticket_reverse = :view_ticket_reverse";
		}
		if (isset($array['view_ticket_filter_projects'])) {
			$query .= ", view_ticket_filter_projects = :view_ticket_filter_projects";
		}
		if (isset($array['facebook_id'])) {
			$query .= ", facebook_id = :facebook_id";
		}		
		if (isset($array['timesheets_hourly_rate'])) {
			$query .= ", timesheets_hourly_rate = :timesheets_hourly_rate";
		}				
		
		$query .= " WHERE id = :id AND site_id = :site_id";
		
		//echo $query;
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		$stmt->bindParam(':id', $array['id'], database::PARAM_INT);
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);
		
		if (isset($array['name'])) {
			$stmt->bindParam(':name', $array['name'], database::PARAM_STR);
		}
		if (isset($array['group_id'])) {
			$stmt->bindParam(':group_id', $array['group_id'], database::PARAM_INT);
		}
		if (isset($array['authentication_id'])) {
			$stmt->bindParam(':authentication_id', $array['authentication_id'], database::PARAM_INT);
		}
		if (isset($array['email'])) {
			$stmt->bindParam(':email', $array['email'], database::PARAM_STR);
		}
		if (isset($array['allow_login'])) {
			$stmt->bindParam(':allow_login', $array['allow_login'], database::PARAM_INT);
		}
		if (isset($array['username'])) {
			$stmt->bindParam(':username', $array['username'], database::PARAM_STR);
		}	
		if (isset($array['password'])) {
			$stmt->bindParam(':salt', $salt, database::PARAM_STR);
			$password	= $auth->hash_password($array['password'], $salt);
			$stmt->bindParam(':password', $password, database::PARAM_STR);
		}
		if (isset($array['user_level'])) {
			$stmt->bindParam(':user_level', $array['user_level'], database::PARAM_INT);
		}
		if (isset($array['fail_expires'])) {
			$stmt->bindParam(':fail_expires', $array['fail_expires'], database::PARAM_STR);
		}	
		if (isset($array['failed_logins'])) {
			$stmt->bindParam(':failed_logins', $array['failed_logins'], database::PARAM_INT);
		}	
		if (isset($array['email_notifications'])) {
			$stmt->bindParam(':email_notifications', $array['email_notifications'], database::PARAM_INT);
		}	
		if (isset($array['reset_key'])) {
			$stmt->bindParam(':reset_key', $array['reset_key'], database::PARAM_STR);
		}
		if (isset($array['reset_expiry'])) {
			$stmt->bindParam(':reset_expiry', $array['reset_expiry'], database::PARAM_STR);
		}	
		if (isset($array['address'])) {
			$stmt->bindParam(':address', $array['address'], database::PARAM_STR);
		}
		if (isset($array['phone_number'])) {
			$stmt->bindParam(':phone_number', $array['phone_number'], database::PARAM_STR);
		}	
		if (isset($array['pushover_key'])) {
			$stmt->bindParam(':pushover_key', $array['pushover_key'], database::PARAM_STR);
		}
		if (isset($array['date_added'])) {
			$stmt->bindParam(':date_added', $array['date_added'], database::PARAM_STR);
		}		
		if (isset($array['company_id'])) {
			$stmt->bindParam(':company_id', $array['company_id'], database::PARAM_INT);
		}	
		if (isset($array['view_ticket_reverse'])) {
			$stmt->bindParam(':view_ticket_reverse', $array['view_ticket_reverse'], database::PARAM_INT);
		}	
		if (isset($array['view_ticket_filter_projects'])) {
			$stmt->bindParam(':view_ticket_filter_projects', $array['view_ticket_filter_projects'], database::PARAM_INT);
		}
		if (isset($array['facebook_id'])) {
			$stmt->bindParam(':facebook_id', $array['facebook_id'], database::PARAM_INT);
		}	
		if (isset($array['timesheets_hourly_rate'])) {
			$stmt->bindParam(':timesheets_hourly_rate', $array['timesheets_hourly_rate'], database::PARAM_INT);
		}				
		
		try {
			$stmt->execute();
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		if (isset($array['name'])) {
			$log_array['event_severity'] = 'notice';
			$log_array['event_number'] = E_USER_NOTICE;
			$log_array['event_description'] = 'User Edited "<a href="' . $config->get('address') . '/users/view/' . (int)$array['id'] . '/">' . safe_output($array['name']) . '</a>"';
			$log_array['event_file'] = __FILE__;
			$log_array['event_file_line'] = __LINE__;
			$log_array['event_type'] = 'edit';
			$log_array['event_source'] = 'users';
			$log_array['event_version'] = '1';
			$log_array['log_backtrace'] = false;	
					
			$log->add($log_array);
		}
		
		return true;
	
	}
	
	public function count($array = NULL) {
		global $db;
		
		$tables =	&singleton::get(__NAMESPACE__ . '\tables');
		$error =	&singleton::get(__NAMESPACE__ . '\error');
		$site_id	= SITE_ID;
				
		$query = "SELECT count(*) AS `count` FROM $tables->users WHERE site_id = :site_id";
		
		if (isset($array['id'])) {
			$query .= " AND id = :id";
		}
		if (isset($array['facebook_id'])) {
			$query .= " AND facebook_id = :facebook_id";
		}
		if (isset($array['user_levels'])) {
				
			$return = ' AND user_level IN ( ';
			
			foreach ($array['user_levels'] as $index => $value) {
				$return .= ' :user_level' . (int) $index . ',' ;
			}
			
			if(substr($return, -1) == ',') {	
				$return = substr($return, 0, strlen($return) - 1);
			}
			
			$return .= ')';
			
			$query .= $return;
		}	
		
		if (isset($array['not_group_ids'])) {
				
			$return = ' AND group_id NOT IN ( ';
			
			foreach ($array['not_group_ids'] as $index => $value) {
				$return .= ' :not_group_ids' . (int) $index . ',' ;
			}
			
			if(substr($return, -1) == ',') {	
				$return = substr($return, 0, strlen($return) - 1);
			}
			
			$return .= ')';
			
			$query .= $return;
		}	

		if (isset($array['not_group_id'])) {
			$query .= " AND group_id != :not_group_id";
		}
				
		try {
			$stmt = $db->prepare($query);
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		$stmt->bindParam(':site_id', $site_id);

		if (isset($array['id'])) {
			$id = $array['id'];
			$stmt->bindParam(':id', $id, database::PARAM_INT);
		}
		if (isset($array['facebook_id'])) {
			$facebook_id = $array['facebook_id'];
			$stmt->bindParam(':facebook_id', $facebook_id, database::PARAM_INT);
		}
		
		if (isset($array['user_levels'])) {	
			foreach ($array['user_levels'] as $index => $value) {
				$t_id = (int) $value;
				$stmt->bindParam(':user_level' . (int) $index, $t_id, database::PARAM_INT);
				unset($t_id);
			}
		}
		if (isset($array['not_group_id'])) {
			$not_group_id = $array['not_group_id'];
			$stmt->bindParam(':not_group_id', $not_group_id, database::PARAM_INT);
		}
		
		if (isset($array['not_group_ids'])) {	
			foreach ($array['not_group_ids'] as $index => $value) {
				$t_id = (int) $value;
				$stmt->bindParam(':not_group_ids' . (int) $index, $t_id, database::PARAM_INT);
				unset($t_id);
			}
		}
		
		try {
			$stmt->execute();
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}

		$count = $stmt->fetch(database::FETCH_ASSOC);
		
		return (int) $count['count'];
	}
	
	public function check_username_taken($array) {
		global $db;
		
		$tables =	&singleton::get(__NAMESPACE__ . '\tables');
		$error =	&singleton::get(__NAMESPACE__ . '\error');
		$site_id	= SITE_ID;
		
		$username = $array['username'];
		
		$query = "SELECT count(*) FROM $tables->users WHERE username = :username AND site_id = :site_id";
		
		if (isset($array['id'])) {
			$query .= " AND id != :id";
		}
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		$stmt->bindParam(':username', $username);
		$stmt->bindParam(':site_id', $site_id);

		if (isset($array['id'])) {
			$id = $array['id'];
			$stmt->bindParam(':id', $id, database::PARAM_INT);
		}
		
		try {
			$stmt->execute();
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}

		$count = $stmt->fetch(database::FETCH_ASSOC);
		if ($count['count(*)'] != 0) {
			//already in list
			return true;
		}
		else {
			return false;
		}
	}
	
	public function check_facebook_id_taken($array) {
		global $db;
		
		$tables =	&singleton::get(__NAMESPACE__ . '\tables');
		$error =	&singleton::get(__NAMESPACE__ . '\error');
		$site_id	= SITE_ID;
		
		$facebook_id = $array['facebook_id'];
		
		$query = "SELECT count(*) FROM $tables->users WHERE facebook_id = :facebook_id AND site_id = :site_id";
		
		if (isset($array['id'])) {
			$query .= " AND id != :id";
		}
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		$stmt->bindParam(':facebook_id', $facebook_id);
		$stmt->bindParam(':site_id', $site_id);

		if (isset($array['id'])) {
			$id = $array['id'];
			$stmt->bindParam(':id', $id, database::PARAM_INT);
		}
		
		try {
			$stmt->execute();
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}

		$count = $stmt->fetch(database::FETCH_ASSOC);
		if ($count['count(*)'] != 0) {
			//already in list
			return true;
		}
		else {
			return false;
		}
	}
	
	public function check_email_address_taken($array) {
		global $db;
		
		$tables =	&singleton::get(__NAMESPACE__ . '\tables');
		$error =	&singleton::get(__NAMESPACE__ . '\error');
		$site_id	= SITE_ID;
		
		$query = "SELECT count(*) FROM $tables->users WHERE email = :email AND site_id = :site_id";
		
		if (isset($array['id'])) {
			$query .= " AND id != :id ";
		}
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		$email = strtolower($array['email']);
		
		$stmt->bindParam(':email', $email, database::PARAM_STR);
		$stmt->bindParam(':site_id', $site_id);
		
		if (isset($array['id'])) {
			$stmt->bindParam(':id', $array['id'], database::PARAM_INT);
		}
		
		try {
			$stmt->execute();
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}

		$count = $stmt->fetch(database::FETCH_ASSOC);
		if ($count['count(*)'] != 0) {
			//already in list
			return true;
		}
		else {
			return false;
		}

	}
	
	public function sanitize_user_name($user_name) {

		//converts username to lowercase.
		$user_name = strtolower($user_name);
		
		//only allow a-z, 0-9 - and _ characters.
		$user_name = preg_replace('([^a-z0-9_-])', '', $user_name);
		
		return $user_name;

	}
	
	public function is_user($user_name) {
		global $db;
		
		$tables =	&singleton::get(__NAMESPACE__ . '\tables');
		$error =	&singleton::get(__NAMESPACE__ . '\error');
		$site_id	= SITE_ID;

		$query = "SELECT count(*) as `count` FROM $tables->users WHERE username = :username AND site_id = :site_id";
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		$stmt->bindParam(':username', $user_name);
		$stmt->bindParam(':site_id', $site_id);

		
		try {
			$stmt->execute();
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$users = $stmt->fetch(database::FETCH_ASSOC);
		
		if ($users['count'] == 0) {
			return false;
		}
		else {
			return true;
		}			
	}
	
	function delete($array) {
		global $db;
		
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
		$log		= &singleton::get(__NAMESPACE__ . '\log');

		
		$site_id	= SITE_ID;

		//delete user from departments
		$query 	= "DELETE FROM $tables->users_to_departments WHERE user_id = :id AND site_id = :site_id";
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		$stmt->bindParam(':id', $array['id'], database::PARAM_INT);
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);
		
		try {
			$stmt->execute();
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}

		
		//delete user
		$query 	= "DELETE FROM $tables->users WHERE id = :id AND site_id = :site_id";
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		$stmt->bindParam(':id', $array['id'], database::PARAM_INT);
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);
		
		try {
			$stmt->execute();
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$log_array['event_severity'] = 'notice';
		$log_array['event_number'] = E_USER_NOTICE;
		$log_array['event_description'] = 'User Deleted ID ' . safe_output($array['id']);
		$log_array['event_file'] = __FILE__;
		$log_array['event_file_line'] = __LINE__;
		$log_array['event_type'] = 'delete';
		$log_array['event_source'] = 'users';
		$log_array['event_version'] = '1';
		$log_array['log_backtrace'] = false;	
				
		$log->add($log_array);
		
	}
	
	
	function create_reset_key($array) {
		global $db;
		
		$notifications	= &singleton::get(__NAMESPACE__ . '\notifications');
		
		if (isset($array['username'])) {
			$users_array = $this->get(array('username' => $array['username'], 'authentication_id' => 1, 'allow_login' => 1));
		}
		else {
			return false;
		}
		
		if (count($users_array) == 1) {
			$id 			= (int) $users_array[0]['id'];
									
			$reset_key			= rand_str();
			//lasts 12 hours
			$reset_expiry		= datetime(43200);
			
			$this->edit(array('id' => $id, 'reset_key' => $reset_key, 'reset_expiry' => $reset_expiry));
			
			$notif_array['reset_key']		= $reset_key;
			$notif_array['reset_expiry']	= $reset_expiry;
			$notif_array['user']			= $users_array[0];
			
			$notifications->password_reset($notif_array);

			
			return $reset_key;
		}
		else {
			return false;
		}
	}
	
	//this function will find any tickets (and replies) without a user and match them based on email address
	function match_tickets($user) {
		global $db;
		
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$config		= &singleton::get(__NAMESPACE__ . '\config');
		$error		= &singleton::get(__NAMESPACE__ . '\error');

		$site_id	= SITE_ID;
		
		$query = "UPDATE $tables->tickets SET user_id = :user_id WHERE user_id = 0 AND site_id = :site_id AND email = :email";
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		$email 		= $user['email'];
		$user_id	= $user['id'];
		
		$stmt->bindParam(':email', $email, database::PARAM_STR);
		$stmt->bindParam(':user_id', $user_id, database::PARAM_INT);
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);
		
		try {
			$stmt->execute();
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}		
	
	}
	
	function add_user($array) {
		$config 				= &singleton::get(__NAMESPACE__ . '\config');
		$users_to_departments 	= &singleton::get(__NAMESPACE__ . '\users_to_departments');
		$language 				= &singleton::get(__NAMESPACE__ . '\language');
		$plugins 				= &singleton::get(__NAMESPACE__ . '\plugins');

		$create_user 	= true;
		$created_user 	= false;
		
		if (isset($array['name']) && !empty($array['name'])) {
			if (!isset($array['email']) || empty($array['email']) || check_email_address($array['email'])) {
				if (empty($array['username']) || !$this->check_username_taken(array('username' => $array['username']))) {				
					$add_array = 
						array(
							'name' 					=> $array['name'], 
							'match_tickets'			=> true
						);

					$add_array['email_notifications'] = 1;
					if (isset($array['email_notifications'])) {
						$add_array['email_notifications'] = $array['email_notifications'] ? 1 : 0;					
					}
					if (isset($array['email'])) {
						$add_array['email'] = $array['email'];
					}						
					if (isset($array['phone_number'])) {
						$add_array['phone_number'] = $array['phone_number'];
					}
					if (isset($array['address'])) {
						$add_array['address'] = $array['address'];
					}					
					if ($config->get('pushover_enabled') && isset($array['pushover_key'])) {
						$add_array['pushover_key']	= $array['pushover_key'];
					}
					if (isset($array['company_id'])) {
						$add_array['company_id']	= (int) $array['company_id'];				
					}
					
					if ($config->get('facebook_enabled') && isset($array['facebook_id'])) {
						if (!$this->check_facebook_id_taken(array('facebook_id' => $array['facebook_id']))) {
							$add_array['facebook_id']	= (int) $array['facebook_id'];
						}
						else {
							$create_user				= false;
							$message = $language->get('Facebook ID In Use');			
						}
					}
				
					if ($array['allow_login'] == 1) {
						$add_array['welcome_email'] = 1;
						if (isset($array['welcome_email'])) {
							$add_array['welcome_email']		=  $array['welcome_email'] ? 1 : 0;
						}

						//permissions
						$add_array['group_id'] = 0;
						if (isset($array['group_id'])) {
							$add_array['group_id']		= (int) $array['group_id'];
							
							if (SAAS_MODE) {
								if ($add_array['group_id'] != 0 && $add_array['group_id'] != 1) {
									if ($this->count(array('not_group_ids' => array(0, 1))) >= SAAS_MAX_USERS) {
										$create_user				= false;
										$message					= $language->get('Maximum number of paid users reached');
									}
								}
							}
						}
						
						$add_array['allow_login'] 		= 1;
						$add_array['username'] 			= $array['username'];
						
						if (empty($array['username'])) {
							$create_user				= false;
							$message = $language->get('Username Empty');						
						}
						else {
							if ((int) $array['authentication_id'] == 1) {
								$add_array['authentication_id']	= 1;

								if (!empty($array['password']) && ($array['password'] == $array['password2'])) {
									$add_array['password']	= $array['password'];
								}
								else {
									$create_user				= false;
									$message = $language->get('Invalid Password');
								}
							}
							else if((int) $array['authentication_id'] == 2 || (int) $array['authentication_id'] == 3 || (int) $array['authentication_id'] == 4) {
								$add_array['authentication_id']	= (int) $array['authentication_id'];
							}
						}
					}
					else {
						$add_array['group_id'] 			= 0;
						$add_array['allow_login'] 		= 0;					
					}
					
					if ($create_user) {
						$plugins->run('add_user', $add_array);

						$id = $this->add($add_array);
						
						if (isset($array['departments']) && !empty($array['departments'])) {
							foreach($array['departments'] as $department) {
								$users_to_departments->add(array('user_id' => $id, 'department_id' => (int) $department));
							}
						}
						
						$created_user 		= true;
						$return['id'] 		= $id;
						$message			= $language->get('User Created');
					}
				}
				else {
					$message = $language->get('Username In Use');
				}
			}
			else {
				$message = $language->get('Email Address Invalid');
			}
		}
		else {
			$message = $language->get('Name Empty');
		}
		
		$return['success'] 	= $created_user;
		$return['message']	= $message;
		
		
		return $return;
	}
}
?>