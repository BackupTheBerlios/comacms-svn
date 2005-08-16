<?php
/*****************************************************************************
 *
 *  file		: user_pages.php
 *  created		: 2005-07-16
 *  copyright		: (C) 2005 The ComaCMS-Team
 *  email		: comacms@williblau.de
 *
 *****************************************************************************/

/*****************************************************************************
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *****************************************************************************/
	function page_pageeditor() {
		global $admin_lang, $actual_user_lang, $_SERVER, $actual_user_id, $extern_action, $extern_page_name, $extern_page_title, $extern_page_lang, $extern_page_parentid, $extern_page_edit, $extern_page_text, $extern_page_visible, $extern_show_hidden_pages, $extern_show_deleted_pages, $extern_sure;
		
		
		$out = "\t\t\t<h3>" . $admin_lang['pageeditor'] . "</h3><hr />\r\n";
				
		//$page_name = $extern_page_name;	
		//$page_title = $extern_page_title;
		//$page_lang = $extern_page_lang;
		//$page_parentid = '';
		
		if(!isset($extern_page_name))
			$extern_page_name = '';
			
		if(!isset($extern_page_title))
			$extern_page_title = '';
		
		if(!isset($extern_page_lang))
			$extern_page_lang = '';
			
		if(!isset($extern_page_parentid))
			$extern_page_parentid = '';
		
		if(!isset($extern_action))
			$extern_action = '';
			
		
		if($extern_action == 'add_new') {
			
			if(!isset($extern_page_edit))
				$extern_page_edit = '';
			if(!isset($extern_page_visible))
				$extern_page_visible = '';
						
			if($extern_page_name != '' && $extern_page_title != '' && $extern_page_lang != '') {
				$a_visible = array('public', 'private', 'hidden');
				if(!in_array($extern_page_visible, $a_visible))
					$extern_page_visible = $a_visible[0];
				$extern_page_name = strtolower($extern_page_name);
				$extern_page_name = str_replace(' ', '_', $extern_page_name);
				$sql = "SELECT page_name
					FROM " . DB_PREFIX . "pages_content
					WHERE page_name='$extern_page_name'";
				$page_result = db_result($sql);
				if(!$page_data = mysql_fetch_object($page_result)) {
					$sql = "INSERT INTO " . DB_PREFIX . "pages_content (page_name, page_type, page_title, page_text, page_lang, page_html, page_parent_id, page_creator, page_created, page_visible)
					VALUES ('$extern_page_name', 'text', '$extern_page_title', '', '$extern_page_lang', '', '$extern_page_parentid', '$actual_user_id', '" . mktime() . "','$extern_page_visible')";
					db_result($sql);
				}
				if($extern_page_edit == 'on')
					header('Location: ' . $_SERVER['PHP_SELF'] . '?page=pageeditor&action=edit&page_name=' . $extern_page_name);
				else
					header('Location: ' . $_SERVER['PHP_SELF'] . '?page=pageeditor');
			}
		}
		elseif($extern_action == 'update') {
			if(!isset($extern_page_text))
				$extern_page_text = '';
			
			if($extern_page_name != '' && $extern_page_title != '' && $extern_page_text != '') {
				$html = convertToPreHtml($extern_page_text);
				$sql = "SELECT *
					FROM " . DB_PREFIX . "pages_content
					WHERE page_name='$extern_page_name'";
				$old_result = db_result($sql);
				if($old = mysql_fetch_object($old_result)) {
					if(($old->page_text != $extern_page_text) || ($old->page_title != $extern_page_title)) {
						if($old->page_text != '') {
							$sql = "INSERT INTO " . DB_PREFIX . "sitedata_history (name, title, text, lang, type, creator, date)
							VALUES ('$extern_page_name', '$old->page_title', '$old->page_text', '$old->page_lang', 'text',$old->page_creator, '$old->page_created')";
							db_result($sql);
						}
						$sql = "UPDATE " . DB_PREFIX . "pages_content
							SET page_title= '$extern_page_title', page_text='$extern_page_text', page_html='$html', page_creator='$actual_user_id', page_created='" . mktime() . "'
							WHERE page_name='$extern_page_name'";
						db_result($sql);
						$out = 'Der Eintrag sollte gespeichert sein';
					}
				}
			}
		}
		elseif($extern_action == 'new') {
			
			$out .= "\t\t\t<form method=\"post\" action=\"" . $_SERVER['PHP_SELF'] . "\">
				<fieldset>
				<legend>Neue Seite</legend>
				<input type=\"hidden\" name=\"page\" value=\"pageeditor\" />
				<input type=\"hidden\" name=\"action\" value=\"add_new\" />
				<table>
					<tr>
						<td>
							Name/Kürzel:
							<span class=\"info\">Mit diesem Kürzel wird auf die Seite zugegriffen und dient es zur eindeutigen Identifizierung der Seite.</span>
						</td>
						<td>
							<input type=\"text\" name=\"page_name\" value=\"" . $extern_page_name . "\" maxlength=\"20\" />
						</td>
					</tr>
					<tr>
						<td>
							Titel:
							<span class=\"info\">Der Titel wird später in der Titelleiste des Browsers angezeigt.</span>
						</td>
						<td>
							<input type=\"text\" name=\"page_title\" maxlength=\"100\" />
						</td>
					</tr>
					<tr>
						<td>
							" . $admin_lang['language'] . ":
							<span class=\"info\">Der Text soll in der gewählten Sprache geschrieben werden.</span>
						</td>
						<td>
							<select name=\"page_lang\">
								<option value=\"de\">Deutsch</option>
								<option value=\"en\">Englisch</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>
							Zugang:
							<span class=\"info\">Wer soll sich die Seite später anschauen können?<br />
							Jeder (öffentlich), nur ausgewählte Benutzer (privat) oder soll die Seite nur erstellt werden um sie später zu veröffentlichen (versteckt)?</span>
						</td>
						<td>
							<select name=\"page_visible\">
								<option value=\"public\">Öffentlich</option>
								<option value=\"private\">Privat</option>
								<option value=\"hidden\">Versteckt</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>
							Unterseite von:
							<span class=\"info\">TODO</span>	
						</td>
						<td>
							<select name=\"page_parentid\">
								<option value=\"0\">Keiner</option>\r\n";
			$sql = "SELECT page_name, page_title, page_id
				FROM " . DB_PREFIX . "pages_content WHERE page_visible!='deleted'
				ORDER BY page_name ASC";
			$pages = db_result($sql);
			while($pageinfo = mysql_fetch_object($pages))
				$out .= "\t\t\t\t\t\t<option value=\"" . $pageinfo->page_id . "\">" . $pageinfo->page_title . "(" . $pageinfo->page_name . ")</option>\r\n";
			$out .= "\t\t\t\t\t\t\t</select>
						</td>
					</tr>
					<tr>
						<td>
							Bearbeiten?
							<span class=\"info\">Soll die Seite nach dem Erstellen bearbeitet werden oder soll wieder auf die Übersichtseite zurückgekehrt werden?</span>
						</td>
						<td><input type=\"checkbox\" name=\"page_edit\" checked=\"true\"/></td>
					</tr>
					<tr>
						<td colspan=\"2\">
							<input type=\"reset\" class=\"button\" value=\"Zurücksetzen\" />&nbsp;
							<input type=\"submit\" class=\"button\" value=\"Erstellen\" />
						</td>
					</tr>
				</table>
			</fieldset>
			</form>";
	
		}
		elseif($extern_action == 'delete') {
			
			if(!isset($extern_sure))
				$extern_sure = '';
			$sql = "SELECT *
				FROM " . DB_PREFIX . "pages_content
				WHERE page_name='$extern_page_name'";
			$exists_result = db_result($sql);
			$exists = null;
			if(!$exists = mysql_fetch_object($exists_result)) {
				$out .= "\t\t\tDer Eintag existiert garnicht, das löschen kann man sich also sparen<br />
			<a href=\"" . $PHP_SELF . "?page=pageeditor\">".$admin_lang['ok']."</a>";
				return $out;
			}
			if($extern_sure == 1) {
				$sql = "INSERT INTO " . DB_PREFIX . "sitedata_history (name, title, text, lang, type, creator, date)
					VALUES ('$extern_page_name', '$exists->page_title', '$exists->page_text', '$exists->page_lang', 'text', $exists->page_creator, '$exists->page_created')";
				db_result($sql);
				$sql = "UPDATE " . DB_PREFIX . "pages_content
					SET  page_visible='deleted', page_text='', page_html='', page_creator='$actual_user_id', page_created='" . mktime() . "'
					WHERE page_name='$extern_page_name'";
				db_result($sql);
			}
			else {
				$out .= "\t\t\tMöchten sie die Seite &quot;" . $exists->page_title . " (" . $exists->page_name . ")&quot; wirklich löschen?<br />
				<a href=\"" . $_SERVER['PHP_SELF'] . "?page=pageeditor&amp;action=delete&amp;sure=1&amp;page_name=" . $extern_page_name . "\">" . $admin_lang['yes'] . "</a> <a href=\"" . $_SERVER['PHP_SELF'] . "?page=pageeditor\">" . $admin_lang['no'] . "</a>";
			}
			
		}
		elseif($extern_action == 'tree') {
			$show_deleted = false;
			$show_hidden = false;
			
			if(isset($extern_show_hidden_pages))
				$show_hidden = $extern_show_hidden_pages == 'on';
			if(isset($extern_show_deleted_pages))
				$show_deleted = $extern_show_deleted_pages == 'on';
				
			if($extern_page_lang == '')
				$extern_page_lang = $actual_user_lang;
			$out .= "\t\t\t<form action=\"" . $_SERVER['PHP_SELF'] . "\" method=\"get\">
				<input type=\"hidden\" name=\"page\" value=\"pageeditor\" />
				<input type=\"hidden\" name=\"action\" value=\"tree\" />
				<select name=\"page_lang\">
					<option value=\"de\"";if($extern_page_lang == "de") $out .= " selected=\"selected\""; $out .=">".$admin_lang['de']."</option>
					<option value=\"en\"";if($extern_page_lang == "en") $out .= " selected=\"selected\""; $out .=">".$admin_lang['en']."</option>
				</select><br />
				<input type=\"checkbox\" name=\"show_hidden_pages\"";if($show_hidden) $out .= " checked=\"true\""; $out .= "/>" . $admin_lang['show hidden'] ."<br />
				<input type=\"checkbox\" name=\"show_deleted_pages\"";if($show_deleted) $out .= " checked=\"true\""; $out .= "/>" . $admin_lang['show deleted'] ."<br />
				<input type=\"submit\" class=\"button\" value=\"" . $admin_lang['show'] . "\" />
			</form>";
			$out .= generatesitestree(0, "\t\t\t", $extern_page_lang, $show_deleted, $show_hidden);
		}
		elseif($extern_action == 'edit') {
			$sql = "SELECT *
			FROM " . DB_PREFIX . "pages_content
			WHERE page_name='$extern_page_name'";
			$page_result = db_result($sql);
			if($page_data = mysql_fetch_object($page_result)){
				$out .= "\t\t\t<form action=\"" . $_SERVER['PHP_SELF'] . "\" method=\"post\">
				<input type=\"hidden\" name=\"page\" value=\"pageeditor\" />
				<input type=\"hidden\" name=\"action\" value=\"update\" />
				<input type=\"hidden\" name=\"page_name\" value=\"" . $page_data->page_name . "\" />
				<input type=\"text\" name=\"page_title\" value=\"" . $page_data->page_title . "\" /><br />
				<script type=\"text/javascript\" language=\"JavaScript\" src=\"system/functions.js\"></script>
				<script type=\"text/javascript\" language=\"javascript\">
					writeButton(\"img/button_fett.png\",\"Formatiert Text Fett\",\"**\",\"**\",\"Fetter Text\",\"f\");
					writeButton(\"img/button_kursiv.png\",\"Formatiert Text kursiv\",\"//\",\"//\",\"Kursiver Text\",\"k\");
					writeButton(\"img/button_unterstrichen.png\",\"Unterstreicht den Text\",\"__\",\"__\",\"Unterstrichener Text\",\"u\");
					writeButton(\"img/button_ueberschrift.png\",\"Markiert den Text als Überschrift\",\"=== \",\" ===\",\"Überschrift\",\"h\");
				</script><br />
				<textarea id=\"editor\" class=\"edit\" name=\"page_text\">".$page_data->page_text."</textarea>
				<input type=\"reset\" value=\"Zurücksetzten\" />
				<input type=\"submit\" value=\"Speichern\" />
			</form>";
			}
			else
				header("Location: " . $_SERVER['PHP_SELF'] . "?page=pageeditor&action=new&page_name=".$extern_page_name);
		}
		elseif($extern_action == 'info') {
			if($extern_page_name == '')
				header("Location: " . $_SERVER['PHP_SELF'] . "?page=pageeditor");
			$sql = "SELECT *
				FROM " . DB_PREFIX . "pages_content
				WHERE page_name='$extern_page_name'";
			$actual_result = db_result($sql);
			$sql = "SELECT *
				FROM " . DB_PREFIX . "sitedata_history
				WHERE name='$extern_page_name'
				ORDER BY id DESC";
			$olds_result = db_result($sql);
			$actual = mysql_fetch_object($actual_result);
			$out .= "\t\t\tName: " . $actual->page_name . "<br />
			Titel: " . $actual->page_title . "<br />
			<fieldset><legend>Text</legend>".$actual->page_html."</fieldset>
			Letzte Veränderung von: ".getUserById($actual->page_creator)."<br />
			insgesamt " . mysql_num_rows($olds_result) . " Veränderungen<br />";
		}
		else { // home site etc.
			$out .= "<a href=\"" . $_SERVER['PHP_SELF'] . "?page=pageeditor&amp;action=new\">Neue Seite</a><br />\r\n";
			$out .= "<a href=\"" . $_SERVER['PHP_SELF'] . "?page=pageeditor&amp;action=tree\">Übersicht</a><br />\r\n";
			$out .= "<form action=\"" . $_SERVER['PHP_SELF'] . "\" method=\"get\">
		<input type=\"hidden\" name=\"page\" value=\"pageeditor\" />
		<input type=\"hidden\" name=\"action\" value=\"edit\" />
		<select name=\"page_name\">";
			$sql = "SELECT page_name, page_title, page_id
				FROM " . DB_PREFIX . "pages_content
				WHERE page_visible!='deleted'
				ORDER BY page_name ASC";
			$sites = db_result($sql);
			while($siteinfo = mysql_fetch_object($sites))
				$out .= "\t\t\t\t\t\t<option value=\"".$siteinfo->page_name."\">" . $siteinfo->page_title . "(".$siteinfo->page_name.")</option>\r\n";
			$out .= "\t\t\t\t\t\t\t</select>
		<input type=\"submit\" class=\"button\" value=\"Öffnen\" /> 
		</form>";
		}
		
		return $out;
	}

	function page_logout() {
		global $actual_user_online_id;
		setcookie('CMS_user_cookie', $actual_user_online_id . '||', time() + 14400);
		header('Location: index.php');
	}
	
	function page_files() {
		global $_SERVER,$_FILES,$extern_action;
	
		$out = "Files<br />";
		$out .= "<form enctype=\"multipart/form-data\" action=\"" . $_SERVER['PHP_SELF'] . "\" method=\"post\">
			<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"1600000\" />
			<input type=\"hidden\" name=\"page\" value=\"files\" />
			<input type=\"hidden\" name=\"action\" value=\"upload\" />
			<input name=\"uploadfile0\" type=\"file\" />
			<input type=\"submit\" value=\"Hochladen\"/>
		</form>";
		$upload_path = './data/upload/';
		if($extern_action == 'upload') {
			foreach($_FILES as $name => $file) {
				if(startsWith($name, 'uploadfile')) {
					$nr = substr($name, -1);
					if($nr < 5) {
						$save_path = $upload_path . $file['name'];
						if(file_exists($save_path))
							$save_path = $upload_path . uniqid() . $file['name'];
						if($file['error'] == 0) {
							//
							// TODO:dont allow an upload if a file with the same md5 exists
							//
							$file_md5 = md5_file($file['tmp_name']);
							
							$sql = "SELECT file_name
								FROM " . DB_PREFIX . "files
								WHERE file_md5='$file_md5'";
							$md5exists_result = db_result($sql);
							if($md5exists = mysql_fetch_object($md5exists_result))
								$out .= "Die Datei <strong>&quot;" . $file['name'] . "&quot;</strong> ist bereits hochgeladen worden (&quot;$md5exists->file_name&quot;)";
							else {
								move_uploaded_file($file['tmp_name'], $save_path);
								
								$sql = "INSERT INTO " . DB_PREFIX . "files (file_name, file_type, file_path, file_size, file_md5, file_date)
									VALUES('" . $file['name'] . "', '" . $file['type'] . "', '$save_path', '" . filesize($save_path) . "', '" . md5_file($save_path) . "', " . mktime() . ")";
								db_result($sql);
							}
						}
						else {
							$out .= "Die Datei konnte nicht hochgeladen werden";
						}
					}
				}
			}
		}
		
		$out .= "
		[Auf Veränderungen überprüfen]
		<table>
		<tr><td>id</td><td>Name</td><td>Größe</td><td>Hochgeladen am</td><td>Typ</td><td>Aktionen</td></tr>";
		$sql = "SELECT *
			FROM " . DB_PREFIX . "files
			ORDER BY file_date DESC";
		$files_result = db_result($sql);
		while($file = mysql_fetch_object($files_result)) {
			$out .= "<tr>
				<td>#$file->file_id</td>
				<td>$file->file_name</td>
				<td>" . kbormb($file->file_size) . "</td>
				<td>" . date('d.m.Y H:i:s',$file->file_date) . "</td>
				<td>$file->file_type</td>
				<td>[Löschen]</td>
			</tr>\r\n";
			//$out .= "$file->file_name<br />";
		}
		$out .= "</table>";
		return $out;
	}
?>