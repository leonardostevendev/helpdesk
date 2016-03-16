<?php
/**
 * 	Support Tickets
 *	Copyright Dalegroup Pty Ltd 2015
 *	support@dalegroup.net
 *
 *
 * @package     tickets
 * @author      Michael Dale <support@dalegroup.net>
 */

namespace sts;

if (!class_exists('\PDO')) die('This program requires the PHP MySQL PDO database class to run.');

if (version_compare(PHP_VERSION, '5.4.0', '<')) {
	die('This program requires <strong>PHP 5.4</strong> (released in 2012) or higher to run. Your server has PHP ' . PHP_VERSION . '.');
}

/**
 *
 * Folder location variables
 *
 */
define(__NAMESPACE__ . '\CLASSES', 		SYSTEM 	. '/classes');
define(__NAMESPACE__ . '\USER', 		ROOT 	. '/user');
define(__NAMESPACE__ . '\THEMES', 		USER 	. '/themes');
define(__NAMESPACE__ . '\SETTINGS', 	USER 	. '/settings');
define(__NAMESPACE__ . '\FILES', 		USER 	. '/files');
define(__NAMESPACE__ . '\FUNCTIONS', 	SYSTEM 	. '/functions');
define(__NAMESPACE__ . '\LIB', 			SYSTEM 	. '/libraries');

/**
 *
 * Other variables
 *
 */
define(__NAMESPACE__ . '\PLUGIN_NAME', 		'STSCore');

require(FUNCTIONS . '/core.php');

start_timer();

//register_globals off
unregister_globals();

if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
	$_POST = remove_magic_quotes($_POST);
	$_GET = remove_magic_quotes($_GET);
	$_COOKIE = remove_magic_quotes($_COOKIE);
	$_SERVER = remove_magic_quotes($_SERVER);
}

register_shutdown_function(__NAMESPACE__  . '\shutdown');
spl_autoload_register(__NAMESPACE__ . '\class_auto_load');

require(FUNCTIONS . '/html.php');

$error		= &singleton::get(__NAMESPACE__  . '\error');

//CI Test Mode
if (!defined(__NAMESPACE__ . '\CI_MODE')) define(__NAMESPACE__ . '\CI_MODE', false);

/*
if (CI_MODE) {
	echo 'Testing Complete';
	exit(0);
}
*/

try {
	if (!file_exists(SETTINGS . '/config.php')) {
		throw new \Exception('The config file could not be found.');
	}
	else {
		require(SETTINGS . '/config.php');
	}
}
catch (\Exception $e) {
	echo 'The config file "user/settings/config.php" could not be found. Please run the <a href="install/">installer</a>.';
	$error->create(array('type' => 'file_not_found', 'message' => $e->getMessage()));
}

define(__NAMESPACE__ . '\SITE_ID', (int) $config['site_id']);

//start database connection here

if (!isset($config['database_port'])) $config['database_port'] = 3306;

$db = new database($config['database_hostname'], $config['database_name'], $config['database_username'], $config['database_password'], $config['database_type'], $config['database_charset'], $config['database_port']);
$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
$db->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);

//tables
$tables			= &singleton::get(__NAMESPACE__ . '\tables', $config['database_table_prefix']);

//active directory class
include(LIB . '/adldap/adLDAP.php');

//phpmailer class
include(LIB . '/phpmailer/class.phpmailer.php');

//auth
$auth			= &singleton::get(__NAMESPACE__ . '\auth');

try {
	if (!isset($config['salt']) || (empty($config['salt']))) {
		throw new \Exception('The salt config value could not be found.');
	}
	else {
		$auth->set_salt($config['salt']);
	}
}
catch (\Exception $e) {
	$error->create(array('type' => 'security_error', 'message' => $e->getMessage()));
}

//unset database details
unset($config);

define(__NAMESPACE__ . '\SEC_DB_LOADED', true);

