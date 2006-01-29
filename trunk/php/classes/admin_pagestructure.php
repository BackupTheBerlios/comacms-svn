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
 	 * @ignore
 	 */
 	require_once('./classes/pagestructure.php');
 	
	/**
	 * @package ComaCMS 
	 */
 	class Admin_PageStructure {
 		
 		/**
 		 * @var array
 		 */
 		var $MenuPageIDs;
 		
 		var $_SqlConnection;
 		var $_Pagestructure;
 		var $_AdminLang;
 		
 		
 		function Admin_PageStructure($SqlConnection, $AdminLang) {
			$this->_SqlConnection = $SqlConnection;
			$this->_AdminLang = $AdminLang;
			$this->_Pagestructure = new Pagestructure($this->_SqlConnection);
		}
 		
 		/**
 		 * @return string
 		 * @param action string
 		 */
		 function GetPage($Action = '') {
		 	$adminLang = $this->_AdminLang;
		 	
			$out = '';
			$Action = strtolower($Action);
			if($Action != 'intern_home')
				$out .= "\t\t\t<h2>" . $adminLang['pagestructure'] . "</h2>\r\n";
		 	switch ($Action) {
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
				case 'inlinemenu':	$out .= $this->_inlineMenu();
							break;
		 		default:		$out .= $this->_homePage();
		 	}
			return $out;
		 }
		 
		 /**
		  * @return string
		  */
		 function _generate_menu() {
		 	$pages = GetPostOrGet('pagestructure_pages');
		 	// Clear the main-menu
		 	$this->_Pagestructure->ClearMenu(1);
		 	// Insert the pages to the main-menu
		 	$this->_Pagestructure->GenerateMenu($pages, 1);
		 	// Print out the default view
		 	return $this->GetPage('intern_home');
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
		 			$out .= "Das l&ouml;schen von Seiten mit Unterseiten ist zur Zeit nicht m6ounl;glich!<br /><strong>Tip:</strong> L&ouml;schen sie erst alle Unterseiten<br /><a href=\"" . $_SERVER['PHP_SELF'] . "?page=pagestructure\">Zur&uuml;ck</a>";
		 		elseif($sure == 1) {
		 			$out .= "L&ouml;schen...";
		 			$sql = "UPDATE " . DB_PREFIX . "pages
						SET  page_access='deleted', page_creator='$user->id', page_date='" . mktime() . "'
						WHERE page_id='$page_id'";
					db_result($sql);
		 		}
		 		else
		 			$out .= "Wollen sie die Seite &quot;$page->page_title&quot; wirklich (vorerst) unwiederruflich l�schen?<br /><a href=\"" . $_SERVER['PHP_SELF'] . "?page=pagestructure&amp;action=delete&amp;page_id=$page_id&amp;sure=1\" class=\"button\">" . $admin_lang['yes'] . "</a>
		 					<a href=\"" . $_SERVER['PHP_SELF'] . "?page=pagestructure\" class=\"button\">" . $admin_lang['no'] . "</a>";
		 	
			 	return $out;
		 	}
		 	else {
		 		return $this->GetPage('intern_home');
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
			$out = "\t\t\t<a href=\"admin.php?page=pagestructure&amp;action=new_page\" class=\"button\">" . $admin_lang['create_new_page'] . "</a><br />\r\n";
			$out .= "<!--\t\t\t<a href =\"" . $_SERVER['PHP_SELF'] . "?page=pagestructur&amp;action=new_link\">neuer Link</a>-->\r\n";
			$out .= "\t\t\t<form method=\"post\" action=\"" . $_SERVER['PHP_SELF'] . "\">\r\n";
			$out .= "\t\t\t<input type=\"hidden\" name=\"page\" value=\"pagestructure\" />\r\n";
			$out .= "\t\t\t<input type=\"hidden\" name=\"action\" value=\"generate_menu\" />\r\n";
		 	$out .= $this->_showStructure(0);
		 	$out .= "<input type=\"submit\" class=\"button\" name=\"pagestructure_savemenu\" value=\"" . $admin_lang['generate_mainmenu'] . "\" />\r\n";
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
				<div class=\"row\">
					<label class=\"row\">
						Name/K&uuml;rzel:
						<span class=\"info\">Mit diesem K&uuml;rzel wird auf die Seite zugegriffen und dient es zur eindeutigen Identifizierung der Seite.</span>
					</label>
					<input type=\"text\" name=\"page_name\" maxlength=\"20\" />
				</div>
				<div class=\"row\">
					<label class=\"row\">
						Titel:
						<span class=\"info\">Der Titel wird sp&auml;ter in der Titelleiste des Browsers angezeigt.</span>
					</label>
					<input type=\"text\" name=\"page_title\" maxlength=\"100\" />
				</div>
				<div class=\"row\">
					<label class=\"row\">
						Seiten-Typ:
						<span class=\"info\">TODO</span>
					</label>
					<select name=\"page_type\">
						<option value=\"text\">Text</option>
						<option value=\"gallery\">" . $admin_lang['gallery'] ."</option>
					</select>
				</div>
				<div class=\"row\">
					<label class=\"row\">
						" . $admin_lang['language'] . ":
						<span class=\"info\">Der Text soll in der gew&auml;hlten Sprache geschrieben werden.</span>
					</label>
					<select name=\"page_lang\">
						<option value=\"de\">Deutsch</option>
						<option value=\"en\">Englisch</option>
					</select>
				</div>
				<div class=\"row\">
					<label class=\"row\">
						Zugang:
						<span class=\"info\">Wer soll sich die Seite sp&auml;ter anschauen k&ouml;nnen?<br />
						Jeder (&Ouml;ffentlich), nur ausgew&auml;hlte Benutzer (privat) oder soll die Seite nur erstellt werden um sie sp&auml;ter zu ver&ouml;ffentlichen (versteckt)?</span>
					</label>
					<select name=\"page_access\">
						<option value=\"public\">&Ouml;ffentlich</option>
						<option value=\"private\">Privat</option>
						<option value=\"hidden\">Versteckt</option>
					</select>
				</div>
				<div class=\"row\">
					<label class=\"row\">
						Unterseite von:
						<span class=\"info\">TODO</span>
					</label>
					<select name=\"page_parent_id\">
						<option value=\"0\">Keiner</option>\r\n";
		 	$out .= $this->_structurePullDown(0);
		 	$out .= "\t\t\t\t\t</select>
				</div>
				<div class=\"row\">
					<label class=\"row\">
						Kommentar
						<span class=\"info\">Eine kurze Beschreibung, was hier gemacht wurde.</span>
					</label>
					<input type=\"text\" name=\"page_edit_comment\" maxlength=\"100\" value=\"" . $admin_lang['created_new_page'] . "\"/>
				</div>
				<div class=\"row\">
					<label class=\"row\">
						Bearbeiten?
						<span class=\"info\">Soll die Seite nach dem Erstellen bearbeitet werden oder soll wieder auf die &Uuml;bersichtseite zur&uuml;ckgekehrt werden?</span>
					</label>
					<input type=\"checkbox\" name=\"page_edit\" value=\"edit\" checked=\"true\" class=\"checkbox\"/>
				</div>
				<div class=\"row\">
					<input type=\"reset\" class=\"button\" value=\"Zur&uuml;cksetzen\" />&nbsp;
					<input type=\"submit\" class=\"button\" value=\"Erstellen\" />
				</div>
			</fieldset>
			</form>";
		 	return $out;
		 }
		 
		 function _structurePullDown($topnode = 0, $deep = 0, $topnumber = '', $without = -1, $selected = -1) {
		 	$out = '';
			$sql = "SELECT *
		 		FROM " . DB_PREFIX . "pages
		 		WHERE page_parent_id=$topnode AND page_access !='deleted'";
		 	// TODO: ORDER BY page_sortid
		 	$pages_result = db_result($sql);
		 	if(mysql_num_rows($pages_result) != 0) {
		 		$number = 1;
		 		while($page = mysql_fetch_object($pages_result)) {
		 			if($page->page_id != $without) {
		 				$out .= "<option style=\"padding-left:" . ($deep * 1.5) . "em;\" value=\"$page->page_id\"" . (($page->page_id == $selected) ? ' selected="selected"' : '') . ">$topnumber$number. $page->page_title ($page->page_name)</option>\r\n";
		 				$out .= $this->_structurePullDown($page->page_id, $deep + 1, $topnumber . $number. "." ,$without, $selected);
		 				$number++;
		 			}
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
		 			$out .= "\t\t\t\t<li class=\"page_type_$page->page_type" . (($page->page_access == 'deleted') ? ' strike' : '' ). "\"><span class=\"structure_row\">" . (($topnode == 0) ?  "<input type=\"checkbox\" name=\"pagestructure_pages[]\"" . ((in_array($page->page_id,$this->MenuPageIDs)) ? ' checked="checked"'  : '') . (($page->page_access != 'public') ? ' disabled="disabled"'  : '') . " value=\"$page->page_id\" class=\"checkbox\"/>\t" : '' );
		 			
		 			$out .= "<strong>$page->page_title</strong> ($page->page_name)";
		 			$out .= "<span class=\"page_lang\">[" . $admin_lang[$page->page_lang] . "]</span><span class=\"page_actions\">";
		 			// edit:
		 			if($page->page_access != 'deleted')
		 				$out .= " <a href=\"admin.php?page=pagestructure&amp;action=edit&amp;page_id=$page->page_id\"><img src=\"./img/edit.png\" class=\"icon\" height=\"16\" width=\"16\" alt=\"" . $admin_lang['edit'] . "\" title=\"" . $admin_lang['edit'] . "\"/></a>";
		 			// info:
		 			$out .= " <a href=\"admin.php?page=pagestructure&amp;action=info&amp;page_id=$page->page_id\"><img src=\"./img/info.png\" class=\"icon\" height=\"16\" width=\"16\" alt=\"" . $admin_lang['info'] . "\" title=\"" . $admin_lang['info'] . "\"/></a>";
		 			// view:
		 			if($page->page_access != 'deleted')
		 				$out .= " <a href=\"index.php?page=$page->page_name\"><img src=\"./img/view.png\" class=\"icon\" height=\"16\" width=\"16\" alt=\"Anschauen $page->page_title\" title=\"Anschauen\"/></a>";
		 			// inlinemenu:
		 			if($page->page_access != 'deleted')
		 					$out .= " <a href=\"admin.php?page=pagestructure&amp;action=inlinemenu&amp;page_id=$page->page_id\" title=\"Das Zusatzmen&uuml; f&uuml;r &quot;$page->page_title&quot; bearbeiten\">" . $admin_lang['inlinemenu'] . "</a>";
		 			// delete:
		 			if($page->page_access != 'deleted')
		 				$out .= " <a href=\"admin.php?page=pagestructure&amp;action=delete&amp;page_id=$page->page_id\"><img src=\"./img/del.png\" class=\"icon\" height=\"16\" width=\"16\" alt=\"" . $admin_lang['delete'] . "\" title=\"" . $admin_lang['delete'] . "\"/></a>";
		 			
					$out .= '</span></span>' . $this->_showStructure($page->page_id);
		 			$out .= "\t\t\t\t</li>\r\n";
				}
				$out .= "\r\n\t\t\t</ol>\r\n\r\n";
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
							SET page_creator=$user->id, page_date=" . mktime() . ", page_title='$page_title', page_edit_comment='$page_edit_comment', page_access='$page_access', page_type='$page_type', page_parent_id='$page_parent_id'
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
						VALUES('$page_lang', '$page_access', '$page_name', '$page_title', $page_parent_id, $user->id, '$page_type', " . mktime() . ", '$page_edit_comment')";
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
		
		function _pagePath($pageid = 0) {
			$out = '';
			$sql = "SELECT *
			FROM " . DB_PREFIX . "pages
			WHERE page_id=$pageid";
			$page_result = db_result($sql);
			while($page = mysql_fetch_object($page_result)) {
				if($pageid == $page->page_id)
					$out = " <span title=\"$page->page_title\">$page->page_name</span>";
				else
					$out = "<a href=\"admin.php?page=pagestructure&amp;action=info&amp;page_id=$page->page_id\" title=\"$page->page_title\">$page->page_name</a>" . $out;
				if($page->page_parent_id != 0)
					$out = '<strong>/</strong>' . $out;
				$sql = "SELECT *
					FROM " . DB_PREFIX . "pages
					WHERE page_id=$page->page_parent_id";
				$page_result = db_result($sql);
			}
			return $out;
		}
		/**
		 * inlineMenu
		 * inlinemenu-management
		 */
		function _inlineMenu() {
			global $admin_lang;
			
			$page_id = GetPostOrGet('page_id');
			$action2 = GetPostOrGet('action2');
			$out = '';
			$sql = "SELECT " . DB_PREFIX. "pages.*, " . DB_PREFIX . "inlinemenu.*
				FROM ( " . DB_PREFIX. "pages
				LEFT JOIN " . DB_PREFIX . "inlinemenu ON " . DB_PREFIX . "inlinemenu.page_id = " . DB_PREFIX. "pages.page_id )
				WHERE " . DB_PREFIX. "pages.page_access!='deleted' AND " . DB_PREFIX. "pages.page_id = $page_id";
			$inline_result = db_result($sql);
			$inline =  mysql_fetch_object($inline_result);
			if($inline->inlinemenu_html === null && $action2 == 'create') {
				$sql = "INSERT INTO " . DB_PREFIX . "inlinemenu (page_id, inlinemenu_image, inlinemenu_html)
					VALUES($page_id, '', '')";
				db_result($sql);
			}
				
			
			if($inline->inlinemenu_html === null && $action2 != 'create') {
				$out .= "Es wurde bis jetzt kein Zusatzmen&uuml; erstellt, soll das nun geschehen?<br />
				<a href=\"admin.php?page=pagestructure&amp;action=inlinemenu&amp;action2=create&amp;page_id=$page_id&amp;sure=1\" title=\"" . $admin_lang['yes'] . "\" class=\"button\">" . $admin_lang['yes'] . "</a>
				<a href=\"admin.php?page=pagestructure\" title=\"" . $admin_lang['no'] . "\" class=\"button\">" . $admin_lang['no'] . "</a>";
				return $out;
			}
			else if($action2 == 'remove_image') {
				$sql = "UPDATE " . DB_PREFIX . "inlinemenu
						SET inlinemenu_image='', inlinemenu_image_thumb=''
						WHERE page_id=$page_id";
					db_result($sql);
					$inline->inlinemenu_image_thumb = '';
					$inline->inlinemenu_image = '';
			}
			else if($action2 == 'set_thumb_title') {
				$thumbTitle = GetPostOrGet('thumb_title');
				$sql = "UPDATE " . DB_PREFIX . "inlinemenu
						SET inlinemenu_image_title='$thumbTitle'
						WHERE page_id=$page_id";
					db_result($sql);
					$inline->inlinemenu_image_title = $thumbTitle;
			}
			else if($action2 == 'select_image') {
				$sql = "SELECT *
					FROM " . DB_PREFIX . "files
					WHERE file_type LIKE 'image/%'
					ORDER BY file_name ASC";
				$images_result = db_result($sql);
				$imgmax = 100;
				$imgmax2 = 200;
				$inlinemenu_folder = 'data/thumbnails/';
				$out .= "<form action=\"admin.php\" method=\"post\"><div class=\"imagesblock\">
				<input type=\"hidden\" name=\"page\" value=\"pagestructure\"/>
				<input type=\"hidden\" name=\"action\" value=\"inlinemenu\"/>
				<input type=\"hidden\" name=\"page_id\" value=\"$page_id\"/>
				<input type=\"hidden\" name=\"action2\" value=\"set_image\"/>";
				while($image = mysql_fetch_object($images_result)) {
					$thumbnail = resizteImageToMaximum($image->file_path, $inlinemenu_folder ,$imgmax);
					if($thumbnail !== false) {
						$out .= "<div class=\"imageblock\">
					<a href=\"" . generateUrl($image->file_path) . "\">
					<img style=\"display:block;margin:auto;\" src=\"" . generateUrl($thumbnail) . "\" alt=\"". basename($thumbnail) ."\" /></a><br />
					<input type=\"radio\" name=\"image_path\" " .(($inline->inlinemenu_image == $image->file_path) ? 'checked="checked" ' : '') . " value=\"$image->file_path\"/></div>";
					}
				}
				$out .= "</div><input type=\"submit\" value=\"" . $admin_lang['apply'] . "\" class=\"button\"/><a href=\"admin.php?page=pagestructure&amp;action=inlinemenu&amp;page_id=$page_id\" class=\"button\">" . $admin_lang['back'] . "</a></form>";
				
			}
			else if($action2 == 'set_image') {
				$imagePath = GetPostOrGet('image_path');
				$imgmax2 = 200;
				$inlinemenu_folder = 'data/thumbnails/';
				$thumbnail = resizteImageToWidth($imagePath, $inlinemenu_folder, $imgmax2);
				if(file_exists($thumbnail)) {
					$sql = "UPDATE " . DB_PREFIX . "inlinemenu
						SET inlinemenu_image_thumb='$thumbnail', inlinemenu_image='$imagePath'
						WHERE page_id=$page_id";
					db_result($sql);
					$inline->inlinemenu_image_thumb = $thumbnail;
				}
					
			}
			else if($action2 == 'up') {
				$entrie_id = GetPostOrGet('entrie_id');
				$sql = "SELECT *
			 		FROM " . DB_PREFIX . "inlinemenu_entries
					WHERE inlineentrie_id=$entrie_id";
				$first_entrie_result = db_result($sql);
				if($first_entrie = mysql_fetch_object($first_entrie_result)) {
					$first_id = $first_entrie->inlineentrie_id;
					$first_sortid = $first_entrie->inlineentrie_sortid;
					$sql = "SELECT *
						FROM " . DB_PREFIX . "inlinemenu_entries
						WHERE inlineentrie_sortid < $first_sortid AND inlineentrie_page_id=$first_entrie->inlineentrie_page_id
						ORDER BY inlineentrie_sortid DESC";
					$second_entrie_result = db_result($sql);
					if($second_entrie = mysql_fetch_object($second_entrie_result)) {
						$second_id = $second_entrie->inlineentrie_id;
						$second_sortid = $second_entrie->inlineentrie_sortid;
						$sql = "UPDATE " . DB_PREFIX . "inlinemenu_entries
							SET inlineentrie_sortid=$second_sortid
							WHERE inlineentrie_id=$first_id";
						db_result($sql);
						$sql = "UPDATE " . DB_PREFIX . "inlinemenu_entries
							SET inlineentrie_sortid=$first_sortid
							WHERE inlineentrie_id=$second_id";
						db_result($sql);
						generateinlinemenu($second_entrie->inlineentrie_page_id);			
					}
				}			
			}
			else if($action2 == 'down') {
				$entrie_id = GetPostOrGet('entrie_id');
				$sql = "SELECT *
			 		FROM " . DB_PREFIX . "inlinemenu_entries
					WHERE inlineentrie_id=$entrie_id";
				$first_entrie_result = db_result($sql);
				if($first_entrie = mysql_fetch_object($first_entrie_result)) {
					$first_id = $first_entrie->inlineentrie_id;
					$first_sortid = $first_entrie->inlineentrie_sortid;
					$sql = "SELECT *
						FROM " . DB_PREFIX . "inlinemenu_entries
						WHERE inlineentrie_sortid > $first_sortid AND inlineentrie_page_id=$first_entrie->inlineentrie_page_id
						ORDER BY inlineentrie_sortid ASC";
					$second_entrie_result = db_result($sql);
					if($second_entrie = mysql_fetch_object($second_entrie_result)) {
						$second_id = $second_entrie->inlineentrie_id;
						$second_sortid = $second_entrie->inlineentrie_sortid;
						$sql = "UPDATE " . DB_PREFIX . "inlinemenu_entries
							SET inlineentrie_sortid=$second_sortid
							WHERE inlineentrie_id=$first_id";
						db_result($sql);
						$sql = "UPDATE " . DB_PREFIX . "inlinemenu_entries
							SET inlineentrie_sortid=$first_sortid
							WHERE inlineentrie_id=$second_id";
						db_result($sql);
						generateinlinemenu($second_entrie->inlineentrie_page_id);			
					}
				}
			}
			else if($action2 == 'delete') {
				$entrie_id = GetPostOrGet('entrie_id');
				$sure= GetPostOrGet('sure');
				$sql = "SELECT *
					FROM " . DB_PREFIX . "inlinemenu_entries
					WHERE inlineentrie_id=$entrie_id";
 				$entrie_result = db_result($sql);
 				if($entrie = mysql_fetch_object($entrie_result)) {
 					if($sure == 1) {
	 					$sql = "DELETE FROM " . DB_PREFIX . "inlinemenu_entries
							WHERE inlineentrie_id=$entrie_id";
 						db_result($sql);
	 					generateinlinemenu($entrie->inlineentrie_page_id);
 						header("Location: admin.php?page=pagestructure&action=inlinemenu&page_id=$page_id");
 					}
 					else {
						$out .= "Sind sie sicher das die das Element &quot;$entrie->inlineentrie_text&quot; unwiederruflich l�schen?<br />
							<a href=\"admin.php?page=pagestructure&amp;action=inlinemenu&amp;entrie_id=$entrie_id&amp;action2=delete&amp;sure=1&amp;page_id=$page_id\" class=\"button\">" . $admin_lang['yes'] . "</a >
							<a href=\"admin.php?page=pagestructure&amp;action=inlinemenu&amp;page_id=$page_id\"class=\"button\">" . $admin_lang['no'] . "</a >";
					}
 				}
			}
			else if($action2 == 'add_new_dialog') {
				$type = GetPostOrGet('type');
				if($type == '') {
					$out .= "<form action=\"admin.php\" method=\"post\">
						<input type=\"hidden\" name=\"page\" value=\"pagestructure\" />
						<input type=\"hidden\" name=\"action\" value=\"inlinemenu\" />
						<input type=\"hidden\" name=\"page_id\" value=\"$page_id\" />
						<input type=\"hidden\" name=\"action2\" value=\"add_new_dialog\" />
						
						<fieldset>
							<legend>Eintrags-Typ:</legend>
							<div class=\"row\">
								<label class=\"row\" for=\"group_name\">
									Link:
									<span class=\"info\">TODO</span>
								</label>
								<input type=\"radio\" name=\"type\" value=\"link\" checked=\"checked\"/>
							</div>
							<div class=\"row\">
								<label class=\"row\" for=\"group_name\">
									Text:
									<span class=\"info\">TODO</span>
								</label>
								<input type=\"radio\" name=\"type\" value=\"text\"/>
							</div>
							<div class=\"row\">
								<label class=\"row\" for=\"group_name\">
									Interner-Link:
									<span class=\"info\">TODO</span>
								</label>
								<input type=\"radio\" name=\"type\" value=\"intern\"/>
							</div>
							<div class=\"row\">
								<label class=\"row\" for=\"group_name\">
									Download:
									<span class=\"info\">TODO</span>
								</label>
								<input type=\"radio\" name=\"type\" value=\"download\"/>
							</div>
							<div class=\"row\">
								<input type=\"submit\" class=\"button\" value=\"Weiter\" />
							</div>
						</fieldset>
						</form>";
				}
				else if($type == 'link') {
					$out .= "<form action=\"admin.php\" method=\"post\">
						<input type=\"hidden\" name=\"page\" value=\"pagestructure\" />
						<input type=\"hidden\" name=\"action\" value=\"inlinemenu\" />
						<input type=\"hidden\" name=\"page_id\" value=\"$page_id\" />
						<input type=\"hidden\" name=\"action2\" value=\"add_new\" />
						<input type=\"hidden\" name=\"type\" value=\"link\" />
						<table>
						<tr><td>Link-Titel<span class=\"info\">Ein wenig Text der den Link deutlich macht.</span></td><td><input type=\"text\" name=\"text\" value=\"\" /></td></tr>
						<tr><td>Link<span class=\"info\">Hier kommt die URL hin die den Link sp&auml;ter ergibt.</span></td><td><input type=\"text\" name=\"link\" value=\"http://\" /></td></tr>
						<tr><td colspan=\"2\"><input type=\"reset\" class=\"button\" value=\"Zur&uuml;cksetzen\" /><input type=\"submit\" class=\"button\" value=\"Speichern\" /></td></tr>
						</table>
						</form>";
				}
				else if($type == 'text') {
					$out .= "<form action=\"admin.php\" method=\"post\">
						<input type=\"hidden\" name=\"page\" value=\"pagestructure\" />
						<input type=\"hidden\" name=\"action\" value=\"inlinemenu\" />
						<input type=\"hidden\" name=\"page_id\" value=\"$page_id\" />
						<input type=\"hidden\" name=\"action2\" value=\"add_new\" />
						<input type=\"hidden\" name=\"type\" value=\"text\" />
						<table>
						<tr><td>Text<span class=\"info\">Das ist der Text, der sp&auml;ter angezeigt werden soll</span></td><td><textarea name=\"text\"></textarea></td></tr>
						<tr><td colspan=\"2\"><input type=\"reset\" class=\"button\" value=\"Zur&uuml;cksetzen\" /><input type=\"submit\" class=\"button\" value=\"Speichern\" /></td></tr>
						</table>
						</form>";
				}
				else if($type == 'intern') {
					$out .= "<form action=\"admin.php\" method=\"post\">
						<input type=\"hidden\" name=\"page\" value=\"pagestructure\" />
						<input type=\"hidden\" name=\"action\" value=\"inlinemenu\" />
						<input type=\"hidden\" name=\"page_id\" value=\"$page_id\" />
						<input type=\"hidden\" name=\"action2\" value=\"add_new\" />
						<input type=\"hidden\" name=\"type\" value=\"intern\" />
						<table>
						<tr><td>Link-Titel<span class=\"info\">Ein wenig Text der den Link deutlich macht.</span></td><td><input type=\"text\" name=\"text\" value=\"\" /></td></tr>
						<tr><td>Interne Seite<span class=\"info\">Das ist die interne Seite, auf die der Link sp&aumlter f&uuml;hren soll.</span></td><td><select name=\"link\">";
			$sql = "SELECT page_name, page_title
				FROM " . DB_PREFIX . "pages
				ORDER BY page_title ASC";
			$pages_result = db_result($sql);
			while($page = mysql_fetch_object($pages_result)) {
				$out .= "\t\t\t\t<option value=\"$page->page_name\">$page->page_title($page->page_name)</option>\r\n";
			}
			
			$out .= "</select></td></tr>
						<tr><td colspan=\"2\"><input type=\"reset\" class=\"button\" value=\"Zur?cksetzen\" /><input type=\"submit\" class=\"button\" value=\"Speichern\" /></td></tr>
						</table>
						</form>";
				}
				else if($type == 'download') {
					$out .= "<form action=\"admin.php\" method=\"post\">
						<input type=\"hidden\" name=\"page\" value=\"pagestructure\" />
						<input type=\"hidden\" name=\"action\" value=\"inlinemenu\" />
						<input type=\"hidden\" name=\"page_id\" value=\"$page_id\" />
						<input type=\"hidden\" name=\"action2\" value=\"add_new\" />
						<input type=\"hidden\" name=\"type\" value=\"download\" />
						<fieldset>
							<legend>Download Hinzuf&uuml;gen</legend>
						
						<div class=\"row\">
							<label class=\"row\" for=\"download_text\">
								Download-Titel:
								<span class=\"info\">Der Text wird als Downloadlink angezeigt er kann zum Beispiel der Dateiname sein, aber auch ein kuzer eindeutiger Text ist sehr sinnvoll.</span>
							</label>
							<input type=\"text\" name=\"text\" id=\"download_text\" />
						</div>
						<div class=\"row\">
							<label class=\"row\" for=\"link\">
								Datei f�r den Download:
								<span class=\"info\">Die hier angegebene Datei kann dann sp&auml;ter heruntergeladen werden.</span>
							</label>
							<select name=\"link\" id=\"link\">";
					$sql = "SELECT *
						FROM " . DB_PREFIX . "files
						ORDER BY file_name";
					$files_result = db_result($sql);
					while($file = mysql_fetch_object($files_result)) {
						if(file_exists($file->file_path))
							$out .= "<option value=\"$file->file_id\">". utf8_encode($file->file_name) . " (" . kbormb($file->file_size) . ")</option>\r\n";
					}			
					$out .= "</select>
							</div>
							<div class=\"row\"><input type=\"reset\" class=\"button\" value=\"Zur&uuml;cksetzen\" /><input type=\"submit\" class=\"button\" value=\"Speichern\" /></div>
						</fieldset>
						</form>";
				}
				else
					$action2 = '';
			}
			else if($action2 == 'add_new') {
				$sql = "SELECT inlineentrie_sortid
			 	FROM " . DB_PREFIX . "inlinemenu_entries
			 	WHERE inlineentrie_page_id = $page_id
			 	ORDER BY inlineentrie_sortid DESC
			 	LIMIT 0,1";
				$lastsort_result = db_result($sql);
				$sortid = 1;
				if($lastsort = mysql_fetch_object($lastsort_result)){
					$sortid = $lastsort->inlineentrie_sortid;
					$sortid++;
				}
				$text = GetPostOrGet('text');
				$type = GetPostOrGet('type');
				$link = GetPostOrGet('link');
				
				$sql = '';
				switch ($type) {
					case 'link':		$sql = "INSERT INTO " . DB_PREFIX . "inlinemenu_entries (inlineentrie_sortid, inlineentrie_page_id, inlinieentrie_type, inlineentrie_text, inlineentrie_link)
									VALUES ($sortid, $page_id, 'link', '$text','$link');";
								break;
					case 'intern':		$link = "index.php?page=$link";
 								$sql = "INSERT INTO " . DB_PREFIX . "inlinemenu_entries (inlineentrie_sortid, inlineentrie_page_id, inlinieentrie_type, inlineentrie_text, inlineentrie_link)
									VALUES ($sortid, $page_id, 'intern', '$text','$link');";
								break;
					case 'text':		$sql = "INSERT INTO " . DB_PREFIX . "inlinemenu_entries (inlineentrie_sortid, inlineentrie_page_id, inlinieentrie_type, inlineentrie_text)
									VALUES ($sortid, $page_id, 'text', '$text');";
								break;
					case 'download':	$sql = "INSERT INTO " . DB_PREFIX . "inlinemenu_entries (inlineentrie_sortid, inlineentrie_page_id, inlinieentrie_type, inlineentrie_text, inlineentrie_link)
									VALUES ($sortid, $page_id, 'download', '$text','$link');";
								break;
				
					default:		break;
				}
				if($sql != '') {
	 				db_result($sql);
 					generateinlinemenu($page_id);
 				}
			}
			
			$hide = array('select_image', 'delete', 'add_new_dialog');
			if(!in_array($action2, $hide)) {
				$image = 'Noch kein bild gesetzt';

				if(file_exists($inline->inlinemenu_image_thumb))
					$image = "<img src=\"" . generateUrl($inline->inlinemenu_image_thumb) . "\"/>";
				else {
					$imgmax2 = 200;
					$inlinemenuFolder = 'data/thumbnails/';
					$thumbnail = resizteImageToWidth($inline->inlinemenu_image, $inlinemenuFolder, $imgmax2);
					if($thumbnail !== false){
						$image = "<img src=\"" . generateUrl($thumbnail) . "\"/>";
					}
						
				}	
				$out .= "
				<fieldset>
					<legend>" . $admin_lang['inlinemenu'] . "</legend>
				<div class=\"row\">
						<label class=\"row\">
							Bild f&uuml;r das Zusatzmen&uuml;
							<span class=\"info\">Das ist der Pfad zu dem Bild, das dem Zusatzmen&uuml; zugeordnet wird, es kann der Einfachheit halber aus den bereits hochgeladenen Bildern ausgew&auml;hlt werden.</span>
						</label>
						$image
				</div>
				<div class=\"row\">
					<a href=\"admin.php?page=pagestructure&amp;action=inlinemenu&amp;page_id=$page_id&amp;action2=select_image\" class=\"button\">Bild ausw&auml;hlen/ver&auml;ndern</a>
					" .((file_exists($inline->inlinemenu_image_thumb)) ?  "<a href=\"admin.php?page=pagestructure&amp;action=inlinemenu&amp;page_id=$page_id&amp;action2=remove_image\" class=\"button\">Bild entfernen</a>" : '') . "
				</div>
				<form action=\"admin.php\" method=\"post\">
				<input type=\"hidden\" name=\"page\" value=\"pagestructure\" />
				<input type=\"hidden\" name=\"action\" value=\"inlinemenu\" />
				<input type=\"hidden\" name=\"page_id\" value=\"$page_id\" />
				<input type=\"hidden\" name=\"action2\" value=\"set_thumb_title\" />
				<div class=\"row\">
					<label for=\"inlinemenu_thumb_title\"class=\"row\">
						Bildunterschrift
						<span class=\"info\">Die Bildunterschrift kann das Bild noch ein wenig erl&auml;utern.</span>
					</label>
					<input id=\"inlinemenu_thumb_title\" name=\"thumb_title\" type=\"text\" value=\"$inline->inlinemenu_image_title\" />
				</div>
				<div>
					<input type=\"submit\" class=\"button\" value=\"" . $admin_lang['save'] . "\" />
				</div>
				</form>";
				$sql = "SELECT *
					FROM " . DB_PREFIX . "inlinemenu_entries
					WHERE inlineentrie_page_id = $page_id
					ORDER BY inlineentrie_sortid ASC";
				$entries_result = db_result($sql);
				$out .= "<table class=\"text_table noform\">
					<thead><tr><th>Text</th><th>Typ</th><th>Aktion</th></tr></thead>";
				while($entrie = mysql_fetch_object($entries_result)) {
					$out .= "<tr>
					<td>". nl2br($entrie->inlineentrie_text) ."</td>
					<td>$entrie->inlinieentrie_type</td>
					<td>
						<a href=\"admin.php?page=pagestructure&amp;action=inlinemenu&amp;page_id=$page_id&amp;entrie_id=$entrie->inlineentrie_id&amp;action2=up\"><img src=\"./img/up.png\" alt=\"" . $admin_lang['move_up'] ."\" title=\"" . $admin_lang['move_up'] ."\" /></a>
						<a href=\"admin.php?page=pagestructure&amp;action=inlinemenu&amp;page_id=$page_id&amp;entrie_id=$entrie->inlineentrie_id&amp;action2=down\"><img src=\"./img/down.png\" alt=\"" . $admin_lang['move_down'] ."\" title=\"" . $admin_lang['move_down'] ."\" /></a>
						<a href=\"admin.php?page=pagestructure&amp;action=inlinemenu&amp;page_id=$page_id&amp;entrie_id=$entrie->inlineentrie_id&amp;action2=delete\"><img src=\"./img/del.png\" alt=\"" . $admin_lang['delete'] ."\" title=\"" . $admin_lang['delete'] ."\" /></a>
						<!--<img src=\"./img/edit.png\" alt=\"Bearbeiten\" title=\"Bearbeiten\" />-->
					</td>
					</tr>";
				}
				$out .= "</table>
					<div class=\"row\"><a href=\"admin.php?page=pagestructure&amp;action=inlinemenu&amp;page_id=$page_id&amp;action2=add_new_dialog\" class=\"button\">Einen Eintrag hinzuf&uuml;gen</a></div>
					</fieldset>";
			}
			return $out;
		}
		
		function _infoPage() {
			global $admin_lang, $user;
			
			$page_id =  GetPostOrGet('page_id');
			$action2 = GetPostOrGet('action2');
			if($action2 == 'save_path' || $action2 == 'save_access') {
				$page_access = GetPostOrGet('page_access');
				$page_access_old = GetPostOrGet('page_access_old');
				$page_parent_id = GetPostOrGet('page_parent_id');
				$page_parent_id_old = GetPostOrGet('page_parent_id_old');
				if(($action2 == 'save_access' && $page_access_old != $page_access) || ($action2 == 'save_path' && $page_id != $page_parent_id && $page_parent_id != $page_parent_id_old)) {
					$sql = "SELECT struct.*, text.*
						FROM ( " . DB_PREFIX. "pages struct
						LEFT JOIN " . DB_PREFIX . "pages_text text ON text.page_id = struct.page_id )
						WHERE struct.page_id='$page_id' AND struct.page_type='text'";
					$page_result = db_result($sql);
					if($page = mysql_fetch_object($page_result)) {
						
						$sql = "INSERT INTO " . DB_PREFIX . "pages_history (page_id, page_type, page_name, page_title, page_parent_id, page_lang, page_creator, page_date, page_edit_comment)
							VALUES($page->page_id, '$page->page_type', '$page->page_name', '$page->page_title', $page->page_parent_id, '$page->page_lang', $page->page_creator, $page->page_date, '$page->page_edit_comment')";
						db_result($sql);
						$lastid = mysql_insert_id();
						
						$sql = "INSERT INTO " . DB_PREFIX . "pages_text_history (page_id, text_page_text)
							VALUES ($lastid, '$page->text_page_text')";
						
						}
					
					if($action2 == 'save_path') {
						
							if(is_numeric($page_parent_id) && is_numeric($page_id)) {
							$sql = "UPDATE " . DB_PREFIX . "pages
	 							SET page_parent_id=$page_parent_id, page_creator='$user->ID', page_date='" . mktime() . "',  page_edit_comment = 'Changed ParentID form $page_parent_id_old to $page_parent_id'
 								WHERE page_id = $page_id";
 								db_result($sql);
							}
						}
					else if($action2 == 'save_access') {
						
						$sql = "UPDATE " . DB_PREFIX . "pages
							SET  page_access='$page_access', page_creator='$user->ID', page_date='" . mktime() . "',  page_edit_comment = 'Changed Page-Access from $page_access_old to $page_access'
							WHERE page_id='$page_id'";
						db_result($sql);
					}
				}
			}	
			$out = '';
			$sql = "SELECT *
				FROM " . DB_PREFIX . "pages
				WHERE page_id=$page_id";
			$page_result = db_result($sql);
			if($page = mysql_fetch_object($page_result)) {
				$out .= "\t\t\t<table class=\"text_table\">
				<tr>
					<th>Titel</th>
					<td>$page->page_title</td>
				</tr>
				<tr>
					<th>Name</th>
					<td>$page->page_name</td>
				</tr>
				<tr>
					<th>Typ</th>
					<td>$page->page_type</td>
				</tr>
				<tr>
					<th>Zugang</th>
					<td>";
					if(GetPostOrGet('action2') == 'change_access') {
						$out .= "<form action=\"admin.php\" method=\"post\">
							<input type=\"hidden\" name=\"page\" value=\"pagestructure\" />
							<input type=\"hidden\" name=\"action\" value=\"info\" />
							<input type=\"hidden\" name=\"page_id\" value=\"$page->page_id\" />
							<input type=\"hidden\" name=\"page_access_old\" value=\"$page->page_access\" />
							<input type=\"hidden\" name=\"action2\" value=\"save_access\" />
							<select name=\"page_access\">
								<option value=\"public\"" . (($page->page_access == 'public') ? ' selected="selected"' : '') . ">" . $admin_lang['public'] . "</option>
								<option value=\"private\"" . (($page->page_access == 'private') ? ' selected="selected"' : '') . ">" . $admin_lang['private'] . "</option>
								<option value=\"hidden\"" . (($page->page_access == 'hidden') ? ' selected="selected"' : '') . ">" . $admin_lang['hidden'] . "</option>
								<option value=\"deleted\"" . (($page->page_access == 'deleted') ? ' selected="selected"' : '') . ">" . $admin_lang['deleted'] . "</option>
							</select>
							<input type=\"submit\" value=\"" . $admin_lang['save'] . "\" class=\"button\" />
							</form>";
					}
					else
						$out .=	$admin_lang[$page->page_access] . " <a href=\"admin.php?page=pagestructure&amp;action=info&amp;page_id=$page->page_id&amp;action2=change_access\"><img src=\"./img/edit.png\" height=\"16\" width=\"16\" alt=\"" . $admin_lang['edit'] . "\" title=\"" . $admin_lang['edit'] . "\"/></a>";
					$out .= "</td>
				</tr>
				<tr>
					<th>Sprache</th>
					<td>" . $admin_lang[$page->page_lang] . "</td>
				</tr>
				<tr>
					";
				if(GetPostOrGet('action2') == 'change_path') {
					$out .= "<th>Unterseite von</th>
					<td><form action=\"admin.php\" method=\"post\">
							<input type=\"hidden\" name=\"page\" value=\"pagestructure\" />
							<input type=\"hidden\" name=\"action\" value=\"info\" />
							<input type=\"hidden\" name=\"page_id\" value=\"$page->page_id\" />
							<input type=\"hidden\" name=\"action2\" value=\"save_path\" />
							<input type=\"hidden\" name=\"page_parent_id_old\" value=\"$page->page_parent_id\" />
							<select name=\"page_parent_id\">
								<option value=\"0\">Keiner</option>\r\n";
					$out .= $this->_structurePullDown(0,0,'', $page->page_id, $page->page_parent_id);
					$out .= "\t\t\t\t\t\t\t</select><input type=\"submit\" value=\"" . $admin_lang['save'] . "\" class=\"button\" /></form>";
				}
				else
					$out .= "<th>Pfad</th>
					<td>
						<a href=\"admin.php?page=pagestructure\">root</a><strong>/</strong>" . $this->_pagePath($page->page_id) . " <a href=\"admin.php?page=pagestructure&amp;action=info&amp;page_id=$page->page_id&amp;action2=change_path\"><img src=\"./img/edit.png\" height=\"16\" width=\"16\" alt=\"" . $admin_lang['edit'] . "\" title=\"" . $admin_lang['edit'] . "\"/></a>";
				$out .= "</td>
				</tr>
				<tr>
					<th>Bearbeitet von</th>
					<td>" . getUserById($page->page_creator) . "</td>
				</tr>
				<tr>
					<th>Bearbeitet am</th>
					<td>" . date("d.m.Y H:i:s",$page->page_date) . "</td>
				</tr>
			</table>\r\n";
				
				$sql = "SELECT *
					FROM " . DB_PREFIX . "pages
					WHERE page_parent_id = $page_id
					ORDER BY page_date DESC";
				$subpages_c_result = db_result($sql);
				$subpages_count = mysql_num_rows($subpages_c_result);
				if($subpages_count != 0) {
					$out .="\t\t\t<h4>Unterseiten</h4><hr />\r\n";
					$out .= $this->_showStructure($page->page_id);
				}
				$sql = "SELECT *
					FROM " . DB_PREFIX . "pages_history
					WHERE page_id = $page_id
					ORDER BY page_date DESC";
				$result = db_result($sql);
				$changes_count = mysql_num_rows($result);
				$out .="\t\t\t<h4>Ver&auml;nderungen($changes_count)</h4><hr />
			<table class=\"page_commits\">
				<thead>
					<tr>
						<td>Datum</td>
						<td>Ver&auml;nderer</td>
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
				
				$out .= "\t\t\t</table>";
				
			}
			
			return $out;
		}
		
 	}
