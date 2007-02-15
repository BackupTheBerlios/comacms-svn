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
	class User_Memberlist extends User {
		
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
			
			// Return the template to the user
			return $template;
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
		
		/**
		 * Returns a template for a userprofile
		 * @access private
		 * @return string Template
		 */
		function _ShowProfile() {
			
			// Initialize the template
			$template = '<h2>{LANG_USERPROFILE}</h2>';
			
			$this->_ComaLate->SetReplacement('LANG_USERPROFILE', $this->_Translation->GetTranslation('user_profile'));
			
			// Get external parameters
			$UserName = GetPostOrGet('user_name');
			
			// Get information about the user from the database
			$sql = "SELECT *
					FROM " . DB_PREFIX . "users
					WHERE user_name='$UserName'";
			$userResult = $this->_SqlConnection->SqlQuery($sql);
			
			if ($user = mysql_fetch_object($userResult)) {
				
				// Generate profile array
				$userProfile = array();
				$userProfile[] = array( 'PROFILE_FIELD_NAME' => 'showname',
										'PROFILE_FIELD_TRANSLATION' => $this->_Translation->GetTranslation('showname'),
										'PROFILE_FIELD_VALUE' => $user->user_showname,
										'PROFILE_FIELD_INFORMATION' => $this->_Translation->GetTranslation('the_name_that_is_displayed_if_the_user_writes_a_news_for_example'));
				$userProfile[] = array( 'PROFILE_FIELD_NAME' => 'email',
										'PROFILE_FIELD_TRANSLATION' => $this->_Translation->GetTranslation('email'),
										'PROFILE_FIELD_VALUE' => $user->user_email,
										'PROFILE_FIELD_INFORMATION' => $this->_Translation->GetTranslation('using_the_email_address_the_user_is_contacted_by_the_system'));
				$userProfile[] = array( 'PROFILE_FIELD_NAME' => 'preferred_language',
										'PROFILE_FIELD_TRANSLATION' => $this->_Translation->GetTranslation('preferred_language'),
										'PROFILE_FIELD_VALUE' => $this->_Translation->GetTranslation($user->user_preferred_language),
										'PROFILE_FIELD_INFORMATION' => $this->_Translation->GetTranslation('this_is_the_preferred_language_of_the_user'));
				
				// Get custom fields
				$sql = "SELECT value.custom_fields_values_value, field.custom_fields_information, field.custom_fields_name, field.custom_fields_title, field.custom_fields_required
						FROM (" . DB_PREFIX . "custom_fields field
						LEFT JOIN " . DB_PREFIX . "custom_fields_values value
						ON field.custom_fields_id = value.custom_fields_values_fieldid)
						WHERE value.custom_fields_values_userid='{$user->user_id}'";
				$customFieldsValuesResult = $this->_SqlConnection->SqlQuery($sql); 
				
				while ($customFieldsValue = mysql_fetch_object($customFieldsValuesResult)) {
					$userProfile[] = array(	'PROFILE_FIELD_NAME' => $customFieldsValue->custom_fields_name,
											'PROFILE_FIELD_TRANSLATION' => $customFieldsValue->custom_fields_title,
											'PROFILE_FIELD_VALUE' => $customFieldsValue->custom_fields_values_value,
											'PROFILE_FIELD_INFORMATION' => $customFieldsValue->custom_fields_information. (($customFieldsValue->custom_fields_required == 1) ? ' ' . $this->_Translation->GetTranslation('(required)') : ''));
				}
				
				$this->_ComaLate->SetReplacement('USER_PROFILE', $userProfile);
				
				// Set replacements for language
				$this->_ComaLate->SetReplacement('LANG_PROFILE', $this->_Translation->GetTranslation('profile'));
				
				// Generate the template
				$template .= '<fieldset>
							<legend>{LANG_PROFILE}</legend>
							<USER_PROFILE:loop>
							<div class="row">
								<label for="{PROFILE_FIELD_NAME}">
									<strong>{PROFILE_FIELD_TRANSLATION}:</strong>
									<span class="info">{PROFILE_FIELD_INFORMATION}</span>
								</label>
								<span class="edit">{PROFILE_FIELD_VALUE}&nbsp;</span>
							</div>
							</USER_PROFILE>
						</fieldset>
						';
				return $template;
			}
			else
				return $template . "\r\n\t\t\t" . $this->_Translation->GetTranslation('the_user_could_not_be_found');
		}
	}
	
?>
