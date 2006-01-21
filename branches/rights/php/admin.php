<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005 The ComaCMS-Team
 */
 #----------------------------------------------------------------------#
 # file			: admin.php					#
 # created		: 2005-07-11					#
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
	define('COMACMS_RUN', true);
	
	include('common.php');

	if(!$user->isLoggedIn)  {
		header('Location: special.php?page=login' . (($user->loginError != -1) ? ('&error=' . $user->loginError) : ''));
		die();
	}
	if(!$user->isAdmin && $user->isLoggedIn) {
		header('Location: index.php');
		die();
	}
	include_once('./system/functions.php');
	
	$text = '';
	$title = '';
	/**
	 * @ignore
	 */
	include('./lang/' . $user->language . '/admin_lang.php');
	include('./system/admin_pages.php');
	$menu_array = array();
	$menu_array[] = array($admin_lang['admincontrol'], 'admin.php?page=admincontrol');
	$menu_array[] = array($admin_lang['sitepreview'], 'admin.php?page=sitepreview');
	$menu_array[] = array($admin_lang['preferences'], 'admin.php?page=preferences');
	$menu_array[] = array($admin_lang['pagestructure'], 'admin.php?page=pagestructure');
//	$menu_array[] = array($admin_lang['menueeditor'], 'admin.php?page=menueeditor');
//	$menu_array[] = array($admin_lang['pageeditor'], 'admin.php?page=pageeditor');
//	$menu_array[] = array($admin_lang['inlinemenu'], 'admin.php?page=inlinemenu');
	$menu_array[] = array($admin_lang['news'], 'admin.php?page=news');
	$menu_array[] = array($admin_lang['dates'], 'admin.php?page=dates');
	$menu_array[] = array($admin_lang['articles'], 'admin.php?page=articles');
	$menu_array[] = array($admin_lang['sitestyle'], 'admin.php?page=sitestyle');
	$menu_array[] = array($admin_lang['users'], 'admin.php?page=users');
	$menu_array[] = array($admin_lang['groups'], 'admin.php?page=groups');
	$menu_array[] = array($admin_lang['rights'], 'admin.php?page=rights');
	$menu_array[] = array($admin_lang['files'], 'admin.php?page=files');
	$menu_array[] = array($admin_lang['logout'], 'admin.php?page=logout');
	
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
		include('classes/admin_admincontrol.php');
		$admin_admincontrol = new Admin_AdminControl($admin_lang, $config);
		$text = $admin_admincontrol->GetPage($extern_action);
	}
	elseif($extern_page == 'sitepreview') {
		$title = $admin_lang['sitepreview'];
		$text = page_sitepreview();
	}
/*	elseif($extern_page == 'menueeditor') {
		$title = $admin_lang['menueeditor'];
		$text = page_menueeditor();
	}*/
	elseif($extern_page == 'sitestyle') {
		$title = $admin_lang['sitestyle'];
		$text = page_sitestyle();
	}
	elseif($extern_page == 'news') {
		$title = $admin_lang['news'];
		$text = page_news();
	}
	elseif($extern_page == 'dates') {
		$title = $admin_lang['dates'];
		include('classes/admin_dates.php');
		$admin_dates = new Admin_Dates();
		$text = $admin_dates->GetPage($extern_action, $admin_lang);
	}
	elseif($extern_page == 'articles') {
		$title = $admin_lang['articles'];
		include('classes/admin_articles.php');
		$admin_articles = new Admin_Articles();
		$text = $admin_articles->GetPage($extern_action, $admin_lang);
		//$text = page_articles();
	}
/*	elseif($extern_page == 'pageeditor') {
		$title = $admin_lang['pageeditor'];
		include('./system/user_pages.php');
		$text = page_pageeditor();
	}*/
	elseif($extern_page == 'users') {
		$title = $admin_lang['users'];
		$text = page_users();
	}
	elseif($extern_page == 'logout') {
		include('./system/user_pages.php');
		page_logout();
	}
	elseif($extern_page == 'preferences') {
		$title = $admin_lang['preferences'];
		$text = page_preferences();
	}
/*	elseif($extern_page == 'gallery_editor') {
		$title = $admin_lang['gallery editor'];
		$text = page_gallery_editor();
	}*/
	elseif($extern_page == 'files') {
		include('system/user_pages.php');
		$title = $admin_lang['files'];
		$text = page_files();
	}
/*	elseif($extern_page == 'inlinemenu') {
		$title = $admin_lang['inlinemenu'];
		$text = page_inlinemenu();
	}*/
	elseif($extern_page == 'pagestructure') {
		$title = $admin_lang['pagestructure'];
		include('classes/admin_pagestructure.php');
		$admin_page = new Admin_PageStructure();
		$text = $admin_page->GetPage($extern_action);
	}
	elseif($extern_page == 'groups') {
		$title = $admin_lang['groups'];
		include('classes/admin_groups.php');
		$admin_page = new Admin_Groups();
		$text = $admin_page->GetPage($extern_action, $admin_lang);
	}
	elseif($extern_page == 'rights') {
		$title = $admin_lang['rights'];
		include('classes/admin_rights.php');
		$admin_page = new Admin_Rights();
		$text = $admin_page->GetPage($extern_action, $admin_lang);
	}
	//
	// end of the 'functions'
	//
	/**
	 * @ignore
	 */
	include($page->Templatefolder . '/menu.php');
	$menu = '';
	foreach($menu_array as $part) {
		$menu_str = $menu_link;
		$menu_str = str_replace('[TEXT]', $part[0], $menu_str);
		$menu_str = str_replace('[LINK]', $part[1], $menu_str);
		$menu_str = str_replace('[NEW]', '', $menu_str);
		$menu .= $menu_str . "\r\n";
	}
	$path = '';
	if($extern_page != 'admincontrol')
		$path = " -> <a href=\"admin.php?page=$extern_page\">$title</a>$path_add";
	$page->Template = str_replace('[POSITION]', "<a href=\"admin.php?page=admincontrol\">Admin</a>$path", $page->Template);
	$page->Template = str_replace('[MENU]', $menu, $page->Template);
	$page->Title = $title;
	$page->Template = str_replace('[MENU2]', '', $page->Template);
	$page->Text = $text;
	
	$page->Template = str_replace('[INLINEMENU]', '', $page->Template);
	$page->Template = preg_replace("/\<forinlinemenu\>(.+?)\<\/forinlinemenu\>/s", "", $page->Template);
	$page->Template = preg_replace("/\<notinadmin\>(.+?)\<\/notinadmin\>/s", '', $page->Template);
	
	echo $page->OutputHTML();
?>