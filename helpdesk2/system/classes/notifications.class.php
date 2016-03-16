<?php
/**
 * 	Notifications Class
 *	Copyright Dalegroup Pty Ltd 2014
 *	support@dalegroup.net
 *
 * 	This class is used for sending email notifications when a new ticket or ticket note is added
 *
 * @package     dgx
 * @author      Michael Dale <support@dalegroup.net>
 */

namespace sts;

class notifications {
	
	function __construct() {

	}
	
	public function reset_new_ticket_notification() {
		$config 		= &singleton::get(__NAMESPACE__ . '\config');
		
		$config->set('notification_new_ticket_subject', '#SITE_NAME# - #TICKET_SUBJECT#');
		
		$config->set('notification_new_ticket_body', '
		#TICKET_DESCRIPTION#
		<br /><br />
		#TICKET_KEY#
		<br /><br />
		#GUEST_URL#');

	}

	public function reset_new_ticket_note_notification() {
		$config 		= &singleton::get(__NAMESPACE__ . '\config');
		
		$config->set('notification_new_ticket_note_subject', '#SITE_NAME# - #TICKET_SUBJECT#');
		$config->set('notification_new_ticket_note_body', '
		#TICKET_NOTE_DESCRIPTION#
		<br /><br />
		#TICKET_KEY#
		<br /><br />
		#GUEST_URL#');	

	}
	
	public function reset_new_department_ticket_notification() {
		$config 		= &singleton::get(__NAMESPACE__ . '\config');
		
		$config->set('notification_new_department_ticket_subject', '#SITE_NAME# - #TICKET_SUBJECT#');		
		$config->set('notification_new_department_ticket_body', '
		A new ticket has been created in a department that you are assigned to.
		<br />
		<br />
		<b>Subject</b>: #TICKET_SUBJECT#
		<br />
		<b>For</b>: #TICKET_OWNER_NAME# (#TICKET_OWNER_EMAIL#)
		<br />
		<b>Department</b>: #TICKET_DEPARTMENT#
		<br />
		<b>Priority</b>: #TICKET_PRIORITY#
		<br />
		<br />
		#TICKET_DESCRIPTION#
		<br />
		<br />
		#TICKET_URL#');

	}

	public function reset_new_department_ticket_note_notification() {
		$config 		= &singleton::get(__NAMESPACE__ . '\config');
		
		$config->set('notification_new_department_ticket_note_subject', '#SITE_NAME# - #TICKET_SUBJECT#');
		$config->set('notification_new_department_ticket_note_body', '
		A new reply has been added to a ticket in a department that you are assigned to.
		<br />
		<br />
		<b>Subject</b>: #TICKET_SUBJECT#
		<br />
		<b>For</b>: #TICKET_OWNER_NAME# (#TICKET_OWNER_EMAIL#)
		<br />
		<b>Department</b>: #TICKET_DEPARTMENT#
		<br />
		<b>Priority</b>: #TICKET_PRIORITY#
		<br />
		<b>Assigned User</b>: #TICKET_ASSIGNED_NAME# (#TICKET_ASSIGNED_EMAIL#)
		<br />
		<br />
		#TICKET_NOTE_DESCRIPTION#
		<br />
		<br />
		#TICKET_URL#');	

	}
	
	public function reset_new_user_notification() {
		$config 		= &singleton::get(__NAMESPACE__ . '\config');
		
		$config->set('notification_new_user_subject', '#SITE_NAME# - New Account');
		$config->set('notification_new_user_body', '
		Hi #USER_FULLNAME#,
		<br /><br />
		A user account has been created for you at #SITE_NAME#.
		<br /><br />
		URL: 		#SITE_ADDRESS#<br />
		Name:		#USER_FULLNAME#<br />
		Username:	#USER_NAME#<br />
		Password:	#USER_PASSWORD#');	

	}
	
	public function reset_ticket_assigned_user_notification() {
		$config 		= &singleton::get(__NAMESPACE__ . '\config');
		
		$config->set('notification_ticket_assigned_user_subject', '#SITE_NAME# - Assigned To #TICKET_SUBJECT#');
		$config->set('notification_ticket_assigned_user_body', '
		A ticket has been assigned to you,
		<br />
		<br />
		<b>Subject</b>: #TICKET_SUBJECT#
		<br />
		<b>For</b>: #TICKET_OWNER_NAME# (#TICKET_OWNER_EMAIL#)
		<br />
		<b>Department</b>: #TICKET_DEPARTMENT#
		<br />
		<b>Priority</b>: #TICKET_PRIORITY#
		<br />
		<br />
		#TICKET_DESCRIPTION#
		<br />
		<br />
		#TICKET_URL#');		
	}
	
	public function reset_ticket_date_due_notification() {
		$config 		= &singleton::get(__NAMESPACE__ . '\config');
		
		$config->set('notification_ticket_date_due_subject', '#SITE_NAME# - #TICKET_SUBJECT# Due Tomorrow');
		$config->set('notification_ticket_date_due_body', '
		A ticket assigned to you is due tomorrow and is still open,
		<br />
		<br />
		<b>Subject</b>: #TICKET_SUBJECT#
		<br />
		<b>For</b>: #TICKET_OWNER_NAME# (#TICKET_OWNER_EMAIL#)
		<br />
		<b>Department</b>: #TICKET_DEPARTMENT#
		<br />
		<b>Priority</b>: #TICKET_PRIORITY#
		<br />
		<br />
		#TICKET_DESCRIPTION#
		<br />
		<br />
		#TICKET_URL#');		
	}
	
	
	public function reset_password_reset_notification() {
		$config 		= &singleton::get(__NAMESPACE__ . '\config');
		
		$config->set('notification_password_reset_subject', '#SITE_NAME# - Password Reset');
		$config->set('notification_password_reset_body', '
		A password reset request has been created for your account at #SITE_NAME#.<br /><br />
		To approve this reset please click on the following link:<br />
		#RESET_URL#');		 
	}
	
	//send email to user or anonymous user when a new ticket is created
	public function new_ticket($array) {
		global $db;
		
		$users 			= &singleton::get(__NAMESPACE__ . '\users');
		$mailer 		= &singleton::get(__NAMESPACE__ . '\mailer');
		$config 		= &singleton::get(__NAMESPACE__ . '\config');
		$pushover 		= &singleton::get(__NAMESPACE__ . '\pushover');
		$language 		= &singleton::get(__NAMESPACE__ . '\language');
		$tickets 		= &singleton::get(__NAMESPACE__ . '\tickets');
		
		if (!isset($array['id'])) {
			return false;
		}
		
		$tickets_array = $tickets->get(array('id' => (int) $array['id'], 'get_other_data' => true));

		if (count($tickets_array) == 1) {
			$ticket		= $tickets_array[0];
			
			if ($config->get('storage_enabled')) {
				$files = $tickets->get_files(array('id' => $ticket['id']));
				foreach($files as $file) {
					//$email_array['file'][] = array('file' => $config->get('storage_path') . $file['uuid'] . '.' . $file['extension'], 'file_name' => $file['name']);
				}
			}
	
			//global pushover notice
			if ($config->get('pushover_enabled')) {
				
				$pushover_users = $config->get('pushover_notify_users');
				
				if (!empty($pushover_users)) {
					
					$send_pushover_users = $users->get(array('ids' => $pushover_users));
			
					if (!empty($send_pushover_users)) {
						foreach ($send_pushover_users as $item) {
							$pushover_array = array(
								'user' 		=> $item['pushover_key'], 
								'title'		=> $ticket['subject']
							);
							
							$pushover_array['message'] 		= strip_tags($ticket['description']);
							
							$pushover_array['url'] 			= $config->get('address') .'/tickets/view/'.$ticket['id'].'/';
							$pushover_array['url_title'] 	= 'View Ticket';
							
							$pushover->queue($pushover_array);
							unset($pushover_array);
						}
					}
				}
			}

			$tokens = array(
				'#SITE_NAME#' 				=> $config->get('name'),
				'#SITE_ADDRESS#' 			=> $config->get('address'),
				'#TICKET_SUBJECT#' 			=> html_output($ticket['subject']),
				'#TICKET_KEY#' 				=> '[TID:' . $ticket['key'] . '-' . $ticket['id'] . ']',
				'#TICKET_ID#' 				=> $ticket['id'],
				'#TICKET_URL#'				=> '<a href="' . $config->get('address') . '/tickets/view/' . $ticket['id'] . '/' . '">' . $language->get('View Ticket') . '</a>',
				'#TICKET_DEPARTMENT#'		=> $ticket['department_name'],
				'#TICKET_STATUS#'			=> $ticket['status_name'],
				'#TICKET_PRIORITY#'			=> $ticket['priority_name'],
				'#TICKET_OWNER_NAME#'		=> $ticket['owner_name'],
				'#TICKET_OWNER_EMAIL#'		=> $ticket['owner_email'],
				'#TICKET_ASSIGNED_NAME#' 	=> $ticket['assigned_name'],
				'#TICKET_ASSIGNED_EMAIL#'	=> $ticket['assigned_email']
			);
			
			if ($ticket['user_id'] == 0) { 
				$tokens['#TICKET_OWNER_NAME#'] 	= $ticket['name'];
				$tokens['#TICKET_OWNER_EMAIL#'] = $ticket['email'];
			}
							
			$email_array['subject'] 	= str_replace_array($tokens, $config->get('notification_new_ticket_subject'));							
			$email_array['body'] 		= str_replace_array($tokens, $config->get('notification_new_ticket_body'));
			
			if (isset($ticket['html']) && $ticket['html'] == 1) {
				$email_array['body'] = str_replace('#TICKET_DESCRIPTION#', html_output($ticket['description']), $email_array['body']);
			}
			else {
				$email_array['body'] = str_replace('#TICKET_DESCRIPTION#', nl2br($ticket['description']), $email_array['body']);
			}
			
			if ($config->get('guest_portal')) {
				$email_array['body'] = str_replace('#GUEST_URL#', '<a href="' . $config->get('address') .'/guest/ticket_view/'.$ticket['id'].'/?access_key='.safe_output($ticket['access_key']).'">'. $language->get('View Ticket') . '</a>', $email_array['body']);
			}
			else {
				$email_array['body'] = str_replace('#GUEST_URL#', '', $email_array['body']);
			}
			
			if ($config->get('real_name_enabled') && isset($array['name'])) {
				$email_array['from_name'] = $array['name'];
			}
								
			$email_array['html']					= true;
			
			if (isset($ticket['pop_account_id'])) {
				$email_array['pop_account_id']		= $ticket['pop_account_id']; 
			}
			
			//send email to assigned user
			if (isset($ticket['assigned_user_id'])) {			
				if ($ticket['assigned_email_notifications'] == 1 && !empty($ticket['assigned_email'])) {
					$email_array['to']['to']				= $ticket['assigned_email'];
					$email_array['to']['to_name']			= $ticket['assigned_name'];					
					$mailer->queue_email($email_array);
				}			
			}
			
			//send email as cc
			if (isset($ticket['cc']) && !empty($ticket['cc'])) {
				$cc = unserialize($ticket['cc']);
				
				if (is_array($cc)) {
					foreach ($cc as $cc_item) {
						if (check_email_address($cc_item)) {
							$email_array['cc'][]	= array('to' => $cc_item);
						}
					}
				}
			}
						
			//email notices and user specific pushover notices
			if (isset($ticket['user_id']) && !empty($ticket['user_id'])) {
			
				if (isset($array['notifications']['owner']) && $array['notifications']['owner'] == false) {
					return false;
				}
				else {									
					if ($ticket['owner_email_notifications'] == 1 && !empty($ticket['owner_email'])) {

						$email_array['to']['to']				= $ticket['owner_email'];
						$email_array['to']['to_name']			= $ticket['owner_name'];					
						$mailer->queue_email($email_array);
						
						if (!empty($ticket['owner_pushover_key']) && $config->get('pushover_enabled') && $config->get('pushover_user_enabled')) {
							$pushover_array = array(
								'user' 		=> $ticket['owner_pushover_key'], 
								'title'		=> $email_array['subject']
							);
							
							$pushover_array['message'] = strip_tags($ticket['description']);
							
							if ($config->get('guest_portal')) {
								$pushover_array['url'] 			= $config->get('address') .'/guest/ticket_view/'.$ticket['id'].'/?access_key='.safe_output($ticket['access_key']);
								$pushover_array['url_title'] 	= 'View Ticket';
							}
							
							$pushover->queue($pushover_array);
						}
						
						return true;
					}
					else {
						return false;
					}
				}
			}
			else if ($config->get('anonymous_tickets_reply') && !empty($ticket['email'])) {
				if (isset($array['notifications']['owner']) && $array['notifications']['owner'] == false) {
					return false;
				}
				else {
					$email_array['to']['to']				= 	$ticket['email'];
					$email_array['to']['to_name']			= 	$ticket['name'];	
					$mailer->queue_email($email_array);
					return true;
				}
			}
			else {
				return false;
			}
		}
		
	}
	
	public function new_department_ticket($array) {
		global $db;
		
		$users 			= &singleton::get(__NAMESPACE__ . '\users');
		$mailer 		= &singleton::get(__NAMESPACE__ . '\mailer');
		$config 		= &singleton::get(__NAMESPACE__ . '\config');
		$pushover 		= &singleton::get(__NAMESPACE__ . '\pushover');
		$language 		= &singleton::get(__NAMESPACE__ . '\language');
		$tickets 		= &singleton::get(__NAMESPACE__ . '\tickets');
		
		if (!isset($array['id']) || !isset($array['department_id'])) {
			return false;
		}	
		
		
		$users_array = $users->get(array('notification_department' => array('id' => $array['department_id'], 'type' => 'new_department_ticket')));
		
		if (!empty($users_array)) {
	
			$tickets_array = $tickets->get(array('id' => (int) $array['id'], 'get_other_data' => true));

			if (count($tickets_array) == 1) {
				$ticket		= $tickets_array[0];

				if ($ticket['user_id'] == 0) { 
					$ticket['owner_name'] 	= $ticket['name'];
					$ticket['owner_email'] 	= $ticket['email'];
				}	
				
				$tokens = array(
						array('token' => '#SITE_NAME#', 'value' => $config->get('name')),
						array('token' => '#SITE_ADDRESS#', 'value' => $config->get('address')),
						array('token' => '#TICKET_SUBJECT#', 'value' => html_output($ticket['subject'])),
						array('token' => '#TICKET_KEY#', 'value' => '[TID:' . $ticket['key'] . '-' . $ticket['id'] . ']'),
						array('token' => '#TICKET_ID#', 'value' => $ticket['id']),
						array('token' => '#TICKET_URL#', 'value' => '<a href="' . $config->get('address') . '/tickets/view/' . $ticket['id'] . '/' . '">' . $language->get('View Ticket') . '</a>'),
						array('token' => '#TICKET_DEPARTMENT#', 'value' => $ticket['department_name']),
						array('token' => '#TICKET_STATUS#', 'value' => $ticket['status_name']),
						array('token' => '#TICKET_PRIORITY#', 'value' => $ticket['priority_name']),
						array('token' => '#TICKET_OWNER_NAME#', 'value' => $ticket['owner_name']),
						array('token' => '#TICKET_OWNER_EMAIL#', 'value' => $ticket['owner_email']),
						array('token' => '#TICKET_ASSIGNED_NAME#', 'value' => $ticket['assigned_name']),
						array('token' => '#TICKET_ASSIGNED_EMAIL#', 'value' => $ticket['assigned_email'])
					);
									
				$email_array['subject'] 	= $this->token_replace($config->get('notification_new_department_ticket_subject'), $tokens);							
				$email_array['body'] 		= $this->token_replace($config->get('notification_new_department_ticket_body'), $tokens);
				
				if (isset($ticket['html']) && $ticket['html'] == 1) {
					$email_array['body'] = str_replace('#TICKET_DESCRIPTION#', html_output($ticket['description']), $email_array['body']);
				}
				else {
					$email_array['body'] = str_replace('#TICKET_DESCRIPTION#', nl2br($ticket['description']), $email_array['body']);
				}
				
				if ($config->get('guest_portal')) {
					$email_array['body'] = str_replace('#GUEST_URL#', '<a href="' . $config->get('address') .'/guest/ticket_view/'.$ticket['id'].'/?access_key='.safe_output($ticket['access_key']).'">'. $language->get('View Ticket') . '</a>', $email_array['body']);
				}
				else {
					$email_array['body'] = str_replace('#GUEST_URL#', '', $email_array['body']);
				}
									
				$email_array['html']					= true;
				
				if (isset($ticket['pop_account_id'])) {
					$email_array['pop_account_id']		= $ticket['pop_account_id']; 
				}
				
				foreach($users_array as $user) {
					if (!empty($user['email']) && $user['email_notifications'] == 1) {
						//don't send email notifications to ticket owner or assigned user
						if (($user['id'] == $ticket['assigned_user_id']) || ($user['id'] == $ticket['user_id'])) {
							continue;
						}
						$email_array['to']['to']				= 	$user['email'];
						$email_array['to']['to_name']			= 	$user['name'];	
						$mailer->queue_email($email_array);
					}
				}
			}
			
		}
		
		
		
	}
	
	public function new_ticket_note($array) {
		global $db;
		
		$users 			= &singleton::get(__NAMESPACE__ . '\users');
		$mailer 		= &singleton::get(__NAMESPACE__ . '\mailer');
		$config 		= &singleton::get(__NAMESPACE__ . '\config');
		$tickets 		= &singleton::get(__NAMESPACE__ . '\tickets');
		$pushover 		= &singleton::get(__NAMESPACE__ . '\pushover');
		$language 		= &singleton::get(__NAMESPACE__ . '\language');
		$storage 		= &singleton::get(__NAMESPACE__ . '\storage');

		if (!isset($array['ticket_id'])) {
			return false;
		}
		
		$tickets_array = $tickets->get(array('id' => (int) $array['ticket_id'], 'get_other_data' => true));

		if (count($tickets_array) == 1) {
			$ticket		= $tickets_array[0];
			
			if ($config->get('storage_enabled')) {
				if (isset($array['attach_file_ids']) && !empty($array['attach_file_ids'])) {
					$files = $storage->get(array('ids' => $array['attach_file_ids']));
					foreach($files as $file) {
						$email_array['file'][] = array('file' => $config->get('storage_path') . $file['uuid'] . '.' . $file['extension'], 'file_name' => $file['name']);
					}
				}
			}
			
			//global pushover notice
			if ($config->get('pushover_enabled')) {
				
				$pushover_users = $config->get('pushover_notify_users');
				
				if (!empty($pushover_users)) {
					
					$send_pushover_users = $users->get(array('ids' => $pushover_users));
			
					if (!empty($send_pushover_users)) {
						foreach ($send_pushover_users as $item) {
							$pushover_array = array(
								'user' 		=> $item['pushover_key'], 
								'title'		=> $ticket['subject']
							);
							
							$pushover_array['message'] 		= strip_tags($array['description']);
							
							$pushover_array['url'] 			= $config->get('address') .'/tickets/view/'.$ticket['id'].'/';
							$pushover_array['url_title'] 	= 'View Ticket';
							
							$pushover->queue($pushover_array);
							unset($pushover_array);
						}
					}
				}
			}
			
			$tokens = array(
				array('token' => '#SITE_NAME#', 'value' => $config->get('name')),
				array('token' => '#SITE_ADDRESS#', 'value' => $config->get('address')),
				array('token' => '#TICKET_SUBJECT#', 'value' => html_output($ticket['subject'])),
				array('token' => '#TICKET_KEY#', 'value' => '[TID:' . $ticket['key'] . '-' . $ticket['id'] . ']'),
				array('token' => '#TICKET_ID#', 'value' => $ticket['id']),
				array('token' => '#TICKET_URL#', 'value' => '<a href="' . $config->get('address') . '/tickets/view/' . $ticket['id'] . '/' . '">' . $language->get('View Ticket') . '</a>'),
				array('token' => '#TICKET_DEPARTMENT#', 'value' => $ticket['department_name']),
				array('token' => '#TICKET_STATUS#', 'value' => $ticket['status_name']),
				array('token' => '#TICKET_PRIORITY#', 'value' => $ticket['priority_name']),
				array('token' => '#TICKET_OWNER_NAME#', 'value' => $ticket['owner_name']),
				array('token' => '#TICKET_OWNER_EMAIL#', 'value' => $ticket['owner_email']),
				array('token' => '#TICKET_ASSIGNED_NAME#', 'value' => $ticket['assigned_name']),
				array('token' => '#TICKET_ASSIGNED_EMAIL#', 'value' => $ticket['assigned_email'])
			);
						
			$email_array['subject'] 	= $this->token_replace($config->get('notification_new_ticket_note_subject'), $tokens);	
			$email_array['body'] 		= $this->token_replace($config->get('notification_new_ticket_note_body'), $tokens);	
			
			if ($config->get('real_name_enabled') && isset($array['name'])) {
				$email_array['from_name'] = $array['name'];
			}

			if (isset($ticket['html']) && $ticket['html'] == 1) {
				$email_array['body'] = str_replace('#TICKET_DESCRIPTION#', html_output($ticket['description']), $email_array['body']);
			}
			else {
				$email_array['body'] = str_replace('#TICKET_DESCRIPTION#', nl2br($ticket['description']), $email_array['body']);
			}
			
			if (isset($array['html']) && $array['html'] == 1) {
				$email_array['body'] = str_replace('#TICKET_NOTE_DESCRIPTION#', html_output($array['description']), $email_array['body']);
			}
			else {
				$email_array['body'] = str_replace('#TICKET_NOTE_DESCRIPTION#', nl2br($array['description']), $email_array['body']);
			}
			
			if ($config->get('guest_portal') && !empty($ticket['access_key'])) {
				$email_array['body'] = str_replace('#GUEST_URL#', '<a href="'. $config->get('address') .'/guest/ticket_view/'.$ticket['id'].'/?access_key='.safe_output($ticket['access_key']).'">' . $language->get('View Ticket') . '</a>', $email_array['body']);
			}
			else {
				$email_array['body'] = str_replace('#GUEST_URL#', '', $email_array['body']);
			}
						
			$email_array['html']				= 	true;

			if (isset($ticket['pop_account_id'])) {
				$email_array['pop_account_id']		= $ticket['pop_account_id']; 
			}
			
			//send email as cc
			if (isset($array['cc']) && !empty($array['cc'])) {
				$cc = unserialize($array['cc']);
				
				if (is_array($cc)) {
					foreach ($cc as $cc_item) {
						if (check_email_address($cc_item)) {
							$email_array['cc'][]	= array('to' => $cc_item);
						}
					}
				}
			}
			
			if (!isset($array['private']) || $array['private'] == 0) {
			
				//send email to ticket owner
				if (!empty($ticket['owner_email'])) {
					if ($ticket['owner_email_notifications'] == 1) {
						if (isset($array['notifications']['owner']) && $array['notifications']['owner'] == false && ($ticket['user_id'] == $array['user_id'])) {

						}
						else {
							$email_array['to']['to']			= 	$ticket['owner_email'];
							$email_array['to']['to_name']		= 	$ticket['owner_name'];		
									
							$mailer->queue_email($email_array);
							
							if (!empty($ticket['owner_pushover_key']) && $config->get('pushover_enabled') && $config->get('pushover_user_enabled')) {
								$pushover_array = array(
									'user' 		=> $ticket['owner_pushover_key'], 
									'title'		=> $email_array['subject']
								);
								
								$pushover_array['message'] = strip_tags($array['description']);
								
								if ($config->get('guest_portal')) {
									$pushover_array['url'] 			= $config->get('address') .'/guest/ticket_view/'.$ticket['id'].'/?access_key='.safe_output($ticket['access_key']);
									$pushover_array['url_title'] 	= 'View Ticket';
								}
								
								$pushover->queue($pushover_array);
							}
						}
					}
					else {
						//required so that CC emails are sent.
						if (!empty($email_array['cc'])) {
							$mailer->queue_email($email_array);
						}
					}
				}
				//send email to ticket owner if not a user
				else if ($config->get('anonymous_tickets_reply') && !empty($ticket['email'])) {
					if (isset($array['notifications']['owner']) && $array['notifications']['owner'] == false && ($ticket['email'] == $array['email'])) {

					}
					else {
						$email_array['to']['to']			= 	$ticket['email'];
						$email_array['to']['to_name']		= 	$ticket['name'];		
								
						$mailer->queue_email($email_array);
					}
				}
			}

			if (isset($email_array['cc'])) unset($email_array['cc']);
			
			//send email to ticket assigned user
			if (!empty($ticket['assigned_email']) && ($ticket['assigned_email_notifications'] == 1)) {
				$email_array['to']['to']			= 	$ticket['assigned_email'];
				$email_array['to']['to_name']		= 	$ticket['assigned_name'];		
						
				$mailer->queue_email($email_array);
				
				if (!empty($ticket['assigned_pushover_key']) && $config->get('pushover_enabled') && $config->get('pushover_user_enabled')) {
					$pushover_array = array(
						'user' 		=> $ticket['assigned_pushover_key'], 
						'title'		=> $email_array['subject']
					);
					
					$pushover_array['message'] = strip_tags($array['description']);
					
					$pushover_array['url'] 			= $config->get('address') .'/tickets/view/'.$ticket['id'].'/';
					$pushover_array['url_title'] 	= 'View Ticket';
					
					$pushover->queue($pushover_array);
				}
			}
		}
		else {
			return false;
		}
	}
	
	public function new_department_ticket_note($array) {
		global $db;
				
		$users 			= &singleton::get(__NAMESPACE__ . '\users');
		$mailer 		= &singleton::get(__NAMESPACE__ . '\mailer');
		$config 		= &singleton::get(__NAMESPACE__ . '\config');
		$tickets 		= &singleton::get(__NAMESPACE__ . '\tickets');
		$pushover 		= &singleton::get(__NAMESPACE__ . '\pushover');
		$language 		= &singleton::get(__NAMESPACE__ . '\language');

		if (!isset($array['ticket_id'])) {
			return false;
		}
			
		$tickets_array = $tickets->get(array('id' => (int) $array['ticket_id'], 'get_other_data' => true));

		if (count($tickets_array) == 1) {
	
			$ticket		= $tickets_array[0];
			
			$users_array = $users->get(array('notification_department' => array('id' => $ticket['department_id'], 'type' => 'new_department_ticket_reply')));
		
			if (!empty($users_array)) {
			
				$tokens = array(
					array('token' => '#SITE_NAME#', 'value' => $config->get('name')),
					array('token' => '#SITE_ADDRESS#', 'value' => $config->get('address')),
					array('token' => '#TICKET_SUBJECT#', 'value' => html_output($ticket['subject'])),
					array('token' => '#TICKET_KEY#', 'value' => '[TID:' . $ticket['key'] . '-' . $ticket['id'] . ']'),
					array('token' => '#TICKET_ID#', 'value' => $ticket['id']),
					array('token' => '#TICKET_URL#', 'value' => '<a href="' . $config->get('address') . '/tickets/view/' . $ticket['id'] . '/' . '">' . $language->get('View Ticket') . '</a>'),
					array('token' => '#TICKET_DEPARTMENT#', 'value' => $ticket['department_name']),
					array('token' => '#TICKET_STATUS#', 'value' => $ticket['status_name']),
					array('token' => '#TICKET_PRIORITY#', 'value' => $ticket['priority_name']),
					array('token' => '#TICKET_OWNER_NAME#', 'value' => $ticket['owner_name']),
					array('token' => '#TICKET_OWNER_EMAIL#', 'value' => $ticket['owner_email']),
					array('token' => '#TICKET_ASSIGNED_NAME#', 'value' => $ticket['assigned_name']),
					array('token' => '#TICKET_ASSIGNED_EMAIL#', 'value' => $ticket['assigned_email'])
				);
							
				$email_array['subject'] 	= $this->token_replace($config->get('notification_new_department_ticket_note_subject'), $tokens);	
				$email_array['body'] 		= $this->token_replace($config->get('notification_new_department_ticket_note_body'), $tokens);	

				if (isset($ticket['html']) && $ticket['html'] == 1) {
					$email_array['body'] = str_replace('#TICKET_DESCRIPTION#', html_output($ticket['description']), $email_array['body']);
				}
				else {
					$email_array['body'] = str_replace('#TICKET_DESCRIPTION#', nl2br($ticket['description']), $email_array['body']);
				}
				
				if (isset($array['html']) && $array['html'] == 1) {
					$email_array['body'] = str_replace('#TICKET_NOTE_DESCRIPTION#', html_output($array['description']), $email_array['body']);
				}
				else {
					$email_array['body'] = str_replace('#TICKET_NOTE_DESCRIPTION#', nl2br($array['description']), $email_array['body']);
				}
				
				if ($config->get('guest_portal') && !empty($ticket['access_key'])) {
					$email_array['body'] = str_replace('#GUEST_URL#', '<a href="'. $config->get('address') .'/guest/ticket_view/'.$ticket['id'].'/?access_key='.safe_output($ticket['access_key']).'">' . $language->get('View Ticket') . '</a>', $email_array['body']);
				}
				else {
					$email_array['body'] = str_replace('#GUEST_URL#', '', $email_array['body']);
				}
							
				$email_array['html']				= 	true;

				if (isset($ticket['pop_account_id'])) {
					$email_array['pop_account_id']		= $ticket['pop_account_id']; 
				}
				
				foreach($users_array as $user) {
					if (!empty($user['email']) && $user['email_notifications'] == 1) {
						//don't send email notifications to ticket owner or assigned user
						if (($user['id'] == $ticket['assigned_user_id']) || ($user['id'] == $ticket['user_id'])) {
							continue;
						}
						$email_array['to']['to']				= 	$user['email'];
						$email_array['to']['to_name']			= 	$user['name'];	
						$mailer->queue_email($email_array);
					}
				}
			}
			else {
				return false;
			}
		}
	}
	
	public function password_reset($array) {
		global $db;
		
		$mailer 		= &singleton::get(__NAMESPACE__ . '\mailer');
		$config 		= &singleton::get(__NAMESPACE__ . '\config');
		
				
		if (is_array($array['user'])) {
			$user = $array['user'];
						
			if (!empty($user['email'])) {
			
			
				$tokens = array(
					'#SITE_NAME#' 				=> $config->get('name'),
					'#SITE_ADDRESS#' 			=> $config->get('address'),
					'#RESET_URL#'				=> '<a href="'. $config->get('address') . '/reset/?key=' . urlencode($array['reset_key']) . '&amp;username=' . urlencode($user['username']) . '">' . $config->get('address') . '/reset/?key=' . urlencode($array['reset_key']) . '&amp;username=' . urlencode($user['username']) . '</a>'
				);
								
				$email_array['subject'] 			= str_replace_array($tokens, $config->get('notification_password_reset_subject'));	
		
				$email_array['body'] 				= str_replace_array($tokens, $config->get('notification_password_reset_body'));

				$email_array['to']['to']			= $user['email'];
				$email_array['to']['to_name']		= $user['name'];
				$email_array['html']				= true;
				
				$mailer->send_email($email_array);
			}
		}
		else {
			return false;
		}
	
	}
	
	public function new_user($array) {
		$mailer 		= &singleton::get(__NAMESPACE__ . '\mailer');
		$config 		= &singleton::get(__NAMESPACE__ . '\config');

		if (is_array($array) && !empty($array['email'])) {
		
			if (!isset($array['password'])) {
				$array['password'] = '';
			}
		
			$tokens = array(
				array('token' => '#SITE_NAME#', 'value' => $config->get('name')),
				array('token' => '#USER_FULLNAME#', 'value' => $array['name']),
				array('token' => '#USER_NAME#', 'value' => $array['username']),
				array('token' => '#USER_PASSWORD#', 'value' => $array['password']),
				array('token' => '#USER_EMAIL#', 'value' => $array['email']),
				array('token' => '#SITE_ADDRESS#', 'value' => $config->get('address')),
				
			);
						
			$email_array['subject'] 	= $this->token_replace($config->get('notification_new_user_subject'), $tokens);	
			$email_array['body'] 		= $this->token_replace($config->get('notification_new_user_body'), $tokens);	
				
			$email_array['html']					= true;
			$email_array['to']['to']				= $array['email'];
			$email_array['to']['to_name']			= $array['name'];			

			if (isset($array['pop_account_id'])) {
				$email_array['pop_account_id']		= $array['pop_account_id']; 
			}	
			
			$mailer->queue_email($email_array);
			
			return true;

		}
		else {
			return false;
		}	
	}
	
	public function ticket_assigned_user($array) {
		global $db;
		
		$users 			= &singleton::get(__NAMESPACE__ . '\users');
		$mailer 		= &singleton::get(__NAMESPACE__ . '\mailer');
		$config 		= &singleton::get(__NAMESPACE__ . '\config');
		$pushover 		= &singleton::get(__NAMESPACE__ . '\pushover');
		$language 		= &singleton::get(__NAMESPACE__ . '\language');
		$tickets 		= &singleton::get(__NAMESPACE__ . '\tickets');
					
		if (!isset($array['id'])) {
			return false;
		}	
		
		$tickets_array = $tickets->get(array('id' => (int) $array['id'], 'get_other_data' => true));

		if (count($tickets_array) == 1) {
			$ticket		= $tickets_array[0];

			$tokens = array(
						array('token' => '#SITE_NAME#', 'value' => $config->get('name')),
						array('token' => '#SITE_ADDRESS#', 'value' => $config->get('address')),
						array('token' => '#TICKET_SUBJECT#', 'value' => html_output($ticket['subject'])),
						array('token' => '#TICKET_KEY#', 'value' => '[TID:' . $ticket['key'] . '-' . $ticket['id'] . ']'),
						array('token' => '#TICKET_ID#', 'value' => $ticket['id']),
						array('token' => '#TICKET_URL#', 'value' => '<a href="' . $config->get('address') . '/tickets/view/' . $ticket['id'] . '/' . '">' . $language->get('View Ticket') . '</a>'),
						array('token' => '#TICKET_DEPARTMENT#', 'value' => $ticket['department_name']),
						array('token' => '#TICKET_STATUS#', 'value' => $ticket['status_name']),
						array('token' => '#TICKET_PRIORITY#', 'value' => $ticket['priority_name']),
						array('token' => '#TICKET_OWNER_NAME#', 'value' => $ticket['owner_name']),
						array('token' => '#TICKET_OWNER_EMAIL#', 'value' => $ticket['owner_email']),
						array('token' => '#TICKET_ASSIGNED_NAME#', 'value' => $ticket['assigned_name']),
						array('token' => '#TICKET_ASSIGNED_EMAIL#', 'value' => $ticket['assigned_email'])
					);
							
			$email_array['subject'] 	= $this->token_replace($config->get('notification_ticket_assigned_user_subject'), $tokens);							
			$email_array['body'] 		= $this->token_replace($config->get('notification_ticket_assigned_user_body'), $tokens);
			
			if (isset($ticket['html']) && $ticket['html'] == 1) {
				$email_array['body'] = str_replace('#TICKET_DESCRIPTION#', html_output($ticket['description']), $email_array['body']);
			}
			else {
				$email_array['body'] = str_replace('#TICKET_DESCRIPTION#', nl2br($ticket['description']), $email_array['body']);
			}
			
			if ($config->get('guest_portal')) {
				$email_array['body'] = str_replace('#GUEST_URL#', '<a href="' . $config->get('address') .'/guest/ticket_view/'.$ticket['id'].'/?access_key='.safe_output($ticket['access_key']).'">'. $language->get('View Ticket') . '</a>', $email_array['body']);
			}
			else {
				$email_array['body'] = str_replace('#GUEST_URL#', '', $email_array['body']);
			}
								
			$email_array['html']					= true;
			
			if (isset($ticket['pop_account_id'])) {
				$email_array['pop_account_id']		= $ticket['pop_account_id']; 
			}
			
			if (!empty($ticket['assigned_email']) && $ticket['assigned_email_notifications'] == 1) {
				$email_array['to']['to']				= 	$ticket['assigned_email'];
				$email_array['to']['to_name']			= 	$ticket['assigned_name'];	
				$mailer->queue_email($email_array);
			}
		}
	
	}	
	
	public function ticket_date_due($array) {
		global $db;
		
		$users 			= &singleton::get(__NAMESPACE__ . '\users');
		$mailer 		= &singleton::get(__NAMESPACE__ . '\mailer');
		$config 		= &singleton::get(__NAMESPACE__ . '\config');
		$pushover 		= &singleton::get(__NAMESPACE__ . '\pushover');
		$language 		= &singleton::get(__NAMESPACE__ . '\language');
		$tickets 		= &singleton::get(__NAMESPACE__ . '\tickets');
					
		if (!isset($array['id'])) {
			return false;
		}	
		
		$tickets_array = $tickets->get(array('id' => (int) $array['id'], 'get_other_data' => true));

		if (count($tickets_array) == 1) {
			$ticket		= $tickets_array[0];

			$tokens = array(
						array('token' => '#SITE_NAME#', 'value' => $config->get('name')),
						array('token' => '#SITE_ADDRESS#', 'value' => $config->get('address')),
						array('token' => '#TICKET_SUBJECT#', 'value' => html_output($ticket['subject'])),
						array('token' => '#TICKET_KEY#', 'value' => '[TID:' . $ticket['key'] . '-' . $ticket['id'] . ']'),
						array('token' => '#TICKET_ID#', 'value' => $ticket['id']),
						array('token' => '#TICKET_URL#', 'value' => '<a href="' . $config->get('address') . '/tickets/view/' . $ticket['id'] . '/' . '">' . $language->get('View Ticket') . '</a>'),
						array('token' => '#TICKET_DEPARTMENT#', 'value' => $ticket['department_name']),
						array('token' => '#TICKET_STATUS#', 'value' => $ticket['status_name']),
						array('token' => '#TICKET_PRIORITY#', 'value' => $ticket['priority_name']),
						array('token' => '#TICKET_OWNER_NAME#', 'value' => $ticket['owner_name']),
						array('token' => '#TICKET_OWNER_EMAIL#', 'value' => $ticket['owner_email']),
						array('token' => '#TICKET_ASSIGNED_NAME#', 'value' => $ticket['assigned_name']),
						array('token' => '#TICKET_ASSIGNED_EMAIL#', 'value' => $ticket['assigned_email'])
					);
							
			$email_array['subject'] 	= $this->token_replace($config->get('notification_ticket_date_due_subject'), $tokens);							
			$email_array['body'] 		= $this->token_replace($config->get('notification_ticket_date_due_body'), $tokens);
			
			if (isset($ticket['html']) && $ticket['html'] == 1) {
				$email_array['body'] = str_replace('#TICKET_DESCRIPTION#', html_output($ticket['description']), $email_array['body']);
			}
			else {
				$email_array['body'] = str_replace('#TICKET_DESCRIPTION#', nl2br($ticket['description']), $email_array['body']);
			}
			
			if ($config->get('guest_portal')) {
				$email_array['body'] = str_replace('#GUEST_URL#', '<a href="' . $config->get('address') .'/guest/ticket_view/'.$ticket['id'].'/?access_key='.safe_output($ticket['access_key']).'">'. $language->get('View Ticket') . '</a>', $email_array['body']);
			}
			else {
				$email_array['body'] = str_replace('#GUEST_URL#', '', $email_array['body']);
			}
								
			$email_array['html']					= true;
			
			if (isset($ticket['pop_account_id'])) {
				$email_array['pop_account_id']		= $ticket['pop_account_id']; 
			}
			
			if (!empty($ticket['assigned_email']) && $ticket['assigned_email_notifications'] == 1) {
				$email_array['to']['to']				= 	$ticket['assigned_email'];
				$email_array['to']['to_name']			= 	$ticket['assigned_name'];	
				$mailer->queue_email($email_array);
			}
		}
	
	}	
	
	public function new_message($array) {
	
	}
	
	public function new_message_note($array) {
	
	}
	
	public function token_replace($string, $array) {
	
		foreach($array as $item) {
			$string = str_replace($item['token'], $item['value'], $string);	
		}
		
		return $string;
	
	}
}

?>