<?php
/**
 * @package ComaCMS
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
	require_once('./classes/admin/admin.php');
	
	
	/**
	 * @package ComaCMS
	 */
	class Admin_Files extends Admin{
		
		/**
 		 * @param SqlConnection SqlConnection
 		 * @param array AdminLang
 		 * @param User User
 		 * @param Config Config
 		 * @return void
 		 */
 		function Admin_Files(&$SqlConnection, &$AdminLang, &$User, &$Config) {
			$this->_SqlConnection = &$SqlConnection;
			$this->_AdminLang = &$AdminLang;
			$this->_User = &$User;
			$this->_Config = &$Config;
		}
 		
		
		function GetPage($action) {
			$out = "\t\t\t<h2>{$this->_AdminLang['files']}</h2>\r\n";
		 	$action = strtolower($action);
		 	switch ($action) {
/*		 		case 'save':		$out .= $this->_saveDate($admin_lang);
		 					break;*/
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
	 	
	 	function _deletePage() {
	 		$fileID = GetPostOrGet('file_id');
	 		$confirmation = GetPostOrGet('confirmation'); 
	 		
	 		if(!is_numeric($fileID))
	 			return $this->_homePage();
	 		
			$sql = "SELECT *
				FROM " . DB_PREFIX . "files
				WHERE file_id = $fileID
				LIMIT 1";
			$fileResult = $this->_SqlConnection->SqlQuery($sql);
			if($file = mysql_fetch_object($fileResult)) {
				if($confirmation == 1) {
					$sql = "DELETE FROM " . DB_PREFIX . "files
						WHERE file_id = $fileID
						LIMIT 1";
					$this->_SqlConnection->SqlQuery($sql);
					unlink($file->file_path);
				}
				else {
					$out = "Sind sie sicher, dass sie die Datei &quot;$file->file_name&quot; unwiederruflich l&ouml;schen wollen?<br />
					Die Datei wurde am " . date('d.m.Y', $file->file_date) . " um " . date('H:i:s', $file->file_date) ." hochgeladen.<br />
					<a href=\"admin.php?page=files&amp;action=delete&amp;file_id=" . $fileID . "&amp;confirmation=1\" title=\"Wirklich L&ouml;schen\"  class=\"button\">{$this->_AdminLang['yes']}</a>
					<a href=\"admin.php?page=files\" title=\"Nicht L&ouml;schen\" class=\"button\">{$this->_AdminLang['no']}</a>";
					return $out;
			}
			}
			return $this->_homePage();
	 	}
	 	
	 	function _updateDatabasePage() {
	 		$changes = GetPostOrGet('change');
			if(count($changes) > 0) {
				foreach($changes as $change) {
					
					$change = rawurldecode($change);
					$change = utf8_decode($change);
					$sql = "SELECT *
						FROM " . DB_PREFIX . "files
						WHERE file_path = '$change'
						LIMIT 0,1";
					$file_result = db_result($sql);
					if(($file = mysql_fetch_object($file_result)) && !file_exists($change)) {
						$sql = "DELETE FROM " . DB_PREFIX . "files
							WHERE file_id=$file->file_id";
						db_result($sql);
						//echo 'not:' . $change .'<br/>';
					}
					elseif(file_exists($change)) {
						$sql = "INSERT INTO " . DB_PREFIX . "files (file_name, file_type, file_path, file_size, file_md5, file_date)
							VALUES('" . basename($change) . "', '" . GetMimeContentType($change) . "', '$change', '" . filesize($change) . "', '" . md5_file($change) . "', " . mktime() . ")";
						db_result($sql);
						//echo 'yes:' . $change. '<br/>';
					}
					
						//echo 'why:' . $change. '<br/>';
				}
			}
			return $this->_homePage();
	 	}
	 	
	 	function _checkNewFilesPage() {
	 		$upload_path = './data/upload/';
	 		$changes = false;
	 		$out = '';
			$sql = "SELECT *
				FROM " . DB_PREFIX . "files
				ORDER BY file_date DESC";
			$files_result = db_result($sql);
			$md5s = array();
			$newmd5s = array();
			while($file = mysql_fetch_object($files_result)) {
				if(file_exists($file->file_path))
					$md5s[$file->file_path] = $file->file_md5;
				else {
					$out .= "<input type=\"checkbox\" name=\"change[]\" value=\"" . rawurlencode(utf8_encode($file->file_path)) ."\" checked=\"checked\" /><strong>Aus der Datenbank entfernen</strong> " . utf8_encode($file->file_name) ."<br />\r\n";
				}
			}
			$files = dir($upload_path);

			while($entry = $files->read()) {
  				if(is_file($upload_path . $entry))
  					if(!in_array(md5_file($upload_path . $entry),$md5s))
  						$out .= "<input type=\"checkbox\" name=\"change[]\" value=\"" . rawurlencode(utf8_encode($upload_path . $entry)) ."\" checked=\"checked\" /><strong>Zur Datenbank hinzuf&uuml;gen</strong> " . utf8_encode($entry) . "<br />\r\n";
  						
  				//echo $upload_path.$entry . '-' . md5_file($upload_path.$entry) . "<br>";
			}
			$files->close();
			if($out != '')
				$changes = true;
			$out = "Hier werden alle Dateien angezeigt, die nicht &uuml;ber das Admin-Interface Ver&auml;ndert worden sind, um diese Ver&auml;nderungen in die Datenbank zu &uuml;bernehmen haken sie alle die Dateien an, die sie aktualisieren m&ouml;chten.<br /><br />\r\n" .
					"\t<form method=\"post\" action=\"" . $_SERVER['PHP_SELF'] . "\">\r\n" .
					"\t\t<input type=\"hidden\" name=\"page\" value=\"files\"/>" .
					"\t\t<input type=\"hidden\" name=\"action\" value=\"update_database\"/>"
					. $out;
			if($changes == true)
				$out .= "<input type=\"submit\" class=\"button\" value=\"Ausf&uuml;hren\"/>" .
					"</form>\n\r";
			else
				$out .= 'there_are_no_changes' . 'back';
			return $out;
	 	}
	 	
	 	function _uploadPage() {
	 		global $_SERVER, $_FILES, $admin_lang;
			//$extern_action = GetPostOrGet('action');
			//$extern_file_id = GetPostOrGet('file_id');
			//$extern_sure = GetPostOrGet('sure'); 
			
			$out = "";
			$upload_path = './data/upload/';
			foreach($_FILES as $name => $file) {
				if(startsWith($name, 'uploadfile') && $file['size'] > 0) {
					$nr = substr($name, -1);
					if($nr < 5) {
						$save_path = $upload_path . $file['name'];
						if(file_exists($save_path))
							$save_path = uniqid($upload_path) . $file['name'];
						if($file['size'] > 1600000)
							$file['error'] = 2;
						if($file['error'] == 0) {
							//
							// TODO: dont allow an upload if a file with the same md5 exists
							//
							$file_md5 = md5_file($file['tmp_name']);
						
							$sql = "SELECT file_name
								FROM " . DB_PREFIX . "files
								WHERE file_md5='$file_md5'";
							$md5exists_result = db_result($sql);
							if($md5exists = mysql_fetch_object($md5exists_result))
								$out .= "Die Datei &quot;<strong>" . $file['name'] . "</strong>&quot; ist bereits hochgeladen worden" . (($md5exists->file_name != $file['name']) ? " (sie hat nur einen anderen Namen: &quot;<strong>$md5exists->file_name</strong>&quot;)." : '.');
							else {
								move_uploaded_file($file['tmp_name'], $save_path);
								
								$sql = "INSERT INTO " . DB_PREFIX . "files (file_name, file_type, file_path, file_size, file_md5, file_date)
									VALUES('" . $file['name'] . "', '" . $file['type'] . "', '$save_path', '" . filesize($save_path) . "', '" . md5_file($save_path) . "', " . mktime() . ")";
								db_result($sql);
							}
						}
						else {
							switch ($file['error']) {
								case 1:		$out .= "Die Datei &uuml;berschreitet die vom Server vorgegebene Maximalgr&ouml;�e f�r einen Upload.";
										break;
								case 2:		$out .= "Die Datei &uuml;berschreitet vorgegebene Maximalgr&ouml;�e von 1,5MB f&uuml;r einen Upload.";
										break;
								case 3:		$out .= "Die Datei ist nur teilweise hochgeladen worden.";
										break;
								case 4:		$out .= "Es wurde keine Datei hochgeladen.";
										break;							
								default:	$out .= "Die Datei konnte nicht hochgeladen werden";
										break;
							}
						}
					}
				}
			}
				
			return $out . $this->_homePage();
			
 		}
	 	
	 	/**
	 	 * @access private
	 	 */
	 	function _homePage() {
	 		$out = "<fieldset><legend>Upload</legend>
				<form enctype=\"multipart/form-data\" action=\"admin.php\" method=\"post\">
			
			<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"1600000\" />
			<input type=\"hidden\" name=\"page\" value=\"files\" />
			<input type=\"hidden\" name=\"action\" value=\"upload\" />
			<div class=\"row\"><label><strong>{$this->_AdminLang['file']} 1:</strong></label><input name=\"uploadfile0\" type=\"file\" /></div>
			<div class=\"row\"><label><strong>{$this->_AdminLang['file']} 2:</strong></label><input name=\"uploadfile1\" type=\"file\" /></div>
			<div class=\"row\"><label><strong>{$this->_AdminLang['file']} 3:</strong></label><input name=\"uploadfile2\" type=\"file\" /></div>
			<div class=\"row\"><input type=\"submit\" class=\"button\" value=\"Hochladen\"/></div>
			
		</form>";
		$out .= "
		<div class=\"row\"><a href=\"admin.php?page=files&amp;action=check_new_files\"  class=\"button\">Auf Ver&auml;nderungen &uuml;berpr&uuml;fen</a></div>
		</fieldset>
		<table class=\"text_table full_width\">
			<thead>
				<tr>
					<th>{$this->_AdminLang['filename']}</th>
					<th>{$this->_AdminLang['filesize']}</th>
					<th>{$this->_AdminLang['uploaded_on']}</th>
					<th>{$this->_AdminLang['filetype']}</th>
					<th>{$this->_AdminLang['actions']}</th>
				</tr>
			</thead>";
		$sql = "SELECT *
			FROM " . DB_PREFIX . "files
			ORDER BY file_name ASC";
		$files_result = db_result($sql);
		$completesize = 0;
		while($file = mysql_fetch_object($files_result)) {
			$out .= "<tr>
				<td>" . utf8_encode($file->file_name) . "</td>
				<td>" . kbormb($file->file_size) . "</td>
				<td>" . date('d.m.Y H:i:s', $file->file_date) . "</td>
				<td>$file->file_type</td>
				<td>
					<a href=\"download.php?file_id=$file->file_id\" ><img src=\"./img/download.png\" height=\"16\" width=\"16\" alt=\"" . $this->_AdminLang['download'] . "\" title=\"" . $this->_AdminLang['download'] . "\"/></a>
					<a href=\"admin.php?page=files&amp;action=delete&amp;file_id=$file->file_id\" ><img src=\"./img/del.png\" height=\"16\" width=\"16\" alt=\"" . $this->_AdminLang['delete'] . "\" title=\"" . $this->_AdminLang['delete'] . "\"/></a>
				</td>
			</tr>\r\n";
			$completesize += $file->file_size;
		}
		$out .= "</table>";
		$out .= $this->_AdminLang['altogether'] . kbormb($completesize);

		return $out;
	 	}
	}
?>