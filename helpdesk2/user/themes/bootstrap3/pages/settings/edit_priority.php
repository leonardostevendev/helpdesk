<?php
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Edit Priority'));
$site->set_config('container-type', 'container');

if (!$auth->can('manage_system_settings')) {
	header('Location: ' . $config->get('address') . '/');
	exit;
}

$id = (int) $url->get_item();

$priority		= $ticket_priorities->get(array('id' => $id));

if (empty($priority)) {
	header('Location: ' . $config->get('address') . '/settings/tickets/#priority');
	exit;
}

$item = $priority[0];

if (isset($_POST['delete'])) {
	$ticket_priorities->delete(array('id' => $item['id']));
	header('Location: ' . $config->get('address') . '/settings/tickets/#priority');
	exit;
}

if (isset($_POST['save'])) {
	if (!empty($_POST['name'])) {
		$add_array['colour']			= $_POST['colour'];
		$add_array['name']				= $_POST['name'];
		$add_array['id']				= $id;

		$ticket_priorities->edit($add_array);


		header('Location: ' . $config->get('address') . '/settings/tickets/#priority');
		exit;

	}
	else {
		$message = $language->get('Name empty');
	}
}



include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<link rel="stylesheet" href="<?php echo $config->get('address'); ?>/system/libraries/colorpicker/css/colorpicker.css" type="text/css" />
<script type="text/javascript" src="<?php echo $config->get('address'); ?>/system/libraries/colorpicker/js/bootstrap-colorpicker.js"></script>
<script type="text/javascript" src="<?php echo $config->get('address'); ?>/system/js/colourpicker.js"></script>

	<script type="text/javascript">
		$(document).ready(function () {
			$('#delete').click(function () {
				if (confirm("<?php echo safe_output($language->get('Are you sure you wish to delete this Priority?')); ?>")){
					return true;
				}
				else{
					return false;
				}
			});
		});
	</script>

<div class="row">
	<form method="post" action="<?php echo safe_output($_SERVER['REQUEST_URI']); ?>">

		<div class="col-md-3">
			<div class="panel panel-default">
				<div class="panel-heading">
					<div class="pull-left">
						<h4><?php echo safe_output($language->get('Priority')); ?></h4>
					</div>
					<div class="pull-right">
						<button type="submit" name="save" class="btn btn-success"><?php echo safe_output($language->get('Save')); ?></button>
						<a href="<?php echo $config->get('address'); ?>/settings/tickets/#priority" class="btn btn-default"><?php echo safe_output($language->get('Cancel')); ?></a>
					</div>
					<div class="clearfix"></div>
				</div>
				<div class="panel-body">
						<button type="submit" id="delete" name="delete" class="btn btn-danger"><?php echo safe_output($language->get('Delete')); ?></button>
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
					<input class="form-control" type="text" name="name" value="<?php echo safe_output($item['name']); ?>" size="30" />
					</div>
					<div class="clearfix"></div>
				</div>
				<div class="panel-heading">
						<div class="pull-left">
					<h4><?php echo $language->get('Colour'); ?></h4>
					<input type="text" name="colour" id="cp1" value="<?php echo safe_output($item['colour']); ?>" maxlength="7" class="input-small form-control">
					</div>
					<div class="clearfix"></div>
				</div>

			</div>

			<div class="clearfix"></div>

		</div>

	</form>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>
