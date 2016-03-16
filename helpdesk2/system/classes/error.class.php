<?php
/**
 * 	Error Class
 *	Copyright Dalegroup Pty Ltd 2014
 *	support@dalegroup.net
 *
 *
 * @package     dgx
 * @author      Michael Dale <mdale@dalegroup.net>
 */
 
namespace sts;

class error {

	function create($array) {
	
		switch($array['type']) {
		
			case 'file_not_found':
				
			break;
			
			case 'sql_type_unsupported':
				
			break;
			
			case 'sql_connect_error':
				
			break;
			
			case 'sql_prepare_error':
				
			break;
			
			case 'sql_execute_error':
				
			break;
			
			case 'security_error':
			
			break;
			
			case '404';

			break;
		
		}
	
		echo '<p><strong>Dalegroup Tickets Error</strong><br />Type: ' . safe_output($array['type']) . '<br />Message: ' . safe_output($array['message']) . '</p>';
		
		exit(1);
	}
}

?>