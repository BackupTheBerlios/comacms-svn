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
	 * @deprecated 27.01.2006
	 * 
	 */
	//FIXME: make it possible to remove this alias
	/*function sdbs_result($command) {
		global $sqlConnection;//$db_con, $queries_count;
		
		return $sqlConnection->SqlQuery($command);
	}*/

	function generate_password($length) {
		$abc = array('1', '2', '3', '4', '5', '6', '7', '8', '9', '0', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 'r', 't', 'u', 'v', 'w', 'x', 'y', 'z', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'R', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0');
		$out = '';
		for($i = 0; $i < $length; $i++) {
			$out .=  $abc[rand(0, count($abc) - 1)];
		}
		return $out;
	}
	
	/**
	  * @param string To The email will be sended to this email address
	  * @param string From The email shows this email address as the sender
	  * @param string Title This is the Title of the Mail
	  * @param string Content This is the content of the email 
	  */
	function sendmail($To, $From, $Title, $Content) {
		
		$header= "From:{$From}\n";
		$header .= 'Content-Type: text/plain; charset="utf-8"';

		return mail($To, $Title, $Content, $header);
	}

	function getmicrotime($mic_time) {
		list($usec, $sec) = explode(' ', $mic_time);

		return ((float)$usec + (float)$sec);
	}

	function isEMailAddress($email){
		return eregi("^[a-z0-9\._-]+@+[a-z0-9\._-]+\.+[a-z]{2,4}$", $email);
	}

	function isIcqNumber($icq) {
		return eregi("^[0-9]{3}(\-)?[0-9]{3}(\-)?[0-9]{3}$", $icq);
	}

	/**
	 * GetPostOrGet
	 *
	 * This function make it easier to catch a variable which is send by GET or POST
	 * if the variable dosen't exist this function returns null
	 * @return mixed
	 * @var Name $Name is the name of the variable wthich value is to return
	 */
	function GetPostOrGet($Name) {
		global $_POST, $_GET;

		$value = null; // no GET- or POST-variable available
		if(isset($_POST[$Name])) // exists an POST-value?
			$value = $_POST[$Name];
		else if(isset($_GET[$Name])) // exists an GET-value?
			$value = $_GET[$Name];
		$value = MakeSecure($value);
		return $value;
	}
	
	/**
	 * @return mixed
	 */
	function MakeSecure($handle) {
		if($handle !== null) {
			if(is_array($handle)) {
				$handle = array_map('MakeSecure', $handle);
			}
			else {
				if(get_magic_quotes_gpc())
					$handle = stripslashes($handle);
				if(!is_numeric($handle))
					$handle = addslashes($handle);
			}
		}
		return $handle;
	}
	
	function GetMimeContentType ($filename) {
		$mime = array(
			'.ai' => 'application/postscript',
			'.aif' => 'audio/x-aiff',
			'.aifc' => 'audio/x-aiff',
			'.aiff' => 'audio/x-aiff',
			'.bmp' => 'image/bmp',
			'.asc' => 'text/plain',
			'.au' => 'audio/basic',
			'.avi' => 'video/x-msvideo',
			'.bcpio' => 'application/x-bcpio',
			'.bin' => 'application/octet-stream',
			'.c' => 'text/plain',
			'.cc' => 'text/plain',
			'.ccad' => 'application/clariscad',
			'.cdf' => 'application/x-netcdf',
			'.class' => 'application/octet-stream',
			'.cpio' => 'application/x-cpio',
			'.cpt' => 'application/mac-compactpro',
			'.csh' => 'application/x-csh',
			'.css' => 'text/css',
			'.dcr' => 'application/x-director',
			'.dir' => 'application/x-director',
			'.dms' => 'application/octet-stream',
			'.doc' => 'application/msword',
			'.drw' => 'application/drafting',
			'.dvi' => 'application/x-dvi',
			'.dwg' => 'application/acad',
			'.dxf' => 'application/dxf',
			'.dxr' => 'application/x-director',
			'.eps' => 'application/postscript',
			'.etx' => 'text/x-setext',
			'.exe' => 'application/octet-stream',
			'.ez' => 'application/andrew-inset',
			'.f' => 'text/plain',
			'.f90' => 'text/plain',
			'.fli' => 'video/x-fli',
			'.gif' => 'image/gif',
			'.gtar' => 'application/x-gtar',
			'.gz' => 'application/x-gzip',
			'.h' => 'text/plain',
			'.hdf' => 'application/x-hdf',
			'.hh' => 'text/plain',
			'.hqx' => 'application/mac-binhex40',
			'.htm' => 'text/html',
			'.html' => 'text/html',
			'.ice' => 'x-conference/x-cooltalk',
			'.ief' => 'image/ief',
			'.iges' => 'model/iges',
			'.igs' => 'model/iges',
			'.ips' => 'application/x-ipscript',
			'.ipx' => 'application/x-ipix',
			'.jpe' => 'image/jpeg',
			'.jpeg' => 'image/jpeg',
			'.jpg' => 'image/jpeg',
			'.js' => 'application/x-javascript',
			'.kar' => 'audio/midi',
			'.latex' => 'application/x-latex',
			'.lha' => 'application/octet-stream',
			'.lsp' => 'application/x-lisp',
			'.lzh' => 'application/octet-stream',
			'.m' => 'text/plain',
			'.man' => 'application/x-troff-man',
			'.me' => 'application/x-troff-me',
			'.mesh' => 'model/mesh',
			'.mid' => 'audio/midi',
			'.midi' => 'audio/midi',
			'.mif' => 'application/vnd.mif',
			'.mime' => 'www/mime',
			'.mov' => 'video/quicktime',
			'.movie' => 'video/x-sgi-movie',
			'.mp2' => 'audio/mpeg',
			'.mp3' => 'audio/mpeg',
			'.mpe' => 'video/mpeg',
			'.mpeg' => 'video/mpeg',
			'.mpg' => 'video/mpeg',
			'.mpga' => 'audio/mpeg',
			'.ms' => 'application/x-troff-ms',
			'.msh' => 'model/mesh',
			'.nc' => 'application/x-netcdf',
			'.oda' => 'application/oda',
			'.pbm' => 'image/x-portable-bitmap',
			'.pdb' => 'chemical/x-pdb',
			'.pdf' => 'application/pdf',
			'.pgm' => 'image/x-portable-graymap',
			'.pgn' => 'application/x-chess-pgn',
			'.png' => 'image/png',
			'.pnm' => 'image/x-portable-anymap',
			'.pot' => 'application/mspowerpoint',
			'.ppm' => 'image/x-portable-pixmap',
			'.pps' => 'application/mspowerpoint',
			'.ppt' => 'application/mspowerpoint',
			'.ppz' => 'application/mspowerpoint',
			'.pre' => 'application/x-freelance',
			'.prt' => 'application/pro_eng',
			'.ps' => 'application/postscript',
			'.qt' => 'video/quicktime',
			'.ra' => 'audio/x-realaudio',
			'.ram' => 'audio/x-pn-realaudio',
			'.ras' => 'image/cmu-raster',
			'.rgb' => 'image/x-rgb',
			'.rm' => 'audio/x-pn-realaudio',
			'.roff' => 'application/x-troff',
			'.rpm' => 'audio/x-pn-realaudio-plugin',
			'.rtf' => 'text/rtf',
			'.rtx' => 'text/richtext',
			'.scm' => 'application/x-lotusscreencam',
			'.set' => 'application/set',
			'.sgm' => 'text/sgml',
			'.sgml' => 'text/sgml',
			'.sh' => 'application/x-sh',
			'.shar' => 'application/x-shar',
			'.silo' => 'model/mesh',
			'.sit' => 'application/x-stuffit',
			'.skd' => 'application/x-koan',
			'.skm' => 'application/x-koan',
			'.skp' => 'application/x-koan',
			'.skt' => 'application/x-koan',
			'.smi' => 'application/smil',
			'.smil' => 'application/smil',
			'.snd' => 'audio/basic',
			'.sol' => 'application/solids',
			'.spl' => 'application/x-futuresplash',
			'.src' => 'application/x-wais-source',
			'.step' => 'application/STEP',
			'.stl' => 'application/SLA',
			'.stp' => 'application/STEP',
			'.sv4cpio' => 'application/x-sv4cpio',
			'.sv4crc' => 'application/x-sv4crc',
			'.svg' => 'image/svg+xml',
			'.swf' => 'application/x-shockwave-flash',
			'.t' => 'application/x-troff',
			'.tar' => 'application/x-tar',
			'.tcl' => 'application/x-tcl',
			'.tex' => 'application/x-tex',
			'.texi' => 'application/x-texinfo',
			'.texinfo' => 'application/x-texinfo',
			'.tif' => 'image/tiff',
			'.tiff' => 'image/tiff',
			'.tr' => 'application/x-troff',
			'.tsi' => 'audio/TSP-audio',
			'.tsp' => 'application/dsptype',
			'.tsv' => 'text/tab-separated-values',
			'.txt' => 'text/plain',
			'.unv' => 'application/i-deas',
			'.ustar' => 'application/x-ustar',
			'.vcd' => 'application/x-cdlink',
			'.vda' => 'application/vda',
			'.viv' => 'video/vnd.vivo',
			'.vivo' => 'video/vnd.vivo',
			'.vrml' => 'model/vrml',
			'.wav' => 'audio/x-wav',
			'.wrl' => 'model/vrml',
			'.xbm' => 'image/x-xbitmap',
			'.xlc' => 'application/vnd.ms-excel',
			'.xll' => 'application/vnd.ms-excel',
			'.xlm' => 'application/vnd.ms-excel',
			'.xls' => 'application/vnd.ms-excel',
			'.xlw' => 'application/vnd.ms-excel',
			'.xml' => 'text/xml',
			'.xpm' => 'image/x-xpixmap',
			'.xwd' => 'image/x-xwindowdump',
			'.xyz' => 'chemical/x-pdb',
			'.zip' => 'application/zip',
			'.odt' => 'application/vnd.oasis.opendocument.text',
			'.ods' => 'application/vnd.oasis.opendocument.spreadsheet',
			'.odp' => 'application/vnd.oasis.opendocument.presentation',
			'.odg' => 'application/vnd.oasis.opendocument.graphics',
			'.odc' => 'application/vnd.oasis.opendocument.chart',
			'.odf' => 'application/vnd.oasis.opendocument.formula',
			'.odi' => 'application/vnd.oasis.opendocument.image',
			'.odm' => 'application/vnd.oasis.opendocument.text-master',
			'.ott' => 'application/vnd.oasis.opendocument.text-template',
			'.ots' => 'application/vnd.oasis.opendocument.spreadsheet-template',
			'.otp' => 'application/vnd.oasis.opendocument.presentation-template',
			'.otg' => 'application/vnd.oasis.opendocument.graphics-template'
		);
		$ext = strtolower(strrchr($filename, '.'));
		if(array_key_exists($ext, $mime))
			return $mime[$ext];
		return 'application/unknown';
	}
	
	/**
	 * Encodes a URI that it is ready for XHTML-output
	 * @param string Uri
	 * @return string
	 */
	function encodeUri($Uri) {
		// convert the URI into different parts
		$urlData = parse_url($Uri);
		if(isset($urlData['scheme']) && isset($urlData['host'])) {
			// FIXME: here is a bug with non ASCII characters, it generates no-standard-URIs
			$encoded = $urlData['scheme'] . '://' . rawurlencode($urlData['host']);
			if(isset($urlData['path'])) { // TODO: Try to fast me up!
				$urlData['path']= rawurldecode($urlData['path']);
				$encoded .= preg_replace( "|([\/]{0,1})(.+?)([\/]{1})|e", "'\\1' . rawurlencode('\\2') . '\\3'", $urlData['path'] );
			}
			if(isset($urlData['query'])) { // TODO: Try to fast me up!
				$urlData['query']= rawurldecode($urlData['query']);
				$urlData['query'] = str_replace('&amp;amp;', '&', $urlData['query']);
				$urlData['query'] = str_replace('&amp;', '&', $urlData['query']);
				$encoded .= '?' . str_replace('&', '&amp;', preg_replace( "|([&=]{0,1})(.+?)([&=]{0,1})|e", "'\\1' . rawurlencode('\\2') . '\\3'", $urlData['query'] ));
			} 
			if(isset($urlData['fragment']))
				$encoded .= "#" . rawurlencode(rawurldecode($urlData['fragment']));
			return $encoded;
		}
		else
			return  rawurlencode($Uri);
	}
	
	function generateUrl($string) {
		$string = preg_replace("~^\ *(.+?)\ *$~", "$1", $string);
		return str_replace(" ", "%20", $string);
	}
	
	function kbormb($bytes, $space = true) {
		$space = ($space) ? ' ' : '&nbsp;';
		if($bytes < 1024)
			return $bytes . $space .'B';
		elseif($bytes < 1048576)
			return round($bytes/1024, 1) . $space . 'KiB';
		else
			return round($bytes/1048576, 1) . $space . 'MiB';
	}
	
?>
