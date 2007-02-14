<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005-2007 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : memberlist.php	
 # created              : 2007-02-14
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
	require_once __ROOT__ . '/classes/user/user.php';
	
	/**
	 * Provides a memberlist
	 * @package ComaCMS
	 */
	class Memberlist extends User {
		
		/**
		 * Gets the subpages of the memberlist
		 * @access public
		 * @param string $Action The name of the subpage
		 * @return string The template for the memberlist page
		 */
		function GetPage($Action = '', $Owner = '') {
			
			if ($Owner != 'userinterface')
				$Owner == '';
			
			$template = '';
			
			// Switch between the subpages
			switch ($Action) {
				
				case 'show_profile':
					$template .= $this->_ShowProfile();
					break;
					
				default:
					// Get the memberlist
					$template .= $this->_ShowMemberlist($Owner);
					break;
			}
		}
		
		/**
		 * Returns a template for a memberlist
		 * @access private
		 * @param string $Owner The owner of the subpage memberlist
		 * @return string A template for the memberlist
		 */
		function _ShowMemberlist($Owner) {
			
			// Get all users from the database
			$sql = "SELECT *
					FROM " . DB_PREFIX . "users";
			$usersResult = $this->_SqlConnection->SqlQuery($sql);
			
			// Generate users array
			$users = array();
			
			while ($user = mysql_fetch_object($usersResult)) {
				
				$users[] = array(	'USER_SHOWNAME' => $user->user_showname,
									'USER_NAME' => $user->user_name);
			}
			$this->_ComaLate->SetReplacement('USERS', $users);
			
			// Set replacements for language
			$this->_ComaLate->SetReplacement('LANG_MEMBERLIST', $this->_Translation->GetTranslation('memberlist'));
			$this->_ComaLate->SetReplacement('POSITION', (($Owner == 'userinterface') ? 'page=userinterface&amp;subpage=memberlist&amp;' : 'page=memberlist&amp;'));
			
			// Generate template
			$template = '
					<h2>{LANG_MEMBERLIST}</h2>
					<ol>
					<USERS:loop>
						<li><a href="special.php?{POSITION}action=show_profile&amp;user_name={USER_NAME}">{USER_SHOWNAME}</a></li>
					</USERS>
					</ol>
					';
			return $template;
		}
	}
	
?>
