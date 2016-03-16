<?php
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Login'));
$site->set_config('container-type', 'container');

if (isset($_POST['submit'])) {
	if ($auth->login(array('username' => $_POST['username'], 'password' => $_POST['password']))) {
		if (isset($_SESSION['page'])) {
			header('Location: ' . safe_output($_SESSION['page']));
		}
		else {
			header('Location: ' . $config->get('address') . '/');
		}
		exit;
	}
	else {
		$message = $language->get('Login Failed');
	}
}
else {
	if ($config->get('facebook_enabled')) {
		if (isset($_SESSION['fb_'. $config->get('facebook_app_id') .'_user_id'])) {
			$message = 'Your current Facebook profile is not linked with ' . $config->get('name') . '. Please login with your local details.';
		}
	}
}

$login_message = $config->get('login_message');

?>

<!-- Leo -->

<?php
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('New Guest Ticket'));
$site->set_config('container-type', 'container');

if (isset($_POST['add'])) {
	if (!empty($_POST['name'])) {
		if (!empty($_POST['subject'])) {		
			
			if (check_email_address($_POST['email'])) {
			
				if ($config->get('captcha_enabled') && (empty($_POST['captcha_input']) || strtoupper($_POST['captcha_input']) !== strtoupper($_SESSION['captcha_text']))) {
					$message = $language->get('Anti-Spam Text Incorrect');
				}
				else {
						
					$upload_file 	= false;
					$files_upload 	= array();
					
					if ($config->get('storage_enabled')) {
						if (isset($_FILES['file']) && is_array($_FILES['file'])) {
							$files_array = rearrange($_FILES['file']);	
							foreach($files_array as $file) {
								if ($file['size'] > 0) {
									$upload_file = true;
									$file_array['file']			= $file;
									$file_array['name']			= $file['name'];	
									$files_upload[] 			= $storage->upload($file_array);
								}
							}
						}
					}
								
					if ($upload_file && empty($files_upload)) {
						$message = $language->get('File Upload Failed. Ticket Not Submitted.');
					}
					else {
						$access_key = rand_str(32);
						
						$add_array = 
							array(
								'subject'			=> $_POST['subject'],
								'description'		=> $_POST['description'],
								'priority_id'		=> (int) $_POST['priority_id'],
								'name'				=> $_POST['name'],
								'email'				=> $_POST['email'],
								'access_key'		=> $access_key,
								'html'				=> 0
							);
						
						if ($config->get('html_enabled')) {
							$add_array['html'] = 1;
						}
						
						$clients 					= $users->get(array('email' => $_POST['email'], 'limit' => 1));
						
						if (empty($clients)) {							
							if ($config->get('guest_portal_auto_create_users') == 1) {

								$add_user_array['group_id'] 		= 0;							
								$add_user_array['allow_login'] 		= 0;
								$add_user_array['welcome_email'] 	= 0;
								$add_user_array['name'] 			= $_POST['email'];
								if (!empty($_POST['name'])) {
									$add_user_array['name'] 		= $_POST['name'];
								}
								$add_user_array['email'] 			= $_POST['email'];
								$add_user_array['match_tickets'] 	= true;
								$add_user_array['company_id']		= 0;
								
								$plugins->run('guest_portal_create_user_company_id', $add_user_array);
							
								$add_array_id 						= $users->add($add_user_array);

								$clients 						= $users->get(array('id' => $add_array_id, 'limit' => 1));
							}
						}
						
						if (empty($clients)) {
							$add_array['user_id'] 	= 0;
						}
						else {
							$add_array['user_id']	= $clients[0]['id'];
						}
						
						$add_array['department_id'] = 1;
						if (isset($_POST['department_id']) && ($_POST['department_id'] != '')) {
							if ($ticket_departments->count(array('id' => $_POST['department_id'], 'public_view' => 1)) > 0) {
								$add_array['department_id']	= (int) $_POST['department_id'];
							}
						}
							
						if (!empty($files_upload)) {
							$add_array['attach_file_ids']	= $files_upload;
						}
							
						$id = $tickets->add($add_array);
						
						foreach($_POST as $index => $value){
							if(strncasecmp($index, 'field-', 6) === 0) {
								$group_index = explode('-', $index);
								$group_id = (int) $group_index[1];
								if ($group_id !== 0) {
																	
									$edit_array['ticket_field_group_id']	= $group_id;
									$edit_array['ticket_id']				= $id;
								
									if (is_array($value)) {
										$values = array();
										foreach($value as $check_index => $check_item) {
											$values[] = $check_item;
										}
										$edit_array['value']				= json_encode($value);								
									}
									else {
										$edit_array['value']				= $value;
									}
												
								
									$ticket_custom_fields->add_value($edit_array);
									unset($edit_array);
								}
							}			
						}
									
						header('Location: ' . $config->get('address') . '/guest/ticket_view/' . $id . '/?access_key=' . $access_key);
						exit;
					}
				}
			}
			else {
				$message = $language->get('Email Address Invalid');
			}
		}
		else {
			$message = $language->get('Subject Empty');
		}
	}
	else {
		$message = $language->get('Name Empty');
	}
}

