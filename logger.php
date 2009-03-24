<?php
require_once("databaseconnect.php");
function setupLogDatabase()
{
	mysql_query(	"CREATE TABLE IF NOT EXISTS requestlog (
			id INT NOT NULL AUTO_INCREMENT,
			PRIMARY KEY(id),
			leaderid INT,
			time INT NOT NULL,
			ip VARCHAR(30),
			useragent VARCHAR(255),
			zip BOOL,
			sha1 VARCHAR(64),
			artist VARCHAR(255),
			album VARCHAR(255),
			title VARCHAR(255)
			) CHARACTER SET utf8;"
	);
}
function logDownload($songArray, $zip)
{
	if(count($songArray) == 0)
	{
		return;
	}
	$first = -1;
	foreach($songArray as $song)
	{
		mysql_query("INSERT INTO requestlog (leaderid, time, ip, useragent, zip, sha1, artist, album, title) VALUES (
			".$first.",
			".time().",
			".nullString($_SERVER["REMOTE_ADDR"]).",
			".nullString($_SERVER["HTTP_USER_AGENT"]).",
			".sqlBool($zip).",
			".nullString($song["sha1"]).",
			".nullString($song["artist"]).",
			".nullString($song["album"]).",
			".nullString($song["title"]).");
		");
		if($first == -1)
		{
			$first = mysql_insert_id();
		}
	}
}
?>