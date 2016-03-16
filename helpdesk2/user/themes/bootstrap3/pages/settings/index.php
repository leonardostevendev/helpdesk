<?php
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Settings'));
$site->set_config('container-type', 'container');

if (!$auth->can('manage_system_settings')) {
	header('Location: ' . $config->get('address') . '/');
	exit;
}

if (isset($_POST['save'])) {

	if (!DEMO_MODE) {
		$config->set('name', $_POST['name']);
		
		if (!SAAS_MODE) {
			$config->set('domain', $_POST['domain']);
			$config->set('script_path', $_POST['script_path']);
			$config->set('port_number', (int) $_POST['port_number']);
			$config->set('https', $_POST['https'] ? 1 : 0);
			$config->set('storage_enabled', $_POST['storage_enabled'] ? 1 : 0);
			$config->set('storage_path', $_POST['storage_path']);
				
			$config->set('default_theme', $_POST['default_theme']);
			
			if (isset($_POST['default_sub_theme'])) {
				$config->set('default_theme_sub', $_POST['default_sub_theme']);
			}
		}
		
		$config->set('display_dashboard', $_POST['display_dashboard'] ? 1 : 0);
		
		$config->set('login_message', $_POST['login_message']);

		$config->set('gravatar_enabled', $_POST['gravatar_enabled'] ? 1 : 0);
		//$config->set('html_enabled', $_POST['html_enabled'] ? 1 : 0);
		$config->set('captcha_enabled', $_POST['captcha_enabled'] ? 1 : 0);
		
		$config->set('default_timezone', $_POST['default_timezone']);
		$config->set('default_language', $_POST['default_language']);
		
		$config->set('pushover_enabled', $_POST['pushover_enabled'] ? 1 : 0);
		$config->set('pushover_user_enabled', $_POST['pushover_user_enabled'] ? 1 : 0);
		$config->set('pushover_token', $_POST['pushover_token']);
		
		if (isset($_POST['pushover_user_id']) && !empty($_POST['pushover_user_id'])) {
			$current_pushover_users = $config->get('pushover_notify_users');
			if (!in_array($_POST['pushover_user_id'], $current_pushover_users)) {
				$current_pushover_users[] = (int) $_POST['pushover_user_id'];
				$config->set('pushover_notify_users', $current_pushover_users);
			}
		}
		
		$plugins->run('submit_settings_general_form');

	}
	
	$log_array['event_severity'] = 'notice';
	$log_array['event_number'] = E_USER_NOTICE;
	$log_array['event_description'] = 'Settings Edited';
	$log_array['event_file'] = __FILE__;
	$log_array['event_file_line'] = __LINE__;
	$log_array['event_type'] = 'edit';
	$log_array['event_source'] = 'settings';
	$log_array['event_version'] = '1';
	$log_array['log_backtrace'] = false;	
			
	$log->add($log_array);
	
	$message = $language->get('Settings Saved');
}

$upgrade 		= new upgrade();
$langs 			= $language->get_system_languages();
$user_langs	 	= $language->get_user_languages();
$timezones		= get_timezones();
$pushover_users = $config->get('pushover_notify_users'); 
$themes_array 	= $themes->get();

include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<script type="text/javascript" src="<?php echo $config->get('address'); ?>/system/js/settings.js"></script>

