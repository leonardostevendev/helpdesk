<?php
include('includes/header.php');

if (!isset($_SESSION['install_data']) || ($_SESSION['install_data']['stage'] < 4)) {
	header('Location: index.php');
}

if (isset($_POST['next'])) {
	
	if (!empty($_POST['admin_name'])) {
		if (!empty($_POST['admin_username'])) {
			if (!empty($_POST['password']) && !empty($_POST['password'])) {
				if ($_POST['password'] === $_POST['password2']) {
					$ipm_install->set_form('admin_name', $_POST['admin_name']);
					$ipm_install->set_form('admin_email', $_POST['admin_email']);
					
					$ipm_install->set_form('admin_username', $_POST['admin_username']);
					$ipm_install->set_form('admin_password', $_POST['password']);
				
					
					$_SESSION['install_data']['stage'] = 5;
					header('Location: finish.php');
				}
				else {
					$message = 'Passwords do not match';
				}
			}
			else {
				$message = 'Password Missing';
			}
		}
		else {
			$message = 'Username Empty';
		}
	}
	else {
		$message = 'Name Empty';
	}	
}

include('includes/html-header.php');

?>

<div class="row">
	<div class="col-md-3">
		<div class="panel panel-default">
            <div class="panel-heading">
                <h4>Help</h4>
            </div>
            <div class="panel-body">
                <p><b>Admin Details</b>: This is the primary admin account.</p>
            </div>
		</div>

	</div>
	<div class="col-md-9">
		<div class="panel panel-default">
			<div class="panel-heading">
				<div class="pull-left">
					<h4>Step 4 - Administrator Account Details</h4>
				</div>
				<div class="clearfix"></div>
			</div>
			<div class="panel-body">		
				<?php if (isset($message)) { ?>
					<div class="alert alert-danger">
						<?php echo ipm_htmlentities($message); ?>
					</div>
				<?php } ?>
				<form method="post" action="<?php echo ipm_htmlentities($_SERVER['PHP_SELF']); ?>">
					<div class="form-group">		
						<div class="col-lg-5">	
							<p>Name<br /><input class="form-control" type="text" name="admin_name" value="<?php echo ipm_htmlentities($ipm_install->form_data('admin_name')); ?>" size="50" /></p>		
							<p>Email Address (optional)<br /><input class="form-control" type="text" name="admin_email" value="<?php echo ipm_htmlentities($ipm_install->form_data('admin_email')); ?>" size="50" /></p>		

							
							<p>Username<br /><input class="form-control"  type="text" name="admin_username" value="<?php echo ipm_htmlentities($ipm_install->form_data('admin_username')); ?>" size="50" /></p>		
							<p>Password<br /><input class="form-control" autocomplete="off" type="password" name="password" value="" size="50" /></p>		
							<p>Password Again<br /><input class="form-control"  autocomplete="off" type="password" name="password2" value="" size="50" /></p>		
						</div>
					</div>
					<div class="clearfix"></div>

					<div class="pull-right">
						<p class="seperator"><button type="submit" name="next" class="btn btn-primary">Install</button></p>
					</div>
				</form>
				
				<br />
				<p><a href="site.php" class="btn btn-default">Back</a></p>
			</div>
		</div>
					
	</div>
	<div class="clearfix"></div>
</div>

<?php
include('includes/html-footer.php');
?>