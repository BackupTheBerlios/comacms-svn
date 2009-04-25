<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005-2007 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : language.php
 # created              : 2006-12-14
 # copyright            : (C) 2005-2007 The ComaCMS-Team
 # email                : comacms@williblau.de
 #----------------------------------------------------------------------
 # This program is free software; you can redistribute it and/or modify
 # it under the terms of the GNU General Public License as published by
 # the Free Software Foundation; either version 2 of the License, or
 # (at your option) any later version.
 #----------------------------------------------------------------------

	/**
	 * @package ComaCMS
	 */
 	class Language {
 		
 		/**
 		 * @var Sql This is a link to the mysql database
 		 * @access private
 		 */
 		var $_SqlConnection;
 		
 		/**
 		 * @var string Contains the output language of the current website
 		 * @access private
 		 */
 		var $OutputLanguage = '';
 		
 		/**
 		 * @var array Contains translations for short text snippets
 		 * @access private
 		 */
 		var $_LangString = array();
 		
 		/**
 		 * @var array Contains all possible languages to set a page to
 		 * @access public
 		 */
 		var $Languages = array('de', 'en');
 		
 		/**
 		 * Initializes the language class
 		 * @access public
 		 * @param string $OutputLanguage This should be the output language of the current webpage
 		 * @param Sql &$SqlConnection This is a link to the mysql database
 		 * @return void Initialized language class
 		 */
 		function Language(&$SqlConnection) {
			$this->_SqlConnection = &$SqlConnection;
 		}
 		
 		/**
 		 * Sets the outputlanguage for the page
 		 * @access public
 		 * @param string $OutputLanguage This is the short tag of the language in which the page should be put out
 		 * @return bool	Returns true if a legal language was set else false
 		 */
 		function SetOutputLanguage($SetLanguage) {
 			if ($SetLanguage != '') 
 				if (in_array($SetLanguage, $this->Languages)) {
 					
 					$this->OutputLanguage = $SetLanguage;
 					return true;
 				}
 			
 			return false;
 		}
 		
 		/**
 		 * Tests if an output language is set for the current page
 		 * @access public
 		 * @return bool Is the outputlanguage set?
 		 */
 		function CheckOutputLanguage() {
 			if ($this->OutputLanguage == '') 
 			
 				// If no language is set return false
 				return false;
 			else {
 				if (in_array($this->OutputLanguage, $this->Languages)) {
 					
 					// if there is something in the variable, and it is a legal language return true
 					return true;
 				}
 			}
 			
 			// in every other case return false
 			return false;
 		}
 		
 		/**
 		 * Loads all language files from a directory
 		 * @access public
 		 * @param string $LanguageDirectory This is the directory that should be searched for language files
 		 * @return void
 		 */
 		function AddSources($LanguageDirectory) {
 			$langString = &$this->_LangString;
 			if(file_exists($LanguageDirectory.'/lang_' . $this->OutputLanguage . '.php'))
 				include($LanguageDirectory.'/lang_' . $this->OutputLanguage . '.php');
 		}
 		
 		/**
 		 * Returns a translation for the requested $TranslationString from the local languages array
 		 * @access public
 		 * @param string $TranslationString The string to get a translation for
 		 * @return string A Translation for the requested string
 		 */
 		function GetTranslation($TranslationString) {
 			if(array_key_exists($TranslationString, $this->_LangString))
 				return $this->_LangString[$TranslationString];
 			else {
 				$this->_LangString[$TranslationString] = preg_replace("/\%([A-Za-z0-9_]+)\%/", "&quot;%s&quot;", $TranslationString);
 				$this->_LangString[$TranslationString] = '*' . str_replace('_', ' ', $this->_LangString[$TranslationString]) . '*';
 				return $this->_LangString[$TranslationString];
 			}
 		}
 	}
?>
