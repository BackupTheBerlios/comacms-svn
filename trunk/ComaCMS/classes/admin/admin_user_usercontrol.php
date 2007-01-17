<?php
/**
 * @package ComaCMS
 * @subpackage UserInterface
 * @copyright (C) 2005-2007 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : admin.php	
 # created              : 2007-01-16
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
	require_once __ROOT__ . '/classes/admin/admin.php';
	
	/**
	 * Returns the Usercontrol
	 * @package ComaCMS
	 * @subpackage UserInterface
	 */
	class Admin_User_Usercontrol extends Admin {
		
		/**
		 * Gets the usercontrolpage
		 * @access public
		 * @param string $Action The subpage of the usercontrol
		 * @return string A template for the outputpage
		 */
		function GetPage($Action = '') {
			
			// Get the data of the userinterface
			$sql = 'SELECT user.user_id, user.user_name, user.user_showname, user.user_registerdate, user.user_admin, user.user_author, user.user_email
					FROM ' . DB_PREFIX . 'users
					WHERE user.user_id=';
			
			
			// Set lang replacements for comalate
			$this->_ComaLate->SetReplacement('LANG_USERINTERFACE', $this->_Translation->GetTranslation('userinterface'));
			
			// Generate the template
			$template = '<h2>{LANG_USERINTERFACE}</h2>
					<table>
					<USER_PROFILE:loop>
						<tr>
							<th>{PROFILE_FIELD_NAME}</th>
							<td>{PROFILE_FIELD_VALUE}</td>
						</tr>
					</USER_PROFILE>
					</table>
					<a href="special.php?page=userinterface&amp;action=edit_profile" class="button">Bearbeiten</a>';
			return $template;
		}
	}

?>