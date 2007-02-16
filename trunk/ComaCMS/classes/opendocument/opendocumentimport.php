<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005-2007 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : opendocumentimport.php
 # created              : 2007-01-24
 # copyright            : (C) 2005-2007 The ComaCMS-Team
 # email                : comacms@williblau.de
 #----------------------------------------------------------------------
 # This program is free software; you can redistribute it and/or modify
 # it under the terms of the GNU General Public License as published by
 # the Free Software Foundation; either version 2 of the License, or
 # (at your option) any later version.
 #----------------------------------------------------------------------
	
	require_once __ROOT__ . '/classes/opendocument/lifo.php';
	
	class OpenDocumentImport {
		
		var $_Content = '';
		var $_Styles = '';
		var $_StyleData = array();
		var $_StylePath;
		var $_OfficeBody = false;
		var $_Filename = '';
		var $_StyleName = '';
		var $_OpensName = '';
		var $Images = array();
		
		var $_ImageQuery = array();
		function OpenDocumentImport() {
			$this->_StylePath = new LiFo();
		}
		
		function LoadFile($Filename, $ImagePath) {	
			if(!file_exists($Filename)) {
				trigger_error('Could not find the file "' .$Filename . '"!', E_USER_ERROR);
				return false;
			}
				
			$this->_ImagesPath = $ImagePath;
			$this->_Filename = $Filename;
			// windows systems need the absolute path
			$zip = zip_open(realpath($Filename));
			if(!$zip)
				return false;
			while($zipEntry = zip_read($zip)) {
				$zipEntryName = zip_entry_name($zipEntry);
				switch($zipEntryName) {
					case 'content.xml':
						if(zip_entry_open($zip, $zipEntry, 'r')){
							$this->_Content = zip_entry_read($zipEntry, zip_entry_filesize($zipEntry));
							zip_entry_close($zipEntry);
						}
						break;
					case 'styles.xml':
						if(zip_entry_open($zip, $zipEntry, 'r')){
							$this->_Styles = zip_entry_read($zipEntry, zip_entry_filesize($zipEntry));
							zip_entry_close($zipEntry);
						}
						break;
				}
			}
			zip_close($zip);
			$this->_ReadStyle();
			$this->_ReadContent();
			$this->_LoadImages();
	
			$this->_Text =  str_replace('****', '', $this->_Text);
			$this->_Text =  str_replace('____', '', $this->_Text);
			$this->_Text =  str_replace('////', '', $this->_Text);
			$this->_Text =  str_replace('&', '&amp;', $this->_Text);
			$this->_Text = preg_replace("#<p>[\r\n\t\ ]{0,}</p>#i", '', $this->_Text);
			clearstatcache();
		}
		
		function _LoadImages() {
			$zip = zip_open(realpath($this->_Filename));
			if(!$zip)
				return false;
			while($zipEntry = zip_read($zip)) {
				$zipEntryName = zip_entry_name($zipEntry);
				if(in_array($zipEntryName, $this->_ImageQuery)) {
					if(zip_entry_open($zip, $zipEntry, 'r')){
							$buf = zip_entry_read($zipEntry, zip_entry_filesize($zipEntry));
							zip_entry_close($zipEntry);
							$fileExtension = end(explode('.', $zipEntryName));
							$newName = $this->_ImagesPath . '/' .basename($this->_Filename). '_' . count($this->_ImageQuery) . '.' . $fileExtension;
							$this->Images[] = $newName;
							if(!file_exists($newName)) {
								$writer = fopen($newName, 'w');
	           					fwrite($writer, $buf);
	           					fclose($writer);
							}
						}
				}
			}
		}
		
		function QueryImage($Entry) {
			
			$this->_ImageQuery[] = $Entry;
			$fileExtension = end(explode('.', $Entry));
			return basename($this->_Filename). '_' . count($this->_ImageQuery) . '.' . $fileExtension;
		}
		
		function _OpenElement($Parser, $Name, $Attributes) {
			if($Name == 'STYLE:STYLE' || $Name == 'TEXT:LIST-STYLE') {
				
				$this->_StyleName = $Attributes['STYLE:NAME'];
				$this->_StyleData[$this->_StyleName] = array();
				if(array_key_exists('STYLE:CLASS', $Attributes))
					$this->_StyleData[$this->_StyleName]['class'] = $Attributes['STYLE:CLASS'];
					 				
				if(array_key_exists('STYLE:PARENT-STYLE-NAME', $Attributes))
					$this->_StyleData[$this->_StyleName]['parent'] = $Attributes['STYLE:PARENT-STYLE-NAME']; 				
			}
			else if($Name == 'TEXT:LIST-LEVEL-STYLE-NUMBER' && $this->_StyleName != '') {
				if(array_key_exists('TEXT:LEVEL', $Attributes))
					$this->_StyleData[$this->_StyleName][$Attributes['TEXT:LEVEL']] = 'numeric';
			}
			else if($Name == 'TEXT:LIST-LEVEL-STYLE-BULLET' && $this->_StyleName != '') {
				if(array_key_exists('TEXT:LEVEL', $Attributes))
					$this->_StyleData[$this->_StyleName][$Attributes['TEXT:LEVEL']] = 'non-numeric';
			}
			else if(($Name == 'STYLE:TEXT-PROPERTIES' || $Name == 'STYLE:PARAGRAPH-PROPERTIES' )&& $this->_StyleName != '') {			
					if(array_key_exists('FO:FONT-SIZE', $Attributes))
						$this->_StyleData[$this->_StyleName]['fontsize'] = $Attributes['FO:FONT-SIZE'];
						
					if(array_key_exists('FO:FONT-WEIGHT', $Attributes))
						$this->_StyleData[$this->_StyleName]['fontweight'] = $Attributes['FO:FONT-WEIGHT'];
						
					if(array_key_exists('FO:FONT-STYLE', $Attributes))
						$this->_StyleData[$this->_StyleName]['fontstyle'] = $Attributes['FO:FONT-STYLE'];
						
					if(array_key_exists('STYLE:TEXT-UNDERLINE-STYLE', $Attributes))
						$this->_StyleData[$this->_StyleName]['underline'] = $Attributes['STYLE:TEXT-UNDERLINE-STYLE'];
						
					if(array_key_exists('FO:TEXT-ALIGN', $Attributes))
						$this->_StyleData[$this->_StyleName]['align'] = $Attributes['FO:TEXT-ALIGN'];
			}
			else if($Name == 'OFFICE:BODY') {
				$this->_StyleName = '';
				$this->_OfficeBody = true;
				// make every style information completely independet of their parent styles
				foreach($this->_StyleData as $styleName => $style) {
					// has it a parent style?
					if(array_key_exists('parent', $style)) {
						// is the parent style available?
						if(array_key_exists($style['parent'], $this->_StyleData)) {
							// stop if 
							while($style['parent'] != '') {
								// get all style-information if it isn't already overwritten
								foreach($this->_StyleData[$style['parent']] as $field => $fieldValue ) {
									if(!array_key_exists($field, $this->_StyleData[$styleName])) {
										$this->_StyleData[$styleName][$field] = $fieldValue;
									}
									else if($field == 'fontsize') {
										$parentUnit = '';
										$unit = '';
										$parent = '';
										$value = '';
										preg_match("/([0-9.]+)(.+?)([0-9.]+)(.+)/s", $fieldValue . $this->_StyleData[$styleName]['fontsize'] , $fontMatches);
										/*$fontMatches[1]; // numeric (parent)
										$fontMatches[2]; // unit (parent)
										$fontMatches[3]; // numeric
										$fontMatches[4]; // unit*/	
										if($fontMatches[4] == '%'){
											$this->_StyleData[$styleName]['fontsize'] = round($fontMatches[1] * $fontMatches[3] * 0.01, 0) . $fontMatches[2]; 
										}
									}
								}
								// if the parent style has an avaliable parent style...
								if(array_key_exists('parent', $this->_StyleData[$style['parent']]) && array_key_exists($this->_StyleData[$style['parent']]['parent'], $this->_StyleData)) {
									$this->_StyleData[$styleName]['parent'] = $this->_StyleData[$style['parent']]['parent'];
									$style['parent'] = $this->_StyleData[$style['parent']]['parent'];
								}
								// if not:
								else {
									unset($this->_StyleData[$styleName]['parent']);
									$style['parent'] = '';
								}
							}
						}
					}
				}
			}
			else if(($Name == 'TEXT:P' || $Name == 'TEXT:SPAN' || $Name == 'TEXT:H' || $Name == 'TABLE:COLUMN' || $Name == 'TEXT:A' || $Name == 'TEXT:LIST-ITEM' || $Name == 'TEXT:LIST' || $Name == 'TEXT:LINE-BREAK') && $this->_OfficeBody) {
				
				$styleName = '';
				$bold = false;
				$italic = false;
				$underline = false;
				$center = false;
				$class = 'text';
				$fontsize = '12pt';
				if(array_key_exists('TEXT:STYLE-NAME', $Attributes)) {
					$styleName = $Attributes['TEXT:STYLE-NAME'];
					if(!array_key_exists($styleName, $this->_StyleData))
						$styleName = '';
					if(array_key_exists('fontweight', $this->_StyleData[$styleName]))
						$bold = ($this->_StyleData[$styleName]['fontweight'] == 'bold');
					if(array_key_exists('fontstyle', $this->_StyleData[$styleName]))
						$italic = ($this->_StyleData[$styleName]['fontstyle'] == 'italic');
					if(array_key_exists('fontsize', $this->_StyleData[$styleName]))
						$fontsize = $this->_StyleData[$styleName]['fontsize'];
					if(array_key_exists('class', $this->_StyleData[$styleName]))
						$class = $this->_StyleData[$styleName]['class'];
					if(array_key_exists('underline', $this->_StyleData[$styleName]))
						$underline = ($this->_StyleData[$styleName]['underline'] == 'solid'); 	 		
					if(array_key_exists('align', $this->_StyleData[$styleName]))
						$center = ($this->_StyleData[$styleName]['align'] == 'center');
				}
							
				$this->_StylePath->Add($styleName);
	
				if($Name == 'TEXT:P' && $class == 'text')
					$this->OpenParagraph();
				
				if(($Name == 'TEXT:P' || $Name == 'TEXT:SPAN' || $Name == 'TABLE:COLUMN' || $Name == 'TEXT:A') && $styleName != ''){
					$this->OpenCenter($center);
					$this->OpenBold($bold);
					$this->OpenItalic($italic);
					$this->OpenUnderline($underline);
				}
				
				if($Name == 'TEXT:H') {
					$headline = 2;
	
					if($styleName != '' && substr($styleName, 0 , 11) ==  'Heading_20_')
						$headline = substr($styleName, 11) +1;
					else if($styleName != '') {
						$defaultFontsize = '12pt';
						if(array_key_exists('Standard', $this->_StyleData))
							if(array_key_exists('fontsize', $this->_StyleData['Standard']))
								$defaultFontsize = $this->_StyleData['Standard']['fontsize'];
						 preg_match("/([0-9.]+)(.+?)([0-9.]+)(.+)/s", $defaultFontsize . $fontsize , $fontMatches);
						/*
						 * $fontMatches[1]; = numeric (default)
						 * $fontMatches[2]; = unit (parent)
						 * $fontMatches[3]; = numeric
						 * $fontMatches[4]; = unit */
						 $multiplier = 1.2;
						 if($fontMatches[2] == $fontMatches[4])
						 	$multiplier = $fontMatches[3] / $fontMatches[1];
						 if($multiplier > 1.41)
						 	$headline = 2;
						 else if($multiplier > 1.16)
						 	$headline = 3;
						 else if($multiplier > 1.08)
						 	$headline = 4;
						 else if($multiplier > 1.00)
						 	$headline = 5;
						 else
						 	$headline = 6;
					}
					$this->OpenHeadline($headline);
				}
				else if($Name == 'TEXT:LINE-BREAK')
					$this->LineBreak();
				else if($Name == 'TEXT:A' && array_key_exists('XLINK:HREF', $Attributes))
					$this->BeginLink($Attributes['XLINK:HREF']);
				else if($Name == 'TEXT:LIST' && $styleName != '')
					$this->OpenList($this->_StyleData[$styleName]);
				else if($Name == 'TEXT:LIST')
					$this->OpenList(null);
				else if($Name == 'TEXT:LIST-ITEM')
					$this->OpenListItem();
			}
			else if($Name == 'DRAW:IMAGE') {
				if(array_key_exists('XLINK:HREF', $Attributes)) {
					$image = $this->QueryImage($Attributes['XLINK:HREF']);
					$this->SetImagePath($image);
				}
			}
	
			else if($Name == 'DRAW:FRAME') {
				if(array_key_exists('SVG:WIDTH', $Attributes) && array_key_exists('SVG:HEIGHT', $Attributes)) {
					$height = $Attributes['SVG:HEIGHT'];
					$width = $Attributes['SVG:WIDTH'];
					define('CMTOPIXEL', 37.7965);
					// convert into pixel
					if(substr($height, -2) == 'cm')
						$height = round(substr($height, 0, -2) * CMTOPIXEL ,0);
					if(substr($width, -2) == 'cm')
						$width = round(substr($width, 0, -2) * CMTOPIXEL ,0);
					$this->BeginImage($width . 'x' . $height);
				}
				else
					$this->BeginImage();	
			}
			else if($Name == 'TABLE:TABLE')
				$this->OpenTable();
			else if($Name == 'TABLE:TABLE-ROW')
				$this->OpenTableRow();
			else if($Name == 'TABLE:TABLE-CELL')
				$this->OpenTableCell();
	
			$this->_OpendName = $Name;
			
		}
		
		function _CloseElement($Parser, $Name) {
			
			if(($Name == 'TEXT:P' || $Name == 'TEXT:SPAN' || $Name == 'TEXT:H' || $Name == 'TABLE:COLUMN' || $Name == 'TEXT:A' || $Name == 'TEXT:LIST-ITEM' || $Name == 'TEXT:LIST' || $Name == 'TEXT:LINE-BREAK') && $this->_OfficeBody) {
				$styleName = $this->_StylePath->Get();
				$bold = false;
				$italic = false;
				$class = 'text';
				$underline = false;
				$center = false;
				if($styleName != '') {
					if(array_key_exists('fontweight', $this->_StyleData[$styleName]))
						$bold = ($this->_StyleData[$styleName]['fontweight'] == 'bold');
						
					if(array_key_exists('class', $this->_StyleData[$styleName]))
						$class = $this->_StyleData[$styleName]['class'];
						
					if(array_key_exists('fontstyle', $this->_StyleData[$styleName]))
						$italic = ($this->_StyleData[$styleName]['fontstyle'] == 'italic');
						
					if(array_key_exists('underline', $this->_StyleData[$styleName]))
						$underline = ($this->_StyleData[$styleName]['underline'] == 'solid');
						
					if(array_key_exists('align', $this->_StyleData[$styleName]))
						$center = ($this->_StyleData[$styleName]['align'] == 'center'); 
				}
				if($Name == 'TEXT:A')
					$this->EndLink();
				if(($Name == 'TEXT:P' || $Name == 'TEXT:SPAN' || $Name == 'TABLE:COLUMN' || $Name == 'TEXT:A') && $styleName != '') {
					$this->CloseUnderline($underline);
					$this->CloseItalic($italic);
					$this->CloseBold($bold);
					$this->CloseCenter($center);
				}
				
				if($Name == 'TEXT:P' && $class == 'text') 
					$this->CloseParagraph();
				else if($Name == 'TEXT:H')
					$this->CloseHeadline();
				else if($Name == 'TEXT:LIST')
					$this->CloseList();
				else if($Name == 'TEXT:LIST-ITEM')
					$this->CloseListItem();
							
			}
			else if($Name == 'DRAW:FRAME')
				$this->EndImage();
			else if($Name == 'TABLE:TABLE')
				$this->CloseTable();
			else if($Name == 'TABLE:TABLE-CELL')
				$this->CloseTableCell();
			else if($Name == 'TABLE:TABLE-ROW')
				$this->CloseTableRow();
		}
		
		function _TextElement($Parser, $Data) {
			$Name = &$this->_OpendName;
			if($Name == 'TEXT:P' || $Name == 'TEXT:SPAN' || $Name == 'TEXT:H' || $Name == 'TABLE:COLUMN' || $Name == 'TEXT:A' || $Name == 'TEXT:LIST-ITEM'|| $Name == 'TEXT:LINE-BREAK' || $Name == 'TEXT:SEQUENCE' || $Name == 'DRAW:IMAGE')
				$this->AddText($Data);
		}
		
		function _ReadStyle() {
			if($this->_Styles == '')
				return;
			
			$StyleParser = xml_parser_create();
			xml_set_object($StyleParser, $this);
			xml_set_element_handler($StyleParser, '_OpenElement', '_CloseElement');
			if (!xml_parse($StyleParser, $this->_Styles))
	       	die(sprintf("XML error: %s at line %d",
	                   xml_error_string(xml_get_error_code($StyleParser)),
	                   xml_get_current_line_number($StyleParser)));
			xml_parser_free($StyleParser);
			
		}
		
		function _ReadContent() {
			if($this->_Content == '')
				return;
			
			$StyleParser = xml_parser_create();
			xml_set_object($StyleParser, $this);
			xml_set_element_handler($StyleParser, '_OpenElement', '_CloseElement');
			xml_set_character_data_handler($StyleParser, '_TextElement');
			if (!xml_parse($StyleParser, $this->_Content))
	      		die(sprintf('XML error: %s at line %d',
	                   xml_error_string(xml_get_error_code($StyleParser)),
	                   xml_get_current_line_number($StyleParser)));
			xml_parser_free($StyleParser);
			
		}
	}
?>
