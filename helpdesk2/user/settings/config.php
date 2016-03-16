<?php
namespace sts;

/*
	STS Config File
	
	You should only need to change: database_hostname, database_username, database_password, database_name and salt
	
	Database Charset should always be UTF8
	Database Table Prefix should always be empty
	SITE ID should always be 1	
*/
if (!defined(__NAMESPACE__ . '\SEC_DB_LOADED')) {
	$config =
		array(
			'database_hostname'		=> 'localhost',
			'database_username'		=> 'helpdesk2',
			'database_password'		=> 'wK6hh83FRhFFefVn',
			'database_name'			=> 'helpdesk2',
			'database_type'			=> 'mysql',
			'database_charset'		=> 'UTF8',
			'database_table_prefix'	=> '',
			'site_id'					=> 1,
			'salt'					=> '5g6go79x5l5cpi1e6fadJBZe36gRQ09K'
		);
}
?>