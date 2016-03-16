<?php
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Add API Key'));
$site->set_config('container-type', 'container');

if (!$auth->can('api_access')) {
	header('Location: ' . $config->get('address') . '/');
	exit;
}

if (isset($_POST['add'])) {
	if (!empty($_POST['name'])) {	
		$add_array['name']				= $_POST['name'];
		$add_array['date_added'] 		= datetime();
		$add_array['key']				= uuid();
		$add_array['user_id']			= $auth->get('id');

		$id = $user_api_keys->add(array('columns' => $add_array));
	
		header('Location: ' . $config->get('address') . '/profile/');

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
			<div class="well well-sm">
				<div class="pull-right">
					<p>
					<button type="submit" name="add" class="btn btn-primary"><?php echo safe_output($language->get('Add')); ?></button>
					<a href="<?php echo $config->get('address'); ?>/profile/" class="btn btn-default"><?php echo safe_output($language->get('Cancel')); ?></a>
					</p>
				</div>
				
				<div class="pull-left">
					<h4><?php echo safe_output($language->get('API Key')); ?></h4>
				</div>
							

					
				<div class="clearfix"></div>	
				
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
					
					<p><?php echo $language->get('Name'); ?><br /><input class="form-control" type="text" name="name" value="<?php if (isset($_POST['name'])) echo safe_output($_POST['name']); ?>" size="30" /></p>

											
				</div>
				<div class="clearfix"></div>	

			</div>
				
		</div>

	</form>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>