<?php
/*
 * Created on 20.04.2009
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 	//$dn = ;
 	//$pass = ;
 
 	$connectid = ldap_connect('earl', '389') or die("Kann nicht verbinden");
 	ldap_set_option($connectid, LDAP_OPT_PROTOCOL_VERSION, 3) or die("Kann nicht auf Version 3 gehen");
 	$binding = ldap_bind($connectid, 'uid=groupware,ou=People,dc=asta,dc=fs,dc=tum,dc=de', 'Wihw8p297)=i6aq') or die("Kann nicht einloggen");
 	$search = ldap_search($connectid, 'ou=Groups,dc=asta,dc=fs,dc=tum,dc=de', "(objectClass=top)"); 
 	$result = ldap_get_entries($connectid, $search);
 	
	for ($index = 0, $max_count = $result['count']; $index < $max_count; $index++) {
		$array_element = $result[ $index ];
		$array_element['dn'] = ldap_explode_dn($array_element['dn'], 1);
		//print_r($array_element['dn']);
		
		$usergroup = false;
		for ($i = 0, $max = $array_element['memberuid']['count']; $i < $max; $i++) {
			if ($array_element['dn']['0'] == $array_element['memberuid']["$i"])
				$usergroup = true;
		}
		if (!$usergroup) {
			print_r($array_element['dn']['0'] . '<br><br>');
			for ($i = 0, $max = $array_element['memberuid']['count']; $i < $max; $i++) {
				print_r($array_element['memberuid']["$i"] . '<br>');
			}
			echo "<br><br><br>";
		}
		
		
		//echo $array_element['dn'] . '<br><br>';
		/*for ($index2 = 0, $max_count2 = $array_element['cn']['count']; $index2 < $max_count2; $index2++) {
			$name = $array_element['cn'][ $index2 ];
			
			echo $name + '<br>';
		}*/
	}
?>
