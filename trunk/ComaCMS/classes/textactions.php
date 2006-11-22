<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005-2006 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : textactions.php
 # created              : 2006-10-06
 # copyright            : (C) 2005-2006 The ComaCMS-Team
 # email                : comacms@williblau.de
 #----------------------------------------------------------------------
 # This program is free software; you can redistribute it and/or modify
 # it under the terms of the GNU General Public License as published by
 # the Free Software Foundation; either version 2 of the License, or
 # (at your option) any later version.
 #----------------------------------------------------------------------
 	
 	require __ROOT__.'/functions.php';
 	define('LINE_STATE_NONE', 0);
 	define('LINE_STATE_TEXT', 1);
 	define('LINE_STATE_ULIST', 2);
 	define('LINE_STATE_OLIST', 3);
 	define('LINE_STATE_TABLE', 4);
 	define('EMAIL_DEFAULT', 0);
 	define('EMAIL_ANTISPAM_TEXT', 1);
 	define('EMAIL_ANTISPAM_ASCII', 2);
 	
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
			$Text = str_replace('&Ouml;', 'O', $Text);
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
			
			// catch all email-adresses which should be convertet to links ( <email@domain.com>)
			preg_match_all("#\&lt\;([a-z0-9\._-]+?)\@([\w\-]+\.[a-z0-9\-\.]+\.*[\w]+)\&gt\;#s", $Text, $emails);
			// allowed auto-link protocols
			$protos = "http|ftp|https";
			// convert urls to links http://www.domain.com to [[http://www.domain.com|www.domain.com]]
			$Text = preg_replace("#(?<!\[\[)($protos):\/\/(.+?)(\ |\\n)#s",'[[$1://$2|$2]]$3', $Text);
			$Text = preg_replace("#\[\[($protos):\/\/([a-z0-9\-\.]+)\]\]#s",'[[$1://$2|$2]]', $Text);
			// convert catched emails into the link format [[email@example.com]]
			$antibot = EMAIL_ANTISPAM_TEXT;
			foreach($emails[0] as $key => $email) {
				if($antibot == EMAIL_ANTISPAM_TEXT){
					$tmpMail = str_replace('.', ' [dot] ', $emails[1][$key] . ' [at] ' . $emails[2][$key]);
					$Text = str_replace('&lt;' . $emails[1][$key] . '@' . $emails[2][$key] . '&gt;', '[[' . $tmpMail . '|' . $tmpMail . ']]', $Text);					
				}
				else if($antibot == EMAIL_ANTISPAM_ASCII){
					$tmpMail = ''; //str_replace('.', ' [dot] ', $emails[1][$key] . ' [at] ' . $emails[2][$key]);
					$email = $emails[1][$key] . '@' . $emails[2][$key];
					$length = strlen($email);
					for($chr = 0; $chr < $length;$chr++) {
						$tmpMail .= '&#'.ord($email[$chr]).';';
					}
					$Text = str_replace('&lt;' . $emails[1][$key] . '@' . $emails[2][$key] . '&gt;', '[[' . $tmpMail . '|' . $tmpMail . ']]', $Text);
				}
				else
					$Text = str_replace('&lt;' . $emails[1][$key] . '@' . $emails[2][$key] .'&gt;', '[[' . $emails[1][$key] . '@' . $emails[2][$key] . '|' . $emails[1][$key] . '@' . $emails[2][$key] . ']]', $Text);
			}
				
			// catch all links
			preg_match_all("#\[\[(.+?)\]\]#s", $Text, $links);
			$link_list = array();
			$link_nr = 1;
			// replace all links with a short uniqe id to replace them later back
			foreach($links[1] as $link) {
				$link_list[$link_nr] = $link;
				$Text = str_replace("[[$link]]", "[[%$link_nr%]]", $Text);
				$link_nr++;
			}
			// convert all **text** to <strong>text</strong> => Bold
			$Text = preg_replace("/\*\*(.+?)\*\*/s", "<strong>$1</strong>", $Text);
			// convert all //text// to <em>text</em> => Italic
			$Text = preg_replace("/\/\/(.+?)\/\//s", "<em>$1</em>", $Text);
			// convert all __text__ to <u>text</u> => Underline
			$Text = preg_replace("/__(.+?)__/s", "<u>$1</u>", $Text);
			// convert ==== text ==== to a header <h2>
			$Text = preg_replace("#==\ (.+?)\ ==#s", "\n\n<h2>$1</h2>\n", $Text);
			// convert === text === to a header <h3>
			$Text = preg_replace("#===\ (.+?)\ ===#s", "\n\n<h3>$1</h3>\n", $Text);
			// convert == text == to a header <h4>
			$Text = preg_replace("#====\ (.+?)\ ====#s", "\n\n<h4>$1</h4>\n", $Text);
			// convert <center>text</center> to <div class="center">text</div>
			$Text = preg_replace("#&lt;center&gt;(.+?)&lt;/center&gt;#s", "\n\n<div class=\"center\">$1</div>\n", $Text);
			
			
			// paste links into the text
			foreach($link_list as $link_nr => $link) {
				if(preg_match("#^(.+?)\|(.+?)$#i", $link, $link2))				
					$Text = str_replace("[[%$link_nr%]]", "<a href=\"" . TextActions::MakeLink($link2[1]) . "\">" . $link2[2] . "</a>", $Text);
				else
					$Text = str_replace("[[%$link_nr%]]", "<a href=\"" . TextActions::MakeLink($link) . "\">" . $link . "</a>", $Text);
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
				// EOF
				else if ($line == "\n")
					$state = LINE_STATE_NONE;
				// Everything else is text!
				else
					$state = LINE_STATE_TEXT;
				if($lastState == $state)
					$tempText .= "\t".$line."\n"  ;
				else{
					
					// convert the specific parts
					if ($lastState == LINE_STATE_TEXT)
						$outputText .= TextActions::ConvertText($tempText);
					else if ($lastState == LINE_STATE_ULIST)
						$outputText .= TextActions::ConvertUList($tempText);
					else if ($lastState == LINE_STATE_OLIST)
						$outputText .= TextActions::ConvertOList($tempText);
					else if ($lastState == LINE_STATE_TABLE)
						$outputText .= TextActions::Converttable($tempText);	
					$tempText = "\t".$line."\n";
				}
			}
			foreach($blocks as $key => $match)
				$outputText = str_replace('[block]%' . $key . '%[/block]', TextActions::ConvertToPreHTML($match), $outputText);
			// paste plain-parts back
			foreach($plains as $key => $match)
				$outputText = str_replace('[plain]%' . $key . '%[/plain]', $match, $outputText);
			
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
			$textpart = str_replace("\n\n","\n</p>\n<p>\n", $textpart);
			//$textpart = "\n<p>\n" . $textpart . "\n</p>\n";
			$textpart = preg_replace("#<p>[\r\n\t\ ]{0,}</p>#i",'',$textpart);
			return $textpart;
		}
		
		function ConvertTable($textpart) {
			$outputText = "<table>\n";
			$lines = explode("\n", $textpart);
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
		function ConvertList($ordered, $textpart) {
			// initialize the settings for an ordered or an unsorted list
			$codesequence = '# ';
			$htmlcode = 'ol';
			if(!$ordered) {
				$codesequence = '* ';
				$htmlcode = 'ul';
			}
			$output_text = "\n<{$htmlcode}>";
			// sepeerate the text into single lines
			$lines = explode("\n", $textpart);
			$nodes = '';
			$first = true;
			// go through each line
			foreach($lines as $line) {
				// get the 'real' content of the line
				$line = substr($line,strpos($line, $codesequence) + strlen($codesequence));
				// check if it's real text or a line which initializes a sublist
				if(TextActions::StartsWith($codesequence, $line))
					$nodes .= $line."\n";
				else { 
					// if it is a 'text'-line make sure if there is a sublist to add before
					if($nodes != '') {
						if($first) {
							$fist = false;
							$output_text .= "\n\t<li>";
						}
						// add the text of a sublist
						$output_text .= TextActions::ConvertList($ordered, $nodes)."\n";
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
		
		/** MakeLink
		 * @param string Link
		 * @return string
	 	 */
		function MakeLink($Link) {
			$antibot = EMAIL_ANTISPAM_TEXT;
			$encodedLink = encodeUri($Link);
			// identify mail-adresses
			if(preg_match("/^[a-z0-9\[\]-_\.]+(\ \[at\]\ |@)[a-z0-9\[\]-_\.]+(\ \[dot\]\ |\.)[a-z]{2,4}$/i", $Link) || (EMAIL_ANTISPAM_ASCII == $antibot && preg_match("/^(&#[0-9]+;)+$/i", $Link)))
				return "mailto:$Link\" class=\"link_email";
			else if(substr($encodedLink, 0, 6) == 'http:/' || substr($encodedLink, 0, 5) == 'ftp:/' || substr($encodedLink, 0, 7) == 'https:/' )
				return "$encodedLink\" class=\"link_extern";
			// TODO: load the title of the page into the link title and set an other css-class if the page does not exists
			return "index.php?page=$encodedLink\" class=\"link_intern";
		}
	}	
?>