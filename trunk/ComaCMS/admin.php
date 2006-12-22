<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005-2006 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : admin.php	
 # created              : 2005-07-11
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
	define('COMACMS_RUN', true);
	
	include('common.php');
	$outputpage = new OutputPage($sqlConnection);

	if(!$user->IsLoggedIn)  {
		header('Location: special.php?page=login' . (($user->LoginError != -1) ? ('&error=' . $user->LoginError) : ''));
		die();
	}
	if(!$user->IsAdmin && $user->IsLoggedIn) {
		header('Location: index.php');
		die();
	}
	include_once('./system/functions.php');
	
	$text = '';
	$title = '';
	/**
	 * @ignore
	 */
	//include('./lang/' . $user->Language . '/admin_lang.php');
	include('./system/admin_pages.php');
	// add the menu-entries for the admin menu (Link-Text, $page)
	$menuArray = array();
	$menuArray[] = array($translation->GetTranslation('admincontrol'), 'admincontrol',);
	$menuArray[] = array($translation->GetTranslation('sitepreview'), 'sitepreview');
	$menuArray[] = array($translation->GetTranslation('pagestructure'), 'pagestructure');
	$menuArray[] = array($translation->GetTranslation('menu-editor'), 'menueditor');
	$menuArray[] = array($translation->GetTranslation('preferences'), 'preferences');
	$menuArray[] = array($translation->GetTranslation('languages'), 'languages');
	$menuArray[] = array($translation->GetTranslation('modules'), 'modules');

	
	// add menu entries for activated modules
	
	// get the activated modules
	$modulesActivated = unserialize($config->Get('modules_activated'));
	// if no data is saved...
	if(!is_array($modulesActivated))
		// create the array to make arrayfunctions possible
		$modulesActivated = array();
	// sort the entries, its a bit better to hold a order in the menu-entries
	sort($modulesActivated);
	foreach($modulesActivated as $moduleActivated) {
		// check if the module has an admin-interface and if it has an info-file
		if(file_exists("./modules/$moduleActivated/{$moduleActivated}_admin.php") && file_exists("./modules/$moduleActivated/{$moduleActivated}_info.php")) {
		
			$module =  array();
			// load the info-file for the module
			/**
			 * @ignore
			 */
			include("./modules/$moduleActivated/{$moduleActivated}_info.php");
			// try to get the 'well-formed' name of the module	
			// if it isn't possible display the internal name of the module
			$moduleName =  (array_key_exists('name', $module)) ? $module['name'] : $moduleActivated;
			// ad the menu entrie for the module
			$menuArray[] = array($moduleName . '-' . $translation->GetTranslation('module'), 'module_'. $moduleActivated);
		}
	}
		
	$menuArray[] = array($translation->GetTranslation('sitestyle'), 'style');
	$menuArray[] = array($translation->GetTranslation('users'), 'users');
	$menuArray[] = array($translation->GetTranslation('groups'), 'groups');
	$menuArray[] = array($translation->GetTranslation('rights'), 'rights');
	$menuArray[] = array($translation->GetTranslation('files'), 'files');
	$menuArray[] = array($translation->GetTranslation('logout'), 'logout');
	
	// FIXME: add path links to make the usability much better! 
	$path_add = '';
	// insert the 'functions' here
	$extern_action = GetPostOrGet('action');
	if(!isset($extern_page))
		$extern_page = 'admincontrol';
	if($extern_page == '')
		$extern_page = 'admincontrol';
	if(!isset($extern_action))
		$extern_action = '';
