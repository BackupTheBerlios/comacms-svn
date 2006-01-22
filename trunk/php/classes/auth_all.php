<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005 The ComaCMS-Team
 */
 #----------------------------------------------------------------------#
 # file			: auth_all.php					#
 # created		: 2006-01-20					#
 # copyright		: (C) 2005 The ComaCMS-Team			#
 # email		: comacms@williblau.de				#
 #----------------------------------------------------------------------#
 # This program is free software; you can redistribute it and/or modify	#
 # it under the terms of the GNU General Public License as published by	#
 # the Free Software Foundation; either version 2 of the License, or	#
 # (at your option) any later version.					#
 #----------------------------------------------------------------------#
 
  	/**
 	 * @ignore
 	 */
 	require_once('./classes/auth.php');
 	
 	/**
	 * @package ComaCMS
	 */
 	class Auth_All extends Auth{
 		
 		var $userID;
 		
 		function Auth_All($UserID) {
 			$this->userID = $UserID;
 		}
 		
 		function setAdmin() {
 			$this->delete = true;
 			$this->edit = true;
 			$this->new_sub = true;
 			$this->view = true;
 		}
 		
 		function load($PageID = 0) {
 			// Load the rights (this is a real mysql query! ;-) )
 			$sql = "SELECT groups . * , auth . *
				FROM ( " . DB_PREFIX . "auth auth
				LEFT JOIN " . DB_PREFIX . "group_users groups ON groups.group_id = auth.auth_group_id)
				WHERE groups.user_id =$this->userID
				OR auth.auth_user_id =$this->userID
				OR ( auth.auth_user_id =0
				AND auth.auth_group_id =0)
				AND ( auth.auth_page_id =0
				OR auth.auth_page_id =$PageID)
				ORDER BY auth.auth_user_id ASC, auth.auth_group_id ASC";
			$auth_result = db_result($sql);
			$user_id_similar = false;
 			while($auth = mysql_fetch_object($auth_result)) {
 				$user_id_similar = ($auth->auth_user_id == $this->userID);
 				if($this->view != $auth->auth_view && $user_id_similar)
 					$this->view = ($auth->auth_view == 1);
 				else if(!$this->view && $auth->auth_view)
 					$this->view = true;
 				if($this->edit != $auth->auth_edit && $user_id_similar)
 					$this->edit = ($auth->auth_edit == 1);
 				else if(!$this->edit && $auth->auth_edit)
 					$this->edit = true;
 				if($this->delete != $auth->auth_delete && $user_id_similar)
 					$this->delete = ($auth->auth_delete == 1);
 				else if(!$this->delete && $auth->auth_delete)
	 				$this->delete = true;
	 			if($this->new_sub != $auth->auth_new_sub && $user_id_similar)
 					$this->new_sub = ($auth->auth_new_sub == 1);
 				else if(!$this->new_sub && $auth->auth_new_sub)
	 				$this->new_sub = true;
 			}

 		}	
 	}
?>