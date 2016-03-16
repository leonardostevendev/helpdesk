<?php
/*
	Live Chat plugin for Tickets.
	Copyright Dalegroup Pty Ltd 2014
	
	You may use this code to help write your own plugins for Ticlets.
	
	If you do write any plugins we suggest submitting them to Codecanyon.
*/

/*
	Required.
	You must include these two lines at the start of your plugin.
*/
namespace sts\plugins;
use sts;

/*
	The class name must be the same as your plugin file name.
	We recommend you use a prefix for your class and function names.
	
	bbawesome.plugin.php would have the class bbawesome
	
	Dalegroup Pty Ltd does not use prefixes, you should!
*/
class livechat {

	private $current_item;

	function __construct() {
		/*
			This will get called on the plugins settings page.
			Please use the load method for startup and not this constructor.
		*/
	}
	
	/*
		This method is used to get the plugin details.
		It is required.
	*/
	public function meta_data() {
		$info = array(
			'name' 				=> 'Live Chat',
			'version' 			=> '5.0',
			'description'		=> 'This plugins gives Tickets a Live Chat system.',
			'website'			=> 'http://codecanyon.net/item/tickets/2478843?ref=michaeldale',
			'author'			=> 'Michael Dale',
			'author_website'	=> 'http://michaeldale.com.au/',
			'update_check_url'	=> 'http://api.apptrack.com.au/api/',
			'application_id'	=> 20
		);

		return $info;
	}
	
	/*
		This function is called on each page load if the plugin is enabled.
		It is required.
	*/
	public function load() {
		
	
		//this is how you get an existing class
		$plugins 			= &sts\singleton::get('sts\plugins');	

		include('livechat_support.class.php');
		$livechat_support 	= &sts\singleton::get(__NAMESPACE__ . '\livechat_support');		
		
		include('live_chat.class.php');
		$live_chat 		= &sts\singleton::get(__NAMESPACE__ . '\live_chat');		

		include('chatmessage_item.class.php');
		$chatmessage_item 	= &sts\singleton::get(__NAMESPACE__ . '\chatmessage_item');		
		
		/*
			If you are using new database tables make sure you add them!
			In future table prefixes might be supported, this code will support it :)
		*/
		$tables 		= &sts\singleton::get('sts\tables');
		$tables->add_table('chat_messages');
		
		//makes sure that the database is installed 
		$livechat_support->make_installed();	
				
		$auth 		= &sts\singleton::get('sts\auth');
		
		
		if ($auth->can('manage_livechat')) {
		
			//This hooks into the menu system
			$plugins->add(
				array(
					'plugin_name'	=> __CLASS__,
					'task_name'		=> __CLASS__ . '_html_header_nav_start',
					'section'		=> 'html_header_nav_start',
					'method'		=> array($this, 'html_header_nav_start')
				)
			);
		
			/*
				View Chat Index
			*/

			//title
			$plugins->add(
				array(
					'plugin_name'	=> __CLASS__,
					'task_name'		=> __CLASS__ . '_plugin_page_header_livechat',
					'section'		=> 'plugin_page_header_livechat',
					'method'		=> array($this, 'plugin_page_header_livechat')
				)
			);
			
			//html
			$plugins->add(
				array(
					'plugin_name'	=> __CLASS__,
					'task_name'		=> __CLASS__ . '_plugin_page_body_livechat',
					'section'		=> 'plugin_page_body_livechat',
					'method'		=> array($this, 'plugin_page_body_livechat')
				)
			);
			
					
			/*
				View Chat
			*/

			//title
			$plugins->add(
				array(
					'plugin_name'	=> __CLASS__,
					'task_name'		=> __CLASS__ . '_plugin_page_header_livechat_view',
					'section'		=> 'plugin_page_header_livechat_view',
					'method'		=> array($this, 'plugin_page_header_livechat_view')
				)
			);
			
			//html
			$plugins->add(
				array(
					'plugin_name'	=> __CLASS__,
					'task_name'		=> __CLASS__ . '_plugin_page_body_livechat_view',
					'section'		=> 'plugin_page_body_livechat_view',
					'method'		=> array($this, 'plugin_page_body_livechat_view')
				)
			);
			
			//admin active sessions.
			$plugins->add(
				array(
					'plugin_name'	=> __CLASS__,
					'task_name'		=> __CLASS__ . '_simple_page_body_livechat_admin_activesessions',
					'section'		=> 'simple_page_body_livechat_admin_activesessions',
					'method'		=> array($this, 'simple_page_body_livechat_admin_activesessions')
				)
			);
			
			//admin receive
			$plugins->add(
				array(
					'plugin_name'	=> __CLASS__,
					'task_name'		=> __CLASS__ . '_simple_page_body_livechat_admin_receive',
					'section'		=> 'simple_page_body_livechat_admin_receive',
					'method'		=> array($this, 'simple_page_body_livechat_admin_receive')
				)
			);
			
			//admin add message
			$plugins->add(
				array(
					'plugin_name'	=> __CLASS__,
					'task_name'		=> __CLASS__ . '_simple_page_body_livechat_admin_add',
					'section'		=> 'simple_page_body_livechat_admin_add',
					'method'		=> array($this, 'simple_page_body_livechat_admin_add')
				)
			);				
						
			//send
			$plugins->add(
				array(
					'plugin_name'	=> __CLASS__,
					'task_name'		=> __CLASS__ . '_simple_page_body_livechat_enabled',
					'section'		=> 'simple_page_body_livechat_enabled',
					'method'		=> array($this, 'simple_page_body_livechat_enabled')
				)
			);
						
		}
		
		/*
			Public Live Chat Page
		*/			
		//html
		$plugins->add(
			array(
				'plugin_name'	=> __CLASS__,
				'task_name'		=> __CLASS__ . '_simple_page_body_livechat',
				'section'		=> 'simple_page_body_livechat',
				'method'		=> array($this, 'simple_page_body_livechat')
			)
		);
		
		/*
			Used for Ajax Livechat HTML
		*/
		//html
		$plugins->add(
			array(
				'plugin_name'	=> __CLASS__,
				'task_name'		=> __CLASS__ . '_simple_page_body_livechat_html',
				'section'		=> 'simple_page_body_livechat_html',
				'method'		=> array($this, 'simple_page_body_livechat_html')
			)
		);
		
		//receive
		$plugins->add(
			array(
				'plugin_name'	=> __CLASS__,
				'task_name'		=> __CLASS__ . '_simple_page_body_livechat_receive',
				'section'		=> 'simple_page_body_livechat_receive',
				'method'		=> array($this, 'simple_page_body_livechat_receive')
			)
		);
		
		//send
		$plugins->add(
			array(
				'plugin_name'	=> __CLASS__,
				'task_name'		=> __CLASS__ . '_simple_page_body_livechat_send',
				'section'		=> 'simple_page_body_livechat_send',
				'method'		=> array($this, 'simple_page_body_livechat_send')
			)
		);
		
	}
	
