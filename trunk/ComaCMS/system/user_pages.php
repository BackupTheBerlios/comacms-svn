<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005-2006 The ComaCMS-Team
 */
 #----------------------------------------------------------------------#
 # file			: user_pages.php				#
 # created		: 2005-07-16					#
 # copyright		: (C) 2005-2006 The ComaCMS-Team		#
 # email		: comacms@williblau.de				#
 #----------------------------------------------------------------------#
 # This program is free software; you can redistribute it and/or modify	#
 # it under the terms of the GNU General Public License as published by	#
 # the Free Software Foundation; either version 2 of the License, or	#
 # (at your option) any later version.					#
 #----------------------------------------------------------------------#


	function page_files() {
		global $_SERVER, $_FILES, $admin_lang;
		$extern_action = GetPostOrGet('action');
		$extern_file_id = GetPostOrGet('file_id');
		$extern_sure = GetPostOrGet('sure'); 
		
		$out = "<h3>Files</h3><hr />";
		$upload_path = './data/upload/';
		if($extern_action == 'upload') {
			foreach($_FILES as $name => $file) {
				if(startsWith($name, 'uploadfile')) {
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
		}
		elseif($extern_action == 'check_new') {
			//echo 'test';
			$out .= "Hier werden alle Dateien angezeigt, die nicht &uuml;ber das Admin-Interface Ver&auml;ndert worden sind, um diese Ver&auml;nderungen in die Datenbank zu &uuml;bernehmen haken sie alle die Dateien an, die sie aktualisieren m&ouml;chten.<br /><br />\r\n" .
					"\t<form method=\"post\" action=\"" . $_SERVER['PHP_SELF'] . "\">\r\n" .
					"\t\t<input type=\"hidden\" name=\"page\" value=\"files\"/>" .
					"\t\t<input type=\"hidden\" name=\"action\" value=\"add_new\"/>";
			$sql = "SELECT *
				FROM " . DB_PREFIX . "files
				ORDER BY file_date DESC";
			$files_result = db_result($sql);
			$md5s = array();
			$newmd5s = array();
			while($file = mysql_fetch_object($files_result)) {
				if(file_exists($file->file_path))
					$md5s[$file->file_path] = $file->file_md5;
				else
					$out .= "<input type=\"checkbox\" name=\"change[]\" value=\"" . utf8_encode($file->file_path) ."\" checked=\"checked\" /><strong>Aus der Datenbank entfernen</strong> " . utf8_encode($file->file_path) ."<br />\r\n";
			}
			$files = dir($upload_path);

			while($entry = $files->read()) {
  				if(is_file($upload_path.$entry))
  					if(!in_array(md5_file($upload_path . $entry),$md5s))
  						$out .= "<input type=\"checkbox\" name=\"change[]\" value=\"" . generateUrl(utf8_encode($upload_path . $entry)) ."\" checked=\"checked\" /><strong>Zur Datenbank hinzuf&uum;gen</strong> " . utf8_encode($entry) . "<br />\r\n";
  						
  				//echo $upload_path.$entry . '-' . md5_file($upload_path.$entry) . "<br>";
			}
			$files->close();
			$out .= "<input type=\"submit\" class=\"button\" value=\"Ausf&uuml;hren\"/>" .
				"</form>\n\r";
			return $out;
			//print_r($md5s);
		}
		elseif($extern_action == 'delete') {
			if($extern_file_id != '') {
				$sql = "SELECT *
					FROM " . DB_PREFIX . "files
					WHERE file_id = $extern_file_id";
				$file_result = db_result($sql);
				$file = mysql_fetch_object($file_result);
				if($extern_sure) {
					$sql = "DELETE FROM " . DB_PREFIX . "files
					WHERE file_id = $extern_file_id";
					db_result($sql);
					unlink($file->file_path);
					//delete
					//unlink()
				}
				else {
					$out .= "Sind sie sicher, dass sie die Datei &quot;$file->file_name&quot; unwiederruflich l&ouml;schen wollen?<br />
					Die Datei wurde am " . date('d.m.Y', $file->file_date) . " um " . date('H:i:s', $file->file_date) ." hochgeladen.<br />
					<a href=\"" . $_SERVER['PHP_SELF'] . "?page=files&amp;action=delete&amp;file_id=" . $extern_file_id . "&amp;sure=1\" title=\"Wirklich L&ouml;schen\"  class=\"button\">" . $admin_lang['yes'] . "</a>
					<a href=\"" . $_SERVER['PHP_SELF'] . "?page=files\" title=\"Nicht L�schen\" class=\"button\">" . $admin_lang['no'] . "</a>";
					return $out;
				}

			}
		}
		elseif($extern_action == 'add_new') {
			$changes = GetPostOrGet('change');
			if(count($changes) > 0) {
				foreach($changes as $change) {
					
					
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
					}
					elseif(file_exists($change)) {
						$sql = "INSERT INTO " . DB_PREFIX . "files (file_name, file_type, file_path, file_size, file_md5, file_date)
							VALUES('" . basename($change) . "', '" . GetMimeContentType($change) . "', '$change', '" . filesize($change) . "', '" . md5_file($change) . "', " . mktime() . ")";
						db_result($sql);
					}
				}
			}
		}
		$out .= "<form enctype=\"multipart/form-data\" action=\"" . $_SERVER['PHP_SELF'] . "\" method=\"post\">
			<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"1600000\" />
			<input type=\"hidden\" name=\"page\" value=\"files\" />
			<input type=\"hidden\" name=\"action\" value=\"upload\" />
			<input name=\"uploadfile0\" type=\"file\" />
			<input type=\"submit\" class=\"button\" value=\"Hochladen\"/>
		</form>";
		$out .= "
		<a href=\"admin.php?page=files&amp;action=check_new\"  class=\"button\">Auf Ver&auml;nderungen &uuml;berpr&uuml;fen</a><br /><br />
		<table class=\"text_table\">
		<tr><th>id</th><th>Name</th><th>Gr&ouml;&szlig;e</th><th>Hochgeladen am</th><th>Typ</th><th>Aktionen</th></tr>";
		$sql = "SELECT *
			FROM " . DB_PREFIX . "files
			ORDER BY file_name ASC";
		$files_result = db_result($sql);
		$completesize = 0;
		while($file = mysql_fetch_object($files_result)) {
			$out .= "<tr>
				<td>#$file->file_id</td>
				<td>" . utf8_encode($file->file_name) . "</td>
				<td>" . kbormb($file->file_size) . "</td>
				<td>" . date('d.m.Y H:i:s', $file->file_date) . "</td>
				<td>$file->file_type</td>
				<td><a href=\"" . $_SERVER['PHP_SELF'] . "?page=files&amp;action=delete&amp;file_id=$file->file_id\" ><img src=\"./img/del.png\" height=\"16\" width=\"16\" alt=\"" . $admin_lang['delete'] . "\" title=\"" . $admin_lang['delete'] . "\"/></a>
			</tr>\r\n";
			$completesize += $file->file_size;
		}
		$out .= "</table>";
		$out .= "Insgesammt " . kbormb($completesize) . ".";
		return $out;
	}
?>
