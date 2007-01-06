<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005-2007 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : special.php
 # created              : 2005-08-10
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
	define('COMACMS_RUN', true);
 
	include('common.php');
	$outputpage = new OutputPage($sqlConnection, $config, $translation, $output, $user);
	

	include(__ROOT__ . '/classes/registration.php');
	
	if(!isset($page))
		header('Locaction: index.php');
		
	$text = '';
	$title = '';
	$path = '';
	if($page == 'login') {
		$error = GetPostOrGet('error');
		$title = "Login";
		$redirection = GetPostOrGet('redirect');
		$action = GetPostOrGet('action');
		
		$text = "<form method=\"post\" action=\"admin.php\">
			<input type=\"hidden\" name=\"action\" value=\"$action\" />
			<input type=\"hidden\" name=\"page\" value=\"$redirection\" />
			<fieldset>
				<legend>Login</legend>
				<div class=\"row\">
					<label for=\"login_name\">
						<strong>" . $translation->GetTranslation('loginname') . ":</strong>" . (($error == '1') ? "\r\n\t\t\t\t\t\t<span class=\"error\">" . $translation->GetTranslation('the_login_was_not_typed_in') . "</span>\r\n\t\t\t\t\t\t" : '') . (($error == '3') ? "\r\n\t\t\t\t\t\t<span class=\"error\">" . $translation->GetTranslation('you_did_not_make_any_inputs') . "</span>\r\n\t\t\t\t\t\t" : '') . (($error == '4') ? "\r\n\t\t\t\t\t\t<span class=\"error\">" . $translation->GetTranslation('the_user_and(or)_the_password_are_wrong') . "</span>\r\n\t\t\t\t\t\t" : '') . (($error == '5') ? "\r\n\t\t\t\t\t\t<span class=\"error\">" . $translation->GetTranslation('the_user_is_not_activated') . "</span>\r\n\t\t\t\t\t\t" : '') . "
						<span class=\"info\">" . $translation->GetTranslation('this_is_your_nickname_not_your_showname') . "</span>
					</label>
					<input type=\"text\" name=\"login_name\" id=\"login_name\" />
				</div>
				<div class=\"row\">
					<label for=\"login_password\">
						<strong>" . $translation->GetTranslation('password') . "</strong>" . (($error == '2') ? "\r\n\t\t\t\t\t\t<span class=\"error\">" . $translation->GetTranslation('the_password_was_not_typed_in')."</span>\r\n\t\t\t\t\t\t" : '') . "
						<span class=\"info\">" . $translation->GetTranslation('this_is_your_loginpassword') . "</span>
					</label>
					<input type=\"password\" name=\"login_password\" id=\"login_password\" />
				</div>
				<div class=\"row\">
					<input type=\"submit\" value=\"" . $translation->GetTranslation('login') . "\" class=\"button\"/>
				</div>
			</fieldset>
		</form>";
	}
	elseif($page == 'register') {
		$Registration = new Registration($sqlConnection, $translation, $config);
		$title = 'Registration';
		$text = $Registration->GetPage(GetPostOrGet('action'));
	}
	elseif($page == '404') {	
		$want = GetPostOrGet('want');
		$title = 'Seite nicht gefunden.';
		$text = "Die Seite mit dem Namen &quot;$want&quot; wurde leider nicht gefunden.<br />
			Falls die Seite aber da sein m&uuml;sste, melden sie sich bitte beim Seitenbetreiber.";
	}
	elseif($page == '410') {	//Gone/Deleted
		$title = 'Seite gel&ouml;scht';
		$text = 'Die Seite wurde leider gel&ouml;scht. <br />
			Falls die Seite dennoch da sein m&uuml;sste, melden sie sich bitte beim Seitenbetreiber.'; 
	}
	elseif($page == 'image') {
		
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
	elseif($page == 'module') {
		// Get the name of Module to show
		$moduleName = GetPostOrGet('moduleName');
		if(file_exists('./modules/' . $moduleName . '/' . $moduleName . '_module.php'))
			/**
			 * @ignore
			 */
			include_once('./modules/' . $moduleName . '/' . $moduleName . '_module.php');
		// If the menu is activated it's class should be created
		// check if the module-class is already created
		if(!isset($$moduleName)) {
			// is the module-class available?
			if(class_exists('Module_' . $moduleName)) {
				// create a link to the initialisation-function for the module-class
				$newClass = create_function('&$SqlConnection, &$User, &$Translation, &$Config, &$ComaLate, &$ComaLib', 'return new Module_' . $moduleName . '(&$SqlConnection, &$User, &$Translation, &$Config, &$ComaLate, &$ComaLib);');
				// create the module-class
				$$moduleName = $newClass($sqlConnection, $user, $translation, $config, $output, $lib);
			}
		}
		// check again if the module-class is available (it should be so)
		if (isset($$moduleName)) {
			// Get Text of the module
			$text = $$moduleName->GetPage(GetPostOrGet('action'));
			$title = $$moduleName->GetTitle();
			$path = "<a href=\"special.php?page={$page}&amp;moduleName={$moduleName}\">{$title}</a>";
		}
	}
	if($text == '') {
		header('Location: index.php');
		die();
	}
	
	if($path == '')
		$path = "<a href=\"special.php?page=$page\">$title</a>";
	
	$output->Title = $config->Get('pagename') . ' - ' . $title;
	$output->SetReplacement('TEXT', $text);
	$output->SetReplacement('PATH', $path);
	$output->SetCondition('notathome', true);
	
	$sql = "SELECT menu_name, menu_id
		FROM " . DB_PREFIX . "menu";
	$menus = $sqlConnection->SqlQuery($sql);
	while ($menu = mysql_fetch_object($menus)) {
		if($output->ReplacementExists('MENU_' . $menu->menu_name, true))
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
				$newClass = create_function('&$SqlConnection, &$User, &$Translation, &$Config, &$ComaLate, &$ComaLib', 'return new Module_' . $moduleName . '(&$SqlConnection, &$User, &$Translation, &$Config, &$ComaLate, &$ComaLib);');
				// create the module-class
				$$moduleName = $newClass($sqlConnection, $user, $translation, $config, $output, $lib);
			}
		}
		// check again if the module-class is available (it should be so)
		if(isset($$moduleName))
			// replace the module-call with the output of the module
			$outputpage->Text = str_replace($module['identifer'], $$moduleName->UseModule($module['identifer'], str_replace('&amp;', '&', $moduleParameter)), $outputpage->Text);
	}
	// prevent unnecesary runs
	$modules = array();
	// search for Modulematches in template
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
				$newClass = create_function('&$SqlConnection, &$User, &$Translation, &$Config, &$ComaLate, &$ComaLib', 'return new Module_' . $moduleName . '(&$SqlConnection, &$User, &$Translation, &$Config, &$ComaLate, &$ComaLib);');
				// create the module-class
				$$moduleName = $newClass($sqlConnection, $user, $translation, $config, $output, $lib);
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
