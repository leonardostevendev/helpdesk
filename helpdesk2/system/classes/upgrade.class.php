<?php
/**
 * 	Upgrade Class
 *	Copyright Dalegroup Pty Ltd 2014
 *	support@dalegroup.net
 *
 *
 * @package     tickets
 * @author      Michael Dale <support@dalegroup.net>
 */

namespace sts;

class upgrade {

	private $db_version 		= '116';
	private $program_version 	= '6.0';

	function __construct() {
	
	}
	public function get_db_version() {
		return $this->db_version;
	}
	public function get_program_version() {
		return $this->program_version;
	}
	
	public function version_info() {
		$config 	= &singleton::get(__NAMESPACE__ . '\config');
		$log		= &singleton::get(__NAMESPACE__ . '\log');

		$update_message 							= $config->get('last_update_response');
		
		$return['code_database_version']			= $this->db_version;
		$return['code_program_version']				= $this->program_version;
		$return['installed_program_version']		= $config->get('program_version');
		$return['installed_database_version']		= $config->get('database_version');
		$return['latest_program_version']			= '';
		$return['latest_database_version']			= '';
		
		if (!empty($update_message)) {
								
			if (isset($update_message['version'])) {
				$return['latest_program_version']			= (string) $update_message['version'];
			}		
			
		}	
		
		return $return;
	}
	
	public function get_update_info() {
		$config 	= &singleton::get(__NAMESPACE__ . '\config');

		$update_array = $config->get('last_update_response');
		
		if (is_array($update_array)) {
			return $update_array;
		}
		else {
			return array();
		}
	}
	
	public function update_available() {
		$config 	= &singleton::get(__NAMESPACE__ . '\config');

		$update_array = $config->get('last_update_response');
		
		$update = false;
		
		if (!empty($update_array)) {
			if (isset($update_array['version'])) {
				$version = $config->get('program_version');
				$version = explode('-', $version);
				if (version_compare($version[0], $update_array['version'], '<')) {
					$update = true;
				}
			}
		}
		
		return $update;
	}
		
	public function do_upgrade($array = NULL) {
		$config 	= &singleton::get(__NAMESPACE__ . '\config');
		$log		= &singleton::get(__NAMESPACE__ . '\log');

		if (!ini_get('safe_mode')) {
			//ooh we can process for sooo long
			set_time_limit(280); 
		}
		
		$log_array['event_severity'] = 'notice';
		$log_array['event_number'] = E_USER_NOTICE;
		$log_array['event_description'] = 'Database Upgrade Started (Program Version "' . safe_output($config->get('program_version')) . '", Database Version "'. safe_output($config->get('database_version')) .'")';
		$log_array['event_file'] = __FILE__;
		$log_array['event_file_line'] = __LINE__;
		$log_array['event_type'] = 'upgrade';
		$log_array['event_source'] = 'upgrade';
		$log_array['event_version'] = '1';
		$log_array['log_backtrace'] = false;	
				
		$log->add($log_array);
		
		for ($i = $config->get('database_version') + 1; $i <= $this->get_db_version(); $i++) {
			if (method_exists($this, 'dbsv_' . $i)) {
				call_user_func(array($this, 'dbsv_' . $i));		
			}
			
			if (method_exists($this, 'dbdv_' . $i)) {
				call_user_func(array($this, 'dbdv_' . $i));
			}
		}
		
		
		$log_array['event_severity'] = 'notice';
		$log_array['event_number'] = E_USER_NOTICE;
		$log_array['event_description'] = 'Database Upgrade Finished (Program Version "' . safe_output($config->get('program_version')) . '", Database Version "'. safe_output($config->get('database_version')) .'")';
		$log_array['event_file'] = __FILE__;
		$log_array['event_file_line'] = __LINE__;
		$log_array['event_type'] = 'upgrade';
		$log_array['event_source'] = 'upgrade';
		$log_array['event_version'] = '1';
		$log_array['log_backtrace'] = false;	
				
		$log->add($log_array);
		
		return true;
	}
	
	private function dbsv_2() {
		global $db;
		
		$config 				= &singleton::get(__NAMESPACE__ . '\config');
		$tables 				= &singleton::get(__NAMESPACE__ . '\tables');
		$error 					= &singleton::get(__NAMESPACE__ . '\error');
		$ticket_departments 	= &singleton::get(__NAMESPACE__ . '\ticket_departments');
		
		$query = "ALTER TABLE `$tables->tickets` ADD `submitted_user_id` int(11) unsigned DEFAULT NULL";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$query = "ALTER TABLE `$tables->tickets` ADD INDEX `submitted_user_id` ( `submitted_user_id` )";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$query = "CREATE TABLE IF NOT EXISTS `$tables->ticket_departments` (
		`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
		`name` VARCHAR( 255 ) NOT NULL ,
		`enabled` int( 1 ) UNSIGNED NOT NULL DEFAULT '1',
		`site_id` INT( 11 ) UNSIGNED NOT NULL ,
		 KEY `enabled` (`enabled`),
		 KEY `site_id` (`site_id`)
		) DEFAULT CHARSET=utf8;
		";
		
				
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$query = "ALTER TABLE `$tables->tickets` ADD `department_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '1', ADD INDEX ( `department_id`  )";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$department_id = $ticket_departments->add(array('name' => 'Default Department', 'enabled' => 1));
		
		$config->add('default_department', $department_id);

		$config->add('html_enabled', 0);
		$config->add('smtp_enabled', 0);
		
		$query = "ALTER TABLE `$tables->tickets` ADD `html` INT( 1 ) UNSIGNED NOT NULL DEFAULT '0'";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$query = "ALTER TABLE `$tables->ticket_notes` ADD `html` INT( 1 ) UNSIGNED NOT NULL DEFAULT '0'";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$config->set('database_version', 2);
		$config->set('program_version', '1.1');


	}
	
	private function dbsv_3() {
		global $db;
		
		$config 	= &singleton::get(__NAMESPACE__ . '\config');
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');

		$query = "ALTER TABLE `$tables->users` ADD `reset_key` VARCHAR(255) NULL";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$query = "ALTER TABLE `$tables->users` ADD `reset_expiry` DATETIME NULL";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
				
		$config->set('database_version', 3);
		$config->set('program_version', '1.2');

	
	}
	
