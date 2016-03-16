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

<!-- Hasta aca llego el daño :v-->

<?php
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

if ($url->get_module() == '') {
	header('Location: ' . $config->get('address') . '/');
	exit;
}

$plugins->run('public_page_header_' . $url->get_module());

include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>

<?php $plugins->run('public_page_body_' . $url->get_module()); ?>

<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>