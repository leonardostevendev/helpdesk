<?php
/**
 * 	Permission Groups Class
 *	Copyright Dalegroup Pty Ltd 2014
 *	support@dalegroup.net
 *
 *
 * @package     dgx
 * @author      Michael Dale <mdale@dalegroup.net>
 */
 
namespace sts;

class permission_groups {

	function add($array) {
		global $db;
		
		$error 			= &singleton::get(__NAMESPACE__ . '\error');
		$log			= &singleton::get(__NAMESPACE__ . '\log');
		$tables 		= &singleton::get(__NAMESPACE__ . '\tables');
		$auth 			= &singleton::get(__NAMESPACE__ . '\auth');
		$config 		= &singleton::get(__NAMESPACE__ . '\config');

		$site_id		= SITE_ID;
		
		$query = "SELECT count(*) FROM $tables->permission_groups WHERE name = :name AND site_id = :site_id LIMIT 1";	
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));		
		}
		
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);		
		$stmt->bindParam(':name', $array['name'], database::PARAM_STR);

		try {
			$stmt->execute();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$count = $stmt->fetch(database::FETCH_ASSOC);
		
		if ($count['count(*)'] > 0) {
			//already in list
			return false;
		}
		else {
			$query = "INSERT INTO $tables->permission_groups (name, site_id";

			if (isset($array['allow_modify'])) {
				$query .= ", allow_modify";
			}
			if (isset($array['global_message'])) {
				$query .= ", global_message";
			}
			
			$query .= ") VALUES (:name, :site_id";
			
			if (isset($array['allow_modify'])) {
				$query .= ", :allow_modify";
			}
			if (isset($array['global_message'])) {
				$query .= ", :global_message";
			}
			
			$query .= ")";
				
			try {
				$stmt = $db->prepare($query);
			}
			catch (\Exception $e) {
				$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));		
			}
			
			$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);		
			$stmt->bindParam(':name', $array['name'], database::PARAM_INT);
		
			if (isset($array['allow_modify'])) {
				$allow_modify = (int) $array['allow_modify'];
				$stmt->bindParam(':allow_modify', $allow_modify, database::PARAM_INT);
			}
			if (isset($array['global_message'])) {
				$global_message = $array['global_message'];
				$stmt->bindParam(':global_message', $global_message, database::PARAM_STR);
			}
			
			try {
				$stmt->execute();
			}
			catch (\Exception $e) {
				$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
			}
					
			$id = $db->lastInsertId();
			
			$log_array['event_severity'] = 'notice';
			$log_array['event_number'] = E_USER_NOTICE;
			$log_array['event_description'] = 'Permission Group Added "' . safe_output($array['name']) . '"';
			$log_array['event_file'] = __FILE__;
			$log_array['event_file_line'] = __LINE__;
			$log_array['event_type'] = 'add';
			$log_array['event_source'] = 'permission_groups';
			$log_array['event_version'] = '1';
			$log_array['log_backtrace'] = false;	
					
			$log->add($log_array);
						
			return $id;
		}

	}

	function delete($array) {
		global $db;
		
		if (!isset($array['id'])) return false;
		
		$error 			= &singleton::get(__NAMESPACE__ . '\error');
		$log			= &singleton::get(__NAMESPACE__ . '\log');
		$tables 		= &singleton::get(__NAMESPACE__ . '\tables');
		$auth 			= &singleton::get(__NAMESPACE__ . '\auth');
		$config 		= &singleton::get(__NAMESPACE__ . '\config');

		$site_id		= SITE_ID;
				
		/*
			Delete Tasks
		*/
		$query = "DELETE FROM $tables->tasks_to_groups WHERE group_id = :group_id AND site_id = :site_id";

		try {
			$stmt = $db->prepare($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));		
		}

		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);		
		$stmt->bindParam(':group_id', $array['id'], database::PARAM_INT);
		
		try {
			$stmt->execute();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		/*
			Delete Group
		*/
		$query = "DELETE FROM $tables->permission_groups WHERE id = :group_id AND site_id = :site_id";
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));		
		}
	
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);			
		$stmt->bindParam(':group_id', $array['id'], database::PARAM_INT);
		
		try {
			$stmt->execute();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}

		
		return true;
	}

	public function edit($array) {
		global $db;
		
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$log		= &singleton::get(__NAMESPACE__ . '\log');
		$config		= &singleton::get(__NAMESPACE__ . '\config');

		$site_id	= SITE_ID;

		
		$query = "UPDATE $tables->permission_groups SET name = :name";

		if (isset($array['global_message'])) {
			$query .= ", global_message = :global_message";
		}	

		$query .= " WHERE id = :id AND site_id = :site_id";
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		$stmt->bindParam(':name', $array['name'], database::PARAM_STR);
	
		if (isset($array['global_message'])) {
			$global_message = $array['global_message'];
			$stmt->bindParam(':global_message', $global_message, database::PARAM_STR);
		}

		$stmt->bindParam(':id', $array['id'], database::PARAM_INT);
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);

	
		try {
			$stmt->execute();
		}
		catch (Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$log_array['event_severity'] = 'notice';
		$log_array['event_number'] = E_USER_NOTICE;
		$log_array['event_description'] = 'Permission Group Edited "' . safe_output($array['name']) . '"';
		$log_array['event_file'] = __FILE__;
		$log_array['event_file_line'] = __LINE__;
		$log_array['event_type'] = 'edit';
		$log_array['event_source'] = 'permission_groups';
		$log_array['event_version'] = '1';
		$log_array['log_backtrace'] = false;	
				
		$log->add($log_array);
				
		
		return true;
	
	}
	
	public function get($array = NULL) {
		global $db;
		
		$error 		=	&singleton::get(__NAMESPACE__ . '\error');
		$tables 	=	&singleton::get(__NAMESPACE__ . '\tables');
		$site_id	= SITE_ID;

		$query = "SELECT pg.*";
		
		if (isset($array['get_other_data']) && ($array['get_other_data'] == true)) {
			$query .= ", count(u.id) as `members_count`";
		}
		
		$query .= " FROM $tables->permission_groups pg";
		
		if (isset($array['get_other_data']) && ($array['get_other_data'] == true)) {
			$query .= " LEFT JOIN $tables->users u";
			
			$query .= " ON pg.id = u.group_id";
		}
	
		$query .= " WHERE 1 = 1 AND pg.site_id = :site_id";

		if (isset($array['name'])) {
			$query .= " AND pg.name = :name";
		}		
		if (isset($array['id'])) {
			$query .= " AND pg.id = :id";
		}
		
		$query .= " GROUP BY pg.id ORDER BY pg.id";
		
		//echo $query;
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);

		if (isset($array['name'])) {
			$stmt->bindParam(':name', $array['name'], database::PARAM_STR);
		}
			
		if (isset($array['id'])) {
			$stmt->bindParam(':id', $array['id'], database::PARAM_INT);
		}
	
		try {
			$stmt->execute();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$groups = $stmt->fetchAll(database::FETCH_ASSOC);
		
		return $groups;
	}

}