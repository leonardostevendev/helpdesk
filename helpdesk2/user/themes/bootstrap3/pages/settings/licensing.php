<?php
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

if (SAAS_MODE) {
	header('Location: ' . $config->get('address') . '/');
	exit;
}

$site->set_title($language->get('Licensing'));
$site->set_config('container-type', 'container');

if (!$auth->can('manage_system_settings')) {
	header('Location: ' . $config->get('address') . '/');
	exit;
}


if (isset($_POST['save'])) {

	$config->set('license_key', $_POST['license_key']);
	
	$message = $language->get('Settings Saved');
}


include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<div class="row">

	<form method="post" action="<?php echo safe_output($_SERVER['REQUEST_URI']); ?>">

		<div class="col-md-3">
			<div class="panel panel-default">
                <div class="panel-heading">
                    <div class="pull-left">
                        <h4><?php echo safe_output($language->get('Licensing')); ?></h4>
                    </div>

                    <div class="pull-right">
                        <button type="submit" name="save" class="btn btn-success"><?php echo safe_output($language->get('Save')); ?></button>
                    </div>
                    <div class="clearfix"></div>
                </div>
				<div class="panel-body">
                    <p><?php echo safe_output($language->get('Your License Key can be found in your CodeCanyon account. Select Downloads and then select "License Certificate", inside this file is a key called "Item Purchase Code", this is your License Key.')); ?></p>
                    <div class="clearfix"></div>	


                    <div class="pull-right">
                        <p><a href="<?php echo $config->get('address'); ?>/update/" class="btn btn-primary"><?php echo safe_output($language->get('Check for updates')); ?></a></p>
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
                    <h4><?php echo safe_output($language->get('License Key')); ?></h4>
                </div>
                <div class="panel-body">
                    <input class="form-control" type="text" name="license_key" size="30" placeholder="Your License Key" value="<?php echo safe_output($config->get('license_key')); ?>" />
                    <div class="clearfix"></div>
                </div>
			</div>
		</div>
	</form>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>