<div class="row">

	<form method="post" action="<?php echo safe_output($_SERVER['REQUEST_URI']); ?>">

		<div class="col-md-3">
			<div class="panel panel-default">
				<div class="panel-heading">
                    <div class="pull-left">
                        <h3><?php echo safe_output($language->get('Settings')); ?></h3>
                    </div>
                    <div class="pull-right">
                        <button type="submit" name="save" class="btn btn-success"><?php echo safe_output($language->get('Save')); ?></button>
                    </div>
				<div class="clearfix"></div>
				</div>
				
				
                <div class="panel-body">
				<?php if (!SAAS_MODE) { ?>

					<label class="left-result"><?php echo safe_output($language->get('Program Version')); ?></label>
					<p class="right-result">
						<?php echo safe_output($config->get('program_version')); ?>
					</p>
					<div class="clearfix"></div>

					<label class="left-result"><?php echo safe_output($language->get('Database Version')); ?></label>
					<p class="right-result">
						<?php echo safe_output($config->get('database_version')); ?>
					</p>
					<div class="clearfix"></div>
				    <br>
					<div class="pull-right">
						<a href="<?php echo $config->get('address'); ?>/update/" class="btn btn-primary"><?php echo safe_output($language->get('Check for updates')); ?></a>
					</div>
					<div class="clearfix"></div>
				<?php } ?>
                </div>
			</div>
		</div>
		<div class="col-md-9">
			<?php if (!SAAS_MODE && (($config->get('database_version') !== $upgrade->get_db_version()) || ($config->get('program_version') !== $upgrade->get_program_version()))) { ?>
				<div class="alert alert-warning">
					<a href="#" class="close" data-dismiss="alert">&times;</a>
					<strong><?php echo $language->get('Warning'); ?>:</strong>
					<?php echo html_output($language->get('The database needs upgrading.')); ?>
					<strong><a href="<?php echo safe_output($config->get('address')); ?>/upgrade/"><?php echo safe_output($language->get('Upgrade')); ?></a></strong>
				</div>
			<?php } ?>
			
			<?php if (!SAAS_MODE && $upgrade->update_available()) { ?>
				<div class="alert alert-warning">
					<a href="#" class="close" data-dismiss="alert">&times;</a>
					<?php echo html_output($language->get('There is a software update available.')); ?>
					<strong><a href="<?php echo safe_output($config->get('address')); ?>/update/info/"><?php echo safe_output($language->get('More Information')); ?></a></strong>
				</div>
			<?php } ?>
			
			<?php if (DEMO_MODE) { ?>
				<div class="alert alert-warning">
					<a href="#" class="close" data-dismiss="alert">&times;</a>
					<strong><?php echo $language->get('Demo Mode'); ?>:</strong>
					<?php echo $language->get('Changing the settings on this page is disabled.'); ?>
				</div>
			<?php } ?>

			<?php if (isset($message)) { ?>
				<div class="alert alert-success">
					<a href="#" class="close" data-dismiss="alert">&times;</a>
					<?php echo html_output($message); ?>
				</div>
			<?php } ?>
			
			<div class="panel panel-default">		
                
                <div class="panel-heading"><h4><?php echo safe_output($language->get('Site Settings')); ?></h4></div>
				
				<div class="panel-body">								
				
					<p><?php echo safe_output($language->get('Site Name')); ?>
					<a href="#" class="popover-item" 
					data-html="true"
					data-content="<?php echo safe_output($language->get('This is the name of this website, it is used in the page titles and is the default from name in email notifications. You can change this any time.')); ?>"
					data-title="<?php echo safe_output($language->get('Site Name')); ?>">
					<span class="glyphicon glyphicon-question-sign"></span>
					</a>					
					<br /><input type="text" name="name" class="form-control" value="<?php echo safe_output($config->get('name')); ?>" /></p>
	
					<?php if (!SAAS_MODE) { ?>
						<p><?php echo safe_output($language->get('Domain Name (e.g example.com)')); ?>
						<a href="#" class="popover-item" 
						data-html="true"
						data-content="<?php echo safe_output($language->get('This is the domain name of this website. It is used for URLs, Cookies, Redirections and more. Please ensure that you use a full domain name and not just a host name or IP address. If changing this value ensure that the new domain name is ready first.')); ?>"
						data-title="<?php echo safe_output($language->get('Domain Name')); ?>">
						<span class="glyphicon glyphicon-question-sign"></span>
						</a>
						<br /><input class="form-control" type="text" size="30" name="domain" value="<?php echo safe_output($config->get('domain')); ?>" />
						</p>
						<p><?php echo safe_output($language->get('Script Path (e.g /sts)')); ?>
						<a href="#" class="popover-item" 
						data-html="true"
						data-content="<?php echo safe_output($language->get('This is the folder that this website is located in on your web hosting. For sub-domains you must leave this field blank. Please do not include a / at the end. If moving folders please change this value first and then move the folder. Please ensure you update the .htaccess file in the root folder.')); ?>"
						data-title="<?php echo safe_output($language->get('Script Path')); ?>">
						<span class="glyphicon glyphicon-question-sign"></span>
						</a>
						<br /><input class="form-control" type="text" name="script_path" value="<?php echo safe_output($config->get('script_path')); ?>" />
						</p>
						<p><?php echo safe_output($language->get('Port Number (80 for HTTP and 443 for Secure HTTP are the norm)')); ?>
						<a href="#" class="popover-item" 
						data-html="true"
						data-content="<?php echo safe_output($language->get('This is port that your web server is running on. It would be rare that a value other than 80 or 443 is used. Use caution when changing this value.')); ?>"
						data-title="<?php echo safe_output($language->get('Port Number')); ?>">
						<span class="glyphicon glyphicon-question-sign"></span>
						</a>
						<br /><input class="form-control" type="text" name="port_number" value="<?php echo (int)($config->get('port_number')); ?>" /></p>
						
						<p><?php echo safe_output($language->get('Secure HTTP (recommended, requires SSL certificate)')); ?>
						<a href="#" class="popover-item" 
						data-html="true"
						data-content="<?php echo safe_output($language->get('Secure HTTP means your data is encrypted when sent to the web browser, increasing security. You must have a valid SSL certificate installed and ready on your web server before enabling this setting. If using Secure HTTP you should also set the port to 443 at the same time.')); ?>"
						data-title="<?php echo safe_output($language->get('Secure HTTP')); ?>">
						<span class="glyphicon glyphicon-question-sign"></span>
						</a>
						<br />
						<select name="https">
							<option value="0"><?php echo safe_output($language->get('No')); ?></option>
							<option value="1"<?php if ($config->get('https') == 1) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('Yes')); ?></option>
						</select></p>
					<?php } ?>
					
					<p><?php echo safe_output($language->get('Default Timezone')); ?>
					<a href="#" class="popover-item" 
						data-html="true"
						data-content="<?php echo safe_output($language->get('This is timezone you want to use for your install. If you change this we recommend resetting your cron schedule. This can be done in Settings -> Email, More Info, Reset Cron.')); ?>"
						data-title="<?php echo safe_output($language->get('Default Timezone')); ?>">
						<span class="glyphicon glyphicon-question-sign"></span>
					</a>
					<br />
					<select name="default_timezone">
						<?php foreach ($timezones as $timezone) { ?>
						<option value="<?php echo safe_output($timezone); ?>"<?php if ($config->get('default_timezone') == $timezone) { echo ' selected="selected"'; } ?>><?php echo safe_output($timezone); ?></option>
						<?php } ?>
					</select>
					</p>
					
					<p><?php echo safe_output($language->get('Default Language')); ?><br />
					<select name="default_language">
						<optgroup label="System Languages">
							<?php foreach ($langs as $lang) { ?>
							<option value="<?php echo safe_output($lang['name']); ?>"<?php if ($config->get('default_language') == $lang['name']) { echo ' selected="selected"'; } ?>><?php echo safe_output($lang['nice_name']); ?></option>
							<?php } ?>
						</optgroup>
						<optgroup label="User Languages">
							<?php foreach ($user_langs as $lang) { ?>
							<option value="<?php echo safe_output($lang['name']); ?>"<?php if ($config->get('default_language') == $lang['name']) { echo ' selected="selected"'; } ?>><?php echo safe_output($lang['nice_name']); ?></option>
							<?php } ?>
						</optgroup>
					</select>
					</p>
					
					<?php if (!SAAS_MODE) { ?>
		
						<p><?php echo safe_output($language->get('Default Theme')); ?><br />
						<select name="default_theme" id="default_theme" data-sub-theme="<?php echo safe_output($config->get('default_theme_sub')); ?>">
							<?php foreach ($themes_array as $theme) { ?>
								<option value="<?php echo safe_output($theme['file_name']); ?>"<?php if ($config->get('default_theme') == $theme['file_name']) { echo ' selected="selected"'; } ?>><?php echo safe_output($theme['name']); ?></option>
							<?php } ?>
						</select>
						</p>
						
						<div id="default_sub_theme_area"></div>
					<?php } ?>
				</div>
				<div class="clearfix"></div>	
				
			</div>

			<div class="panel panel-default">
                
                <div class="panel-heading"><h4><?php echo safe_output($language->get('Site Options')); ?></h4></div>
				
				<div class="panel-body">							

					<p><?php echo safe_output($language->get('Display Dashboard')); ?><br />
					<select name="display_dashboard">
						<option value="0"><?php echo safe_output($language->get('No')); ?></option>
						<option value="1"<?php if ($config->get('display_dashboard') == 1) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('Yes')); ?></option>
					</select></p>
				
					<p><?php echo safe_output($language->get('Login Message')); ?><br /><input type="text" class="form-control" name="login_message" size="50" value="<?php echo safe_output($config->get('login_message')); ?>" /></p>
					
					<p><?php echo safe_output($language->get('Anti-Spam Captcha Enabled (helps protect the guest portal and user registration from bots)')); ?><br />
					<select name="captcha_enabled">
						<option value="0"><?php echo safe_output($language->get('No')); ?></option>
						<option value="1"<?php if ($config->get('captcha_enabled') == 1) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('Yes')); ?></option>
					</select></p>
					
					<?php if (!SAAS_MODE) { ?>
						<p><?php echo safe_output($language->get('File Storage Enabled (for file attachments)')); ?><br />
						<select name="storage_enabled">
							<option value="0"><?php echo safe_output($language->get('No')); ?></option>
							<option value="1"<?php if ($config->get('storage_enabled') == 1) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('Yes')); ?></option>
						</select></p>
					
						<p><?php echo safe_output($language->get('File Storage Path (must be outside the public web folder e.g./home/sts/files/ or C:\sts\files\)')); ?><br /><input type="text" class="form-control" name="storage_path" size="50" value="<?php echo safe_output($config->get('storage_path')); ?>" /></p>
					<?php } ?>
					
					<div class="clearfix"></div>
				</div>
				<div class="clearfix"></div>

			</div>
			
			<div class="panel panel-default">
                
                <div class="panel-heading"><h4><?php echo safe_output($language->get('External Services')); ?></h4></div>
				
				<div class="panel-body">								
					
			
					<p><?php echo safe_output($language->get('Gravatar Enabled')); ?><br />
					<select name="gravatar_enabled">
						<option value="0"><?php echo safe_output($language->get('No')); ?></option>
						<option value="1"<?php if ($config->get('gravatar_enabled') == 1) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('Yes')); ?></option>
					</select></p>	
					
					<p><?php echo safe_output($language->get('Pushover Enabled')); ?><br />
					<select id="pushover_enabled" name="pushover_enabled">
						<option value="0"><?php echo safe_output($language->get('No')); ?></option>
						<option value="1"<?php if ($config->get('pushover_enabled') == 1) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('Yes')); ?></option>
					</select></p>

					<div id="pushover_extra">
						<p><?php echo safe_output($language->get('Pushover for all Users')); ?><br />
						<select name="pushover_user_enabled">
							<option value="0"><?php echo safe_output($language->get('No')); ?></option>
							<option value="1"<?php if ($config->get('pushover_user_enabled') == 1) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('Yes')); ?></option>
						</select></p>				
						
						<p><?php echo safe_output($language->get('Pushover Application Token')); ?><br /><input class="form-control" type="text" name="pushover_token" size="35" value="<?php echo safe_output($config->get('pushover_token')); ?>" /></p>
												
						<p><?php echo safe_output($language->get('Below is a list of the users who will receive pushover notifications whenever a new ticket or ticket note is added.')); ?></p>
											
						<div id="no_underline">
						<?php if (!empty($pushover_users)) { ?>
							<?php foreach ($pushover_users as $pushover_user_id) { ?>
								<?php $user = $users->get(array('id' => $pushover_user_id)); ?>
								<?php if (!empty($user)) { ?>
									<div class="current_pushover_users_field" id="pushover_existing-<?php echo (int) $user[0]['id']; ?>">
										<p><input type="text" size="25" name="pushover_existing-<?php echo (int) $user[0]['id']; ?>" value="<?php echo safe_output($user[0]['name']); ?>" disabled="disabled" /> <a href="#pushover_users" id="delete_existing_pushover_user"><img src="<?php echo $config->get('address'); ?>/user/themes/<?php echo safe_output(CURRENT_THEME); ?>/images/icons/delete.png" alt="Delete Pushover User" /></a></p>
									</div>
								<?php } ?>
							<?php } ?>
						<?php } ?>
						</div>
					
						<?php $users_array 	= $users->get(); ?>
							
						<p><?php echo safe_output($language->get('Add User')); ?><br />
						<select name="pushover_user_id">
							<option value=""></option>
							<?php foreach ($users_array as $user) { ?>
							<option value="<?php echo (int) $user['id']; ?>"><?php echo safe_output($user['name']); ?></option>
							<?php } ?>
						</select></p>	

					</div>
				</div>
				
				<div class="clearfix"></div>

			</div>
			
			<?php $plugins->run('view_settings_general_container_finish'); ?>			
		</div>
	
	</form>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>