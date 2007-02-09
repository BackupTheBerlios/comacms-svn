<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005-2007 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : common.php
 # created              : 2005-08-05
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
	require_once __ROOT__ . '/classes/sql.php';
	require_once __ROOT__ . '/classes/comalib.php';
	require_once __ROOT__ . '/classes/outputpage.php';
	require_once __ROOT__ . '/classes/config.php';
	require_once __ROOT__ . '/classes/account.php';
	require_once __ROOT__ . '/classes/inlinemenu.php';
	require_once __ROOT__ . '/classes/module.php';
	require_once __ROOT__ . '/classes/language.php';
	require_once __ROOT__ . '/functions.php';
	require_once __ROOT__ . '/lib/comalate/comalate.class.php';
	$lib = new ComaLib();
	
	$page = GetPostOrGet('page');	
	
	$queries_count = 0;
	define('DB_PREFIX', $d_pre);
	$sqlConnection = new Sql($d_user, $d_pw, $d_server);
	$sqlConnection->Connect($d_base);
	$config = new Config(&$sqlConnection);
	$config->LoadAll();
	$user = new Account($sqlConnection);
	$translation = new Language($user->Language);
	$translation->AddSources(__ROOT__  . '/lang/');
	$output = new ComaLate();
	$styleName = $config->Get('style', 'default');
	$headerStyleName = GetPostOrGet('style');
	if(!empty($headerStyleName))
		$styleName = $headerStyleName; 
	$output->LoadTemplate('./styles/', $styleName);
	$output->SetMeta('generator', 'ComaCMS v0.2 (http://comacms.berlios.de)');
	
	$output->SetCondition('notinadmin', true);
	
	if(!isset($page) && substr($_SERVER['PHP_SELF'], -9) == 'index.php')
		$page = $config->Get('default_page', 'home');
	elseif(!isset($page))
		$page = '';
	
	if(substr($page, 1,1) == ':') { 	
		$sign = substr($page, 0, 1);
		switch($sign) {
			// "a" => admin(interface)
			case 'a': 
				header('Location: admin.php?page=' . substr($page, 2));
				die();
			// s => special(page)
			case 's':
				header('Location: special.php?page=' . substr($page, 2));
 				die();
 			// s => local
 			case 'l':
 				header('Location: index.php?page=' . substr($extern_page, 2));
 				die();
		}
	}
 	
 	$pagePrefix = 'l:';
	if(substr($_SERVER['PHP_SELF'], -9) == 'admin.php')
		$pagePrefix = 'a:';
	else if(substr($_SERVER['PHP_SELF'], -11) == 'special.php')
		$pagePrefix = 's:';
	else
		$pagePrefix = '';
	
	$user->SetPage($pagePrefix . $page, $config);
?>
