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
		$return_str .= "\t\t\t<div class=\"news\">
				<div class=\"news-title\">
					" . $row->title . "
					<span class=\"news-title\">".date("d.m.Y H:i:s",$row->date)."</span>
				</div>
				" . nl2br($row->text) . "
				<span class=\"news-author\">&nbsp;" . getUserByID($row->userid) . "</span>
				</div>\r\n";	
	}
	
	return $return_str;
}
?>