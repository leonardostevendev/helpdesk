<?php
/**
 *  Live Chat
 *	Copyright Dalegroup Pty Ltd 2014
 *	support@dalegroup.net
 *
 *
 * @package     sts
 * @author      Michael Dale <support@dalegroup.net>
 */

namespace sts\plugins;
use sts;

class chatmessage_item {

	function __construct() {

	}
	
	public function add($array) {
		global $db;
		
		$tables 		= &sts\singleton::get('sts\tables');
		$error 			= &sts\singleton::get('sts\error');
		$log 			= &sts\singleton::get('sts\log');
		$config 		= &sts\singleton::get('sts\config');
				
		//$livechat_item 	= &sts\singleton::get(__NAMESPACE__ . '\livechat_item');		

		$site_id		= sts\SITE_ID;
		$date_added 	= sts\datetime();

		$query = "INSERT INTO $tables->chat_messages (site_id, date_added";

		if (isset($array['user_id'])) {
			$query .= ", user_id";
		}	
		if (isset($array['chat_id'])) {
			$query .= ", chat_id";
		}		
		if (isset($array['message'])) {
			$query .= ", message";
		}		
		if (isset($array['guest'])) {
			$query .= ", guest";
		}	
		$query .= ") VALUES (:site_id, :date_added";

		if (isset($array['user_id'])) {
			$query .= ", :user_id";
		}		
		if (isset($array['chat_id'])) {
			$query .= ", :chat_id";
		}
		if (isset($array['message'])) {
			$query .= ", :message";
		}
		if (isset($array['guest'])) {
			$query .= ", :guest";
		}		
		$query .= ")";
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));		
		}
		
		if (isset($array['user_id'])) {
			$user_id = (int) $array['user_id'];
			$stmt->bindParam(':user_id', $user_id, sts\database::PARAM_INT);
		}
		
		$stmt->bindParam(':site_id', $site_id, sts\database::PARAM_INT);
		$stmt->bindParam(':date_added', $date_added, sts\database::PARAM_STR);

		if (isset($array['chat_id'])) {
			$chat_id = $array['chat_id'];
			$stmt->bindParam(':chat_id', $chat_id, sts\database::PARAM_INT);
		}
		if (isset($array['message'])) {
			$message = $array['message'];
			$stmt->bindParam(':message', $message, sts\database::PARAM_STR);
		}
		if (isset($array['guest'])) {
			$guest = $array['guest'];
			$stmt->bindParam(':guest', $guest, sts\database::PARAM_INT);
		}

		try {
			$stmt->execute();
			$id = $db->lastInsertId();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$array['id']	= (int) $id;
				
		if (isset($array['chat_id'])) {
			//$livechat_item->edit(array('id' => $array['chat_id']));
		}
				
		return $id;
	}
	
	public function count($array = NULL) {
		global $db;
		
		$tables 		= &sts\singleton::get('sts\tables');
		$error 			= &sts\singleton::get('sts\error');
		$log 			= &sts\singleton::get('sts\log');
		$config 		= &sts\singleton::get('sts\config');
		
		$site_id		= sts\SITE_ID;
				
		$query = "SELECT count(*) AS `count` FROM $tables->chat_messages WHERE site_id = :site_id";
		
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
		
		$stmt->bindParam(':site_id', $site_id);

		if (isset($array['id'])) {
			$id = $array['id'];
			$stmt->bindParam(':id', $id, sts\database::PARAM_INT);
		}
		
		if (isset($array['user_id'])) {
			$user_id = $array['user_id'];
			$stmt->bindParam(':user_id', $user_id, sts\database::PARAM_INT);
		}
		try {
			$stmt->execute();
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}

		$count = $stmt->fetch(sts\database::FETCH_ASSOC);
		
		return (int) $count['count'];
	}
	
	public function get($array = NULL) {
		global $db;
		
		$tables 		= &sts\singleton::get('sts\tables');
		$error 			= &sts\singleton::get('sts\error');
		$log 			= &sts\singleton::get('sts\log');
		
		$site_id	= sts\SITE_ID;

		$query = "SELECT cm.*";
		
		if (isset($array['get_other_data']) && ($array['get_other_data'] == true)) {
			$query .= ", u.name AS `user_name`, u.email AS `user_email`";
			$query .= ", c.name AS `name`, c.email AS `email`";
		}
		
		$query .= " FROM $tables->chat_messages cm";
		
		if (isset($array['get_other_data']) && ($array['get_other_data'] == true)) {
			$query .= " LEFT JOIN $tables->users u ON (u.id = cm.user_id)";
			$query .= " LEFT JOIN $tables->live_chat c ON (c.id = cm.chat_id)";
		}
		
		$query .= " WHERE 1 = 1 AND cm.site_id = :site_id";
		
		if (isset($array['id'])) {
			$query .= " AND cm.id = :id";
		}
		if (isset($array['user_id'])) {
			$query .= " AND cm.user_id = :user_id";
		}
		if (isset($array['chat_id'])) {
			$query .= " AND cm.chat_id = :chat_id";
		}
		
		$query .= " GROUP BY cm.id ORDER BY cm.id DESC";
		
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
		if (isset($array['chat_id'])) {
			$stmt->bindParam(':chat_id', $array['chat_id'], sts\database::PARAM_INT);
		}
	
		try {
			$stmt->execute();
		}
		catch (Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$messages = $stmt->fetchAll(sts\database::FETCH_ASSOC);
		
		return $messages;
	}
}

?>