<?php
/*****************************************************************************
 *
 *  file		: common.php
 *  created		: 2005-08-05
 *  copyright		: (C) 2005 The Comasy-Team
 *  email		: comasy@williblau.de
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
	if(!defined('COMASY_RUN'))
		die("");
	if(file_exists("./install/") && !file_exists("./.svn/")) {
		if(defined("COMASY_INSTALLED"))
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
		FROM " . DB_PREFIX . "vars";
	$var_result = db_result($sql);
	while($var_data = mysql_fetch_object($var_result)) {
		$_N_ = 'internal_' . $var_data->name;
		$$_N_ = $var_data->value;
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
 		header('Location: admin.php?site='.substr($extern_page, 2));
	
	set_usercookies();
	//
	// TOOD : GET AUTHORISATION
	//
		$pagePrefix = 'l:';
	if(endsWith($_SERVER['PHP_SELF'], 'admin.php'))
		$pagePrefix = 'a:';
	elseif(endsWith($_SERVER['PHP_SELF'], 'gallery.php'))
		$pagePrefix = 'g:';
		
	counter_set($pagePrefix . $extern_page);
	
	actual_online();
	
	if(!isset($internal_style))
		$internal_style = "clear";
	
	if(isset($_GET['style']) && $actual_user_is_admin)
		$internal_style = $_GET['style'];
	
	
	
?>