<?php
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Add User'));
$site->set_config('container-type', 'container');

if (!$auth->can('manage_users')) {
	header('Location: ' . $config->get('address') . '/');
	exit;
}

$create_user = true;

if (isset($_POST['add'])) {

	$add_array['name']					= $_POST['name'];
	$add_array['email']					= $_POST['email'];
	$add_array['username']				= $_POST['username'];
	$add_array['address']				= $_POST['address'];
	$add_array['phone_number']			= $_POST['phone_number'];
	$add_array['email_notifications']	= $_POST['email_notifications'] ? 1 : 0;
	$add_array['match_tickets']			= true;

	if (isset($_POST['pushover_key'])) {
		$add_array['pushover_key']	= $_POST['pushover_key'];
	}
	if (isset($_POST['company_id'])) {
		$add_array['company_id']	= (int) $_POST['company_id'];
	}
	if (isset($_POST['timesheets_hourly_rate'])) {
		$add_array['timesheets_hourly_rate']	= $_POST['timesheets_hourly_rate'];
	}

	$add_array['allow_login']			= 0;
	if ($_POST['allow_login'] == 1) {
		$add_array['allow_login']		= 1;
		$add_array['welcome_email']		= $_POST['welcome_email'] ? 1 : 0;
		$add_array['group_id']			= (int) $_POST['group_id'];
		$add_array['allow_login'] 		= 1;
		$add_array['username'] 			= $_POST['username'];
		$add_array['authentication_id'] = (int) $_POST['authentication_id'];
		$add_array['password']			= $_POST['password'];
		$add_array['password2']			= $_POST['password2'];
	}

	if (isset($_POST['departments']) && !empty($_POST['departments'])) {
		$add_array['departments'] = $_POST['departments'];
	}

	$add_result = $users->add_user($add_array);

	if ($add_result['success']) {
		header('Location: ' . $config->get('address') . '/users/view/' . (int) $add_result['id'] . '/');
		exit;
	}
	else {
		$message = $add_result['message'];
	}
}

$departments 	= $ticket_departments->get(array('enabled' => 1));

$groups 		= $permission_groups->get();

