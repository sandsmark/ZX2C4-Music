<?php
require_once("databaseconnect.php");
setupLogDatabase();
function setupLogDatabase()
{
	mysql_query(	"CREATE TABLE IF NOT EXISTS requestlog (
			id INT NOT NULL AUTO_INCREMENT,
			leaderid INT,
			PRIMARY KEY(id),
			time INT NOT NULL,
			ip VARCHAR(30),
			useragent VARCHAR(255),
			zip BOOL,
			sha1 VARCHAR(64),
			file VARCHAR(255),
			track INT,
			artist VARCHAR(255),
			album VARCHAR(255),
			title VARCHAR(255)
			) CHARACTER SET utf8;"
	);
}
function logDownload($songArray, $zip)
{
	setupLogDatabase();
	if(count($songArray) == 0)
	{
		return;
	}
	$first = -1;
	foreach($songArray as $song)
	{
		mysql_query("INSERT INTO requestlog (leaderid, time, ip, useragent, zip, sha1, file, track, artist, album, title) VALUES (
			".$first.",
			".time().",
			".nullString($_SERVER["REMOTE_ADDR"]).",
			".nullString($_SERVER["HTTP_USER_AGENT"]).",
			".sqlBool($zip).",
			".nullString($song["sha1"]).",
			".nullString($song["file"]).",
			".nullInt($song["track"]).",
			".nullString($song["artist"]).",
			".nullString($song["album"]).",
			".nullString($song["title"]).");");
		if($first == -1)
		{
			$first = mysql_insert_id();
		}
	}
}
?>