<?php
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Authentication'));
$site->set_config('container-type', 'container');

if (!$auth->can('manage_system_settings')) {
	header('Location: ' . $config->get('address') . '/');
	exit;
}

if (isset($_POST['save'])) {

	$config->set('lockout_enabled', $_POST['lockout_enabled'] ? 1 : 0);

	if (!SAAS_MODE) {
		$config->set('ad_server', $_POST['ad_server']);
		$config->set('ad_account_suffix', $_POST['ad_account_suffix']);
		$config->set('ad_base_dn', $_POST['ad_base_dn']);
		$config->set('ad_create_accounts', $_POST['ad_create_accounts'] ? 1 : 0);
		$config->set('ad_enabled', $_POST['ad_enabled'] ? 1 : 0);

		$config->set('ldap_server', $_POST['ldap_server']);
		$config->set('ldap_base_dn', $_POST['ldap_base_dn']);
		$config->set('ldap_create_accounts', $_POST['ldap_create_accounts'] ? 1 : 0);
		$config->set('ldap_enabled', $_POST['ldap_enabled'] ? 1 : 0);

		$config->set('facebook_enabled', $_POST['facebook_enabled'] ? 1 : 0);
		$config->set('auth_facebook_create_accounts', $_POST['auth_facebook_create_accounts'] ? 1 : 0);
		$config->set('facebook_app_id', $_POST['facebook_app_id']);
		$config->set('facebook_app_secret', $_POST['facebook_app_secret']);

		$config->set('auth_sso_enabled', $_POST['auth_sso_enabled'] ? 1 : 0);

	}

	$config->set('registration_enabled', $_POST['registration_enabled'] ? 1 : 0);

	$config->set('auth_json_url', $_POST['auth_json_url']);
	$config->set('auth_json_site_id', (int) $_POST['auth_json_site_id']);
	$config->set('auth_json_key', $_POST['auth_json_key']);
	$config->set('auth_json_create_accounts', $_POST['auth_json_create_accounts'] ? 1 : 0);
	$config->set('auth_json_enabled', $_POST['auth_json_enabled'] ? 1 : 0);

	$config->set('session_life_time', (int) $_POST['session_life_time']);

	$plugins->run('submit_settings_authentication_form');

	$log_array['event_severity'] = 'notice';
	$log_array['event_number'] = E_USER_NOTICE;
	$log_array['event_description'] = 'Authentication Settings Edited';
	$log_array['event_file'] = __FILE__;
	$log_array['event_file_line'] = __LINE__;
	$log_array['event_type'] = 'edit';
	$log_array['event_source'] = 'authentication_settings';
	$log_array['event_version'] = '1';
	$log_array['log_backtrace'] = false;

	$log->add($log_array);

	$message = $language->get('Settings Saved');
}


