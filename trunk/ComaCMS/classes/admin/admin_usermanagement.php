<?php
/**
 * @package ComaCMS
 * @subpackage AdminInterface
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
	require_once __ROOT__ . '/lib/formmaker/formmaker.class.php';
	require_once __ROOT__ . '/functions.php';
	
	/**
	 * Manages the registred users
	 * @package ComaCMS
	 * @subpackage AdminInterface
	 */
	class Admin_Usermanagement extends Admin {
		
		/**
		 * Gets the subpage of the usermanagement which is selected by <var>$Action</var>
		 * 
		 * @access public
		 * @param string $Action The name of the subpage
		 * @return string The template for the usermanagement
		 */
		function GetPage($Action) {
			
			$template = '<h2>{LANG_USERMANAGEMENT}</h2>';
			$this->_ComaLate->SetReplacement('LANG_USERMANAGEMENT', $this->_Translation->GetTranslation('usermanagement'));
			
			// TODO: Bug by type of userdefined field
			
			// Switch between the subpages of the usermanagement
			switch ($Action) {
				
				
				
				default:
					// Returns a list with all registred users
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
			
			// Get the existing users
			$sql = "SELECT *
					FROM " . DB_PREFIX . "users";
			$usersResult = $this->_SqlConnection->SqlQuery($sql);
			
			// Initialize users array
			$users = array();

			while ($user = mysql_fetch_object($usersResult)) {

				$users[substr($user->user_showname, 0, 1)]['SUBTITLE'] = substr($user->user_showname, 0, 1);
				$users[substr($user->user_showname, 0, 1)]['USERS'] = array(
																			'USER_ID' => $user->user_id,
																			'USER_SHOWNAME' => $user->user_showname,
																			'USER_ADMIN' => (($user->user_admin == 1) ? '(' . $this->_Translation->GetTranslation('administrator') . ')' : ''),
																			'USER_COLOR' => (($user->user_activated == 1) ? 'green' : 'red'),
																			'USER_EDIT' => sprintf($this->_Translation->GetTranslation('edit_%user%'), $user->user_showname),
																			'USER_DELETE' => sprintf($this->_Translation->GetTranslation('delete_%user%'), $user->user_showname)
																		);
			}

			if (count(array_keys($users)) == 0) {

				// Set empty usercontent
				$this->_ComaLate->SetReplacement('USER_CONTENT', '<span class="full-size-error">' . $this->_Translation->GetTranslation('no_users') . '</span>');
			}
			else {

				$users = TwoColumns($users, 'USERS');

				// Check wether we got any entries in column b and build the right template
				if (count(array_keys($users[2])) == 0) {
					$template = '<LISTS:loop>
						<ul style="list-style: none">
							<li>
								<h4>{SUBTITLE}</h4>
								<ul>
									<USERS:loop><li class="group_user_item"><span class="structure_row"><span class="group_actions"><a href="admin.php?page=users&amp;action=edit_user&amp;user_id={USER_ID}"><img src="./img/edit.png" class="icon" height="16" width="16" alt="{USER_EDIT}" title="{USER_EDIT}" /></a><a href="admin.php?page=groups&amp;action=remove_user&amp;user_id={USER_ID}&amp;group_id={GROUP_ID}"><img src="./img/del.png" class="icon" height="16" width="16" alt="{USER_REMOVE}" title="{USER_REMOVE}"/></a></span><strong>{USER_NAME}</strong></span></li></USERS>
								</ul>
							</li>
						</ul>
						</LISTS>';
					$this->_ComaLate->SetReplacement('LISTS', $users[1]);
				}
				else {
					// Generate a template
					$template = '<div class="column ctwo">
						<LISTS_A:loop>
							<ul style="list-style: none">
								<li>
									<h4>{SUBTITLE}</h4>
									<ul>
										<USERS:loop><li class="group_user_item"><span class="structure_row"><span class="group_actions"><a href="admin.php?page=users&amp;action=edit_user&amp;user_id={USER_ID}"><img src="./img/edit.png" class="icon" height="16" width="16" alt="{USER_EDIT}" title="{USER_EDIT}" /></a><a href="admin.php?page=groups&amp;action=remove_user&amp;user_id={USER_ID}&amp;group_id={GROUP_ID}"><img src="./img/del.png" class="icon" height="16" width="16" alt="{USER_REMOVE}" title="{USER_REMOVE}"/></a></span><strong>{USER_NAME}</strong></span></li></USERS>
									</ul>
								</li>
							</ul>
						</LISTS_A>
						</div>
						<div class="column ctwo">
						<LISTS_B:loop>
							<ul style="list-style: none">
								<li>
									<h4>{SUBTITLE}</h4>
									<ul>
										<USERS:loop><li class="group_user_item"><span class="structure_row"><span class="group_actions"><a href="admin.php?page=users&amp;action=edit_user&amp;user_id={USER_ID}"><img src="./img/edit.png" class="icon" height="16" width="16" alt="{USER_EDIT}" title="{USER_EDIT}" /></a><a href="admin.php?page=groups&amp;action=remove_user&amp;user_id={USER_ID}&amp;group_id={GROUP_ID}"><img src="./img/del.png" class="icon" height="16" width="16" alt="{USER_REMOVE}" title="{USER_REMOVE}" /></a></span><strong>{USER_NAME}</strong></span></li></USERS>
									</ul>
								</li>
							</ul>
						</LISTS_B>
						</div>
						<p class="after_column" />';
					$this->_ComaLate->SetReplacement('LISTS_A', $users[1]);
					$this->_ComaLate->SetReplacement('LISTS_B', $users[2]);
				}
				$this->_ComaLate->SetReplacement('USER_CONTENT', $template);
			}
			
			$template = '';
			return $template;
		}
		
	}
?>
