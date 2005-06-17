<?
include_once("functions.php");

function getNews($last = 6)
{
global $d_pre;
	$sql_str = "SELECT * FROM ".$d_pre."news ORDER BY date DESC LIMIT 0, ".$last."";
	$result = db_result($sql_str);
	$return_str = "";
	while($row = mysql_fetch_object($result))
	{
		$return_str .= "<div class=\"news\">";
		$return_str .= "<div class=\"news-title\">";
		$return_str .= $row->title;
		$return_str .= "<span class=\"news-title\">".date("d.m.Y H:i:s",$row->date)."</span>";
		$return_str .= "</div>";
		$return_str .= nl2br($row->text)."";
		$return_str .= "<span class=\"news-author\">&nbsp;".getUserByID($row->userid)."</span>";
		$return_str .= "</div>";	
	}
	
	return $return_str;
}
?>