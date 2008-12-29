<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005-2007 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : download.php
 # created              : 2006-01-13
 # copyright            : (C) 2005-2007 The ComaCMS-Team
 # email                : comacms@williblau.de
 #----------------------------------------------------------------------
 # This program is free software; you can redistribute it and/or modify
 # it under the terms of the GNU General Public License as published by
 # the Free Software Foundation; either version 2 of the License, or
 # (at your option) any later version.
 #----------------------------------------------------------------------
	/*
	 * Usage:
	 * download.php?file_id=$[id_of the_file]
	 * Example:
	 * download.php?file_id=14
	 */
	/**
	 * @ignore
	 */
	define('COMACMS_RUN', true);
	// Do the things which are necessary
	include('common.php');
	// Load the file_id for the file which should be downloaded
	$file_id = GetPostOrGet('file_id');
	// Is it a numeric ID?
	if(is_numeric($file_id)) { // It is possible that this is a real file_id
		// Look up in the database
		$sql = "SELECT *
			FROM " .  DB_PREFIX . "files
			WHERE file_id = $file_id
			LIMIT 1";
		$file_result = $sqlConnection->SqlQuery($sql);
		if($file = mysql_fetch_object($file_result)) { // We have found a file in the database
			if(!file_exists($file->file_path) || $file->file_type == 'dir') { // Check: exists the file also on the server?
				// Show error page "download not found" 
				header('Location: special.php?page=d404');
				die();
			} 
			// Increment the downloads-count of the file
			$sql = "UPDATE " . DB_PREFIX . "files
				SET file_downloads = " . ($file->file_downloads + 1) . "
				WHERE file_id = $file_id";
			$sqlConnection->SqlQuery($sql);
			// set the headers for the download
			header("Content-Type: application/octet-stream");
			header("Content-Disposition: attachment; filename=\"$file->file_name\"");
			$fileSize = filesize($file->file_path);
			if (is_numeric($fileSize))
				header("Content-Length: $fileSize");
			readfile($file->file_path);
			die();	
		}
		else { // Show error page "download not found" 
			header('Location: special.php?page=d404');
			die();
		}
	}
	else { // It is impossible
		header('Location: index.php');
	}
	
?>