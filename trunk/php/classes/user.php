<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005 The ComaCMS-Team
 */
 #----------------------------------------------------------------------#
 # file			: user.php					#
 # created		: 2005-09-03					#
 # copyright		: (C) 2005 The ComaCMS-Team			#
 # email		: comacms@williblau.de				#
 #----------------------------------------------------------------------#
 # This program is free software; you can redistribute it and/or modify	#
 # it under the terms of the GNU General Public License as published by	#
 # the Free Software Foundation; either version 2 of the License, or	#
 # (at your option) any later version.					#
 #----------------------------------------------------------------------#

	/**
 	 * @ignore
 	 */
 	require_once('./classes/auth/auth_all.php');
 
 	/**
 	 * @package ComaCMS
 	 */
	class User {
		
		/**
		 * @var string
		 */
		var $OnlineID = '';
		
		/**
		 * @var string
		 */
		var $Name = '';
		
		/**
		 * @var string
		 */
		var $Showname = '';
		
		/**
		 * @var string
		 */
		var $PasswordMd5 = '';
		
		/**
		 * @var integer
		 */
		var $ID = 0;
		
		/**
		 * @var bool
		 */
		var $IsAdmin = false;
		
		/**
		 * @var bool
		 */
		var $IsLoggedIn = false;
		
		/**
		 * 
		 * @var string
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
		 * @var integer is an error disciption for better login handling
		 */
		var $LoginError = -1;
		
		/**
		 * @var Auth_All
		 */
		var $AccessRghts;
		
		/**
		 * @return void
		 */
		function User() {
			global $_COOKIE;
			
			$extern_login_name = GetPostOrGet('login_name');
			$extern_login_password = GetPostOrGet('login_password');
			$extern_lang = strtolower(GetPostOrGet('lang'));
			$languages = array('de', 'en');
			
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
				$this->Language = $languages[0];
			// Set the cookie (for the next 93(= 3x31) days)
			setcookie('ComaCMS_user_lang', $this->Language, time() + 8035200); 
		
			// Tells the cookie: "the user is logged in!"?
			if(isset($_COOKIE['ComaCMS_user'])) {
				$data = explode('|', $_COOKIE['ComaCMS_user']);
				$this->OnlineID = @$data[0];
				$this->Name = @$data[1];
				$this->PasswordMd5 = @$data[2];
			}
			// Tries somebody to log in?
			if(!empty($extern_login_name) && !empty($extern_login_password)) {
				$this->Name = $extern_login_name;
				$this->PasswordMd5 = md5($extern_login_password);
			}
			// Has the user no OnlineId? Generate one!
			if($this->OnlineID == '')
				$this->OnlineID =  md5(uniqid(rand()));
			if($extern_login_name === '' && $extern_login_password === '')
				$this->LoginError = 3;
			elseif($extern_login_name === '' && $extern_login_password !== '')
				$this->LoginError = 1;
			elseif($extern_login_name !== '' && $extern_login_password === '')
				$this->LoginError = 2;
			// Check: is the user really logged in? Or had he typed in the right name and password?
			elseif($this->Name != '' && $this->PasswordMd5 != '') {
				$sql = "SELECT *
					FROM " . DB_PREFIX . "users
					WHERE user_name='$this->Name' AND user_password='$this->PasswordMd5'
					LIMIT 0,1";
				$original_user_result = db_result($sql);
				if($original_user = mysql_fetch_object($original_user_result)) {
					$this->IsLoggedIn = true;
					$this->Showname = $original_user->user_showname;
					$this->ID = $original_user->user_id;
					if($original_user->user_admin == 'y')
						$this->IsAdmin = true;
					$this->LoginError = 0;
				}
				else {
					$this->IsAdmin = false;
					$this->IsLoggedIn = false;
					$this->Name = '';
					$this->PasswordMd5 = '';
					$this->LoginError = 4;
				}
			}
			$this->accessRghts = new Auth_All($this->ID);
			if(!$this->IsAdmin) 
				$this->accessRghts->Load();
			else
				$this->accessRghts->setAdmin();
			
			// Set the cookie (for the next 4 hours) 
			setcookie('ComaCMS_user', $this->OnlineID . '|' . $this->Name . '|' . $this->PasswordMd5, time() + 14400);
			
		}
		
		/**
		 * @return void
		 * @param string page
		 * @param Config config
		 */
		function SetPage($page, $config) {
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
			$result_new = db_result($sql);
			if($row3 = mysql_fetch_object($result_new)) {
				$sql = "UPDATE " . DB_PREFIX . "online
					SET lastaction='" . mktime() . "', userid=$this->ID, lang='$this->Language', page='$page'
					WHERE online_id='$this->OnlineID'";
				db_result($sql);
			}
			else {
				$sql = "INSERT INTO " . DB_PREFIX . "online (online_id, ip, lastaction, page, userid, lang, host)
				VALUES ('$this->OnlineID', '$REMOTE_ADDR', '" . mktime() . "', '$page', $this->ID, '$this->Language', '" . gethostbyaddr($REMOTE_ADDR). "')";
				db_result($sql);
				$counter_all++;
			}

			// set the new counterstatus with the count of all users who visted the site since countig
			if($counter_all != 1)
				$config->Save('counter_all', $counter_all);
			
			// delete all enries with a last action which is more than 20 minutes passed
			$sql = "DELETE FROM " . DB_PREFIX . "online
				WHERE lastaction < '" . (mktime() - 1200) . "'";
			db_result($sql);
			
		}
	}
?>
