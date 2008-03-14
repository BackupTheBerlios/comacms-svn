<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005-2007 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : textactions.php
 # created              : 2006-10-06
 # copyright            : (C) 2005-2007 The ComaCMS-Team
 # email                : comacms@williblau.de
 #----------------------------------------------------------------------
 # This program is free software; you can redistribute it and/or modify
 # it under the terms of the GNU General Public License as published by
 # the Free Software Foundation; either version 2 of the License, or
 # (at your option) any later version.
 #----------------------------------------------------------------------
 	
 	/**
 	 * 
 	 */
 	require_once __ROOT__ . '/functions.php';
 	require_once __ROOT__ . '/classes/imageconverter.php';
 	
 	/**
 	 * @access private
 	 */
 	define('LINE_STATE_NONE', 0);
 	/**
 	 * @access private
 	 */
 	define('LINE_STATE_TEXT', 1);
 	/**
 	 * @access private
 	 */
 	define('LINE_STATE_ULIST', 2);
 	/**
 	 * @access private
 	 */
 	define('LINE_STATE_OLIST', 3);
 	/**
 	 * @access private
 	 */
 	define('LINE_STATE_TABLE', 4);
 	/**
 	 * @access private
 	 */
 	define('LINE_STATE_HEADER', 5);
 	/**
 	 * @access private
 	 */
 	define('LINE_STATE_DEFINITON', 6);
 	
 	/**
 	 * Do nothing with email-address-links
 	 */
 	define('EMAIL_DEFAULT', 0);
 	/**
 	 * Convert email-addresses into a 'text-format'
 	 * @ => [at]
 	 * . => [dot]
 	 */
 	define('EMAIL_ANTISPAM_TEXT', 1);
 	/**
 	 * Convert email-adresses into html-hex-entities
 	 */
 	define('EMAIL_ANTISPAM_ASCII', 2);
 	
 	// Constants for image aligns
	define('IMG_ALIGN_NORMAL', 'normal');
	define('IMG_ALIGN_LEFT', 'left');
	define('IMG_ALIGN_CENTER', 'center');
	define('IMG_ALIGN_RIGHT', 'right');
	// Constants for image layouts
	define('IMG_DISPLAY_PICTURE', 'picture');
	define('IMG_DISPLAY_BOX', 'box');
	define('IMG_DISPLAY_BOX_ONLY', 'box_only');
 	
 	
 	/** TextActions
 	 * This class contains the functions which are used to convert plain text into a html-sourcecode
 	 * 
 	 * @package ComaCMS
 	 */
	class TextActions {
		
		/** ConvertToPreHTML
		 * 
		 * @access public
		 * @param string Text
		 * @return string to HTML convertet Code
		 */
		function ConvertToPreHTML($Text) {

			$Text = stripslashes($Text) . " ";
			// make all EOL-sings equal
			$Text = preg_replace("!(\r\n)|(\r)!","\n",$Text);
			
			// convert htmlentities to
			$Text = str_replace('&auml;', 'ä', $Text);
			$Text = str_replace('&Auml;', 'Ä', $Text);
			$Text = str_replace('&uuml;', 'ü', $Text);
			$Text = str_replace('&Uuml;', 'Ü', $Text);
			$Text = str_replace('&ouml;', 'ö', $Text);
			$Text = str_replace('&Ouml;', 'Ö', $Text);
			$Text = str_replace('&szlig;', 'ß', $Text);
			$Text = str_replace('&gt;', '>', $Text);
			$Text = str_replace('&lt;', '<', $Text);

			// remove comments
			$Text = preg_replace("/<!--(.+?)-->/s", '', $Text);
			
			// extract all text we won't convert <plain>...TEXT...</plain>
			preg_match_all("/<plain>(.+?)<\/plain>/s", $Text, $matches);
			$plains = array();
			foreach($matches[1] as $key => $match)  {
				$plains[$key] = $matches[1][$key];
				$Text = str_replace($matches[0][$key], '[plain]%' . $key . '%[/plain]', $Text);
			}

			// catch all parts, which should used as blocks <block>...TEXT...</block>
			preg_match_all("/<block>(.+?)<\/block>/s", $Text, $matches);
			$blocks = array();
			foreach($matches[1] as $key => $match)  {
				$blocks[$key] = $matches[1][$key];
				$Text = str_replace($matches[0][$key], '[block]%' . $key . '%[/block]', $Text);
			}

			// 'repair' all urls (with no http:// but a www or ftp)
			$Text = preg_replace("/(\ |\\r|\\n|\[)(www|ftp)\.(.+?)\.([a-zA-Z.]{2,6}(|\/.+?))/s", '$1' . "http://$2.$3.$4", $Text);
						
			// remove all html characters
			$Text = htmlspecialchars($Text);
			
			// fixes for some security bugs
			$Text = str_replace("\\r", "\r", $Text);
			$Text = str_replace("\\n", "\n", $Text);
			$Text = preg_replace("!(\r\n)|(\r)!", "\n", $Text);
			$Text = preg_replace("#\\\\(\ |\\r|\\n)#s", "\n<br />\n", $Text);
			
			// catch all email-adresses which should be convertet to links (<email@domain.com>)
			preg_match_all("#\&lt\;([a-z0-9\._-]+?)\@([\w\-]+\.[a-z0-9\-\.]+\.*[\w]+)\&gt\;#s", $Text, $emails);
			// catch all images
			preg_match_all("#\{\{(.+?)\}\}#s", $Text, $images);
			
			$imagesData = array();
			foreach($images[1] as $key => $image) {
				$imagesData[$key] = TextActions::MakeImage($image);			
				$Text = str_replace('{{' . $image . '}}', '[img]%' . $key . '%[/img]', $Text);
			}
			
			
			// allowed auto-link protocols
			$protos = "http|ftp|https";
			// convert urls to links http://www.domain.com to [[http://www.domain.com|www.domain.com]]
			$Text = preg_replace("#(?<!\[\[)($protos):\/\/(.+?)(\ |\\n)#s",'[[$1://$2|$2]]$3', $Text);
			$Text = preg_replace("#\[\[($protos):\/\/([a-z0-9\-\.]+)\]\]#s",'[[$1://$2|$2]]', $Text);
			// FIXME: make configurable
			$antibot = EMAIL_ANTISPAM_TEXT;
			// convert catched emails into the link format [[email@example.com]]
			foreach($emails[0] as $key => $email) {
				$Text = str_replace('&lt;' . $emails[1][$key] . '@' . $emails[2][$key] .'&gt;', '[[' . $emails[1][$key] . '@' . $emails[2][$key] . '|' . TextActions::EmailAntispam($emails[1][$key] . '@' . $emails[2][$key], $antibot) . ']]', $Text);
			}


			// catch all links
			preg_match_all("#\[\[(.+?)\]\]#s", $Text, $links);
			$link_list = array();
			$linkNr = 1;
			// replace all links with a short uniqe id to replace them later back
			foreach($links[1] as $link) {
				$link_list[$linkNr] = $link;
				$Text = str_replace("[[$link]]", "[[%$linkNr%]]", $Text);
				$linkNr++;
			}
			// convert all **text** to <strong>text</strong> => Bold
			$Text = preg_replace("/\*\*(.+?)\*\*/s", "<strong>$1</strong>", $Text);
			// convert all //text// to <em>text</em> => Italic
			$Text = preg_replace("/\/\/(.+?)\/\//s", "<em>$1</em>", $Text);
			// convert all __text__ to <u>text</u> => Underline
			$Text = preg_replace("/__(.+?)__/s", "<u>$1</u>", $Text);
			// convert ====== text ====== to a header <h5>
			$Text = preg_replace("#======\ (.+?)\ ======#s", "\n\n<h6>$1</h6>\n", $Text);
			// convert ===== text ===== to a header <h5>
			$Text = preg_replace("#=====\ (.+?)\ =====#s", "\n\n<h5>$1</h5>\n", $Text);
			// convert ==== text ==== to a header <h4>
			$Text = preg_replace("#====\ (.+?)\ ====#s", "\n\n<h4>$1</h4>\n", $Text);
			// convert === text === to a header <h3>
			$Text = preg_replace("#===\ (.+?)\ ===#s", "\n\n<h3>$1</h3>\n", $Text);
			// convert == text == to a header <h2>
			$Text = preg_replace("#==\ (.+?)\ ==#s", "\n\n<h2>$1</h2>\n", $Text);
			
			// convert <center>text</center> to <div class="center">text</div>
			$Text = preg_replace("#&lt;center&gt;(.+?)&lt;/center&gt;#s", "</p><div class=\"center\">$1</div><p>", $Text);
		
			// convert ({text}{text}) to two colums
			$Text = preg_replace("#\(\{([^\}\{]*?)[\r\n ]*\}\{([^\}\{]*?)[\r\n ]*\}\)#mu", "</p>\n<div class=\"column ctwo\">\n<p>\n$1\n</p>\n</div>\n<div class=\"column ctwo\">\n<p>\n$2\n</p>\n</div>\n<p class=\"after_column\"/>\n<p>\n", $Text);
			// convert ({text}{text}{text}) to tree colums
			$Text = preg_replace("#\(\{([^\}\{]*?)[\r\n ]*\}\{([^\}\{]*?)[\r\n ]*\}\{([^\}\{]*?)[\r\n ]*\}\)#mu", "</p>\n<div class=\"column ctree\">\n<p>\n$1\n</p>\n</div>\n<div class=\"column ctree\">\n<p>\n$2\n</p>\n</div><div class=\"column ctree\">\n<p>\n$3\n</p>\n</div>\n<p class=\"after_column\">\n<p>\n", $Text);
			
			// paste links into the text
			foreach($link_list as $linkNr => $link) {
				if(preg_match("#^(.+?)\|(.+?)$#i", $link, $link2))				
					$Text = str_replace("[[%$linkNr%]]", "<a href=\"" . TextActions::MakeLink($link2[1]) . "\">" . $link2[2] . "</a>", $Text);
				else
					$Text = str_replace("[[%$linkNr%]]", "<a href=\"" . TextActions::MakeLink($link) . "\">" . $link . "</a>", $Text);
			}
		
			$lines = explode("\n", $Text);
			$lines[] = "\n";
			$tempText = '';
			$outputText = '';
			$state = LINE_STATE_NONE;
			$lastState = LINE_STATE_NONE;
			foreach($lines as $line) {
				$lastState = $state;
				// Unsorted lists: *
				if(TextActions::StartsWith('* ', $line))
					$state = LINE_STATE_ULIST;
				// Ordered lists: #
				else if(TextActions::StartsWith('# ', $line))
					$state = LINE_STATE_OLIST;
				// Tables : ^ or |
				else if(TextActions::StartsWith('^', $line) || TextActions::StartsWith('|', $line))
					$state = LINE_STATE_TABLE;
				else if(TextActions::StartsWith('<h', $line))
					$state = LINE_STATE_HEADER;
				else if(TextActions::StartsWith('&gt;', $line) || TextActions::StartsWith('=&gt;', $line))
					$state = LINE_STATE_DEFINITON;
				// EOF
				else if ($line == "\n" || $line == "")
					$state = LINE_STATE_NONE;
				// Everything else is text!
				else
					$state = LINE_STATE_TEXT;
				if($lastState == $state)
					$tempText .= "\t" . $line . "\n"  ;
				else{
					
					// convert the specific parts
					switch ($lastState) {
						case LINE_STATE_TEXT:
							$outputText .= TextActions::ConvertText($tempText);
							break;
						case LINE_STATE_ULIST:
							$outputText .= TextActions::ConvertUList($tempText);
							break;
						case LINE_STATE_OLIST:
							$outputText .= TextActions::ConvertOList($tempText);
							break;
						case LINE_STATE_TABLE:
							$outputText .= TextActions::ConvertTable($tempText);
							break;
						case LINE_STATE_HEADER:
							$outputText .= $tempText;
							break;
						case LINE_STATE_DEFINITON:
							$outputText .= TextActions::ConvertDefinition($tempText);
							break;	
					}
					$tempText = "\t" . $line . "\n";
				}
				
			
				
				
			}
			foreach($blocks as $key => $match)
				$outputText = str_replace('[block]%' . $key . '%[/block]', TextActions::ConvertToPreHTML($match), $outputText);
			// paste plain-parts back
			foreach($plains as $key => $match)
				$outputText = str_replace('[plain]%' . $key . '%[/plain]', $match, $outputText);
			foreach($imagesData as $key => $imgHtml)
				$outputText = str_replace('[img]%' . $key . '%[/img]', $imgHtml, $outputText);
				
			// remove the spaces which are not necessary
			$outputText = preg_replace('/\ \ +/', ' ', $outputText);
			
			$outputText = str_replace(' -- ', ' &ndash; ', $outputText);
			$outputText = str_replace(' --- ', ' &mdash; ', $outputText);
		
			$outputText = str_replace('(c)', '&copy;', $outputText);
			$outputText = str_replace('(r)', '&reg;', $outputText);
			$outputText = str_replace('ä', '&auml;', $outputText);
			$outputText = str_replace('Ä', '&Auml;', $outputText);
			$outputText = str_replace('ü', '&uuml;', $outputText);
			$outputText = str_replace('Ü', '&Uuml;', $outputText);
			$outputText = str_replace('ö', '&ouml;', $outputText);
			$outputText = str_replace('Ö', '&Ouml;', $outputText);
			$outputText = str_replace('ß', '&szlig;', $outputText);
			
			
			return $outputText;
		}
		
		/** special_start_with
	 	* 
	 	* Diese Funktion schaut, ob ein String mit einer bestimmten Zeichenkette anfaengt und ignoriert dabei einige Zeichen,
	 	* so können grundsaetzlich noch Lehrzeichen und Tabs vor der Zeichenkette sein, nach der gesucht wird und dennoch
	 	* wird zurueckgegeben, dass der String mit der gesuchten Zeichenkette beginnt. 
	 	* 
	 	* @return bool
	 	* @param string search
	 	* @param string input
	 	* @param array allowedchars
	 	* 
	 	*/	
		function StartsWith($search, $input, $allowedchars = array(' ', "\t")) {
			
			$search_len = strlen($search);
			$position = strpos($input, $search);
			if($position === false)
				return false;
			$str_temp = substr($input, 0, $position);
			$str_temp = str_replace($allowedchars,'', $str_temp);
			if($str_temp == '')
				return true;
			return false;
		}
		
		
		function ConvertText($textpart) {
			$textpart = '<p>' . $textpart . '</p>';
			$textpart = preg_replace("#<p>[\r\n\t\ ]{0,}</p>#i", '', $textpart);
			return $textpart;
		}
		
		function ConvertTable($Textpart) {
			$outputText = "<table>\n";
			$lines = explode("\n", $Textpart);
			// go through each line
			foreach($lines as $line) {
				if($line != '') {
					$outputText .= "\t<tr>\n";
					//$outputText .= "\t\t<td>$line</td>\n";
					$row = str_replace('^', '|', $line);
					$rowElements = explode('|', $row);
					$rowCount = count($rowElements) - 1;
					$charPosition = strpos($row, '|');
					for($position = 1; $position < $rowCount; $position++) {
						$htmlElement = 'td';
						if(substr($line, $charPosition,1) == '^')
							$htmlElement = 'th';
						$outputText .= "\t\t<$htmlElement>\n\t\t\t";
						$outputText .= $rowElements[$position];
						$outputText .= "\n\t\t</$htmlElement>\n";
						$charPosition += 1 + strlen($rowElements[$position]);
					}
					$outputText .= "\t</tr>\n";
				}
			}
			$outputText .= "</table>\n";
			return $outputText;
		}
		
		/** ConvertDefinition
		 * 	
		 * Generates a definiton-list
		 * <code>
		 * >Word
		 * =>Definiton
		 * <dl>
		 * 	<dt>Word</dt>
		 * 		</dd>Definiton</dd>
		 * </dl>
		 * </code>
		 * @return string
		 * @param string Textpart
		 */
		function ConvertDefinition($Textpart) {
			$lines = explode("\n", $Textpart);
			$outputText = "\n\t<dl>\n";
			foreach($lines as $line) {
				if($line != '') {
				$textLine = substr($line, strpos($line, '&gt;') + strlen('&gt;'));
				if(TextActions::StartsWith('&gt;', $line))
					$outputText .= "\t\t<dt>" . $textLine . "</dt>\n";
				else
					$outputText .= "\t\t\t<dd>" . $textLine . "</dd>\n";
				}
			}
			$outputText .= "\t</dl>\n";
			return $outputText;
		}
		
		/** ConvertList
		 * 
		 * This function generates a html-list from a plain text which starts list-points with:
		 *  #(ordered) or *(unsorted).
		 * There are no restrictions with the deepnes of a "list-tree".
		 *   
		 * @return string
		 * @param bool ordered
		 * @param string textpart
		 */
		function ConvertList($Ordered, $textpart) {
			// initialize the settings for an ordered or an unsorted list
			$codesequence = '# ';
			$codesequence2 = '* ';
			$htmlcode = 'ol';
			if(!$Ordered) {
				$codesequence = '* ';
				$codesequence2 = '# ';
				$htmlcode = 'ul';
			}
			$output_text = "\n<{$htmlcode}>";
			// sepeerate the text into single lines
			$lines = explode("\n", $textpart);
			$nodes = '';
			$first = true;
			$sublistordered = $Ordered;
			// go through each line
			foreach($lines as $line) {
				// get the 'real' content of the line
				$line = substr($line, strpos($line, $codesequence) + strlen($codesequence));
				// check if it's real text or a line which initializes a sublist
				if(TextActions::StartsWith($codesequence, $line)) {
					if($Ordered != $sublistordered && $nodes != '') {
						// add subnodes
						if($nodes != '') {
							if($first) {
								$fist = false;
								$output_text .= "\n\t<li>";
							}
						}
						$output_text .= TextActions::ConvertList($sublistordered, $nodes)."\n";
						$nodes = '';
					}
					$nodes .= $line."\n";
					$sublistordered = $Ordered;
				}
				else if(TextActions::StartsWith($codesequence2, $line)) {
					if($Ordered == $sublistordered && $nodes != '') {
						// add subnodes
						if($nodes != '') {
							if($first) {
								$fist = false;
								$output_text .= "\n\t<li>";
							}
						}
						$output_text .= TextActions::ConvertList($sublistordered, $nodes)."\n";
						$nodes = '';
					}
					$nodes .= $line."\n";
					$sublistordered = !$Ordered;
				}
				else { 
					// if it is a 'text'-line make sure if there is a sublist to add before
					if($nodes != '') {
						if($first) {
							$fist = false;
							$output_text .= "\n\t<li>";
						}
						// add the text of a sublist
						$output_text .= TextActions::ConvertList($sublistordered, $nodes)."\n";
						$nodes = '';
					}
					// if the line isn't empty add it to the oter code
					if($line != "") {
						if(!$first)
							$output_text .= "</li>\n\t<li>$line";
						else {
							$first = false;
							$output_text .= "\n\t<li>$line";
						}
					}
				}
			}
			$output_text .= "</li>\n</$htmlcode>";
			return $output_text;
		}
		
		function ConvertUList($textpart) {
			return TextActions::ConvertList(false, $textpart);
		}
		
		function ConvertOList($textpart) {
			return TextActions::ConvertList(true, $textpart);
		}
		
		function EmailAntispam($Email, $AntispamType) {
			switch($AntispamType) {
				case EMAIL_ANTISPAM_TEXT:
						// only a replacement of "dots" and the "at"
						$Email = str_replace('.', ' [dot] ', $Email);
						$Email = str_replace('@', ' [at] ', $Email);
					break;
				case EMAIL_ANTISPAM_ASCII:
						// replace all characters with the HTML-ASCII codes
						$tmpMail = '';
						$length = strlen($Email);
						for($chr = 0; $chr < $length; $chr++)
							$tmpMail .= '&#' . ord($Email[$chr]) . ';';
						$Email = $tmpMail;
					break;
			}
			return $Email;
		}
		
		/** MakeLink
		 * @param string Link
		 * @return string
	 	 */
		function MakeLink($Link) {
			$antibot = EMAIL_ANTISPAM_TEXT;
			$encodedLink = encodeUri($Link);
			// identify mail-adresses
			if(preg_match("/^[a-z0-9-_\.]+@[a-z0-9\-_\.]+\.[a-z]{2,4}$/i", $Link)) {
				$Link = TextActions::EmailAntispam($Link, $antibot);
				return "mailto:$Link\" class=\"link_email";
			}
			else if(substr($encodedLink, 0, 6) == 'http:/' || substr($encodedLink, 0, 5) == 'ftp:/' || substr($encodedLink, 0, 7) == 'https:/' )
				return "$encodedLink\" class=\"link_extern";
			else if(substr($encodedLink, 0, 11) == 'download%3A')
				return TextActions::GetDownloadUrl(substr($encodedLink, 11));
			return TextActions::GetInternUrl($encodedLink);
		}
		
		function GetDownloadUrl($File)
		{
			$sql = "SELECT *
					FROM " . DB_PREFIX . "files
					WHERE file_name=\"{$File}\"
					LIMIT 1";
					$fileResult = $this->_SqlConnection->SqlQuery($sql);
					if($file = mysql_fetch_object($fileResult)) {
						return "download.php?file_id=" . $file->file_id . "\" class=\"link_intern link_download";
					}
			return "#\" class=\"link_intern link_intern_missing";
		}
		
		function GetInternUrl($Pagename) {
			// TODO: get the connection throug the parameters, not as a global
			global $sqlConnection;
			$link = rawurlencode($Pagename);
			$sql = "SELECT page_title
					FROM " . DB_PREFIX . "pages
					WHERE page_name='{$link}' OR page_id='{$link}'
					LIMIT 1";
			$result = $sqlConnection->SqlQuery($sql);
			if($pageTitleData = mysql_fetch_object($result)) {
				return "index.php?page={$Pagename}\" title=\"{$pageTitleData->page_title}\" class=\"link_intern";
			}
			else
				return "index.php?page={$Pagename}\" title=\"{$link}\" class=\"link_intern link_intern_missing";
		}
		
		function MakeImage($Image) {
			#[size] {[int_x]X[int_y],[int_maxsize], w[int_maxwidth], thumb=>w180, original=>[orig_x]X[orig_y], big=>800}
			#[format] {box, box_only, picture}
			#[Url]|[Title] => [Url]|box|thumb|[Title]
			#[Url]|[size]|[Title] = [Url]|box|[size]|[Title]
			#[Url]|[display]|[size]|[Title]
			
			// TODO: get the connection through the parameters, not as a global
			global $sqlConnection;
			
			// Defaults
			$ImageAlign = IMG_ALIGN_NORMAL;
			$imageDisplay = IMG_DISPLAY_BOX;
			$imageSize = 'w180';
			$imageWidth = 180;
			$imageHeight = 180;
			$imageTitle = '';
			$imageUrl = '';
			
			$leftSpace = false;
			$rightSpace = false;
			if(substr($Image, 0, 1) == ' ')
				$leftSpace = true;
			if(substr($Image, -1, 1) == ' ')
				$rightSpace = true;
			if($leftSpace && $rightSpace)
				$ImageAlign = IMG_ALIGN_CENTER;
			else if($leftSpace)
				$ImageAlign = IMG_ALIGN_LEFT;
			else if($rightSpace)
				$ImageAlign = IMG_ALIGN_RIGHT;
			
			// remove spaces at the begin and end of the string
			$Image = preg_replace("~^\ *(.+?)\ *$~", '$1', $Image);
			
			// Split into all Parameters
			$parameters = explode('|', $Image);
				
			// set the path to the local media dir
			$imageUrl = preg_replace("~^\media:\ *(.+?)$~", 'data/upload/' . '$1', $parameters[0]);
			$imageTitle = $parameters[0];		
			//remove first entry (we don't have to check it)
			unset($parameters[0]);
			
			// go through each parameter
			foreach($parameters as $key => $value) {
				// extract the image layout
				if(preg_match('~^(' . IMG_DISPLAY_BOX_ONLY .'|' . IMG_DISPLAY_BOX . '|' . IMG_DISPLAY_PICTURE . ')$~', $value))
					$imageDisplay = $value;
				// extract the size for the image
				else if(preg_match('~^(thumb|original|big|[0-9]+[Xx][0-9]+|[0-9]+|\w[0-9]+)$~', $value))
					$imageSize = $value;
				else // its the Title of the picture (it is the last unused parameter)
					$imageTitle = $value;
			}
			// TODO:
			// check if the image isn't saved "local", if it is, download it!
			// extern_{$filename}_timestamp.png
			
			
			// if the file doesn't exists under the given path, try to find it in the database
			if(!file_exists($imageUrl))
			{
				$sql = "SELECT file_path
						FROM " . DB_PREFIX . "files
						WHERE LOWER(file_path) = '" . strtolower($imageUrl) . "'
							OR LOWER(file_name) = '" . strtolower(basename($imageUrl)) . "'
						LIMIT 1" ;
				$result = $sqlConnection->SqlQuery($sql);
				if($fileData = mysql_fetch_object($result))
					$imageUrl = $fileData->file_path;
				clearstatcache();
				// check if the file from the database really exists
				if(!file_exists($imageUrl))			
					return "<strong>Bild (&quot;<em>$imageUrl</em>&quot;) nicht gefunden.</strong>";
			}
				
			
			// Resize the image
			$image = new ImageConverter($imageUrl);
			// convert the 'name-sizes' to 'pixel-sizes' 
			if($imageSize == 'thumb') 
				$imageSize = 'w180'; //width: 180px
			else if ($imageSize == 'big')
				$imageSize = '800'; // maximal width/length: 800px
			else if ($imageSize == 'original') {
				// took the original sizes
				$imageWidth = $image->Size[0] ;
				$imageHeight = $image->Size[1];
			}
			
			// 'width-format''
			if(preg_match('~^w[0-9]+$~', $imageSize)) {
				$imageWidth = substr($imageSize, 1);
				// calculate the proporitonal height 
				$imageHeight = round($image->Size[1] / $image->Size[0] *  $imageWidth, 0);
			}
			// 'maximal-format'
			else if(preg_match('~^[0-9]+$~', $imageSize)) {
				// look for the longer side and resize it to te given size,
				// short the other side proportional to the longer side
				$imageWidth = ($image->Size[0] > $image->Size[1]) ? round($imageSize, 0) : round($image->Size[0] / ($image->Size[1] / $imageSize), 0);
				$imageHeight = ($image->Size[1] > $image->Size[0]) ? round($imageSize, 0) : round($image->Size[1] / ($image->Size[0] / $imageSize), 0);
			}
			// 'exacact-size'
			else if(preg_match('~^([0-9]+)[Xx]([0-9]+)$~', $imageSize, $maches)) {
				// took the given sizes
				$imageWidth = ($maches[1] < $image->Size[0]) ? $maches[1] : $image->Size[0];
				$imageHeight = ($maches[2] < $image->Size[1]) ? $maches[2] : $image->Size[1];
			}
			$originalUrl = encodeUri($imageUrl); // str_replace(' ', '%20', basename($imageUrl));
			// TODO: don't use the global
			global $config;
			// check if the image exists already
			$thumbnailfolder = $config->Get('thumbnailfolder', 'data/thumbnails/'); 
			if (file_exists($thumbnailfolder . '/' .  $imageWidth . 'x' . $imageHeight . '_' . basename($imageUrl)))
				$imageUrl = $thumbnailfolder . '/' .  $imageWidth . 'x' . $imageHeight . '_' . basename($imageUrl);
			else if(($image->Size[0] >= $imageWidth && $image->Size[1] > $imageHeight) || ($image->Size[0] > $imageWidth && $image->Size[1] >= $imageHeight)) {
				$imageUrl = $image->SaveResizedTo($imageWidth, $imageHeight, $thumbnailfolder, $imageWidth . 'x' . $imageHeight . '_');
				if($imageUrl === false)
				 return "Not enough memory available!(resize your image!)";
				
			}
			else {
				$imageWidth = $image->Size[0];
				$imageHeight = $image->Size[1];
			}
			$imageName = generateUrl(basename($image->_file));
			$originalUrl = generateUrl($originalUrl);
			$imageUrl = generateUrl($imageUrl);
			
			if($imageDisplay == IMG_DISPLAY_BOX) {
				$imageString = "</p>\n\n<div class=\"thumb t" . $ImageAlign . "\">
						<div style=\"width:" . ($imageWidth + 4) . "px\">
							<img width=\"{$imageWidth}\" height=\"{$imageHeight}\" src=\"{$imageUrl}\" title=\"$imageTitle\" alt=\"$imageTitle\" />
							<div class=\"description\" title=\"$imageTitle\"><div class=\"magnify\"><a href=\"special.php?page=image&amp;file=$imageName\" title=\"vergr&ouml;&szlig;ern\"><img src=\"img/magnify.png\" title=\"vergr&ouml;&szlig;ern\" alt=\"vergr&ouml;&szlig;ern\"/></a></div>$imageTitle</div>
						</div>
					</div><p>\n";
			}
			// HTMLcode for the box style without a title
			else if($imageDisplay == IMG_DISPLAY_BOX_ONLY) {
				$imageString = "</p>\n\n<div class=\"thumb tbox t" . $ImageAlign . "\">
						<div style=\"width:" . ($imageWidth + 4) . "px\">
							<img width=\"$imageWidth\" height=\"$imageHeight\" src=\"$imageUrl\" title=\"$imageTitle\" alt=\"$imageTitle\" />
							<div class=\"magnify\"><a href=\"special.php?page=image&amp;file=$imageName\" title=\"vergr&ouml;&szlig;ern\"><img src=\"img/magnify.png\" title=\"vergr&ouml;&szlig;ern\" alt=\"vergr&ouml;&szlig;ern\"/></a></div>
						</div>
					</div>\n<p>";
			}
			// HTMLcode for the picture only
			else if($imageDisplay == IMG_DISPLAY_PICTURE) {
				$imageString = "</p>\n\n<div class=\"thumb tbox t" . $ImageAlign . "\">
					<img width=\"$imageWidth\" height=\"$imageHeight\" src=\"$imageUrl\" title=\"$imageTitle\" alt=\"$imageTitle\" />
					</div><p>";
			}
		
			return "$imageString";	

		}
			
	}	
?>