	//this adds menu items
	public function html_header_nav_start() {
		$config 		= &sts\singleton::get('sts\config');
		$auth 			= &sts\singleton::get('sts\auth');
		$url 			= &sts\singleton::get('sts\url');
		$language 		= &sts\singleton::get('sts\language');
		$plugins 		= &sts\singleton::get('sts\plugins');
	
		$active = false;
		
		if ($url->get_action() == 'p') {
			if (in_array($url->get_module(), array('livechat', 'livechat_view'))) {			
				$active = true;
			}
		}
		
		$url_basename			= $plugins->plugin_base_url(__FILE__);

	?>
		<?php if ($auth->logged_in()) { ?>
			<li<?php if ($active) echo ' class="active"'; ?>><a href="<?php echo $config->get('address'); ?>/p/livechat/"><span class="glyphicon glyphicon-eye-open"></span> <?php echo $language->get('Live Chat'); ?> <span id="lcs_admin_active_sessions_menu"></span></a></li>
			<script type="text/javascript" src="<?php echo $config->get('address'); ?>/user/plugins/<?php echo sts\safe_output($url_basename); ?>/../js/admin_notifications.js"></script>
		<?php } ?>
	<?php
	}
	
	public function plugin_page_header_livechat() {
		$site 		= &sts\singleton::get('sts\site');
		$language 				= &sts\singleton::get('sts\language');
		
		//this sets the title of the page.
		$site->set_title($language->get('Live Chat'));
		
	}
	
	public function simple_page_body_livechat_enabled() {
		$config 				= &sts\singleton::get('sts\config');
	
		if (isset($_POST['data'])) {
			$receive_array = json_decode($_POST['data'], true);
			
			if ($receive_array['enabled']) {
				$config->set('livechat_enabled', 1);
			}
			else {
				$config->set('livechat_enabled', 0);
			}
		}		
	}
	
