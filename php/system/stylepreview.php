<?
	@include("../functions.php");
	@include("../config.php");
	$title = "Vorschausage";
	$text = "Das hier ist eine <b>Testseite</b>...";
	if(!isset($style))
	{
		$style = "clear";
	}
	_start();	
	$menue = generatemenue(@$style,1,"","..");
	$stylefile = "../styles/".$style."/mainpage.php";
	$_file = fopen($stylefile, "r");
	$page = fread($_file, filesize($stylefile));
	
	$page = str_replace("[title]", $title, $page);
	$page = str_replace("[text]", $text, $page);
	$page = str_replace("[menue]", $menue, $page);
	$page = str_replace("\"./styles", "\"../styles", $page);
	_end();
	echo $page;

?>