	private function dbsv_4() {
		global $db;
		
		$config 	= &singleton::get(__NAMESPACE__ . '\config');
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');

		$config->add('smtp_tls', 0);
		$config->add('smtp_port', 25);
		$config->add('pop_leave_message', 0);
		
		
		$query = "CREATE TABLE IF NOT EXISTS `$tables->pop_messages` (
		`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
		`message_id` text NOT NULL,
		`site_id` INT( 11 ) UNSIGNED NOT NULL,
		 KEY `message_id` (message_id(300)),
		 KEY `site_id` (`site_id`)
		) DEFAULT CHARSET=utf8;
		";	
				
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}

		
		$config->set('database_version', 4);
		$config->set('program_version', '1.2.1');

	}
	
	private function dbdv_5() {
		$config 	= &singleton::get(__NAMESPACE__ . '\config');
		$config->set('database_version', 5);
		$config->set('program_version', '1.3');
	}
	
	private function dbsv_6() {
		global $db;
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');

		$query = "ALTER TABLE `$tables->tickets` MODIFY `description` LONGTEXT NOT NULL";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$query = "ALTER TABLE `$tables->ticket_notes` MODIFY `description` LONGTEXT NOT NULL";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
	
		$config->set('database_version', 6);
		$config->set('program_version', '1.3.1');	
	}
	
	private function dbsv_7() {
		global $db;
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
		

		$query = "ALTER TABLE `$tables->users` ADD `address` TEXT NULL";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$query = "ALTER TABLE `$tables->users` ADD `phone_number` VARCHAR(255) NULL";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		

		$config->add('plugin_data', array());
		$config->add('plugin_update_data', array());
		
		$config->set('database_version', 7);
		$config->set('program_version', '1.4');	
	}
	
	private function dbsv_8() {
		global $db;
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
		$cron 		= &singleton::get(__NAMESPACE__ . '\cron');
		

		$query = "ALTER TABLE `$tables->tickets` ADD `date_state_changed` DATETIME NULL";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}	
				
		$query = "ALTER TABLE `$tables->tickets` ADD INDEX `date_state_changed` ( `date_state_changed` )";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$config->set('last_update_response', '');
				
		$config->set('database_version', 8);
		$config->set('program_version', '1.5');	

		$cron->update_check();
	}
	
	private function dbsv_9() {
		global $db;
		
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
	
		$config->add('anonymous_tickets_reply', 0);
				
		$config->set('database_version', 9);
		$config->set('program_version', '1.5.1');	

	}
	
	private function dbsv_10() {
		global $db;
		
		$config				= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 			= &singleton::get(__NAMESPACE__ . '\tables');
		$error 				= &singleton::get(__NAMESPACE__ . '\error');
		$cron 				= &singleton::get(__NAMESPACE__ . '\cron');
		$pop_accounts 		= &singleton::get(__NAMESPACE__ . '\pop_accounts');
		$notifications 		= &singleton::get(__NAMESPACE__ . '\notifications');
		
				
		$query = "ALTER TABLE `$tables->tickets` ADD `access_key` VARCHAR( 32 ) NULL";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$query = "ALTER TABLE `$tables->tickets` ADD INDEX `access_key` ( `access_key` )";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}	

		$query = "ALTER TABLE `$tables->config` MODIFY `config_value` LONGTEXT NOT NULL";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}	


		$query = "
		CREATE TABLE IF NOT EXISTS $tables->pop_accounts (
			  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `site_id` INT( 11 ) UNSIGNED NOT NULL ,
			  `name` VARCHAR( 255 ),
			  `enabled` int(1) unsigned NOT NULL DEFAULT '0',
			  `hostname` varchar(255) NOT NULL,
			  `port` int(11) NOT NULL DEFAULT '110',
			  `tls` int(1) NOT NULL DEFAULT '0',
			  `username` varchar(255) NOT NULL,
			  `password` varchar(255) NOT NULL,
			  `download_files` int(1) NOT NULL DEFAULT '0',
			  `department_id` int(11) unsigned NOT NULL,
			  `priority_id` int(11) unsigned NOT NULL,
			  `leave_messages` int(1) NOT NULL DEFAULT '0',
			  PRIMARY KEY (`id`),
			  KEY `site_id` (`site_id`),
			  KEY `enabled` (`enabled`)
		) DEFAULT CHARSET=utf8;
		";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}	
		
		$query = "
		CREATE TABLE IF NOT EXISTS `$tables->messages` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `site_id` INT( 11 ) UNSIGNED NOT NULL ,
		  `user_id` int(11) unsigned NOT NULL,
		  `from_user_id` int(11) unsigned NOT NULL,
		  `subject` varchar(255) NOT NULL,
		  `message` LONGTEXT NOT NULL,
		  `date_added` datetime NOT NULL,
		  `last_modified` datetime NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `site_id` (`site_id`),
		  KEY `user_id` (`user_id`),
		  KEY `from_user_id` (`from_user_id`),
		  KEY `last_modified` (`last_modified`)
		) DEFAULT CHARSET=utf8;
		";
		
				
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$query = "
		CREATE TABLE IF NOT EXISTS `$tables->message_notes` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `message_id` int(11) unsigned NOT NULL,
		  `site_id` INT( 11 ) UNSIGNED NOT NULL ,
		  `user_id` int(11) unsigned NOT NULL,
		  `message` LONGTEXT NOT NULL,
		  `date_added` datetime NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `site_id` (`site_id`),
		  KEY `message_id` (`message_id`),
		  KEY `user_id` (`user_id`)
		) DEFAULT CHARSET=utf8;
		";
		
				
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$query = "
		CREATE TABLE IF NOT EXISTS `$tables->message_unread` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `site_id` INT( 11 ) UNSIGNED NOT NULL ,
		  `user_id` int(11) unsigned NOT NULL,
		  `message_id` int(11) unsigned NULL,
		  `message_note_id` int(11) unsigned NULL,
		  PRIMARY KEY (`id`),
		  KEY `site_id` (`site_id`),
		  KEY `message_id` (`message_id`),
		  KEY `message_note_id` (`message_note_id`),
		  KEY `user_id` (`user_id`)
		) DEFAULT CHARSET=utf8;
		";
		
				
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		//migrate old pop3 download details
		$email_account['name'] 				= 'Migrated Email Account';
		$email_account['enabled'] 			= $config->get('pop_enabled');
		$email_account['hostname'] 			= $config->get('pop_server');
		$email_account['port'] 				= $config->get('pop_port');
		$email_account['tls'] 				= $config->get('pop_tls');
		$email_account['username'] 			= $config->get('pop_username');
		$email_account['password'] 			= $config->get('pop_password');
		$email_account['leave_messages'] 	= $config->get('pop_leave_message');
		$email_account['priority_id'] 		= $config->get('pop_priority_id');
		$email_account['download_files'] 	= 1;
		$email_account['department_id'] 	= 1;

		$pop_accounts->add($email_account);
			
		//delete old account details
		$config->delete('pop_enabled');
		$config->delete('pop_server');
		$config->delete('pop_port');
		$config->delete('pop_tls');
		$config->delete('pop_username');
		$config->delete('pop_password');
		$config->delete('pop_leave_message');
		$config->delete('pop_priority_id');
		
		$config->add('notification_new_ticket_subject', '');
		$config->add('notification_new_ticket_body', '');
		$config->add('notification_new_ticket_note_subject', '');
		$config->add('notification_new_ticket_note_body', '');		
			
		$notifications->reset_new_ticket_notification();	
		$notifications->reset_new_ticket_note_notification();	
			
		$config->add('guest_portal', 0);
		$config->add('guest_portal_index_html', '');
		
		$config->add('default_language', 'english_aus');
		$config->add('captcha_enabled', 0);

		
		$config->set('database_version', 10);
		$config->set('program_version', '2.0');	
	}
	
	private function dbsv_11() {
		global $db;
		
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
	
		$config->add('default_theme', 'standard');
				
		$config->set('database_version', 11);
		$config->set('program_version', '2.0.1');	

	}
	private function dbsv_12() {
		global $db;
		
		$config 	= &singleton::get(__NAMESPACE__ . '\config');
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');

		$query = "ALTER TABLE `$tables->users` CHANGE `group_id` `group_id` int(11) unsigned NOT NULL DEFAULT '0'";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$query = "ALTER TABLE `$tables->events` CHANGE `event_summary` `event_summary` varchar(255) NULL COMMENT 'A summary of the description'";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$query = "ALTER TABLE `$tables->events` CHANGE `event_trace` `event_trace` TEXT NULL COMMENT 'Full PHP trace'";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}	
		
		$config->set('database_version', 12);
		$config->set('program_version', '2.0.2');
	}	
	
	private function dbsv_13() {
		global $db;
		
		$config 	= &singleton::get(__NAMESPACE__ . '\config');
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
		
		$query = "CREATE TABLE IF NOT EXISTS `$tables->ticket_field_group` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`site_id` INT( 11 ) UNSIGNED NOT NULL ,
			`name` VARCHAR( 255 ) NULL ,
			`type` VARCHAR( 255 ) NOT NULL ,
			`client_modify` int( 1 ) NOT NULL ,
			`enabled` int( 1 ) NOT NULL ,
			`default_field_id` int(11) unsigned NULL,
			PRIMARY KEY (`id`),
			KEY `site_id` (`site_id`),
			KEY `enabled` (`enabled`)
			) DEFAULT CHARSET=utf8;
		";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$query = "CREATE TABLE IF NOT EXISTS `$tables->ticket_fields` (
			`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
			`site_id` INT( 11 ) UNSIGNED NOT NULL ,
			`value` VARCHAR( 255 ) NOT NULL,
			`ticket_field_group_id` int( 11 ) unsigned NOT NULL,
			PRIMARY KEY (`id`),
			KEY `site_id` (`site_id`),
			KEY `ticket_field_group_id` (`ticket_field_group_id`)
			) DEFAULT CHARSET=utf8;
		";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$query = "CREATE TABLE IF NOT EXISTS `$tables->ticket_field_values` (
			`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
			`site_id` INT( 11 ) UNSIGNED NOT NULL ,
			`ticket_id` int( 11 ) unsigned NOT NULL,
			`ticket_field_group_id` int( 11 ) unsigned NOT NULL,
			`value` TEXT NOT NULL,
			PRIMARY KEY (`id`),
			KEY `site_id` (`site_id`),
			KEY `ticket_id` (`ticket_id`),
			KEY `ticket_field_group_id` (`ticket_field_group_id`)
			) DEFAULT CHARSET=utf8;
		";
		
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		

		$query = "ALTER TABLE `$tables->users` CHANGE `password` `password` varchar(255) NULL";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}	

		$query = "ALTER TABLE `$tables->users` CHANGE `email` `email` varchar(255) NULL";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}	
		
		$config->set('database_version', 13);
		$config->set('program_version', '2.1');
	}
	
	private function dbsv_14() {
		global $db;
		
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');

		//status
		$query = "CREATE TABLE IF NOT EXISTS `$tables->ticket_status` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `name` varchar(255) NOT NULL,
		  `colour` varchar(255) NOT NULL,
		  `enabled` int( 1 ) NOT NULL DEFAULT '1',
		  `active` int( 1 ) NOT NULL DEFAULT '1',
		  `site_id` int(11) unsigned NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `site_id` (`site_id`),
		  KEY `active` (`active`)		  
		) DEFAULT CHARSET=utf8;
		";
			
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}	
		
		$ticket_status 	= &singleton::get(__NAMESPACE__ . '\ticket_status');	
		
		$ticket_status->add(array('name' => 'Open', 'enabled' => 1, 'colour' => 'e93e3e', 'active' => 1));
		$ticket_status->add(array('name' => 'Closed', 'enabled' => 1, 'colour' => '71c255', 'active' => 0));

		$config->add('default_timezone', 'Australia/Sydney');
			
		$config->set('database_version', 14);
		$config->set('program_version', '2.2');	

	}	
	private function dbsv_15() {
		global $db;

		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
		
		//smtp accounts
		$query = "CREATE TABLE IF NOT EXISTS `$tables->smtp_accounts` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `site_id` INT( 11 ) UNSIGNED NOT NULL ,
		  `name` VARCHAR( 255 ),
		  `enabled` int(1) unsigned NOT NULL DEFAULT '0',	  
		  `hostname` varchar(255) NOT NULL,
		  `port` int(11) NOT NULL DEFAULT '25',
		  `tls` int(1) NOT NULL DEFAULT '0',	
		  `authentication` int(1) NOT NULL DEFAULT '0',			  
		  `username` varchar(255) NULL,
		  `password` varchar(255) NULL,
		  `email_address` varchar(255) NULL,
		  PRIMARY KEY (`id`),
		  KEY `site_id` (`site_id`),
		  KEY `enabled` (`enabled`)	  
		) DEFAULT CHARSET=utf8;
		";
			
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}	
		
		$query = "ALTER TABLE `$tables->tickets` ADD `pop_account_id` int(11) unsigned DEFAULT NULL";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$query = "ALTER TABLE `$tables->tickets` ADD INDEX `pop_account_id` ( `pop_account_id` )";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$query = "ALTER TABLE `$tables->pop_accounts` ADD `smtp_account_id` int(11) unsigned DEFAULT NULL";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
				
		$smtp_accounts 		= &singleton::get(__NAMESPACE__ . '\smtp_accounts');

		//migrate old smtp details
		$email_account['name'] 				= 'Migrated Email Account';
		$email_account['enabled'] 			= $config->get('smtp_enabled');
		$email_account['hostname'] 			= $config->get('smtp_server');
		$email_account['port'] 				= $config->get('smtp_port');
		$email_account['tls'] 				= $config->get('smtp_tls');
		$email_account['username'] 			= $config->get('smtp_username');
		$email_account['password'] 			= $config->get('smtp_password');
		$email_account['authentication'] 	= $config->get('smtp_auth');
		$email_account['email_address'] 	= $config->get('smtp_email_address');

		$smtp_accounts->add($email_account);
			
		//delete old account details
		$config->delete('smtp_enabled');
		$config->delete('smtp_server');
		$config->delete('smtp_port');
		$config->delete('smtp_tls');
		$config->delete('smtp_username');
		$config->delete('smtp_password');
		$config->delete('smtp_auth');
		$config->delete('smtp_email_address');
		
		$config->add('default_smtp_account', 1);

		$config->add('pushover_enabled', 0);
		$config->add('pushover_notify_users', array());
		$config->add('pushover_user_enabled', 0);
		$config->add('pushover_token', '');
		
		
		$query = "ALTER TABLE `$tables->users` ADD `pushover_key` VARCHAR(255) NULL";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$query = "ALTER TABLE `$tables->users` ADD INDEX `pushover_key` ( `pushover_key` )";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$query = "ALTER TABLE `$tables->events` MODIFY `event_trace` LONGTEXT NULL";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}

		
		$config->set('database_version', 15);
		$config->set('program_version', '2.3');		
		
	}	
	
	private function dbsv_16() {
		global $db;
		
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');

		//departments
		$query = "CREATE TABLE IF NOT EXISTS `$tables->users_to_departments` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `user_id` int(11) unsigned NOT NULL,
		  `department_id` int(11) unsigned NOT NULL,
		  `site_id` int(11) unsigned NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `site_id` (`site_id`),
		  KEY `user_id` (`user_id`),	
		  KEY `department_id` (`department_id`),
		  UNIQUE `unique` ( `user_id` , `department_id` , `site_id` ) 		  
		) DEFAULT CHARSET=utf8;
		";
			
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}	
		
		$query = "ALTER TABLE `$tables->ticket_departments` ADD `public_view` int(1) unsigned NOT NULL DEFAULT '1'";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$query = "ALTER TABLE `$tables->ticket_departments` ADD INDEX `public_view` ( `public_view` )";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}

		$config->add('license_key', '');

		$config->set('database_version', 16);
		$config->set('program_version', '2.4');		
				
	}
	
	private function dbdv_17() {
		global $db;
		
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
				
		//remove users from departments (for users that no longer exist)
		$query 		= "DELETE FROM `$tables->users_to_departments` WHERE user_id NOT IN (SELECT id FROM users)";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
					
		$config->set('database_version', 17);
		$config->set('program_version', '2.4.1');	

	}
	
	private function dbsv_18() {
		global $db;
		
		$config 		= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 		= &singleton::get(__NAMESPACE__ . '\tables');
		$error 			= &singleton::get(__NAMESPACE__ . '\error');
		$notifications 	= &singleton::get(__NAMESPACE__ . '\notifications');

				
		$query = "ALTER TABLE `$tables->ticket_notes` ADD `private` INT( 1 ) UNSIGNED NOT NULL DEFAULT '0'";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$query = "ALTER TABLE `$tables->ticket_notes` ADD INDEX `private` ( `private` )";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$config->add('notification_new_user_subject', '');
		$config->add('notification_new_user_body', '');		
			
		$notifications->reset_new_user_notification();
		
		$cron_intervals = $config->get('cron_intervals');		
		$cron_intervals[] = array('name' => 'every_two_minutes', 'description' => 'Every Two Minutes', 'next_run' => '0000-00-00 00:00:00', 'frequency' => '120');
		$config->set('cron_intervals', $cron_intervals);
		
		
		$config->add('log_limit', '100000');
					
		$config->set('database_version', 18);
		$config->set('program_version', '2.5');		
	}
	
	private function dbsv_19() {
		global $db;
		
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
		$api_keys 	= &singleton::get(__NAMESPACE__ . '\api_keys');
		
				
		$query = "ALTER TABLE `$tables->ticket_notes` ADD `name` VARCHAR(255) NULL";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
			

		$query = "ALTER TABLE `$tables->ticket_notes` ADD `email` VARCHAR(255) NULL";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}			
		
	
		$query = "CREATE TABLE IF NOT EXISTS `$tables->api_keys` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `name` varchar(255) NOT NULL,
		  `key` varchar(255) NOT NULL,
		  `date_added` datetime NOT NULL,
		  `access_level` int(11) unsigned NOT NULL DEFAULT '1',
		  `site_id` int(11) unsigned NOT NULL,
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `key` (`key`),
		  KEY `access_level` (`access_level`),
		  KEY `site_id` (`site_id`)
		) DEFAULT CHARSET=utf8;
		";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$config->add('ldap_server', '');
		$config->add('ldap_account_suffix', '');
		$config->add('ldap_base_dn', '');
		$config->add('ldap_create_accounts', 0);
		$config->add('ldap_enabled', 0);
		
		$config->add('dalegroup_portal_uuid', '');
		$config->add('dalegroup_portal_enabled', 0);
		$config->add('api_enabled', 0);

		$config->set('database_version', 19);
		$config->set('program_version', '2.6');	

	}
	
	private function dbdv_20() {
		global $db;
		
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
					
		$config->set('default_theme', 'bootstrap');			
					
		$config->set('database_version', 20);
		$config->set('program_version', '3.0-alpha-1');	

	}
	
	private function dbsv_21() {
		global $db;
		
		$config 				= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 				= &singleton::get(__NAMESPACE__ . '\tables');
		$error 					= &singleton::get(__NAMESPACE__ . '\error');
		
		$query = "ALTER TABLE `$tables->sessions` MODIFY `session_id` varchar(255) NOT NULL DEFAULT ''";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
					
		$config->set('database_version', 21);
	
	}
	
	private function dbsv_22() {
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	

	
		$config->add('default_theme_sub', 'default');			
					
		$config->set('database_version', 22);
		$config->set('program_version', '3.0-alpha-2');	
	
	}
	
	private function dbdv_23() {
		global $db;
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
		
		$query = "UPDATE $tables->ticket_status SET colour = concat('#', colour);";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
								
		$config->set('database_version', 23);
		$config->set('program_version', '3.0-beta-1');

			
		
	}
	
	private function dbsv_24() {
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	

		$config->set('database_version', 24);
		$config->set('program_version', '3.0');		
	}
	
	private function dbdv_25() {
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		
		$config->add('application_id' , 1);

		$config->set('database_version', 25);
		$config->set('program_version', '3.1-alpha-1');			
	}
	
	private function dbsv_26() {
		global $db;
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
		$notifications 		= &singleton::get(__NAMESPACE__ . '\notifications');
		
		$query = "
		CREATE TABLE IF NOT EXISTS `user_levels_to_department_notifications` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `site_id` int(11) unsigned NOT NULL,
		  `department_id` int(11) unsigned NOT NULL,
		  `user_level` int(11) unsigned NOT NULL,
		  `type` varchar(255) NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `site_id` (`site_id`),
		  KEY `department_id` (`department_id`),
		  KEY `user_level` (`user_level`),
		  KEY `type` (`type`)
		) DEFAULT CHARSET=utf8;
		";
	
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$config->add('notification_new_department_ticket_subject', '');
		$config->add('notification_new_department_ticket_body', '');
		$config->add('notification_new_department_ticket_note_subject', '');
		$config->add('notification_new_department_ticket_note_body', '');		
			
		$notifications->reset_new_department_ticket_notification();	
		$notifications->reset_new_department_ticket_note_notification();	
								
		$config->set('database_version', 26);
	
	}
	
	private function dbdv_27() {
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
	
		$config->add('auto_update_cache', array());
		$config->set('database_version', 27);
		$config->set('program_version', '3.1-beta-1');			
	
	}

	private function dbdv_28() {
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
	
		$config->add('notification_owner_on_pop', 0);
		
		$config->set('database_version', 28);
		$config->set('program_version', '3.1-beta-2');			
	
	}
	
	private function dbdv_29() {
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
			
		$config->set('database_version', 29);
		$config->set('program_version', '3.1');		
	}
	
	private function dbsv_30() {
		global $db;
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
		
		$query = "ALTER TABLE `$tables->tickets` ADD `company_id` int(11) unsigned DEFAULT NULL";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}		
		
		$query = "ALTER TABLE `$tables->tickets` ADD INDEX `company_id` ( `company_id` )";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$query = "ALTER TABLE `$tables->users` ADD `company_id` int(11) unsigned DEFAULT NULL";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}		
		
		$query = "ALTER TABLE `$tables->users` ADD INDEX `company_id` ( `company_id` )";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$query = "ALTER TABLE `$tables->users` ADD `date_added` datetime DEFAULT NULL";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}	

		$query = "UPDATE `$tables->users` SET `date_added` = :datetime WHERE site_id = :site_id";
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}	
		
		$datetime = datetime();
		$site_id = SITE_ID;

		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);
		$stmt->bindParam(':datetime', $datetime, database::PARAM_INT);
		
		try {
			$stmt->execute();
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}		
		
		$query = "ALTER TABLE `$tables->users` ADD `facebook_id` BIGINT(20) DEFAULT NULL";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$query = "ALTER TABLE `$tables->users` ADD INDEX `facebook_id` ( `facebook_id` )";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}		
		
		//future companies support (via plugin)
		$config->add('default_company_id', '');		
		$config->add('ad_default_company_id', '');
		$config->add('ldap_default_company_id', '');
		
		//store data software was installed
		$config->add('utc_date_installed', datetime_utc());
		
		//future facebook login support
		$config->add('facebook_enabled', '0');
		$config->add('facebook_app_id', '');
		$config->add('facebook_app_secret', '');
		
		//future invoicing support (via plugin)
		$config->add('finance_dollar_sign', '$');
		$config->add('finance_bank_enabled', '0');
		$config->add('finance_bank_bsb', '');
		$config->add('finance_bank_account', '');
		$config->add('finance_bank_account_name', '');
		$config->add('finance_bank_name', '');
		$config->add('finance_tax_number', '');
		$config->add('finance_invoice_logo', '');
		$config->add('finance_invoice_comment', '');
			
	
		$config->set('database_version', 30);
		$config->set('program_version', '3.2-alpha-1');		
		
	}
	
	private function dbdv_31() {	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');
		$config->set('html_enabled', 1);
		$config->set('database_version', 31);
		$config->set('program_version', '3.2-alpha-2');	
	}
	
	private function dbdv_32() {	
		global $db;
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
		
		$query = "ALTER TABLE `$tables->users` MODIFY `username` varchar(255) DEFAULT NULL";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}		
			
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');
		$config->set('database_version', 32);
		$config->set('program_version', '3.2-alpha-3');	
	}
	
	private function dbdv_33() {
		global $db;
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	

		$config->add('pop_messages_limit', '100000');
		$config->set('database_version', 33);
		$config->set('program_version', '3.2-alpha-4');	
	
	}
	
	private function dbdv_34() {
		global $db;
	
		$config 			= &singleton::get(__NAMESPACE__ . '\config');	
		$notifications 		= &singleton::get(__NAMESPACE__ . '\notifications');

		$config->add('notification_ticket_assigned_user_subject', '');
		$config->add('notification_ticket_assigned_user_body', '');
			
		$notifications->reset_ticket_assigned_user_notification();	
								
		$config->set('database_version', 34);
		$config->set('program_version', '3.2-beta-1');	
	}
	
	private function dbdv_35() {
		global $db;
	
		$config 			= &singleton::get(__NAMESPACE__ . '\config');	
								
		$config->set('database_version', 35);
		$config->set('program_version', '3.2');	
	}
	
	private function dbdv_36() {
		global $db;
	
		$config 			= &singleton::get(__NAMESPACE__ . '\config');	
								
		$config->set('database_version', 36);
	}
	
	private function dbsv_37() {
		global $db;
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
		
		$query = "ALTER TABLE `$tables->storage` CHANGE `user_id` `user_id` INT( 11 ) UNSIGNED NULL";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}		
				
		$config->set('database_version', 37);
		$config->set('program_version', '3.3-alpha-1');
		
	}
	
	private function dbsv_38() {
		global $db;
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
		
		$query = "CREATE TABLE IF NOT EXISTS `$tables->canned_responses` (
		`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
		`name` VARCHAR( 255 ) NOT NULL ,
		`description` LONGTEXT NOT NULL,
		`site_id` INT( 11 ) UNSIGNED NOT NULL ,
		 KEY `site_id` (`site_id`)
		) DEFAULT CHARSET=utf8;
		";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}		
			
	
		$config->set('database_version', 38);
	}
	
	private function dbdv_39() {
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	

		$config->set('database_version', 39);
		$config->set('program_version', '3.3');
	
	}
	
	private function dbdv_40() {
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	

		$config->set('database_version', 40);
		$config->set('program_version', '3.3.1');
	
	}
	
	private function dbdv_41() {
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	

		$config->set('database_version', 41);
		$config->set('program_version', '3.4-alpha-1');
	
	}
	
	private function dbsv_42() {
		global $db;
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
		
		$query = "ALTER TABLE `$tables->tickets` ADD `cc` LONGTEXT NULL";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}		
				
		$config->set('database_version', 42);
		
	}
	
	private function dbsv_43() {
		global $db;
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
		
		$query = "ALTER TABLE `$tables->ticket_notes` ADD `cc` LONGTEXT NULL";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}		
				
		$config->set('database_version', 43);
		
	}
	
	private function dbsv_44() {
		global $db;
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
		
		$config->add('pop_auto_create_users', 0);

		$config->set('program_version', '3.4-beta-1');		
		$config->set('database_version', 44);
		
	}
	
	private function dbsv_45() {
		global $db;
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
		
		$config->add('smtp_reject_missing_message_id', 0);

		$config->set('program_version', '3.4');		
		$config->set('database_version', 45);
		
	}
	
	private function dbsv_46() {
		global $db;
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
		
		$config->add('display_dashboard', 0);

		$config->set('database_version', 46);
		
	}
	
	private function dbdv_47() {
		global $db;
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
		
		$config->add('auth_json_url', '');
		$config->add('auth_json_key', '');
		$config->add('auth_json_enabled', 0);
		$config->add('auth_json_site_id', '');
		$config->add('auth_json_create_accounts', 0);

		$config->set('program_version', '3.5-alpha-1');	
		$config->set('database_version', 47);	
	}
	
	private function dbsv_48() {
		global $db;
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
		
		$config->delete('pop_auto_create_users');

		$query = "ALTER TABLE `$tables->pop_accounts` ADD `auto_create_users` int(1) unsigned DEFAULT 0";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$config->set('database_version', 48);		
	}
	
	private function dbsv_49() {
		global $db;
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
				
		$query = "ALTER TABLE `$tables->ticket_notes` ADD `company_id` int(11) unsigned DEFAULT NULL";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}		
		
		$query = "ALTER TABLE `$tables->ticket_notes` ADD INDEX `company_id` ( `company_id` )";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$config->set('program_version', '3.5-beta-1');	
		$config->set('database_version', 49);		

	}
	
	private function dbdv_50() {
		global $db;
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
		
		$config->add('guest_portal_auto_create_users', 0);

		$config->set('database_version', 50);		
	}
	
	private function dbdv_51() {	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$config->set('program_version', '3.5');	
		$config->set('database_version', 51);
	}
	
	private function dbdv_60() {	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$config->set('program_version', '4.0-alpha-1');	
		$config->set('database_version', 60);
	}
	
	private function dbsv_61() {
		global $db;
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
				
		$query = "ALTER TABLE `$tables->ticket_notes` ADD `subject` varchar(255) NULL";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}		

		$config->set('database_version', 61);		

	}
	
	private function dbdv_62() {	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$config->set('program_version', '4.0');	
		$config->set('database_version', 62);
	}
	
	private function dbsv_63() {
		global $db;
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');

		$query = "ALTER TABLE `$tables->tickets` ADD `email_data` LONGTEXT NULL";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$query = "ALTER TABLE `$tables->ticket_notes` ADD `email_data` LONGTEXT NULL";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}		

		$config->set('database_version', 63);		
		$config->set('program_version', '4.1-alpha-1');	

	}
	
	private function dbdv_64() {
		global $db;
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
		
		$config->add('api_require_https', 1);
		$config->set('database_version', 64);		

	}
	
	private function dbdv_65() {
		global $db;
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
		
		$config->set('database_version', 65);		
		$config->set('program_version', '4.1-beta-1');	

	}

	private function dbdv_66() {
		global $db;
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
		
		$config->set('database_version', 66);		
		$config->set('program_version', '4.1');	

	}	
	
	private function dbdv_67() {
		global $db;
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
		
		$config->set('default_theme', 'bootstrap3');			
		$config->set('default_theme_sub', 'default');
		
		$config->set('database_version', 67);		
		$config->set('program_version', '4.1.1');		
	}
	
	private function dbdv_68() {
		global $db;
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
		
		$config->add('auth_sso_enabled', 0);			
		
		$config->set('database_version', 68);		
		$config->set('program_version', '4.2');		
	}
	
	private function dbdv_69() {
		global $db;
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
		
				
		$ticket_status 	= &singleton::get(__NAMESPACE__ . '\ticket_status');	
		
		$ticket_status->edit(array('active' => 1, 'id' => 1));
		$ticket_status->edit(array('active' => 0, 'id' => 2));

		
		$config->set('database_version', 69);		
		$config->set('program_version', '4.2.1');		
	}
	
	private function dbdv_70() {
		global $db;
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
		
		$config->add('store_email_data', 1);			
				
		$config->set('database_version', 70);		
		$config->set('program_version', '4.2.1');		
	}
	
	private function dbsv_71() {
		global $db;
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
		
		$query = "ALTER TABLE `$tables->users` ADD `view_ticket_reverse` int(1) unsigned DEFAULT 0";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}				
		
		$config->set('database_version', 71);		
		$config->set('program_version', '4.3-alpha-1');		
	}
	
	private function dbsv_72() {
		global $db;
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
		
		
		$query = "ALTER TABLE `$tables->files_to_tickets` ADD `private` INT( 1 ) UNSIGNED NOT NULL DEFAULT '0'";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$query = "ALTER TABLE `$tables->files_to_tickets` ADD INDEX `private` ( `private` )";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}			


		$query = "CREATE TABLE IF NOT EXISTS `$tables->ticket_history` (
		`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
		`ticket_id` int( 11 ) unsigned NOT NULL,
		`type` VARCHAR( 255 ) NOT NULL ,
		`ip_address` varchar(255) NOT NULL,
		`date_added` datetime NOT NULL,
		`site_id` INT( 11 ) UNSIGNED NOT NULL ,
	    `by_user_id` int(11) unsigned NULL,
		`history_description` LONGTEXT NOT NULL,
		`reply` INT( 1 ) UNSIGNED NOT NULL DEFAULT '0',
		`subject` varchar(255) NOT NULL,
		`description` LONGTEXT NOT NULL,
		`user_id` int(11) unsigned NOT NULL,
		`priority_id` int(11) unsigned NOT NULL,
		`state_id` int(11) unsigned DEFAULT NULL,
		`assigned_user_id` int(11) unsigned DEFAULT NULL,
		`name` VARCHAR( 255 ) NULL,
		`email` VARCHAR( 255 ) NULL,
		`merge_ticket_id` int(11) UNSIGNED NULL,
		`submitted_user_id` int(11) unsigned DEFAULT NULL,
		`department_id` INT( 11 ) UNSIGNED DEFAULT NULL,
		`pop_account_id` int(11) unsigned DEFAULT NULL,
		`company_id` int(11) unsigned DEFAULT NULL,
		`cc` LONGTEXT NULL,
		`private` INT( 1 ) UNSIGNED NOT NULL DEFAULT '0',		
		 KEY `ticket_id` (`ticket_id`),
		 KEY `by_user_id` (`by_user_id`),
		 KEY `site_id` (`site_id`)
		) DEFAULT CHARSET=utf8;
		";
		
				
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		
		$config->set('database_version', 72);		
	}
	
	private function dbdv_73() {
		global $db;
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
				
		$ticket_status 	= &singleton::get(__NAMESPACE__ . '\ticket_status');	
		
		$ticket_status->edit(array('active' => 1, 'id' => 1));
		$ticket_status->edit(array('active' => 0, 'id' => 2));
		
		$config->set('database_version', 73);		
		$config->set('program_version', '4.3');		
	}
	
	private function dbdv_74() {
		global $db;
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
						
		$config->set('database_version', 74);		
		$config->set('program_version', '4.3.1');		
	}
	
	private function dbdv_75() {
		global $db;
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
						
		$config->set('database_version', 75);		
		$config->set('program_version', '4.3.2');		
	}
	
		
	private function dbdv_76() {
		global $db;
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
		
		$config->add('auth_facebook_create_accounts', 0);
						
		$config->set('database_version', 76);		
		$config->set('program_version', '4.4');		
	}
	
	private function dbsv_77() {
		global $db;
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
		
		$query = "CREATE TABLE IF NOT EXISTS `$tables->ticket_views` (
		`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
		`date_added` datetime NOT NULL,
		`user_id` INT( 11 ) UNSIGNED NOT NULL ,
		`ticket_id` INT( 11 ) UNSIGNED NOT NULL ,
		`site_id` INT( 11 ) UNSIGNED NOT NULL ,
		 KEY `date_added` (`date_added`),
		 KEY `user_id` (`user_id`),
		 KEY `ticket_id` (`ticket_id`),
		 KEY `site_id` (`site_id`)
		) DEFAULT CHARSET=utf8;
		";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}		
			
	
		$config->set('database_version', 77);
		$config->set('program_version', '4.5');		

	}
	
	private function dbdv_78() {
		global $db;
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');

		$config->add('ticket_views_enabled', 1);		
		//in seconds
		$config->add('ticket_views_max_age', 30);
						
		$config->set('database_version', 78);		
	}
	
	private function dbdv_79() {
		global $db;
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');

		$config->add('session_life_time', 3600);		
						
		$config->set('database_version', 79);		
	}
	
	private function dbsv_80() {
		global $db;
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
		
				
		$query = "ALTER TABLE `$tables->tickets` ADD `date_due` date DEFAULT NULL";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$query = "ALTER TABLE `$tables->tickets` ADD INDEX `date_due` ( `date_due` )";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}

		$query = "ALTER TABLE `$tables->ticket_history` ADD `date_due` date DEFAULT NULL";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}		
	
		$config->set('database_version', 80);
		$config->set('program_version', '4.5.1');		
	}
	
	private function dbdv_81() {
		global $db;
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
								
		$config->set('database_version', 81);		
		$config->set('program_version', '5.0-alpha-1');		
	}
	
	/*
		ZOMG Permissions
	*/
	private function dbsv_82() {
		global $db;
	
		$config 	= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
		
		/*
			Task based permissions
		*/
		
		//status
		$query = "CREATE TABLE IF NOT EXISTS `$tables->permission_groups` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `name` varchar(255) NOT NULL,
		  `site_id` int(11) unsigned NOT NULL,
		  `allow_modify` int( 1 ) UNSIGNED NOT NULL DEFAULT '1',
		  PRIMARY KEY (`id`),
		  KEY `site_id` (`site_id`)
		) DEFAULT CHARSET=utf8;
		";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
	
		$query = "
		CREATE TABLE IF NOT EXISTS `$tables->permission_tasks` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `site_id` INT( 11 ) UNSIGNED NOT NULL ,
		  `name` varchar(255) NOT NULL,
		  `description` text,
		  PRIMARY KEY (`id`),
		  KEY `site_id` (`site_id`),
		  KEY `name` (`name`)
		) DEFAULT CHARSET=utf8;
		";

		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}

		$query = "
		CREATE TABLE IF NOT EXISTS `$tables->tasks_to_groups` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `site_id` INT( 11 ) UNSIGNED NOT NULL ,
		  `group_id` int(11) unsigned NOT NULL,
		  `task_id` int(11) unsigned NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `site_id` (`site_id`),
		  KEY `group_id` (`group_id`),
		  KEY `task_id` (`task_id`),
		  KEY `site_group` (`site_id`, `group_id`),
		  KEY `task_group` (`task_id`, `group_id`)
		) DEFAULT CHARSET=utf8;
		";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
	
		$config->set('database_version', 82);		

	}
	
	private function dbdv_83() {
		$config 				= &singleton::get(__NAMESPACE__ . '\config');	
		$config->set('database_version', 83);		
	}
	
	private function dbdv_84() {
		global $db;

		$config 				= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 				= &singleton::get(__NAMESPACE__ . '\tables');
		$error 					= &singleton::get(__NAMESPACE__ . '\error');
		$permissions 			= &singleton::get(__NAMESPACE__ . '\permissions');
		$permission_groups 		= &singleton::get(__NAMESPACE__ . '\permission_groups');
		$language 				= &singleton::get(__NAMESPACE__ . '\language');
		
		/*
			Add New Permission Groups
		*/
		
		//1
		$permission_groups->add(array('name' => $language->get('Submitter'), 'allow_modify' => 0));
		
		//2
		$permission_groups->add(array('name' => $language->get('Administrator'), 'allow_modify' => 0));

		//3
		$permission_groups->add(array('name' => $language->get('User'), 'allow_modify' => 0));

		//4
		$permission_groups->add(array('name' => $language->get('Staff'), 'allow_modify' => 0));

		//5
		$permission_groups->add(array('name' => $language->get('Moderator'), 'allow_modify' => 0));

		//6
		$permission_groups->add(array('name' => $language->get('Global Moderator'), 'allow_modify' => 0));
		
		/*
			Create Some Permission Tasks
		*/
		//1
		$permissions->add_task(array('name' => 'manage_system_settings'));
		//2
		$permissions->add_task(array('name' => 'manage_users'));
		//3
		$permissions->add_task(array('name' => 'manage_logs'));
		
		
		/*
			Various Ticket Tasks
		*/
		
		//global permission
		$permissions->add_task(array('name' => 'tickets'));

		//ticket specific tasks
		$permissions->add_task(array('name' => 'tickets_carbon_copy_reply'));
		$permissions->add_task(array('name' => 'tickets_change_status'));
		$permissions->add_task(array('name' => 'tickets_transfer_department'));
		$permissions->add_task(array('name' => 'tickets_assign_user'));
		$permissions->add_task(array('name' => 'tickets_view_private_replies'));
		
		/*
			Various Ticket Tasks
		*/
		
		//ticket specific tasks
		$permissions->add_task(array('name' => 'manage_tickets'));
		$permissions->add_task(array('name' => 'tickets_view_assigned_department'));
		$permissions->add_task(array('name' => 'tickets_view_assigned'));		
		$permissions->add_task(array('name' => 'tickets_view_audit_history'));

		//private messages
		$permissions->add_task(array('name' => 'send_private_messages'));
		
		
		/*
			Assign Tasks To Permissions
		*/
		
		//Admin Tasks (ID 2)
		$permissions->add_task_to_group(array('task_id' => 1, 'group_id' => 2));
		$permissions->add_task_to_group(array('task_id' => 2, 'group_id' => 2));
		$permissions->add_task_to_group(array('task_id' => 3, 'group_id' => 2));
		
		
			//Admin Tasks (ID 2)
		/*
			$permissions->add_task(array('name' => 'tickets'));
			$permissions->add_task(array('name' => 'tickets_carbon_copy_reply'));
			$permissions->add_task(array('name' => 'tickets_change_status'));
			$permissions->add_task(array('name' => 'tickets_transfer_department'));
			$permissions->add_task(array('name' => 'tickets_assign_user'));
			$permissions->add_task(array('name' => 'tickets_view_private_replies'));
		*/	
		$permissions->add_task_to_group(array('task_id' => 4, 'group_id' => 2));
		$permissions->add_task_to_group(array('task_id' => 5, 'group_id' => 2));
		$permissions->add_task_to_group(array('task_id' => 6, 'group_id' => 2));
		$permissions->add_task_to_group(array('task_id' => 7, 'group_id' => 2));
		$permissions->add_task_to_group(array('task_id' => 8, 'group_id' => 2));
		$permissions->add_task_to_group(array('task_id' => 9, 'group_id' => 2));
	
		/*
			$permissions->add_task(array('name' => 'manage_tickets'));
			$permissions->add_task(array('name' => 'tickets_view_assigned_department'));
			$permissions->add_task(array('name' => 'tickets_view_assigned'));
		*/
		$permissions->add_task_to_group(array('task_id' => 10, 'group_id' => 2));
		$permissions->add_task_to_group(array('task_id' => 11, 'group_id' => 2));
		$permissions->add_task_to_group(array('task_id' => 12, 'group_id' => 2));

		/*
			$permissions->add_task(array('name' => 'tickets_view_audit_history'));
		*/
		$permissions->add_task_to_group(array('task_id' => 13, 'group_id' => 2));
		
		/*
			$permissions->add_task(array('name' => 'send_private_messages'));
		*/
		$permissions->add_task_to_group(array('task_id' => 14, 'group_id' => 2));
		
		
		//1 Submitter
		
		//tickets
		$permissions->add_task_to_group(array('task_id' => 4, 'group_id' => 1));
		
		//3 User
		
		//tickets
		$permissions->add_task_to_group(array('task_id' => 4, 'group_id' => 3));
		//tickets_view_assigned
		$permissions->add_task_to_group(array('task_id' => 12, 'group_id' => 3));

		//4 Staff
		
		//tickets
		$permissions->add_task_to_group(array('task_id' => 4, 'group_id' => 4));
		//tickets_view_assigned
		$permissions->add_task_to_group(array('task_id' => 12, 'group_id' => 4));
		//tickets_view_assigned_department
		$permissions->add_task_to_group(array('task_id' => 11, 'group_id' => 4));
		//tickets_view_private_replies
		$permissions->add_task_to_group(array('task_id' => 9, 'group_id' => 4));
		//tickets_change_status
		$permissions->add_task_to_group(array('task_id' => 6, 'group_id' => 4));
		//tickets_transfer_department
		$permissions->add_task_to_group(array('task_id' => 7, 'group_id' => 4));

		//5 Moderator

		//tickets
		$permissions->add_task_to_group(array('task_id' => 4, 'group_id' => 5));
		//tickets_view_assigned
		$permissions->add_task_to_group(array('task_id' => 12, 'group_id' => 5));
		//tickets_view_assigned_department
		$permissions->add_task_to_group(array('task_id' => 11, 'group_id' => 5));
		//tickets_view_private_replies
		$permissions->add_task_to_group(array('task_id' => 9, 'group_id' => 5));
		//tickets_change_status
		$permissions->add_task_to_group(array('task_id' => 6, 'group_id' => 5));
		//tickets_transfer_department
		$permissions->add_task_to_group(array('task_id' => 7, 'group_id' => 5));
		
		//tickets_assign_user
		$permissions->add_task_to_group(array('task_id' => 8, 'group_id' => 5));
		//tickets_carbon_copy_reply
		$permissions->add_task_to_group(array('task_id' => 5, 'group_id' => 5));
		//tickets_view_audit_history
		$permissions->add_task_to_group(array('task_id' => 13, 'group_id' => 5));

		
		//6 Global Moderator
		
		//tickets
		$permissions->add_task_to_group(array('task_id' => 4, 'group_id' => 6));
		//tickets_view_assigned
		$permissions->add_task_to_group(array('task_id' => 12, 'group_id' => 6));
		//tickets_view_assigned_department
		$permissions->add_task_to_group(array('task_id' => 11, 'group_id' => 6));
		//tickets_view_private_replies
		$permissions->add_task_to_group(array('task_id' => 9, 'group_id' => 6));
		//tickets_change_status
		$permissions->add_task_to_group(array('task_id' => 6, 'group_id' => 6));
		//tickets_transfer_department
		$permissions->add_task_to_group(array('task_id' => 7, 'group_id' => 6));
		
		//tickets_assign_user
		$permissions->add_task_to_group(array('task_id' => 8, 'group_id' => 6));
		//tickets_carbon_copy_reply
		$permissions->add_task_to_group(array('task_id' => 5, 'group_id' => 6));
		//tickets_view_audit_history
		$permissions->add_task_to_group(array('task_id' => 13, 'group_id' => 6));

		//manage_tickets
		$permissions->add_task_to_group(array('task_id' => 10, 'group_id' => 6));
		//send_private_messages
		$permissions->add_task_to_group(array('task_id' => 14, 'group_id' => 6));	
		
		/*
			Assign Existing Users To Permission Groups
		*/
		
		//Admin
		$query = "UPDATE $tables->users SET group_id = 2 WHERE user_level = 2 AND site_id = :site_id";
			
		try {
			$stmt = $db->prepare($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}	
		
		$site_id = SITE_ID;

		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);
		
		try {
			$stmt->execute();
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}	
		
		//Submitter
		$query = "UPDATE $tables->users SET group_id = 1 WHERE user_level = 1 AND site_id = :site_id";
			
		try {
			$stmt = $db->prepare($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}	
		
		$site_id = SITE_ID;

		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);
		
		try {
			$stmt->execute();
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		//User
		$query = "UPDATE $tables->users SET group_id = 3 WHERE user_level = 3 AND site_id = :site_id";
			
		try {
			$stmt = $db->prepare($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}	
		
		$site_id = SITE_ID;

		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);
		
		try {
			$stmt->execute();
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		//Staff
		$query = "UPDATE $tables->users SET group_id = 4 WHERE user_level = 4 AND site_id = :site_id";
			
		try {
			$stmt = $db->prepare($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}	
		
		$site_id = SITE_ID;

		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);
		
		try {
			$stmt->execute();
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		//Moderator
		$query = "UPDATE $tables->users SET group_id = 5 WHERE user_level = 5 AND site_id = :site_id";
			
		try {
			$stmt = $db->prepare($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}	
		
		$site_id = SITE_ID;

		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);
		
		try {
			$stmt->execute();
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		//Global Moderator
		$query = "UPDATE $tables->users SET group_id = 6 WHERE user_level = 6 AND site_id = :site_id";
			
		try {
			$stmt = $db->prepare($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}	
		
		$site_id = SITE_ID;

		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);
		
		try {
			$stmt->execute();
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
	
		$config->set('database_version', 84);		

	}
	
	private function dbdv_85() {
		global $db;

		$config 				= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 				= &singleton::get(__NAMESPACE__ . '\tables');
		$error 					= &singleton::get(__NAMESPACE__ . '\error');
		$permissions 			= &singleton::get(__NAMESPACE__ . '\permissions');
		$permission_groups 		= &singleton::get(__NAMESPACE__ . '\permission_groups');
		$language 				= &singleton::get(__NAMESPACE__ . '\language');
		
		$config->set('database_version', 85);		

	}
	
	private function dbdv_86() {
		global $db;

		$config 				= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 				= &singleton::get(__NAMESPACE__ . '\tables');
		$error 					= &singleton::get(__NAMESPACE__ . '\error');
		$permissions 			= &singleton::get(__NAMESPACE__ . '\permissions');
		$permission_groups 		= &singleton::get(__NAMESPACE__ . '\permission_groups');
		$language 				= &singleton::get(__NAMESPACE__ . '\language');
		
		$config->set('database_version', 86);		

	}
	
	private function dbdv_87() {
		global $db;

		$config 				= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 				= &singleton::get(__NAMESPACE__ . '\tables');
		$error 					= &singleton::get(__NAMESPACE__ . '\error');
		$permissions 			= &singleton::get(__NAMESPACE__ . '\permissions');
		$permission_groups 		= &singleton::get(__NAMESPACE__ . '\permission_groups');
		$language 				= &singleton::get(__NAMESPACE__ . '\language');
		
		$query = "
		CREATE TABLE IF NOT EXISTS `$tables->permission_groups_to_department_notifications` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `site_id` int(11) unsigned NOT NULL,
		  `department_id` int(11) unsigned NOT NULL,
		  `group_id` int(11) unsigned NOT NULL,
		  `type` varchar(255) NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `site_id` (`site_id`),
		  KEY `department_id` (`department_id`),
		  KEY `group_id` (`group_id`),
		  KEY `type` (`type`)
		) DEFAULT CHARSET=utf8;
		";
	
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		/*			
		$query = "ALTER TABLE `$tables->ticket_status` ADD `allow_auto_state_change` int(1) unsigned NOT NULL DEFAULT '0'";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$query = "ALTER TABLE `$tables->ticket_status` ADD `auto_state_id` int(11) unsigned NULL";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		*/
			
		
		$config->set('database_version', 87);		

	}
	
	private function dbdv_88() {
		global $db;

		$config 				= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 				= &singleton::get(__NAMESPACE__ . '\tables');
		$error 					= &singleton::get(__NAMESPACE__ . '\error');
		$permissions 			= &singleton::get(__NAMESPACE__ . '\permissions');
		$permission_groups 		= &singleton::get(__NAMESPACE__ . '\permission_groups');
		$language 				= &singleton::get(__NAMESPACE__ . '\language');
	
		$query = "ALTER TABLE `$tables->tickets` ADD `project_id` int(11) unsigned DEFAULT NULL";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$query = "ALTER TABLE `$tables->tickets` ADD INDEX `project_id` ( `project_id` )";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$config->set('database_version', 88);		
		$config->set('program_version', '5.0-alpha-2');		
	
	}
	
	private function dbdv_89() {
		global $db;
		
		$config 				= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 				= &singleton::get(__NAMESPACE__ . '\tables');
		$error 					= &singleton::get(__NAMESPACE__ . '\error');
		$permissions 			= &singleton::get(__NAMESPACE__ . '\permissions');
		$permission_groups 		= &singleton::get(__NAMESPACE__ . '\permission_groups');
		$language 				= &singleton::get(__NAMESPACE__ . '\language');
	
		$query = "ALTER TABLE `$tables->ticket_history` ADD `project_id` int(11) unsigned DEFAULT NULL";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$query = "ALTER TABLE `$tables->ticket_history` ADD INDEX `project_id` ( `project_id` )";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$config->set('database_version', 89);		
	
	}
	
	private function dbdv_90() {
		global $db;
		
		$config 				= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 				= &singleton::get(__NAMESPACE__ . '\tables');
		$error 					= &singleton::get(__NAMESPACE__ . '\error');
		$permissions 			= &singleton::get(__NAMESPACE__ . '\permissions');
		$permission_groups 		= &singleton::get(__NAMESPACE__ . '\permission_groups');
		$language 				= &singleton::get(__NAMESPACE__ . '\language');
	
		$query = "ALTER TABLE `$tables->users` ADD `timesheets_hourly_rate` decimal(10,2) DEFAULT NULL";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$config->set('database_version', 90);		
	
	}	
			
	private function dbsv_91() {
		global $db;
		
		$config 				= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 				= &singleton::get(__NAMESPACE__ . '\tables');
		$error 					= &singleton::get(__NAMESPACE__ . '\error');
		$permissions 			= &singleton::get(__NAMESPACE__ . '\permissions');
		$permission_groups 		= &singleton::get(__NAMESPACE__ . '\permission_groups');
		$language 				= &singleton::get(__NAMESPACE__ . '\language');
		
		$query = "
			CREATE TABLE IF NOT EXISTS `$tables->table_access_cf` (
			  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `site_id` int(11) unsigned NOT NULL,
			  `table_name` varchar(255) NOT NULL,
			  `name` varchar(255) DEFAULT NULL,
			  `type` varchar(255) NOT NULL,
			  `client_modify` int(1) NOT NULL,
			  `enabled` int(1) NOT NULL,
			  `allowed_values` LONGTEXT NULL,
			  `index_display` int(1) NOT NULL DEFAULT '0',
			  PRIMARY KEY (`id`),
			  KEY `site_id` (`site_id`),
			  KEY `enabled` (`enabled`)
			) DEFAULT CHARSET=utf8;
		";
		
				
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$config->set('database_version', 91);		
		$config->set('program_version', '5.0-beta-1');		

	}
	
	private function dbsv_92() {
		global $db;
		
		$config 				= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 				= &singleton::get(__NAMESPACE__ . '\tables');
		$error 					= &singleton::get(__NAMESPACE__ . '\error');
		$permissions 			= &singleton::get(__NAMESPACE__ . '\permissions');
		$permission_groups 		= &singleton::get(__NAMESPACE__ . '\permission_groups');
		$language 				= &singleton::get(__NAMESPACE__ . '\language');
		
		$query = "ALTER TABLE `$tables->ticket_priorities` ADD `colour` varchar(255) DEFAULT NULL";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$config->set('database_version', 92);		
	}
	
	private function dbsv_93() {
		global $db;
		
		$config 				= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 				= &singleton::get(__NAMESPACE__ . '\tables');
		$error 					= &singleton::get(__NAMESPACE__ . '\error');
		$permissions 			= &singleton::get(__NAMESPACE__ . '\permissions');
		$permission_groups 		= &singleton::get(__NAMESPACE__ . '\permission_groups');
		$language 				= &singleton::get(__NAMESPACE__ . '\language');
		
		/*
			Convert Users to Department Notifications
		*/
		
		$user_levels_to_department_notifications				= &singleton::get(__NAMESPACE__ . '\user_levels_to_department_notifications');
		$permission_groups_to_department_notifications			= &singleton::get(__NAMESPACE__ . '\permission_groups_to_department_notifications');

		$array = $user_levels_to_department_notifications->get();
	
		//1 through 6 user_levels match new group_id system
		foreach($array as $item) {
			$permission_groups_to_department_notifications->add(
				array(
					'columns' => array(
						'department_id' => $item['department_id'],
						'group_id'		=> $item['user_level'],
						'type'			=> $item['type']
						)
					)
				);
		}
				
		$config->set('database_version', 93);			
	
	}

	private function dbsv_94() {
		global $db;
		
		$config 				= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 				= &singleton::get(__NAMESPACE__ . '\tables');
		$error 					= &singleton::get(__NAMESPACE__ . '\error');
		$permissions 			= &singleton::get(__NAMESPACE__ . '\permissions');
		$permission_groups 		= &singleton::get(__NAMESPACE__ . '\permission_groups');
		$language 				= &singleton::get(__NAMESPACE__ . '\language');
		
		// moved permissions around
		
		$config->set('database_version', 94);			
	
	}

	private function dbsv_95() {
		global $db;
		
		$config 				= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 				= &singleton::get(__NAMESPACE__ . '\tables');
		$error 					= &singleton::get(__NAMESPACE__ . '\error');
		$permissions 			= &singleton::get(__NAMESPACE__ . '\permissions');
		$permission_groups 		= &singleton::get(__NAMESPACE__ . '\permission_groups');
		$language 				= &singleton::get(__NAMESPACE__ . '\language');
		
		$config->set('default_theme', 'bootstrap3');			
		$config->set('default_theme_sub', 'v5');

		
		$config->set('program_version', '5.0.0');		
		
		$config->set('database_version', 95);			
	
	}
	
	private function dbsv_96() {
		global $db;
		
		$config 				= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 				= &singleton::get(__NAMESPACE__ . '\tables');
		$error 					= &singleton::get(__NAMESPACE__ . '\error');
		$permissions 			= &singleton::get(__NAMESPACE__ . '\permissions');
		$permission_groups 		= &singleton::get(__NAMESPACE__ . '\permission_groups');
		$language 				= &singleton::get(__NAMESPACE__ . '\language');
				
		$config->set('program_version', '5.0.1');		
		
		$config->set('database_version', 96);			
	
	}
	
	private function dbsv_97() {
		global $db;
		
		$config 				= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 				= &singleton::get(__NAMESPACE__ . '\tables');
		$error 					= &singleton::get(__NAMESPACE__ . '\error');
		$permissions 			= &singleton::get(__NAMESPACE__ . '\permissions');
		$permission_groups 		= &singleton::get(__NAMESPACE__ . '\permission_groups');
		$language 				= &singleton::get(__NAMESPACE__ . '\language');
				
		$config->add('finance_company_info', '');

		$config->set('database_version', 97);			
	
	}
	
	private function dbsv_98() {
		global $db;
		
		$config 				= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 				= &singleton::get(__NAMESPACE__ . '\tables');
		$error 					= &singleton::get(__NAMESPACE__ . '\error');
		$permissions 			= &singleton::get(__NAMESPACE__ . '\permissions');
		$permission_groups 		= &singleton::get(__NAMESPACE__ . '\permission_groups');
		$language 				= &singleton::get(__NAMESPACE__ . '\language');
				
		$config->set('program_version', '5.0.2');		

		$config->set('database_version', 98);			
	
	}

	private function dbsv_99() {
		global $db;
		
		$config 				= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 				= &singleton::get(__NAMESPACE__ . '\tables');
		$error 					= &singleton::get(__NAMESPACE__ . '\error');
		$permissions 			= &singleton::get(__NAMESPACE__ . '\permissions');
		$permission_groups 		= &singleton::get(__NAMESPACE__ . '\permission_groups');
		$language 				= &singleton::get(__NAMESPACE__ . '\language');
				
		$canned_id = $permissions->add_task(array('name' => 'tickets_view_canned_responses'));
		
		$permissions->add_task_to_group(array('task_id' => $canned_id, 'group_id' => 2));
		$permissions->add_task_to_group(array('task_id' => $canned_id, 'group_id' => 4));
		$permissions->add_task_to_group(array('task_id' => $canned_id, 'group_id' => 5));
		$permissions->add_task_to_group(array('task_id' => $canned_id, 'group_id' => 6));
	
	
		$config->set('program_version', '5.0.3');		

		$config->set('database_version', 99);		
	
	}

	private function dbsv_100() {
		global $db;
		
		$config 				= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 				= &singleton::get(__NAMESPACE__ . '\tables');
		$error 					= &singleton::get(__NAMESPACE__ . '\error');
		$permissions 			= &singleton::get(__NAMESPACE__ . '\permissions');
		$permission_groups 		= &singleton::get(__NAMESPACE__ . '\permission_groups');
		$language 				= &singleton::get(__NAMESPACE__ . '\language');
	
		$query = "ALTER TABLE `$tables->tickets` MODIFY `subject` VARCHAR(255) NULL";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}				
		
		$query = "ALTER TABLE `$tables->ticket_history` MODIFY `subject` VARCHAR(255) NULL";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}	

		$config->add('pop_auto_reopen_ticket', 0);		
		
		
		$config->set('program_version', '5.0.4');		
		$config->set('database_version', 100);		
	
	}
		
	private function dbsv_101() {
		global $db;
		
		$config 				= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 				= &singleton::get(__NAMESPACE__ . '\tables');
		$error 					= &singleton::get(__NAMESPACE__ . '\error');
		$permissions 			= &singleton::get(__NAMESPACE__ . '\permissions');
		$permission_groups 		= &singleton::get(__NAMESPACE__ . '\permission_groups');
		$language 				= &singleton::get(__NAMESPACE__ . '\language');
	
		$query = "ALTER TABLE `$tables->storage` ADD `public` INT(1) NOT NULL DEFAULT 0";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}				
		
		$config->set('program_version', '5.1-alpha-1');		
		$config->set('database_version', 101);		
	
	}
	
	private function dbsv_102() {
		global $db;
		
		$config 				= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 				= &singleton::get(__NAMESPACE__ . '\tables');
		$error 					= &singleton::get(__NAMESPACE__ . '\error');
		$permissions 			= &singleton::get(__NAMESPACE__ . '\permissions');
		$permission_groups 		= &singleton::get(__NAMESPACE__ . '\permission_groups');
		$language 				= &singleton::get(__NAMESPACE__ . '\language');
	
		$query = "ALTER TABLE `$tables->permission_groups` ADD `global_message` VARCHAR(255) NULL";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}				
		
		$config->set('database_version', 102);		
	
	}
	
	private function dbsv_103() {
		global $db;
		
		$config 				= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 				= &singleton::get(__NAMESPACE__ . '\tables');
		$error 					= &singleton::get(__NAMESPACE__ . '\error');
		$permissions 			= &singleton::get(__NAMESPACE__ . '\permissions');
		$permission_groups 		= &singleton::get(__NAMESPACE__ . '\permission_groups');
		$language 				= &singleton::get(__NAMESPACE__ . '\language');
				
		$delete_id = $permissions->add_task(array('name' => 'tickets_delete'));
		
		//Admin, Mod, Global Mod
		$permissions->add_task_to_group(array('task_id' => $delete_id, 'group_id' => 2));
		$permissions->add_task_to_group(array('task_id' => $delete_id, 'group_id' => 5));
		$permissions->add_task_to_group(array('task_id' => $delete_id, 'group_id' => 6));
	
		$config->set('database_version', 103);		
	
	}
	
	private function dbsv_104() {
		global $db;
		
		$config 				= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 				= &singleton::get(__NAMESPACE__ . '\tables');
		$error 					= &singleton::get(__NAMESPACE__ . '\error');
		$permissions 			= &singleton::get(__NAMESPACE__ . '\permissions');
		$permission_groups 		= &singleton::get(__NAMESPACE__ . '\permission_groups');
		$language 				= &singleton::get(__NAMESPACE__ . '\language');
				
		$query = "ALTER TABLE `$tables->pop_accounts` ADD `state_id` int(11) unsigned NOT NULL DEFAULT 1";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
	
		$config->set('database_version', 104);	
	
	}
	
	private function dbsv_105() {
		global $db;
		
		$config 				= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 				= &singleton::get(__NAMESPACE__ . '\tables');
		$error 					= &singleton::get(__NAMESPACE__ . '\error');
		$permissions 			= &singleton::get(__NAMESPACE__ . '\permissions');
		$permission_groups 		= &singleton::get(__NAMESPACE__ . '\permission_groups');
		$language 				= &singleton::get(__NAMESPACE__ . '\language');
		
		$config->add('real_name_enabled', 1);
		$config->set('program_version', '5.1-alpha-2');	
	
		$config->set('database_version', 105);	
	
	}
	
	private function dbdv_106() {
		global $db;
		
		$config 				= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 				= &singleton::get(__NAMESPACE__ . '\tables');
		$error 					= &singleton::get(__NAMESPACE__ . '\error');
		$permissions 			= &singleton::get(__NAMESPACE__ . '\permissions');
		$permission_groups 		= &singleton::get(__NAMESPACE__ . '\permission_groups');
		$language 				= &singleton::get(__NAMESPACE__ . '\language');
		$notifications 			= &singleton::get(__NAMESPACE__ . '\notifications');
			
	
		$config->add('notification_password_reset_subject', '');
		$config->add('notification_password_reset_body', '');		
			
		$notifications->reset_password_reset_notification();	
		
		$config->set('database_version', 106);	
	
	}
	
	private function dbsv_107() {
		global $db;
		
		$config 				= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 				= &singleton::get(__NAMESPACE__ . '\tables');
		$error 					= &singleton::get(__NAMESPACE__ . '\error');
		$permissions 			= &singleton::get(__NAMESPACE__ . '\permissions');
		$permission_groups 		= &singleton::get(__NAMESPACE__ . '\permission_groups');
		$language 				= &singleton::get(__NAMESPACE__ . '\language');
		
		$config->set('program_version', '5.1');	
	
		$config->set('database_version', 107);	
	
	}
	
	private function dbsv_108() {
		global $db;
		
		$config 				= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 				= &singleton::get(__NAMESPACE__ . '\tables');
		$error 					= &singleton::get(__NAMESPACE__ . '\error');
		$permissions 			= &singleton::get(__NAMESPACE__ . '\permissions');
		$permission_groups 		= &singleton::get(__NAMESPACE__ . '\permission_groups');
		$language 				= &singleton::get(__NAMESPACE__ . '\language');
		$notifications 			= &singleton::get(__NAMESPACE__ . '\notifications');
		
		$config->add('notification_ticket_date_due_subject', '');
		$config->add('notification_ticket_date_due_body', '');		
			
		$notifications->reset_ticket_date_due_notification();	
		
		$query = "ALTER TABLE `$tables->users` ADD `view_ticket_filter_projects` int(1) unsigned DEFAULT 0";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}				
		
		$config->set('program_version', '5.2-alpha-1');	
	
		$config->set('database_version', 108);	
	
	}
	
	private function dbsv_109() {
		global $db;
		
		$config 				= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 				= &singleton::get(__NAMESPACE__ . '\tables');
		$error 					= &singleton::get(__NAMESPACE__ . '\error');
		$permissions 			= &singleton::get(__NAMESPACE__ . '\permissions');
		$permission_groups 		= &singleton::get(__NAMESPACE__ . '\permission_groups');
		$language 				= &singleton::get(__NAMESPACE__ . '\language');
			
		$query = "ALTER TABLE `$tables->tickets` ADD `archived` int(1) unsigned DEFAULT 0";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}				
		
		$query = "ALTER TABLE `$tables->tickets` ADD INDEX `archived` ( `archived` )";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
				
		$config->add('auto_archive_enabled', '0');
		$config->add('auto_archive_months', '8');
			
		$config->set('database_version', 109);	
	
	}
	
		
	private function dbsv_110() {
		global $db;
		
		$config 				= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 				= &singleton::get(__NAMESPACE__ . '\tables');
		$error 					= &singleton::get(__NAMESPACE__ . '\error');
		$permissions 			= &singleton::get(__NAMESPACE__ . '\permissions');
		$permission_groups 		= &singleton::get(__NAMESPACE__ . '\permission_groups');
		$language 				= &singleton::get(__NAMESPACE__ . '\language');
		
		$query = "CREATE TABLE IF NOT EXISTS `$tables->user_api_keys` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `name` varchar(255) NOT NULL,
		  `key` varchar(255) NOT NULL,
		  `date_added` datetime NOT NULL,
		  `user_id` int(11) unsigned NOT NULL,
		  `site_id` int(11) unsigned NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `key` (`key`),
		  KEY `user_id` (`user_id`),
		  KEY `site_id` (`site_id`)
		) DEFAULT CHARSET=utf8;
		";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$api_id = $permissions->add_task(array('name' => 'api_access'));
		
		$permissions->add_task_to_group(array('task_id' => $api_id, 'group_id' => 1));
		$permissions->add_task_to_group(array('task_id' => $api_id, 'group_id' => 2));
		$permissions->add_task_to_group(array('task_id' => $api_id, 'group_id' => 3));
		$permissions->add_task_to_group(array('task_id' => $api_id, 'group_id' => 4));
		$permissions->add_task_to_group(array('task_id' => $api_id, 'group_id' => 5));
		$permissions->add_task_to_group(array('task_id' => $api_id, 'group_id' => 6));
	
		$config->set('database_version', 110);	
	
	}
	
	private function dbsv_111() {
		global $db;
		
		$config 				= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 				= &singleton::get(__NAMESPACE__ . '\tables');
		$error 					= &singleton::get(__NAMESPACE__ . '\error');
		$permissions 			= &singleton::get(__NAMESPACE__ . '\permissions');
		$permission_groups 		= &singleton::get(__NAMESPACE__ . '\permission_groups');
		$language 				= &singleton::get(__NAMESPACE__ . '\language');
		
		$config->set('program_version', '5.2');	
	
		$config->set('database_version', 111);	
	
	}
	
	private function dbsv_112() {
		global $db;
		
		$config 				= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 				= &singleton::get(__NAMESPACE__ . '\tables');
		$error 					= &singleton::get(__NAMESPACE__ . '\error');
		$permissions 			= &singleton::get(__NAMESPACE__ . '\permissions');
		$permission_groups 		= &singleton::get(__NAMESPACE__ . '\permission_groups');
		$language 				= &singleton::get(__NAMESPACE__ . '\language');
	
		$query = "CREATE TABLE IF NOT EXISTS `$tables->files_to_ticket_notes` (
			`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
			`ticket_note_id` int(11) unsigned NOT NULL,
			`file_id` int(11) unsigned NOT NULL,
			`site_id` INT( 11 ) UNSIGNED NOT NULL ,
			`private` INT( 1 ) UNSIGNED NOT NULL DEFAULT '0',
			  KEY `ticket_note_id` (`ticket_note_id`),
			  KEY `file_id` (`file_id`),
			  KEY `private` ( `private` ),
			  KEY `site_id` (`site_id`)
			) DEFAULT CHARSET=utf8;
		";
			
				
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
			
			
		$config->set('program_version', '5.3-alpha-1');	
	
		$config->set('database_version', 112);
	
	}
	
	private function dbsv_113() {
		global $db;
		
		$config 				= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 				= &singleton::get(__NAMESPACE__ . '\tables');
		$error 					= &singleton::get(__NAMESPACE__ . '\error');
		$permissions 			= &singleton::get(__NAMESPACE__ . '\permissions');
		$permission_groups 		= &singleton::get(__NAMESPACE__ . '\permission_groups');
		$language 				= &singleton::get(__NAMESPACE__ . '\language');
	
		$query = "CREATE TABLE IF NOT EXISTS `$tables->language_words` (
			`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
			`site_id` INT( 11 ) UNSIGNED NOT NULL ,
			`version` varchar(255) NOT NULL,
			`name` LONGTEXT NOT NULL,
			`translation` LONGTEXT NOT NULL,
			`date_added` datetime NOT NULL,
			`last_modified` datetime NOT NULL,
			  KEY `site_id` (`site_id`)
			) DEFAULT CHARSET=utf8;
		";
				
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
				
		$config->set('database_version', 113);
	
	}
	
	private function dbsv_114() {
		global $db;
		
		$config 				= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 				= &singleton::get(__NAMESPACE__ . '\tables');
		$error 					= &singleton::get(__NAMESPACE__ . '\error');
		$permissions 			= &singleton::get(__NAMESPACE__ . '\permissions');
		$permission_groups 		= &singleton::get(__NAMESPACE__ . '\permission_groups');
		$language 				= &singleton::get(__NAMESPACE__ . '\language');
	

		$query = "ALTER TABLE `$tables->files_to_ticket_notes` ADD `ticket_id` int(11) unsigned NOT NULL";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$query = "ALTER TABLE `$tables->files_to_ticket_notes` ADD INDEX `ticket_id` ( `ticket_id` )";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}	
				
		$config->set('database_version', 114);
	
	}

	private function dbsv_115() {
		global $db;
		
		$config 				= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 				= &singleton::get(__NAMESPACE__ . '\tables');
		$error 					= &singleton::get(__NAMESPACE__ . '\error');
		$permissions 			= &singleton::get(__NAMESPACE__ . '\permissions');
		$permission_groups 		= &singleton::get(__NAMESPACE__ . '\permission_groups');
		$language 				= &singleton::get(__NAMESPACE__ . '\language');
	

		$config->set('program_version', '5.3');							
		$config->set('database_version', 115);
	
	}	
	
	private function dbsv_116() {
		global $db;
		
		$config 				= &singleton::get(__NAMESPACE__ . '\config');	
		$tables 				= &singleton::get(__NAMESPACE__ . '\tables');
		$error 					= &singleton::get(__NAMESPACE__ . '\error');
		$permissions 			= &singleton::get(__NAMESPACE__ . '\permissions');
		$permission_groups 		= &singleton::get(__NAMESPACE__ . '\permission_groups');
		$language 				= &singleton::get(__NAMESPACE__ . '\language');

		$config->set('default_theme', 'bootstrap3');			
		$config->set('default_theme_sub', 'v6dg');
		
		$config->set('program_version', '6.0');							
		$config->set('database_version', 116);
	
	}	
	
	
	
}

?>