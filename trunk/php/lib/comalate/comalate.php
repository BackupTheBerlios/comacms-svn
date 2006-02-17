<?php
/**
 * @package ComaLate
 * @copyright (C) 2005-2006 The ComaCMS-Team
 */
 #----------------------------------------------------------------------#
 # file			: comalate.php					#
 # created		: 2006-02-12					#
 # copyright		: (C) 2005-2006 The ComaCMS-Team		#
 # email		: comacms@williblau.de				#
 #----------------------------------------------------------------------#
 # This program is free software; you can redistribute it and/or modify	#
 # it under the terms of the GNU General Public License as published by	#
 # the Free Software Foundation; either version 2 of the License, or	#
 # (at your option) any later version.					#
 #----------------------------------------------------------------------#
 
   	
  	define('DOCTYPE_XHTML_STRICT', 'xhtml strict');
  	define('DOCTYPE_XHTML_TRANSITIONAL', 'xhtmltramsitional');
  	//define('DOCTYPE_XHTML_FRAMESET', 'xhtml frameset');
  	define('DOCTYPE_DEFAULT', DOCTYPE_XHTML_STRICT);
  	
	/**
	 * ComaLate Template Engine
	 * @package ComaLate
	 */
 	class ComaLate {
 		
 		
 		var $_Meta = array(); 
 		var $_Replacements = array();
 		var $_ReplacementsArrays = array();
 		var $_Conditions = array();
 		var $_Doctype = '';	
 		var $Language = 'en';
 		var $Title = '';
 		var $Charset = 'UTF-8';
		var $_CssFiles = '';
		var $Template = '';
		var $_Config;
		
 		function ComaLate() {
 			$this->SetDoctype(DOCTYPE_DEFAULT);
 		}
 		
 		function SetDoctype($Doctype) {
 			switch ($Doctype) {
				case DOCTYPE_XHTML_TRANSITIONAL:
					$this->_Doctype = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
					break;
				case DOCTYPE_XHTML_STRICT:
				default:
					$this->_Doctype = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN""http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
					break;
			}
 		}
 		
 		 		
 		function SetMeta($Name, $Value) {
 			$this->_Meta[$Name] = $Value;
		}
 		
 		function SetCondition($Name, $Value) {
 			if($Value)
 				$this->_Conditions[$Name]= true;
 			else	
 				$this->_Conditions[$Name]= false;
 		}
 		
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
 		
 		function _AddCssFiles($cssFiles,$TemplatesFolder, $TemplateName = 'default') {
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
 			
 			if(file_exists($config['template'])) {
 				$template_file = fopen($config['template'], 'r');
				$this->Template = fread($template_file, filesize($config['template']));
				fclose($template_file);
 			}
 			$this->_Config = $config;
 		}
 		
 		function _Replace($Match) {
 			if(array_key_exists($Match, $this->_Replacements))
 				return $this->_Replacements[$Match];
 			return "{$Match}";
 		}
 		
 		function _RepeatReplace($Match, $Replacement) {
 			if(array_key_exists($Match, $this->_ReplacementsArrays)) {
 				$output = '';
 				foreach($this->_ReplacementsArrays[$Match] as $repeat) {
					$toReplaceString = $Replacement;
					foreach($repeat as $subName => $subValue) {
						$toReplaceString = str_replace('{' . $subName . '}', $subValue, $toReplaceString);
					}
					$output .= $toReplaceString;
				}
				return stripslashes($output);
 			}
 			return '';
 		}
 		
 		function _AddConditionalInlineCss($condition) {
 			if(!empty($this->_Config['conditional-css'][$condition])) {
 				foreach($this->_Config['conditional-css'][$condition] as $outputType => $output) {
 					if($outputType == 'iefix') {
 						$ieCondition = key($output);
 						$value = current($output);
 						$this->_CssFiles .= "\t\t<!--[if $ieCondition]><style type=\"text/css\">$value</style><![endif]-->\r\n";
 							
 					}
 					else {
 						$this->_CssFiles .= "\t\t<style type=\"text/css\" media=\"$outputType\">$output</style>\r\n";
 						
 					}
 				}
 			}
 		}
 		
 		function PrintOutput() {
 			echo $this->_Doctype;
 			echo "\r\n<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"$this->Language\" lang=\"$this->Language\">\r\n\t<head>\r\n\t\t<title>$this->Title</title>\r\n\t\t<meta http-equiv=\"Content-Type\" content=\"text/html; charset=$this->Charset\" />\r\n";
 			if (count($this->_Meta) > 0) {
 				foreach($this->_Meta as $metaName => $metaValue) {
 					echo "\t\t<meta name=\"$metaName\" content=\"$metaValue\" />\r\n";
 				}
 			}
 			
			
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
			
			$this->Template = preg_replace("/<([A-Za-z0-9_]+)\:loop>(.+?)<\/\\1>/es", "\$this->_RepeatReplace('\\1', '\\2')", $this->Template);
			$this->Template = preg_replace( '/{(.+?)}/e', "\$this->_Replace('\\1')", $this->Template);
			$this->Template = str_replace('<p></p>', '', $this->Template);
			echo $this->_CssFiles;
			echo "\t</head>\r\n\t<body>\r\n";
			echo $this->Template;
 			echo "\t</body>\r\n</html>";
 		}
 	}
?>