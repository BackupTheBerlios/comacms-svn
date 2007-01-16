<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005-2007 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : settings.php
 # created              : 2006-01-29
 # copyright            : (C) 2005-2007 The ComaCMS-Team
 # email                : comacms@williblau.de
 #----------------------------------------------------------------------
 # This program is free software; you can redistribute it and/or modify
 # it under the terms of the GNU General Public License as published by
 # the Free Software Foundation; either version 2 of the License, or
 # (at your option) any later version.
 #----------------------------------------------------------------------
 
 	/**
 	 * @package ComaCMS
 	 */
	class Preferences {
		
		/**
		 * Contains all Settings setted by any loaded settings file
		 * @access public
		 * @staticvar Settings Contains all settings loaded by the preferences system
		 */
		var $Settings;
		
		/**
		 * This is the link to the Translation class for the Preferences class
		 * @access private
		 * @var Translation A link to the translation class
		 */
		var $_Translation;
		
		/**
		 * @access public
		 * @param Translation Translation A link to the translation class
		 * @return void
		 */
		function Preferences(&$Translation) {
			$this->_Translation = &$Translation;
			static $staticSettings;
			$this->Settings = &$staticSettings;
		}
		
		/**
		 * Loads all settings from the $SettingsFile to the local array
		 * @access public
		 * @param string $SettingsFile A link to a settingsfile that should be loaded
		 * @return void Load settings file
		 */
		function Load($SettingsFile) {
			$translation = &$this->_Translation;
			include($SettingsFile);
		}
		
		/** SetSetting
		 * Adds a property to the preferences-page
		 * 
		 * @access public
		 * @param string $Name The name of the property
		 * @param string $Display The displayed title of the property
		 * @param string $Description The description of the property
		 * @param string $Group The group
		 * @param string $Default The default-value of the property
		 * @param string $DataType The data-type of the property 
	 	 * @return void Sets a setting or updates an existing one
		 */
		function SetSetting($Name, $Display, $Description, $Group,  $Default = '', $DataType = 'string') {
			$this->Settings[$Group][$Name] = array(	'name' => $Name, 
													'display' => $Display, 
													'description' => $Description, 
													'default' => $Default, 
													'datatype' => $DataType);
		}
	}
	
	
	
	
?>