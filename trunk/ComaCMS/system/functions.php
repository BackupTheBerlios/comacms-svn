<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005-2006 The ComaCMS-Team
 */
 #----------------------------------------------------------------------
 # file                 : functions.php
 # created              : 2005-06-17
 # copyright            : (C) 2005-2006 The ComaCMS-Team
 # email                : comacms@williblau.de
 #----------------------------------------------------------------------
 # This program is free software; you can redistribute it and/or modify
 # it under the terms of the GNU General Public License as published by
 # the Free Software Foundation; either version 2 of the License, or
 # (at your option) any later version.
 #----------------------------------------------------------------------
	/**
	 *
	 */

	
	/**
	 * @return string
	 */
	/*function make_link($Link) {
		
		$Link = encodeUri($Link);
		
		if(eregi("^[a-z0-9\._-]+@+[a-z0-9\._-]+\.+[a-z]{2,4}$", $Link))
			return "mailto:$Link\" class=\"link_email";
		else if(substr($Link, 0, 4) == 'http')
			return "$Link\" class=\"link_extern";
		// TODO: load the title of the page into the link title and set an other css-class if the page does not exists
		return "index.php?page=$Link\" class=\"link_intern";
	}*/
	

	
		
	/*function generateThumb($file, $outputdir, $maxsize= 100) {
	
		list($width, $height) = getimagesize($file);
		
		$newfile = $outputdir . basename($file);
		preg_match("'^(.*)\.(gif|jpe?g|png|bmp)$'i", $file, $ext);
		//$newfile = './data/thumbnails/' . $ext[1] . '.' . $ext[2];
		
		if($width > $maxsize || $height > $maxsize) {
			$newwidth = ($width > $height) ? $maxsize : $width / ($height / $maxsize);
			$newheight = ($height > $width) ? $maxsize : $height / ($width / $maxsize);
			
			$memory_limit = ini_get("memory_limit");
			if(substr($memory_limit, -1) == 'M')
				$memory_limit = substr($memory_limit, 0, -1) * 1048576;
			//
			// mostly all php-binarys for windows are not compiled with --enable-memory-limit
			// and don't suport memory_get_usage() and are able to handle bigger data
			// (it is not bad for us) 
			//
			if(function_exists('memory_get_usage'))
				$free_memory = $memory_limit - memory_get_usage();
			else
				$free_memory = 0;

			$needspace = ($width * $height + $newwidth * $newheight) * 5;
			// check for enough available memory to resize the image
			if($needspace > $free_memory && $free_memory > 0)
				return false;
			
			$newimage = ImageCreateTrueColor($newwidth, $newheight);
			
			switch (strtolower($ext[2])) {
				case 'jpg' :
				case 'jpeg': $source  = imagecreatefromjpeg($file);
					break;
				case 'gif' : $source  = imagecreatefromgif($file);
					break;
				case 'png' : $source  = imagecreatefrompng($file);
					break;
				default    : return false;
			}
			imagecopyresized($newimage, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
			switch (strtolower($ext[2])) {
				case 'jpg' :
				case 'jpeg': imagejpeg($newimage, $newfile ,100);
					break;
				case 'gif' : imagepng($newimage, $newfile . '.png');
					break;
				case 'png' : imagepng($newimage, $newfile);
					break;
			}
			//imagejpeg($newimage, $newfile ,100);
			
			return true;
		}
		else
			return copy($file, $newfile);
	}*/
	
	/**
	 * @return string filename of the thumbnail
	 */
	/*function resizeImageToMaximum($InputFile, $OutputDir, $Maximum) {
		list($originalWidth, $originalHeight) = getimagesize($InputFile);
		$width = ($originalWidth > $originalHeight) ? round($Maximum, 0) : round($originalWidth / ($originalHeight / $Maximum), 0);
		$height = ($originalHeight > $originalWidth) ? round($Maximum, 0) : round($originalHeight / ($originalWidth / $Maximum), 0);
		if($width >= $originalWidth || $height >= $originalHeight)
			return $InputFile;
		$outputFile = (substr($OutputDir, -1) == '/') ? $OutputDir :  $OutputDir . '/';
		$outputFile .= $width . 'x' . $height . '_'. basename($InputFile);
		return resizeImage($InputFile, $outputFile, $width, $height);
	}*/
	
	/**
	 * @return string filename of the thumbnail
	 */
	/*function resizeImageToWidth($InputFile, $OutputDir, $Width) {
		if(!file_exists($InputFile))
			return false;
		list($originalWidth, $originalHeight) = getimagesize($InputFile);
		$height = round($originalHeight / $originalWidth *  $Width, 0);
		$outputFile = (substr($OutputDir, -1) == '/') ? $OutputDir :  $OutputDir . '/';
		$outputFile .= $Width . 'x' . $height . '_'. basename($InputFile);
		return resizeImage($InputFile, $outputFile, $Width, $height);
	}*/
	
	/**
	 * @return string filename of the thumbnail
	 */
	/*function resizeImage($InputFile, $OutputFile, $Width, $Height) {
		
		preg_match("'^(.*)\.(gif|jpe?g|png|bmp)$'i", $InputFile, $ext);
		
		if(file_exists($OutputFile))
			return $OutputFile;
		list($originalWidth, $originalHeight) = getimagesize($InputFile);

		$memory_limit = ini_get("memory_limit");
		if(substr($memory_limit, -1) == 'M')
			$memory_limit = substr($memory_limit, 0, -1) * 1048576;
		//
		 // mostly all php-binarys for windows are not compiled with --enable-memory-limit
		 // and don't suport memory_get_usage() and are able to handle bigger data
		 // (it is not bad for us)
		 //
		if(function_exists('memory_get_usage'))
			$free_memory = $memory_limit - memory_get_usage();
		else
			$free_memory = 0;
		$needspace = ($originalWidth * $originalHeight + $Width * $Height) * 10;
		// check for enough available memory to resize the image
		if($needspace > $free_memory && $free_memory > 0)
			return false;
		
		$newImage = ImageCreateTrueColor($Width, $Height);
			
		switch (strtolower($ext[2])) {
			case 'jpg' :
			case 'jpeg': $source  = imagecreatefromjpeg($InputFile);
				break;
			case 'gif' : $source  = imagecreatefromgif($InputFile);
				break;
			case 'png' : $source  = imagecreatefrompng($InputFile);
				break;
			default    : return false;
		}
		imagecopyresized($newImage, $source, 0, 0, 0, 0, $Width, $Height, $originalWidth, $originalHeight);
		switch (strtolower($ext[2])) {
			case 'jpg' :
			case 'jpeg': imagejpeg($newImage, $OutputFile, 100);
				break;
			case 'gif' : imagepng($newImage, $OutputFile . '.png');
				$OutputFile .= '.png';
				break;
			case 'png' : imagepng($newImage, $OutputFile);
				break;
		}
		return $OutputFile;
	}*/
	
	
?>
