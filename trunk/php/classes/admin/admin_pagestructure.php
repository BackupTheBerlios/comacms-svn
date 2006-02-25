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
 	require_once('./classes/admin/admin.php');
 	
	/**
	 * @package ComaCMS 
	 */
 	class Admin_PageStructure extends Admin{
 		
 		/**
 		 * All pageIDs of the pages which are in the main-menu
 		 * @var array
 		 */
 		var $MenuPageIDs;
 		
		/**
		 * PageStructure functions
		 * @var PageStructure
		 */
 		var $_PageStructure;
 		/**
 		 * The current User
 		 * @var User;
 		 */
 		var $_User;
 		/**
 		 * A Config-link
 		 * @var Config
 		 */
 		var $_Config;
 		
 		/**
 		 * @param SqlConnection SqlConnection
 		 * @param array AdminLang
 		 * @return void
 		 */
 		function Admin_PageStructure(&$SqlConnection, &$AdminLang, &$User, &$Config) {
			$this->_SqlConnection = &$SqlConnection;
			$this->_AdminLang = &$AdminLang;
			$this->_User = &$User;
			$this->_Config = &$Config;
			$this->_PageStructure = new PageStructure($this->_SqlConnection, $this->_User);
		}
 		
 		/**
 		 * @return string
 		 * @param string Action
 		 */
		 function GetPage($Action = '') {
		 	$adminLang = &$this->_AdminLang;
		 	$out = '';
			
			if($Action != 'internHome')
				$out .= "\t\t\t<h2>" . $adminLang['pagestructure'] . "</h2>\r\n";
		 	switch ($Action) {
		 		case 'deletePage':	$out .= $this->_deletePage();
		 					break;
		 		case 'pageInfo':	$out .= $this->_infoPage();
		 					break;
		 		case 'newPage':		$out .= $this->_newPage();
		 					break;
		 		case 'addNewPage':	$out .= $this->_addPage();
		 					break;
		 		case 'editPage':	$out .= $this->_editPage();
							break;
				case 'savePage':	$out .= $this->_savePage();
							break;
				case 'generateMenu':	$out .= $this->_generate_menu();
							break;
				case 'pageInlineMenu':	$out .= $this->_inlineMenu();
							break;
		 		default:		$out .= $this->_homePage();
		 	}
			return $out;
		 }
		 
		 /**
		  * @return string
		  */
		 function _generate_menu() {
		 	$pages = GetPostOrGet('mainMenuPages');
		 	// Clear the main-menu
		 	$this->_PageStructure->ClearMenu(1);
		 	// Insert the pages to the main-menu
		 	$this->_PageStructure->GenerateMenu($pages, 1);
		 	// Print out the default view
		 	return $this->GetPage('internHome');
		 }
		 
		 function _deletePage() {
		 	$adminLang = &$this->_AdminLang;
		 	$confirmation = GetPostOrGet('confirmation');
		 	$pageID = GetPostOrGet('pageID');
		 	if(!is_numeric($pageID))
		 		return $this->GetPage('internHome');
		 	if($this->_PageStructure->PageExists($pageID)) {
		 		if($confirmation == 1) {
		 			$this->_PageStructure->SetPageDeleted($pageID);
		 			return $this->GetPage('internHome');
		 		}
		 		else if($confirmation == 2) {
		 			$newParentPageID = GetPostOrGet('newParentPageID');
		 			$action2 = GetPostOrGet('action2');
		 			if($action2 == 'move') {
		 				if(!is_numeric($newParentPageID))
		 					return $this->GetPage('internHome');
		 				$this->_PageStructure->MoveSubPagesFromTo($pageID, $newParentPageID);
		 				$this->_PageStructure->SetPageDeleted($pageID);
		 			}
		 			else if($action2 == 'deleteAll') {
		 				$this->_PageStructure->SetSubPagesDeleted($pageID);
		 				$this->_PageStructure->SetPageDeleted($pageID);	
		 			}
		 			return $this->GetPage('internHome');
		 		}
		 		else {
		 			$out = '';
		 			if ($this->_PageStructure->PageHasSubPages($pageID, false)) {
		 				$out .= "<fieldset>
		 						<legend>Unterseiten vorhanden</legend>
		 						<form action=\"admin.php\" method=\"post\">
		 							<input type=\"hidden\" name=\"page\" value=\"pagestructure\" />
		 							<input type=\"hidden\" name=\"action\" value=\"deletePage\" />
		 							<input type=\"hidden\" name=\"pageID\" value=\"$pageID\" />
		 							<input type=\"hidden\" name=\"confirmation\" value=\"2\" />
		 							<div class=\"row error\">
		 								Diese Seite besitzt eine oder mehrere Unterseiten, wie möchten sie mit diesen verfahren?
		 							</div>
		 							<div class=\"row\">
		 								<label for=\"action2\">Aktion:
		 									<span class=\"info\">...</span>
		 								</label>
		 								<select id=\"action2\" name=\"action2\">
		 									<option value=\"move\">Alle Unterseiten verschieben</option>
		 									<option value=\"deleteAll\">Alle Unterseiten löschen.</option>
		 									<option value=\"nothing\">Alles beim Alten belassen</option>
		 								</select>
		 							</div>
		 							<div class=\"row\">
		 								<label for=\"newParentPageID\">Neue Elternseite:
		 									<span class=\"info\">...</span>
		 								</label>
		 							<select id=\"newParentPageID\" name=\"newParentPageID\">";
		 				$out .= $this->_structurePullDown(0, 0, '', $pageID, $pageID);
		 				$out .= "</select>
		 							</div>
		 							<div class=\"row error\">
		 								Mit dem Klicken auf OK wird die Aktion sofort durchgeführt und nicht noch einmal hinterfragt!
		 							</div>
		 							<div class=\"row\">
		 								<a href=\"admin.php?page=pagestructure\" class=\"button\">" . $adminLang['back'] . "</a>
		 								<input type=\"submit\" class=\"button\" value=\"" . $adminLang['ok'] . "\"/>
		 							</div>	
		 						</form>
		 					</fieldset>";
		 			}
		 			else {
		 				$out .= sprintf($adminLang['Do you really want to delete the page %page_title%?'], $this->_PageStructure->GetPageData($pageID, 'title')) . "<br />
		 				<a href=\"admin.php?page=pagestructure&amp;action=deletePage&amp;pageID=$pageID&amp;confirmation=1\" class=\"button\">" . $adminLang['yes'] . "</a>
		 					<a href=\"admin.php?page=pagestructure\" class=\"button\">" . $adminLang['no'] . "</a>";
		 			}
		 			return $out;
		 		}
		 	}
		 	else
		 		return $this->GetPage('internHome');
		 }
		 
		 function _getMenuPageIDs() {
		 	$this->MenuPageIDs = array();
		 	$sql = "SELECT menu_page_id
		 		FROM " . DB_PREFIX . "menu
		 		WHERE menu_menuid=1";
		 	$ids_result = db_result($sql);
		 	while($id = mysql_fetch_object($ids_result))
		 		$this->MenuPageIDs[] = $id->menu_page_id;
		 }
		 
		 function _homePage() {
		 	$adminLang = &$this->_AdminLang;
		 	$this->_getMenuPageIDs();
		 	$out = "\t\t\t<script type=\"text/javascript\" language=\"JavaScript\" src=\"system/functions.js\"></script>
			<a href=\"admin.php?page=pagestructure&amp;action=newPage\" class=\"button\">" . $adminLang['create_new_page'] . "</a>
			<form method=\"post\" action=\"admin.php\">
				<input type=\"hidden\" name=\"page\" value=\"pagestructure\" />
				<input type=\"hidden\" name=\"action\" value=\"generateMenu\" />\r\n";
		 	$out .= $this->_showStructure(0);
		 	$out .= "\t\t\t\t<input type=\"submit\" class=\"button\" value=\"" . $adminLang['generate_mainmenu'] . "\" />
			</form>
			<script type=\"text/javascript\" language=\"JavaScript\">
				SetHover('span', 'structure_row', 'structure_row_hover', function additional() {document.getElementById('menu').className = '';});
			</script>";
			return $out;
		 }
		 
		 function _newPage() {
		 	$adminLang = &$this->_AdminLang;
		 	$this->_PageStructure->LoadParentIDs();
				 	
		 	$out = "\t\t\t<form method=\"post\" action=\"admin.php\">
				<fieldset>
					<legend>" . $adminLang['new_page'] . "</legend>
					<input type=\"hidden\" name=\"page\" value=\"pagestructure\" />
					<input type=\"hidden\" name=\"action\" value=\"addNewPage\" />
					<div class=\"row\">
						<label>
							" . $adminLang['name/contraction'] . ":
							<span class=\"info\">Mit diesem K&uuml;rzel wird auf die Seite zugegriffen und dient es zur eindeutigen Identifizierung der Seite.</span>
						</label>
						<input type=\"text\" name=\"pageName\" maxlength=\"20\" />
					</div>
					<div class=\"row\">
						<label>
							" . $adminLang['title'] . ":
							<span class=\"info\">Der Titel wird sp&auml;ter in der Titelleiste des Browsers angezeigt.</span>
						</label>
						<input type=\"text\" name=\"pageTitle\" maxlength=\"100\" />
					</div>
					<div class=\"row\">
						<label>
							Seiten-Typ:
							<span class=\"info\">TODO</span>
						</label>
						<select name=\"pageType\">
							<option value=\"text\">Text</option>
							<option value=\"gallery\">" . $adminLang['gallery'] ."</option>
						</select>
					</div>
					<div class=\"row\">
						<label>
							" . $adminLang['language'] . ":
							<span class=\"info\">Der Text soll in der gew&auml;hlten Sprache geschrieben werden.</span>
						</label>
						<select name=\"pageLang\">
							<option value=\"de\">Deutsch</option>
							<option value=\"en\">Englisch</option>
						</select>
					</div>
					<div class=\"row\">
						<label>
							Zugang:
							<span class=\"info\">Wer soll sich die Seite sp&auml;ter anschauen k&ouml;nnen?<br />
							Jeder (&Ouml;ffentlich), nur ausgew&auml;hlte Benutzer (privat) oder soll die Seite nur erstellt werden um sie sp&auml;ter zu ver&ouml;ffentlichen (versteckt)?</span>
						</label>
						<select name=\"pageAccess\">
							<option value=\"public\">&Ouml;ffentlich</option>
							<option value=\"private\">Privat</option>
							<option value=\"hidden\">Versteckt</option>
						</select>
					</div>
					<div class=\"row\">
						<label>
							Unterseite von:
							<span class=\"info\">TODO</span>
						</label>
						<select name=\"pageParentID\">
							<option value=\"0\">Keiner</option>\r\n";
		 	$out .= $this->_PageStructure->PageStructurePulldown();
		 	$out .=	"\t\t\t\t\t\t</select>
					</div>
					<div class=\"row\">
						<label>
							Kommentar
							<span class=\"info\">Eine kurze Beschreibung, was hier gemacht wurde.</span>
						</label>
						<input type=\"text\" name=\"pageEditComment\" maxlength=\"100\" value=\"" . $adminLang['created_new_page'] . "\"/>
					</div>
					<div class=\"row\">
						<label>
							Bearbeiten?
							<span class=\"info\">Soll die Seite nach dem Erstellen bearbeitet werden oder soll wieder auf die &Uuml;bersichtseite zur&uuml;ckgekehrt werden?</span>
						</label>
						<input type=\"checkbox\" name=\"pageEdit\" value=\"edit\" checked=\"true\" class=\"checkbox\"/>
					</div>
					<div class=\"row\">
						<input type=\"reset\" class=\"button\" value=\"" . $adminLang['reset'] . "\" />&nbsp;
						<input type=\"submit\" class=\"button\" value=\"" . $adminLang['create'] . "\" />
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
		 
		 function _Structure($TopNodeID = 0) {
		 	$adminLang = $this->_AdminLang;
		 	$out = '';
		 	if(empty($this->_PageStructure->_ParentIDPages[$TopNodeID]))
		 		return;
		 	$out .= "\r\n\t\t\t<ol>\r\n";
		 	foreach($this->_PageStructure->_ParentIDPages[$TopNodeID] as $page) {
	 			$out .= "\t\t\t\t<li class=\"page_type_". $page['type'] . (($page['access'] == 'deleted') ? ' strike' : '' ). "\"><span class=\"structure_row\">" . (($TopNodeID == 0) ?  "<input type=\"checkbox\" name=\"mainMenuPages[]\"" . ((in_array($page['id'], $this->MenuPageIDs)) ? ' checked="checked"'  : '') . (($page['access'] != 'public') ? ' disabled="disabled"'  : '') . " value=\"" . $page['id'] . "\" class=\"checkbox\"/>\t" : '' );
	 			$out .= "<strong>" . $page['title'] . "</strong> (" . $page['name'] . ")";
		 			$out .= "<span class=\"page_lang\">[" . $adminLang[$page['lang']] . "]</span><span class=\"page_actions\">";
		 			// edit:
		 			if($page['access'] != 'deleted')
		 				$out .= " <a href=\"admin.php?page=pagestructure&amp;action=editPage&amp;pageID=" . $page['id'] . "\"><img src=\"./img/edit.png\" class=\"icon\" height=\"16\" width=\"16\" alt=\"" . $adminLang['edit'] . "\" title=\"" . $adminLang['edit'] . "\"/></a>";
		 			// info:
		 			$out .= " <a href=\"admin.php?page=pagestructure&amp;action=pageInfo&amp;pageID=" . $page['id'] . "\"><img src=\"./img/info.png\" class=\"icon\" height=\"16\" width=\"16\" alt=\"" . $adminLang['info'] . "\" title=\"" . $adminLang['info'] . "\"/></a>";
		 			// view:
		 			if($page['access'] != 'deleted')
		 				$out .= " <a href=\"index.php?page=" . $page['name'] . "\"><img src=\"./img/view.png\" class=\"icon\" height=\"16\" width=\"16\" alt=\"Anschauen " . $page['title'] . "\" title=\"Anschauen\"/></a>";
		 			// inlinemenu:
		 			if($page['access'] != 'deleted')
		 					$out .= " <a href=\"admin.php?page=pagestructure&amp;action=pageInlineMenu&amp;pageID=" . $page['id'] . "\" title=\"" . sprintf($adminLang['edit_inlinemenu_of_%page_title%'], $page['title']) . "\"><img src=\"./img/inlinemenu.png\" class=\"icon\" height=\"16\" width=\"16\" alt=\"" . sprintf($adminLang['edit_inlinemenu_of_%page_title%'], $page['title']) . "\" title=\"" . sprintf($adminLang['edit_inlinemenu_of_%page_title%'], $page['title']) . "\"/></a>";
		 			// delete:
		 			if($page['access'] != 'deleted')
		 				$out .= " <a href=\"admin.php?page=pagestructure&amp;action=deletePage&amp;pageID=" . $page['id'] . "\"><img src=\"./img/del.png\" class=\"icon\" height=\"16\" width=\"16\" alt=\"" . sprintf($adminLang['delete_page_%page_title%'], $page['title']) . "\" title=\"" . sprintf($adminLang['delete_page_%page_title%'], $page['title']) . "\"/></a>";
		 			$out .= '</span></span>' . $this->_Structure($page['id']);
		 			$out .= "\t\t\t\t</li>\r\n";
		 	}
		 	$out .= "\r\n\t\t\t</ol>\r\n\r\n";
		 	return $out;
		 }
		 
		 function _showStructure($TopNodeID = 0) {
			$this->_PageStructure->LoadParentIDs();
	 		$out = $this->_Structure($TopNodeID);
			return $out;
		}
		
		function _editPage() {
			$page_id = GetPostOrGet('pageID');
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
			$pageID = GetPostOrGet('pageID');
			$out = '';
			$sql = "SELECT page_id, page_type
				FROM " . DB_PREFIX . "pages
				WHERE page_id = $pageID";
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
			
			$user = &$this->_User;
			$page_access = GetPostOrGet('pageAccess');
			$page_edit = GetPostOrGet('pageEdit');
			$page_edit_comment = GetPostOrGet('pageEditComment');
			$page_lang = GetPostOrGet('pageLang');
			$page_name = GetPostOrGet('pageName');
			$page_parent_id = GetPostOrGet('pageParentID');
			$page_title = GetPostOrGet('pageTitle');
			$page_type = GetPostOrGet('pageType');
						
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
						VALUES('$page_lang', '$page_access', '$page_name', '$page_title', $page_parent_id, $user->ID, '$page_type', " . mktime() . ", '$page_edit_comment')";
					db_result($sql);
					$lastid = mysql_insert_id();
					$edit->NewPage($lastid);
				}
			}
			if($page_edit != '')
				header("Location: admin.php?page=pagestructure&action=editPage&pageID=$lastid");
			else
				header("Location: admin.php?page=pagestructure");
	
		}
		
		function _pagePath($PageID = 0) {
			$out = '';
			$sql = "SELECT *
				FROM " . DB_PREFIX . "pages
				WHERE page_id=$PageID";
			$page_result = db_result($sql);
			while($page = mysql_fetch_object($page_result)) {
				if($PageID == $page->page_id)
					$out = " <span title=\"$page->page_title\">$page->page_name</span>";
				else
					$out = "<a href=\"admin.php?page=pagestructure&amp;action=pageInfo&amp;pageID=$page->page_id\" title=\"$page->page_title\">$page->page_name</a>" . $out;
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
			$adminLang = &$this->_AdminLang;
			// Get data from header
			$pageID = GetPostOrGet('pageID');
			$action = GetPostOrGet('action2');
			
			// The ID of the page must(!) be an numeric value!
			if(!is_numeric($pageID))
				return $this->_homePage();
			
			$out = '';
			if(!$this->_PageStructure->InlineMenuExists($pageID)) {
				if($action == 'create') {
					$this->_PageStructure->CreateInlineMenu($pageID);
					$out .= $this->_inlineMenu();
				}
				else {
					$out .= $adminLang['at_the_moment_there_is_no_inlinemenu_for_this_page_created,_should_this_be_done_now'] . "<br />
					<a href=\"admin.php?page=pagestructure&amp;action=pageInlineMenu&amp;action2=create&amp;pageID=$pageID\" title=\"" . $adminLang['yes'] . "\" class=\"button\">" . $adminLang['yes'] . "</a>
					<a href=\"admin.php?page=pagestructure\" title=\"" . $adminLang['no'] . "\" class=\"button\">" . $adminLang['no'] . "</a>";	
				}
				
			}
			else {
				switch ($action) {
					case 'selectImage':	$out .= $this->_InlineMenuSelectImage($pageID);
								break;
					case 'setImage':	$out .= $this->_InlineMenuSetImagePage($pageID);
								break;
					case 'removeImage':	$out .= $this->_InlineMenuRemoveImagePage($pageID);
								break;
					case 'setImageTitle':	$out .= $this->_InlineMenuSetImageTitlePage($pageID);
								break;
					case 'addNewEntryDialog':	$out .= $this->_InlineMenuAddNewEntryDialogPage($pageID);
								break;
					case 'addNewEntry':	$out .= $this->_InlineMenuAddNewEntryPage($pageID); 
								break;
					case 'moveEntryUp':	$out .= $this->_InlineMenuMoveUpPage($pageID);
								break;
					case 'moveEntryDown':	$out .= $this->_InlineMenuMoveDownPage($pageID);
								break;
					case 'removeEntry':	$out .= $this->_InlineMenuRemoveEntryPage($pageID);
								break;
					case 'editEntry':	$out .= $this->_InlineMenuEditEntryPage($pageID);
								break;
					case 'saveEntry':	$out .= $this->_InlineMenuSaveEntryPage($pageID);
								break;
					default:		$out .= $this->_InlineMenuHomePage($pageID);
								break;
				}
			}
			return $out;
		}
		
		function _InlineMenuEditEntryPage($PageID) {
			$adminLang = &$this->_AdminLang;
			$entryID = GetPostOrGet('entryID');
			if($entryData = $this->_PageStructure->LoadInlineMenuEntry($entryID)) {
				$out = '';
				
				if($entryData['type'] == 'link') {
					$out .= "<form action=\"admin.php\" method=\"post\">
						<input type=\"hidden\" name=\"page\" value=\"pagestructure\" />
						<input type=\"hidden\" name=\"action\" value=\"pageInlineMenu\" />
						<input type=\"hidden\" name=\"pageID\" value=\"$PageID\" />
						<input type=\"hidden\" name=\"entryID\" value=\"$entryID\" />
						<input type=\"hidden\" name=\"action2\" value=\"saveEntry\" />
						<input type=\"hidden\" name=\"type\" value=\"link\" />
						<fieldset>
						<legend>Erstelle neuen InlineMen&uuml;-Interner-Link:</legend>
						<div class=\"row\">
							<label>Link-Titel:
								<span class=\"info\">Ein wenig Text der den Link deutlich macht.</span>
							</label>
							<input type=\"text\" name=\"text\" value=\"" . $entryData['text'] . "\" />
						</div>
						<div class=\"row\">
							<label>Link:
								<span class=\"info\">Hier kommt die URL hin die den Link sp&auml;ter ergibt.</span>
							</label>
							<input type=\"text\" name=\"link\" value=\"" . $entryData['link'] . "\" />
						</div>
						<div class=\"row\">
							<input type=\"reset\" class=\"button\" value=\"Zur&uuml;cksetzen\" />
							<input type=\"submit\" class=\"button\" value=\"Speichern\" />
						</div>
						</fieldset>
						</form>";
				}
				else if($entryData['type'] == 'text') {
					$out .= "<form action=\"admin.php\" method=\"post\">
						<input type=\"hidden\" name=\"page\" value=\"pagestructure\" />
						<input type=\"hidden\" name=\"action\" value=\"pageInlineMenu\" />
						<input type=\"hidden\" name=\"pageID\" value=\"$PageID\" />
						<input type=\"hidden\" name=\"entryID\" value=\"$entryID\" />
						<input type=\"hidden\" name=\"action2\" value=\"saveEntry\" />
						<input type=\"hidden\" name=\"type\" value=\"text\" />
						<fieldset>
						<legend>Erstelle neuen InlineMen&uuml;-Text:</legend>
						<div class=\"row\"><label>Text: <span class=\"info\">Das ist der Text, der sp&auml;ter angezeigt werden soll</span></label>
							<textarea name=\"text\">" . $entryData['text'] . "</textarea></div>
						<div class=\"row\">
							<input type=\"reset\" class=\"button\" value=\"Zur&uuml;cksetzen\" />
							<input type=\"submit\" class=\"button\" value=\"Speichern\" />
						</div>
						</fieldset>
						</form>";
				}
				else if($entryData['type'] == 'intern') {
					$out .= "<form action=\"admin.php\" method=\"post\">
						<input type=\"hidden\" name=\"page\" value=\"pagestructure\" />
						<input type=\"hidden\" name=\"action\" value=\"pageInlineMenu\" />
						<input type=\"hidden\" name=\"pageID\" value=\"$PageID\" />
						<input type=\"hidden\" name=\"entryID\" value=\"$entryID\" />
						<input type=\"hidden\" name=\"action2\" value=\"saveEntry\" />
						<input type=\"hidden\" name=\"type\" value=\"intern\" />
						<fieldset>
						<legend>Erstelle neuen InlineMen&uuml;-Interner-Link:</legend>
						<div class=\"row\"><label>Link-Titel<span class=\"info\">Ein wenig Text der den Link deutlich macht.</span></label><input type=\"text\" name=\"text\" value=\"" . $entryData['text'] . "\" /></div>
						<div class=\"row\"><label>Interne Seite<span class=\"info\">Das ist die interne Seite, auf die der Link sp&auml;ter f&uuml;hren soll.</span></label><select name=\"link\">";
					$out .= $this->_structurePullDown(0, 0, '', -1, substr($entryData['link'], 15));
					$out .= "</select></div>
						<div class=\"row\">
							<input type=\"reset\" class=\"button\" value=\"" . $adminLang['reset'] . "\" />
							<input type=\"submit\" class=\"button\" value=\"" . $adminLang['save'] . "\" />
						</div>
						</fieldset>
						</form>";
				}
				else if($entryData['type'] == 'download') {
					$out .= "<form action=\"admin.php\" method=\"post\">
						<input type=\"hidden\" name=\"page\" value=\"pagestructure\" />
						<input type=\"hidden\" name=\"action\" value=\"pageInlineMenu\" />
						<input type=\"hidden\" name=\"pageID\" value=\"$PageID\" />
						<input type=\"hidden\" name=\"entryID\" value=\"$entryID\" />
						<input type=\"hidden\" name=\"action2\" value=\"saveEntry\" />
						<input type=\"hidden\" name=\"type\" value=\"download\" />
						<fieldset>
							<legend>Download Hinzuf&uuml;gen</legend>
						
						<div class=\"row\">
							<label class=\"row\" for=\"download_text\">
								Download-Titel:
								<span class=\"info\">Der Text wird als Downloadlink angezeigt er kann zum Beispiel der Dateiname sein, aber auch ein kuzer eindeutiger Text ist sehr sinnvoll.</span>
							</label>
							<input type=\"text\" name=\"text\" id=\"download_text\" value=\"" . $entryData['text'] . "\" />
						</div>
						<div class=\"row\">
							<label class=\"row\" for=\"link\">
								Datei f&uuml;r den Download:
								<span class=\"info\">Die hier angegebene Datei kann dann sp&auml;ter heruntergeladen werden.</span>
							</label>
							<select name=\"link\" id=\"link\">";
					$sql = "SELECT *
						FROM " . DB_PREFIX . "files
						ORDER BY file_name";
					$files_result = db_result($sql);
					while($file = mysql_fetch_object($files_result)) {
						if(file_exists($file->file_path))
							$out .= "<option " . (($entryData['link'] == $file->file_id) ? 'selected="selected" ' : '') . "value=\"$file->file_id\">" . utf8_encode($file->file_name) . " (" . kbormb($file->file_size) . ")</option>\r\n";
					}			
					$out .= "</select>
							</div>
							<div class=\"row\"><input type=\"reset\" class=\"button\" value=\"Zur&uuml;cksetzen\" /><input type=\"submit\" class=\"button\" value=\"Speichern\" /></div>
						</fieldset>
						</form>";
				}
				
				return $out;
			}
			return $this->_InlineMenuHomePage($PageID); 
		}
		
		function _InlineMenuRemoveEntryPage ($PageID) {
			$entryID = GetPostOrGet('entryID');
			$confirmation = GetPostOrGet('confirmation');
			$adminLang = &$this->_AdminLang;
			if($confirmation == 1) {
				$this->_PageStructure->RemoveInlineMenuEntry($entryID);
				return $this->_InlineMenuHomePage($PageID);
			}
			else {
				$sql = "SELECT *
					FROM " . DB_PREFIX . "inlinemenu_entries
					WHERE inlineentrie_id=$entryID";
 				$entryResult = $this->_SqlConnection->SqlQuery($sql);
 				if($entry = mysql_fetch_object($entryResult)) {
					return "Sind sie sicher, dass sie das Element &quot;$entry->inlineentrie_text&quot; unwiederruflich l&ouml;schen m&ouml;chten?<br />
						<a href=\"admin.php?page=pagestructure&amp;action=pageInlineMenu&amp;entryID=$entryID&amp;action2=removeEntry&amp;confirmation=1&amp;pageID=$PageID\" class=\"button\">" . $adminLang['yes'] . "</a >
						<a href=\"admin.php?page=pagestructure&amp;action=pageInlineMenu&amp;pageID=$PageID\"class=\"button\">" . $adminLang['no'] . "</a >";
 				}
			}
		}
		
		/**
		 * @param integer PageID
		 * @return string
		 */
		function _InlineMenuSetImagePage ($PageID) {
			// Get data from header
			$imagePath = GetPostOrGet('imagePath');
			// FIXME: load from config?
			$imgmax2 = 200;
			// Get data form config
			$inlinemenuFolder = $this->_Config->Get('thumbnailfolder', 'data/thumbnails/');
			// resize the image if it doesn't exist
			$thumbnail = resizeImageToWidth($imagePath, $inlinemenuFolder, $imgmax2);
			// if resizing was successful ...
			if(file_exists($thumbnail)) {
				// ... set it to the inlinemenu
				$this->_PageStructure->SetInlineMenuImage($PageID, $thumbnail, $imagePath);
			}
			// go back to the overview of the inlinemenu
			return $this->_InlineMenuHomePage($PageID);
		}
		
		/**
		 * @param integer PageID
		 * @return string
		 */
		function _InlineMenuSaveEntryPage ($PageID) {
			$entryID = GetPostOrGet('entryID');
			$text = GetPostOrGet('text');
			$type = GetPostOrGet('type');
			$link = GetPostOrGet('link');
			$sql = '';
			switch ($type) {
				case 'link':		$this->_PageStructure->SaveInlineMenuLink($text, $link, $entryID, $PageID);
							break;
				case 'intern':		$this->_PageStructure->SaveInlineMenuInternLink($text, $link, $entryID, $PageID);
							break;
				case 'text':		$this->_PageStructure->SaveInlineMenuText($text, $entryID, $PageID);
							break;
				case 'download':	$this->_PageStructure->SaveInlineMenuDownload($text, $link, $entryID, $PageID);
							break;
				default:		break;
			}
			
 			return $this->_InlineMenuHomePage($PageID);
		}
		
		/**
		 * @param integer PageID
		 * @return string
		 */
		function _InlineMenuAddNewEntryPage ($PageID) {
			$text = GetPostOrGet('text');
			$type = GetPostOrGet('type');
			$link = GetPostOrGet('link');
			$sql = '';
			switch ($type) {
				case 'link':		$this->_PageStructure->AddInlineMenuLink($text, $link, $PageID);
							break;
				case 'intern':		$this->_PageStructure->AddInlineMenuInternLink($text, $link, $PageID);
							break;
				case 'text':		$this->_PageStructure->AddInlineMenuText($text, $PageID);
							break;
				case 'download':	$this->_PageStructure->AddInlineMenuDownload($text, $link, $PageID);
							break;
				default:		break;
			}
			
 			return $this->_InlineMenuHomePage($PageID);
		}
		
		/**
		 * @param integer PageID
		 * @return string
		 */
		function _InlineMenuAddNewEntryDialogPage($PageID) {
			$adminLang = &$this->_AdminLang;
			// Get data from header
			$type = GetPostOrGet('type');
			
			$out = '';
			
			if($type == 'link') {
				$out .= "<form action=\"admin.php\" method=\"post\">
						<input type=\"hidden\" name=\"page\" value=\"pagestructure\" />
						<input type=\"hidden\" name=\"action\" value=\"pageInlineMenu\" />
						<input type=\"hidden\" name=\"pageID\" value=\"$PageID\" />
						<input type=\"hidden\" name=\"action2\" value=\"addNewEntry\" />
						<input type=\"hidden\" name=\"type\" value=\"link\" />
						<fieldset>
						<legend>Erstelle neuen InlineMen&uuml;-Interner-Link:</legend>
						<div class=\"row\">
							<label>Link-Titel:
								<span class=\"info\">Ein wenig Text der den Link deutlich macht.</span>
							</label>
							<input type=\"text\" name=\"text\" value=\"\" />
						</div>
						<div class=\"row\">
							<label>Link:
								<span class=\"info\">Hier kommt die URL hin die den Link sp&auml;ter ergibt.</span>
							</label>
							<input type=\"text\" name=\"link\" value=\"http://\" />
						</div>
						<div class=\"row\">
							<input type=\"reset\" class=\"button\" value=\"Zur&uuml;cksetzen\" />
							<input type=\"submit\" class=\"button\" value=\"Speichern\" />
						</div>
						</fieldset>
						</form>";
			}
			else if($type == 'text') {
				$out .= "<form action=\"admin.php\" method=\"post\">
						<input type=\"hidden\" name=\"page\" value=\"pagestructure\" />
						<input type=\"hidden\" name=\"action\" value=\"pageInlineMenu\" />
						<input type=\"hidden\" name=\"pageID\" value=\"$PageID\" />
						<input type=\"hidden\" name=\"action2\" value=\"addNewEntry\" />
						<input type=\"hidden\" name=\"type\" value=\"text\" />
						<fieldset>
						<legend>Erstelle neuen InlineMen&uuml;-Text:</legend>
						<div class=\"row\"><label>Text: <span class=\"info\">Das ist der Text, der sp&auml;ter angezeigt werden soll</span></label>
							<textarea name=\"text\"></textarea></div>
						<div class=\"row\">
							<input type=\"reset\" class=\"button\" value=\"Zur&uuml;cksetzen\" />
							<input type=\"submit\" class=\"button\" value=\"Speichern\" />
						</div>
						</fieldset>
						</form>";
			}
			else if($type == 'intern') {
				$out .= "<form action=\"admin.php\" method=\"post\">
						<input type=\"hidden\" name=\"page\" value=\"pagestructure\" />
						<input type=\"hidden\" name=\"action\" value=\"pageInlineMenu\" />
						<input type=\"hidden\" name=\"pageID\" value=\"$PageID\" />
						<input type=\"hidden\" name=\"action2\" value=\"addNewEntry\" />
						<input type=\"hidden\" name=\"type\" value=\"intern\" />
						<fieldset>
						<legend>Erstelle neuen InlineMen&uuml;-Interner-Link:</legend>
						<div class=\"row\"><label>Link-Titel<span class=\"info\">Ein wenig Text der den Link deutlich macht.</span></label><input type=\"text\" name=\"text\" value=\"\" /></div>
						<div class=\"row\"><label>Interne Seite<span class=\"info\">Das ist die interne Seite, auf die der Link sp&auml;ter f&uuml;hren soll.</span></label><select name=\"link\">";
						$out .= $this->_structurePullDown(0);
				$out .= "</select></div>
						<div class=\"row\">
							<input type=\"reset\" class=\"button\" value=\"" . $adminLang['reset'] . "\" />
							<input type=\"submit\" class=\"button\" value=\"" . $adminLang['save'] . "\" />
						</div>
						</fieldset>
						</form>";
			}
			else if($type == 'download') {
				$out .= "<form action=\"admin.php\" method=\"post\">
						<input type=\"hidden\" name=\"page\" value=\"pagestructure\" />
						<input type=\"hidden\" name=\"action\" value=\"pageInlineMenu\" />
						<input type=\"hidden\" name=\"pageID\" value=\"$PageID\" />
						<input type=\"hidden\" name=\"action2\" value=\"addNewEntry\" />
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
								Datei f&uuml;r den Download:
								<span class=\"info\">Die hier angegebene Datei kann dann sp&auml;ter heruntergeladen werden.</span>
							</label>
							<select name=\"link\" id=\"link\">";
					$sql = "SELECT *
						FROM " . DB_PREFIX . "files
						ORDER BY file_name";
					$files_result = db_result($sql);
					while($file = mysql_fetch_object($files_result)) {
						if(file_exists($file->file_path))
							$out .= "<option value=\"$file->file_id\">" . utf8_encode($file->file_name) . " (" . kbormb($file->file_size) . ")</option>\r\n";
					}			
					$out .= "</select>
							</div>
							<div class=\"row\"><input type=\"reset\" class=\"button\" value=\"Zur&uuml;cksetzen\" /><input type=\"submit\" class=\"button\" value=\"Speichern\" /></div>
						</fieldset>
						</form>";
			}
			else {
				$out = "<form action=\"admin.php\" method=\"post\">
						<input type=\"hidden\" name=\"page\" value=\"pagestructure\" />
						<input type=\"hidden\" name=\"action\" value=\"pageInlineMenu\" />
						<input type=\"hidden\" name=\"pageID\" value=\"$PageID\" />
						<input type=\"hidden\" name=\"action2\" value=\"addNewEntryDialog\" />
						
						<fieldset>
							<legend>Eintrags-Typ:</legend>
							<div class=\"row\">
								<label for=\"type_link\">
									Link:
									<span class=\"info\">TODO</span>
								</label>
								<input id=\"type_link\" type=\"radio\" name=\"type\" value=\"link\" checked=\"checked\"/>
							</div>
							<div class=\"row\">
								<label for=\"type_text\">
									Text:
									<span class=\"info\">TODO</span>
								</label>
								<input id=\"type_text\" type=\"radio\" name=\"type\" value=\"text\"/>
							</div>
							<div class=\"row\">
								<label for=\"type_intern\">
									Interner-Link:
									<span class=\"info\">TODO</span>
								</label>
								<input id=\"type_intern\" type=\"radio\" name=\"type\" value=\"intern\"/>
							</div>
							<div class=\"row\">
								<label for=\"type_download\">
									Download:
									<span class=\"info\">TODO</span>
								</label>
								<input id=\"type_download\" type=\"radio\" name=\"type\" value=\"download\"/>
							</div>
							<div class=\"row\">
								<input type=\"submit\" class=\"button\" value=\"Weiter\" />
							</div>
						</fieldset>
						</form>";
			}
			return $out;
		}
		
		function _InlineMenuMoveUpPage($PageID) {
			$entrySortID = GetPostOrGet('entrySortID');
			$this->_PageStructure->InlineMenuEntryMoveUp($entrySortID, $PageID);
			return $this->_InlineMenuHomePage($PageID);
		}
		
		function _InlineMenuMoveDownPage($PageID) {
			$entrySortID = GetPostOrGet('entrySortID');
			$this->_PageStructure->InlineMenuEntryMoveDown($entrySortID, $PageID);
			return $this->_InlineMenuHomePage($PageID);
		}
		
		function _InlineMenuSetImageTitlePage($PageID) {
			$imageTitle = GetPostOrGet('imageTitle');
			$this->_PageStructure->SetInlineMenuImageTitle($PageID, $imageTitle);
			return $this->_InlineMenuHomePage($PageID);
		}
		
		function _InlineMenuRemoveImagePage($PageID) {
			$this->_PageStructure->RemoveInlineMenuImage($PageID);
			return $this->_InlineMenuHomePage($PageID);
		}
		
		function _InlineMenuSelectImage($PageID) {
			$imagePath = $this->_PageStructure->GetInlineMenuData($PageID, 'image');
			$adminLang = $this->_AdminLang;
			$sql = "SELECT *
				FROM " . DB_PREFIX . "files
				WHERE file_type LIKE 'image/%'
				ORDER BY file_name ASC";
			$images_result = db_result($sql);
			$imgmax = 100;
			$imgmax2 = 200;
			$inlinemenu_folder = 'data/thumbnails/';
			$out = "<form action=\"admin.php\" method=\"post\">
				<input type=\"hidden\" name=\"page\" value=\"pagestructure\"/>
				<input type=\"hidden\" name=\"action\" value=\"pageInlineMenu\"/>
				<input type=\"hidden\" name=\"pageID\" value=\"$PageID\"/>
				<input type=\"hidden\" name=\"action2\" value=\"setImage\"/>
				<fieldset>
				<legend>" . $adminLang['inlinemenu_image'] . "</legend>
				<div class=\"row\"><div class=\"imagesblock\">";
			while($image = mysql_fetch_object($images_result)) {
				$thumbnail = resizeImageToMaximum($image->file_path, $inlinemenu_folder ,$imgmax);
				if($thumbnail !== false) {
					list($originalWidth, $originalHeight) = getimagesize($thumbnail);
					
					$out .= "<div class=\"imageblock\">
				<a href=\"" . generateUrl($image->file_path) . "\">
				<img style=\"margin-top:" . ($imgmax-$originalHeight) . "px;\" src=\"" . generateUrl($thumbnail) . "\" alt=\"". basename($thumbnail) ."\" /></a><br />
				<input type=\"radio\" name=\"imagePath\" " .(($imagePath == $image->file_path) ? 'checked="checked" ' : '') . " value=\"$image->file_path\"/></div>";
				}
			}
			$out .= "</div></div>
				<div class=\"row noform\"><input type=\"submit\" value=\"" . $adminLang['apply'] . "\" class=\"button\"/>
				<a href=\"admin.php?page=pagestructure&amp;action=pageInlineMenu&amp;pageID=$PageID\" class=\"button\">" . $adminLang['back'] . "</a></div></fieldset></form>";
				
			
			return $out;
		}
		
		function _InlineMenuHomePage($PageID) {
			
			$adminLang = $this->_AdminLang;
			$image = 'Noch kein Bild gesetzt';
			$thumbPath = $this->_PageStructure->GetInlineMenuData($PageID, 'imageThumb');
			$imagePath = $this->_PageStructure->GetInlineMenuData($PageID, 'image');
			$imageTitle = $this->_PageStructure->GetInlineMenuData($PageID, 'imageTitle');
			
			if(file_exists($thumbPath))
				$image = "<img src=\"" . generateUrl($thumbPath) . "\"/>";
			else {
				$imgmax2 = 200;
				$inlinemenuFolder = 'data/thumbnails/';
				$thumbnail = resizeImageToWidth($imagePath, $inlinemenuFolder, $imgmax2);
				if($thumbnail !== false){
					$image = "<img src=\"" . generateUrl($thumbnail) . "\"/>";
				}
			}	
			$out = "
				<fieldset>
					<legend>" . $adminLang['inlinemenu'] . "</legend>
				<div class=\"row\">
						<label class=\"row\">
							" . $adminLang['inlinemenu_image'] . ":
							<span class=\"info\">Das ist der Pfad zu dem Bild, das dem Zusatzmen&uuml; zugeordnet wird, es kann der Einfachheit halber aus den bereits hochgeladenen Bildern ausgew&auml;hlt werden.</span>
						</label>
						$image
				</div>
				<div class=\"row\">
					<a href=\"admin.php?page=pagestructure&amp;action=pageInlineMenu&amp;pageID=$PageID&amp;action2=selectImage\" class=\"button\">Bild ausw&auml;hlen/ver&auml;ndern</a>
					" .((file_exists($thumbPath)) ?  "<a href=\"admin.php?page=pagestructure&amp;action=pageInlineMenu&amp;pageID=$PageID&amp;action2=removeImage\" class=\"button\">Bild entfernen</a>" : '') . "
				</div>
				<form action=\"admin.php\" method=\"post\">
				<input type=\"hidden\" name=\"page\" value=\"pagestructure\" />
				<input type=\"hidden\" name=\"action\" value=\"pageInlineMenu\" />
				<input type=\"hidden\" name=\"pageID\" value=\"$PageID\" />
				<input type=\"hidden\" name=\"action2\" value=\"setImageTitle\" />
				<div class=\"row\">
					<label for=\"inlinemenuThumbTitle\"class=\"row\">
						Bildunterschrift:
						<span class=\"info\">Die Bildunterschrift kann das Bild noch ein wenig erl&auml;utern.</span>
					</label>
					<input id=\"inlinemenuThumbTitle\" name=\"imageTitle\" type=\"text\" value=\"$imageTitle\" />
				</div>
				<div class=\"row\">
					<input type=\"submit\" class=\"button\" value=\"" . $adminLang['save'] . "\" />
				</div>
				</form>";
			$sql = "SELECT *
				FROM " . DB_PREFIX . "inlinemenu_entries
				WHERE inlineentry_page_id = $PageID
				ORDER BY inlineentry_sortid ASC";
			$entries_result = db_result($sql);
			$out .= "<h3 id=\"entries\">Eintr&auml;ge</h3>
				<div class=\"row\"><table class=\"text_table full_width\">
					<thead><tr><th>Text</th><th class=\"small_width\">Typ</th><th class=\"actions\">Aktion</th></tr></thead>";
				while($entry = mysql_fetch_object($entries_result)) {
					$typeImage = '';
					switch ($entry->inlineentry_type) {
						case 'download':	$typeImage = "<img alt=\"\" src=\"img/download.png\"/>Download";
									break;
						case 'link':		$typeImage = "<img alt=\"\" src=\"img/extern.png\"/>Externer-Link";
									break;
						case 'intern':		$typeImage = "<img alt=\"\" src=\"img/view.png\"/>Interner-Link";
									break;
						default:		$typeImage = "Text";
									break;
					}
					
					$out .= "<tr>
					<td>". nl2br($entry->inlineentry_text) ."</td>
					<td>" . $typeImage . "</td>
					<td>
						<a href=\"admin.php?page=pagestructure&amp;action=pageInlineMenu&amp;pageID=$PageID&amp;entrySortID=$entry->inlineentry_sortid&amp;action2=moveEntryUp#entries\"><img src=\"./img/up.png\" alt=\"" . $adminLang['move_up'] ."\" title=\"" . $adminLang['move_up'] ."\" /></a>
						<a href=\"admin.php?page=pagestructure&amp;action=pageInlineMenu&amp;pageID=$PageID&amp;entrySortID=$entry->inlineentry_sortid&amp;action2=moveEntryDown#entries\"><img src=\"./img/down.png\" alt=\"" . $adminLang['move_down'] ."\" title=\"" . $adminLang['move_down'] ."\" /></a>
						<a href=\"admin.php?page=pagestructure&amp;action=pageInlineMenu&amp;pageID=$PageID&amp;entryID=$entry->inlineentry_id&amp;action2=editEntry\"><img src=\"./img/edit.png\" alt=\"" . $adminLang['edit'] ."\" title=\"" . $adminLang['edit'] ."\" /></a>
						<a href=\"admin.php?page=pagestructure&amp;action=pageInlineMenu&amp;pageID=$PageID&amp;entryID=$entry->inlineentry_id&amp;action2=removeEntry\"><img src=\"./img/del.png\" alt=\"" . $adminLang['delete'] ."\" title=\"" . $adminLang['delete'] ."\" /></a>
						
					</td>
					</tr>";
				}
				$out .= "</table>
					</div>
					<div class=\"row\"><a href=\"admin.php?page=pagestructure&amp;action=pageInlineMenu&amp;pageID=$PageID&amp;action2=addNewEntryDialog\" class=\"button\">Einen Eintrag hinzuf&uuml;gen</a></div>
					</fieldset>";
			return $out;
		}
		
		function _infoPage() {
			global $admin_lang, $user;
			
			$page_id =  GetPostOrGet('pageID');
			$action2 = GetPostOrGet('action2');
			if($action2 == 'savePath' || $action2 == 'saveAccess') {
				$page_access = GetPostOrGet('pageAccess');
				$page_access_old = GetPostOrGet('pageAccessOld');
				$page_parent_id = GetPostOrGet('pageParentID');
				$page_parent_id_old = GetPostOrGet('pageParentIDOld');
				if(($action2 == 'saveAccess' && $page_access_old != $page_access) || ($action2 == 'savePath' && $page_id != $page_parent_id && $page_parent_id != $page_parent_id_old)) {
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
					
					if($action2 == 'savePath') {
						
							if(is_numeric($page_parent_id) && is_numeric($page_id)) {
							$sql = "UPDATE " . DB_PREFIX . "pages
	 							SET page_parent_id=$page_parent_id, page_creator='$user->ID', page_date='" . mktime() . "',  page_edit_comment = 'Changed ParentID form $page_parent_id_old to $page_parent_id'
 								WHERE page_id = $page_id";
 								db_result($sql);
							}
						}
					else if($action2 == 'saveAccess') {
						
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
					if(GetPostOrGet('action2') == 'changeAccess') {
						$out .= "<form action=\"admin.php\" method=\"post\">
							<input type=\"hidden\" name=\"page\" value=\"pagestructure\" />
							<input type=\"hidden\" name=\"action\" value=\"pageInfo\" />
							<input type=\"hidden\" name=\"pageID\" value=\"$page->page_id\" />
							<input type=\"hidden\" name=\"pageAccessOld\" value=\"$page->page_access\" />
							<input type=\"hidden\" name=\"action2\" value=\"saveAccess\" />
							<select name=\"pageAccess\">
								<option value=\"public\"" . (($page->page_access == 'public') ? ' selected="selected"' : '') . ">" . $admin_lang['public'] . "</option>
								<option value=\"private\"" . (($page->page_access == 'private') ? ' selected="selected"' : '') . ">" . $admin_lang['private'] . "</option>
								<option value=\"hidden\"" . (($page->page_access == 'hidden') ? ' selected="selected"' : '') . ">" . $admin_lang['hidden'] . "</option>
								<option value=\"deleted\"" . (($page->page_access == 'deleted') ? ' selected="selected"' : '') . ">" . $admin_lang['deleted'] . "</option>
							</select>
							<input type=\"submit\" value=\"" . $admin_lang['save'] . "\" class=\"button\" />
							</form>";
					}
					else
						$out .=	$admin_lang[$page->page_access] . " <a href=\"admin.php?page=pagestructure&amp;action=pageInfo&amp;pageID=$page->page_id&amp;action2=changeAccess\"><img src=\"./img/edit.png\" height=\"16\" width=\"16\" alt=\"" . $admin_lang['edit'] . "\" title=\"" . $admin_lang['edit'] . "\"/></a>";
					$out .= "</td>
				</tr>
				<tr>
					<th>Sprache</th>
					<td>" . $admin_lang[$page->page_lang] . "</td>
				</tr>
				<tr>
					";
				if(GetPostOrGet('action2') == 'changePath') {
					$out .= "<th>Unterseite von</th>
					<td><form action=\"admin.php\" method=\"post\">
							<input type=\"hidden\" name=\"page\" value=\"pagestructure\" />
							<input type=\"hidden\" name=\"action\" value=\"pageInfo\" />
							<input type=\"hidden\" name=\"pageID\" value=\"$page->page_id\" />
							<input type=\"hidden\" name=\"action2\" value=\"savePath\" />
							<input type=\"hidden\" name=\"pageParentIDOld\" value=\"$page->page_parent_id\" />
							<select name=\"pageParentID\">
								<option value=\"0\">Keiner</option>\r\n";
					$out .= $this->_structurePullDown(0, 0, '', $page->page_id, $page->page_parent_id);
					$out .= "\t\t\t\t\t\t\t</select><input type=\"submit\" value=\"" . $admin_lang['save'] . "\" class=\"button\" /></form>";
				}
				else
					$out .= "<th>Pfad</th>
					<td>
						<a href=\"admin.php?page=pagestructure\">root</a><strong>/</strong>" . $this->_pagePath($page->page_id) . " <a href=\"admin.php?page=pagestructure&amp;action=pageInfo&amp;pageID=$page->page_id&amp;action2=changePath\"><img src=\"./img/edit.png\" height=\"16\" width=\"16\" alt=\"" . $admin_lang['edit'] . "\" title=\"" . $admin_lang['edit'] . "\"/></a>";
				$out .= "</td>
				</tr>
				<tr>
					<th>Bearbeitet von</th>
					<td>" . getUserById($page->page_creator) . "</td>
				</tr>
				<tr>
					<th>Bearbeitet am</th>
					<td>" . date("d.m.Y H:i:s",$page->page_date) . "</td>
				</tr>";
				$sql = "SELECT *
				FROM " . DB_PREFIX . "inlinemenu_entries
				WHERE inlineentry_page_id = $page_id
				ORDER BY inlineentry_sortid ASC";
				$inlineMenuEntriesResult = $this->_SqlConnection->SqlQuery($sql);
				
				$out .= "<tr>
					<th>" . $admin_lang['inlinemenu'] . "</th>
					<td>" . mysql_num_rows($inlineMenuEntriesResult) . " Einträge [<a href=\"admin.php?page=pagestructure&amp;action=pageInlineMenu&amp;pageID=$page_id\">" . $admin_lang['inlinemenu'] . "</a>]</td>
				</tr>
			</table>\r\n";
				
				$sql = "SELECT *
					FROM " . DB_PREFIX . "pages
					WHERE page_parent_id = $page_id
					ORDER BY page_date DESC";
				$subpages_c_result = db_result($sql);
				$subpages_count = mysql_num_rows($subpages_c_result);
				if($subpages_count != 0) {
					$out .="\t\t\t<h2>Unterseiten</h2>\r\n
						<script type=\"text/javascript\" language=\"JavaScript\" src=\"system/functions.js\"></script>";
					$out .= $this->_showStructure($page->page_id);
					$out .= "<script type=\"text/javascript\" language=\"JavaScript\">
						SetHover('span', 'structure_row', 'structure_row_hover', function additional() {document.getElementById('menu').className = '';});
						</script>\r\n";
				}
				$sql = "SELECT *
					FROM " . DB_PREFIX . "pages_history
					WHERE page_id = $page_id
					ORDER BY page_date DESC";
				$result = db_result($sql);
				$changes_count = mysql_num_rows($result);
				$out .="\t\t\t<h2>Ver&auml;nderungen($changes_count)</h2>
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
						<a href=\"admin.php?page=pagestructure&amp;action=editPage&amp;pageID=$page->page_id\"><img src=\"./img/edit.png\" height=\"16\" width=\"16\" alt=\"" . $admin_lang['edit'] . "\" title=\"" . $admin_lang['edit'] . "\"/></a>
					</td>
				</tr>\r\n";
				
				while($change = mysql_fetch_object($result)) {
					$out .= "\t\t\t\t<tr>
					<td>" . date("d.m.Y H:i:s",$change->page_date) . "</td>
					<td>".getUserById($change->page_creator) . "</td>
					<td>$change->page_title</td>
					<td>$change->page_edit_comment&nbsp;</td>
					<td><a href=\"index.php?page=$page->page_id&amp;change=$changes_count\"><img src=\"./img/view.png\" height=\"16\" width=\"16\" alt=\"Anschauen\" title=\"Anschauen\"/></a>
					<a href=\"admin.php?page=pagestructure&amp;action=editPage&amp;pageID=$page->page_id&amp;change=$changes_count\"><img src=\"./img/edit.png\" height=\"16\" width=\"16\" alt=\"" . $admin_lang['edit'] . "\" title=\"" . $admin_lang['edit'] . "\"/></a>
					<a href=\"admin.php?page=pagestructure&amp;action=savePage&amp;pageID=$page->page_id&amp;change=$changes_count\"><img src=\"./img/restore.png\" height=\"16\" width=\"16\" alt=\"" . $admin_lang['restore'] . "\" title=\"" . $admin_lang['restore'] . "\"/></a></td>
				</tr>\r\n";
					$changes_count--;
				}
				
				$out .= "\t\t\t</table>";
				
			}
			
			return $out;
		}
		
 	}
