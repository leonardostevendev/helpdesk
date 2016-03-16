<?php
include('includes/header.php');

include('includes/html-header.php');

?>
<div class="row">
	<div class="col-md-3">
		<div class="panel panel-default">
            <div class="panel-heading">
                <h4>Help</h4>
            </div>
            <div class="panel-body">
			 <p>This section will include tips that will be useful during the install.</p>
            </div>
		</div>
		<div class="clearfix"></div>
		<div class="panel panel-default">
            <div class="panel-heading">
                <h4>Support</h4>
            </div>
            <div class="panel-body">
                <p>Please sign up to the Dalegroup support portal if you require assistance.</p>
                <div class="pull-right">
                    <a href="http://portal.dalegroup.net/" class="btn btn-primary">Dalegroup Portal</a>
                </div>
            </div>

		</div>
		<div class="clearfix"></div>
	</div>
	<div class="col-md-9">
	
		<div class="panel panel-default">
			<div class="panel-heading">
                <div class="panel-heading">
					<h4>Welcome</h4>
                    <div class="clearfix"></div>
                </div>
			</div>
			<div class="panel-body">
				<p>Welcome to the Dalegroup Tickets installer.</p>
				<p>This installer is designed to setup a new copy of Tickets. It will create the configuration file (config.php), the MySQL database and the hypertext access file (.htaccess).</p>
				<br />
				<p>Please make sure you have read the documentation and agreed to the license before you start.</p>
				<p><a href="../documentation/" class="btn btn-info">Documentation &amp; License</a></p>
				<br />
				
				<div class="alert alert-warning">
					Although there is no License Key this application is not free.
					<strong>You may install a single copy for each purchase from CodeCanyon.</strong> Please do not share this application.
				</div>
			
				<div class="alert alert-warning">
					Using HTTPs/SSL will increase the security of this system (you can setup in the Settings page after install).
				</div>
				

				<div class="clearfix"></div>
				<br />
				<div class="pull-right">
					<p><a href="check_system.php" class="btn btn-primary">Next</a></p>
				</div>
				<div class="clearfix"></div>
			</div>
		</div>	

	</div>
	<div class="clearfix"></div>
</div>
<?php include('includes/html-footer.php'); ?>