<?php
require_once("databaseconnect.php");
require_once("logger.php");

set_time_limit(0);

if(!isset($_GET["hash"]))
{
	echo "Invalid hash.";
	exit;
}

connectToDatabase();

if(!($row = mysql_fetch_assoc(mysql_query("SELECT file,track,artist,album,title,sha1 FROM musictags WHERE sha1 = ".nullString($_GET["hash"])))))
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
	$handle = @fopen($row["file"], 'rb');
	if($handle)
	{
		$filelength = @filesize($row["file"]);
		$length = $filelength;
		$posfrom = 0;
		$posto = $length - 1;
		if(isset($_SERVER['HTTP_RANGE']))
		{
			$data = explode('=',$_SERVER['HTTP_RANGE']);
			$ppos = explode('-', trim($data[1]));
			$posfrom = (int)trim($ppos[0]);
			if(trim($ppos[1]) != "")
			{
				$posto = (int)trim($ppos[1]);
			}
			$length = $posto - $posfrom + 1;
			@fseek($handle, $posfrom, SEEK_SET);
			header('HTTP/1.1 206 Partial Content', true);
			header('Content-Range: bytes '.$posfrom.'-'.$posto.'/'.$filelength);
		}
		header('Accept-Ranges: bytes');
		header('Content-Disposition: inline; filename="'.getPrettyFilename($row).'"');
		header('Content-Type: '.$mime);
		header('Content-Transfer-Encoding: binary');
		header('Pragma: public');
		header('Content-Length: '.$length);
		while(!feof($handle) && !connection_aborted() && $length > 0) 
		{	
			echo @fread($handle, min(16384, $length));
			$length -= 16384;
			ob_flush();	
			flush();
		}
		@fclose($handle);
	}
}
?>
