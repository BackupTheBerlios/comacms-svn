<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005-2007 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : files.php
 # created              : 2007-02-10
 # copyright            : (C) 2005-2007 The ComaCMS-Team
 # email                : comacms@williblau.de
 #----------------------------------------------------------------------
 # This program is free software; you can redistribute it and/or modify
 # it under the terms of the GNU General Public License as published by
 # the Free Software Foundation; either version 2 of the License, or
 # (at your option) any later version.
 #----------------------------------------------------------------------
 	define('FILES_NAME', 0);
 	define('FILES_SIZE', 1);
 	define('FILES_DATE', 2);
 	define('FILES_TYPE', 3);
 	define('FILES_DOWNLOADS', 4);
 	
	class Files {
		
		var $SizeCount = 0;
		var $_SqlConnection;
		
		function Files(&$SqlConnection) {
			$this->_SqlConnection =&$SqlConnection;
		}
		
		function GetIDByPath($Path) {
			
		}
		
		function GetIDByFileName($FileName) {
			
		}
		
		function UploadFile() {
			
		}
		function AddFile() {
			
		}
		
		function GetFile($FileID) {
			$sql = "SELECT file_type, file_path, file_name, file_id, file_downloads, file_date, file_size, file_creator
					FROM " . DB_PREFIX . "files
					WHERE file_id = $FileID";
			$fileResult = $this->_SqlConnection->SqlQuery($sql);
			$file = null;
			if($file = mysql_fetch_object($fileResult))
				$file = array(
							'FILE_ID' => $file->file_id,
							'FILE_CREATOR' => $file->file_creator,
							'FILE_PATH' => utf8_encode($file->file_path),
							'FILE_NAME' => $file->file_name,
							'FILE_SIZE' => $file->file_size, 
							'FILE_DATE' => $file->file_date,
							'FILE_TYPE' => $file->file_type,
							'FILE_DOWNLOADS' => $file->file_downloads);
			return $file;
			
		}
		
		function DeleteFile($FileID) {
			$file = $this->GetFile($FileID);
			if($file === null)
				return false;
			// try to delete delete the file
			if(unlink($file['FILE_PATH'])) {
				// delete the database-entry
				$sql = "DELETE FROM " . DB_PREFIX . "files
						WHERE file_id = $FileID
						LIMIT 1";
				$this->_SqlConnection->SqlQuery($sql);
				return true;
			}
			return false; 
		}
		
		/*function RenameFile() {
			
		}*/
		
		function FillArray($Order = FILES_NAME, $Ascending = true) {
			$SizeCount = 0;
			
			$sql = "SELECT file_type, file_path, file_name, file_id, file_downloads, file_date, file_size
					FROM " . DB_PREFIX . "files
					ORDER BY ";
			switch ($Order) {
				case FILES_SIZE:
					$sql .= 'file_size';
					break;
				case FILES_DATE:
					$sql .= 'file_date';
					break;
				case FILES_TYPE:
					$sql .= 'file_type';
					break;
				case FILES_DOWNLOADS:
					$sql .= 'file_downloads';
					break;
				case FILES_NAME:
				default:
					$sql .= 'file_name';
					break;
			}
			// descending or ascending?
			if($Ascending)
				$sql .= ' ASC';
			else
				$sql .= ' DESC';
			
			$filesResult = $this->_SqlConnection->SqlQuery($sql);
			$files = array();						
			// show all files
			while($file = mysql_fetch_object($filesResult)) {
				$files[] = array(
							'FILE_ID' => $file->file_id,
							'FILE_PATH' => utf8_encode($file->file_path),
							'FILE_NAME' => $file->file_name,
							'FILE_SIZE' => $file->file_size, 
							'FILE_DATE' => $file->file_date,
							'FILE_TYPE' => $file->file_type,
							'FILE_DOWNLOADS' => $file->file_downloads);
				$this->SizeCount += $file->file_size;
			}
			return $files;
		}
	}
 
?>
