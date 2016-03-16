<?php
/**
 * 	Forum Thread Subscriptions
 *	Copyright Dalegroup Pty Ltd 2013
 *	support@dalegroup.net
 *
 *
 * @package     sts
 * @author      Michael Dale <mdale@dalegroup.net>
 */

namespace sts\plugins;
use sts;

class forum_thread_subscriptions extends sts\table_access {

	private $table_name 		= NULL;
	private $allowed_columns 	= NULL;

	function __construct() {
	
		$this->set_table('forum_thread_subscriptions');
		$this->allowed_columns(
				array(
					'user_id',
					'thread_id',
					'date_added'
				)
			);
		$this->table_name 		= $this->get_table();
		$this->allowed_columns	= $this->get_allowed_columns();
	}
	
	function get_users($array) {
		global $db;
		
		$config 						= &sts\singleton::get('sts\config');
		$auth 							= &sts\singleton::get('sts\auth');
		$url 							= &sts\singleton::get('sts\url');
		$tables 						= &sts\singleton::get('sts\tables');
		
		$site_id						= sts\SITE_ID;
		
		
		$query = "
			SELECT u.name, u.email, u.email_notifications, u.id FROM $tables->forum_thread_subscriptions fts 
			LEFT JOIN $tables->users u ON fts.user_id = u.id
			WHERE fts.site_id = :site_id AND fts.thread_id = :thread_id
		";
		try {
			$stmt = $db->prepare($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));		
		}

		$stmt->bindParam(':site_id', $site_id, sts\database::PARAM_INT);
		$stmt->bindParam(':thread_id', $array['thread_id'], sts\database::PARAM_INT);

		
		try {
			$stmt->execute();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$users = $stmt->fetchAll(sts\database::FETCH_ASSOC);
		
		return $users;
		
	}

}


?>