TL;DR
-----

This plugin enables a shortcode hook (i.e., [health]) which can be used to display the current and past availibity of pre-defined webservices. It also publishes xml feeds documenting changes in the availibillity of these services. These feeds can be used as triggers for tools like [IFTTT](http://ifttt.com). This means you can get a text/email/phone call when service X becomes unavailible. I use it to keep an eye on webservices I administor.  

This plug-in is licensed under an MIT licence. 

Usage
--------------

Placing the shortcode (e.g., `[health id="1" t="0"]`) on a WordPress post or page will result in the display of either a red, orange, or green dot. You pass the shortcode two arrguments: `id` (an integer id for the webservce) and `t` (an floating point number defining the time over which you are interested in the service). 

1. `id` is defined by your database structure (discused below).
2. `t` can either equal zero, which will direct the plugin to look at the most recent status, or some floating point number, measured in days. The latter will cause the plugin to look back at availabilty for the defined number of days. 

Installation 
-------------

Create a mySQL database named "health". Presumably, you could use the same mySQL server as your Wordpress install.

**SQL:**
```
CREATE database health;
```

Create two tables in the "health" database, one storing information about the services you want to monitor, and one to act as a log of these services' availbility. 

**SQL:**
```
CREATE TABLE `services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  `address` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE `stat` (
  `id` int(11) NOT NULL,
  `stat` int(11) NOT NULL COMMENT '0 = down, 1 = up',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

Add the service or services you want to monitor to the "services" table using the following form: 

**SQL:**
```
INSERT INTO health.services (name,address) VALUES ('GitHub','https://github.com');
```

Hard-code your database credentials into the config.php file found in this plugin's main directory. 

**./confog.php:**
```
# Hard-code your DB's credentials into the line below. 
$dbh = new PDO("mysql:host=localhost;dbname=abtest", "[user]", "[password]");

# Path to xml directory where feeds will be placed. 
# An "xml" file can be found in this plugin's structure already. 
# However, you may want to move it.
$xml_path = dirname(__FILE__) . '/xml/';
```

Make sure the "xml" folder has the permissions that will allow the "./scripts/test_write.php" script to write to it. 

Schedule a task (IIS) or set up a cron job (Apache) to regurarly run "./scripts/test_write.php". I happened to set this up on IIS. So I included a batch file in "./scripts/" that I used to run the script. 

Upload the content of this repo as a folder titled "health" to [yoursite]/wp-content/plugins/ and activate the "Health" plugin as you would any other Wordpress plugin. 

