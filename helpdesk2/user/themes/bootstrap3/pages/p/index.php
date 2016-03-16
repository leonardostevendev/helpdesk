<?php
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_config('container-type', 'container');

if ($url->get_module() == '') {
	header('Location: ' . $config->get('address') . '/');
	exit;
}

$plugins->run('plugin_page_header_' . $url->get_module());

include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<?php if (DEMO_MODE) { ?>
	<div class="alert alert-warning">
		<a href="#" class="close" data-dismiss="alert">&times;</a>
		<strong><?php echo $language->get('Demo Mode'); ?>:</strong>
		<?php echo $language->get('Plugins must be purchased separately.'); ?>
	</div>
<?php } ?>

<?php if ($auth->get('global_message')) { ?>
	<div class="alert alert-success">
		<a href="#" class="close" data-dismiss="alert">&times;</a>
		<strong><?php echo $language->get('Notice'); ?>:</strong>
		<?php echo html_output($auth->get('global_message')); ?>
	</div>
<?php } ?>

<?php $plugins->run('plugin_page_body_' . $url->get_module()); ?>

<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>