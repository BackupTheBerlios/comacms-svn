<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005-2006 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : registration.php
 # created              : 2005-11-23
 # copyright            : (C) 2005-2006 The ComaCMS-Team
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
 	
 	/**
 	 * User-Interface-Page to register at the system
 	 * @package ComaCMS
 	 */
 	class Registration {
 		
 		/**
 		 * @access private
 		 * @var SqlConnection
 		 */
 		var $_SqlConnection;
 		
 		/**
 		 * @access private
 		 * @var AdminLang
 		 * @
 		 */
 		var $_AdminLang;
 		
 		/**
 		 * @access private
 		 * @var Config
 		 */
 		var $_Config;
 		
 		/**
 		 * Initializes the Registration class
 		 * @access public
 		 * @param SqlConnection SqlConnection Connection to the MysqlDatabase
 		 * @param array AdminLang Array with texts in the language of the current user
 		 * @param config Config Access to the Configurations
 		 * @return void
 		 */
	 	function Registration(&$SqlConnection, &$AdminLang, &$Config) {
			$this->_SqlConnection = &$SqlConnection;
			$this->_AdminLang = &$AdminLang;
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
		 		case 'checkRegistration':	$out .= $this->_checkRegistration(GetPostOrGet('showname'), GetPostOrGet('name'), GetPostOrGet('email'), GetPostOrGet('password'), GetPostOrGet('password_repetition'));
		 						break;
		 		case 'activateRegistration':	$out .= $this->_activateRegistration();
		 						break;
		 		default:			$out .= $this->_register();
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
									<strong>{$this->_AdminLang['name']}:</strong>" . (($ShownameError != '') ? "\r\n\t\t\t\t\t\t\t\t\t<span class=\"error\">{$ShownameError}</span>\r\n\t\t\t\t\t\t\t\t\t" : '') . "
									<span class=\"info\">{$this->_AdminLang['the_name_that_is_displayed_if_the_user_writes_a_news_for_example']}</span>
								</label>
								<input type=\"text\" name=\"showname\" id=\"showname\" value=\"{$Showname}\" />
							</div>
							<div class=\"row\">
								<label for=\"name\">
									<strong>{$this->_AdminLang['loginname']}:</strong>" . (($NameError != '') ? "\r\n\t\t\t\t\t\t\t\t\t<span class=\"error\">{$NameError}</span>\r\n\t\t\t\t\t\t\t\t\t" : '') . "
									<span class=\"info\">{$this->_AdminLang['with_this_nick_the_user_can_login_so_he_must_not_fill_in_his_long_name']}</span>
								</label>
								<input type=\"text\" name=\"name\" id=\"name\" value=\"{$Name}\" />
							</div>
							<div class=\"row\">
								<label for=\"email\">
									<strong>{$this->_AdminLang['email']}:</strong>" . (($EmailError != '') ? "\r\n\t\t\t\t\t\t\t\t\t<span class=\"error\">{$EmailError}</span>\r\n\t\t\t\t\t\t\t\t\t" : '') . "
									<span class=\"info\">{$this->_AdminLang['using_the_email_address_the_user_is_contacted_by_the_system']}</span>
								</label>
								<input type=\"text\" name=\"email\" id=\"email\" value=\"{$Email}\" />
							</div>
							<div class=\"row\">
								<label for=\"password\">
									<strong>{$this->_AdminLang['password']}:</strong>" . (($PasswordError != '') ? "\r\n\t\t\t\t\t\t\t\t\t<span class=\"error\">{$PasswordError}</span>\r\n\t\t\t\t\t\t\t\t\t" : '') . "
									<span class=\"info\">{$this->_AdminLang['with_this_password_the_user_can_login_to_restricted_areas']}</span>
								</label>
								<input type=\"password\" name=\"password\" id=\"password\" />
							</div>
							<div class=\"row\">
								<label for=\"password_repetition\">
									<strong>{$this->_AdminLang['password_repetition']}:</strong>
									<span class=\"info\">{$this->_AdminLang['it_is_guaranteed_by_a_repetition_that_the_user_did_not_mistype_during_the_input']}</span>
								</label>
								<input type=\"password\" name=\"password_repetition\" id=\"password_repetition\" />
							</div>
							<div class=\"row\">
								<input type=\"reset\" class=\"button\" value=\"{$this->_AdminLang['reset']}\" />
								<input type=\"submit\" class=\"button\" value=\"{$this->_AdminLang['save']}\" />
							</div>
						</fieldset>
					</form>";
			return $out;
	 	}
	 	
	 	/**
	 	 * Checks the registration form, returns a new one if there are any mistakes, and saves the new user if everything is correct
	 	 * @access private
	 	 * @return string PageData
	 	 */
		function _checkRegistration($Showname, $Name, $Email, $Password, $Password_repetition) {
			$out = '';
			$fehlerfrei = true;
			
			$ShownameError = '';
			$NameError = '';
			$EmailError = '';
			$PasswordError = '';
			
			if ($Showname == '') {
				$ShownameError = $this->_AdminLang['the_name_must_be_indicated'];
				$fehlerfrei = false;
			}
			if ($Name == '') {
				$NameError = $this->_AdminLang['the_nickname_must_be_indicated'];
				$fehlerfrei = false;
			}
			if ($Email == '') {
				$EmailError = $this->_AdminLang['the_email_address_must_be_inicated'];
				$fehlerfrei = false;
			}
			elseif (!isEmailAddress($Email)) {
				$EmailError = $this->_AdminLang['this_is_not_a_valid_email_address'];
				$fehlerfrei = false;
			}
			if ($Password == '' || $Password_repetition == '') {
				$PasswordError = $this->_AdminLang['none_of_the_passwordfields_must_not_be_empty'];
				$fehlerfrei = false;
			}
			elseif ($Password != $Password_repetition) {
				$PasswordError = $this->_AdminLang['the_password_and_its_repetition_are_unequal'];
				$fehlerfrei = false;
			}
			
			if (!$fehlerfrei) {
				$out .= $this->_registerVar($Showname, $Name, $Email, $ShownameError, $NameError, $EmailError, $PasswordError);
			}
			
			return $out;
	 	 }
 	}
?>