$_SESSION['captcha_text'] = $captcha->get_random_text();

$priorities 	= $ticket_priorities->get(array('enabled' => 1));
$departments	= $ticket_departments->get(array('enabled' => 1, 'public_view' => 1));


include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<script>
$(document).ready(function(){
var tooltipAll = 
	{
"test":"ayudaTest",
"Plataforma":"Selecciona la plataforma correspondiente a tu solicitud SAGA: Para la plataforma de Gestion Academica o LMS: Para el aula virtual",
"Usuario":"Digite su usuario o su numero de c√dula",
"Curso":"jhgkjhgkjhgkjhg"


	};
var tooltipTitles=[];
for (var i = 0 ; i < Object.keys(tooltipAll).length; i++) {
    tooltipTitles[i] = Object.keys(tooltipAll)[i];
};

$("#custom-forms p").each(function(index){
var str = $(this).text().trim().split(" ")
console.log(str)
	if($.inArray(str[0], tooltipTitles)>=0){
		$(this).find(".ayuda").html('<a href="#" title="" data-toggle="tooltip" data-placement="top" class="catedra-tooltip" data-original-title="'+
			tooltipAll[str[0]]+'"><i class="glyphicon glyphicon-question-sign"></i></a>');
	}
})
$('[data-toggle="tooltip"]').tooltip(); 
})
</script>

