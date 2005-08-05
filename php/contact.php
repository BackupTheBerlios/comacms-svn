<?php
/*****************************************************************************
 *
 *  file		: contact.php
 *  created		: 2005-06-18
 *  copyright		: (C) 2005 The Comasy-Team
 *  email		: comasy@williblau.de
 *
 *****************************************************************************/

/*****************************************************************************
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *****************************************************************************/
	function contact_formular() {
		include("functions.php");
		global $_site, $contact_name, $contact_mail, $contact_icq, $contact_text, $contact_homepage, $REMOTE_ADDR, $input;
		$error = "";

		if($input == "true") {
			if($contact_name == "")
				$error .= "<li class=\"error\">Es wurde kein Name angegeben.</li>\n";
			if($contact_text == "")
				$error .= "<li class=\"error\">Es wurde kein Nachrichtentext eingegeben.</li>\n";
			if($contact_mail != "") {
				if(!eregi("^[a-z0-9\._-]+@+[a-z0-9\._-]+\.+[a-z]{2,4}$", $contact_mail))
					$error .= "<li class=\"error\">Die Email-Adresse ist ungültig.</li>\n";	 
			}
			if($contact_icq != "") {
				if(!eregi("^[0-9]{3}(\-)?[0-9]{3}(\-)?[0-9]{3}$", $contact_icq))
					$error .= "<li class=\"error\">Die Icq-Nummer ist ungültig.</li>\n";
				else
				 	$gb_icq = str_replace("-", "", $contact_icq);
			}
			if($contact_homepage == 'http://')
				$contact_homepage = '';
		}

		if($error == "" && $input == "true"){
		$mailtext = "<html>
  <body>
    <h2>Kontaktmail</h2>
    <table>
      <tr>
        <td>Name:</td>
        <td>$contact_name</td>
      </tr>
      <tr>
        <td>Mail:</td>
        <td>$contact_mail</td>
      </tr>
      <tr>
        <td>ICQ:</td>
        <td>$contact_icq</td>
      </tr>
      <tr>
        <td>Homepage:</td>
        <td>".$contact_homepage."</td>
      </tr>
      <tr>
        <td>Text:</td>
        <td>".nl2br($contact_text)."</td>
      </tr>
      <tr>
        <td>Datum:</td>
        <td>".date("d.m.Y H:i:s",mktime())."</td>
      </tr>
      <tr>
        <td>IP:</td><td>".$REMOTE_ADDR."</td>
      </tr>
      <tr>
        <td>Host:</td>
        <td>".gethostbyaddr($REMOTE_ADDR)."</td>
      </tr>
    </table>
  </body>
</html>";
			//
			// TODO: THIS PART MUST BE CONFIGURABLE!!!
			// sendmail("Sebastian W.<willi@williblau.de>",$contact_name."<".$contact_mail.">","Kontakt von ".$contact_name,$mailtext);
			//
			$contact_name = "";
			$contact_icq = "";
			$contact_homepage = "http://";
			$contact_mail = "";
			$contact_text = "";
		}
		if($contact_homepage == "")
			$contact_homepage ="http://";
		if($error != "")
			$error = "Folgende Fehler sind aufgetreten:\n<ul>".$error."</ul>";
		$text = "<div class=\"gbook\">
  <div class=\"error\">$error</div>
  <form method=\"post\" action=\"index.php?site=".$_site."\">
    <input type=\"hidden\" name=\"input\" value=\"true\" />
    <table class=\"gbook\">
      <tr>
        <td><label>Name:</label></td>
        <td><input type=\"text\" name=\"contact_name\" value=\"".$contact_name."\" /></td>
      </tr>
      <tr>
        <td><label>Email:</label></td>
        <td><input type=\"text\" name=\"contact_mail\" value=\"".$contact_mail."\" /></td>
      </tr>
      <tr>
        <td><label>ICQ:</label></td>
        <td><input type=\"text\" name=\"contact_icq\" value=\"".$contact_icq."\" /></td>
      </tr>
      <tr>
        <td><label>Homepage:</label></td>
        <td><input type=\"text\" name=\"contact_homepage\" value=\"".$contact_homepage."\" /></td>
      </tr>
      <tr>
        <td><label>Nachricht:</label></td>
        <td><textarea name=\"contact_text\">$contact_text</textarea></td>
      </tr>
      <tr>
        <td><input type=\"reset\" value=\"Zurücksetzen\" class=\"button\" /></td>
        <td><input class=\"button\" type=\"submit\" value=\"Absenden\" /></td>
      </tr>
    </table>
  </form>
</div>";

		return $text;
	}


?>