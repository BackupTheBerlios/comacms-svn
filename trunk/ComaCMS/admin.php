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
	include('./lang/' . $user->Language . '/admin_lang.php');
	include('./system/admin_pages.php');
	// add the menu-entries for the admin menu (Link-Text, $page)
	$menuArray = array();
	$menuArray[] = array($admin_lang['admincontrol'], 'admincontrol',);
	$menuArray[] = array($admin_lang['sitepreview'], 'sitepreview');
	$menuArray[] = array($admin_lang['pagestructure'], 'pagestructure');
	$menuArray[] = array($admin_lang['menu-editor'], 'menueditor');
	$menuArray[] = array($admin_lang['preferences'], 'preferences');
	$menuArray[] = array($admin_lang['modules'], 'modules');

	
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
			$menuArray[] = array($moduleName . '-' . $admin_lang['module'], 'module_'. $moduleActivated);
		}
	}
		
	$menuArray[] = array($admin_lang['sitestyle'], 'sitestyle');
	$menuArray[] = array($admin_lang['users'], 'users');
	$menuArray[] = array($admin_lang['groups'], 'groups');
	$menuArray[] = array($admin_lang['rights'], 'rights');
	$menuArray[] = array($admin_lang['files'], 'files');
	$menuArray[] = array($admin_lang['logout'], 'logout');
	
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
		$title = $admin_lang['admincontrol'];
		include('classes/admin/admin_admincontrol.php');
		$admin_admincontrol = new Admin_AdminControl($admin_lang, $config);
		$text = $admin_admincontrol->GetPage($extern_action);
	}
	elseif($extern_page == 'sitepreview') {
		$title = $admin_lang['sitepreview'];
		$text = page_sitepreview();
	}
	elseif($extern_page == 'sitestyle') {
		$title = $admin_lang['sitestyle'];
		$text = page_sitestyle();
	}
	elseif($extern_page == 'users') {
		$title = $admin_lang['users'];
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
		$title = $admin_lang['preferences'];
		//$text = page_preferences();
		$admin_page = new Admin_Preferences($sqlConnection, $admin_lang, $config);
		$text = $admin_page->GetPage($extern_action);
	}
	elseif($extern_page == 'files') {
		$title = $admin_lang['files'];
		include('classes/admin/admin_files.php');
		$admin_page = new Admin_Files($sqlConnection, $admin_lang, $user, $config, $lib);
		$text = $admin_page->GetPage($extern_action);
	}
	elseif($extern_page == 'pagestructure') {
		$title = $admin_lang['pagestructure'];
		include('classes/admin/admin_pagestructure.php');
		$admin_page = new Admin_PageStructure($sqlConnection, $admin_lang, $user, $config);
		$text = $admin_page->GetPage($extern_action);
	}
	elseif($extern_page == 'groups') {
		$title = $admin_lang['groups'];
		include('classes/admin/admin_groups.php');
		$admin_page = new Admin_Groups();
		$text = $admin_page->GetPage($extern_action, $admin_lang);
	}
	elseif($extern_page == 'rights') {
		$title = $admin_lang['rights'];
		include('classes/admin/admin_rights.php');
		$admin_page = new Admin_Rights();
		$text = $admin_page->GetPage($extern_action, $admin_lang);
	}
	elseif($extern_page == 'menueditor') {
		$title = $admin_lang['menu-editor'];
		include('classes/admin/admin_menu.php');
		$admin_page = new Admin_Menu($sqlConnection, $admin_lang);
		$text = $admin_page->GetPage($extern_action);
	}
	elseif($extern_page == 'modules') {
		$title = $admin_lang['modules'];
		include('classes/admin/admin_modules.php');
		$admin_page = new Admin_Modules($sqlConnection, $admin_lang, $config);
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
			include("./modules/$moduleName/{$moduleName}_admin.php");
			if(class_exists('Admin_Module_' . $moduleName)) {
				// create a link to the initialisation-function for the module-class
				$newClass = create_function('&$SqlConnection, &$User, &$Lang, &$Config, &$ComaLate, &$ComaLib', 'return new Admin_Module_' . $moduleName . '(&$SqlConnection, &$User, &$Lang, &$Config, &$ComaLate, &$ComaLib);');
				// create the module-class
				$moduleAdminInterface = $newClass($sqlConnection, $user, $admin_lang, $config, $output, $lib);
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
	$output->SetReplacement('MENU_DEFAULT' , $menu);
	$output->SetReplacement('TEXT' , $text);
	$output->Title = $admin_lang['administration'] . ' - ' . $title;
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