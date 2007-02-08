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
	require_once __ROOT__ . '/lib/formmaker/formmaker.class.php';
	
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
			
			$template = '<h2>{LANG_USERMANAGEMENT}</h2>';
			$this->_ComaLate->SetReplacement('LANG_USERMANAGEMENT', $this->_Translation->GetTranslation('usermanagement'));
			
			// Switch between the subpages of the usermanagement
			switch ($Action) {
				
				case 'edit_user':
					// Returns a formtemplate to edit an existing user
					$template .= $this->_EditUser();
					break;
				
				case 'check_user':
					// Returns a formtemplate to check the inputs
					$template .= $this->_CheckUser();
					break;
				
				case 'new_user':
					// Returns a formtemplate to add a new user to the system
					$template .= $this->_NewUser();
					break;
					
				case 'add_user':
					// Returns a formtemplate to check the userdata
					$template .= $this->_AddUser();
					break;
				
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
			
			// Fetch all users data and save them to the array
			while ($user = mysql_fetch_object($usersResult)) {
				
				// Add the next user to the array
				$users[] = array(   'USER_ID' => $user->user_id,
									'USER_SHOWNAME' => $user->user_showname,
									'USER_NICKNAME' => $user->user_name,
									'USER_EMAIL' => $user->user_email,
									'USER_ADMIN' => (($user->user_admin == 1) ? $this->_Translation->GetTranslation('yes') : $this->_Translation->GetTranslation('no')),
									'USER_AUTHOR' => (($user->user_author == 1) ? $this->_Translation->GetTranslation('yes') : $this->_Translation->GetTranslation('no')),
									'USER_ACTIONS' => array(
										0 => array('ACTION' => 'edit_user', 'ACTION_IMG' => './img/edit.png', 'ACTION_TITLE' => $this->_Translation->GetTranslation('edit')),
										1 => array('ACTION' => 'delete_user', 'ACTION_IMG' => './img/del.png', 'ACTION_TITLE' => $this->_Translation->GetTranslation('delete'))
										)
									);
			}
			mysql_free_result($usersResult);
			$this->_ComaLate->SetReplacement('USERS', $users);
			
			// Get the existing custom fields from the database
			$sql = "SELECT *
					FROM " . DB_PREFIX . "custom_fields";
			$customFieldsResult = $this->_SqlConnection->SqlQuery($sql);
			
			// Initialize fields array
			$customFields = array();
			
			// Fetch all field data and save them to the array
			while ($customField = mysql_fetch_object($customFieldsResult)) {
				$customFields[] = array('CUSTOM_FIELDS_FIELD_NAME' => $customField->custom_fields_name,
										'CUSTOM_FIELDS_FIELD_TITLE' => $customField->custom_fields_title,
										'CUSTOM_FIELDS_FIELD_TYPE' => $customField->custom_fields_type,
										'CUSTOM_FIELDS_FIELD_SIZE' => $customField->custom_fields_size,
										'CUSTOM_FIELDS_SHOW_AT_REGISTRATION' => (($customField->custom_fields_show_at_registration == 1) ? $this->_Translation->GetTranslation('yes') : $this->_Translation->GetTranslation('no')),
										'CUSTOM_FIELDS_REQUIRED' => (($customField->custom_fields_required == 1) ? $this->_Translation->GetTranslation('yes') : $this->_Translation->GetTranslation('no')),
										'CUSTOM_FIELDS_ACTIONS' => array(
											0 => array('ACTION' => 'edit_custom_field', 'ACTION_IMG' => './img/edit.png', 'ACTION_TITLE' => $this->_Translation->GetTranslation('edit')),
											1 => array('ACTION' => 'view_custom_field', 'ACTION_IMG' => './img/info.png', 'ACTION_TITLE' => $this->_Translation->GetTranslation('info')),
											2 => array('ACTION' => 'move_custom_field_down', 'ACTION_IMG' => './img/down.png', 'ACTION_TITLE' => $this->_Translation->GetTranslation('move_down')),
											3 => array('ACTION' => 'move_custom_field_up', 'ACTION_IMG' => './img/up.png', 'ACTION_TITLE' => $this->_Translation->GetTranslation('move_up')),
											4 => array('ACTION' => 'delete_custom_field', 'ACTION_IMG' => './img/del.png', 'ACTION_TITLE' => $this->_Translation->GetTranslation('delete'))
											)
										);
			}
			mysql_free_result($customFieldsResult);
			$this->_ComaLate->SetReplacement('CUSTOM_FIELDS', $customFields);
			
			// Set replacements for language
			$this->_ComaLate->SetReplacement('LANG_SHOWNAME', $this->_Translation->GetTranslation('showname'));
			$this->_ComaLate->SetReplacement('LANG_NICKNAME', $this->_Translation->GetTranslation('nickname'));
			$this->_ComaLate->SetReplacement('LANG_EMAIL', $this->_Translation->GetTranslation('email'));
			$this->_ComaLate->SetReplacement('LANG_ADMIN', $this->_Translation->GetTranslation('admin'));
			$this->_ComaLate->SetReplacement('LANG_AUTHOR', $this->_Translation->GetTranslation('author'));
			$this->_ComaLate->SetReplacement('LANG_ACTIONS', $this->_Translation->GetTranslation('actions'));
			$this->_ComaLate->SetReplacement('LANG_CREATE_NEW_USER', $this->_Translation->GetTranslation('create_new_user'));
			
			$this->_ComaLate->SetReplacement('LANG_CUSTOM_FIELDS', $this->_Translation->GetTranslation('custom_fields'));
			$this->_ComaLate->SetReplacement('LANG_CREATE_NEW_CUSTOM_FIELD', $this->_Translation->GetTranslation('create_new_custom_field'));
			$this->_ComaLate->SetReplacement('LANG_FIELD_NAME', $this->_Translation->GetTranslation('name'));
			$this->_ComaLate->SetReplacement('LANG_FIELD_TITLE', $this->_Translation->GetTranslation('title'));
			$this->_ComaLate->SetReplacement('LANG_FIELD_TYPE', $this->_Translation->GetTranslation('type'));
			$this->_ComaLate->SetReplacement('LANG_SIZE', $this->_Translation->GetTranslation('size'));
			$this->_ComaLate->SetReplacement('LANG_SHOW_AT_REGISTRATION', $this->_Translation->GetTranslation('show_at_registration'));
			$this->_ComaLate->SetReplacement('LANG_REQUIRED', $this->_Translation->GetTranslation('required'));
			
			
			// Generate the template
			$template = '
				<a href="admin.php?page=users&amp;action=new_user" class="button">{LANG_CREATE_NEW_USER}</a>
				<table class="full_width">
					<tr>
						<th>{LANG_SHOWNAME}</th>
						<th>{LANG_NICKNAME}</th>
						<th>{LANG_EMAIL}</th>
						<th>{LANG_ADMIN}</th>
						<th>{LANG_AUTHOR}</th>
						<th>{LANG_ACTIONS}</th>
					</tr>
				<USERS:loop>
					<tr>
						<td>{USER_SHOWNAME}</td>
						<td>{USER_NICKNAME}</td>
						<td>{USER_EMAIL}</td>
						<td>{USER_ADMIN}</td>
						<td>{USER_AUTHOR}</td>
						<td><USER_ACTIONS:loop><a href="admin.php?page=users&amp;action={ACTION}&amp;user_id={USER_ID}"><img src="{ACTION_IMG}" height="16" width="16" alt="{ACTION_TITLE}" title="{ACTION_TITLE}" /></a>&nbsp;</USER_ACTIONS></td>
					</tr>
				</USERS>
				</table>
				
				<h2>{LANG_CUSTOM_FIELDS}</h2>
				<a href="admin.php?page=users&amp;action=new_custom_field" class="button">{LANG_CREATE_NEW_CUSTOM_FIELD}</a>
				<table class="full_width">
					<tr>
						<th>{LANG_FIELD_NAME}</th>
						<th>{LANG_FIELD_TITLE}</th>
						<th>{LANG_FIELD_TYPE}</th>
						<th>{LANG_SIZE}</th>
						<th>{LANG_SHOW_AT_REGISTRATION}</th>
						<th>{LANG_REQUIRED}</th>
						<th>{LANG_ACTIONS}</th>
					</tr>
				<CUSTOM_FIELDS:loop>
					<tr>
						<td>{CUSTOM_FIELDS_FIELD_NAME}</td>
						<td>{CUSTOM_FIELDS_FIELD_TITLE}</td>
						<td>{CUSTOM_FIELDS_FIELD_TYPE}</td>
						<td>{CUSTOM_FIELDS_FIELD_SIZE}</td>
						<td>{CUSTOM_FIELDS_SHOW_AT_REGISTRATION}</td>
						<td>{CUSTOM_FIELDS_REQUIRED}</td>
						<td><CUSTOM_FIELDS_ACTIONS:loop><a href="admin.php?page=users&amp;action={ACTION}&amp;user_id={USER_ID}"><img src="{ACTION_IMG}" height="16" width="16" alt="{ACTION_TITLE}" title="{ACTION_TITLE}" /></a>&nbsp;</CUSTOM_FIELDS_ACTIONS></td>
					</tr>
				</CUSTOM_FIELDS>
				</table>';
			return $template;
		}
		
		/**
		 * Edits a user in the system
		 * @access private
		 * @return string A template for a form to edit a user
		 */
		function _EditUser() {
			
			// Get external parameters
			$UserID = GetPostOrGet('user_id');
			
			// Get the data of the user that should be edited
			$sql = "SELECT *
					FROM " . DB_PREFIX . "users
					WHERE user_id={$UserID}";
			$userResult = $this->_SqlConnection->SqlQuery($sql);
			
			// If there is a user found
			if (mysql_num_rows($userResult) == 1) {
				
				$user = mysql_fetch_object($userResult);
				mysql_free_result($userResult);
				
				// Initialize the formmaker class
				$formMaker = new FormMaker($this->_Translation->GetTranslation('todo'), $this->_SqlConnection);
				$formMaker->AddForm('edit_user', 'admin.php', $this->_Translation->GetTranslation('save'), $this->_Translation->GetTranslation('user'), 'post');
				
				$formMaker->AddHiddenInput('edit_user', 'page', 'users');
				$formMaker->AddHiddenInput('edit_user', 'action', 'check_user');
				$formMaker->AddHiddenInput('edit_user', 'user_id', $UserID);
				
				$formMaker->AddInput('edit_user', 'user_showname', 'text', $this->_Translation->GetTranslation('showname'), $this->_Translation->GetTranslation('the_name_that_is_displayed_if_the_user_writes_a_news_for_example'), $user->user_showname);
				$formMaker->AddInput('edit_user', 'user_name', 'text', $this->_Translation->GetTranslation('nickname'), $this->_Translation->GetTranslation('with_this_nick_the_user_can_login_so_he_must_not_fill_in_his_long_name'), $user->user_name);
				$formMaker->AddInput('edit_user', 'user_email', 'text', $this->_Translation->GetTranslation('email'), $this->_Translation->GetTranslation('using_the_email_address_the_user_is_contacted_by_the_system'), $user->user_email);
				$formMaker->AddInput('edit_user', 'user_password', 'password', $this->_Translation->GetTranslation('password'), $this->_Translation->GetTranslation('with_this_password_the_user_can_login_to_restricted_areas'));
				$formMaker->AddInput('edit_user', 'user_password_repetition', 'password', $this->_Translation->GetTranslation('password_repetition'), $this->_Translation->GetTranslation('it_is_guaranteed_by_a_repetition_that_the_user_did_not_mistype_during_the_input'));
				$formMaker->AddInput('edit_user', 'user_admin', 'checkbox', $this->_Translation->GetTranslation('admin'), $this->_Translation->GetTranslation('if_an_user_is_an_administrator_he_has_access_to_the_system_configuration_**choose_only_if_realy_necessary**'), (($user->user_admin == 1) ? true : false));
				$formMaker->AddInput('edit_user', 'user_author', 'checkbox', $this->_Translation->GetTranslation('author'), $this->_Translation->GetTranslation('if_an_user_is_an_author_he_has_access_to_the_page_management_and_the_menu_editor'), (($user->user_author == 1) ? true : false));
				
				// Generate the template
				$template = "\r\n\t\t\t\t" . $formMaker->GenerateMultiFormTemplate(&$this->_ComaLate, false);
				return $template;
			}
		}
		
		/**
		 * Checks the inputs of the editformular and returns a form showing the errors if there are any
		 * @access private
		 * @return string Template for the Errorform
		 */
		function _CheckUser() {
			
			// Get external parameters
			$UserID = GetPostOrGet('user_id');
			
			// Get the data of the user that should be edited
			$sql = "SELECT *
					FROM " . DB_PREFIX . "users
					WHERE user_id={$UserID}";
			$userResult = $this->_SqlConnection->SqlQuery($sql);
			
			// If there is a user found
			if (mysql_num_rows($userResult) == 1) {
				
				$user = mysql_fetch_object($userResult);
				mysql_free_result($userResult);
				
				// Get the rest of the external parameters
				$UserShowname = GetPostOrGet('user_showname');
				$UserName = GetPostOrGet('user_name');
				$UserEmail = GetPostOrGet('user_email');
				$UserPassword = GetPostOrGet('user_password');
				$UserPasswordRepetition = GetPostOrGet('user_password_repetition');
				$UserAdmin = ((GetPostOrGet('user_admin') == 'on') ? 1 : 0);
				$UserAuthor = ((GetPostOrGet('user_author') == 'on') ? 1 : 0);
				
				// Check wether anything was changed in the userdetailes
				if (($UserShowname != $user->user_showname) || ($UserName != $user->user_name) || ($UserEmail != $user->user_email) || (!empty($UserPassword)) || (!empty($UserPasswordRepetition)) || ($UserAdmin != $user->user_admin) || ($UserAuthor != $user->user_author)) {
					
					// Initialize the formmaker class
					$formMaker = new FormMaker($this->_Translation->GetTranslation('todo'), $this->_SqlConnection);
					$formMaker->AddForm('edit_user', 'admin.php', $this->_Translation->GetTranslation('save'), $this->_Translation->GetTranslation('user'), 'post');
					
					$formMaker->AddHiddenInput('edit_user', 'page', 'users');
					$formMaker->AddHiddenInput('edit_user', 'action', 'check_user');
					$formMaker->AddHiddenInput('edit_user', 'user_id', $UserID);
					
					$formMaker->AddInput('edit_user', 'user_showname', 'text', $this->_Translation->GetTranslation('showname'), $this->_Translation->GetTranslation('the_name_that_is_displayed_if_the_user_writes_a_news_for_example'), $UserShowname);
					$formMaker->AddCheck('edit_user', 'user_showname', 'empty', $this->_Translation->GetTranslation('the_nickname_must_be_indicated'));
					if ($user->user_showname != $UserShowname)
						$formMaker->AddCheck('edit_user', 'user_showname', 'already_assigned', $this->_Translation->GetTranslation('the_name_is_already_assigned'), '', 'users', 'user_showname');
					
					$formMaker->AddInput('edit_user', 'user_name', 'text', $this->_Translation->GetTranslation('nickname'), $this->_Translation->GetTranslation('with_this_nick_the_user_can_login_so_he_must_not_fill_in_his_long_name'), $UserName);
					$formMaker->AddCheck('edit_user', 'user_name', 'empty', $this->_Translation->GetTranslation('the_nickname_must_be_indicated'));
					if ($user->user_name != $UserName)
						$formMaker->AddCheck('edit_user', 'user_name', 'already_assigned', $this->_Translation->GetTranslation('the_nickname_is_already_assigned'), '', 'users', 'user_name');
					
					$formMaker->AddInput('edit_user', 'user_email', 'text', $this->_Translation->GetTranslation('email'), $this->_Translation->GetTranslation('using_the_email_address_the_user_is_contacted_by_the_system'), $UserEmail);
					$formMaker->AddCheck('edit_user', 'user_email', 'empty', $this->_Translation->GetTranslation('the_email_address_must_be_indicated'));
					$formMaker->AddCheck('edit_user', 'user_email', 'not_email', $this->_Translation->GetTranslation('this_is_not_a_valid_email_address'));
					if ($user->user_email != $UserEmail)
						$formMaker->AddCheck('edit_user', 'user_email', 'already_assigned', $this->_Translation->GetTranslation('the_email_is_already_assigned_to_another_user'), '', 'users', 'user_email');
					
					$formMaker->AddInput('edit_user', 'user_password', 'password', $this->_Translation->GetTranslation('password'), $this->_Translation->GetTranslation('with_this_password_the_user_can_login_to_restricted_areas'), ((!empty($UserPassword)) ? $UserPassword : ''));
					$formMaker->AddInput('edit_user', 'user_password_repetition', 'password', $this->_Translation->GetTranslation('password_repetition'), $this->_Translation->GetTranslation('it_is_guaranteed_by_a_repetition_that_the_user_did_not_mistype_during_the_input'), ((!empty($UserPasswordRepetition)) ? $UserPasswordRepetition : ''));
					
					if (!empty($UserPassword) || !empty($UserPasswordRepetition)) {
						$formMaker->AddCheck('edit_user', 'user_password', 'empty', $this->_Translation->GetTranslation('the_password_field_must_not_be_empty'));
						$formMaker->AddCheck('edit_user', 'user_password', 'not_same_password_value_as', $this->_Translation->GetTranslation('the_password_and_its_repetition_are_unequal'), 'user_password_repetition');
						
						$formMaker->AddCheck('edit_user', 'user_password_repetition', 'empty', $this->_Translation->GetTranslation('the_password_field_must_not_be_empty'));
					}
					
					$formMaker->AddInput('add_user', 'user_admin', 'checkbox', $this->_Translation->GetTranslation('admin'), $this->_Translation->GetTranslation('if_an_user_is_an_administrator_he_has_access_to_the_system_configuration_**choose_only_if_realy_necessary**'), (($UserAdmin == 1) ? true : false));
					$formMaker->AddInput('add_user', 'user_author', 'checkbox', $this->_Translation->GetTranslation('author'), $this->_Translation->GetTranslation('if_an_user_is_an_author_he_has_access_to_the_page_management_and_the_menu_editor'), (($UserAuthor == 1) ? true : false));
					
					if ($formMaker->CheckInputs('edit_user', true)) {
						
						$user_password = ((!empty($UserPassword)) ? ", user_password='" . md5($UserPassword) . "'": '');
						// Update the user in the database
						$sql = "UPDATE " . DB_PREFIX . "users
								SET user_showname='$UserShowname', user_name='$UserName', user_email='$UserEmail', user_admin='$UserAdmin', user_author='$UserAuthor'$user_password
								WHERE user_id=$UserID";
						$this->_SqlConnection->SqlQuery($sql);
						
						// Send the user the HomePage of the usermanager
						$template = $this->_HomePage();
						return $template;
					}
					else {
						
						// Generate the template
						$template = "\r\n\t\t\t\t" . $formMaker->GenerateMultiFormTemplate(&$this->_ComaLate, false);
						return $template;
					}
				}
				else {
					
					// Send the user the HomePage of the usermanager
					$template = $this->_HomePage();
					return $template;
				}
			}
		}
		
		/**
		 * Add a new user to the system
		 * @access private
		 * @return string A formtemplate to add a new user to the system
		 */
		function _NewUser() {
			
			// Initialize the formmaker class
			$formMaker = new FormMaker($this->_Translation->GetTranslation('todo'), $this->_SqlConnection);
			$formMaker->AddForm('new_user', 'admin.php', $this->_Translation->GetTranslation('save'), $this->_Translation->GetTranslation('user'), 'post');
			
			$formMaker->AddHiddenInput('new_user', 'page', 'users');
			$formMaker->AddHiddenInput('new_user', 'action', 'add_user');
			
			$formMaker->AddInput('new_user', 'user_showname', 'text', $this->_Translation->GetTranslation('showname'), $this->_Translation->GetTranslation('the_name_that_is_displayed_if_the_user_writes_a_news_for_example'));
			$formMaker->AddInput('new_user', 'user_name', 'text', $this->_Translation->GetTranslation('nickname'), $this->_Translation->GetTranslation('with_this_nick_the_user_can_login_so_he_must_not_fill_in_his_long_name'));
			$formMaker->AddInput('new_user', 'user_email', 'text', $this->_Translation->GetTranslation('email'), $this->_Translation->GetTranslation('using_the_email_address_the_user_is_contacted_by_the_system'));
			$formMaker->AddInput('new_user', 'user_password', 'password', $this->_Translation->GetTranslation('password'), $this->_Translation->GetTranslation('with_this_password_the_user_can_login_to_restricted_areas'));
			$formMaker->AddInput('new_user', 'user_password_repetition', 'password', $this->_Translation->GetTranslation('password_repetition'), $this->_Translation->GetTranslation('it_is_guaranteed_by_a_repetition_that_the_user_did_not_mistype_during_the_input'));
			$formMaker->AddInput('new_user', 'user_admin', 'checkbox', $this->_Translation->GetTranslation('admin'), $this->_Translation->GetTranslation('if_an_user_is_an_administrator_he_has_access_to_the_system_configuration_**choose_only_if_realy_necessary**'));
			$formMaker->AddInput('new_user', 'user_author', 'checkbox', $this->_Translation->GetTranslation('author'), $this->_Translation->GetTranslation('if_an_user_is_an_author_he_has_access_to_the_page_management_and_the_menu_editor'));
			
			// Generate the template
			$template = "\r\n\t\t\t\t" . $formMaker->GenerateMultiFormTemplate(&$this->_ComaLate, false);
			return $template;
		}
		
		/**
		 * Checks the inputs of the editformular and returns a form showing the errors if there are any
		 * @access private
		 * @return string Template for the Errorform
		 */
		function _AddUser() {
			
			// Get the rest of the external parameters
			$UserShowname = GetPostOrGet('user_showname');
			$UserName = GetPostOrGet('user_name');
			$UserEmail = GetPostOrGet('user_email');
			$UserPassword = GetPostOrGet('user_password');
			$UserPasswordRepetition = GetPostOrGet('user_password_repetition');
			$UserAdmin = ((GetPostOrGet('user_admin') == 'on') ? 1 : 0);
			$UserAuthor = ((GetPostOrGet('user_author') == 'on') ? 1 : 0);
			
			// Initialize the formmaker class
			$formMaker = new FormMaker($this->_Translation->GetTranslation('todo'), $this->_SqlConnection);
			$formMaker->AddForm('add_user', 'admin.php', $this->_Translation->GetTranslation('save'), $this->_Translation->GetTranslation('user'), 'post');
			
			$formMaker->AddHiddenInput('add_user', 'page', 'users');
			$formMaker->AddHiddenInput('add_user', 'action', 'add_user');
			
			$formMaker->AddInput('add_user', 'user_showname', 'text', $this->_Translation->GetTranslation('showname'), $this->_Translation->GetTranslation('the_name_that_is_displayed_if_the_user_writes_a_news_for_example'), $UserShowname);
			$formMaker->AddCheck('add_user', 'user_showname', 'empty', $this->_Translation->GetTranslation('the_nickname_must_be_indicated'));
			$formMaker->AddCheck('add_user', 'user_showname', 'already_assigned', $this->_Translation->GetTranslation('the_name_is_already_assigned'), '', 'users', 'user_showname');
			
			$formMaker->AddInput('add_user', 'user_name', 'text', $this->_Translation->GetTranslation('nickname'), $this->_Translation->GetTranslation('with_this_nick_the_user_can_login_so_he_must_not_fill_in_his_long_name'), $UserName);
			$formMaker->AddCheck('add_user', 'user_name', 'empty', $this->_Translation->GetTranslation('the_nickname_must_be_indicated'));
			$formMaker->AddCheck('add_user', 'user_name', 'already_assigned', $this->_Translation->GetTranslation('the_nickname_is_already_assigned'), '', 'users', 'user_name');
			
			$formMaker->AddInput('add_user', 'user_email', 'text', $this->_Translation->GetTranslation('email'), $this->_Translation->GetTranslation('using_the_email_address_the_user_is_contacted_by_the_system'), $UserEmail);
			$formMaker->AddCheck('add_user', 'user_email', 'empty', $this->_Translation->GetTranslation('the_email_address_must_be_indicated'));
			$formMaker->AddCheck('add_user', 'user_email', 'not_email', $this->_Translation->GetTranslation('this_is_not_a_valid_email_address'));
			$formMaker->AddCheck('add_user', 'user_email', 'already_assigned', $this->_Translation->GetTranslation('the_email_is_already_assigned_to_another_user'), '', 'users', 'user_email');
			
			$formMaker->AddInput('add_user', 'user_password', 'password', $this->_Translation->GetTranslation('password'), $this->_Translation->GetTranslation('with_this_password_the_user_can_login_to_restricted_areas'), $UserPassword);
			$formMaker->AddInput('add_user', 'user_password_repetition', 'password', $this->_Translation->GetTranslation('password_repetition'), $this->_Translation->GetTranslation('it_is_guaranteed_by_a_repetition_that_the_user_did_not_mistype_during_the_input'), $UserPasswordRepetition);
			$formMaker->AddCheck('add_user', 'user_password', 'empty', $this->_Translation->GetTranslation('the_password_field_must_not_be_empty'));
			$formMaker->AddCheck('add_user', 'user_password', 'not_same_password_value_as', $this->_Translation->GetTranslation('the_password_and_its_repetition_are_unequal'), 'user_password_repetition');
			$formMaker->AddCheck('add_user', 'user_password_repetition', 'empty', $this->_Translation->GetTranslation('the_password_field_must_not_be_empty'));
			
			$formMaker->AddInput('add_user', 'user_admin', 'checkbox', $this->_Translation->GetTranslation('admin'), $this->_Translation->GetTranslation('if_an_user_is_an_administrator_he_has_access_to_the_system_configuration_**choose_only_if_realy_necessary**'), (($UserAdmin == 1) ? true : false));
			$formMaker->AddInput('add_user', 'user_author', 'checkbox', $this->_Translation->GetTranslation('author'), $this->_Translation->GetTranslation('if_an_user_is_an_author_he_has_access_to_the_page_management_and_the_menu_editor'), (($UserAuthor == 1) ? true : false));
			
			if ($formMaker->CheckInputs('add_user', true)) {
				
				// Encrypt userpassword
				$user_password = md5($UserPassword);
				
				// Add new user to the database
				$sql = "INSERT INTO " . DB_PREFIX . "users
						(user_showname, user_name, user_email, user_password, user_registerdate, user_admin, user_author)
						VALUES ('$UserShowname', '$UserName', '$UserEmail', '$UserPassword', '" . mktime() . "', '$UserAdmin', '$UserAuthor')";
				$this->_SqlConnection->SqlQuery($sql);
					
				// Set user to the HomePage of the usermanager
				$template = "\r\n\t\t\t\t" . $this->_HomePage();
				return $template;
			}
			else {
				
				// Generate to edit the errors
				$template = "\r\n\t\t\t\t" . $formMaker->GenerateMultiFormTemplate(&$this->_ComaLate, true);
				return $template;
			}
		}
	}
?>
