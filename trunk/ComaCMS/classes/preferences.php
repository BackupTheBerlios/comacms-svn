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
		 * @access public
		 * @var array
		 * @static
		 */
		var $Settings;
		
		/**
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
		 * @access public
		 * @param string PreferencesFile
		 * @return void
		 */
		function Load($PreferencesFile) {
			$translation = &$this->_Translation;
			include($PreferencesFile);
		}
		
		/** SetSetting
		 * Adds a property to the preferences-page
		 * 
		 * @access public
		 * @static
		 * @param string Name The name of the property
		 * @param string Display The displayed title of the property
		 * @param string Description The description of the property
		 * @param string Group The group
		 * @param string Default The default-value of the property
		 * @param string DataType The data-type of the property 
	 	 * @return void
		 */
		function SetSetting($Name, $Display, $Description, $Group,  $Default = '', $DataType = 'string') {
			$this->Settings[$Group][$Name] = array('name' => $Name, 'display' => $Display, 'description' => $Description, 'default' => $Default, 'datatype' => $DataType);
		}
	}
	
	
	
	
?>