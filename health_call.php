<?php
/*
  Plugin Name: Health
  Description: A WordPress plugin to perform simple checks on a web service's "health," where availability without errors is a proxy for good health.
  Author: David Colarusso
  Author URI: http://www.davidcolarusso.com
 */
 
 /**
 * Check on availability of IP as proxy for health
 * Shortcode call: 
 * [health]
 */
function service_health($atts) {

	# Include configuration file. 
	include (dirname(__FILE__) . '/config.php');

	extract(shortcode_atts(array(
      'id' => '',
	  't' => ''
	), $atts));
	$up_count = 0;
	$count = 0;

	if ($t == 0) {
		$stmt = $dbh->prepare("select stat,timestamp from health.stat where id=? order by timestamp DESC limit 1");
		$stmt->execute(array($id));
	} else {
		$stmt = $dbh->prepare("select stat,timestamp from health.stat where id=? and unix_timestamp(now())-unix_timestamp(timestamp) <= ?*24*60*60");
		$stmt->execute(array($id,$t));
	}
	while ($row = $stmt->fetch (PDO::FETCH_OBJ)) {
		if ($row->stat == 1) {
			$up_count++;
		}
		$count++;
    }
	$dbh = null;

	if ( ($up_count/$count) == 1 ) {
		$ans = 1;
	} elseif (($up_count/$count) < 1 and ($up_count/$count) > 0) {
		$ans = 2;
	} else {
		$ans = 0;	
	}
	
	if ($ans == 1) {
		return "<div style='float: left; background: #00ff00; width: 10px; height: 10px; border-radius: 50%; margin: 7px 4px 0 0;'></div>";
	} elseif ($ans == 0) {
		return "<div style='float: left; background: #ff0000; width: 10px; height: 10px; border-radius: 50%; margin: 7px 4px 0 0;'></div>";
	} else {
		return "<div style='float: left; background: orange; width: 10px; height: 10px; border-radius: 50%; margin: 7px 4px 0 0;'></div>";
	}

}
add_shortcode('health', 'service_health');

// allows shortcodes to be used in sidebars
add_filter('widget_text', 'do_shortcode');

?>