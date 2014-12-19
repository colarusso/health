<?php
# Hard-code your DB's credentials into the line below. 
#$dbh = new PDO("mysql:host=localhost;dbname=health", "[user]", "[password]");
$dbh = new PDO("mysql:host=localhost;dbname=health", "root", "faH57!#2bNn@");

# Path to xml directory where feeds will be placed. 
# An "xml" file can be found in this plugin's structure already. 
# However, you may want to move it.
$xml_path = dirname(__FILE__) . '/xml/';
# iis make sure the xml directory can be edited by the php user. iss_iuser for command line/task scheduler, user for web
# apache, same deall but chmod 755 or some such
?>