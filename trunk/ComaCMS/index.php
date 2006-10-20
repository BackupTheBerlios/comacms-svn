<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : index.php
 # created              : 2005-07-11
 # copyright            : (C) 2005-2006 The ComaCMS-Team
 # email                : comacms@williblau.de
 #----------------------------------------------------------------------
 # This program is free software; you can redistribute it and/or modify
 # it under the terms of the GNU General Public License as published by
 # the Free Software Foundation; either version 2 of the License, or
 # (at your option) any later version.
 # 
 # This program is distributed in the hope that it will be useful,
 # but WITHOUT ANY WARRANTY; without even the implied warranty of
 # MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 # GNU General Public License for more details.
 # 
 # You should have received a copy of the GNU General Public License
 # along with this program; if not, write to the Free Software
 # Foundation, Inc., 59 Temple Place, Suite 330,
 # Boston, MA  02111-1307  USA
 #----------------------------------------------------------------------

	/**
	 * Set a global to make sure that common.php is executet only in the right context
	 */
	define("COMACMS_RUN", true);
	// run common.php to have all ordinary things done, which every page needs
	include('common.php');
	$outputpage = new OutputPage($sqlConnection);
	$extern_page = rawurlencode($extern_page);
	// load the page
	$outputpage->LoadPage($extern_page, $user);
	// get the language strings
	/**
	 * @ignore
	 */
	include('./lang/' . $user->Language . '/admin_lang.php');
	
	/*
	 * wtf are we doin here?
	 * this causes to much memory usage if we have some more menues...
	 */
	$sql = "SELECT *
		FROM " . DB_PREFIX . "menu";
	$menus = $sqlConnection->SqlQuery($sql);
	while ($menu = mysql_fetch_object($menus)) {
		$output->SetReplacement('MENU_' . $menu->menu_name, $outputpage->GenerateMenu($menu->menu_id));
	}
	
	$output->SetReplacement('PATH' , $outputpage->Position);
	// is the actual page the default page?
	if($config->Get('default_page', '1') != $outputpage->PageID)
		$output->SetCondition('notathome' , true);
	$output->Title = $outputpage->Title;
	$output->Language = $outputpage->Language;
	// is the user an admin
	if($user->IsAdmin) {
		// allow him to paste htmlcode into the page to make it possible to see a preview of the edited page
		// there won't be any changes saved
		$content = GetPostOrGet('content');
		if($content != '')
			$outputpage->Text = stripcslashes($content);
	}
	// try to load the inlinemenu
	$inlineMenu = InlineMenu::LoadInlineMenu($sqlConnection, $outputpage->PageID);
	// is a inlinemenu available?
	if(count($inlineMenu) > 0){
		$output->SetReplacement($inlineMenu);
		$output->SetCondition('inlinemenu', true);
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
	// prevent unnecesary runs
	$modules = array();
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
	
	/*
	if(strpos($outputpage->Text, '[dates]') !== false)
		$outputpage->Text = str_replace('[dates]', nextDates(10), $outputpage->Text);
	
	if (strpos ($page, "[gbook-")) {
		include("gbook.php");
		$page = str_replace("[gbook-input]", gbook_input(), $page);
		$page = str_replace("[gbook-pages]", gbook_pages(), $page);
		$page = str_replace("[gbook-content]", gbook_content(), $page);
	}
	
	if (strpos ($page, "[contact]")) {
		include("contact.php");
		$page = str_replace("[contact]", contact_formular(), $page);
	}
	*/

	// paste the text to the template-generator
	$output->SetReplacement('TEXT' , $outputpage->Text);
	$output->GenerateOutput();
	echo $output->GeneratedOutput;
	echo "\r\n<!-- rendered in " . round(getmicrotime(microtime()) - getmicrotime($starttime), 4) . ' seconds with ' . $sqlConnection->QueriesCount .' SQL queries -->';
?>