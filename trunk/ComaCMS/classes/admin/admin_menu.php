<?php
/**
 * @package ComaCMS
 * @subpackage AdminInterface
 * @copyright (C) 2005-2006 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : admin_menu.php
 # created              : 2005-01-28
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
 	require_once __ROOT__ . '/classes/menu.php';
 	require_once __ROOT__ . '/classes/pagestructure.php';
 	require_once __ROOT__ . '/lib/formmaker/formmaker.class.php';
 	
	/**
	 * @package ComaCMS
	 */
	 
	class Admin_Menu extends Admin{
	 	
	 	/**
	 	 * Menufunktions
	 	 * @access private
	 	 * @var Menu MenuClass
	 	 */
	 	var $_Menu;
	 	
	 	
	 	function _Init() {
	 		$this->_Menu = new Menu($this->_SqlConnection);
	 	}
	 	/**
	 	 * Gets HTML out from the different parts of the Menuengine
	 	 * @access public
	 	 * @param string Action parts name of the Menuengine
	 	 * @return string HTML Code of the menu part
	 	 */
	 	function GetPage($Action = '') {
	 		
	 		// Set headline of the page
	 		$template = "\r\n\t\t\t<h2>{LANG_MENU_EDITOR}</h2>\r\n";
	 		$this->_ComaLate->SetReplacement('LANG_MENU_EDITOR', $this->_Translation->GetTranslation('menu-editor'));
	 		
	 		// Switch between the subpages of the menueditor
	 		switch ($Action) {
	 			case 'new_menu_entry':
	 				// Returns a formular to choose the type of the menuentry	
	 				$template .= $this->_ChooseEntryType('new');
	 				break;
	 			
	 			case 'new_menu_entry_second':
	 				// Returns a formular to choose a type for the menuentry
	 				$template .= $this->_NewMenuEntry();
	 				break;
	 			
	 			case 'add_menu_entry':	
	 				// Returns a formular to correct any errors if there are any or set the user back to showmenu
	 				$template .= $this->_AddMenuEntry();
	 				break;
	 			
	 			case 'edit_menu_entry':
	 				// Returns a formular to choose the type of the menuentry
	 				$template .= $this->_ChooseEntryType('edit');
	 				break;
	 			
	 			case 'edit_menu_entry_second':
	 				// Returns a formular to edit an existing menu entry	
	 				$template .= $this->_EditMenuEntry();
	 				break;
	 			
	 			case 'check_menu_entry':
	 				// Return a formular to edit any errors if there are any or set the user back to showmenu
	 				$template .= $this->_CheckMenuEntry();
	 				break;
	 			
	 			case 'move_entry_up':
	 				// Move an entry one step up
	 				$template .= $this->_Menu->ItemMoveUp(GetPostOrGet('menu_entry_orderid'), GetPostOrGet('menu_id'));
	 				// Set the user back to the menuoverview
	 				$template .= $this->_ShowMenu();
	 				break;
	 			
	 			case 'move_entry_down':
	 				// Move an entry one step down 		
	 				$template .= $this->_Menu->ItemMoveDown(GetPostOrGet('menu_entry_orderid'), GetPostOrGet('menu_id'));
	 				// Set the user back to the menuoverview
	 				$template .= $this->_ShowMenu();
	 				break;
	 			
	 			case 'delete_menu_entry':
	 				// Returns a question wether the menuentry should really be deleted	
	 				$template .= $this->_DeleteMenuEntry();
	 				break;
	 			
	 			case 'delete_menu_entry_sure':
	 				// Delete the menuentry from the database	
	 				$template .= $this->_DeleteMenuEntrySure();
	 				// Set the user back to the homepage
	 				$template .= $this->_ShowMenu();
	 				break;
	 			
	 			case 'new_menu':
	 				// Returns a formular to add a new menu to the database		
	 				$template .= $this->_NewMenu();
	 				break;
	 			
	 			case 'add_menu':		
	 				// Return a formular to edit errors if there are any or set the user back to homepage
	 				$template .= $this->_AddMenu();
	 				break;
	 			
	 			case 'edit_menu':
	 				// Return a formular to edit a menu
	 				$template .= $this->_EditMenu();
	 				break;
	 			
	 			case 'check_menu':
	 				// Returns a formular 	
	 				$template .= $this->_CheckMenu();
					break;
					
	 			case 'delete_menu':	
	 				// Return a question wether the menu should really be deleted
	 				$template .= $this->_DeleteMenu();
	 				break;
	 			
	 			case 'delete_menu_sure':	
	 				// Remove the menu from the database
	 				$template .= $this->_DeleteMenuSure();
	 				// Set the user back to the homepage
	 				$template .= $this->_HomePage();
	 				break;
	 			
	 			case 'show_menu':	
	 				// Shows the entries of a menu
	 				$template .= $this->_ShowMenu();
	 				break;
	 			
	 			default:		
	 				// Give the user an overview over all existing menus
	 				$template .= $this->_HomePage();
	 				break;
	 		}
	 		return $template; 
	 	}
	 	
	 	/**
	 	 * Returs the HomePage of the menueditor
	 	 * @access private
	 	 * @return string The ready HTML Code of the menu HomePage
	 	 */
	 	function _HomePage() {
	 		
	 		// Get all menus from the database
 			$sql = "SELECT *
 				FROM " . DB_PREFIX . "menu";
 			$menusResult = $this->_SqlConnection->SqlQuery($sql);
 			
 			// Initialize menusarray
 			$menus = array();
 			
 			while ($menu = mysql_fetch_object($menusResult)) {
 				
 				$menuActions = array();
 				if ($menu->menu_name != 'DEFAULT')
 					$menuActions[] = array('ACTION' => 'edit_menu', 'IMG_PATH' => './img/edit.png', 'IMG_TITLE' => $this->_Translation->GetTranslation('edit_menu'));
 				$menuActions[] = array('ACTION' => 'show_menu', 'IMG_PATH' => './img/view.png', 'IMG_TITLE' => $this->_Translation->GetTranslation('edit_menuitems'));				
 				if ($menu->menu_name != 'DEFAULT')
 					$menuActions[] = array('ACTION' => 'delete_menu', 'IMG_PATH' => './img/del.png', 'IMG_TITLE' => $this->_Translation->GetTranslation('delete_menu'));
 				
 				$menus[] = array(	'MENU_ID' => $menu->menu_id,
 									'MENU_NAME' => $menu->menu_name,
 									'MENU_TITLE' => $menu->menu_title,
 									'MENU_ACTIONS' => $menuActions
								);
 			}
	 		$this->_ComaLate->SetReplacement('MENUS', $menus);
	 		
	 		// Set replacements for language
	 		$this->_ComaLate->SetReplacement('LANG_CREATE_NEW_MENU', $this->_Translation->GetTranslation('create_new_menu'));
	 		
	 		$template = '
	 			<a href="admin.php?page=menueditor&amp;action=new_menu" class="button">{LANG_CREATE_NEW_MENU}</a>
	 			
	 			<MENUS:loop>
	 				<ol>
	 					<li class="page_type_text">
	 						<span class="structure_row">
	 							<span class="page_actions">
	 							<MENU_ACTIONS:loop>
	 								<a href="admin.php?page=menueditor&amp;action={ACTION}&amp;menu_id={MENU_ID}&amp;menu_name={MENU_NAME}"><img src="{IMG_PATH}" class="icon" alt="{IMG_TITLE}" title="{IMG_TITLE}" height="16" width="16" /></a>
	 							</MENU_ACTIONS>
	 							</span>
	 							<strong>{MENU_TITLE}</strong> ({MENU_NAME})
	 						</span>
	 					</li>
	 				</ol>
	 			</MENUS>
				';
	 		
	 		return $template;
	 	}
	 	
	 	/**
	 	 * Returns the entries of the selected Menu
	 	 * @access private
	 	 * @return string HTML list of the entries defined in the selected Menu
	 	 */
	 	function _ShowMenu($MenuID = '') {
	 		
	 		// Get external parameters
	 		if (empty($MenuID))
	 			$MenuID = GetPostOrGet('menu_id');
	 		$MenuName = GetPostOrGet('menu_name');
	 		
	 		if (empty($MenuName)) {
	 			
	 			// Get the information from the database
	 			$sql = "SELECT *
	 					FROM " . DB_PREFIX . "menu
	 					WHERE menu_id='$MenuID'";
	 			$menuResult = $this->_SqlConnection->SqlQuery($sql);
	 			if ($menu = mysql_fetch_object($menuResult)) {
	 				
	 				$MenuName = $menu->menu_name;
	 			}
	 			else
	 				$MenuName = $this->_Translation->GetTranslation('could_not_find_menu');
	 		}
	 		
	 		// Get the entries of the menu from the database
	 		$sql = "SELECT *
	 			FROM " . DB_PREFIX . "menu_entries
	 			WHERE menu_entries_menuid=$MenuID
	 			ORDER BY menu_entries_orderid ASC";
	 		$menuResult = $this->_SqlConnection->SqlQuery($sql);
	 		
			// Initialize menuentries array
			$menuEntries = array();
			
	 		while ($menuEntry = mysql_fetch_object($menuResult)) {
	 			
	 			// Save all actions to a subarray
	 			$entryActions = array();
	 			$entryActions[] = array('ENTRY_ACTION' => 'edit_menu_entry', 'ENTRY_ID_TYPE' => 'id', 'ENTRY_ID' => $menuEntry->menu_entries_id, 'IMG_SRC' => './img/edit.png', 'IMG_TITLE' => $this->_Translation->GetTranslation('edit'));
	 			$entryActions[] = array('ENTRY_ACTION' => 'move_entry_up', 'ENTRY_ID_TYPE' => 'orderid', 'ENTRY_ID' => $menuEntry->menu_entries_orderid, 'IMG_SRC' => './img/up.png', 'IMG_TITLE' => $this->_Translation->GetTranslation('move_up'));
	 			$entryActions[] = array('ENTRY_ACTION' => 'move_entry_down', 'ENTRY_ID_TYPE' => 'orderid', 'ENTRY_ID' => $menuEntry->menu_entries_orderid, 'IMG_SRC' => './img/down.png', 'IMG_TITLE' => $this->_Translation->GetTranslation('move_down'));
				$entryActions[] = array('ENTRY_ACTION' => 'delete_menu_entry', 'ENTRY_ID_TYPE' => 'id', 'ENTRY_ID' => $menuEntry->menu_entries_id, 'IMG_SRC' => './img/del.png', 'IMG_TITLE' =>$this->_Translation->GetTranslation('delete'));
				
				$menuEntries[] = array(	'ENTRY_MENU_ID' => $menuEntry->menu_entries_menuid,
										'MENU_ENTRIES_TITLE' => $menuEntry->menu_entries_title,
										'MENU_ENTRIES_ACTIONS' => $entryActions);
	 		}
	 		$this->_ComaLate->SetReplacement('MENU_ENTRIES', $menuEntries);
	 		
	 		// Set replacements for language
	 		$this->_ComaLate->SetReplacement('MENU_NAME', $MenuName);
	 		$this->_ComaLate->SetReplacement('MENU_ID', $MenuID);
	 		$this->_ComaLate->SetReplacement('LANG_ADD_MENU_ENTRY', $this->_Translation->GetTranslation('add_menu_entry'));
	 		
	 		// Generate the template
	 		$template = '
	 				<fieldset>
	 					<legend>{MENU_NAME}</legend>
	 					<ol>
	 					<MENU_ENTRIES:loop>
	 						<li class="page_type_text">
	 							<span class="structure_row">
	 								<span class="page_actions">
	 									<MENU_ENTRIES_ACTIONS:loop>
	 										<a href="admin.php?page=menueditor&amp;action={ENTRY_ACTION}&amp;menu_entry_{ENTRY_ID_TYPE}={ENTRY_ID}&amp;menu_id={ENTRY_MENU_ID}"><img src="{IMG_SRC}" class="icon" alt="{IMG_TITLE}" title="{IMG_TITLE}" height="16" width="16" /></a>
	 									</MENU_ENTRIES_ACTIONS>
	 								</span>
	 								<strong>{MENU_ENTRIES_TITLE}</strong>
	 							</span>
	 						</li>
	 					</MENU_ENTRIES>
	 						<li style="list-style: none"><a href="admin.php?page=menueditor&amp;action=new_menu_entry&amp;menu_id={MENU_ID}&amp;menu_name={MENU_NAME}" class="button">{LANG_ADD_MENU_ENTRY}</a>
	 					</ol>
	 				</fieldset>
	 				';
	 		
	 		// Return the template
	 		return $template;
	 	}
	 	
	 	/**
	 	 * Returns a formular to choose the type of the menuentry
	 	 * @access private
	 	 * @return void
	 	 */
	 	function _ChooseEntryType($AddType) {
	 		
	 		// Get external parameters
	 		$MenuID = GetPostOrGet('menu_id');
	 		$MenuEntryID = GetPostOrGet('menu_entry_id');
	 		$Checked = '';
	 		
	 		// Get information about the menuentry from the database
	 		$sql = "SELECT menu_entries_type
	 				FROM " . DB_PREFIX . "menu_entries
	 				WHERE menu_entries_id='$MenuEntryID'";
	 		$menuEntryResult = $this->_SqlConnection->SqlQuery($sql);
	 		if($menuEntry = mysql_fetch_object($menuEntryResult))
	 			$Checked = $menuEntry->menu_entries_type;
	 		
	 		// Check function vars
	 		if ($Checked != 'intern_link' && $Checked != 'extern_link' && $Checked != 'download')
	 			$Checked = 'intern_link';
	 		
	 		// Initialize type array
	 		$types = array();
	 		
	 		$types[] = array(	'TYPE_ID' => 'intern_link',
	 							'TYPE_TRANSLATION' => $this->_Translation->GetTranslation('intern_link'),
	 							'TYPE_INFORMATION' => $this->_Translation->GetTranslation('choose_this_option_to_link_to_an_existing_page_in_the_system'),
	 							'TYPE_CHECKED' => (($Checked == 'intern_link') ? 'checked="checked" ' : ''),
	 							'TYPE_VALUE' => 'intern_link');
	 		$types[] = array(	'TYPE_ID' => 'extern_link',
	 							'TYPE_TRANSLATION' => $this->_Translation->GetTranslation('extern_link'),
	 							'TYPE_INFORMATION' => $this->_Translation->GetTranslation('choose_this_option_to_link_to_an_extern_url'),
	 							'TYPE_CHECKED' => (($Checked == 'extern_link') ? 'checked="checked" ' : ''),
	 							'TYPE_VALUE' => 'extern_link');
	 		$types[] = array(	'TYPE_ID' => 'download',
	 							'TYPE_TRANSLATION' => $this->_Translation->GetTranslation('download'),
	 							'TYPE_INFORMATION' => $this->_Translation->GetTranslation('choose_this_option_to_make_it_possible_to_download_a_file_from_the_system'),
	 							'TYPE_CHECKED' => (($Checked == 'download') ? 'checked="checked" ' : ''),
	 							'TYPE_VALUE' => 'download');
	 		$this->_ComaLate->SetReplacement('TYPES', $types);
	 		
	 		// Set replacements for language
	 		$this->_ComaLate->SetReplacement('LANG_CHOOSE_TYPE', $this->_Translation->GetTranslation('choose_type'));
	 		$this->_ComaLate->SetReplacement('LANG_NEXT', $this->_Translation->GetTranslation('next'));
	 		$this->_ComaLate->SetReplacement('ACTION', (($AddType == 'edit') ? 'edit_menu_entry_second' : 'new_menu_entry_second'));
	 		$this->_ComaLate->SetReplacement('MENU_ID', $MenuID);
	 		$this->_ComaLate->SetReplacement('MENU_ENTRY_ID', $MenuEntryID);
	 		
	 		// Return a template to the user
	 		$template = '
	 				<form action="admin.php" method="post">
	 					<fieldset>
	 						<legend>{LANG_CHOOSE_TYPE}</legend>
	 						<input type="hidden" name="page" value="menueditor" />
	 						<input type="hidden" name="action" value="{ACTION}" />
	 						<input type="hidden" name="menu_id" value="{MENU_ID}" />
	 						<input type="hidden" name="menu_entry_id" value="{MENU_ENTRY_ID}" />
	 					<TYPES:loop>
	 						<div class="row">
	 							<label for="{TYPE_ID}">
	 								<strong>{TYPE_TRANSLATION}</strong>
	 								<span class="info">{TYPE_INFORMATION}</span>
	 							</label>
	 							<input type="radio" name="menu_entry_type" id="{TYPE_ID}" {TYPE_CHECKED}value="{TYPE_VALUE}" />
	 						</div>
	 					</TYPES>
	 						<div class="row"><input type="submit" class="button" value="{LANG_NEXT}" /></div>
	 					</fieldset>
	 				</form>';
	 		return $template;
	 	}
	 	
	 	/**
	 	 * Returns a form to add a new Menuentry to the selected Menu
	 	 * @access private
	 	 * @return string Template of an addForm
	 	 */
	 	function _NewMenuEntry() {
	 		
	 		// Get external parameters
	 		$MenuID = GetPostOrGet('menu_id');
	 		$MenuEntryType = GetPostOrGet('menu_entry_type');
	 		
	 		// Initialize the formmaker class
	 		$formMaker = new FormMaker($this->_Translation->GetTranslation('todo'), &$this->_SqlConnection);
	 		$formMaker->AddForm('new_menu_entry', 'admin.php', $this->_Translation->GetTranslation('save'), $this->_Translation->GetTranslation('add_menu_entry'), 'post');
	 		
	 		// Add hidden inputs to give some variables to next page
	 		$formMaker->AddHiddenInput('new_menu_entry', 'page', 'menueditor');
	 		$formMaker->AddHiddenInput('new_menu_entry', 'action', 'add_menu_entry');
	 		$formMaker->AddHiddenInput('new_menu_entry', 'menu_entry_type', $MenuEntryType);
	 		
	 		// Add the inputs to the formmakerclass
	 		$formMaker->AddInput('new_menu_entry', 'menu_id', 'select', $this->_Translation->GetTranslation('belongs_to_menu'), $this->_Translation->GetTranslation('this_is_the_menu_the_new_entry_should_be_added_to'));
	 		
	 			// Get the existing menus from the database 
	 			$sql = "SELECT *
					FROM " . DB_PREFIX . "menu";
				$menuResult = $this->_SqlConnection->SqlQuery($sql);
				while ($menu = mysql_fetch_object($menuResult)) {
					
					// Add an entry for each existing menu
					$formMaker->AddSelectEntry('new_menu_entry', 'menu_id', (($menu->menu_id == $MenuID) ? true : false), $menu->menu_id, $menu->menu_id . ". " . $menu->menu_name);
				}
			
			$formMaker->AddInput('new_menu_entry', 'menu_entry_title', 'text', $this->_Translation->GetTranslation('menu_entry_title'), $this->_Translation->GetTranslation('this_is_the_title_of_the_menuentry_that_will_be_shown_in_the_menu'));
	 		
	 		if ($MenuEntryType == 'intern_link') {
	 			
	 			// Add select input for all pages in the system
	 			$formMaker->AddInput('new_menu_entry', 'menu_entry_link', 'select', $this->_Translation->GetTranslation('menu_entry_link'), $this->_Translation->GetTranslation('choose_here_the_page_to_which_the_link_should_refer'));
	 			
	 			// Add all existing pages to the select
	 			$pageStructure = new Pagestructure(&$this->_SqlConnection, &$this->_User, &$this->_ComaLib);
	 			$pageStructure->LoadParentIDs();
	 			$formMaker->AddSelectEntrysCode('new_menu_entry', 'menu_entry_link', $pageStructure->PageStructurePulldown(0, 0, '',  -1));
	 		}
	 		elseif ($MenuEntryType == 'extern_link') {
	 			
	 			// Add input for the extern url
	 			$formMaker->AddInput('new_menu_entry', 'menu_entry_link', 'text', $this->_Translation->GetTranslation('menu_entry_link'), $this->_Translation->GetTranslation('type_here_the_url_of_the_page_to_link_to'));
	 		}
	 		elseif ($MenuEntryType == 'download') {
	 			
	 			// Add select input for the downloads
	 			$formMaker->AddInput('new_menu_entry', 'menu_entry_link', 'select', $this->_Translation->GetTranslation('download'), $this->_Translation->GetTranslation('choose_here_the_download_you_want_to_link_to'));
	 			
	 			// Add all existing files to the select input
	 			$sql = "SELECT *
						FROM " . DB_PREFIX . "files
						ORDER BY file_name";
				$files_result = $this->_SqlConnection->SqlQuery($sql);
				
				while($file = mysql_fetch_object($files_result)) {
					
					if(file_exists($file->file_path))
						$formMaker->AddSelectEntry('new_menu_entry', 'menu_entry_link', false, $file->file_id, utf8_encode($file->file_name) . " (" . kbormb($file->file_size) . ")");
				}	
	 		}
	 		
	 		$formMaker->AddInput('new_menu_entry', 'menu_entry_css_id', 'text', $this->_Translation->GetTranslation('menu_entry_css'), $this->_Translation->GetTranslation('type_in_here_the_css_id_for_the_menuentry_if_you_need_one_for_it'));
	 		
	 		// Generate the template
			$template = "\r\n\t\t\t\t" . $formMaker->GenerateMultiFormTemplate(&$this->_ComaLate, false);
			return $template;
	 	}
	 	
	 	/**
	 	 * Returns a form template to edit any errors
	 	 * @access private
	 	 * @return string A Template for a formmaker
	 	 */
	 	function _AddMenuEntry() {
	 		
	 		// Get external parameters
	 		$MenuID = GetPostOrGet('menu_id');
	 		$MenuEntryType = GetPostOrGet('menu_entry_type');
	 		$MenuEntryTitle = GetPostOrGet('menu_entry_title');
	 		$MenuEntryLink = GetPostOrGet('menu_entry_link');
	 		$MenuEntryCssID = GetPostOrGet('menu_entry_css_id');
	 		
	 		// Initialize the formmaker class
	 		$formMaker = new FormMaker($this->_Translation->GetTranslation('todo'), &$this->_SqlConnection);
	 		$formMaker->AddForm('add_menu_entry', 'admin.php', $this->_Translation->GetTranslation('save'), $this->_Translation->GetTranslation('add_menu_entry'), 'post');
	 		
	 		// Add hidden inputs to give some variables to next page
	 		$formMaker->AddHiddenInput('add_menu_entry', 'page', 'menueditor');
	 		$formMaker->AddHiddenInput('add_menu_entry', 'action', 'add_menu_entry');
	 		$formMaker->AddHiddenInput('add_menu_entry', 'menu_entry_type', $MenuEntryType);
	 		
	 		// Add the inputs to the formmakerclass
	 		$formMaker->AddInput('add_menu_entry', 'menu_id', 'select', $this->_Translation->GetTranslation('belongs_to_menu'), $this->_Translation->GetTranslation('this_is_the_menu_the_new_entry_should_be_added_to'));
	 		
	 			// Get the existing menus from the database 
	 			$sql = "SELECT *
					FROM " . DB_PREFIX . "menu";
				$menuResult = $this->_SqlConnection->SqlQuery($sql);
				while ($menu = mysql_fetch_object($menuResult)) {
					
					// Add an entry for each existing menu
					$formMaker->AddSelectEntry('add_menu_entry', 'menu_id', (($menu->menu_id == $MenuID) ? true : false), $menu->menu_id, $menu->menu_id . ". " . $menu->menu_name);
				}
			
			$formMaker->AddInput('add_menu_entry', 'menu_entry_title', 'text', $this->_Translation->GetTranslation('menu_entry_title'), $this->_Translation->GetTranslation('this_is_the_title_of_the_menuentry_that_will_be_shown_in_the_menu'), $MenuEntryTitle);
			$formMaker->AddCheck('add_menu_entry', 'menu_entry_title', 'empty', $this->_Translation->GetTranslation('you_have_to_define_a_title_for_the_menu_entry'));
			
			if ($MenuEntryType == 'intern_link') {
				
				// Add select input for all pages in the system
				$formMaker->AddInput('add_menu_entry', 'menu_entry_link', 'select', $this->_Translation->GetTranslation('menu_entry_link'), $this->_Translation->GetTranslation('choose_here_the_page_to_which_the_link_should_refer'));
	 			
	 			// Add all existing pages to the select
	 			$pageStructure = new Pagestructure(&$this->_SqlConnection, &$this->_User, &$this->_ComaLib);
	 			$pageStructure->LoadParentIDs();
	 			$formMaker->AddSelectEntrysCode('add_menu_entry', 'menu_entry_link', $pageStructure->PageStructurePulldown(0, 0, '', -1, $MenuEntryLink));
	 		}
	 		elseif ($MenuEntryType == 'extern_link') {
	 			
	 			// Add input for the extern url
	 			$formMaker->AddInput('add_menu_entry', 'menu_entry_link', 'text', $this->_Translation->GetTranslation('menu_entry_link'), $this->_Translation->GetTranslation('type_here_the_url_of_the_page_to_link_to'), $MenuEntryLink);
	 			$formMaker->AddCheck('add_menu_entry', 'menu_entry_link', 'empty', $this->_Translation->GetTranslation('you_have_to_define_an_extern_url_for_this_link'));
	 		}
	 		elseif ($MenuEntryType == 'download') {
	 			
	 			// Add select input for the downloads
	 			$formMaker->AddInput('add_menu_entry', 'menu_entry_link', 'select', $this->_Translation->GetTranslation('download'), $this->_Translation->GetTranslation('choose_here_the_download_you_want_to_link_to'));
	 			
	 			// Add all existing files to the select input
	 			$sql = "SELECT *
						FROM " . DB_PREFIX . "files
						ORDER BY file_name";
				$files_result = $this->_SqlConnection->SqlQuery($sql);
				
				while($file = mysql_fetch_object($files_result)) {
					
					if(file_exists($file->file_path))
						$formMaker->AddSelectEntry('add_menu_entry', 'menu_entry_link', (($MenuEntryLink == $file->file_id) ? true : false), $file->file_id, utf8_encode($file->file_name) . " (" . kbormb($file->file_size) . ")");
				}	
	 		}
	 		
	 		$formMaker->AddInput('add_menu_entry', 'menu_entry_css_id', 'text', $this->_Translation->GetTranslation('menu_entry_css'), $this->_Translation->GetTranslation('type_in_here_the_css_id_for_the_menuentry_if_you_need_one_for_it'));
	 		
	 		if ($formMaker->CheckInputs('add_menu_entry', true)) {
				
				// Get the next orderid
				$sql = "SELECT *
	 					FROM " . DB_PREFIX . "menu_entries
 						WHERE menu_entries_menuid='$MenuID'
 						ORDER BY menu_entries_orderid DESC
	 					LIMIT 1";
				$menuResult = $this->_SqlConnection->SqlQuery($sql);
 				if($menuEntry = mysql_fetch_object($menuResult)) {
					$MenuEntryOrderID = $menuEntry->menu_entries_orderid + 1;
				}
				else {
 					$MenuEntryOrderID = 0;
				}
				
				if ($MenuEntryType == 'intern_link') {
					// Get the name of the page
					$sql = "SELECT *
		 				FROM " . DB_PREFIX . "pages
	 					WHERE page_id='$MenuEntryLink'";
	 				$pageResult = $this->_SqlConnection->SqlQuery($sql);
	 				if ($page = mysql_fetch_object($pageResult)) {
	 					$PageID = $MenuEntryLink;
	 					$MenuEntryLink = $page->page_name;
	 				}
				}
 				else
 					$PageID = '';
				
				// Add new user to the database
				$sql = "INSERT INTO " . DB_PREFIX . "menu_entries
						(menu_entries_link, menu_entries_title, menu_entries_type, menu_entries_css_id, menu_entries_orderid, menu_entries_menuid, menu_entries_page_id)
						VALUES ('" . (($MenuEntryType == 'intern_link') ? 'l:' : (($MenuEntryType == 'download') ? 'd:' : (($MenuEntryType == 'extern_link') ? 'e:' : ''))) . "$MenuEntryLink', '$MenuEntryTitle', '$MenuEntryType', '$MenuEntryCssID', '$MenuEntryOrderID', '$MenuID', '$PageID')";
				$this->_SqlConnection->SqlQuery($sql);
				
				// Set user to the HomePage of the usermanager
				$template = "\r\n\t\t\t\t" . $this->_ShowMenu($MenuID);
				return $template;
			}
			else {
				
				// Generate to edit the errors
				$template = "\r\n\t\t\t\t" . $formMaker->GenerateMultiFormTemplate(&$this->_ComaLate, true);
				return $template;
			}
	 	}
	 	
	 	/**
	 	 * Returns a form to edit a menuentry
	 	 * @access private
	 	 * @return string HTML code of the form
	 	 */
	 	function _EditMenuEntry() {
	 		
	 		// Get external parameters
	 		$MenuID = GetPostOrGet('menu_id');
	 		$MenuEntryID = GetPostOrGet('menu_entry_id');
	 		$MenuEntryType = GetPostOrGet('menu_entry_type');
			
			// Get information about the menuentry from the database
			$sql = "SELECT *
					FROM " . DB_PREFIX . "menu_entries
					WHERE menu_entries_id='$MenuEntryID'";
			$menuEntryResult = $this->_SqlConnection->SqlQuery($sql);
			$menuEntry = mysql_fetch_object($menuEntryResult);
			
	 		// Initialize the formmaker class
	 		$formMaker = new FormMaker($this->_Translation->GetTranslation('todo'), &$this->_SqlConnection);
	 		$formMaker->AddForm('edit_menu_entry', 'admin.php', $this->_Translation->GetTranslation('save'), $this->_Translation->GetTranslation('add_menu_entry'), 'post');
	 		
	 		// Add hidden inputs to give some variables to next page
	 		$formMaker->AddHiddenInput('edit_menu_entry', 'page', 'menueditor');
	 		$formMaker->AddHiddenInput('edit_menu_entry', 'action', 'check_menu_entry');
	 		$formMaker->AddHiddenInput('edit_menu_entry', 'menu_entry_type', $MenuEntryType);
	 		$formMaker->AddHiddenInput('edit_menu_entry', 'menu_entry_id', $MenuEntryID);
	 		
	 		// Add the inputs to the formmakerclass
	 		$formMaker->AddInput('edit_menu_entry', 'menu_id', 'select', $this->_Translation->GetTranslation('belongs_to_menu'), $this->_Translation->GetTranslation('this_is_the_menu_the_new_entry_should_be_added_to'));
	 		
	 			// Get the existing menus from the database 
	 			$sql = "SELECT *
					FROM " . DB_PREFIX . "menu";
				$menuResult = $this->_SqlConnection->SqlQuery($sql);
				while ($menu = mysql_fetch_object($menuResult)) {
					
					// Add an entry for each existing menu
					$formMaker->AddSelectEntry('edit_menu_entry', 'menu_id', (($menu->menu_id == $MenuID) ? true : false), $menu->menu_id, $menu->menu_id . ". " . $menu->menu_name);
				}
			
			$formMaker->AddInput('edit_menu_entry', 'menu_entry_title', 'text', $this->_Translation->GetTranslation('menu_entry_title'), $this->_Translation->GetTranslation('this_is_the_title_of_the_menuentry_that_will_be_shown_in_the_menu'), $menuEntry->menu_entries_title);
			
			// Check wether the linktype has changed
			if ($MenuEntryType != $menuEntry->menu_entries_type) {
				$menuEntry->menu_entries_link = '';
				$menuEntry->menu_entries_page_id = '';
			}
			
			if ($MenuEntryType == 'intern_link') {
				
				// Add select input for all pages in the system
				$formMaker->AddInput('edit_menu_entry', 'menu_entry_link', 'select', $this->_Translation->GetTranslation('menu_entry_link'), $this->_Translation->GetTranslation('choose_here_the_page_to_which_the_link_should_refer'));
	 			
	 			// Add all existing pages to the select
	 			$pageStructure = new Pagestructure(&$this->_SqlConnection, &$this->_User, &$this->_ComaLib);
	 			$pageStructure->LoadParentIDs();
	 			$formMaker->AddSelectEntrysCode('edit_menu_entry', 'menu_entry_link', $pageStructure->PageStructurePulldown(0, 0, '', -1, $menuEntry->menu_entries_page_id));
	 		}
	 		elseif ($MenuEntryType == 'extern_link') {
	 			
	 			// Add input for the extern url
	 			$formMaker->AddInput('edit_menu_entry', 'menu_entry_link', 'text', $this->_Translation->GetTranslation('menu_entry_link'), $this->_Translation->GetTranslation('type_here_the_url_of_the_page_to_link_to'), $menuEntry->menu_entries_link);
	 		}
	 		elseif ($MenuEntryType == 'download') {
	 			
	 			// Add select input for the downloads
	 			$formMaker->AddInput('edit_menu_entry', 'menu_entry_link', 'select', $this->_Translation->GetTranslation('download'), $this->_Translation->GetTranslation('choose_here_the_download_you_want_to_link_to'));
	 			
	 			// Add all existing files to the select input
	 			$sql = "SELECT *
						FROM " . DB_PREFIX . "files
						ORDER BY file_name";
				$files_result = $this->_SqlConnection->SqlQuery($sql);
				
				while($file = mysql_fetch_object($files_result)) {
					
					if(file_exists($file->file_path))
						$formMaker->AddSelectEntry('edit_menu_entry', 'menu_entry_link', ((substr($menuEntry->menu_entries_link, 2) == $file->file_id) ? true : false), $file->file_id, utf8_encode($file->file_name) . " (" . kbormb($file->file_size) . ")");
				}	
	 		}
	 		
	 		$formMaker->AddInput('edit_menu_entry', 'menu_entry_css_id', 'text', $this->_Translation->GetTranslation('menu_entry_css'), $this->_Translation->GetTranslation('type_in_here_the_css_id_for_the_menuentry_if_you_need_one_for_it'), $menuEntry->menu_entries_css_id);
	 		
			// Generate to edit the errors
			$template = "\r\n\t\t\t\t" . $formMaker->GenerateMultiFormTemplate(&$this->_ComaLate, true);
			return $template;
	 	}
	 	
	 	/**
	 	 * Returns a formular to edit any errors if there are any or set the user back to showmenu page
	 	 * @access private
	 	 * @return string A template for a formular to edit the errors
	 	 */
	 	function _CheckMenuEntry() {
	 		
	 		// Get external parameters
	 		$MenuID = GetPostOrGet('menu_id');
	 		$MenuEntryID = GetPostOrGet('menu_entry_id');
	 		$MenuEntryType = GetPostOrGet('menu_entry_type');
	 		$MenuEntryTitle = GetPostOrGet('menu_entry_title');
	 		$MenuEntryLink = GetPostOrGet('menu_entry_link');
	 		$MenuEntryCssID = GetPostOrGet('menu_entry_css_id');
	 		
	 		// Initialize the formmaker class
	 		$formMaker = new FormMaker($this->_Translation->GetTranslation('todo'), &$this->_SqlConnection);
	 		$formMaker->AddForm('check_menu_entry', 'admin.php', $this->_Translation->GetTranslation('save'), $this->_Translation->GetTranslation('add_menu_entry'), 'post');
	 		
	 		// Add hidden inputs to give some variables to next page
	 		$formMaker->AddHiddenInput('check_menu_entry', 'page', 'menueditor');
	 		$formMaker->AddHiddenInput('check_menu_entry', 'action', 'add_menu_entry');
	 		$formMaker->AddHiddenInput('check_menu_entry', 'menu_entry_type', $MenuEntryType);
	 		
	 		// Add the inputs to the formmakerclass
	 		$formMaker->AddInput('check_menu_entry', 'menu_id', 'select', $this->_Translation->GetTranslation('belongs_to_menu'), $this->_Translation->GetTranslation('this_is_the_menu_the_new_entry_should_be_added_to'));
	 		
	 			// Get the existing menus from the database 
	 			$sql = "SELECT *
					FROM " . DB_PREFIX . "menu";
				$menuResult = $this->_SqlConnection->SqlQuery($sql);
				while ($menu = mysql_fetch_object($menuResult)) {
					
					// Add an entry for each existing menu
					$formMaker->AddSelectEntry('check_menu_entry', 'menu_id', (($menu->menu_id == $MenuID) ? true : false), $menu->menu_id, $menu->menu_id . ". " . $menu->menu_name);
				}
			
			$formMaker->AddInput('check_menu_entry', 'menu_entry_title', 'text', $this->_Translation->GetTranslation('menu_entry_title'), $this->_Translation->GetTranslation('this_is_the_title_of_the_menuentry_that_will_be_shown_in_the_menu'), $MenuEntryTitle);
			$formMaker->AddCheck('check_menu_entry', 'menu_entry_title', 'empty', $this->_Translation->GetTranslation('you_have_to_define_a_title_for_the_menu_entry'));
			
			if ($MenuEntryType == 'intern_link') {
				
				// Add select input for all pages in the system
				$formMaker->AddInput('check_menu_entry', 'menu_entry_link', 'select', $this->_Translation->GetTranslation('menu_entry_link'), $this->_Translation->GetTranslation('choose_here_the_page_to_which_the_link_should_refer'));
	 			
	 			// Add all existing pages to the select
	 			$pageStructure = new Pagestructure(&$this->_SqlConnection, &$this->_User, &$this->_ComaLib);
	 			$pageStructure->LoadParentIDs();
	 			$formMaker->AddSelectEntrysCode('check_menu_entry', 'menu_entry_link', $pageStructure->PageStructurePulldown(0, 0, '', -1, $MenuEntryLink));
	 		}
	 		elseif ($MenuEntryType == 'extern_link') {
	 			
	 			// Add input for the extern url
	 			$formMaker->AddInput('check_menu_entry', 'menu_entry_link', 'text', $this->_Translation->GetTranslation('menu_entry_link'), $this->_Translation->GetTranslation('type_here_the_url_of_the_page_to_link_to'), $MenuEntryLink);
	 			$formMaker->AddCheck('check_menu_entry', 'menu_entry_link', 'empty', $this->_Translation->GetTranslation('you_have_to_define_an_extern_url_for_this_link'));
	 		}
	 		elseif ($MenuEntryType == 'download') {
	 			
	 			// Add select input for the downloads
	 			$formMaker->AddInput('check_menu_entry', 'menu_entry_link', 'select', $this->_Translation->GetTranslation('download'), $this->_Translation->GetTranslation('choose_here_the_download_you_want_to_link_to'));
	 			
	 			// Add all existing files to the select input
	 			$sql = "SELECT *
						FROM " . DB_PREFIX . "files
						ORDER BY file_name";
				$files_result = $this->_SqlConnection->SqlQuery($sql);
				
				while($file = mysql_fetch_object($files_result)) {
					
					if(file_exists($file->file_path))
						$formMaker->AddSelectEntry('check_menu_entry', 'menu_entry_link', (($MenuEntryLink == $file->file_id) ? true : false), $file->file_id, utf8_encode($file->file_name) . " (" . kbormb($file->file_size) . ")");
				}	
	 		}
	 		
	 		$formMaker->AddInput('check_menu_entry', 'menu_entry_css_id', 'text', $this->_Translation->GetTranslation('menu_entry_css'), $this->_Translation->GetTranslation('type_in_here_the_css_id_for_the_menuentry_if_you_need_one_for_it'));
	 		
	 		if ($formMaker->CheckInputs('check_menu_entry', true)) {
				
				if ($MenuEntryType == 'intern_link') {
					// Get the name of the page
					$sql = "SELECT *
		 				FROM " . DB_PREFIX . "pages
	 					WHERE page_id='$MenuEntryLink'";
	 				$pageResult = $this->_SqlConnection->SqlQuery($sql);
	 				if ($page = mysql_fetch_object($pageResult)) {
	 					$PageID = $MenuEntryLink;
	 					$MenuEntryLink = $page->page_name;
	 				}
				}
 				else
 					$PageID = '';
				
				// Add new user to the database
				$sql = "UPDATE " . DB_PREFIX . "menu_entries
						SET menu_entries_link='" . (($MenuEntryType == 'intern_link') ? 'l:' : (($MenuEntryType == 'download') ? 'd:' : (($MenuEntryType == 'extern_link') ? 'e:' : ''))) . "$MenuEntryLink', 
							menu_entries_title='$MenuEntryTitle', 
							menu_entries_type='$MenuEntryType', 
							menu_entries_css_id='$MenuEntryCssID', 
							menu_entries_menuid='$MenuID', 
							menu_entries_page_id='$PageID'
						WHERE menu_entries_id='$MenuEntryID'";
				$this->_SqlConnection->SqlQuery($sql);
				
				// Set user to the HomePage of the usermanager
				$template = "\r\n\t\t\t\t" . $this->_ShowMenu($MenuID);
				return $template;
			}
			else {
				
				// Generate to edit the errors
				$template = "\r\n\t\t\t\t" . $formMaker->GenerateMultiFormTemplate(&$this->_ComaLate, true);
				return $template;
			}
	 	}
	 	
	 	/**
	 	 * Make sure, that the menuentry should really be deleted
	 	 * @access private
	 	 * @return string HTML code
	 	 */
	 	function _DeleteMenuEntry() {
	 		
	 		// Get external parameters
	 		$MenuEntryID = GetPostOrGet('menu_entry_id');
	 		$MenuID = GetPostOrGet('menu_id');
	 		$MenuName = GetPostOrGet('menu_name');
	 		
	 		$sql = "SELECT *
	 			FROM " . DB_PREFIX . "menu_entries
	 			WHERE menu_entries_id=$MenuEntryID";
	 		$menuResult = $this->_SqlConnection->SqlQuery($sql);
	 		$menuEntry = mysql_fetch_object($menuResult);
	 		
	 		$out = "\t\t\t" . sprintf($this->_Translation->GetTranslation('Do you really want to delete the menuentry %menuEntryTitle%?'), $menuEntry->menu_entries_title) . "<br />
			<a href=\"admin.php?page=menueditor&amp;action=delete_menu_entry_sure&amp;menu_entry_id=$MenuEntryID&amp;menu_id=$MenuID&amp;menu_name=$MenuName\" class=\"button\">" . $this->_Translation->GetTranslation('yes') . "</a>
		 	<a href=\"admin.php?page=menueditor&amp;action=show_menu&amp;menu_id=$MenuID&amp;menu_name=$MenuName\" class=\"button\">" . $this->_Translation->GetTranslation('no') . "</a>";
	 		
	 		return $out;
	 	}
	 	 
	 	/**
 		 * Deletes a MenuEntry by it's ID
 		 * @access private
 		 * @param integer Menu_entry_id The id of the Entry that should be deleted
 		 * @return void
 		 */
 		function _DeleteMenuEntrySure () {
 			
 			// Get external Parameters
 			$MenuEntryID = GetPostOrGet('menu_entry_id');
 			
 			// Check wether the parameter is really an id
 			if(is_numeric($MenuEntryID)) {
 				
 				// Remove the menuentry from the database
 				$sql = "DELETE
	 				FROM " . DB_PREFIX . "menu_entries
 					WHERE menu_entries_id=$MenuEntryID";
 				$this->_SqlConnection->SqlQuery($sql);
 			}
 		}
 		
	 	/**
	 	 * Returns a formular to add a new menu to the database
	 	 * @access private
	 	 * @return void
	 	 */
	 	function _NewMenu() {
	 		
	 		// Initialize the formmaker class
	 		$formMaker = new FormMaker($this->_Translation->GetTranslation('todo'), &$this->_SqlConnection);
	 		$formMaker->AddForm('new_menu', 'admin.php', $this->_Translation->GetTranslation('save'), $this->_Translation->GetTranslation('new_menu'), 'post');
	 		
	 		// Add hiddeninputs to send some parameters to the next page
	 		$formMaker->AddHiddenInput('new_menu', 'page', 'menueditor');
	 		$formMaker->AddHiddenInput('new_menu', 'action', 'add_menu');
	 		
	 		// Add the inputs to the formmaker
	 		$formMaker->AddInput('new_menu', 'menu_title', 'text', $this->_Translation->GetTranslation('menu_title'), $this->_Translation->GetTranslation('type_here_the_title_of_the_menu'));
	 		$formMaker->AddInput('new_menu', 'menu_name', 'text', $this->_Translation->GetTranslation('menu_name'), $this->_Translation->GetTranslation('type_here_the_name_of_the_menu'));
	 		
	 		$template = "\r\n\t\t\t\t" . $formMaker->GenerateMultiFormTemplate(&$this->_ComaLate, false);
	 		return $template;
	 	}
	 	
	 	/**
	 	 * Form to add a new Menu
	 	 * @access private
	 	 * @return string HTML code of the form
	 	 */
	 	function _AddMenu() {
	 		
	 		// Get external parameters
	 		$MenuTitle = GetPostOrGet('menu_title');
	 		$MenuName = GetPostOrGet('menu_name');
	 		
	 		// Initialize the formmaker class
	 		$formMaker = new FormMaker($this->_Translation->GetTranslation('todo'), &$this->_SqlConnection);
	 		$formMaker->AddForm('add_menu', 'admin.php', $this->_Translation->GetTranslation('save'), $this->_Translation->GetTranslation('new_menu'), 'post');
	 		
	 		// Add hiddeninputs to place the backcome of the menu
	 		$formMaker->AddHiddenInput('add_menu', 'page', 'menueditor');
	 		$formMaker->AddHiddenInput('add_menu', 'action', 'add_menu');
	 		
	 		// Add the inputs to the formmaker
	 		$formMaker->AddInput('add_menu', 'menu_title', 'text', $this->_Translation->GetTranslation('menu_title'), $this->_Translation->GetTranslation('type_here_the_title_of_the_menu'), $MenuTitle);
	 		$formMaker->AddCheck('add_menu', 'menu_title', 'empty', $this->_Translation->GetTranslation('you_have_to_define_a_menu_title'));
	 		
	 		$formMaker->AddInput('add_menu', 'menu_name', 'text', $this->_Translation->GetTranslation('menu_name'), $this->_Translation->GetTranslation('type_here_the_name_of_the_menu'), $MenuName);
	 		$formMaker->AddCheck('add_menu', 'menu_name', 'empty', $this->_Translation->GetTranslation('you_have_to_define_a_menu_name'));
	 		$formMaker->AddCheck('add_menu', 'menu_name', 'already_assigned', $this->_Translation->GetTranslation('this_menu_name_is_already_assigned'), '', 'menu', 'menu_name');
	 		
	 		if ($formMaker->CheckInputs('add_menu', true)) {
				
				// Add menu to the database
				$sql = "INSERT INTO " . DB_PREFIX . "menu
 					(menu_title, menu_name)
 					VALUES ('$MenuTitle', '$MenuName')";
 				$this->_SqlConnection->SqlQuery($sql);
				
				// Set user to the HomePage of the usermanager
				$template = "\r\n\t\t\t\t" . $this->_HomePage();
				return $template;
			}
			else {
				
				// Generate to edit the errors
				$template = "\r\n\t\t\t\t" . $formMaker->GenerateMultiFormTemplate(&$this->_ComaLate, true);
				return $template;
			}
	 	}
	 	
	 	/**
	 	 * Form to edit a new Menu
	 	 * @access private
	 	 * @return string HTML code of the form
	 	 * @todo Information about DEFAULT menu
	 	 */
		function _EditMenu() {
	 		
	 		// Get external parameters
	 		$MenuID = GetPostOrGet('menu_id');
	 		
	 		// Get the information about the menu from the database
	 		$sql = "SELECT *
	 			FROM " . DB_PREFIX . "menu
	 			WHERE menu_id=$MenuID";
	 		$menuResult = $this->_SqlConnection->SqlQuery($sql);
	 		$menu = mysql_fetch_object($menuResult);
	 		
	 		// Initialize the formmaker class
	 		$formMaker = new FormMaker($this->_Translation->GetTranslation('todo'), &$this->_SqlConnection);
	 		$formMaker->AddForm('edit_menu', 'admin.php', $this->_Translation->GetTranslation('save'), $this->_Translation->GetTranslation('new_menu'), 'post');
	 		
	 		// Add hiddeninputs to place the backcome of the menu
	 		$formMaker->AddHiddenInput('edit_menu', 'page', 'menueditor');
	 		$formMaker->AddHiddenInput('edit_menu', 'action', 'check_menu');
	 		$formMaker->AddHiddenInput('edit_menu', 'menu_id', $MenuID);
	 		
	 		// Add the inputs to the formmaker
	 		$formMaker->AddInput('edit_menu', 'menu_title', 'text', $this->_Translation->GetTranslation('menu_title'), $this->_Translation->GetTranslation('type_here_the_title_of_the_menu'), $menu->menu_title);
	 		if ($menu->menu_name != 'DEFAULT')
	 			$formMaker->AddInput('edit_menu', 'menu_name', 'text', $this->_Translation->GetTranslation('menu_name'), $this->_Translation->GetTranslation('type_here_the_name_of_the_menu'), $menu->menu_name);
	 		
	 		$template = "\r\n\t\t\t\t" . $formMaker->GenerateMultiFormTemplate(&$this->_ComaLate, false);
	 		return $template;
	 	}
	 	
	 	/**
 		 * Saves a new 'version' of a Menu by it's ID 
 		 * @access private
 		 * @return void
 		 */
 		function _CheckMenu() {
 			
 			// Get external parameters
 			$MenuID = GetPostOrGet('menu_id');
	 		$MenuTitle = GetPostOrGet('menu_title');
	 		$MenuName = GetPostOrGet('menu_name');
	 		
	 		// Check external parameter
	 		if (is_numeric($MenuID)) {
		 		
		 		// Get the data of the menu from the database
		 		$sql = "SELECT *
		 				FROM " . DB_PREFIX . "menu
		 				WHERE menu_id='$MenuID'";
		 		$menuResult = $this->_SqlConnection->SqlQuery($sql);
		 		$menu = mysql_fetch_object($menuResult);
		 		
		 		// Initialize the formmaker class
		 		$formMaker = new FormMaker($this->_Translation->GetTranslation('todo'), &$this->_SqlConnection);
		 		$formMaker->AddForm('check_menu', 'admin.php', $this->_Translation->GetTranslation('save'), $this->_Translation->GetTranslation('new_menu'), 'post');
		 		
		 		// Add hiddeninputs to place the backcome of the menu
		 		$formMaker->AddHiddenInput('check_menu', 'page', 'menueditor');
		 		$formMaker->AddHiddenInput('check_menu', 'action', 'check_menu');
		 		$formMaker->AddHiddenInput('check_menu', 'menu_id', $MenuID);
		 		
		 		// Add the inputs to the formmaker
		 		$formMaker->AddInput('check_menu', 'menu_title', 'text', $this->_Translation->GetTranslation('menu_title'), $this->_Translation->GetTranslation('type_here_the_title_of_the_menu'), $MenuTitle);
		 		$formMaker->AddCheck('check_menu', 'menu_title', 'empty', $this->_Translation->GetTranslation('you_have_to_define_a_menu_title'));
		 		
		 		$formMaker->AddInput('check_menu', 'menu_name', 'text', $this->_Translation->GetTranslation('menu_name'), $this->_Translation->GetTranslation('type_here_the_name_of_the_menu'), $MenuName);
		 		$formMaker->AddCheck('check_menu', 'menu_name', 'empty', $this->_Translation->GetTranslation('you_have_to_define_a_menu_name'));
		 		if ($menu->menu_name != $MenuName)
		 			$formMaker->AddCheck('check_menu', 'menu_name', 'already_assigned', $this->_Translation->GetTranslation('this_menu_name_is_already_assigned'), '', 'menu', 'menu_name');
		 		
		 		if ($formMaker->CheckInputs('check_menu', true)) {
					
					// Add menu to the database
					$sql = "UPDATE " . DB_PREFIX . "menu
	 					SET menu_name='$MenuName', menu_title='$MenuTitle'
	 					WHERE menu_id='$MenuID'";
	 				$this->_SqlConnection->SqlQuery($sql);
					
					// Set user to the HomePage of the usermanager
					$template = "\r\n\t\t\t\t" . $this->_HomePage();
					return $template;
				}
				else {
					
					// Generate to edit the errors
					$template = "\r\n\t\t\t\t" . $formMaker->GenerateMultiFormTemplate(&$this->_ComaLate, true);
					return $template;
				}
	 		}
 		}
	 	
	 	/**
	 	 * Question if a menu should really be deleted
	 	 * @access private
	 	 * @return string HTML code
	 	 */
	 	function _DeleteMenu() {
	 		
	 		// Get external parameters
			$MenuID = GetPostOrGet('menu_id');
	 		
	 		if (is_numeric($MenuID)) {
	 			
	 			// Get the data of the menu from the database
	 			$sql = "SELECT *
	 				FROM " . DB_PREFIX . "menu
	 				WHERE menu_id='$MenuID'";
	 			$menuResult = $this->_SqlConnection->SqlQuery($sql);
	 			
	 			if ($menu = mysql_fetch_object($menuResult)) {
	 				
	 				// Check wether the user wants to delete the default menu
	 				if ($menu->menu_name != 'DEFAULT') {
	 					$out = "\r\n\t\t\t" . sprintf($this->_Translation->GetTranslation('shall_the_menu_%menutitle%_really_be_deleted?'), $menu->menu_name) . "<br />
			<a href=\"admin.php?page=menueditor&amp;action=delete_menu_sure&amp;menu_id={$MenuID}&amp;menu_name={$menu->menu_name}\" class=\"button\">" . $this->_Translation->GetTranslation('yes') . "</a>
			<a href=\"admin.php?page=menueditor\" class=\"button\">" . $this->_Translation->GetTranslation('no') . "</a>";
	 				}
	 				else
	 					$out = $this->_Translation->GetTranslation('the_DEFAULT_menu_can_for_technical_reasons_not_be_deleted') .
								"<br /><a href=\"admin.php?page=menueditor\" class=\"button\">" . $this->_Translation->GetTranslation('back') . "</a>";
	 			}
	 			else {
	 				$out = $this->_Translation->GetTranslation('the_menu_could_not_be_found') . 
							"<br /> <a href=\"admin.php?page=menueditor\" class=\"button\">" . $this->_Translation->GetTranslation('back') . "</a>";
	 			}
	 		} 
			
			return $out;
	 	}
		
		/**
 		 * Deletes a Menu by it's ID
 		 * @access public
 		 * @return void
 		 */
 		function _DeleteMenuSure() {
 			
 			// Get external parameters
 			$MenuID = GetPostOrGet('menu_id');
 			$MenuName = GetPostOrGet('menu_name');
 			
 			// Check external parameters
 			if (is_numeric($MenuID) && $MenuName != 'DEFAULT' && $MenuName != '') {
 				
 				// Remove the menu from the database
 				$sql = "DELETE
 					FROM " . DB_PREFIX . "menu
 					WHERE menu_id='$MenuID'";
 				$this->_SqlConnection->SqlQuery($sql);
 				
 				// Remove all entrys of the menu from the database 
 				$sql = "DELETE
					FROM " . DB_PREFIX . "menu_entries
					WHERE menu_entries_menuid='$MenuID'";
				$this->_SqlConnection->SqlQuery($sql);
 			}
 		}
	}
?>