//	counter_set("a:$extern_page");
	
	if($extern_page == 'admincontrol') {
		// Get the admin-controll-class
		include_once('classes/admin/admin_admincontrol.php');
		$title = $translation->GetTranslation('admincontrol');
		
		$admin_admincontrol = new Admin_AdminControl($sqlConnection, $translation, $config, $user, $lib, $output);
		$text = $admin_admincontrol->GetPage();
	}
	elseif($extern_page == 'sitepreview') {
		// Get the admin-sitepreview-class
		include_once('classes/admin/admin_pagepreview.php');
		$title = $translation->GetTranslation('sitepreview');
		
		$admin_PagePreview = new Admin_PagePreview($sqlConnection, $translation, $config, $user, $lib, $output);
		$text = $admin_PagePreview->GetPage();
	}
	elseif($extern_page == 'style') {
		// Get the admin-sitepreview-class
		include_once('classes/admin/admin_pagepreview.php');
		$title = $translation->GetTranslation('sitestyle');
		
		$admin_PagePreview = new Admin_PagePreview($sqlConnection, $translation, $config, $user, $lib, $output);
		$text = $admin_PagePreview->GetPage('style');
	}
	elseif($extern_page == 'languages') {
		// Get the languages-class
		include_once('classes/admin/admin_languages.php');
		$title = $translation->GetTranslation('languages');
		
		$admin_Languages = new Admin_Languages($sqlConnection, $translation, $config, $user, $lib, $output);
		$text = $admin_Languages->GetPage($extern_action);
	}
	elseif($extern_page == 'users') {
		$title = $translation->GetTranslation('users');
		$text = page_users();
	}
	elseif($extern_page == 'logout') {
		//include('./system/user_pages.php');
		//page_logout();
		$user->Logout();
		header("Location: index.php");
		die();
	}
	elseif($extern_page == 'preferences') {
		include('classes/admin/admin_preferences.php');
		$title = $translation->GetTranslation('preferences');
		//$text = page_preferences();
		$admin_page = new Admin_Preferences($sqlConnection, $translation, $config, $user, $lib, $output);
		$text = $admin_page->GetPage($extern_action);
	}
	elseif($extern_page == 'files') {
		$title = $translation->GetTranslation('files');
		include('classes/admin/admin_files.php');
		$admin_page = new Admin_Files($sqlConnection, $translation, $config, $user, $lib, $output);
		$text = $admin_page->GetPage($extern_action);
	}
	elseif($extern_page == 'pagestructure') {
		$title = $translation->GetTranslation('pagestructure');
		include('classes/admin/admin_pagestructure.php');
		$admin_page = new Admin_PageStructure($sqlConnection, $translation, $config, $user, $lib, $output);
		$text = $admin_page->GetPage($extern_action);
	}
	elseif($extern_page == 'groups') {
		$title = $admin_lang['groups'];
		include('classes/admin/admin_groups.php');
		$admin_page = new Admin_Groups($sqlConnection, $translation, $config, $user, $lib, $output);
		$text = $admin_page->GetPage($extern_action);
	}
	elseif($extern_page == 'rights') {
		$title = $translation->GetTranslation('rights');
		include('classes/admin/admin_rights.php');
		$admin_page = new Admin_Rights($sqlConnection, $translation, $config, $user, $lib, $output);
		$text = $admin_page->GetPage($extern_action);
	}
	elseif($extern_page == 'menueditor') {
		$title = $translation->GetTranslation('menu-editor');
		include('classes/admin/admin_menu.php');
		$admin_page = new Admin_Menu($sqlConnection, $translation, $config, $user, $lib, $output);
		$text = $admin_page->GetPage($extern_action);
	}
	elseif($extern_page == 'modules') {
		$title = $translation->GetTranslation('modules');
		include('classes/admin/admin_modules.php');
		$admin_page = new Admin_Modules($sqlConnection, $translation, $config, $user, $lib, $output);
		$text = $admin_page->GetPage($extern_action);
	}
	elseif(substr($extern_page, 0, 7) == 'module_')
	{
		// get the name of the module which's  admin-interface should be shown
		$moduleName = substr($extern_page, 7);
		// is the module really activated? (yes, I'm paranoid... :-P )
		if(in_array($moduleName, $modulesActivated)) {
			/**
			 * @ignore
			 */
			include_once("./modules/$moduleName/{$moduleName}_admin.php");
			if(class_exists('Admin_Module_' . $moduleName)) {
				// create a link to the initialisation-function for the module-class
				$newClass = create_function('&$SqlConnection, &$Translation, &$Config, &$User, &$ComaLib, &$ComaLata', 'return new Admin_Module_' . $moduleName . '(&$SqlConnection, &$Translation, &$Config, &$User, &$ComaLib, &$ComaLata);');
				// create the module-class
				$moduleAdminInterface = $newClass($sqlConnection, $translation, $config, $user, $lib, $output);
				if(isset($moduleAdminInterface)) {
					$text = $moduleAdminInterface->GetPage($extern_action);
					$title = $moduleAdminInterface->GetTitle();
				}
			} 
		}
	}
	//
	// end of the 'functions'
	//
	/**
	 * @ignore
	 */
	$menu = array();
	
	foreach($menuArray as $part) {
		if($extern_page == $part[1])
			$linkStyle = ' class="actual"';
		else
			$linkStyle = '';
		$menu[] = array('LINK_TEXT' => $part[0], 'LINK' => 'admin.php?page=' . $part[1], 'CSS_ID' => '', 'LINK_STYLE' => $linkStyle);
	}
	// Replace all menus except of DEFAULT with data of database 
	$sql = "SELECT *
		FROM " . DB_PREFIX . "menu";
	$menus = $sqlConnection->SqlQuery($sql);
	while ($database_menu = mysql_fetch_object($menus)) {
		if ($database_menu->menu_name != 'DEFAULT') {
			$output->SetReplacement('MENU_' . $database_menu->menu_name, $outputpage->GenerateMenu($database_menu->menu_id));
		}
	}
	// Replace DEFAULT menu by admin menuarray
	$output->SetReplacement('MENU_DEFAULT' , $menu);
	// Replace outputtext
	$output->SetReplacement('TEXT' , $text);
	// Replace Title
	$output->Title = $translation->GetTranslation('administration') . ' - ' . $title;
	$output->SetCondition('notathome', true);
	$output->SetCondition('notinindex', true);
	$output->SetCondition('notinadmin', false);
	$path = '';
	if($extern_page != 'admincontrol')
		$path = " -> <a href=\"admin.php?page=$extern_page\">$title</a>$path_add";
	$output->SetReplacement('PATH', "<a href=\"admin.php?page=admincontrol\">Admin</a>$path");
	
	$output->GenerateOutput();
	echo $output->GeneratedOutput;
	echo "\r\n<!-- rendered in " . round(getmicrotime(microtime()) - getmicrotime($starttime), 4) . ' seconds with ' . $sqlConnection->QueriesCount .' SQL queries -->';
?>