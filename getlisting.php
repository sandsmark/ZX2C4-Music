<?php
include_once("databaseconnect.php");

if(!isset($_GET["language"]))
{
	exit;
}

set_time_limit(0);

connectToDatabase();

if($_GET["query"] != "")
{
	$words = explode(" ", $_GET["query"]);
	$conditions = "WHERE (sha1 = ".nullString($_GET["query"]).") OR (";
	for($i = 0; $i < count($words); $i++)
	{
		$word = mysql_real_escape_string($words[$i]);
		$conditions .= "(artist LIKE '%${word}%' OR title LIKE '%${word}%' OR album LIKE '%${word}%')";
		if($i != count($words) - 1)
		{
			$conditions .= " AND ";
		}
	}
	$conditions .= ")";
}
$result = @mysql_query("SELECT sha1,track,title,artist,album,format FROM musictags ${conditions} ORDER BY artist,album,year,disc,track,title;");

$language = strtolower($_GET["language"]);
if($language == "javascript")
{
	echo "[[\"sha1\",\"track\",\"title\",\"artist\",\"album\",\"format\"]";
	while($row = @mysql_fetch_assoc($result))
	{
		$sha1 = htmlentities($row["sha1"]);
		$track = htmlentities($row["track"]);
		$title = htmlentities($row["title"]);
		$artist = htmlentities($row["artist"]);
		$album = htmlentities($row["album"]);
		$format = htmlentities($row["format"]);
		
		echo ",[\"${sha1}\",\"${track}\",\"${title}\",\"${album}\",\"${artist}\",\"${format}\"]";
	}
	echo "]";
}
elseif($language == "xml")
{
	header("Content-Type: text/xml");
	$doc = new DOMDocument();
	$root = $doc->createElement("songs");
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
