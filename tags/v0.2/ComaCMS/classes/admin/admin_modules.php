<?php
/**
 * @package ComaCMS
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
		
		/**
		 * Initializes this class with links to the needed 'global' variables
		 * @access public
		 * @param SqlConnection SqlConnection
		 * @param array AdminLang
		 * @param Config Config
		 * @return Admin_modules
		 */
		function Admin_Modules(&$SqlConnection, &$AdminLang, &$Config) {
			$this->_SqlConnection = &$SqlConnection;
			$this->_AdminLang = &$AdminLang;
			$this->_Config = &$Config;
		}
		
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
			// same procedure again...
			$modulesAutorun = unserialize ($this->_Config->Get('modules_autorun'));
			if(!is_array($modulesAutorun))
				$modulesAutorun = array();
			
			$out = "<h2>{$this->_AdminLang['manage_modules']}</h2>
				<table class=\"text_table full_width\">
				<thead>
					<tr>
						<th>Pluginname</th>
						<th>Version</th>
						<th>Aktiviert</th>
						<th>Autostart</th>
						<th class=\"actions\">Aktionen</th>
					</tr>
				</thead>
				<tbody>";
			$modulesFiles = dir("./modules/");
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
					// try to get the versioninformation
					// if there is no info, a 'unknown' will be displayed
					$moduleVersion =  (array_key_exists('version', $module)) ? 'v' . $module['version'] : $this->_AdminLang['unknown'];
					// is the module activated
					$moduleActivated = (in_array($moduleDirectory, $modulesActivated)) ? $this->_AdminLang['yes'] : $this->_AdminLang['no'];
					// is the module registered as a 'autorun-module'
					$moduleAutorun = (in_array($moduleDirectory, $modulesAutorun)) ? $this->_AdminLang['yes'] : $this->_AdminLang['no'];
					// print the row for the actual module
					$out .= "<tr>
						<td>$moduleName</td>
						<td>$moduleVersion</td>
						<td>" .
						// Show the activate or the deactivate function for the module
						((!in_array($moduleDirectory, $modulesActivated)) ?
						 "<a href=\"admin.php?page=modules&amp;action=activate&amp;name=$moduleDirectory\" title=\"" . sprintf($this->_AdminLang['activate_module_%modulename%'], $moduleName) . "\"><img alt=\"{$this->_AdminLang['activate']}\" src=\"img/add.png\"/></a>" :
						 "<a href=\"admin.php?page=modules&amp;action=deactivate&amp;name=$moduleDirectory\" title=\"" . sprintf($this->_AdminLang['deactivate_module_%modulename%'], $moduleName) . "\"><img alt=\"{$this->_AdminLang['deactivate']}\" src=\"img/del.png\"/></a>") .
						" $moduleActivated</td>
						<td>$moduleAutorun</td>
						<td>						
						</td>
					</tr>\r\n";
				}
			}
			$modulesFiles->close();	
			$out .= "</tbody>	
			</table>
			";

			return $out;
		}
 	}
?>