//demo mode
if (!defined(__NAMESPACE__ . '\DEMO_MODE')) define(__NAMESPACE__ . '\DEMO_MODE', false);
if (!defined(__NAMESPACE__ . '\DEMO_USER_ID_1')) define(__NAMESPACE__ . '\DEMO_USER_ID_1', 0);

//generate languages
if (!defined(__NAMESPACE__ . '\LANG_GENERATE')) define(__NAMESPACE__ . '\LANG_GENERATE', false);

//saas mode
if (!defined(__NAMESPACE__ . '\SAAS_MODE')) define(__NAMESPACE__ . '\SAAS_MODE', false);
if (!defined(__NAMESPACE__ . '\SAAS_MAX_USERS')) define(__NAMESPACE__ . '\SAAS_MAX_USERS', 0);

//config
$config 			= &singleton::get(__NAMESPACE__ . '\config');

if (file_exists(SETTINGS . '/override.php')) {
	include(SETTINGS . '/override.php');
}

$config->load();

//set timezone
if ($config->get('default_timezone')) {
	date_default_timezone_set($config->get('default_timezone'));
}
else {
	date_default_timezone_set('Australia/Sydney');
}

//setlocale(LC_ALL, 'nl_NL'); ???

//set theme
if ($config->get('default_theme')) {
	define(__NAMESPACE__ . '\CURRENT_THEME', $config->get('default_theme'));
}
else {
	define(__NAMESPACE__ . '\CURRENT_THEME', 'standard');
}

if ($config->get('default_theme_sub')) {
	define(__NAMESPACE__ . '\CURRENT_THEME_SUB', $config->get('default_theme_sub'));
}
else {
	define(__NAMESPACE__ . '\CURRENT_THEME_SUB', 'default');
}

/**
 *
 * The following sets up all the classes that will be required to run the application.
 * The singleton allows classes to be called from within functions and methods without the use of global variables.
 *
 */

//language
$language			= &singleton::get(__NAMESPACE__ . '\language');
$language_words		= &singleton::get(__NAMESPACE__ . '\language_words');

//sessions
$session_array['database'] 		= $db;
$session_array['table_name'] 	= $tables->sessions;

if ($config->get('auth_sso_enabled')) {
	if (isset($_GET['session_id']) && !empty($_GET['session_id'])) {
		$session_array['session_id'] 	= $_GET['session_id'];
	}
}

$session 			= &singleton::get(__NAMESPACE__ . '\sessions', $session_array);

//log
$log				= &singleton::get(__NAMESPACE__ . '\log');

//site
$site				= &singleton::get(__NAMESPACE__ . '\site');

//users
$users				= &singleton::get(__NAMESPACE__ . '\users');

//tickets
$tickets			= &singleton::get(__NAMESPACE__ . '\tickets');

//ticket priorities
$ticket_priorities	= &singleton::get(__NAMESPACE__ . '\ticket_priorities');

//ticket status
$ticket_status		= &singleton::get(__NAMESPACE__ . '\ticket_status');

//ticket departments
$ticket_departments	= &singleton::get(__NAMESPACE__ . '\ticket_departments');

//tickets support
$tickets_support	= &singleton::get(__NAMESPACE__ . '\tickets_support');

//ticket notes
$ticket_notes		= &singleton::get(__NAMESPACE__ . '\ticket_notes');

//queue
$queue				= &singleton::get(__NAMESPACE__ . '\queue');

//cron system
$cron				= &singleton::get(__NAMESPACE__ . '\cron');

//mailer
$mailer				= &singleton::get(__NAMESPACE__ . '\mailer');

//plugins
$plugins			= &singleton::get(__NAMESPACE__ . '\plugins');

//gravatar
$gravatar			= &singleton::get(__NAMESPACE__ . '\gravatar');

//pop_system
$smtp_accounts		= &singleton::get(__NAMESPACE__ . '\smtp_accounts');

//pop_system
$pop_accounts		= &singleton::get(__NAMESPACE__ . '\pop_accounts');

