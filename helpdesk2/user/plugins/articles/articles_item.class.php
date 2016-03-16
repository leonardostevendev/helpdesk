<?php
/**
 * 	Articles
 *	Copyright Dalegroup Pty Ltd 2013
 *	support@dalegroup.net
 *
 *
 * @package     sts
 * @author      Michael Dale <mdale@dalegroup.net>
 */

namespace sts\plugins;
use sts;

class articles_item extends sts\table_access {

	private $table_name 		= NULL;
	private $allowed_columns 	= NULL;

	function __construct() {
	
		$this->set_table('articles');
		$this->allowed_columns(
				array(
					'subject',
					'date_added',
					'last_modified',
					'description',
					'public',
					'published',
					'category_id',
					'views',
					
				)
			);
		$this->table_name = $this->get_table();
		$this->allowed_columns	= $this->get_allowed_columns();

	}

	public function add($array) {
		global $db;
		
		$error 			= &sts\singleton::get('sts\error');
		$tables 		= &sts\singleton::get('sts\tables');
		$log			= &sts\singleton::get('sts\log');
		$config			= &sts\singleton::get('sts\config');
		
		$site_id		= sts\SITE_ID;
		$date_added 	= sts\datetime();

		
		$query = "INSERT INTO $tables->articles (subject, site_id, date_added, last_modified, user_id";
		
		if (isset($array['description'])) {
			$query .= ", description";
		}
		if (isset($array['public'])) {
			$query .= ", public";
		}
		if (isset($array['published'])) {
			$query .= ", published";
		}
		if (isset($array['category_id'])) {
			$query .= ", category_id";
		}
	
		$query .= ") VALUES (:subject, :site_id, :date_added, :last_modified, :user_id";
		
		if (isset($array['description'])) {
			$query .= ", :description";
		}
		if (isset($array['public'])) {
			$query .= ", :public";
		}
		if (isset($array['published'])) {
			$query .= ", :published";
		}
		if (isset($array['category_id'])) {
			$query .= ", :category_id";
		}
	
		
		$query .= ")";
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));		
		}
		
		$stmt->bindParam(':subject', $array['subject'], sts\database::PARAM_STR);
		$stmt->bindParam(':site_id', $site_id, sts\database::PARAM_INT);
		$stmt->bindParam(':date_added', $date_added, sts\database::PARAM_STR);
		$stmt->bindParam(':last_modified', $date_added, sts\database::PARAM_STR);
		$stmt->bindParam(':user_id', $array['user_id'], sts\database::PARAM_INT);
		
		if (isset($array['description'])) {
			$stmt->bindParam(':description', $array['description'], sts\database::PARAM_STR);
		}
		if (isset($array['public'])) {
			$stmt->bindParam(':public', $array['public'], sts\database::PARAM_INT);
		}
		if (isset($array['published'])) {
			$stmt->bindParam(':published', $array['published'], sts\database::PARAM_INT);
		}
		if (isset($array['category_id'])) {
			$stmt->bindParam(':category_id', $array['category_id'], sts\database::PARAM_INT);
		}
		
		try {
			$stmt->execute();
			$id = $db->lastInsertId();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
				
		$log_array['event_severity'] = 'notice';
		$log_array['event_number'] = E_USER_NOTICE;
		$log_array['event_description'] = 'Article Added "<a href="' . $config->get('address') . '/p/kb_view/' . (int)$id . '/">' . sts\safe_output($array['subject']) . '</a>"';
		$log_array['event_file'] = __FILE__;
		$log_array['event_file_line'] = __LINE__;
		$log_array['event_type'] = 'add';
		$log_array['event_source'] = 'articles_item';
		$log_array['event_version'] = '1';
		$log_array['log_backtrace'] = false;	
				
		$log->add($log_array);
				
		return $id;	
	}
	
	public function edit($array) {
		global $db;
					
		$error 			= &sts\singleton::get('sts\error');
		$tables 		= &sts\singleton::get('sts\tables');
		$log			= &sts\singleton::get('sts\log');
		$config			= &sts\singleton::get('sts\config');
		
		$site_id		= sts\SITE_ID;
		$last_modified 	= sts\datetime();
				
		$query = "UPDATE $tables->articles SET site_id = :site_id";

		if (isset($array['last_modified'])) {
			$query .= ", last_modified = :last_modified";
		}
		if (isset($array['subject'])) {
			$query .= ", subject = :subject";
		}
		if (isset($array['description'])) {
			$query .= ", description = :description";
		}
		if (isset($array['public'])) {
			$query .= ", public = :public";
		}
		if (isset($array['published'])) {
			$query .= ", published = :published";
		}
		if (isset($array['category_id'])) {
			$query .= ", category_id = :category_id";
		}
		if (isset($array['views'])) {
			$query .= ", views = :views";
		}		
		$query .= " WHERE id = :id AND site_id = :site_id";
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		if (isset($array['last_modified'])) {
			$stmt->bindParam(':last_modified', $last_modified, sts\database::PARAM_STR);
		}
		$stmt->bindParam(':id', $array['id'], sts\database::PARAM_INT);
		$stmt->bindParam(':site_id', $site_id, sts\database::PARAM_INT);
		
		if (isset($array['subject'])) {
			$stmt->bindParam(':subject', $array['subject'], sts\database::PARAM_STR);
		}	
		if (isset($array['description'])) {
			$stmt->bindParam(':description', $array['description'], sts\database::PARAM_STR);
		}
		if (isset($array['public'])) {
			$stmt->bindParam(':public', $array['public'], sts\database::PARAM_STR);
		}
		if (isset($array['published'])) {
			$stmt->bindParam(':published', $array['published'], sts\database::PARAM_STR);
		}
		if (isset($array['category_id'])) {
			$stmt->bindParam(':category_id', $array['category_id'], sts\database::PARAM_STR);
		}			
		if (isset($array['views'])) {
			$stmt->bindParam(':views', $array['views'], sts\database::PARAM_STR);
		}
		try {
			$stmt->execute();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		if (isset($array['subject'])) {
			$log_array['event_severity'] = 'notice';
			$log_array['event_number'] = E_USER_NOTICE;
			$log_array['event_description'] = 'Article Edited "' . sts\safe_output($array['subject']) . '"';
			$log_array['event_file'] = __FILE__;
			$log_array['event_file_line'] = __LINE__;
			$log_array['event_type'] = 'edit';
			$log_array['event_source'] = 'articles_item';
			$log_array['event_version'] = '1';
			$log_array['log_backtrace'] = false;	
					
			$log->add($log_array);
		}
				
		
		return true;
	}
	
	public function get($array = NULL) {
		global $db;
		
		$error 			= &sts\singleton::get('sts\error');
		$tables 		= &sts\singleton::get('sts\tables');
		$site_id		= sts\SITE_ID;
		$order_array 	= array();


		$query = "SELECT a.* ";
		
		if (isset($array['get_other_data']) && ($array['get_other_data'] == true)) {			
			$query .= ", ac.name AS `category_name`";
		}
		
		$query .= " FROM $tables->articles a";
		
		if (isset($array['get_other_data']) && ($array['get_other_data'] == true)) {
			$query .= " LEFT JOIN $tables->article_categories ac ON ac.id = a.category_id";
		}
		
		$query .= " WHERE 1 = 1 AND a.site_id = :site_id";
		
		if (isset($array['id'])) {
			$query .= " AND a.id = :id";
		}
		if (isset($array['user_id'])) {
			$query .= " AND a.user_id = :user_id";
		}
		if (isset($array['public'])) {
			$query .= " AND a.public = :public";
		}
		if (isset($array['published'])) {
			$query .= " AND a.published = :published";
		}
		if (isset($array['category_id'])) {
			$query .= " AND a.category_id = :category_id";
		}
		if (isset($array['like_search'])) {
			$query .= " AND (a.subject LIKE :like_search OR a.description LIKE :like_search)";
		}
		
		if (isset($array['order_by']) && in_array($array['order_by'], $order_array)) {
			if (isset($array['order']) && $array['order'] == 'desc') {
				$query .= ' ORDER BY a.' . $array['order_by'] . ' DESC';
			}
			else {
				$query .= ' ORDER BY a.' . $array['order_by'];
			}			
		}
		else {
			$query .= " ORDER BY a.subject";
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
		if (isset($array['user_id'])) {
			$stmt->bindParam(':user_id', $array['user_id'], sts\database::PARAM_INT);
		}
		if (isset($array['public'])) {
			$stmt->bindParam(':public', $array['public'], sts\database::PARAM_INT);
		}
		if (isset($array['published'])) {
			$stmt->bindParam(':published', $array['published'], sts\database::PARAM_INT);
		}
		if (isset($array['category_id'])) {
			$stmt->bindParam(':category_id', $array['category_id'], sts\database::PARAM_INT);
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
		
		$articles = $stmt->fetchAll(sts\database::FETCH_ASSOC);
		
		return $articles;		
		
	}
	
	
	function delete($array) {
		global $db;
		
		$error 			= &sts\singleton::get('sts\error');
		$tables 		= &sts\singleton::get('sts\tables');
		$log 			= &sts\singleton::get('sts\log');
		$site_id		= sts\SITE_ID;
		
		if (!isset($array['id'])) return false;
		
		//delete file links
		$query 	= "DELETE FROM $tables->files_to_articles WHERE site_id = :site_id AND article_id = :id";
				
		try {
			$stmt = $db->prepare($query);
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		$stmt->bindParam(':site_id', $site_id, sts\database::PARAM_INT);
		$stmt->bindParam(':id', $array['id'], sts\database::PARAM_INT);
		
		try {
			$stmt->execute();
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		//delete article
		$query 	= "DELETE FROM $tables->articles WHERE site_id = :site_id";
		
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
		$log_array['event_description'] = 'Article Deleted ID ' . sts\safe_output($array['id']);
		$log_array['event_file'] = __FILE__;
		$log_array['event_file_line'] = __LINE__;
		$log_array['event_type'] = 'delete';
		$log_array['event_source'] = 'articles_item';
		$log_array['event_version'] = '1';
		$log_array['log_backtrace'] = false;	
				
		$log->add($log_array);
		
	}
	

}

?>