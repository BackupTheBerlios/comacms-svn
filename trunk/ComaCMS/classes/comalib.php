<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005-2007 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : comalib.php
 # created              : 2006-12-04
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
 	class ComaLib {
 		/**
 		 * @access private
 		 */
 		var $_UserIDs = array();
  		
  		/**
 		 * @access private
 		 */
 		var $_UserShownames = array();
  		
  		/**
 		 * @access private
 		 */
 		var $_GroupNames = array();
 		
 		function GetUserIDByName($Name) {
 			if(isset($this->_UserIDs[$Name]))
 				return $this->_UserIDs[$Name];
 			else {
				$sql = "SELECT user_id
					FROM " . DB_PREFIX . "users
					WHERE user_name='$Name'";
				$result = db_result($sql);
				if($user_data = mysql_fetch_object($result)) {
					$this->_UserIDs[$Name] = $user_data->user_id;
					return $user_data->user_id;
				}
 			}
		}

		function GetUserByID($ID) {
			if(isset($this->_UserShownames[$ID]))
 				return $this->_UserShownames[$ID];
 			else {
				$sql = "SELECT user_showname
					FROM " . DB_PREFIX . "users
					WHERE user_id = '$ID'";
				$result = db_result($sql);
				if($user_data = mysql_fetch_object($result)) {
					$this->_UserShownames[$ID] = $user_data->user_showname;
					return $user_data->user_showname;
				}
 			}
		}
	
		function GetGroupByID($ID) {
			if(isset($this->_GroupNames[$ID]))
 				return $this->_GroupNames[$ID];
 			else {
				$sql = "SELECT group_name
					FROM " . DB_PREFIX . "groups
					WHERE group_id = '$ID'";
				$result = db_result($sql);
				if($group_data = mysql_fetch_object($result)) {
					$this->_GroupNames[$ID] = $group_data->group_name;
					return $group_data->group_name;
				}
			}	
		}
 	}
?>