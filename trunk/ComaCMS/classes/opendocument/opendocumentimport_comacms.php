<?php
/**
 * @package ComaCMS
 * @subpackage Opendocument Importer
 * @copyright (C) 2005-2007 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : opendocumentimport_comacms.php
 # created              : 2007-01-24
 # copyright            : (C) 2005-2007 The ComaCMS-Team
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
 	require_once __ROOT__ . '/classes/opendocument/opendocumentimport.php';
 	
 	/**
 	 * @package ComaCMS
 	 * @subpackage Opendocument Importer
 	 */
	class OpenDocumentImport_ComaCMS extends OpenDocumentImport {
		var $_Text = '';
		var $_StatusBold = false;
		var $_StatusItalic = false;
		var $_StatusUnderline = false;
		var $_StatusItalicNonItalic = false;
		var $_StatusBoldNonBold = false;
		var $_StatusUnderlineNonUnderline = false;
		var $_HeadlineNr = 0;
		var $_StatusLink = false;
		var $_ImageFrames = 0;
		var $_ImageTitle = '';
		var $_ImagePath = '';
		var $_SizeString = '';
		var $_StatusList = false;
		var $_ListNr = 0;
		var $_ListStructure = array();
		var $_FirstCell = '';
		
		function OpenItalic($Italic = true) {
			if(!$this->_StatusItalic && $Italic) {
				$this->_StatusItalic = true;
				$this->_Text .= "//";
			}
			else if($this->_StatusItalic && !$Italic) {
				$this->CloseItalic();
				$this->_StatusItalicNonItalic = true;
			}
		}
		
		function CloseItalic($Italic = true) {
			if($this->_StatusItalic && $Italic) {
				$this->_StatusItalic = false;
				if($this->_StatusUnderline)
					$this->CloseUnderline();
				$this->_Text .= "//";
			}
			else if(!$this->_StatusItalic && !$Italic && $this->_StatusItalicNonItalic) {
				$this->OpenItalic();
				$this->_StatusItalicNonItalic = false;
			}
		}
		
		function OpenUnderline($Underline = true) {
			if(!$this->_StatusUnderline && $Underline) {
				$this->_StatusUnderline = true;
				$this->_Text .= "__";
			}
			else if($this->_StatusUnderline && !$Underline) {
				$this->CloseUnderline();
				$this->_StatusUnderlineNonUnderline = true;
			}
		}
		
		function CloseUnderline($Underline = true) {
			if($this->_StatusUnderline && $Underline) {
				$this->_StatusUnderline = false;
				$this->_Text .= "__";
			}
			else if(!$this->_StatusUnderline && !$Underline && $this->_StatusUnderlineNonUnderline) {
				$this->OpenUnderline();
				$this->_StatusUnderlineNonUnderline = false;
			}
		}
		
		
		function OpenBold($Bold = true) {
			if(!$this->_StatusBold && $Bold) {
				$this->_StatusBold = true;
				$this->_Text .= "**";
			}
			else if($this->_StatusBold && !$Bold) {
				$this->CloseBold();
				$this->_StatusBoldNonBold = true;
			}
		}
		
		function CloseBold($Bold = true) {
			if($this->_StatusBold && $Bold) {
				$this->_StatusBold = false;
				$this->_Text .= "**";
			}
			else if(!$this->_StatusBold && !$Bold && $this->_StatusBoldNonBold) {
				$this->OpenBold();
				$this->_StatusBoldNonBold = false;
			}
		}
		
		function BeginImage($SizeString = '') {
			$this->_SizeString = $SizeString;
			$this->_ImageFrames++;
		}
		
		function SetImagePath($Path) {
			$this->_ImagePath = $Path;
		}
		
		function EndImage() {
			$this->_ImageFrames--;
			if($this->_ImageFrames == 0) {
				$size = '';
				if($this->_SizeString != '') {
					$sizes = explode('x', $this->_SizeString);
					$this->_SizeString = '|' . $this->_SizeString;
				}
				if($this->_ImageTitle == '')
					$this->_Text .= '{{' . $this->_ImagePath . $this->_SizeString . '}}';
				else
					$this->_Text .= '{{' . $this->_ImagePath . $this->_SizeString . '|' . $this->_ImageTitle . '}}';
				$this->_ImageTitle = '';
				$this->_SizeString = '';
			}
		}
		
		function BeginLink($Url) {
			$this->_Text .= '[[' . $Url . '|';
			$this->_StatusLink = true;
		}
		
		function EndLink() {
			if(!$this->_StatusLink)
				return;
			$this->_Text .= ']]';
			$this->_StatusLink = false; 
		}
	
		function OpenParagraph() {
			if(!$this->_StatusList)
				$this->_Text .= "\n";
		}
		
		function CloseParagraph() {
			if(!$this->_StatusList)
				$this->_Text .= "\n";
		}
		
		function OpenHeadline($Nr = 2) {
			if($this->_HeadlineNr < 1)
				$this->CloseHeadline();
			if($Nr > 6)
				$Nr = 6;
			else if($Nr < 1)
				$Nr = 1;
			$this->_HeadlineNr = $Nr;
			$this->_Text .= "\n" . str_repeat('=', $Nr) . ' ';
		}
		
		function CloseHeadline() {
			if($this->_HeadlineNr > 0) {
				$this->_Text .= ' ' . str_repeat('=', $this->_HeadlineNr) . "\n";
				$this->_HeadlineNr = 0;
			}
		}
		
		function AddText($Text) {
			if($this->_ImageFrames == 0)
				$this->_Text .= $Text;
			else 
				$this->_ImageTitle .= $Text;
		}
		
		function LineBreak(){
			$this->_Text .= '\\\\';
		}	
		
		function OpenTable() {}
		
		function OpenTableRow() {
			$this->_FirstCell = true;
		}
		function OpenTableCell() {
			if($this->_FirstCell) {
				$this->_Text .= '|';
				$this->_FirstCell = false;
			}
		}
		
		function CloseTableCell() {
			$this->_Text .= '|';
		}
		
		function CloseTableRow() {
			$this->_Text .= "\n";
		}
		
		function CloseTable() {}
		
		function OpenList($Structure) {
			$this->_StatusList = true;
			$this->_ListNr++;
			if($Structure !== null) 
				$this->_ListStructure = $Structure;
			else
				$this->_Text .= "\n";
		}
		
		function CloseList() {
			if($this->_ListNr <= 1)
				$this->_StatusList = false;
			$this->_ListNr--;
		}
		
		function OpenListItem() {
			$nr = ($this->_ListNr > 10) ? 10 : $this->_ListNr;
			$tmp = ' ';
			for($i = 0; $i < $this->_ListNr;$i++) {
				if($this->_ListStructure[$i+1] == 'numeric')
					$tmp .= "# ";
				else
					$tmp .= "* ";
			}
			$this->_Text .= $tmp;		
		}
		
		function CloseListItem() {
			$this->_Text .= "\n";
		}
		
		function OpenCenter($Center) {
			if($Center)
				$this->_Text .= '<center>';
		}
		
		function CloseCenter($Center) {
			if($Center)
				$this->_Text .= '</center>';
		}
	}
?>
