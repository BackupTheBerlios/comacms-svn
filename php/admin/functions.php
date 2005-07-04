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
		if(strtolower(@$data->name) == strtolower(@$username))
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
	<head>
		<title>Admin-Login</title>
	</head>
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
		foreach ($matches[1] as $key => $match) 
		{
			$codes[$key] = $matches[1][$key];
			$text = str_replace($matches[1][$key],"%".$key."%",$text);
		}

		$text = preg_replace("/\*\*(.+?)\*\*/s", "<strong>$1</strong>", $text);	//Bold

		$text = preg_replace("/\/\/(.+?)\/\//s", "<em>$1</em>", $text);		//Italic

		$text = preg_replace("/__(.+?)__/s", "<u>$1</u>", $text);

		$text = preg_replace("/\[ul\](.+?)\[\/ul\]/s", "<ul>$1</ul>", $text); 
		$text = preg_replace("/\[li\](.+?)\[\/li\]/s", "<li>$1</li>", $text); 
		$text = preg_replace("/\[code\](.+?)\[\/code\]/s", "<pre class=\"code\">$1</pre>", $text); //underline
		$text = preg_replace("/===\ (.+?)\ ===/s", "<h3>$1</h3><hr />", $text); //header
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
		
		return $text;
	}

	function special_convert($name,$before,$after,$end)
	{


	}	

?>