	public function plugin_page_body_livechat() {
		$config 				= &sts\singleton::get('sts\config');
		$auth 					= &sts\singleton::get('sts\auth');
		$plugins 				= &sts\singleton::get('sts\plugins');
		$users 					= &sts\singleton::get('sts\users');
		$language 				= &sts\singleton::get('sts\language');
		$live_chat 				= &sts\singleton::get(__NAMESPACE__ . '\live_chat');		

		
		$url_basename			= $plugins->plugin_base_url(__FILE__);

		$finished_sessions 		= $live_chat->get(array('where' => array('active' => 0), 'limit' => 25));
		
		?>
		<script type="text/javascript" src="<?php echo $config->get('address'); ?>/user/plugins/<?php echo sts\safe_output($url_basename); ?>/../js/chat.js"></script>

		<script type="text/javascript">		
			$(document).ready(function () {
				
				//process login form
				$('body').on("click", '#lcs_enable_button', function (e) {
					e.preventDefault();	
					
					
					data  = {};

					if ($(this).hasClass("btn-success")) {
						$(this).html('<?php echo sts\safe_output($language->get('Live Chat Disabled')); ?>');
						$(this).removeClass("btn-success");
						$(this).addClass("btn-primary");
						alert('<?php echo sts\safe_output($language->get('Note: Existing sessions will stay active.')); ?>');
						data.enabled = false;
					}
					else {		
						$(this).html('<?php echo sts\safe_output($language->get('Live Chat Enabled')); ?>');
						$(this).addClass("btn-success");
						$(this).removeClass("btn-primary");
						data.enabled = true;
					}
				
					$.ajax({
						type: "POST",
						cache: false,
						data: {data:JSON.stringify(data)},
						url: sts_base_url + "/simple/livechat_enabled/",
						success: function(html){
						}
					});
						 
				});
				
				
				
			});
		</script>
		
		<div class="row">

			<div class="col-md-3">
				<div class="well well-sm">
					<h4><?php echo sts\safe_output($language->get('Live Chat')); ?></h4>
				
					<div class="clearfix"></div>
				
					<div class="pull-right">
						<?php if ($config->get('livechat_enabled')) { ?>
							<a href="#" id="lcs_enable_button" class="btn btn-success"><?php echo sts\safe_output($language->get('Live Chat Enabled')); ?></a>						
						<?php } else { ?>
							<a href="#" id="lcs_enable_button" class="btn btn-primary"><?php echo sts\safe_output($language->get('Live Chat Disabled')); ?></a>
						<?php } ?>
					</div>
					<div class="clearfix"></div>
					
				</div>
			</div>
			
			<div class="col-md-9">
			
				<div class="panel panel-default">
					<div class="panel-heading">
						<div class="pull-left">
							<h1 class="panel-title"><?php echo sts\safe_output($language->get('Active Sessions')); ?></h1>
						</div>
						<div class="clearfix"></div>
					</div>
					<div class="panel-body">
						<div id="active_sessions">
							<table class="table table-striped table-bordered" id="active_sessions_table">
								<thead>
									<tr>
										<th><?php echo sts\safe_output($language->get('ID')); ?></th>
										<th><?php echo sts\safe_output($language->get('Name')); ?></th>
										<th><?php echo sts\safe_output($language->get('Email')); ?></th>
										<th><?php echo sts\safe_output($language->get('Start Time')); ?></th>
										<th><?php echo sts\safe_output($language->get('Last Guest Message')); ?></th>
									</tr>
								</thead>
								<tbody>
								
								</tbody>
				
							</table>		
						</div>
					</div>
				</div>
				
				<div class="panel panel-default">
					<div class="panel-heading">
						<div class="pull-left">
							<h1 class="panel-title"><?php echo sts\safe_output($language->get('Finished Sessions')); ?></h1>
						</div>
						<div class="clearfix"></div>
					</div>
					<div class="panel-body">
						<div id="finished_sessions">
							<table class="table table-striped table-bordered">
								<thead>
									<tr>
										<th><?php echo sts\safe_output($language->get('Name')); ?></th>
										<th><?php echo sts\safe_output($language->get('Email')); ?></th>
										<th><?php echo sts\safe_output($language->get('Finished Time')); ?></th>
									</tr>
								</thead>
								<tbody>
							<?php
								$i = 0;
								foreach ($finished_sessions as $session) {
							?>
							<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
								<td class="centre"><a href="<?php echo $config->get('address'); ?>/p/livechat_view/<?php echo (int) $session['id']; ?>/"><?php echo sts\safe_output($session['name']); ?></a></td>
								<td class="centre"><?php echo sts\safe_output($session['email']); ?></td>
								<td class="centre"><?php echo sts\safe_output(sts\time_ago_in_words($session['date_finished'])); ?> <?php echo sts\safe_output($language->get('ago')); ?></td>
							</tr>
							<?php $i++; } ?>					
								</tbody>
				
							</table>			
						</div>
					</div>
				</div>
			
			</div>
		</div>
		<?php
	}
	
	
	public function plugin_page_header_livechat_view() {
		$site 		= &sts\singleton::get('sts\site');
		$url 		= &sts\singleton::get('sts\url');
		$config 	= &sts\singleton::get('sts\config');
		$gravatar 	= &sts\singleton::get('sts\gravatar');
		$plugins 	= &sts\singleton::get('sts\plugins');
		$language 	= &sts\singleton::get('sts\language');

		
		$live_chat 				= &sts\singleton::get(__NAMESPACE__ . '\live_chat');				
		$chatmessage_item 		= &sts\singleton::get(__NAMESPACE__ . '\chatmessage_item');		
				
		//this sets the title of the page.
		$site->set_title('View Live Chat');
		
		$id = (int) $url->get_item();

		if ($id == 0) {
			header('Location: ' . $config->get('address') . '/p/livechat/');
			exit;
		}

		$c_array['id']				= $id;
		$c_array['get_other_data'] 	= true;
		$c_array['limit']			= 1;

		$chat_array = $live_chat->get($c_array);

		if (count($chat_array) == 1) {
			$this->current_item = $chat_array[0];
		}
		else {
			header('Location: ' . $config->get('address') . '/p/livechat/');
			exit;
		}
		
		
		if (isset($_POST['end'])) {
			$chatmessage_item->add(
				array(
					'message'		=> $language->get('Chat Ended'),
					'guest'			=> 0,
					'chat_id'		=> $id
				)
			);
		
			$live_chat->edit(array('id' => $id, 'columns' => array('active' => 0, 'date_finished' => sts\datetime())));
			header('Location: ' . $config->get('address') . '/p/livechat/');
			exit;			
		}
		
	}
	
