<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005 The ComaCMS-Team
 */
 #----------------------------------------------------------------------#
 # file			: admin_pagestructure.php			#
 # created		: 2005-09-04					#
 # copyright		: (C) 2005 The ComaCMS-Team			#
 # email		: comacms@williblau.de				#
 #----------------------------------------------------------------------#
 # This program is free software; you can redistribute it and/or modify	#
 # it under the terms of the GNU General Public License as published by	#
 # the Free Software Foundation; either version 2 of the License, or	#
 # (at your option) any later version.					#
 #----------------------------------------------------------------------#
	
	/**
	 * @package ComaCMS 
	 */
 	class Admin_PageStructure {
 		
 		/**
 		 * @var array
 		 */
 		var $MenuPageIDs;
 		
 		/**
 		 * @return string
 		 * @param action string
 		 */
		 function GetPage($action = '') {
		 	global $admin_lang;
		 	
			$out = "\t\t\t<h3>" . $admin_lang['pagestructure'] . "</h3><hr />\r\n";
		 	$action = strtolower($action);
		 	switch ($action) {
		 		case 'delete':		$out .= $this->_deletePage();
		 					break;
		 		case 'info':		$out .= $this->_infoPage();
		 					break;
		 		case 'new_page':	$out .= $this->_newPage();
		 					break;
		 		case 'new_link':	$out .= $this->_newLink();
		 					break;
		 		case 'add_new':		$out .= $this->_addPage();
		 					break;
		 		case 'edit':		$out .= $this->_editPage();
							break;
				case 'save':		$out .= $this->_savePage();
							break;
				case 'generate_menu':	$out .= $this->_generate_menu();
							break;
		 		default:		$out .= $this->_homePage();
		 	}
			return $out;
		 }
		 
		 /**
		  * @return string
		  */
		 function _generate_menu() {
		 	global $admin_lang;
		 	//$out = '';
		 	$pages = GetPostOrGet('pagestructure_pages');
		 	$menu = GetPostOrGet('pagestructure_savemenu');
		 	
		 	if($menu == $admin_lang['generate_mainmenu']) {
		 		$menu_id = "1";
		 		
		 		$sql = "DELETE " .
		 			"FROM " . DB_PREFIX . "menu ";
		 		$db_result = db_result($sql);
		 		
		 		foreach($pages as $page) {
		 			$sql = "SELECT * " .
		 				"FROM " . DB_PREFIX . "pages " .
		 				"WHERE page_id=$page";
		 			$page_result = db_result($sql);
		 			$page_db = mysql_fetch_object($page_result);
		 			if($page_db->page_parent_id == "0") {
		 				if($page_db->page_access == 'public') {
		 					if($page_db->page_type != 'link') {
			 					$new = 'no';
		 					}
		 					else {
		 						$new = 'yes';
			 				}
			 				// FIXME: What is gonig on if it is an link-page??
			 				$link = "l:" . $page_db->page_name;
		 				
		 					$sql = "SELECT menu_orderid
		 						FROM " . DB_PREFIX . "menu
		 						WHERE menu_id=$menu_id
		 						ORDER BY menu_orderid DESC
			 					LIMIT 1";
		 					$menu_result = db_result($sql);
							$menu_data = mysql_fetch_object($menu_result);
							if($menu_data != null)
								$ordid = $menu_data->menu_orderid + 1;
							else
								$ordid = 0;
						
						
							$sql = "INSERT INTO " . DB_PREFIX . "menu
								(menu_text, menu_link, menu_new, menu_orderid, menu_menuid, menu_page_id)
								VALUES ('$page_db->page_title', '$link', '$new', $ordid, $menu_id, $page_db->page_id)";
							db_result($sql);
		 				}
		 			}
		 		}
		 	}
		 	header('Location: ' . $_SERVER['PHP_SELF'] . '?page=pagestructure');
		 	//return $out;
		 }
		 
		 function _newLink() {
		 	$out = '';
		 	
		 	return $out;
		 }
		 
		 function _deletePage() {
		 	global $admin_lang, $user;
		 	
		 	$sure = GetPostOrGet('sure');
		 	$page_id = GetPostOrGet('page_id');
		 	$sql = "SELECT *
		 		FROM " . DB_PREFIX . "pages
		 		WHERE page_id=$page_id";
		 	$page_result = db_result ($sql);
		 	if($page = mysql_fetch_object($page_result)) {
		 		$out = '';
		 		$sql = "SELECT *
		 			FROM " . DB_PREFIX . "pages
		 			WHERE page_parent_id=$page->page_id";
		 		$subpages_result = db_result($sql);
		 		if($subpage = mysql_fetch_object($subpages_result))
		 			$out .= "Das l�schen von Seiten mit Unterseiten ist zur Zeit nicht m�glich!<br /><strong>Tip:</strong> L�schen sie erst alle Unterseiten<br /><a href=\"" . $_SERVER['PHP_SELF'] . "?page=pagestructure\">Zur�ck</a>";
		 		elseif($sure == 1) {
		 			$out .= "L�schen...";
		 			$sql = "UPDATE " . DB_PREFIX . "pages
						SET  page_access='deleted', page_creator='$user->ID', page_date='" . mktime() . "'
						WHERE page_id='$page_id'";
					db_result($sql);
		 		}
		 		else
		 			$out .= "Wollen sie die Seite &quot;$page->page_title&quot; wirklich (vorerst) unwiederruflich l�schen?<br /><a href=\"" . $_SERVER['PHP_SELF'] . "?page=pagestructure&amp;action=delete&amp;page_id=$page_id&amp;sure=1\" class=\"button\">" . $admin_lang['yes'] . "</a>
		 					<a href=\"" . $_SERVER['PHP_SELF'] . "?page=pagestructure\" class=\"button\">" . $admin_lang['no'] . "</a>";
		 	
			 	return $out;
		 	}
		 	else {
		 		header('Location: ' . $_SERVER['PHP_SELF'] . '?page=pagestructure');
		 	}
		 	
		 }
		 
		 function _getMenuPageIDs() {
		 	$this->MenuPageIDs = array();
		 	$sql = "SELECT menu_page_id
		 		FROM " . DB_PREFIX . "menu";
		 	$ids_result = db_result($sql);
		 	while($id = mysql_fetch_object($ids_result))
		 		$this->MenuPageIDs[] = $id->menu_page_id;
		 }
		 
		 function _homePage() {
		 	global $admin_lang;
		 	
		 	$this->_getMenuPageIDs();
			$out = "\t\t\t<a href=\"admin.php?page=pagestructure&amp;action=new_page\" class=\"button\">" . $admin_lang['create_new_page'] . "</a><br />\r\n";;
			$out .= "<!--\t\t\t<a href =\"" . $_SERVER['PHP_SELF'] . "?page=pagestructur&amp;action=new_link\">neuer Link</a>-->\r\n";
			$out .= "\t\t\t<form method=\"post\" action=\"" . $_SERVER['PHP_SELF'] . "\">\r\n";
			$out .= "\t\t\t<input type=\"hidden\" name=\"page\" value=\"pagestructure\" />\r\n";
			$out .= "\t\t\t<input type=\"hidden\" name=\"action\" value=\"generate_menu\" />\r\n";
		 	$out .= $this->_showStructure(0);
			$out .= "\t\t\t</form>\r\n";
			
			return $out;
		 }
		 
		 function _newPage() {
		 	global $_SERVER, $admin_lang;
			$out = '';
		 	/*$out .= "<select>\r\n";
		 	$out .= $this->_structurePullDown(0);
		 	$out .= "</select>\r\n";*/
		 	$out .= "\t\t\t<form method=\"post\" action=\"admin.php\">
				<fieldset>
				<legend>Neue Seite</legend>
				<input type=\"hidden\" name=\"page\" value=\"pagestructure\" />
				<input type=\"hidden\" name=\"action\" value=\"add_new\" />
				<table>
					<tr>
						<td>
							Name/K�rzel:
							<span class=\"info\">Mit diesem K�rzel wird auf die Seite zugegriffen und dient es zur eindeutigen Identifizierung der Seite.</span>
						</td>
						<td>
							<input type=\"text\" name=\"page_name\" maxlength=\"20\" />
						</td>
					</tr>
					<tr>
						<td>
							Titel:
							<span class=\"info\">Der Titel wird sp�ter in der Titelleiste des Browsers angezeigt.</span>
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
							<span class=\"info\">Der Text soll in der gew�hlten Sprache geschrieben werden.</span>
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
							<span class=\"info\">Wer soll sich die Seite sp�ter anschauen k�nnen?<br />
							Jeder (�ffentlich), nur ausgew�hlte Benutzer (privat) oder soll die Seite nur erstellt werden um sie sp�ter zu ver�ffentlichen (versteckt)?</span>
						</td>
						<td>
							<select name=\"page_access\">
								<option value=\"public\">�ffentlich</option>
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
							Kommentar
							<span class=\"info\">Eine kurze Beschreibung, was hier gemacht wurde.</span>
						</td>
						<td><input type=\"text\" name=\"page_edit_comment\" maxlength=\"100\" value=\"" . $admin_lang['created_new_page'] . "\"/></td>
					</tr>
					<tr>
						<td>
							Bearbeiten?
							<span class=\"info\">Soll die Seite nach dem Erstellen bearbeitet werden oder soll wieder auf die �bersichtseite zur�ckgekehrt werden?</span>
						</td>
						<td><input type=\"checkbox\" name=\"page_edit\" value=\"edit\" checked=\"true\" class=\"checkbox\"/></td>
					</tr>
					<tr>
						<td colspan=\"2\">
							<input type=\"reset\" class=\"button\" value=\"Zur�cksetzen\" />&nbsp;
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
		 		WHERE page_parent_id=$topnode AND page_access !='deleted'";
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
		 		$out .= "\r\n\t\t\t<ol>\r\n";
		 		while($page = mysql_fetch_object($pages_result)) {
		 			$out .= "\t\t\t\t<li class=\"page_type_$page->page_type" . (($page->page_access == 'deleted') ? ' strike' : '' ). "\">" . (($topnode == 0) ?  "<input type=\"checkbox\" name=\"pagestructure_pages[]\"" . ((in_array($page->page_id,$this->MenuPageIDs)) ? ' checked="checked"'  : '') . (($page->page_access != 'public') ? ' disabled="disabled"'  : '') . " value=\"$page->page_id\" class=\"checkbox\"/>\t" : '' );
		 			
		 			$out .= "<strong>$page->page_title</strong> ($page->page_name)";
		 			$out .= "[$page->page_lang]";
		 			// edit:
		 			if($page->page_access != 'deleted')
		 				$out .= " <a href=\"" . $_SERVER['PHP_SELF'] . "?page=pagestructure&amp;action=edit&amp;page_id=$page->page_id\"><img src=\"./img/edit.png\" class=\"icon\" height=\"16\" width=\"16\" alt=\"" . $admin_lang['edit'] . "\" title=\"" . $admin_lang['edit'] . "\"/></a>";
		 			// info:
		 			$out .= " <a href=\"" . $_SERVER['PHP_SELF'] . "?page=pagestructure&amp;action=info&amp;page_id=$page->page_id\"><img src=\"./img/info.png\" class=\"icon\" height=\"16\" width=\"16\" alt=\"" . $admin_lang['info'] . "\" title=\"" . $admin_lang['info'] . "\"/></a>";
		 			// view:
		 			if($page->page_access != 'deleted')
		 				$out .= " <a href=\"index.php?page=$page->page_name\"><img src=\"./img/view.png\" class=\"icon\" height=\"16\" width=\"16\" alt=\"Anschauen\" title=\"Anschauen\"/></a>";
		 			// delete:
		 			if($page->page_access != 'deleted')
		 				$out .= " <a href=\"" . $_SERVER['PHP_SELF'] . "?page=pagestructure&amp;action=delete&amp;page_id=$page->page_id\"><img src=\"./img/del.png\" class=\"icon\" height=\"16\" width=\"16\" alt=\"" . $admin_lang['delete'] . "\" title=\"" . $admin_lang['delete'] . "\"/></a>";
		 			
					$out .= $this->_showStructure($page->page_id);
		 			$out .= "\t\t\t\t</li>\r\n";
				}
				
				if($topnode == 0) {
					$out .= "\t\t\t\t<li class=\"pagestructure_sendbutton\"><input type=\"submit\" class=\"button\" name=\"pagestructure_savemenu\" value=\"" . $admin_lang['generate_mainmenu'] . "\" /></li>" .
						"\r\n\t\t\t</ol>\r\n";
				}
				else {
					/*$sql = "SELECT *
						FROM " . DB_PREFIX . "pages
						WHERE page_id=$topnode";
					$page_result = db_result($sql);
					$page = mysql_fetch_object($page_result);*/
					
					$out .= /*"\t\t\t\t<li class=\"pagestructure_sendbutton\"><input type=\"submit\" name=\"pagestructu_savemenu\" value=\"" . $admin_lang['generate_menu_for'] . ": $page->page_title\" /></li>" .*/
						"\r\n\t\t\t</ol>\r\n\r\n";
				}
			}
			
			return $out;
		}
		
		function _editPage() {
			$page_id = GetPostOrGet('page_id');
			$out = '';
			$sql = "SELECT page_id, page_type
				FROM " . DB_PREFIX . "pages
				WHERE page_id = $page_id";
			$page_result = db_result($sql);
			if($page = mysql_fetch_object($page_result)) {
				$edit = null;
				switch($page->page_type) {
					case 'text':		include('classes/edit_text_page.php');
								$edit = new Edit_Text_Page();
								break;
					case 'gallery':		include('classes/edit_gallery_page.php');
								$edit = new Edit_Gallery_Page();
								break;	
					case 'link':		include('classes/edit_link_page.php');
								$edit = new Edit_Link_Page();
								break;			
					default:		$out .= "Der Seitentyp <strong>$page->page_type</strong> l�sst sich noch nicht bearbeiten.";
								break;
				}
				if($edit !== null)
					$out .= $edit->Edit($page->page_id);
				return $out;
			}
		}
		
		function _savePage() {
			$page_id = GetPostOrGet('page_id');
			$out = '';
			$sql = "SELECT page_id, page_type
				FROM " . DB_PREFIX . "pages
				WHERE page_id = $page_id";
			$page_result = db_result($sql);
			if($page = mysql_fetch_object($page_result)) {
				$edit = null;
				switch($page->page_type) {
					case 'text':		include('classes/edit_text_page.php');
								$edit = new Edit_Text_Page();
								break;
					case 'gallery':		include('classes/edit_gallery_page.php');
								$edit = new Edit_Gallery_Page();
								break;				
					default:		$out .= "Der Seitentyp <strong>$page->page_type</strong> l�sst sich noch nicht bearbeiten.";
								break;
				}
				if($edit !== null)
					$out .= $edit->Save($page->page_id);
				return $out;
			}
		}
		
		function _addPage() {
			global $user;
			
			$page_access = GetPostOrGet('page_access');
			$page_edit = GetPostOrGet('page_edit');
			$page_edit_comment = GetPostOrGet('page_edit_comment');
			$page_lang = GetPostOrGet('page_lang');
			$page_name = GetPostOrGet('page_name');
			$page_parent_id = GetPostOrGet('page_parent_id');
			$page_title = GetPostOrGet('page_title');
			$page_type = GetPostOrGet('page_type');
						
			$page_edit_comment = htmlspecialchars($page_edit_comment);
			$edit = null;
			$out = '';
			$id = -1;
			// create new page_type-data-page
			switch($page_type) {
				case 'text':		include('classes/edit_text_page.php');
							$edit = new Edit_Text_Page();
							break;
				case 'gallery':		include('classes/edit_gallery_page.php');
							$edit = new Edit_Gallery_Page();
							break;
				case 'link':		include('classes/edit_link_page.php');
							$edit = new Edit_Link_Page();
							break;
				default:		$out .= "Der Seitentyp <strong>$page_type</strong> l�sst sich noch nicht bearbeiten.";
							return $out;
			}
			if($edit !== null) {
				
				$a_access = array('public', 'private', 'hidden');
				if(!in_array($page_access, $a_access))
					$page_access = $a_access[0];
				$page_name = strtolower($page_name);
				$page_name = str_replace(' ', '_', $page_name);
				
				// check if the page exists
				$sql = "SELECT *
					FROM " . DB_PREFIX . "pages
					WHERE page_name = '$page_name' AND page_lang = '$page_lang'
					LIMIT 0,1";
				$exists_result = db_result($sql);
				if($exists = mysql_fetch_object($exists_result)) { // exists
					if($exists->page_access == 'deleted') { // the page is deleted so we can overwrite it
						
						$sql = "INSERT INTO " . DB_PREFIX . "pages_history (page_id, page_type, page_name, page_title, page_parent_id, page_lang, page_creator, page_date, page_edit_comment)
							VALUES($exists->page_id, '$exists->page_type', '$exists->page_name', '$exists->page_title', $exists->page_parent_id, '$exists->page_lang', $exists->page_creator, $exists->page_date, '$exists->page_edit_comment')";
						db_result($sql);
						$history_id = mysql_insert_id();
						$sql = "UPDATE " . DB_PREFIX . "pages
							SET page_creator=$user->ID, page_date=" . mktime() . ", page_title='$page_title', page_edit_comment='$page_edit_comment', page_access='$page_access', page_type='$page_type', page_parent_id='$page_parent_id'
							WHERE page_id=$exists->page_id";
						db_result($sql);
						$lastid = $exists->page_id;
						$edit->NewPage($exists->page_id, $history_id);
					}
					else {
						return sprintf("a page with the name %s exists already", $exists->page_name);
					}
				}
				else {// dont extist
					$sql = "INSERT INTO " . DB_PREFIX . "pages (page_lang, page_access, page_name, page_title, page_parent_id, page_creator, page_type, page_date, page_edit_comment)
						VALUES('$page_lang', '$page_access', '$page_name', '$page_title', $page_parent_id, $user->ID, '$page_type', " . mktime() . ", '$page_edit_comment')";
					db_result($sql);
					$lastid = mysql_insert_id();
					$edit->NewPage($lastid);
				}
			}
			if($page_edit != '')
				header("Location: admin.php?page=pagestructure&action=edit&page_id=$lastid");
			else
				header("Location: admin.php?page=pagestructure");
	
		}
		
		function _pagePath($pageid=0) {
			$out = '';
			$sql = "SELECT *
			FROM " . DB_PREFIX . "pages
			WHERE page_id=$pageid";
			$page_result = db_result($sql);
			while($page = mysql_fetch_object($page_result)) {
				$out = "<a href=\"admin.php?page=pagestructure&amp;action=info&amp;page_id=$page->page_id\">$page->page_name</a>" . $out;
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
			global $admin_lang;
			
			$page_id =  GetPostOrGet('page_id');
			$out = '';
			$sql = "SELECT *
				FROM " . DB_PREFIX . "pages
				WHERE page_id=$page_id";
			$page_result = db_result($sql);
			if($page = mysql_fetch_object($page_result)) {
				$out .= "\t\t\t<table>
				<tr>
					<td>Titel:</td>
					<td>$page->page_title</td>
				</tr>
				<tr>
					<td>Name:</td>
					<td>$page->page_name</td>
				</tr>
				<tr>
					<td>Typ:</td>
					<td>$page->page_type</td>
				</tr>
				<tr>
					<td>Sprache:</td>
					<td>" . $admin_lang[$page->page_lang] . "</td>
				</tr>
				<tr>
					<td>Pfad:</td>
					<td>" . $this->_pagePath($page->page_id) . "</td>
				</tr>
				<tr>
					<td>Bearbeitet von:</td>
					<td>" . getUserById($page->page_creator) . "</td>
				</tr>
				<tr>
					<td>Bearbeitet am:</td>
					<td>" . date("d.m.Y H:i:s",$page->page_date) . "</td>
				</tr>
			</table>\r\n";
				$sql = "SELECT *
					FROM " . DB_PREFIX . "pages_history
					WHERE page_id = $page_id
					ORDER BY page_date DESC";
				$result = db_result($sql);
				$changes_count = mysql_num_rows($result);
				$out .="\t\t\t<h4>Ver�nderungen($changes_count)</h4><hr />
			<table class=\"page_commits\">
				<thead>
					<tr>
						<td>Datum</td>
						<td>Ver�nderer</td>
						<td>Titel</td>
						<td>Kommentar</td>
						<td>Aktionen</td>
					</tr>
				</thead>
				<tr>
					<td>" . date("d.m.Y H:i:s",$page->page_date) . "</td>
					<td>".getUserById($page->page_creator) . "</td>
					<td>$page->page_title</td>
					<td>$page->page_edit_comment&nbsp;</td>
					<td>
						<a href=\"index.php?page=$page->page_name\"><img src=\"./img/view.png\" height=\"16\" width=\"16\" alt=\"Anschauen\" title=\"Anschauen\"/></a>
						<a href=\"admin.php?page=pagestructure&amp;action=edit&amp;page_id=$page->page_id\"><img src=\"./img/edit.png\" height=\"16\" width=\"16\" alt=\"" . $admin_lang['edit'] . "\" title=\"" . $admin_lang['edit'] . "\"/></a>
					</td>
				</tr>\r\n";
				
				while($change = mysql_fetch_object($result)) {
					$out .= "\t\t\t\t<tr>
					<td>" . date("d.m.Y H:i:s",$change->page_date) . "</td>
					<td>".getUserById($change->page_creator) . "</td>
					<td>$change->page_title</td>
					<td>$change->page_edit_comment&nbsp;</td>
					<td><a href=\"index.php?page=$page->page_id&amp;change=$changes_count\"><img src=\"./img/view.png\" height=\"16\" width=\"16\" alt=\"Anschauen\" title=\"Anschauen\"/></a>
					<a href=\"admin.php?page=pagestructure&amp;action=edit&amp;page_id=$page->page_id&amp;change=$changes_count\"><img src=\"./img/edit.png\" height=\"16\" width=\"16\" alt=\"" . $admin_lang['edit'] . "\" title=\"" . $admin_lang['edit'] . "\"/></a>
					<a href=\"admin.php?page=pagestructure&amp;action=save&amp;page_id=$page->page_id&amp;change=$changes_count\"><img src=\"./img/restore.png\" height=\"16\" width=\"16\" alt=\"" . $admin_lang['restore'] . "\" title=\"" . $admin_lang['restore'] . "\"/></a></td>
				</tr>\r\n";
					$changes_count--;
				}
				
				$out .="\t\t\t</table>";
				
				
			}
			
			return $out;
		}
		
 	}