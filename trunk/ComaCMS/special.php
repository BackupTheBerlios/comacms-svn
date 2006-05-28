<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005-2006 The ComaCMS-Team
 */
 #----------------------------------------------------------------------#
 # file			: special.php					#
 # created		: 2005-08-10					#
 # copyright		: (C) 2005-2006 The ComaCMS-Team		#
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
	define("COMACMS_RUN", true);
 
	include('common.php');
	
	/**
	 * @ignore
	 */
	include('./lang/' . $user->Language  . '/admin_lang.php');
	
	if(!isset($extern_page))
		header('Locaction: index.php');
	$text = '';
	$title = '';
	if($extern_page == 'login') {
		$error = GetPostOrGet('error');
		$text_error = '';
		if($error == '1')
			$text_error = 'Der Login wurde nicht angegeben.';
		if($error == '2')
			$text_error = 'Das Passwort wurde nicht angegeben.';
		if($error == '3')
			$text_error = 'Es wurden keine Eingaben gemacht.';
		if($error == '4')
			$text_error = 'Der Benutzer und(oder) das Passwort sind falsch.';
		if($text_error != '')
			$text_error = "\r\n<strong>" . $text_error . '</strong>';
		$title = "Login";
		$text = "<form method=\"post\" action=\"admin.php\">
			<input type=\"hidden\" name=\"page\" value=\"admincontrol\" />$text_error
			<fieldset>
				<legend>Login</legend>
				<div class=\"row\"><label>Loginname:</label><input type=\"text\" name=\"login_name\" /></div>
				<div class=\"row\"><label>Passwort:</label><input type=\"password\" name=\"login_password\" /></div>
				<div class=\"row\"><input type=\"submit\" value=\"Login\" class=\"button\"/></div>
			</fieldset>
		</form>";
	}
	elseif($extern_page == '404') {	
		$want = GetPostOrGet('want');
		$title = 'Seite nicht gefunden.';
		$text = "Die Seite mit dem Namen &quot;$want&quot; wurde leider nicht gefunden.<br />
			Falls die Seite aber da sein m&uuml;sste, melden sie sich bitte beim Seitenbetreiber.";
	}
	elseif($extern_page == '410') {	//Gone/Deleted
		$text = ' '; 
	}
	elseif($extern_page == 'image') {
		
		$imageID = GetPostOrGet('id');
		$imageFile = GetPostOrGet('file');
		if(is_numeric($imageID) || !empty($imageFile)) {
			$title = 'Bild';
			$condition = 'file_id = ' . $imageID;
			if(empty($imageID))
				$condition = "file_name = '$imageFile'";
			$sql = "SELECT *
				FROM " . DB_PREFIX . "files
				WHERE $condition
				LIMIT 0,1";
			$imageResult = $sqlConnection->SqlQuery($sql);
			if($imageData = mysql_fetch_object($imageResult)) {
				$text = "<img src=\"" . generateUrl($imageData->file_path) ."\"/>";
			}
		}
	}
	if($text == '') {
		header('Location: index.php');
		die();
	}
	$output->Title = $title;
	$output->SetReplacement('TEXT', $text);
	$output->SetReplacement('PATH', "<a href=\"special.php?page=$extern_page\">$title</a>");
	$output->SetCondition('notathome', true);
	$outputpage = new OutputPage($sqlConnection);
	$output->SetReplacement('MENU' , $outputpage->GenerateMenu());
	$output->SetReplacement('MENU2' , $outputpage->GenerateMenu(2));
	$output->GenerateOutput();
	echo $output->GeneratedOutput;
	echo "\r\n<!-- rendered in " . round(getmicrotime(microtime()) - getmicrotime($starttime), 4) . ' seconds with ' . $sqlConnection->QueriesCount .' SQL queries -->';
?>