	public function plugin_page_body_livechat_view() {
	
		$url 		= &sts\singleton::get('sts\url');
		$config 	= &sts\singleton::get('sts\config');
		$gravatar 	= &sts\singleton::get('sts\gravatar');
		$plugins 	= &sts\singleton::get('sts\plugins');
		$language 	= &sts\singleton::get('sts\language');
		$users 		= &sts\singleton::get('sts\users');
		$auth 		= &sts\singleton::get('sts\auth');

		
		$live_chat 				= &sts\singleton::get(__NAMESPACE__ . '\live_chat');				
		$chatmessage_item 		= &sts\singleton::get(__NAMESPACE__ . '\chatmessage_item');		
		
		$url_basename		= $plugins->plugin_base_url(__FILE__);		

			
		$chat = $this->current_item;

		
		?>
		<script type="text/javascript" src="<?php echo $config->get('address'); ?>/user/plugins/<?php echo sts\safe_output($url_basename); ?>/../js/chat.js"></script>
		<script type="text/javascript">
			$(document).ready(function () {
				$('#end_chat').click(function () {
					if (confirm("<?php echo sts\safe_output($language->get('Are you sure you wish to end this chat?')); ?>")){
						return true;
					}
					else{
						return false;
					}
				});
			});
		</script>	
		<form method="post" action="<?php echo sts\safe_output($_SERVER['REQUEST_URI']); ?>">
			<div class="col-md-3">
				<div class="well well-sm">
					<h4><?php echo sts\safe_output($language->get('Chat')); ?></h4>
					
					<label class="left-result"><?php echo sts\safe_output($language->get('Name')); ?></label>
					<p class="right-result">
						<?php echo sts\safe_output(ucwords($chat['name'])); ?>
					</p>
					<div class="clearfix"></div>
					
					<?php if (!empty($chat['email'])) { ?>
						<label class="left-result"><?php echo sts\safe_output($language->get('Email')); ?></label>
						<p class="right-result">
							<?php echo sts\safe_output($chat['email']); ?>
						</p>
						<div class="clearfix"></div>
					<?php } ?>

					<label class="left-result"><?php echo sts\safe_output($language->get('Started')); ?></label>
					<p class="right-result"><?php echo sts\safe_output(sts\time_ago_in_words($chat['date_added'])); ?> <?php echo sts\safe_output($language->get('ago')); ?></p>
					<div class="clearfix"></div>

					<label class="left-result"><?php echo sts\safe_output($language->get('Last Guest Message')); ?></label>
					<p class="right-result"><?php echo sts\safe_output(sts\time_ago_in_words($chat['last_guest_message'])); ?> <?php echo sts\safe_output($language->get('ago')); ?></p>
					<div class="clearfix"></div>
					
					<?php if (!empty($chat['date_finished']) && ($chat['date_finished'] != '0000-00-00 00:00:00')) { ?>
						<label class="left-result"><?php echo sts\safe_output($language->get('Finished')); ?></label>
						<p class="right-result"><?php echo sts\safe_output(sts\time_ago_in_words($chat['date_finished'])); ?> <?php echo sts\safe_output($language->get('ago')); ?></p>
						<div class="clearfix"></div>
					<?php } ?>

					<label class="left-result"><?php echo sts\safe_output($language->get('ID')); ?></label>
					<p class="right-result"><?php echo sts\safe_output($chat['id']); ?></p>
				
					<div class="clearfix"></div>
					
					<?php if ($chat['active']) { ?>
						<div class="pull-right">
							<button type="submit" id="end_chat" name="end" class="btn btn-danger"><?php echo sts\safe_output($language->get('End Chat')); ?></button>		
							
						</div>
						<div class="clearfix"></div>
					<?php } ?>

				</div>
				
				<?php if (isset($chat['email']) && !empty($chat['email'])) { 
					$user_array = $users->get(array('email' => $chat['email'], 'limit' => 1));
					
					if (!empty($user_array)) {
						$user = $user_array[0];
					?>
					<div class="well well-sm">
						<div class="pull-left">
							<h4><?php echo sts\safe_output($language->get('User Details')); ?></h4>
						</div>
						
						<div class="clearfix"></div>

						<?php if ($auth->can('manage_users')) { ?>
							<label class="left-result"><?php echo sts\safe_output($language->get('Name')); ?></label>
							<p class="right-result"><a href="<?php echo $config->get('address'); ?>/users/view/<?php echo (int) $user['id']; ?>/"><?php echo sts\safe_output(ucwords($user['name'])); ?></a></p>
							<div class="clearfix"></div>
						<?php } else { ?>
							<label class="left-result"><?php echo sts\safe_output($language->get('Name')); ?></label>
							<p class="right-result"><?php echo sts\safe_output(ucwords($user['name'])); ?></p>
							<div class="clearfix"></div>
						<?php } ?>
					
						<label class="left-result"><?php echo sts\safe_output($language->get('Email')); ?></label>
						<p class="right-result"><a href="mailto:<?php echo sts\safe_output($user['email']); ?>"><?php echo sts\safe_output($user['email']); ?></a></p>
						<div class="clearfix"></div>
					</div>
					<?php
					}
				}
				?>
			</div>
			
			<div class="col-md-9">
				<div class="panel panel-default">
					<div class="panel-heading">
						<div class="pull-left">
							<h1 class="panel-title"><?php echo sts\safe_output($language->get('Messages')); ?></h1>
						</div>
						<div class="clearfix"></div>
					</div>
					<div class="panel-body">
						<div style="overflow-y:scroll; height: 300px" class="view_chat_messages" id="chat_id-<?php echo (int) $chat['id']; ?>"></div>
						<div class="clearfix"></div>
					</div>
				</div>

				<?php if ($chat['active']) { ?>
					<ul class="nav nav-tabs">
						<li class="active"><a href="#"><?php echo sts\safe_output($language->get('Reply')); ?></a></li>
					</ul>
					
					<div class="tab-content">
						<form method="post" action="<?php echo $config->get('address'); ?>/simple/livechat_admin_add/<?php echo (int) $chat['id']; ?>/">
							<p><textarea class="form-control" id="lcs_admin_chat_text" id="message" name="message" cols="70" rows="4"></textarea></p>
							<p>
								<input type="hidden" id="lcs_admin_chat_id" name="id" value="<?php echo (int) $chat['id']; ?>" />
								<button id="lcs_admin_chat_submit" name="add" type="submit" class="btn btn-primary"><?php echo sts\safe_output($language->get('Send')); ?></button>
							</p>
						</form>
					</div>
				<?php } ?>
				
			</div>	
		</form>
		<?php
	}
	
