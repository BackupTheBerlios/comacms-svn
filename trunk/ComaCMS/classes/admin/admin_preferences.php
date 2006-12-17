<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005-2006 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : admin_preferences.php
 # created              : 2006-01-29
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
 	require_once __ROOT__ . '/classes/admin/admin.php';
 	require_once __ROOT__ . '/classes/preferences.php';
 	require_once __ROOT__ . '/classes/pagestructure.php';
 	
	/**
	 * @package ComaCMS 
	 */
 	class Admin_Preferences extends Admin{
 		
 		/**
 		 * @access public
 		 * @return void
 		 */
 		function _Init() {
 			$this->_Preferences = new Preferences(&$this->_Translation);
 		}
 		
 		/**
 		 * @return string
 		 * @param Action string
 		 */
 		function GetPage($Action) {
 			$out = '';
 			$Action = strtolower($Action);
 			switch ($Action) {
				case 'save':	$out .= $this->_SavePage();
						break;
				default:	$out .= $this->_HomePage();
			}
			return $out;
 		}
 		
 		/**
 		 * @return string
 		 * @access private
 		 */
 		function _ShowPreferences() {
 			if(count($this->_Preferences->Settings) <= 0)
 				return '';
 			$out = "<form action=\"admin.php\" method=\"post\">
 				<input type=\"hidden\" name=\"page\" value=\"preferences\" />
				<input type=\"hidden\" name=\"action\" value=\"save\" />";
 			// Go through each preferences group
 			foreach($this->_Preferences->Settings as $settingsGroup => $settings) {
 				$out .= "<fieldset>
 					<legend>$settingsGroup</legend>";
 				// Display all pereferneces of the actual group
 				foreach($settings as $setting) {
 					// Load the current config and if it isn't available use the default
 					$setting['default'] = $this->_Config->Get($setting['name'], $setting['default']);
 					$out .= "<div class=\"row\">
 							<label class=\"row\" for=\"setting_{$setting['name']}\"><strong>{$setting['display']}:</strong>
 								<span class=\"info\">" . $setting['description'] . "</span></label>";
 						// Make it possible to define simpla options lists
 						if(substr($setting['datatype'], 0, 6) == 'array(') {
 							$setting['data'] = explode(',', substr($setting['datatype'], 6, -1));
 							$setting['datatype'] = 'array';
 						}
 						switch ($setting['datatype']) {
 							// 'simple options list'
							case 'array':		$out .= "<select id=\"setting_" . $setting['name'] . "\" name=\"setting_" . $setting['name'] . "\">";
										foreach($setting['data'] as $option) {
											$out .= "<option value=\"$option\"" . (($option == $setting['default']) ? ' selected="selected"' : '') . ">$option</option>";
										}
										$out .= '</select>';
										break;
							// The pages-tree
							case 'page_select':	$pageStructure = new Pagestructure($this->_SqlConnection, null);
										$pageStructure->LoadParentIDs();
										$out .= "<select id=\"setting_" . $setting['name'] . "\" name=\"setting_" . $setting['name'] . "\">" . $pageStructure->PageStructurePulldown(0, 0, '',  -1, $setting['default']) . '</select>';
										break;
							// 'bool'-options-list
							case 'bool':		$out .= "<select id=\"setting_" . $setting['name'] . "\" name=\"setting_" . $setting['name'] . "\">
												<option value=\"1\"" . (($setting['default'] == 1 ) ? ' selected="selected"': '') . ">" . $this->_Translation->GetTranslation('yes') . "</option>
												<option value=\"0\"" . (($setting['default'] == 0 ) ? ' selected="selected"': '') . ">" . $this->_Translation->GetTranslation('no') . "</option>
											</select>";
										break;
							// Every thing else
							default: 		$out .= "<input id=\"setting_" . $setting['name'] . "\" name=\"setting_" . $setting['name'] . "\" type=\"text\" value=\"" . $setting['default'] . "\"/>";
										break;
						}
 					$out .= "</div>";
 				}
 				$out .= "<div class=\"row\"><input type=\"submit\" class=\"button\" value=\"" . $this->_Translation->GetTranslation('save') . "\"/>
 				</div>";
 				
 				$out .= "</fieldset>";
 			}
 			$out .= "</form>";
 			return $out;
 		}
 		
 		/**
 		 * @return string
 		 * @access private
 		 */
 		function _HomePage() {
 			$out = '<h2>' . $this->_Translation->GetTranslation('preferences') . '</h2>';
 			// Load the main-preferences file
 			$this->_Preferences->Load('system/settings.php');
 			
 			// Load the preferences files of the modules (if there are some)
			
			// get the activated modules
			$modulesActivated = unserialize ($this->_Config->Get('modules_activated'));
			// some data aviailable?
			if(is_array($modulesActivated)) {
				if(count($modulesActivated) >= 0) {
					foreach($modulesActivated as $moduleName) {
						$settingsFile = "modules/$moduleName/{$moduleName}_settings.php";
						if(file_exists($settingsFile))
							// Load the config file of this module
							$this->_Preferences->Load($settingsFile);
					}
				}
			}
 			
 			
 			// Show all Preferences
 			$out .= $this->_ShowPreferences();
 			return $out;
 		}
 		
 		/**
 		 * @access private
 		 * @return string
 		 */
 		function _SavePage() {

			// Load the main-preferences file
			$this->_Preferences->Load('system/settings.php');
			// Load the preferences files of the modules (if there are some)
			
			// get the activated modules
			$modulesActivated = unserialize ($this->_Config->Get('modules_activated'));
			// some data aviailable?
			if(is_array($modulesActivated)) {
				if(count($modulesActivated) >= 0) {
					foreach($modulesActivated as $moduleName) {
						$settingsFile = "modules/$moduleName/{$moduleName}_settings.php";
						if(file_exists($settingsFile))
							// Load the config file of this module
							$this->_Preferences->Load($settingsFile);
					}
				}
			}
			 
			if(count($this->_Preferences->Settings) <= 0)
 				return $this->GetPage('');
 			// Go through all preferences entries
			foreach($this->_Preferences->Settings as $settings) {
 				foreach($settings as $setting) {
 					$settingValue = GetPostOrGet('setting_' . $setting['name']);
 					//TODO : value-type-check!!
 					if(!empty($settingValue) || (is_numeric($settingValue) && $settingValue == 0) || $setting['datatype'] == 'string0') {
 						$currentValue = $this->_Config->Get($setting['name']);
 						// Check if something has changed
 						if($currentValue != $settingValue) {
 							// TODO: check the data before saving
 							$this->_Config->Save($setting['name'], $settingValue);
 						}
 					}
 				}
			}
			// Show the 'main-view'
 			return $this->GetPage('');
 		}
	}
?>