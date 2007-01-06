<?php
/**
 * @package ComaCMS
 * @subpackage AdminInterface
 * @copyright (C) 2005-2006 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : filename.php
 # created              : 2006-12-17
 # copyright            : (C) 2005-2006 The ComaCMS-Team
 # email                : comacms@williblau.de
 #----------------------------------------------------------------------
 # This program is free software; you can redistribute it and/or modify
 # it under the terms of the GNU General Public License as published by
 # the Free Software Foundation; either version 2 of the License, or
 # (at your option) any later version.
 #----------------------------------------------------------------------
 	
 	/**
 	 * @ignore
 	 */
 	require_once __ROOT__ . '/classes/admin/admin.php';
 	require_once __ROOT__ . '/classes/pagepreview.php';
 	
 	/**
 	 * @package ComaCMS
 	 */
 	class Admin_PagePreview extends Admin {
 		
 		/**
 		 * @access private
 		 * @var class Functions
 		 */
 		var $_PagePreview;
 		/**
 		 * @access private
 		 */
 		function _Init() {
 			$this->_PagePreview = new PagePreview(&$this->_Config);
 		}
 		/**
 		 * Returns the code of the page
 		 * @access public
 		 * @param string Action Gives the name of the subpage to call
 		 * @return string Pagedata
 		 */
 		function GetPage($Action = '') {
 			$out = '';
 			// Get external parameters
 			$style = GetPostOrGet('style');
 			if (empty($style))
 				$style = $this->_Config->Get('style', 'comacms');
 			$save = GetPostOrGet('save');
 			
 			if(!empty($save))
 				$Action = 'saveStyle';
 			switch ($Action) {
 				case 'saveStyle':	$this->_PagePreview->SaveStyle($style);
 				case 'style': 		$out .= $this->_Style($style);
 									break;
 				default:			$out .= $this->_PagePreview();
 									break;
 			}
 			return $out;
 		}
 		
 		/**
 		 * Returns the template for the PagePreview page
 		 * @access private
 		 * @return string Pagedata
 		 */
 		 function _PagePreview() {
 		 	// Set Replacements for the template
 		 	$this->_ComaLate->SetReplacement('PAGEPREVIEW', $this->_Translation->GetTranslation('pagepreview'));
 		 	
 		 	// Throw out the template data for this page
 		 	$template = '<h2>{PAGEPREVIEW}</h2>
 		 				<iframe src="index.php" class="pagepreview"></iframe>';
 		 	return $template;
 		 }
 		 
 		 /**
 		  * Returns the template for the Style page
 		  * @access private
 		  * @param string Style Show the page with this style
 		  * @return string PageData
 		  */
 		 function _Style($Style) {
 		 	
 		 	// Check parameters
 		 	if(empty($style))
				$style = $this->_Config->Get('style');
 		 	
 		 	$styleSelect = $this->_PagePreview->GetStyles(__ROOT__ . '/styles/', $Style);
			$this->_ComaLate->SetReplacement('PREVIEW_STYLE_SELECT', $styleSelect);
			
			// Set replacements for the template
 		 	$this->_ComaLate->SetReplacement('ACTUALSTYLE', $Style);
 		 	$this->_ComaLate->SetReplacement('SITESTYLE', $this->_Translation->GetTranslation('sitestyle'));
 		 	
 		 	// Throw out the template data
 		 	$template = '<script type="text/javascript" language="JavaScript" src="./system/functions.js"></script>
 		 				<h2>{SITESTYLE}</h2>
						<iframe id="previewiframe" class="pagepreview" src="index.php?style={ACTUALSTYLE}"></iframe>
						<form action="admin.php" method="get">
							<input type="hidden" name="page" value="style" />
							<label for="stylepreviewselect"><strong>{SITESTYLE}:</strong>
								<select id="stylepreviewselect" name="style" size="1">
									<PREVIEW_STYLE_SELECT:loop>
										<option value="{ENTRY_VALUE}"{ENTRY_SELECTED}>{ENTRY_LONGNAME}</option>
									</PREVIEW_STYLE_SELECT>
								</select>
							</label>
							<input type="submit" value="Vorschau" onclick="preview_style();return false;" class="button" />
							<input type="submit" value="Speichern" name="save" class="button" />
						</form>';
			return $template;
 		}
 	} 
?>