<?php
require_once("databaseconnect.php");

$dbpassword = isset($_POST["dbpassword"]) ? $_POST["dbpassword"] : $_GET["dbpassword"];
if($dbpassword != DATABASE_PASSWORD)
{
    $databaseLogin = true;
    header("HTTP/1.1 403 Incorrect Username and/or Password");
    require_once("login.php");
    exit;
}

set_time_limit(0);

$failCount = 0;
$removeCount = 0;
$updateCount = 0;
$addCount = 0;

setupDatabase();
deleteBadEntries();
scanDirectory(MUSIC_DIRECTORY);
echo $failCount." songs failed<br>".$removeCount." songs removed<br>".$updateCount." songs updated<br>".$addCount." songs added";

function isExcluded($file)
{
	global $excludeList;
	foreach($excludeList as $excludedPath)
	{
		$excludedPath = joinPaths(MUSIC_DIRECTORY, $excludedPath);
		$len = strlen($excludedPath);
		if(!$len)
		{
			continue;
		}
		if(!strncmp($excludedPath, $file, $len))
		{
			echo "Excluded ".$file."<br>";
			return true;
		}
	}
	return false;
}

function deleteBadEntries()
{
	$result = mysql_query("SELECT file FROM musictags");
	while($row = mysql_fetch_assoc($result))
	{
		if(!file_exists($row["file"]) || isExcluded($row["file"]))
		{
			removeEntry($row["file"]);
		}
	}
	mysql_free_result($result);
}

function removeEntry($file)
{
	global $removeCount;
	echo "Removed ".$file."<br>";
	mysql_query("DELETE FROM musictags WHERE file = ".nullString($file));
	$removeCount++;
}

function processFile($file)
{
	global $failCount;
	global $addCount;
	global $updateCount;
	global $removeCount;
	
	ob_flush();
	flush();
	
	$format = getFormat($file);
	if(!$format)
	{
		echo "Cannot read ".$file."<br>";
		$failCount++;
		return;
	}
	
	$nullifiedFile = nullString($file);
	$lastModified = filemtime($file);
	$result = @mysql_query("SELECT sha1,lastmodified FROM musictags WHERE file = ${nullifiedFile}");
	if($result)
	{
		$row = mysql_fetch_assoc($result);		
		if($lastModified == $row["lastmodified"])
		{
			return;
		}
		elseif(($sha1 = sha1_file($file)) == $row["sha1"])
		{
			echo "Updated last modified for ".$file."<br>";
			mysql_query("UPDATE musictags SET lastmodified = ${lastModified} WHERE file = ${nullifiedFile}");
			$updateCount++;
			return;
		}
		elseif($row)
		{
			removeEntry($file);
			$removeCount--;
			$addCount--;
			$updateCount++;
		}
		mysql_free_result($result);
	}
	
	$tags = getTags($file);
	if(!isset($sha1))
	{
		$sha1 = sha1_file($file);
	}
	$sha1 = nullString($sha1);
	$lastModified = nullInt($lastModified);
	$format = nullString($format);
	$artist = nullString($tags["artist"]);
	$album = nullString($tags["album"]);
	if(trim($tags["title"]) == "")
	{
		$tags["title"] = basename($file);
	}
	$title = nullString($tags["title"]);
	$year = nullInt((int)$tags["year"]);
	$comment = nullString($tags["comment"]);
	$track = nullInt((int)$tags["track"]);
	$discString = $tags["disc"];
	$discString = explode("/", $discString);
	$disc = nullInt((int)$discString[0]);
	$discTotal = nullInt((int)$discString[1]);
	$genre = nullString($tags["genre"]);
	$bpm = nullInt((int)$tags["bpm"]);
	$composer = nullString($tags["composer"]);
	$albumArtist = nullString($tags["albumArtist"]);
	$compilation = sqlBool((bool)($tags["compilation"] == "1" || $artist == "Various Artists"));
	$bitrate = nullInt((int)$tags["bitrate"]);
	$sampleRate = nullInt((int)$tags["sample rate"]);
	$channels = nullInt((int)$tags["channels"]);
	$length = nullInt((int)$tags["length"]);

	mysql_query(	"INSERT INTO `musictags` ( `sha1` , `file` , `lastmodified` , `format` , `artist` , `album` , 
			`albumartist` , `title` , `year` , `comment` , `track` , `disc` , `disctotal` , 
			`genre` , `bpm` , `composer` , `compilation` , `bitrate` , `samplerate` , `channels` , `length` )
			VALUES (
			${sha1}, ${nullifiedFile}, ${lastModified}, ${format}, ${artist}, ${album}, 
			${albumArtist}, ${title} , ${year}, ${comment}, ${track}, ${disc}, ${discTotal}, 
			${genre}, ${bpm}, ${composer}, ${compilation}, ${bitrate}, ${sampleRate}, ${channels}, ${length}
			);"
	);
	
	echo "Added ".$file."<br>";
	$addCount++;
}

function getTags($file)
{
	exec("./tagreader ".escapeshellarg($file), $outputLines);
	$tags = array();
	foreach($outputLines as $line)
	{
		$split = explode("|", $line, 2);
		if(count($split) == 2)
		{
			$tags[$split[0]] = $split[1];
		}
	}
	return $tags;
}

function scanDirectory($directory)
{
	if($handle = @opendir($directory))
	{
		while(($file = @readdir($handle)) !== false)
		{
			if($file == ".." || $file == ".")
			{
				continue;
			}
			$joined = joinPaths($directory, $file);
			if(is_dir($joined))
			{
				scanDirectory($joined);
			}
			elseif(!isExcluded($joined))
			{
				processFile($joined);
			}
		}
	}
}

function joinPaths()
{
	$joinedString = func_get_arg(0);
	for($i = 1; $i < func_num_args(); $i++)
	{
		if($joinedString[strlen($joinedString) - 1] == '/')
		{
			$joinedString = substr($joinedString, 0, -1);
		}
		$nextString = func_get_arg($i);
		if($nextString[0] == '/')
		{
			$nextString = substr($nextString, 1);
		}
		$joinedString .= '/'.$nextString;
	}
	return $joinedString;
}

function setupDatabase()
{
	connectToDatabase();
	mysql_query(	"CREATE TABLE IF NOT EXISTS `musictags` (
			`file` VARCHAR( 255 ) NOT NULL ,
			`sha1` VARCHAR( 64 ) NOT NULL ,
			`lastmodified` INT NOT NULL ,
			`format` VARCHAR( 255 ) NULL ,
			`artist` VARCHAR( 255 ) NULL ,
			`album` VARCHAR( 255 ) NULL ,
			`albumartist` VARCHAR( 255 ) NULL ,
			`title` VARCHAR( 255 ) NULL ,
			`year` INT NULL ,
			`comment` VARCHAR( 255 ) NULL ,
			`track` INT NULL ,
			`disc` INT NULL ,
			`disctotal` INT NULL ,
			`genre` VARCHAR( 255 ) NULL ,
			`bpm` INT NULL ,
			`composer` VARCHAR( 255 ) NULL ,
			`compilation` BOOL NULL ,
			`bitrate` INT NULL ,
			`samplerate` INT NULL ,
			`channels` INT NULL ,
			`length` INT NULL ,
			PRIMARY KEY ( `file` )
			) ENGINE = MYISAM CHARACTER SET utf8 COMMENT = 'music tag table';"
	);
	echo "Connected to database<br>";
	
}
?>
