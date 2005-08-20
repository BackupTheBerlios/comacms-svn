<?php
/*****************************************************************************
 *
 *  file		: common.php
 *  created		: 2005-08-05
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
	
	import_request_variables("gP", "extern_");
	@include_once("config.php");
	if(!defined('COMACMS_RUN'))
		die("");
	if(file_exists("./install/") && !file_exists("./.svn/")) {
		if(defined("COMACMS_INSTALLED"))
			die("Please remove the install-folder id would be better.");
		else
			header('location: install/install.html');
	}
	include_once("functions.php");
	include_once("counter.php");
	_start();
	//
	// load vars
	//
	$sql = "SELECT *
		FROM " . DB_PREFIX . "config";
	$var_result = db_result($sql);
	while($var_data = mysql_fetch_object($var_result)) {
		$_N_ = 'internal_' . $var_data->config_name;
		$$_N_ = $var_data->config_value;
	}
	//
	// end
	//
	
	//
	// if there is no sitename to load get the defaultssite
	//
	if(!isset($extern_page) && isset($internal_default_page))
		$extern_page = $internal_default_page;
	elseif(!isset($extern_page))
		$extern_page = 'home';
	
	if(startsWith($extern_page, 'a:'))
 		header('Location: admin.php?page='.substr($extern_page, 2));
 	elseif(startsWith($extern_page, 's:'))
 		header('Location: special.php?page='.substr($extern_page, 2));
 	elseif(startsWith($extern_page, 'l:'))
 		header('Location: index.php?page='.substr($extern_page, 2));
 	elseif(startsWith($extern_page, 'g:'))
 		header('Location: gallery.php?page='.substr($extern_page, 2));
	
	set_usercookies();
	//
	// TOOD : GET AUTHORISATION
	//
	if(endsWith($_SERVER['PHP_SELF'], 'admin.php') && !$actual_user_is_admin && !$actual_user_is_logged_in)
		header('Location: special.php?page=login');
	$pagePrefix = 'l:';
	if(endsWith($_SERVER['PHP_SELF'], 'admin.php'))
		$pagePrefix = 'a:';
	elseif(endsWith($_SERVER['PHP_SELF'], 'gallery.php'))
		$pagePrefix = 'g:';
	elseif(endsWith($_SERVER['PHP_SELF'], 'special.php'))
		$pagePrefix = 's:';
		
	counter_set($pagePrefix . $extern_page);
	
	actual_online();
	
	if(!isset($internal_style))
		$internal_style = "clear";
	
	if(isset($extern_style) && $actual_user_is_admin && (!endsWith($_SERVER['PHP_SELF'], 'admin.php') || isset($extern_save)))
		$internal_style = $extern_style;
	
	$stylefile = "./styles/$internal_style/mainpage.php";
	$_file = fopen($stylefile, 'r');
	$page = fread($_file, filesize($stylefile));
	if(!isset($internal_default_page))
		$internal_default_page = '';
	if($extern_page == $internal_default_page)
		$page = preg_replace("/\<notathome\>(.+?)\<\/notathome\>/s", "", $page);
	else
		$page = preg_replace("/\<notathome\>(.+?)\<\/notathome\>/s", "$1", $page);
	if($pagePrefix == 'a:')
		$page = preg_replace("/\<notinadmin\>(.+?)\<\/notinadmin\>/s", "", $page);
	else
		$page = preg_replace("/\<notinadmin\>(.+?)\<\/notinadmin\>/s", "$1", $page);
?>