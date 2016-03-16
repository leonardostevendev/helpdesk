<?php
/**
 * 	Export Class
 *	Copyright Dalegroup Pty Ltd 2014
 *	support@dalegroup.net
 *
 *
 * @package     dgx
 * @author      Michael Dale <mdale@dalegroup.net>
 */
 
namespace sts;

if (!ini_get('safe_mode')) {
	//ooh we can process for sooo long
	set_time_limit(280); 
}

class export {     

	function direct_download($array) {
		
		// send response headers to the browser
		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment;filename=' . $array['filename_prefix'] .  '-' . datetime() . '.csv');
		$fp = fopen('php://output', 'w');

		fputcsv($fp, $array['headers']);
		
		foreach($array['rows'] as $row) {
			fputcsv($fp, $row);
		}
       
        fclose($fp);
	}
	
	function zip_download($array) {
	
		$uuid = uuid();
	
		// Prepare File
		$tmp_file = tempnam($uuid, "zip");
		$zip = new \ZipArchive();
		$zip->open($tmp_file, \ZipArchive::OVERWRITE);

		// Stuff with content
		//$zip->addFromString('file_name_within_archive.ext', $your_string_data);
				
		if (isset($array['files'])) {
			foreach($array['files'] as $file) {
				$zip->addFile($file['file'], $file['name']);
			}
		}

		// Close and send to users
		$zip->close();
		
		header('Content-Type: application/zip');
		header('Content-Length: ' . filesize($tmp_file));
		header( 'Content-Disposition: attachment;filename=' . $array['filename_prefix'] .  '-' . datetime() . '.zip');
		
		readfile($tmp_file);
		unlink($tmp_file); 
	}
}
?>