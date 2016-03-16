<?php
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Login'));
$site->set_config('container-type', 'container');

if (isset($_POST['submit'])) {
	if ($auth->login(array('username' => $_POST['username'], 'password' => $_POST['password']))) {
		if (isset($_SESSION['page'])) {
			header('Location: ' . safe_output($_SESSION['page']));
		}
		else {
			header('Location: ' . $config->get('address') . '/');
		}
		exit;
	}
	else {
		$message = $language->get('Login Failed');
	}
}
else {
	if ($config->get('facebook_enabled')) {
		if (isset($_SESSION['fb_'. $config->get('facebook_app_id') .'_user_id'])) {
			$message = 'Your current Facebook profile is not linked with ' . $config->get('name') . '. Please login with your local details.';
		}
	}
}

$login_message = $config->get('login_message');

?>
<!-- Leo -->
<?php
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Guest Portal'));
$site->set_config('container-type', 'container');

$guest_portal_index_html	= $config->get('guest_portal_index_html');
if (empty($guest_portal_index_html)) {
	header('Location: ' . $config->get('address') . '/guest/ticket_add/');
	exit;
}

include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<div class="row-fluid">
	
	<div class="col-md-3">
		<div class="well well-sm">
			<div class="pull-left">
				<h4><?php echo safe_output($language->get('Guest Portal')); ?></h4>
			</div>
			
			<div class="pull-right">
				<a href="<?php echo safe_output($config->get('address')); ?>/guest/ticket_add/" class="btn btn-default"><?php echo safe_output($language->get('Create a Support Ticket')); ?></a>
			</div>
			<div class="clearfix"></div>
		</div>
	</div>

	<div class="col-md-9">
		<div class="well well-sm">
			
			<?php if ($config->get('html_enabled') == 1) { ?>
				<?php echo html_output($config->get('guest_portal_index_html')); ?>
			<?php } else { ?>
				<p><?php echo nl2br(safe_output($config->get('guest_portal_index_html'))); ?></p>
			<?php } ?>
			
			<div class="clearfix"></div>

		</div>
	</div>
</div>		
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>