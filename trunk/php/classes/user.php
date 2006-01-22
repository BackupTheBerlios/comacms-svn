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
 	require_once('./classes/auth_all.php');
 
 	/**
 	 * @package ComaCMS
 	 */
	class User {
		
		/**
		 * @var string
		 */
		var $onlineID = '';
		
		/**
		 * @var string
		 */
		var $name = '';
		
		/**
		 * @var string
		 */
		var $showname = '';
		
		/**
		 * @var string
		 */
		var $passwordMd5 = '';
		
		/**
		 * @var integer
		 */
		var $id = 0;
		
		/**
		 * @var bool
		 */
		var $isAdmin = false;
		
		/**
		 * @var bool
		 */
		var $isLoggedIn = false;
		
		/**
		 * 
		 * @var string
		 */
		var $language = '';
		
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
		var $loginError = -1;
		
		/**
		 * @var Auth_All
		 */
		var $accessRghts;
		
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
					$this->language = $extern_lang;
			}
			// Get the language from the cookie if it' s not changed
			elseif(isset($_COOKIE['ComaCMS_user_lang'])) {
				if(in_array($_COOKIE['ComaCMS_user_lang'], $languages))
					$this->language = $_COOKIE['ComaCMS_user_lang'];
			}
			// if no language is set, load the language from the HTTP-header
			if($this->language == '') {
				if(isset($_ENV['HTTP_ACCEPT_LANGUAGE'])) {
					$langs = $_ENV['HTTP_ACCEPT_LANGUAGE'];
					$langs = preg_replace("#\;q=[0-9\.]+#i", '', $langs);
					$langs = explode(',', $langs);
					$this->language = $languages[0];
					foreach($langs as $lang) {
						if(in_array($lang, $languages)) {
							$this->language = $lang;
							break;
						}
					}
				}
			}
			if($this->language == '')
				$this->language = $languages[0];
			// Set the cookie (for the next 93(= 3x31) days)
			setcookie('ComaCMS_user_lang', $this->language, time() + 8035200); 
		
			// Tells the cookie: "the user is logged in!"?
			if(isset($_COOKIE['ComaCMS_user'])) {
				$data = explode('|', $_COOKIE['ComaCMS_user']);
				$this->onlineID = @$data[0];
				$this->name = @$data[1];
				$this->passwordMd5 = @$data[2];
			}
			// Tries somebody to log in?
			if(!empty($extern_login_name) && !empty($extern_login_password)) {
				$this->name = $extern_login_name;
				$this->passwordMd5 = md5($extern_login_password);
			}
			// Has the user no OnlineId? Generate one!
			if($this->onlineID == '')
				$this->onlineID =  md5(uniqid(rand()));
			if($extern_login_name === '' && $extern_login_password === '')
				$this->loginError = 3;
			elseif($extern_login_name === '' && $extern_login_password !== '')
				$this->loginError = 1;
			elseif($extern_login_name !== '' && $extern_login_password === '')
				$this->loginError = 2;
			// Check: is the user really logged in? Or had he typed in the right name and password?
			elseif($this->name != '' && $this->passwordMd5 != '') {
				$sql = "SELECT *
					FROM " . DB_PREFIX . "users
					WHERE user_name='$this->name' AND user_password='$this->passwordMd5'
					LIMIT 0,1";
				$original_user_result = db_result($sql);
				if($original_user = mysql_fetch_object($original_user_result)) {
					$this->isLoggedIn = true;
					$this->showname = $original_user->user_showname;
					$this->id = $original_user->user_id;
					if($original_user->user_admin == 'y')
						$this->isAdmin = true;
					$this->loginError = 0;
				}
				else {
					$this->isAdmin = false;
					$this->isLoggedIn = false;
					$this->name = '';
					$this->passwordMd5 = '';
					$this->loginError = 4;
				}
			}
			$this->accessRghts = new Auth_All($this->id);
			if(!$this->isAdmin) 
				$this->accessRghts->Load();
			else
				$this->accessRghts->setAdmin();
			
			// Set the cookie (for the next 4 hours) 
			setcookie('ComaCMS_user', $this->onlineID . '|' . $this->name . '|' . $this->passwordMd5, time() + 14400);
			
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
				WHERE online_id='$this->onlineID'
				LIMIT 0,1";
			$result_new = db_result($sql);
			if($row3 = mysql_fetch_object($result_new)) {
				$sql = "UPDATE " . DB_PREFIX . "online
					SET lastaction='" . mktime() . "', userid=$this->id, lang='$this->language', page='$page'
					WHERE online_id='$this->onlineID'";
				db_result($sql);
			}
			else {
				$sql = "INSERT INTO " . DB_PREFIX . "online (online_id, ip, lastaction, page, userid, lang, host)
				VALUES ('$this->onlineID', '$REMOTE_ADDR', '" . mktime() . "', '$page', $this->id, '$this->language', '" . gethostbyaddr($REMOTE_ADDR). "')";
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