	public function simple_page_body_livechat() {
	
		$site 						= &sts\singleton::get('sts\site');		
		$config 					= &sts\singleton::get('sts\config');	
		$plugins 					= &sts\singleton::get('sts\plugins');
		$language 					= &sts\singleton::get('sts\language');
		$live_chat 					= &sts\singleton::get(__NAMESPACE__ . '\live_chat');	
		$url_basename				= $plugins->plugin_base_url(__FILE__);		
		
		$site->set_title($language->get('Live Chat'));

	?>
		<!DOCTYPE html>
		<html>
			<head>
				<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
				<title><?php echo sts\safe_output($site->get_title()); ?></title>

				<meta name="viewport" content="width=device-width, maximum-scale=1.0" />
				
				<link href="<?php echo $config->get('address'); ?>/user/themes/<?php echo sts\safe_output(sts\CURRENT_THEME); ?>/sub/<?php echo sts\safe_output(sts\CURRENT_THEME_SUB); ?>/css/bootstrap.css" rel="stylesheet">
				<link href="<?php echo $config->get('address'); ?>/user/themes/<?php echo sts\safe_output(sts\CURRENT_THEME); ?>/sub/<?php echo sts\safe_output(sts\CURRENT_THEME_SUB); ?>/css/responsive-tables.css" rel="stylesheet">    
				<link href="<?php echo $config->get('address'); ?>/user/themes/<?php echo sts\safe_output(sts\CURRENT_THEME); ?>/sub/<?php echo sts\safe_output(sts\CURRENT_THEME_SUB); ?>/css/bootstrap-custom.css" rel="stylesheet">    

				<link rel="shortcut icon" href="<?php echo $config->get('address'); ?>/favicon.ico" />
				
				<script type="text/javascript" src="<?php echo $config->get('address'); ?>/system/libraries/js/jquery.js"></script>

				<script type="text/javascript" src="<?php echo $config->get('address'); ?>/user/themes/<?php echo sts\safe_output(sts\CURRENT_THEME); ?>/sub/<?php echo sts\safe_output(sts\CURRENT_THEME_SUB); ?>/js/bootstrap.min.js"></script>

				<script type="text/javascript" src="<?php echo $config->get('address'); ?>/system/libraries/js/respond.min.js"></script>
				
				<script type="text/javascript" src="<?php echo $config->get('address'); ?>/system/libraries/js/moment.min.js"></script>	
				<script type="text/javascript" src="<?php echo $config->get('address'); ?>/system/libraries/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
				<link href="<?php echo $config->get('address'); ?>/system/libraries/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet">    
				
				<script type="text/javascript">
					var sts_base_url 			= "<?php echo sts\safe_output($config->get('address')); ?>";
					var sts_current_theme		= "<?php echo sts\safe_output(sts\CURRENT_THEME); ?>";
					var sts_current_theme_sub	= "<?php echo sts\safe_output(sts\CURRENT_THEME_SUB); ?>";
				</script>
				
				<link href="<?php echo $config->get('address'); ?>/user/plugins/<?php echo sts\safe_output($url_basename); ?>/../css/style.css" rel="stylesheet">    

				<script type="text/javascript" src="<?php echo $config->get('address'); ?>/user/plugins/<?php echo sts\safe_output($url_basename); ?>/../js/client.js"></script>
		
			</head>

			<body>
				<div class="panel panel-default">
					<div class="panel-heading">
						<div class="pull-left">
							<h1 class="panel-title"><?php echo sts\safe_output($language->get('Live Chat')); ?></h1>
						</div>
						<div class="clearfix"></div>
					</div>
					<div class="panel-body">
						<div id="lcs_body">
							<div id="lcs_message"></div>
							<div id="lcs_content"></div>
						</div>
						<div class="clearfix"></div>
					</div>
				</div>
			</body>
		</html>	
	<?php
	}
	
