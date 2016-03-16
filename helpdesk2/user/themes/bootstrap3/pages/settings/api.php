<?php
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('API'));
$site->set_config('container-type', 'container');

/*
if (SAAS_MODE) {
	header('Location: ' . $config->get('address') . '/');
	exit;
}
*/

if (!$auth->can('manage_system_settings')) {
	header('Location: ' . $config->get('address') . '/');
	exit;
}

if (isset($_POST['save'])) {
	$config->set('api_enabled', $_POST['api_enabled'] ? 1 : 0);
	$config->set('api_require_https', $_POST['api_require_https'] ? 1 : 0);

	$log_array['event_severity'] = 'notice';
	$log_array['event_number'] = E_USER_NOTICE;
	$log_array['event_description'] = 'API Settings Edited';
	$log_array['event_file'] = __FILE__;
	$log_array['event_file_line'] = __LINE__;
	$log_array['event_type'] = 'edit';
	$log_array['event_source'] = 'api_settings';
	$log_array['event_version'] = '1';
	$log_array['log_backtrace'] = false;

	$log->add($log_array);

	$message = $language->get('Settings Saved');
}

$api_keys_array = $api_keys->get();

include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<div class="row">

	<script type="text/javascript" src="<?php echo $config->get('address'); ?>/system/js/api.js"></script>

	<form method="post" action="<?php echo safe_output($_SERVER['REQUEST_URI']); ?>">

		<div class="col-md-3">
			<div class="panel panel-default">
				<div class="panel-heading">
					<div class="pull-left">
						<h3><?php echo safe_output($language->get('API')); ?></h3>
					</div>
					<div class="pull-right">
						<button type="submit" name="save" class="btn btn-success"><?php echo safe_output($language->get('Save')); ?></button>
					</div>
					<div class="clearfix"></div>
				</div>
			</div>
		</div>
		<div class="col-md-9">

			<?php if (isset($message)) { ?>
				<div class="alert alert-success">
					<a href="#" class="close" data-dismiss="alert">&times;</a>
					<?php echo html_output($message); ?>
				</div>
			<?php } ?>

			<div class="panel panel-default">
				<div class="panel-heading">
					<h4><?php echo safe_output($language->get('API Settings')); ?></h4>
				</div>
				<div class="panel-body">
					<p><?php echo safe_output($language->get('API Enabled')); ?><br />
					<select name="api_enabled">
						<option value="0"><?php echo safe_output($language->get('No')); ?></option>
						<option value="1"<?php if ($config->get('api_enabled') == 1) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('Yes')); ?></option>
					</select></p>

					<p><?php echo safe_output($language->get('API Require Secure HTTP')); ?><br />
					<select name="api_require_https">
						<option value="0"><?php echo safe_output($language->get('No')); ?></option>
						<option value="1"<?php if ($config->get('api_require_https') == 1) { echo ' selected="selected"'; } ?>><?php echo safe_output($language->get('Yes')); ?></option>
					</select></p>
				</div>

				<div class="clearfix"></div>

			</div>

			<div class="panel panel-default">
					<div class="panel-heading">
						<div class="pull-left">
							<h4><?php echo safe_output($language->get('API Accounts')); ?></h4>
						</div>
						<div class="pull-right">
							<a href="<?php echo safe_output($config->get('address')); ?>/settings/add_api_key/" class="btn btn-primary"><?php echo safe_output($language->get('Add')); ?></a>
						</div>
						<div class="clearfix"></div>

					</div>

				<div class="panel-body">

					<table class="table table-striped table-bordered">
						<tr>
							<th><?php echo safe_output($language->get('Name')); ?></th>
							<th><?php echo safe_output($language->get('Key')); ?></th>
							<th><?php echo safe_output($language->get('Access Level')); ?></th>
							<th><?php echo safe_output($language->get('Delete')); ?></th>
						</tr>
						<?php
							$i = 0;
							foreach ($api_keys_array as $item) {
						?>
						<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
							<td class="centre"><?php echo safe_output($item['name']); ?></td>
							<td class="centre"><?php echo safe_output($item['key']); ?></td>
							<td class="centre">
								<?php
								if ($item['access_level'] == 1) {
									echo $language->get('Guest');
								} else if ($item['access_level'] == 2) {
									echo $language->get('Admin');
								}
								?>
							</td>
							<td class="centre" id="keyexisting-<?php echo (int) $item['id']; ?>"><a href="#custom" id="delete_existing_api_key_item"><img src="<?php echo $config->get('address'); ?>/user/themes/<?php echo safe_output(CURRENT_THEME); ?>/images/icons/delete.png" alt="Delete API Key" /></a></td>
						</tr>
						<?php $i++; } ?>
					</table>
				</div>
				<div class="clearfix"></div>

			</div>
		</div>

	</form>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>
