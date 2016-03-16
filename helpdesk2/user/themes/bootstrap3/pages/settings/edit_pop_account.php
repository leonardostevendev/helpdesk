<?php
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title('Edit POP Account');
$site->set_config('container-type', 'container');

if (!$auth->can('manage_system_settings')) {
	header('Location: ' . $config->get('address') . '/');
	exit;
}

$id = (int) $url->get_item();

if ($id == 0) {
	header('Location: ' . $config->get('address') . '/settings/email/#pop3_accounts');
	exit;
}

$pop_array 			= $pop_accounts->get(array('id' => $id));

if (count($pop_array) == 1) {
	$item = $pop_array[0];
}
else {
	header('Location: ' . $config->get('address') . '/settings/email/#pop3_accounts');
	exit;	
}


if (isset($_POST['delete'])) {
	$pop_accounts->delete(array('id' => $id));
	header('Location: ' . $config->get('address') . '/settings/email/#pop3_accounts');
	exit;
}

if (isset($_POST['save'])) {

	if (!empty($_POST['name'])) {
	
		//pop3
		$account_array['name']				= $_POST['name'];
		$account_array['hostname']			= $_POST['hostname'];
		$account_array['username']			= $_POST['username'];
		$account_array['password']			= $_POST['password'];
		$account_array['id']				= $item['id'];
		
		$account_array['enabled']			= $_POST['enabled'] ? 1 : 0;
		$account_array['tls']				= $_POST['tls'] ? 1 : 0;
		$account_array['download_files']	= $_POST['download_files'] ? 1 : 0;
		
		$account_array['leave_messages']		= 0;
		if (!SAAS_MODE) {
			$account_array['leave_messages']	= $_POST['leave_messages'] ? 1 : 0;
		}
		
		$account_array['auto_create_users']	= $_POST['auto_create_users'] ? 1 : 0;
		
		$account_array['port']				= (int) $_POST['port'];
		$account_array['department_id']		= (int) $_POST['department_id'];
		$account_array['state_id']			= (int) $_POST['state_id'];
		$account_array['priority_id']		= (int) $_POST['priority_id'];
		$account_array['smtp_account_id']	= (int) $_POST['smtp_account_id'];
		
		$pop_accounts->edit($account_array);
			
		header('Location: ' . $config->get('address') . '/settings/email/#pop3_accounts');
		exit;
	}
	else {
		$message = $language->get('Name Empty');
	}
	
}

$status 		= $ticket_status->get(array('enabled' => 1));
$priorities 	= $ticket_priorities->get(array('enabled' => 1));
$departments	= $ticket_departments->get(array('enabled' => 1));
$smtp_array 	= $smtp_accounts->get();

