<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : config.php
 # created              : 2005-09-03
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
	 * make sure that all used functions are available
	 */
	require_once('./system/functions.php');
		
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
 		 * Loads all configurations into a local array to easily get them
 		 * @access public
 		 */
		function LoadAll() {
			$sql = "SELECT *
				FROM " . DB_PREFIX . "config";
			$var_result = db_result($sql);
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
		 * @param string name Name of the Configurationitem
		 * @param string value Value of the Configurationitem
		 * @return void
		 */
		function Save($name, $value) {
			if(isset($this->Elements[$name])) {
				if($value != $this->Elements[$name]) {
				$sql = "UPDATE " . DB_PREFIX . "config
					SET config_value = '$value'
					WHERE config_name = '$name'";
				db_result($sql);
				}
			}
			else {
				$sql = "INSERT INTO " . DB_PREFIX . "config (config_name , config_value )
					VALUES ( '$name', '$value')";
				db_result($sql);
			}
			$this->Elements[$name] = $value;
		}
	}
?>