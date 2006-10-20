<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005-2006 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : special.php
 # created              : 2005-08-10
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
	define("COMACMS_RUN", true);
 
	include('common.php');
	
	/**
	 * @ignore
	 */
	include('./lang/' . $user->Language  . '/admin_lang.php');
	
	if(!isset($extern_page))
		header('Locaction: index.php');
	$text = '';
	$title = '';
	if($extern_page == 'login') {
		$error = GetPostOrGet('error');
		$text_error = '';
		if($error == '1')
			$text_error = 'Der Login wurde nicht angegeben.';
		if($error == '2')
			$text_error = 'Das Passwort wurde nicht angegeben.';
		if($error == '3')
			$text_error = 'Es wurden keine Eingaben gemacht.';
		if($error == '4')
			$text_error = 'Der Benutzer und(oder) das Passwort sind falsch.';
		if($text_error != '')
			$text_error = "\r\n<strong>" . $text_error . '</strong>';
		$title = "Login";
		$text = "<form method=\"post\" action=\"admin.php\">
			<input type=\"hidden\" name=\"page\" value=\"admincontrol\" />$text_error
			<fieldset>
				<legend>Login</legend>
				<div class=\"row\"><label>Loginname:</label><input type=\"text\" name=\"login_name\" /></div>
				<div class=\"row\"><label>Passwort:</label><input type=\"password\" name=\"login_password\" /></div>
				<div class=\"row\"><input type=\"submit\" value=\"Login\" class=\"button\"/></div>
			</fieldset>
		</form>";
	}
	elseif($extern_page == '404') {	
		$want = GetPostOrGet('want');
		$title = 'Seite nicht gefunden.';
		$text = "Die Seite mit dem Namen &quot;$want&quot; wurde leider nicht gefunden.<br />
			Falls die Seite aber da sein m&uuml;sste, melden sie sich bitte beim Seitenbetreiber.";
	}
	elseif($extern_page == '410') {	//Gone/Deleted
		$text = ' '; 
	}
	elseif($extern_page == 'image') {
		
		$imageID = GetPostOrGet('id');
		$imageFile = GetPostOrGet('file');
		if(is_numeric($imageID) || !empty($imageFile)) {
			$title = 'Bild';
			$condition = 'file_id = ' . $imageID;
			if(empty($imageID))
				$condition = "file_name = '$imageFile'";
			$sql = "SELECT *
				FROM " . DB_PREFIX . "files
				WHERE $condition
				LIMIT 0,1";
			$imageResult = $sqlConnection->SqlQuery($sql);
			if($imageData = mysql_fetch_object($imageResult)) {
				$text = "<img src=\"" . generateUrl($imageData->file_path) ."\" class=\"pureimage\"/>";
			}
		}
	}
	if($text == '') {
		header('Location: index.php');
		die();
	}
	$output->Title = $title;
	$output->SetReplacement('TEXT', $text);
	$output->SetReplacement('PATH', "<a href=\"special.php?page=$extern_page\">$title</a>");
	$output->SetCondition('notathome', true);
	$outputpage = new OutputPage($sqlConnection);
	
	$sql = "SELECT *
		FROM " . DB_PREFIX . "menu";
	$menus = $sqlConnection->SqlQuery($sql);
	while ($menu = mysql_fetch_object($menus)) {
		$output->SetReplacement('MENU_' . $menu->menu_name, $outputpage->GenerateMenu($menu->menu_id));
	}
	
	$modules = array();
	// get the activated modules
	$modulesActivated = unserialize ($config->Get('modules_activated'));
	// if no data is saved...
	if(!is_array($modulesActivated))
		// create the array to make arrayfunctions possible
		$modulesActivated = array();
	// find all module-calls in the text
	if(preg_match_all("/{(:([A-Za-z0-9_.-]+)(\?(.+?))?)}/s", $outputpage->Text, $moduleMatches)) {
		foreach($moduleMatches[2] as $key => $moduleName) {
			// if module is available and activated
			if(file_exists('./modules/' . $moduleName . '/' . $moduleName . '_module.php') && in_array($moduleName, $modulesActivated)) {
				// paste it to the list with all module-calls
				$modules[] = array('moduleName' => $moduleName, 'moduleParameter' => $moduleMatches[4][$key], 'identifer' => $moduleMatches[0][$key]);
			}
		}
	}
	// work through all module-calls
	foreach($modules as $module) {
		// get the directory-name of the module
		$moduleName = $module['moduleName'];
		// get the transmitted parameters
		$moduleParameter = $module['moduleParameter'];
		// load the module file
		/**
		 * @ignore
		 */
		include_once('./modules/' . $moduleName . '/' . $moduleName . '_module.php');
		// check if the module-class is already created
		if(!isset($$moduleName)) {
			// is the module-class available?
			if(class_exists('Module_' . $moduleName)) {
				// create a link to the initialisation-function for the module-class
				$newClass = create_function('&$SqlConnection, &$User, &$Lang, &$Config, &$ComaLate, &$ComaLib', 'return new Module_' . $moduleName . '(&$SqlConnection, &$User, &$Lang, &$Config, &$ComaLate, &$ComaLib);');
				// create the module-class
				$$moduleName = $newClass($sqlConnection, $user, $admin_lang, $config, $output, $lib);
			}
		}
		// check again if the module-class is available (it should be so)
		if(isset($$moduleName))
			// replace the module-call with the output of the module
			$outputpage->Text = str_replace($module['identifer'], $$moduleName->UseModule($module['identifer'], str_replace('&amp;', '&', $moduleParameter)), $outputpage->Text);
	}
	
	if(preg_match_all("/{(:([A-Za-z0-9_.-]+)(\?(.+?))?)}/s", $output->Template, $moduleMatches)) {
		foreach($moduleMatches[2] as $key => $moduleName) {
			// if module is available and activated
			if(file_exists('./modules/' . $moduleName . '/' . $moduleName . '_module.php') && in_array($moduleName, $modulesActivated)) {
				// paste it to the list with all module-calls
				$modules[] = array('moduleName' => $moduleName, 'moduleParameter' => $moduleMatches[4][$key], 'identifer' => $moduleMatches[0][$key]);
			}
		}
	}
	// work through all module-calls
	foreach($modules as $module) {
		// get the directory-name of the module
		$moduleName = $module['moduleName'];
		// get the transmitted parameters
		$moduleParameter = $module['moduleParameter'];
		// load the module file
		/**
		 * @ignore
		 */
		include_once('./modules/' . $moduleName . '/' . $moduleName . '_module.php');
		// check if the module-class is already created
		if(!isset($$moduleName)) {
			// is the module-class available?
			if(class_exists('Module_' . $moduleName)) {
				// create a link to the initialisation-function for the module-class
				$newClass = create_function('&$SqlConnection, &$User, &$Lang, &$Config, &$ComaLate, &$ComaLib', 'return new Module_' . $moduleName . '(&$SqlConnection, &$User, &$Lang, &$Config, &$ComaLate, &$ComaLib);');
				// create the module-class
				$$moduleName = $newClass($sqlConnection, $user, $admin_lang, $config, $output, $lib);
			}
		}
		// check again if the module-class is available (it should be so)
		if(isset($$moduleName))
			// replace the module-call with the output of the module
			$output->Template = str_replace($module['identifer'], $$moduleName->UseModule($module['identifer'], str_replace('&amp;', '&', $moduleParameter)), $output->Template);
	}
	$output->GenerateOutput();
	echo $output->GeneratedOutput;
	echo "\r\n<!-- rendered in " . round(getmicrotime(microtime()) - getmicrotime($starttime), 4) . ' seconds with ' . $sqlConnection->QueriesCount .' SQL queries -->';
?>