include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<div class="row">

	<script type="text/javascript">
		$(document).ready(function () {
			$('#delete').click(function () {
				if (confirm("<?php echo safe_output($language->get('Are you sure you wish to delete this POP3 Account?')); ?>")){
					return true;
				}
				else{
					return false;
				}
			});
		});
	</script>
	
	
	<form method="post" action="<?php echo safe_output($_SERVER['REQUEST_URI']); ?>">

		<div class="col-md-3">
			<div class="well well-sm">
				<div class="pull-left">
					<h4><?php echo safe_output($language->get('Edit Account')); ?></h4>
				</div>
				
				<div class="pull-right">
					<p>
					<button type="submit" name="save" class="btn btn-primary"><?php echo safe_output($language->get('Save')); ?></button>
					<a href="<?php echo safe_output($config->get('address')); ?>/settings/email/#pop3_accounts" class="btn btn-default"><?php echo safe_output($language->get('Cancel')); ?></a>
					</p>
				</div>
				<div class="clearfix"></div>	
				<br />
				<div class="pull-right">
					<button type="submit" id="delete" name="delete" class="btn btn-danger"><?php echo safe_output($language->get('Delete')); ?></button>
				</div>
				<div class="clearfix"></div>
			</div>
		</div>

		<div class="col-md-9">
				
			<?php if (isset($message)) { ?>
				<div class="alert alert-danger">
					<a href="#" class="close" data-dismiss="alert">&times;</a>
					<?php echo html_output($message); ?>
				</div>
			<?php } ?>
			
			<div class="well well-sm">
				<div class="col-lg-6">	
					
					<p><?php echo safe_output($language->get('Enabled')); ?><br />
					<select name="enabled">
						<option value="0"><?php echo safe_output($language->get('No')); ?></option>
						<option value="1"<?php if ($item['enabled'] == 1) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('Yes')); ?></option>
					</select></p>
					
					<p><?php echo safe_output($language->get('Name (nickname for this account)')); ?><br /><input class="form-control" type="text" name="name" size="30" value="<?php echo safe_output($item['name']); ?>" /></p>

					<p><?php echo safe_output($language->get('Hostname (i.e mail.example.com)')); ?><br /><input class="form-control" type="text" name="hostname" size="30" value="<?php echo safe_output($item['hostname']); ?>" /></p>
					
					<p><?php echo safe_output($language->get('Port (default 110)')); ?><br /><input class="form-control" type="text" name="port" size="5" value="<?php echo (int) $item['port']; ?>" /></p>
		
					<p><?php echo safe_output($language->get('TLS (required for gmail and other servers that use SSL)')); ?><br />
					<select name="tls">
						<option value="0"><?php echo safe_output($language->get('No')); ?></option>
						<option value="1"<?php if ($item['tls'] == 1) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('Yes')); ?></option>
					</select></p>

					<p><?php echo safe_output($language->get('Download File Attachments')); ?><br />
					<select name="download_files">
						<option value="1"><?php echo safe_output($language->get('Yes')); ?></option>
						<option value="0"<?php if ($item['download_files'] == 0) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('No')); ?></option>
					</select></p>
					
					<?php if (!SAAS_MODE) { ?>
						<p><?php echo safe_output($language->get('Leave Message on Server (not recommended)')); ?><br />
						<select name="leave_messages">
							<option value="0"><?php echo safe_output($language->get('No')); ?></option>
							<option value="1"<?php if ($item['leave_messages'] == 1) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('Yes')); ?></option>
						</select></p>
					<?php } ?>
					
					
					<p><?php echo safe_output($language->get('Auto Create Users')); ?><br />
					<select name="auto_create_users">
						<option value="0"><?php echo safe_output($language->get('No')); ?></option>
						<option value="1"<?php if ($item['auto_create_users'] == 1) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('Yes')); ?></option>
					</select></p>
		
					<p><?php echo safe_output($language->get('Username')); ?><br /><input class="form-control" autocomplete="off" type="text" name="username" size="30" value="<?php echo safe_output($item['username']); ?>" /></p>
					<p><?php echo safe_output($language->get('Password')); ?><br /><input class="form-control" autocomplete="off" type="password" name="password" size="30" value="<?php echo safe_output(decode($item['password'])); ?>" /></p>

					<p><?php echo safe_output($language->get('Department')); ?><br />
					<select name="department_id">
						<?php foreach ($departments as $department) { ?>
						<option value="<?php echo (int) $department['id']; ?>"<?php if ($item['department_id'] == $department['id']) { echo ' selected="selected"'; } ?>><?php echo safe_output($department['name']); ?></option>
						<?php } ?>
					</select></p>
					
					<p><?php echo safe_output($language->get('Status')); ?><br />					
						<select name="state_id">
						<?php foreach ($status as $status_item) { ?>
							<option value="<?php echo (int) $status_item['id']; ?>"<?php if ($item['state_id'] == $status_item['id']) { echo ' selected="selected"'; } ?>><?php echo safe_output($status_item['name']); ?></option>
						<?php } ?>
						</select>
					</p>
					
					<p><?php echo safe_output($language->get('Priority')); ?><br />
					<select name="priority_id">
						<?php foreach ($priorities as $priority) { ?>
						<option value="<?php echo (int) $priority['id']; ?>"<?php if ($item['priority_id'] == $priority['id']) { echo ' selected="selected"'; } ?>><?php echo safe_output($priority['name']); ?></option>
						<?php } ?>
					</select></p>		

					<p><?php echo safe_output($language->get('SMTP Account')); ?><br />
					<select name="smtp_account_id">
						<option value="0"><?php echo safe_output($language->get('Default SMTP Account')); ?></option>
						<?php foreach ($smtp_array as $smtp) { ?>
							<option value="<?php echo safe_output($smtp['id']); ?>"<?php if ($smtp['id'] == $item['smtp_account_id']) { echo ' selected="selected"'; } ?>><?php echo safe_output($smtp['name']); ?></option>
						<?php } ?>
					</select>
					</p>

				</div>
				
				<div class="clearfix"></div>

			</div>
		</div>
	</form>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>