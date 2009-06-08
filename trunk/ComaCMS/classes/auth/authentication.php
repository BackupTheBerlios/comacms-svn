<?php
/**
 * @package ComaCMS
 * @subpackage Authentication
 * @copyright (C) 2005-2009 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : authentication.php
 # created              : 2006-01-20
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
 	class Authentication {
 		
 		/**
 		 * @access private
 		 * @var array Contains all rights for the current user
 		 */
 		var $UserRights = array();
 		
 		/**
 		 * @access private
 		 * @var arrax Contains all rights of the groups of the current user
 		 */
 		var $GroupRights = array();
 		
 		/**
 		 * @access private
 		 * @var integer This is the ID of the current User
 		 */
 		var $UserID = 0;
 		
 		/**
 		 * @access private
 		 * @var Sql This is a link to the mysql database
 		 */
 		var $_SqlConnection;
 		
 		/**
 		 * Creates a new Authentication class for the current system user
 		 * @access public
 		 * @param Sql &$SqlConnection THis is a link to the sql database
 		 * @param integer $UserID This is the ID of the current user logged into the system
 		 * @return void
 		 */
 		function Authentication(&$SqlConnection, $UserID) {
 			
 			$this->UserID = $UserID;
 			$this->_SqlConnection = &$SqlConnection;
 		}
 		
 		/**
 		 * Loads all rights of the current user from the database and stores them in the loca array
 		 * @access public
 		 * @return bool Is everything done correctly?
 		 */
 		function LoadAll() {
 			
 			if ($this->UserID != 0) {
 				
 				// get all the groups of the user from the database
 				$sql = "SELECT auth_global_name, auth_global_value
 						FROM " . DB_PREFIX . "auth_global
 						WHERE auth_global_user_id='$this->UserID'";
 				$result = $this->_SqlConnection->SqlQuery($sql);
 				
 				if ($right = mysql_fetch_object($result)) {
 					
 					// Save rights for further use in local array
	 				$this->UserRights[$right->auth_name] = (($right->auth_value == 1) ? true : false);
	 				
 					while ($right = mysql_fetch_object($result)) {
	 					
	 					// Save rights for further use in local array
	 					$this->UserRights[$right->auth_name] = (($right->auth_value == 1) ? true : false);
	 				}
	 				mysql_free_result($result);
 				}
	 				
 				// Get the groups of the user and load existing rights for these
 				$sql = "SELECT group_id
 						FROM " . DB_PREFIX . "group_users
 						WHERE user_id='{$this->UserID}'";
 				$result = $this->_SqlConnection->SqlQuery($sql);
 				
	 			if ($group = mysql_fetch_object($result)) {
	 				
	 				$sql2 = "SELECT auth_global_name, auth_global_value
	 						 FROM " . DB_PREFIX . "auth_global
	 						 WHERE auth_global_group_id='{$group->group_id}'";
	 				$result2 = $this->_SqlConnection->SqlQuery($sql2);
	 				
	 				if ($right = mysql_fetch_object($result2)) {
	 					
	 					// Save the rights from the groups to the local array
						$this->GroupRights[$right->auth_name] = (($right->auth_value == 1) ? true : false);
						
	 					while ($right = mysql_fetch_object($result2)) {
	 					
	 						// Save the rights from the groups to the local array
							$this->GroupRights[$right->auth_name] = (($right->auth_value == 1) ? true : false);
	 					}
	 					mysql_free_result($result2);
	 				}
	 				
	 				while ($group = mysql_fetch_object($result)) {
	 					
	 					$sql2 = "SELECT auth_global_name, auth_global_value
	 							 FROM " . DB_PREFIX . "auth_global
	 							 WHERE auth_global_group_id='{$group->group_id}'";
	 					$result2 = $this->_SqlConnection->SqlQuery($sql2);
	 					
	 					if ($right = mysql_fetch_object($result2)) {
		 					
		 					// Save the rights from the groups to the local array
							$this->GroupRights[$right->auth_name] = (($right->auth_value == 1) ? true : false);
							
		 					while ($right = mysql_fetch_object($result2)) {
		 					
		 						// Save the rights from the groups to the local array
								$this->GroupRights[$right->auth_name] = (($right->auth_value == 1) ? true : false);
		 					}
		 					mysql_free_result($result2);
		 				}
	 				}
	 				mysql_free_result($result);
	 			}
 			}
 			else
 				return false;
 		}
 		
 		/**
 		 * Gets one specific right from the local array and returns the value if there is one else use default value
 		 * @access public
 		 * @param string $Name This is the name of the right
 		 * @param bool $Default This is the default value for the right
 		 * @return bool The value for the requested right
 		 */
 		function Get($Name, $Default = false) {
 			
 			// Check wether we have a value for the requested right
 			if (isset($this->UserRights[$Name]) || isset($this->GroupRights[$Name])) {
 				
 				// Check wether the userrights or a group allows the user to do what he wants and if not vorbid his action
 				if ($this->UserRights[$Name] == true || $this->GroupRights[$Name] == true)
 					return true;
 				else
 					return false;
 			}
 			
 			// If we still have no value return the default value
 			return $Default;
 		}
 		
 		/**
 		 * Sets a new value for a specific userright
 		 * @access public
 		 * @param string $Name This is the name of the right
 		 * @param bool $Value This is the value for the right
 		 * @return bool Was everything ok?
 		 */
 		function UserSave($Name, $Value) {
 			
 			// Check wether we got a true Right and a true Value
 			if ($Name == "" || $Value == "")
 				return false;
 			
 			// Check wether we have already a value for this right
 			if (isset($this->UserRights[$Name])) {
 				
 				// Update the database only if the value has changed
 				if ($this->UserRights[$Name] != $Value) {
 					
 					$sql = "UPDATE " . DB_PREFIX . "auth_global
 							SET auth_global_value = '" . (($Value) ? 1 : 0) . "'
 							WHERE auth_global_user_id='$this->UserID'";
 					$this->_SqlConnection->SqlQuery($sql);
 				}
 			}
 			else {
 				
 				// So there is no entry until now... so we have to add one to the database
 				$sql = "INSERT INTO " . DB_PREFIX . "auth_global
 						(auth_global_user_id, auth_global_name, auth_global_value)
 						VALUES ('$this->UserID', '$Name', '$Value')";
 				$this->_SqlConnection->SqlQuery($sql);
 			}
 			
 			// At least update the local array with the new value
 			$this->UserRights[$Name] = $Value;
 			return true;
 		}
 		
 		/**
 		 * Sets a new value for a specific group right
 		 * @access public
 		 * @param string $Name This is the name of the right
 		 * @param bool $Value This is the value for the right
 		 * @param integer $GroupID This is the ID of the group to change the right
 		 * @return bool Was everything ok?
 		 */
 		function GroupSave($Name, $Value, $GroupID) {
 			// Check wether we got a true Right and a true Value
 			if ($Name == "" || $Value == "" || !is_long($GroupID))
 				return false;
 			
 			// Check wether we have already a value for this right
 			if (isset($this->GroupRights[$Name])) {
 				
 				// Update the database only if the value has changed
 				if ($this->GroupRights[$Name] != $Value) {
 					
 					$sql = "UPDATE " . DB_PREFIX . "auth_global
 							SET auth_global_value = '" . (($Value) ? 1 : 0) . "'
 							WHERE auth_global_goup_id='$GroupID' AND auth_global_name = '$Name'";
 					$this->_SqlConnection->SqlQuery($sql);
 				}
 			}
 			else {
 				
 				// So there is no entry until now... so we have to add one to the database
 				$sql = "INSERT INTO " . DB_PREFIX . "auth_global
 						(auth_global_group_id, auth_global_name, auth_global_value)
 						VALUES ('$GroupID', '$Name', '$Value')";
 				$this->_SqlConnection->SqlQuery($sql);
 			}
 			
 			// At least update the local array with the new value
 			$this->GroupRights[$Name] = $Value;
 			return true;
 		}
 	}
?>