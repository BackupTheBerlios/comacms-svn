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
				
				case 'new_user':
					// Returns a formtemplate to add a new user to the system
					$template .= $this->_NewUser();
					break;
					
				case 'add_user':
					// Returns a formtemplate to check the userdata
					$template .= $this->_AddUser();
					break;
				
				case 'edit_user':
					// Returns a formtemplate to edit an existing user
					$template .= $this->_EditUser();
					break;
				
				case 'check_user':
					// Returns a formtemplate to check the inputs
					$template .= $this->_CheckUser();
					break;
				
				case 'delete_user':
					// Returns a question if the user shall really be deleted
					$template .= $this->_DeleteUser();
					break;
				
				case 'remove_user':
					// Removes a user definitly from the database
					$this->_RemoveUser();
					$template .= $this->_HomePage();
					break;
				
				case 'new_custom_field':
					// Returns a template to add a new custom field
					$template .= $this->_NewCustomField();
					break;
				
				case 'add_custom_field':
					// Adds a new custom field to the database and returns a form to correct errors if there are any
					$template .= $this->_AddCustomField();
					break;
				
				case 'edit_custom_field':
					// Returns a formular to edit an existing custom field
					$template .= $this->_EditCustomField();
					break;
				
				case 'check_custom_field':
					// Returns a formular to correct any errors between the inputs
					$template .= $this->_CheckCustomField();
					break;
				
				case 'view_custom_field':
					// Returns a template to view a special custom field
					$template .= $this->_ViewCustomField();
					break;
				
				case 'move_custom_field_up':
					// Moves a custom field one step up
					$this->_MoveCustomFieldUp();
					$template .= $this->_HomePage();
					break;
				
				case 'move_custom_field_down':
					// Moves a custom field one step down
					$this->_MoveCustomFieldDown();
					$template .= $this->_HomePage();
					break;
				
				case 'delete_custom_field':
					// Returns a question wether a custom field shall really be deleted
					$template .= $this->_DeleteCustomField();
					break;
					
				case 'remove_custom_field':
					// Removes a custom field definitly from the database
					$this->_RemoveCustomField();
					$template .= $this->_HomePage();
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
					FROM " . DB_PREFIX . "custom_fields
					ORDER BY custom_fields_orderid ASC";
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
											0 => array('FIELD_ID' => $customField->custom_fields_id, 'ACTION' => 'edit_custom_field', 'ACTION_IMG' => './img/edit.png', 'ACTION_TITLE' => $this->_Translation->GetTranslation('edit')),
											1 => array('FIELD_ID' => $customField->custom_fields_id, 'ACTION' => 'view_custom_field', 'ACTION_IMG' => './img/info.png', 'ACTION_TITLE' => $this->_Translation->GetTranslation('info')),
											2 => array('FIELD_ID' => $customField->custom_fields_orderid, 'ACTION' => 'move_custom_field_down', 'ACTION_IMG' => './img/down.png', 'ACTION_TITLE' => $this->_Translation->GetTranslation('move_down')),
											3 => array('FIELD_ID' => $customField->custom_fields_orderid, 'ACTION' => 'move_custom_field_up', 'ACTION_IMG' => './img/up.png', 'ACTION_TITLE' => $this->_Translation->GetTranslation('move_up')),
											4 => array('FIELD_ID' => $customField->custom_fields_id, 'ACTION' => 'delete_custom_field', 'ACTION_IMG' => './img/del.png', 'ACTION_TITLE' => $this->_Translation->GetTranslation('delete'))
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
						<td><CUSTOM_FIELDS_ACTIONS:loop><a href="admin.php?page=users&amp;action={ACTION}&amp;field_id={FIELD_ID}"><img src="{ACTION_IMG}" height="16" width="16" alt="{ACTION_TITLE}" title="{ACTION_TITLE}" /></a>&nbsp;</CUSTOM_FIELDS_ACTIONS></td>
					</tr>
				</CUSTOM_FIELDS>
				</table>';
			return $template;
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
			
			// Get custom fields
				$sql = "SELECT custom_fields_information, custom_fields_name, custom_fields_title, custom_fields_type, custom_fields_required
					FROM " . DB_PREFIX . "custom_fields field";
				$customFieldsDataResult = $this->_SqlConnection->SqlQuery($sql);
				
				while ($customFieldsData = mysql_fetch_object($customFieldsDataResult)) {
					
					// Add input to the formmaker class
					$formMaker->AddInput('edit_user', $customFieldsData->custom_fields_name, 'text', $customFieldsData->custom_fields_title, $customFieldsData->custom_fields_information, $customFieldsData->custom_fields_values_value);
				}
			
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
				if ($this->_User->ID != $UserID) {
					$formMaker->AddInput('edit_user', 'user_admin', 'checkbox', $this->_Translation->GetTranslation('admin'), $this->_Translation->GetTranslation('if_an_user_is_an_administrator_he_has_access_to_the_system_configuration_**choose_only_if_realy_necessary**'), (($user->user_admin == 1) ? true : false));
					$formMaker->AddInput('edit_user', 'user_author', 'checkbox', $this->_Translation->GetTranslation('author'), $this->_Translation->GetTranslation('if_an_user_is_an_author_he_has_access_to_the_page_management_and_the_menu_editor'), (($user->user_author == 1) ? true : false));
				}
				
				// Get custom fields
				$sql = "SELECT value.custom_fields_values_value, field.custom_fields_information, field.custom_fields_name, field.custom_fields_title, field.custom_fields_type, field.custom_fields_required
					FROM (" . DB_PREFIX . "custom_fields field
					LEFT JOIN " . DB_PREFIX . "custom_fields_values value
					ON field.custom_fields_id = value.custom_fields_values_fieldid)
					WHERE value.custom_fields_values_userid='{$UserID}'
					OR value.custom_fields_values_userid IS NULL";
				$customFieldsDataResult = $this->_SqlConnection->SqlQuery($sql);
				
				while ($customFieldsData = mysql_fetch_object($customFieldsDataResult)) {
					
					// Add input to the formmaker class
					$formMaker->AddInput('edit_user', $customFieldsData->custom_fields_name, 'text', $customFieldsData->custom_fields_title, $customFieldsData->custom_fields_information, $customFieldsData->custom_fields_values_value);
				}
				
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
					$formMaker->AddForm('check_user', 'admin.php', $this->_Translation->GetTranslation('save'), $this->_Translation->GetTranslation('user'), 'post');
					
					$formMaker->AddHiddenInput('check_user', 'page', 'users');
					$formMaker->AddHiddenInput('check_user', 'action', 'check_user');
					$formMaker->AddHiddenInput('check_user', 'user_id', $UserID);
					
					$formMaker->AddInput('check_user', 'user_showname', 'text', $this->_Translation->GetTranslation('showname'), $this->_Translation->GetTranslation('the_name_that_is_displayed_if_the_user_writes_a_news_for_example'), $UserShowname);
					$formMaker->AddCheck('check_user', 'user_showname', 'empty', $this->_Translation->GetTranslation('the_nickname_must_be_indicated'));
					if ($user->user_showname != $UserShowname)
						$formMaker->AddCheck('check_user', 'user_showname', 'already_assigned', $this->_Translation->GetTranslation('the_name_is_already_assigned'), '', 'users', 'user_showname');
					
					$formMaker->AddInput('check_user', 'user_name', 'text', $this->_Translation->GetTranslation('nickname'), $this->_Translation->GetTranslation('with_this_nick_the_user_can_login_so_he_must_not_fill_in_his_long_name'), $UserName);
					$formMaker->AddCheck('check_user', 'user_name', 'empty', $this->_Translation->GetTranslation('the_nickname_must_be_indicated'));
					if ($user->user_name != $UserName)
						$formMaker->AddCheck('check_user', 'user_name', 'already_assigned', $this->_Translation->GetTranslation('the_nickname_is_already_assigned'), '', 'users', 'user_name');
					
					$formMaker->AddInput('check_user', 'user_email', 'text', $this->_Translation->GetTranslation('email'), $this->_Translation->GetTranslation('using_the_email_address_the_user_is_contacted_by_the_system'), $UserEmail);
					$formMaker->AddCheck('check_user', 'user_email', 'empty', $this->_Translation->GetTranslation('the_email_address_must_be_indicated'));
					$formMaker->AddCheck('check_user', 'user_email', 'not_email', $this->_Translation->GetTranslation('this_is_not_a_valid_email_address'));
					if ($user->user_email != $UserEmail)
						$formMaker->AddCheck('check_user', 'user_email', 'already_assigned', $this->_Translation->GetTranslation('the_email_is_already_assigned_to_another_user'), '', 'users', 'user_email');
					
					$formMaker->AddInput('check_user', 'user_password', 'password', $this->_Translation->GetTranslation('password'), $this->_Translation->GetTranslation('with_this_password_the_user_can_login_to_restricted_areas'), ((!empty($UserPassword)) ? $UserPassword : ''));
					$formMaker->AddInput('check_user', 'user_password_repetition', 'password', $this->_Translation->GetTranslation('password_repetition'), $this->_Translation->GetTranslation('it_is_guaranteed_by_a_repetition_that_the_user_did_not_mistype_during_the_input'), ((!empty($UserPasswordRepetition)) ? $UserPasswordRepetition : ''));
					
					if (!empty($UserPassword) || !empty($UserPasswordRepetition)) {
						$formMaker->AddCheck('check_user', 'user_password', 'empty', $this->_Translation->GetTranslation('the_password_field_must_not_be_empty'));
						$formMaker->AddCheck('check_user', 'user_password', 'not_same_password_value_as', $this->_Translation->GetTranslation('the_password_and_its_repetition_are_unequal'), 'user_password_repetition');
						
						$formMaker->AddCheck('check_user', 'user_password_repetition', 'empty', $this->_Translation->GetTranslation('the_password_field_must_not_be_empty'));
					}
					
					if ($this->_User->ID != $UserID) {
						$formMaker->AddInput('check_user', 'user_admin', 'checkbox', $this->_Translation->GetTranslation('admin'), $this->_Translation->GetTranslation('if_an_user_is_an_administrator_he_has_access_to_the_system_configuration_**choose_only_if_realy_necessary**'), (($UserAdmin == 1) ? true : false));
						$formMaker->AddInput('check_user', 'user_author', 'checkbox', $this->_Translation->GetTranslation('author'), $this->_Translation->GetTranslation('if_an_user_is_an_author_he_has_access_to_the_page_management_and_the_menu_editor'), (($UserAuthor == 1) ? true : false));
					}
					
					// Get custom fields
					$sql = "SELECT value.custom_fields_values_value, field.custom_fields_information, field.custom_fields_name, field.custom_fields_title, field.custom_fields_type, field.custom_fields_required
						FROM (" . DB_PREFIX . "custom_fields field
						LEFT JOIN " . DB_PREFIX . "custom_fields_values value
						ON field.custom_fields_id = value.custom_fields_values_fieldid)
						WHERE value.custom_fields_values_userid='{$UserID}'
						OR value.custom_fields_values_userid IS NULL";
					$customFieldsDataResult = $this->_SqlConnection->SqlQuery($sql);
					
					while ($customFieldsData = mysql_fetch_object($customFieldsDataResult)) {
						
						// Get external value for that field
						${$customFieldsData->custom_fields_name} = GetPostOrGet($customFieldsData->custom_fields_name);
						
						// Add input to the formmaker class
						$formMaker->AddInput('check_user', $customFieldsData->custom_fields_name, 'text', $customFieldsData->custom_fields_title, $customFieldsData->custom_fields_information, ${$customFieldsData->custom_fields_name});
						
						// Get the type of the field
						switch ($customFieldsData->custom_fields_type) {
								
							case 'EMail':
								$type = 'not_email';
								$text =$this->_Translation->GetTranslation('this_is_not_a_valid_email_address');
								break;
							
							case 'ICQ':
								$type = 'not_icq';
								$text = $this->_Translation->GetTranslation('this_is_not_a_valid_icq_number');
								break;
							
							default:
								$type = '';
								$text = '';
								break;
						}
						
						// Add necessary checks
						if ($customFieldsData->custom_fields_required == 1) {
							
							// Check wether the field has any value
							$formMaker->AddCheck('check_user', $customFieldsData->custom_fields_name, 'empty', sprintf($this->_Translation->GetTranslation('you_have_to_give_a_value_for_the_field_%field%!'), $customFieldsData->custom_fields_title));
							
							// Check wether the field has the necessary value
							if (!empty($type) && !empty($text))
								$formMaker->AddCheck('check_user', $customFieldsData->custom_fields_name, $type, $text);
						}
						else {
							if (!empty(${$customFieldsData->custom_fields_name}))
								$formMaker->AddCheck('check_user', $customFieldsData->custom_fields_name, $type, $text);
						}
					}
					
					if ($formMaker->CheckInputs('check_user', true)) {
						
						$user_password = ((!empty($UserPassword)) ? ", user_password='" . md5($UserPassword) . "'": '');
						// Update the user in the database
						$sql = "UPDATE " . DB_PREFIX . "users
								SET user_showname='$UserShowname', user_name='$UserName', user_email='$UserEmail', " . (($this->_User->ID != $UserID) ? "user_admin='$UserAdmin'," : '') . "user_author='$UserAuthor'$user_password
								WHERE user_id=$UserID";
						$this->_SqlConnection->SqlQuery($sql);
						
						// Get custom fields
						$sql = "SELECT value.custom_fields_values_value, field.custom_fields_name, value.custom_fields_values_id, field.custom_fields_id, value.custom_fields_values_userid
							FROM (" . DB_PREFIX . "custom_fields field
							LEFT JOIN " . DB_PREFIX . "custom_fields_values value
							ON field.custom_fields_id = value.custom_fields_values_fieldid)
							WHERE value.custom_fields_values_userid='{$UserID}'
							OR value.custom_fields_values_userid IS NULL";
						$customFieldsDataResult = $this->_SqlConnection->SqlQuery($sql);
						
						while ($customFieldsData = mysql_fetch_object($customFieldsDataResult)) {
							
							// Get external value for that field
							${$customFieldsData->custom_fields_name} = GetPostOrGet($customFieldsData->custom_fields_name);
							
							if ($customFieldsData->custom_fields_values_userid != '') {
								
								// Update existing entry
								$sql = "UPDATE " . DB_PREFIX . "custom_fields_values
										SET custom_fields_values_value='" . ${$customFieldsData->custom_fields_name} . "'
										WHERE custom_fields_values_id='$customFieldsData->custom_fields_values_id'";
								$this->_SqlConnection->SqlQuery($sql);
							}
							else {
								
								// Insert a new entry into the database
								$sql = "INSERT INTO " . DB_PREFIX . "custom_fields_values
										(custom_fields_values_userid, custom_fields_values_fieldid, custom_fields_values_value)
										VALUES ('{$UserID}', '{$customFieldsData->custom_fields_id}', '" . ${$customFieldsData->custom_fields_name} . "')";
								$this->_SqlConnection->SqlQuery($sql);
							}
						}
						
						// Send the user the HomePage of the usermanager
						$template = $this->_HomePage();
						return $template;
					}
					else {
						
						// Generate the template
						$template = "\r\n\t\t\t\t" . $formMaker->GenerateMultiFormTemplate(&$this->_ComaLate, true);
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
		 * Returns a question wether the user shall really be deleted
		 * @access private
		 * @return string A template for the page
		 */
		function _DeleteUser() {
			
			// Get external parameters
			$UserID = GetPostOrGet('user_id');
			
			// Check wether the actual user wants to delete himself. That mustn't be possible!
			if ($UserID == $this->_User->ID)
				header('Location: admin.php?page=users');
			
			// Get information about the user that should be deleted
			$sql = "SELECT user_showname
					FROM " . DB_PREFIX . "users
					WHERE user_id=$UserID";
			$userResult = $this->_SqlConnection->SqlQuery($sql);
			$user = mysql_fetch_object($userResult);
			
			// Generate a template
			$template = "\r\n\t\t\t\t" .sprintf($this->_Translation->GetTranslation('do_you_really_want_to_delete_the_user_%user%?'), $user->user_showname) . '<br />
					<a href="admin.php?page=users&amp;action=remove_user&amp;user_id=' . $UserID . '" class="button">' . $this->_Translation->GetTranslation('yes') . '</a>&nbsp;
					<a href="admin.php?page=users" class="button">' . $this->_Translation->GetTranslation('no') . '</a>';
			return $template;
		}
		
		/**
		 * Removes a user from the database
		 * @access private
		 * @return void Removes a user from the database
		 */
		function _RemoveUser() {
			
			// Get external parameters
			$UserID = GetPostOrGet('user_id');
			
			// Check wether the actual user wants to delete himself. That mustn't be possible
			if ($UserID == $this->_User->ID)
				header('Location: admin.php?page=users');
			
			// Remove the user from the database
			$sql = "DELETE
					FROM " . DB_PREFIX . "users
					WHERE user_id=$UserID";
			$this->_SqlConnection->SqlQuery($sql);
		}
		
		/**
		 * Returns a template to add a new custom field
		 * @access private
		 * @return string A template for a formular
		 */
		function _NewCustomField() {
			
			// Initialize the formmaker class
			$formMaker = new FormMaker($this->_Translation->GetTranslation('todo'), $this->_SqlConnection);
			$formMaker->AddForm('new_custom_field', 'admin.php', $this->_Translation->GetTranslation('save'), $this->_Translation->GetTranslation('custom_field'), 'post');
			
			$formMaker->AddHiddenInput('new_custom_field', 'page', 'users');
			$formMaker->AddHiddenInput('new_custom_field', 'action', 'add_custom_field');
			
			$formMaker->AddInput('new_custom_field', 'custom_field_name', 'text', $this->_Translation->GetTranslation('name'), $this->_Translation->GetTranslation('with_this_name_shall_the_admin_identify_the_custom_field'));
			$formMaker->AddInput('new_custom_field', 'custom_field_title', 'text', $this->_Translation->GetTranslation('title'), $this->_Translation->GetTranslation('this_is_the_title_of_the_custom_field_that_is_shown_to_the_user'));
			
			$formMaker->AddInput('new_custom_field', 'custom_field_type', 'select', $this->_Translation->GetTranslation('type'), $this->_Translation->GetTranslation('this_is_the_type_of_text_the_user_shall_type_into_this_field'));
			$formMaker->AddSelectEntry('new_custom_field', 'custom_field_type', true, 'Text', 'Text');
			$formMaker->AddSelectEntry('new_custom_field', 'custom_field_type', false, 'EMail', 'EMail');
			$formMaker->AddSelectEntry('new_custom_field', 'custom_field_type', false, 'ICQ', 'ICQ');
			
			$formMaker->AddInput('new_custom_field', 'custom_field_size', 'text', $this->_Translation->GetTranslation('size'), $this->_Translation->GetTranslation('this_is_the_maximum_number_of_digits_the_user_may_type_in_this_field_(write_"0"_for_unlimited_input)'));
			$formMaker->AddInput('new_custom_field', 'custom_field_show_at_registration', 'checkbox', $this->_Translation->GetTranslation('show_at_registration'), $this->_Translation->GetTranslation('shall_this_input_be_displayed_in_the_registration_of_a_new_user?'));
			// TODO: make better description
			$formMaker->AddInput('new_custom_field', 'custom_field_required', 'checkbox', $this->_Translation->GetTranslation('required'), $this->_Translation->GetTranslation('is_this_field_required_to_save_the_userdetails?'));
			$formMaker->AddInput('new_custom_field', 'custom_field_information', 'text', $this->_Translation->GetTranslation('information'), $this->_Translation->GetTranslation('this_is_the_information_displayed_for_this_field_to_the_user'));
			
			// Generate the template
			$template = "\r\n\t\t\t\t" . $formMaker->GenerateMultiFormTemplate(&$this->_ComaLate, false);
			return $template;
		}
		
		/**
		 * Returns a form to correct any errors in the addform for a new custom field if there are any
		 * @access private
		 * @return string A template for a formular
		 */
		function _AddCustomField() {
			
			// Get external parameters
			$CustomFieldName = GetPostOrGet('custom_field_name');
			$CustomFieldTitle = GetPostOrGet('custom_field_title');
			$CustomFieldType = GetPostOrGet('custom_field_type');
			$CustomFieldSize = GetPostOrGet('custom_field_size');
			$CustomFieldShowAtRegistration = ((GetPostOrGet('custom_field_show_at_registration') == 'on') ? 1 : 0);
			$CustomFieldRequired = ((GetPostOrGet('custom_field_required') == 'on') ? 1 : 0);
			$CustomFieldInformation = GetPostOrGet('custom_field_information');
			
			// Initialize the formmaker class
			$formMaker = new FormMaker($this->_Translation->GetTranslation('todo'), $this->_SqlConnection);
			$formMaker->AddForm('add_custom_field', 'admin.php', $this->_Translation->GetTranslation('save'), $this->_Translation->GetTranslation('custom_field'), 'post');
			
			$formMaker->AddHiddenInput('add_custom_field', 'page', 'users');
			$formMaker->AddHiddenInput('add_custom_field', 'action', 'add_custom_field');
			
			$formMaker->AddInput('add_custom_field', 'custom_field_name', 'text', $this->_Translation->GetTranslation('name'), $this->_Translation->GetTranslation('with_this_name_shall_the_admin_identify_the_custom_field'), $CustomFieldName);
			$formMaker->AddCheck('add_custom_field', 'custom_field_name', 'empty', $this->_Translation->GetTranslation('you_have_to_give_each_custom_field_a_name!'));
			
			$formMaker->AddInput('add_custom_field', 'custom_field_title', 'text', $this->_Translation->GetTranslation('title'), $this->_Translation->GetTranslation('this_is_the_title_of_the_custom_field_that_is_shown_to_the_user'), $CustomFieldTitle);
			$formMaker->AddCheck('add_custom_field', 'custom_field_title', 'empty', $this->_Translation->GetTranslation('you_have_to_give_each_custom_field_a_title!'));
			
			$formMaker->AddInput('add_custom_field', 'custom_field_type', 'select', $this->_Translation->GetTranslation('type'), $this->_Translation->GetTranslation('this_is_the_type_of_text_the_user_shall_type_into_this_field'), $CustomFieldType);
			$formMaker->AddSelectEntry('add_custom_field', 'custom_field_type', (($CustomFieldType == 'Text') ? true : false), 'Text', 'Text');
			$formMaker->AddSelectEntry('add_custom_field', 'custom_field_type', (($CustomFieldType == 'EMail') ? true : false), 'EMail', 'EMail');
			$formMaker->AddSelectEntry('add_custom_field', 'custom_field_type', (($CustomFieldType == 'ICQ') ? true : false), 'ICQ', 'ICQ');
			
			
			$formMaker->AddInput('add_custom_field', 'custom_field_size', 'text', $this->_Translation->GetTranslation('size'), $this->_Translation->GetTranslation('this_is_the_maximum_number_of_digits_the_user_may_type_in_this_field_(write_"0"_for_unlimited_input)'), $CustomFieldSize);
			$formMaker->AddCheck('add_custom_field', 'custom_field_size', 'not_nummeric', $this->_Translation->GetTranslation('you_have_to_define_an_absolute_value_for_the_size!'));
			
			$formMaker->AddInput('add_custom_field', 'custom_field_show_at_registration', 'checkbox', $this->_Translation->GetTranslation('show_at_registration'), $this->_Translation->GetTranslation('shall_this_input_be_displayed_in_the_registration_of_a_new_user?'), (($CustomFieldShowAtRegistration == 1) ? true : false));
			// TODO: make better description
			$formMaker->AddInput('add_custom_field', 'custom_field_required', 'checkbox', $this->_Translation->GetTranslation('required'), $this->_Translation->GetTranslation('is_this_field_required_to_save_the_userdetails?'), (($CustomFieldRequired == 1) ? true : false));
			$formMaker->AddInput('add_custom_field', 'custom_field_information', 'text', $this->_Translation->GetTranslation('information'), $this->_Translation->GetTranslation('this_is_the_information_displayed_for_this_field_to_the_user'), $CustomFieldInformation);
			
			
			
			// Check the inputs made by the user
			if ($formMaker->CheckInputs('add_custom_field', true)) {
				
				$sql = "SELECT custom_fields_orderid
	 					FROM " . DB_PREFIX . "custom_fields
 						ORDER BY custom_fields_orderid DESC
	 					LIMIT 1";
	 			$orderResult = $this->_SqlConnection->SqlQuery($sql);
	 			if($orderItem = mysql_fetch_object($orderResult)) {
 						$customFieldOrderid = $orderItem->custom_fields_orderid + 1;
 				}
 				else {
	 				$customFieldOrderid = 0;
 				}
				
				// Add new custom field to the database
				$sql = "INSERT INTO " . DB_PREFIX . "custom_fields
						(custom_fields_name, custom_fields_title, custom_fields_type, custom_fields_size, custom_fields_show_at_registration, custom_fields_required, custom_fields_information, custom_fields_orderid)
						VALUES ('$CustomFieldName', '$CustomFieldTitle', '$CustomFieldType', '$CustomFieldSize', '" . (($CustomFieldShowAtRegistration == 1) ? 1 : 0) . "', '" . (($CustomFieldRequired == 1) ? 1 : 0) . "', '$CustomFieldInformation', '$customFieldOrderid')";
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
		
		/**
		 * Returns a form to edit a custom field
		 * @access private
		 * @return string A template for a formular
		 */
		function _EditCustomField() {
			
			// Get the information about the custom field from the database
			$CustomFieldId = GetPostOrGet('field_id');
			$sql = "SELECT *
					FROM " . DB_PREFIX . "custom_fields
					WHERE custom_fields_id=$CustomFieldId";
			$customFieldResult = $this->_SqlConnection->SqlQuery($sql);
			$customField = mysql_fetch_object($customFieldResult);
			
			// Initialize the formmaker class
			$formMaker = new FormMaker($this->_Translation->GetTranslation('todo'), $this->_SqlConnection);
			$formMaker->AddForm('edit_custom_field', 'admin.php', $this->_Translation->GetTranslation('save'), $this->_Translation->GetTranslation('custom_field'), 'post');
			
			$formMaker->AddHiddenInput('edit_custom_field', 'page', 'users');
			$formMaker->AddHiddenInput('edit_custom_field', 'action', 'check_custom_field');
			$formMaker->AddHiddenInput('edit_custom_field', 'custom_field_id', $customField->custom_fields_id);
			
			$formMaker->AddInput('edit_custom_field', 'custom_field_name', 'text', $this->_Translation->GetTranslation('name'), $this->_Translation->GetTranslation('with_this_name_shall_the_admin_identify_the_custom_field'), $customField->custom_fields_name);
			$formMaker->AddInput('edit_custom_field', 'custom_field_title', 'text', $this->_Translation->GetTranslation('title'), $this->_Translation->GetTranslation('this_is_the_title_of_the_custom_field_that_is_shown_to_the_user'), $customField->custom_fields_title);
	
			$formMaker->AddInput('edit_custom_field', 'custom_field_type', 'select', $this->_Translation->GetTranslation('type'), $this->_Translation->GetTranslation('this_is_the_type_of_text_the_user_shall_type_into_this_field'), $customField->custom_fields_type);
			$formMaker->AddSelectEntry('edit_custom_field', 'custom_field_type', (($customField->custom_fields_type == 'Text') ? true : false), 'Text', 'Text');
			$formMaker->AddSelectEntry('edit_custom_field', 'custom_field_type', (($customField->custom_fields_type == 'EMail') ? true : false), 'EMail', 'EMail');
			$formMaker->AddSelectEntry('edit_custom_field', 'custom_field_type', (($customField->custom_fields_type == 'ICQ') ? true : false), 'ICQ', 'ICQ');
	
			$formMaker->AddInput('edit_custom_field', 'custom_field_size', 'text', $this->_Translation->GetTranslation('size'), $this->_Translation->GetTranslation('this_is_the_maximum_number_of_digits_the_user_may_type_in_this_field_(write_"0"_for_unlimited_input)'), $customField->custom_fields_size);
			$formMaker->AddInput('edit_custom_field', 'custom_field_show_at_registration', 'checkbox', $this->_Translation->GetTranslation('show_at_registration'), $this->_Translation->GetTranslation('shall_this_input_be_displayed_in_the_registration_of_a_new_user?'), (($customField->custom_fields_show_at_registration == 1) ? true : false));
			// TODO: make better description
			$formMaker->AddInput('edit_custom_field', 'custom_field_required', 'checkbox', $this->_Translation->GetTranslation('required'), $this->_Translation->GetTranslation('is_this_field_required_to_save_the_userdetails?'), (($customField->custom_fields_required == 1) ? true : false));
			$formMaker->AddInput('edit_custom_field', 'custom_field_information', 'text', $this->_Translation->GetTranslation('information'), $this->_Translation->GetTranslation('this_is_the_information_displayed_for_this_field_to_the_user'), $customField->custom_fields_information);
			
			// Generate the template
			$template = "\r\n\t\t\t\t" . $formMaker->GenerateMultiFormTemplate(&$this->_ComaLate, false);
			return $template;
		}
		
		/**
		 * Returns a form to correct any errors
		 * @access private
		 * @return string A template for a formular
		 */
		function _CheckCustomField() {
			
			// Get external parameters
			$CustomFieldId = GetPostOrGet('custom_field_id');
			$CustomFieldName = GetPostOrGet('custom_field_name');
			$CustomFieldTitle = GetPostOrGet('custom_field_title');
			$CustomFieldType = GetPostOrGet('custom_field_type');
			$CustomFieldSize = GetPostOrGet('custom_field_size');
			$CustomFieldShowAtRegistration = ((GetPostOrGet('custom_field_show_at_registration') == 'on') ? 1 : 0);
			$CustomFieldRequired = ((GetPostOrGet('custom_field_required') == 'on') ? 1 : 0);
			$CustomFieldInformation = GetPostOrGet('custom_field_information');
			
			// Initialize the formmaker class
			$formMaker = new FormMaker($this->_Translation->GetTranslation('todo'), $this->_SqlConnection);
			$formMaker->AddForm('check_custom_field', 'admin.php', $this->_Translation->GetTranslation('save'), $this->_Translation->GetTranslation('custom_field'), 'post');
			
			$formMaker->AddHiddenInput('check_custom_field', 'page', 'users');
			$formMaker->AddHiddenInput('check_custom_field', 'action', 'check_custom_field');
			$formMaker->AddHiddenInput('edit_custom_field', 'custom_field_id', $CustomFieldId);
			
			$formMaker->AddInput('check_custom_field', 'custom_field_name', 'text', $this->_Translation->GetTranslation('name'), $this->_Translation->GetTranslation('with_this_name_shall_the_admin_identify_the_custom_field'), $CustomFieldName);
			$formMaker->AddCheck('check_custom_field', 'custom_field_name', 'empty', $this->_Translation->GetTranslation('you_have_to_give_each_custom_field_a_name!'));
			
			$formMaker->AddInput('check_custom_field', 'custom_field_title', 'text', $this->_Translation->GetTranslation('title'), $this->_Translation->GetTranslation('this_is_the_title_of_the_custom_field_that_is_shown_to_the_user'), $CustomFieldTitle);
			$formMaker->AddCheck('check_custom_field', 'custom_field_title', 'empty', $this->_Translation->GetTranslation('you_have_to_give_each_custom_field_a_title!'));
			
			$formMaker->AddInput('check_custom_field', 'custom_field_type', 'select', $this->_Translation->GetTranslation('type'), $this->_Translation->GetTranslation('this_is_the_type_of_text_the_user_shall_type_into_this_field'), $CustomFieldType);
			$formMaker->AddSelectEntry('check_custom_field', 'custom_field_type', (($CustomFieldType == 'Text') ? true : false), 'Text', 'Text');
			$formMaker->AddSelectEntry('check_custom_field', 'custom_field_type', (($CustomFieldType == 'EMail') ? true : false), 'EMail', 'EMail');
			$formMaker->AddSelectEntry('check_custom_field', 'custom_field_type', (($CustomFieldType == 'ICQ') ? true : false), 'ICQ', 'ICQ');
			
			
			$formMaker->AddInput('check_custom_field', 'custom_field_size', 'text', $this->_Translation->GetTranslation('size'), $this->_Translation->GetTranslation('this_is_the_maximum_number_of_digits_the_user_may_type_in_this_field_(write_"0"_for_unlimited_input)'), $CustomFieldSize);
			$formMaker->AddCheck('check_custom_field', 'custom_field_size', 'not_nummeric', $this->_Translation->GetTranslation('you_have_to_define_an_absolute_value_for_the_size!'));
			
			$formMaker->AddInput('check_custom_field', 'custom_field_show_at_registration', 'checkbox', $this->_Translation->GetTranslation('show_at_registration'), $this->_Translation->GetTranslation('shall_this_input_be_displayed_in_the_registration_of_a_new_user?'), (($CustomFieldShowAtRegistration == 1) ? true : false));
			// TODO: make better description
			$formMaker->AddInput('check_custom_field', 'custom_field_required', 'checkbox', $this->_Translation->GetTranslation('required'), $this->_Translation->GetTranslation('is_this_field_required_to_save_the_userdetails?'), (($CustomFieldRequired == 1) ? true : false));
			$formMaker->AddInput('check_custom_field', 'custom_field_information', 'text', $this->_Translation->GetTranslation('information'), $this->_Translation->GetTranslation('this_is_the_information_displayed_for_this_field_to_the_user'), $CustomFieldInformation);
			
			// Check the inputs made by the user
			if ($formMaker->CheckInputs('check_custom_field', true)) {
				
				// Add new custom field to the database
				$sql = "UPDATE " . DB_PREFIX . "custom_fields
						SET custom_fields_name='$CustomFieldName', custom_fields_title='$CustomFieldTitle', custom_fields_type='$CustomFieldType', custom_fields_size='$CustomFieldSize', custom_fields_show_at_registration='" . (($CustomFieldShowAtRegistration == 1) ? 1 : 0) . "', custom_fields_required='" . (($CustomFieldRequired == 1) ? 1 : 0) . "', custom_fields_information='$CustomFieldInformation'
						WHERE custom_fields_id='$CustomFieldId'";
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
		
		/**
		 * Shows a specific custom field with all its settings
		 * @access private
		 * @return string Template for a table
		 */
		function _ViewCustomField() {
			
			// TODO: Find something better than the table
			// Get external parameters
			$CustomFieldId = GetPostOrGet('field_id');
			$this->_ComaLate->SetReplacement('FIELD_ID', $CustomFieldId);
			
			// Get the settings of the field from the database
			$sql = "SELECT *
					FROM " . DB_PREFIX . "custom_fields
					WHERE custom_fields_id=$CustomFieldId";
			$customFieldResult = $this->_SqlConnection->SqlQuery($sql);
			$customField = mysql_fetch_object($customFieldResult);
			
			// Initialize informationsarray
			$customFieldInformation = array();
			$customFieldInformation[] = array('name' => $this->_Translation->GetTranslation('name'), 'value' => $customField->custom_fields_name);
			$customFieldInformation[] = array('name' => $this->_Translation->GetTranslation('title'), 'value' => $customField->custom_fields_title);
			$customFieldInformation[] = array('name' => $this->_Translation->GetTranslation('type'), 'value' => $customField->custom_fields_type);
			$customFieldInformation[] = array('name' => $this->_Translation->GetTranslation('size'), 'value' => $customField->custom_fields_size);
			$customFieldInformation[] = array('name' => $this->_Translation->GetTranslation('show_at_registration'), 'value' => (($customField->custom_fields_show_at_registration == 1) ? $this->_Translation->GetTranslation('yes') : $this->_Translation->GetTranslation('no')));
			$customFieldInformation[] = array('name' => $this->_Translation->GetTranslation('required'), 'value' => (($customField->custom_fields_required == 1) ? $this->_Translation->GetTranslation('yes') : $this->_Translation->GetTranslation('no')));
			$customFieldInformation[] = array('name' => $this->_Translation->GetTranslation('information'), 'value' => $customField->custom_fields_information);
			
			$this->_ComaLate->SetReplacement('CUSTOM_FIELD_INFORMATION', $customFieldInformation);
			
			// Set replacements for language
			$this->_ComaLate->SetReplacement('LANG_EDIT', $this->_Translation->GetTranslation('edit'));
			$this->_ComaLate->SetReplacement('LANG_DELETE', $this->_Translation->GetTranslation('delete'));
			
			// Generate template
			$template = '
					<table>
					<CUSTOM_FIELD_INFORMATION:loop>
						<tr>
							<th>{name}</th>
							<td>{value}</td>
						</tr>
					</CUSTOM_FIELD_INFORMATION>
					</table>
					<a href="admin.php?page=users&amp;action=edit_custom_field&amp;field_id={FIELD_ID}" class="button">{LANG_EDIT}</a>&nbsp;<a href="admin.php?page=users&amp;action=delete&amp;field_id={FIELD_ID}" class="button">{LANG_DELETE}</a>';
			
			// Return the generated template
			return $template;
		}
		
		/**
		 * Switches the orderids of two custom fields
 		 * @access private
 		 * @param resource $CustomFieldsResult This is an mysqlresult that should include two 'rows'
 		 * @return void
 		 */
 		function _SwitchOrderIDs ($CustomFieldsResult) {
 			if ($customField = mysql_fetch_object($CustomFieldsResult)) {
				$customFieldID1 = $customField->custom_fields_id;
				$customFieldOrderID1 = $customField->custom_fields_orderid;
				
				if ($customField = mysql_fetch_object($CustomFieldsResult)) {
					$customFieldID2 = $customField->custom_fields_id;
					$customFieldOrderID2 = $customField->custom_fields_orderid;
					
					$sql = "UPDATE " . DB_PREFIX . "custom_fields
						SET custom_fields_orderid=$customFieldOrderID2
						WHERE custom_fields_id=$customFieldID1";
					$this->_SqlConnection->SqlQuery($sql);
						 
					$sql = "UPDATE " . DB_PREFIX . "custom_fields
						SET custom_fields_orderid=$customFieldOrderID1
						WHERE custom_fields_id=$customFieldID2";
					$this->_SqlConnection->SqlQuery($sql);
				}
			}
 		}
		
		/**
		 * Moves a custom field one position up
		 * @access private
		 * @return void Moves custom field up
		 */
		function _MoveCustomFieldUp() {
			
			// Get external parameters
			$CustomFieldOrderID = GetPostOrGet('field_id');
			
			// is this parameter really a number?
 			if(is_numeric($CustomFieldOrderID)) {
 				
 				// this query should return two 'rows' 
 				$sql = "SELECT *
					FROM " . DB_PREFIX . "custom_fields
					WHERE custom_fields_orderid <= $CustomFieldOrderID
					ORDER BY custom_fields_orderid DESC
					LIMIT 0 , 2";
				$fieldsResult = $this->_SqlConnection->SqlQuery($sql);
				// try to switch the orderID between these 'rows'
				$this->_SwitchOrderIDs($fieldsResult);
 			}
		}
		
		/**
		 * Moves a custom field one position down
		 * @access private
		 * @return void Moves custom field down
		 */
		function _MoveCustomFieldDown() {
			
			// Get external parameters
			$CustomFieldOrderID = GetPostOrGet('field_id');
			
			// is this parameter really a number?
 			if(is_numeric($CustomFieldOrderID)) {
 				
 				// this query should return two 'rows' 
 				$sql = "SELECT *
					FROM " . DB_PREFIX . "custom_fields
					WHERE custom_fields_orderid >= $CustomFieldOrderID
					ORDER BY custom_fields_orderid ASC
					LIMIT 0 , 2";
				$fieldsResult = $this->_SqlConnection->SqlQuery($sql);
				// try to switch the orderID between these 'rows'
				$this->_SwitchOrderIDs($fieldsResult);
 			}
		}
		
		/**
		 * Returns a question wether the custom field should really be deleted
		 * @access private
		 * @return string A template for the page
		 */
		function _DeleteCustomField() {
			
			// Get external parameters
			$CustomFieldID = GetPostOrGet('field_id');
			
			// Get information about the custom field that should be deleted
			$sql = "SELECT custom_fields_name
					FROM " . DB_PREFIX . "custom_fields
					WHERE custom_fields_id=$CustomFieldID";
			$fieldResult = $this->_SqlConnection->SqlQuery($sql);
			$customField = mysql_fetch_object($fieldResult);
			
			// Generate a template
			$template = "\r\n\t\t\t\t" .sprintf($this->_Translation->GetTranslation('do_you_really_want_to_delete_the_custom_field_%field%?'), $customField->custom_fields_name) . '<br />
					<a href="admin.php?page=users&amp;action=remove_custom_field&amp;field_id=' . $CustomFieldID . '" class="button">' . $this->_Translation->GetTranslation('yes') . '</a>&nbsp;
					<a href="admin.php?page=users" class="button">' . $this->_Translation->GetTranslation('no') . '</a>';
			return $template;
		}
		
		/**
		 * Removes a custom field from the database
		 * @access private
		 * @return void Removes a custom field from the database
		 */
		function _RemoveCustomField() {
			
			// Get external parameters
			$CustomFieldID = GetPostOrGet('field_id');
			
			// Remove the custom field from the database
			$sql = "DELETE
					FROM " . DB_PREFIX . "custom_fields
					WHERE custom_fields_id=$CustomFieldID";
			$this->_SqlConnection->SqlQuery($sql);
		}
	}
?>
