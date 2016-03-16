<?php
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Facebook'));
$site->set_config('container-type', 'container');

$facebook_user = $auth_facebook->get_user(array('url' => $config->get('address') . '/profile/facebook/'));


if (isset($_POST['link_profile']) && !empty($_POST['facebook_id']) && $facebook_user && ($facebook_user->getId() == $_POST['facebook_id'])) {
	if ($auth_facebook->link_profile($_SESSION['facebook_id'])) {
		$message = 'Profile Linked.';
	}
	else {
		$message = 'An existing account already has this Profile linked.';
	}
}

if (isset($_POST['remove_profile'])) {
	$update_array['facebook_id'] 	= '';
	$update_array['id']				= $auth->get('id');
	
	$users->edit($update_array);
	
	$auth->load();
	
	$message = $language->get('Profile removed. Please logout to complete this process.');
}

$stored_facebook_id = $auth->get('facebook_id');

$loginUrl = $auth_facebook->get_login_url(array('url' => $config->get('address') . '/profile/facebook/'));

include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<div class="row">
	<div class="col-md-3">
		<div class="well well-sm">
			<div class="pull-left">
				<h4><?php echo safe_output($language->get('Facebook')); ?></h4>
			</div>
			
			<div class="pull-right">
				<p><a href="<?php echo safe_output($config->get('address')); ?>/profile/" class="btn btn-default"><?php echo safe_output($language->get('Cancel')); ?></a></p>
			</div>		
			<div class="clearfix"></div>
			
			<label class="left-result"><?php echo safe_output($language->get('Name')); ?></label>
			<p class="right-result">
				<?php echo safe_output(ucwords($auth->get('name'))); ?>
			</p>	
			<div class="clearfix"></div>
			
			<label class="left-result"><?php echo safe_output($language->get('Username')); ?></label>
			<p class="right-result">
				<?php echo safe_output($auth->get('username')); ?>
			</p>					
			<div class="clearfix"></div>
			
			<label class="left-result"><?php echo safe_output($language->get('Email')); ?></label>
			<p class="right-result">
				<?php echo safe_output($auth->get('email')); ?>
			</p>	

			<div class="clearfix"></div>
			<?php if ($config->get('gravatar_enabled')) { ?>
			<label class="left-result"><?php echo safe_output($language->get('Gravatar')); ?></label>
			<p class="right-result">
				<?php $gravatar->setEmail($auth->get('email')); ?>
				<img src="<?php echo $gravatar->getUrl(); ?>" alt="Gravatar" />
			</p>
			<?php } ?>
			<div class="clearfix"></div>
			
				
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
				<div class="pull-left">
					<h1 class="panel-title"><?php echo safe_output($language->get('Link Your Facebook Profile')); ?></h1>
				</div>
				<div class="clearfix"></div>
			</div>
			<div class="panel-body">

				<p><?php echo safe_output($language->get('This page allows you to link your Facebook profile with your account here at')); ?> <?php echo safe_output($config->get('name')); ?>.</p>
				
				<p><?php echo safe_output($language->get('Linking your Facebook profile allows you login with your Facebook details.')); ?></p>
				
				<p><?php echo safe_output($config->get('name')); ?> <?php echo safe_output($language->get('cannot store your Facebook password or access information that you have not allowed (via your Facebook privacy settings), so your Facebook account will be safe.')); ?></p>

				<?php if ($facebook_user) { ?>
					<?php if (empty($stored_facebook_id)) { ?>
						<form method="post" action="<?php echo safe_output($_SERVER['REQUEST_URI']); ?>">
							<p><?php echo safe_output($language->get('Your Facebook Profile')); ?></p>
							<p><img src="https://graph.facebook.com/<?php echo safe_output($facebook_user->getId()); ?>/picture" /><br /><?php echo safe_output($auth->get('name')); ?><br /><?php echo safe_output($auth->get('username')); ?></p>
							<p><input type="hidden" name="facebook_id" value="<?php echo safe_output($facebook_user->getId()); ?>" /><button name="link_profile" type="submit" class="btn btn-primary"><?php echo safe_output($language->get('Link This Profile')); ?></button></p>
						</form>
					<?php } else if ($facebook_user == $stored_facebook_id) { ?>
						<form method="post" action="<?php echo safe_output($_SERVER['REQUEST_URI']); ?>">
							<p><?php echo safe_output($language->get('The following Facebook profile is linked to this account')); ?></p>
							<p><img src="https://graph.facebook.com/<?php echo safe_output($facebook_user); ?>/picture" /><br /><?php echo safe_output($auth->get('name')); ?><br /><?php echo safe_output($auth->get('username')); ?></p>
							<p><button name="remove_profile" type="submit" class="btn btn-danger"><?php echo safe_output($language->get('Remove Profile')); ?></button></p>
						</form>
					<?php } else { ?>
						<form method="post" action="<?php echo safe_output($_SERVER['REQUEST_URI']); ?>">
						<p><?php echo safe_output($language->get('You already have a linked Facebook profile that is different to the profile you are logged in with. You must remove the existing (old) profile first.')); ?></p>
							<p><button name="remove_profile" type="submit" class="btn btn-default"><?php echo safe_output($language->get('Remove Existing Profile')); ?></button></p>
						</form>
					<?php } ?>
				<?php } else { ?>
					<?php if (empty($stored_facebook_id)) { ?>
						<p><a href="<?php echo $loginUrl; ?>"><img src="<?php echo $config->get('address'); ?>/user/themes/<?php echo safe_output(CURRENT_THEME); ?>/images/connect_with_facebook.gif"></a></p>
					<?php } else { ?>
						<form method="post" action="<?php echo safe_output($_SERVER['REQUEST_URI']); ?>">
							<p><?php echo safe_output($language->get('The following Facebook profile is linked to this account')); ?></p>
							<p><img src="https://graph.facebook.com/<?php echo safe_output($stored_facebook_id); ?>/picture" /><br /><?php echo safe_output($auth->get('name')); ?><br /><?php echo safe_output($auth->get('username')); ?></p>
							<p><button name="remove_profile" type="submit" class="btn btn-danger"><?php echo safe_output($language->get('Remove Profile')); ?></button></p>
						</form>
					<?php } ?>
				<?php } ?>

				
				<div class="clearfix"></div>
			
			</div>
		</div>
		
	</div>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>