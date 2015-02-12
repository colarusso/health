<?php
# Include configuration file. 
include (dirname(__FILE__) . '/../config.php');

$stmt_top = $dbh->prepare("select id,name,address from health.services");
$stmt_top->execute(array()); 
  while ($row_top = $stmt_top->fetch()) {
	$serviceID = $row_top['id']; 
	$name = $row_top['name'];
    	$url = $row_top['address'];
	$file = $xml_path.$serviceID.'.xml';
	checkit($serviceID,$file,$url,$name);
	$dbh = new PDO("mysql:host=localhost;dbname=health", "root", "faH57!#2bNn@");
	$stmt_bottom = $dbh->prepare("INSERT INTO health.stat (id,stat) VALUES (?,?)");
	$stmt_bottom->execute(array($serviceID,$up)); 
  }
$dbh = null;
	
function checkit ($serviceID,$file,$url,$name) {

	global $up;

	$dbh = new PDO("mysql:host=localhost;dbname=health", "root", "faH57!#2bNn@");
	$stmt = $dbh->prepare("select stat,timestamp from health.stat where id=? order by timestamp DESC limit 1");
	$stmt->execute(array($serviceID)); 

	#based on http://www.thecave.info/php-ping-script-to-check-remote-server-or-website/
	$up = "0";
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); #allows any certificate. See http://unitstep.net/blog/2009/05/05/using-curl-in-php-to-access-https-ssltls-protected-sites/
	curl_setopt($ch, CURLOPT_TIMEOUT, 5);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$data = curl_exec($ch);
	$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
	if($httpcode>=200 && $httpcode<300){
	  $up = "1";
	}

	while ($row = $stmt->fetch (PDO::FETCH_OBJ)) {
		if ($row->stat == $up) {
			echo "$serviceID: no change\n";
			#uncomment the call below to write xml on every check, not just on change
			#writeRSS($file,$url,$up,$name,$httpcode);
		} else {
			echo "$serviceID: change\n";
			writeRSS($file,$url,$up,$name,$httpcode);
		}
	}
	$dbh = null;
}

function writeRSS ($file,$url,$up,$name,$httpcode) {	
	$contents = file_get_contents($file);
	$fh = fopen($file, "w");
	if (!$fh) {
		echo "Error: ";
		print_r (error_get_last());
	} else {
		$pattern = '/\s{2}<\/channel>\n/i';
		$replacement .= "	<item>\n";
		if ($up == 1) {
			$replacement .= "		<title>$name error(s) cleared.</title>\n";
		} else {
			$replacement .= "		<title>$name error: $httpcode</title>\n";
		}
		$uid = time();
		$replacement .= "		<link>$url/#$uid</link>\n";
		$replacement .= "		<description></description>\n";
		$pubdate = date("Y-m-d\TH:i:s\Z");
		$replacement .= "		<pubDate>$pubdate</pubDate>\n";
		$replacement .= "		<guid>$uid</guid>\n";
		$replacement .= "	</item>\n";	
		$replacement .= "  </channel>\n";	
		if (preg_match($pattern, $contents)) {
			$contents = preg_replace($pattern, $replacement, $contents);
		} else {
			$contents = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
			$contents .= "<rss version=\"2.0\">\n";
			$contents .= "  <channel>\n";
			$contents .= "	<title>$name status</title>\n";
			$contents .= "	<link>$url</link>\n";
			$contents .= "	<description>Has the site's status Changed?</description>\n";
			$contents .= "	<language>en</language>\n";
			$contents .= $replacement;
			$contents .= "</rss>";
		}
	}
	fwrite($fh, $contents);
	fclose($fh);
}
?>
