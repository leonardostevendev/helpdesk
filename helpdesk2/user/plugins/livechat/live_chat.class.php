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

class live_chat extends sts\table_access {

	private $table_name 		= NULL;
	private $allowed_columns 	= NULL;

	function __construct() {
	
		$this->set_table('live_chat');
		$this->allowed_columns(
				array(
					'date_added',
					'date_finished',
					'last_guest_message',
					'name',
					'email',
					'uuid',
					'active'
				)
			);
		$this->table_name = $this->get_table();
		$this->allowed_columns	= $this->get_allowed_columns();
		
		$plugins 			= &sts\singleton::get('sts\plugins');

		//auto prune
		$plugins->add(
			array(
				'plugin_name'	=> __CLASS__,
				'task_name'		=> __CLASS__ . '\live_chat_prune',
				'section'		=> 'cron_every_day',
				'method'		=> array($this, 'prune')
			)
		);

	}
	
	public function prune() {
		global $db;
		
		$tables 		= &sts\singleton::get('sts\tables');
		$error 			= &sts\singleton::get('sts\error');
		$config 		= &sts\singleton::get('sts\config');
		$log 			= &sts\singleton::get('sts\log');

		$site_id		= sts\SITE_ID;
					
		$query = "UPDATE $this->table_name SET active = 0, date_finished = :finished_date WHERE active = 1 AND last_guest_message < :date_added AND site_id = :site_id";
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		//2 days ago
		$date_added = sts\datetime(-172800);
		$finished_date = sts\datetime();
		
		$stmt->bindParam(':site_id', $site_id, sts\database::PARAM_INT);
		$stmt->bindParam(':date_added', $date_added, sts\database::PARAM_STR);
		$stmt->bindParam(':finished_date', $finished_date, sts\database::PARAM_STR);

		try {
			$stmt->execute();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
	
		$log_array['event_severity'] = 'notice';
		$log_array['event_number'] = E_USER_NOTICE;
		$log_array['event_description'] = 'Live Chat active chats auto prune has finished.';
		$log_array['event_file'] = __FILE__;
		$log_array['event_file_line'] = __LINE__;
		$log_array['event_type'] = 'prune';
		$log_array['event_source'] = 'live_chat';	
		$log_array['event_version'] = '1';
		$log_array['log_backtrace'] = false;	
				
		$log->add($log_array);
	
	}

}


?>