<?php
/**
 * @package ComaLate
@copyright (C) 2005-2007 The ComaCMS-Team
 * @version ComaLate 0.3
 */
 #----------------------------------------------------------------------
 # file                 : comalate.php
 # created              : 2006-02-12
 # copyright            : (C) 2005-2007 The ComaCMS-Team
 # email                : comacms@williblau.de
 #----------------------------------------------------------------------
 # This program is free software; you can redistribute it and/or modify
 # it under the terms of the GNU General Public License as published by
 # the Free Software Foundation; either version 2 of the License, or
 # (at your option) any later version.
 #----------------------------------------------------------------------
 
   	/** Document type for XHTML strict */
  	define('DOCTYPE_XHTML_STRICT', 'xhtml strict');
  	/** Document type for XHTML transitional */
  	define('DOCTYPE_XHTML_TRANSITIONAL', 'xhtml transitional');
  	/**
  	 * Default docty type
  	 * The same like DOCTYPE_XHTML_STRICT
  	 */
  	define('DOCTYPE_DEFAULT', DOCTYPE_XHTML_STRICT);
  	
	/**
	 * ComaLate Template Engine
	 * @package ComaLate
	 */
 	class ComaLate {
 		
 		/**
 		 * @var string The output language of the generated page
 		 * @access public
 		 */
 		var $Language = 'en';
 		
 		/**
 		 * @var string The title of the page
 		 * @access public
 		 */
 		var $Title = '';
 		
 		/**
 		 * @var string The charset of not-generated part of ComaLate
 		 * @access public
 		 */
 		var $Charset = 'UTF-8';
 		
 		/**
 		 * @var string The template which is loaded from the templatefile
 		 * @access public
 		 */
 		var $Template = '';
 		
 		/**
 		 * @var string The finished HTML-code which should be 'browseable'
 		 * @access public
 		 */
 		var $GeneratedOutput = '';
 		
 		/**
 		 * @access private
 		 */
 		var $_Meta = array();
 		
 		/**
 		 * @access private
 		 */ 
 		var $_Replacements = array();
 		
 		/**
 		 * @access private
 		 */
 		var $_ReplacementsArrays = array();
 		
 		/**
 		 * @access private
 		 */
 		var $_Conditions = array();
 		
 		/**
 		 * @access private
 		 */
 		var $_Doctype = '';	
 		
 		/**
 		 * @access private
 		 */
 		var $_CssFiles = '';
		
		/**
		 * Contains the path to the shoutcut icon file
		 * @access private
		 * @var string Path to the icon file
		 */
		var $_Icon = '';
		
		/**
 		 * @access private
 		 */
		var $_Config;
		
		/**
		 * Initializes the class
		 * and sets the default XHTML-doctype
		 */
 		function ComaLate() {
 			$this->SetDoctype(DOCTYPE_DEFAULT);
 		}
 		
 		/**
 		 * With this function it is possible to change the Doctype
 		 * @param constant $Doctype The doctype to use for the output in short form
 		 * @return void Sets the doctype of the generated output
 		 */
 		function SetDoctype($Doctype) {
 			switch ($Doctype) {
				case DOCTYPE_XHTML_TRANSITIONAL:
					$this->_Doctype = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
					break;
				case DOCTYPE_XHTML_STRICT:
				default:
					$this->_Doctype = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';	 
					break;
			}
 		}
 		
 		/**
 		 * Adds or changes (if the meta-tag already exists) a meta-tag by name
 		 * @param string $Name The name of the meta-tag
 		 * @param string $Value The value of the meta-tag
  		 * @return void Sets a MetaTag for the output
 		 */		
 		function SetMeta($Name, $Value) {
 			$this->_Meta[$Name] = $Value;
		}
 		
 		/**
 		 * Adds or changes (if the condition already exists) a condition by name
 		 * A condition in the template looks like this:
 		 * <NameOfTheCondition:condition>
 		 * Content of the condition
 		 * </NameOfTheCondition>
 		 * @param string $Name The name of the condition
 		 * @param boolean $Value If this value is true, the content of the condition will be displayed
 		 * @return void Sets a Condition for the output
 		 */
 		function SetCondition($Name, $Value = true) {
 			if($Value)
 				$this->_Conditions[$Name] = true;
 			else	
 				$this->_Conditions[$Name] = false;
 		}
 		
 		/**
 		 * Adds or changes a replacement (if the replacement already exists) in one of the local replacement arrays
 		 * 
 		 * A single replacement looks like this:
 		 * {NameOfTheReplacement}
 		 * 
 		 * A loop replacement looks like this:
 		 * 
 		 * <NameOfTheReplacement:loop>
 		 *   {ASingleSubReplacement}{AnotherSingleSubReplacement}{ASingleSubReplacement}
 		 *     <ALoopSubReplacement:loop>
 		 *       {ASingleSubReplacement}{AnotherSingleSubReplacement}{ASingleSubReplacement}
 		 *     </ALoopSubReplacement>
 		 * </NameOfTheReplacement>
 		 * @param string $Name The name of the replacement
 		 * @param string/array $Value The value of the replacement. Can be a [string] for a single Replacement or an [array] for a "loop" replacement
 		 * @return void Sets a replacement
 		 */
 		function SetReplacement($Name, $Value = '') {
 			if(is_array($Name)) {
 				foreach($Name as $replacementName => $replacementValue) {
 					if(is_array($replacementValue)) 
	 					$this->_ReplacementsArrays[$replacementName] = $replacementValue;
 					else
	 					$this->_Replacements[$replacementName] = $replacementValue;
 					}
 			}
 			else {
 				if(is_array($Value)) 
	 				$this->_ReplacementsArrays[$Name] = $Value;
 				else
	 				$this->_Replacements[$Name] = $Value;
	 		}
 		}
 		
 		/**
 		 * Adds a link to the specified CSS-File 
 		 * @access public
 		 * @param string File
 		 * @param string Media
 		 * @return void Adds a css file
 		 */
 		function AddCssFile($File, $Media = 'all') {
 			$this->_CssFiles .= "\t\t<link rel=\"stylesheet\" href=\"{$File}\" type=\"text/css\" media=\"{$Media}\"/>\r\n";
 		}
 		
 		/**
 		 * Sets the link to the shoutcuticon so that each template can define its one one.
 		 * @access public
 		 * @param string $File The path to the icon file
 		 * @return void Sets the Iconpath
 		 */
 		function SetShortcutIcon($File) {
 			$this->_Icon = $File;
 		}
 		
 		/**
 		 * Adds links for css files by there names to the local cssfiles array 
 		 * @access private
 		 * @param array $cssFiles The array contains all names of new css files
 		 * @param string $TemplatesFolder The name of the folder in witch the templates are saved
 		 * @param string $TemplateName The name of the subfolder of the template
 		 * @return void Adds some css files
 		 */
 		function _AddCssFiles($cssFiles, $TemplatesFolder, $TemplateName = 'default') {
 			if(!empty($cssFiles)) {
 					foreach($cssFiles as $key => $cssFile) {
 						foreach($cssFile as $cssKey => $Value) {
 							if($cssKey == 'iefix') {
 								$condition = key($Value);
 								$value = current($Value);
 								$this->_CssFiles .= "\t\t<!--[if $condition]><link rel=\"stylesheet\" href=\"{$TemplatesFolder}{$TemplateName}/$value\" type=\"text/css\"/><![endif]-->\r\n";
 							}
 							else if($cssKey == 'operafix') {
 								$this->_CssFiles .= "\t\t<style type=\"text\/css\">/*/*/@import url({$TemplatesFolder}{$TemplateName}/". substr($Value, 0,-2) . "\ss) screen;/* */</style>\r\n";
 							}
 							else {
 								$this->_CssFiles .= "\t\t<link rel=\"stylesheet\" href=\"{$TemplatesFolder}{$TemplateName}/$Value\" type=\"text/css\" media=\"$cssKey\" />\r\n";
 							}
 						}
 					}
 				}
 		}
 		
 		/**
 		 * @access public
 		 * @param string Replacement This is the replacement we are looking for
 		 * @param bool CheckForLoops Should we also look for Loops?
 		 * @return bool returs true if the requested replacement exists
 		 */
 		function ReplacementExists($Replacement, $CheckForLoops = false) {
 			if($CheckForLoops) {
 				if(preg_match("/<($Replacement)\:loop>(.+?)<\/\\1>/es", $this->Template))
 					return true;
 			}
 			return preg_match('/\{(.+?)\}/e', $this->Template);
 			
 		}
 		
 		/**
 		 * Loads a ComaLate template from a file to local variables and sets css files for the output
 		 * @access public
 		 * @param string $TemplatesFolder The folder in witch all templates are saved in subfolders
 		 * @param string $TemplateName The name of the subfolder of the template. In most cases the template short name, too
 		 */
 		function LoadTemplate($TemplatesFolder, $TemplateName) {
 			$config = array();
 			$TemplatesFolder .= (substr($TemplatesFolder, -1) != '/') ? '/' : ''; 
 			$defaultTemplateConfigFile = $TemplatesFolder . 'default/config.php';
 			$defaultTemplate = '';
 			if(file_exists($defaultTemplateConfigFile)) {
 				include($defaultTemplateConfigFile);
 				
 				if(!empty($config['css-files'])) {
 					$this->_AddCssFiles($config['css-files'], $TemplatesFolder);
 					$config['css-files'] = array();
 				}
 				if(!empty($config['template'])) {
 					$config['template'] = $TemplatesFolder . 'default/' . $config['template'];
 					$defaultTemplate = $config['template'];
 				}
 				
 			}
 				
 			if($TemplateName != 'default') {
 				// load mainstyle
 				$mainTemplateConfigFile = $TemplatesFolder . $TemplateName . '/config.php';
 				if(file_exists($mainTemplateConfigFile)) {
 					include($mainTemplateConfigFile);
 					if(array_key_exists('withoutDefault', $config)) {
 						if($config['withoutDefault'] == true) {
 							echo $this->_CssFiles = '';
 							$config = array();
 							include($mainTemplateConfigFile);
 						}
 					}
 					if(!empty($config['css-files'])) {

 						$this->_AddCssFiles($config['css-files'], $TemplatesFolder, $TemplateName);
 					}
 					if(!empty($config['template'])) {
 						if($config['template'] != $defaultTemplate)
 							$config['template'] = $TemplatesFolder . $TemplateName . '/' . $config['template'];
 					}
 				}
 			}
 			$this->SetReplacement('STYLE_PATH', $TemplatesFolder . $TemplateName);
 			
 			if (array_key_exists('favicon', $config))
 				if (file_exists($TemplatesFolder . $TemplateName . '/' .$config['favicon']))
 					$this->SetShortcutIcon($TemplatesFolder . $TemplateName . '/' . $config['favicon']);
 			
 		/*	foreach($config['conditional-css'] as $conditionName => $conditionValue) {
 				$this->_Conditions[$conditionName] = false;
 				echo "<!--\t" . $conditionName . "\t-->\r\n";
 			}*/
 			
 			if(file_exists($config['template'])) {
 				$template_file = fopen($config['template'], 'r');
				$this->Template = fread($template_file, filesize($config['template']));
				fclose($template_file);
 			}
 			$this->_Config = $config;
 		}
 		
 		/**
 		 * Returns a single replacement during the outputgeneration or the original value if there is no value for that match
 		 * @access private
 		 * @param string $Match The name of the replacement match
 		 */
 		function _Replace($Match) {
 			
 			if(array_key_exists($Match, $this->_Replacements))
 				return $this->_Replacements[$Match];
 			return '{' . $Match . '}';
 		}
 		
 		/**
 		 * Replaces inline "loop" replacements during the output generation recursively
 		 * @access private
 		 * @param serializedArray/array $Replacements The replacements array for each row
 		 * @param string $ReplacementString The string in which the variables should be replaced
 		 * @return string The generated rows
 		 */
 		function _RepeatInlineReplace($Replacements, $ReplacementString) {

 			// Stripe the backslashes from the replacement string
 			$ReplacementString = stripslashes($ReplacementString);
 			
 			// Generate the replacements array if it is given in serialized form
 			if (!is_array($Replacements)) {
 				$Replacements = stripslashes($Replacements);
 				$Replacements = unserialize($Replacements);
 			}
 			
 			// If $Replacements is still no array the function will not work so return ''
 			if (!is_array($Replacements))
 				return '';
 			
 			// Generate an output for the inline replacement
 			$output = '';
 			
 			// Repeat generating rows for each match as long as there are some inputs in the array
 			foreach($Replacements as $repeat) {
 				
 				// Save the template for each row so that you can access it later again
				$toReplaceString = $ReplacementString;
				
				// Get the replacement name $subName and it`s value $subValue
				foreach($repeat as $subName => $subValue) {
					
					// If the subvalue is an array to try to begin an inline replacement
					if (is_array($subValue)) {
						
						$serializedSubValue = serialize($subValue);
						// try to find a matching "loop" replacement and do an inline replacement for that
						$toReplaceString = preg_replace("/<$subName\:loop>(.+?)<\/$subName>/es", "\$this->_RepeatInlineReplace('$serializedSubValue', '$1')", $toReplaceString);
					}
					// Else do a normal replacement for a single value
					else
						$toReplaceString = str_replace('{' . $subName . '}', $subValue, $toReplaceString);
				}
				
				// Add each generated row to the output string
				$output .= $toReplaceString;
			}
			
			// Return the generated output for the $Match
			return $output;
 		}
 		//var $ser
 		
 		/**
 		 * Works through a "loop" replacement by replacing all of it`s subreplacements
 		 * @access private
 		 * @param string $Match The match in the replacementstring $Replacement
 		 * @param string $Replacement The string in witch the function shall replace the "loop" replacement with the name $Match
 		 * @return string The generated rows
 		 */
 		function _RepeatReplace($Match, $Replacement) {
 			
 			// Stripe the backslashes from the replacement string
 			$Replacement = stripslashes($Replacement);
 			
 			// if there is any value for this replacement do the "loop" replacement. If not return ''
 			if(array_key_exists($Match, $this->_ReplacementsArrays)) {
 				
 				// Generate output for the $Match
 				$output = '';
 				
 				// Repeat generating rows for each match as long as there are some inputs in the array
 				foreach($this->_ReplacementsArrays[$Match] as $repeat) {
 					
 					// Save the template for each row so that you can access it later again
					$toReplaceString = $Replacement;
					
					// Get the replacement name $subName and it`s value $subValue
					foreach($repeat as $subName => $subValue) {
						
						// If the subvalue is an array to try to begin an inline replacement
						if (is_array($subValue)) {
							
							$serializedSubValue = serialize($subValue);
							// try to find a matching "loop" replacement and do an inline replacement for that
							$toReplaceString = preg_replace("/<$subName\:loop>(.+?)<\/$subName>/es", "\$this->_RepeatInlineReplace('" . addslashes($serializedSubValue) . "', '$1')", $toReplaceString);
						}
						// Else do a normal replacement for a single value
						else
							$toReplaceString = str_replace('{' . $subName . '}', $subValue, $toReplaceString);
					}
					
					// Add each generated row to the output string
					$output .= $toReplaceString;
				}
				
				// Return the generated output for the $Match
				return $output;
 			}
 			return '';
 		}
 		
 		/**
 		 * @access private
 		 * @param string $Condition The Name of the condition which is "true"
 		 */
 		function _AddConditionalInlineCss($Condition) {
 			// check if the style contains any information for this condition
 			if(!empty($this->_Config['conditional-css'][$Condition])) {
 				foreach($this->_Config['conditional-css'][$Condition] as $outputType => $output) {

 					switch($outputType) {
 						// Possiblity to add fixes for the IE to specifix versions
 						case 'iefix':
							$ieCondition = key($output);
 							$value = current($output);
 							$this->_CssFiles .= "\t\t<!--[if $ieCondition]><style type=\"text/css\">$value</style><![endif]-->\r\n"; 						
 							break;
 						// Same thing as the IE for Opera but without different actions in different versions
 						case 'operafix':
 							$this->_CssFiles .= "\t\t<style type=\"text\/css\">/*/*/@import url(". substr($output, 0,-2) . "\ss) screen;/* */</style>\r\n";
 							break;
 						// All other cases
 						default:
 							$this->_CssFiles .= "\t\t<style type=\"text/css\" media=\"$outputType\">$output</style>\r\n";
 					}					
  				}
 				unset($this->_Config['conditional-css'][$Condition]);
 			}
 		}
 		
		/**
		 * Generates the output html and saves it to a local string variable
		 * @access public
		 */
 		function GenerateOutput() {
 			$document = $this->_Doctype;
 			$document .= "\r\n<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"$this->Language\" lang=\"$this->Language\">\r\n\t<head>\r\n\t\t<title>$this->Title</title>\r\n\t\t<meta http-equiv=\"Content-Type\" content=\"text/html; charset=$this->Charset\" />\r\n";
 			// Print all meta-tags if there are some
 			if (count($this->_Meta) > 0) {
 				foreach($this->_Meta as $metaName => $metaValue)
 					$document .= "\t\t<meta name=\"$metaName\" content=\"$metaValue\" />\r\n";
 			}
 			// find all connditions and handle them			
			if(preg_match_all("/<([A-Za-z0-9_]+)\:condition>(.+?)<\/\\1>/s", $this->Template, $conditionMatches)) {
				foreach($conditionMatches[1] as $condition) {
					if(!array_key_exists($condition, $this->_Conditions))
						$this->Template = preg_replace("/\<$condition:condition\>(.+?)\<\/$condition\>/s", '', $this->Template);
					else if($this->_Conditions[$condition] == false) 
						$this->Template = preg_replace("/\<$condition:condition\>(.+?)\<\/$condition\>/s", '', $this->Template);
					else {
						$this->Template = preg_replace("/\<$condition:condition\>(.+?)\<\/$condition\>/s", '$1', $this->Template);
						$this->_AddConditionalInlineCss($condition);
					}
				}
			}
			
			if(array_key_exists('conditional-css', $this->_Config)) {
				if(count($this->_Config['conditional-css']) > 0) {
					foreach($this->_Config['conditional-css'] as $conditionName => $cssValue) {
						if(array_key_exists($conditionName, $this->_Conditions))
							if($this->_Conditions[$conditionName]) 
								$this->_AddConditionalInlineCss($conditionName);
					}
				}
			}
			// Replace all replaceable fields
			$this->Template = preg_replace( '/\{(.+?)\}/e', "\$this->_Replace('$1')", $this->Template);
			// Replace all replacements generated by the first replacements
			$this->Template = preg_replace( '/\{(.+?)\}/e', "\$this->_Replace('$1')", $this->Template);
			// Replace all loop-replacements
			$this->Template = preg_replace("/<([A-Za-z0-9_]+)\:loop>(.+?)<\/\\1>/es", "\$this->_RepeatReplace('$1', '$2')", $this->Template);
			
			
			// find all connditions (genarated through some replacements) and handle them			
			if(preg_match_all("/<([A-Za-z0-9_]+)\:condition>(.+?)<\/\\1>/s", $this->Template, $conditionMatches)) {
				foreach($conditionMatches[1] as $condition) {
					if(!array_key_exists($condition, $this->_Conditions))
						$this->Template = preg_replace("/\<$condition:condition\>(.+?)\<\/$condition\>/s", '', $this->Template);
					else if($this->_Conditions[$condition] == false) 
						$this->Template = preg_replace("/\<$condition:condition\>(.+?)\<\/$condition\>/s", '', $this->Template);
					else 
						$this->Template = preg_replace("/\<$condition:condition\>(.+?)\<\/$condition\>/s", '$1', $this->Template);
				}
			}
				
			// Remove empty p-tags
			$this->Template = str_replace('<p></p>', '', $this->Template);
			
			if (!empty($this->_Icon))
				$document .= "\t\t<link href=\"{$this->_Icon}\" rel=\"shortcut icon\" type=\"image/x-icon\" />"; 
			$document .= $this->_CssFiles;
			$document .= "\t</head>\r\n\t<body>\r\n";
			$document .= $this->Template;
 			$document .= "\r\n\t</body>\r\n</html>";
 			$this->GeneratedOutput = $document;
 		}
 	}
?>