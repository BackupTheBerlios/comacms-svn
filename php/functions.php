<?
function _start()
{
	global $db_con,$d_user,$d_base,$d_pw,$pin,$session,$d_server;
	$db_con = connect_to_db($d_user,$d_pw,$d_base,$d_server);
}

function _end()
{
	global $db_con;
	mysql_close($db_con);
}

function connect_to_db($username, $userpw, $database, $server = "localhost")
{
	error_reporting( E_ALL );
	$db = mysql_connect($server, $username, $userpw) or die(mysql_error());
		mysql_select_db($database, $db) or die(mysql_error());
	return $db;
}

function db_result($command)
{
	global $db_con;
		$result = mysql_query ($command, $db_con);
	if (!$result)
		echo ('Error: ' . $command . ':' . mysql_error () . ';');
	return $result;
}

function generate_password($length)
{
	$abc = array("1","2","3","4","5","6","7","8","9","0","a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","r","t","u","v","w","x","y","z","1","2","3","4","5","6","7","8","9","0","A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","R","T","U","V","W","X","Y","Z","1","2","3","4","5","6","7","8","9","0");
	$out = "";
	for($i = 0;$i < $length;$i++)
	{
	$out .=  $abc[rand(0,count($abc)-1)];
	}
	return $out;
}

function check_user($name,$pw)
{
	if($name == "")
		return false;
	
	$result = db_result("SELECT * FROM users WHERE name = '$name'");
	
	$row = mysql_fetch_object ($result);
		if($row->pw == $pw)
			$ret = true;
		else
			$ret = false;
	
	return $ret;
}
 
function sendmail($to,$from,$title,$text)
{
	$to = strtolower($to);
	$from = strtolower($from);
	$header="From:$from\n";
	$header .= "Content-Type: text/html"; 
	return mail($to, $title, $text, $header);  
}

function getmicrotime($mic_time)
{
	list($usec, $sec) = explode(" ",$mic_time);
	return ((float)$usec + (float)$sec);
}

function writelog($text)
{
	$handle = fopen ("log.log", "a");
	fwrite($handle,$text."\n");
	fclose ($handle);
}

function getUserIDByName($name)
{
	global $d_pre;
	$result = db_result("SELECT * FROM ".$d_pre."users WHERE name='$name'");
	$row = mysql_fetch_object($result);
	return $row->id;
}

function getUserByID($id = "1")
{
	global $d_pre;
	$result = db_result("SELECT * FROM ".$d_pre."users WHERE id = '$id'");
	$row = mysql_fetch_object($result);
	return $row->showname;
}

function replace_smilies($textdata)
{
	$smilies_path = "data/smilies";

	$textdata = str_replace("??:-)",	"<img src=\"".$smilies_path."/uneasy.gif\" />",$textdata);
	$textdata = str_replace(":-)",		"<img src=\"".$smilies_path."/icon_smile.gif\" />",$textdata);
	$textdata = str_replace(";-)",		"<img src=\"".$smilies_path."/icon_wink.gif\" />",$textdata);
	$textdata = str_replace(":-&lt;",	"<img src=\"".$smilies_path."/icon_sad.gif\" />",$textdata);
	$textdata = str_replace(":-<",		"<img src=\"".$smilies_path."/icon_sad.gif\" />",$textdata);
	$textdata = str_replace(":-X",		"<img src=\"".$smilies_path."/xx.gif\" />",$textdata);
	$textdata = str_replace("8-)",		"<img src=\"".$smilies_path."/icon_cool.gif\" />",$textdata);
	$textdata = str_replace("=D&gt;",	"<img src=\"".$smilies_path."/clap.gif\" />",$textdata);
	$textdata = str_replace("=D>",		"<img src=\"".$smilies_path."/clap.gif\" />",$textdata);
	$textdata = str_replace(":music:",	"<img src=\"".$smilies_path."/dance.gif\" />",$textdata);
	$textdata = str_replace(":n&ouml;:","<img src=\"".$smilies_path."/noe.gif\" />",$textdata);
	$textdata = str_replace("](*,)",	"<img src=\"".$smilies_path."/wall.gif\" />",$textdata);
	$textdata = str_replace(":-~",		"<img src=\"".$smilies_path."/confused.gif\" />",$textdata);
	$textdata = str_replace(":cry:",	"<img src=\"".$smilies_path."/cry.gif\" />",$textdata);
	$textdata = str_replace("lol",		"<img src=\"".$smilies_path."/lol.gif\" />",$textdata);
	$textdata = str_replace("LOL",		"<img src=\"".$smilies_path."/lol.gif\" />",$textdata);
	$textdata = str_replace(":-/",		"<img src=\"".$smilies_path."/neutral.gif\" />",$textdata);
	$textdata = str_replace(":-D",		"<img src=\"".$smilies_path."/razz.gif\" />",$textdata);
	$textdata = str_replace("??:-)",	"<img src=\"".$smilies_path."/neutral.gif\" />",$textdata);
	$textdata = str_replace(":nö:",		"<img src=\"".$smilies_path."/noe.gif\" />",$textdata);
	$textdata = str_replace(":noe:",	"<img src=\"".$smilies_path."/noe.gif\" />",$textdata);
	$textdata = str_replace(":-O",		"<img src=\"".$smilies_path."/oo.gif\" />",$textdata);
	$textdata = str_replace(":devil:",	"<img src=\"".$smilies_path."/devil.gif\" />",$textdata);
	$textdata = str_replace(":love:",	"<img src=\"".$smilies_path."/love.gif\" />",$textdata);
	return $textdata;
}
function generatemenue($style = "clear", $selected = "", $style_root = ".")
{
	global $internal_page_root, $d_pre;
	$menue = " ";
	include($style_root."/styles/".$style."/menue.php");
	$menue_result = db_result("SELECT * FROM ".$d_pre."menue ORDER BY orderid ASC");
	while($menue_data = mysql_fetch_object($menue_result))
	{
		$menue_str = $menue_link;
		$menue_str = str_replace("[text]",$menue_data->text,$menue_str);
		$link = $menue_data->link;
		if(substr($link,0,2) == "l:")
			$link = @$internal_page_root."index.php?site=".substr($link,2);
		if(substr($link,0,2) == "g:")
			$link = @$internal_page_root."gallery.php?site=".substr($link,2);

		$menue_str = str_replace("[link]",$link,$menue_str);
		$new = $menue_data->new;
		if($new == "yes")
			$new = "target=\"_blank\" ";
		else
			$new = "";
		$menue_str = str_replace("[new]",$new,$menue_str);
		$menue .= $menue_str."\n";
	}

	return $menue;
}

?>