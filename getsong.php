<?php
include_once("databaseconnect.php");
include_once("logger.php");
include_once("sendfile.php");

set_time_limit(0);

if(!isset($_GET["hash"]))
{
	echo "Invalid hash.";
	exit;
}

connectToDatabase();

if(!($row = mysql_fetch_assoc(mysql_query("SELECT file,artist,album,title,sha1 FROM musictags WHERE sha1 = ".nullString($_GET["hash"])))))
{
	exit;
}
logDownload(array($row));
if($_GET["transcode"] == true)
{
	header('Content-Type: audio/mpeg');
	header('Content-Transfer-Encoding: binary');
	$command = 'ffmpeg -i '.escapeshellarg($row["file"]).' -f mp3 -y -ab 160';
	if($row["title"] != "")
	{
		$command .= " -title ".escapeshellarg($row["title"]);
	}
	if($row["artist"] != "")
	{
		$command .= " -author ".escapeshellarg($row["artist"]);
	}
	if($row["album"] != "")
	{
		$command .= " -album ".escapeshellarg($row["album"]);
	}
	$command .= ' /dev/stdout';
	passthru($command);
}
else
{
	switch(getFormat($row["file"]))
	{
		case "mp3":
			$mime = "audio/mpeg";
			break;
		case "aac":
			$mime = "audio/mp4";
			break;
		case "flac":
			$mime = "audio/x-flac";
			break;
		case "wav":
			$mime = "audio/x-wav";
			break;
		case "ogg":
			$mime = "audio/x-ogg";
			break;
		case "wma":
			$mime = "audio/x-ms-wma";
			break;
		default:
			$mime = "application/octet-stream";
			break;
	}
	sendFile($row["file"], true, str_replace(",", "", substr($row["file"], strrpos($row["file"], "/") + 1)), $mime, true);
}
?>
