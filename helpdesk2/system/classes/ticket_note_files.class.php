<?php
/**
 * 	Files to Tickets
 *	Copyright Dalegroup Pty Ltd 2015
 *	support@dalegroup.net
 *
 *
 * @package     sts
 * @author      Michael Dale <mdale@dalegroup.net>
 */

namespace sts;

class ticket_note_files extends table_access {

	private $table_name 		= NULL;
	private $allowed_columns 	= NULL;

	function __construct() {
	
		$this->set_table('files_to_ticket_notes');
		$this->allowed_columns(
				array(
					'ticket_id',
					'ticket_note_id',
					'file_id',
					'private'
				)
			);
		$this->table_name = $this->get_table();
		$this->allowed_columns	= $this->get_allowed_columns();

	}

}


?>