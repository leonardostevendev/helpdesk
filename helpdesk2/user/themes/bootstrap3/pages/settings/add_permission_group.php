<?php
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Add Group'));
$site->set_config('container-type', 'container');

if (!$auth->can('manage_system_settings')) {
	header('Location: ' . $config->get('address') . '/');
	exit;
}

if (isset($_POST['add'])) {
	if (!empty($_POST['name'])) {
		$add_array['name']				= $_POST['name'];

		$id = $permission_groups->add($add_array);
	
		header('Location: ' . $config->get('address') . '/settings/edit_permission_group/' . (int) $id . '/');
		
	}
	else {
		$message = $language->get('Name Empty');
	}
}



include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
	
<form method="post" action="<?php echo safe_output($_SERVER['REQUEST_URI']); ?>" class="settings">

	<div class="row">
	
		<div class="col-md-3">
			<div class="well well-sm">

				<div class="pull-left">
					<h4><?php echo safe_output($language->get('Add Group')); ?></h4>
				</div>
				<div class="pull-right">
					<button type="submit" name="add" class="btn btn-success"><?php echo safe_output($language->get('Add')); ?></button>
					<a href="<?php echo $config->get('address'); ?>/settings/permissions/" class="btn btn-default"><?php echo safe_output($language->get('Cancel')); ?></a>
				</div>
				<div class="clearfix"></div>
			</div>

		</div>
				
		<div class="col-md-9">
			<?php if (isset($message)) { ?>
				<div class="alert alert-danger">
					<?php echo html_output($message); ?>
				</div>
			<?php } ?>
			
			<div class="panel panel-default">
                <div class="panel-heading">
                    <div class="pull-left">
                        <h4><?php echo $language->get('Name'); ?></h4>
                        <input type="text" class="form-control" name="name" value="<?php if (isset($_POST['name'])) echo safe_output($_POST['name']); ?>" size="30" />
                    </div>

                    <div class="clearfix"></div>
                </div>
			</div>
		</div>
	</div>

	

</form>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>