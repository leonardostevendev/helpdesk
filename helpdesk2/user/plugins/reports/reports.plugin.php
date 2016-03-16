<?php
/*
	Reports plugin for Tickets.
	Copyright Dalegroup Pty Ltd 2012
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
class reports {

	private $message 		= NULL;
	private $url_basename	= NULL;

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
			'name' 				=> 'Reports',
			'version' 			=> '5.0',
			'description'		=> 'This plugins gives Tickets a reports section.',
			'website'			=> 'http://codecanyon.net/item/tickets/2478843?ref=michaeldale',
			'author'			=> 'Michael Dale',
			'author_website'	=> 'http://michaeldale.com.au/',
			'update_check_url'	=> 'http://api.apptrack.com.au/api/',
			'application_id'	=> 15				
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
		
		$this->url_basename	= $plugins->plugin_base_url(__FILE__);
						
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
			View Reports
		*/

		//title
		$plugins->add(
			array(
				'plugin_name'	=> __CLASS__,
				'task_name'		=> __CLASS__ . '_plugin_page_header_reports',
				'section'		=> 'plugin_page_header_reports',
				'method'		=> array($this, 'plugin_page_header_reports')
			)
		);
		
		//html
		$plugins->add(
			array(
				'plugin_name'	=> __CLASS__,
				'task_name'		=> __CLASS__ . '_plugin_page_body_reports',
				'section'		=> 'plugin_page_body_reports',
				'method'		=> array($this, 'plugin_page_body_reports')
			)
		);
		
		//dashboard graph
		/*
		$plugins->add(
			array(
				'plugin_name'	=> __CLASS__,
				'task_name'		=> __CLASS__ . '_view_dashboard_content_start',
				'section'		=> 'view_dashboard_content_start',
				'method'		=> array($this, 'view_dashboard_content_start')
			)
		);
		*/
		
	

	}
	
	//this adds menu items
	public function html_header_nav_start() {
		$config 		= &sts\singleton::get('sts\config');
		$auth 			= &sts\singleton::get('sts\auth');
		$url 			= &sts\singleton::get('sts\url');
		$plugins 		= &sts\singleton::get('sts\plugins');
		$language 		= &sts\singleton::get('sts\language');
		
		$active = false;
		
		if ($url->get_action() == 'p') {
			if (in_array($url->get_module(), array('reports'))) {			
				$active = true;
			}
		}

		if ($auth->logged_in()) { 
			if ($auth->can('manage_tickets')) {
				switch(sts\CURRENT_THEME_TYPE) {
					case 'bootstrap3':
					?>
					
					<li class="dropdown<?php if ($active) echo ' active'; ?>">
						<a class="dropdown-toggle" data-toggle="dropdown" data-target="#reports" href="<?php echo $config->get('address'); ?>/p/reports/"><span class="glyphicon glyphicon-signal"></span> <?php echo sts\safe_output($language->get('Reports')); ?> <strong class="caret"></strong></a>							
						<ul class="dropdown-menu">
							<li><a href="<?php echo $config->get('address'); ?>/p/reports/"><?php echo sts\safe_output($language->get('Dashboard')); ?></a></li>
							<!--<li><a href="<?php echo $config->get('address'); ?>/p/reports_users/"><?php echo sts\safe_output($language->get('User Reports')); ?></a></li>-->
							<?php $plugins->run('html_header_nav_reports'); ?>
						</ul>
					</li>					
					<?php
					break;
				}
			}
		}
	}

	
	public function plugin_page_header_reports() {
		$site 		= &sts\singleton::get('sts\site');
		$language 		= &sts\singleton::get('sts\language');
	
		//this sets the title of the page.
		$site->set_title($language->get('Reports'));
		
	}
	
	
	public function plugin_page_body_reports() {
		$config 				= &sts\singleton::get('sts\config');
		$auth 					= &sts\singleton::get('sts\auth');
		$tickets 				= &sts\singleton::get('sts\tickets');
		$auth 					= &sts\singleton::get('sts\auth');
		$tickets_history 		= &sts\singleton::get('sts\ticket_history');
		$language 			= &sts\singleton::get('sts\language');

		if ($auth->can('manage_tickets')) {

			/*
				Last Week
			*/
			$last_7_days 			= sts\last_x_days();

			$total_day_stats		= $tickets->day_stats(array('days' => 7, 'date_select' => 'date_added'));
			//$open_day_stats		= $tickets->day_stats(array('days' => 7, 'state_id' => 1, 'date_select' => 'date_added'));
			$closed_day_stats		= $tickets->day_stats(array('days' => 7, 'active' => 0, 'date_select' => 'date_state_changed'));

			$total_tickets = array();
			foreach ($last_7_days as $day) {
				$temp['date'] 	= date('l', strtotime($day));
				$temp['count'] 	= 0;
				foreach($total_day_stats as $stat) {
					if (strtotime($stat['year'] . '-' . $stat['month'] . '-' . $stat['day']) == strtotime($day)) {
						$temp['count'] = $stat['count'];
					}
				}
				$total_tickets[] = $temp;
			}

			$closed_tickets = array();
			foreach ($last_7_days as $day) {
				$temp['date'] 	= $day;
				$temp['count'] 	= 0;
				foreach($closed_day_stats as $stat) {
					if (strtotime($stat['year'] . '-' . $stat['month'] . '-' . $stat['day']) == strtotime($day)) {
						$temp['count'] = $stat['count'];
					}
				}
				$closed_tickets[] = $temp;
			}
			
			$top_users				= $tickets->day_users(array('days' => 7, 'limit' => 6));
			$top_month_users		= $tickets->month_users(array('months' => 0, 'limit' => 6));

			$top_history_users				= $tickets_history->day_users(array('days' => 7, 'limit' => 6));
			$top_history_month_users		= $tickets_history->month_users(array('months' => 0, 'limit' => 6));
			
			
			/*
				Last 6 Months
			*/
			
			$last_6_months 			= sts\last_x_months();

			$total_month_stats		= $tickets->month_stats(array('months' => 6, 'date_select' => 'date_added'));
			$closed_month_stats		= $tickets->month_stats(array('months' => 6, 'active' => 0, 'date_select' => 'date_state_changed'));

			$total_tickets_month = array();
			foreach ($last_6_months as $month) {
				$temp['date'] 	= date('F', strtotime($month));
				$temp['count'] 	= 0;
				foreach($total_month_stats as $stat) {
					if (strtotime($stat['year'] . '-' . $stat['month']) == strtotime($month)) {
						$temp['count'] = $stat['count'];
					}
				}
				$total_tickets_month[] = $temp;
			}
			
			$closed_tickets_month = array();
			foreach ($last_6_months as $month) {
				$temp['date'] 	= $month;
				$temp['count'] 	= 0;
				foreach($closed_month_stats as $stat) {
					if (strtotime($stat['year'] . '-' . $stat['month']) == strtotime($month)) {
						$temp['count'] = $stat['count'];
					}
				}
				$closed_tickets_month[] = $temp;
			}
			
			/*
				Active Tickets
			*/
			if (method_exists($tickets, 'active_tickets_by_department')) {
				$active_tickets_by_department 	= $tickets->active_tickets_by_department();
			}
			
			if (method_exists($tickets, 'active_tickets_by_status')) {
				$active_tickets_by_status	 	= $tickets->active_tickets_by_status();
			}
		
			
			include('graph_header.php');
		}
		else {
			header('Location: ' . $config->get('address') . '/');
			exit;	
		}
	?>
	
	<?php
	
		switch (sts\CURRENT_THEME_TYPE) {
		
			case 'bootstrap3':
			?>
						
				<link rel="stylesheet" href="<?php echo sts\safe_output($config->get('address')); ?>/system/libraries/morris/morris.css">
				<script src="<?php echo sts\safe_output($config->get('address')); ?>/system/libraries/js/raphael-min.js"></script>
				<script src="<?php echo sts\safe_output($config->get('address')); ?>/system/libraries/morris/morris.min.js"></script>
						
				<div class="row">
					<!--
					<div class="col-md-3">
						<div class="well well-sm">
							<div class="left">
								<h4><?php echo sts\safe_output($language->get('Dashboard')); ?></h4>
							</div>

							<div class="clearfix"></div>

						</div>
							
					</div>
					-->
					
					<div class="col-md-12">
						<div class="panel panel-default">
							<div class="panel-heading">
								<div class="pull-left">
									<h1 class="panel-title"><?php echo sts\safe_output($language->get('Reports Dashboard')); ?></h1>
								</div>
								<div class="clearfix"></div>
							</div>
							<div class="panel-body">
					
								<?php if (isset($active_tickets_by_department)) { ?>
									<div class="col-md-4">
										<h4 class="text-center">Active/Open Tickets (By Department)</h4>
										<div id="active_tickets_by_department" style="height: 250px;"></div>
									</div>
								<?php } ?>

								<?php if (isset($active_tickets_by_status)) { ?>
									<div class="col-md-4">	
										<h4 class="text-center">Active/Open Tickets (By Status)</h4>
										<div id="active_tickets_by_status" style="height: 250px;"></div>
									</div>
								<?php } ?>
								
								<div class="clearfix"></div>	
								<hr />
								
								<!--<div class="well well-small">-->
									<div class="col-md-12">	
										<h4 class="text-center">Tickets (last 7 days)</h4>
										<div id="ticket_day_stats" style="height: 250px;"></div>
									</div>
								<!--</div>-->
								
								<div class="clearfix"></div>	
								<hr />
								
								<!--<div class="well well-small">-->
									<div class="col-md-4">	
										<h4 class="text-center">Top 6 Submitters (last 7 days)</h4>
										<div id="ticket_top_users" style="height: 250px;"></div>
									</div>
									
									<div class="col-md-4">	
										<h4 class="text-center">Top 6 User Actions (last 7 days)</h4>
										<div id="ticket_history_top_users" style="height: 250px;"></div>
									</div>
								<!--</div>-->
								
								<!--<div class="well well-small">-->
									<div class="col-md-4">	
										<h4 class="text-center">Top 6 Submitters (this month)</h4>
										<div id="ticket_top_month_users" style="height: 250px;"></div>	
									</div>
									
									<div class="col-md-4">
										<h4 class="text-center">Top 6 User Actions (this month)</h4>
										<div id="ticket_history_top_month_users" style="height: 250px;"></div>
									</div>
								<!--</div>-->
									
								<div class="clearfix"></div>	
								<hr />	
									
								<!--<div class="well well-small">-->
									<div class="col-md-12">
										<h4 class="text-center">Tickets (last 6 months)</h4>
										<div id="ticket_month_stats" style="height: 285px;"></div>
									</div>
								<!--</div>-->
							</div>
						</div>
					</div>
				</div>			
			<?php
			break;
		
		}
	
	?>
	
		<script>
			window.onload = function ()
			{	
				<?php
					$morris_data = array();
					foreach ($active_tickets_by_department as $item) {
						$morris_data[] = array('label' => sts\safe_output(ucwords($item['name'])), 'value' => (int) $item['count']);
					}
				?>
						
				new Morris.Donut({
				  element: 'active_tickets_by_department',
				  data: <?php echo json_encode($morris_data); ?>,
				  resize: true,
				  hideHover: 'auto',
				  pointSize: 5
				});
				
				<?php
					$morris_data = array();
					foreach ($active_tickets_by_status as $item) {
						$morris_data[] = array('label' => sts\safe_output(ucwords($item['name'])), 'value' => (int) $item['count']);
					}
				?>
						
				new Morris.Donut({
				  element: 'active_tickets_by_status',
				  data: <?php echo json_encode($morris_data); ?>,
				  resize: true,
				  hideHover: 'auto',
				  pointSize: 5
				});
					
				<?php
					$morris_data = array();
					foreach ($top_users as $item) {
						$morris_data[] = array('label' => sts\safe_output(ucwords($item['full_name'])), 'value' => (int) $item['count']);
					}
				?>
						
				new Morris.Donut({
				  element: 'ticket_top_users',
				  data: <?php echo json_encode($morris_data); ?>,
				  resize: true,
				  hideHover: 'auto',
				  pointSize: 5
				});

				<?php
					$morris_data = array();
					foreach ($top_history_users as $item) {
						$morris_data[] = array('label' => sts\safe_output(ucwords($item['full_name'])), 'value' => (int) $item['count']);
					}
				?>
						
				new Morris.Donut({
				  element: 'ticket_history_top_users',
				  data: <?php echo json_encode($morris_data); ?>,
				  resize: true,
				  hideHover: 'auto',
				  pointSize: 5
				});
				
				<?php
					$morris_data = array();
					foreach ($top_month_users as $item) {
						$morris_data[] = array('label' => sts\safe_output(ucwords($item['full_name'])), 'value' => (int) $item['count']);
					}
				?>
						
				new Morris.Donut({
				  element: 'ticket_top_month_users',
				  data: <?php echo json_encode($morris_data); ?>,
				  resize: true,
				  hideHover: 'auto',
				  pointSize: 5
				});
				
				<?php
					$morris_data = array();
					foreach ($top_history_month_users as $item) {
						$morris_data[] = array('label' => sts\safe_output(ucwords($item['full_name'])), 'value' => (int) $item['count']);
					}
				?>
						
				new Morris.Donut({
				  element: 'ticket_history_top_month_users',
				  data: <?php echo json_encode($morris_data); ?>,
				  resize: true,
				  hideHover: 'auto',
				  pointSize: 5
				});
				
				
								
				<?php
					$morris_data = array();
										
					for($i = 0; $i < 7; $i++) {
						$temp = array();
						
						$temp['day'] = $total_tickets[$i]['date'];
						
						$temp['added'] = 0;
						if (isset($total_tickets[$i])) {
							$temp['added'] = (int) $total_tickets[$i]['count'];
						}

						$temp['closed'] = 0;
						if (isset($closed_tickets[$i])) {
							$temp['closed'] = (int) $closed_tickets[$i]['count'];
						}						
						
						$morris_data[] = $temp;
					}				
				?>
				
				new Morris.Line({
				  element: 'ticket_day_stats',
				  data: <?php echo json_encode($morris_data); ?>,
				  xkey: 'day',
				  ykeys: ['added', 'closed'],
				  labels: ['Added', 'Closed'],
				  resize: true,
				  hideHover: 'auto',
				  pointSize: 5,
				  parseTime: false
				});	
				
				<?php
					$morris_data = array();
										
					for($i = 0; $i < 6; $i++) {
						$temp = array();
						
						$temp['month'] = $total_tickets_month[$i]['date'];
												
						$temp['added'] = 0;
						if (isset($total_tickets_month[$i])) {
							$temp['added'] = (int) $total_tickets_month[$i]['count'];
						}

						$temp['closed'] = 0;
						if (isset($closed_tickets_month[$i])) {
							$temp['closed'] = (int) $closed_tickets_month[$i]['count'];
						}						
						
						$morris_data[] = $temp;
					}		

				?>
				
				
				new Morris.Line({
				  element: 'ticket_month_stats',
				  data: <?php echo json_encode($morris_data); ?>,
				  xkey: 'month',
				  ykeys: ['added', 'closed'],
				  labels: ['Added', 'Closed'],
				  resize: true,
				  hideHover: 'auto',
				  pointSize: 5,
				  parseTime: false,
				  padding: 35
				});	
								
			}
		</script>		
		<?php
	}
	
	public function view_dashboard_content_start() {
		$config 				= &sts\singleton::get('sts\config');
		$auth 					= &sts\singleton::get('sts\auth');
		$language 				= &sts\singleton::get('sts\language');
		$users 					= &sts\singleton::get('sts\users');
		$tickets 				= &sts\singleton::get('sts\tickets');
		
		
		/*
			Last Week
		*/
		$last_7_days 			= sts\last_x_days();

		$total_day_stats		= $tickets->day_stats(array('days' => 7, 'date_select' => 'date_added'));
		//$open_day_stats		= $tickets->day_stats(array('days' => 7, 'state_id' => 1, 'date_select' => 'date_added'));
		$closed_day_stats		= $tickets->day_stats(array('days' => 7, 'state_id' => 2, 'date_select' => 'date_state_changed'));

		$total_tickets = array();
		foreach ($last_7_days as $day) {
			$temp['date'] 	= date('l', strtotime($day));
			$temp['count'] 	= 0;
			foreach($total_day_stats as $stat) {
				if (strtotime($stat['year'] . '-' . $stat['month'] . '-' . $stat['day']) == strtotime($day)) {
					$temp['count'] = $stat['count'];
				}
			}
			$total_tickets[] = $temp;
		}

		$closed_tickets = array();
		foreach ($last_7_days as $day) {
			$temp['date'] 	= $day;
			$temp['count'] 	= 0;
			foreach($closed_day_stats as $stat) {
				if (strtotime($stat['year'] . '-' . $stat['month'] . '-' . $stat['day']) == strtotime($day)) {
					$temp['count'] = $stat['count'];
				}
			}
			$closed_tickets[] = $temp;
		}
			
		include('graph_header.php');

		if (sts\CURRENT_THEME_TYPE == 'bootstrap3') {

			?>
				<div class="span4">
					<div class="well well-small">
						<div class="pull-left">
							<h4><?php echo sts\safe_output($language->get('Tickets')); ?></h4>
						</div>
						<div class="pull-right">
					
						</div>
						
						<div class="clearfix"></div>
					
						<canvas id="ticket_day_stats" width="300" height="250">[No canvas support]</canvas>

					</div>
				</div>
			<?php
		}
		else {
		
		}
		?>
		<script>
			window.onload = function ()
			{	
				var requests_data = [
				<?php foreach ($total_tickets as $request) { ?>
				<?php echo (int) $request['count']; ?>,
				<?php } ?>
				];
				
				
				var closed_data = [
				<?php foreach ($closed_tickets as $request) { ?>
				<?php echo (int) $request['count']; ?>,
				<?php } ?>
				];
				
				
				var ticket_day_stats = new RGraph.Line("ticket_day_stats", requests_data, closed_data);
				ticket_day_stats.Set('chart.background.barcolor1', 'rgba(255,255,255,1)');
				ticket_day_stats.Set('chart.background.grid.color', 'rgba(238,238,238,1)');
				ticket_day_stats.Set('chart.linewidth', 2);
				ticket_day_stats.Set('chart.filled', false);
				ticket_day_stats.Set('chart.hmargin', 34);
				ticket_day_stats.Set('chart.labels.above', true);
				ticket_day_stats.Set('chart.colors', ['red', 'green', 'blue']);
				ticket_day_stats.Set('chart.gutter.top', 50);
				ticket_day_stats.Set('chart.gutter', 40);
				ticket_day_stats.Set('chart.gutter.left', 60);


				ticket_day_stats.Set('chart.labels', [
				<?php foreach ($total_tickets as $request) { ?>
				'<?php echo sts\safe_output($request['date']); ?>',
				<?php } ?>
					]);
				ticket_day_stats.Set('chart.title.vpos', 0.65);
				ticket_day_stats.Set('chart.title.hpos', 0.2);
				
				ticket_day_stats.Set('chart.key.background', 'white');
				ticket_day_stats.Set('chart.key', ["Added", "Closed"]);
				ticket_day_stats.Set('chart.key.position', 'gutter');
				ticket_day_stats.Set('chart.key.position.gutter.boxed', false);


				ticket_day_stats.Draw();
			}
		</script>		
		<?php
	}

}

?>