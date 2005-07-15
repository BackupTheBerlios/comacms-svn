<?
$en = array("is","are");
$en_count = array("no","one");
$de = array("ist","sind");
$de_count = array("kein","ein","zwei","drei","vier","fünf","sechs","sieben","acht","zehn");
$internal_counter_online = 0;
$internal_counter_online_text = ":-)";

function counter_set($index_prefix, $site) {
	global $d_pre, $internal_counter_all, $internal_counter_start_date,
	$actual_user_online_id, $actual_user_id,$actual_user_lang, $REMOTE_ADDR;
	
	//is the counter counting the first time ever?
	if($internal_counter_start_date == "")
	{
		$internal_counter_start_date = mktime();
		db_result("INSERT INTO ".$d_pre."vars (name, value) VALUES ('counter_start_date', '".$internal_counter_start_date."')");
	}
	
	if($internal_counter_all == "") {
		$internal_counter_all = 0;
		db_result("INSERT INTO ".$d_pre."vars (name, value) VALUES ('counter_all', '1')");
	}
	// check if the user is new on the page
	$result_new = db_result("SELECT * FROM ".$d_pre."online WHERE online_id='".$actual_user_online_id."'");
	if($row3 = mysql_fetch_object($result_new)) {
		db_result("UPDATE ".$d_pre."online SET lastaction='".mktime()."', userid=".$actual_user_id.", lang='".$actual_user_lang."', page='".$index_prefix.$site."' WHERE online_id='".$actual_user_online_id."'");
	}
	else {
		db_result("INSERT INTO ".$d_pre."online (online_id, ip, lastaction, page, userid, lang) VALUES ('".$actual_user_online_id."', '".$REMOTE_ADDR."', '".mktime()."', '".$index_prefix.$site."', ".$actual_user_id.", '".$actual_user_lang."')");
		$internal_counter_all++;
	}
	// set the new counterstatus with the count of all users who visted the site since countig
	if($internal_counter_all != 1)
		db_result("UPDATE ".$d_pre."vars SET value='".$internal_counter_all."' WHERE name='counter_all'");
	
	
	
	// delete all enries with a last action which is more than 20 minutes passed
	db_result("DELETE FROM ".$d_pre."online WHERE lastaction < '".(mktime()-(20*60))."'");
}

function actual_online()
{
	global $internal_counter_online_text, $internal_counter_online,$d_pre;
	$result = db_result("SELECT * FROM ".$d_pre."online");
	$internal_counter_online = mysql_num_rows($result);
	//FIX ME: no language
	$lang = 'de';
	$temp = $lang."_count";
	global $$temp, $$lang;
	$text_array = $$temp;
	$plural_array = $$lang;
	if(count($text_array) - 1 > $internal_counter_online)
		$text = $text_array[$internal_counter_online];
	else
		$text = $internal_counter_online;
	if($internal_counter_online > 1)	
		$internal_counter_online_text = $plural_array[1]." ".$text;
	else
 		$internal_counter_online_text = $plural_array[0]." ".$text;
}
?>