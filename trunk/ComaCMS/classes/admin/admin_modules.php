<?php
/**
 * @package ComaCMS
 * @subpackage AdminInterface
 * @copyright (C) 2005-2006 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : admin_system.php
 # created              : 2006-02-18
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
 	require_once('./classes/admin/admin.php');
 	
 	/**
	 * @package ComaCMS
	 */
 	class Admin_Modules extends Admin{
		

		/** GetPage
		 * 
		 * Returns the requestet page and if it isn't there the default page will be returned
		 * 
		 * @param string action
		 * @access public
		 */
		function GetPage($Action) {
			$out = '';
			switch ($Action) {
				case 'activate':
						$out .= $this->_ActivatePage(); 					
						break;
				case 'deactivate':
						$out .= $this->_DeactivatePage(); 					
						break;
				default:
						$out .= $this->_HomePage();
						break;
			}
			return $out;
		}
		
		/**
		 * Activates the page which is transmitted in $GET/POST['name']
		 * @access private
		 * @return srting
		 */
		function _ActivatePage() {
			$moduleName = GetPostOrGet('name');
			// is the module existent?
			if(file_exists("modules/$moduleName/{$moduleName}_info.php")) {
				// get the 'other' modules
				$modulesActivated = unserialize ($this->_Config->Get('modules_activated'));
				// no data was saved...
				if(!is_array($modulesActivated))
					// create the array to make arrayfunctions possible
					$modulesActivated = array();
				// is the module already activated?
				if(!in_array($moduleName, $modulesActivated)) {
					// 'activate' it!
					$modulesActivated[] = $moduleName;
					// Save these changes
					$this->_Config->Save('modules_activated', serialize($modulesActivated));
				}	
				// Go back to the default-view
				return $this->_HomePage();
			}
		}
		
		/**
		 * Dectivates the page which is transmitted in $GET/POST['name']
		 * @access private
		 * @return srting
		 */
		function _DeactivatePage() {
			$moduleName = GetPostOrGet('name');
			// is the module existent?
			if(file_exists("modules/$moduleName/{$moduleName}_info.php")) {
				// get the 'other' modules
				$modulesActivated = unserialize ($this->_Config->Get('modules_activated'));
				// no data was saved...
				if(is_array($modulesActivated)) {
					// is the module activated?
					if(in_array($moduleName, $modulesActivated)) {
						// 'deactivate' it!
						unset($modulesActivated[array_search($moduleName, $modulesActivated)]);
						// Save these changes
						$this->_Config->Save('modules_activated', serialize($modulesActivated));
					}
				}
				// Go back to the default-view
				return $this->_HomePage();
			}
		}
		
		/**
		 * Returns a table with all available modules
		 * @access private
		 * @return srting
		 */
		function _HomePage() {
			// load the name of all already activated modules
			$modulesActivated = unserialize ($this->_Config->Get('modules_activated'));
			// if the data was empty
			if(!is_array($modulesActivated))
				// create the array to prevent bugs caused by 'var-is-not-an-array'-exceptions
				$modulesActivated = array();


			$modules = array();
			$modulesFiles = dir(__ROOT__ . '/modules/');
			
			// Get all directories in the modules directory
			while($moduleDirectory = $modulesFiles->read()) {
				// Check if it is a directory and nothing else
				// Check also if could be a real module directory
				if($moduleDirectory != '.' && $moduleDirectory != '..' && file_exists("./modules/$moduleDirectory/{$moduleDirectory}_info.php")) {
					$module =  array();
					// load the info-file for the module
					include("./modules/$moduleDirectory/{$moduleDirectory}_info.php");
					
					// try to get the 'well-formed' name of the module	
					// if it isn't possible display the internal name of the module
					$moduleName =  (array_key_exists('name', $module)) ? $module['name'] : $moduleDirectory;
					
					// try to get the version-information
					// if there is no info, a 'unknown' will be displayed
					$moduleVersion =  (array_key_exists('version', $module)) ? 'v' . $module['version'] : $this->_Translation->GetTranslation('unknown');

					// the module isn't activated
					$moduleAction = 'activate';
					$moduleActivated = $this->_Translation->GetTranslation('not_activated');
					$actionImage = 'add';
					
					// if the module is already activated
					if(in_array($moduleDirectory, $modulesActivated)) {
						$moduleAction = 'deactivate';
						$moduleActivated = $this->_Translation->GetTranslation('activated');
						$actionImage = 'del';
					}
						
					$modules[] = array('MODULE_NAME' => $moduleName,
									'MODULE_VERSION' => $moduleVersion,
									'MODULE_ACTIVATED' => $moduleActivated,
									'MODULE_ACTION' => $moduleAction,
									'ACTION_IMAGE' => $actionImage,
									'MODULE_DIRECTORY' => $moduleDirectory,
									'MODULE_LANG_ACTION' => sprintf($this->_Translation->GetTranslation($moduleAction . '_module_%modulename%'), $moduleName));
				}
			}
			$modulesFiles->close();
			$this->_ComaLate->SetReplacement('MODULE_MANAGER_TITLE', $this->_Translation->GetTranslation('module_manager'));
			$this->_ComaLate->SetReplacement('MODULE_TITLE_NAME', $this->_Translation->GetTranslation('module_name'));
			$this->_ComaLate->SetReplacement('MODULE_TITLE_VERSION', $this->_Translation->GetTranslation('version'));
			$this->_ComaLate->SetReplacement('MODULE_TITLE_ACTIVATED', $this->_Translation->GetTranslation('activated'));
			$this->_ComaLate->SetReplacement('MODULE_TITLE_ACTIONS', $this->_Translation->GetTranslation('actions'));
						
			$this->_ComaLate->SetReplacement('MODULES', $modules);	
			
			$template = '<h2>{MODULE_MANAGER_TITLE}</h2>
					<table class="full_width">
						<tr>
							<th>{MODULE_TITLE_NAME}</th>
							<th>{MODULE_TITLE_VERSION}</th>
							<th>{MODULE_TITLE_ACTIVATED}</th>
							<th class="actions">{MODULE_TITLE_ACTIONS}</th>
						</tr>
						<MODULES:loop>
						<tr>
							<td>{MODULE_NAME}</td>
							<td>{MODULE_VERSION}</td>
							<td>{MODULE_ACTIVATED}</td>
							<td><a href="admin.php?page=modules&amp;action={MODULE_ACTION}&amp;name={MODULE_DIRECTORY}" title="{MODULE_LANG_ACTION}"><img alt="{MODULE_LANG_ACTION}" src="img/{ACTION_IMAGE}.png" /></a></td>
						</tr>
						</MODULES>
					</table>';
			return $template;
		}
 	}
?>