<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005-2006 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : common.php
 # created              : 2005-08-05
 # copyright            : (C) 2005-2005 The ComaCMS-Team
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
	@include_once("./config.php");
	$starttime = microtime();
	if(!defined('COMACMS_RUN'))
		die("");
	if(file_exists("./install/") && !file_exists("./.svn/") && !file_exists("./_svn/")) {
		if(defined("COMACMS_INSTALLED"))
			die("Please remove the install-folder it would be better.");
		else
			header('location: install/install.php');
	}
	header('Content-type: text/html; charset=utf-8');
	define('__ROOT__', dirname(__FILE__));
	include('classes/sql.php');
	include('classes/comalib.php');
	include('classes/outputpage.php');
	include('classes/config.php');
	include('classes/user.php');
	include('classes/inlinemenu.php');
	include('classes/module.php');
	include('functions.php');
	include('lib/comalate/comalate.class.php');
	$lib = new ComaLib();
	
	$extern_page = GetPostOrGet('page');	
	
	$queries_count = 0;
	define('DB_PREFIX', $d_pre);
	$sqlConnection = new Sql($d_user, $d_pw, $d_server);
	$sqlConnection->Connect($d_base);
	$config = new Config();
	$config->LoadAll();
	$user = new User($sqlConnection);
	$output = new ComaLate();
	$styleName = $config->Get('style', 'default');
	$headerStyleName = GetPostOrGet('style');
	if(!empty($headerStyleName))
		$styleName = $headerStyleName; 
	$output->LoadTemplate('./styles/', $styleName);
	$output->SetMeta('generator', 'ComaCMS v0.2 (http://comacms.berlios.de)');
	$output->SetCondition('notinadmin', true);
	
	if(!isset($extern_page) && endsWith($_SERVER['PHP_SELF'], 'index.php'))
		$extern_page = $config->Get('default_page', 'home');
	elseif(!isset($extern_page))
		$extern_page = '';
		
	
	if(startsWith($extern_page, 'a:')) {
 		header('Location: admin.php?page='.substr($extern_page, 2));
 		die();
	}
 	elseif(startsWith($extern_page, 's:')) {
 		header('Location: special.php?page='.substr($extern_page, 2));
 		die();
	}
 	elseif(startsWith($extern_page, 'l:')) {
 		header('Location: index.php?page='.substr($extern_page, 2));
 		die();
	}
 	
 	$pagePrefix = 'l:';
	if(endsWith($_SERVER['PHP_SELF'], 'admin.php'))
		$pagePrefix = 'a:';
	elseif(endsWith($_SERVER['PHP_SELF'], 'special.php'))
		$pagePrefix = 's:';
	else
		$pagePrefix = '';
	
	$user->SetPage($pagePrefix . $extern_page, $config);
?>
