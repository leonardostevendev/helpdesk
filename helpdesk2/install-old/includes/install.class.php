<?php
/*
	Copyright Dalegroup Pty Ltd 2014
*/
class install {

	var $db;
	private $tasks;
	private $config;

	function __construct() {
		$this->config['version']	= '6.0';
		$this->config['db_version']	= 116;
	}
	
	public function system_check() {
		$system_check = array();

		$system_check['php_version']	= PHP_VERSION;
		$system_check['pass']			= true;

		$system_check['php']			= true;
		if (version_compare(PHP_VERSION, '5.4.0', '<')) {
			$system_check['php']		= false;
			$system_check['pass']		= false;
		}

		$system_check['php_pdo']		= true;
		if (!extension_loaded('pdo')) {
			$system_check['php_pdo']	= false;
			$system_check['pass']		= false;
		}

		$system_check['php_pdo_mysql']		= true;
		if (!extension_loaded('pdo_mysql')) {
			$system_check['php_pdo_mysql']	= false;
			$system_check['pass']		= false;
		}

		$system_check['php_mcrypt']		= true;
		if (!extension_loaded('mcrypt')) {
			$system_check['php_mcrypt']	= false;
			$system_check['pass']		= false;
		}

		$system_check['php_hash']			= true;
		if (!extension_loaded('hash')) {
			$system_check['php_hash']		= false;
			$system_check['pass']			= false;
		}

		$system_check['file_write']				= true;
		$system_check['config_file_write']		= true;

		if (isset($_GET['file_check']) && ($_GET['file_check'] == 'skip')) {

		}
		else {
			if (!$this->test_write()) {
				$system_check['file_write']			= false;
				$system_check['pass']				= false;
			}
			if (!$this->test_write_config()) {
				$system_check['config_file_write']	= false;
				$system_check['pass']				= false;
			}
		}

		$system_check['php_ldap']		= false;
		if (extension_loaded('ldap')) {
			$system_check['php_ldap']	= true;
		}

		$system_check['php_gd']		= false;
		if (extension_loaded('gd')) {
			$system_check['php_gd']	= true;
		}

		$system_check['php_openssl']	= false;
		if (extension_loaded('openssl')) {
			$system_check['php_openssl']	= true;
		}

		$system_check['php_mbstring']		= false;
		if (extension_loaded('mbstring')) {
			$system_check['php_mbstring']	= true;
		}

		if (file_exists(ROOT . '/user/settings/config.php')) {
			$system_check['pass'] = false;
		}

		$system_check['no_htaccess']			= true;
		if (file_exists(ROOT . '/.htaccess')) {
			$system_check['no_htaccess']		= false;
		}

		$storage_path = $this->storage_path();
		$system_check['storage_path']			= false;
		if (!empty($storage_path) && (is_writable($storage_path))) {
			$system_check['storage_path']		= true;
		}

		return $system_check;
	}
	
	function form_data($form_name, $default_value = NULL) {
		if (isset($_POST[$form_name])) {
			return $_POST[$form_name];
		}
		else if (isset($_SESSION['install_data']['config'][$form_name])) {
			return $_SESSION['install_data']['config'][$form_name];
		}
		else if (!empty($default_value)) {
			return $default_value;
		}
		else {
			return '';
		}
	}
	
	public function get_config($name) {
		if (isset($this->config[$name])) {
			return $this->config[$name];
		}
		else {
			return false;
		}
	}
	
	function set_form($form_name, $form_data) {
		$_SESSION['install_data']['config'][$form_name] = $form_data;
	}
	
	function session_data($form_name) {
		if (isset($_SESSION['install_data']['config'][$form_name])) {
			return $_SESSION['install_data']['config'][$form_name];
		}
		else {
			return false;
		}
	}
	
	function connect_db() {
	
		//start database connection here
		$ipm_db = new PDO('mysql:host=' . $this->session_data('dbhost') . ';dbname=' . $this->session_data('dbname'), $this->session_data('dbusername'), $this->session_data('dbpassword'));
	
		$ipm_db->exec('SET NAMES UTF8');
	
		$ipm_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$ipm_db->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
		
		$this->db = $ipm_db;
	}
	
	private function install_db_structure() {
			
		$query = "CREATE TABLE IF NOT EXISTS `config` (
					  `config_name` varchar(255) NOT NULL,
					  `config_value` LONGTEXT NOT NULL,
					  `site_id` int(11) unsigned NOT NULL,
					  KEY `site_id` (`site_id`)
				) DEFAULT CHARSET=utf8";
		
		try {
			$this->db->exec($query);
		}
		catch (Exception $e) {
			die($e->getMessage());
		}

