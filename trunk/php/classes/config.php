<?php
/*****************************************************************************
 *
 *  file		: config.php
 *  created		: 2005-09-03
 *  copyright		: (C) 2005 The ComaCMS-Team
 *  email		: comacms@williblau.de
 *
 *****************************************************************************/

/*****************************************************************************
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *****************************************************************************/
	
	require_once('./system/functions.php');	
	
	class Config {
 		var $Elements = array();
 		
		function LoadAll() {
			$sql = "SELECT *
				FROM " . DB_PREFIX . "config";
			$var_result = db_result($sql);
			while($var_data = mysql_fetch_object($var_result)) {
				$this->Elements[$var_data->config_name] = $var_data->config_value;
			}
	 	}
		
		function Get($name, $default = '') {
			if(isset($this->Elements[$name]))
				return $this->Elements[$name];
			return $default;
		}
		 	
		function Save($name, $value) {
		
		}
	}
?>