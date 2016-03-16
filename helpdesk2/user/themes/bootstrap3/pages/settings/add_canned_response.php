<?php
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Add Canned Response'));

$site->set_config('container-type', 'container');

if (!$auth->can('manage_system_settings')) {
	header('Location: ' . $config->get('address') . '/');
	exit;
}

if (isset($_POST['add'])) {
	if (!empty($_POST['name'])) {
		$add_array['name']				= $_POST['name'];
		$add_array['description']		= $_POST['description'];

		$id = $canned_responses->add(array('columns' => $add_array));
	
		header('Location: ' . $config->get('address') . '/settings/tickets/#canned_responses');
		
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
			<div class="panel panel-default">
                <div class="panel-heading">
                        <div class="pull-left">
                            <h3><?php echo safe_output($language->get('Canned Response')); ?></h3>
                        </div>
                        <div class="pull-right">
                            <button type="submit" name="add" class="btn btn-primary"><?php echo safe_output($language->get('Add')); ?></button>
                            <a href="<?php echo $config->get('address'); ?>/settings/tickets/#canned_responses" class="btn btn-success"><?php echo safe_output($language->get('Cancel')); ?></a>
                        </div>
                        <div class="clearfix"></div>	

                </div>
                <div class="panel-body">
				    <p><?php echo safe_output($language->get('Canned Response can be used by all users except Submitters.')); ?></p>
                </div>

			</div>
		</div>

		<div class="col-md-9">

			<?php if (isset($message)) { ?>
				<div class="alert alert-danger">
					<a href="#" class="close" data-dismiss="alert">&times;</a>
					<?php echo html_output($message); ?>
				</div>
			<?php } ?>

			<div class="panel panel-default">
                <div class="panel-heading">
                    <h4><?php echo safe_output($language->get('Add new Canned Response')); ?></h4>
                </div>
                <div class="panel-body">
                        <p><?php echo $language->get('Name'); ?><br /><input style="width: 200px" class="form-control" type="text" name="name" value="<?php if (isset($_POST['name'])) echo safe_output($_POST['name']); ?>" size="30" /></p>
                    <div class="clearfix"></div>
                    <p><?php echo $language->get('Response'); ?><br /><textarea class="wysiwyg_enabled" name="description" cols="70" rows="12"></textarea></p>
                    <div class="clearfix"></div>
                </div>



			</div>
				
			<div class="clearfix"></div>

		</div>

	</form>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>