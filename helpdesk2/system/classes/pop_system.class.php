<?php
/**
 * 	POP System
 *	Copyright Dalegroup Pty Ltd 2015
 *	support@dalegroup.net
 *
 *
 * @package     dgx
 * @author      Michael Dale <support@dalegroup.net>
 */

namespace sts;

class pop_system {
	
	function __construct() {
	
	}
	
	public function download() {
	
		$log			= &singleton::get(__NAMESPACE__ . '\log');
		$config			= &singleton::get(__NAMESPACE__ . '\config');
		$pop_accounts	= &singleton::get(__NAMESPACE__ . '\pop_accounts');
		
		$accounts = $pop_accounts->get(array('enabled' => 1));
		
		if (count($accounts) > 0) {
			
			include(LIB . '/pop3/mime_parser.php');
			include(LIB . '/pop3/rfc822_addresses.php');
			include(LIB . '/pop3/pop3.php');
			include(LIB . '/pop3/sasl.php');

			if (in_array('pop3', stream_get_wrappers())) {
				stream_wrapper_unregister('pop3');
			}
			
			stream_wrapper_register('pop3', 'pop3_stream');  /* Register the pop3 stream handler class */
			
			$pop3 = new \pop3_class();

			$log_array['event_severity'] = 'notice';
			$log_array['event_number'] = E_USER_NOTICE;
			$log_array['event_description'] = 'POP3 Start';
			$log_array['event_file'] = __FILE__;
			$log_array['event_file_line'] = __LINE__;
			$log_array['event_type'] = 'download';
			$log_array['event_source'] = 'pop_system';
			$log_array['event_version'] = '1';
			$log_array['log_backtrace'] = false;	
				
			//$log->add($log_array);
			
			$storage_path = $config->get('storage_path');
			
			//loop through each enabled email account and download email
			foreach($accounts as $account) {
				$array = array();
			
				$array['obj'] 				= $pop3;
				$array['hostname']			= $account['hostname'];
				$array['port']				= $account['port'];
				$array['tls']				= $account['tls'];
				$array['username']			= $account['username'];
				$array['password']			= decode($account['password']);
				$array['priority_id']		= $account['priority_id'];
				$array['state_id']			= $account['state_id'];
				$array['department_id']		= $account['department_id'];
				$array['leave_messages']	= $account['leave_messages'];
				$array['auto_create_users']	= $account['auto_create_users'];
				$array['id']				= $account['id'];

				
				if ($config->get('storage_enabled') && !empty($storage_path) && ($account['download_files'])) {
					$array['download_files']	= 1;
				}
				else {
					$array['download_files']	= 0;
				}
				
				if ($config->get('html_enabled')) {
					$array['html_enabled']	= 1;
				}
				else {
					$array['html_enabled']	= 0;
				}
				
				$this->download_email($array);
				
				unset($array);
			}
			
			$log_array['event_severity'] = 'notice';
			$log_array['event_number'] = E_USER_NOTICE;
			$log_array['event_description'] = 'POP3 Stop Time: ' . stop_timer();
			$log_array['event_file'] = __FILE__;
			$log_array['event_file_line'] = __LINE__;
			$log_array['event_type'] = 'download';
			$log_array['event_source'] = 'pop_system';
			$log_array['event_version'] = '1';
			$log_array['log_backtrace'] = false;	
				
			//$log->add($log_array);
		}
		
		
	}	
	
