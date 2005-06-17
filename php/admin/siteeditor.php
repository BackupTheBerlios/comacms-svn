<?
	include("functions.php");
	include("../config.php");
	include("../functions.php");
	if(isloggedin())
	{
		_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
 <title>SeitenEditor</title>
 <link rel="stylesheet" href="style.css" type="text/css"/>
</head>
<body>
  <h1>SeitenEditor</h1>
<table><tr><td>
  <form method="post" action="">
  <select name="site" size="1">
<? 
		$site_result = db_result("SELECT * FROM ".$d_pre."sitedata");
		while($site_data = mysql_fetch_object($site_result))
		{
			if($site_data->name == @$site) 
				echo "<option selected>".$site_data->name."</option>";
			else
				echo "<option>".$site_data->name."</option>";
		}
?>
</select><input type="submit" value="Öffnen" ></form></td>
<?
		if(!isset($new))
		{
?>
<td>
<form name="newsite" method="post">
<input name="new" >
<input type="submit" value="Neu" ></form>
</td>
<?
		}
?>
</tr></table>
<hr >
<?
		$s_lang = "de";
		$s_name = "";
		$s_title = "";
		$s_text = "";
		$new_b = true;
	
		if(isset($save))
		{
			if($save == "true")
			{
				$title = @$_POST['title'];
				$lang = @$_POST['lang'];
				$text = @$_POST['text'];
				$name = @$_POST['name'];
				$forward = true;
				echo "<ul>";
				if($name == "")
				{
					echo "<li>Die Seite benötigt einen Namen um abgespeichert zu werden.</li>";
					$forward = false;
				}
				echo "</ul>";
				if(!$forward)
				{
?>
</body>
</html>
<?
					die();
				}	
				$site_result = db_result("SELECT * FROM ".$d_pre."sitedata WHERE name='".$name."'");
				$site_data = mysql_fetch_object($site_result);
				$html = convertToPreHtml($text);
				if($site_data == null)
				{
					$result = db_result("INSERT INTO ".$d_pre."sitedata (name, title, text, lang, html) VALUES ('".$name."', '".$title."', '".$text."', '".$lang."', '".$html."')");
				}
				else
				{
					db_result("UPDATE ".$d_pre."sitedata SET title= '".$title."', lang= '".$lang."', text= '".$text."', html= '".$html."' WHERE id=".$site_data->id);
				}

?>
</body>
</html>
<?
				_end();
				die();
			}
		}
		if(isset($site))
		{
			$site_result = db_result("SELECT * FROM ".$d_pre."sitedata WHERE name='".$site."'");
			$site_data = mysql_fetch_object($site_result);
			$s_text = $site_data->text;
			$s_title = $site_data->title;
			$s_lang = $site_data->lang;
			$s_name = $site_data->name;
			$new_b = false;
		}

		if(isset($new))
		{
			$s_name = $new;
			$new_b = true;
		}
?>
<form name="save" method="post">
<input type="hidden" name="save" value="true" />
<table>
<tr><td>Name:</td><td><input <? if(!$new_b) echo "readonly=\"true\"";?>" name="name" value="<? echo $s_name; ?>" ></td></tr>
<tr><td>Titel:</td><td><input name="title" value="<? echo $s_title; ?>"></td></tr>
<tr><td>Sprache:</td><td><select name="lang">
	<option value="de" <?if($s_lang == "de") echo "selected=\"selected\" ";?>>Deutsch</option>
	<option value="en" <?if($s_lang == "en") echo "selected=\"selected\" ";?>>Englisch</option>
</select></td></tr>
<tr><td colspan="2"><textarea cols="85" rows="18" name="text"><? echo $s_text; ?></textarea></td></tr>
<tr><td>
<input type="reset" value="Zurücksetzen" ></td><td><input type="submit" value="Speichern" ></td></tr>
</table>
</form>
</body>
</html>
<?
		_end();
	}
	else
	{
	login();
	}
?>