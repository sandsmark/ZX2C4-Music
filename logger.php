<?php
require_once("databaseconnect.php");
function setupLogDatabase()
{
	pg_query(	"CREATE TABLE requestlog (
			id SERIAL,
			PRIMARY KEY(id),
			leaderid INT,
			time INT NOT NULL,
			ip VARCHAR(30),
			useragent VARCHAR,
			zip BOOL,
			sha1 VARCHAR(64),
			artist VARCHAR,
			album VARCHAR,
			title VARCHAR
			);"
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
		$result = pg_query("INSERT INTO requestlog (leaderid, time, ip, useragent, zip, sha1, artist, album, title) VALUES (
			".$first.",
			".time().",
			".nullString($_SERVER["REMOTE_ADDR"]).",
			".nullString($_SERVER["HTTP_USER_AGENT"]).",
			".sqlBool($zip).",
			".nullString($song["sha1"]).",
			".nullString($song["artist"]).",
			".nullString($song["album"]).",
			".nullString($song["title"]).")
		 RETURNING id;");
		if($first == -1)
		{
            $row = pg_fetch_row($result);
			$first = $row[0];
		}
	}
}
?>
