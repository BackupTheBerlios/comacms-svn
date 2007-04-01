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
		var $_User;
		function Files(&$SqlConnection, &$User) {
			$this->_SqlConnection = &$SqlConnection;
			$this->_User = &$User;
		}
		
		
		
		function GetIDByPath($Path) {
			$sql = "SELECT file_id
					FROM " . DB_PREFIX . "files
					WHERE file_path='$Path'
					LIMIT 1";
			$result = $this->_SqlConnection->SqlQuery($sql);
			if($file = mysql_fetch_object($result))
				return $file->file_id;
			else
				return -1;
		}
		
		function GetIDByFileName($FileName) {
			$sql = "SELECT file_id
					FROM " . DB_PREFIX . "files
					WHERE file_name='$FileName'
					LIMIT 1";
			$result = $this->_SqlConnection->SqlQuery($sql);
			if($file = mysql_fetch_object($result))
				return $file->file_id;
			else
				return -1;
		}
		/**
		 * @return string UploadedFileName
		 */
		function UploadFile($Name, $DestinationFolder, $RegisterFile = true, $Chmod = 0775) {
			if(!array_key_exists($Name, $_FILES))
				return false;
			$file = &$_FILES[$Name];
			// generate the new location of the file
			$savePath = $DestinationFolder . $file['name'];
			// if there exists a file try to rename the file that it is possible to save both
			if(file_exists($savePath))
				$savePath = uniqid($DestinationFolder) . $file['name'];
			if(file_exists($savePath))
				return false;
			// maximum filesize: ~1.5MB
			// TODO: make it configutable
			if($file['size'] > 1600000)
				$file['error'] = 2;
			
			if($file['error'] != 0)
				return $file['error'];
			
			if($RegisterFile) {
				// dont allow an upload if a file with the same md5 exists
				$fileMd5 = md5_file($file['tmp_name']);
				$sql = "SELECT file_path
						FROM " . DB_PREFIX . "files
						WHERE file_md5 = '$fileMd5'
						LIMIT 1";
				$md5ExistsResult = $this->_SqlConnection->SqlQuery($sql);
				// is there a file with the same md5?
				if($md5Exists = mysql_fetch_object($md5ExistsResult))
					// return the filename of the equivalent file
					return $md5Exists->file_path;
			}
			// move the file into the uploadfolder
			if(move_uploaded_file($file['tmp_name'], $savePath)) {
				if($RegisterFile)
					// add the database-entry for the file
					$this->AddFile($savePath);
				chmod($savePath, $Chmod);
				return $savePath;
			}
			return false;
		}
		
		
		function AddFile($Path) {
			if($this->GetIDByPath($Path) != -1)
				return;
			$sql = "INSERT INTO " . DB_PREFIX . "files (file_name, file_type, file_path, file_size, file_md5, file_date, file_creator)
					VALUES('" . basename($Path) . "', '" . GetMimeContentType($Path) . "', '$Path', '" . filesize($Path) . "', '" . md5_file($Path) . "', " . mktime() . ", {$this->_User->ID})";
			$this->_SqlConnection->SqlQuery($sql);
		}
		
		function GetFile($FileID) {
			if(!is_numeric($FileID))
				return null;
			$sql = "SELECT file_type, file_path, file_name, file_id, file_downloads, file_date, file_size, file_creator
					FROM " . DB_PREFIX . "files
					WHERE file_id = $FileID";
			$fileResult = $this->_SqlConnection->SqlQuery($sql);
			$file = null;
			if($file = mysql_fetch_object($fileResult))
				$file = array(
							'FILE_ID' => $file->file_id,
							'FILE_CREATOR' => $file->file_creator,
							'FILE_PATH' => $file->file_path,
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
			$fileName = $file['FILE_PATH'];
		
			// try to delete delete the file
			if(file_exists($fileName) && unlink($fileName)) {
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
		
		function FillArray($Order = FILES_NAME, $Ascending = true, $Where = '') {
			$SizeCount = 0;
			if(!empty($Where))
				$Where = "WHERE " . $Where;
			$sql = "SELECT file_type, file_path, file_name, file_id, file_downloads, file_date, file_size
					FROM " . DB_PREFIX . "files " .
					$Where . "
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
							'FILE_PATH' => $file->file_path,
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
