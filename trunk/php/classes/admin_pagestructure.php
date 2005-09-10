<?php
/*****************************************************************************
 *
 *  file		: admin_pagestructure.php
 *  created		: 2005-09-04
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
 
 	class Admin_PageStructure {
 		
		 function GetPage($action = '') {
		 	global $admin_lang;
			$out = "<h3>" . $admin_lang['pagestructure'] . "</h3><hr />\r\n";
		 	$action = strtolower($action);
		 	switch ($action) {
		 		case 'delete':		$out .= $this->_deletePage();
		 					break;
		 		case 'info':		$out .= $this->_infoPage();
		 					break;
		 		case 'new':		$out .= $this->_newPage();
		 					break;
		 		case 'add_new':		$out .= $this->_addPage();
		 					break;
		 		case 'edit':		$out .= $this->_editPage();
							break;
				case 'save':		$out .= $this->_savePage();
							break;
		 		default:		$out .= $this->_homePage();
		 	}
			return $out;
		 }
		 
		 function _homePage() {
		 	global $admin_lang, $_SERVER;
			$out = "<a href=\"" . $_SERVER['PHP_SELF'] . "?page=pagestructure&amp;action=new\">neue Seite</a>";;
		 	$out .= $this->_showStructure(0);

			return $out;
		 }
		 
		 function _newPage() {
		 	global $_SERVER, $admin_lang;
			$out = '';
		 	/*$out .= "<select>\r\n";
		 	$out .= $this->_structurePullDown(0);
		 	$out .= "</select>\r\n";*/
		 	$out .= "\t\t\t<form method=\"post\" action=\"" . $_SERVER['PHP_SELF'] . "\">
				<fieldset>
				<legend>Neue Seite</legend>
				<input type=\"hidden\" name=\"page\" value=\"pagestructure\" />
				<input type=\"hidden\" name=\"action\" value=\"add_new\" />
				<table>
					<tr>
						<td>
							Name/Kürzel:
							<span class=\"info\">Mit diesem Kürzel wird auf die Seite zugegriffen und dient es zur eindeutigen Identifizierung der Seite.</span>
						</td>
						<td>
							<input type=\"text\" name=\"page_name\" maxlength=\"20\" />
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
							Seiten-Typ:
							<span class=\"info\">TODO</span>
						</td>
						<td>
							<select name=\"page_type\">
								<option value=\"text\">Text</option>
								<option value=\"gallery\">" . $admin_lang['gallery'] ."</option>
							</select>
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
							<select name=\"page_access\">
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
							<select name=\"page_parent_id\">
								<option value=\"0\">Keiner</option>\r\n";
		 	$out .= $this->_structurePullDown(0);
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
		 	return $out;
		 }
		 
		 function _structurePullDown($topnode = 0, $deep = 0, $topnumber = '') {
		 	$out = '';
			$sql = "SELECT *
		 		FROM " . DB_PREFIX . "pages
		 		WHERE page_parent_id=$topnode";
		 	// TODO: ORDER BY page_sortid
		 	$pages_result = db_result($sql);
		 	if(mysql_num_rows($pages_result) != 0) {
		 		$number = 1;
		 		while($page = mysql_fetch_object($pages_result)) {
		 		$out .= "<option style=\"padding-left:" . ($deep * 1.5) . "em;\" value=\"$page->page_id\">$topnumber$number. $page->page_title ($page->page_name)</option>\r\n";
		 		$out .= $this->_structurePullDown($page->page_id, $deep + 1, $topnumber . $number. "." );
		 		$number++;
		 		}
		 	}
		 	return $out;
		 }
		 
		 function _showStructure($topnode = 0) {
		 	global $admin_lang, $_SERVER;
			
			$out = '';
			$sql = "SELECT *
		 		FROM " . DB_PREFIX . "pages
		 		WHERE page_parent_id=$topnode";
	 		// TODO: ORDER BY page_sortid
		 	$pages_result = db_result($sql);
		 	if(mysql_num_rows($pages_result) != 0) {
		 		$out .= "\r\n<ol>\r\n";
		 		while($page = mysql_fetch_object($pages_result)) {
		 			$out .= "<li class=\"page_type_$page->page_type\">";
		 			$out .= "<strong>$page->page_title</strong> ($page->page_name)";
		 			$out .= "[$page->page_lang]";
		 			$out .= " <a href=\"index.php?page=$page->page_name\">[Anschauen]</a>"; //an eye as picture
		 			$out .= " <a href=\"" . $_SERVER['PHP_SELF'] . "?page=pagestructure&amp;action=info&amp;page_id=$page->page_id\">[Infos]</a>";	//a paper-page as picture
		 			$out .= " <a href=\"" . $_SERVER['PHP_SELF'] . "?page=pagestructure&amp;action=edit&amp;page_id=$page->page_id\"><img src=\"./img/edit.png\" height=\"16\" width=\"16\" alt=\"" . $admin_lang['edit'] . "\" title=\"" . $admin_lang['edit'] . "\"/></a>";
		 			$out .= " <a href=\"" . $_SERVER['PHP_SELF'] . "?page=pagestructure&amp;action=delete&amp;page_id=$page->page_id\"><img src=\"./img/del.jpg\" height=\"16\" width=\"16\" alt=\"" . $admin_lang['delete'] . "\" title=\"" . $admin_lang['delete'] . "\"/></a>";
					$out .= $this->_showStructure($page->page_id);
		 			$out .= "</li>\r\n";
				}
				$out .= "\r\n</ol>\r\n";
			}
			return $out;
		}
		
		function _editPage() {
			global $extern_page_id;
			
			$out = '';
			$sql = "SELECT page_id, page_type
				FROM " . DB_PREFIX . "pages
				WHERE page_id = $extern_page_id";
			$page_result = db_result($sql);
			if($page = mysql_fetch_object($page_result)) {
				$edit = null;
				switch($page->page_type) {
					case 'text':		include('classes/edit_text_page.php');
								$edit = new Edit_Text_Page();
								break;				
					default:		$out .= "Der Seitentyp <strong>$page->page_type</strong> lässt sich noch nicht bearbeiten.";
								break;
				}
				if($edit !== null)
					$out .= $edit->Edit($page->page_id);
				return $out;
			}
		}
		
		function _savePage() {
			global $extern_page_id;
			
			$out = '';
			$sql = "SELECT page_id, page_type
				FROM " . DB_PREFIX . "pages
				WHERE page_id = $extern_page_id";
			$page_result = db_result($sql);
			if($page = mysql_fetch_object($page_result)) {
				$edit = null;
				switch($page->page_type) {
					case 'text':		include('classes/edit_text_page.php');
								$edit = new Edit_Text_Page();
								break;				
					default:		$out .= "Der Seitentyp <strong>$page->page_type</strong> lässt sich noch nicht bearbeiten.";
								break;
				}
				if($edit !== null)
					$out .= $edit->Save($page->page_id);
				return $out;
			}
		}
		
		function _addPage() {
			global $extern_page_type, $user, $extern_page_access, $extern_page_name, $extern_page_title, $extern_page_parent_id, $extern_page_lang;

			$edit = null;
			$id = -1;
			// create new page_type-data-page
			switch($extern_page_type) {
				case 'text':		include('classes/edit_text_page.php');
							$edit = new Edit_Text_Page();
							break;
				default:		$out .= "Der Seitentyp <strong>$extern_page_type</strong> lässt sich noch nicht bearbeiten.";
							break;
			}
			if($edit !== null) {
				$a_access = array('public', 'private', 'hidden');
				if(!in_array($extern_page_access, $a_access))
					$extern_page_access = $a_access[0];
				$extern_page_name = strtolower($extern_page_name);
				$extern_page_name = str_replace(' ', '_', $extern_page_name);
				$sql = "INSERT INTO " . DB_PREFIX . "pages (page_lang, page_access, page_name, page_title, page_parent_id, page_creator, page_type, page_date)
					VALUES('$extern_page_lang', '$extern_page_access', '$extern_page_name', '$extern_page_title', $extern_page_parent_id, $user->Id, '$extern_page_type', " . mktime() . ")";
				db_result($sql);
				$lastid =  mysql_insert_id();
				$edit->NewPage($lastid);
			}


			
				
		}
		
		function _pagePath($pageid=0) {
			$out = '';
			$sql = "SELECT *
			FROM " . DB_PREFIX . "pages
			WHERE page_id=$pageid";
			$page_result = db_result($sql);
			while($page = mysql_fetch_object($page_result)) {
				$out = "<a href=\"" . $_SERVER['PHP_SELF'] . "?page=pagestructure&amp;action=info&amp;page_id=$page->page_id\">$page->page_name</a>" . $out;
				if($page->page_parent_id != 0)
				$out = '/' . $out;
				$sql = "SELECT *
					FROM " . DB_PREFIX . "pages
					WHERE page_id=$page->page_parent_id";
				$page_result = db_result($sql);
			}
			return $out;
		}
		
		function _infoPage() {
			global $extern_page_id, $admin_lang;
			
			$out = '';
			$sql = "SELECT *
				FROM " . DB_PREFIX . "pages
				WHERE page_id=$extern_page_id";
			$page_result = db_result($sql);
			if($page = mysql_fetch_object($page_result)) {
				$out .= "<table>
				<tr>
				<td>Titel:</td><td>$page->page_title</td>
				</tr>
				<tr>
				<td>Name:</td><td>$page->page_name</td>
				</tr>
				<tr>
				<td>Typ:</td><td>$page->page_type</td>
				</tr>
				<tr>
				<td>Sprache:</td><td>" . $admin_lang[$page->page_lang] . "</td>
				</tr>
				<tr>
				<td>Pfad:</td><td>" . $this->_pagePath($page->page_id) . "</td>
				</tr>
				<tr>
				<td>Bearbeitet von:</td><td>" . getUserById($page->page_creator) . "</td>
				</tr>
				<tr>
				<td>Bearbeitet am:</td><td>" . date("d.m.Y H:i:s",$page->page_date) . "</td>
				</tr>
				</table>";
				
			}
			
			return $out;
		}
		
 	}