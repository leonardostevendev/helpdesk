<?php
/*
	Articles plugin for Tickets.
	Copyright Dalegroup Pty Ltd 2014	
*/

namespace sts\plugins;
use sts;

class articles_support {

	private $database_version = 4;

	public function make_installed() {
		$config 		= &sts\singleton::get('sts\config');

		if (!$config->get('articles_installed')) {
			$this->install();
		}
		else if ($config->get('articles_version') < $this->database_version) {
			for ($i = $config->get('articles_version') + 1; $i <= $this->database_version; $i++) {
				if (method_exists($this, 'dbsv_' . $i)) {
					call_user_func(array($this, 'dbsv_' . $i));		
				}
			}
		}

	}
	
	private function install() {
		global $db;
		
		$config 		= &sts\singleton::get('sts\config');
		$tables 		= &sts\singleton::get('sts\tables');
		$error 			= &sts\singleton::get('sts\error');
		$log 			= &sts\singleton::get('sts\log');
						
		$query = "CREATE TABLE IF NOT EXISTS `$tables->articles` (
		`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
		`date_added` datetime NOT NULL,
		`last_modified` datetime NOT NULL,
		`subject` VARCHAR( 255 ) NOT NULL ,
		`description` LONGTEXT NOT NULL,
		`public` int( 1 ) UNSIGNED NOT NULL DEFAULT '0',
		`published` int( 1 ) UNSIGNED NOT NULL DEFAULT '0',
		`category_id` int(11) UNSIGNED NOT NULL,
		`user_id` int(11) UNSIGNED NOT NULL,
		`site_id` INT( 11 ) UNSIGNED NOT NULL ,
		`views` int(11) unsigned DEFAULT '0',
		 KEY `public` (`public`),
		 KEY `published` (`published`),
		 KEY `category_id` (`category_id`),
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
		
			
		$query = "CREATE TABLE IF NOT EXISTS `$tables->article_categories` (
			`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
			`name` VARCHAR( 255 ) NOT NULL ,
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
		
		
		$query = "CREATE TABLE IF NOT EXISTS `$tables->files_to_articles` (
			`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
			`article_id` int(11) unsigned NOT NULL,
			`file_id` int(11) unsigned NOT NULL,
			`site_id` INT( 11 ) UNSIGNED NOT NULL ,
			  KEY `article_id` (`article_id`),
			  KEY `file_id` (`file_id`),
			  KEY `site_id` (`site_id`)
			) DEFAULT CHARSET=utf8;
		";
		
						
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		
		$config->add('articles_version', '2');
		$config->add('articles_installed', 1);
		
		$log_array['event_severity'] = 'notice';
		$log_array['event_number'] = E_USER_NOTICE;
		$log_array['event_description'] = 'Articles Database Installed.';
		$log_array['event_file'] = __FILE__;
		$log_array['event_file_line'] = __LINE__;
		$log_array['event_type'] = 'install';
		$log_array['event_source'] = 'articles_support';
		$log_array['event_version'] = '1';
		$log_array['log_backtrace'] = false;	
				
		$log->add($log_array);
	}
	
	private function dbsv_2() {
		global $db;
		
		$config 		= &sts\singleton::get('sts\config');
		$tables 		= &sts\singleton::get('sts\tables');
		$error 			= &sts\singleton::get('sts\error');
		$log 			= &sts\singleton::get('sts\log');
	
		$query = "ALTER TABLE `$tables->articles` ADD `views` int(11) unsigned DEFAULT '0'";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}		
		
		$config->set('articles_version', 2);

	}

	private function dbsv_3() {
		global $db;
		
		$config 		= &sts\singleton::get('sts\config');
		$tables 		= &sts\singleton::get('sts\tables');
		$error 			= &sts\singleton::get('sts\error');
		$log 			= &sts\singleton::get('sts\log');
		$permissions 			= &sts\singleton::get('sts\permissions');
		$permission_groups 		= &sts\singleton::get('sts\permission_groups');
					
		//articles
		$id = $permissions->add_task(array('name' => 'manage_articles'));
		$permissions->add_task_to_group(array('task_id' => $id, 'group_id' => 2));	

		$id = $permissions->add_task(array('name' => 'articles'));
		$permissions->add_task_to_group(array('task_id' => $id, 'group_id' => 2));	
			
		$config->set('articles_version', 3);

	}
	
	
	private function dbsv_4() {
		global $db;
		
		$config 		= &sts\singleton::get('sts\config');
		$tables 		= &sts\singleton::get('sts\tables');
		$error 			= &sts\singleton::get('sts\error');
		$log 			= &sts\singleton::get('sts\log');
		$permissions 	= &sts\singleton::get('sts\permissions');
	
		$all_tasks 		= $permissions->get_available_tasks(array('all_tasks' => true));
						
		foreach($all_tasks as $task) {
			if ($task['name'] == 'articles') {
				//Add permissions
				$permissions->add_task_to_group(array('task_id' => $task['id'], 'group_id' => 1));	
				$permissions->add_task_to_group(array('task_id' => $task['id'], 'group_id' => 3));	
				$permissions->add_task_to_group(array('task_id' => $task['id'], 'group_id' => 4));	
				$permissions->add_task_to_group(array('task_id' => $task['id'], 'group_id' => 5));	
				$permissions->add_task_to_group(array('task_id' => $task['id'], 'group_id' => 6));	
			}
		}
	
		$config->set('articles_version', 4);

	}
	
}

?>