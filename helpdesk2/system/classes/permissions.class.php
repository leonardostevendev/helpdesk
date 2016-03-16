<?php
/**
 * 	Permissions Class
 *	Copyright Dalegroup Pty Ltd 2014
 *	support@dalegroup.net
 *
 *
 * @package     dgx
 * @author      Michael Dale <mdale@dalegroup.net>
 */
 
namespace sts;

class permissions {

	private $permitted_tasks = array();

	/**
	 * Checks if the current user can perform a task.
	 *
	 * @param   string   $task 			The task to test
	 * @param   int   	$group_id 		You can override the group_id if needed
	 * @return  bool					TRUE if allowed or FALSE if not.
	 */
	function can($task, $group_id = '') {

		$permitted_tasks = $this->get_permitted_tasks(array('group_id' => $group_id));

		if (in_array($task, $permitted_tasks)) {
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * Returns the list of tasks for a specific group. Cached.
	 *
	 * @param   int		$group_id	The ID of the group to get the tasks for
	 * @return  array				The array of tasks
	 */
	public function get_permitted_tasks($array = NULL) {
		global $db;
			
		$error 			= &singleton::get(__NAMESPACE__ . '\error');
		$auth 			= &singleton::get(__NAMESPACE__ . '\auth');
		$tables 		= &singleton::get(__NAMESPACE__ . '\tables');
		$site_id		= SITE_ID;
		
		if (!isset($array['group_id']) || empty($array['group_id'])) {
			$group_id = $auth->get('group_id');
		}
		else {
			$group_id = (int) $array['group_id'];
		}

		if (isset($this->permitted_tasks[$group_id])) {
			return $this->permitted_tasks[$group_id];
		}
		else {
			$query = "SELECT pt.name FROM $tables->permission_tasks pt";

			$query .= " INNER JOIN $tables->tasks_to_groups ttg ON ttg.task_id = pt.id";

			$query .= " WHERE ttg.group_id = :group_id AND pt.site_id = :site_id";

			try {
				$stmt = $db->prepare($query);
			}
			catch (\Exception $e) {
				$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));		
			}
			
			$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);		
			$stmt->bindParam(':group_id', $group_id, database::PARAM_INT);

			try {
				$stmt->execute();
			}
			catch (\Exception $e) {
				$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
			}

			$permissions_array = $stmt->fetchAll(database::FETCH_ASSOC);
			
			$task_list = array();
			foreach ($permissions_array as $index => $value) {
				$task_list[] = $value['name'];
			}
			
			$this->permitted_tasks[$group_id] = $task_list;
			
			return $task_list;
		}

	}
	
	function get_permitted_task_list($array = NULL) {
		global $db;
		
		$error 			= &singleton::get(__NAMESPACE__ . '\error');
		$auth 			=	&singleton::get(__NAMESPACE__ . '\auth');
		$tables 		=	&singleton::get(__NAMESPACE__ . '\tables');
		$site_id		= SITE_ID;
		
		if (!isset($array['group_id']) || empty($array['group_id'])) {
			$group_id = $auth->get('group_id');
		}
		else {
			$group_id = (int) $array['group_id'];
		}			

		$query = "
			SELECT pt.name, pt.id
			FROM $tables->permission_tasks pt INNER JOIN $tables->tasks_to_groups ttg
			ON ttg.task_id = pt.id AND ttg.site_id = :site_id
			WHERE ttg.group_id = :group_id AND pt.site_id = :site_id ORDER BY pt.name";

		try {
			$stmt = $db->prepare($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));		
		}
		
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);		
		$stmt->bindParam(':group_id', $group_id, database::PARAM_INT);

		try {
			$stmt->execute();
		}
		catch (Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}

		$permissions = $stmt->fetchAll(database::FETCH_ASSOC);
		
		return $permissions;

	}
	
	
	/**
	 * Returns the list of tasks not in a specific group.
	 *
	 * @return  array				The array of tasks
	 */
	public function get_available_tasks($array) {
		global $db;
		
		$error 			= &singleton::get(__NAMESPACE__ . '\error');
		$auth 			=	&singleton::get(__NAMESPACE__ . '\auth');
		$tables 		=	&singleton::get(__NAMESPACE__ . '\tables');
		$site_id		= SITE_ID;
		
		if (isset($array['all_tasks']) && ($array['all_tasks'] == true)) {
			$query = "SELECT pt.name, pt.id FROM $tables->permission_tasks pt WHERE site_id = :site_id ORDER BY pt.name";
		}
		else {
			$query = "
				SELECT pt.name, pt.id
				FROM $tables->permission_tasks pt
				WHERE pt.id NOT IN (SELECT task_id FROM $tables->tasks_to_groups WHERE tasks_to_groups.group_id = :group_id AND site_id = :site_id)
				AND site_id = :site_id
				ORDER BY pt.name";
		}

		try {
			$stmt = $db->prepare($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));		
		}

		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);		
		
		if (!isset($array['all_tasks']) || ($array['all_tasks'] == false)) {
			$stmt->bindParam(':group_id', $array['group_id'], database::PARAM_INT);
		}

		try {
			$stmt->execute();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}

		$tasks = $stmt->fetchAll(database::FETCH_ASSOC);
		
		return $tasks;
	}

	/**
	 * Adds a new task that can be assigned a a group
	 *
	 * @param   array		$array	Array containing 'name' as element of task name to add
	 * @return  int			The id of the task
	 */
	public function add_task($array) {
		global $db;
		
		$error 			= &singleton::get(__NAMESPACE__ . '\error');
		$tables 		= &singleton::get(__NAMESPACE__ . '\tables');
		$site_id		= SITE_ID;
		
		$query = "SELECT count(*) FROM $tables->permission_tasks WHERE name = :name AND site_id = :site_id LIMIT 1";	

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
			$query = "INSERT INTO $tables->permission_tasks (name, site_id";
			
			if (isset($array['description'])) {
				
			}
			
			$query .= ") VALUES (:name, :site_id";
			
			$query .= ")";

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
				return $db->lastInsertId();
			}
			catch (\Exception $e) {
				$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
			}
		}
	}

	public function remove_task_from_group($array) {
		global $db;
		
		$error 			= &singleton::get(__NAMESPACE__ . '\error');
		$tables 		= &singleton::get(__NAMESPACE__ . '\tables');
		$site_id		= SITE_ID;

		$query = "DELETE FROM $tables->tasks_to_groups WHERE task_id = :task_id AND group_id = :group_id AND site_id = :site_id";
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));		
		}

		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);		
		$stmt->bindParam(':task_id', $array['task_id'], database::PARAM_INT);
		$stmt->bindParam(':group_id', $array['group_id'], database::PARAM_INT);
		
		try {
			$stmt->execute();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}

		return true;
	}

	public function add_task_to_group($array) {
		global $db;
		
		$error 			= &singleton::get(__NAMESPACE__ . '\error');
		$tables 		= &singleton::get(__NAMESPACE__ . '\tables');
		$site_id		= SITE_ID;
		
		$query = "SELECT count(*) FROM $tables->tasks_to_groups WHERE task_id = :task_id AND group_id = :group_id AND site_id = :site_id LIMIT 1";	
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));		
		}
		
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);		
		$stmt->bindParam(':task_id', $array['task_id'], database::PARAM_INT);
		$stmt->bindParam(':group_id', $array['group_id'], database::PARAM_INT);

		try {
			$stmt->execute();
		}
		catch (Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$count = $stmt->fetch(database::FETCH_ASSOC);
		
		if ($count['count(*)'] > 0) {
			//already in list
			return false;
		}
		else {
			$query = "INSERT INTO $tables->tasks_to_groups (task_id, group_id, site_id) VALUES (:task_id, :group_id, :site_id)";
			
			try {
				$stmt = $db->prepare($query);
			}
			catch (\Exception $e) {
				$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));		
			}			
			
			$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);		
			$stmt->bindParam(':task_id', $array['task_id'], database::PARAM_INT);
			$stmt->bindParam(':group_id', $array['group_id'], database::PARAM_INT);
			
			try {
				$stmt->execute();
				return $db->lastInsertId();
			}
			catch (\Exception $e) {
				$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
			}
		}

	}
	

}