<?php
/**
 * @package ComaCMS
 * @subpackage Authentication
 * @copyright (C) 2005-2009 The ComaCMS-Team
 */
 
 #----------------------------------------------------------------------
 # file                 : rights.php
 # created              : 2009-05-27
 # copyright            : (C) 2005-2009 The ComaCMS-Team
 # email                : comacms@williblau.de
 #----------------------------------------------------------------------
 # This program is free software; you can redistribute it and/or modify
 # it under the terms of the GNU General Public License as published by
 # the Free Software Foundation; either version 2 of the License, or
 # (at your option) any later version.
 #----------------------------------------------------------------------

	/**
	 * @package ComaCMS
	 * @subpackage Authentication
	 */
	class Rights {
		
		/**
 		 * @access private
 		 * @var Sql This is a link to the mysql database
 		 */
 		var $_SqlConnection;
 		
 		/**
 		 * @access private
 		 * @var Language This is a link to the translation system
 		 */
 		var $_Translation;
		
		/**
		 * Contains all loaded rights
		 * @access public
		 * @staticvar array Contains all rights of the system
		 */
		var $Rights;
		
		/**
		 * Initializes a new instance of the rights class
		 * 
		 * @access public
		 * @param Sql &$SqlConnection This is a link to the sql connection class
		 * @param Language &$Translation This is a link to the translation class
		 * @return void A new instance of the rights class
		 */
		function Rights(&$SqlConnection, &$Translation) {
			
			$this->_SqlConnection = &$SqlConnection;
			$this->_Translation = &$Translation;
			
			// Initialize static rights array allow access without creation of new instances of the rights class
			static $staticRights;
			$this->Rights = &$staticRights;
		}
		
		/**
		 * Loads a rights file to a static array
		 * 
		 * @access public
		 * @param string $RightsFile This should be the complete path to the rightsfile
		 * @return bool Was the file correctly loaded?
		 */
		function Load($RightsFile) {
			
			// Link the translation class for the rightsfile
			$translation = &$this->_Translation;
			
			// Load the rightsfile to the local array using the static function SetRight and the static array Rights
			if (file_exists($RightsFile)) {
				include($RightsFile);
				return true;
			}
			return false;
		}
		
		/**
		 * Adds a right to the settings page of the admin menu
		 * 
		 * Types of rights are:
		 * - Main content: 0
		 * - Dynamic content: 1
		 * 
		 * @access public
		 * @static
		 * @param string $Name This is the name of the right to set
		 * @param string $Display This is the string displayd to the user for the right
		 * @param string $Description This is a description of the right displayed in <span class='info'></span> tags
		 * @param string $Group This is the main group the right is belonging to
		 * @param string $Subgroup This is a subgroup if there is any
		 * @param bool $Default The default value for this right
		 * @param integer $Type This is the type of the right
		 * @param string $MysqlTable This is the mysql table containing dynamic content for the right
		 * @param string $MysqlPrimaryKey This is the primary key of the dynamic content to identify a specific one
		 * @return void Sets a right or updates the existing one in the local array
		 */
		function SetRight($Name, $Display, $Description, $Group, $Default = true, $Type = '0', $Subgroup = '', $MysqlTable = '', $MysqlPrimaryKey = '') {
			
			// Check wether a subgoup is set or use the main group
			if ($Subgroup == '') {
				$Subgroup = 'main';
			}
			
			// Add the right to the local array
			$this->Rights[$Group][$Name] = array(	'name' => $Name,
													'display' => $Display,
													'description' => $Description,
													'default' => $Default,
													'type' => $Type,
													'subgroup' => $Subgroup,
													'mysql_table' => $MysqlTable,
													'mysql_primary_key' => $MysqlPrimaryKey);
		}
	}
?>