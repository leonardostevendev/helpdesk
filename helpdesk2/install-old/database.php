<?php
include('includes/header.php');

if (!isset($_SESSION['install_data']) || ($_SESSION['install_data']['stage'] < 2)) {
	header('Location: index.php');
}

if (isset($_POST['next']) && (!file_exists(ROOT . '/user/settings/config.php'))) {
	if (!empty($_POST['site_salt'])) {
		if (!empty($_POST['dbhost'])) {
			if (!empty($_POST['dbname'])) {
				if (!empty($_POST['dbusername'])) {
				
					$ipm_install->set_form('dbhost', 		$_POST['dbhost']);
					$ipm_install->set_form('dbname', 		$_POST['dbname']);
					$ipm_install->set_form('dbusername', 	$_POST['dbusername']);				
					$ipm_install->set_form('dbpassword', 	$_POST['dbpassword']);
					$ipm_install->set_form('site_salt', 	$_POST['site_salt']);
					$ipm_install->set_form('encryption_key', 	ipm_rand_str());
					$ipm_install->set_form('storage_enabled', 	0);
					$ipm_install->set_form('storage_path', 	$ipm_install->storage_path());
				
					
					$database_error = TRUE;
					
					try {
						$ipm_install_db = new PDO('mysql:host=' . $ipm_install->form_data('dbhost') . ';dbname=' . $ipm_install->form_data('dbname'), $ipm_install->form_data('dbusername'), $ipm_install->form_data('dbpassword'), array(PDO::ATTR_PERSISTENT => true));
						
						if ($ipm_install->test_is_installed($ipm_install_db)) {
							$database_message 	= 'The connection was successful but database selected is not empty and cannot be used.';
							$database_error 	= TRUE;
						}
						else {
							$database_error = FALSE;
						}
						
					}
					catch (PDOException $e) {
						$database_message = $e->getMessage();
						$database_error = TRUE;
					}
					
					if (!$database_error) {
						$_SESSION['install_data']['stage'] = 3;
						header('Location: site.php');
					}
					else {
						$message = $database_message;
					}
				}
				else {
					$message = 'Database Username Empty';
				}
				
			}
			else {
				$message = 'Database Name Empty';
			}

		}
		else {
			$message = 'Database Host Empty';
		}
	}
	else {
		$message = 'The site salt must be set.';
	}
	
}

include('includes/html-header.php');

?>

<div class="row">
	<div class="col-md-3">
		<div class="well well-sm">
			<h4>Help</h4>
			<p><b>Database Host</b>: The fully qualified domain name or IP address of your MySQL server. For single server installs this is likely to be "localhost".</p>
			<br />
			<p><b>Database Name</b>: The name of the MySQL database that Tickets will be installed in to. This should be an empty database that has already been created.</p>
			<br />
			<p><b>Database Username</b>: The username used to connect to the database host. This user must have full permissions to the database listed in Database Name.</p>
			<br />
			<p><b>Database Password</b>: The password used to connect to the database host. This password should not be used for any other purpose.</p>
			<br />
			<p><b>Site Salt</b>: This value is used as a salt to hash client passwords (for security). Simply type a random set of numbers and letters (e.g. 76vbdsygu3uTgy3U8!#) or use the random string already generated. Please do not use the "'" character. This value will be stored in your config file. Please keep it secure.</p>
			<br />
		</div>

	</div>
	<div class="col-md-9">
		<div class="panel panel-default">
			<div class="panel-heading">
				<div class="pull-left">
					<h1 class="panel-title">Step 2 - Database Details</h1>
				</div>
				<div class="clearfix"></div>
			</div>
			<div class="panel-body">
			
				<?php if (file_exists(ROOT . '/user/settings/config.php')) { ?>
					<div class="alert alert-danger">
						The config file "user/settings/config.php" already exists, the install cannot continue.
						<br />
						You must delete this file if you wish to start a new install.
					</div>
				<?php } else { ?>
					<?php if (isset($message)) { ?>
						<div class="alert alert-danger">
							<?php echo ipm_htmlentities($message); ?>
						</div>
					<?php } ?>
					<form method="post" action="<?php echo ipm_htmlentities($_SERVER['PHP_SELF']); ?>">
						<div class="form-group">		
							<div class="col-lg-5">	
								<p>Database Host<br /><input class="form-control" autocomplete="off" type="text" name="dbhost" value="<?php echo ipm_htmlentities($ipm_install->form_data('dbhost')); ?>" size="50" /></p>		
								<p>Database Name<br /><input class="form-control" autocomplete="off" type="text" name="dbname" value="<?php echo ipm_htmlentities($ipm_install->form_data('dbname')); ?>" size="50" /></p>		
								<p>Database Username<br /><input class="form-control" autocomplete="off" type="text" name="dbusername" value="<?php echo ipm_htmlentities($ipm_install->form_data('dbusername')); ?>" size="50" /></p>		
								<p>Database Password<br /><input class="form-control" autocomplete="off" type="password" name="dbpassword" value="<?php echo ipm_htmlentities($ipm_install->form_data('dbpassword')); ?>" size="50" /></p>		
								
								<p>Site Salt<br /><input class="form-control" autocomplete="off" type="text" name="site_salt" value="<?php echo ipm_htmlentities($ipm_install->form_data('site_salt', ipm_rand_str())); ?>" size="50" /></p>		
							</div>	
						</div>
						<div class="clearfix"></div>
						
						<div class="pull-right">
							<button type="submit" name="next" class="btn btn-primary">Next</button>
						</div>
					</form>
				<?php } ?>
				
				<br />
				<p><a href="check_system.php" class="btn btn-default">Back</a></p>
			</div>
		</div>
				
	</div>
	<div class="clearfix"></div>
</div>

<?php
include('includes/html-footer.php');
?>