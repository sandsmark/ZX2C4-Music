<?php
require_once("authenticate.php");
function connectToDatabase()
{
	mysql_connect(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD);
	mysql_select_db(DATABASE_NAME);
}
function nullString($string)
{
	if($string == "")
	{
		return "NULL";
	}
	return "'".mysql_real_escape_string($string)."'";
}
function nullInt($int)
{
	if($int <= 0)
	{
		return "NULL";
	}
	return $int;
}
function sqlBool($bool)
{
	return $bool ? "1" : "0";
}
function getFormat($file)
{
	switch(getFileExtension($file))
	{
		case "aac":
		case "m4a":
		case "m4b":
		case "m4p":
		case "mp4":
			return "aac";
		case "mp3":
			return "mp3";
		case "wav":
			return "wav";
		case "ogg":
			return "ogg";
		case "wma":
		case "asf":
			return "wma";
		case "flac":
			return "flac";
		default:
			return false;
	}
}
function getFileExtension($file)
{
	return strtolower(substr($file, strrpos($file, ".") + 1));
}
function getPrettyFilename($row)
{
	$filename = "";
	if(strlen($row["artist"]) > 0)
	{
		$filename .= $row["artist"];
	}
	else
	{
		$filename .= "(No Artist)";
	}
	if(strlen($row["album"]) > 0)
	{
		$filename .= " - ".$row["album"];
	}
	if((strlen($row["track"]) > 0 && intval($row["track"]) > 0) || strlen($row["title"]) > 0)
	{
		$filename .= " - ";
	}
	if(strlen($row["track"]) > 0 && intval($row["track"]) > 0)
	{
		if(intval($row["track"]) < 10)
		{
			$filename .= "0";
		}
		$filename .= $row["track"];
	}
	if(strlen($row["track"]) > 0 && intval($row["track"]) > 0 && strlen($row["title"]) > 0)
	{
		$filename .= " ";
	}
	if(strlen($row["title"]) > 0)
	{
		$filename .= $row["title"];
	}
	$filename .= ".".getFileExtension($row["file"]);
	return $filename;
}
?>
