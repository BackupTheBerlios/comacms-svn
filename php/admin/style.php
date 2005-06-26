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
	if(isset($speichern))
	{
		db_result("UPDATE ".$d_pre."vars SET value= '".$style."' WHERE name='style'");
	}
?>
		<iframe src="stylepreview.php?style=<?php echo $style; ?>" name="test" class="stylepreview"></iframe>
		<form action="style.php">
			<label for="stylepreviewselect">Style:<select id="stylepreviewselect" name="style" size="1">
<?
	$verz = dir("../styles/");

	while($entry = $verz->read()) 
	{
		if($entry != "." && $entry != ".." && file_exists("../styles/".$entry."/mainpage.php") && $entry == $style)
			echo "\t\t\t<option selected=\"selected\">".$entry."</option>\n\r";
		elseif($entry != "." && $entry != ".." && file_exists("../styles/".$entry."/mainpage.php"))
			echo "\t\t\t<option>".$entry."</option>";
	}
	$verz->close();
?>
			</select></label>


			<input type="submit" value="Test" name="test" />
			<input type="submit" value="Speichern" name="speichern" />

		</form>
	</body>
</html>
<?
		_end();
	}
	else
	{	
		_login();
	}
?>