	private function download_email($account) {
	
		$log			= &singleton::get(__NAMESPACE__ . '\log');
		$tickets		= &singleton::get(__NAMESPACE__ . '\tickets');
		$ticket_notes	= &singleton::get(__NAMESPACE__ . '\ticket_notes');
		$ticket_history	= &singleton::get(__NAMESPACE__ . '\ticket_history');
		$users			= &singleton::get(__NAMESPACE__ . '\users');
		$storage		= &singleton::get(__NAMESPACE__ . '\storage');
		$config			= &singleton::get(__NAMESPACE__ . '\config');
		$plugins		= &singleton::get(__NAMESPACE__ . '\plugins');
		$language		= &singleton::get(__NAMESPACE__ . '\language');
		
		$pop3				= $account['obj'];
		
		$pop3->hostname 	= $account['hostname'];		// POP 3 server host name
		$pop3->port 		= $account['port'];     	// POP 3 server host port
		$pop3->tls 			= $account['tls'];          // Establish secure connections using TLS
		$pop3->realm = '';                         /* Authentication realm or domain              */
		$pop3->workstation = '';                   /* Workstation for NTLM authentication         */
		$apop = 0;                                 /* Use APOP authentication                     */
		$pop3->authentication_mechanism = 'USER';  /* SASL authentication mechanism               */
		$pop3->debug = 0;                          /* Output debug information                    */
		$pop3->html_debug = 0;                     /* Debug information is in HTML                */
		$pop3->join_continuation_header_lines = 1; /* Concatenate headers split in multiple lines */

		if (($error = $pop3->Open()) == '') {
			if (($error = $pop3->Login($account['username'], $account['password'], $apop)) == '') {
				if (($error = $pop3->Statistics($messages, $size)) == '') {
					if ($messages > 0) {
						$log_array['event_severity'] = 'notice';
						$log_array['event_number'] = E_USER_NOTICE;
						$log_array['event_description'] = 'Found "' . (int) $messages . '" messages on POP server "' . safe_output($pop3->hostname) . '" for download';
						$log_array['event_file'] = __FILE__;
						$log_array['event_file_line'] = __LINE__;
						$log_array['event_type'] = 'download_email';
						$log_array['event_source'] = 'pop_system';
						$log_array['event_version'] = '1';
						$log_array['log_backtrace'] = false;	
							
						$log->add($log_array);
					
						$pop3->GetConnectionName($connection_name);
						
						for ($index = 1; $index <= $messages; $index++) {
							$saved_files		= array();
							$found_subject		= false;
						
							$message_file = 'pop3://' . $connection_name . '/' . $index;
							$mime = new \mime_parser_class();

							/*
							* Set to 0 for not decoding the message bodies
							*/
							$mime->decode_bodies = 1;
							$mime->decode_headers = 1;

							$mime->ignore_syntax_errors = 1;
														
							$parameters = array(
								'File' 		=> $message_file
							);
							$success = $mime->Decode($parameters, $email_message);
																					
							$email = array();
							
							$email['body'] = '';
							$email['html']	= 0;
									
							//get the message id								
							$email['message_id']		= '';
							
							if (isset($email_message[0]['Headers']['message-id:']))	{
								$email['message_id']	 = $email_message[0]['Headers']['message-id:'];
							}
							else if (isset($email_message[0]['Headers']['Message-ID:'])) {
								$email['message_id']	 = $email_message[0]['Headers']['Message-ID:'];							
							}
							else if (isset($email_message[0]	['Headers']['Message-Id:'])) {
								$email['message_id']	 = $email_message[0]['Headers']['Message-Id:'];							
							}
							else {
								if ($config->get('smtp_reject_missing_message_id')) {
									
									$log_array['event_severity'] = 'warning';
									$log_array['event_number'] = E_USER_WARNING;
									$log_array['event_description'] = 'Ignoring email from "'.safe_output($email_message[0]['ExtractedAddresses']['from:'][0]['address']).'" due to missing Message-ID';
									$log_array['event_file'] = __FILE__;
									$log_array['event_file_line'] = __LINE__;
									$log_array['event_type'] = 'download_email';
									$log_array['event_source'] = 'pop_system';
									$log_array['event_version'] = '1';
									$log_array['log_backtrace'] = false;	
										
									$log->add($log_array);
									
									continue;
								}
							}

							//check if already downloaded	
							$import_message = true;
										
							if (!empty($email['message_id'])) {
								$count = $this->count_message(array('message_id' => $email['message_id']));
								if ($count > 0) {
									$import_message = false;
								}
								else {
									$this->add_message(array('message_id' => $email['message_id']));
								}
							}
					
							
							if ($import_message) {

								/*
									$log_array['event_severity'] = 'notice';
									$log_array['event_number'] = E_USER_NOTICE;
									$log_array['event_description'] = 'Importing email with message-id "'.safe_output($email['message_id']).'"';
									$log_array['event_file'] = __FILE__;
									$log_array['event_file_line'] = __LINE__;
									$log_array['event_type'] = 'download_email';
									$log_array['event_source'] = 'pop_system';
									$log_array['event_version'] = '1';
									$log_array['log_backtrace'] = false;	
										
									$log->add($log_array);
								*/
							
								if ($mime->Analyze($email_message[0], $results)) {
								
									//print_r($results);
									//exit;
									if (!isset($results['Encoding'])) $results['Encoding'] = '';
								
									if ($results['Type'] == 'html') {
										$email['type'] = 'html';
										
										if ($account['html_enabled'] == 1) {
											if (isset($results['Data']) && !empty($results['Data'])) {	
												$email['html'] = 1;												
												$email['body'] = convert_encoding($results['Data'], $results['Encoding']);
											}
											else {
												$email['body'] = convert_encoding($results['Alternative'][0]['Data'], $results['Encoding']);
											}
										}
										else {
											if (isset($results['Alternative'][0]['Data']) && !empty($results['Alternative'][0]['Data'])) {	
												$email['body'] = convert_encoding($results['Alternative'][0]['Data'], $results['Encoding']);
											}
											else {
												$data = str_replace('<!DOCTYPE','<DOCTYPE',$results['Data']);

												$data = preg_replace("'<style[^>]*>.*</style>'siU",'',$data);
												$data = preg_replace('/</', ' <', $data);
												$data = preg_replace('/>/', '> ', $data);
												$data = html_entity_decode(strip_tags($data));
												$data = preg_replace('/[\n\r\t]/', ' ', $data);
												$data = preg_replace('/  /', ' ', $data);
												$data = trim($data);
												
												$email['body'] = convert_encoding($data, $results['Encoding']);
											}
										}

									}
									elseif ($results['Type'] == 'text') {
										$email['type'] = 'text';
										if (isset($results['Data']) && !empty($results['Data'])) {	
											$email['body'] = convert_encoding($results['Data'], $results['Encoding']);										
										}
									}
									
									//subject								
									if (isset($results['Subject']) && !empty($results['Subject'])) {
										$email['subject'] = convert_encoding($results['Subject'], $results['Encoding']);
									
										$found_subject				= TRUE;
									}
																		
									//save attachments
									if ($account['download_files'] == 1) {
										//there are 3 possible places where the file attachments could be
										if (isset($results['Attachments']) && is_array($results['Attachments'])) {
											foreach($results['Attachments'] as $possible_file) {
												if (isset($possible_file['Type'])) {
													if (!empty($possible_file['FileName'])) {
														$save_array['file']['name']		= convert_encoding($possible_file['FileName'], $results['Encoding']);
														$save_array['file']['data']		= $possible_file['Data'];
														$save_array['name']				= convert_encoding($possible_file['FileName'], $results['Encoding']);
														
														$saved_files[] = $storage->save_data($save_array);
													}
												}
											}
										}
										
										if (isset($results['Data'])) {
											if (!empty($results['FileName'])) {
												$save_array['file']['name']		= convert_encoding($results['FileName'], $results['Encoding']);
												$save_array['file']['data']		= $results['Data'];
												$save_array['name']				= convert_encoding($results['FileName'], $results['Encoding']);
												
												$saved_files[] = $storage->save_data($save_array);
											}
										}
										
										if (isset($results['Related']) && is_array($results['Related'])) {
											foreach($results['Related'] as $possible_file2) {
												if (isset($possible_file2['Type'])) {
													if (!empty($possible_file2['FileName'])) {
														$save_array['file']['name']		= convert_encoding($possible_file2['FileName'], $results['Encoding']);
														$save_array['file']['data']		= $possible_file2['Data'];
														$save_array['name']				= convert_encoding($possible_file2['FileName'], $results['Encoding']);
														
														$saved_files[] = $storage->save_data($save_array);
													}
												}
											}
										}									
										
									}
			
									if (isset($email_message[0]['ExtractedAddresses']['reply-to:'][0]['address'])) {
										$email['from_address'] 	= $email_message[0]['ExtractedAddresses']['reply-to:'][0]['address'];
									}
									else {
										$email['from_address'] 		= $email_message[0]['ExtractedAddresses']['from:'][0]['address'];
									}
									
									if (isset($email_message[0]['ExtractedAddresses']['cc:']) && is_array($email_message[0]['ExtractedAddresses']['cc:'])) {
										$email['cc'] = '';
										foreach($email_message[0]['ExtractedAddresses']['cc:'] as $cc) {
											$email['cc'] .= $cc['address'] . ',';
										}
										if (!empty($email['cc'])) {
											$email['cc'] = rtrim($email['cc'], ',');
										}
									}
									
									$email['from_name']			= '';
									if (isset($email_message[0]['ExtractedAddresses']['reply-to:'][0]['name'])) {
										$email['from_name'] 	= convert_encoding($email_message[0]['ExtractedAddresses']['reply-to:'][0]['name'], $results['Encoding']);
									}
									else if (isset($email_message[0]['ExtractedAddresses']['from:'][0]['name'])) {
										$email['from_name'] 	= convert_encoding($email_message[0]['ExtractedAddresses']['from:'][0]['name'], $results['Encoding']);
									}
									
									$email['to_address'] 		= $email_message[0]['Headers']['to:'];
									
									if (!$found_subject) {
										$email['subject']		 = convert_encoding($email_message[0]['Headers']['subject:'], $results['Encoding']);
									}
									
									if (empty($email['subject'])) {
										$email['subject']		= $language->get('{No Subject}');
									}
									
									$email['date'] 				= $email_message[0]['Headers']['date:'];
									
									$clients = NULL;
									if (!empty($email['from_address'])) {
										$clients 				= $users->get(array('email' => $email['from_address'], 'limit' => 1));
										
										if (empty($clients)) {
											if ($account['auto_create_users'] == 1) {
											
												$add_array['group_id']	 		= 0;
												$add_array['allow_login'] 		= 0;
												$add_array['welcome_email'] 	= 0;
												$add_array['name'] 				= $email['from_address'];
												if (!empty($email['from_name'])) {
													$add_array['name'] 			= $email['from_name'];
												}
												$add_array['email'] 			= $email['from_address'];
												$add_array['match_tickets'] 	= true;
												$add_array['company_id']		= 0;
												
												$plugins->run('pop_create_user_company_id', $add_array);
											
												$add_array_id = $users->add($add_array);

												$clients 						= $users->get(array('id' => $add_array_id, 'limit' => 1));
												
											}
										}
										
									}
										
									$new_ticket = true;

									$ticket_id = 0;
									
									if (preg_match("/\[TID:([[:alnum:]]+)-([[:digit:]]+)\]/", $email['subject'], $matches)) {
										$ticket_key = $matches[1];
										$ticket_id 	= (int) $matches[2];
									}
									else if (preg_match("/\[TID:([[:alnum:]]+)-([[:digit:]]+)\]/", $email['body'], $matches)) {
										$ticket_key = $matches[1];
										$ticket_id 	= (int) $matches[2];
									}
																	
									if ($ticket_id != 0) {
										//now we can check for the actual ticket
										$ticket_result = $tickets->get(array('id' => $ticket_id, 'get_other_data' => true));
										
										if (!empty($ticket_result)) {
											$ticket = $ticket_result[0];
											
											//confirm that the key matches the ticket
											if ($ticket['key'] == $ticket_key) {
											
												$history_array['ticket_id'] 			= (int) $ticket['id'];
												$history_array['date_added'] 			= datetime();
												$history_array['ip_address'] 			= ip_address();
												
												//start building note array
												$ticket_note['date_added']	= datetime();

												$ticket_note['description']	= $email['body'];
												$ticket_note['subject']		= $email['subject'];
												if (isset($email['cc'])) {
													$ticket_note['cc']			= $email['cc'];
												}
												
												if (count($clients) == 1) {
													$ticket_note['user_id']		= $clients[0]['id'];
													$ticket_note['company_id']	= $clients[0]['company_id'];
													$ticket_note['name']		= $clients[0]['name'];
												}
												else {
													$ticket_note['user_id']		= 0;
													$ticket_note['name']		= $email['from_name'];
													$ticket_note['email']		= $email['from_address'];
												}
												
												//get the user id if possible
												$history_array['by_user_id'] 	= $ticket_note['user_id'];
												
												//only reopen ticket if it is not already active or as per database config
												if ($ticket['active'] == 0 || $config->get('pop_auto_reopen_ticket')) {
													
													$update_ticket['state_id']				= $account['state_id'];
													$update_ticket['date_state_changed']	= datetime();
													$update_ticket['id']					= $ticket['id'];
													$tickets->edit($update_ticket);
													unset($update_ticket);
													
													$history_array['state_id'] 				= $account['state_id'];		
													$history_array['type'] 					= 'status';
													$history_array['history_description'] 	= $language->get('Auto Status Changed (via POP3)');
														
													$ticket_history->add(
														array(
															'columns' => $history_array
														)
													);	
													unset($history_array['state_id']);	
													
												}
												
												$ticket_note['html']			= (int) $email['html'];
												$ticket_note['ticket_id']		= $ticket['id'];
												
												if (!$config->get('notification_owner_on_pop')) {
													$ticket_note['notifications']['owner']	= false;
												}
												else {
													if ($ticket['owner_email'] == $email['from_address'] || $ticket['email'] == $email['from_address']) {
														//don't notify owner as they just sent the note
														$ticket_note['notifications']['owner']	= false;
													}
												}
												
												if ($config->get('store_email_data')) {
													$ticket_note['email_data'] = $email_message;
												}
												
												//attach files to ticket
												if ($account['download_files'] == 1) {
													if (!empty($saved_files)) {
														/*
														foreach ($saved_files as $file_id) {
															if ($file_id !== false) {
																$storage->add_file_to_ticket(array('file_id' => $file_id, 'ticket_id' => $ticket['id']));
															}
														}
														*/
														
														//attached files to ticket note (for email notification)
														$ticket_note['attach_file_ids']	= $saved_files;
													}
												}
																			

												$history_array['type'] 					= 'reply';
												$history_array['history_description'] 	= $language->get('Reply Added (via POP3)');
													
												$ticket_history->add(
													array(
														'columns' => $history_array
													)
												);			
												
												$note_id = $ticket_notes->add($ticket_note);
												
												$new_ticket = false;									
												
												unset($ticket_note);
											}
										}
									}

									//adds a new ticket if anything else fails
									if ($new_ticket) {
										//new ticket
										$array['subject']			= $email['subject'];
										$array['priority_id']		= $account['priority_id'];
										$array['description']		= $email['body'];
										$array['date_added']		= datetime();
										$array['department_id']		= $account['department_id'];
										$array['state_id']			= $account['state_id'];
							
										$array['user_id']			= 0;
										if (count($clients) == 1) {
											$array['user_id']		= $clients[0]['id'];
											$array['company_id']	= $clients[0]['company_id'];
											$array['name']			= $clients[0]['name'];
										}
									
										$array['email']				= $email['from_address'];
										$array['name']				= $email['from_name'];
										
										if (isset($email['cc'])) {
											$array['cc']				= $email['cc'];
										}
										
										$array['html']				= (int) $email['html'];
										$array['pop_account_id']	= (int) $account['id'];
										
										//print_r($array);
										
										if (!$config->get('notification_owner_on_pop')) {
											//don't notify owner as they just sent the ticket
											$array['notifications']['owner']	= false;
										}
										
										//attach files to ticket
										if ($account['download_files'] == 1) {
											if (!empty($saved_files)) {
												$array['attach_file_ids']	= $saved_files;
											}
										}
										
										if ($config->get('store_email_data')) {
											$array['email_data'] = $email_message;
										}
										
										$id = $tickets->add($array);
										
										unset($array);
									}
									
									if (!$account['leave_messages']) {
										//delete email from pop3 server if it has been added to the database (won't delete from gmail, but will stop it being downloaded via pop3 again)
										$pop3->DeleteMessage($index);
									}
																	
									unset($results);
								} //mime end
								
							} //import message end
						}
					}
				}
				else {
					//unable to retrieve mailbox statistics
				}
			}
			else {
				$log_array['event_severity'] = 'warning';
				$log_array['event_number'] = E_USER_WARNING;
				$log_array['event_description'] = 'Authentication to POP server "' . safe_output($pop3->hostname) . '" failed';
				$log_array['event_file'] = __FILE__;
				$log_array['event_file_line'] = __LINE__;
				$log_array['event_type'] = 'download_email';
				$log_array['event_source'] = 'pop_system';
				$log_array['event_version'] = '1';
				$log_array['log_backtrace'] = false;	
					
				$log->add($log_array);
			}
		}
		else {
			$log_array['event_severity'] = 'warning';
			$log_array['event_number'] = E_USER_WARNING;
			$log_array['event_description'] = 'Unable to connect to POP server "' . safe_output($pop3->hostname) . '", Error "' . safe_output($error) . '"';
			$log_array['event_file'] = __FILE__;
			$log_array['event_file_line'] = __LINE__;
			$log_array['event_type'] = 'download_email';
			$log_array['event_source'] = 'pop_system';
			$log_array['event_version'] = '1';
			$log_array['log_backtrace'] = false;	
				
			$log->add($log_array);
			
		}
		
		$pop3->Close();
	
	}
	
