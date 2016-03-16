<?php
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

if ($config->get('storage_enabled')) {

	$id 			= (int) $url->get_item();

	if ($id != 0) {
		$files = array();
		
		$files = $storage->get(array('id' => $id, 'public' => 1));
		
		if (count($files) == 1) {
			$file_name = $config->get('storage_path') . $files[0]['uuid'] . '.' . $files[0]['extension'];
					
			//only allow specific extensions
			if (in_array($files[0]['extension'], array('jpeg', 'jpg', 'gif', 'png'))) {
				//now test that the file is a real image
				if ($storage->load_image($file_name)) {
					//this is where we can display the image
					$file = file_get_contents($file_name);
					
					switch ($files[0]['extension']) { 
						case 'gif': 	$ctype="image/gif"; break; 
						case 'png': 	$ctype="image/png"; break; 
						case 'jpeg': 
						case 'jpg': 	$ctype="image/jpg"; break; 
						default: 		$ctype="application/force-download"; 
					} 
					
					header("Content-Type: $ctype"); 
					
					echo $file;
				}
				else {
					$error->create(array('type' => 'storage_image_corrupt', 'message' => 'Image must be a valid jpeg, jpg, gif or png file'));
				}
			}
			else {
				$error->create(array('type' => 'storage_image_invalid', 'message' => 'Image must be jpeg, jpg, gif or png file'));
			}
		}
		else {
			$error->create(array('type' => 'storage_file_not_found', 'message' => 'File Not Found'));
		}
	}
	else {
		$error->create(array('type' => 'storage_file_not_found', 'message' => 'File Not Found'));		
	}
}
else {
	$error->create(array('type' => 'storage_disabled', 'message' => 'File Storage Is Disabled'));
}

?>