//pop_system
$pop_system			= &singleton::get(__NAMESPACE__ . '\pop_system');

//storage/files support
$storage			= &singleton::get(__NAMESPACE__ . '\storage');

//captcha
$captcha			= &singleton::get(__NAMESPACE__ . '\captcha');

//messages
$messages			= &singleton::get(__NAMESPACE__ . '\messages');

//message_notes
$message_notes		= &singleton::get(__NAMESPACE__ . '\message_notes');

//custom fields
$ticket_custom_fields		= &singleton::get(__NAMESPACE__ . '\ticket_custom_fields');

//ticket files
$ticket_files		= &singleton::get(__NAMESPACE__ . '\ticket_files');

//ticket note files
$ticket_note_files		= &singleton::get(__NAMESPACE__ . '\ticket_note_files');

//pushover
$pushover					= &singleton::get(__NAMESPACE__ . '\pushover');

//users_to_departments
$users_to_departments		= &singleton::get(__NAMESPACE__ . '\users_to_departments');

//user_levels_to_department_notifications
$user_levels_to_department_notifications		= &singleton::get(__NAMESPACE__ . '\user_levels_to_department_notifications');

//db_maintenance
$db_maintenance			= &singleton::get(__NAMESPACE__ . '\db_maintenance');

//db_maintenance
$update					= &singleton::get(__NAMESPACE__ . '\update');

//canned responses
$canned_responses		= &singleton::get(__NAMESPACE__ . '\canned_responses');

//themes
$themes						= &singleton::get(__NAMESPACE__ . '\themes');

//ticket_history
$ticket_history				= &singleton::get(__NAMESPACE__ . '\ticket_history');

/*
	Task based permissions
*/
$permissions				= &singleton::get(__NAMESPACE__ . '\permissions');
$permission_groups			= &singleton::get(__NAMESPACE__ . '\permission_groups');
$permission_groups_to_department_notifications			= &singleton::get(__NAMESPACE__ . '\permission_groups_to_department_notifications');

//custom fields
$table_access_cf			= &singleton::get(__NAMESPACE__ . '\table_access_cf');


$api						= &singleton::get(__NAMESPACE__ . '\api');
$api_keys					= &singleton::get(__NAMESPACE__ . '\api_keys');

$auth_facebook				= &singleton::get(__NAMESPACE__ . '\auth_facebook');

$ticket_views				= &singleton::get(__NAMESPACE__ . '\ticket_views');

$user_api_keys				= &singleton::get(__NAMESPACE__ . '\user_api_keys');

require(FUNCTIONS . '/default_tasks.php');

/**
 *
 * URL Handling Code. Everything is redirected with the .htaccess file to index.php?url=
 *
 */
if (isset($_GET['url'])) {
	$input_url = $_GET['url'];
}
else {
	$input_url = '';
}

$url			= &singleton::get(__NAMESPACE__ . '\url', array('url' => $input_url));

unset($input_url);

$auth->load();

//html purifier
include(LIB . '/htmlpurifier/HTMLPurifier.auto.php');

$htmlpurifier_config = \HTMLPurifier_Config::createDefault();

//default html is set to XHTML 1.1
//$htmlpurifier_config->set('Core.Encoding', 'XHTML 1.1');

//create the class we are going to use.
$purifier	= &singleton::get('HTMLPurifier', $htmlpurifier_config);

/**
 *	Load Theme
 */
$themes->load();

//currently supported: bootstrap3 only
define(__NAMESPACE__ . '\CURRENT_THEME_TYPE', $themes->get_type());

/**
 * Load Plugins
 */
 
$plugins->load();

$plugins->run('loader');

/*
	Auto upgrade SAAS sites
*/

if (SAAS_MODE) {
	$upgrade 		= new upgrade();
	if ($config->get('database_version') != $upgrade->get_db_version()) {
		$upgrade->do_upgrade();
	}
	unset($upgrade);
}
?>