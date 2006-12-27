<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005-2006 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : language.php
 # created              : 2006-12-14
 # copyright            : (C) 2005-2006 The ComaCMS-Team
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
 		
 		var $OutputLanguage = '';
 		var $_LangString = array();
 		
 		function Language($OutputLanguage) {
			$this->OutputLanguage = $OutputLanguage;
 		}
 		
 		function AddSources($LanguageDirectory) {
 			$langString = &$this->_LangString;
 			if(file_exists($LanguageDirectory.'/lang_' . $this->OutputLanguage . '.php'))
 				include($LanguageDirectory.'/lang_' . $this->OutputLanguage . '.php');
 		}
 		
 		function GetTranslation($TranslationString) {
 			if(array_key_exists($TranslationString, $this->_LangString))
 				return $this->_LangString[$TranslationString];
 			else {
 				$this->_LangString[$TranslationString] = $TranslationString;
 				return $TranslationString;
 			}
 		}
 	}
?>
