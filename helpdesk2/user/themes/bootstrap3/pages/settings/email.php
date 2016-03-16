<?php
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Email Settings'));
$site->set_config('container-type', 'container');

if (!$auth->can('manage_system_settings')) {
	header('Location: ' . $config->get('address') . '/');
	exit;
}

if (isset($_POST['save'])) {

	if (isset($_POST['default_smtp_account'])) {
		$config->set('default_smtp_account', (int) $_POST['default_smtp_account']);
	}

	$config->set('notification_new_ticket_subject', $_POST['notification_new_ticket_subject']);
	$config->set('notification_new_ticket_body', $_POST['notification_new_ticket_body']);
	$config->set('notification_new_ticket_note_subject', $_POST['notification_new_ticket_note_subject']);
	$config->set('notification_new_ticket_note_body', $_POST['notification_new_ticket_note_body']);

	$config->set('notification_new_department_ticket_subject', $_POST['notification_new_department_ticket_subject']);
	$config->set('notification_new_department_ticket_body', $_POST['notification_new_department_ticket_body']);
	$config->set('notification_new_department_ticket_note_subject', $_POST['notification_new_department_ticket_note_subject']);
	$config->set('notification_new_department_ticket_note_body', $_POST['notification_new_department_ticket_note_body']);

	$config->set('notification_new_user_subject', $_POST['notification_new_user_subject']);
	$config->set('notification_new_user_body', $_POST['notification_new_user_body']);

	$config->set('notification_ticket_assigned_user_subject', $_POST['notification_ticket_assigned_user_subject']);
	$config->set('notification_ticket_assigned_user_body', $_POST['notification_ticket_assigned_user_body']);

	$config->set('notification_password_reset_subject', $_POST['notification_password_reset_subject']);
	$config->set('notification_password_reset_body', $_POST['notification_password_reset_body']);

	$config->set('notification_ticket_date_due_subject', $_POST['notification_ticket_date_due_subject']);
	$config->set('notification_ticket_date_due_body', $_POST['notification_ticket_date_due_body']);

	$plugins->run('submit_settings_email_form');

	$log_array['event_severity'] 		= 'notice';
	$log_array['event_number'] 			= E_USER_NOTICE;
	$log_array['event_description'] 	= 'Email Settings Edited';
	$log_array['event_file'] 			= __FILE__;
	$log_array['event_file_line'] 		= __LINE__;
	$log_array['event_type'] 			= 'edit';
	$log_array['event_source'] 			= 'email_settings';
	$log_array['event_version'] 		= '1';
	$log_array['log_backtrace'] 		= false;

	$log->add($log_array);

	$message = $language->get('Settings Saved');
}

if (isset($_POST['test_cron'])) {
	$cron->run();

	$message = $language->get('Cron has been run.');
}

if (isset($_POST['test'])) {
	if (!empty($_POST['test_email'])) {
		$test_array['subject']			= $config->get('name') . ' - Test Email';
		$test_array['body']				= 'This is a test email.';
		$test_array['to']['to']			= $_POST['test_email'];
		$test_array['to']['to_name']	= 'Test';

		if (!empty($_POST['test_smtp_account_id']) && $_POST['test_smtp_account_id'] != 0) {
			$test_array['smtp_account_id']	= (int) $_POST['test_smtp_account_id'];
		}

		if ($mailer->send_email($test_array)) {
			$message =  $language->get('Test Email Sent');
		}
		else {
			$message =  $language->get('Test Email Failed. View the logs for more details.');
		}
	}
	else {
		$message =  $language->get('Test Email Failed. Email address was empty.');
	}

}

$pop_array 			= $pop_accounts->get(array('get_other_data' => true));
$smtp_array 		= $smtp_accounts->get();


