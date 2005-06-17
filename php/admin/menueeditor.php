<?
	include("functions.php");
	include("../config.php");
	include("../functions.php");
	if(isloggedin())
	{
		_start();
		$error = "";
		if(isset($intern_link))
		{
			if($intern_link == "")
				$link_str = $link;
			else
				$link_str = "l:".$intern_link;
			$menue_result = db_result("SELECT * FROM ".$d_pre."menue WHERE link='".$link_str."'");
			$menue_data = mysql_fetch_object($menue_result);
			if($menue_data != null)
				$error = "Für diese Seite existiert bereits ein Link.";
			if($link_str == "")
				$error = "Es wurde kein Link angegeben.";
			if($error == "")
			{
				if(@$newwindow == "on")
					$neww = "yes";
				else
					$neww = "no";

				$menue1_result = db_result("SELECT orderid FROM ".$d_pre."menue ORDER BY orderid DESC");
				$menue1_data = mysql_fetch_object($menue1_result);
				if($menue1_data != null)
					$ordid = $menue1_data->orderid + 1;
				else
					$ordid = 0;
				
				db_result("INSERT INTO ".$d_pre."menue (text, link, new, orderid) VALUES ('".$text."', '".$link_str."', '".$neww."', ".$ordid.")");
			}
		}
		if(isset($delete))
		{
			if(isset($sure))
			{
				if($sure == 1)
					db_result("DELETE FROM ".$d_pre."menue WHERE id=".$delete."");
			}
			else
			{
				$_result = db_result("SELECT * FROM ".$d_pre."menue WHERE id=".$delete."");
				$_data = mysql_fetch_object($_result);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
 <title>Men&uuml;Editor</title>
 <link rel="stylesheet" href="style.css" type="text/css"/>
</head>
<body>
  <h1>Men&uuml;Editor</h1>
  <div class="error">Soll der Link <? echo $_data->text; ?>(<? echo $_data->link; ?>) wirklich gelöscht werden?<br />
<a href="menueeditor.php?delete=<? echo $delete; ?>&amp;sure=1" title="Wirklich Löschen?">Ja</a> &nbsp;&nbsp;&nbsp; <a href="menueeditor.php" title="Nein! nicht löschen">Nein</a></div>
</body>
</html>
<?
_end();
die();
			}
		}
		if(isset($up))
		{
			$_result = db_result("SELECT * FROM ".$d_pre."menue WHERE id=".$up."");
			$_data = mysql_fetch_object($_result);
			$id1 = $_data->id;
			$orderid1 = $_data->orderid;
			$_result2 = db_result("SELECT * FROM ".$d_pre."menue WHERE orderid <".$orderid1." ORDER BY orderid DESC");
			$_data2 = mysql_fetch_object($_result2);
			if($_data2 != null)
			{
				$id2 = $_data2->id;
				$orderid2 = $_data2->orderid;
				db_result("UPDATE ".$d_pre."menue SET orderid= ".$orderid2." WHERE id=".$id1);
				db_result("UPDATE ".$d_pre."menue SET orderid= ".$orderid1." WHERE id=".$id2);
			}
			
		}
		if(isset($down))
		{
			$_result = db_result("SELECT * FROM ".$d_pre."menue WHERE id=".$down."");
			$_data = mysql_fetch_object($_result);
			$id1 = $_data->id;
			$orderid1 = $_data->orderid;
			$_result2 = db_result("SELECT * FROM ".$d_pre."menue WHERE orderid >".$orderid1." ORDER BY orderid ASC");
			$_data2 = mysql_fetch_object($_result2);
			if($_data2 != null)
			{
				$id2 = $_data2->id;
				$orderid2 = $_data2->orderid;
				db_result("UPDATE ".$d_pre."menue SET orderid= ".$orderid2." WHERE id=".$id1);
				db_result("UPDATE ".$d_pre."menue SET orderid= ".$orderid1." WHERE id=".$id2);
			}
		}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
 <title>Men&uuml;Editor</title>
 <link rel="stylesheet" href="style.css" type="text/css"/>
</head>
<body>
  <h1>Men&uuml;Editor</h1>
  <div class="error"><? echo $error; ?></div>
  <table class="linktable">
  <thead>
  <tr><td>Text</td><td>Link</td><td>Aktionen</td></tr>
  </thead>
  <tbody>
<?
		$menue_result = db_result("SELECT * FROM ".$d_pre."menue ORDER BY orderid ASC");
		while($menue_data = mysql_fetch_object($menue_result))
		{
			echo "<tr><td>".$menue_data->text."</td><td>".$menue_data->link."</td><td>
<a href=\"menueeditor.php?delete=".$menue_data->id."\" title=\"Löschen\">
<img src=\"../img/del.jpg\" height=\"16\" width=\"16\" border=\"0\" alt=\"Löschen\" />
</a>
<a href=\"menueeditor.php?up=".$menue_data->id."\" title=\"Nach Oben\">
<img src=\"../img/up.jpg\" height=\"16\" width=\"16\" border=\"0\" alt=\"Nach Oben\"/>
</a>
<a href=\"menueeditor.php?down=".$menue_data->id."\" title=\"Nach Unten\">
<img src=\"../img/down.jpg\" height=\"16\" width=\"16\" border=\"0\" alt=\"Nach Unten\"/>
</a>
</td></tr>\n";
		}
?>
  </tbody>
  </table>
  <br />
  <br />
  <form method="post" action="menueeditor.php">
  <table>
  <tr><td>Text:</td><td><input type="text" name="text" /></td></tr>
  <tr><td>Interner Link:</td><td><select name="intern_link">
  <option value="">externer Link</option>
<?
		$site_result = db_result("SELECT * FROM ".$d_pre."sitedata ORDER BY name ASC");
		while($site_data = mysql_fetch_object($site_result))
		{
				echo "  <option>".$site_data->name."</option>";
		}	

?>
  </select></td></tr>
  <tr><td>Externer Link:</td><td><input type="text" name="link" /></td></tr>
  <tr><td>Neue Fenster:</td><td><input type="checkbox" name="newwindow" /></td></tr>
  <tr><td colspan="2"><input type="submit" value="Hinzufügen" /></td></tr>
  </table>
  </form>
</body>
</html>
<?	
		_end();
	}
	else
	{
	}
?>