<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005-2007 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : config.php
 # created              : 2005-09-03
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
	class Config {
		
		/**
		 * @access private
		 * @var array Configelements
		 */
 		var $Elements = array();
 		
 		/**
 		 * A link to the mysql connection class
 		 * @var class SqlConnection
 		 * @access private
 		 */
 		var $_SqlConnection;
 		
 		/**
 		 * Initializes the config class
 		 * @access public
 		 * @param Sql &$SqlConnection A link to the mysql connection class
 		 * @return void
 		 */
 		function Config(&$SqlConnection) {
 			$this->_SqlConnection = &$SqlConnection;
 		}
 		
 		/**
 		 * Loads all configurations into a local array to easily get them
 		 * @access public
 		 */
		function LoadAll() {
			$sql = "SELECT *
				FROM " . DB_PREFIX . "config";
			$var_result = $this->_SqlConnection->SqlQuery($sql);
			while($var_data = mysql_fetch_object($var_result)) {
				$this->Elements[$var_data->config_name] = $var_data->config_value;
			}
	 	}
		
		/**
		 * Gets one specific configurationitem from local array and returns its value if it is in local array, else returns default value
		 * @access public
		 * @param string name Name of the Configurationitem
		 * @param string default Default Value of the Configurationitem
		 * @return string Value of the Configurationitem
		 */
		function Get($name, $default = '') {
			if(isset($this->Elements[$name]))
				return $this->Elements[$name];
			return $default;
		}
		
		/**
		 * Saves a new value for a configurationitem to database or adds it if it does not exist
		 * @access public
		 * @param string $Name Name of the Configurationitem
		 * @param string $Value Value of the Configurationitem
		 * @return void
		 */
		function Save($Name, $Value) {
			if(isset($this->Elements[$Name])) {
				if($Value != $this->Elements[$Name]) {
				$sql = "UPDATE " . DB_PREFIX . "config
					SET config_value = '$Value'
					WHERE config_name = '$Name'";
				$this->_SqlConnection->SqlQuery($sql);
				}
			}
			else {
				$sql = "INSERT INTO " . DB_PREFIX . "config (config_name , config_value )
					VALUES ( '$Name', '$Value')";
				$this->_SqlConnection->SqlQuery($sql);
			}
			$this->Elements[$Name] = $Value;
		}
	}
?>