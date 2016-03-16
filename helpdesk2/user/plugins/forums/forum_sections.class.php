<?php
/**
 * 	Forum Sections
 *	Copyright Dalegroup Pty Ltd 2014
 *	support@dalegroup.net
 *
 *
 * @package     sts
 * @author      Michael Dale <mdale@dalegroup.net>
 */

namespace sts\plugins;
use sts;

class forum_sections extends sts\table_access {

	private $table_name 		= NULL;
	private $allowed_columns 	= NULL;

	function __construct() {
	
		$this->set_table('forum_sections');
		$this->allowed_columns(
				array(
					'id',
					'name'
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


		$query = "SELECT fs.* ";
		
		if (isset($array['get_other_data']) && ($array['get_other_data'] == true)) {
			$query .= ", COUNT(DISTINCT ft.id) AS `forum_threads`";
			$query .= ", COUNT(DISTINCT fp.id) AS `forum_posts`";
			$query .= ", MAX(fp.date_added) AS `last_post`";
			$query .= ", MAX(ft.date_added) AS `last_thread`";
			$query .= ", GREATEST(IFNULL(MAX(fp.date_added), 0), IFNULL(MAX(ft.date_added), 0)) AS `last_update`";
		}
		
		$query .= " FROM $table fs";
		
		if (isset($array['get_other_data']) && ($array['get_other_data'] == true)) {
			$query .= " LEFT JOIN $tables->forum_threads ft ON fs.id = ft.section_id";
			$query .= " LEFT JOIN $tables->forum_posts fp ON ft.id = fp.thread_id";
		}
		
		$query .= " WHERE 1 = 1 AND fs.site_id = :site_id";
		
		if (isset($array['id'])) {
			$query .= " AND fs.id = :id";
		}
		
		if (isset($array['group_id'])) {
			$query .= " AND fs.id IN (SELECT section_id FROM $tables->forum_to_permission_groups WHERE group_id = :group_id AND site_id = :site_id)";
		}
		
		if (isset($array['where'])) {
			foreach($array['where'] as $index => $value) {
				if (in_array($index, $this->allowed_columns)) {
					$query .= ' AND fs.'.$index.' = :'.$index;
					unset($index);
					unset($value);
				}
			}
		}
		
		if (isset($array['like'])) {
			$query .= ' AND (';
			foreach($array['like'] as $index => $value) {
				if (in_array($index, $this->allowed_columns)) {
					$query .= 'fs.'.$index.' LIKE :'.$index . ' OR ';
					unset($index);
					unset($value);
				}
			}
			
			if(substr($query, -4) == ' OR ') {	
				$query = substr($query, 0, strlen($query) - 4);
			}
			
			$query .= ')';
		}
		
		$query .= " GROUP BY fs.id";
		
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
				$query .= ' ORDER BY fs.id';
			}
			else {
				$query .= " ORDER BY fs.id DESC";
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
		$query 	= "DELETE FROM $tables->forum_posts 
			WHERE site_id = :site_id 
			AND thread_id IN (
					SELECT id FROM $tables->forum_threads WHERE section_id = :id AND site_id = :site_id
			)";
		
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
		$query 	= "DELETE FROM $tables->forum_threads WHERE site_id = :site_id AND section_id = :id";
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
		
		
		//delete section
		$query 	= "DELETE FROM $tables->forum_sections WHERE id = :id AND site_id = :site_id";
		
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
		$log_array['event_description'] = 'Forum Section Deleted';
		$log_array['event_file'] = __FILE__;
		$log_array['event_file_line'] = __LINE__;
		$log_array['event_type'] = 'delete';
		$log_array['event_source'] = 'forum_sections';
		$log_array['event_version'] = '1';
		$log_array['log_backtrace'] = false;	
				
		$log->add($log_array);
		
	}


}


?>