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
	 
	// Initialize System
	@include_once("./config.php");
	$starttime = microtime();
	
	// Ignore direct access to the common.php file
	if(!defined('COMACMS_RUN'))
		die("");
		
	// If the installation is a subversion working repository do not remove the install folder
	if(file_exists("./install/") && !file_exists("./.svn/") && !file_exists("./_svn/")) {
		if(defined("COMACMS_INSTALLED"))
			die("Please remove the install-folder it would be better.");
		else
			header('location: install/install.php');
	}
	
	// Set headerinformation and load neccessary data files
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
	
	// Get the requested page from the system
	$page = GetPostOrGet('page');	
	
	// Define neccessary variables and constantes for the system
	$queries_count = 0;
	define('DB_PREFIX', $d_pre);
	
	// Initialize a connection to the mysql database
	$sqlConnection = new Sql($d_user, $d_pw, $d_server);
	$sqlConnection->Connect($d_base);
	
	// Load all configuration saved in the mysql database
	$config = new Config(&$sqlConnection);
	$config->LoadAll();
	
	// Initialize the translation of short texts in the system
	$translation = new Language(&$sqlConnection);
	
	// Create a new user class
	$user = new Account(&$sqlConnection, &$translation, &$config);
	
	// Load the language file
	$translation->AddSources(__ROOT__  . '/lang/');
	
	// Load the comascript interpreter for the html output, set document and style information
	$output = new ComaLate();
	$output->SetDoctype(DOCTYPE_XHTML_TRANSITIONAL);
	
	$styleName = $config->Get('style', 'default');
	$headerStyleName = GetPostOrGet('style');
	if(!empty($headerStyleName))
		$styleName = $headerStyleName; 
	$output->LoadTemplate('./styles/', $styleName);
	
	$output->SetMeta('generator', 'ComaCMS v0.2 (http://comacms.berlios.de)');
	
	// Set page conditions
	$output->SetCondition('notinadmin', true);
	
	// Try to get a name of the requested page
	if(!isset($page) && substr($_SERVER['PHP_SELF'], -9) == 'index.php')
		$page = $config->Get('default_page', 'home');
	elseif(!isset($page))
		$page = '';
	
	// Switch between the different accesspoints of the system for different page types
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
 			
 			// l => local
 			case 'l':
 				header('Location: index.php?page=' . substr($page, 2));
 				die();
 			
 			// d => download
 			case 'd':
 				header('Location: download.php?file_id=' . substr($page, 2));
 				die();
		}
	}
 	
 	// Link pageprefixes to accesspoint files
	if(substr($_SERVER['PHP_SELF'], -9) == 'admin.php')
		$pagePrefix = 'a:';
	else if(substr($_SERVER['PHP_SELF'], -11) == 'special.php')
		$pagePrefix = 's:';
	else if(substr($_SERVER['PHP_SELF'], -12) == 'download.php') {
		$pagePrefix = 'd:';
		$page = GetPostOrGet('file_id');
	}
	else
		$pagePrefix = '';
	
	// Set the pageinformation of the user
	$user->SetPage($pagePrefix . $page);
	
	// Get a list of all installed languages
	$installedLanguages = array();
	$languageFolder = dir(__ROOT__ . "/lang/");
	while($file = $languageFolder->read()) {
		
		// check if the found file is really a language file
		if($file != "." && $file != ".." && (strpos($file, 'lang_') === 0) && substr($file,-4) == '.php') {
			
			// extract the pure language name
			$file = str_replace('lang_', '', $file);
			$file = str_replace('.php', '', $file);
			
			// Check wether the language is the actual one of the user
			if($translation->OutputLanguage == $file)
				$selected = true;
			else
				$selected = false;
			
			// Add the found language to the lokal array
			$installedLanguages[] = array( 	'LANGUAGE_NAME' => $file,
											'LANGUAGE_TRANSLATION' => $translation->GetTranslation($file),
											'LANGUAGE_SELECTED' => $selected);
		}
	}
	$output->SetReplacement('LANGUAGES_LIST', $installedLanguages);
	$output->SetReplacement('PHP_SELF', $_SERVER['PHP_SELF']);
?>
