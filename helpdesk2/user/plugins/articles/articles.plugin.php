<?php
/*
	Articles Plugin for Tickets.
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
class articles {

	private $message 	= NULL;
	private $edit_array = NULL;

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
			'name' 				=> 'Articles',
			'version' 			=> '5.0',
			'description'		=> 'This plugin adds a section to store Articles (Knowledge Base items).',
			'website'			=> 'http://codecanyon.net/item/tickets/2478843?ref=michaeldale',
			'author'			=> 'Michael Dale',
			'author_website'	=> 'http://bluetrait.com/',
			'update_check_url'	=> 'http://api.apptrack.com.au/api/',
			'application_id'	=> 14			
		);

		return $info;
	}
	
	/*
		This function is called on each page load if the plugin is enabled.
		It is required.
	*/
	public function load() {
		
		//this is a custom class from the plugin.
		include('articles_support.class.php');
		$articles_support 		= &sts\singleton::get(__NAMESPACE__ . '\articles_support');
		
		include('articles_item.class.php');
		$articles_item 			= &sts\singleton::get(__NAMESPACE__ . '\articles_item');

		include('articles_categories.class.php');
		$articles_categories 	= &sts\singleton::get(__NAMESPACE__ . '\articles_categories');		


		include('articles_files.class.php');
		$articles_files 		= &sts\singleton::get(__NAMESPACE__ . '\articles_files');	
		
		//this is how you get an existing class
		$plugins 		= &sts\singleton::get('sts\plugins');	
		
		/*
			If you are using new database tables make sure you add them!
			In future table prefixes might be supported, this code will support it :)
		*/
		$tables 		= &sts\singleton::get('sts\tables');
		$tables->add_table('article_categories');
		
		//makes sure that 
		$articles_support->make_installed();		
				
		//This hooks into the menu system
		$plugins->add(
			array(
				'plugin_name'	=> __CLASS__,
				'task_name'		=> __CLASS__ . '_html_header_nav_start',
				'section'		=> 'html_header_nav_start',
				'method'		=> array($this, 'html_header_nav_start')
			)
		);
		
						
		//This hooks into the settings menu system
		$plugins->add(
			array(
				'plugin_name'	=> __CLASS__,
				'task_name'		=> __CLASS__ . '_html_header_nav_settings',
				'section'		=> 'html_header_nav_settings',
				'method'		=> array($this, 'html_header_nav_settings')
			)
		);
	
		/*
			View Articles
		*/

		//title
		$plugins->add(
			array(
				'plugin_name'	=> __CLASS__,
				'task_name'		=> __CLASS__ . '_plugin_page_header_kb',
				'section'		=> 'plugin_page_header_kb',
				'method'		=> array($this, 'plugin_page_header_kb')
			)
		);
		
		//html
		$plugins->add(
			array(
				'plugin_name'	=> __CLASS__,
				'task_name'		=> __CLASS__ . '_plugin_page_body_kb',
				'section'		=> 'plugin_page_body_kb',
				'method'		=> array($this, 'plugin_page_body_kb')
			)
		);
		
		/*
			Add Article
		*/
		
		//title
		$plugins->add(
			array(
				'plugin_name'	=> __CLASS__,
				'task_name'		=> __CLASS__ . '_plugin_page_header_kb_add',
				'section'		=> 'plugin_page_header_kb_add',
				'method'		=> array($this, 'plugin_page_header_kb_add')
			)
		);
		
		//html
		$plugins->add(
			array(
				'plugin_name'	=> __CLASS__,
				'task_name'		=> __CLASS__ . '_plugin_page_body_kb_add',
				'section'		=> 'plugin_page_body_kb_add',
				'method'		=> array($this, 'plugin_page_body_kb_add')
			)
		);
		
		/*
			View Article
		*/

		//title
		$plugins->add(
			array(
				'plugin_name'	=> __CLASS__,
				'task_name'		=> __CLASS__ . '_plugin_page_header_kb_view',
				'section'		=> 'plugin_page_header_kb_view',
				'method'		=> array($this, 'plugin_page_header_kb_view')
			)
		);
		
		//html
		$plugins->add(
			array(
				'plugin_name'	=> __CLASS__,
				'task_name'		=> __CLASS__ . '_plugin_page_body_kb_view',
				'section'		=> 'plugin_page_body_kb_view',
				'method'		=> array($this, 'plugin_page_body_kb_view')
			)
		);
		
		/*
			Edit Article
		*/
		
		//title
		$plugins->add(
			array(
				'plugin_name'	=> __CLASS__,
				'task_name'		=> __CLASS__ . '_plugin_page_header_kb_edit',
				'section'		=> 'plugin_page_header_kb_edit',
				'method'		=> array($this, 'plugin_page_header_kb_edit')
			)
		);
		
		//html
		$plugins->add(
			array(
				'plugin_name'	=> __CLASS__,
				'task_name'		=> __CLASS__ . '_plugin_page_body_kb_edit',
				'section'		=> 'plugin_page_body_kb_edit',
				'method'		=> array($this, 'plugin_page_body_kb_edit')
			)
		);
		
		/*
			Categories
		*/
		//title
		$plugins->add(
			array(
				'plugin_name'	=> __CLASS__,
				'task_name'		=> __CLASS__ . '_plugin_page_header_kb_categories',
				'section'		=> 'plugin_page_header_kb_categories',
				'method'		=> array($this, 'plugin_page_header_kb_categories')
			)
		);
		
		//html
		$plugins->add(
			array(
				'plugin_name'	=> __CLASS__,
				'task_name'		=> __CLASS__ . '_plugin_page_body_kb_categories',
				'section'		=> 'plugin_page_body_kb_categories',
				'method'		=> array($this, 'plugin_page_body_kb_categories')
			)
		);
		
		/*
			Used for Ajax category delete
		*/
		//html
		$plugins->add(
			array(
				'plugin_name'	=> __CLASS__,
				'task_name'		=> __CLASS__ . '_simple_page_body_kb_category_delete',
				'section'		=> 'simple_page_body_kb_category_delete',
				'method'		=> array($this, 'simple_page_body_kb_category_delete')
			)
		);
		
		/*
			Used for Ajax file delete
		*/
		$plugins->add(
			array(
				'plugin_name'	=> __CLASS__,
				'task_name'		=> __CLASS__ . '_simple_page_body_kb_file_delete',
				'section'		=> 'simple_page_body_kb_file_delete',
				'method'		=> array($this, 'simple_page_body_kb_file_delete')
			)
		);
		
		/*
			The two calls below add a page at /public/kb/ and can be seen by all users (even if not logged in).
		*/

		//title
		$plugins->add(
			array(
				'plugin_name'	=> __CLASS__,
				'task_name'		=> __CLASS__ . '_public_page_header_kb',
				'section'		=> 'public_page_header_kb',
				'method'		=> array($this, 'public_page_header_kb')
			)
		);
		
		//html
		$plugins->add(
			array(
				'plugin_name'	=> __CLASS__,
				'task_name'		=> __CLASS__ . '_public_page_body_kb',
				'section'		=> 'public_page_body_kb',
				'method'		=> array($this, 'public_page_body_kb')
			)
		);
		
		
		//view title
		$plugins->add(
			array(
				'plugin_name'	=> __CLASS__,
				'task_name'		=> __CLASS__ . '_public_page_header_kb_view',
				'section'		=> 'public_page_header_kb_view',
				'method'		=> array($this, 'public_page_header_kb_view')
			)
		);
		
		//view html
		$plugins->add(
			array(
				'plugin_name'	=> __CLASS__,
				'task_name'		=> __CLASS__ . '_public_page_body_kb_view',
				'section'		=> 'public_page_body_kb_view',
				'method'		=> array($this, 'public_page_body_kb_view')
			)
		);
		
		/*
			Download File
		*/
		$plugins->add(
			array(
				'plugin_name'	=> __CLASS__,
				'task_name'		=> __CLASS__ . '_download_other_files',
				'section'		=> 'download_other_files',
				'method'		=> array($this, 'download_other_files')
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
			if (in_array($url->get_module(), array('kb', 'kb_add', 'kb_view', 'kb_edit'))) {			
				$active = true;
			}
		}
		else if ($url->get_action() == 'public') {
			if (in_array($url->get_module(), array('kb', 'kb_view'))) {			
				$active = true;
			}		
		}

	?>
		<?php if ($auth->logged_in()) { ?>
			<?php if (($auth->can('articles') || $auth->can('manage_articles')) && sts\CURRENT_THEME_TYPE == 'bootstrap3') { ?>
				<li<?php if ($active) echo ' class="active"'; ?>><a href="<?php echo $config->get('address'); ?>/p/kb/"><span class="glyphicon glyphicon-bullhorn"></span> <?php echo sts\safe_output($language->get('Articles')); ?></a></li>
			<?php } ?>
		<?php } else { ?>
			<?php if (sts\CURRENT_THEME_TYPE == 'bootstrap3') { ?>
				<li<?php if ($active) echo ' class="active"'; ?>><a href="<?php echo $config->get('address'); ?>/public/kb/"><span class="glyphicon glyphicon-bullhorn"></span> <?php echo sts\safe_output($language->get('Articles')); ?></a></li>			
			<?php } ?>
		<?php } ?>
	<?php
	}
	
	//this adds the settings menu item
	public function html_header_nav_settings() {
		$config 		= &sts\singleton::get('sts\config');
		$auth 			= &sts\singleton::get('sts\auth');
		$language 		= &sts\singleton::get('sts\language');

		if ($auth->can('manage_articles')) {
		?>
			<li><a href="<?php echo $config->get('address'); ?>/p/kb_categories/"><span><?php echo sts\safe_output($language->get('Articles')); ?></span></a></li>
		<?php	
		}
	}
	
	public function plugin_page_header_kb() {
		$site 		= &sts\singleton::get('sts\site');
		$auth 		= &sts\singleton::get('sts\auth');
		$language 	= &sts\singleton::get('sts\language');
		$config 	= &sts\singleton::get('sts\config');
		
		if (!$auth->can('articles') && !$auth->can('manage_articles')) {
			header('Location: ' . $config->get('address') . '/');
			exit;
		}
		
		//this sets the title of the page.
		$site->set_title($language->get('Articles'));
		
	}
	
	public function plugin_page_body_kb() {
		$config 				= &sts\singleton::get('sts\config');
		$auth 					= &sts\singleton::get('sts\auth');
		$language 				= &sts\singleton::get('sts\language');

		$articles_item 			= &sts\singleton::get(__NAMESPACE__ . '\articles_item');
		$articles_categories 	= &sts\singleton::get(__NAMESPACE__ . '\articles_categories');		
	
		$get_array = array();
		
		if (!$auth->can('manage_articles')) {
			$get_array['published'] = 1;
		}
		
		if (isset($_GET['like_search']) && !empty($_GET['like_search'])) {
			$get_array['like_search'] = $_GET['like_search'];
		}
		
		if (isset($_GET['category_id']) && !empty($_GET['category_id'])) {
			$get_array['category_id'] = (int) $_GET['category_id'];
		}
		
		$get_array['get_other_data']	= true;
				
		$items 		= $articles_item->get($get_array);
		
		$categories = $articles_categories->get();

		/*
			The function sts\safe_output should be used when outputting data that might not be safe (i.e user entered data).
			The function sts\html_output should be used when outputting data that might not be safe and contains HTML code (html code will be displayed).
		*/
	?>
	
		<?php switch (sts\CURRENT_THEME_TYPE) {
			case 'bootstrap3':
			?>
				<div class="row">

					<div class="col-md-3">
						<div class="well well-sm">
							<div class="pull-left">
								<h4><?php echo sts\safe_output($language->get('Articles')); ?></h4>
							</div>
							
							<?php if ($auth->can('manage_articles')) { ?>
								<div class="pull-right">
									<a href="<?php echo $config->get('address'); ?>/p/kb_add/" class="btn btn-default"><?php echo sts\safe_output($language->get('Add')); ?></a>
								</div>
							<?php } ?>

							<div class="clearfix"></div>

						</div>
						
						<div class="well well-sm">
							<form method="get" action="<?php echo sts\safe_output($_SERVER['REQUEST_URI']); ?>">
					
								<div class="form-group">		
									<input type="text" class="form-control" placeholder="<?php echo sts\safe_output($language->get('Search')); ?>" name="like_search" value="<?php if (isset($_GET['like_search'])) echo sts\safe_output($_GET['like_search']); ?>" size="15" />
								</div>
								<div class="clearfix"></div>
													
								<label class="left-result"><?php echo sts\safe_output($language->get('Category')); ?></label>
								<p class="right-result">
									<select name="category_id">
										<option value="">&nbsp;</option>
										<?php foreach ($categories as $category) { ?>
										<option value="<?php echo (int) $category['id']; ?>"<?php if (isset($_GET['category_id']) && ($_GET['category_id'] == $category['id'])) { echo ' selected="selected"'; } ?>><?php echo sts\safe_output($category['name']); ?></option>
										<?php } ?>
									</select>
								</p>
								<div class="clearfix"></div>
								<br />
								<div class="pull-right">
									<p><button type="submit" name="filter" class="btn btn-info"><?php echo sts\safe_output($language->get('Filter')); ?></button></p>
								</div>
								
								<div class="clearfix"></div>

							</form>

						</div>
					</div>
					<div class="col-md-9">
						<?php if (!empty($items)) { ?>
							<section id="no-more-tables">	
								<table class="table table-striped table-bordered">
									<thead>
										<tr>
											<th><?php echo sts\safe_output($language->get('Subject')); ?></th>
											<th><?php echo sts\safe_output($language->get('Category')); ?></th>
											<th><?php echo sts\safe_output($language->get('Added')); ?></th>
											<th><?php echo sts\safe_output($language->get('Updated')); ?></th>
											<th><?php echo sts\safe_output($language->get('Views')); ?></th>
										</tr>
									</thead>
									<?php
									$i = 0;
									foreach ($items as $item) {
									?>
										<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
											<td data-title="<?php echo sts\safe_output($language->get('Subject')); ?>" class="centre"><a href="<?php echo $config->get('address'); ?>/p/kb_view/<?php echo (int) $item['id']; ?>/"><?php echo sts\safe_output($item['subject']); ?></a></td>
											<td data-title="<?php echo sts\safe_output($language->get('Category')); ?>" class="centre"><?php echo sts\safe_output($item['category_name']); ?></td>
											<td data-title="<?php echo sts\safe_output($language->get('Added')); ?>" class="centre"><?php echo sts\safe_output(sts\time_ago_in_words($item['date_added'])); ?> <?php echo sts\safe_output($language->get('ago')); ?></td>
											<td data-title="<?php echo sts\safe_output($language->get('Updated')); ?>" class="centre"><?php echo sts\safe_output(sts\time_ago_in_words($item['last_modified'])); ?> <?php echo sts\safe_output($language->get('ago')); ?></td>
											<td data-title="<?php echo sts\safe_output($language->get('Views')); ?>" class="centre"><?php echo (int) $item['views']; ?></td>
										</tr>
									<?php $i++; } ?>
								</table>
							</section>
						<?php } else { ?>
							<div class="alert alert-success">
								<a href="#" class="close" data-dismiss="alert">&times;</a>
								<?php echo sts\safe_output($language->get('No Articles Found.')); ?>
							</div>	
						<?php } ?>
					</div>
				</div>			
			<?php
			break;
			
		} ?>
		
	<?php
	}
	
	public function plugin_page_header_kb_add() {
		$site 				= &sts\singleton::get('sts\site');
		$config 			= &sts\singleton::get('sts\config');
		$articles_item 		= &sts\singleton::get(__NAMESPACE__ . '\articles_item');
		$auth 				= &sts\singleton::get('sts\auth');
		$language 			= &sts\singleton::get('sts\language');
		$storage 			= &sts\singleton::get('sts\storage');
		$articles_files 	= &sts\singleton::get(__NAMESPACE__ . '\articles_files');
		
		if ($auth->can('manage_articles')) {

			//this sets the title of the page.
			$site->set_title($language->get('Add Article'));
			
			if (isset($_POST['add'])) {
				if (!empty($_POST['subject'])) {
					if (!empty($_POST['description'])) {
					
						$upload_file 	= false;
						$file_id		= false;
						
						if ($config->get('storage_enabled')) {
							if (isset($_FILES['file'])) {
								if ($_FILES['file']['size'] > 0) {
									$file_array['file']			= $_FILES['file'];
									$file_array['name']			= $_FILES['file']['name'];
									
									$file_id = $storage->upload($file_array);
									$upload_file = true;
								}
							}
						}
					
						if ($upload_file && !$file_id) {
							$message = $language->get('File Upload Failed. Article Not Submitted.');
						}
						else {
							$id = $articles_item->add(
								array(
									'subject' 		=> $_POST['subject'],
									'user_id' 		=> $auth->get('id'),
									'description' 	=> $_POST['description'],
									'public' 		=> $_POST['public'] ? 1 : 0,
									'published' 	=> $_POST['published'] ? 1 : 0,
									'category_id'	=> (int) $_POST['category_id'] 
								)
							);
						
							if ($upload_file && $file_id) {
								$articles_files->add(array('columns' => array('file_id' => $file_id, 'article_id' => $id)));
							}
						
							header('Location: ' . $config->get('address') . '/p/kb_view/' . $id . '/');
							exit;
						}
					}
					else {
						$this->message = $language->get('Description Empty');
					}
				}
				else {
					$this->message = $language->get('Subject Empty');
				}
			}
		}
		else {
			header('Location: ' . $config->get('address') . '/p/kb/');
			exit;
		}
	}
	
	public function plugin_page_body_kb_add() {
		$config 				= &sts\singleton::get('sts\config');
		$language 				= &sts\singleton::get('sts\language');

		$articles_categories 	= &sts\singleton::get(__NAMESPACE__ . '\articles_categories');	

		$categories = $articles_categories->get();

	
		/*
			The function sts\safe_output should be used when outputting data that might not be safe (i.e user entered data).
			The function sts\html_output should be used when outputting data that might not be safe and contains HTML code (html code will be displayed).
			
			sts\safe_output(sts\CURRENT_THEME)
		*/
	?>
	
		<?php
		switch (sts\CURRENT_THEME_TYPE) {
			case 'bootstrap3':
			?>
				<div class="row">

					<form method="post" enctype="multipart/form-data" action="<?php echo sts\safe_output($_SERVER['REQUEST_URI']); ?>">

						<div class="col-md-3">
							<div class="well well-sm">
								<div class="pull-left">
									<h4><?php echo sts\safe_output($language->get('New Article')); ?></h4>
								</div>
								<div class="pull-right">
									<p><button type="submit" name="add" class="btn btn-primary"><?php echo sts\safe_output($language->get('Add')); ?></button></p>
								</div>
								
								<div class="clearfix"></div>
								
								<p><?php echo sts\safe_output($language->get('Published Articles can be viewed by everyone logged in.')); ?></p>
								
								<p><?php echo sts\safe_output($language->get('Public Articles can be viewed without needing to login (they must also be published).')); ?></p>

							</div>
						</div>
						<div class="col-md-9">
							<?php if (isset($this->message)) { ?>
							<div class="alert alert-danger">
								<a href="#" class="close" data-dismiss="alert">&times;</a>
								<?php echo sts\message($this->message); ?>
							</div>	
							<?php } ?>
							<div class="well well-sm">
								
								<div class="col-lg-6">								
									<p><?php echo sts\safe_output($language->get('Subject')); ?><br /><input class="form-control" type="text" name="subject" value="<?php if (isset($_POST['subject'])) echo sts\safe_output($_POST['subject']); ?>" size="50" /></p>		
								</div>
								<div class="clearfix"></div>
		
								<div class="col-lg-12">								

									<p><?php echo sts\safe_output($language->get('Published')); ?><br />
									<select name="published">
										<option value="0"<?php if (isset($_POST['published']) && ($_POST['published'] == 0)) { echo ' selected="selected"'; } ?>><?php echo sts\safe_output($language->get('No')); ?></option>
										<option value="1"<?php if (isset($_POST['published']) && ($_POST['published'] == 1)) { echo ' selected="selected"'; } ?>><?php echo sts\safe_output($language->get('Yes')); ?></option>
									</select></p>	
								
									<p><?php echo sts\safe_output($language->get('Public')); ?><br />
									<select name="public">
										<option value="0"<?php if (isset($_POST['public']) && ($_POST['public'] == 0)) { echo ' selected="selected"'; } ?>><?php echo sts\safe_output($language->get('No')); ?></option>
										<option value="1"<?php if (isset($_POST['public']) && ($_POST['public'] == 1)) { echo ' selected="selected"'; } ?>><?php echo sts\safe_output($language->get('Yes')); ?></option>
									</select></p>				
					
									<p><?php echo sts\safe_output($language->get('Category')); ?><br />
									<select name="category_id">
										<option value=""></option>
										<?php foreach ($categories as $category) { ?>
										<option value="<?php echo (int) $category['id']; ?>"<?php if (isset($_POST['category_id']) && ($_POST['category_id'] == $category['id'])) { echo ' selected="selected"'; } ?>><?php echo sts\safe_output($category['name']); ?></option>
										<?php } ?>
									</select></p>
									
									<p><?php echo sts\safe_output($language->get('Description')); ?><br />
										<textarea class="wysiwyg_enabled" name="description"><?php if (isset($_POST['description'])) echo sts\safe_output($_POST['description']); ?></textarea>
									</p>
									
									<?php if ($config->get('storage_enabled')) { ?>
										<p><?php echo sts\safe_output($language->get('Attach File')); ?><br /><input name="file" type="file" /></p>
									<?php } ?>
								</div>

								<div class="clearfix"></div>		
							</div>
						</div>
				
					</form>
				</div>		
			<?php
			break;
		
		}
		?>

	<?php
	}
	
	public function plugin_page_header_kb_view() {
		$site 			= &sts\singleton::get('sts\site');
		$language 		= &sts\singleton::get('sts\language');
		$auth 			= &sts\singleton::get('sts\auth');
		$config 		= &sts\singleton::get('sts\config');
		
		if (!$auth->can('articles') && !$auth->can('manage_articles')) {
			header('Location: ' . $config->get('address') . '/');
			exit;
		}
		
		//this sets the title of the page.
		$site->set_title($language->get('View Article'));
	}
	
	
	public function plugin_page_body_kb_view() {
		$config 		= &sts\singleton::get('sts\config');
		$url 			= &sts\singleton::get('sts\url');
		$auth 			= &sts\singleton::get('sts\auth');
		$language 		= &sts\singleton::get('sts\language');
		
		
		$articles_item 		= &sts\singleton::get(__NAMESPACE__ . '\articles_item');
		$articles_files 	= &sts\singleton::get(__NAMESPACE__ . '\articles_files');
		
		$id = (int) $url->get_item();

		if ($id == 0) {
			header('Location: ' . $config->get('address') . '/p/kb/');
			exit;
		}
		
		$get_array['id'] = $id;
		
		if (!$auth->can('manage_articles')) {
			$get_array['published'] = 1;		
		}
		
		$get_array['get_other_data']	= true;
		
		$items = $articles_item->get($get_array);
		
		if (count($items) == 1) {
			$item = $items[0];
			$articles_item->edit(array('views' => $item['views'] + 1, 'id' => $item['id']));
		}
		else {
			header('Location: ' . $config->get('address') . '/p/kb/');
			exit;
		}

		switch (sts\CURRENT_THEME_TYPE) {
			case 'bootstrap3':
			?>
				<div class="row">
					<div class="col-md-3">
						<div class="well well-sm">
							<div class="pull-left">
								<h4><?php echo sts\safe_output($language->get('Article')); ?></h4>
							</div>
							
							<?php if ($auth->can('manage_articles')) { ?>
								<div class="pull-right">
									<p><a href="<?php echo $config->get('address'); ?>/p/kb_edit/<?php echo (int) $item['id']; ?>/" class="btn btn-default"><?php echo sts\safe_output($language->get('Edit')); ?></a></p>
								</div>
							<?php } ?>
							
							<div class="clearfix"></div>
							
							<label class="left-result"><?php echo sts\safe_output($language->get('Added')); ?></label>
							<p class="right-result"><?php echo sts\safe_output(sts\time_ago_in_words($item['date_added'])); ?> <?php echo sts\safe_output($language->get('ago')); ?></p>
							<div class="clearfix"></div>
							
							<label class="left-result"><?php echo sts\safe_output($language->get('Updated')); ?></label>
							<p class="right-result"><?php echo sts\safe_output(sts\time_ago_in_words($item['last_modified'])); ?> <?php echo sts\safe_output($language->get('ago')); ?></p>
							<div class="clearfix"></div>
							
							<?php if (!empty($item['category_name'])) { ?>
								<label class="left-result"><?php echo sts\safe_output($language->get('Category')); ?></label>
								<p class="right-result"><?php echo sts\safe_output($item['category_name']); ?></p>
								<div class="clearfix"></div>
							<?php } ?>
							
							<label class="left-result"><?php echo sts\safe_output($language->get('Views')); ?></label>
							<p class="right-result"><?php echo (int) $item['views']; ?></p>
							<div class="clearfix"></div>
							
						</div>
						<?php $files = $articles_files->get_files(array('id' => $item['id'])); ?>
						<?php if (count($files) > 0) { ?>
							<div class="well well-sm">
								<h4><?php echo sts\safe_output($language->get('Files')); ?></h4>
								<ul>
									<?php foreach ($files as $file) { ?>
									<li><a href="<?php echo $config->get('address'); ?>/files/download/<?php echo (int) $file['id']; ?>/?article_id=<?php echo (int) $item['id']; ?>"><?php echo sts\safe_output($file['name']); ?></a></li>
									<?php } ?>
								</ul>
							</div>
						<?php } ?>
					</div>
					<div class="col-md-9">
						<div class="well well-sm">
							<h3><?php echo sts\safe_output($item['subject']); ?></h3>
							
							<?php echo sts\html_output($item['description']); ?>
						</div>
					</div>
				</div>			
			<?php
			break;
			
		}
	}
	

	
	public function plugin_page_header_kb_edit() {
		$site 			= &sts\singleton::get('sts\site');
		$config 		= &sts\singleton::get('sts\config');
		$url 			= &sts\singleton::get('sts\url');
		$storage 		= &sts\singleton::get('sts\storage');
		$language 		= &sts\singleton::get('sts\language');

		$articles_item 	= &sts\singleton::get(__NAMESPACE__ . '\articles_item');
		$articles_files 	= &sts\singleton::get(__NAMESPACE__ . '\articles_files');
		$auth 			= &sts\singleton::get('sts\auth');

		if ($auth->can('manage_articles')) {
			
			//this sets the title of the page.
			$site->set_title('Edit Article');
			
			$id = (int) $url->get_item();

			if ($id == 0) {
				header('Location: ' . $config->get('address') . '/p/kb/');
				exit;
			}

			$items = $articles_item->get(array('id' => $id));
			
			if (count($items) == 1) {
				$this->edit_array = $items[0];
			}
			else {
				header('Location: ' . $config->get('address') . '/p/kb/');
				exit;
			}
			
			if (isset($_POST['delete'])) {
				$articles_item->delete(array('id' => $id));
				header('Location: ' . $config->get('address') . '/p/kb/');
				exit;
			}

			
			if (isset($_POST['save'])) {

				$upload_file 	= false;
				$file_id		= false;
	
				if ($config->get('storage_enabled')) {
					if (isset($_FILES['file'])) {
						if ($_FILES['file']['size'] > 0) {
							$file_array['file']			= $_FILES['file'];
							$file_array['name']			= $_FILES['file']['name'];
							
							$file_id = $storage->upload($file_array);
							$upload_file = true;
						}
					}
				}
				
				if ($upload_file && !$file_id) {
					$this->message = $language->get('File Upload Failed. Article Not Saved.');
				}
				else {
					$articles_item->edit(
						array(
							'subject' 		=> $_POST['subject'],
							'description' 	=> $_POST['description'],
							'public' 		=> $_POST['public'] ? 1 : 0,
							'published' 	=> $_POST['published'] ? 1 : 0,
							'category_id'	=> (int) $_POST['category_id'],
							'last_modified'	=> true,
							'id'			=> $id
						)
					);
			
					if ($upload_file && $file_id) {
						$articles_files->add(array('columns' => array('file_id' => $file_id, 'article_id' => $id)));
					}
				
					header('Location: ' . $config->get('address') . '/p/kb_view/' . $id . '/');
					exit;
				}
			}
		
		}
		else {
			header('Location: ' . $config->get('address') . '/p/kb/');
			exit;
		}
	}
	
	public function plugin_page_body_kb_edit() {
		$config 		= &sts\singleton::get('sts\config');
		$language 		= &sts\singleton::get('sts\language');
		$plugins 		= &sts\singleton::get('sts\plugins');
		
		$articles_categories 	= &sts\singleton::get(__NAMESPACE__ . '\articles_categories');		
		$articles_files 		= &sts\singleton::get(__NAMESPACE__ . '\articles_files');		

		$categories 			= $articles_categories->get();

		/*
			The function sts\safe_output should be used when outputting data that might not be safe (i.e user entered data).
			The function sts\html_output should be used when outputting data that might not be safe and contains HTML code (html code will be displayed).
		*/
	?>
	
		<script type="text/javascript">
			$(document).ready(function () {
				$('#delete').click(function () {
					if (confirm("<?php echo sts\safe_output($language->get('Are you sure you wish to delete this article?')); ?>")){
						return true;
					}
					else{
						return false;
					}
				});
			});
		</script>
		
		<script type="text/javascript" src="<?php echo $config->get('address'); ?>/user/plugins/<?php echo sts\safe_output($plugins->plugin_base_url(__FILE__)); ?>_categories.js"></script>

		<?php
		switch(sts\CURRENT_THEME_TYPE) {
			case 'bootstrap3':
			?>
				<div class="row">

					<form method="post" enctype="multipart/form-data" action="<?php echo sts\safe_output($_SERVER['REQUEST_URI']); ?>">
				
						<div class="col-md-3">
							<div class="well well-sm">
								<div class="pull-left">
									<h4><?php echo sts\safe_output($language->get('Edit')); ?></h4>
								</div>
								<div class="pull-right">
									<p><button type="submit" name="save" class="btn btn-primary"><?php echo sts\safe_output($language->get('Save')); ?></button>
									<a href="<?php echo sts\safe_output($config->get('address')); ?>/p/kb_view/<?php echo (int) $this->edit_array['id']; ?>/" class="btn btn-default"><?php echo sts\safe_output($language->get('Cancel')); ?></a></p>
								</div>
								
								<div class="clearfix"></div>
							
								<p><?php echo sts\safe_output($language->get('Published Articles can be viewed by everyone logged in.')); ?></p>

								<p><?php echo sts\safe_output($language->get('Public Articles can be viewed without needing to login (they must also be published).')); ?></p>

								
								<br />
								<div class="pull-right"><button type="submit" id="delete" name="delete" class="btn btn-danger"><?php echo sts\safe_output($language->get('Delete')); ?></button></div>
								<div class="clearfix"></div>
								
							</div>
							<?php $files = $articles_files->get_files(array('id' => $this->edit_array['id'])); ?>
							<?php if (count($files) > 0) { ?>
								<div class="well well-sm">
									<h4><?php echo sts\safe_output($language->get('Files')); ?></h4>
									<ul>
										<?php foreach ($files as $file) { ?>
											<li id="existing-<?php echo (int) $file['link_id']; ?>"><a href="<?php echo $config->get('address'); ?>/files/download/<?php echo (int) $file['id']; ?>/?article_id=<?php echo (int) $this->edit_array['id']; ?>"><?php echo sts\safe_output($file['name']); ?></a> <a href="#delete_existing_file" id="delete_existing_file"><img src="<?php echo $config->get('address'); ?>/user/themes/<?php echo sts\safe_output(sts\CURRENT_THEME); ?>/images/icons/delete.png" alt="<?php echo sts\safe_output($language->get('Delete File')); ?>" /></a></li>
										<?php } ?>
									</ul>
								</div>
							<?php } ?>
						</div>
						<div class="col-md-9">
							<?php if (isset($this->message)) { ?>
								<div class="alert alert-danger">
									<a href="#" class="close" data-dismiss="alert">&times;</a>
									<?php echo sts\message($this->message); ?>
								</div>	
							<?php } ?>
							<div class="well well-sm">								
								<div class="col-lg-6">								
									<p><?php echo sts\safe_output($language->get('Subject')); ?><br /><input class="form-control" type="text" name="subject" value="<?php echo sts\safe_output($this->edit_array['subject']); ?>" size="50" /></p>		
								</div>
								<div class="clearfix"></div>		
		
								<div class="col-lg-12">
									<p><?php echo sts\safe_output($language->get('Published')); ?><br />
									<select name="published">
										<option value="0"<?php if ($this->edit_array['published'] == 0) { echo ' selected="selected"'; } ?>><?php echo sts\safe_output($language->get('No')); ?></option>
										<option value="1"<?php if ($this->edit_array['published'] == 1) { echo ' selected="selected"'; } ?>><?php echo sts\safe_output($language->get('Yes')); ?></option>
									</select></p>	
									
									<p><?php echo sts\safe_output($language->get('Public')); ?><br />
									<select name="public">
										<option value="0"<?php if ($this->edit_array['public'] == 0) { echo ' selected="selected"'; } ?>><?php echo sts\safe_output($language->get('No')); ?></option>
										<option value="1"<?php if ($this->edit_array['public'] == 1) { echo ' selected="selected"'; } ?>><?php echo sts\safe_output($language->get('Yes')); ?></option>
									</select></p>
									
									<p><?php echo sts\safe_output($language->get('Category')); ?><br />
									<select name="category_id">
										<option value=""></option>
										<?php foreach ($categories as $category) { ?>
										<option value="<?php echo (int) $category['id']; ?>"<?php if ($this->edit_array['category_id'] == $category['id']) { echo ' selected="selected"'; } ?>><?php echo sts\safe_output($category['name']); ?></option>
										<?php } ?>
									</select></p>
									
									<p><?php echo sts\safe_output($language->get('Description')); ?><br />
										<textarea class="wysiwyg_enabled" name="description" cols="80" rows="12"><?php echo sts\safe_output($this->edit_array['description']); ?></textarea>
									</p>
									
									<?php if ($config->get('storage_enabled')) { ?>
										<p><?php echo sts\safe_output($language->get('Attach File')); ?><br /><input name="file" type="file" /></p>
									<?php } ?>
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
	
	public function plugin_page_header_kb_categories() {
		$site 					= &sts\singleton::get('sts\site');
		$config 				= &sts\singleton::get('sts\config');
		$url 					= &sts\singleton::get('sts\url');
		$auth 					= &sts\singleton::get('sts\auth');
		$log 					= &sts\singleton::get('sts\log');
		$language 				= &sts\singleton::get('sts\language');

		$articles_categories 	= &sts\singleton::get(__NAMESPACE__ . '\articles_categories');		

		
		if ($auth->can('manage_articles')) {
			//this sets the title of the page.
			$site->set_title('Article Categories');
			
			if (isset($_POST['save'])) {
	
				//add new categories
				$i = 0;
				foreach ($_POST['item_name'] as $name) {
					if (!empty($name)) {
						$item_array['name']			= $name;
						$articles_categories->add($item_array);
					}
					$i++;
				}
				
				//update existing categories
				foreach($_POST as $index => $value){
					if(strncasecmp($index, 'existing-', 9) === 0) {
						$cat_index = explode('-', $index);
						$item_array['name']			= $value;
						$item_array['id']			= (int) $cat_index[1];
						$articles_categories->edit($item_array);
					}
				}
				
				$log_array['event_severity'] = 'notice';
				$log_array['event_number'] = E_USER_NOTICE;
				$log_array['event_description'] = 'Article Categories Edited';
				$log_array['event_file'] = __FILE__;
				$log_array['event_file_line'] = __LINE__;
				$log_array['event_type'] = 'edit';
				$log_array['event_source'] = 'articles';
				$log_array['event_version'] = '1';
				$log_array['log_backtrace'] = false;	
						
				$log->add($log_array);
				
				$this->message = 'Settings Saved';
			}
	
			
		}
		else {
			header('Location: ' . $config->get('address') . '/p/kb/');
			exit;
		}
	}
	
	public function plugin_page_body_kb_categories() {
		$config 		= &sts\singleton::get('sts\config');
		$plugins 		= &sts\singleton::get('sts\plugins');
		$language 		= &sts\singleton::get('sts\language');

		$articles_categories 	= &sts\singleton::get(__NAMESPACE__ . '\articles_categories');		
		
		$categories = $articles_categories->get();
		?>
		<script type="text/javascript" src="<?php echo $config->get('address'); ?>/user/plugins/<?php echo sts\safe_output($plugins->plugin_base_url(__FILE__)); ?>_categories.js"></script>
	
		<?php
		switch(sts\CURRENT_THEME_TYPE) {
			case 'bootstrap3':
			?>
				<div class="row">

					<form method="post" action="<?php echo sts\safe_output($_SERVER['REQUEST_URI']); ?>">

						<div class="col-md-3">
							<div class="well well-sm">
								
								<div class="pull-left">
									<h4><?php echo sts\safe_output($language->get('Articles')); ?></h4>
								</div>
								
								<div class="pull-right">
									<p><button type="submit" name="save" class="btn btn-primary"><?php echo sts\safe_output($language->get('Save')); ?></button></p>

								</div>
								<div class="clearfix"></div>
								
								<p><?php echo sts\safe_output($language->get('Please note that removing categories that are in use will leave articles without a category.')); ?></p>
								
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
								<h3><?php echo sts\safe_output($language->get('Categories')); ?></h3>
		
								<div class="form-group">	
									<div class="col-lg-4">
		
										<?php foreach ($categories as $category) { ?>
											<div class="current_category_field" id="existing-<?php echo (int) $category['id']; ?>">
												<div class="pull-left">
													<input class="form-control" type="text" size="25" name="existing-<?php echo (int) $category['id']; ?>" value="<?php echo sts\safe_output($category['name']); ?>" /> 
												</div>
												<div class="pull-right">												
													<a href="#custom" id="delete_existing_category_item"><img src="<?php echo $config->get('address'); ?>/user/themes/<?php echo sts\safe_output(sts\CURRENT_THEME); ?>/images/icons/delete.png" alt="<?php echo sts\safe_output($language->get('Delete Category')); ?>" /></a>
												</div>
												<div class="clearfix"></div>
												<br />
											</div>
										<?php } ?>
								
										<div class="category_field">
											<p><input class="form-control" type="text" size="25" name="item_name[]" value="" /></p>
										</div>
									
										<div class="extra_category_field"></div>
									</div>
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
	
	public function simple_page_body_kb_category_delete() {
		$auth 					= &sts\singleton::get('sts\auth');
		$articles_categories 	= &sts\singleton::get(__NAMESPACE__ . '\articles_categories');		
		$url 					= &sts\singleton::get('sts\url');
		$language 				= &sts\singleton::get('sts\language');

		if (!$auth->can('manage_articles')) {
			exit;
		}

		$id = (int) $url->get_item();

		if (isset($_POST['delete'])) {
			$articles_categories->delete(array('id' => $id));
			exit;	
		}
	
	}
	
	public function public_page_header_kb() {
		$site 			= &sts\singleton::get('sts\site');
		$language 		= &sts\singleton::get('sts\language');
	
		//this sets the title of the page.
		$site->set_title($language->get('Articles'));
		$site->set_config('container-type', 'container');

		
	}
	
	public function public_page_body_kb() {
		$config 				= &sts\singleton::get('sts\config');
		$auth 					= &sts\singleton::get('sts\auth');
		$language 				= &sts\singleton::get('sts\language');

		$articles_item 			= &sts\singleton::get(__NAMESPACE__ . '\articles_item');
		$articles_categories 	= &sts\singleton::get(__NAMESPACE__ . '\articles_categories');		
	
		$get_array = array();
		
	
		$get_array['public'] 	= 1;
		$get_array['published'] = 1;
		
		if (isset($_GET['like_search']) && !empty($_GET['like_search'])) {
			$get_array['like_search'] = $_GET['like_search'];
		}
		
		if (isset($_GET['category_id']) && !empty($_GET['category_id'])) {
			$get_array['category_id'] = (int) $_GET['category_id'];
		}
		
		$get_array['get_other_data']	= true;
				
		$items 		= $articles_item->get($get_array);
		
		$categories = $articles_categories->get();

		/*
			The function sts\safe_output should be used when outputting data that might not be safe (i.e user entered data).
			The function sts\html_output should be used when outputting data that might not be safe and contains HTML code (html code will be displayed).
		*/

		switch(sts\CURRENT_THEME_TYPE) {
			case 'bootstrap3':
			?>
				<div class="row">

					<div class="col-md-3">
						<div class="well well-sm">
							<div class="pull-left">
								<h4><?php echo sts\safe_output($language->get('Articles')); ?></h4>
							</div>

							<div class="clearfix"></div>

						</div>
						
						<div class="well well-sm">
							<form method="get" action="<?php echo sts\safe_output($_SERVER['REQUEST_URI']); ?>">
					
								<div class="form-group">		
									<input type="text" class="form-control" placeholder="Search" name="like_search" value="<?php if (isset($_GET['like_search'])) echo sts\safe_output($_GET['like_search']); ?>" size="15" />
								</div>
								
								<div class="clearfix"></div>
													
								<label class="left-result"><?php echo sts\safe_output($language->get('Category')); ?></label>
								<p class="right-result">
									<select name="category_id">
										<option value="">&nbsp;</option>
										<?php foreach ($categories as $category) { ?>
										<option value="<?php echo (int) $category['id']; ?>"<?php if (isset($_GET['category_id']) && ($_GET['category_id'] == $category['id'])) { echo ' selected="selected"'; } ?>><?php echo sts\safe_output($category['name']); ?></option>
										<?php } ?>
									</select>
								</p>
								<div class="clearfix"></div>
								<br />
								<div class="pull-right">
									<p><button type="submit" name="filter" class="btn btn-info"><?php echo sts\safe_output($language->get('Filter')); ?></button></p>
								</div>
								
								<div class="clearfix"></div>

							</form>

						</div>
					</div>
					<div class="col-md-9">
						<?php if (!empty($items)) { ?>
							<section id="no-more-tables">	
								<table class="table table-striped table-bordered">
									<thead>
										<tr>
											<th><?php echo sts\safe_output($language->get('Subject')); ?></th>
											<th><?php echo sts\safe_output($language->get('Category')); ?></th>
											<th><?php echo sts\safe_output($language->get('Added')); ?></th>
											<th><?php echo sts\safe_output($language->get('Updated')); ?></th>
											<th><?php echo sts\safe_output($language->get('Views')); ?></th>
										</tr>
									</thead>
									<?php
									$i = 0;
									foreach ($items as $item) {
									?>
										<tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
											<td data-title="<?php echo sts\safe_output($language->get('Subject')); ?>" class="centre"><a href="<?php echo $config->get('address'); ?>/public/kb_view/<?php echo (int) $item['id']; ?>/"><?php echo sts\safe_output($item['subject']); ?></a></td>
											<td data-title="<?php echo sts\safe_output($language->get('Category')); ?>" class="centre"><?php echo sts\safe_output($item['category_name']); ?></td>
											<td data-title="<?php echo sts\safe_output($language->get('Added')); ?>" class="centre"><?php echo sts\safe_output(sts\time_ago_in_words($item['date_added'])); ?> <?php echo sts\safe_output($language->get('ago')); ?></td>
											<td data-title="<?php echo sts\safe_output($language->get('Updated')); ?>" class="centre"><?php echo sts\safe_output(sts\time_ago_in_words($item['last_modified'])); ?> <?php echo sts\safe_output($language->get('ago')); ?></td>
											<td data-title="<?php echo sts\safe_output($language->get('Views')); ?>" class="centre"><?php echo (int) $item['views']; ?></td>
										</tr>
									<?php $i++; } ?>

								</table>
							</section>
						<?php } else { ?>
							<div class="alert alert-success">
								<a href="#" class="close" data-dismiss="alert">&times;</a>
								<?php echo sts\safe_output($language->get('No Articles Found.')); ?>
							</div>	
						<?php } ?>
					</div>
				</div>			
			<?php
			break;
	
		}
	}
	
	public function public_page_header_kb_view() {
		$site 			= &sts\singleton::get('sts\site');
		$language 		= &sts\singleton::get('sts\language');
	
		//this sets the title of the page.
		$site->set_title($language->get('View Article'));
		$site->set_config('container-type', 'container');

	}
	
	
	public function public_page_body_kb_view() {
		$config 		= &sts\singleton::get('sts\config');
		$url 			= &sts\singleton::get('sts\url');
		$language 		= &sts\singleton::get('sts\language');
	
		$articles_item 		= &sts\singleton::get(__NAMESPACE__ . '\articles_item');
		
		$id = (int) $url->get_item();

		if ($id == 0) {
			header('Location: ' . $config->get('address') . '/p/kb/');
			exit;
		}

		$items = $articles_item->get(array('id' => $id, 'published' => 1, 'public' => 1, 'get_other_data' => true));
		
		if (count($items) == 1) {
			$item = $items[0];
			$articles_item->edit(array('views' => $item['views'] + 1, 'id' => $item['id']));
		}
		else {
			header('Location: ' . $config->get('address') . '/public/kb/');
			exit;
		}

		switch (sts\CURRENT_THEME_TYPE) {
			case 'bootstrap3':
			?>
				<div class="row">
					<div class="col-md-3">
						<div class="well well-sm">
							<div class="left">
								<h4><?php echo sts\safe_output($language->get('Article')); ?></h4>
							</div>
									
							<div class="clearfix"></div>

							<label class="left-result"><?php echo sts\safe_output($language->get('Added')); ?></label>
							<p class="right-result"><?php echo sts\safe_output(sts\time_ago_in_words($item['date_added'])); ?> <?php echo sts\safe_output($language->get('ago')); ?></p>
							<div class="clearfix"></div>
							
							<label class="left-result"><?php echo sts\safe_output($language->get('Updated')); ?></label>
							<p class="right-result"><?php echo sts\safe_output(sts\time_ago_in_words($item['last_modified'])); ?> <?php echo sts\safe_output($language->get('ago')); ?></p>
							<div class="clearfix"></div>
							
							<?php if (!empty($item['category_name'])) { ?>
								<label class="left-result"><?php echo sts\safe_output($language->get('Category')); ?></label>
								<p class="right-result"><?php echo sts\safe_output($item['category_name']); ?></p>
								<div class="clearfix"></div>
							<?php } ?>
							
							<label class="left-result"><?php echo sts\safe_output($language->get('Views')); ?></label>
							<p class="right-result"><?php echo (int) $item['views']; ?></p>
							<div class="clearfix"></div>
						</div>
					</div>
					<div class="col-md-9">
						<div class="well well-sm">
							<h3><?php echo sts\safe_output($item['subject']); ?></h3>
							
							<?php echo sts\html_output($item['description']); ?>

						</div>
					</div>
				</div>			
			<?php
			break;
	
		}

	?>

	<?php
	}
	
	public function download_other_files(&$files) {
		$url 				= &sts\singleton::get('sts\url');
		$error 				= &sts\singleton::get('sts\error');
		$auth 				= &sts\singleton::get('sts\auth');
		$language 			= &sts\singleton::get('sts\language');

		$articles_files 	= &sts\singleton::get(__NAMESPACE__ . '\articles_files');
		$articles_item 		= &sts\singleton::get(__NAMESPACE__ . '\articles_item');
		
		if ($auth->can('articles')) {
			if (isset($_GET['article_id'])) {
				$id 			= (int) $url->get_item();
				$article_id		= (int) $_GET['article_id'];	
		
				$get_array['id']				= $article_id;
				
				if (!$auth->can('manage_articles')) {
					$get_array['published']		= 1;			
				}

				$get_array['get_other_data']	= true;
		
				$items = $articles_item->get($get_array);

				if (!empty($items)) {	
					$files = $articles_files->get_files(array('id' => $article_id, 'file_id' => $id));
				}
				else {
					$error->create(array('type' => 'storage_file_not_found', 'message' => 'File Not Found'));
				}
			}
		}
	}
	
	public function simple_page_body_kb_file_delete() {
		$auth 					= &sts\singleton::get('sts\auth');
		$articles_files 		= &sts\singleton::get(__NAMESPACE__ . '\articles_files');		
		$url 					= &sts\singleton::get('sts\url');

		if ($auth->can('manage_articles')) {
			$id = (int) $url->get_item();

			if (isset($_POST['delete'])) {
				$articles_files->delete(array('id' => $id));
				exit;	
			}
		}
		else {
			exit;
		}
	
	}
}

?>