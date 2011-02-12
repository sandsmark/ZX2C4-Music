<?php
require_once("databaseconnect.php");

if(!isset($_GET["language"]))
{
	exit;
}

set_time_limit(0);

if($_GET["query"] != "")
{
	$words = explode(" ", $_GET["query"]);
	$conditions = "WHERE ";
	for($i = 0; $i < count($words); $i++)
	{
		$word = mysql_real_escape_string($words[$i]);
		if($_GET["querytype"] == "all" || $_GET["querytype"] == "")
		{
			$conditions .= "(artist LIKE '%${word}%' OR title LIKE '%${word}%' OR album LIKE '%${word}%' OR sha1='${word}')";
		}
		elseif($_GET["querytype"] == "artist")
		{
			$conditions .= "(artist LIKE '%${word}%')";
		}
		elseif($_GET["querytype"] == "album")
		{
			$conditions .= "(album LIKE '%${word}%')";
		}
		elseif($_GET["querytype"] == "title")
		{
			$conditions .= "(title LIKE '%${word}%')";
		}
		elseif($_GET["querytype"] == "sha1")
		{
			$conditions .= "(sha1='${word}')";
		}
		if($i != count($words) - 1)
		{
			if($_GET["querytype"] == "sha1")
			{
				$conditions .= " OR ";
			}
			else
			{
				$conditions .= " AND ";
			}
		}
	}
}
$limiter = "";
if(is_numeric($_GET["limit"]))
{
	$limiter .= " LIMIT ".intval($_GET["limit"]);
}
if(is_numeric($_GET["offset"]))
{
	if($limiter == "")
	{
		$limiter .= " LIMIT 18446744073709551610";
	}
	$limiter .= " OFFSET ".intval($_GET["offset"]);
}
$result = @mysql_query("SELECT SQL_CALC_FOUND_ROWS sha1,track,title,artist,album,format FROM musictags ${conditions} ORDER BY artist,year,album,disc,track,title ${limiter};");
$totalResult = @mysql_query("SELECT FOUND_ROWS()");
$totalRows = @mysql_result($totalResult, 0, 0);
@mysql_free_result($totalResult);

$language = strtolower($_GET["language"]);
if($language == "javascript")
{
	header("Content-Type: text/javascript; charset=UTF-8");
	echo "[$totalRows,[";
	$first = true;
	while($row = @mysql_fetch_assoc($result))
	{
		$sha1 = htmlentities($row["sha1"], ENT_QUOTES, "UTF-8");
		$track = htmlentities($row["track"], ENT_QUOTES, "UTF-8");
		$title = htmlentities(utf8_encode($row["title"]), ENT_QUOTES, "UTF-8");
		$artist = htmlentities(utf8_encode($row["artist"]), ENT_QUOTES, "UTF-8");
		$album = htmlentities(utf8_encode($row["album"]), ENT_QUOTES, "UTF-8");
		$format = htmlentities($row["format"], ENT_QUOTES, "UTF-8");
		if(!$first)
		{
			echo ",";
		}
		else
		{
			$first = false;
		}
		echo "[\"${sha1}\",\"${track}\",\"${title}\",\"${album}\",\"${artist}\",\"${format}\"]";
	}
	echo "]]";
}
elseif($language == "xml")
{
	header("Content-Type: text/xml; charset=UTF-8");
	$doc = new DOMDocument();
	$doc->encoding = "UTF-8";
	$root = $doc->createElement("songs");
	$root->appendChild(new DOMAttr("totalsongs", $totalRows));
	$doc->appendChild($root);
	while($row = @mysql_fetch_assoc($result))
	{
		$song = $doc->createElement("song");
		foreach($row as $key => $value)
		{
			$keypair = $doc->createElement($key);
			$keypair->appendChild($doc->createTextNode(utf8_encode($value)));
			$song->appendChild($keypair);
		}
		$root->appendChild($song);
	}
	$output = @$doc->saveXML();
	header("Content-Length: ".strlen($output));
	if(!empty($_SERVER["HTTP_ACCEPT_ENCODING"]))
	{
		header("Uncompressed-Length: ".strlen($output));
	}
	if(ini_get("zlib.output_compression") != "1" && strstr($_SERVER["HTTP_ACCEPT_ENCODING"], 'gzip'))
	{
		$output = gzencode($output, 9);
		header("Content-Encoding: gzip");
	}
	echo $output;
}
@mysql_free_result($result);
?>
