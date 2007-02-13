<?php
/**
 * @package ComaCMS
 * @subpackage AdminInterface
 * @copyright (C) 2005-2007 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : admin.php	
 # created              : 2005-07-11
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
	define('COMACMS_RUN', true);
	// include the file common.php to make all preparing actions
	include('common.php');
	
	
	$action = GetPostOrGet('action');
	if(!isset($page))
		$page = 'admincontrol';
	if($page == '')
		$page = 'admincontrol';
	if(!isset($action))
		$action = '';
	

		
	// If the user isn't logged in
	if(!$user->IsLoggedIn)  {
		$redirect = '';
		if($page != '')
			$redirect .= '&redirect=' . rawurldecode($page);
		if($redirect != '' && $action != '' )
			$redirect .= '&action='. rawurldecode($action);
		header('Location: special.php?page=login' . (($user->LoginError != -1) ? ('&error=' . $user->LoginError) : '') . $redirect);
		die();
	}
	// The user must be a admin to access this page
	if(!$user->IsAdmin && $user->IsLoggedIn) {
		header('Location: index.php');
		die();
	}
	
	$outputpage = new OutputPage($sqlConnection, $config, $translation, $output, $user);
	
	$text = '';
	$title = '';
	/**
	 * @ignore
	 */
	
	// add the menu-entries for the admin menu (Link-Text, $page)
	$menuArray = array();
	$menuArray[] = array($translation->GetTranslation('admincontrol'), 'admincontrol',);
	$menuArray[] = array($translation->GetTranslation('pagepreview'), 'pagepreview');
	$menuArray[] = array($translation->GetTranslation('pagestructure'), 'pagestructure');
	$menuArray[] = array($translation->GetTranslation('menu-editor'), 'menueditor');
	$menuArray[] = array($translation->GetTranslation('preferences'), 'preferences');
	//$menuArray[] = array($translation->GetTranslation('languages'), 'languages');
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
	// $menuArray[] = array($translation->GetTranslation('groups'), 'groups');
	// $menuArray[] = array($translation->GetTranslation('rights'), 'rights');
	$menuArray[] = array($translation->GetTranslation('files'), 'files');
	$menuArray[] = array($translation->GetTranslation('logout'), 'logout');
	
	// FIXME: add path links to make the usability much better! 
	$path_add = '';
	
	// insert the 'functions' here
	
	
	switch($page) {
		case 'pagepreview':
		case 'style':
			// Load the pagepreview-class
			include_once(__ROOT__ . '/classes/admin/admin_pagepreview.php');
			
			if ($page == 'style')
				$title = $translation->GetTranslation('sitestyle');
			else
				$title = $translation->GetTranslation('pagepreview');
			$adminClass = new Admin_PagePreview($sqlConnection, $translation, $config, $user, $lib, $output);
			if($page == 'style')
				$action = 'style';
			$text = $adminClass->GetPage($action);
			break;
		
		case 'languages':
			// Load the languages-class
			include_once(__ROOT__ . '/classes/admin/admin_languages.php');
			
			$title = $translation->GetTranslation('languages');
			$adminClass = new Admin_Languages($sqlConnection, $translation, $config, $user, $lib, $output);
			$text = $adminClass->GetPage($action);
			break;
		
		case 'users':
			// Load the usermanagement-class
			include_once(__ROOT__ . '/classes/admin/admin_usermanagement.php');
			
			$title = $translation->GetTranslation('users');
			$usermanagementClass = new Admin_Usermanagement($sqlConnection, $translation, $config, $user, $lib, $output);
			$text = $usermanagementClass->GetPage($action);
			break;
		
		case 'logout':
			// call the logout and redirect to the 
			$user->Logout();
			header("Location: index.php");
			die();
		
		case 'preferences':
			// Load the preferences-class (preferences-management)
			include_once (__ROOT__ . '/classes/admin/admin_preferences.php');
			
			$title = $translation->GetTranslation('preferences');
			$adminClass = new Admin_Preferences($sqlConnection, $translation, $config, $user, $lib, $output);
			$text = $adminClass->GetPage($action);
			break;
		
		case 'files':
			// Load the files-class (file-management)
			include_once(__ROOT__ . '/classes/admin/admin_files.php');
			
			$title = $translation->GetTranslation('files');
			$adminClass = new Admin_Files($sqlConnection, $translation, $config, $user, $lib, $output);
			$text = $adminClass->GetPage($action);
			break;
		
		case 'pagestructure':
			// Load the pagestructure-class (manage & edit all pages)
			include_once(__ROOT__ . '/classes/admin/admin_pagestructure.php');
			
			$title = $translation->GetTranslation('pagestructure');
			$adminClass = new Admin_PageStructure($sqlConnection, $translation, $config, $user, $lib, $output);
			$text = $adminClass->GetPage($action);
			break;
		
		/*case 'groups':
			// Load the groups-class (groups-management)		
			include_once(__ROOT__  . '/classes/admin/admin_groups.php');

			$title = $translation->GetTranslation('groups');
			$adminClass = new Admin_Groups($sqlConnection, $translation, $config, $user, $lib, $output);
			$text = $adminClass->GetPage($action);
			break;*/
		
		/*case 'rights':
			// Load the rights-class (access-management)
			include_once(__ROOT__ . '/classes/admin/admin_rights.php');
			
			$title = $translation->GetTranslation('rights');
			$adminClass = new Admin_Rights($sqlConnection, $translation, $config, $user, $lib, $output);
			$text = $adminClass->GetPage($action);
			break;*/
		
		case 'menueditor':
			// Load the menu-class (menu-management)
			include_once(__ROOT__ . '/classes/admin/admin_menu.php');
			
			$title = $translation->GetTranslation('menu-editor');
			$adminClass = new Admin_Menu($sqlConnection, $translation, $config, $user, $lib, $output);
			$text = $adminClass->GetPage($action);			
			break;
		
		case 'modules':	
			// Load te modles-class (module-management)
			include_once(__ROOT__ . '/classes/admin/admin_modules.php');
			
			$title = $translation->GetTranslation('modules');
			$adminClass = new Admin_Modules($sqlConnection, $translation, $config, $user, $lib, $output);
			$text = $adminClass->GetPage($action);
			break;
		
		case 'admincontrol': 		
		default:
			if(substr($page, 0, 7) == 'module_') {
				// get the name of the module which's admin-interface should be shown
				$moduleName = substr($page, 7);
				// is the module really activated? (yes, I'm paranoid... :-P )
				if(in_array($moduleName, $modulesActivated)) {
					// Load the admin-class of the module
					include_once(__ROOT__ . "/modules/$moduleName/{$moduleName}_admin.php");
					if(class_exists('Admin_Module_' . $moduleName)) {
						// create a link to the initialisation-function for the module-class
						$newClass = create_function('&$SqlConnection, &$Translation, &$Config, &$User, &$ComaLib, &$ComaLata', 'return new Admin_Module_' . $moduleName . '(&$SqlConnection, &$Translation, &$Config, &$User, &$ComaLib, &$ComaLata);');
						// create the module-class
						$moduleAdminInterface = $newClass($sqlConnection, $translation, $config, $user, $lib, $output);
						if(isset($moduleAdminInterface)) {
							$text = $moduleAdminInterface->GetPage($action);
							$title = $moduleAdminInterface->GetTitle();
						}
					} 
				}
			}
			else {		
				// Load the admincontrol-class (system-overview)
				include_once(__ROOT__ . '/classes/admin/admin_admincontrol.php');
				
				$title = $translation->GetTranslation('admincontrol');
				$adminClass = new Admin_AdminControl($sqlConnection, $translation, $config, $user, $lib, $output);
				$text = $adminClass->GetPage();
			}
			break;
		
	}
	
	$menu = array();
	
	foreach($menuArray as $part) {
		if($page == $part[1])
			$linkStyle = ' class="actual"';
		else
			$linkStyle = '';
		$menu[] = array('LINK_TEXT' => $part[0], 'LINK' => 'admin.php?page=' . $part[1], 'CSS_ID' => '', 'LINK_STYLE' => $linkStyle);
	}
	// Replace all menus except of DEFAULT with data of database 
	$sql = "SELECT menu_name, menu_id
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
	if($page != 'admincontrol')
		$path = " -> <a href=\"admin.php?page=$page\">$title</a>$path_add";
	$output->SetReplacement('PATH', "<a href=\"admin.php?page=admincontrol\">Admin</a>$path");
	
	$output->GenerateOutput();
	echo $output->GeneratedOutput;
	echo "\r\n<!-- rendered in " . round(getmicrotime(microtime()) - getmicrotime($starttime), 4) . ' seconds with ' . $sqlConnection->QueriesCount .' SQL queries -->';
?>