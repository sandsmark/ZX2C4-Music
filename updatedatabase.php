<?php
require_once("databaseconnect.php");
require_once("databaseauthenticate.php");
require_once("logger.php");

set_time_limit(0);

$failCount = 0;
$removeCount = 0;
$updateCount = 0;
$addCount = 0;
$excludeCount = 0;

setupDatabase();
setupLogDatabase();
deleteBadEntries();
scanDirectory(MUSIC_DIRECTORY);
echo $failCount." songs failed<br>".$removeCount." songs removed<br>".$updateCount." songs updated<br>".$addCount." songs added<br>".$excludeCount." songs excluded";

function isExcluded($file)
{
	global $excludeList;
	global $excludeCount;
	if(!is_array($excludeList) || count($excludeList) == 0)
	{
		return false;
	}
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
			$excludeCount++;
			return true;
		}
	}
	return false;
}

function deleteBadEntries()
{
	global $removeCount;
	$result = pg_query("SELECT file FROM musictags");
	while($row = pg_fetch_assoc($result))
	{
		if(!file_exists($row["file"]) || isExcluded($row["file"]))
		{
			echo "Removed ".$row["file"]."<br>";
			pg_query("DELETE FROM musictags WHERE file = ".nullString($row["file"]));
			$removeCount++;
		}
	}
	pg_free_result($result);
}

function processFile($file)
{
	global $failCount;
	global $addCount;
	global $updateCount;
	
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
	$update = false;
	$result = @pg_query("SELECT sha1,lastmodified FROM musictags WHERE file = ${nullifiedFile}");
	if($result)
	{
		$row = pg_fetch_assoc($result);		
		if($lastModified == $row["lastmodified"])
		{
			return;
		}
		elseif(($sha1 = sha1_file($file)) == $row["sha1"])
		{
			echo "Updated last modified for ".$file."<br>";
			pg_query("UPDATE musictags SET lastmodified = ${lastModified} WHERE file = ${nullifiedFile}");
			$updateCount++;
			return;
		}
		elseif($row)
		{
			$update = true;
		}
		pg_free_result($result);
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
	
	if($update)
	{
		pg_query("UPDATE musictags SET 
			sha1=${sha1},
			lastmodified=${lastModified},
			format=${format},
			artist=${artist},
			album=${album},
			albumartist=${albumArtist},
			title=${title},
			year=${year},
			comment=${comment},
			track=${track},
			disc=${disc},
			disctotal=${discTotal},
			genre=${genre},
			bpm=${bpm},
			composer=${composer},
			compilation=${compilation},
			bitrate=${bitrate},
			samplerate=${sampleRate},
			channels=${channels},
			length=${length} 
			WHERE file=${nullifiedFile};"
		);
		echo "Updated tags for ".$file."<br>";
		$updateCount++;
	}
	else
	{
		pg_query(	"INSERT INTO musictags ( sha1 , file , lastmodified , format , artist , album , 
			albumartist , title , year , comment , track , disc , disctotal , 
			genre , bpm , composer , compilation , bitrate , samplerate , channels , length )
			VALUES (
			${sha1}, ${nullifiedFile}, ${lastModified}, ${format}, ${artist}, ${album}, 
			${albumArtist}, ${title} , ${year}, ${comment}, ${track}, ${disc}, ${discTotal}, 
			${genre}, ${bpm}, ${composer}, ${compilation}, ${bitrate}, ${sampleRate}, ${channels}, ${length}
			);"
		);
		echo "Added ".$file."<br>";
		$addCount++;
	}
	
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
	pg_query(	"CREATE TABLE musictags (
			file VARCHAR NOT NULL ,
			sha1 VARCHAR( 64 ) NOT NULL ,
			lastmodified INT NOT NULL ,
			format VARCHAR NULL ,
			artist VARCHAR NULL ,
			album VARCHAR NULL ,
			albumartist VARCHAR NULL ,
			title VARCHAR NULL ,
			year INT NULL ,
			comment VARCHAR NULL ,
			track INT NULL ,
			disc INT NULL ,
			disctotal INT NULL ,
			genre VARCHAR NULL ,
			bpm INT NULL ,
			composer VARCHAR NULL ,
			compilation BOOL NULL ,
			bitrate INT NULL ,
			samplerate INT NULL ,
			channels INT NULL ,
			length INT NULL ,
			PRIMARY KEY ( file )
			);"
	);
	echo "Connected to database<br>";
}
?>
