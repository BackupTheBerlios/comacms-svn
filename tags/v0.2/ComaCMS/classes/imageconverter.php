<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005-2006 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : imageconverter.php
 # created              : 2006-11-25
 # copyright            : (C) 2005-2006 The ComaCMS-Team
 # email                : comacms@williblau.de
 #----------------------------------------------------------------------
 # This program is free software; you can redistribute it and/or modify
 # it under the terms of the GNU General Public License as published by
 # the Free Software Foundation; either version 2 of the License, or
 # (at your option) any later version.
 #----------------------------------------------------------------------
 
 	/** ImageConverter
 	 * This class contains functions to manipulate images
 	 * 
 	 * @package ComaCMS
 	 */
	class ImageConverter {
		
		var $Size = array();
		var $_file = '';
		
		function ImageConverter($File) {
			$this->_file = $File;
			if(!file_exists($File))
				return null;
			// get the original sizes
			list($this->Size[0], $this->Size[1]) = getimagesize($File);
		}
		/**
		 * @param int Width
		 * @param int Height
		 * @param string Destinationfolder
		 * @param string Prefix
		 */
		function SaveResizedTo($Width, $Height, $DestinationFolder, $Prefix = '') {
			if(!file_exists($this->_file))
				return null;
			// get the extension of the file
			preg_match("'^(.*)\.(gif|jpe?g|png|bmp)$'i", $this->_file, $ext);
			
			// mostly all php-binarys for windows are not compiled with --enable-memory-limit
			// and don't suport memory_get_usage() and are able to handle bigger data
		 	// (it is not bad for us)
		 	$memory_limit = ini_get("memory_limit");
		 	
			if(substr($memory_limit, -1) == 'M')
				$memory_limit = substr($memory_limit, 0, -1) * 1048576;
		 	
			$needspace = ($this->Size[0] * $this->Size[1] + $Width * $Height) * 6.88;// was 5
			
			if(function_exists('memory_get_usage'))
				$free_memory = $memory_limit - memory_get_usage();
			else
				$free_memory = 0;
			
			// if there is not enough free memory available return false
			if($needspace > $free_memory && $free_memory > 0)
				return false;
			$newImage = ImageCreateTrueColor($Width, $Height);

			switch (strtolower($ext[2])) {
				case 'jpg' :
				case 'jpeg': $source  = imagecreatefromjpeg($this->_file);
					break;
				case 'gif' : $source  = imagecreatefromgif($this->_file);
					break;
				case 'png' : $source  = imagecreatefrompng($this->_file);
					break;
				default    : return false;
			}
			imagecopyresized($newImage, $source, 0, 0, 0, 0, $Width, $Height, $this->Size[0], $this->Size[1]);
			$OutputFile = $DestinationFolder . '/' . $Prefix . basename($this->_file);
			switch (strtolower($ext[2])) {
				case 'jpg' :
				case 'jpeg': imagejpeg($newImage, $OutputFile, 100);
					break;
				case 'gif' : imagepng($newImage, $OutputFile . '.png');
					$OutputFile .= '.png';
					break;
				case 'png' : imagepng($newImage, $OutputFile);
					break;
				default	   : return false;
			}

			return $OutputFile;
		}
		
		/**
		 * Calculate the width and height of the loaded picture, with a given maximum
		 */
		function CalcSizeByMax($Maximum) {
			if(!file_exists($this->_file))
				return null;
			$size = array();
			$size[0] = ($this->Size[0] > $this->Size[1]) ? round($Maximum, 0) : round($this->Size[0] / ($this->Size[1] / $Maximum), 0);
			$size[1] = ($this->Size[1] > $this->Size[0]) ? round($Maximum, 0) : round($this->Size[1] / ($this->Size[0] / $Maximum), 0);
			return $size;
		}
	}
 
?>
