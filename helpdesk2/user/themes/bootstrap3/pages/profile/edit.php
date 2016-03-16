<?php
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Profile'));
$site->set_config('container-type', 'container');


if (isset($_POST['save'])) {

	if (DEMO_MODE && DEMO_USER_ID_1 == $auth->get('id')) {
		$message = $language->get('Editing this user is disabled.');
	}
	else {
		
		if (!empty($_POST['name'])) {

			$array = array(
				'id'							=> $auth->get('id'),
				'name'							=> $_POST['name'],
				'email_notifications'			=> $_POST['email_notifications'] ? 1 : 0,
				'view_ticket_reverse'			=> $_POST['view_ticket_reverse'] ? 1 : 0
			);

			if (isset($_POST['view_ticket_filter_projects'])) {
				$array['view_ticket_filter_projects']	= $_POST['view_ticket_filter_projects'] ? 1 : 0;			
			}	

			if (isset($_POST['timesheets_hourly_rate']) && $auth->can('timesheets_change_hourly_rate')) {
				$array['timesheets_hourly_rate']	= $_POST['timesheets_hourly_rate'];				
			}	
			
			if ($config->get('pushover_enabled') && $config->get('pushover_user_enabled')) {
				if (isset($_POST['pushover_key'])) {
					$array['pushover_key'] = $_POST['pushover_key'];
				}
			}
			
			$plugins->run('submit_edit_profile_form_success_before_edit_user', $array);

			$users->edit($array);
			
			$auth->load();
			
			$message = $language->get('Profile Updated');
		}
		else {
			$message = $language->get('Name Empty');
		}

		if ($auth->get('authentication_id') == 1) {
			
			if (!empty($_POST['current_password']) && !empty($_POST['password']) && !empty($_POST['password2'])) {
			
				if ($auth->test_password(array('id' => $auth->get('id'), 'password' => $_POST['current_password']))) {
					
					if ($_POST['password'] === $_POST['password2']) {
					
						$array = array(
							'id'							=> $auth->get('id'),
							'name'							=> $_POST['name'],
							'email_notifications'			=> $_POST['email_notifications'] ? 1 : 0,
							'view_ticket_reverse'			=> $_POST['view_ticket_reverse'] ? 1 : 0,
							'view_ticket_filter_projects'	=> $_POST['view_ticket_filter_projects'] ? 1 : 0,
							'password'						=> $_POST['password'],
							
						);
					
						if (isset($_POST['timesheets_hourly_rate']) && $auth->can('timesheets_change_hourly_rate')) {
							$array['timesheets_hourly_rate']	= $_POST['timesheets_hourly_rate'];				
						}	
					
						if ($config->get('pushover_enabled') && $config->get('pushover_user_enabled')) {
							if (isset($_POST['pushover_key'])) {
								$array['pushover_key'] = $_POST['pushover_key'];
							}
						}
					
						$users->edit($array);
					
						$message = 'Password Changed';
					}
					else {
						$message = 'New Passwords Do Not Match';
					}
					
				}
				else {
					$message = 'Current Password Incorrect';
				}
			
			}
		}
	}
}


