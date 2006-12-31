<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : install.php
 # created              : 2005-06-1
 # copyright            : (C) 2005-2006 The ComaCMS-Team
 # email                : comacms@williblau.de
 #----------------------------------------------------------------------
 # This program is free software; you can redistribute it and/or modify
 # it under the terms of the GNU General Public License as published by
 # the Free Software Foundation; either version 2 of the License, or
 # (at your option) any later version.
 #----------------------------------------------------------------------
	
	error_reporting(E_ALL);
	
	define('__ROOT__', dirname(__FILE__) . '/..');
	
	/**
	 * @ignore
	 */
	require_once __ROOT__ . '/classes/language.php';
	require_once __ROOT__ . '/classes/comalib.php';
	require_once __ROOT__ . '/classes/sql.php';
	require_once __ROOT__ . '/functions.php';
	require_once __ROOT__ . '/lib/comalate/comalate.class.php';
	require_once __ROOT__ . '/install/install.class.php';
	
	// ComaLib
	$lib = new ComaLib();
	
	// SqlConnection to prevent errors
	$sqlConnection = new Sql('', '', '');
	
	// Get style for the page
	$extern_style = GetPostOrGet('style');
	if (empty($extern_style))
		$extern_style = 'comacms';
	
	// ComaLate for output replacements and template support
	$output = new ComaLate();
	$output->LoadTemplate('../styles/', $extern_style);
	$output->AddCssFile('./install.css');
	$output->SetMeta('generator', 'ComaCMS v0.2 (http://comacms.berlios.de)');
	$output->SetCondition('notinadmin', true);
	$output->SetCondition('notathome', true);
	$output->Title = 'ComaCMS - Installation';
	
	// Get the actual subpage of the installation
	$extern_page = 1;
	$extern_page = GetPostOrGet('page');
	// Check external page
	if(!is_numeric($extern_page)) {
		$extern_page = 1;
	}
	
	// Local language variable
	$language = '';
	// try to get the language-setting from the language-cookie
	if(isset($_COOKIE['ComaCMS_user_lang'])) {
		// check if the language-file is available
		if(file_exists("../lang/lang_{$_COOKIE['ComaCMS_user_lang']}.php"))
			$language = $_COOKIE['ComaCMS_user_lang'];
	}
	if($language == '') {
		// there was no (valid) language-setting in the cookie try to get the default language of the browser/user
		if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
			$langs = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
			// remove all unneeded things in the information
			$langs = preg_replace("#\;q=[0-9\.]+#i" , '' , $langs);
			$langs = explode(',' , $langs);
			//$language = $languages[0];
			// finde a matching language
			foreach($langs as $lang) {
				if(file_exists("../lang/lang_{$lang}.php")) {
					$language = $lang;
					break;
				}
			}
		}
	}
	// tries someone to set the language 'by hand'?
	$external_lang = GetPostOrGet('lang');
	if($external_lang != '')
		if(file_exists("../lang/lang_{$external_lang}.php"))
			$language = $external_lang;
	// if there is no language use english ;-)
	if($language == '')
		$language == 'en';
	// Set the cookie (for the next 93(= 3x31) days)
	setcookie('ComaCMS_user_lang', $language, time() + 8035200);
	
	// Set the translation class
	$translation = new Language($language);
	$translation->AddSources('../lang/');
	$output->Language = $language;
	
	// Initialize the install class
	$installation = new Installation($translation, $output);
	
	// Define menuentries
	$menuArray = array();
	$menuArray[] = array($translation->GetTranslation('language'), '1');
	$menuArray[] = array($translation->GetTranslation('requirements'), '2');
	$menuArray[] = array($translation->GetTranslation('license'), '3');
	$menuArray[] = array($translation->GetTranslation('database_settings'), '4');
	$menuArray[] = array($translation->GetTranslation('create_administrator'), '5');
	
	// Generate menu and replace it in template
	$menu = array();
	foreach($menuArray as $part) {
		if($extern_page == $part[1])
			$linkStyle = ' class="actual"';
		else
			$linkStyle = '';
		$menu[] = array('LINK_TEXT' => $part[0], 'LINK' => 'install.php?page=' . $part[1], 'CSS_ID' => '', 'LINK_STYLE' => $linkStyle);
	}
	$output->SetReplacement('MENU_DEFAULT' , $menu);
	
	// Generate and replace PATH
	switch ($extern_page) {
		case 1:
			$pagename = $translation->GetTranslation('language');
			break;
	
		case 2:
			$pagename = $translation->GetTranslation('requirements');
			break;
			
		case 3:
			$pagename = $translation->GetTranslation('license');
			break;
		
		case 4:
			$pagename = $translation->GetTranslation('database_settings');
			break;
		
		case 5:
			$pagename = $translation->GetTranslation('create_administrator');
		
		default:
			$pagename = '';
			break;
	}
	$output->SetReplacement('PATH', "<a href=\"install.php\">" . $translation->GetTranslation('installation') . "</a> -> <a href=\"install.php?page={$extern_page}\">{$pagename}</a>");
	
	$output->SetReplacement('TEXT' , $installation->GetPage($extern_page, $language));
	$output->GenerateOutput();
	echo $output->GeneratedOutput;