	private function add_message($array) {
		global $db;
				
		$error 		= &singleton::get(__NAMESPACE__ . '\error');
		$tables 	= &singleton::get(__NAMESPACE__ . '\tables');

		$site_id	= SITE_ID;

		$query = "INSERT INTO $tables->pop_messages (message_id, site_id";
	
		$query .= ") VALUES (:message_id, :site_id";
		
		$query .= ")";
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (Exception $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));		
		}
		
		$stmt->bindParam(':message_id', $array['message_id'], database::PARAM_STR);
		$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);
		
		try {
			$stmt->execute();
			$id = $db->lastInsertId();
		}
		catch (\Exception $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}
						
		return $id;	
	}
	
	public function count_message($array = NULL) {
		global $db;
		
		$tables =	&singleton::get(__NAMESPACE__ . '\tables');
		$error =	&singleton::get(__NAMESPACE__ . '\error');
		$site_id	= SITE_ID;
				
		$query = "SELECT count(*) AS `count` FROM $tables->pop_messages WHERE site_id = :site_id";
		
		if (isset($array['id'])) {
			$query .= " AND id = :id";
		}
		if (isset($array['message_id'])) {
			$query .= " AND message_id = :message_id";
		}
		
		try {
			$stmt = $db->prepare($query);
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
		}
		
		$stmt->bindParam(':site_id', $site_id);

		if (isset($array['id'])) {
			$id = $array['id'];
			$stmt->bindParam(':id', $id, database::PARAM_INT);
		}
		
		if (isset($array['message_id'])) {
			$message_id = $array['message_id'];
			$stmt->bindParam(':message_id', $message_id, database::PARAM_STR);
		}
		try {
			$stmt->execute();
		}
		catch (\PDOException $e) {
			$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
		}

		$count = $stmt->fetch(database::FETCH_ASSOC);
		
		return (int) $count['count'];
	}
	
	//this function removes old entries from the pop_messages table
	public function prune() {
		global $db;
		
		$tables 		= &singleton::get(__NAMESPACE__ . '\tables');
		$error 			= &singleton::get(__NAMESPACE__ . '\error');
		$config 		= &singleton::get(__NAMESPACE__ . '\config');
		$log 			= &singleton::get(__NAMESPACE__ . '\log');

		$site_id		= SITE_ID;
		
		$max_logs = (int) $config->get('pop_messages_limit');
		
		if ($max_logs > 0) {
			/*
				Get Total POP Messages
			*/
			
			$query = "SELECT count(*) as `count` FROM $tables->pop_messages WHERE site_id = :site_id";
							
			try {
				$stmt = $db->prepare($query);
			}
			catch (\Exception $e) {
				$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
			}
			
			$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);

					
			try {
				$stmt->execute();
			}
			catch (\Exception $e) {
				$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
			}
			
			$count_return = $stmt->fetchAll(database::FETCH_ASSOC);
			
			$count = (int) $count_return[0]['count'];
					
					
			if ($count > $max_logs) {
				$logs_to_delete = $count - $max_logs;
				
				$logs_to_delete = (int) $logs_to_delete;
				
				$event_delete_query = "DELETE FROM $tables->pop_messages WHERE site_id = :site_id ORDER BY id LIMIT :logs_to_delete";
				
				try {
					$stmt = $db->prepare($event_delete_query);
				}
				catch (\Exception $e) {
					$error->create(array('type' => 'sql_prepare_error', 'message' => $e->getMessage()));
				}

				$stmt->bindParam(':logs_to_delete', $logs_to_delete, database::PARAM_INT);				
				$stmt->bindParam(':site_id', $site_id, database::PARAM_INT);

				try {
					$stmt->execute();
				}
				catch (\Exception $e) {
					$error->create(array('type' => 'sql_execute_error', 'message' => $e->getMessage()));
				}
			}
			else {
				$logs_to_delete = 0;
			}

			$log_array['event_severity'] = 'notice';
			$log_array['event_number'] = E_USER_NOTICE;
			$log_array['event_description'] = 'POP Messages auto prune has finished and deleted ' . $logs_to_delete . ' entries.';
			$log_array['event_file'] = __FILE__;
			$log_array['event_file_line'] = __LINE__;
			$log_array['event_type'] = 'prune';
			$log_array['event_source'] = 'pop_system';	
			$log_array['event_version'] = '1';
			$log_array['log_backtrace'] = false;	
					
			$log->add($log_array);			
		}
	}

}

?>