<?php
/**
 * @package ComaCMS
 * @copyright (C) 2005-2006 The ComaCMS-Team
 */
 #----------------------------------------------------------------------#
 # file 		: functions.php					#
 # created		: 2005-06-17					#
 # copyright		: (C) 2005-2006 The ComaCMS-Team		#
 # email		: comacms@williblau.de				#
 #----------------------------------------------------------------------#
 # This program is free software; you can redistribute it and/or modify	#
 # it under the terms of the GNU General Public License as published by	#
 # the Free Software Foundation; either version 2 of the License, or	#
 # (at your option) any later version.					#
 #----------------------------------------------------------------------#

	/**
	 * @deprecated 27.01.2006
	 * 
	 */
	//FIXME: make it possible to remove this alias
	function db_result($command) {
		global $sqlConnection;//$db_con, $queries_count;
		
		return $sqlConnection->SqlQuery($command);
	}

	function generate_password($length) {
		$abc = array('1', '2', '3', '4', '5', '6', '7', '8', '9', '0', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 'r', 't', 'u', 'v', 'w', 'x', 'y', 'z', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'R', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0');
		$out = '';
		for($i = 0; $i < $length; $i++) {
			$out .=  $abc[rand(0, count($abc) - 1)];
		}
		return $out;
	}

	function sendmail($to, $from, $title, $text) {
		$to = strtolower($to);
		$from = strtolower($from);
		$header="From:$from\n";
		$header .= 'Content-Type: text/html';

		return mail($to, $title, $text, $header);
	}

	function getmicrotime($mic_time) {
		list($usec, $sec) = explode(' ', $mic_time);

		return ((float)$usec + (float)$sec);
	}

	function writelog($text) {
		$handle = fopen ('log.log', 'a');
		fwrite($handle, $text . "\n");
		fclose ($handle);
	}

	function getUserIDByName($name) {
		global $lib;
		return $lib->GetUserIDByName($name);
	}

	function getUserByID($id) {
		global $lib;
		return $lib->GetUserByID($id);
	}
	
	function getGroupByID($id) {
		global $lib;
		return $lib->GetGroupByID($id);
	}

	function replace_smilies($textdata) {
		$sql = "SELECT *
			FROM " . DB_PREFIX . "_smilies";
		$result = db_result($sql);
		// FIXME: problem with replacing if a smilie_text is in a smile_title => <img src="*" alt="*<img */>*" />
		while($smilie = mysql_fetch_object($result)){
			$textdata = str_replace($smilie->smilie_text, "<img src=\"".$smilie->smilie_path."\" alt=\"".$smilie->smilie_title."\"/>", $textdata);
		}
		return $textdata;
	}

	function isEMailAddress($email){
		return eregi("^[a-z0-9\._-]+@+[a-z0-9\._-]+\.+[a-z]{2,4}$", $email);
	}

	function isIcqNumber($icq) {
		return eregi("^[0-9]{3}(\-)?[0-9]{3}(\-)?[0-9]{3}$", $icq);
	}

	function endsWith($string, $search) {
		return $search == substr($string, 0 - (strlen($search)));
	}

	function startsWith($string, $search) {
		return 0 === strpos($string, $search);
	}

	/*function nextDates($count = 5) {
		$sql = "SELECT *
			FROM " . DB_PREFIX . "dates
			WHERE date_date >= " . mktime() . "
			ORDER BY date_date ASC
			LIMIT 0, $count";
		$result = db_result($sql);
		$out = "<table class=\"dates\"><thead>
				<tr>
					<td>Datum</td>
					<td>Ort</td>
					<td>Veranstaltung</td>
					</tr></thead>";
		while($date = mysql_fetch_object($result)) {
			$out .= "<tr>
				<td>" . date("d.m.Y H:i",$date->date_date) . "</td>
					<td>$date->date_place</td>
					<td>$date->date_topic</td>
				</tr>";
		}
		$out .= "</table>";
		return $out;
	}*/

	/*function articlesPreview($count = 5) {
		global $config;
		$sql = "SELECT *
			FROM " . DB_PREFIX . "articles
			ORDER BY article_date DESC
			LIMIT 0, $count";
		$result = db_result($sql);
		$articlesTitle =  $config->Get('articles_title', '');
		if($articlesTitle != '')
			$articlesTitle = '<h3>' . $articlesTitle . '</h3>';
		$out = '</p><div class="articles-block">' . $articlesTitle;
		$imgmax = 100;
		$inlinemenu_folder = $config->Get('thumbnailfolder', 'data/thumbnails/');
		$dateFormat = $config->Get('articles_date_format', 'd.m.Y');
		$dateFormat .= ' ' . $config->Get('articles_time_format', 'H:i:s'); 
		while($data = mysql_fetch_object($result)) {
			$thumb = '';
			$size = '';
			$showAuthor = $config->Get('articles_display_author', 1);
			if($data->article_image != '') {
				$filename = basename($data->article_image);
				if(file_exists($inlinemenu_folder . $imgmax . '_' . $filename)) {
					//$size = getimagesize($inlinemenu_folder . $imgmax . '_' . $filename);
					
					$thumb = '<img class="article_image" title="' . $data->article_title . '" alt="' . $data->article_title . '" src="' . generateUrl($inlinemenu_folder . $imgmax . '_' . $filename) . '" />';
					$size = getimagesize($inlinemenu_folder . $imgmax . '_' . $filename);
					$size = ' style="min-height:' . ($size[1] - (13 * $showAuthor)) . 'px"';
				}
			}
			
			$out .= "\t\t\t<div class=\"article\">
				<div class=\"article-title\">
					<span class=\"article-date\">" . date($dateFormat, $data->article_date) . "</span>
					$data->article_title
				</div><div class=\"article_inside\"$size>
				$thumb" . nl2br($data->article_description) . " <a href=\"article.php?id=$data->article_id\" title=\"Den vollst&auml;ndigen Artikel '$data->article_title' lesen\">mehr...</a></div>\r\n";
					 
			if($showAuthor == 1)	
				$out .= "<div class=\"article-author\">" . getUserByID($data->article_creator) . "</div>\r\n";
			$out .= "
			</div>\r\n";
		}
		$out .= '</div><p>';
		return $out;
	}*/

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
			'.zip' => 'application/zip'
		);
		$ext = strrchr($filename, '.');
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
				$encoded .= preg_replace( "|([\/]{0,1})(.+?)([\/]{0,1})|e", "'\\1' . rawurlencode('\\2') . '\\3'", $urlData['path'] );
			}
			if(isset($urlData['query'])) { // TODO: Try to fast me up!
				$urlData['query']= rawurldecode($urlData['query']);
				$urlData['query'] = str_replace('&amp;amp;', '&', $urlData['query']);
				$urlData['query'] = str_replace('&amp;', '&', $urlData['query']);
				$encoded .= '?' . preg_replace( "|([&=]{0,1})(.+?)([&=]{0,1})|e", "'\\1' . rawurlencode('\\2') . '\\3'", $urlData['query'] );
			} 
			if(isset($urlData['fragment']))
				$encoded .= "#" . rawurlencode(rawurldecode($urlData['fragment']));
			return $encoded;
		}
		else
			return  rawurlencode($Uri);
	}
?>