include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<div class="row">
	<form method="post" action="<?php echo safe_output($_SERVER['REQUEST_URI']); ?>">

		<div class="col-md-3">
			<div class="well well-sm">
				<div class="pull-left">
					<h4><?php echo safe_output($language->get('Profile')); ?></h4>
				</div>
				<div class="pull-right">
					<p>
						<button type="submit" name="save" class="btn btn-primary"><?php echo safe_output($language->get('Save')); ?></button>
						<a href="<?php echo safe_output($config->get('address')); ?>/profile/" class="btn btn-default"><?php echo safe_output($language->get('Cancel')); ?></a>
					</p>
				</div>		
			
				<div class="clearfix"></div>
				
				<label class="left-result"><?php echo safe_output($language->get('Name')); ?></label>
				<p class="right-result">
					<?php echo safe_output(ucwords($auth->get('name'))); ?>
				</p>	
				<div class="clearfix"></div>
				
				<label class="left-result"><?php echo safe_output($language->get('Username')); ?></label>
				<p class="right-result">
					<?php echo safe_output($auth->get('username')); ?>
				</p>					
				<div class="clearfix"></div>
				<label class="left-result"><?php echo safe_output($language->get('Email')); ?></label>
				<p class="right-result">
					<?php echo safe_output($auth->get('email')); ?>
				</p>	

				<div class="clearfix"></div>
				<?php if ($config->get('gravatar_enabled')) { ?>
				<label class="left-result"><?php echo safe_output($language->get('Gravatar')); ?></label>
				<p class="right-result">
					<?php $gravatar->setEmail($auth->get('email')); ?>
					<img src="<?php echo $gravatar->getUrl(); ?>" alt="Gravatar" />
				</p>
				<?php } ?>
		

				<div class="clearfix"></div>
			
			</div>
		</div>
		
		<div class="col-md-9">
		
			<?php if (DEMO_MODE && DEMO_USER_ID_1 == $auth->get('id')) { ?>
				<div class="alert alert-warning">
					<a href="#" class="close" data-dismiss="alert">&times;</a>
					<strong><?php echo $language->get('Demo Mode'); ?>:</strong>
					<?php echo $language->get('Changing this user is disabled.'); ?>
				</div>
			<?php } ?>

			<?php if (isset($message)) { ?>
				<div class="alert alert-success">
					<a href="#" class="close" data-dismiss="alert">&times;</a>
					<?php echo html_output($message); ?>
				</div>
			<?php } ?>
		
			<div class="well well-sm">
				<div class="col-lg-5">

					<p><?php echo safe_output($language->get('Name')); ?><br /><input class="form-control" type="text" name="name" size="20" value="<?php echo safe_output($auth->get('name')); ?>" /></p>
				
					<p><?php echo safe_output($language->get('Notifications')); ?><br />
					<select name="email_notifications">
						<option value="0"><?php echo safe_output($language->get('Off')); ?></option>
						<option value="1"<?php if ($auth->get('email_notifications') == 1) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('On')); ?></option>
					</select></p>

					<p><?php echo safe_output($language->get('Reverse View Ticket Order')); ?><br />
					<select name="view_ticket_reverse">
						<option value="0"><?php echo safe_output($language->get('Off')); ?></option>
						<option value="1"<?php if ($auth->get('view_ticket_reverse') == 1) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('On')); ?></option>
					</select></p>

					<?php if ($config->get('pushover_enabled') && $config->get('pushover_user_enabled')) { ?>
						<p><?php echo $language->get('Pushover Key'); ?> (<a href="https://pushover.net/">Pushover Website</a>)<br /><input type="text" name="pushover_key" size="35" value="<?php echo safe_output($auth->get('pushover_key')); ?>" /></p>
					<?php } ?>
					
					<?php $plugins->run('edit_profile_content_before_change_password'); ?>

					<h3><?php echo safe_output($language->get('Change Password')); ?></h3>			
					<?php if ($auth->get('authentication_id') == 1) { ?>
						<p><?php echo safe_output($language->get('Current Password')); ?><br /><input class="form-control" type="password" name="current_password" value="" autocomplete="off" /></p>
						<p><?php echo safe_output($language->get('New Password')); ?><br /><input class="form-control" type="password" name="password" value="" autocomplete="off" /></p>
						<p><?php echo safe_output($language->get('New Password Again')); ?><br /><input class="form-control" type="password" name="password2" value="" autocomplete="off" /></p>
					<?php } else { ?>
						<?php echo message($language->get('You cannot change the password for this account.')); ?>
					<?php } ?>
				</div>
				
				<div class="clearfix"></div>

			</div>
				
			<?php
				$plugins->run('edit_profile_content_finish');
			?>
		</div>
		
		<?php
			$plugins->run('edit_profile_form_finish');
		?>
		
	</form>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>