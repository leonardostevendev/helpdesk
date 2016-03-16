<?php
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Forgot Password'));
$site->set_config('container-type', 'container');

if (isset($_POST['reset'])) {
	if (!empty($_POST['username'])) {
		$users_array = $users->get(array('username' => $_POST['username'], 'allow_login' => 1, 'authentication_id' => 1));
		
		if (count($users_array) == 1) {
			$user = $users_array[0];
			
			if (!empty($user['email'])) {
			
				$users->create_reset_key(array('username' => $_POST['username']));
			
				$message = $language->get('An email with a reset link has been sent to your account.');
			}
			else {
				$message = $language->get('Unable to reset password.');
			}
		}
		else {
			$message = $language->get('Unable to reset password.');
		}
	}
	else {
		$message = $language->get('Unable to reset password.');
	}
}

include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<div class="row">

	<form method="post" action="<?php echo safe_output($_SERVER['REQUEST_URI']); ?>">

		<div class="col-md-3">
			<div class="well well-sm">
				<div class="pull-left">
					<h4><?php echo safe_output($language->get('Forgot Password')); ?></h4>
				</div>
				<div class="pull-right">
					<p><button type="submit" name="reset" class="btn btn-primary"><?php echo safe_output($language->get('Reset')); ?></button></p>
				</div>
				
				<div class="clearfix"></div>
	
				<p><?php echo safe_output($language->get('If you have forgotten your password you can reset it here.')); ?></p>
				<p><?php echo safe_output($language->get('An email will be sent to your account with a reset password link. Please follow this link to complete the password reset process.')); ?></p>
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
					<p><?php echo safe_output($language->get('Username')); ?><br /><input class="form-control" type="text" name="username" value="<?php if (isset($_POST['username'])) echo safe_output($_POST['username']); ?>" /></p>
				</div>	
				<div class="clearfix"></div>

			</div>
		</div>
	</form>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>