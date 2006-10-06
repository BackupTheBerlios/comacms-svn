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
 	define('LINE_STATE_NONE', 0);
 	define('LINE_STATE_TEXT', 1);
 	define('LINE_STATE_ULIST', 2);
 	define('LINE_STATE_OLIST', 3);
 	define('LINE_STATE_TABLE', 4);
 	/**
 	 * @package ComaCMS
 	 */
	class TextActions {
		
		/** ConvertToPreHTML
		 * 
		 * @access public
		 * @return string to HTML convertet Code
		 */
		function ConvertToPreHTML($text) {

			$text = stripslashes($text);
			// make all EOL-sings equal
			$text = preg_replace("!(\r\n)|(\r)!","\n",$text);
			$text = "\n" . $text . "\n";
			$text = str_replace('&auml;', 'ä', $text);
			$text = str_replace('&Auml;', 'Ä', $text);
			$text = str_replace('&uuml;', 'ü', $text);
			$text = str_replace('&Uuml;', 'Ü', $text);
			$text = str_replace('&ouml;', 'ö', $text);
			$text = str_replace('&Ouml;', 'O', $text);
			$text = str_replace('&szlig;', 'ß', $text);
			$text = str_replace('&gt;', '>', $text);
			$text = str_replace('&lt;', '<', $text);
			
			$lines = explode("\n", $text);
			$lines[] = "\n";
			$temp_text = '';
			$output_text = '';
			$state = LINE_STATE_NONE;
			$last_state = LINE_STATE_NONE;
			foreach($lines as $line) {
				$last_state = $state;
				if(TextActions::StartsWith('* ', $line))
					$state = LINE_STATE_ULIST;
				else if(TextActions::StartsWith('# ', $line))
					$state = LINE_STATE_OLIST;
				else if ($line == "\n")
					$state = LINE_STATE_NONE;
				else
					$state = LINE_STATE_TEXT;
				if($last_state == $state)
					$temp_text .= $line."\n"  ;
				else{
					if ($last_state == LINE_STATE_TEXT)
						$output_text .= $temp_text;
					else if ($last_state == LINE_STATE_ULIST)
						$output_text .= TextActions::ConvertUList($temp_text);
					else if ($last_state == LINE_STATE_OLIST)
						$output_text .= TextActions::ConvertOList($temp_text);
					$temp_text = $line."\n";
				}
					
			}
			
			
			return $output_text;
		}
		
		/** special_start_with
	 	* 
	 	* Diese Funktion schaut, ob ein String mit einer bestimmten Zeichenkette anfaengt und ignoriert dabei einige Zeichen,
	 	* so können grundsaetzlich noch Lehrzeichen und Tabs vor der Zeichenkette sein, nach der gesucht wird und dennoch
	 	* wird zurueckgegeben, dass der String mit der gesuchten Zeichenkette beginnt. 
	 	* 
	 	* @return bool
	 	* @param string search
	 	* @param string str
	 	* @param array $allowedchars
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
		
		function ConvertUList($textpart) {
			$output_text = "\n<ul>";
			$lines = explode("\n", $textpart);
			$nodes = '';
			$first = true;
			foreach($lines as $line) {
				
				$line = substr($line,strpos($line, '* ')+2);
				
				if(TextActions::StartsWith('* ', $line))
					$nodes .= $line."\n";
				else {
					if($nodes != '') {
						$output_text .= TextActions::ConvertUList($nodes)."\n";
						$nodes = '';
					}
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
			$output_text .= "</li>\n</ul>";
			return $output_text;
		}
		
		function ConvertOList($textpart) {
			$output_text = "\n<ol>\n";
			$lines = explode("\n", $textpart);
			$nodes = '';
			$first = true;
			foreach($lines as $line) {
				
				$line = substr($line,strpos($line, '# ')+2);
				if(TextActions::StartsWith('# ', $line))
					$nodes .= $line."\n";
				else {
					if($nodes != '') {
						$output_text .= "\n" . TextActions::ConvertOList($nodes) . "\n";
						$nodes = '';
					}
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
			$output_text .= "</li>\n</ol>\n";
			return $output_text;
		}
		
	
	}	
 
?>
