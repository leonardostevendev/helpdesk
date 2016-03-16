<?php
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Add Priority'));
$site->set_config('container-type', 'container');

if (!$auth->can('manage_system_settings')) {
	header('Location: ' . $config->get('address') . '/');
	exit;
}

if (isset($_POST['add'])) {
	if (!empty($_POST['name'])) {
		$add_array['name']				= $_POST['name'];
		$add_array['colour']			= $_POST['colour'];
		$add_array['enabled']			= 1;

		$id = $ticket_priorities->add($add_array);
	
		header('Location: ' . $config->get('address') . '/settings/tickets/#priority');
		
	}
	else {
		$message = $language->get('Name Empty');
	}
}



include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<link rel="stylesheet" href="<?php echo $config->get('address'); ?>/system/libraries/colorpicker/css/colorpicker.css" type="text/css" />	
<script type="text/javascript" src="<?php echo $config->get('address'); ?>/system/libraries/colorpicker/js/bootstrap-colorpicker.js"></script>
<script type="text/javascript" src="<?php echo $config->get('address'); ?>/system/js/colourpicker.js"></script>

<div class="row">
	<form method="post" action="<?php echo safe_output($_SERVER['REQUEST_URI']); ?>">

		<div class="col-md-3">
			<div class="panel panel-default">
                <div class="panel panel-heading">
                    <div class="pull-left">
                        <h3><?php echo safe_output($language->get('Priority')); ?></h3>
                    </div>	

                    <div class="pull-right">
                        <button type="submit" name="add" class="btn btn-success"><?php echo safe_output($language->get('Add')); ?></button>
                        <a href="<?php echo $config->get('address'); ?>/settings/tickets/#priority" class="btn btn-default"><?php echo safe_output($language->get('Cancel')); ?></a>
                    </div>

                    <div class="clearfix"></div>	
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
                    <div class="pull-left">
                        <h4><?php echo $language->get('Name'); ?></h4>
                        <input class="form-control" type="text" name="name" value="<?php if (isset($_POST['name'])) echo safe_output($_POST['name']); ?>" size="30" />
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="panel-heading">
                    <div class="pull-left">
                        <h4><?php echo $language->get('Colour'); ?></h4>
                        <input type="text" name="colour" id="cp1" value="<?php if (isset($_POST['colour'])) echo safe_output($_POST['colour']); ?>" maxlength="7" class="input-small form-control">
                    </div>
                    <div class="clearfix"></div>
                </div>

			</div>
				
			<div class="clearfix"></div>

		</div>

	</form>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>