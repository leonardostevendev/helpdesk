<?php
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Add SMTP Account'));
$site->set_config('container-type', 'container');

if (!$auth->can('manage_system_settings')) {
	header('Location: ' . $config->get('address') . '/');
	exit;
}

if (isset($_POST['add'])) {

	if (!empty($_POST['name'])) {
	
		$account_array['name']				= $_POST['name'];
		$account_array['hostname']			= $_POST['hostname'];
		$account_array['username']			= $_POST['username'];
		$account_array['password']			= $_POST['password'];
		$account_array['email_address']		= $_POST['email_address'];

		$account_array['enabled']			= $_POST['enabled'] ? 1 : 0;
		$account_array['tls']				= $_POST['tls'] ? 1 : 0;
		$account_array['authentication']	= $_POST['authentication'] ? 1 : 0;
		
		$account_array['port']				= (int) $_POST['port'];
		
		$id = $smtp_accounts->add($account_array);
		
		if (!$config->get('default_smtp_account') || $config->get('default_smtp_account') == 0) {
			$config->set('default_smtp_account', $id);
		}
			
		header('Location: ' . $config->get('address') . '/settings/email/#smtp_accounts');
		exit;
	}
	else {
		$message = $language->get('Name Empty');
	}
	
}

include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<div class="row">

	<form method="post" action="<?php echo safe_output($_SERVER['REQUEST_URI']); ?>">
	
		<div class="col-md-3">
			<div class="well well-sm">
				<div class="pull-left">
					<h4><?php echo safe_output($language->get('Add Account')); ?></h4>
				</div>
				
				<div class="pull-right">
					<p>
					<button type="submit" name="add" class="btn btn-primary"><?php echo safe_output($language->get('Add')); ?></button>
					<a href="<?php echo $config->get('address'); ?>/settings/email/#smtp_accounts" class="btn btn-default"><?php echo safe_output($language->get('Cancel')); ?></a>
					</p>
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
						<option value="1"<?php if (isset($_POST['enabled']) && $_POST['enabled'] == 1) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('Yes')); ?></option>
					</select></p>
					
					<p><?php echo safe_output($language->get('Name (nickname for this account)')); ?><br /><input class="form-control" type="text" name="name" size="30" value="<?php if (isset($_POST['name'])) echo safe_output($_POST['name']); ?>" /></p>

					<p><?php echo safe_output($language->get('Hostname (i.e mail.example.com)')); ?><br /><input class="form-control" type="text" name="hostname" size="30" value="<?php if (isset($_POST['hostname'])) echo safe_output($_POST['hostname']); ?>" /></p>
					
					<p><?php echo safe_output($language->get('Port (default 25)')); ?><br /><input type="text" class="form-control" name="port" size="5" value="<?php if (isset($_POST['hostname'])) { echo (int) ($_POST['port']); } else { echo '25'; } ?>" /></p>
		
					<p><?php echo safe_output($language->get('TLS (required for gmail and other servers that use SSL)')); ?><br />
					<select name="tls">
						<option value="0"><?php echo safe_output($language->get('No')); ?></option>
						<option value="1"<?php if (isset($_POST['tls']) && $_POST['tls'] == 1) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('Yes')); ?></option>
					</select></p>
					
					<p><?php echo safe_output($language->get('Email Address')); ?><br /><input type="text" class="form-control" name="email_address" size="30" value="<?php if (isset($_POST['email_address'])) echo safe_output($_POST['email_address']); ?>" /></p>


					<p><?php echo safe_output($language->get('Authentication')); ?><br />
					<select name="authentication">
						<option value="0"><?php echo safe_output($language->get('No')); ?></option>
						<option value="1"<?php if (isset($_POST['authentication']) && $_POST['authentication'] == 1) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('Yes')); ?></option>
					</select></p>
							
					<p><?php echo safe_output($language->get('Username')); ?><br /><input class="form-control" autocomplete="off" type="text" name="username" size="30" value="<?php if (isset($_POST['username'])) echo safe_output($_POST['username']); ?>" /></p>
					<p><?php echo safe_output($language->get('Password')); ?><br /><input class="form-control" autocomplete="off" type="password" name="password" size="30" value="<?php if (isset($_POST['password'])) echo safe_output($_POST['password']); ?>" /></p>			
				</div>
				<div class="clearfix"></div>

			</div>
		</div>
	</form>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>