<?php
namespace sts\plugins;
use sts;

class articles_categories {

	public function add($array) {
		global $db;
	
		$log		= &sts\singleton::get('sts\log');
		$error 		= &sts\singleton::get('sts\error');
		$tables 	= &sts\singleton::get('sts\tables');
		$site_id	= sts\SITE_ID;

		$query = "INSERT INTO $tables->article_categories (name, site_id";

		$query .= ") VALUES (:name, :site_id";
		
		$query .= ")";
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));		
		}
		
		$stmt->bindParam(':site_id', $site_id, sts\database::PARAM_INT);
		
		$name = $array['name'];
		$stmt->bindParam(':name', $name, sts\database::PARAM_STR);
		
		try {
			$stmt->execute();
			$id = $db->lastInsertId();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
				
		$log_array['event_severity'] = 'notice';
		$log_array['event_number'] = E_USER_NOTICE;
		$log_array['event_description'] = 'Article Category Added "' . sts\safe_output($array['name']) . '"';
		$log_array['event_file'] = __FILE__;
		$log_array['event_file_line'] = __LINE__;
		$log_array['event_type'] = 'add';
		$log_array['event_source'] = 'articles_categories';
		$log_array['event_version'] = '1';
		$log_array['log_backtrace'] = false;	
				
		$log->add($log_array);
				
		return $id;	
	}
	
	public function edit($array) {
		global $db;
		
		$log		= &sts\singleton::get('sts\log');
		$error 		= &sts\singleton::get('sts\error');
		$tables 	= &sts\singleton::get('sts\tables');
		$site_id	= sts\SITE_ID;

		
		$query = "UPDATE $tables->article_categories SET name = :name";

		
		$query .= " WHERE id = :id AND site_id = :site_id";
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		$stmt->bindParam(':name', $array['name'], sts\database::PARAM_STR);
		$stmt->bindParam(':id', $array['id'], sts\database::PARAM_INT);
		$stmt->bindParam(':site_id', $site_id, sts\database::PARAM_INT);

	
		try {
			$stmt->execute();
		}
		catch (Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$log_array['event_severity'] = 'notice';
		$log_array['event_number'] = E_USER_NOTICE;
		$log_array['event_description'] = 'Article Category Edited "' . sts\safe_output($array['name']) . '"';
		$log_array['event_file'] = __FILE__;
		$log_array['event_file_line'] = __LINE__;
		$log_array['event_type'] = 'edit';
		$log_array['event_source'] = 'articles_categories';
		$log_array['event_version'] = '1';
		$log_array['log_backtrace'] = false;	
				
		$log->add($log_array);
				
		
		return true;
	}
	
	public function get($array = NULL) {
		global $db;
		
		$error 		=	&sts\singleton::get('sts\error');
		$tables 	=	&sts\singleton::get('sts\tables');
		$site_id	= 	sts\SITE_ID;

		$query = "SELECT ac.*";
		
		$query .= " FROM $tables->article_categories ac";
	
		$query .= " WHERE 1 = 1 AND ac.site_id = :site_id";
		
		if (isset($array['id'])) {
			$query .= " AND ac.id = :id";
		}
		
		$query .= " GROUP BY ac.id ORDER BY ac.name";
		
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
	
		try {
			$stmt->execute();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$cats = $stmt->fetchAll(sts\database::FETCH_ASSOC);
		
		return $cats;
	}
	
	public function delete($array) {
		global $db;
		
		$log		= &sts\singleton::get('sts\log');
		$error 		= &sts\singleton::get('sts\error');
		$tables 	= &sts\singleton::get('sts\tables');
		$site_id	= sts\SITE_ID;

		
		//delete ticket priorities
		$query 	= "DELETE FROM $tables->article_categories WHERE site_id = :site_id";
		
		if (isset($array['id'])) {
			$query .= " AND id = :id";
		}

		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		$stmt->bindParam(':site_id', $site_id, sts\database::PARAM_INT);

		if (isset($array['id'])) {
			$stmt->bindParam(':id', $array['id'], sts\database::PARAM_INT);
		}
		
		try {
			$stmt->execute();
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$log_array['event_severity'] = 'notice';
		$log_array['event_number'] = E_USER_NOTICE;
		$log_array['event_description'] = 'Article Category Deleted ID ' . sts\safe_output($array['id']);
		$log_array['event_file'] = __FILE__;
		$log_array['event_file_line'] = __LINE__;
		$log_array['event_type'] = 'delete';
		$log_array['event_source'] = 'articles_categories';
		$log_array['event_version'] = '1';
		$log_array['log_backtrace'] = false;	
				
		$log->add($log_array);
	}

}

?>