<div class="row">

	<form method="post" enctype="multipart/form-data" action="<?php echo safe_output($_SERVER['REQUEST_URI']); ?>">
	
		<div class="col-md-3">
			<div class="well well-sm">
				<div class="pull-left">
					<h4><?php echo safe_output($language->get('New Ticket')); ?></h4>
				</div>
				<div class="pull-right">
					<p><button type="submit" name="add" class="btn btn-primary"><?php echo safe_output($language->get('Add Ticket')); ?></button></p>
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

			<div class="col-md-6 marginless">

				<div class="well well-sm">	
					
					<p><?php echo safe_output($language->get('Subject')); ?> <a href="#" title="" data-toggle="tooltip" data-placement="top" class="catedra-tooltip" data-original-title="<?php echo safe_output($language->get('HelpSubject'));?>"><i class="glyphicon glyphicon-question-sign"></i></a> <br /><input type="text" required class="form-control" name="subject" value="<?php if (isset($_POST['subject'])) echo safe_output($_POST['subject']); ?>" size="50" /></p>		

					<?php if (count($departments) > 1) { ?>
						<p><?php echo safe_output($language->get('Department')); ?><br />
						<select name="department_id">
							<?php foreach ($departments as $department) { ?>
							<option value="<?php echo (int) $department['id']; ?>"<?php if (isset($_POST['department_id']) && ($_POST['department_id'] == $department['id'])) { echo ' selected="selected"'; } ?>><?php echo safe_output($department['name']); ?></option>
							<?php } ?>
						</select></p>
					<?php } ?>
					
					<p><?php echo safe_output($language->get('Priority')); ?> <a href="#" title="" data-toggle="tooltip" data-placement="top" class="catedra-tooltip" data-original-title="<?php echo safe_output($language->get('HelpPriority'));?>"><i class="glyphicon glyphicon-question-sign"></i></a> 
					<br />
					<select name="priority_id">
						<?php foreach ($priorities as $priority) { ?>
						<option value="<?php echo (int) $priority['id']; ?>"<?php if (isset($_POST['priority_id']) && ($_POST['priority_id'] == $priority['id'])) { echo ' selected="selected"'; } ?>><?php echo safe_output($priority['name']); ?></option>
						<?php } ?>
					</select></p>

					<div class="clearfix"></div>

				</div>
			</div>
			
						
			<div class="col-md-5 marginless">
				<div class="well well-sm">	
				
					<p><?php echo safe_output($language->get('Name')); ?> <a href="#" title="" data-toggle="tooltip" data-placement="top" class="catedra-tooltip" data-original-title="<?php echo safe_output($language->get('HelpName'));?>"><i class="glyphicon glyphicon-question-sign"></i></a> <br /><input type="text" required class="form-control" name="name" value="<?php if (isset($_POST['name'])) echo safe_output($_POST['name']); ?>" size="50" /></p>		
					<p><?php echo safe_output($language->get('Email')); ?> <span><a href="#" title="" data-html="true"  data-toggle="tooltip" data-placement="top" class="catedra-tooltip" data-original-title="<?php echo safe_output($language->get('HelpMail'));?>"><i class="glyphicon glyphicon-question-sign"></i></a>
					</span><br /><input type="text" required class="form-control" name="email" value="<?php if (isset($_POST['email'])) echo safe_output($_POST['email']); ?>" size="50" /></p>		

				</div>
			</div>

			<div class="col-md-5 marginless">
                <div class="well well-sm">       
                  	<div id="custom-forms">
                  		<?php $site->display_custom_field_forms(); ?>                          	
                  	</div>                                  	                 
                </div> 
            </div>

           		<div class="well well-sm col-md-6 well well-sm">          			
                                <?php if ($config->get('storage_enabled')) { ?>
                                <p><?php echo safe_output($language->get('Attach File')); ?> <span><a href="#" title="" data-toggle="tooltip" data-placement="top" class="catedra-tooltip" data-original-title="<?php echo safe_output($language->get('HelpAttach'));?>"><i class="glyphicon glyphicon-question-sign"></i></a>
								</span></p>
                                <?php } ?>                          
                                <?php if ($config->get('storage_enabled')) { ?>
                                <div class="col-lg-4 nopad-left">
                                    <div class="pull-left"><input name="file[]" type="file" /></div>
                                    <div class="pull-right"><a href="#" id="add_extra_file"><span class="glyphicon glyphicon-plus"></span><?php echo safe_output($language->get(' Attach More')); ?></a></div>
                                    <div id="attach_file_area"></div>
                                </div>
                                <?php } ?>  
           		</div>           
			<div class="clearfix"></div>

			<div class="col-md-12 marginless">
			
				<div class="well well-sm">	
					<p><?php echo safe_output($language->get('Description')); ?>				
					<br />
					<textarea class="wysiwyg_enabled" name="description" cols="80" rows="12"><?php if (isset($_POST['description'])) echo safe_output($_POST['description']); ?></textarea>
					</p>
					
										
					<?php if ($config->get('captcha_enabled')) { ?>
						<br />
						<p><?php echo safe_output($language->get('Anti-Spam Image')); ?><br /><img src="<?php echo safe_output($config->get('address')); ?>/captcha/" alt="captcha_image" /></p>
						<p><?php echo safe_output($language->get('Anti-Spam Text')); ?><br /><input class="form-control" type="text" name="captcha_input" value="" autocomplete="off" /></p>
					<?php } ?>
								
				</div>
				
			</div>
			<div class="clearfix"></div>

			<div class="col-md-12">

				<div class="pull-right">
					<p><button type="submit" name="add" class="btn btn-primary"><?php echo safe_output($language->get('Add Ticket')); ?></button></p>
				</div>
			</div>
				
		</div>

	</form>
</div>




<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>