	public function simple_page_body_livechat_html() {
		header("Access-Control-Allow-Origin: *");
			
		$live_chat 		= &sts\singleton::get(__NAMESPACE__ . '\live_chat');		
		$language 		= &sts\singleton::get('sts\language');	
		$config 		= &sts\singleton::get('sts\config');	
	
		if ($config->get('livechat_enabled')) {
			if (isset($_POST['data'])) {
				$receive_array = json_decode($_POST['data'], true);

				if (!empty($receive_array['name'])) {
					$create_session = true;
					if (!empty($receive_array['email'])) {
						if (!sts\check_email_address($receive_array['email'])) {
							$create_session = false;
						}
					}
				
					if ($create_session) {
						$chat = $live_chat->add(
							array(
								'columns' => array (
									'name'					=> $receive_array['name'],
									'email'					=> $receive_array['email'],
									'active'				=> 1,
									'date_added'			=> sts\datetime(),
									'last_guest_message'	=> sts\datetime()
								)
							)
						);
						
						$_SESSION['chat']['session']['id'] = $chat;
					}
				}
			}
		}

		?>
		<?php if (isset($_SESSION['chat']['session']) && $live_chat->count(array('where' => array('active' => 1), 'id' => $_SESSION['chat']['session']['id'])) > 0) { ?>
			<div id="lcs_receive" style="overflow-y:scroll; height: 200px"></div>
			<div class="clearfix"></div>
			<br />
			<form id="lcs_chat_form" role="form">
				<div class="form-group">	
					<div class="col-xs-8">
						<input id="lcs_chat_text" require placeholder="<?php echo sts\safe_output($language->get('Message')); ?>" type="text" class="form-control" name="lcs_chat_text" value="" />
					</div>
					<div class="col-xs-2">
						<button id="lcs_chat_submit" type="submit" name="lcs_chat_submit" class="btn btn-primary"><?php echo sts\safe_output($language->get('Send')); ?></button>
					</div>
				</div>
			</form>			
		<?php } else { ?>
			<?php if ($config->get('livechat_enabled')) { ?>		
				<form method="post" id="lcs_login_form" action="<?php echo sts\safe_output($_SERVER['REQUEST_URI']); ?>">
					<div class="form-group">	
						<div class="col-lg-10">
							<p><input class="form-control" required placeholder="<?php echo sts\safe_output($language->get('Name')); ?>" type="text" id="lcs_login_name" name="lcs_login_name" value="" /></p>	
							<p><input class="form-control" placeholder="<?php echo sts\safe_output($language->get('Email (optional)')); ?>" type="text" id="lcs_login_email" name="lcs_login_email" value="" /></p>
						</div>
					</div>
					<div class="clearfix"></div>
					<div class="col-lg-10">
						<button type="submit" name="lcs_login_submit" class="btn btn-primary btn-sm"><?php echo sts\safe_output($language->get('Start Chat')); ?></button>
					</div>
				</form>
			<?php } else { ?>
				<p><?php echo sts\safe_output($language->get('Sorry the chat system is currently off-line.')); ?></p>
			<?php } ?>
		<?php }
	}

