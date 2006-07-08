<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : install.php
 # created              : 2005-06-1
 # copyright            : (C) 2005-2006 The ComaCMS-Team
 # email                : comacms@williblau.de
 #----------------------------------------------------------------------
 # This program is free software; you can redistribute it and/or modify
 # it under the terms of the GNU General Public License as published by
 # the Free Software Foundation; either version 2 of the License, or
 # (at your option) any later version.
 #----------------------------------------------------------------------
	
	error_reporting(E_ALL);
	require_once '../functions.php';
	$language = '';
	// try to get the language-setting from the language-cookie
	if(isset($_COOKIE['ComaCMS_user_lang'])) {
		// check if the language-file is available
		if(file_exists("../lang/{$_COOKIE['ComaCMS_user_lang']}/admin_lang.php"))
			$language = $_COOKIE['ComaCMS_user_lang'];
	}
	if($language == '') {
		// there was no (valid) language-setting in the cookie try to get the default language of the browser/user
		if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
			$langs = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
			// remove all unneeded things in the information
			$langs = preg_replace("#\;q=[0-9\.]+#i" , '' , $langs);
			$langs = explode(',' , $langs);
			//$language = $languages[0];
			// finde a matching language
			foreach($langs as $lang) {
				if(file_exists("../lang/{$lang}/admin_lang.php")) {
					$language = $lang;
					break;
				}
			}
		}
	}
	// tries someone to set the language 'by hand'?
	$external_lang = GetPostOrGet('lang');
	if($external_lang != '')
		if(file_exists("../lang/{$external_lang}/admin_lang.php"))
			$language = $external_lang;
	// if there is no language use english ;-)
	if($language == '')
		$language == 'en';
	// Set the cookie (for the next 93(= 3x31) days)
	setcookie('ComaCMS_user_lang', $language, time() + 8035200);
	include "../lang/{$language}/admin_lang.php";
	$step = 1;
	$content = '';
	$step = GetPostOrGet('step');
	$confirmation = GetPostOrGet('confirmation');
	if(!is_numeric($step))
		$step = 1;
	if($step == 1) { // Language check
		$content = "<input type=\"hidden\" name=\"step\" value=\"2\" />
				<legend>{$admin_lang['language']}</legend>
				<div class=\"row\">
					<label>
						<strong>{$admin_lang['language']}:</strong>
						<span class=\"info\">{$admin_lang['please_select_your_language']}</span>
					</label>
					<select name=\"lang\">\r\n";
		$languageFolder = dir("../lang/");
		// read the available language-files
		while($folder = $languageFolder->read()) {
			// check if the language-file really exists
			if($folder != "." && $folder != ".." && file_exists("../lang/{$folder}/admin_lang.php")) {
				if($language == $folder)
					$content .= "<option selected=\"selected\" value=\"{$folder}\">";
				else
					$content .= "<option value=\"{$folder}\">";
				// try to 'translate' the language
				if(array_key_exists($folder, $admin_lang))
					$content .= "{$admin_lang[$folder]}</option>";
				else
					$content .= "{$folder}</option>";
			}
		}
		$content .=	"</select>
				</div>
				<div class=\"row\">
					<input type=\"submit\" value=\"{$admin_lang['next']}\"/>
				</div>";
		
	}
	elseif($step == 2) { // requirements check
		$phpversion_min = '4.3.0';
		$content = "<input type=\"hidden\" name=\"step\" value=\"3\" />
				<input  type=\"hidden\" name=\"lang\" value=\"{$language}\" />
				<legend>{$admin_lang['requirements']}</legend>
				<div class=\"row\">" . sprintf($admin_lang['is_the_file_%file%_writeable'], '/config.php') . ": <strong>";
		$ok = true;
		if(!file_exists('../config.php')) {
			$writeHandle = @fopen ('../config.php', 'w');
			if($writeHandle !== false) {
				@fwrite($writeHandle, "<?php\r\n//empty config will call the installation-scrip\r\n?>");
				@fclose($writeHandle);
			}
		}
			
		if(is_writable('../config.php'))
			$content .= $admin_lang['yes']; 
		else {
			$content .= $admin_lang['no'];
			$ok = false;
		}	
		$content .= "</strong></div>
			<div class=\"row\">" . sprintf($admin_lang['is_the_directory_%directory%_writeable'], '/data/') . ": <strong>";
		$ok = true;
		if(is_writable('../data/'))
			$content .= $admin_lang['yes']; 
		else {
			$content .= $admin_lang['no'];
			$ok = false;
		}	
		$content .= "</strong></div>
			<div class=\"row\">{$admin_lang['php_version']} > {$phpversion_min}?: <strong>";
		if(version_compare($phpversion_min, phpversion(), '<=') == 1)
			$content .= $admin_lang['yes']; 
		else {
			$content .= $admin_lang['no'];
			$ok = false;
		}
		$content .= "</strong></div>\r\n";
		if($ok)
			$content .= "<div class=\"row\">
				<input type=\"submit\" value=\"{$admin_lang['next']}\"/>
			</div>";
		else
			$content .= "<div class=\"row\">{$admin_lang['please_try_to fix_these_problems_to_finish_the_installation']}<br /><a class=\"button\" href=\"install.php?step=2&amp;lang={$language}\">{$admin_lang['reload']}</a></div>";
	}
	elseif($step == 3) {
		$content = "<input type=\"hidden\" name=\"step\" value=\"4\" />
				<input  type=\"hidden\" name=\"lang\" value=\"{$language}\" />
				<legend>{$admin_lang['license']}</legend>
				<div class=\"row\">
				<textarea readonly=\"readonly\" id=\"license\" rows=\"17\">" . file_get_contents('license.txt') . "</textarea>
			</div>
			<div class=\"row\">
				<label for=\"confirmation\">
					<strong>{$admin_lang['i_agree']}:</strong>
					<span class=\"info\">{$admin_lang['do_you_agee_with_this_conditions']}</span>
				</label>
				<input type=\"checkbox\" value=\"yes\" name=\"confirmation\" id=\"confirmation\" />
			</div>
			<div class=\"row\">
				<input type=\"submit\" value=\"{$admin_lang['next']}\"/>
			</div>";
	}
	elseif($step == 4 && $confirmation == 'yes') {
		$content = "<input type=\"hidden\" name=\"step\" value=\"5\" />
				<input  type=\"hidden\" name=\"lang\" value=\"{$language}\" />
				<input  type=\"hidden\" name=\"confirmation\" value=\"yes\" />
				<legend>{$admin_lang['database_settings']}</legend>
				<div class=\"row\">
					<label for=\"database_server\">
						<strong>{$admin_lang['database_server']}:</strong>
						<span class=\"info\">{$admin_lang['todo']}</span>
					</label>
					<input type=\"text\" name=\"database_server\" id=\"database_server\" value=\"localhost\"/>
				</div>
				<div class=\"row\">
					<label for=\"database_name\">
						<strong>{$admin_lang['database_name']}:</strong>
						<span class=\"info\">{$admin_lang['todo']}</span>
					</label>
					<input type=\"text\" name=\"database_name\" id=\"database_name\"/>
				</div>
				<div class=\"row\">
					<label for=\"database_username\">
						<strong>{$admin_lang['database_username']}:</strong>
						<span class=\"info\">{$admin_lang['todo']}</span>
					</label>
					<input type=\"text\" name=\"database_username\" id=\"database_username\"/>
				</div>
				<div class=\"row\">
					<label for=\"database_password\">
						<strong>{$admin_lang['database_password']}:</strong>
						<span class=\"info\">{$admin_lang['todo']}</span>
					</label>
					<input type=\"password\" name=\"database_password\" id=\"database_password\"/>
				</div>
				<div class=\"row\">
					<label for=\"database_prefix\">
						<strong>{$admin_lang['prefix_for_tables']}:</strong>
						<span class=\"info\">{$admin_lang['todo']}</span>
					</label>
					<input type=\"text\" name=\"database_prefix\" id=\"database_prefix\" value=\"comacms_\"/>
				</div>
			<div class=\"row\">
				<input type=\"submit\" value=\"{$admin_lang['next']}\"/>
			</div>
		";
	}
	elseif($step == 4 && $confirmation != 'yes') {
		$content = "<a class=\"button\" href=\"install.php?lang={$language}&step=3\">{$admin_lang['back']}</a>";
	}
	elseif($step == 5 && $confirmation == 'yes') {
		require_once '../classes/sql.php';
		$database_server = GetPostOrGet('database_server');
		$database_name = GetPostOrGet('database_name');
		$database_username = GetPostOrGet('database_username');
		$database_password = GetPostOrGet('database_password');
		$database_prefix = GetPostOrGet('database_prefix');
		$file = 'sql/install.sql';
		
		$sqlConnection = new Sql($database_username, $database_password, $database_server);
		$sqlConnection->Connect($database_name);
		$fileHandle = fopen($file, "r");
		// Read the whole file
	  	$queries = str_replace('{DB_PREFIX}', $database_prefix, fread($fileHandle, filesize($file)));
		// Close the handle
	  	fclose($fileHandle);
		$sqlConnection->SqlExecMultiple($queries);
		
		
		$config_data = "<?php\n";
		$config_data .= '$d_server = \'' . $database_server.'\';' . "\r\n";
		$config_data .= '$d_user   = \'' . $database_username . '\';' . "\r\n";
		$config_data .= '$d_pw     = \'' . $database_password . '\';' . " \r\n";
		$config_data .= '$d_base   = \'' . $database_name . '\';' . "\r\n";
		$config_data .= '$d_pre = \'' . $database_prefix . '\';' . " \r\n\r\n";
		$config_data .= 'define(\'COMACMS_INSTALLED\', true);' . "\r\n";
		$config_data .= '?>';
		$fp = @fopen('../config.php', 'w');
		$result = @fputs($fp, $config_data, strlen($config_data));
		@fclose($fp);
		$content = "<input type=\"hidden\" name=\"step\" value=\"6\" />
				<input  type=\"hidden\" name=\"lang\" value=\"{$language}\" />
				<input  type=\"hidden\" name=\"confirmation\" value=\"yes\" />
				<legend>{$admin_lang['create_administrator']}</legend>
				<div class=\"row\">
					<label for=\"admin_showname\">
						<strong>{$admin_lang['name']}:</strong>
						<span class=\"info\">{$admin_lang['todo']}</span>
					</label>
					<input type=\"text\" name=\"admin_showname\" id=\"admin_showname\"/>
				</div>
				<div class=\"row\">
					<label for=\"admin_name\">
						<strong>{$admin_lang['loginname']}:</strong>
						<span class=\"info\">{$admin_lang['todo']}</span>
					</label>
					<input type=\"text\" name=\"admin_name\" id=\"admin_name\"/>
				</div>
				<div class=\"row\">
					<label for=\"admin_password\">
						<strong>{$admin_lang['password']}:</strong>
						<span class=\"info\">{$admin_lang['todo']}</span>
					</label>
					<input type=\"password\" name=\"admin_password\" id=\"admin_password\"/>
				</div>
				<div class=\"row\">
					<label for=\"admin_password2\">
						<strong>{$admin_lang['password_repetition']}:</strong>
						<span class=\"info\">{$admin_lang['todo']}</span>
					</label>
					<input type=\"password\" name=\"admin_password2\" id=\"admin_password2\"/>
				</div>
				
				<div class=\"row\">
					<input type=\"submit\" value=\"{$admin_lang['next']}\"/>
				</div>
		";
	}
	elseif($step == 6 && $confirmation == 'yes') {
		$admin_name = GetPostOrGet('admin_name');
		$admin_showname = GetPostOrGet('admin_showname');
		$admin_password = GetPostOrGet('admin_password');
		$admin_password2 = GetPostOrGet('admin_password2');
		include '../config.php';
		require_once '../classes/sql.php';
		$sql = "INSERT INTO {$d_pre}users (user_name, user_showname, user_password, user_registerdate, user_admin, user_icq)
		VALUES ('$admin_name', '$admin_showname', '" . md5($admin_password) . "', '" . mktime() . "', 'y', '');
		INSERT INTO {$d_pre}config (config_name, config_value)
		VALUES ('install_date', '" . mktime() . "');
		INSERT INTO {$d_pre}pages (page_lang, page_access, page_name, page_title, page_parent_id, page_creator, page_type, page_date, page_edit_comment)
		VALUES('de', 'public', 'home', '{$admin_lang['homepage']}', 0, 1, 'text', " . mktime() . ", 'Installed the Homepage');";
		
		
		
		//TODO: make sure that the id of the default page is everytime the right one 
	
		$ok = true;
		if($admin_name == "" || $admin_showname == "" || $admin_password == "") {
			$content = $admin_lang['the_form_was_not_filled_in_completely']; "Die Angaben zum Adminaccount sind unvollst&auml;ndig.";
			$content .= "<a class=\"button\" href=\"install.php?lang={$language}&step=3\">{$admin_lang['back']}</a>";
			$ok = false;
		}
	
		if($admin_password != $admin_password2) {
			$content = $admin_lang['the_repetition_of_the_password_was_incorrect']; //"Das Passwort wurde nicht korrekt wiederholt";
			$content .= "<a class=\"button\" href=\"install.php?lang={$language}&step=3\">{$admin_lang['back']}</a>";
			$ok = false;
		}
		if($ok) {	
			$sqlConnection = new Sql($d_user, $d_pw, $d_server);
			$sqlConnection->Connect($d_base);
			$sqlConnection->SqlExecMultiple($sql);
			$lastid =  mysql_insert_id();
			$sql = "INSERT INTO {$d_pre}pages_text (page_id, text_page_text,text_page_html)
				VALUES ($lastid, '{$admin_lang['welcome_to_this_homepage']}', '{$admin_lang['welcome_to_this_homepage']}')";
			$sqlConnection->SqlQuery($sql);
			$content  = $admin_lang['installation_complete'];
		}
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
		<title><?php echo $admin_lang['installation'] . ': ' . $admin_lang['step'] . ' ' . $step; ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<link rel="stylesheet" href="./install.css" type="text/css" media="all" />
		<link rel="stylesheet" href="./style.css" type="text/css" media="all" />
	</head>
	<body>
	<h1><?php echo $admin_lang['installation'] . ': ' . $admin_lang['step'] . ' ' . $step; ?></h1>
	<div id="list">
	<ol>
<?php
		$pages = array(
			1 => $admin_lang['language'],
			2 => $admin_lang['requirements'],
			3 => $admin_lang['license'],
			4 => $admin_lang['database_settings'],
			5 => $admin_lang['create_administrator']
			);
		foreach($pages as $stepnr => $name) {
			if($stepnr == $step)
				echo "<li class=\"actual\"><a href=\"install.php?step={$stepnr}&amp;confirmation={$confirmation}\">{$name}</a></li>\r\n";
			elseif($stepnr < $step)
				echo "<li><a href=\"install.php?step={$stepnr}&amp;confirmation={$confirmation}\">{$name}</a></li>\r\n";
			else
				echo "<li>{$name}</li>\r\n";
		}
		
?>
	</ol>
	</div>
	<div id="text">
		<form action="install.php">
			<fieldset>
				<?php echo $content; ?>
			</fieldset>
		</form>
	</div>
	</body>
</html>