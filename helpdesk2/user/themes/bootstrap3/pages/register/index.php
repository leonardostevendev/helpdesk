<?php
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Register'));
$site->set_config('container-type', 'container');

if ($config->get('registration_enabled') && isset($_POST['register'])) {

	if (!empty($_POST['name'])) {
		if (!empty($_POST['email'])) {
			if (check_email_address($_POST['email'])) {
				if (!$users->check_email_address_taken(array('email' => $_POST['email']))) {
					if (!empty($_POST['username'])) {
						$_POST['username']	= strtolower($_POST['username']);
						if (!$users->check_username_taken(array('username' => $_POST['username']))) {
							if (!empty($_POST['password']) && ($_POST['password'] === $_POST['password2'])) {			
								if ($config->get('captcha_enabled') && (empty($_POST['captcha_input']) || strtoupper($_POST['captcha_input']) !== strtoupper($_SESSION['captcha_text']))) {
									$message = $language->get('Anti-Spam Text Incorrect');
								}
								else {	
									$custom_register['success'] = true;
									$plugins->run('submit_register_form_success_before_create_user', $custom_register);
									
									if ($custom_register['success']) {

										$id = $users->add(
											array(
												'name' 				=> $_POST['name'], 
												'email' 			=> $_POST['email'],
												'authentication_id' => 1,
												'allow_login'		=> 1,
												'username'			=> $_POST['username'],
												'password'			=> $_POST['password'],
												'group_id'			=> 1,
												'welcome_email'		=> 1
											)
										);
										
										$user_array['id']	= $id;					
										$plugins->run('submit_register_form_success_after_create_user', $user_array);
										unset($user_array);
										
										if ($auth->login(array('username' => $_POST['username'], 'password' => $_POST['password']))) {
											 header('Location: ' . $config->get('address') . '/tickets/add/');
											 exit;
										}
										else {
											$message = $language->get('Failed To Create Account');
										}
									}
									else {
										$message = $custom_register['message'];
									}
								}
							}
							else {
								$message = $language->get('Passwords Do Not Match');
							}
						}
						else {
							$message = $language->get('Username Invalid');
						}
					}
					else {
						$message = $language->get('Username Invalid');
					}
				}
				else{
					$message = $language->get('Email Address In Use');
				}
			}
			else {
				$message = $language->get('Email Address Invalid');
			}
		}
		else {
			$message = $language->get('Email Address Invalid');
		}
	}
	else {
		$message = $language->get('Please Enter A Name');
	}
}
$_SESSION['captcha_text'] = $captcha->get_random_text();

include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<div class="row">
	<form method="post" action="<?php echo safe_output($_SERVER['REQUEST_URI']); ?>">

		<div class="col-md-3">
			<div class="well well-sm">
	
			
				<?php if ($config->get('registration_enabled')) { ?>
					<div class="pull-right">
						<p><button type="submit" name="register" class="btn btn-primary"><?php echo safe_output($language->get('Register')); ?></button></p>
					</div>
				<?php } ?>
				
				<div class="pull-left">
					<h4><?php echo safe_output($language->get('Register')); ?></h4>
				</div>
	
				<div class="clearfix"></div>
				<br />
				
				<p><?php echo safe_output($language->get('You must have an account before you can submit a ticket. Please register here.')); ?></p>
				<div class="clearfix"></div>
				
				<?php if ($config->get('facebook_enabled') && $config->get('auth_facebook_create_accounts')) { ?>
					<br />
					<p><?php echo safe_output($language->get('If you would prefer to register with a Facebook account please click the button below.')); ?></p>
					<div class="pull-right">
						<a href="<?php echo safe_output($config->get('address')); ?>/register/facebook/" class="btn btn-default"><?php echo safe_output($language->get('Register with a Facebook account')); ?></a>
					</div>
					<div class="clearfix"></div>
				<?php } ?>
			
			</div>
			
			<?php $plugins->run('view_register_sidebar_finish'); ?>

		</div>
		
		<div class="col-md-9">

			<?php if (isset($message)) { ?>
				<div class="alert alert-danger">
					<a href="#" class="close" data-dismiss="alert">&times;</a>
					<?php echo html_output($message); ?>
				</div>
			<?php } ?>
			
			<div class="well well-sm">
				<?php if ($config->get('registration_enabled')) { ?>

					<div class="col-lg-6">								

						<p><?php echo safe_output($language->get('Name')); ?><br /><input class="form-control" required type="text" name="name" value="<?php if (isset($_POST['name'])) echo safe_output($_POST['name']); ?>" /></p>
						<p><?php echo safe_output($language->get('Username')); ?><br /><input class="form-control" required type="text" name="username" value="<?php if (isset($_POST['username'])) echo safe_output($_POST['username']); ?>" /></p>
						<p><?php echo safe_output($language->get('Email')); ?><br /><input class="form-control" required type="email" name="email" value="<?php if (isset($_POST['email'])) echo safe_output($_POST['email']); ?>" /></p>					
						<p><?php echo safe_output($language->get('Password')); ?><br /><input class="form-control" required type="password" name="password" value="" autocomplete="off" /></p>
						<p><?php echo safe_output($language->get('Password Again')); ?><br /><input class="form-control" required type="password" name="password2" value="" autocomplete="off" /></p>
						
						<?php $plugins->run('view_register_form'); ?>

						<?php if ($config->get('captcha_enabled')) { ?>
							<p><?php echo safe_output($language->get('Anti-Spam Image')); ?><br /><img src="<?php echo safe_output($config->get('address')); ?>/captcha/" alt="captcha_image" /></p>
							<p><?php echo safe_output($language->get('Anti-Spam Text')); ?><br /><input class="form-control" type="text" name="captcha_input" value="" autocomplete="off" /></p>
						<?php } ?>	
					</div>
				<?php } else { ?>
					<div class="alert alert-danger">
						<a href="#" class="close" data-dismiss="alert">&times;</a>
						<?php echo html_output($language->get('Account Registration Is Disabled.')); ?>
					</div>
				<?php } ?>

				<div class="clearfix"></div>

			</div>
		</div>
	
	</form>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>