include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<div class="row">
	<form method="post" action="<?php echo safe_output($_SERVER['REQUEST_URI']); ?>">

		<div class="col-md-3">
			<div class="panel panel-default">
				<div class="panel-heading">

					<div class="pull-right">
						<button type="submit" name="save" class="btn btn-success"><?php echo safe_output($language->get('Save')); ?></button>
					</div>

					<div class="pull-left">
						<h3><?php echo safe_output($language->get('Authentication')); ?></h3>
					</div>

					<div class="clearfix"></div>

				</div>
			</div>
		</div>

		<div class="col-md-9">

			<?php if (!SAAS_MODE && !extension_loaded('ldap')) { ?>
				<div class="alert alert-danger">
					<a href="#" class="close" data-dismiss="alert">&times;</a>
					<?php echo html_output($language->get('Note: The PHP LDAP extension is required for Active Directory and LDAP.')); ?>
				</div>
			<?php } ?>


			<?php if (isset($message)) { ?>
				<div class="alert alert-success">
					<a href="#" class="close" data-dismiss="alert">&times;</a>
					<?php echo html_output($message); ?>
				</div>
			<?php } ?>

			<div class="panel panel-default">
				<div class="panel-heading">
					<h4><?php echo safe_output($language->get('Authentication Settings')); ?></h4>
				</div>

				<div class="panel-body">

					<p><?php echo safe_output($language->get('Account Registration Enabled')); ?><br />
					<select name="registration_enabled">
						<option value="0"><?php echo safe_output($language->get('No')); ?></option>
						<option value="1"<?php if ($config->get('registration_enabled') == 1) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('Yes')); ?></option>
					</select></p>

					<p><?php echo safe_output($language->get('Account Protection (user accounts are locked for 15 minutes after 5 failed logins)')); ?><br />
					<select name="lockout_enabled">
						<option value="0"><?php echo safe_output($language->get('No')); ?></option>
						<option value="1"<?php if ($config->get('lockout_enabled') == 1) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('Yes')); ?></option>
					</select></p>

					<p><?php echo safe_output($language->get('Session Expiration Timeout')); ?><br />
					<select name="session_life_time">
						<option value="3600"><?php echo safe_output($language->get('1 Hour')); ?></option>
						<option value="7200"<?php if ($config->get('session_life_time') == 7200) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('2 Hours')); ?></option>
						<option value="14400"<?php if ($config->get('session_life_time') == 14400) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('4 Hours')); ?></option>
						<option value="21600"<?php if ($config->get('session_life_time') == 21600) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('6 Hours')); ?></option>
					</select></p>

					<?php if (!SAAS_MODE) { ?>
						<p><?php echo safe_output($language->get('Single Sign On (SSO) Support')); ?><br />
						<select name="auth_sso_enabled">
							<option value="0"><?php echo safe_output($language->get('No')); ?></option>
							<option value="1"<?php if ($config->get('auth_sso_enabled') == 1) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('Yes')); ?></option>
						</select></p>
					<?php } ?>

				</div>
				<div class="clearfix"></div>

			</div>

			<?php if (!SAAS_MODE) { ?>
				<div class="panel panel-default">
					<div class="panel-heading"><h3><?php echo safe_output($language->get('Active Directory')); ?></h3></div>

					<div class="panel-body">

						<p><?php echo safe_output($language->get('Enabled')); ?><br />
						<select name="ad_enabled">
							<option value="0"><?php echo safe_output($language->get('No')); ?></option>
							<option value="1"<?php if ($config->get('ad_enabled') == 1) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('Yes')); ?></option>
						</select></p>
						<p><?php echo safe_output($language->get('Server (e.g. dc.example.local or 192.168.1.1)')); ?><br /><input class="form-control" type="text" name="ad_server" size="30" value="<?php echo safe_output($config->get('ad_server')); ?>" /></p>
						<p><?php echo safe_output($language->get('Account Suffix (e.g. @example.local)')); ?><br /><input class="form-control" type="text" name="ad_account_suffix" size="30" value="<?php echo safe_output($config->get('ad_account_suffix')); ?>" /></p>
						<p><?php echo safe_output($language->get('Base DN (e.g. DC=example,DC=local)')); ?><br /><input class="form-control" type="text" name="ad_base_dn" size="50" value="<?php echo safe_output($config->get('ad_base_dn')); ?>" /></p>

						<p><?php echo safe_output($language->get('Create user on valid login')); ?><br />
						<select name="ad_create_accounts">
							<option value="0"><?php echo safe_output($language->get('No')); ?></option>
							<option value="1"<?php if ($config->get('ad_create_accounts') == 1) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('Yes')); ?></option>
						</select></p>

						<div class="clearfix"></div>
					</div>
					<div class="clearfix"></div>

				</div>

				<div class="panel panel-default">
					<div class="panel-heading"><h3><?php echo safe_output($language->get('LDAP')); ?></h3></div>
					<div class="panel-body">
						<p><?php echo safe_output($language->get('Enabled')); ?><br />
						<select name="ldap_enabled">
							<option value="0"><?php echo safe_output($language->get('No')); ?></option>
							<option value="1"<?php if ($config->get('ldap_enabled') == 1) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('Yes')); ?></option>
						</select></p>
						<p><?php echo safe_output($language->get('Server (e.g. dc.example.local or 192.168.1.1)')); ?><br /><input class="form-control" type="text" name="ldap_server" size="30" value="<?php echo safe_output($config->get('ldap_server')); ?>" /></p>
						<p><?php echo safe_output($language->get('Base DN (e.g. OU=sydney,DC=example,DC=local)')); ?><br /><input class="form-control" type="text" name="ldap_base_dn" size="50" value="<?php echo safe_output($config->get('ldap_base_dn')); ?>" /></p>

						<p><?php echo safe_output($language->get('Create user on valid login')); ?><br />
						<select name="ldap_create_accounts">
							<option value="0"><?php echo safe_output($language->get('No')); ?></option>
							<option value="1"<?php if ($config->get('ldap_create_accounts') == 1) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('Yes')); ?></option>
						</select></p>

					</div>
					<div class="clearfix"></div>
				</div>

			<?php } ?>

			<div class="panel panel-default">

				<div class="panel-heading"><h3><?php echo safe_output($language->get('JSON')); ?></h3></div>

				<div class="panel-body">
					<p><?php echo safe_output($language->get('Enabled')); ?><br />
					<select name="auth_json_enabled">
						<option value="0"><?php echo safe_output($language->get('No')); ?></option>
						<option value="1"<?php if ($config->get('auth_json_enabled') == 1) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('Yes')); ?></option>
					</select></p>
					<p><?php echo safe_output($language->get('URL (SSL Recommended)')); ?><br /><input class="form-control" type="text" class="input-xlarge" name="auth_json_url" size="30" value="<?php echo safe_output($config->get('auth_json_url')); ?>" /></p>
					<p><?php echo safe_output($language->get('Site ID')); ?><br /><input type="text" class="form-control" name="auth_json_site_id" class="input-small" size="30" value="<?php echo safe_output($config->get('auth_json_site_id')); ?>" /></p>

					<p><?php echo safe_output($language->get('Security Key')); ?><br /><input class="form-control" type="text" class="input-xlarge" name="auth_json_key" size="50" value="<?php echo safe_output($config->get('auth_json_key')); ?>" /></p>

					<p><?php echo safe_output($language->get('Create user on valid login')); ?><br />
					<select name="auth_json_create_accounts">
						<option value="0"><?php echo safe_output($language->get('No')); ?></option>
						<option value="1"<?php if ($config->get('auth_json_create_accounts') == 1) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('Yes')); ?></option>
					</select></p>
				</div>
				<div class="clearfix"></div>

			</div>

			<?php if (!SAAS_MODE) { ?>
				<div class="panel panel-default">

					<div class="panel-heading"><h3><?php echo safe_output($language->get('Facebook')); ?></h3></div>

					<div class="panel-body">

						<p><?php echo safe_output($language->get('Enabled')); ?><br />
						<select name="facebook_enabled">
							<option value="0"><?php echo safe_output($language->get('No')); ?></option>
							<option value="1"<?php if ($config->get('facebook_enabled') == 1) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('Yes')); ?></option>
						</select></p>
						<p><?php echo safe_output($language->get('App ID')); ?><br /><input type="text" class="form-control" name="facebook_app_id" class="input-small" size="30" value="<?php echo safe_output($config->get('facebook_app_id')); ?>" /></p>

						<p><?php echo safe_output($language->get('App Secret')); ?><br /><input class="form-control" type="text" class="input-xlarge" name="facebook_app_secret" size="50" value="<?php echo safe_output($config->get('facebook_app_secret')); ?>" /></p>

						<p><?php echo safe_output($language->get('Create user on valid login')); ?><br />
						<select name="auth_facebook_create_accounts">
							<option value="0"><?php echo safe_output($language->get('No')); ?></option>
							<option value="1"<?php if ($config->get('auth_facebook_create_accounts') == 1) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('Yes')); ?></option>
						</select></p>
					</div>
					<div class="clearfix"></div>

				</div>
			<?php } ?>

			<?php
				$plugins->run('view_settings_authentication_content_finish');
			?>

		</div>

		<?php
			$plugins->run('view_settings_authentication_form_finish');
		?>
	</form>
</div>

<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>
