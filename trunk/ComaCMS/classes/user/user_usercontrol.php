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
	require_once __ROOT__ . '/classes/user/user.php';
	require_once __ROOT__ . '/lib/formmaker/formmaker.class.php';
	
	/**
	 * Returns the Usercontrol
	 * @package ComaCMS
	 * @subpackage UserInterface
	 */
	class User_Usercontrol extends User {
		
		/**
		 * Gets the subpages of the usercontrol
		 * @access public
		 * @param string $Action The subpage of the usercontrol
		 * @return string A template for the outputpage
		 */
		function GetPage($Action = '') {
			
			$template = '';
			
			// Get the template of the subpages
			switch ($Action) {
				
				case 'edit_profile':
					$template .= $this->_EditProfile();
					break;
				
				case 'check_profile':
					$template .= $this->_CheckProfile();
					break;
				
				default: 
					$template .= $this->_HomePage();
					break;
			}
			
			// Return the generated template
			return $template;
		}
		
		/**
		 * Returns the template for the usercontrol page and sets the necessary comalate replacements
		 * @access private
		 * @return string The template for the page
		 */
		function _HomePage() {
			// Get external parameters
			$dateDayFormat = $this->_Config->Get('date_day_format', 'd.m.Y');
			$dateTimeFormat = $this->_Config->Get('date_time_format', 'H:i:s');
			$dateFormat = $dateDayFormat . ' ' . $dateTimeFormat;
			
			// Get the data of the userinterface
			$sql = "SELECT user_registerdate, user_email
					FROM " . DB_PREFIX . "users
					WHERE user_id='{$this->_User->ID}'";
			$userResult = $this->_SqlConnection->SqlQuery($sql);
			$user = mysql_fetch_object($userResult);
			
			$userProfile = array();
			$userProfile[] = array( 'PROFILE_FIELD_NAME' => $this->_Translation->GetTranslation('name'),
									'PROFILE_FIELD_VALUE' => $this->_User->Showname);
			$userProfile[] = array( 'PROFILE_FIELD_NAME' => $this->_Translation->GetTranslation('loginname'),
									'PROFILE_FIELD_VALUE' => $this->_User->Name);
			$userProfile[] = array( 'PROFILE_FIELD_NAME' => $this->_Translation->GetTranslation('email'),
									'PROFILE_FIELD_VALUE' => $user->user_email);
			$userProfile[] = array( 'PROFILE_FIELD_NAME' => $this->_Translation->GetTranslation('registered_since'),
									'PROFILE_FIELD_VALUE' => date($dateDayFormat, $user->user_registerdate));
			$userProfile[] = array( 'PROFILE_FIELD_NAME' => $this->_Translation->GetTranslation('is_author'),
									'PROFILE_FIELD_VALUE' => (($this->_User->IsAuthor) ? $this->_Translation->GetTranslation('yes') : $this->_Translation->GetTranslation('no')));
			$userProfile[] = array( 'PROFILE_FIELD_NAME' => $this->_Translation->GetTranslation('is_admin'),
									'PROFILE_FIELD_VALUE' => (($this->_User->IsAdmin) ? $this->_Translation->GetTranslation('yes') : $this->_Translation->GetTranslation('no')));
			
			$this->_ComaLate->SetReplacement('USER_PROFILE', $userProfile);
			
			// Set lang replacements for comalate
			$this->_ComaLate->SetReplacement('LANG_USERPROFILE', $this->_Translation->GetTranslation('user_profile'));
			
			// Generate the template
			$template = '<h2>{LANG_USERPROFILE}</h2>
					<table>
					<USER_PROFILE:loop>
						<tr>
							<th>{PROFILE_FIELD_NAME}</th>
							<td>{PROFILE_FIELD_VALUE}</td>
						</tr>
					</USER_PROFILE>
					</table>
					<a href="special.php?page=userinterface&amp;action=edit_profile" class="button">Bearbeiten</a>
					';
			
			if ($this->_User->IsAuthor) {
				
				// Initialize pages array
				$pages = array();
				
				// get the last 6 pages
				$sql = "SELECT page_name, page_title, page_creator, page_edit_comment, page_date
						FROM " . DB_PREFIX . "pages
						ORDER BY page_date DESC
						LIMIT 6";
				
				$pagesResult = $this->_SqlConnection->SqlQuery($sql);
							
				while($page = mysql_fetch_object($pagesResult))
					$pages[$page->page_date] = array($page->page_name, $page->page_title, $page->page_creator, $page->page_edit_comment);
				
				// get the last 6 pages of the history
				$sql = "SELECT page_name, page_title, page_creator, page_edit_comment, page_date
						FROM " . DB_PREFIX . "pages_history
						ORDER BY page_date DESC
						LIMIT 6";
				
				$pagesResult = $this->_SqlConnection->SqlQuery($sql);
				
				while($page = mysql_fetch_object($pagesResult))
					$pages[$page->page_date] = array($page->page_name, $page->page_title, $page->page_creator, $page->page_edit_comment);
				
				krsort($pages);
				
				$logData = array();
				$count = 0;
				
				foreach ($pages as $date => $page) {
	   				$logData[] = array( 'LOG_DATE' => date($dateFormat, $date),
										'LOG_PAGE_URL'  =>  $page[0],
										'LOG_PAGE_TITLE' => $page[1],
										'LOG_PAGE_NAME' => rawurldecode($page[0]),
										'LOG_USER' => $this->_ComaLib->GetUserByID($page[2]),
										'LOG_COMMENT' => $page[3]);
					if($count++ == 5)
						break;	
				}
				$this->_ComaLate->SetReplacement('USER_PAGESLOG', $logData);
				
				// Set replacements for language
				$this->_ComaLate->SetReplacement('LANG_LAST_CHANGES', $this->_Translation->GetTranslation('last_changes'));
				$this->_ComaLate->SetReplacement('LANG_PAGESLOG_TITLE_DATE', $this->_Translation->GetTranslation('date'));
				$this->_ComaLate->SetReplacement('LANG_PAGESLOG_TITLE_PAGE', $this->_Translation->GetTranslation('page'));
				$this->_ComaLate->SetReplacement('LANG_PAGESLOG_TITLE_USER', $this->_Translation->GetTranslation('user'));
				$this->_ComaLate->SetReplacement('LANG_PAGESLOG_TITLE_COMMENT', $this->_Translation->GetTranslation('comment'));
				
				// Add the template
				$template .= '<h2>{LANG_LAST_CHANGES}</h2>
					<table class="full_width">
						<tr>
							<th>{LANG_PAGESLOG_TITLE_DATE}</th>
							<th>{LANG_PAGESLOG_TITLE_PAGE}</th>
							<th>{LANG_PAGESLOG_TITLE_USER}</th>
							<th>{LANG_PAGESLOG_TITLE_COMMENT}</th>
						</tr>
						<USER_PAGESLOG:loop>
						<tr>
							<td>{LOG_DATE}</td>
							<td><a href="index.php?page={LOG_PAGE_URL}">{LOG_PAGE_TITLE}</a>({LOG_PAGE_NAME})</td>
							<td>{LOG_USER}</td>
							<td>{LOG_COMMENT}</td>
						</tr>
						</USER_PAGESLOG>
					</table>
					';
			}
			
			return $template;
		}
	
		/**
		 * Edits the profile of the current user
		 * @access private
		 * @return string The template for the profileeditor
		 */
		function _EditProfile() {
			
			// Get the data of the userinterface
			$sql = "SELECT user_registerdate, user_email
					FROM " . DB_PREFIX . "users
					WHERE user_id='{$this->_User->ID}'";
			$userResult = $this->_SqlConnection->SqlQuery($sql);
			$user = mysql_fetch_object($userResult);
			mysql_free_result($userResult);
			
			// Initialize the formmaker class
			$formMaker = new FormMaker($this->_Translation->GetTranslation('todo'), $this->_SqlConnection);
			$formMaker->AddForm('edit_user', 'special.php', $this->_Translation->GetTranslation('save'), $this->_Translation->GetTranslation('user'), 'post');
			
			$formMaker->AddHiddenInput('edit_user', 'page', 'userinterface');
			$formMaker->AddHiddenInput('edit_user', 'action', 'check_profile');
			$formMaker->AddHiddenInput('edit_user', 'user_id', $this->_User->ID);
			
			$formMaker->AddInput('edit_user', 'user_showname', 'text', $this->_Translation->GetTranslation('showname'), $this->_Translation->GetTranslation('the_name_that_is_displayed_if_the_user_writes_a_news_for_example'), $this->_User->Showname);
			$formMaker->AddInput('edit_user', 'user_name', 'text', $this->_Translation->GetTranslation('nickname'), $this->_Translation->GetTranslation('with_this_nick_the_user_can_login_so_he_must_not_fill_in_his_long_name'), $this->_User->Name);
			$formMaker->AddInput('edit_user', 'user_email', 'text', $this->_Translation->GetTranslation('email'), $this->_Translation->GetTranslation('using_the_email_address_the_user_is_contacted_by_the_system'), $user->user_email);
			$formMaker->AddInput('edit_user', 'user_password', 'password', $this->_Translation->GetTranslation('password'), $this->_Translation->GetTranslation('with_this_password_the_user_can_login_to_restricted_areas'));
			$formMaker->AddInput('edit_user', 'user_password_repetition', 'password', $this->_Translation->GetTranslation('password_repetition'), $this->_Translation->GetTranslation('it_is_guaranteed_by_a_repetition_that_the_user_did_not_mistype_during_the_input'));
			
			// Generate the template
			$template = "\r\n\t\t\t\t" . $formMaker->GenerateMultiFormTemplate(&$this->_ComaLate, false);
			return $template;
		}
		
		/**
		 * Checks the inputs of the user and saves them to the database if they are correct
		 * @access private
		 * @return string The template for the correctionspage
		 */
		function _CheckProfile() {
			
			// Get external parameters
			$UserID = GetPostOrGet('user_id');
			
			// Check wether the actual logged in user is the same that should be edited
			if ($UserID == $this->_User->ID) {
				
				// Get the values of the editfields
				$UserShowname = GetPostOrGet('user_showname');
				$UserName = GetPostOrGet('user_name');
				$UserEmail = GetPostOrGet('user_email');
				$UserPassword = GetPostOrGet('user_password');
				$UserPasswordRepetition = GetPostOrGet('user_password_repetition');
				
				// Get the missing data of the user
				$sql = "SELECT user_email
						FROM " . DB_PREFIX . "users
						WHERE user_id='{$this->_User->ID}'";
				$userResult = $this->_SqlConnection->SqlQuery($sql);
				$user = mysql_fetch_object($userResult);
				mysql_free_result($userResult);
				
				if (($UserShowname != $this->_User->Showname) || ($UserName != $this->_User->Name) || ($UserEmail != $user->user_email) || (!empty($UserPassword)) || (!empty($UserPasswordRepetition))) {
					
					// Initialize the formmaker class
					$formMaker = new FormMaker($this->_Translation->GetTranslation('todo'), $this->_SqlConnection);
					$formMaker->AddForm('edit_user', 'special.php', $this->_Translation->GetTranslation('save'), $this->_Translation->GetTranslation('user'), 'post');
					
					$formMaker->AddHiddenInput('edit_user', 'page', 'userinterface');
					$formMaker->AddHiddenInput('edit_user', 'action', 'check_profile');
					$formMaker->AddHiddenInput('edit_user', 'user_id', $UserID);
					
					$formMaker->AddInput('edit_user', 'user_showname', 'text', $this->_Translation->GetTranslation('name'), $this->_Translation->GetTranslation('the_name_that_is_displayed_if_the_user_writes_a_news_for_example'), $UserShowname);
					$formMaker->AddCheck('edit_user', 'user_showname', 'empty', $this->_Translation->GetTranslation('the_nickname_must_be_indicated'));
					if ($user->user_showname != $UserShowname)
						$formMaker->AddCheck('edit_user', 'user_showname', 'already_assigned', $this->_Translation->GetTranslation('the_name_is_already_assigned'), '', 'users', 'user_showname');
					
					$formMaker->AddInput('edit_user', 'user_name', 'text', $this->_Translation->GetTranslation('loginname'), $this->_Translation->GetTranslation('with_this_nick_the_user_can_login_so_he_must_not_fill_in_his_long_name'), $UserName);
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
					
					
					if ($formMaker->CheckInputs('edit_user', true)) {
						
						$user_password = ((!empty($UserPassword)) ? ", user_password='" . md5($UserPassword) . "'": '');
						// Update the user in the database
						$sql = "UPDATE " . DB_PREFIX . "users
								SET user_showname='$UserShowname', user_name='$UserName', user_email='$UserEmail'$user_password
								WHERE user_id=$UserID";
						$this->_SqlConnection->SqlQuery($sql);
						
						$this->_User->Logout();
						
						// Set user back to userinterface
						header('Location: special.php?page=userinterface');
						die();
					}
					else {
						// Generate the template
						$template = "\r\n\t\t\t\t" . $formMaker->GenerateMultiFormTemplate(&$this->_ComaLate, true);
						return $template;
					}
				}
				else {
					header('Location: special.php?page=userinterface');
					die();
				}
			}
			else
				return $this->_Translation->GetTranslation('you_have_no_right_to_edit_the_profile_of_another_user');
		}
	}

?>