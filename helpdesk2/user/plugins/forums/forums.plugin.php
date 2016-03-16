<?php
/*
	Forums plugin for Tickets.
	Copyright Dalegroup Pty Ltd 2014
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
class forums {

	private $root = NULL;
	private $current_forum = NULL;
	private $current_thread = NULL;
	private $message = NULL;

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
			'name' 				=> 'Forums',
			'version' 			=> '5.0',
			'description'		=> 'This plugin adds a Forum.',
			'website'			=> 'http://codecanyon.net/item/tickets/2478843?ref=michaeldale',
			'author'			=> 'Dalegroup Pty Ltd',
			'author_website'	=> 'http://dalegroup.net/',
			'update_check_url'	=> 'http://api.apptrack.com.au/api/',
			'application_id'	=> 13
		);

		return $info;
	}
	
	/*
		This function is called on each page load if the plugin is enabled.
		It is required.
	*/
	public function load() {
		

		//this is how you get an existing class
		$plugins 		= &sts\singleton::get('sts\plugins');	
		$config 		= &sts\singleton::get('sts\config');	
		
		$url_basename			= $plugins->plugin_base_url(__FILE__);

		$this->root = $config->get('address') . '/user/plugins/' . $url_basename . '/..';
		
		//this is a custom class from the plugin.
		include('forum_sections.class.php');
		$forum_sections 		= &sts\singleton::get(__NAMESPACE__ . '\forum_sections');
		include('forum_threads.class.php');
		$forum_threads 			= &sts\singleton::get(__NAMESPACE__ . '\forum_threads');
		include('forum_posts.class.php');
		$forum_posts 			= &sts\singleton::get(__NAMESPACE__ . '\forum_posts');		
		
		include('forum_to_user_levels.class.php');
		$forum_to_user_levels 	= &sts\singleton::get(__NAMESPACE__ . '\forum_to_user_levels');				
		
		include('forum_to_permission_groups.class.php');
		$forum_to_permission_groups 	= &sts\singleton::get(__NAMESPACE__ . '\forum_to_permission_groups');				
		
		include('forum_to_departments.class.php');
		$forum_to_departments 		= &sts\singleton::get(__NAMESPACE__ . '\forum_to_departments');				

		include('forum_thread_subscriptions.class.php');
		$forum_thread_subscriptions 	= &sts\singleton::get(__NAMESPACE__ . '\forum_thread_subscriptions');		
		
		//do last
		include('forums_install.class.php');
		$forums_install 		= &sts\singleton::get(__NAMESPACE__ . '\forums_install');		

		//makes sure that we're installed
		$forums_install->make_installed();	
			
		//Forum Menu
		$plugins->add(
			array(
				'plugin_name'	=> __CLASS__,
				'task_name'		=> __CLASS__ . '_html_header_nav_start',
				'section'		=> 'html_header_nav_start',
				'method'		=> array($this, 'html_header_nav_start')
			)
		);
		
		//Forums Title
		$plugins->add(
			array(
				'plugin_name'	=> __CLASS__,
				'task_name'		=> __CLASS__ . '_plugin_page_header_forums',
				'section'		=> 'plugin_page_header_forums',
				'method'		=> array($this, 'plugin_page_header_forums')
			)
		);
		
		//Forums Body
		$plugins->add(
			array(
				'plugin_name'	=> __CLASS__,
				'task_name'		=> __CLASS__ . '_plugin_page_body_forums',
				'section'		=> 'plugin_page_body_forums',
				'method'		=> array($this, 'plugin_page_body_forums')
			)
		);
		
		//Forum Title
		$plugins->add(
			array(
				'plugin_name'	=> __CLASS__,
				'task_name'		=> __CLASS__ . '_plugin_page_header_forum',
				'section'		=> 'plugin_page_header_forum',
				'method'		=> array($this, 'plugin_page_header_forum')
			)
		);
		
		//Forum Body
		$plugins->add(
			array(
				'plugin_name'	=> __CLASS__,
				'task_name'		=> __CLASS__ . '_plugin_page_body_forum',
				'section'		=> 'plugin_page_body_forum',
				'method'		=> array($this, 'plugin_page_body_forum')
			)
		);
		
		//Thread Title
		$plugins->add(
			array(
				'plugin_name'	=> __CLASS__,
				'task_name'		=> __CLASS__ . '_plugin_page_header_thread',
				'section'		=> 'plugin_page_header_thread',
				'method'		=> array($this, 'plugin_page_header_thread')
			)
		);
		
		//Thread Body
		$plugins->add(
			array(
				'plugin_name'	=> __CLASS__,
				'task_name'		=> __CLASS__ . '_plugin_page_body_thread',
				'section'		=> 'plugin_page_body_thread',
				'method'		=> array($this, 'plugin_page_body_thread')
			)
		);
		
		//Add Thread Title
		$plugins->add(
			array(
				'plugin_name'	=> __CLASS__,
				'task_name'		=> __CLASS__ . '_plugin_page_header_add_thread',
				'section'		=> 'plugin_page_header_add_thread',
				'method'		=> array($this, 'plugin_page_header_add_thread')
			)
		);
		
		//Add Thread Body
		$plugins->add(
			array(
				'plugin_name'	=> __CLASS__,
				'task_name'		=> __CLASS__ . '_plugin_page_body_add_thread',
				'section'		=> 'plugin_page_body_add_thread',
				'method'		=> array($this, 'plugin_page_body_add_thread')
			)
		);
		
		//Add Thread
		$plugins->add(
			array(
				'plugin_name'	=> __CLASS__,
				'task_name'		=> __CLASS__ . '_plugin_page_body_add_thread',
				'section'		=> 'plugin_page_body_add_thread',
				'method'		=> array($this, 'plugin_page_body_add_thread')
			)
		);
		
		//Add Post
		$plugins->add(
			array(
				'plugin_name'	=> __CLASS__,
				'task_name'		=> __CLASS__ . '_simple_page_header_add_thread_post',
				'section'		=> 'simple_page_header_add_thread_post',
				'method'		=> array($this, 'simple_page_header_add_thread_post')
			)
		);	
		
		//Edit Thread Title
		$plugins->add(
			array(
				'plugin_name'	=> __CLASS__,
				'task_name'		=> __CLASS__ . '_plugin_page_header_edit_thread',
				'section'		=> 'plugin_page_header_edit_thread',
				'method'		=> array($this, 'plugin_page_header_edit_thread')
			)
		);
		
		//Edit Thread Body
		$plugins->add(
			array(
				'plugin_name'	=> __CLASS__,
				'task_name'		=> __CLASS__ . '_plugin_page_body_edit_thread',
				'section'		=> 'plugin_page_body_edit_thread',
				'method'		=> array($this, 'plugin_page_body_edit_thread')
			)
		);
		
		//Forum Search Title
		$plugins->add(
			array(
				'plugin_name'	=> __CLASS__,
				'task_name'		=> __CLASS__ . '_plugin_page_header_forums_search',
				'section'		=> 'plugin_page_header_forums_search',
				'method'		=> array($this, 'plugin_page_header_forums_search')
			)
		);
		
		//Forum Search Body
		$plugins->add(
			array(
				'plugin_name'	=> __CLASS__,
				'task_name'		=> __CLASS__ . '_plugin_page_body_forums_search',
				'section'		=> 'plugin_page_body_forums_search',
				'method'		=> array($this, 'plugin_page_body_forums_search')
			)
		);		
		
		/*
			Forum Settings
		*/
		
		//This hooks into the settings menu system
		$plugins->add(
			array(
				'plugin_name'	=> __CLASS__,
				'task_name'		=> __CLASS__ . '_html_header_nav_settings',
				'section'		=> 'html_header_nav_settings',
				'method'		=> array($this, 'html_header_nav_settings')
			)
		);
		
		//title
		$plugins->add(
			array(
				'plugin_name'	=> __CLASS__,
				'task_name'		=> __CLASS__ . '_plugin_page_header_forum_settings',
				'section'		=> 'plugin_page_header_forum_settings',
				'method'		=> array($this, 'plugin_page_header_forum_settings')
			)
		);
		
		//html
		$plugins->add(
			array(
				'plugin_name'	=> __CLASS__,
				'task_name'		=> __CLASS__ . '_plugin_page_body_forum_settings',
				'section'		=> 'plugin_page_body_forum_settings',
				'method'		=> array($this, 'plugin_page_body_forum_settings')
			)
		);
		
		/*
			Add Forum Section
		*/
		
		//title
		$plugins->add(
			array(
				'plugin_name'	=> __CLASS__,
				'task_name'		=> __CLASS__ . '_plugin_page_header_add_forum_section',
				'section'		=> 'plugin_page_header_add_forum_section',
				'method'		=> array($this, 'plugin_page_header_add_forum_section')
			)
		);
		
		//html
		$plugins->add(
			array(
				'plugin_name'	=> __CLASS__,
				'task_name'		=> __CLASS__ . '_plugin_page_body_add_forum_section',
				'section'		=> 'plugin_page_body_add_forum_section',
				'method'		=> array($this, 'plugin_page_body_add_forum_section')
			)
		);
		
		/*
			Edit Forum Section
		*/
		
		//title
		$plugins->add(
			array(
				'plugin_name'	=> __CLASS__,
				'task_name'		=> __CLASS__ . '_plugin_page_header_edit_forum_section',
				'section'		=> 'plugin_page_header_edit_forum_section',
				'method'		=> array($this, 'plugin_page_header_edit_forum_section')
			)
		);
		
		//html
		$plugins->add(
			array(
				'plugin_name'	=> __CLASS__,
				'task_name'		=> __CLASS__ . '_plugin_page_body_edit_forum_section',
				'section'		=> 'plugin_page_body_edit_forum_section',
				'method'		=> array($this, 'plugin_page_body_edit_forum_section')
			)
		);
				
		//
		$plugins->add(
			array(
				'plugin_name'	=> __CLASS__,
				'task_name'		=> __CLASS__ . '_simple_page_header_forum_get_subscription',
				'section'		=> 'simple_page_header_forum_get_subscription',
				'method'		=> array($this, 'simple_page_header_forum_get_subscription')
			)
		);
				
	}
	
	//this adds menu items
	public function html_header_nav_start() {
		$config 		= &sts\singleton::get('sts\config');
		$auth 			= &sts\singleton::get('sts\auth');
		$url 			= &sts\singleton::get('sts\url');
		$language 		= &sts\singleton::get('sts\language');

		$active = false;
		
		if ($url->get_action() == 'p') {
			if (in_array($url->get_module(), array('forums', 'forum', 'thread', 'add_thread', 'edit_thread'))) {			
				$active = true;
			}
		}
		
		?>
		<?php if ($auth->logged_in() && $auth->can('forums')) { ?>
			<?php
				switch(sts\CURRENT_THEME_TYPE) { 
					case 'bootstrap3':
					?>
						<li<?php if ($active) echo ' class="active"'; ?>><a href="<?php echo $config->get('address'); ?>/p/forums/"><i class="glyphicon glyphicon-list-alt"></i> <?php echo $language->get('Forums'); ?></a></li>
					<?php					
					break;
				}
			?>
		<?php }
	}
	
	//Forums Header
	public function plugin_page_header_forums() {
		$site 		= &sts\singleton::get('sts\site');
		$language 	= &sts\singleton::get('sts\language');
		$auth 		= &sts\singleton::get('sts\auth');
		$config 	= &sts\singleton::get('sts\config');
		
		if (!$auth->can('forums') && !$auth->can('manage_forums')) {
			header('Location: ' . $config->get('address') . '/');
			exit;
		}
		
		//this sets the title of the page.
		$site->set_title($language->get('Forums'));
	
	}
	
	//Forums Body
	public function plugin_page_body_forums() {
		$config 				= &sts\singleton::get('sts\config');
		$auth 					= &sts\singleton::get('sts\auth');
		$language 				= &sts\singleton::get('sts\language');

		$forum_sections 		= &sts\singleton::get(__NAMESPACE__ . '\forum_sections');
		$forum_threads 			= &sts\singleton::get(__NAMESPACE__ . '\forum_threads');
		
		$items					= $forum_sections->get(array('get_other_data' => true, 'order_by' => 'id', 'group_id' => $auth->get('group_id')));
		
		if (!empty($items)) {
			$posts					= $forum_threads->get(array('get_other_data' => true, 'limit' => 10, 'group_id' => $auth->get('group_id')));
		}
	
		switch(sts\CURRENT_THEME_TYPE) {
			case 'bootstrap3':
			?>
				<div class="row">
					<div class="col-md-3">
						<div class="well well-sm">
							<div class="left">
								<h4><?php echo $language->get('Forums'); ?></h4>
							</div>

							<div class="clearfix"></div>

						</div>	
						
					<div class="well well-sm">
						<form method="post" action="<?php echo sts\safe_output($config->get('address')); ?>/p/forums_search/">
							
							<div class="form-group">		
								<input type="text" class="form-control" placeholder="<?php echo sts\safe_output($language->get('Search')); ?>" name="like_search" value="" size="15" />
							</div>
							<div class="clearfix"></div>
						
							<br />
							
							<div class="pull-right">
								<p>
									<button type="submit" name="filter" class="btn btn-info"><?php echo sts\safe_output($language->get('Search')); ?></button>
								</p>
							</div>
							<div class="clearfix"></div>
						</form>		
					</div>	
					</div>
					<div class="col-md-9">
						<?php if (!empty($items)) { ?>
							<!--<div class="well well-small">-->
								<h4><?php echo $language->get('Forums'); ?></h4>
								<section id="no-more-tables">	
									<table class="table table-striped table-bordered">
										<thead>
											<tr>
												<th><?php echo sts\safe_output($language->get('Forum')); ?></th>
												<th><?php echo sts\safe_output($language->get('Last Post')); ?></th>
												<th><?php echo sts\safe_output($language->get('Threads')); ?></th>
												<th><?php echo sts\safe_output($language->get('Posts')); ?></th>
											</tr>
										</thead>
										<?php
										$i = 0;
										foreach ($items as $item) {
										?>
											<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
												<td data-title="<?php echo sts\safe_output($language->get('Forum')); ?>" class="centre"><a href="<?php echo $config->get('address'); ?>/p/forum/<?php echo (int) $item['id']; ?>/"><?php echo sts\safe_output($item['name']); ?></a></td>
												<td data-title="<?php echo sts\safe_output($language->get('Last Post')); ?>" class="centre"><?php if ($item['last_update'] != 0 ) echo sts\time_ago_in_words($item['last_update']) . ' ago'; ?></td>								
												<td data-title="<?php echo sts\safe_output($language->get('Threads')); ?>" class="centre"><?php echo $item['forum_threads']; ?></td>								
												<td data-title="<?php echo sts\safe_output($language->get('Posts')); ?>" class="centre"><?php echo $item['forum_posts']; ?></td>								
											</tr>
										<?php $i++; } ?>
									</table>
								</section>
								<br />
								<h4><?php echo sts\safe_output($language->get('Latest Posts')); ?></h4>
								<section id="no-more-tables">	
									<table class="table table-striped table-bordered">
										<thead>
											<tr>
												<th><?php echo sts\safe_output($language->get('Title')); ?></th>
												<th><?php echo sts\safe_output($language->get('Forum')); ?></th>
												<th><?php echo sts\safe_output($language->get('Last Post')); ?></th>
												<th><?php echo sts\safe_output($language->get('Last Replier')); ?></th>
												<th><?php echo sts\safe_output($language->get('Replies')); ?></th>
											</tr>
										</thead>
										<?php
										$i = 0;
										foreach ($posts as $post) {
										?>
											<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
												<td data-title="<?php echo sts\safe_output($language->get('Title')); ?>" class="centre"><a href="<?php echo $config->get('address'); ?>/p/thread/<?php echo (int) $post['id']; ?>/"><?php echo sts\safe_output($post['title']); ?></a></td>
												<td data-title="<?php echo sts\safe_output($language->get('Forum')); ?>" class="centre"><?php echo sts\safe_output($post['section_name']); ?></td>
												<td data-title="<?php echo sts\safe_output($language->get('Last Post')); ?>" class="centre"><?php echo sts\time_ago_in_words($post['last_update']) . ' ago'; ?></td>								
												<td data-title="<?php echo sts\safe_output($language->get('Last Replier')); ?>" class="centre"><?php echo sts\safe_output(ucwords($post['last_name'])); ?></td>								
												<td data-title="<?php echo sts\safe_output($language->get('Replies')); ?>" class="centre"><?php echo (int) $post['forum_posts']; ?></td>								
											</tr>
										<?php $i++; } ?>				
									</table>
								</section>
							<!--</div>-->
						<?php } else { ?>
							<div class="alert alert-success">
								<a href="#" class="close" data-dismiss="alert">&times;</a>
								<?php echo sts\safe_output($language->get('No Forums Found')); ?>
							</div>					
						<?php } ?>		
					</div>	
				</div>			
			<?php
			break;
		
		}

	}
	
	
	//Forum Header
	public function plugin_page_header_forum() {
		$site 		= &sts\singleton::get('sts\site');
		$url 		= &sts\singleton::get('sts\url');
		$config 	= &sts\singleton::get('sts\config');	
		$auth 		= &sts\singleton::get('sts\auth');
		$language 	= &sts\singleton::get('sts\language');
		
		if (!$auth->can('forums') && !$auth->can('manage_forums')) {
			header('Location: ' . $config->get('address') . '/');
			exit;
		}

		$id = (int) $url->get_item();

		$forum_sections 		= &sts\singleton::get(__NAMESPACE__ . '\forum_sections');

		//$items		= $forum_sections->get(array('id' => $id));
		$items		= $forum_sections->get(array('id' => $id, 'group_id' => $auth->get('group_id')));
		
		if (!empty($items)) {
			//this sets the title of the page.
			$site->set_title('Forum - ' . $items[0]['name']);
			$this->current_forum = $items[0];
		}
		else {
			header('Location: ' . $config->get('address') . '/p/forums/');
			exit;
		}
		
	}
	
	//Forum Body
	public function plugin_page_body_forum() {
		$config 				= &sts\singleton::get('sts\config');
		$auth 					= &sts\singleton::get('sts\auth');
		$language 				= &sts\singleton::get('sts\language');
		$forum_threads 			= &sts\singleton::get(__NAMESPACE__ . '\forum_threads');
		
		if (isset($_GET['page']) && (int) $_GET['page'] != 0) {
			$page = (int) $_GET['page'];
		}
		else {
			$page = 1;
		}
				
		$limit = 50;
		
		$page_array_temp 			= sts\paging_start(array('page' => $page, 'limit' => $limit));
		$offset						= $page_array_temp['offset'];
	
		$items						= $forum_threads->get(array('where' => array('section_id' => $this->current_forum['id']), 'get_other_data' => true, 'limit' => $limit, 'offset' => $offset));
		
		$page_array 				= sts\paging_finish(array('events' => count($items), 'limit' => $limit, 'next_page' => $page_array_temp['next_page']));
		$next_page 					= $page_array['next_page'];
		$previous_page 				= $page_array_temp['previous_page'];
				
		$page_previous 	= $config->get('address') . '/p/forum/'.(int) $this->current_forum['id'].'/?page=' . (int)$previous_page;
		$page_next 		= $config->get('address') . '/p/forum/'.(int) $this->current_forum['id'].'/?page=' . (int)$next_page;
		
		switch(sts\CURRENT_THEME_TYPE) {
			case 'bootstrap3':
			?>
				<div class="row">
					<div class="col-md-3">
						<div class="well well-sm">
							<div class="pull-left">
								<h4><?php echo sts\safe_output($this->current_forum['name']); ?></h4>
							</div>
							<div class="pull-right">
								<a href="<?php echo $config->get('address'); ?>/p/add_thread/<?php echo (int) $this->current_forum['id']; ?>/" class="btn btn-default"><?php echo sts\safe_output($language->get('Add Thread')); ?></a>
							</div>				

							<div class="clearfix"></div>

						</div>
						
					</div>		
					<div class="col-md-9">		
					
						<ul class="breadcrumb">
							<li><a href="<?php echo $config->get('address') . '/p/forums/'; ?>"><?php echo sts\safe_output($language->get('Forums')); ?></a></li>
							<li class="active"><?php echo sts\safe_output($this->current_forum['name']); ?></li>
						</ul>
					
						<?php if (!empty($items)) { ?>
							<!--<div class="well well-small">-->

								<div class="pull-right">
									<ul class="pagination pagination-sm">
										<li><a href="<?php echo $page_previous; ?>">&laquo; <?php echo sts\safe_output($language->get('Previous')); ?></a></li>
										<li><a href=""><?php echo (int)$page; ?></a></li>
										<li><a href="<?php echo $page_next; ?>"><?php echo sts\safe_output($language->get('Next')); ?> &raquo;</a></li>
									</ul>
								</div>							
								
								<div class="clearfix"></div>
								
								<section id="no-more-tables">	
									<table class="table table-striped table-bordered">
										<thead>
											<tr>
												<th><?php echo sts\safe_output($language->get('Title')); ?></th>
												<th><?php echo sts\safe_output($language->get('Posted By')); ?></th>
												<th><?php echo sts\safe_output($language->get('Last Post')); ?></th>
												<th><?php echo sts\safe_output($language->get('Last Replier')); ?></th>
												<th><?php echo sts\safe_output($language->get('Views')); ?></th>
												<th><?php echo sts\safe_output($language->get('Replies')); ?></th>
											</tr>
										</thead>
										<?php
										$i = 0;
										foreach ($items as $item) {
										?>
											<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
												<td data-title="<?php echo sts\safe_output($language->get('Title')); ?>" class="centre"><a href="<?php echo $config->get('address'); ?>/p/thread/<?php echo (int) $item['id']; ?>/"><?php echo sts\safe_output($item['title']); ?></a></td>
												<td data-title="<?php echo sts\safe_output($language->get('Posted By')); ?>" class="centre"><?php echo sts\safe_output(ucwords($item['name'])); ?></td>
												<td data-title="<?php echo sts\safe_output($language->get('Last Post')); ?>" class="centre"><?php echo sts\time_ago_in_words($item['last_update']) . ' ago'; ?></td>								
												<td data-title="<?php echo sts\safe_output($language->get('Last Replier')); ?>" class="centre"><?php echo sts\safe_output(ucwords($item['last_name'])); ?></td>
												<td data-title="<?php echo sts\safe_output($language->get('Views')); ?>" class="centre"><?php echo (int) $item['views']; ?></td>
												<td data-title="<?php echo sts\safe_output($language->get('Replies')); ?>" class="centre"><?php echo (int) $item['forum_posts']; ?></td>
											</tr>
										<?php $i++; } ?>
									</table>
								</section>
							<!--</div>-->
						<?php } else { ?>
							<div class="alert alert-success">
								<a href="#" class="close" data-dismiss="alert">&times;</a>
								<?php echo sts\safe_output($language->get('No Threads Found')); ?>
							</div>
						<?php } ?>			
					</div>
				</div>				
			<?php
			break;
			
		}
	}
	
	function plugin_page_header_add_thread() {
		$site 		= &sts\singleton::get('sts\site');
		$url 		= &sts\singleton::get('sts\url');
		$config 	= &sts\singleton::get('sts\config');	
		$auth 		= &sts\singleton::get('sts\auth');	
		
		if (!$auth->can('forums') && !$auth->can('manage_forums')) {
			header('Location: ' . $config->get('address') . '/');
			exit;
		}
	
		$id = (int) $url->get_item();

		$forum_sections 				= &sts\singleton::get(__NAMESPACE__ . '\forum_sections');
		$forum_threads 					= &sts\singleton::get(__NAMESPACE__ . '\forum_threads');
		$forum_thread_subscriptions 	= &sts\singleton::get(__NAMESPACE__ . '\forum_thread_subscriptions');

		$items					= $forum_sections->get(array('id' => $id, 'group_id' => $auth->get('group_id')));
		
		if (!empty($items)) {
			//this sets the title of the page.
			$site->set_title('Add Thread - ' . $items[0]['name']);
			$this->current_forum = $items[0];
		}
		else {
			header('Location: ' . $config->get('address') . '/p/forums/');
			exit;
		}	
		
		if (isset($_POST['add'])) {
			if (!empty($_POST['title'])) {
				if (!empty($_POST['message'])) {
					$add_thread['section_id']		= $id;
					$add_thread['user_id']			= $auth->get('id');
					$add_thread['date_added']		= sts\datetime();
					$add_thread['last_modified']	= sts\datetime();
					$add_thread['title']			= $_POST['title'];
					$add_thread['message']			= $_POST['message'];
					
					$thread_id						= (int) $forum_threads->add(array('columns' => $add_thread));
					
					if (isset($_POST['subscribe']) && ($_POST['subscribe'] == 1)) {
						$forum_thread_subscriptions->add(array('columns' => array('user_id' => $auth->get('id'), 'thread_id' => $thread_id, 'date_added' => sts\datetime())));
					}
					
					header('Location: ' . $config->get('address') . '/p/thread/' . $thread_id . '/');
					exit;
				}
				else {
					$this->message = 'Message Empty';
				}
			}
			else {
				$this->message = 'Title Empty';
			}
		}
	}
	
	function plugin_page_body_add_thread() {
		$site 		= &sts\singleton::get('sts\site');
		$url 		= &sts\singleton::get('sts\url');
		$config 	= &sts\singleton::get('sts\config');	
		$language 	= &sts\singleton::get('sts\language');	
		
				
		switch(sts\CURRENT_THEME_TYPE) {
			case 'bootstrap3':
			?>
				<div class="row">

					<form method="post" action="<?php echo sts\safe_output($_SERVER['REQUEST_URI']); ?>">
					
						<div class="col-md-3">
							<div class="well well-sm">
								<div class="pull-left">
									<h4><?php echo sts\safe_output($language->get('Add Thread')); ?></h4>
								</div>
								<div class="pull-right">
									<p><button type="submit" name="add" class="btn btn-primary"><?php echo sts\safe_output($language->get('Add Thread')); ?></button></p>
								</div>
								<div class="clearfix"></div>
							</div>
						</div>

						<div class="col-md-9">
							
							<ul class="breadcrumb">
								<li><a href="<?php echo $config->get('address') . '/p/forums/'; ?>"><?php echo sts\safe_output($language->get('Forums')); ?></a></li>
								<li><a href="<?php echo $config->get('address') . '/p/forum/' . $this->current_forum['id'] . '/'; ?>"><?php echo sts\safe_output($this->current_forum['name']); ?></a></li>
								<li class="active"><?php echo sts\safe_output($language->get('Add Thread')); ?></li>
							</ul>
						
							<?php if (isset($this->message)) { ?>
								<div class="alert alert-danger">
									<a href="#" class="close" data-dismiss="alert">&times;</a>
									<?php echo sts\message($this->message); ?>
								</div>
							<?php } ?>
														
							<div class="well well-sm">
												
								<p><?php echo sts\safe_output($language->get('Title')); ?><br /><input class="form-control" type="text" name="title" value="<?php if (isset($_POST['title'])) echo sts\safe_output($_POST['title']); ?>" size="50" /></p>		
								<p><?php echo sts\safe_output($language->get('Message')); ?><br />
									<textarea class="wysiwyg_enabled" name="message" cols="80" rows="12"><?php if (isset($_POST['message'])) echo sts\safe_output($_POST['message']); ?></textarea>
								</p>

								<label class="checkbox">
									<input type="checkbox" name="subscribe" value="1" /> <?php echo sts\safe_output($language->get('Subscribe')); ?>
								</label>							
									
								<div class="clearfix"></div>

							</div>
						</div>

					</form>
				</div>			
			<?php
			break;
		
		}
			
	}
	
	function plugin_page_header_thread() {
		$site 		= &sts\singleton::get('sts\site');
		$url 		= &sts\singleton::get('sts\url');
		$config 	= &sts\singleton::get('sts\config');	
		$auth 		= &sts\singleton::get('sts\auth');	
	

		if (!$auth->can('forums') && !$auth->can('manage_forums')) {
			header('Location: ' . $config->get('address') . '/');
			exit;
		}
	
		$id = (int) $url->get_item();

		$forum_threads 			= &sts\singleton::get(__NAMESPACE__ . '\forum_threads');

		$items		= $forum_threads->get(array('id' => $id, 'get_other_data' => true, 'group_id' => $auth->get('group_id')));
		
		if (!empty($items)) {
			//this sets the title of the page.
			$site->set_title('View Thread - ' . $items[0]['title']);
			$this->current_thread = $items[0];
			$forum_threads->edit(array('columns' => array('views' => $this->current_thread['views'] + 1), 'id' => $this->current_thread['id']));
		}
		else {
			header('Location: ' . $config->get('address') . '/p/forums/');
			exit;
		}	
	}
	
	function plugin_page_body_thread() {
		$config 			= &sts\singleton::get('sts\config');	
		$gravatar 			= &sts\singleton::get('sts\gravatar');	
		$auth 				= &sts\singleton::get('sts\auth');	
		$language 			= &sts\singleton::get('sts\language');	

		$forum_posts 		= &sts\singleton::get(__NAMESPACE__ . '\forum_posts');

		
		$posts_array		= $forum_posts->get(array('where' => array('thread_id' => $this->current_thread['id']), 'get_other_data' => true, 'order_by' => 'id'));

	?>
	
		<script type="text/javascript" src="<?php echo $this->root; ?>/forums.js"></script>

		<script type="text/javascript">
			$(document).ready(function () {
				$('#delete').click(function () {
					if (confirm("<?php echo sts\safe_output($language->get('Are you sure you wish to delete this thread?')); ?>")){
						return true;
					}
					else{
						return false;
					}
				});
			});
		</script>
		
		<?php
			switch(sts\CURRENT_THEME_TYPE) {
				case 'bootstrap3':
				?>
					<div class="row">
						<div class="col-md-3">
							<div class="well well-sm">
								<div class="pull-left">
									<h4><?php echo sts\safe_output($language->get('View Thread')); ?></h4>
								</div>

								<?php if ($auth->can('manage_forums')) { ?>
									<div class="pull-right">
										<a href="<?php echo $config->get('address'); ?>/p/edit_thread/<?php echo (int) $this->current_thread['id']; ?>/" class="btn btn-default"><?php echo sts\safe_output($language->get('Edit')); ?></a>
									</div>
								<?php } ?>
								
								<div class="clearfix"></div>
								<br />
								<div class="pull-right">
									<a href="#" class="btn btn-info forums_thread_subscription" id="thread_id-<?php echo (int) $this->current_thread['id']; ?>">...</a>							
								</div>
								<div class="clearfix"></div>
								
							</div>
						</div>

						<div class="col-md-9">
							<?php if (isset($this->message)) { ?>
								<div class="alert alert-success">
									<a href="#" class="close" data-dismiss="alert">&times;</a>
									<?php echo sts\safe_output($this->message); ?>
								</div>
							<?php } ?>
							
							<ul class="breadcrumb">
								<li><a href="<?php echo $config->get('address') . '/p/forums/'; ?>"><?php echo sts\safe_output($language->get('Forums')); ?></a></li>
								<li><a href="<?php echo $config->get('address') . '/p/forum/' . $this->current_thread['section_id'] . '/'; ?>"><?php echo sts\safe_output($this->current_thread['section_name']); ?></a></li>
								<li class="active"><?php echo sts\safe_output($this->current_thread['title']); ?></li>
							</ul>
							
							<div class="panel panel-default">
								<div class="panel-heading">
									<div class="pull-left">
										<h1 class="panel-title"><?php echo sts\safe_output($this->current_thread['title']); ?></h1>
									</div>
									<div class="clearfix"></div>
								</div>
								<div class="panel-body">
									<?php if ($config->get('gravatar_enabled')) { ?>
										<div class="pull-right">
											<?php $gravatar->setEmail($this->current_thread['email']); ?>
											<img src="<?php echo $gravatar->getUrl(); ?>" alt="Gravatar" />
										</div>
									<?php } ?>

									<?php echo sts\html_output($this->current_thread['message']); ?>

								
									<div class="clearfix"></div>
								
								</div>
								
								<div class="panel-footer">
									<div class="pull-right">
										<small><?php echo sts\safe_output(ucwords($this->current_thread['name'])); ?> - <?php echo sts\safe_output(sts\time_ago_in_words($this->current_thread['date_added'])); ?> <?php echo sts\safe_output($language->get('ago')); ?></small>
									</div>
									<div class="clearfix"></div>
								</div>
							</div>
							
							<?php if (!empty($posts_array)) { ?>
								<div class="page-header">
									<h4><?php echo sts\safe_output($language->get('Replies')); ?></h4>
								</div>
								<?php $i = 0; foreach($posts_array as $post) { ?>
									<div class="panel panel-default">
										<div class="panel-body">
											<?php if ($config->get('gravatar_enabled')) { ?>
												<div class="pull-right">
													<?php $gravatar->setEmail($post['email']); ?>
													<img src="<?php echo $gravatar->getUrl(); ?>" alt="Gravatar" />
												</div>
											<?php } ?>

											<?php echo sts\html_output($post['message']); ?>
											<div class="clearfix"></div>
										
										</div>
										
										<div class="panel-footer">
											<div class="pull-right">
												<small><?php echo sts\safe_output(ucwords($post['name'])); ?> - <?php echo sts\safe_output(sts\time_ago_in_words($post['date_added'])); ?> <?php echo sts\safe_output($language->get('ago')); ?></small>
											</div>
											<div class="clearfix"></div>
										</div>
									</div>
								<?php } ?>
								
							<?php } ?>
							
							<div class="well-small">
								<div class="page-header">
									<h4><a name="addnote"></a><?php echo sts\safe_output($language->get('Reply')); ?></h4>
								</div>
													
								<form method="post" action="<?php echo $config->get('address'); ?>/simple/add_thread_post/<?php echo (int) $this->current_thread['id']; ?>/">
									<p><textarea class="wysiwyg_enabled" name="message" cols="70" rows="12"></textarea></p>
									
									<label class="checkbox">
										<input type="checkbox" name="subscribe" value="1" /> <?php echo sts\safe_output($language->get('Subscribe')); ?>
									</label>
									
									<br />
									
									<p><input type="hidden" name="id" value="<?php echo (int) $this->current_thread['id']; ?>" /><button name="add" type="submit" class="btn btn-primary"><?php echo sts\safe_output($language->get('Add Reply')); ?></button></p>
								</form>
								
								<div class="clearfix"></div>
							</div>
						
						</div>	
					</div>				
				<?php
				break;
	
			}
	}
	
	function simple_page_header_add_thread_post() {
		$site 							= &sts\singleton::get('sts\site');
		$url 							= &sts\singleton::get('sts\url');
		$config 						= &sts\singleton::get('sts\config');	
		$auth 							= &sts\singleton::get('sts\auth');	
		$forum_threads 					= &sts\singleton::get(__NAMESPACE__ . '\forum_threads');
		$forum_posts 					= &sts\singleton::get(__NAMESPACE__ . '\forum_posts');
		$forum_thread_subscriptions 	= &sts\singleton::get(__NAMESPACE__ . '\forum_thread_subscriptions');
	
		if (!$auth->can('forums') && !$auth->can('manage_forums')) {
			header('Location: ' . $config->get('address') . '/');
			exit;
		}
		
		if ($auth->logged_in()) {
		
			if (isset($_POST['add'])) {
				$id = (int) $url->get_item();
				
				$items		= $forum_threads->get(array('id' => $id, 'group_id' => $auth->get('group_id')));
				
				if (!empty($items)) {
					$add_post['message']	= $_POST['message'];
					$add_post['user_id']	= $auth->get('id');
					$add_post['date_added']	= sts\datetime();
					$add_post['thread_id']	= $id;
					
					$forum_posts->add(array('columns' => $add_post));
					
					$notify 			= $items[0];
					$notify['new_post']	= $add_post;
					
					$this->notify_thread_subscribed_users($notify);
					
					if (isset($_POST['subscribe']) && ($_POST['subscribe'] == 1)) {
						$forum_thread_subscriptions->add(array('columns' => array('user_id' => $auth->get('id'), 'thread_id' => $id, 'date_added' => sts\datetime())));
					}

					
					header('Location: ' . $config->get('address') . '/p/thread/' . $id . '/');
					exit;
					
				}
				else {
					header('Location: ' . $config->get('address') . '/p/forums/');
					exit;
				}
			}
		}
		header('Location: ' . $config->get('address') . '/');
		exit;

	}
	
	//this adds the settings menu item
	public function html_header_nav_settings() {
		$config 		= &sts\singleton::get('sts\config');
		$auth 			= &sts\singleton::get('sts\auth');
		$language 		= &sts\singleton::get('sts\language');	

		if ($auth->can('manage_forums')) {
			?>
			<li><a href="<?php echo $config->get('address'); ?>/p/forum_settings/"><span><?php echo sts\safe_output($language->get('Forums')); ?></span></a></li>
			<?php
		}
	}
	
	public function plugin_page_header_forum_settings() {
		$site 					= &sts\singleton::get('sts\site');
		$config 				= &sts\singleton::get('sts\config');
		$auth 					= &sts\singleton::get('sts\auth');

		$site->set_title('Forum Setting');

		if (!$auth->can('manage_forums')) {
			header('Location: ' . $config->get('address') . '/');
			exit;
		}
		
	}
	
	public function plugin_page_body_forum_settings() {
		$config 		= &sts\singleton::get('sts\config');
		$plugins 		= &sts\singleton::get('sts\plugins');
		$language 		= &sts\singleton::get('sts\language');

		$forum_sections 	= &sts\singleton::get(__NAMESPACE__ . '\forum_sections');		
		
		$items = $forum_sections->get(array('order_by' => 'id', 'get_other_data' => true));
	
		switch(sts\CURRENT_THEME_TYPE) {
			case 'bootstrap3':
			?>
				<div class="row">	
		
					<form method="post" action="<?php echo sts\safe_output($_SERVER['REQUEST_URI']); ?>">

						<div class="col-md-3">
							<div class="well well-sm">
								<div class="pull-left">
									<h4><?php echo sts\safe_output($language->get('Forum Settings')); ?></h4>
								</div>
															
								<div class="clearfix"></div>

							</div>
						</div>

						<div class="col-md-9">
						
							<?php if (isset($this->message)) { ?>
								<div class="alert alert-success">
									<a href="#" class="close" data-dismiss="alert">&times;</a>
									<?php echo sts\message($this->message); ?>
								</div>
							<?php } ?>
												
							<div class="pull-left">
								<h3><?php echo sts\safe_output($language->get('Forum Sections')); ?></h3>
							</div>
							
							<div class="pull-right">
								<p><a href="<?php echo sts\safe_output($config->get('address')); ?>/p/add_forum_section/" class="btn btn-default"><?php echo sts\safe_output($language->get('Add')); ?></a></p>
							</div>
							
							<div class="clearfix"></div>
							
							<section id="no-more-tables">	
								<table class="table table-striped table-bordered">
									<thead>
										<tr>
											<th><?php echo sts\safe_output($language->get('Name')); ?></th>
											<th><?php echo sts\safe_output($language->get('Threads')); ?></th>
											<th><?php echo sts\safe_output($language->get('Posts')); ?></th>
										</tr>
									</thead>
									<?php
										$i = 0;
										foreach ($items as $item) {
									?>
									<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
										<td data-title="<?php echo sts\safe_output($language->get('Name')); ?>" class="centre"><a href="<?php echo $config->get('address'); ?>/p/edit_forum_section/<?php echo (int) $item['id']; ?>/"><?php echo sts\safe_output($item['name']); ?></a></td>
										<td data-title="<?php echo sts\safe_output($language->get('Threads')); ?>" class="centre"><?php echo sts\safe_output($item['forum_threads']); ?></td>
										<td data-title="<?php echo sts\safe_output($language->get('Posts')); ?>" class="centre"><?php echo sts\safe_output($item['forum_posts']); ?></td>
									</tr>
									<?php $i++; } ?>
								</table>
							</section>
							
							<div class="clearfix"></div>

						</div>
					</form>
				</div>			
			<?php
			break;
			
		}
		
	}
	
	public function plugin_page_header_edit_thread() {
		$site 		= &sts\singleton::get('sts\site');
		$url 		= &sts\singleton::get('sts\url');
		$config 	= &sts\singleton::get('sts\config');	
		$auth 		= &sts\singleton::get('sts\auth');	
			
		if (!$auth->can('manage_forums')) {
			header('Location: ' . $config->get('address') . '/');
			exit;
		}

		$id = (int) $url->get_item();

		$forum_threads 			= &sts\singleton::get(__NAMESPACE__ . '\forum_threads');
		$forum_sections 		= &sts\singleton::get(__NAMESPACE__ . '\forum_sections');

	
		$items		= $forum_threads->get(array('id' => $id, 'get_other_data' => true, 'group_id' => $auth->get('group_id')));
		
		if (!empty($items)) {
			//this sets the title of the page.
			$site->set_title('Edit Thread - ' . $items[0]['title']);
			$this->current_thread = $items[0];
			
			if (isset($_POST['save'])) {
				$edit_array['title']		= $_POST['title'];
				$edit_array['message']		= $_POST['message'];
				
				$new_thread		= $forum_sections->get(array('id' => (int) $_POST['section_id'], 'group_id' => $auth->get('group_id')));

				if (!empty($new_thread)) {
					$edit_array['section_id']	= (int) $_POST['section_id'];
				}
				
				$forum_threads->edit(array('columns' => $edit_array, 'id' => $id));
				
				header('Location: ' . $config->get('address') . '/p/thread/' . $id . '/');
				exit;
			}
			else if (isset($_POST['delete'])) {
				$forum_threads->delete(array('id' => $id));

				header('Location: ' . $config->get('address') . '/p/forums/');
				exit;		
			}
		}
		else {
			header('Location: ' . $config->get('address') . '/p/forums/');
			exit;
		}
	
	}

	public function plugin_page_body_edit_thread() {
		$site 				= &sts\singleton::get('sts\site');
		$url 				= &sts\singleton::get('sts\url');
		$config 			= &sts\singleton::get('sts\config');	
		$auth 				= &sts\singleton::get('sts\auth');	
		$language			= &sts\singleton::get('sts\language');	
		$gravatar 			= &sts\singleton::get('sts\gravatar');	
			
		$forum_sections 	= &sts\singleton::get(__NAMESPACE__ . '\forum_sections');
		
		$items				= $forum_sections->get(array('order_by' => 'id', 'group_id' => $auth->get('group_id')));
	
		?>
		<script type="text/javascript">
			$(document).ready(function () {
				$('#delete').click(function () {
					if (confirm("<?php echo sts\safe_output($language->get('Are you sure you wish to delete this thread?')); ?>")){
						return true;
					}
					else{
						return false;
					}
				});
			});
		</script>
		
		<?php
			switch(sts\CURRENT_THEME_TYPE) {
				case 'bootstrap3':
				?>
					<div class="row">
				
						<form method="post" action="<?php echo $config->get('address'); ?>/p/edit_thread/<?php echo (int) $this->current_thread['id']; ?>/">
							
							<div class="col-md-3">
								<div class="well well-sm">
									<div class="pull-left">
										<h4><?php echo sts\safe_output($language->get('Edit Thread')); ?></h4>
									</div>
									<div class="pull-right">
										<p>
											<button type="submit" name="save" class="btn btn-primary"><?php echo sts\safe_output($language->get('Save')); ?></button>
											<a href="<?php echo $config->get('address'); ?>/p/thread/<?php echo (int) $this->current_thread['id']; ?>/" class="btn btn-default"><?php echo sts\safe_output($language->get('Cancel')); ?></a>
										</p>
									</div>
									
									<div class="clearfix"></div>
									<br />
									<div class="pull-right">
										<p><button type="submit" id="delete" name="delete" class="btn btn-danger"><?php echo sts\safe_output($language->get('Delete')); ?></button></p>		
									</div>
										
									
									<div class="clearfix"></div>
								
								</div>
							</div>

							<div class="col-md-9">
								<?php if (isset($this->message)) { ?>
								<div class="alert alert-success">
									<a href="#" class="close" data-dismiss="alert">&times;</a>
									<?php echo sts\message($this->message); ?>
								</div>	
								<?php } ?>
								
								<div class="well well-sm">
									<?php if ($config->get('gravatar_enabled')) { ?>
										<div class="pull-right">
											<?php $gravatar->setEmail($this->current_thread['email']); ?>
											<img src="<?php echo $gravatar->getUrl(); ?>" alt="Gravatar" />
										</div>
									<?php } ?>		

									<div class="col-lg-10">
										<p><?php echo $language->get('Subject'); ?><br /><input type="text" class="form-control" name="title" value="<?php echo sts\safe_output($this->current_thread['title']); ?>" size="30" /></p>
											
										<p><?php echo $language->get('Section'); ?><br />
										<select name="section_id">
											<?php foreach ($items as $item) { ?>
											<option value="<?php echo (int) $item['id']; ?>"<?php if ($item['id'] == $this->current_thread['section_id']) { echo ' selected="selected"'; } ?>><?php echo sts\safe_output($item['name']); ?></option>
											<?php } ?>
										</select></p>
										
										<p><?php echo sts\safe_output($language->get('Message')); ?><br /><textarea class="wysiwyg_enabled" name="message" cols="70" rows="12"><?php echo sts\html_output($this->current_thread['message']); ?></textarea></p>
										
										<div class="clearfix"></div>
										<div class="pull-right">
											<p><?php echo sts\safe_output(ucwords($this->current_thread['name'])); ?> - <?php echo sts\safe_output(sts\time_ago_in_words($this->current_thread['date_added'])); ?> ago</p>
										</div>
										<div class="clearfix"></div>
									</div>
									<div class="clearfix"></div>

								</div>
							</div>
						
						</form>
					</div>					
				<?php
				break;
				
			}
	}

	public function plugin_page_header_add_forum_section() {
		$site 							= &sts\singleton::get('sts\site');
		$url 							= &sts\singleton::get('sts\url');
		$config 						= &sts\singleton::get('sts\config');	
		$auth 							= &sts\singleton::get('sts\auth');	
		$forum_sections 				= &sts\singleton::get(__NAMESPACE__ . '\forum_sections');	
		$forum_to_permission_groups 	= &sts\singleton::get(__NAMESPACE__ . '\forum_to_permission_groups');	

		if (!$auth->can('manage_forums')) {
			header('Location: ' . $config->get('address') . '/');
			exit;
		}
	
		$site->set_title('Add Forum Section');

		if (isset($_POST['add'])) {
			if (!empty($_POST['name'])) {
				$id = $forum_sections->add(array('columns' => array('name' => $_POST['name'])));
				
				if (!empty($_POST['group_id'])) {
					foreach ($_POST['group_id'] as $group_id) {
						$forum_to_permission_groups->add(array('columns' => array('section_id' => $id, 'group_id' => (int) $group_id)));
					}
				}

				header('Location: ' . $config->get('address') . '/p/forum_settings/');
				exit;			
			}
			else {
				$this->message = 'Name Empty';
			}
		}
	}
	
	public function plugin_page_body_add_forum_section() {
		$site 				= &sts\singleton::get('sts\site');
		$url 				= &sts\singleton::get('sts\url');
		$config 			= &sts\singleton::get('sts\config');	
		$auth 				= &sts\singleton::get('sts\auth');	
		$language 			= &sts\singleton::get('sts\language');	
		$permission_groups 	= &sts\singleton::get('sts\permission_groups');	
		

		$groups 		= $permission_groups->get();

		
		switch(sts\CURRENT_THEME_TYPE) {
			case 'bootstrap3':
			?>
				<div class="row">

					<form method="post" action="<?php echo sts\safe_output($_SERVER['REQUEST_URI']); ?>">

						<div class="col-md-3">
							<div class="well well-sm">
								<div class="pull-right">
									<p>
										<button type="submit" name="add" class="btn btn-primary"><?php echo sts\safe_output($language->get('Add')); ?></button>
										<a href="<?php echo $config->get('address'); ?>/p/forum_settings/" class="btn btn-default"><?php echo sts\safe_output($language->get('Cancel')); ?></a>
									</p>
								</div>
								
								<div class="pull-left">
									<h4><?php echo sts\safe_output($language->get('Forum Section')); ?></h4>
								</div>
											

									
								<div class="clearfix"></div>	
								
							</div>
						</div>

						<div class="col-md-9">

							<?php if (isset($this->message)) { ?>
								<div class="alert alert-danger">
									<a href="#" class="close" data-dismiss="alert">&times;</a>
									<?php echo sts\html_output($this->message); ?>
								</div>
							<?php } ?>

							<div class="well well-sm">	

								<p><?php echo $language->get('Name'); ?><br /><input class="form-control" type="text" name="name" value="<?php if (isset($_POST['name'])) echo sts\safe_output($_POST['name']); ?>" size="30" /></p>
								
								<p><?php echo sts\safe_output($language->get('Permissions')); ?><br />
									<?php foreach($groups as $group) { ?>
										<input type="checkbox" name="group_id[]" value="<?php echo (int) $group['id']; ?>" <?php if (isset($_POST['group_id']) && in_array($group['id'], $_POST['group_id'])) echo 'checked="checked"'; ?> /> <?php echo sts\safe_output($group['name']); ?><br />
									<?php } ?>
								</p>
							</div>
								
						</div>

					</form>
				</div>				
			<?php
			break;
			
		}

	}

	public function plugin_page_header_edit_forum_section() {
		$site 							= &sts\singleton::get('sts\site');
		$url 							= &sts\singleton::get('sts\url');
		$config 						= &sts\singleton::get('sts\config');	
		$auth 							= &sts\singleton::get('sts\auth');	
		$forum_sections 				= &sts\singleton::get(__NAMESPACE__ . '\forum_sections');	
		$forum_to_permission_groups 	= &sts\singleton::get(__NAMESPACE__ . '\forum_to_permission_groups');	
		
		if (!$auth->can('manage_forums')) {
			header('Location: ' . $config->get('address') . '/');
			exit;
		}
	
		$site->set_title('Edit Forum Section');
		
		$id = (int) $url->get_item();
		
		$section = $forum_sections->get(array('id' => $id));

		if (!empty($section)) {
			$this->current_forum = $section[0];
		}
		else {
			header('Location: ' . $config->get('address') . '/p/forum_settings/');
			exit;			
		}
		
		if (isset($_POST['delete'])) {
			$forum_sections->delete(array('id' => $id));
			header('Location: ' . $config->get('address') . '/p/forum_settings/');
			exit;
		}

		
		if (isset($_POST['save'])) {
			if (!empty($_POST['name'])) {
				$forum_sections->edit(array('columns' => array('name' => $_POST['name']), 'id' => $id));
				
				$forum_to_permission_groups->delete(array('columns' => array('section_id' => $id)));
				if (!empty($_POST['group_id'])) {
					foreach ($_POST['group_id'] as $group_id) {
						$forum_to_permission_groups->add(array('columns' => array('section_id' => $id, 'group_id' => (int) $group_id)));
					}
				}

				header('Location: ' . $config->get('address') . '/p/forum_settings/');
				exit;			
			}
			else {
				$this->message = 'Name Empty';
			}
		}
	}
	
	public function plugin_page_body_edit_forum_section() {
		$site 							= &sts\singleton::get('sts\site');
		$url 							= &sts\singleton::get('sts\url');
		$config 						= &sts\singleton::get('sts\config');	
		$auth 							= &sts\singleton::get('sts\auth');	
		$language 						= &sts\singleton::get('sts\language');	
		$forum_to_permission_groups 	= &sts\singleton::get(__NAMESPACE__ . '\forum_to_permission_groups');	
		$permission_groups 	= &sts\singleton::get('sts\permission_groups');	
		

		$groups 		= $permission_groups->get();

		$permissions_array = $forum_to_permission_groups->get(array('where' => array('section_id' => $this->current_forum['id'])));
		
		$permissions = array();
		foreach($permissions_array as $item) {
			$permissions[] = $item['group_id'];
		}
		
		?>
		<script type="text/javascript">
			$(document).ready(function () {
				$('#delete').click(function () {
					if (confirm("<?php echo sts\safe_output($language->get('Are you sure you wish to permanently delete this forum section (including all threads and posts)?')); ?>")){
						return true;
					}
					else{
						return false;
					}
				});
			});
		</script>
		
		<?php
			switch(sts\CURRENT_THEME_TYPE) {
				case 'bootstrap3':
				?>
					<div class="row">

						<form method="post" action="<?php echo sts\safe_output($_SERVER['REQUEST_URI']); ?>">

							<div class="col-md-3">
								<div class="well well-sm">
									<div class="pull-right">
										<p>
											<button type="submit" name="save" class="btn btn-primary"><?php echo sts\safe_output($language->get('Save')); ?></button>
											<a href="<?php echo $config->get('address'); ?>/p/forum_settings/" class="btn btn-default"><?php echo sts\safe_output($language->get('Cancel')); ?></a>
										</p>
									</div>
									
									<div class="pull-left">
										<h4><?php echo sts\safe_output($language->get('Forum Section')); ?></h4>
									</div>
										
									<div class="clearfix"></div>	
								
									<br />
									<div class="pull-right">
										<button type="submit" id="delete" name="delete" class="btn btn-danger"><?php echo sts\safe_output($language->get('Delete')); ?></button>
									</div>
									<div class="clearfix"></div>
									
								</div>
							</div>

							<div class="col-md-9">

								<?php if (isset($this->message)) { ?>
									<div class="alert alert-danger">
										<a href="#" class="close" data-dismiss="alert">&times;</a>
										<?php echo sts\html_output($this->message); ?>
									</div>
								<?php } ?>

								<div class="well well-sm">	
									
									<p><?php echo $language->get('Name'); ?><br /><input class="form-control" type="text" name="name" value="<?php echo sts\safe_output($this->current_forum['name']); ?>" size="30" /></p>
				
									<p><?php echo sts\safe_output($language->get('Permissions')); ?><br />
										<?php foreach($groups as $group) { ?>
											<input type="checkbox" name="group_id[]" value="<?php echo (int) $group['id']; ?>" <?php if (in_array($group['id'], $permissions)) echo 'checked="checked"'; ?> /> <?php echo sts\safe_output($group['name']); ?><br />
										<?php } ?>
									</p>
										
								</div>
									
							</div>

						</form>
					</div>				
				<?php
				break;
				
			}
	}
	
	function simple_page_header_forum_get_subscription() {
		$config 						= &sts\singleton::get('sts\config');
		$auth 							= &sts\singleton::get('sts\auth');
		$url 							= &sts\singleton::get('sts\url');
		$forum_thread_subscriptions 	= &sts\singleton::get(__NAMESPACE__ . '\forum_thread_subscriptions');	
		$forum_threads 					= &sts\singleton::get(__NAMESPACE__ . '\forum_threads');
		
		if (!$auth->can('forums') && !$auth->can('manage_forums')) {
			header('Location: ' . $config->get('address') . '/');
			exit;
		}

		$id = (int) $url->get_item();

		$items		= $forum_threads->get(array('id' => $id, 'get_other_data' => true, 'group_id' => $auth->get('group_id')));
		
		if (!empty($items)) {		
			if (isset($_POST['save'])) {
				if ($id != 0) {
					$results = $forum_thread_subscriptions->get(array('where' => array('user_id' => $auth->get('id'), 'thread_id' => $id)));
					
					if (empty($results)) {
						$forum_thread_subscriptions->add(array('columns' => array('user_id' => $auth->get('id'), 'thread_id' => $id, 'date_added' => sts\datetime())));
						
						echo json_encode(array('subscribed' => 1));
					}
					else {
						$forum_thread_subscriptions->delete(array('columns' => array('user_id' => $auth->get('id'), 'thread_id' => $id)));

						echo json_encode(array('subscribed' => 0));
					}
				}		
			}
			else {		
				if ($id != 0) {
					$results = $forum_thread_subscriptions->get(array('where' => array('user_id' => $auth->get('id'), 'thread_id' => $id)));
					
					if (!empty($results)) {
						echo json_encode(array('subscribed' => 1));
					}
					else {
						echo json_encode(array('subscribed' => 0));
					}
				}
			}
		}
		
	}
	
	
	function notify_thread_subscribed_users($array) {
		$config 						= &sts\singleton::get('sts\config');
		$auth 							= &sts\singleton::get('sts\auth');
		$url 							= &sts\singleton::get('sts\url');
		$mailer 						= &sts\singleton::get('sts\mailer');

		$forum_thread_subscriptions 	= &sts\singleton::get(__NAMESPACE__ . '\forum_thread_subscriptions');	
		
		$results = $forum_thread_subscriptions->get_users(array('thread_id' => $array['id']));
				
		foreach($results as $user) {
			if (!empty($user['email']) && $user['email_notifications'] && $array['new_post']['user_id'] != $user['id']) {
				
				$email_array['subject']				= $config->get('name') . ' - New Forum Post';
				$email_array['body']				= 'Hi ' . sts\safe_output($user['name']) . ",<br /><br />";
				$email_array['body']				.= 'A new post on the thread "'. sts\safe_output($array['title']) .'" has been made at ' . sts\safe_output($config->get('name')) . '.';
				$email_array['body']				.= "<br /><br />";
				$email_array['body']				.= '<a href="'.sts\safe_output($config->get('address')).'/p/thread/'.(int)$array['id'].'/">'.sts\safe_output($config->get('address')).'/p/thread/'.(int)$array['id'].'/</a>';
				
				$email_array['to']['to']			= $user['email'];
				$email_array['to']['to_name']		= $user['name'];
				$email_array['html']				= true;
				
				$mailer->queue_email($email_array);
				unset($email_array);
			}
		}
	
	}
	
	//Forums Header
	public function plugin_page_header_forums_search() {
		$site 		= &sts\singleton::get('sts\site');
		$language 	= &sts\singleton::get('sts\language');
		$auth 		= &sts\singleton::get('sts\auth');
		$config 	= &sts\singleton::get('sts\config');
		
		if (!$auth->can('forums') && !$auth->can('manage_forums')) {
			header('Location: ' . $config->get('address') . '/');
			exit;
		}
		
		//this sets the title of the page.
		$site->set_title($language->get('Forums Search'));
		
		if (!isset($_POST['like_search']) || empty($_POST['like_search'])) {
			header('Location: ' . $config->get('address') . '/p/forums/');
			exit;			
		}
	
	}
	
	//Forums Body
	public function plugin_page_body_forums_search() {
		$config 				= &sts\singleton::get('sts\config');
		$auth 					= &sts\singleton::get('sts\auth');
		$language 				= &sts\singleton::get('sts\language');

		$forum_threads 			= &sts\singleton::get(__NAMESPACE__ . '\forum_threads');
		
		
		$posts					= $forum_threads->get(array('get_other_data' => true, 'like_search' => $_POST['like_search'], 'group_id' => $auth->get('group_id')));
		
		switch(sts\CURRENT_THEME_TYPE) {
			case 'bootstrap3':
			?>
				<div class="row">
					<div class="col-md-3">
						<div class="well well-sm">
							<div class="left">
								<h4><?php echo $language->get('Forums Search'); ?></h4>
							</div>

							<div class="clearfix"></div>

						</div>	
						
					<div class="well well-sm">
						<form method="post" action="<?php echo sts\safe_output($config->get('address')); ?>/p/forums_search/">
							
							<div class="form-group">		
								<input type="text" class="form-control" placeholder="<?php echo sts\safe_output($language->get('Search')); ?>" name="like_search" value="<?php if (isset($_POST['like_search'])) { echo sts\safe_output($_POST['like_search']); } ?>" size="15" />
							</div>
							<div class="clearfix"></div>
						
							<br />
							
							<div class="pull-right">
								<p>
									<button type="submit" name="filter" class="btn btn-info"><?php echo sts\safe_output($language->get('Search')); ?></button>
									<a class="btn btn-default" href="<?php echo sts\safe_output($config->get('address')); ?>/p/forums/"><?php echo sts\safe_output($language->get('Clear')); ?></a>
								</p>
							</div>
							<div class="clearfix"></div>
						</form>		
					</div>	
					</div>
					<div class="col-md-9">
						<?php if (!empty($posts)) { ?>
							<div class="panel panel-default">
								<div class="panel-heading">
									<div class="pull-left">
										<h1 class="panel-title"><?php echo sts\safe_output($language->get('Posts Found')); ?></h1>
									</div>
									<div class="clearfix"></div>
								</div>
								<div class="panel-body">							
									<section id="no-more-tables">	
										<table class="table table-striped table-bordered">
											<thead>
												<tr>
													<th><?php echo sts\safe_output($language->get('Title')); ?></th>
													<th><?php echo sts\safe_output($language->get('Forum')); ?></th>
													<th><?php echo sts\safe_output($language->get('Last Post')); ?></th>
													<th><?php echo sts\safe_output($language->get('Last Replier')); ?></th>
													<th><?php echo sts\safe_output($language->get('Replies')); ?></th>
												</tr>
											</thead>
											<?php
											$i = 0;
											foreach ($posts as $post) {
											?>
												<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
													<td data-title="<?php echo sts\safe_output($language->get('Title')); ?>" class="centre"><a href="<?php echo $config->get('address'); ?>/p/thread/<?php echo (int) $post['id']; ?>/"><?php echo sts\safe_output($post['title']); ?></a></td>
													<td data-title="<?php echo sts\safe_output($language->get('Forum')); ?>" class="centre"><?php echo sts\safe_output($post['section_name']); ?></td>
													<td data-title="<?php echo sts\safe_output($language->get('Last Post')); ?>" class="centre"><?php echo sts\time_ago_in_words($post['last_update']) . ' ago'; ?></td>								
													<td data-title="<?php echo sts\safe_output($language->get('Last Replier')); ?>" class="centre"><?php echo sts\safe_output(ucwords($post['last_name'])); ?></td>								
													<td data-title="<?php echo sts\safe_output($language->get('Replies')); ?>" class="centre"><?php echo (int) $post['forum_posts']; ?></td>								
												</tr>
											<?php $i++; } ?>				
										</table>
									</section>
								</div>
							</div>
						<?php } else { ?>
							<div class="alert alert-success">
								<a href="#" class="close" data-dismiss="alert">&times;</a>
								<?php echo sts\safe_output($language->get('No Posts Found')); ?>
							</div>					
						<?php } ?>		
					</div>	
				</div>			
			<?php
			break;
		
		}

	}
	

}

?>