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

class articles_files extends sts\table_access {

	private $table_name 		= NULL;
	private $allowed_columns 	= NULL;

	function __construct() {
	
		$this->set_table('files_to_articles');
		$this->allowed_columns(
				array(
					'article_id',
					'file_id'
				)
			);
		$this->table_name = $this->get_table();
		$this->allowed_columns	= $this->get_allowed_columns();

	}
	
	public function get_files($array) {
		global $db;
		
		$tables =	&sts\singleton::get('sts\tables');
		$error 	=	&sts\singleton::get('sts\error');
		$log 	=	&sts\singleton::get('sts\log');

		$site_id	= sts\SITE_ID;
		
		$query = "SELECT $tables->storage.*, $tables->files_to_articles.id AS `link_id` FROM $tables->files_to_articles LEFT JOIN $tables->storage ON $tables->files_to_articles.file_id = $tables->storage.id WHERE 1 = 1 AND $tables->storage.site_id = :site_id";
		
		if (isset($array['id'])) {
			$query .= " AND $tables->files_to_articles.article_id = :id";
		}
		
		if (isset($array['file_id'])) {
			$query .= " AND $tables->files_to_articles.file_id = :file_id";
		}
		
		
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
		if (isset($array['file_id'])) {
			$stmt->bindParam(':file_id', $array['file_id'], sts\database::PARAM_INT);
		}
	
		try {
			$stmt->execute();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$files = $stmt->fetchAll(sts\database::FETCH_ASSOC);
		
		return $files;
		
	}

}

?>