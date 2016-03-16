<?php
include('includes/header.php');

if (!isset($_SESSION['install_data']) || ($_SESSION['install_data']['stage'] < 5)) {
	header('Location: index.php');
}

$ipm_install->connect_db();

include('includes/html-header.php');

?>

<div class="row">
	<div class="col-md-3">
		<div class="panel panel-default">
            <div class="panel-heading">
                <h4>Help</h4>
            </div>
            <div class="panel-body">
                <p>The install process may take 0-1 minutes, please do not leave this page until the install is completed.</p>
            </div>
		</div>

	</div>
	<div class="col-md-9">
		<div class="panel panel-default">
			<div class="panel-heading">
				<div class="pull-left">
					<h4>Step 5 - Installing</h4>
				</div>
				<div class="clearfix"></div>
			</div>
			<div class="panel-body">	
				<?php if (!$ipm_install->is_installed()) { ?>
					<?php $ipm_install->install_db(); ?>
					<div class="alert alert-success">
						The database install has been completed.
					</div>
					<?php if (false == $config_result = $ipm_install->write_htaccess()) { ?>
						<div class="alert alert-warning">
							Unable to write .htaccess file.
						</div>
					<?php } else { ?>
						<div class="alert alert-success">
							The .htaccess file has been written successfully.
						</div>
					<?php } ?>
					
					<?php if (false == $config_result = $ipm_install->write_config()) { ?>
						<?php echo $config_result; ?>
						<div class="alert alert-danger">
							Unable to write config file.
						</div>
					<?php } else { ?>
						<div class="alert alert-success">
							The config file has been written successfully.
						</div>
						<div class="alert alert-success">
							The install has been completed.
						</div>
					<?php } ?>
					<?php session_destroy(); ?>
					<div class="alert alert-warning">
						If you get a 404 or 500 error after the install please check to ensure the Apache Mod Rewrite module is enabled.
						<strong><a href="https://portal.dalegroup.net/public/kb_view/1/">More Info</a></strong>.
					</div>					
					<div class="alert alert-warning">
						You should now delete the install/ folder, this folder is only required for the first install.
					</div>
					<p><a href="../" class="btn btn-primary">Login</a></p>
				<?php } else { ?>
					<div class="alert alert-danger">
						The database selected is not empty and cannot be used.
					</div>
				<?php } ?>
			</div>
		</div>
	</div>
	<div class="clearfix"></div>
</div>

<?php
include('includes/html-footer.php');
?>