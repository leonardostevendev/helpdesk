<?php
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Plugins'));
$site->set_config('container-type', 'container');

if (!$auth->can('manage_system_settings')) {
	header('Location: ' . $config->get('address') . '/');
	exit;
}

$get_array = array();

$enabled = '';
if (isset($_GET['enabled'])) {
	if ($_GET['enabled'] == 'true') {
		$get_array['enabled'] = true;
		$enabled = 'true';
	}
	else if ($_GET['enabled'] == 'false') {
		$get_array['enabled'] = false;
		$enabled = 'false';
	}
}

$upgrade 		= new upgrade();

$plugins_array = $plugins->get($get_array);

include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<div class="row">
	<div class="col-md-3">
		<div class="well well-sm">

			<div class="pull-left">
				<h4><?php echo safe_output($language->get('Plugins')); ?></h4>
			</div>
		
			<?php if (!SAAS_MODE) { ?>
				<div class="pull-right">
					<a href="http://portal.dalegroup.net/public/kb_view/15/" class="btn btn-small btn-info"><?php echo safe_output($language->get('Online Plugins Directory')); ?></a>
				</div>		
			
				<div class="clearfix"></div>
				
				<p><?php echo safe_output($language->get('Plugins can be used to add extra functionality to Tickets.')); ?></p>
				<p><?php echo safe_output($language->get('Please ensure that you only install trusted plugins.')); ?></p>
				<p><?php echo safe_output($language->get('Plugins are located on the web server in the user/plugins/ folder.')); ?></p>
			<?php } ?>
			
			<div class="clearfix"></div>

		</div>
	</div>
	<div class="col-md-9">
		<?php if (!SAAS_MODE && (($config->get('database_version') !== $upgrade->get_db_version()) || ($config->get('program_version') !== $upgrade->get_program_version()))) { ?>
			<div class="alert alert-warning">
				<a href="#" class="close" data-dismiss="alert">&times;</a>
				<strong><?php echo $language->get('Warning'); ?>:</strong>
				<?php echo html_output($language->get('The database needs upgrading.')); ?>
				<strong><a href="<?php echo safe_output($config->get('address')); ?>/upgrade/"><?php echo safe_output($language->get('Upgrade')); ?></a></strong>
			</div>
		<?php } ?>
		
		<?php if (!SAAS_MODE && $upgrade->update_available()) { ?>
			<div class="alert alert-warning">
				<a href="#" class="close" data-dismiss="alert">&times;</a>
				<?php echo html_output($language->get('There is a software update available.')); ?>
				<strong><a href="<?php echo safe_output($config->get('address')); ?>/update/info/"><?php echo safe_output($language->get('More Information')); ?></a></strong>
			</div>
		<?php } ?>
	
		<?php if (DEMO_MODE) { ?>
			<div class="alert alert-warning">
				<a href="#" class="close" data-dismiss="alert">&times;</a>
				<strong><?php echo $language->get('Demo Mode'); ?>:</strong>
				<?php echo $language->get('Plugins must be purchased separately.'); ?>
			</div>
		<?php } ?>

		<div class="btn-group">
			<a href="<?php echo safe_output($config->get('address')); ?>/settings/plugins/" class="btn btn-default btn-sm<?php if (empty($enabled)) echo ' active'; ?>">Show All Plugins</a>
			<a href="<?php echo safe_output($config->get('address')); ?>/settings/plugins/?enabled=true" class="btn btn-default btn-sm<?php if ($enabled == 'true') echo ' active'; ?>">Show Enabled Plugins</a>
			<a href="<?php echo safe_output($config->get('address')); ?>/settings/plugins/?enabled=false" class="btn btn-default btn-sm<?php if ($enabled == 'false') echo ' active'; ?>">Show Disabled Plugins</a>
		</div>
		
		<div class="clearfix"></div><br />		
		
		<?php if (!empty($plugins_array)) { ?>
			<section id="no-more-tables">	
				<table class="table table-striped table-bordered">
					<thead>
						<tr>
							<th><?php echo safe_output($language->get('Name')); ?></th>
							<?php if (!SAAS_MODE) { ?>
								<th><?php echo safe_output($language->get('Version')); ?></th>
							<?php } ?>
							<th><?php echo safe_output($language->get('Description')); ?></th>
							<th><?php echo safe_output($language->get('Status')); ?></th>
						</tr>
					</thead>
					<?php
						foreach ($plugins_array as $plugin) {
					?>
					<tr>
						<td data-title="<?php echo safe_output($language->get('Name')); ?>" class="centre">
							<?php if (SAAS_MODE) { ?>
								<?php echo safe_output($plugin['name']); ?>
							<?php } else { ?>
								<a href="<?php echo safe_output($plugin['website']); ?>"><?php echo safe_output($plugin['name']); ?></a>
							<?php } ?>
						</td>
						<?php if (!SAAS_MODE) { ?>
							<td data-title="<?php echo safe_output($language->get('Version')); ?>" class="centre"><?php echo safe_output($plugin['version']); ?></td>
						<?php } ?>
						<td data-title="<?php echo safe_output($language->get('Description')); ?>" class="centre">
							<?php echo html_output($plugin['description']); ?>
						</td>
						<td data-title="<?php echo safe_output($language->get('Status')); ?>" class="centre plugin-buttons">
							<?php if ($plugins->loaded($plugin['file_name'])) { ?>
								<a class="btn btn-success btn-sm" href="<?php echo safe_output($config->get('address')); ?>/settings/plugin_action/?action=disable&amp;name=<?php echo safe_output($plugin['file_name']); ?>&amp;enabled=<?php echo safe_output($enabled); ?>"><?php echo safe_output($language->get('Enabled')); ?></a>
							<?php } else { ?>
								<a class="btn btn-danger btn-sm" href="<?php echo safe_output($config->get('address')); ?>/settings/plugin_action/?action=enable&amp;name=<?php echo safe_output($plugin['file_name']); ?>&amp;enabled=<?php echo safe_output($enabled); ?>"><?php echo safe_output($language->get('Disabled')); ?></a>
							<?php } ?>
						</td>
					</tr>
					<?php if (!SAAS_MODE && $plugins->update_available($plugin)) { ?>
						<tr>
							<td colspan="4" class="alert-warning">
								<?php echo html_output($language->get('There is a software update available for this plugin.')); ?>
								<strong>
									<a href="<?php echo safe_output($config->get('address')); ?>/update/plugin_info/?file_name=<?php echo safe_output($plugin['file_name']); ?>">
										<?php echo safe_output($language->get('More Information')); ?>
									</a>
								</strong>
							</td>
						</tr>
					<?php } ?>
					<?php } ?>
				</table>
			</section>
		<?php } else { ?>
			<div class="alert alert-warning">
				<?php echo $language->get('No Plugins Found.'); ?>
			</div>		
		<?php } ?>
	</div>	
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>