include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<div class="row">

	<form method="post" action="<?php echo safe_output($_SERVER['REQUEST_URI']); ?>">

		<div class="col-md-3">
			<div class="panel panel-default">
				<div class="panel-heading">
					<div class="pull-left">
						<h3><?php echo safe_output($language->get('Email Settings')); ?></h3>
					</div>

					<div class="pull-right">
						<button type="submit" name="save" class="btn btn-success"><?php echo safe_output($language->get('Save')); ?></button>
					</div>
					<div class="clearfix"></div>
				</div>
				<div class="panel-body">
				<?php if (!SAAS_MODE) { ?>
					<p><?php echo safe_output($language->get('Please ensure that you have the cron system setup, otherwise emails will not be sent or downloaded.')); ?></p>

					<div class="pull-right">
						<p>
							<button type="submit" name="test_cron" class="btn btn-primary"><?php echo safe_output($language->get('Run Cron Manually')); ?></button>
							<a href="<?php echo safe_output($config->get('address')); ?>/settings/support/" class="btn btn-info"><?php echo safe_output($language->get('More Info')); ?></a>
						</p>
					</div>
				<?php } ?>
				<div class="clearfix"></div>
				</div>

			</div>

			<?php if (!empty($smtp_array)) { ?>
				<div class="panel panel-default">
					<div class="panel-heading">
						<div class="pull-left">
							<h4><?php echo safe_output($language->get('Test Email')); ?></h4>
						</div>

						<div class="pull-right">
							<button type="submit" name="test" class="btn btn-info"><?php echo safe_output($language->get('Send Test')); ?></button>
						</div>

						<div class="clearfix"></div>
					</div>

					<div class="panel-body">
						<p><?php echo safe_output($language->get('Select SMTP Account')); ?><br />
							<select name="test_smtp_account_id">
								<option value="0"><?php echo safe_output($language->get('Default SMTP Account')); ?></option>
								<?php foreach ($smtp_array as $item) { ?>
									<option value="<?php echo safe_output($item['id']); ?>"><?php echo safe_output($item['name']); ?></option>
								<?php } ?>
							</select>
							</p>

						<p><?php echo safe_output($language->get('Email Address')); ?><br /><input type="text" name="test_email" class="form-control" size="20" value="" /></p>
					</div>

				</div>
			<?php } ?>

		</div>

		<div class="col-md-9">
			<?php if (isset($message)) { ?>
				<div class="alert alert-success">
					<a href="#" class="close" data-dismiss="alert">&times;</a>
					<?php echo html_output($message); ?>
				</div>
			<?php } ?>

			<div class="panel panel-default">

				<a name="smtp_accounts"></a>

				<div class="panel-heading">
					<div class="pull-left">
						<h4><?php echo safe_output($language->get('SMTP Accounts')); ?></h4>
					</div>

					<div class="pull-right">
						<a href="<?php echo safe_output($config->get('address')); ?>/settings/add_smtp_account/" class="btn btn-primary"><?php echo safe_output($language->get('Add')); ?></a>
					</div>

					<div class="clearfix"></div>
				</div>

				<div class="panel-body">
				<?php if (!empty($smtp_array)) { ?>
					<section id="no-more-tables">
						<table class="table table-striped table-bordered">
							<thead>
								<tr>
									<th><?php echo safe_output($language->get('Default SMTP Account')); ?></th>
									<th><?php echo safe_output($language->get('Name')); ?></th>
									<th><?php echo safe_output($language->get('Hostname')); ?></th>
									<th><?php echo safe_output($language->get('Email Address')); ?></th>
									<th><?php echo safe_output($language->get('Enabled')); ?></th>
								</tr>
							</thead>

							<tbody>
								<?php
								$i = 0;
								foreach ($smtp_array as $item) {
								?>
								<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
									<td data-title="Default" class="centre"><input type="radio" name="default_smtp_account" value="<?php echo (int) $item['id']; ?>" <?php if ($config->get('default_smtp_account') == $item['id']) { echo ' checked="checked"'; } ?> /></td>
									<td data-title="Name" class="centre"><a href="<?php echo safe_output($config->get('address')); ?>/settings/edit_smtp_account/<?php echo (int) $item['id']; ?>/"><?php echo safe_output($item['name']); ?></a></td>
									<td data-title="Hostname" class="centre"><?php echo safe_output($item['hostname']); ?></td>
									<td data-title="Email" class="centre"><?php echo safe_output($item['email_address']); ?></td>
									<td data-title="Enabled" class="centre"><?php if ($item['enabled'] == 1) { ?><?php echo safe_output($language->get('Yes')); ?><?php } else { ?><?php echo safe_output($language->get('No')); ?><?php } ?></td>
								</tr>
								<?php $i++; } ?>
							</tbody>
						</table>
					</section>
				<?php } else { ?>
					<?php echo message($language->get('No SMTP Accounts Are Setup.')); ?>
				<?php } ?>
					</div>

				<div class="clearfix"></div>
			</div>

			<div class="panel panel-default">
				<a name="pop3_accounts"></a>

				<div class="panel-heading">
					<div class="pull-left">
						<h4><?php echo safe_output($language->get('POP3 Accounts')); ?></h4>
					</div>

					<div class="pull-right">
						<p><a href="<?php echo safe_output($config->get('address')); ?>/settings/add_pop_account/" class="btn btn-primary"><?php echo safe_output($language->get('Add')); ?></a></p>
					</div>

					<div class="clearfix"></div>
				</div>

				<div class="panel-body">
				<?php if (!empty($pop_array)) { ?>
					<section id="no-more-tables">
						<table class="table table-striped table-bordered">
							<thead>
								<tr>
									<th><?php echo safe_output($language->get('Name')); ?></th>
									<th><?php echo safe_output($language->get('Hostname')); ?></th>
									<th><?php echo safe_output($language->get('Username')); ?></th>
									<th><?php echo safe_output($language->get('Department')); ?></th>
									<th><?php echo safe_output($language->get('Enabled')); ?></th>
								</tr>
							</thead>

							<tbody>
								<?php
								$i = 0;
								foreach ($pop_array as $item) {
								?>
								<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
									<td data-title="Name" class="centre"><a href="<?php echo safe_output($config->get('address')); ?>/settings/edit_pop_account/<?php echo (int) $item['id']; ?>/"><?php echo safe_output($item['name']); ?></a></td>
									<td data-title="Hostname" class="centre"><?php echo safe_output($item['hostname']); ?></td>
									<td data-title="Username" class="centre"><?php echo safe_output($item['username']); ?></td>
									<td data-title="Department" class="centre"><?php echo safe_output($item['department_name']); ?></td>
									<td data-title="Enabled"  class="centre"><?php if ($item['enabled'] == 1) { ?><?php echo safe_output($language->get('Yes')); ?><?php } else { ?><?php echo safe_output($language->get('No')); ?><?php } ?></td>
								</tr>
								<?php $i++; } ?>
							</tbody>
						</table>
					</section>
				<?php } else { ?>
					<?php echo message($language->get('No POP3 Accounts Are Setup.')); ?>
				<?php } ?>
				</div>

				<div class="clear"></div>

			</div>


			<div class="panel panel-default">
				<a name="email_templates"></a>

				<div class="panel-heading">
					<h4><?php echo safe_output($language->get('Email Notification Templates')); ?></h4>
				</div>

				<div class="panel-body">
				<h4><?php echo safe_output($language->get('New Ticket')); ?></h4>

				<p><?php echo safe_output($language->get('Subject')); ?><br /><input class="form-control" type="text" name="notification_new_ticket_subject" size="50" value="<?php echo safe_output($config->get('notification_new_ticket_subject')); ?>" /></p>

				<div id="no_underline">
					<p><?php echo safe_output($language->get('Body')); ?><br />
						<textarea class="wysiwyg_enabled" name="notification_new_ticket_body" cols="80" rows="12">
							<?php echo safe_output($config->get('notification_new_ticket_body')); ?>
						</textarea>
					</p>
				</div>

				</div>
				<div class="panel-body">
				<h4><?php echo safe_output($language->get('New Ticket Reply')); ?></h4>

				<p><?php echo safe_output($language->get('Subject')); ?><br /><input class="form-control" type="text" name="notification_new_ticket_note_subject" size="50" value="<?php echo safe_output($config->get('notification_new_ticket_note_subject')); ?>" /></p>

				<div id="no_underline">
					<p><?php echo safe_output($language->get('Body')); ?><br />
						<textarea class="wysiwyg_enabled" name="notification_new_ticket_note_body" cols="80" rows="12">
							<?php echo safe_output($config->get('notification_new_ticket_note_body')); ?>
						</textarea>
					</p>
				</div>

				</div>
				<div class="panel-body">

				<h4><?php echo safe_output($language->get('New Department Ticket')); ?></h4>

				<p><?php echo safe_output($language->get('Subject')); ?><br /><input class="form-control" type="text" name="notification_new_department_ticket_subject" size="50" value="<?php echo safe_output($config->get('notification_new_department_ticket_subject')); ?>" /></p>

				<div id="no_underline">
					<p><?php echo safe_output($language->get('Body')); ?><br />
						<textarea class="wysiwyg_enabled" name="notification_new_department_ticket_body" cols="80" rows="12">
							<?php echo safe_output($config->get('notification_new_department_ticket_body')); ?>
						</textarea>
					</p>
				</div>

				</div>
				<div class="panel-body">

				<h4><?php echo safe_output($language->get('New Department Ticket Reply')); ?></h4>

				<p><?php echo safe_output($language->get('Subject')); ?><br /><input class="form-control" type="text" name="notification_new_department_ticket_note_subject" size="50" value="<?php echo safe_output($config->get('notification_new_department_ticket_note_subject')); ?>" /></p>

				<div id="no_underline">
					<p><?php echo safe_output($language->get('Body')); ?><br />
						<textarea class="wysiwyg_enabled" name="notification_new_department_ticket_note_body" cols="80" rows="12">
							<?php echo safe_output($config->get('notification_new_department_ticket_note_body')); ?>
						</textarea>
					</p>
				</div>

					</div>
				<div class="panel-body">
				<h4><?php echo safe_output($language->get('Assigned User To Ticket')); ?></h4>

				<p><?php echo safe_output($language->get('Subject')); ?><br /><input class="form-control" type="text" name="notification_ticket_assigned_user_subject" size="50" value="<?php echo safe_output($config->get('notification_ticket_assigned_user_subject')); ?>" /></p>

				<div id="no_underline">
					<p><?php echo safe_output($language->get('Body')); ?><br />
						<textarea class="wysiwyg_enabled" name="notification_ticket_assigned_user_body" cols="80" rows="12">
							<?php echo safe_output($config->get('notification_ticket_assigned_user_body')); ?>
						</textarea>
					</p>
				</div>

				</div>
				<div class="panel-body">

				<h4><?php echo safe_output($language->get('New User (Welcome Email)')); ?></h4>

				<p><?php echo safe_output($language->get('Subject')); ?><br /><input class="form-control" type="text" name="notification_new_user_subject" size="50" value="<?php echo safe_output($config->get('notification_new_user_subject')); ?>" /></p>

				<div id="no_underline">
					<p><?php echo safe_output($language->get('Body')); ?><br />
						<textarea class="wysiwyg_enabled" name="notification_new_user_body" cols="80" rows="12">
							<?php echo safe_output($config->get('notification_new_user_body')); ?>
						</textarea>
					</p>
				</div>

				</div>
				<div class="panel-body">

				<h4><?php echo safe_output($language->get('Password Reset')); ?></h4>

				<p><?php echo safe_output($language->get('Subject')); ?><br /><input class="form-control" type="text" name="notification_password_reset_subject" size="50" value="<?php echo safe_output($config->get('notification_password_reset_subject')); ?>" /></p>

				<div id="no_underline">
					<p><?php echo safe_output($language->get('Body')); ?><br />
						<textarea class="wysiwyg_enabled" name="notification_password_reset_body" cols="80" rows="12">
							<?php echo safe_output($config->get('notification_password_reset_body')); ?>
						</textarea>
					</p>
				</div>

				</div>
				<div class="panel-body">

				<h4><?php echo safe_output($language->get('Ticket Date Due')); ?></h4>

				<p><?php echo safe_output($language->get('Subject')); ?><br /><input class="form-control" type="text" name="notification_ticket_date_due_subject" size="50" value="<?php echo safe_output($config->get('notification_ticket_date_due_subject')); ?>" /></p>

				<div id="no_underline">
					<p><?php echo safe_output($language->get('Body')); ?><br />
						<textarea class="wysiwyg_enabled" name="notification_ticket_date_due_body" cols="80" rows="12">
							<?php echo safe_output($config->get('notification_ticket_date_due_body')); ?>
						</textarea>
					</p>
				</div>
				<?php $plugins->run('view_settings_email_notifications_content_finish'); ?>
				</div>

			</div>

			<?php $plugins->run('view_settings_email_container_finish'); ?>
		</div>
	</form>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>
