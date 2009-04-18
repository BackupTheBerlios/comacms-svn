<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005-2007 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : user.php
 # created              : 2005-09-03
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
 	require_once __ROOT__ . '/classes/auth/auth_all.php';
 
 	/**
 	 * @package ComaCMS
 	 */
	class Account {
		
		/**
		 * @access public
		 * @var string The online id of the user to change his informations in the online table
		 */
		var $OnlineID = '';
		
		/**
		 * @access public
		 * @var string The name of the user if he is logged in
		 */
		var $Name = '';
		
		/**
		 * @access public
		 * @var string The showname of the user if he is logged in
		 */
		var $Showname = '';
		
		/**
		 * @access private
		 * @var string The md5 code of the password during the login of the user
		 */
		var $PasswordMd5 = '';
		
		/**
		 * @access public
		 * @var integer The ID of the user in the system
		 */
		var $ID = 0;
		
		/**
		 * @access public
		 * @var bool Is the user an administrator?
		 */
		var $IsAdmin = false;
		
		/**
		 * @access public
		 * @var bool Is the user an author?
		 */
		var $IsAuthor = false;
		
		/**
		 * @access public
		 * @var bool Is the user logged in to a valid user account?
		 */
		var $IsLoggedIn = false;
		
		/**
		 * @access public
		 * @var string The language that the user has choosen
		 */
		var $Language = '';
		
		/**
		 * LoginError
		 * 
		 * - -1: no attempt to log in
		 * -  0: Everything is OK
		 * -  1: No name
		 * -  2: No password
		 * -  3: Nothing of both
		 * -  4: Sorry wrong data
		 * -  5: Sorry the user is not activated
		 * @access public
		 * @var integer is an error disciption for better login handling
		 */
		var $LoginError = -1;
		
		/**
		 * @access public
		 * @var Auth_All
		 */
		var $AccessRghts;
		
		/**
		 * @access private
		 * @var Sql A link to the SqlConnection class
		 */
		var $_SqlConnection;
		
		/**
		 * @access private
		 * @var Config A link to the local Configuration class to get standart settings from the page
		 */
		var $_Config;
		
		/**
		 * @param Sql &$SqlConnection A link to the SqlConnection class
		 * @return void
		 */
		function Account(&$SqlConnection, &$Config) {
			
			global $_COOKIE;
			$this->_SqlConnection = &$SqlConnection;
			$this->_Config = &$Config;
			$extern_login_name = GetPostOrGet('login_name');
			$extern_login_password = GetPostOrGet('login_password');
			$extern_lang = strtolower(GetPostOrGet('lang'));
			$languages = array('de', 'en');
			
			// Tells the cookie: "the user is logged in!"?
			if(isset($_COOKIE['ComaCMS_user'])) {
				//$data = explode('|', $_COOKIE['ComaCMS_user']);
				$this->OnlineID = $_COOKIE['ComaCMS_user'];
				//$this->Name = @$data[1];
				//$this->PasswordMd5 = @$data[2];
			}
			// Tries somebody to log in?
			if(!empty($extern_login_name) && !empty($extern_login_password)) {
				$this->Name = $extern_login_name;
				$this->_PasswordMd5 = md5($extern_login_password);
			}
			// Has the user no OnlineId? Generate one!
			$newOnlineID = false;
			if($this->OnlineID == '') {
				$this->OnlineID =  md5(uniqid(rand()));
				$newOnlineID = true;
			}
				
			if($extern_login_name === '' && $extern_login_password === '')
				$this->LoginError = 3;
			elseif($extern_login_name === '' && $extern_login_password !== '')
				$this->LoginError = 1;
			elseif($extern_login_name !== '' && $extern_login_password === '')
				$this->LoginError = 2;
			// Check: had the user typed in the right name and password?
			elseif($this->Name != '' && $this->_PasswordMd5 != '') {

				$sql = "SELECT *
					FROM " . DB_PREFIX . "users
					WHERE user_name='$this->Name'
					LIMIT 1";
				$original_user_result = $this->_SqlConnection->SqlQuery($sql);
				if($original_user = mysql_fetch_object($original_user_result)) {
					// If the user was found check if it is activated
					if ($original_user->user_activated == '1') {
						// If the user is activated check if the typed password is right
						if ($original_user->user_password === $this->_PasswordMd5) {
							$this->IsLoggedIn = true;
							$this->Showname = $original_user->user_showname;
							$this->Name = $original_user->user_name;
							$this->ID = $original_user->user_id;
							if($original_user->user_admin == '1')
								$this->IsAdmin = true;
							if($original_user->user_author == '1' || $original_user->user_admin == '1')
								$this->IsAuthor = true;
							$this->LoginError = 0;
						}
						// else set user back to login
						else {
							$this->IsAdmin = false;
							$this->IsAuthor = false;
							$this->IsLoggedIn = false;
							$this->Name = '';
							$this->_PasswordMd5 = '';
							$this->LoginError = 4;
						}
					}
					else {
						// If the user is not activated set him back to login and throw exception
						$this->IsAdmin = false;
						$this->IsAuthor = false;
						$this->IsLoggedIn = false;
						$this->Name = '';
						$this->_PasswordMd5 = '';
						$this->LoginError = 5;
					}
				}
				else {
					// If the user was not found set him back to login
					$this->IsAdmin = false;
					$this->IsAuthor = false;
					$this->IsLoggedIn = false;
					$this->Name = '';
					$this->_PasswordMd5 = '';
					$this->LoginError = 4;
				}
			}
			// Is he logged on? check the data behind his OnlineID!
			elseif($this->OnlineID != '' && !$newOnlineID) {
				$sql  = "SELECT user.user_showname, user.user_admin, user.user_author, user.user_name, user.user_id, online.online_loggedon, online.online_ip
					FROM (
						". DB_PREFIX . "users user LEFT JOIN " . DB_PREFIX . "online online
						ON online.online_userid = user.user_id
					)
					WHERE online.online_id = '{$this->OnlineID}'
					LIMIT 1";		
				$onlineUserResult = $this->_SqlConnection->SqlQuery($sql);
				if($onlineUser = mysql_fetch_object($onlineUserResult)) {
					$ip = getenv ('REMOTE_ADDR');
					// the user has the same ip and is saved as logged on? Give him his rights!
					if($ip == $onlineUser->online_ip && $onlineUser->online_loggedon == 'yes') {
						$this->IsLoggedIn = true;
						$this->Showname = $onlineUser->user_showname;
						$this->Name = $onlineUser->user_name;
						$this->ID = $onlineUser->user_id;
						if($onlineUser->user_admin == '1')
							$this->IsAdmin = true;
						if($onlineUser->user_author == '1' || $onlineUser->user_admin == '1')
							$this->IsAuthor = true;
						$this->LoginError = 0;
					}
					else {
						$this->ID = $onlineUser->user_id;
						$this->IsAdmin = false;
						$this->IsAuthor = false;
						$this->IsLoggedIn = false;
						$this->Name = '';
						$this->_PasswordMd5 = '';
						$this->LoginError = -1;
					}
				}		
			}
			if($this->IsLoggedIn) {
				$this->accessRghts = new Auth_All($this->_SqlConnection, $this->ID);
				
			if(!$this->IsAdmin) 
				$this->accessRghts->Load();
			else
				$this->accessRghts->setAdmin();
			}
			// Set the cookie (for the next 1 hour/3600 seconds) 
			setcookie('ComaCMS_user', $this->OnlineID, time() + 3600);
			
			// Check: has the user changed the language by hand?
			if(!empty($extern_lang)) {
				if(in_array($extern_lang, $languages))
					$this->Language = $extern_lang;
			}
			// Get the language from the cookie if it' s not changed
			elseif(isset($_COOKIE['ComaCMS_user_lang'])) {
				if(in_array($_COOKIE['ComaCMS_user_lang'], $languages))
					$this->Language = $_COOKIE['ComaCMS_user_lang'];
			}
			elseif ($this->IsLoggedIn) {
				$sql = "SELECT user_preferred_language
						FROM " . DB_PREFIX . "users
						WHERE user_id='$this->ID'";
				$userResult = $this->_SqlConnection->SqlQuery($sql);
				if ($user = mysql_fetch_object($userResult))
					$this->Language == $user->user_preferred_language;
			}
			
			// if no language is set, load the language from the HTTP-header
			if($this->Language == '') {
				if(isset($_ENV['HTTP_ACCEPT_LANGUAGE'])) {
					$langs = $_ENV['HTTP_ACCEPT_LANGUAGE'];
					$langs = preg_replace("#\;q=[0-9\.]+#i", '', $langs);
					$langs = explode(',', $langs);
					$this->Language = $languages[0];
					foreach($langs as $lang) {
						if(in_array($lang, $languages)) {
							$this->Language = $lang;
							break;
						}
					}
				}
			}
			if($this->Language == '')
				$this->Language = $this->_Config->Get('default_langugage', 'en');
			// Set the cookie (for the next 93(= 3x31) days)
			setcookie('ComaCMS_user_lang', $this->Language, time() + 8035200); 
		}
		
		/**
		 * Sets the actual page of the user to the database
		 * @access public
		 * @param string $page The actual page
		 * @param Config &$config A link to the config
		 * @return void 
		 */
		function SetPage($page, &$Config) {
			
			$config = &$Config;
			global $REMOTE_ADDR;
			
			$counter_start_date = $config->Get('counter_start_date');
			$counter_all = $config->Get('counter_all');
			
			// is the counter counting the first time ever?
			if($counter_start_date == '') {
				$counter_start_date = mktime();
				$config->Save('counter_start_date', $counter_start_date);
			}
			if($counter_all == '') {
				$counter_all = 0;
				$config->Save('counter_all', 1);
			}
			
			// check if the user is new on the page
			$sql = "SELECT *
				FROM " . DB_PREFIX . "online
				WHERE online_id='$this->OnlineID'
				LIMIT 0,1";
			$result_new = $this->_SqlConnection->SqlQuery($sql);
			if($row3 = mysql_fetch_object($result_new)) {
				$sql = "UPDATE " . DB_PREFIX . "online
					SET online_lastaction='" . mktime() . "', online_userid=$this->ID, online_lang='$this->Language', online_page='$page', online_loggedon = '" . (($this->IsLoggedIn) ? 'yes' : 'no' ) . "'
					WHERE online_id='$this->OnlineID'";
				$this->_SqlConnection->SqlQuery($sql);
			}
			else {
				// get the ip of the user
				$ip = getenv ('REMOTE_ADDR');
				// add the online-record for the user
				$sql = "INSERT INTO " . DB_PREFIX . "online (online_id, online_ip, online_lastaction, online_page, online_userid, online_lang, online_host, online_loggedon)
				VALUES ('$this->OnlineID', '$ip', '" . mktime() . "', '$page', $this->ID, '$this->Language', '" . gethostbyaddr($ip) . "', '" . (($this->IsLoggedIn) ? 'yes' : 'no' ) . "')";
				$this->_SqlConnection->SqlQuery($sql);
				$counter_all++;
			}

			// set the new counterstatus with the count of all users who visted the site since countig
			if($counter_all != 1 && $counter_all != $config->Get('counter_all'))
				$config->Save('counter_all', $counter_all);
			
			// delete all enries with a last action which is more than 20 minutes passed
			$sql = "DELETE FROM " . DB_PREFIX . "online
				WHERE online_lastaction < '" . (mktime() - 1200) . "'";
			$this->_SqlConnection->SqlQuery($sql);
			
		}
		
		function Logout() {
			if($this->IsLoggedIn) {
				$sql = $sql = "UPDATE " . DB_PREFIX . "online
					SET online_loggedon = 'no'
					WHERE online_id='$this->OnlineID'";
				$this->_SqlConnection->SqlQuery($sql);
			}
		}
	}
?>
