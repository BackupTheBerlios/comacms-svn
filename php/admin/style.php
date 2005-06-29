<?
	include("functions.php");
	include("../config.php");
	include("../functions.php");
	if(isloggedin())
	{
		_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="de">
	<head>
		<title>Seitenstyle</title>
		<link rel="stylesheet" type="text/css" href="style.css"/>
		<script type="text/javascript" language="JavaScript" src="functions.js"></script>
	</head>
	<body>
<?
	//load vars
	$var_result = db_result("SELECT * FROM ".$d_pre."vars");
	while($var_data = mysql_fetch_object($var_result))
	{
		$_N_ = "internal_".$var_data->name;
		$$_N_ = $var_data->value;
	}
	//end
	if(!isset($style))
	{
		$object = mysql_fetch_object(db_result("SELECT * FROM ".$d_pre."vars WHERE name='style'"));
		$style = $object->value;
		
	}
	if(isset($save))
	{
		if(file_exists("../styles/".$style."/mainpage.php"))
			db_result("UPDATE ".$d_pre."vars SET value= '".$style."' WHERE name='style'");
	}
?>
		<iframe id="previewiframe" src="stylepreview.php?style=<?php echo $style; ?>" class="stylepreview"></iframe>
		<form action="style.php" method="post">
			<label for="stylepreviewselect">Style:
				<select id="stylepreviewselect" name="style" size="1">
<?
	$verz = dir("../styles/");

	while($entry = $verz->read()) 
	{
		if($entry != "." && $entry != ".." && file_exists("../styles/".$entry."/mainpage.php") && $entry == $style)
			echo "\t\t\t\t\t<option value=\"".$entry."\" selected=\"selected\">".$entry."</option>\r\n";
		elseif($entry != "." && $entry != ".." && file_exists("../styles/".$entry."/mainpage.php"))
			echo "\t\t\t\t\t<option value=\"".$entry."\">".$entry."</option>\r\n";
	}
	$verz->close();
?>
				</select>
			</label>

			<input type="submit" value="Vorschau" onclick="preview_style();return false;" name="preview" />
			<input type="submit" value="Speichern" name="save" />

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