<?php
	
	$srcDir = 'staff/uploads/prevPayslips/';
	
	
	
	function listFolderFiles($dir){
		$ffs = scandir($dir);
		echo '<ol>';
		foreach($ffs as $ff){
			if($ff != '.' && $ff != '..'){
				echo '<li>'.$ff;
				if(is_dir($dir.'/'.$ff)) listFolderFiles($dir.'/'.$ff);
				echo '</li>';
			}
		}
		echo '</ol>';
	}

	//listFolderFiles($srcDir);
	
	
	$srcDir = 'staff/uploads/prevPayslips/Aquino, Joshua Luke/Galan, Alessandre';
	$destDir = 'staff/uploads/staffs/agalan/payslips';
	
	if (!file_exists($destDir)) {
		mkdir($destDir, 0755, true);
		chmod($destDir.'/', 0777);
	}
	
	$delete = array();
	if (file_exists($destDir)) {
	  if (is_dir($destDir)) {
		if (is_writable($destDir)) {
		  if ($handle = opendir($srcDir)) {
			while (false !== ($file = readdir($handle))) {			
			  if (is_file($srcDir . '/' . $file)) {
				if(copy($srcDir . '/' . $file, $destDir . '/' . $file)){
					$delete[] = $srcDir . "/" . $file;
				}					
			  }
			}
			closedir($handle);
		  } else {
			echo "$srcDir could not be opened.\n";
		  }
		} else {
		  echo "$destDir is not writable!\n";
		}
	  } else {
		echo "$destDir is not a directory!\n";
	  }
	} else {
	  echo "$destDir does not exist\n";
	} 
	
	echo '<pre>';
	print_r($delete);
	echo '</pre>';
	
	unlink(realpath('prevPayslips/Aquino, Joshua Luke/Galan, Alessandre/Payroll_Galan_A_03-14-2014.pdf'));
	
	
	delete_directory($srcDir);
	
	$files = glob($srcDir); // get all file names
	foreach($files as $file){ // iterate files
	  if(is_file($file)){
		  unlink($file); // delete file
	  }
		//unlink($file); // delete file
	}
	
	removeDirectory('prevPayslips/Aquino, Joshua Luke/Galan, Alessandre');
	
	function removeDirectory($path) {
		$files = glob($path . '/*');
		foreach ($files as $file) {
			is_dir($file) ? removeDirectory($file) : unlink($file);
		}
		rmdir($path);
		return;
	}

	
	
	function delete_directory($dirname){
		if (is_dir($dirname))
			$dir_handle = opendir($dirname);
		if (!$dir_handle)
			return false;

		while($file = readdir($dir_handle)){
			if ($file != "." && $file != ".."){
				if (!is_dir($dirname."/".$file))
					unlink($dirname."/".$file);
				else
					delete_directory($dirname.'/'.$file);
			}
		}
		closedir($dir_handle);
		rmdir($dirname);
		return true;
	}
		
	
	
?>