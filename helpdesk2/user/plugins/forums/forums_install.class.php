<?php
/*
	Forums plugin for Tickets.
	Copyright Dalegroup Pty Ltd 2013	
*/

/*
	Required.
	You must include these two lines at the start of your plugin.
*/
namespace sts\plugins;
use sts;

/*
	The class name must be the same as your plugin file name.
	We recommend you use a prefix for your class and function names.
	
	bbawesome.plugin.php would have the class bbawesome
	
	Dalegroup Pty Ltd does not use prefixes, you should!
*/
class forums_install {

	private $database_version = 7;

	function __construct() {
		/*
			This will get called on the plugins settings page.
			Please use the load method for startup and not this constructor.
		*/
	}
	
		
	public function make_installed() {
		$config 		= &sts\singleton::get('sts\config');

		if (!$config->get('forums_installed')) {
			$this->install();
		}
		else if ($config->get('forums_version') < $this->database_version) {
			for ($i = $config->get('forums_version') + 1; $i <= $this->database_version; $i++) {
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
						
		$query = "CREATE TABLE IF NOT EXISTS `$tables->forum_sections` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `site_id` int(11) unsigned NOT NULL,
		  `name` varchar(255) NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `site_id` (`site_id`)
		) DEFAULT CHARSET=utf8;";
				
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$query = "CREATE TABLE IF NOT EXISTS `$tables->forum_threads` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `site_id` int(11) unsigned NOT NULL,
		  `user_id` int(11) unsigned NOT NULL,
		  `section_id` int(11) unsigned NOT NULL,
		  `title` varchar(255) NOT NULL,
		  `message` longtext NOT NULL,
		  `date_added` datetime NOT NULL,
		  `last_modified` datetime NOT NULL,
		  `views` int(11) unsigned DEFAULT '0',
		  PRIMARY KEY (`id`),
		  KEY `site_id` (`site_id`),
		  KEY `user_id` (`user_id`),
		  KEY `last_modified` (`last_modified`),
		  KEY `section_id` (`section_id`)
		) DEFAULT CHARSET=utf8;";
				
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$query = "CREATE TABLE IF NOT EXISTS `$tables->forum_posts` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `site_id` int(11) unsigned NOT NULL,
		  `thread_id` int(11) unsigned NOT NULL,
		  `user_id` int(11) unsigned NOT NULL,
		  `date_added` datetime NOT NULL,
		  `message` longtext NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `site_id` (`site_id`),
		  KEY `thread_id` (`thread_id`),
		  KEY `user_id` (`user_id`)
		) DEFAULT CHARSET=utf8;";
				
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		
		$query = "CREATE TABLE IF NOT EXISTS `$tables->forum_to_user_levels` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `site_id` int(11) unsigned NOT NULL,
		  `section_id` int(11) unsigned NOT NULL,
		  `user_level` int(11) unsigned NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `site_id` (`site_id`),
		  KEY `main` (`section_id`, `user_level`),
		  KEY `section_id` (`section_id`),
		  KEY `user_level` (`user_level`)
		) DEFAULT CHARSET=utf8;";
				
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
		

		$config->add('forums_version', 2);
		$config->add('forums_installed', 1);
		
		$log_array['event_severity'] = 'notice';
		$log_array['event_number'] = E_USER_NOTICE;
		$log_array['event_description'] = 'Forums Installed.';
		$log_array['event_file'] = __FILE__;
		$log_array['event_file_line'] = __LINE__;
		$log_array['event_type'] = 'install';
		$log_array['event_source'] = 'forums_install';
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
						
		$query = "CREATE TABLE IF NOT EXISTS `$tables->forum_to_user_levels` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `site_id` int(11) unsigned NOT NULL,
		  `section_id` int(11) unsigned NOT NULL,
		  `user_level` int(11) unsigned NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `site_id` (`site_id`),
		  KEY `main` (`section_id`, `user_level`),
		  KEY `section_id` (`section_id`),
		  KEY `user_level` (`user_level`)
		) DEFAULT CHARSET=utf8;";
				
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}	

		$query = "ALTER TABLE `$tables->forum_threads` ADD `views` int(11) unsigned DEFAULT '0'";
		
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}		
		
		$config->set('forums_version', 2);

	}

	private function dbsv_3() {
		global $db;
		
		$config 		= &sts\singleton::get('sts\config');
		$tables 		= &sts\singleton::get('sts\tables');
		$error 			= &sts\singleton::get('sts\error');
		$log 			= &sts\singleton::get('sts\log');
						
		$query = "CREATE TABLE IF NOT EXISTS `$tables->forum_thread_subscriptions` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `site_id` int(11) unsigned NOT NULL,
		  `thread_id` int(11) unsigned NOT NULL,
		  `user_id` int(11) unsigned NOT NULL,
		  `date_added` datetime NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `site_id` (`site_id`),
		  KEY `thread_id` (`thread_id`),
		  KEY `user_id` (`user_id`)
		) DEFAULT CHARSET=utf8;";
				
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}	

		$config->set('forums_version', 3);
	
	}
	
	private function dbsv_4() {
		global $db;
		
		$config 		= &sts\singleton::get('sts\config');
		$tables 		= &sts\singleton::get('sts\tables');
		$error 			= &sts\singleton::get('sts\error');
		$log 			= &sts\singleton::get('sts\log');
						
		$query = "CREATE TABLE IF NOT EXISTS `$tables->forum_to_departments` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `site_id` int(11) unsigned NOT NULL,
		  `section_id` int(11) unsigned NOT NULL,
		  `department_id` int(11) unsigned NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `site_id` (`site_id`),
		  KEY `main` (`section_id`, `department_id`),
		  KEY `section_id` (`section_id`),
		  KEY `department_id` (`department_id`)
		) DEFAULT CHARSET=utf8;";
				
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}	

		$config->set('forums_version', 4);
	
	}
	
	private function dbsv_5() {
		global $db;
		
		$config 		= &sts\singleton::get('sts\config');
		$tables 		= &sts\singleton::get('sts\tables');
		$error 			= &sts\singleton::get('sts\error');
		$log 			= &sts\singleton::get('sts\log');
		$permissions 			= &sts\singleton::get('sts\permissions');
		$permission_groups 		= &sts\singleton::get('sts\permission_groups');
					
		$id = $permissions->add_task(array('name' => 'manage_forums'));
		$permissions->add_task_to_group(array('task_id' => $id, 'group_id' => 2));	

		$id = $permissions->add_task(array('name' => 'forums'));
		$permissions->add_task_to_group(array('task_id' => $id, 'group_id' => 2));	
			
		$config->set('forums_version', 5);

	}

	private function dbsv_6() {
		global $db;
		
		$config 		= &sts\singleton::get('sts\config');
		$tables 		= &sts\singleton::get('sts\tables');
		$error 			= &sts\singleton::get('sts\error');
		$log 			= &sts\singleton::get('sts\log');
						
		$query = "CREATE TABLE IF NOT EXISTS `$tables->forum_to_permission_groups` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `site_id` int(11) unsigned NOT NULL,
		  `section_id` int(11) unsigned NOT NULL,
		  `group_id` int(11) unsigned NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `site_id` (`site_id`),
		  KEY `main` (`section_id`, `group_id`),
		  KEY `section_id` (`section_id`),
		  KEY `group_id` (`group_id`)
		) DEFAULT CHARSET=utf8;";
				
		try {
			$db->query($query);
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}	
		
		$config->set('forums_version', 6);

	}
	
	private function dbsv_7() {
		global $db;
		
		$config 		= &sts\singleton::get('sts\config');
		$tables 		= &sts\singleton::get('sts\tables');
		$error 			= &sts\singleton::get('sts\error');
		$log 			= &sts\singleton::get('sts\log');
		$permissions 	= &sts\singleton::get('sts\permissions');
	
		$all_tasks 		= $permissions->get_available_tasks(array('all_tasks' => true));
						
		foreach($all_tasks as $task) {
			if ($task['name'] == 'forums') {
				//Add permissions
				$permissions->add_task_to_group(array('task_id' => $task['id'], 'group_id' => 1));	
				$permissions->add_task_to_group(array('task_id' => $task['id'], 'group_id' => 3));	
				$permissions->add_task_to_group(array('task_id' => $task['id'], 'group_id' => 4));	
				$permissions->add_task_to_group(array('task_id' => $task['id'], 'group_id' => 5));	
				$permissions->add_task_to_group(array('task_id' => $task['id'], 'group_id' => 6));	
			}
		}
	
		$config->set('forums_version', 7);

	}
}

?>