	public function simple_page_body_livechat_receive() {
		
		$live_chat 			= &sts\singleton::get(__NAMESPACE__ . '\live_chat');		
		$chatmessage_item 	= &sts\singleton::get(__NAMESPACE__ . '\chatmessage_item');		
		$language 			= &sts\singleton::get('sts\language');	
	
		header("Access-Control-Allow-Origin: *");
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-Type: application/json; charset=utf-8');

		if (isset($_SESSION['chat']['session'])) {

			if ($live_chat->count(array('where' => array('active' => 1), 'id' => $_SESSION['chat']['session']['id'])) > 0) {

				$messages = $chatmessage_item->get(array('chat_id' => (int) $_SESSION['chat']['session']['id'], 'get_other_data' => true));
				
				$output = array('message' => 'Session Active', 'success' => true, 'messages' => array());
				foreach($messages as $message) {
					$output['messages'][] = array(
						'name' 			=> $message['name'],
						'user_name'		=> $message['user_name'],
						'guest'			=> $message['guest'],
						'message'		=> $message['message'],
						'date_added'	=> $message['date_added'],
						'time_ago'		=> sts\time_ago_in_words($message['date_added']) . ' ' . $language->get('ago')
					);
				}
				
				$output['messages'] = array_reverse($output['messages']);
								
				echo json_encode($output);
			}
			else {
				echo json_encode(array('message' => $language->get('Chat Session Ended'), 'success' => false));
				unset($_SESSION['chat']['session']);
			}

		}
	
	}
	
