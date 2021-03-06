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
	
	// Initialize outputpage
	$outputpage = new OutputPage($sqlConnection, $config, $translation, $output, $user);
	
	if(!isset($page))
		header('Locaction: index.php');
	
	// Initialize Variables
	$text = '';
	$title = '';
	$path = '';
	
	// Switch between the subpages
	switch ($page) {
		case 'login':
			$error = GetPostOrGet('error');
			$title = "Login";
			$externRedirection = GetPostOrGet('extern_redirect');
			if (empty($externRedirection))
				$externRedirection = 'admin.php';
			$redirection = GetPostOrGet('redirect');
			$action = GetPostOrGet('action');
			
			$text = "<form method=\"post\" action=\"{$externRedirection}\">
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
			break;
		
		case 'register':
			include_once(__ROOT__ . '/classes/registration.php');
			$Registration = new Registration($sqlConnection, $translation, $config);
			$title = $translation->GetTranslation('registration');
			$text = $Registration->GetPage(GetPostOrGet('action'));
			break;
		
		case '404':
			$want = GetPostOrGet('want');
			$title = 'Seite nicht gefunden.';
			$text = "Die Seite mit dem Namen &quot;$want&quot; wurde leider nicht gefunden.<br />
				Falls die Seite aber da sein m&uuml;sste, melden sie sich bitte beim Seitenbetreiber.";
			break;
		case 'd404':
			$title = 'Fehlerhafter Download.';
			$text = "Der Download dieser Datei ist nicht zul&auml;ssig.<br />
				Falls der Download aber da sein m&uuml;sste, melden sie sich bitte beim Seitenbetreiber.";
			break;	
		case '401':
			$title = 'Zugriff verweigert!';
			$text = 'Sie haben keine Zugriffsrechte zu dieser Seite!';
			break;
		
		case '410':
			$title = 'Seite gel&ouml;scht';
			$text = 'Die Seite wurde leider gel&ouml;scht. <br />
				Falls die Seite dennoch da sein m&uuml;sste, melden sie sich bitte beim Seitenbetreiber.';
			break;
		
		case 'image':
			
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
					LIMIT 1";
				$imageResult = $sqlConnection->SqlQuery($sql);
				if($imageData = mysql_fetch_object($imageResult)) {
					$text = "<img src=\"" . generateUrl($imageData->file_path) ."\" class=\"pureimage\"/>";
				}
			}
			break;
		
		case 'module':
			// Get the name of Module to show
			$moduleName = GetPostOrGet('moduleName');
			if(file_exists('./modules/' . $moduleName . '/' . $moduleName . '_module.php'))
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
			break;
		
		case 'memberlist':
			if ($config->Get('public_memberlist', '1') == 1 || $user->IsLoggedIn) {
				include_once(__ROOT__ . '/classes/user/user_memberlist.php');
				$memberlist = new User_Memberlist($sqlConnection, $translation, $config, $user, $lib, $output);
				$text = $memberlist->GetPage(GetPostOrGet('action'));
			}
			else {
				$text = $translation->GetTranslation('anonymus_users_may_not_access_to_the_memberlist');
			}
			$title = $translation->GetTranslation('memberlist');
			break;
		
		case 'userinterface':
			
			if (!$user->IsLoggedIn) {
				header("Location: special.php?page=login&extern_redirect=special.php&redirect=userinterface");
				die();
			}
			
			// Generate UI menu
			$menuArray = array();
			$menuArray[] = array($translation->GetTranslation('usercontrol'), 'usercontrol');
			$menuArray[] = array($translation->GetTranslation('memberlist'), 'memberlist');
			$menuArray[] = array($translation->GetTranslation('back_to_homepage'), 'url:index.php');
			if($user->IsAdmin)
				$menuArray[] = array($translation->GetTranslation('administration'), 'url:admin.php');	
			$menuArray[] = array($translation->GetTranslation('logout'), 'logout');
			
			// Switch between the subpages of the userinterface
			$subpage = GetPostOrGet('subpage');
			$action = GetPostOrGet('action');
			switch ($subpage) {
				
				case 'logout':
					// call the logout and redirect to the index 
					$user->Logout();
					header("Location: index.php");
					die();
				
				case 'memberlist':
					include_once(__ROOT__ . '/classes/user/user_memberlist.php');
					$memberlist = new User_Memberlist($sqlConnection, $translation, $config, $user, $lib, $output);
					$title = $translation->GetTranslation('memberlist');
					$text = $memberlist->GetPage(GetPostOrGet('action'), 'userinterface');
					break;
				
				case 'userinterface':
				default:
					if(substr($page, 0, 7) == 'module_') {
						// get the name of the module which's admin-interface should be shown
						$moduleName = substr($page, 7);
						
						$access = $config->Get($moduleName. '_author_access');
						if(!is_bool($access)) {
							if (file_exists(__ROOT__ . "/modules/{$moduleName}/{$moduleName}_info.php")) {
								
								$module = array();
								include (__ROOT__ . "/modules/{$moduleName}/{$moduleName}_info.php");
								if (array_key_exists('author_access', $module))
									$access = $module['author_access'];
								else
									$access = false;
							}
							else 
								$access = false;
						}
							
						if ($access) {
							// is the module really activated? (yes, I'm paranoid... :-P )
							$modulesActivated = unserialize($config->Get('modules_activated'));
							if(in_array($moduleName, $modulesActivated)) {
								// Load the admin-class of the module
								include_once(__ROOT__ . "/modules/$moduleName/{$moduleName}_admin.php");
								if(class_exists('Admin_Module_' . $moduleName)) {
									// create a link to the initialisation-function for the module-class
									$newClass = create_function('&$SqlConnection, &$Translation, &$Config, &$User, &$ComaLib, &$ComaLata', 'return new Admin_Module_' . $moduleName . '(&$SqlConnection, &$Translation, &$Config, &$User, &$ComaLib, &$ComaLata);');
									// create the module-class
									$moduleAdminInterface = $newClass($sqlConnection, $translation, $config, $user, $lib, $output);
									if(isset($moduleAdminInterface)) {
										$text = $moduleAdminInterface->GetPage($action);
										$title = $moduleAdminInterface->GetTitle();
									}
								} 
							}
						}
						else {
							header('Location: special.php?page=401');
							die();
						}
					}
					else {		
						// Load the admincontrol-class (system-overview)
						include_once(__ROOT__ . '/classes/user/user_usercontrol.php');
						
						$title = $translation->GetTranslation('usercontrol');
						$adminClass = new User_UserControl($sqlConnection, $translation, $config, $user, $lib, $output);
						$text = $adminClass->GetPage($action);
					}
					break;
			}
			break;
		
		default:
			header('Location: index.php');
			die();
	}
	
	// Set the path if it is not done now
	if($path == '')
		$path = "<a href=\"special.php?page=$page\">$title</a>";
	
	// Set replacements and variables for ComaLate
	$output->Title = $config->Get('pagename', 'ComaCMS') . ' - ' . $title;
	$output->SetReplacement('TEXT', $text);
	$output->SetReplacement('PATH', $path);
	$output->SetCondition('notathome', true);
	
	//  Get and generate the menus for the page
	$sql = "SELECT menu_name, menu_id
		FROM " . DB_PREFIX . "menu";
	$menus = $sqlConnection->SqlQuery($sql);
	
	while ($menu = mysql_fetch_object($menus)) {
		
		if (empty($menuArray)) { 
			if($output->ReplacementExists('MENU_' . $menu->menu_name, true))
				$output->SetReplacement('MENU_' . $menu->menu_name, $outputpage->GenerateMenu($menu->menu_id));
		}
		else {
			if ($menu->menu_name != 'DEFAULT') {
				if($output->ReplacementExists('MENU_' . $menu->menu_name, true))
					$output->SetReplacement('MENU_' . $menu->menu_name, $outputpage->GenerateMenu($menu->menu_id));
			}
			else {
				$menu = array();
	
				foreach($menuArray as $part) {
					if($page == $part[1] || $subpage == $part[1])
						$linkStyle = ' class="actual"';
					else
						$linkStyle = '';
					if(strpos($part[1],'url:') === 0)
						$menu[] = array('LINK_TEXT' => $part[0], 'LINK' => substr($part[1], 4), 'CSS_ID' => '', 'LINK_STYLE' => $linkStyle);
					else
						$menu[] = array('LINK_TEXT' => $part[0], 'LINK' => 'special.php?page=userinterface&amp;subpage=' . $part[1], 'CSS_ID' => '', 'LINK_STYLE' => $linkStyle);
				}
				$output->SetReplacement('MENU_DEFAULT', $menu);
			}
		}
	}
	
	// Work throug all modules in the text of the page
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
	
	// Work throug all modulecalls in the template
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
	
	// Generate Output and return it to the waiting client
	$output->GenerateOutput();
	echo $output->GeneratedOutput;
	echo "\r\n<!-- rendered in " . round(getmicrotime(microtime()) - getmicrotime($starttime), 4) . ' seconds with ' . $sqlConnection->QueriesCount .' SQL queries -->';
?>
