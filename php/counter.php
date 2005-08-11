<?php
/*****************************************************************************
 *
 *  file		: counter.php
 *  created		: 2005-06-17
 *  copyright		: (C) 2005 The Comasy-Team
 *  email		: comasy@williblau.de
 *
 *****************************************************************************/

/*****************************************************************************
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *****************************************************************************/

	$en = array('is', 'are');
	$en_count = array('no', 'one');
	$de = array('ist', 'sind');
	$de_count = array('kein', 'ein', 'zwei', 'drei', 'vier', 'fünf', 'sechs', 'sieben', 'acht', 'zehn');
	$internal_counter_online = 0;
	$internal_counter_online_text = ":-)";

	function counter_set($site) {
		global $internal_counter_all, $internal_counter_start_date, $actual_user_online_id, $actual_user_id,$actual_user_lang, $REMOTE_ADDR;
		
		//
		// is the counter counting the first time ever?
		//
		if($internal_counter_start_date == "") {
			$internal_counter_start_date = mktime();
			db_result("INSERT INTO ".DB_PREFIX."config (config_name, config_value) VALUES ('counter_start_date', '".$internal_counter_start_date."')");
		}
	
		if($internal_counter_all == "") {
			$internal_counter_all = 0;
			db_result("INSERT INTO ".DB_PREFIX."config (config_name, config_value) VALUES ('counter_all', '1')");
		}
		//
		// check if the user is new on the page
		//
		$result_new = db_result("SELECT * FROM " . DB_PREFIX . "online WHERE online_id='$actual_user_online_id'");
		if($row3 = mysql_fetch_object($result_new)) {
			$sql = "UPDATE " . DB_PREFIX . "online
				SET lastaction='" . mktime() . "', userid=$actual_user_id, lang='$actual_user_lang', page='$site'
				WHERE online_id='$actual_user_online_id'";
			db_result($sql);
		}
		else {
			$sql = "INSERT INTO " . DB_PREFIX . "online (online_id, ip, lastaction, page, userid, lang)
			VALUES ('$actual_user_online_id', '$REMOTE_ADDR', '" . mktime() . "', '$site', $actual_user_id, '$actual_user_lang')";
			db_result($sql);
			$internal_counter_all++;
		}
		//
		// set the new counterstatus with the count of all users who visted the site since countig
		//
		if($internal_counter_all != 1) {
			$sql = "UPDATE " . DB_PREFIX . "config
			SET config_value='$internal_counter_all'
			WHERE config_name='counter_all'";
			db_result($sql);
		}
	
	
		//
		// delete all enries with a last action which is more than 20 minutes passed
		//
		$sql = "DELETE FROM " . DB_PREFIX . "online
		WHERE lastaction < '" . (mktime() - 1200) . "'";
		db_result($sql);
	}

	function actual_online() {
		global $internal_counter_online_text, $internal_counter_online;
		$sql = "SELECT * 
			FROM " . DB_PREFIX . "online";
		$result = db_result($sql);
		$internal_counter_online = mysql_num_rows($result);
		//
		// FIX ME: no language
		//
		$lang = 'de';
		$temp = $lang . '_count';
		global $$temp, $$lang;
		
		$text_array = $$temp;
		$plural_array = $$lang;
		if(count($text_array) - 1 > $internal_counter_online)
			$text = $text_array[$internal_counter_online];
		else
			$text = $internal_counter_online;
		if($internal_counter_online > 1)	
			$internal_counter_online_text = $plural_array[1] . ' ' . $text;
		else
	 		$internal_counter_online_text = $plural_array[0] . ' ' . $text;
	}
?>