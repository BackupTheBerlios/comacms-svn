<?php
/**
 * @package ComaCMS
 * @subpackage AdminInterface
 * @copyright (C) 2005-2007 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : admin_pagestructure.php
 # created              : 2005-09-04
 # copyright            : (C) 2005-2007 The ComaCMS-Team
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
 	require_once __ROOT__ . '/classes/pagestructure.php';
 	require_once __ROOT__ . '/classes/imageconverter.php';
 	require_once __ROOT__ . '/classes/admin/admin.php';
 	
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
		 * @access private
		 */
 		var $_PageStructure;
 		var $FormUrl = 'admin.php';
 		var $LinkUrl = 'admin.php?';
 		var $FormPage = 'page';
 		
		/**
		 * @access private
		 */
		function _Init() {
			$this->_PageStructure = new PageStructure($this->_SqlConnection, $this->_User, $this->_ComaLib);
		}
 		
 		/**
 		 * @return string
 		 * @param string Action
 		 */
		 function GetPage($Action = '') {
		 	$out = '';
			
			if($Action != 'internHome')
				$out .= "\t\t\t<h2>" . $this->_Translation->GetTranslation('pagestructure') . "</h2>\r\n";
		 	switch ($Action) {
		 		case 'deletePage':
		 			$out .= $this->_deletePage();
		 			break;
		 		case 'pageInfo':
		 			$out .= $this->_infoPage();
		 			break;
		 		case 'newPage':
		 			$out .= $this->_newPage();
		 			break;
		 		case 'addNewPage':
		 			$out .= $this->_addPage();
		 			break;
		 		case 'editPage':
		 			$out .= $this->_editPage();
					break;
				case 'savePage':
					$out .= $this->_savePage();
					break;
				case 'generateMenu':
					$out .= $this->_generate_menu();
					break;
				case 'pageInlineMenu':
					$out .= $this->_inlineMenu();
					break;
				case 'restorePage':
					$out .= $this->_restorePage();
					break;
		 		default:
		 			$out .= $this->_homePage();
		 	}
			return $out;
		 }
		 
		 /**
		  * @access private
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
		 
		 /**
		  * @access private
		  * @return string
		  */
		 function _deletePage() {
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
		 			if ($this->_PageStructure->PageHasSubPages($pageID, true)) {
		 				$out .= "<fieldset>
		 						<legend>Unterseiten vorhanden</legend>
		 						<form action=\"{$this->FormUrl}\" method=\"post\">
		 							<input type=\"hidden\" name=\"{$this->FormPage}\" value=\"pagestructure\" />
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
		 				//$this->_PageStructure->LoadParentIDs();
		 				$out .= $this->_PageStructure->PageStructurePulldown(0, 0, '', $pageID, $pageID);
		 				$out .= "</select>
		 							</div>
		 							<div class=\"row error\">
		 								Mit dem Klicken auf OK wird die Aktion sofort durchgeführt und nicht noch einmal hinterfragt!
		 							</div>
		 							<div class=\"row\">
		 								<a href=\"{$this->LinkUrl}page=pagestructure\" class=\"button\">" . $this->_Translation->GetTranslation('back') . "</a>
		 								<input type=\"submit\" class=\"button\" value=\"" . $this->_Translation->GetTranslation('ok') . "\"/>
		 							</div>	
		 						</form>
		 					</fieldset>";
		 			}
		 			else {
		 				$out .= sprintf($this->_Translation->GetTranslation('Do you really want to delete the page %page_title%?'), $this->_PageStructure->GetPageData($pageID, 'title')) . "<br />
		 				<a href=\"{$this->LinkUrl}page=pagestructure&amp;action=deletePage&amp;pageID=$pageID&amp;confirmation=1\" class=\"button\">" . $this->_Translation->GetTranslation('yes') . "</a>
		 					<a href=\"{$this->LinkUrl}page=pagestructure\" class=\"button\">" . $this->_Translation->GetTranslation('no') . "</a>";
		 			}
		 			return $out;
		 		}
		 	}
		 	else
		 		return $this->GetPage('internHome');
		 }
		 
		 /**
		  * @access private
		  * @return void
		  */
		 
		 function _getMenuPageIDs() {
		 	$this->MenuPageIDs = array();
		 	$sql = "SELECT menu_entries_page_id
		 		FROM " . DB_PREFIX . "menu_entries
		 		WHERE menu_entries_menuid=1";
		 	$ids_result = $this->_SqlConnection->SqlQuery($sql);
		 	while($id = mysql_fetch_object($ids_result))
		 		$this->MenuPageIDs[] = $id->menu_entries_page_id;
		 }
		 
		 /**
		  * @access private
		  * @return string
		  */
		 function _homePage() {
		 	$this->_getMenuPageIDs();
		 	$out = "\t\t\t<script type=\"text/javascript\" language=\"JavaScript\" src=\"system/functions.js\"></script>
			<a href=\"{$this->LinkUrl}page=pagestructure&amp;action=newPage\" class=\"button\">" . $this->_Translation->GetTranslation('create_new_page') . "</a>
			<form method=\"post\" action=\"{$this->FormUrl}\">
				<input type=\"hidden\" name=\"{$this->FormPage}\" value=\"pagestructure\" />
				<input type=\"hidden\" name=\"action\" value=\"generateMenu\" />\r\n";
		 	$out .= $this->_showStructure(0);
			$out .= "</form>
			<script type=\"text/javascript\" language=\"JavaScript\">
				SetHover('span', 'structure_row', 'structure_row_hover', function additional() {document.getElementById('menu').className = '';});
			</script>";
			return $out;
		 }
		 
		 /**
		  * @access private
		  * @return string
		  */
		 function _newPage() {
		 	$this->_PageStructure->LoadParentIDs();
				 	
		 	$out = "\t\t\t<form method=\"post\" action=\"{$this->FormUrl}\">
				<fieldset>
					<legend>" . $this->_Translation->GetTranslation('new_page') . "</legend>
					<input type=\"hidden\" name=\"{$this->FormPage}\" value=\"pagestructure\" />
					<input type=\"hidden\" name=\"action\" value=\"addNewPage\" />
					<div class=\"row\">
						<label>
							" . $this->_Translation->GetTranslation('name/contraction') . ":
							<span class=\"info\">Mit diesem K&uuml;rzel wird auf die Seite zugegriffen und dient es zur eindeutigen Identifizierung der Seite.</span>
						</label>
						<input type=\"text\" name=\"pageName\" maxlength=\"28\" />
					</div>
					<div class=\"row\">
						<label>
							" . $this->_Translation->GetTranslation('title') . ":
							<span class=\"info\">Der Titel wird sp&auml;ter in der Titelleiste des Browsers angezeigt.</span>
						</label>
						<input type=\"text\" name=\"pageTitle\" maxlength=\"85\" />
					</div>
					<div class=\"row\">
						<label>
							Seiten-Typ:
							<span class=\"info\">TODO</span>
						</label>
						<select name=\"pageType\">
							<option value=\"text\">Text</option>
							<option value=\"gallery\">" . $this->_Translation->GetTranslation('gallery') ."</option>
						</select>
					</div>
					<div class=\"row\">
						<label>
							" . $this->_Translation->GetTranslation('language') . ":
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
						<input type=\"text\" name=\"pageEditComment\" maxlength=\"100\" value=\"" . $this->_Translation->GetTranslation('created_new_page') . "\"/>
					</div>
					<div class=\"row\">
						<label>
							Bearbeiten?
							<span class=\"info\">Soll die Seite nach dem Erstellen bearbeitet werden oder soll wieder auf die &Uuml;bersichtseite zur&uuml;ckgekehrt werden?</span>
						</label>
						<input type=\"checkbox\" name=\"pageEdit\" value=\"edit\" checked=\"true\" class=\"checkbox\"/>
					</div>
					<div class=\"row\">
						<input type=\"submit\" class=\"button\" value=\"" . $this->_Translation->GetTranslation('create') . "\" />
					</div>
				</fieldset>
			</form>";
		 	return $out;
		 }
		 
		 /**
		  * @access private
		  * @return string
		  */
		 function _Structure($TopNodeID = 0) {
		 	$pages = $this->_PageStructure->RemoveAcessDeletedPages();
		 	if(!array_key_exists($TopNodeID, $pages))
		 		return;
		 	$pages = $pages[$TopNodeID];
		 	$out = '';
		 	if(empty($pages))
		 		return;
		 	$out .= "\r\n\t\t\t<ol>\r\n";
		 	foreach($pages as $page) {
	 			if($page['access'] != 'deleted') {
	 				// block elements
		 			$out .= "\r\n\t\t\t\t<li class=\"page_type_". $page['type'] . (($page['access'] == 'deleted') ? ' strike' : '' ). "\"><span class=\"structure_row\">";
			 		// blockelement for pageactions
			 		$out .= "<span class=\"page_actions\">";
			 			// edit:
			 			if($page['access'] != 'deleted')
				 			$out .= " <a href=\"{$this->LinkUrl}page=pagestructure&amp;action=editPage&amp;pageID=" . $page['id'] . "\"><img src=\"./img/edit.png\" class=\"icon\" height=\"16\" width=\"16\" alt=\"" . $this->_Translation->GetTranslation('edit') . "\" title=\"" . $this->_Translation->GetTranslation('edit') . "\"/></a>";
			 			// info:
			 			$out .= " <a href=\"{$this->LinkUrl}page=pagestructure&amp;action=pageInfo&amp;pageID=" . $page['id'] . "\"><img src=\"./img/info.png\" class=\"icon\" height=\"16\" width=\"16\" alt=\"" . $this->_Translation->GetTranslation('info') . "\" title=\"" . $this->_Translation->GetTranslation('info') . "\"/></a>";
				 		// view:
			 			if($page['access'] != 'deleted')
			 				$out .= " <a href=\"index.php?page=" . $page['name'] . "\"><img src=\"./img/view.png\" class=\"icon\" height=\"16\" width=\"16\" alt=\"Anschauen " . $page['title'] . "\" title=\"Anschauen\"/></a>";
				 		// inlinemenu:
			 			if($page['access'] != 'deleted')
			 				$out .= " <a href=\"{$this->LinkUrl}page=pagestructure&amp;action=pageInlineMenu&amp;pageID=" . $page['id'] . "\" title=\"" . sprintf($this->_Translation->GetTranslation('edit_inlinemenu_of_%page_title%'), $page['title']) . "\"><img src=\"./img/inlinemenu.png\" class=\"icon\" height=\"16\" width=\"16\" alt=\"" . sprintf($this->_Translation->GetTranslation('edit_inlinemenu_of_%page_title%'), $page['title']) . "\" title=\"" . sprintf($this->_Translation->GetTranslation('edit_inlinemenu_of_%page_title%'), $page['title']) . "\"/></a>";
		 				// delete:
			 			if($page['access'] != 'deleted')
			 				$out .= " <a href=\"{$this->LinkUrl}page=pagestructure&amp;action=deletePage&amp;pageID=" . $page['id'] . "\"><img src=\"./img/del.png\" class=\"icon\" height=\"16\" width=\"16\" alt=\"" . sprintf($this->_Translation->GetTranslation('delete_page_%page_title%'), $page['title']) . "\" title=\"" . sprintf($this->_Translation->GetTranslation('delete_page_%page_title%'), $page['title']) . "\"/></a>";
			 		// end blockelement for pageactions
			 		$out .= '</span>';
			 		// lang:
			 		$out .= "<span class=\"page_lang\">[" . $this->_Translation->GetTranslation($page['lang']) . "]</span>";
			 		$out .= "<strong>" . $page['title'] . "</strong> (" . rawurldecode($page['name']) . ")";
			 		$out .= '</span>' . $this->_Structure($page['id']);
			 		$out .= "\t\t\t\t</li>\r\n";
	 			}
		 	}
		 	$out .= "\r\n\t\t\t</ol>\r\n\r\n";
		 	return $out;
		 }
		 
		 /**
		  * @access private
		  * @return string
		  */
		 function _showStructure($TopNodeID = 0) {
			$this->_PageStructure->LoadParentIDs();
	 		$out = $this->_Structure($TopNodeID);
			return $out;
		}
		
		/**
		 * @access private
		 * @return string
		 */
		function _editPage() {
			$pageID = GetPostOrGet('pageID');
			if(!is_numeric($pageID))
				return false;
			$out = '';
			$sql = "SELECT page_id, page_type
				FROM " . DB_PREFIX . "pages
				WHERE page_id = $pageID";
			$page_result = $this->_SqlConnection->SqlQuery($sql);
			if($page = mysql_fetch_object($page_result)) {
				$edit = null;
				switch($page->page_type) {
					case 'text':
						include_once (__ROOT__ . '/classes/page/page_extended_text.php');
						$edit = new Page_Extended_Text($this->_SqlConnection, $this->_Config, $this->_Translation, $this->_ComaLate, $this->_User);
						break;
					case 'gallery':
						include (__ROOT__ . '/classes/page/page_extended_gallery.php');
						$edit = new Page_Extended_Gallery($this->_SqlConnection, $this->_Config, $this->_Translation, $this->_ComaLate, $this->User);
						break;	
					default:
						$out .= "Der Seitentyp <strong>$page->page_type</strong> l&auml;sst sich noch nicht bearbeiten.";
						break;
				}
				if($edit !== null) {
					$this->_ComaLate->SetReplacement('ADMIN_FORM_URL', $this->FormUrl);
					$this->_ComaLate->SetReplacement('ADMIN_FORM_PAGE', $this->FormPage);
					$this->_ComaLate->SetReplacement('ADMIN_LINK_URL', $this->LinkUrl);			
					$out .= $edit->GetEditPage($page->page_id);
				}
				return $out;
			}
		}
		
		/**
		 * @access private
		 * @return string
		 */
		function _savePage() {
			$pageID = GetPostOrGet('pageID');
			$out = '';
			$sql = "SELECT page_id, page_type
				FROM " . DB_PREFIX . "pages
				WHERE page_id = $pageID";
			$page_result = $this->_SqlConnection->SqlQuery($sql);
			if($page = mysql_fetch_object($page_result)) {
				$edit = null;
				switch($page->page_type) {
					case 'text':
						include_once (__ROOT__ . '/classes/page/page_extended_text.php');
						$edit = new Page_Extended_Text($this->_SqlConnection, $this->_Config, $this->_Translation, $this->_ComaLate, $this->_User);
						break;
					case 'gallery':
						include_once (__ROOT__ . '/classes/page/page_extended_gallery.php');
						$edit = new Page_Extended_Gallery($this->_SqlConnection, $this->_Config, $this->_Translation, $this->_ComaLate, $this->_User);
						break;				
					default:
						$out .= "Der Seitentyp <strong>$page->page_type</strong> l&auuml;sst sich noch nicht bearbeiten.";
						break;
				}
				if($edit !== null) {
					$this->_ComaLate->SetReplacement('ADMIN_FORM_URL', $this->FormUrl);
					$this->_ComaLate->SetReplacement('ADMIN_FORM_PAGE', $this->FormPage);
					$this->_ComaLate->SetReplacement('ADMIN_LINK_URL', $this->LinkUrl);	
					$out .= $edit->GetSavePage($page->page_id);
				}
				if($out == '')
					return $this->_homePage();
				return $out;
			}
		}
		
		/**
		 * @access private
		 */
		function _restorePage() {
			$revision = GetPostOrGet('revision');
			$sure = GetPostOrGet('sure');
			$pageID = GetPostOrGet('pageID');
			$pageData = $this->_PageStructure->GetPageDataArray($pageID);
			$edit = 0;
			switch($pageData['type']) {
					case 'text':
						include_once (__ROOT__ . '/classes/page/page_extended_text.php');
						$edit = new Page_Extended_Text($this->_SqlConnection, $this->_Config, $this->_Translation, $this->_ComaLate, $this->_User);
						break;
					case 'gallery':
						include_once (__ROOT__ . '/classes/page/page_extended_gallery.php');
						$edit = new Page_Extended_Gallery($this->_SqlConnection, $this->_Config, $this->_Translation, $this->_ComaLate, $this->_User);
						break;	
					default:
						return "Der Seitentyp <strong>{$pageData['type']}</strong> l&auml;sst sich noch nicht bearbeiten.";
				}
			$out = '';
			$this->_ComaLate->SetReplacement('ADMIN_FORM_URL', $this->FormUrl);
			$this->_ComaLate->SetReplacement('ADMIN_FORM_PAGE', $this->FormPage);
			$this->_ComaLate->SetReplacement('ADMIN_LINK_URL', $this->LinkUrl);	
			if($sure == 1)
				$edit->RestoreRevision($pageID, $revision);
			else
				$out .= $edit->GetRestoreRevisionPage($pageID, $revision);
			if($out != '')
				return $out;
			return $this->_homePage();
				
		}
		/**
		 * @access private
		 * @return string
		 */
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
			$lastid = $this->_PageStructure->AddNewPage($page_name, $page_title, $page_lang, $page_access, $page_type, $page_parent_id, $page_edit_comment);
		
			if($page_edit != '')
				header("Location: {$this->LinkUrl}page=pagestructure&action=editPage&pageID=$lastid");
			else
				header("Location: {$this->LinkUrl}page=pagestructure");
	
		}
		
		/**
		 * @access private
		 * @return string
		 */
		function _pagePath($PageID = 0) {
			$out = '';
			$sql = "SELECT *
				FROM " . DB_PREFIX . "pages
				WHERE page_id=$PageID";
			$page_result = $this->_SqlConnection->SqlQuery($sql);
			while($page = mysql_fetch_object($page_result)) {
				if($PageID == $page->page_id)
					$out = " <span title=\"$page->page_title\">" . rawurldecode($page->page_name) . "</span>";
				else
					$out = "<a href=\"{$this->LinkUrl}page=pagestructure&amp;action=pageInfo&amp;pageID=$page->page_id\" title=\"$page->page_title\">" . rawurldecode($page->page_name) ."</a>" . $out;
				if($page->page_parent_id != 0)
					$out = '<strong>/</strong>' . $out;
				$sql = "SELECT *
					FROM " . DB_PREFIX . "pages
					WHERE page_id=$page->page_parent_id";
				$page_result = $this->_SqlConnection->SqlQuery($sql);
			}
			return $out;
		}
		/**
		 * inlineMenu
		 * inlinemenu-management
		 * @access private
		 */
		function _inlineMenu() {
		
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
					$out .= $this->_Translation->GetTranslation('at_the_moment_there_is_no_inlinemenu_for_this_page_created,_should_this_be_done_now') . "<br />
					<a href=\"{$this->LinkUrl}page=pagestructure&amp;action=pageInlineMenu&amp;action2=create&amp;pageID=$pageID\" title=\"" . $this->_Translation->GetTranslation('yes') . "\" class=\"button\">" . $this->_Translation->GetTranslation('yes') . "</a>
					<a href=\"{$this->LinkUrl}page=pagestructure\" title=\"" . $this->_Translation->GetTranslation('no') . "\" class=\"button\">" . $this->_Translation->GetTranslation('no') . "</a>";	
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
		
		/**
		 * @access private
		 * @return string
		 */
		function _InlineMenuEditEntryPage($PageID) {
			
			$entryID = GetPostOrGet('entryID');
			if($entryData = $this->_PageStructure->LoadInlineMenuEntry($entryID)) {
				$out = '';
				
				if($entryData['type'] == 'link') {
					$out .= "<form action=\"{$this->FormUrl}\" method=\"post\">
						<input type=\"hidden\" name=\"{$this->FormPage}\" value=\"pagestructure\" />
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
					$out .= "<form action=\"{$this->FormUrl}\" method=\"post\">
						<input type=\"hidden\" name=\"{$this->FormPage}\" value=\"pagestructure\" />
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
					$out .= "<form action=\"{$this->FormUrl}\" method=\"post\">
						<input type=\"hidden\" name=\"{$this->FormPage}\" value=\"pagestructure\" />
						<input type=\"hidden\" name=\"action\" value=\"pageInlineMenu\" />
						<input type=\"hidden\" name=\"pageID\" value=\"$PageID\" />
						<input type=\"hidden\" name=\"entryID\" value=\"$entryID\" />
						<input type=\"hidden\" name=\"action2\" value=\"saveEntry\" />
						<input type=\"hidden\" name=\"type\" value=\"intern\" />
						<fieldset>
						<legend>Erstelle neuen InlineMen&uuml;-Interner-Link:</legend>
						<div class=\"row\"><label>Link-Titel<span class=\"info\">Ein wenig Text der den Link deutlich macht.</span></label><input type=\"text\" name=\"text\" value=\"" . $entryData['text'] . "\" /></div>
						<div class=\"row\"><label>Interne Seite<span class=\"info\">Das ist die interne Seite, auf die der Link sp&auml;ter f&uuml;hren soll.</span></label><select name=\"link\">";
					$this->_PageStructure->LoadParentIDs();
					$out .= $this->_PageStructure->PageStructurePullDown(0, 0, '', -1, substr($entryData['link'], 15));
					$out .= "</select></div>
						<div class=\"row\">
							<input type=\"submit\" class=\"button\" value=\"" . $this->_Translation->GetTranslation('save') . "\" />
						</div>
						</fieldset>
						</form>";
				}
				else if($entryData['type'] == 'download') {
					$out .= "<form action=\"{$this->FormUrl}\" method=\"post\">
						<input type=\"hidden\" name=\"{$this->FormPage}\" value=\"pagestructure\" />
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
					$files_result = $this->_SqlConnection->SqlQuery($sql);
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
		
		/**
		 * @access private
		 * @return string
		 */
		function _InlineMenuRemoveEntryPage ($PageID) {
			$entryID = GetPostOrGet('entryID');
			$confirmation = GetPostOrGet('confirmation');
		
			if($confirmation == 1) {
				$this->_PageStructure->RemoveInlineMenuEntry($entryID);
				$this->_PageStructure->GenerateInlineMenu($PageID);
				return $this->_InlineMenuHomePage($PageID);
			}
			else {
				$sql = "SELECT *
					FROM " . DB_PREFIX . "inlinemenu_entries
					WHERE inlineentry_id=$entryID";
 				$entryResult = $this->_SqlConnection->SqlQuery($sql);
 				if($entry = mysql_fetch_object($entryResult)) {
					return "Sind sie sicher, dass sie das Element &quot;$entry->inlineentry_text&quot; unwiederruflich l&ouml;schen m&ouml;chten?<br />
						<a href=\"{$this->LinkUrl}page=pagestructure&amp;action=pageInlineMenu&amp;entryID=$entryID&amp;action2=removeEntry&amp;confirmation=1&amp;pageID=$PageID\" class=\"button\">" . $this->_Translation->GetTranslation('yes') . "</a >
						<a href=\"{$this->LinkUrl}page=pagestructure&amp;action=pageInlineMenu&amp;pageID=$PageID\"class=\"button\">" . $this->_Translation->GetTranslation('no') . "</a >";
 				}
			}
		}
		
		/**
		 * @param integer PageID
		 * @return string
		 * @access private
		 */
		function _InlineMenuSetImagePage ($PageID) {
			// Get data from header
			$imagePath = GetPostOrGet('imagePath');
			// FIXME: load from config?
			$imgmax2 = 200;
			// Get data form config
			$inlinemenuFolder = $this->_Config->Get('thumbnailfolder', 'data/thumbnails/');
			// resize the image if it doesn't exist
			$imageResizer = new ImageConverter($imagePath);
			//$sizes = $imageResizer->CalcSizeByMaxWidth($imgmax2);
			$sizes = array();
			if($imageResizer->Size[0] > $imgmax2)
				$sizes = $imageResizer->CalcSizeByMaxWidth($imgmax2);
			else if($imageResizer->Size[1] > $imgmax2)
				$sizes = $imageResizer->CalcSizeByMax($imgmax2);
				
				
			$thumbnail = $imagePath;
			if(count($sizes) == 2)
				$thumbnail = $imageResizer->SaveResizedTo($sizes[0], $sizes[1], $inlinemenuFolder, $sizes[0] . 'x' . $sizes[1] . '_');
			
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
		 * @access private
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
		 * @access private
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
		 * @access private
		 */
		function _InlineMenuAddNewEntryDialogPage($PageID) {
			
			// Get data from header
			$type = GetPostOrGet('type');
			
			$out = '';
			
			if($type == 'link') {
				$out .= "<form action=\"{$this->FormUrl}\" method=\"post\">
						<input type=\"hidden\" name=\"{$this->FormPage}\" value=\"pagestructure\" />
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
				$out .= "<form action=\"{$this->FormUrl}\" method=\"post\">
						<input type=\"hidden\" name=\"{$this->FormPage}\" value=\"pagestructure\" />
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
				$out .= "<form action=\"{$this->FormUrl}\" method=\"post\">
						<input type=\"hidden\" name=\"{$this->FormPage}\" value=\"pagestructure\" />
						<input type=\"hidden\" name=\"action\" value=\"pageInlineMenu\" />
						<input type=\"hidden\" name=\"pageID\" value=\"$PageID\" />
						<input type=\"hidden\" name=\"action2\" value=\"addNewEntry\" />
						<input type=\"hidden\" name=\"type\" value=\"intern\" />
						<fieldset>
						<legend>Erstelle neuen InlineMen&uuml;-Interner-Link:</legend>
						<div class=\"row\"><label>Link-Titel<span class=\"info\">Ein wenig Text der den Link deutlich macht.</span></label><input type=\"text\" name=\"text\" value=\"\" /></div>
						<div class=\"row\"><label>Interne Seite<span class=\"info\">Das ist die interne Seite, auf die der Link sp&auml;ter f&uuml;hren soll.</span></label><select name=\"link\">";
						$this->_PageStructure->LoadParentIDs();
						$out .= $this->_PageStructure->PageStructurePulldown(0);
				$out .= "</select></div>
						<div class=\"row\">
							<input type=\"submit\" class=\"button\" value=\"" . $this->_Translation->GetTranslation('save') . "\" />
						</div>
						</fieldset>
						</form>";
			}
			else if($type == 'download') {
				$out .= "<form action=\"{$this->FormUrl}\" method=\"post\">
						<input type=\"hidden\" name=\"{$this->FormPage}\" value=\"pagestructure\" />
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
					$files_result = $this->_SqlConnection->SqlQuery($sql);
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
				$out = "<form action=\"{$this->FormUrl}\" method=\"post\">
						<input type=\"hidden\" name=\"{$this->FormPage}\" value=\"pagestructure\" />
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
		
		/**
		 * @access private
		 * @return string
		 */
		function _InlineMenuMoveUpPage($PageID) {
			$entrySortID = GetPostOrGet('entrySortID');
			$this->_PageStructure->InlineMenuEntryMoveUp($entrySortID, $PageID);
			return $this->_InlineMenuHomePage($PageID);
		}
		
		/**
		 * @access private
		 * @return string
		 */
		function _InlineMenuMoveDownPage($PageID) {
			$entrySortID = GetPostOrGet('entrySortID');
			$this->_PageStructure->InlineMenuEntryMoveDown($entrySortID, $PageID);
			return $this->_InlineMenuHomePage($PageID);
		}
		
		/**
		 * @access private
		 * @return string
		 */
		function _InlineMenuSetImageTitlePage($PageID) {
			$imageTitle = GetPostOrGet('imageTitle');
			$this->_PageStructure->SetInlineMenuImageTitle($PageID, $imageTitle);
			return $this->_InlineMenuHomePage($PageID);
		}
		
		/**
		 * @access private
		 * @return string
		 */
		function _InlineMenuRemoveImagePage($PageID) {
			$this->_PageStructure->RemoveInlineMenuImage($PageID);
			return $this->_InlineMenuHomePage($PageID);
		}
		
		/**
		 * @access private
		 * @return string
		 */
		function _InlineMenuSelectImage($PageID) {
			$imagePath = $this->_PageStructure->GetInlineMenuData($PageID, 'image');
			$sql = "SELECT *
				FROM " . DB_PREFIX . "files
				WHERE file_type LIKE 'image/%'
				ORDER BY file_name ASC";
			$images_result = $this->_SqlConnection->SqlQuery($sql);
			$imgmax = 100;
			$imgmax2 = 200;
			$inlinemenuFolder = 'data/thumbnails/';
			$out = "<form action=\"{$this->FormUrl}\" method=\"post\">
				<input type=\"hidden\" name=\"{$this->FormPage}\" value=\"pagestructure\"/>
				<input type=\"hidden\" name=\"action\" value=\"pageInlineMenu\"/>
				<input type=\"hidden\" name=\"pageID\" value=\"$PageID\"/>
				<input type=\"hidden\" name=\"action2\" value=\"setImage\"/>
				<fieldset>
				<legend>" . $this->_Translation->GetTranslation('inlinemenu_image') . "</legend>
				<div class=\"row\"><div class=\"imagesblock\">";
			while($image = mysql_fetch_object($images_result)) {
				$imageResizer = new ImageConverter($image->file_path);
				$sizes = $imageResizer->CalcSizeByMax($imgmax);
				if($sizes[0] > $imageResizer->Size[0] && $sizes[1] > $imageResizer->Size[1])
					$sizes = $imageResizer->Size;
				$thumbnail = $imageResizer->SaveResizedTo($sizes[0], $sizes[1], $inlinemenuFolder, $sizes[0] . 'x' . $sizes[1] . '_');
				if(file_exists($thumbnail)) {
					//list($originalWidth, $originalHeight) = getimagesize($thumbnail);
					
					$out .= "<div class=\"imageblock\">
				<a href=\"" . generateUrl($image->file_path) . "\">
				<img style=\"margin-top:" . ($imgmax - $sizes[1]) . "px;\" src=\"" . generateUrl($thumbnail) . "\" alt=\"". basename($thumbnail) ."\" /></a><br />
				<input type=\"radio\" name=\"imagePath\" " .(($imagePath == $image->file_path) ? 'checked="checked" ' : '') . " value=\"$image->file_path\"/></div>";
				}
			}
			$out .= "</div></div>
				<div class=\"row noform\"><input type=\"submit\" value=\"" . $this->_Translation->GetTranslation('apply') . "\" class=\"button\"/>
				<a href=\"{$this->LinkUrl}page=pagestructure&amp;action=pageInlineMenu&amp;pageID=$PageID\" class=\"button\">" . $this->_Translation->GetTranslation('back') . "</a></div></fieldset></form>";
				
			
			return $out;
		}
		
		/**
		 * @access private
		 * @return string
		 */
		function _InlineMenuHomePage($PageID) {
			
			
			$image = 'Noch kein Bild gesetzt';
			$thumbPath = $this->_PageStructure->GetInlineMenuData($PageID, 'imageThumb');
			$imagePath = $this->_PageStructure->GetInlineMenuData($PageID, 'image');
			$imageTitle = $this->_PageStructure->GetInlineMenuData($PageID, 'imageTitle');
			
			if(file_exists($thumbPath))
				$image = "<img alt=\"{$imageTitle}\" src=\"" . generateUrl($thumbPath) . "\"/>";
			else if(file_exists($imagePath)){
				// maximum width
				$imgmax2 = 200;
				// if it isn't "wide" enough
				$imgmax3 = 350;
				$inlinemenuFolder = 'data/thumbnails/';
				$imageResizer = new ImageConverter($imagePath);
				$sizes = array();
				// it is "wide" enough??
				if($imageResizer->Size[0] > $imgmax2)
					$sizes = $imageResizer->CalcSizeByMaxWidth($imgmax2);
				// make sure, that it isn't to big
				else if($imageResizer->Size[1] > $imgmax3)
					$sizes = $imageResizer->CalcSizeByMax($imgmax2);
				
				$thumbnail = $imagePath;
				// is there something to resize?
				if(count($sizes) == 2)
					$thumbnail = $imageResizer->SaveResizedTo($sizes[0], $sizes[1], $inlinemenuFolder, $sizes[0] . 'x' . $sizes[1] . '_');
				if($thumbnail !== false)
					$image = "<img alt=\"{$imageTitle}\" src=\"" . generateUrl($thumbnail) . "\"/>";
			}	
			$out = "
				<fieldset>
					<legend>" . $this->_Translation->GetTranslation('inlinemenu') . "</legend>
				<div class=\"row\">
						<label class=\"row\">
							" . $this->_Translation->GetTranslation('inlinemenu_image') . ":
							<span class=\"info\">Das ist der Pfad zu dem Bild, das dem Zusatzmen&uuml; zugeordnet wird, es kann der Einfachheit halber aus den bereits hochgeladenen Bildern ausgew&auml;hlt werden.</span>
						</label>
						$image
				</div>
				<div class=\"row\">
					<a href=\"{$this->LinkUrl}page=pagestructure&amp;action=pageInlineMenu&amp;pageID=$PageID&amp;action2=selectImage\" class=\"button\">Bild ausw&auml;hlen/ver&auml;ndern</a>
					" .((file_exists($thumbPath)) ?  "<a href=\"{$this->LinkUrl}page=pagestructure&amp;action=pageInlineMenu&amp;pageID=$PageID&amp;action2=removeImage\" class=\"button\">Bild entfernen</a>" : '') . "
				</div>
				<form action=\"{$this->FormUrl}\" method=\"post\">
				<input type=\"hidden\" name=\"{$this->FormPage}\" value=\"pagestructure\" />
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
					<input type=\"submit\" class=\"button\" value=\"" . $this->_Translation->GetTranslation('save') . "\" />
				</div>
				</form>";
			$sql = "SELECT *
				FROM " . DB_PREFIX . "inlinemenu_entries
				WHERE inlineentry_page_id = $PageID
				ORDER BY inlineentry_sortid ASC";
			$entries_result = $this->_SqlConnection->SqlQuery($sql);
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
						<a href=\"{$this->LinkUrl}page=pagestructure&amp;action=pageInlineMenu&amp;pageID=$PageID&amp;entrySortID=$entry->inlineentry_sortid&amp;action2=moveEntryUp#entries\"><img src=\"./img/up.png\" alt=\"" . $this->_Translation->GetTranslation('move_up') ."\" title=\"" . $this->_Translation->GetTranslation('move_up') ."\" /></a>
						<a href=\"{$this->LinkUrl}page=pagestructure&amp;action=pageInlineMenu&amp;pageID=$PageID&amp;entrySortID=$entry->inlineentry_sortid&amp;action2=moveEntryDown#entries\"><img src=\"./img/down.png\" alt=\"" . $this->_Translation->GetTranslation('move_down') ."\" title=\"" . $this->_Translation->GetTranslation('move_down') ."\" /></a>
						<a href=\"{$this->LinkUrl}page=pagestructure&amp;action=pageInlineMenu&amp;pageID=$PageID&amp;entryID=$entry->inlineentry_id&amp;action2=editEntry\"><img src=\"./img/edit.png\" alt=\"" . $this->_Translation->GetTranslation('edit') ."\" title=\"" . $this->_Translation->GetTranslation('edit') ."\" /></a>
						<a href=\"{$this->LinkUrl}page=pagestructure&amp;action=pageInlineMenu&amp;pageID=$PageID&amp;entryID=$entry->inlineentry_id&amp;action2=removeEntry\"><img src=\"./img/del.png\" alt=\"" . $this->_Translation->GetTranslation('delete') ."\" title=\"" . $this->_Translation->GetTranslation('delete') ."\" /></a>
						
					</td>
					</tr>";
				}
				$out .= "</table>
					</div>
					<div class=\"row\"><a href=\"{$this->LinkUrl}page=pagestructure&amp;action=pageInlineMenu&amp;pageID=$PageID&amp;action2=addNewEntryDialog\" class=\"button\">Einen Eintrag hinzuf&uuml;gen</a></div>
					</fieldset>";
			return $out;
		}
		
		/**
		 * @access private
		 * @return string
		 */
		function _infoPage() {
			$pageID =  GetPostOrGet('pageID');
			$action = GetPostOrGet('action2');
			
			$pageData = $this->_PageStructure->GetPageDataArray($pageID);
			if($pageData === false)
				return $this->_homePage();
				
			// get some config-values
			$dateDayFormat = $this->_Config->Get('date_day_format', '');
			$dateTimeFormat = $this->_Config->Get('date_time_format', '');
			$dateFormat = $dateDayFormat . ' ' . $dateTimeFormat;
			$this->_ComaLate->SetCondition('pageaccess_default');
			$this->_ComaLate->SetCondition('pagepath_default');
			$this->_ComaLate->SetCondition('pagename_default');
			$this->_ComaLate->SetReplacement('LANG_PATH', $this->_Translation->GetTranslation('path'));
			
			switch($action){
				case 'changeAccess':
					$this->_ComaLate->SetCondition('pageaccess_edit');
					$this->_ComaLate->SetCondition('pageaccess_default', false);
					$this->_ComaLate->SetReplacement('LANG_PUBLIC', $this->_Translation->GetTranslation('public'));
					$this->_ComaLate->SetReplacement('LANG_PRIVATE', $this->_Translation->GetTranslation('private'));
					$this->_ComaLate->SetReplacement('LANG_HIDDEN', $this->_Translation->GetTranslation('hidden'));
					$this->_ComaLate->SetReplacement('LANG_DELETED', $this->_Translation->GetTranslation('deleted'));
					$this->_ComaLate->SetReplacement('PAGEACCESS_PUBLIC_SELECTED', ('public' == $pageData['access']) ? 'selected="selected"' : '');
					$this->_ComaLate->SetReplacement('PAGEACCESS_PRIVATE_SELECTED',  ('private' == $pageData['access']) ? 'selected="selected"' : '');
					$this->_ComaLate->SetReplacement('PAGEACCESS_HIDDEN_SELECTED',  ('hidden' == $pageData['access']) ? 'selected="selected"' : '');
					$this->_ComaLate->SetReplacement('PAGEACCESS_DELETED_SELECTED',  ('deleted' == $pageData['access']) ? 'selected="selected"' : '');
					
					$this->_ComaLate->SetReplacement('LANG_SAVE', $this->_Translation->GetTranslation('save'));
					break;
				case 'saveAccess':
					$newPageAccess = GetPostOrGet('pageAccess');
					if($newPageAccess == $pageData['access'])
						break;
					$logMessage = 'Changed Page-Access from ' . $pageData['access'] . ' to '. $newPageAccess;
					$this->_PageStructure->LogPage($pageID, $logMessage);
					$this->_PageStructure->ChangePageAccess($pageID, $newPageAccess);
					$pageData['date'] = mktime();
					$pageData['creator'] = $this->_User->ID;
					$pageData['access'] = $newPageAccess;
					$pageData['comment'] = $logMessage;
					break;
				case 'changePath':
					$this->_ComaLate->SetReplacement('LANG_SAVE', $this->_Translation->GetTranslation('save'));
					$this->_ComaLate->SetReplacement('LANG_NONE', $this->_Translation->GetTranslation('none'));
					$this->_ComaLate->SetReplacement('LANG_PATH', $this->_Translation->GetTranslation('subpage_of'));
					$this->_ComaLate->SetCondition('pagepath_default', false);
					$this->_ComaLate->SetCondition('pagepath_edit');
					break;
				case 'savePath':
					$newPatentID = GetPostOrGet('pageParentID');
					if($newPatentID == $pageData['parentID'])
						break;
					$logMessage = 'Changed ParentID form ' . $pageData['parentID'] . ' to ' . $newPatentID;
					$this->_PageStructure->LogPage($pageID, $logMessage);
					$this->_PageStructure->ChangePageParentID($pageID, $newPatentID);		
					$pageData['date'] = mktime();
					$pageData['creator'] = $this->_User->ID;
					$pageData['parentID'] = $newPatentID;
					$pageData['comment'] = $logMessage;
					break;
				case 'changeName':
					$this->_ComaLate->SetReplacement('LANG_SAVE', $this->_Translation->GetTranslation('save'));
					$this->_ComaLate->SetCondition('pagename_default', false);
					$this->_ComaLate->SetCondition('pagename_edit');
					break;
				case 'saveName':
					$newName = GetPostOrGet('pageName');
					$newName = str_replace(' ', '_', $newName);
					$newNameRawUrl = rawurlencode($newName);
					if($newNameRawUrl == $pageData['name'])
						break;
					$logMessage = 'Changed Name form ' . rawurldecode($pageData['name']) . ' to ' . $newName;
					$this->_PageStructure->LogPage($pageID, $logMessage);
					$this->_PageStructure->ChangePageName($pageID, $newNameRawUrl);		
					$pageData['date'] = mktime();
					$pageData['creator'] = $this->_User->ID;
					$pageData['comment'] = $logMessage;
					$pageData['name'] = $newNameRawUrl;
					break;
			}	
			
			$this->_ComaLate->SetReplacement('LANG_TITLE', $this->_Translation->GetTranslation('title'));
			$this->_ComaLate->SetReplacement('LANG_EDIT', $this->_Translation->GetTranslation('edit'));
			$this->_ComaLate->SetReplacement('LANG_NAME', $this->_Translation->GetTranslation('name'));
			$this->_ComaLate->SetReplacement('LANG_TYPE', $this->_Translation->GetTranslation('type'));
			$this->_ComaLate->SetReplacement('LANG_ACCESS', $this->_Translation->GetTranslation('access'));
			$this->_ComaLate->SetReplacement('LANG_LANGUAGE', $this->_Translation->GetTranslation('language'));
			$this->_ComaLate->SetReplacement('LANG_EDITED_BY', $this->_Translation->GetTranslation('edited_by'));
			$this->_ComaLate->SetReplacement('LANG_LAST_CHANGE', $this->_Translation->GetTranslation('last_change'));
			$this->_ComaLate->SetReplacement('LANG_INLINEMENU', $this->_Translation->GetTranslation('inlinemenu'));
			$this->_ComaLate->SetReplacement('LANG_SUBPAGES', $this->_Translation->GetTranslation('subpages'));
			$this->_ComaLate->SetReplacement('LANG_HISTORY', $this->_Translation->GetTranslation('history'));
			$this->_ComaLate->SetReplacement('LANG_DATE', $this->_Translation->GetTranslation('date'));
			$this->_ComaLate->SetReplacement('LANG_USER', $this->_Translation->GetTranslation('user'));
			$this->_ComaLate->SetReplacement('LANG_COMMENT', $this->_Translation->GetTranslation('comment'));
			$this->_ComaLate->SetReplacement('LANG_ACTIONS', $this->_Translation->GetTranslation('actions'));
			$this->_ComaLate->SetReplacement('LANG_ENTRIES', $this->_Translation->GetTranslation('entries'));
			$this->_ComaLate->SetReplacement('LANG_VIEW', $this->_Translation->GetTranslation('view'));
			$this->_ComaLate->SetReplacement('LANG_RESTORE', $this->_Translation->GetTranslation('restore'));
			
			$this->_ComaLate->SetReplacement('PAGE_ID', $pageID);
			$this->_ComaLate->SetReplacement('TITLE_VALUE', $pageData['title']);
			$this->_ComaLate->SetReplacement('NAME_VALUE', rawurldecode($pageData['name']));
			$this->_ComaLate->SetReplacement('TYPE_VALUE', $this->_Translation->GetTranslation($pageData['type']));
			$this->_ComaLate->SetReplacement('ACCESS_VALUE', $this->_Translation->GetTranslation($pageData['access']));
			$this->_ComaLate->SetReplacement('LANGUAGE_VALUE', $this->_Translation->GetTranslation($pageData['lang']));
			$this->_ComaLate->SetReplacement('EDITED_BY_VALUE', $this->_ComaLib->GetUserByID($pageData['creator']));
			$this->_ComaLate->SetReplacement('LAST_CHANGE_VALUE', date($dateFormat, $pageData['date']));
			
			$history = array();
			$history = $this->_PageStructure->GetPageHistory($pageID);
			$inlineMenuEntriesCount = $this->_PageStructure->GetInlineMenuEntriesCount($pageID);
			
			$historyCount = count($history);
			for($i = 0; $i < $historyCount; $i++) {
				$history[$i]['HISTORY_PAGE_DATE'] = date($dateFormat,$history[$i]['HISTORY_PAGE_DATE']);
				$history[$i]['HISTORY_PAGE_USER'] = $this->_ComaLib->GetUserByID($history[$i]['HISTORY_PAGE_USER']);
			}
			$this->_ComaLate->SetReplacement('INLINEMENU_ENTRIES_COUNT', $inlineMenuEntriesCount);
			$this->_ComaLate->SetReplacement('HISTORY_COUNT', $historyCount + 1);
			$this->_ComaLate->SetReplacement('PAGE_HISTORY', $history);
			$this->_ComaLate->SetReplacement('PAGE_COMMENT', $pageData['comment']);
			
			$template = '<form action="' .$this->FormUrl . '" method="post">
						<input type="hidden" name="' .$this->FormPage . '" value="pagestructure" />
						<input type="hidden" name="action" value="pageInfo" />
						<input type="hidden" name="pageID" value="{PAGE_ID}" />
						<table>
						<tr>
							<th>{LANG_TITLE}</th>
							<td>{TITLE_VALUE} <a href="' .$this->LinkUrl . 'page=pagestructure&amp;action=editPage&amp;pageID={PAGE_ID}"><img src="./img/edit.png" height="16" width="16" alt="{LANG_EDIT}" title="{LANG_EDIT}"/></a></td>
						</tr>
						<tr>
							<th>{LANG_NAME}</th>
							<td>
								<pagename_default:condition>
								{NAME_VALUE} <a href="' .$this->LinkUrl . 'page=pagestructure&amp;action=pageInfo&amp;pageID={PAGE_ID}&amp;action2=changeName"><img src="./img/edit.png" height="16" width="16" alt="{LANG_EDIT}" title="{LANG_EDIT}"/></a>
								</pagename_default>
								<pagename_edit:condition>
								<input type="hidden" name="action2" value="saveName" />
								<input type="text" maxlength="28" name="pageName" value="{NAME_VALUE}" /><input type="submit" value="{LANG_SAVE}" class="button" />
								</pagename_edit>
							</td>
						</tr>
						<tr>
							<th>{LANG_TYPE}</th>
							<td>{TYPE_VALUE}</td>
						</tr>
						<tr>
							<th>{LANG_ACCESS}</th>
							<td>
								<pageaccess_default:condition>
								{ACCESS_VALUE} <a href="' .$this->LinkUrl . 'page=pagestructure&amp;action=pageInfo&amp;pageID={PAGE_ID}&amp;action2=changeAccess"><img src="./img/edit.png" height="16" width="16" alt="{LANG_EDIT}" title="{LANG_EDIT}"/></a>
								</pageaccess_default>
								<pageaccess_edit:condition>
								<input type="hidden" name="action2" value="saveAccess" />
								<select name="pageAccess">
									<option value="public" {PAGEACCESS_PUBLIC_SELECTED}>{LANG_PUBLIC}</option>
									<option value="private" {PAGEACCESS_PRIVATE_SELECTED}>{LANG_PRIVATE}</option>
									<option value="hidden" {PAGEACCESS_HIDDEN_SELECTED}>{LANG_HIDDEN}</option>
									<option value="deleted" {PAGEACCESS_DELETED_SELECTED}>{LANG_DELETED}</option>
								</select>
								<input type="submit" value="{LANG_SAVE}" class="button" />
								</pageaccess_edit>
							</td>
						</tr>
						<tr>
							<th>{LANG_LANGUAGE}</th>
							<td>{LANGUAGE_VALUE}</td>
						</tr>
						<tr>					
							<th>{LANG_PATH}</th>
							<td>
								<pagepath_default:condition>
								<a href="' . $this->LinkUrl . 'page=pagestructure">root</a><strong>/</strong> ' . $this->_pagePath($pageID) . ' <a href="' .$this->LinkUrl . 'page=pagestructure&amp;action=pageInfo&amp;pageID={PAGE_ID}&amp;action2=changePath"><img src="./img/edit.png" height="16" width="16" alt="{LANG_EDIT}" title="{LANG_EDIT}"/></a>
								</pagepath_default>
								<pagepath_edit:condition>
								<input type="hidden" name="action2" value="savePath" />
								<select name="pageParentID">
									<option value="0">{LANG_NONE}</option>';
					if($action == 'changePath') {
						$this->_PageStructure->LoadParentIDs();
						$template .= $this->_PageStructure->PageStructurePulldown(0, 0, '', $pageID, $pageData['parentID']);
					}
					$template .= '</select><input type="submit" value="{LANG_SAVE}" class="button" /></pagepath_edit>
							</td>
						</tr>
						<tr>
							<th>{LANG_EDITED_BY}</th>
							<td>{EDITED_BY_VALUE}</td>
						</tr>
						<tr>
							<th>{LANG_LAST_CHANGE}</th>
							<td>{LAST_CHANGE_VALUE}</td>
						</tr>
						<tr>
							<th>{LANG_INLINEMENU}</th>
							<td>{INLINEMENU_ENTRIES_COUNT} {LANG_ENTRIES} <a href="' . $this->LinkUrl . 'page=pagestructure&amp;action=pageInlineMenu&amp;pageID={PAGE_ID}"><img src="./img/inlinemenu.png" height="16" width="16" alt="{LANG_INLINEMENU}" title="{LANG_ILNINEMENU}"/></a></td>
						</tr>
					</table>
				</form>
				<h2>{LANG_SUBPAGES}</h2>
							<script type="text/javascript" language="JavaScript" src="system/functions.js"></script>
				' . $this->_showStructure($pageID) . '
				<script type="text/javascript" language="JavaScript">
					SetHover(\'span\', \'structure_row\', \'structure_row_hover\', function additional() {document.getElementById(\'menu\').className = \'\';});
				</script>
				<h2>{LANG_HISTORY} ({HISTORY_COUNT})</h2>
					<table class="full_width">
						<thead>
							<tr>
								<th>{LANG_DATE}</th>
								<th>{LANG_USER}</th>
								<th>{LANG_TITLE}</th>
								<th>{LANG_COMMENT}</th>
								<th>{LANG_ACTIONS}</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>{LAST_CHANGE_VALUE}</td>
								<td>{EDITED_BY_VALUE}</td>
								<td>{TITLE_VALUE}</td>
								<td>{PAGE_COMMENT}</td>
								<td>
									<a href="index.php?page={PAGE_ID}"><img src="./img/view.png" height="16" width="16" alt="{LANG_VIEW}" title="{LANG_VIEW}"/></a>
									<a href="' .$this->LinkUrl . 'page=pagestructure&amp;action=editPage&amp;pageID={PAGE_ID}"><img src="./img/edit.png" height="16" width="16" alt="{LANG_EDIT}" title="{LANG_EDIT}"/></a>
								</td>
							</tr>
						<PAGE_HISTORY:loop>
							<tr>
								<td>{HISTORY_PAGE_DATE}</td>
								<td>{HISTORY_PAGE_USER}</td>
								<td>{HISTORY_PAGE_TITLE}</td>
								<td>{HISTORY_PAGE_COMMENT}</td>
								<td>
									<a href="index.php?page={PAGE_ID}&amp;change={HISTORY_PAGE_REVISION}"><img src="./img/view.png" height="16" width="16" alt="{LANG_VIEW}" title="{LANG_VIEW}"/></a>
									<a href="' .$this->LinkUrl . 'page=pagestructure&amp;action=editOldPage&amp;pageID={PAGE_ID}&amp;revision={HISTORY_PAGE_REVISION}"><img src="./img/edit.png" height="16" width="16" alt="{LANG_EDIT}" title="{LANG_EDIT}"/></a>
									<a href="' .$this->LinkUrl . 'page=pagestructure&amp;action=restorePage&amp;pageID={PAGE_ID}&amp;revision={HISTORY_PAGE_REVISION}"><img src="./img/restore.png" height="16" width="16" alt="{LANG_RESTORE}" title="{LANG_RESTORE}"/></a>
								</td>
							</tr>
						</PAGE_HISTORY>
						</tbody>
					</table>';
					
			return $template;
		}
 	}