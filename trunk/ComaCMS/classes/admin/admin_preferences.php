<?php
/**
 * @package ComaCMS
 * @subpackage AdminInterface
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
 	require_once __ROOT__ . '/lib/formmaker/formmaker.class.php';
 	
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
 			
 			// If there are no settings load return
 			if(count($this->_Preferences->Settings) <= 0)
 				return '';
 			
 			// Initialize the formMaker class
 			$formMaker = new FormMaker($this->_Translation->GetTranslation('todo'), $this->_SqlConnection);
 			
 			// Add necessary inputs
 			$formMaker->AddForm('settings');
 			$formMaker->AddHiddenInput('settings', 'page', 'preferences');
 			$formMaker->AddHiddenInput('settings', 'action', 'save');
			
 			// Go through each preferences group
 			foreach($this->_Preferences->Settings as $settingsGroup => $settings) {
				
 				// Add a new form to the formmaker
 				$formMaker->AddForm("settings_group_{$settingsGroup}", '', $this->_Translation->GetTranslation('save'), $settingsGroup);
 				
 				// Display all pereferences of the actual group
 				foreach($settings as $setting) {
 					
 					// Load the current config and if it isn't available use the default
 					$setting['value'] = $this->_Config->Get($setting['name'], $setting['default']);
 					
 					// Make it possible to define simple options lists
 					if(substr($setting['datatype'], 0, 6) == 'array(') {
 						$setting['data'] = explode(',', substr($setting['datatype'], 6, -1));
 						$setting['datatype'] = 'array';
 					}
 					switch ($setting['datatype']) {

 						// 'simple options list'
						case 'array':
							$formMaker->AddInput("settings_group_{$settingsGroup}", "setting_{$setting['name']}", 'select', $setting['display'], $setting['description']);
							
							// Add the possible values
							foreach($setting['data'] as $option) {
								
								$formMaker->AddSelectEntry("settings_group_{$settingsGroup}", "setting_{$setting['name']}", (($option == $setting['value']) ? true : false), $option, $option);
							}
							break;

						// The pages-tree
						case 'page_select':	
							$pageStructure = new Pagestructure($this->_SqlConnection, $this->_User, $this->_ComaLib);
							$pageStructure->LoadParentIDs();
							
							$formMaker->AddInput("settings_group_{$settingsGroup}", "setting_{$setting['name']}", 'select', $setting['display'], $setting['description']);
							$formMaker->AddSelectEntrysCode("settings_group_{$settingsGroup}", "setting_{$setting['name']}", $pageStructure->PageStructurePulldown(0, 0, '',  -1, $setting['default']));
							break;

						// 'bool'-options-list
						case 'bool':
							$formMaker->AddInput("settings_group_{$settingsGroup}", "setting_{$setting['name']}", 'select', $setting['display'], $setting['description']);
							$formMaker->AddSelectEntry("settings_group_{$settingsGroup}", "setting_{$setting['name']}", (($setting['value'] == 1 ) ? true : false), '1', $this->_Translation->GetTranslation('yes'));
							$formMaker->AddSelectEntry("settings_group_{$settingsGroup}", "setting_{$setting['name']}", (($setting['value'] == 0 ) ? true : false), '0', $this->_Translation->GetTranslation('no'));
							break;
						// Every thing else
						default: 		
							$formMaker->AddInput("settings_group_{$settingsGroup}", "setting_{$setting['name']}", 'text', $setting['display'], $setting['description'], $setting['value']);
							break;
					}
 				}
 				
 			}
 			
 			$formMaker->SetComaLateReplacement(&$this->_ComaLate, false);
 			
 			// Generate the template
 			$template = "\r\n\t\t\t\t<form action=\"admin.php\" method=\"post\"><FORM_MAKER:loop>
					{fieldset_start}
						<hidden_inputs:loop><input type=\"hidden\" name=\"{name}\" value=\"{value}\" />\r\n\t\t\t\t\t\t</hidden_inputs>
						{fieldset_legend}
						<inputs:loop>
							<div class=\"row\">
								<label for=\"{name}\">
									<strong>{translation}:</strong>
									<errorinformation:loop><span class=\"error\">{errortext}</span>
									</errorinformation>
									<span class=\"info\">{information}</span>
								</label>
								{start_input} name=\"{name}\" id=\"{name}\" {end_input}
							</div>
						</inputs>
						{submit_button}
					{fieldset_end}
				</FORM_MAKER>
				</form>"; 
				
 			return $template;
 		}
 		
 		/**
 		 * @return string
 		 * @access private
 		 */
 		function _HomePage() {
 			$out = '<h2>' . $this->_Translation->GetTranslation('preferences') . '</h2>';
 			// Load the main-preferences file
 			$this->_Preferences->Load(__ROOT__ . '/system/settings.php');
 			
 			// Load the preferences files of the modules (if there are some)
			
			// get the activated modules
			$modulesActivated = unserialize ($this->_Config->Get('modules_activated'));
			// some data aviailable?
			if(is_array($modulesActivated)) {
				
				if(count($modulesActivated) >= 0) {
					
					foreach($modulesActivated as $moduleName) {
						
						$settingsFile = __ROOT__ . "/modules/{$moduleName}/{$moduleName}_settings.php";
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