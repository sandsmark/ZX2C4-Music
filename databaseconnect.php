<?php
include_once("authenticate.php");
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
?>