		$query = "CREATE TABLE IF NOT EXISTS `events` (
			  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Event Primary Key',
			  `event_number` int(11) NOT NULL,
			  `user_id` int(11) unsigned NOT NULL COMMENT 'User ID',
			  `server_id` int(11) unsigned DEFAULT NULL COMMENT 'The ID of the remote log client',
			  `remote_id` int(11) unsigned DEFAULT NULL COMMENT 'The Event Primary Key from the remote client',
			  `event_date` datetime NOT NULL COMMENT 'Event Datetime in local timezone',
			  `event_date_utc` datetime NOT NULL COMMENT 'Event Datetime in UTC timezone',
			  `event_type` varchar(255) NOT NULL COMMENT 'The type of event',
			  `event_source` varchar(255) NOT NULL COMMENT 'Text description of the source of the event',
			  `event_severity` varchar(255) NOT NULL COMMENT 'Notice, Warning etc',
			  `event_file` text NOT NULL COMMENT 'The full file location of the source of the event',
			  `event_file_line` int(11) NOT NULL COMMENT 'The line in the file that triggered the event',
			  `event_ip_address` varchar(255) NOT NULL COMMENT 'IP Address of the user that triggered the event',
			  `event_summary` varchar(255) NULL COMMENT 'A summary of the description',
			  `event_description` text NOT NULL COMMENT 'Full description of the event',
			  `event_trace` LONGTEXT NULL COMMENT 'Full PHP trace',
			  `event_synced` int(1) unsigned DEFAULT '0',
			  `site_id` int(11) unsigned NOT NULL,
			  PRIMARY KEY (`id`),
			  KEY `event_type` (`event_type`),
			  KEY `event_source` (`event_source`),
			  KEY `user_id` (`user_id`),
			  KEY `server_id` (`server_id`),
			  KEY `event_date` (`event_date`),
			  KEY `site_id` (`site_id`)
			) DEFAULT CHARSET=utf8";
		
		try {
			$this->db->exec($query);
		}
		catch (Exception $e) {
			die($e->getMessage());
		}
		
		$query = "CREATE TABLE IF NOT EXISTS `sessions` (
			  `session_id` varchar(255) NOT NULL DEFAULT '',
			  `session_start` datetime NOT NULL,
			  `session_start_utc` datetime NOT NULL,
			  `session_expire` datetime NOT NULL,
			  `session_expire_utc` datetime NOT NULL,
			  `session_data` text,
			  `session_active_key` varchar(32) DEFAULT NULL,
			  `ip_address` varchar(100) DEFAULT NULL,
			  `site_id` int(11) unsigned NOT NULL,
			  PRIMARY KEY (`session_id`),
			  KEY `session_expire` (`session_expire`),
			  KEY `site_id` (`site_id`)
			) DEFAULT CHARSET=utf8";
		
		try {
			$this->db->exec($query);
		}
		catch (Exception $e) {
			die($e->getMessage());
		}
				
		$query = "CREATE TABLE IF NOT EXISTS `users` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `name` varchar(255) DEFAULT NULL,
		  `username` varchar(255) DEFAULT NULL,
		  `password` varchar(255) NULL,
		  `salt` varchar(255) NOT NULL,
		  `email` varchar(255) NULL,
		  `authentication_id` int(11) unsigned NOT NULL DEFAULT '0',
		  `group_id` int(11) unsigned NOT NULL DEFAULT '0',
		  `user_level` int(11) unsigned NOT NULL DEFAULT '1',
		  `allow_login` int(1) unsigned NOT NULL DEFAULT '0',
		  `site_id` int(11) unsigned NOT NULL,
		  `failed_logins` INT( 11 ) UNSIGNED NULL,
		  `fail_expires` DATETIME NULL,
		  `email_notifications` int(1) unsigned NOT NULL DEFAULT '1',
		  `reset_key` VARCHAR(255) NULL,
		  `reset_expiry` DATETIME NULL,
		  `address` TEXT NULL,
		  `phone_number` VARCHAR(255) NULL,
		  `pushover_key` VARCHAR(255) NULL,
		  `company_id` int(11) unsigned DEFAULT NULL,
		  `date_added` datetime DEFAULT NULL,
		  `facebook_id` BIGINT(20) DEFAULT NULL,
		  `view_ticket_reverse` int(1) unsigned DEFAULT 0,
		  `timesheets_hourly_rate` decimal(10,2) DEFAULT NULL,
		  `view_ticket_filter_projects` int(1) unsigned DEFAULT 0,
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `username` (`username`),
		  KEY `site_id` (`site_id`),
		  KEY `pushover_key` ( `pushover_key` ),
		  KEY `company_id` ( `company_id` ),
		  KEY `facebook_id` ( `facebook_id` ) 
		) DEFAULT CHARSET=utf8";
		
		try {
			$this->db->exec($query);
		}
		catch (Exception $e) {
			die($e->getMessage());
		}
		
		//tickets
		$query = "CREATE TABLE IF NOT EXISTS `tickets` (
			  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `date_added` datetime NOT NULL,
			  `last_modified` datetime NOT NULL,
			  `subject` varchar(255) NULL,
			  `description` LONGTEXT NOT NULL,
			  `user_id` int(11) unsigned NOT NULL,
			  `priority_id` int(11) unsigned NOT NULL,
			  `state_id` int(11) unsigned NOT NULL DEFAULT '1',
			  `assigned_user_id` int(11) unsigned DEFAULT NULL,
			  `key` VARCHAR( 8 ) NULL,
			  `name` VARCHAR( 255 ) NULL,
			  `email` VARCHAR( 255 ) NULL,
			  `merge_ticket_id` int(11) UNSIGNED NULL,
			  `site_id` int(11) unsigned NOT NULL,
			  `submitted_user_id` int(11) unsigned DEFAULT NULL,
			  `department_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '1',
			  `html` INT( 1 ) UNSIGNED NOT NULL DEFAULT '0',
			  `date_state_changed` DATETIME NULL,
			  `access_key` VARCHAR( 32 ) NULL,
			  `pop_account_id` int(11) unsigned DEFAULT NULL,
			  `company_id` int(11) unsigned DEFAULT NULL,
			  `cc` LONGTEXT NULL,
			  `email_data` LONGTEXT NULL,
			  `date_due` date DEFAULT NULL,
			  `project_id` int(11) unsigned DEFAULT NULL,
			  `archived` int(1) unsigned DEFAULT 0,
			  PRIMARY KEY (`id`),
			  KEY `state_id` (`state_id`),
			  KEY `assigned_user_id` (`assigned_user_id`),
			  KEY `priority_id` (`priority_id`),
			  KEY `user_id` (`user_id`),
			  KEY `last_modified` (`last_modified`),
			  KEY `site_id` (`site_id`),
			  KEY `submitted_user_id` ( `submitted_user_id` ),
			  KEY `department_id` ( `department_id`  ),
			  KEY `date_state_changed` ( `date_state_changed` ),
			  KEY `access_key` ( `access_key` ),
			  KEY `pop_account_id` ( `pop_account_id` ),
			  KEY `company_id` ( `company_id` ),
			  KEY `date_due` ( `date_due` ),
			  KEY `project_id` ( `project_id` ),
			  KEY `archived` ( `archived` )
			) DEFAULT CHARSET=utf8;
		";
		
		try {
			$this->db->exec($query);
		}
		catch (Exception $e) {
			die($e->getMessage());
		}
		
		//priorities
		$query = "CREATE TABLE IF NOT EXISTS `ticket_priorities` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `name` varchar(255) NOT NULL,
		  `enabled` int( 1 ) NOT NULL DEFAULT '1',
		  `site_id` int(11) unsigned NOT NULL,
		  `colour` varchar(255) DEFAULT NULL,
		  PRIMARY KEY (`id`),
		  KEY `site_id` (`site_id`) 
		) DEFAULT CHARSET=utf8;
		";
		
		try {
			$this->db->exec($query);
		}
		catch (Exception $e) {
			die($e->getMessage());
		}
		
		
		//ticket notes
		$query = "CREATE TABLE IF NOT EXISTS `ticket_notes` (
			  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `ticket_id` int(11) unsigned NOT NULL,
			  `user_id` int(11) unsigned NOT NULL,
			  `description` LONGTEXT NOT NULL,
			  `subject` varchar(255) NULL,
			  `date_added` datetime NOT NULL,
			  `site_id` int(11) unsigned NOT NULL,
			  `html` INT( 1 ) UNSIGNED NOT NULL DEFAULT '0',
			  `private` INT( 1 ) UNSIGNED NOT NULL DEFAULT '0',
			  `name` VARCHAR(255) NULL,
			  `email` VARCHAR(255) NULL,
			  `cc` LONGTEXT NULL,
			  `company_id` int(11) unsigned DEFAULT NULL,
			  `email_data` LONGTEXT NULL,
			  PRIMARY KEY (`id`),
			  KEY `ticket_id` (`ticket_id`),
			  KEY `user_id` (`user_id`),
			  KEY `site_id` (`site_id`),
			  KEY `private` (`private`),
			  KEY `company_id` (`company_id`)
			) DEFAULT CHARSET=utf8;
		";
		
		try {
			$this->db->exec($query);
		}
		catch (Exception $e) {
			die($e->getMessage());
		}
				
		//queue
		$query = "CREATE TABLE IF NOT EXISTS `queue` (
					  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
					  `data` longtext NOT NULL,
					  `type` varchar(255) NOT NULL,
					  `start_date` datetime DEFAULT NULL,
					  `date` datetime NOT NULL,
					  `retry` int(11) unsigned DEFAULT '0',
					  `site_id` int(11) unsigned NOT NULL,
					  PRIMARY KEY (`id`),
					  KEY `site_id` (`site_id`) 
				)  DEFAULT CHARSET=utf8;
				";
		
		try {
			$this->db->exec($query);
		}
		catch (Exception $e) {
			die($e->getMessage());
		}
		
		//storage
		
		$query = "
		CREATE TABLE IF NOT EXISTS `storage` (
			`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
			`name` VARCHAR( 255 ) NULL ,
			`uuid` VARCHAR( 255 ) NOT NULL ,
			`date_added` DATETIME NOT NULL ,
			`extension` VARCHAR( 255 ) NULL ,
			`description` TEXT NULL ,
			`type` VARCHAR( 255 ) NULL ,
			`category_id` INT( 11 ) UNSIGNED NULL ,
			`user_id` INT( 11 ) UNSIGNED NULL ,
			`site_id` INT( 11 ) UNSIGNED NOT NULL ,
			`public` INT(1) NOT NULL DEFAULT 0,
			UNIQUE (
				`uuid`
			)
		) DEFAULT CHARSET=utf8;		
		";
		
		try {
			$this->db->exec($query);
		}
		catch (Exception $e) {
			die($e->getMessage());
		}
		
		$query = "CREATE TABLE IF NOT EXISTS `files_to_tickets` (
			`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
			`ticket_id` int(11) unsigned NOT NULL,
			`file_id` int(11) unsigned NOT NULL,
			`site_id` INT( 11 ) UNSIGNED NOT NULL ,
			`private` INT( 1 ) UNSIGNED NOT NULL DEFAULT '0',
			  KEY `ticket_id` (`ticket_id`),
			  KEY `file_id` (`file_id`),
			  KEY `private` ( `private` ),
			  KEY `site_id` (`site_id`)
			) DEFAULT CHARSET=utf8;
		";
		
		try {
			$this->db->exec($query);
		}
		catch (Exception $e) {
			die($e->getMessage());
		}
		
		$query = "CREATE TABLE IF NOT EXISTS `ticket_departments` (
		`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
		`name` VARCHAR( 255 ) NOT NULL ,
		`enabled` int( 1 ) UNSIGNED NOT NULL DEFAULT '1',
		`site_id` INT( 11 ) UNSIGNED NOT NULL ,
		`public_view` int(1) unsigned NOT NULL DEFAULT '1',
		 KEY `enabled` (`enabled`),
		 KEY `site_id` (`site_id`),
		 KEY `public_view` ( `public_view` )
		) DEFAULT CHARSET=utf8;
		";
	
		try {
			$this->db->exec($query);
		}
		catch (Exception $e) {
			die($e->getMessage());
		}
		
		//pop messages
		
		$query = "CREATE TABLE IF NOT EXISTS `pop_messages` (
		`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
		`message_id` text NOT NULL,
		`site_id` INT( 11 ) UNSIGNED NOT NULL,
		 KEY `message_id` (message_id(255)),
		 KEY `site_id` (`site_id`)
		) DEFAULT CHARSET=utf8;
		";
	
		try {
			$this->db->exec($query);
		}
		catch (Exception $e) {
			die($e->getMessage());
		}	

		$query = "
		CREATE TABLE IF NOT EXISTS pop_accounts (
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
			  `smtp_account_id` int(11) unsigned DEFAULT NULL,
			  `auto_create_users` int(1) unsigned DEFAULT 0,
			  `state_id` int(11) unsigned NOT NULL DEFAULT 1,
			  PRIMARY KEY (`id`),
			  KEY `site_id` (`site_id`),
			  KEY `enabled` (`enabled`)
		) DEFAULT CHARSET=utf8;
		";	

		try {
			$this->db->exec($query);
		}
		catch (Exception $e) {
			die($e->getMessage());
		}	

		$query = "
		CREATE TABLE IF NOT EXISTS `messages` (
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
			$this->db->exec($query);
		}
		catch (Exception $e) {
			die($e->getMessage());
		}
		
		$query = "
		CREATE TABLE IF NOT EXISTS `message_notes` (
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
			$this->db->exec($query);
		}
		catch (Exception $e) {
			die($e->getMessage());
		}
		
				
		$query = "
		CREATE TABLE IF NOT EXISTS `message_unread` (
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
			$this->db->exec($query);
		}
		catch (Exception $e) {
			die($e->getMessage());
		}

		$query = "CREATE TABLE IF NOT EXISTS `ticket_field_group` (
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
			$this->db->exec($query);
		}
		catch (Exception $e) {
			die($e->getMessage());
		}
		

		$query = "CREATE TABLE IF NOT EXISTS `ticket_fields` (
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
			$this->db->exec($query);
		}
		catch (Exception $e) {
			die($e->getMessage());
		}
		
		$query = "CREATE TABLE IF NOT EXISTS `ticket_field_values` (
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
			$this->db->exec($query);
		}
		catch (Exception $e) {
			die($e->getMessage());
		}
		
		//status
		$query = "CREATE TABLE IF NOT EXISTS `ticket_status` (
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
			$this->db->exec($query);
		}
		catch (Exception $e) {
			die($e->getMessage());
		}
		
		//SMTP accounts
		$query = "CREATE TABLE IF NOT EXISTS `smtp_accounts` (
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
			$this->db->exec($query);
		}
		catch (Exception $e) {
			die($e->getMessage());
		}
	

		//departments
		$query = "CREATE TABLE IF NOT EXISTS `users_to_departments` (
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
			$this->db->exec($query);
		}
		catch (Exception $e) {
			die($e->getMessage());
		}
		
		//api keys
		$query = "CREATE TABLE IF NOT EXISTS `api_keys` (
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
			$this->db->exec($query);
		}
		catch (Exception $e) {
			die($e->getMessage());
		}		
		
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
			$this->db->exec($query);
		}
		catch (Exception $e) {
			die($e->getMessage());
		}
		
		$query = "CREATE TABLE IF NOT EXISTS `canned_responses` (
		`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
		`name` VARCHAR( 255 ) NOT NULL ,
		`description` LONGTEXT NOT NULL,
		`site_id` INT( 11 ) UNSIGNED NOT NULL ,
		 KEY `site_id` (`site_id`)
		) DEFAULT CHARSET=utf8;
		";
		
		try {
			$this->db->exec($query);
		}
		catch (Exception $e) {
			die($e->getMessage());
		}
		
		
		$query = "CREATE TABLE IF NOT EXISTS `ticket_history` (
		`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
		`ticket_id` int( 11 ) unsigned NOT NULL,
		`type` VARCHAR( 255 ) NOT NULL ,
		`ip_address` varchar(255) NOT NULL,
		`date_added` datetime NOT NULL,
		`site_id` INT( 11 ) UNSIGNED NOT NULL ,
	    `by_user_id` int(11) unsigned NULL,
		`history_description` LONGTEXT NOT NULL,
		`reply` INT( 1 ) UNSIGNED NOT NULL DEFAULT '0',
		`subject` varchar(255) NULL,
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
		`date_due` date DEFAULT NULL,
		`project_id` int(11) unsigned DEFAULT NULL,
		 KEY `ticket_id` (`ticket_id`),
		 KEY `by_user_id` (`by_user_id`),
		 KEY `site_id` (`site_id`),
		 KEY `project_id` (`project_id`)
		) DEFAULT CHARSET=utf8;
		";
		
		try {
			$this->db->exec($query);
		}
		catch (Exception $e) {
			die($e->getMessage());
		}
		
		$query = "CREATE TABLE IF NOT EXISTS `ticket_views` (
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
			$this->db->exec($query);
		}
		catch (Exception $e) {
			die($e->getMessage());
		}
		
		//Permissions
		
		$query = "CREATE TABLE IF NOT EXISTS `permission_groups` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `name` varchar(255) NOT NULL,
		  `site_id` int(11) unsigned NOT NULL,
		  `allow_modify` int( 1 ) UNSIGNED NOT NULL DEFAULT '1',
		  `global_message` VARCHAR(255) NULL,
		  PRIMARY KEY (`id`),
		  KEY `site_id` (`site_id`)
		) DEFAULT CHARSET=utf8;
		";
		
		try {
			$this->db->exec($query);
		}
		catch (Exception $e) {
			die($e->getMessage());
		}
		
		$query = "CREATE TABLE IF NOT EXISTS `permission_tasks` (
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
			$this->db->exec($query);
		}
		catch (Exception $e) {
			die($e->getMessage());
		}
		
		$query = "
		CREATE TABLE IF NOT EXISTS `tasks_to_groups` (
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
			$this->db->exec($query);
		}
		catch (Exception $e) {
			die($e->getMessage());
		}
		
		$query = "
		CREATE TABLE IF NOT EXISTS `permission_groups_to_department_notifications` (
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
			$this->db->exec($query);
		}
		catch (Exception $e) {
			die($e->getMessage());
		}
	
		$query = "
			CREATE TABLE IF NOT EXISTS `table_access_cf` (
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
			$this->db->exec($query);
		}
		catch (Exception $e) {
			die($e->getMessage());
		}
		
		$query = "
		CREATE TABLE IF NOT EXISTS `user_api_keys` (
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
			$this->db->exec($query);
		}
		catch (Exception $e) {
			die($e->getMessage());
		}
	
		//112
		$query = "CREATE TABLE IF NOT EXISTS `files_to_ticket_notes` (
			`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
			`ticket_note_id` int(11) unsigned NOT NULL,
			`file_id` int(11) unsigned NOT NULL,
			`site_id` INT( 11 ) UNSIGNED NOT NULL ,
			`private` INT( 1 ) UNSIGNED NOT NULL DEFAULT '0',
			`ticket_id` int(11) unsigned NOT NULL,
			  KEY `ticket_note_id` (`ticket_note_id`),
			  KEY `file_id` (`file_id`),
			  KEY `private` ( `private` ),
			  KEY `ticket_id` ( `ticket_id` ),
			  KEY `site_id` (`site_id`)
			) DEFAULT CHARSET=utf8;
		";
					
		try {
			$this->db->exec($query);
		}
		catch (Exception $e) {
			die($e->getMessage());
		}	

		$query = "CREATE TABLE IF NOT EXISTS `language_words` (
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
			$this->db->exec($query);
		}
		catch (Exception $e) {
			die($e->getMessage());
		}		
		
	}
	
	private function install_db_config() {	
		
		$stmt = $this->db->prepare("INSERT INTO `config` (`config_name`, `config_value`, `site_id`) VALUES
			('domain', :domain, :site_id),
			('script_path', :path, :site_id),
			('https', :https, :site_id),
			('port_number', :port, :site_id),
			('name', :name, :site_id),
			('cookie_name', 'sts', :site_id),
			('encryption_key', :key, :site_id),
			('database_version', :db_version, :site_id),
			('program_version', :program, :site_id),
			('ad_server', '', :site_id),
			('ad_account_suffix', '', :site_id),
			('ad_base_dn', '', :site_id),
			('ad_create_accounts', 0, :site_id),
			('ad_enabled', 0, :site_id),
			('lockout_enabled', 1, :site_id),
			('login_message', '', :site_id),
			('cron_intervals', :cron_intervals, :site_id),
			('last_update_response', '', :site_id),
			('gravatar_enabled', 1, :site_id),
			('registration_enabled', 0, :site_id),
			('storage_enabled', :storage_enabled, :site_id),
			('storage_path', :storage_path, :site_id),
			('html_enabled', 1, :site_id),
			('default_department', 1, :site_id),
			('plugin_data', :plugin_data, :site_id),
			('plugin_update_data', :plugin_update_data, :site_id),
			('anonymous_tickets_reply', 1, :site_id),
			('notification_new_ticket_subject', :notification_new_ticket_subject, :site_id),
			('notification_new_ticket_body', :notification_new_ticket_body, :site_id),
			('notification_new_ticket_note_subject', :notification_new_ticket_note_subject, :site_id),
			('notification_new_ticket_note_body', :notification_new_ticket_note_body, :site_id),
			('notification_new_user_subject', :notification_new_user_subject, :site_id),
			('notification_new_user_body', :notification_new_user_body, :site_id),
			('notification_new_department_ticket_subject', :notification_new_department_ticket_subject, :site_id),
			('notification_new_department_ticket_body', :notification_new_department_ticket_body, :site_id),
			('notification_new_department_ticket_note_subject', :notification_new_department_ticket_note_subject, :site_id),
			('notification_new_department_ticket_note_body', :notification_new_department_ticket_note_body, :site_id),
			('notification_password_reset_subject', :notification_password_reset_subject, :site_id),
			('notification_password_reset_body', :notification_password_reset_body, :site_id),
			('notification_ticket_date_due_subject', :notification_ticket_date_due_subject, :site_id),
			('notification_ticket_date_due_body', :notification_ticket_date_due_body, :site_id),
			('guest_portal', 0, :site_id),
			('guest_portal_index_html', '', :site_id),
			('default_language', 'english_aus', :site_id),
			('captcha_enabled', 0, :site_id),
			('default_theme', 'bootstrap3', :site_id),
			('default_timezone', :timezone, :site_id),
			('default_smtp_account', 0, :site_id),
			('pushover_enabled', 0, :site_id),
			('pushover_user_enabled', 0, :site_id),
			('pushover_token', '', :site_id),
			('pushover_notify_users', :pushover_notify_users, :site_id),
			('license_key', '', :site_id),
			('log_limit', '100000', :site_id),	
			('ldap_server', '', :site_id),
			('ldap_account_suffix', '', :site_id),
			('ldap_base_dn', '', :site_id),
			('ldap_create_accounts', 0, :site_id),
			('ldap_enabled', 0, :site_id),
			('dalegroup_portal_uuid', '', :site_id),
			('dalegroup_portal_enabled', 0, :site_id),
			('api_enabled', 0, :site_id),
			('default_theme_sub', 'v6dg', :site_id),
			('application_id', 1, :site_id),
			('auto_update_cache', :auto_update_cache, :site_id),
			('notification_owner_on_pop', 0, :site_id),
			('default_company_id', '', :site_id),
			('ad_default_company_id', '', :site_id),
			('ldap_default_company_id', '', :site_id),
			('utc_date_installed', :datetime_utc, :site_id),
			('facebook_enabled', 0, :site_id),
			('facebook_app_id', '', :site_id),
			('facebook_app_secret', '', :site_id),
			('finance_dollar_sign', '$', :site_id),
			('finance_bank_enabled', '', :site_id),
			('finance_bank_bsb', '', :site_id),
			('finance_bank_account', '', :site_id),
			('finance_bank_account_name', '', :site_id),
			('finance_bank_name', '', :site_id),
			('finance_tax_number', '', :site_id),
			('finance_invoice_logo', '', :site_id),
			('finance_invoice_comment', '', :site_id),
			('pop_messages_limit', '100000', :site_id),
			('notification_ticket_assigned_user_subject', :notification_ticket_assigned_user_subject, :site_id),
			('notification_ticket_assigned_user_body', :notification_ticket_assigned_user_body, :site_id),
			('pop_auto_create_users', 1, :site_id),
			('smtp_reject_missing_message_id', 0, :site_id),
			('display_dashboard', 0, :site_id),	
			('auth_json_url', '', :site_id),
			('auth_json_key', '', :site_id),
			('auth_json_enabled', 0, :site_id),
			('auth_json_site_id', '', :site_id),
			('auth_json_create_accounts', 0, :site_id),
			('guest_portal_auto_create_users', 1, :site_id),
			('api_require_https', 1, :site_id),
			('auth_sso_enabled', 0, :site_id),
			('store_email_data', 1, :site_id),
			('auth_facebook_create_accounts', 0, :site_id),
			('ticket_views_enabled', 1, :site_id),
			('ticket_views_max_age', 30, :site_id),
			('session_life_time', 3600, :site_id),
			('finance_company_info', '', :site_id),
			('pop_auto_reopen_ticket', 0, :site_id),
			('real_name_enabled', 1, :site_id),
			('auto_archive_enabled', 0, :site_id),
			('auto_archive_months', 8, :site_id)
		");
	
		$domain 			= $this->session_data('domain');
		$name 				= $this->session_data('site_name');
		$script_path 		= $this->session_data('script_path');
		$port 				= (int) $this->session_data('port');
		$https 				= (int) $this->session_data('https');
		$key 				= $this->session_data('encryption_key');
		$db_version			= $this->get_config('db_version');
		$program			= $this->get_config('version');
		$timezone			= $this->session_data('default_timezone');
		$site_id			= (int) $this->session_data('site_id');
		$storage_enabled	= (int) $this->session_data('storage_enabled');
		$storage_path		= $this->session_data('storage_path');
		
		$cron_intervals = array(
			array('name' => 'every_minute', 'description' => 'Every Minute', 'next_run' => '0000-00-00 00:00:00', 'frequency' => '60'),
			array('name' => 'every_two_minutes', 'description' => 'Every Two Minutes', 'next_run' => '0000-00-00 00:00:00', 'frequency' => '120'),
			array('name' => 'every_five_minutes', 'description' => 'Every Five Minutes', 'next_run' => '0000-00-00 00:00:00', 'frequency' => '300'),
			array('name' => 'every_fifteen_minutes', 'description' => 'Every Fifteen Minutes', 'next_run' => '0000-00-00 00:00:00', 'frequency' => '900'),
			array('name' => 'every_hour', 'description' => 'Every Hour', 'next_run' => '0000-00-00 00:00:00', 'frequency' => '3600'),
			array('name' => 'every_day', 'description' => 'Every Day', 'next_run' => '0000-00-00 00:00:00', 'frequency' => '86400'),
			array('name' => 'every_week', 'description' => 'Every Week', 'next_run' => '0000-00-00 00:00:00', 'frequency' => '604800'),
			array('name' => 'every_month', 'description' => 'Every Month', 'next_run' => '0000-00-00 00:00:00', 'frequency' => '2592000')
		);
		
		$cron_intervals			= serialize($cron_intervals);
		$plugin_data			= serialize(array());
		$plugin_update_data		= serialize(array());
		$pushover_notify_users	= serialize(array());
		$auto_update_cache		= serialize(array());
		
		date_default_timezone_set($timezone);
		
		$datetime_utc			= datetime_utc();

		$stmt->bindParam(':site_id', $site_id, PDO::PARAM_INT);
		$stmt->bindParam(':domain', $domain, PDO::PARAM_STR);
		$stmt->bindParam(':name', $name, PDO::PARAM_STR);
		$stmt->bindParam(':path', $script_path, PDO::PARAM_STR);
		$stmt->bindParam(':port', $port, PDO::PARAM_INT);
		$stmt->bindParam(':https', $https, PDO::PARAM_INT);
		$stmt->bindParam(':key', $key, PDO::PARAM_STR);
		$stmt->bindParam(':db_version', $db_version, PDO::PARAM_STR);
		$stmt->bindParam(':program', $program, PDO::PARAM_STR);
		$stmt->bindParam(':cron_intervals', $cron_intervals, PDO::PARAM_STR);
		$stmt->bindParam(':plugin_data', $plugin_data, PDO::PARAM_STR);
		$stmt->bindParam(':plugin_update_data', $plugin_update_data, PDO::PARAM_STR);
		$stmt->bindParam(':pushover_notify_users', $pushover_notify_users, PDO::PARAM_STR);
		$stmt->bindParam(':timezone', $timezone, PDO::PARAM_STR);
		$stmt->bindParam(':auto_update_cache', $auto_update_cache, PDO::PARAM_STR);
		$stmt->bindParam(':storage_enabled', $storage_enabled, PDO::PARAM_INT);
		$stmt->bindParam(':storage_path', $storage_path, PDO::PARAM_STR);
		$stmt->bindParam(':datetime_utc', $datetime_utc, PDO::PARAM_STR);

		
		$notification_new_ticket_subject = '#SITE_NAME# - #TICKET_SUBJECT#';
		$notification_new_ticket_body = '
		#TICKET_DESCRIPTION#
		<br /><br />
		#TICKET_KEY#
		<br /><br />
		#GUEST_URL#';
		
		$notification_new_ticket_note_subject = '#SITE_NAME# - #TICKET_SUBJECT#';
		$notification_new_ticket_note_body = '
		#TICKET_NOTE_DESCRIPTION#
		<br /><br />
		#TICKET_KEY#
		<br /><br />
		#GUEST_URL#';
		
		$notification_new_user_subject = '#SITE_NAME# - New Account';
		$notification_new_user_body = '
		Hi #USER_FULLNAME#,
		<br /><br />
		A user account has been created for you at #SITE_NAME#.
		<br /><br />
		URL: 		#SITE_ADDRESS#<br />
		Name:		#USER_FULLNAME#<br />
		Username:	#USER_NAME#<br />
		Password:	#USER_PASSWORD#';
		
		$notification_new_department_ticket_subject = '#SITE_NAME# - #TICKET_SUBJECT#';
		$notification_new_department_ticket_body = '
		A new ticket has been created in a department that you are assigned to.
		<br />
		<br />
		<b>Subject</b>: #TICKET_SUBJECT#
		<br />
		<b>For</b>: #TICKET_OWNER_NAME# (#TICKET_OWNER_EMAIL#)
		<br />
		<b>Department</b>: #TICKET_DEPARTMENT#
		<br />
		<b>Priority</b>: #TICKET_PRIORITY#
		<br />
		<br />
		#TICKET_DESCRIPTION#
		<br />
		<br />
		#TICKET_URL#';

		$notification_new_department_ticket_note_subject = '#SITE_NAME# - #TICKET_SUBJECT#';
		$notification_new_department_ticket_note_body = '
		A new reply has been added to a ticket in a department that you are assigned to.
		<br />
		<br />
		<b>Subject</b>: #TICKET_SUBJECT#
		<br />
		<b>For</b>: #TICKET_OWNER_NAME# (#TICKET_OWNER_EMAIL#)
		<br />
		<b>Department</b>: #TICKET_DEPARTMENT#
		<br />
		<b>Priority</b>: #TICKET_PRIORITY#
		<br />
		<b>Assigned User</b>: #TICKET_ASSIGNED_NAME# (#TICKET_ASSIGNED_EMAIL#)
		<br />
		<br />
		#TICKET_NOTE_DESCRIPTION#
		<br />
		<br />
		#TICKET_URL#';	
		
		$stmt->bindParam(':notification_new_ticket_subject', $notification_new_ticket_subject, PDO::PARAM_STR);
		$stmt->bindParam(':notification_new_ticket_body', $notification_new_ticket_body, PDO::PARAM_STR);
		$stmt->bindParam(':notification_new_ticket_note_subject', $notification_new_ticket_note_subject, PDO::PARAM_STR);
		$stmt->bindParam(':notification_new_ticket_note_body', $notification_new_ticket_note_body, PDO::PARAM_STR);
		$stmt->bindParam(':notification_new_user_subject', $notification_new_user_subject, PDO::PARAM_STR);
		$stmt->bindParam(':notification_new_user_body', $notification_new_user_body, PDO::PARAM_STR);

		$stmt->bindParam(':notification_new_department_ticket_subject', $notification_new_department_ticket_subject, PDO::PARAM_STR);
		$stmt->bindParam(':notification_new_department_ticket_body', $notification_new_department_ticket_body, PDO::PARAM_STR);
		$stmt->bindParam(':notification_new_department_ticket_note_subject', $notification_new_department_ticket_note_subject, PDO::PARAM_STR);
		$stmt->bindParam(':notification_new_department_ticket_note_body', $notification_new_department_ticket_note_body, PDO::PARAM_STR);		
		
		$notification_ticket_assigned_user_subject = '#SITE_NAME# - Assigned To #TICKET_SUBJECT#';
		$notification_ticket_assigned_user_body = '
		A ticket has been assigned to you,
		<br />
		<br />
		<b>Subject</b>: #TICKET_SUBJECT#
		<br />
		<b>For</b>: #TICKET_OWNER_NAME# (#TICKET_OWNER_EMAIL#)
		<br />
		<b>Department</b>: #TICKET_DEPARTMENT#
		<br />
		<b>Priority</b>: #TICKET_PRIORITY#
		<br />
		<br />
		#TICKET_DESCRIPTION#
		<br />
		<br />
		#TICKET_URL#';
		
		
		$stmt->bindParam(':notification_ticket_assigned_user_subject', $notification_ticket_assigned_user_subject, PDO::PARAM_STR);
		$stmt->bindParam(':notification_ticket_assigned_user_body', $notification_ticket_assigned_user_body, PDO::PARAM_STR);		
	
		$notification_password_reset_subject = '#SITE_NAME# - Password Reset';
		$notification_password_reset_body = '
		A password reset request has been created for your account at #SITE_NAME#.<br /><br />
		To approve this reset please click on the following link:<br />
		#RESET_URL#';
		
		$stmt->bindParam(':notification_password_reset_subject', $notification_password_reset_subject, PDO::PARAM_STR);
		$stmt->bindParam(':notification_password_reset_body', $notification_password_reset_body, PDO::PARAM_STR);		

		$notification_ticket_date_due_subject = '#SITE_NAME# - #TICKET_SUBJECT# Due Tomorrow';
		$notification_ticket_date_due_body = '
		A ticket assigned to you is due tomorrow and is still open,
		<br />
		<br />
		<b>Subject</b>: #TICKET_SUBJECT#
		<br />
		<b>For</b>: #TICKET_OWNER_NAME# (#TICKET_OWNER_EMAIL#)
		<br />
		<b>Department</b>: #TICKET_DEPARTMENT#
		<br />
		<b>Priority</b>: #TICKET_PRIORITY#
		<br />
		<br />
		#TICKET_DESCRIPTION#
		<br />
		<br />
		#TICKET_URL#';
		
		$stmt->bindParam(':notification_ticket_date_due_subject', $notification_ticket_date_due_subject, PDO::PARAM_STR);
		$stmt->bindParam(':notification_ticket_date_due_body', $notification_ticket_date_due_body, PDO::PARAM_STR);		
		
		try {
			$stmt->execute();
		}
		catch (Exception $e) {
			die($e->getMessage());
		}	
	}
	
	private function install_db_data() {	
	
		$site_id		= (int) $this->session_data('site_id');

		
		$stmt = $this->db->prepare("INSERT INTO `ticket_priorities` (`name`, `enabled`, `site_id`) VALUES
			('Low', 1, :site_id),
			('Medium', 1, :site_id),
			('High', 1, :site_id)
		");
	
		$stmt->bindParam(':site_id', $site_id, PDO::PARAM_INT);
	
		try {
			$stmt->execute();
		}
		catch (Exception $e) {
			die($e->getMessage());
		}
		
		$stmt = $this->db->prepare("INSERT INTO `ticket_departments` (`name`, `enabled`, `site_id`) VALUES
			('Default Department', 1, :site_id)
		");
		
		$stmt->bindParam(':site_id', $site_id, PDO::PARAM_INT);
	
		try {
			$stmt->execute();
		}
		catch (Exception $e) {
			die($e->getMessage());
		}
		
		$stmt = $this->db->prepare("INSERT INTO `ticket_status` (`name`, `enabled`, `active`, `colour`, `site_id`) VALUES
			('Open', 1, 1, '#e93e3e', :site_id),
			('Closed', 1, 0, '#71c255', :site_id),
			('In Progress', 1, 1, '#00a3ff', :site_id)
		");
	
		$stmt->bindParam(':site_id', $site_id, PDO::PARAM_INT);

	
		try {
			$stmt->execute();
		}
		catch (Exception $e) {
			die($e->getMessage());
		}	

		$url = 'http://' . $this->session_data('domain') . $this->session_data('script_path');
		if ($this->session_data('https') == 1) {
			$url = 'https://' . $this->session_data('domain') . $this->session_data('script_path');
		}
$subject = 'Welcome Guide';
$description =
		'
<h1 style="text-align:center;">Welcome to <img src="'.$url.'/user/themes/bootstrap3/images/nav-logo-2x.png" /></h1>
<p>
	Hello and welcome to 
	<strong>Bluetrait Tickets</strong>!
</p>
<p>
	Lets run over a few of the features to get you started.
</p>
<h3>Email Integration</h3>
<p>
	Out of the box Bluetrait allows your clients to be kept up to date with email alerts. Bluetrait has built in templates so that you don\'t need to change anything.
</p>
<p>
	If you want, these alerts can be easily customised with your own design and brand. Simply head over to the 
	<a href="'.$url.'/settings/email/">Email Settings</a> page to modify them. Bluetrait uses special tokens that you can insert into your template, these tokens are used for setting things like the client name or ticket subject. We have a full list of the supported tokens <a href="http://bluetrait.com/documentation/">here</a>.
</p>
<h4>Email to Ticket Conversion</h4>
<p>
	Once you are happy with your templates you probably want to start importing emails and converting them into tickets.
</p>
<p>
	Bluetrait allows you to add email accounts, every 5 minutes Bluetrait will check these accounts for new emails. If any new emails are found Bluetrait will download them and create new tickets for each email.
</p>
<p>
	If you want to use this feature you will need an existing email account. We recommend that you start with a fresh empty email account otherwise Bluetrait will download and convert all of the emails into tickets, and we suspect you won\'t want that!
</p>
<p>
	For email to ticket conversion you will need both POP3 and SMTP details. POP3 is what Bluetrait uses to download your emails (and file attachments) and SMTP is so that Bluetrait can send emails from the account (the POP3 and SMTP details should be for the same email account).
</p>
<p>
	If you are unsure of your POP3/SMTP details you will need to contact your email provider.
</p>
<p>
	Once you have this running give it a test yourself. Replies to emails will be automatically added as ticket replies.
</p>
<p>
	If you have any issues with your POP3 or SMTP settings you can check the 
	<a href="'.$url.'/logs/">logs</a> for any errors.
</p>
<h3>Users</h3>
<p>
	Bluetrait is a multi-user system, this is one of the main benefits of using a ticketing system over email. It allows your staff members to keep up to date.
</p>
<p>
	There are a number of different levels of users that you can create. Each level is designed suit a specific user.
</p>
<p>
	All your clients will be \'Submitters\'. Submitters are free of charge, you can add as many as you like without increasing your monthly bill.
</p>
<p>
	The other user levels (User, Staff, Moderator, Global Moderator and Administrator) are designed for your staff.
</p>
<p>
	Here is what each of them can do.
</p>
<h5>Submitters</h5>
<ul>
	<li>Create tickets for themselves.</li>
	<li>Login, search and view the status of their own tickets.</li>
	<li>Close their own tickets.</li>
</ul>
<h5>Users</h5>
<p>
	The same as Submitters plus
</p>
<ul>
	<li>They can view and be assigned tickets to them</li>
</ul>
<p>
	Users are useful if you want to give a contractor access to the system but only allow tickets that are assign specifically to them.
</p>
<h5>Staff</h5>
<p>
	The same as Users plus
</p>
<ul>
	<li>They can view any tickets in their assigned department</li>
</ul>
<p>
	Staff are useful for most of your staff/employees. They can view and reply to all tickets in departments that they are a member of.
</p>
<h5>Moderator</h5>
<p>
	The same as Staff plus
</p>
<ul>
	<li>They gain access to the mass moderate feature (allowing them to close multiple tickets at a time)</li>
</ul>
<p>
	Moderators are useful for department managers or people dealing with lots of tickets in their department.
</p>
<h5>Global Moderator</h5>
<p>
	The same as Moderator plus
</p>
<ul>
	<li>They can access all tickets no matter what department they are assigned to.</li>
</ul>
<p>
	Global Moderators are useful for help desk masters, they get access to all tickets!
</p>
<h5>Administrator</h5>
<p>
	Full access. This is the access that you are given when the system is first created. You can change system settings and add new users. You should only give users this permission level if you really trust them.
</p>
<h3>Departments</h3>
<p>
	Departments allow you to categorise, limit and filter tickets.
</p>
<p>
	For example you could have a sales@example.com email account which gets imported into the sales department with only the sales staff having access.
</p>
<p>
	You could then have an info@example.com account which gets imported into the default department and give all staff access to them.
</p>
<p>
	Global Moderator and Administrator are exempt from the permissions but all other users must be assign to the departments that you wish to give access to.
</p>
<p>
	You can setup Bluetrait to email all staff within a department when a new ticket is added.
</p>
<p>
	This allows you to focus on the work that is important to you and leave the rest.
</p>
<h3>Help</h3>
<p>
	We hope this has given you a quick overview with some of the main features of Bluetrait. If you require any assistance or have suggestions please email us at support@bluetrait.com, we are keen to listen.
</p>		
';	
		//$this->create_ticket(array('subject' => $subject, 'description' => $description));
	
	$subject 		= 'Getting Started';
	$description 	= '
<ol>
	<li><a href="'.$url.'/tickets/view/1/">Read Welcome Guide</a></li>
	<li><a href="'.$url.'/settings/email/#pop3_accounts">Fill out your POP3 email account details</a></li>
	<li><a href="'.$url.'/settings/email/#smtp_accounts">Fill out your SMTP details</a></li>
	<li><a href="'.$url.'/settings/email/#email_templates">Check your email templates</a></li>
	<li><a href="'.$url.'/users/">Create Users</a></li>
</ol>	
';

		//$this->create_ticket(array('subject' => $subject, 'description' => $description, 'add_seconds' => true));
	
		//permission groups
			
		$stmt = $this->db->prepare("INSERT INTO `permission_groups` (`name`, `allow_modify`, `site_id`) VALUES
			('Submitter', 0, :site_id),
			('Administrator', 0, :site_id),
			('User', 0, :site_id),
			('Staff', 0, :site_id),
			('Moderator', 0, :site_id),
			('Global Moderator', 0, :site_id)
		");
	
		$stmt->bindParam(':site_id', $site_id, PDO::PARAM_INT);

	
		try {
			$stmt->execute();
		}
		catch (Exception $e) {
			die($e->getMessage());
		}	
	
		//tasks
		$stmt = $this->db->prepare("INSERT INTO `permission_tasks` (`name`, `site_id`) VALUES
			('manage_system_settings', :site_id),
			('manage_users', :site_id),
			('manage_logs', :site_id),
			('tickets', :site_id),
			('tickets_carbon_copy_reply', :site_id),
			('tickets_change_status', :site_id),
			('tickets_transfer_department', :site_id),
			('tickets_assign_user', :site_id),
			('tickets_view_private_replies', :site_id),
			('manage_tickets', :site_id),
			('tickets_view_assigned_department', :site_id),
			('tickets_view_assigned', :site_id),
			('tickets_view_audit_history', :site_id),
			('send_private_messages', :site_id),
			('tickets_view_canned_responses', :site_id),
			('tickets_delete', :site_id),
			('api_access', :site_id)		
		");
	
		$stmt->bindParam(':site_id', $site_id, PDO::PARAM_INT);

	
		try {
			$stmt->execute();
		}
		catch (Exception $e) {
			die($e->getMessage());
		}	
	
		//admin tasks
		$stmt = $this->db->prepare("INSERT INTO `tasks_to_groups` (`site_id`, `group_id`, `task_id`) VALUES
			(:site_id, 2, 1),
			(:site_id, 2, 2),
			(:site_id, 2, 3),
			(:site_id, 2, 4),
			(:site_id, 2, 5),
			(:site_id, 2, 6),
			(:site_id, 2, 7),
			(:site_id, 2, 8),
			(:site_id, 2, 9),
			(:site_id, 2, 10),
			(:site_id, 2, 11),
			(:site_id, 2, 12),
			(:site_id, 2, 13),
			(:site_id, 2, 13),
			(:site_id, 2, 14),
			(:site_id, 2, 15),
			(:site_id, 2, 16),
			(:site_id, 2, 17)
		");
			
		$stmt->bindParam(':site_id', $site_id, PDO::PARAM_INT);
	
		try {
			$stmt->execute();
		}
		catch (Exception $e) {
			die($e->getMessage());
		}	

		//submitter tasks
		$stmt = $this->db->prepare("INSERT INTO `tasks_to_groups` (`site_id`, `group_id`, `task_id`) VALUES
			(:site_id, 1, 4),
			(:site_id, 1, 17)
		");		
	
		$stmt->bindParam(':site_id', $site_id, PDO::PARAM_INT);
	
		try {
			$stmt->execute();
		}
		catch (Exception $e) {
			die($e->getMessage());
		}	
		
		
		//user tasks
		$stmt = $this->db->prepare("INSERT INTO `tasks_to_groups` (`site_id`, `group_id`, `task_id`) VALUES
			(:site_id, 3, 4),
			(:site_id, 3, 12),
			(:site_id, 3, 17)
		");		
	
		$stmt->bindParam(':site_id', $site_id, PDO::PARAM_INT);
	
		try {
			$stmt->execute();
		}
		catch (Exception $e) {
			die($e->getMessage());
		}	
			
		//staff tasks
		$stmt = $this->db->prepare("INSERT INTO `tasks_to_groups` (`site_id`, `group_id`, `task_id`) VALUES
			(:site_id, 4, 4),
			(:site_id, 4, 12),
			(:site_id, 4, 11),
			(:site_id, 4, 9),
			(:site_id, 4, 6),
			(:site_id, 4, 7),
			(:site_id, 4, 15),
			(:site_id, 4, 17)
		");		
	
		$stmt->bindParam(':site_id', $site_id, PDO::PARAM_INT);
	
		try {
			$stmt->execute();
		}
		catch (Exception $e) {
			die($e->getMessage());
		}	
	
		//mod tasks
		$stmt = $this->db->prepare("INSERT INTO `tasks_to_groups` (`site_id`, `group_id`, `task_id`) VALUES
			(:site_id, 5, 4),
			(:site_id, 5, 12),
			(:site_id, 5, 11),
			(:site_id, 5, 9),
			(:site_id, 5, 6),
			(:site_id, 5, 7),
			(:site_id, 5, 8),
			(:site_id, 5, 5),
			(:site_id, 5, 13),
			(:site_id, 5, 15),
			(:site_id, 5, 16),
			(:site_id, 5, 17)
		");		
	
		$stmt->bindParam(':site_id', $site_id, PDO::PARAM_INT);
	
		try {
			$stmt->execute();
		}
		catch (Exception $e) {
			die($e->getMessage());
		}	
		
		//global mod tasks
		$stmt = $this->db->prepare("INSERT INTO `tasks_to_groups` (`site_id`, `group_id`, `task_id`) VALUES
			(:site_id, 6, 4),
			(:site_id, 6, 12),
			(:site_id, 6, 11),
			(:site_id, 6, 9),
			(:site_id, 6, 6),
			(:site_id, 6, 7),
			(:site_id, 6, 8),
			(:site_id, 6, 5),
			(:site_id, 6, 13),
			(:site_id, 6, 10),
			(:site_id, 6, 14),
			(:site_id, 6, 15),
			(:site_id, 6, 16),
			(:site_id, 6, 17)
		");		
	
		$stmt->bindParam(':site_id', $site_id, PDO::PARAM_INT);
	
		try {
			$stmt->execute();
		}
		catch (Exception $e) {
			die($e->getMessage());
		}	
	
	}
	
	private function create_ticket($array) {

	
		$stmt = $this->db->prepare("INSERT INTO `tickets` (
			`date_added`, `last_modified`, `subject`, `description`, `user_id`, `priority_id`, `state_id`, `key`, `site_id`, `department_id`, `html`, `access_key`
			) VALUES
			(:date_added, :last_modified, :subject, :description, 1, 1, 1, :key, :site_id, 1, 1, :access_key)
		");
		
		$site_id			= (int) $this->session_data('site_id');
		$date_added			= datetime();
		$last_modified		= datetime();
		if (isset($array['add_seconds']) && ($array['add_seconds'] == true)) {
			$last_modified		= datetime(5);
		}
		$subject			= $array['subject'];
		$description		= $array['description'];
		$key				= rand_str(8);
		$access_key			= rand_str(32);
	
		$stmt->bindParam(':site_id', $site_id, PDO::PARAM_INT);
		$stmt->bindParam(':date_added', $date_added, PDO::PARAM_STR);
		$stmt->bindParam(':last_modified', $last_modified, PDO::PARAM_STR);
		$stmt->bindParam(':subject', $subject, PDO::PARAM_STR);
		$stmt->bindParam(':description', $description, PDO::PARAM_STR);
		$stmt->bindParam(':key', $key, PDO::PARAM_STR);
		$stmt->bindParam(':access_key', $access_key, PDO::PARAM_STR);

		try {
			$stmt->execute();
		}
		catch (Exception $e) {
			die($e->getMessage());
		}	
			
	}
	
	private function install_admin_user() {
	
		$user_salt 			= rand_str(64);
		$password			= $this->session_data('admin_password');
		$salt				= $this->session_data('site_salt');
		$hashed_password	= hash_hmac('sha512', $password . $user_salt, $salt);
		$site_id			= (int) $this->session_data('site_id');
	

		$stmt = $this->db->prepare("INSERT INTO `users` (
			`name`, `username`, `password`, `salt`, `email`, `authentication_id`, `user_level`, `allow_login`, `site_id`, `date_added`, `group_id`
			) VALUES (:name, :username, :password, :salt, :email, 1, 2, 1, :site_id, :date_added, 2)");
		
		$name 			= $this->session_data('admin_name');
		$username 		= $this->session_data('admin_username');
		$email 			= $this->session_data('admin_email');
		$date_added		= datetime();
			
		$stmt->bindParam(':site_id', $site_id, PDO::PARAM_INT);
		$stmt->bindParam(':name', $name, PDO::PARAM_STR);
		$stmt->bindParam(':username', $username, PDO::PARAM_STR);
		$stmt->bindParam(':password', $hashed_password, PDO::PARAM_STR);
		$stmt->bindParam(':salt', $user_salt, PDO::PARAM_STR);
		$stmt->bindParam(':email', $email, PDO::PARAM_STR);
		$stmt->bindParam(':date_added', $date_added, PDO::PARAM_STR);


		try {
			$stmt->execute();
		}
		catch (Exception $e) {
			die($e->getMessage());
		}

	}
	
	public function install_db() {
	
		if (!ini_get('safe_mode')) {
			//ooh we can process for sooo long
			set_time_limit(280); 
		}
	
		$this->install_db_structure();
		$this->install_db_config();
		$this->install_db_data();
		$this->install_admin_user();

	}
	
	public function install_import_db() {
		
		if (!ini_get('safe_mode')) {
			//ooh we can process for sooo long
			set_time_limit(280); 
		}
		
		$this->install_db_structure();	
		$this->install_db_config();
	}
	
	public function test_is_installed($database_connection) {
		
		$stmt = $database_connection->prepare("SHOW TABLES LIKE 'config'");
		
		try {
			$stmt->execute();
		}
		catch (Exception $e) {
			ipm_die($e->getMessage());
		}
		
		$array = $stmt->fetchAll(PDO::FETCH_ASSOC);
		
		if (!isset($array[0])) return false;
		
		return true;	
	}
	
	public function is_installed() {
		
		$stmt = $this->db->prepare("SHOW TABLES LIKE 'config'");
		
		try {
			$stmt->execute();
		}
		catch (Exception $e) {
			ipm_die($e->getMessage());
		}
		
		$array = $stmt->fetchAll(PDO::FETCH_ASSOC);
		
		if (!isset($array[0])) return false;
		
		return true;
	}
	
	public function test_write() {
	
		$filename 		= dirname(__FILE__) . '/../../.sts_test_file';
		
		$data = 'This is a test file for Tickets and can be deleted.';
		
		if (@file_put_contents($filename, $data)) { 
			return true;
		}
		else {
			return false;
		}
  
	}
	
	public function test_write_config() {
	
		$filename 		= dirname(__FILE__) . '/../../user/settings/.sts_test_file';
		
		$data = 'This is a test file for Tickets and can be deleted.';
		
		if (@file_put_contents($filename, $data)) { 
			return true;
		}
		else {
			return false;
		}
  
	}

	public function write_htaccess() {
	
		$data 			= array(
								'script_path' => $this->session_data('script_path')				
							);
	
		$filename 		= dirname(__FILE__) . '/../../.htaccess';
		$config_data	= $this->create_htaccess($data);
		
		if ($handle = @fopen($filename, 'x')) { 
  
			if (fwrite($handle, $config_data)) {   
				fclose($handle);
				return true;
			}
			else {
				$return['message'] = 'Unable to write "' . ipm_htmlentities($filename) . '". Please make sure you create it yourself.';
				$return['success'] = false;
				fclose($handle);
				return $return;
			}
		}
		else {
			$return['message'] = '"' . ipm_htmlentities($filename) . '" already seems to exist or PHP doesn\'t have write access to its location. Please make sure this file has been created with the correct details.';
			$return['success'] = false;
		
			return false;
		}
	}
	
	public function write_config() {
	
		$data 			= array(
								'db_hostname' => $this->session_data('dbhost'),
								'db_username' => $this->session_data('dbusername'),
								'db_password' => $this->session_data('dbpassword'),
								'db_name' => $this->session_data('dbname'),
								'salt' => $this->session_data('site_salt')								
							);
	
		$filename 		= dirname(__FILE__) . '/../../user/settings/config.php';
		$config_data	= $this->create_mysql_config($data);
		
		if ($handle = fopen($filename, 'x')) { 
  
			if (fwrite($handle, $config_data)) {   
				fclose($handle);
				return true;
			}
			else {
				$return['message'] = 'Unable to write "' . ipm_htmlentities($filename) . '". Please make sure you create it yourself.';
				$return['success'] = false;
				fclose($handle);
				return $return;
			}
		}
		else {
			$return['message'] = '"' . ipm_htmlentities($filename) . '" already seems to exist or PHP doesn\'t have write access to its location. Please make sure this file has been created with the correct details.';
			$return['success'] = false;
		
			return false;
		}
	}
	
	private function create_htaccess($array) {
	
	$array['script_path'] = str_replace(' ', '%20', $array['script_path']);
	
	$config_data = '<IfModule mod_rewrite.c>
	RewriteEngine On
	RewriteBase ' .$array['script_path'] . '/

	RewriteCond %{REQUEST_FILENAME} !-f
	RewriteCond %{REQUEST_FILENAME} !-d
	RewriteRule ^(.*)$ index.php?url=$1 [L,QSA]
</IfModule>	';
		return $config_data;
	}
	
	private function create_mysql_config($config_array)  {
	
	$config_data = '<?php
namespace sts;

/*
	STS Config File
	
	You should only need to change: database_hostname, database_username, database_password, database_name and salt
	
	Database Charset should always be UTF8
	Database Table Prefix should always be empty
	SITE ID should always be 1	
*/
if (!defined(__NAMESPACE__ . \'\SEC_DB_LOADED\')) {
	$config =
		array(
			\'database_hostname\'		=> \'' . $config_array['db_hostname'] . '\',
			\'database_username\'		=> \'' . $config_array['db_username'] . '\',
			\'database_password\'		=> \'' . $config_array['db_password'] . '\',
			\'database_name\'			=> \'' . $config_array['db_name'] . '\',
			\'database_type\'			=> \'mysql\',
			\'database_charset\'		=> \'UTF8\',
			\'database_table_prefix\'	=> \'\',
			\'site_id\'					=> 1,
			\'salt\'					=> \'' . $config_array['salt'] . '\'
		);
}
?>';
	return $config_data;
	}
	
	public function storage_path() {
		$root_path = substr(__DIR__, 0, -16);
		$root_path .= 'user/files/';

		if (is_dir($root_path)) {
			return $root_path;
		}
		else {
			return '';
		}
	}

}
	
?>