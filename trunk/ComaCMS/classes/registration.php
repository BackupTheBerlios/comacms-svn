<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005-20067 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : registration.php
 # created              : 2006-11-23
 # copyright            : (C) 2005-2007 The ComaCMS-Team
 # email                : comacms@williblau.de
 #----------------------------------------------------------------------
 # This program is free software; you can redistribute it and/or modify
 # it under the terms of the GNU General Public License as published by
 # the Free Software Foundation; either version 2 of the License, or
 # (at your option) any later version.
 #----------------------------------------------------------------------
 
	
 	/**
 	 * User-Interface-Page to register at the system
 	 * @package ComaCMS
 	 */
 	class Registration {
 		
 		/**
 		 * @access private
 		 * @var Sql 
 		 */
 		var $_SqlConnection;
 		
 		/**
 		 * @access private
 		 * @var Language
 		 */
 		var $_Translation;
 		
 		/**
 		 * @access private
 		 * @var Config
 		 */
 		var $_Config;
 		
 		/**
 		 * Initializes the Registration class
 		 * @access public
 		 * @param Sql SqlConnection Connection to the MysqlDatabase
 		 * @param Language Translation
 		 * @param Config Config Access to the Configurations
 		 * @return void
 		 */
	 	function Registration(&$SqlConnection, &$Translation, &$Config) {
			$this->_SqlConnection = &$SqlConnection;
			$this->_Translation = &$Translation;
			$this->_Config = &$Config;
		}
		
 		/**
		 * Available actions (value of <var>$Action</var>):
		 *  - register
		 *  - checkRegistration
		 *  - registerError
		 *  - insert new user
		 *  - complete registration
		 * @access public
		 * @param string Action text
		 * @return sting Pagetext
		 */
		function GetPage($Action) {
			$out = "";
		 	switch ($Action) {
		 		case 'checkRegistration':	
		 			$out .= $this->_checkRegistration(GetPostOrGet('showname'), GetPostOrGet('name'), GetPostOrGet('email'), GetPostOrGet('password'), GetPostOrGet('password_repetition'));
		 			break;
		 			
		 		case 'activateRegistration':	
		 			$out .= $this->_activateRegistration(GetPostOrGet('code'));
		 			break;
		 			
		 		default:			
		 			$out .= $this->_register();
		 			break;
		 	}
			return $out;
	 	}
	 	
	 	/**
	 	 * Shows main registration Form
	 	 * @access private
	 	 * @return string RegistrationForm
	 	 */
	 	 function _register() {
	 	 	$out = '';
	 	 	//$out .= $this->_registerVar(GetPostOrGet('showname'), GetPostOrGet('name'), GetPostOrGet('email'));
	 	 	$out .= $this->_registerVar('', '', '', '', '', '', '');
	 	 	return $out;
	 	 }
	 	
	 	/**
	 	 * Shows registration Form
	 	 * @access private
	 	 * @param string Showname The name of the new user to display for corrections
	 	 * @param string Name The nickname of the new user to display for corrections
	 	 * @param string Email The emailaddress of the new user to display for corrections
	 	 * @param string ShownameError Any errors in name of the user to display
	 	 * @param string NameError Any errors in nickname of the user to display
	 	 * @param string EmailError Any errors in the emailaddress of the new user to display
	 	 * @param string PasswordError Any errors with the password and its repetition to display
	 	 * @return string RegistrationForm
	 	 */
	 	function _registerVar($Showname, $Name, $Email, $ShownameError, $NameError, $EmailError, $PasswordError) {
			$out = '';
			$out .= "\t\t\t\t\t<form method=\"post\" action=\"special.php\">
						<input type=\"hidden\" name=\"page\" value=\"register\" />
						<input type=\"hidden\" name=\"action\" value=\"checkRegistration\" />
						<fieldset>
							<legend>Registrieren</legend>
							<div class=\"row\">
								<label for=\"showname\">
									<strong>" . $this->_Translation->GetTranslation('name') . ":</strong>" . (($ShownameError != '') ? "\r\n\t\t\t\t\t\t\t\t\t<span class=\"error\">{$ShownameError}</span>\r\n\t\t\t\t\t\t\t\t\t" : '') . "
									<span class=\"info\">" . $this->_Translation->GetTranslation('the_name_that_is_displayed_if_the_user_writes_a_news_for_example') . "</span>
								</label>
								<input type=\"text\" name=\"showname\" id=\"showname\" value=\"{$Showname}\" />
							</div>
							<div class=\"row\">
								<label for=\"name\">
									<strong>" . $this->_Translation->GetTranslation('loginname') . ":</strong>" . (($NameError != '') ? "\r\n\t\t\t\t\t\t\t\t\t<span class=\"error\">{$NameError}</span>\r\n\t\t\t\t\t\t\t\t\t" : '') . "
									<span class=\"info\">" . $this->_Translation->GetTranslation('with_this_nick_the_user_can_login_so_he_must_not_fill_in_his_long_name') . "</span>
								</label>
								<input type=\"text\" name=\"name\" id=\"name\" value=\"{$Name}\" />
							</div>
							<div class=\"row\">
								<label for=\"email\">
									<strong>" . $this->_Translation->GetTranslation('email') . ":</strong>" . (($EmailError != '') ? "\r\n\t\t\t\t\t\t\t\t\t<span class=\"error\">{$EmailError}</span>\r\n\t\t\t\t\t\t\t\t\t" : '') . "
									<span class=\"info\">" . $this->_Translation->GetTranslation('using_the_email_address_the_user_is_contacted_by_the_system') . "</span>
								</label>
								<input type=\"text\" name=\"email\" id=\"email\" value=\"{$Email}\" />
							</div>
							<div class=\"row\">
								<label for=\"password\">
									<strong>" . $this->_Translation->GetTranslation('password') . ":</strong>" . (($PasswordError != '') ? "\r\n\t\t\t\t\t\t\t\t\t<span class=\"error\">{$PasswordError}</span>\r\n\t\t\t\t\t\t\t\t\t" : '') . "
									<span class=\"info\">" . $this->_Translation->GetTranslation('with_this_password_the_user_can_login_to_restricted_areas') . "</span>
								</label>
								<input type=\"password\" name=\"password\" id=\"password\" />
							</div>
							<div class=\"row\">
								<label for=\"password_repetition\">
									<strong>" . $this->_Translation->GetTranslation('password_repetition') . ":</strong>
									<span class=\"info\">" . $this->_Translation->GetTranslation('it_is_guaranteed_by_a_repetition_that_the_user_did_not_mistype_during_the_input') . "</span>
								</label>
								<input type=\"password\" name=\"password_repetition\" id=\"password_repetition\" />
							</div>
							<div class=\"row\">
								<input type=\"submit\" class=\"button\" value=\"" . $this->_Translation->GetTranslation('register') . "\" />
							</div>
						</fieldset>
					</form>";
			return $out;
	 	}
	 	
	 	/**
	 	 * Checks the registration form, returns a new one if there are any mistakes, and saves the new user if everything is correct
	 	 * @access private
	 	 * @param string Showname The name for the new user
	 	 * @param string Name The nickname for the new user
	 	 * @param string Email The Emailaddress for the new user
	 	 * @param string Password The Password for the new user
	 	 * @param string Password_repetition The Passwordrepetition to exclude typing errors
	 	 * @return string PageData
	 	 */
		function _checkRegistration($Showname, $Name, $Email, $Password, $Password_repetition) {
			$out = '';
			$fehlerfrei = true;
			
			//Set all errors to '' to prevent errors with clear variables
			$ShownameError = '';
			$NameError = '';
			$EmailError = '';
			$PasswordError = '';
			
			// Check the registrationfields for common errors and write them to the error variables
			if ($Showname == '') {
				$ShownameError = $this->_Translation->GetTranslation('the_name_must_be_indicated');
				$fehlerfrei = false;
			}
			else {
				// If showname is not empty check wether it is used already
				$sql = "SELECT *
					FROM " . DB_PREFIX . "users
					WHERE user_showname='$Showname'
					LIMIT 0 , 1";
				$result = $this->_SqlConnection->SqlQuery($sql);
				if (mysql_num_rows($result) == 1) {
					$ShownameError = $this->_Translation->GetTranslation('the_name_is_already_assigned');
					$fehlerfrei = false;
				}
			}
			if ($Name == '') {
				$NameError = $this->_Translation->GetTranslation('the_nickname_must_be_indicated');
				$fehlerfrei = false;
			}
			else {
				// If nickname is not empty check wether it is used already
				$sql = "SELECT *
					FROM " . DB_PREFIX . "users
					WHERE user_name='$Name'
					LIMIT 0 , 1";
				$result = $this->_SqlConnection->SqlQuery($sql);
				if (mysql_num_rows($result) == 1) {
					$NameError = $this->_Translation->GetTranslation('the_nickname_is_already_assigned');
					$fehlerfrei = false;
				}
			}
			if ($Email == '') {
				$EmailError = $this->_Translation->GetTranslation('the_email_address_must_be_indicated');
				$fehlerfrei = false;
			}
			else {
				// If Emailaddress is not empty check wether it is a real emailaddress and if check wether it is used already
				if (isEmailAddress($Email)) {
					$sql = "SELECT *
						FROM " . DB_PREFIX . "users
						WHERE user_email='$Email'
						LIMIT 0 , 1";
					$result = $this->_SqlConnection->SqlQuery($sql);
					if (mysql_num_rows($result) >= 1) {
						$EmailError = $this->_Translation->GetTranslation('the_email_is_already_assigned_to_another_user');
						$fehlerfrei = false;
					}
				}
				else {
					// If its not a real emailaddress throw exception
					$EmailError = $this->_Translation->GetTranslation('this_is_a_invalid_email_address');
					$fehlerfrei = false;
				}
			}
			if ($Password == '' || $Password_repetition == '') {
				$PasswordError = $this->_Translation->GetTranslation('none_of_the_passwordfields_must_not_be_empty');
				$fehlerfrei = false;
			}
			elseif ($Password != $Password_repetition) {
				$PasswordError = $this->_Translation->GetTranslation('the_password_and_its_repetition_are_unequal');
				$fehlerfrei = false;
			}
			
			if (!$fehlerfrei) {
				// Show registrationform again and display all existing errors
				$out .= $this->_registerVar($Showname, $Name, $Email, $ShownameError, $NameError, $EmailError, $PasswordError);
			}
			// If there are no errors left put user into database
			else {
				$registrationTime = time();
				$activated = false;
				$activationCode = '';
				
				// If a validation of the emailaddress is required make a registration code and send email to the user
				if ($this->_Config->Get('validate_email', '1')) {
					$activationCode = md5($Showname . $registrationTime . $Email);
					
					// Send mail with registrationcode and logindata to the user
					$title = $this->_Translation->GetTranslation('activation_of_your_new_accout_at') . $this->_Config->Get('pagename', 'ComaCMS');
					$message = sprintf($this->_Translation->GetTranslation('welcome_%1\$s:Pagename_%2\$s:Benutzername_%3\$s:Password_%4\$s:Email_%5\$s:ActivationCode'), $this->_Config->Get('pagename', 'ComaCMS'), $Name, $Password, $Email, $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'], $activationCode);
					$header = 'From: ' . $this->_Config->Get('administrator_emailaddress', 'administrator@comacms') . "\n";
					mail($Email, $title, $message, $header);
				}
				else {
					if ($this->_Config->Get('activate_through_admin', '0')) {
						// Send mail with logindata to the user, activation throw administrator
						$title = $this->_Translation->GetTranslation('activation_of_your_new_accout_at') . $this->_Config->Get('pagename', 'ComaCMS');
						$message = sprintf($this->_Translation->GetTranslation('welcome_%1\$s:Pagename_%2\$s:Benutzername_%3\$s:Password_%4\$s:Email_activation_throw_admin'), $this->_Config->Get('pagename', 'ComaCMS'), $Name, $Password, $Email);
						$header = 'From: ' . $this->_Config->Get('administrator_emailaddress', 'administrator@comacms') . "\n";
						mail($Email, $title, $message, $header);
					}
					else {
						// Activate the useraccount
						$activated = true;
						
						// Send mail with logindata to the user
						$title = $this->_Translation->GetTranslation('activation_of_your_new_accout_at') . $this->_Config->Get('pagename', 'ComaCMS');
						$message = sprintf($this->_Translation->GetTranslation('welcome_%1\$s:Pagename_%2\$s:Benutzername_%3\$s:Password_%4\$s:Email'), $this->_Config->Get('pagename', 'ComaCMS'), $Name, $Password, $Email);
						$header = 'From: ' . $this->_Config->Get('administrator_emailaddress', 'administrator@comacms') . "\n";
						mail($Email, $title, $message, $header);
					}
				}
				
				// Insert User into database
				$sql = "INSERT INTO " . DB_PREFIX . "users
					(user_name, user_showname, user_password, user_registerdate, user_email, user_activated" . (($activationCode != '') ? ", user_activationcode" : '') . ")
					VALUES ('$Name', '$Showname', '" . md5($Password) . "', '$registrationTime', '$Email', " . (($activated) ? '1' : '0') . (($activationCode != '') ? ", '$activationCode'" : '') . ")";
				$this->_SqlConnection->SqlQuery($sql);
				
				$out .= $this->_Translation->GetTranslation('you_have_been_successfully_registred_please_check_your_emails_for_your_logininformation');
			}
			
			return $out;
	 	 }
	 	 
	 	 /**
	 	  * Activates a new account
	 	  * @access private
	 	  * @param string ActivationCode An md5 code for the new user to identify him without his username
	 	  * @return string Page
	 	  */
	 	 function _activateRegistration($ActivationCode) {
	 	 	$out = '';
	 	 	if ($this->_Config->Get('activate_throw_admin', '0')) {
	 	 		$out .= $this->_Translation->GetTranslation('activation_only_by_an_administrator_possible');
	 	 	}
	 	 	else {
	 	 		if ($ActivationCode != '') {
		 	 		$sql = "UPDATE " . DB_PREFIX . "users
						SET user_activated=1, user_activationcode=NULL
						WHERE user_activationcode='$ActivationCode'";
					$this->_SqlConnection->SqlQuery($sql);
					$out .= $this->_Translation->GetTranslation('your_account_has_been_successfully_activated');
	 	 		}
	 	 	}
	 	 	return $out;
	 	 }
 	}
?>