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

class forum_threads extends sts\table_access {

	private $table_name 		= NULL;
	private $allowed_columns 	= NULL;

	function __construct() {
	
		$this->set_table('forum_threads');
		$this->allowed_columns(
				array(
					'title',
					'message',
					'date_added',
					'last_modified',
					'user_id',
					'section_id',
					'views',
				)
			);
		$this->table_name 		= $this->get_table();
		$this->allowed_columns	= $this->get_allowed_columns();
	}
	
	public function get($array = NULL) {
		global $db;
		
		$error 			= &sts\singleton::get('sts\error');
		$tables 		= &sts\singleton::get('sts\tables');
		$table 			= $this->get_table();

		$site_id		= sts\SITE_ID;


		$query = "SELECT ft.* ";
		
		if (isset($array['get_other_data']) && ($array['get_other_data'] == true)) {
			$query .= ", COUNT(fp.id) AS `forum_posts`";
			$query .= ", MAX(fp.date_added) AS `last_post`";
			$query .= ", GREATEST(IFNULL(MAX(fp.date_added), 0), ft.date_added) AS `last_update`";

			$query .= ", fs.name AS `section_name`";
			$query .= ", u.name AS `name`, u.email AS `email`";
			$query .= ", u2.name AS `last_name` ";
		}
		
		$query .= " FROM $table ft";
		
		if (isset($array['get_other_data']) && ($array['get_other_data'] == true)) {
			$query .= " LEFT JOIN $tables->forum_posts fp ON ft.id = fp.thread_id";
			$query .= " LEFT JOIN $tables->forum_sections fs ON ft.section_id = fs.id";
			$query .= " LEFT JOIN $tables->users u ON ft.user_id = u.id";
			$query .= " LEFT JOIN $tables->forum_posts fp2 ON fp2.id = (SELECT fp3.id FROM $tables->forum_posts fp3 WHERE fp3.thread_id = ft.id ORDER BY fp3.id DESC LIMIT 1)";
			$query .= " LEFT JOIN $tables->users u2 ON fp2.user_id = u2.id";
		}
		
		$query .= " WHERE 1 = 1 AND ft.site_id = :site_id";
		
		if (isset($array['id'])) {
			$query .= " AND ft.id = :id";
		}

		if (isset($array['group_id'])) {
			$query .= " AND ft.section_id IN (SELECT section_id FROM $tables->forum_to_permission_groups WHERE group_id = :group_id AND site_id = :site_id)";
		}
		
		if (isset($array['where'])) {
			foreach($array['where'] as $index => $value) {
				if (in_array($index, $this->allowed_columns)) {
					$query .= ' AND ft.'.$index.' = :'.$index;
					unset($index);
					unset($value);
				}
			}
		}
		
		if (isset($array['like'])) {
			$query .= ' AND (';
			foreach($array['like'] as $index => $value) {
				if (in_array($index, $this->allowed_columns)) {
					$query .= 'ft.'.$index.' LIKE :'.$index . ' OR ';
					unset($index);
					unset($value);
				}
			}
			
			if(substr($query, -4) == ' OR ') {	
				$query = substr($query, 0, strlen($query) - 4);
			}
			
			$query .= ')';
		}
		
		if (isset($array['like_search'])) {
			$query .= " AND 
			(
				ft.title LIKE :like_search 
				OR ft.message LIKE :like_search 
				OR ft.id 
					IN (
						SELECT thread_id 
						FROM $tables->forum_posts fp4 
						WHERE fp4.site_id = :site_id 
						AND ft.id = fp4.thread_id 
						AND (
							fp4.message LIKE :like_search
						)
					)
			)";		
		}
		
		$query .= " GROUP BY ft.id";
		
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
				$query .= ' ORDER BY ft.last_update';
			}
			else {
				if (isset($array['get_other_data']) && ($array['get_other_data'] == true)) {
					$query .= " ORDER BY last_update DESC";
				} else {
					$query .= " ORDER BY ft.id DESC";
				}
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

		if (isset($array['group_id'])) {
			$stmt->bindParam(':group_id', $array['group_id'], sts\database::PARAM_INT);
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
		
		if (isset($array['like_search'])) {
			$value = $array['like_search'];
			$value = "%{$value}%";
			$stmt->bindParam(':like_search', $value, sts\database::PARAM_STR);
			unset($value);
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
	
	function delete($array) {
		global $db;
		
		$error 			= &sts\singleton::get('sts\error');
		$tables 		= &sts\singleton::get('sts\tables');
		$log 			= &sts\singleton::get('sts\log');

		$site_id		= sts\SITE_ID;

		//delete posts
		$query 	= "DELETE FROM $tables->forum_posts WHERE site_id = :site_id AND thread_id = :id";
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		$stmt->bindParam(':id', $array['id'], sts\database::PARAM_INT);
		$stmt->bindParam(':site_id', $site_id, sts\database::PARAM_INT);
		
		try {
			$stmt->execute();
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}

		//delete threads
		$query 	= "DELETE FROM $tables->forum_threads WHERE site_id = :site_id AND id = :id";
		try {
			$stmt = $db->prepare($query);
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		$stmt->bindParam(':id', $array['id'], sts\database::PARAM_INT);
		$stmt->bindParam(':site_id', $site_id, sts\database::PARAM_INT);
		
		try {
			$stmt->execute();
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		
		$log_array['event_severity'] = 'notice';
		$log_array['event_number'] = E_USER_NOTICE;
		$log_array['event_description'] = 'Forum Thread Deleted';
		$log_array['event_file'] = __FILE__;
		$log_array['event_file_line'] = __LINE__;
		$log_array['event_type'] = 'delete';
		$log_array['event_source'] = 'forum_threads';
		$log_array['event_version'] = '1';
		$log_array['log_backtrace'] = false;	
				
		$log->add($log_array);
		
	}


}


?>