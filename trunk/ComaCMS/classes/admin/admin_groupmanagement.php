<?php
/**
 * @package ComaCMS
 * @subpackage AdminInterface
 * @copyright (C) 2005-2009 The ComaCMS-Team
 */
 
 #----------------------------------------------------------------------
 # file                 : admin_groupmanagement.php
 # created              : 2009-05-28
 # copyright            : (C) 2005-2009 The ComaCMS-Team
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
	 * Manages the usergroups of the system
	 * 
	 * @package ComaCMS
	 * @subpackage AdminInterface
	 */
	class Admin_Groupmanagement extends Admin {
		
		/**
		 * Gets the subpage of the groupmanagement selected by <var>$Action</var>
		 * 
		 * @access public
		 * @param string $Action This is the name of the subpage
		 * @return string The code of the subpage for the comalate engine
		 */
		function GetPage($Action) {
			
			// Set a headline for the current page of the admin menu
			$template = '<h2>{LANG_LOCAL_GROUPMANAGEMENT}</h2>';
			$this->_ComaLate->SetReplacement('LANG_LOCAL_GROUPMANAGEMENT', $this->_Translation->GetTranslation('local_groupmanagement'));
			
			// Switch between the different subpages of the groupmanagement
			switch ($Action) {
				
				case 'new_group':
					// Returns a formular to create a new group
					$template .= $this->_NewGroup();
					break;
					
				case 'add_group':
					// Adds a new group to the system, but before tests the inputs
					$template .= $this->_AddGroup();
					break;
				
				case 'view_group':
					// Shows the users of the group to add or remove some
					$template .= $this->_ViewGroup();
					break;
				
				case 'edit_group':
					// Returns a formular to edit the parameters of the group
					$template .= $this->_EditGroup();
					break;
				
				case 'save_group':
					// Saves changes of a certain group to the database
					$template .= $this->_SaveGroup();
					break;
				
				case 'delete_group':
					// Confirms wether the group realy should be deleted
					$template .= $this->_DeleteGroup();
					break;
				
				case 'remove_group':
					// Removes a group from the system
					$this->_RemoveGroup();
					$template .= $this->_HomePage();
					break;
				
				case 'add_user':
					// Adds a user to a group
					$template .= $this->_AddUser();
					break;
				
				case 'remove_user':
					// Removes a user from a group
					$template .= $this->_RemoveUser();
					break;
				
				case 'remove_all_users':
					// Removes all users from a group
					$template .= $this->_RemoveAllUsers();
					break;
				
				default:
					// Returns a list with all existing groups
					$template .= $this->_HomePage();
					break;
			}
			
			// Return the template to the admin menu
			return $template;
		}
		
		/**
		 * Generates a template for the homepage of the groupmanagement
		 * 
		 * @access private
		 * @return string The template for the page
		 */
		function _HomePage() {
			
			// Get all existing groups from the database
			$sql = 'SELECT *
					FROM ' . DB_PREFIX . 'groups';
			$result = $this->_SqlConnection->SqlQuery($sql);
			
			// Initialize the grouparray
			$groups = array();
			
			while ($group = mysql_fetch_object($result)) {
				
				$sql = 'SELECT user_id
						FROM ' . DB_PREFIX . 'group_users
						WHERE group_id="' . $group->group_id . '"';
				$result2 = $this->_SqlConnection->SqlQuery($sql);
				$usercount = mysql_num_rows($result2);
				mysql_free_result($result2);
				
				$groups[] = array (	'GROUP_ID' => $group->group_id,
									'GROUP_NAME' => $group->group_name,
									'GROUP_DESCRIPTION' => $group->group_description,
									'GROUP_USERCOUNT' => $usercount,
									'GROUP_ACTIONS' => array(
											0 => array('ACTION' => 'view_group', 'ACTION_IMG' => './img/view.png', 'ACTION_TITLE' => $this->_Translation->GetTranslation('view')),
											1 => array('ACTION' => 'edit_group', 'ACTION_IMG' => './img/edit.png', 'ACTION_TITLE' => $this->_Translation->GetTranslation('edit')),
											2 => array('ACTION' => 'delete_group', 'ACTION_IMG' => './img/del.png', 'ACTION_TITLE' => $this->_Translation->GetTranslation('delete'))
										)
									);
			}
			mysql_free_result($result);
			$this->_ComaLate->SetReplacement('GROUPS', $groups);
			
			// Set language replacements
			$this->_ComaLate->SetReplacement('LANG_CREATE_NEW_GROUP', $this->_Translation->GetTranslation('create_new_group'));
			$this->_ComaLate->SetReplacement('LANG_GROUPNAME', $this->_Translation->GetTranslation('name'));
			$this->_ComaLate->SetReplacement('LANG_GROUPDESCRIPTION', $this->_Translation->GetTranslation('groupdescription'));
			$this->_ComaLate->SetReplacement('LANG_USERCOUNT', $this->_Translation->GetTranslation('usercount'));
			$this->_ComaLate->SetReplacement('LANG_ACTIONS', $this->_Translation->GetTranslation('actions'));
			
			// Generate the template for the output
			$template = '
						<a href="admin.php?page=groups&amp;action=new_group" class="button">{LANG_CREATE_NEW_GROUP}</a>
						<table class="full_width">
							<tr>
								<th>{LANG_GROUPNAME}</th>
								<th>{LANG_GROUPDESCRIPTION}</th>
								<th>{LANG_USERCOUNT}</th>
								<th>{LANG_ACTIONS}</th>
							</tr>
						<GROUPS:loop>
							<tr>
								<td>{GROUP_NAME}</td>
								<td>{GROUP_DESCRIPTION}</td>
								<td>{GROUP_USERCOUNT}</td>
								<td><GROUP_ACTIONS:loop><a href="admin.php?page=groups&amp;action={ACTION}&amp;group_id={GROUP_ID}"><img src="{ACTION_IMG}" height="16" width="16" alt="{ACTION_TITLE}" title="{ACTION_TITLE}" /></a>&nbsp;</GROUP_ACTIONS></td>
							</tr>
						</GROUPS>
						</table>';
			return $template;
		}
		
		/**
		 * Returns a formular to add a new group to the system
		 * 
		 * @access private
		 * @return string A template for the groupformular
		 */
		function _NewGroup() {
			
			// Initialize a new instance of the formmaker
			$formMaker = new FormMaker($this->_Translation->GetTranslation('todo'), $this->_SqlConnection);
			$formMaker->AddForm('add_group', 'admin.php', $this->_Translation->GetTranslation('save'), $this->_Translation->GetTranslation('group'), 'post');
			
			$formMaker->AddHiddenInput('add_group', 'page', 'groups');
			$formMaker->AddHiddenInput('add_group', 'action', 'add_group');
			
			$formMaker->AddInput('add_group', 'group_name', 'text', $this->_Translation->GetTranslation('name'), $this->_Translation->GetTranslation('this_is_the_name_of_the_new_group'));
			$formMaker->AddInput('add_group', 'group_description', 'text', $this->_Translation->GetTranslation('description'), $this->_Translation->GetTranslation('this_is_a_description_of_the_new_group'));
			
			// Generate the template
			$template = "\r\n\t\t\t\t" . $formMaker->GenerateSingleFormTemplate($this->_ComaLate, false);
			return $template;
		}
		
		/**
		 * Returns a formular to check inputs for a new group or adds the group to the system
		 * 
		 * @access private
		 * @return string A template for the groupformular
		 */
		function _AddGroup() {
			
			// Get external input values
			$GroupName = GetPostOrGet('group_name');
			$GroupDescription = GetPostOrGet('group_description');
			
			// Initialize a new instance of the formmaker
			$formMaker = new FormMaker($this->_Translation->GetTranslation('todo'), $this->_SqlConnection);
			$formMaker->AddForm('add_group', 'admin.php', $this->_Translation->GetTranslation('save'), $this->_Translation->GetTranslation('group'), 'post');
			
			$formMaker->AddHiddenInput('add_group', 'page', 'groups');
			$formMaker->AddHiddenInput('add_group', 'action', 'add_group');
			
			$formMaker->AddInput('add_group', 'group_name', 'text', $this->_Translation->GetTranslation('name'), $this->_Translation->GetTranslation('this_is_the_name_of_the_new_group'), $GroupName);
			$formMaker->AddCheck('add_group', 'group_name', 'empty', $this->_Translation->GetTranslation('a_groupname_must_be_indicated'));
			$formMaker->AddCheck('add_group', 'group_name', 'allready_assigned', $this->_Translation->GetTranslation('the_groupname_you_indicated_is_already_assigned'), '', 'groups', 'group_name');
			
			$formMaker->AddInput('add_group', 'group_description', 'text', $this->_Translation->GetTranslation('description'), $this->_Translation->GetTranslation('this_is_a_description_of_the_new_group'), $GroupDescription);
			$formMaker->AddCheck('add_group', 'group_description', 'empty', $this->_Translation->GetTranslation('a_groupdescription_musst_be_indicated'));
			
			// Check the inputs of the user for correctnes
			if ($formMaker->CheckInputs('add_group', true)) {
				
				// everything is correct so insert the new gorup into the database
				$sql = "INSERT INTO " . DB_PREFIX . "groups
						(group_name, group_description)
						VALUES ('{$GroupName}', '{$GroupDescription}')";
				$this->_SqlConnection->SqlQuery($sql);
				
				// Get the id of the new group from the database
				$sql = "SELECT group_id
						FROM " . DB_PREFIX . "groups
						WHERE group_name='{$GroupName}' AND group_description='{$GroupDescription}'";
				$result = $this->_SqlConnection->SqlQuery($sql);
				$group = mysql_fetch_object($result);
				mysql_free_result($result);
				
				// Set the user to view the new group and maybee to add some users to it
				$template = "\r\n\t\t\t\t" . $this->_ViewGroup($group->group_id);
				return $template;
			}
			else {
				// Generate the template to correct the inputs
				$template = "\r\n\t\t\t\t" . $formMaker->GenerateSingleFormTemplate($this->_ComaLate, true);
				return $template;
			}
		}
		
		/**
		 * Returns a formular to edit an existing group of the system
		 * 
		 * @access private
		 * @return string A template for the groupformular
		 */
		function _EditGroup() {
			
			// Get external parameters
			$GroupID = GetPostOrGet('group_id');
			
			// Gether the groupinformation from the database
			$sql = 'SELECT *
					FROM ' . DB_PREFIX . "groups
					WHERE group_id={$GroupID}";
			$result = $this->_SqlConnection->SqlQuery($sql);
			
			if ($group = mysql_fetch_object($result)) {
				
				// Free unused dataspace
				mysql_free_result($result);
				
				// Generate the formular using formmaker
				$formMaker = new FormMaker($this->_Translation->GetTranslation('todo'), $this->_SqlConnection);
				$formMaker->AddForm('edit_group', 'admin.php', $this->_Translation->GetTranslation('save'), $this->_Translation->GetTranslation('group'), 'post');
				
				$formMaker->AddHiddenInput('edit_group', 'page', 'groups');
				$formMaker->AddHiddenInput('edit_group', 'action', 'save_group');
				$formMaker->AddHiddenInput('edit_group', 'group_id', $GroupID);
				
				$formMaker->AddInput('edit_group', 'group_name', 'text', $this->_Translation->GetTranslation('name'), $this->_Translation->GetTranslation('this_is_the_name_of_the_new_group'), $group->group_name);
				
				$formMaker->AddInput('edit_group', 'group_description', 'text', $this->_Translation->GetTranslation('description'), $this->_Translation->GetTranslation('this_is_a_description_of_the_new_group'), $group->group_description);
				
				// Generate the template to edit the inputs
				$template = "\r\n\t\t\t\t" . $formMaker->GenerateSingleFormTemplate($this->_ComaLate, false);
				return $template;
			}
			else {
				return $this->_Translation->GetTranslation('this_group_id_does_not_exist');
			}
		}
		
		/**
		 * Returns a formular to correct all wrong inputs
		 * 
		 * @access private
		 * @return string A template for the groupformular
		 */
		function _SaveGroup() {
			
			// Get external parameters
			$GroupID = GetPostOrGet('group_id');
			$GroupName = GetPostOrGet('group_name');
			$GroupDescription = GetPostOrGet('group_description');
			
			// Generate the formular using formmaker
			$formMaker = new FormMaker($this->_Translation->GetTranslation('todo'), $this->_SqlConnection);
			$formMaker->AddForm('save_group', 'admin.php', $this->_Translation->GetTranslation('save'), $this->_Translation->GetTranslation('group'), 'post');
			
			$formMaker->AddHiddenInput('save_group', 'page', 'groups');
			$formMaker->AddHiddenInput('save_group', 'action', 'save_group');
			$formMaker->AddHiddenInput('save_group', 'group_id', $GroupID);
			
			$formMaker->AddInput('save_group', 'group_name', 'text', $this->_Translation->GetTranslation('name'), $this->_Translation->GetTranslation('this_is_the_name_of_the_new_group'), $GroupName);
			$formMaker->AddCheck('save_group', 'group_name', 'empty', $this->_Translation->GetTranslation('a_groupname_must_be_indicated'));
			$formMaker->AddCheck('save_group', 'group_name', 'allready_assigned', $this->_Translation->GetTranslation('the_groupname_you_indicated_is_already_assigned'), '', 'groups', 'group_name');
			
			$formMaker->AddInput('save_group', 'group_description', 'text', $this->_Translation->GetTranslation('description'), $this->_Translation->GetTranslation('this_is_a_description_of_the_new_group'), $GroupDescription);
			$formMaker->AddCheck('save_group', 'group_description', 'empty', $this->_Translation->GetTranslation('a_groupdescription_musst_be_indicated'));
				
			if ($formMaker->CheckInputs('save_group', true)) {
				
				// everything is correct so insert the new gorup into the database
				$sql = "UPDATE " . DB_PREFIX . "groups
						SET group_name='{$GroupName}', group_description='{$GroupDescription}'
						WHERE group_id='{$GroupID}'";
				$this->_SqlConnection->SqlQuery($sql);
				
				// Set the user to view the new group and maybee to add some users to it
				$template = "\r\n\t\t\t\t" . $this->_ViewGroup($GroupID);
				return $template;
			}
			else {
				// Generate the template to correct the inputs
				$template = "\r\n\t\t\t\t" . $formMaker->GenerateSingleFormTemplate($this->_ComaLate, true);
				return $template;
			}
		}
		
		/**
		 * Returns an overview over the current users of the group
		 * 
		 * @access private
		 * @param integer $GroupID This is the id of the group that should be shown
		 * @return string A template for the groupoverview
		 */
		function _ViewGroup($GroupID = '') {
			
			// Get external inputs
			if ($GroupID == '')
				$GroupID = GetPostOrGet('group_id');
			if (!is_numeric($GroupID))
				return $this->_HomePage();
			
			// We are looking for an existing group now so get the groupdata from the database
			$sql = "SELECT *
					FROM " . DB_PREFIX . "groups
					WHERE group_id='$GroupID'";
			$result = $this->_SqlConnection->SqlQuery($sql);
			$group = mysql_fetch_object($result);
			mysql_free_result($result);
			
			$this->_ComaLate->SetReplacement('GROUP_NAME', $group->group_name);
			$this->_ComaLate->SetReplacement('GROUP_DESCRIPTION', $group->group_description);
			
			// Get the current users of the group from the database
			$sql = "SELECT users.user_showname, group_users.user_id
					FROM " . DB_PREFIX . "group_users group_users
					LEFT JOIN " . DB_PREFIX . "users users
					ON (group_users.user_id = users.user_id)
					WHERE group_users.group_id='$GroupID'
					ORDER BY users.user_showname ASC";
			$result = $this->_SqlConnection->SqlQuery($sql);
			
			$users = array();
			while ($user = mysql_fetch_object($result)) {
				
				// Add users to the array to display 
				$users[substr($user->user_showname, 0, 1)]['SUBTITLE'] = substr($user->user_showname, 0, 1);
				$users[substr($user->user_showname, 0, 1)]['USERS'][] = array(	
																				'USER_ID' => $user->user_id,
																				'GROUP_ID' => $GroupID, 
																				'USER_NAME' => $user->user_showname, 
																				'USER_REMOVE' => sprintf($this->_Translation->GetTranslation('remove_%user%_from_the_group'), $user->user_showname),
																				'USER_EDIT' => sprintf($this->_Translation->GetTranslation('edit_%user%'), $user->user_showname));
			}
			
			// Order the items for two column output
			if (count(array_keys($users)) == 0) {
				
				// Set groupcontend to empty
				$this->_ComaLate->SetReplacement('GROUP_CONTENT', '<span class="full-size-error">' . $this->_Translation->GetTranslation('no_users') . '</span>');
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
				$this->_ComaLate->SetReplacement('GROUP_CONTENT', $template);
			}
			
			// Language replacements
			$this->_ComaLate->SetReplacement('LANG_GROUP_NAME', $this->_Translation->GetTranslation('group_name'));
			$this->_ComaLate->SetReplacement('LANG_ADD_USER', $this->_Translation->GetTranslation('add_user'));
			$this->_ComaLate->SetReplacement('LANG_EDIT_GROUP', $this->_Translation->GetTranslation('edit_group'));
			$this->_ComaLate->SetReplacement('LANG_REMOVE_ALL_USERS', $this->_Translation->GetTranslation('remove_all_users'));
			$this->_ComaLate->SetReplacement('GROUP_ID', $GroupID);
			
			// Generate the template for the page
			$template = '
						<a href="admin.php?page=groups&amp;action=add_user&amp;group_id={GROUP_ID}" class="button">{LANG_ADD_USER}</a>
						<a href="admin.php?page=groups&amp;action=edit_group&amp;group_id={GROUP_ID}" class="button">{LANG_EDIT_GROUP}</a>
						<a href="admin.php?page=groups&amp;action=remove_all_users&amp;group_id={GROUP_ID}" class="button">{LANG_REMOVE_ALL_USERS}</a>
						<h3>{LANG_GROUP_NAME}: {GROUP_NAME}</h3>
						<span class="full-size-info">
							{GROUP_DESCRIPTION}
						</span>
						<br />
						{GROUP_CONTENT}';
			return $template;
		}
		
		/**
		 * Returns a formular to get a confirmation to remove a group from the system
		 * 
		 * @access private
		 * @return string A template for the groupformular
		 */
		function _DeleteGroup() {
			
			// Get external parameters
			$GroupID = GetPostOrGet('group_id');
			
			// Get some information about the group
			$sql = 'SELECT group_name
					FROM ' . DB_PREFIX . "groups
					WHERE group_id='{$GroupID}'";
			$result = $this->_SqlConnection->SqlQuery($sql);
			$group = mysql_fetch_object($result);
			mysql_free_result($result);
			
			// Create a new instance of the formmaker
			$formMaker = new FormMaker($this->_Translation->GetTranslation('todo'), $this->_SqlConnection);
			$formMaker->AddForm('delete_group', 'admin.php', $this->_Translation->GetTranslation('execute'), $this->_Translation->GetTranslation('delete_group'), 'post');
			
			$formMaker->AddHiddenInput('delete_group', 'page', 'groups');
			$formMaker->AddHiddenInput('delete_group', 'action', 'remove_group');
			$formMaker->AddHiddenInput('delete_group', 'group_id', $GroupID);
			
			$formMaker->AddInput('delete_group', 'confirmation', 'select', sprintf($this->_Translation->GetTranslation('delete_%group%'), $group->group_name), sprintf($this->_Translation->GetTranslation('do_you_really_want_to_delete_the_group_%group%?'), $group->group_name));
			$formMaker->AddSelectEntry('delete_group', 'confirmation', true, '0', $this->_Translation->GetTranslation('no'));
			$formMaker->AddSelectEntry('delete_group', 'confirmation', false, '1', $this->_Translation->GetTranslation('yes'));
			
			// Generate the template to correct the inputs
			$template = "\r\n\t\t\t\t" . $formMaker->GenerateSingleFormTemplate($this->_ComaLate, false);
			return $template;
		}
		
		/**
		 * Removes a group from the system and then sets the user back to the home page 
		 * 
		 * @access private
		 * @return string A template of the homepage
		 */
		function _RemoveGroup() {
			
			// Get external parameters
			$GroupID = GetPostOrGet('group_id');
			$Confirmation = GetPostOrGet('confirmation');
			
			if ($Confirmation == 1 && $GroupID != 0) {
				
				// Remove the group from the system
				$sql = 'DELETE
						FROM ' . DB_PREFIX . "groups
						WHERE group_id='{$GroupID}'";
				$this->_SqlConnection->SqlQuery($sql);
				
				// Remove the information about the groupusers
				$sql = 'DELETE
						FROM ' . DB_PREFIX . "group_users
						WHERE group_id='{$GroupID}'";
				$this->_SqlConnection->SqlQuery($sql);
			}
		}
		
		/**
		 * Adds a user to a group of the system
		 * 
		 * @access private
		 * @return string A formular to choose a user of the system
		 */
		function _AddUser() {
			
			// Get external parameters
			$GroupID = GetPostOrGet('group_id');
			$UserID = GetPostOrGet('user_id');
			
			if ($UserID != 0 && $GroupID != 0) {
				// we got a new user... check wether he is already in the group and if not add him
				$sql = 'SELECT *
						FROM ' . DB_PREFIX . "group_users
						WHERE group_id='{$GroupID}' AND user_id='{$UserID}'";
				$result = $this->_SqlConnection->SqlQuery($sql);
				
				if (mysql_fetch_object($result)) {
					
					// The user already exists in the group...
					mysql_free_result($result);
					$template = "\r\n\t\t\t\t" . $this->_ViewGroup($GroupID);
					return $template;
				}
				else {
					
					// Add the user to the group
					$sql = 'INSERT INTO ' . DB_PREFIX . "group_users
							(group_id, user_id)
							VALUES ('{$GroupID}', '{$UserID}')";
					$this->_SqlConnection->SqlQuery($sql);
					
					$template = "\r\n\t\t\t\t" . $this->_ViewGroup($GroupID);
					return $template;
				}
			}
			elseif ($GroupID != 0) {
				
				$users = array();
				
				// Get some information about the group from the database
				$sql = 'SELECT group_name
						FROM ' . DB_PREFIX . "groups
						WHERE group_id='{$GroupID}'";
				$result = $this->_SqlConnection->SqlQuery($sql);
				$group = mysql_fetch_object($result);
				mysql_free_result($result);
				
				$sql = 'SELECT user_id
						FROM ' . DB_PREFIX . "group_users
						WHERE group_id='{$GroupID}'";
				$result = $this->_SqlConnection->SqlQuery($sql);
				while ($user = mysql_fetch_object($result)) {
					
					$users[$user->user_id] = $user->user_id;
				}
				mysql_free_result($result);
				
				// Generate a formular to find a new user for the group
				$formMaker = new FormMaker($this->_Translation->GetTranslation('todo'), $this->_SqlConnection);
				$formMaker->AddForm('add_user', 'admin.php', $this->_Translation->GetTranslation('add'), $this->_Translation->GetTranslation('add_user'), 'post');
				
				$formMaker->AddHiddenInput('add_user', 'page', 'groups');
				$formMaker->AddHiddenInput('add_user', 'action', 'add_user');
				$formMaker->AddHiddenInput('add_user', 'group_id', $GroupID);
				
				$formMaker->AddInput('add_user', 'user_id', 'select', $this->_Translation->GetTranslation('user_name'), sprintf($this->_Translation->GetTranslation('please_select_the_user_you_want_to_add_to_the_group_%group%'), $group->group_name));
				
				$sql = 'SELECT user_id, user_showname
						FROM ' . DB_PREFIX . 'users';
				$result = $this->_SqlConnection->SqlQuery($sql);
				
				while ($user = mysql_fetch_object($result)) {
					
					// if the user is not in the group now, add a select entry!
					if (!array_key_exists($user->user_id, $users)) {
						
						$formMaker->AddSelectEntry('add_user', 'user_id', false, $user->user_id, $user->user_showname);
					}
				}
				mysql_free_result($result);
				
				// Generate the template to correct the inputs
				$template = "\r\n\t\t\t\t" . $formMaker->GenerateSingleFormTemplate($this->_ComaLate, false);
				return $template;
			}
			else {
				
				// Set the user back to the homepage
				$template = "\r\n\t\t\t\t" . $this->_HomePage();
				return $template;
			}
		}
		
		/**
		 * Removes a user from a group of the system
		 * 
		 * @access private
		 * @return string A template for a confirmation to remove the user
		 */
		function _RemoveUser() {
			
			// Get external parameters
			$GroupID = GetPostOrGet('group_id');
			$UserID = GetPostOrGet('user_id');
			$Confirmation = GetPostOrGet('confirmation');
			
			if ($UserID != 0 && $GroupID != 0 && $Confirmation == 1) {
				// we got a user... check wether he is really in the group and if remove him
				$sql = 'SELECT *
						FROM ' . DB_PREFIX . "group_users
						WHERE group_id='{$GroupID}' AND user_id='{$UserID}'";
				$result = $this->_SqlConnection->SqlQuery($sql);
				
				if (mysql_fetch_object($result)) {
					
					// The user really exists... so remove him
					mysql_free_result($result);
					$sql = 'DELETE
							FROM ' . DB_PREFIX . "group_users
							WHERE group_id='{$GroupID}' AND user_id='{$UserID}'";
					$this->_SqlConnection->SqlQuery($sql);
					$template = "\r\n\t\t\t\t" . $this->_ViewGroup($GroupID);
					return $template;
				}
				else {
					
					// Nothing to do... the user is not in the group...
					$template = "\r\n\t\t\t\t" . $this->_ViewGroup($GroupID);
					return $template;
				}
			}
			elseif ($GroupID != 0 && $UserID != 0) {
				
				// Get some information about the user and the group from the database
				$sql = 'SELECT user_showname
						FROM ' . DB_PREFIX . "users
						WHERE user_id='{$UserID}'";
				$result = $this->_SqlConnection->SqlQuery($sql);
				$user = mysql_fetch_object($result);
				$user = $user->user_showname;
				mysql_free_result($result);
				
				$sql = 'SELECT group_name
						FROM ' . DB_PREFIX . "groups
						WHERE group_id={$GroupID}";
				$result = $this->_SqlConnection->SqlQuery($sql);
				$group = mysql_fetch_object($result);
				$group = $group->group_name;
				mysql_free_result($result);
				
				// Generate a formular to find a new user for the group
				$formMaker = new FormMaker($this->_Translation->GetTranslation('todo'), $this->_SqlConnection);
				$formMaker->AddForm('remove_user', 'admin.php', $this->_Translation->GetTranslation('remove'), $this->_Translation->GetTranslation('remove_user'), 'post');
				
				$formMaker->AddHiddenInput('remove_user', 'page', 'groups');
				$formMaker->AddHiddenInput('remove_user', 'action', 'remove_user');
				$formMaker->AddHiddenInput('remove_user', 'group_id', $GroupID);
				$formMaker->AddHiddenInput('remove_user', 'user_id', $UserID);
				
				$formMaker->AddInput('remove_user', 'confirmation', 'select', sprintf($this->_Translation->GetTranslation('remove_%user%'), $user), sprintf($this->_Translation->GetTranslation('do_you_really_want_to_remove_the_user_%user%_from_the_group_%group%?'), $user, $group));
				$formMaker->AddSelectEntry('remove_user', 'confirmation', true, 0, $this->_Translation->GetTranslation('no'));
				$formMaker->AddSelectEntry('remove_user', 'confirmation', false, 1, $this->_Translation->GetTranslation('yes'));
				
				// Generate the template to correct the inputs
				$template = "\r\n\t\t\t\t" . $formMaker->GenerateSingleFormTemplate($this->_ComaLate, false);
				return $template;
			}
			else {
				
				// Set the user back to the homepage
				$template = "\r\n\t\t\t\t" . $this->_HomePage();
				return $template;
			}
		}
		
		/**
		 * Removes all users from a group after asking for confirmation
		 * 
		 * @access private
		 * @return string A template for the confirmation formular
		 */
		function _RemoveAllUsers() {
			
			// Get external parameters
			$GroupID = GetPostOrGet('group_id');
			$Confirmation = GetPostOrGet('confirmation');
			
			if ($GroupID != 0 && $Confirmation == 1) {
				// we got a group... check wether it got any users and if remove all of them
				$sql = 'SELECT *
						FROM ' . DB_PREFIX . "group_users
						WHERE group_id='{$GroupID}'";
				$result = $this->_SqlConnection->SqlQuery($sql);
				
				if (mysql_fetch_object($result)) {
					
					// The group got some users... remove them!
					mysql_free_result($result);
					$sql = 'DELETE
							FROM ' . DB_PREFIX . "group_users
							WHERE group_id='{$GroupID}'";
					$this->_SqlConnection->SqlQuery($sql);
					$template = "\r\n\t\t\t\t" . $this->_ViewGroup($GroupID);
					return $template;
				}
				else {
					
					// Nothing to do... there are no users in the group...
					$template = "\r\n\t\t\t\t" . $this->_ViewGroup($GroupID);
					return $template;
				}
			}
			elseif ($GroupID != 0) {
				
				// Get some information about the group
				$sql = 'SELECT group_name
						FROM ' . DB_PREFIX . "groups
						WHERE group_id={$GroupID}";
				$result = $this->_SqlConnection->SqlQuery($sql);
				$group = mysql_fetch_object($result);
				$group = $group->group_name;
				mysql_free_result($result);
				
				// Generate a formular to find a new user for the group
				$formMaker = new FormMaker($this->_Translation->GetTranslation('todo'), $this->_SqlConnection);
				$formMaker->AddForm('remove_all_users', 'admin.php', $this->_Translation->GetTranslation('remove'), $this->_Translation->GetTranslation('remove_all_users'), 'post');
				
				$formMaker->AddHiddenInput('remove_all_users', 'page', 'groups');
				$formMaker->AddHiddenInput('remove_all_users', 'action', 'remove_all_users');
				$formMaker->AddHiddenInput('remove_all_users', 'group_id', $GroupID);
				
				$formMaker->AddInput('remove_all_users', 'confirmation', 'select', $this->_Translation->GetTranslation('remove_users'), sprintf($this->_Translation->GetTranslation('do_you_really_want_to_remove_all_users_from_the_group_%group%?'), $group));
				$formMaker->AddSelectEntry('remove_all_users', 'confirmation', true, 0, $this->_Translation->GetTranslation('no'));
				$formMaker->AddSelectEntry('remove_all_users', 'confirmation', false, 1, $this->_Translation->GetTranslation('yes'));
				
				// Generate the template to correct the inputs
				$template = "\r\n\t\t\t\t" . $formMaker->GenerateSingleFormTemplate($this->_ComaLate, false);
				return $template;
			}
			else {
				
				// Set the user back to the homepage
				$template = "\r\n\t\t\t\t" . $this->_HomePage();
				return $template;
			}
		}
	}
?>