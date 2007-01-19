<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005-2007 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : admin_usermanagement.php
 # created              : 2007-01-19
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
	 * Manages the registred users
	 * @package ComaCMS
	 * @subpackage AdminInterface
	 */
	class Admin_Usermanagement extends Admin {
		
		/**
		 * Gets the subpage of the usermanagement which is selected by <var>$Action</var>
		 * @access public
		 * @param string $Action The name of the subpage
		 * @return string The template for the usermanagement
		 */
		function GetPage($Action) {
			
			$template = '';
			
			// Switch between the subpages of the usermanagement
			switch ($Action) {
				
				default:
					$template .= $this->_HomePage();
					break;
			}
			
			// Return the template
			return $template;
		}
		
		/**
		 * Generates the template for the homepage of the usermanagement
		 * @access private
		 * @return string The template for the home page
		 */
		function _HomePage() {
			
			
			// Generate the template
			$template = '<h2>{LANG_USERMANAGEMENT}</h2>
				<table class="full_with">
					<tr>
						<th>{LANG_NAME}</th>
						<th>{LANG_NICKNAME}</th>
						<th>{LANG_EMAIL}</th>
						<th>{LANG_ADMIN}</th>
						<th>{LANG_ACTIONS}</th>
					</tr>
				<USERS:loop>
					<tr>
						<td>{USER_SHOWNAME}</td>
						<td>{USER_NICKNAME}</td>
						<td>{USER_EMAIL}</td>
						<td>{USER_ADMIN}</td>
						<td><USER_ACTIONS:loop><a href="{ACTION_HREF}"><img src="{ACTION_IMG}" height="16" width="16" alt="{ACTION_TITLE}" title="{ACTION_TITLE}" /></a>&nbsp;</USER_ACTIONS></td>
					</tr>
				</USERS>
				</table>
				<a href="admin.php?page=users&action=new_user" class="button">{LANG_CREATE_NEW_USER}</a>';
			return $template;
		}
	}
?>