	public function simple_page_body_livechat_send() {
	
		$live_chat 		= &sts\singleton::get(__NAMESPACE__ . '\live_chat');		
		$chatmessage_item 	= &sts\singleton::get(__NAMESPACE__ . '\chatmessage_item');		

		if (isset($_SESSION['chat']['session'])) {
			if (isset($_POST['data'])) {
				$receive_array = json_decode($_POST['data'], true);
				
				if (!empty($receive_array['text'])) {
					//check if chat session is live.
					if ($live_chat->count(array('where' => array('active' => 1), 'id' => $_SESSION['chat']['session']['id'])) > 0) {
						$id = $chatmessage_item->add(
							array(
								'message'	=> $receive_array['text'],
								'guest'		=> 1,
								'chat_id'	=> $_SESSION['chat']['session']['id']
							)
						);
						$live_chat->edit(array('id' => $_SESSION['chat']['session']['id'], 'columns' => array('last_guest_message' => sts\datetime())));
					}
					else {
						unset($_SESSION['chat']['session']);
					}
				}
			}
		}
	}
	
	public function simple_page_body_livechat_admin_activesessions() {
		
		$auth 			= &sts\singleton::get('sts\auth');
		
		$sessions = array();
		
		if ($auth->can('manage_livechat')) {

			$live_chat 		= &sts\singleton::get(__NAMESPACE__ . '\live_chat');		

			$sessions = $live_chat->get(array('where' => array('active' => 1), 'order_by' => 'last_guest_message', 'order' => 'desc'));

			header('Cache-Control: no-cache, must-revalidate');
			header('Content-Type: application/json; charset=utf-8');

		}

		echo json_encode($sessions);
	}
	
	public function simple_page_body_livechat_admin_receive() {
		
		$auth 				= &sts\singleton::get('sts\auth');
		$language 			= &sts\singleton::get('sts\language');
		$url 				= &sts\singleton::get('sts\url');

		$chatmessage_item 	= &sts\singleton::get(__NAMESPACE__ . '\chatmessage_item');		
	
		$output = array();

		if ($auth->can('manage_livechat')) {
		
			header('Cache-Control: no-cache, must-revalidate');
			header('Content-Type: application/json; charset=utf-8');

			$id = (int) $url->get_item();
				
			$messages = $chatmessage_item->get(array('chat_id' => $id, 'get_other_data' => true));


			foreach($messages as $message) {
				$output[] = array(
					'name' 			=> $message['name'],
					'email' 		=> $message['email'],
					'user_name'		=> $message['user_name'],
					'user_email'	=> $message['user_email'],
					'guest'			=> $message['guest'],
					'message'		=> $message['message'],
					'time_ago'		=> sts\time_ago_in_words($message['date_added']) . ' ' . $language->get('ago')
				);

			}
			
			$output = array_reverse($output);

		}
	
		echo json_encode($output);

	}
	
	public function simple_page_body_livechat_admin_add() {
	
		$url 				= &sts\singleton::get('sts\url');
		$auth 				= &sts\singleton::get('sts\auth');
		$config 			= &sts\singleton::get('sts\config');
		$live_chat 			= &sts\singleton::get(__NAMESPACE__ . '\live_chat');		
		$chatmessage_item 	= &sts\singleton::get(__NAMESPACE__ . '\chatmessage_item');		

		$id = (int) $url->get_item();

		$t_array['id']				= $id;

		$chat_array = $live_chat->get($t_array);


		if (count($chat_array) == 1) {
			$chat = $chat_array[0];
				
			//add note!
			if (!empty($_POST['message'])) {

				$note['message'] 		= $_POST['message'];
				$note['chat_id'] 		= (int) $chat['id'];
				$note['user_id'] 		= $auth->get('id');
				$note['guest']			= 0;
				
				$chatmessage_item->add($note);
				
				
			}

			header('Location: ' . $config->get('address') . '/p/livechat_view/' . $chat['id'] . '/#addnote');
		}
		else {
			header('Location: ' . $config->get('address') . '/p/livechat/');
		}
	}
	
}

?>