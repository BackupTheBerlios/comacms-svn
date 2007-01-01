<?php
/**
 * @package ComaCMS
 * @subpackage Auth
 * @copyright (C) 2005-2007 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : group_auth.php
 # created              : 2005-12-04
 # copyright            : (C) 2005-2007 The ComaCMS-Team
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
 	require_once('./classes/auth/auth.php');
 	
 	/**
	 * @package ComaCMS
	 * @subpackage Auth
	 */
 	class Auth_Group extends Auth{
 		var $group_id = 0;
 		var $has_own = false;
 		
 		function Auth_Group($group_id = 0) {
 			$this->group_id = $group_id;	  			
 			$sql = "SELECT *
 				FROM " . DB_PREFIX . "auth
 				WHERE (auth_group_id=0 OR auth_group_id=$this->group_id) AND auth_page_id=0 AND auth_user_id=0
 				ORDER BY auth_group_id ASC";
 			$auth_result = db_result($sql);
 			$group_id_similar = false;
 			while($auth = mysql_fetch_object($auth_result)) {
 				if($auth->auth_group_id == $this->group_id) {
 					$this->has_own = true;
 					$group_id_similar = true;
 				}
 				else
 					$group_id_similar = false;
 				
 				if($this->view != $auth->auth_view && $group_id_similar)
 					$this->view = ($auth->auth_view == 1);
 				else if(!$this->view && $auth->auth_view)
 					$this->view = true;
 				if($this->edit != $auth->auth_edit && $group_id_similar)
 					$this->edit = ($auth->auth_edit == 1);
 				else if(!$this->edit && $auth->auth_edit)
 					$this->edit = true;
 				if($this->delete != $auth->auth_delete && $group_id_similar)
 					$this->delete = ($auth->auth_delete == 1);
 				else if(!$this->delete && $auth->auth_delete)
	 				$this->delete = true;
	 			if($this->new_sub != $auth->auth_new_sub && $group_id_similar)
 					$this->new_sub = ($auth->auth_new_sub == 1);
 				else if(!$this->new_sub && $auth->auth_new_sub)
	 				$this->new_sub = true;
 			
 			}
 				
 			if(!$this->edit && $this->delete)
 				$this->edit = true;
 			if(!$this->view && $this->edit)
 				$this->view = true;
 		}
 		
 		function Save() {
 			if($this->has_own)
 				$sql = "UPDATE " . DB_PREFIX . "auth
 					SET auth_view=" . (($this->view)? 1 : 0) . ", auth_edit=" . (($this->edit)? 1 : 0) . ", auth_delete=" . (($this->delete)? 1 : 0) . ", auth_new_sub=" . (($this->new_sub)? 1 : 0) . "
 					WHERE auth_group_id=$this->group_id AND auth_user_id=0 AND auth_page_id=0";
 			else
 				$sql = "INSERT INTO " . DB_PREFIX . "auth (auth_user_id, auth_group_id, auth_page_id, auth_view, auth_edit, auth_delete, auth_new_sub)
					VALUES (0, $this->group_id, 0, " . (($this->view)? 1 : 0) . ", " . (($this->edit)? 1 : 0) . ", " . (($this->delete)? 1 : 0) . ", " . (($this->new_sub)? 1 : 0) . ")";
 			db_result($sql);
 		}
 	}
?>