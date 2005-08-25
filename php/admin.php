<?php
/*****************************************************************************
 *
 *  file		: admin.php
 *  created		: 2005-07-11
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

	define("COMACMS_RUN", true);
	
	include("common.php");
	include('./system/functions.php');
	
	$text = '';
	$title = '';
	include('./lang/' . $actual_user_lang . '/admin_lang.php');
	include('./system/admin_pages.php');
	$menu_array = array();
	$menu_array[] = array($admin_lang['admincontrol'], 'admin.php?page=admincontrol');
	$menu_array[] = array($admin_lang['sitepreview'], 'admin.php?page=sitepreview');
	$menu_array[] = array($admin_lang['preferences'], 'admin.php?page=preferences');
	$menu_array[] = array($admin_lang['menueeditor'], 'admin.php?page=menueeditor');
	$menu_array[] = array($admin_lang['pageeditor'], 'admin.php?page=pageeditor');
	$menu_array[] = array($admin_lang['inlinemenu'], 'admin.php?page=inlinemenu');
	$menu_array[] = array($admin_lang['news'], 'admin.php?page=news');
	$menu_array[] = array($admin_lang['dates'], 'admin.php?page=dates');
	$menu_array[] = array($admin_lang['articles'], 'admin.php?page=articles');
	$menu_array[] = array($admin_lang['sitestyle'], 'admin.php?page=sitestyle');
	$menu_array[] = array($admin_lang['users'], 'admin.php?page=users');
	$menu_array[] = array($admin_lang['gallery editor'], 'admin.php?page=gallery_editor');
	$menu_array[] = array($admin_lang['files'], 'admin.php?page=files');
	$menu_array[] = array($admin_lang['logout'], 'admin.php?page=logout');
	
	//
	// insert the 'functions' here
	//
	if(!isset($extern_page))
		counter_set('a:', $extern_page);
	if($extern_page == 'admincontrol') {
		$title = $admin_lang['admincontrol'];
		$text = page_admincontrol();
	}
	elseif($extern_page == 'sitepreview') {
		$title = $admin_lang['sitepreview'];
		$text = page_sitepreview();
	}
	elseif($extern_page == 'menueeditor') {
		$title = $admin_lang['menueeditor'];
		$text = page_menueeditor();
	}
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
		$text = page_dates();
	}
	elseif($extern_page == 'articles') {
		$title = $admin_lang['articles'];
		$text = page_articles();
	}
	elseif($extern_page == 'pageeditor') {
		$title = $admin_lang['pageeditor'];
		include('./system/user_pages.php');
		$text = page_pageeditor();
	}
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
	elseif($extern_page == 'gallery_editor') {
		$title = $admin_lang['gallery editor'];
		$text = page_gallery_editor();
	}
	elseif($extern_page == 'files') {
		include('system/user_pages.php');
		$title = $admin_lang['files'];
		$text = page_files();
	}
	elseif($extern_page == 'inlinemenu') {
		$title = $admin_lang['inlinemenu'];
		$text = page_inlinemenu();
	}
	
	//
	// end of the 'functions'
	//
	if(@$internal_style == '')
		$internal_style = 'clear';
	include('./styles/' . $internal_style . '/menue.php');
	$menu = '';
	foreach($menu_array as $part) {
		$menu_str = $menu_link;
		$menu_str = str_replace('[text]', $part[0], $menu_str);
		$menu_str = str_replace('[link]', $part[1], $menu_str);
		$menu_str = str_replace('[new]', '', $menu_str);
		$menu .= $menu_str . "\r\n";
	}
	$path = '';
	if($extern_page != 'admincontrol')
		$path = " -> <a href=\"admin.php?page=$extern_page\">$title</a>";
	$page = str_replace("[position]", "<a href=\"admin.php?page=admincontrol\">Admin</a>$path", $page);
	$page = str_replace('[menu]', $menu, $page);
	$page = str_replace('[title]', $title, $page);
	$page = str_replace('[menu2]', '', $page);
	$page = str_replace('[text]', $text, $page);
	$page = str_replace("[inlinemenu]", '', $page);
	$page = preg_replace("/\<forinlinemenu\>(.+?)\<\/forinlinemenu\>/s", "", $page);
	echo $page;
?>