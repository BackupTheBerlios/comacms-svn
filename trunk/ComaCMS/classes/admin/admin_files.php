<?php
/**
 * @package ComaCMS
 * @subpackage AdminInterface
 * @copyright (C) 2005-2006 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : admin_files.php
 # created              : 2006-03-27
 # copyright            : (C) 2005-2006 The ComaCMS-Team
 # email                : comacms@williblau.de
 #----------------------------------------------------------------------
 # This program is free software; you can redistribute it and/or modify
 # it under the terms of the GNU General Public License as published by
 # the Free Software Foundation; either version 2 of the License, or
 # (at your option) any later version.
 #----------------------------------------------------------------------
 
 	/**
	 * @ignore
	 */
	require_once __ROOT__ . '/classes/admin/admin.php';
	require_once __ROOT__ . '/classes/imageconverter.php';
	
	/**
	 * Admin-Interface-Page to mangage Files
	 * @package ComaCMS
	 */
	class Admin_Files extends Admin{
		

 		
		/**
		 * Available actions (value of <var>$Action</var>):
		 *  - delete
		 *  - update_database
		 *  - check_new_files
		 *  - upload
		 * @access public
		 * @param string Action text
		 */
		function GetPage($Action) {
			$out = "\t\t\t<h2>" . $this->_Translation->GetTranslation('files') . "</h2>\r\n";
		 	$Action = strtolower($Action);
		 	switch ($Action) {
		 		case 'delete':		$out .= $this->_deletePage();
		 					break;
		 		case 'update_database':	$out .= $this->_updateDatabasePage();
		 					break;
		 		case 'check_new_files':	$out .= $this->_checkNewFilesPage();
		 					break;
		 		case 'upload':		$out .= $this->_uploadPage();
		 					break;
		 		default:		$out .= $this->_homePage();
		 	}
			return $out;
	 	}
	 	
	 	/**
	 	 * Shows a dialog, which makes it possible to confirm the deleting of a file or to delete the file if it's confirmed
	 	 * @access private
	 	 */
	 	function _deletePage() {
	 		// get the fileID of the file
	 		$fileID = GetPostOrGet('file_id');
	 		// try to get the confirmation
	 		$confirmation = GetPostOrGet('confirmation'); 
	 		// is the fileID something numeric? if not, stop the
	 		if(!is_numeric($fileID))
	 			return $this->_homePage();
	 		// try to get the file-information from database
			$sql = "SELECT *
				FROM " . DB_PREFIX . "files
				WHERE file_id = $fileID
				LIMIT 1";
			$fileResult = $this->_SqlConnection->SqlQuery($sql);
			if($file = mysql_fetch_object($fileResult)) {
				// the confirmation is given: delete this file!
				if($confirmation == 1) {
					// delete the database-entry
					$sql = "DELETE FROM " . DB_PREFIX . "files
						WHERE file_id = $fileID
						LIMIT 1";
					$this->_SqlConnection->SqlQuery($sql);
					// delete the file
					unlink($file->file_path);
				}
				else {
					// ask for the confirmation
					$out = sprintf($this->_Translation->GetTranslation('do_you_really_want_to_delete_the_file_%filename%_irrevocablly'), utf8_encode($file->file_name)). "<br />\r\n";
					$out .= sprintf($this->_Translation->GetTranslation('this_file_was_uploaded_on_%date%_at%_%time%_oclock_by_%username%'), date('d.m.Y', $file->file_date), date('H:i:s', $file->file_date), $this->_ComaLib->GetUserByID($file->file_creator)). "<br />\r\n";
					$out .= "<a href=\"admin.php?page=files&amp;action=delete&amp;file_id=$fileID&amp;confirmation=1\" title=\"" . sprintf($this->_Translation->GetTranslation('delete_file_%file%'), utf8_encode($file->file_name)) . "\"  class=\"button\">" . $this->_Translation->GetTranslation('yes') . "</a>
					<a href=\"admin.php?page=files\" title=\"" . sprintf($this->_Translation->GetTranslation('dont_delete_file_%file%'), utf8_encode($file->file_name)) . "\" class=\"button\">" . $this->_Translation->GetTranslation('no') . "</a>";
					return $out;
				}
			}
			return $this->_homePage();
	 	}
	 	
	 	/**
	 	 * Updates the file-table (but only the selected files):
	 	 *  - if a file is deleted manualy (not with ComaCMS) it removes the database-entry
	 	 *  - if a file is there but it isn't in the database it will be added
	 	 * @access private
	 	 */
	 	function _updateDatabasePage() {
	 		// get the selected files
	 		$changes = GetPostOrGet('change');
			// are files selected? no?
			if(count($changes) <= 0)
				// 'go home!' 
				return $this->_homePage();
			// for each selcted file
			foreach($changes as $change) {
				
				// 'repair' the filepath
				$change = rawurldecode($change);
				$change = utf8_decode($change);

				// is the file in the table?
				$sql = "SELECT file_id, file_path
					FROM " . DB_PREFIX . "files
					WHERE file_path = '$change'
					LIMIT 1";
				$file_result = $this->_SqlConnection->SqlQuery($sql);
				// is the file in the database?
				if(($file = mysql_fetch_object($file_result))) {
 					// the file doesn't exist?
 					if(!file_exists($change)) {
						// remove the database entry
						$sql = "DELETE FROM " . DB_PREFIX . "files
							WHERE file_id = $file->file_id
							LIMIT 1";
						$this->_SqlConnection->SqlQuery($sql);
 					}
 					// the file exists!
 					else {
 						// update the values, which could be changed
 						$sql = "UPDATE " . DB_PREFIX . "files
 							SET file_size = " . filesize($file->file_path) . ",
 							file_md5 = '" . md5_file($file->file_path) . "'
							WHERE file_id =$file->file_id
							LIMIT 1";
						$this->_SqlConnection->SqlQuery($sql);
 					}
				}
				// the file exists and has no entry in the database?
				elseif(file_exists($change)) {
					// create him a database-entry
					$sql = "INSERT INTO " . DB_PREFIX . "files (file_name, file_type, file_path, file_size, file_md5, file_date, file_creator)
						VALUES('" . basename($change) . "', '" . GetMimeContentType($change) . "', '$change', '" . filesize($change) . "', '" . md5_file($change) . "', " . mktime() . ", {$this->_User->ID})";
					$this->_SqlConnection->SqlQuery($sql);
				}
			}
			// 'go home!' 
			return $this->_homePage();
	 	}
	 	
	 	/**
	 	 * Shows a page where you are can check if the file-database is up-to-date and if not select the files which should be updated
	 	 * @access private
	 	 */
	 	function _checkNewFilesPage() {
	 		// TODO: make it configurable
	 		$uploadPath = './data/upload/';
	 		// basicly we think: "there are no changes" ;-)
	 		$changes = false;
	 		
	 		$md5s = array();
			
	 		$out = '';
			// get all files
			$sql = "SELECT file_path, file_md5, file_name
				FROM " . DB_PREFIX . "files";
			$files_result = $this->_SqlConnection->SqlQuery($sql);
			
			while($file = mysql_fetch_object($files_result)) {
				// the file still exists 
				if(file_exists($file->file_path))
					$md5s[$file->file_path] = $file->file_md5;
				// the file doesn't exist
				else {
					$out .= "<div class=\"row\"><label><strong>" . $this->_Translation->GetTranslation('remove_database_entry') . ":</strong> <span class=\"info\">" . $this->_Translation->GetTranslation('this_file_doesnt_exist_any_longer') . "</span></label><input type=\"checkbox\" name=\"change[]\" value=\"" . rawurlencode(utf8_encode($file->file_path)) ."\" checked=\"checked\" /> &quot;" . utf8_encode($file->file_name) ."&quot;</div>\r\n";
				}
			}
			// get all files in the upload-directory
			$files = dir($uploadPath);
			while($entry = $files->read()) {
  				if(is_file($uploadPath . $entry))
  					// exitsts this file in the database?
  					if(array_key_exists($uploadPath . $entry, $md5s)) {
  						// is it the same file we found?
  						if(md5_file($uploadPath . $entry) != $md5s[$uploadPath . $entry])
  							$out .= "<div class=\"row\"><label><strong>" . $this->_Translation->GetTranslation('refresh_database_entry') . ":</strong> <span class=\"info\">" . $this->_Translation->GetTranslation('this_insnt_the_file_which_is_registered_as_a_database_entry_under_this_name') . "</span></label><input type=\"checkbox\" name=\"change[]\" value=\"" . rawurlencode(utf8_encode($uploadPath . $entry)) ."\" checked=\"checked\" /> &quot;" . utf8_encode($entry) . "&quot;</div>\r\n";
  					}
  					// the file doesn't exist in the database
  					else
  						$out .= "<div class=\"row\"><label><strong>" . $this->_Translation->GetTranslation('add_to_database') . ":</strong> <span class=\"info\">" . $this->_Translation->GetTranslation('this_file_isnt_registered_in_the_database') . "</span></label><input type=\"checkbox\" name=\"change[]\" value=\"" . rawurlencode(utf8_encode($uploadPath . $entry)) ."\" checked=\"checked\" /> &quot;" . utf8_encode($entry) . "&quot;</div>\r\n";
  			
  						

			}
			$files->close();
			// is the output not empty? then there are some changes
			if($out != '')
				$changes = true;
				/*this_page_shows_all_files_which_are_changed_without_the_admin_interface
				to_apply_these_changes_select_the_files_which_should_be_updated
				warning:
				this_page_cant_recover_deleted_files*/
			$out = $this->_Translation->GetTranslation('this_page_shows_all_files_which_are_changed_without_the_admin_interface') . "<br />
				" . $this->_Translation->GetTranslation('to_apply_these_changes_select_the_files_which_should_be_updated') . "
				<div class=\"warning\"><strong>" . $this->_Translation->GetTranslation('warning') . ":</strong> " . $this->_Translation->GetTranslation('this_page_cant_recover_deleted_files') . "</div> 
				<fieldset>
					<legend>" . $this->_Translation->GetTranslation('changes') . "</legend>
					<form method=\"post\" action=\"admin.php\">
						<input type=\"hidden\" name=\"page\" value=\"files\"/>
						<input type=\"hidden\" name=\"action\" value=\"update_database\"/>\r\n"
				. $out
				. "<div class=\"row\">\r\n";
			// there are no changes : show this result to the user
			if(!$changes)
				$out .= $this->_Translation->GetTranslation('there_are_no_changes') . "</div>\r\n<div class=\"row\">";
			// back button
			$out .= "<a class=\"button\" href=\"admin.php?page=files\" title=\"" . $this->_Translation->GetTranslation('back') . "\">" . $this->_Translation->GetTranslation('back') . "</a>\r\n";

			// there are changes: add a 'appyl-button'
			if($changes)
				$out .= "<input type=\"submit\" class=\"button\" value=\"" . $this->_Translation->GetTranslation('apply') . "\"/>\r\n";
				
			$out .= "</div>
					</form>
				</fieldset>\n\r";
			return $out;
	 	}
	 	
	 	/**
	 	 * uploads files...
	 	 * @access private
	 	 */
	 	function _uploadPage() {
	 		// TODO: make it configurable
			$uploadPath = './data/upload/';
			
			$out = '';
			// foreach file that is 'posted' with this request
			foreach($_FILES as $name => $file) {
				// has it a trusted name? and has it some content 
				if(startsWith($name, 'uploadfile') && $file['error'] != 4) {
					// get the 'number of the upload'
					$nr = substr($name, 10);
					// alow to upload max. 5 files in one action
					if($nr < 5) {
						// genereate the new location of the file
						$savePath = $uploadPath . $file['name'];
						// if there exists a file try to rename the file that it is possible to save both
						if(file_exists($savePath))
							$savePath = uniqid($uploadPath) . $file['name'];
						// maximum filesize: ~1.5MB
						// TODO: make it configutable
						if($file['size'] > 1600000)
							$file['error'] = 2;
						// no upload errors?
						if($file['error'] == 0) {
							// dont allow an upload if a file with the same md5 exists
							$file_md5 = md5_file($file['tmp_name']);
							$sql = "SELECT file_name
								FROM " . DB_PREFIX . "files
								WHERE file_md5 = '$file_md5'
								LIMIT 1";
							$md5ExistsResult = $this->_SqlConnection->SqlQuery($sql);
							// is there a file with the same md5?
							if($md5Exists = mysql_fetch_object($md5ExistsResult)) {
								// show the user that the same file is already uploaded
								$out .= "<div class=\"error\"><strong>" . $this->_Translation->GetTranslation('error') . ":</strong> ". sprintf($this->_Translation->GetTranslation('the_file_%file%_is already_uploaded'), $file['name']); 
									
									/*Die Datei &quot;<strong>" . $file['name'] . "</strong>&quot; ist bereits hochgeladen worden" . " .*/
								if($md5Exists->file_name != $file['name'])
									$out .= ' ' . sprintf($this->_Translation->GetTranslation('the_file_has_a_different_name_%file%'), $md5Exists->file_name);
									//$out .= "(Sie hat nur einen anderen Namen: &quot;<strong>$md5exists->file_name</strong>&quot;).";
								$out .= "</div>\r\n";
							}
							else {
								// move the file into the uploadfolder
								if(move_uploaded_file($file['tmp_name'], $savePath)) {
								// add the database-entry for the file
									$sql = "INSERT INTO " . DB_PREFIX . "files (file_name, file_type, file_path, file_size, file_md5, file_date, file_creator)
										VALUES('{$file['name']}', '{$file['type']}', '$savePath', '" . filesize($savePath) . "', '" . md5_file($savePath) . "', " . mktime() . ", {$this->_User->ID})";
									$this->_SqlConnection->SqlQuery($sql);
									// prevent uploads, which aren't dowloadable(read-/writeable) by another user(ftp-access etc.)
									chmod($savePath, 0755);
									$out .= "<div><strong>" . $this->_Translation->GetTranslation('ok') . ":</strong> " . sprintf($this->_Translation->GetTranslation('the_file_%file%_was_uploaded'), $file['name']) . "</div>\r\n";
								}
							}
						}
						// there are some errors... show them!
						else {
							
							$out .= "<div class=\"error\"><strong>" . $this->_Translation->GetTranslation('error') . ":</strong> ";
							switch ($file['error']) {
								// file is to big (php.ini)
								case 1:		$out .= sprintf($this->_Translation->GetTranslation('the_file_%file%_is_bigger_than_the_maximum_upload_size_of_the_server'), $file['name']);
										break;
								// file is to big (MAX_FILE_SIZE)
								case 2:		$out .= sprintf($this->_Translation->GetTranslation('the_file_%file%_is_bigger_than_the_maximum_upload_size_of_%maximumsize%'), $file['name'], '1.5MB' );
										break;
								// file isn't completly transmitted
								case 3:		$out .= $this->_Translation->GetTranslation('the_file_was_only_partly_transmitted');
										break;
								// no upload
								case 4:		$out .= $this->_Translation->GetTranslation('there_was_no_file_transmitted');
										break;
								// unknown error -> say it wasn't possible to upload							
								default:	$out .= $this->_Translation->GetTranslation('wasnt_able_to_transmit_the_file');
										break;
							}
							$out .= "</div>\r\n";
						}
					}	
				}
			}
			// 'go home'
			return $out . $this->_homePage();
			
 		}
	 	
	 	/**
	 	 * mainpage with an overview over all files and a form to select 3 files for an upload
	 	 * @access private
	 	 */
	 	function _homePage() {
	 		$out = "\t\t\t<fieldset>
	 			<legend>" . $this->_Translation->GetTranslation('upload') . "</legend>
				<form enctype=\"multipart/form-data\" action=\"admin.php?page=files\" method=\"post\">
					<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"1600000\" />
					<input type=\"hidden\" name=\"action\" value=\"upload\" />
					<div class=\"row\">
						<label>
							<strong>" . $this->_Translation->GetTranslation('file') . " 1:</strong>
						</label>
						<input name=\"uploadfile0\" type=\"file\" />
					</div>
					<div class=\"row\">
						<label>
							<strong>" . $this->_Translation->GetTranslation('file') . " 2:</strong>
						</label>
						<input name=\"uploadfile1\" type=\"file\" />
					</div>
					<div class=\"row\">
						<label>
							<strong>" . $this->_Translation->GetTranslation('file') . " 3:</strong>
						</label>
						<input name=\"uploadfile2\" type=\"file\" />
					</div>
					<div class=\"row\">
						<input type=\"submit\" class=\"button\" value=\"" . $this->_Translation->GetTranslation('upload_files') . "\"/>
					</div>
				</form>
				<div class=\"row\">
					<a href=\"admin.php?page=files&amp;action=check_new_files\" class=\"button\">" . $this->_Translation->GetTranslation('check_for_changes') . "</a>
				</div>
			</fieldset>
			<table id=\"files\" class=\"text_table full_width\">
				<thead>
					<tr>
						<th>
							" .  $this->_Translation->GetTranslation('preview') . "
						</th>
						<th>
							<a href=\"admin.php?page=files&amp;sort=filename#files\" title=\"" . @sprintf($this->_Translation->GetTranslation('sort_ascending_by_%name%'), $this->_Translation->GetTranslation('filename')) . "\"><img alt=\"[" . @sprintf($this->_Translation->GetTranslation('sort_ascending_by_%name%'), $this->_Translation->GetTranslation('filename')) . "]\" src=\"img/up.png\"/></a>
							" . $this->_Translation->GetTranslation('filename') . "
							<a href=\"admin.php?page=files&amp;sort=filename&amp;desc=1#files\" title=\"" . @sprintf($this->_Translation->GetTranslation('sort_descending_by_%name%'), $this->_Translation->GetTranslation('filename')) . "\"><img alt=\"[" . @sprintf($this->_Translation->GetTranslation('sort_descending_by_%name%'), $this->_Translation->GetTranslation('filename')) . "]\" src=\"img/down.png\"/></a>
						</th>
						<th class=\"small_width\">
							<a href=\"admin.php?page=files&amp;sort=filesize#files\" title=\"" . sprintf($this->_Translation->GetTranslation('sort_ascending_by_%name%'), $this->_Translation->GetTranslation('filesize')) . "\"><img alt=\"[" . sprintf($this->_Translation->GetTranslation('sort_ascending_by_%name%'), $this->_Translation->GetTranslation('filesize')) . "]\" src=\"img/up.png\"/></a>
							" . $this->_Translation->GetTranslation('filesize') . "
							<a href=\"admin.php?page=files&amp;sort=filesize&amp;desc=1#files\" title=\"" . sprintf($this->_Translation->GetTranslation('sort_descending_by_%name%'), $this->_Translation->GetTranslation('filesize')) . "\"><img alt=\"[" . sprintf($this->_Translation->GetTranslation('sort_descending_by_%name%'), $this->_Translation->GetTranslation('filesize')) . "]\" src=\"img/down.png\"/></a>
						</th>
						<th class=\"table_date_width_plus\">
							<a href=\"admin.php?page=files&amp;sort=filedate#files\" title=\"" . sprintf($this->_Translation->GetTranslation('sort_ascending_by_%name%'), $this->_Translation->GetTranslation('date')) . "\"><img alt=\"[" . sprintf($this->_Translation->GetTranslation('sort_ascending_by_%name%'), $this->_Translation->GetTranslation('date')) . "]\" src=\"img/up.png\"/></a>
							" . $this->_Translation->GetTranslation('uploaded_on') . "
							<a href=\"admin.php?page=files&amp;sort=filedate&amp;desc=1#files\" title=\"" . sprintf($this->_Translation->GetTranslation('sort_descending_by_%name%'), $this->_Translation->GetTranslation('date')) . "\"><img alt=\"[" . sprintf($this->_Translation->GetTranslation('sort_descending_by_%name%'), $this->_Translation->GetTranslation('date')) . "]\" src=\"img/down.png\"/></a>
						</th>
						<th class=\"small_width\">
							<a href=\"admin.php?page=files&amp;sort=filetype#files\" title=\"" . sprintf($this->_Translation->GetTranslation('sort_ascending_by_%name%'), $this->_Translation->GetTranslation('filetype')) . "\"><img alt=\"[" . sprintf($this->_Translation->GetTranslation('sort_ascending_by_%name%'), $this->_Translation->GetTranslation('filetype')) . "]\" src=\"img/up.png\"/></a>
							" . $this->_Translation->GetTranslation('filetype') . "
							<a href=\"admin.php?page=files&amp;sort=filetype&amp;desc=1#files\" title=\"" . sprintf($this->_Translation->GetTranslation('sort_descending_by_%name%'), $this->_Translation->GetTranslation('filetype')) . "\"><img alt=\"[" . sprintf($this->_Translation->GetTranslation('sort_descending_by_%name%'), $this->_Translation->GetTranslation('filetype')) . "]\" src=\"img/down.png\"/></a>
						</th>
						<th class=\"table_mini_width\">
							<a href=\"admin.php?page=files&amp;sort=filedownloads#files\" title=\"" . sprintf($this->_Translation->GetTranslation('sort_ascending_by_%name%'), $this->_Translation->GetTranslation('downloads')) . "\"><img alt=\"[" . sprintf($this->_Translation->GetTranslation('sort_ascending_by_%name%'), $this->_Translation->GetTranslation('downloads')) . "]\" src=\"img/up.png\"/></a>
							<abbr title=\"" . $this->_Translation->GetTranslation('downloads') . "\">" . $this->_Translation->GetTranslation('downl') . "</abbr>
							<a href=\"admin.php?page=files&amp;sort=filedownloads&amp;desc=1#files\" title=\"" . sprintf($this->_Translation->GetTranslation('sort_descending_by_%name%'), $this->_Translation->GetTranslation('downloads')) . "\"><img alt=\"[" . sprintf($this->_Translation->GetTranslation('sort_descending_by_%name%'), $this->_Translation->GetTranslation('downloads')) . "]\" src=\"img/down.png\"/></a>
						</th>
						<th class=\"actions\">" . $this->_Translation->GetTranslation('actions') . "</th>
					</tr>
				</thead>\r\n";
			// get all files from the database/ which are registered in the database
			$sql = "SELECT file_type, file_path, file_name, file_id, file_downloads, file_date, file_size
				FROM " . DB_PREFIX . "files
				ORDER BY ";
			$sort = GetPostOrGet('sort');
			$desc = GetPostOrGet('desc');
			// sorting by what?
			switch ($sort) {
				case 'filename':	$sql .= 'file_name';	
							break;
				case 'filesize':	$sql .= 'file_size';
							break;
				case 'filedate':	$sql .= 'file_date';
							break;
				case 'filetype':	$sql .= 'file_type';
							break;
				case 'filedownloads':	$sql .= 'file_downloads';
							break;
				default:		$sql .= 'file_name';
							break;
			}
			// descending or ascending?
			if($desc == 1)
				$sql .= ' DESC';
			else
				$sql .= ' ASC';

			$files_result = $this->_SqlConnection->SqlQuery($sql);
			$completeSize = 0;
			$thumbnailfolder = $this->_Config->Get('thumbnailfolder', 'data/thumbnails/'); 
			// show all files
			while($file = mysql_fetch_object($files_result)) {
				$filePath = utf8_encode($file->file_path);
				$imageThumb = '';
				// if the file is an image try to get a thumbnail
				if(substr($file->file_type,0,6) == 'image/') {
					$image = new ImageConverter($file->file_path);
					// max: 100px;
					$maximum = 100;
					$size = $image->CalcSizeByMax($maximum);
					$imageUrl = '';
					if (file_exists($thumbnailfolder . '/' .  $size[0] . 'x' . $size[1] . '_' . basename($file->file_path)))
						$imageUrl = $thumbnailfolder . '/' .  $size[0] . 'x' . $size[1] . '_' . basename($file->file_path);
					else
						$imageUrl = $image->SaveResizedTo($size[0], $size[1], $thumbnailfolder, $size[0] . 'x' . $size[1] . '_');
					
					if($imageUrl)
						$imageThumb = "<img alt=\"$filePath\" src=\"". $imageUrl . "\" />";
				}
				
				$out .= "\t\t\t\t<tr>
					<td>$imageThumb</td>
					<td><span title=\"" . utf8_encode($file->file_path) . "\">" . utf8_encode($file->file_name) . "</span></td>
					<td>" . kbormb($file->file_size) . "</td>
					<td>" . date('d.m.Y H:i:s', $file->file_date) . "</td>
					<td>$file->file_type</td>
					<td>$file->file_downloads</td>
					<td>
						<a href=\"download.php?file_id=$file->file_id\" ><img src=\"./img/download.png\" height=\"16\" width=\"16\" alt=\"[" . sprintf($this->_Translation->GetTranslation('download_file_%file%'), utf8_encode($file->file_name)) . "]\" title=\"" . sprintf($this->_Translation->GetTranslation('download_file_%file%'), utf8_encode($file->file_name)) . "\"/></a>
						<a href=\"admin.php?page=files&amp;action=delete&amp;file_id=$file->file_id\" ><img src=\"./img/del.png\" height=\"16\" width=\"16\" alt=\" [" . sprintf($this->_Translation->GetTranslation('delete_file_%file%'), utf8_encode($file->file_name)) . "]\" title=\"" . sprintf($this->_Translation->GetTranslation('delete_file_%file%'), utf8_encode($file->file_name)) . "\"/></a>
					</td>
				</tr>\r\n";
				// count the size of all files together
				$completeSize += $file->file_size;
			}
			$out .= "\t\t\t</table>\r\n";
			$out .= "\t\t\t" . $this->_Translation->GetTranslation('altogether') . ' ' . kbormb($completeSize);

			return $out;
	 	}
	}
?>