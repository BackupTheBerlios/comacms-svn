<?
$en = array("is","are");
$en_count = array("no","one");
$de = array("ist","sind");
$de_count = array("kein","ein","zwei","drei","vier","fünf","sechs","sieben","acht","zehn");
$internal_counter_online = 0;
$internal_counter_online_text = ":-)";
function counter_set()
{
	global $internal_counter_start_date,$internal_counter_all, $d_pre,$_site,$REMOTE_ADDR;
	$result = db_result("SELECT * FROM ".$d_pre."vars WHERE name='counter_start_date'");
	if($row =  mysql_fetch_object($result))
	{
		$internal_counter_start_date = $row->value;
	}
	else
	{
		$internal_counter_start_date = date("d.m.Y",mktime());
		db_result("INSERT INTO ".$d_pre."vars (name, value) VALUES ('counter_start_date', '".$internal_counter_start_date."')");
	}
	
	$result2 = db_result("SELECT * FROM ".$d_pre."vars WHERE name='counter_all'");
	$new = 0;
	if($row2 =  mysql_fetch_object($result2))
	{
		
		$internal_counter_all = $row2->value;
	}
	else
	{
		$internal_counter_all = 0;
		$new = 1;
	}
	$result_new = db_result("SELECT * FROM ".$d_pre."online WHERE ip='".$REMOTE_ADDR."'");
	if($row3 = mysql_fetch_object($result_new))
	{
		db_result("UPDATE ".$d_pre."online SET lastaction='".mktime()."', page='".$_site."' WHERE ip='".$REMOTE_ADDR."'");
	}
	else
	{
		db_result("INSERT INTO ".$d_pre."online (ip, lastaction, page) VALUES ('".$REMOTE_ADDR."', '".mktime()."', '".$_site."')");
		$internal_counter_all++;
	}
	if($new == 1)
	{
		db_result("INSERT INTO ".$d_pre."vars (name, value) VALUES ('counter_all', '1')");
	}
	else
	{
		db_result("UPDATE ".$d_pre."vars SET value='".$internal_counter_all."' WHERE name='counter_all'");
	}
	
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