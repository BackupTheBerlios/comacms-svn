<?
function isloggedin()
{
global $name,$password,$_COOKIE;
@include("../config.php");
$username = "";
$userpassword = "";
if(isset($name) && isset($password))
{
$username = $name;
$userpassword = md5($password);
}
elseif(isset($_COOKIE["CMS_user_cookie"]))
{
$data = explode("|",$_COOKIE["CMS_user_cookie"]);
$username = $data[0];
//print_r($data);
//echo "<br>";
$userpassword = $data[1];
}
//print_r($_COOKIE);

$connection = mysql_connect($d_server, $d_user, $d_pw) or die(mysql_error());
mysql_select_db($d_base, $connection) or die(mysql_error());
 $query = "SELECT * FROM ".$d_pre."users WHERE name='".$username."' AND password='".$userpassword."' AND admin='y'";
//echo "<br>";
//echo $query;
  $result = mysql_query($query, $connection) or die(mysql_error());
$data = mysql_fetch_object($result);
mysql_close($connection);
if(@$data->name == "")
return false;
if(@$data->name == @$username)
{
setcookie ("CMS_user_cookie", $data->name."|".$data->password, time()+5600); 
return true;
}
else
return false;

}
function login()
{
global $PHP_SELF;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head><title>Admin-Login</title></head>
<body>
<form method="post" action="<?echo $PHP_SELF;?>">
<table>
<tr><td>Loginname:</td><td><input type="text" name="name" /></td></tr>
<tr><td>Passwort:</td><td><input type="password" name="password" /></td></tr>
<tr><td colspan="2"><input type="submit" value="Login" /></td></tr>
</table>

</form>


</body>

</html>

<?
}
function alt($link)
{
$text = preg_replace("/(.+?)\|(.+$)/s","$1\" alt=\"\\2",$link);
echo $link."<br>".$text."<br>";
return $text;
}

function convertToPreHtml($text)
{
	//include_once("../gbook.php");
$text = htmlspecialchars($text);
preg_match_all("/\[code\](.+?)\[\/code\]/s", $text, $matches);
$codes = array();
//foreach ($matches as $value) 
//{
	//echo $value."<br>";
	foreach ($matches[1] as $key => $match) 
	{
		
		//echo $key."=>".$matsch."<br>";
		$codes[$key] = $matches[1][$key];
		$text = str_replace($matches[1][$key],"%".$key."%",$text);
	}
//}
$text = preg_replace("/\[b\](.+?)\[\/b\]/s", "<b>$1</b>", $text); //bold
$text = preg_replace("/\[i\](.+?)\[\/i\]/s", "<i>$1</i>", $text); //italic
$text = preg_replace("/\[u\](.+?)\[\/u\]/s", "<u>$1</u>", $text); //underline
$text = preg_replace("/\[ul\](.+?)\[\/ul\]/s", "<ul>$1</ul>", $text); 
$text = preg_replace("/\[li\](.+?)\[\/li\]/s", "<li>$1</li>", $text); 
$text = preg_replace("/\[code\](.+?)\[\/code\]/s", "<pre class=\"code\">$1</pre>", $text); //underline
$text = preg_replace("/\[t\](.+?)\[\/t\]/s", "<h3>$1</h3><hr />", $text); //header
$text = preg_replace("/\[img:(.+?)\]/s", "<img src=\"\\1\" />", $text);    	
$text = preg_replace("/<img src=\"(.+?)\|(.+?)\" \/>/s", "<img src=\"$1\" title=\"$2\" alt=\"$2\"/>", $text);    	
$text = preg_replace("/\[style:(.+?)\](.+?)\[\/style\]/s", "<p style=\"$1\">$2</p>", $text);
$text = preg_replace("/\[link:(.+?)\](.+?)\[\/link\]/s", "<a href=\"$1\" >$2</a>", $text);
$text = preg_replace("/\[linkex:(.+?)\](.+?)\[\/linkex\]/s", "<a href=\"$1\" target=\"_blank\">$2</a>", $text);
$text = preg_replace("/\"l:(.+?)\"/s","\"index.php?site=$1\"", $text);
$text = preg_replace("/\"([A-Za-z]{1,})\.(.+?)\.([a-zA-Z.]{2,6}(|\/.+?))\"/s","\"http://$1.$2.$3\"", $text);//"repai" urls
$text = preg_replace("/<a href=\"(.+?)\|(.+?)\" >/s", "<a href=\"$1\" title=\"$2\">", $text);
$text = nl2br($text);
foreach($codes as $key => $match)
{
$text = str_replace("%".$key."%",$match,$text);
}
/*
	$text = str_replace("[b]","<b>",$text);
	$text = str_replace("[/b]","</b>",$text);
	$text = str_replace("[i]","<i>",$text);
	$text = str_replace("[/i]","</i>",$text);
	$text = str_replace("[u]","<u>",$text);
	$text = str_replace("[/u]","</u>",$text);
	$text = str_replace("[t]","<h3>",$text);
	$text = str_replace("[/t]","</h3>\n<hr />",$text);
	//$text = str_replace("[gbook-input]", gbook_input(), $text);
	$text = preg_replace('/__(.+?)__/s','<u>\1</u>',$text); 
	while(eregi("\[img:*", $text))
	{
		$pos = strpos ($text, "[img:");
		$pos2 = strpos ($text, "]",$pos);
		$str = substr($text,$pos + 5,$pos2 - $pos - 5);
		$imgs = explode("|",$str);
		$text = str_replace("[img:".$str."]", "<img src=\"".$imgs[0]."\" title=\"".@$imgs[1]."\" alt=\"".@$imgs[1]."\" border=\"0\" />", $text);
	}

	while(strpos($text, "[style:"))
	{
		$pos = strpos ($text, "[style:");
	
		$pos2 = strpos ($text, "]",$pos);
	
		$str = substr($text,$pos + 7,$pos2 - $pos - 7);
		
		$text = str_replace("[style:".$str."]", "<p style=\"".$str."\">", $text);
	
	
	}

	while(strpos($text, "[link:"))
	{
		$pos = strpos ($text, "[link:");
	
		$pos2 = strpos ($text, "]",$pos);
	
		$str = substr($text,$pos + 6,$pos2 - $pos - 6);
		$str2 = $str;
		if(substr($str,0,2)=="l:")
			$str2 = "index.php?site=".substr($str,2);
		$text = str_replace("[link:".$str."]", "<a href=\"".$str2."\">", $text);
	
	
	}
	$text = str_replace("[/link]","</a>",$text);
	$text = str_replace("[/style]","</p>",$text);
	*/
	
	return $text;
}

function special_convert($name,$before,$after,$end)
{


}


?>