include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<div class="row">
	<form method="post" action="<?php echo safe_output($_SERVER['REQUEST_URI']); ?>">

		<div class="col-md-3">
			<div class="panel panel-default">
				<div class="panel-heading">
					<div class="pull-left">
						<h4><?php echo safe_output($language->get('Users')); ?></h4>
					</div>

					<div class="pull-right">
						<button type="submit" name="add" class="btn btn-success"><?php echo safe_output($language->get('Add')); ?></button>
					</div>

					<div class="clearfix"></div>
				</div>

				<div class="panel-body">
					<p><?php echo safe_output($language->get('If email address is present notifications can be emailed to the user.')); ?></p>

					<div id="login_user_account_form_help">

						<h4><?php echo safe_output($language->get('Authentication Type')); ?></h4>
						<p><?php echo safe_output($language->get('Local: The password is stored in the local database.')); ?></p>
						<?php if (!SAAS_MODE) { ?>
							<p><?php echo safe_output($language->get('Active Directory: The password is stored in Active Directory, password fields are ignored.')); ?></p>
							<p><?php echo safe_output($language->get('Note: Active Directory must be enabled and connected to an Active Directory server in the settings page.')); ?></p>
						<?php } ?>
						<h4><?php echo safe_output($language->get('Permissions')); ?></h4>
						<p><?php echo safe_output($language->get('Submitters: Can create and view their own tickets.')); ?></p>
						<p><?php echo safe_output($language->get('Users: Can create and view their own tickets and view assigned tickets.')); ?></p>
						<p><?php echo safe_output($language->get('Staff: Can create and view their own tickets, view assigned tickets and view tickets within assigned departments.')); ?></p>
						<p><?php echo safe_output($language->get('Moderators: Can create and view tickets, assign tickets and create tickets for other users within assigned departments.')); ?></p>
						<p><?php echo safe_output($language->get('Global Moderators: Can create and view all tickets, assign tickets and create tickets for other users.')); ?></p>
						<p><?php echo safe_output($language->get('Administrators: The same as Global Moderators but can add users and change System Settings.')); ?></p>
					</div>
				</div>

			</div>
		</div>

		<div class="col-md-9">

			<?php if (isset($message)) { ?>
				<div class="alert alert-danger">
					<a href="#" class="close" data-dismiss="alert">&times;</a>
					<?php echo html_output($message); ?>
				</div>
			<?php } ?>

			<div class="panel panel-default">
				<div class="panel-heading"><h4><?php echo safe_output($language->get('Add')); ?></h4></div>
				<div class="panel-body">

					<p><?php echo safe_output($language->get('Full Name')); ?><br /><input class="form-control" type="text" required name="name" size="20" value="<?php if (isset($_POST['name'])) { echo safe_output($_POST['name']); } else if (isset($_GET['name'])) { echo safe_output($_GET['name']); } ?>" /></p>

					<?php $plugins->run('add_user_form_after_name'); ?>

					<p><?php echo safe_output($language->get('Email Address (recommended)')); ?><br /><input class="form-control" type="text" name="email" size="30" value="<?php if (isset($_POST['email'])) { echo safe_output($_POST['email']); } else if (isset($_GET['email'])) { echo safe_output($_GET['email']); } ?>" /></p>

					<p><?php echo safe_output($language->get('Email Notifications')); ?><br />
					<select name="email_notifications">
						<option value="1"<?php if (isset($_POST['email_notifications']) && ($_POST['email_notifications'] == 1)) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('On')); ?></option>
						<option value="0"><?php echo safe_output($language->get('Off')); ?></option>
					</select></p>

					<p><?php echo safe_output($language->get('Phone Number (optional)')); ?><br /><input class="form-control" type="text" name="phone_number" size="20" value="<?php if (isset($_POST['phone_number'])) { echo safe_output($_POST['phone_number']); } ?>" /></p>

					<p><?php echo safe_output($language->get('Address (optional)')); ?><br />
						<textarea id="address" name="address" cols="30" rows="5"><?php if (isset($_POST['address'])) echo safe_output($_POST['address']); ?></textarea>
					</p>

					<p><?php echo safe_output($language->get('Allow Login')); ?><br />
					<select name="allow_login" id="allow_login">
						<option value="0"><?php echo safe_output($language->get('No')); ?></option>
						<option value="1"<?php if (isset($_POST['allow_login']) && ($_POST['allow_login'] == 1)) echo ' selected="selected"'; ?>><?php echo safe_output($language->get('Yes')); ?></option>
					</select></p>

					<div id="login_user_account_form">

						<p><?php echo safe_output($language->get('Username')); ?><br /><input type="text" class="form-control" name="username" value="<?php if (isset($_POST['username'])) echo safe_output($_POST['username']); ?>" /></p>

						<p><?php echo safe_output($language->get('Authentication Type')); ?><br />
						<select name="authentication_id" id="authentication_id">
							<option value="1"><?php echo safe_output($language->get('Local')); ?></option>
							<?php if (!SAAS_MODE) { ?>
								<option value="2"<?php if (isset($_POST['authentication_id']) && ($_POST['authentication_id'] == 2)) echo ' selected="selected"'; ?>><?php echo safe_output($language->get('Active Directory')); ?></option>
								<option value="3"<?php if (isset($_POST['authentication_id']) && ($_POST['authentication_id'] == 3)) echo ' selected="selected"'; ?>><?php echo safe_output($language->get('LDAP')); ?></option>
							<?php } ?>
							<option value="4"<?php if (isset($_POST['authentication_id']) && ($_POST['authentication_id'] == 4)) echo ' selected="selected"'; ?>><?php echo safe_output($language->get('JSON')); ?></option>
						</select></p>

						<div id="login_user_password_fields">
							<p><?php echo safe_output($language->get('Password')); ?><br /><input class="form-control" type="password" name="password" value="" autocomplete="off" /></p>
							<p><?php echo safe_output($language->get('Password Again')); ?><br /><input class="form-control" type="password" name="password2" value="" autocomplete="off" /></p>
						</div>

						<p><?php echo safe_output($language->get('Send Welcome Email')); ?><br />
						<select name="welcome_email">
							<option value="1"<?php if (isset($_POST['welcome_email']) && ($_POST['welcome_email'] == 1)) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('Yes')); ?></option>
							<option value="0"><?php echo safe_output($language->get('No')); ?></option>
						</select>
						</p>

						<?php if (SAAS_MODE) { ?>
							<div class="alert alert-warning">
								<a href="#" class="close" data-dismiss="alert">&times;</a>
								<strong><?php echo safe_output($language->get('Note:')); ?></strong>
								<?php echo safe_output($language->get('User, Staff, Moderator, Global Moderator and Administrator permissions count towards your paid user count. Users with these permission types will be charged for.')); ?>
							</div>
						<?php } ?>

						<p><?php echo safe_output($language->get('Permissions')); ?><br />
						<select name="group_id">
							<?php foreach($groups as $group) { ?>
								<option value="<?php echo (int) $group['id']; ?>"<?php if (isset($_POST['group_id']) && ($_POST['group_id'] == $group['id'])) echo ' selected="selected"'; ?>><?php echo safe_output($group['name']); ?></option>
							<?php } ?>
						</select></p>

						<?php if ($config->get('pushover_enabled')) { ?>
							<p><?php echo $language->get('Pushover Key'); ?><br /><input class="form-control" type="text" name="pushover_key" size="35" value="<?php if (isset($_POST['pushover_key'])) { echo safe_output($_POST['pushover_key']); } ?>" /></p>
						<?php } ?>

					</div>

					<p><?php echo safe_output($language->get('Departments')); ?><br />
					<?php foreach ($departments as $department) { ?>
						<div class="checkbox"><label><input type="checkbox" name="departments[]" value="<?php echo (int) $department['id']; ?>" /> <?php echo safe_output($department['name']); ?></label></div><br />
					<?php } ?>
					</p>
					</div>


				<div class="clearfix"></div>

			</div>
		</div>
	</form>
</div>

<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>
