<?php 
include_once("databaseconnect.php");
include_once("logger.php");
include_once("sendfile.php");

set_time_limit(0);

$count = $_POST["count"];
if($count <= 0)
{
	echo "No hashes.";
	exit;
}

connectToDatabase();
$query = "SELECT file,track,artist,album,title,sha1 FROM musictags WHERE ";
for($i = 0; $i < $_POST["count"]; $i++)
{
	if($i != 0)
	{
		$query .= " OR ";
	}
	$query .= "sha1 = ".nullString($_POST["hash".$i]);
}
$query = mysql_query($query);
while($row = mysql_fetch_assoc($query))
{
	$filename = "";
	if(strlen($row["track"]) > 0 && intval($row["track"]) > 0)
	{
		$filename .= $row["track"];
	}
	if(strlen($row["artist"]) > 0)
	{
		$filename .= " - ".$row["artist"];
	}
	if(strlen($row["album"]) > 0)
	{
		$filename .= " - ".$row["album"];
	}
	if(strlen($row["title"]) > 0)
	{
		$filename .= " - ".$row["title"];
	}
	$filename .= ".".getFileExtension($row["file"]);
	$files[] = array($row["file"], $filename);
	$rowList[] = $row;
}
if(count($files) == 0)
{
	exit;
}
logDownload($rowList);

if(eregi("MSIE", $_SERVER['HTTP_USER_AGENT']) || eregi("Internet Explorer", $_SERVER['HTTP_USER_AGENT']))
{
	header("Content-Type: application/octet-stream");
}
else
{
	header("Content-Type: application/zip");
}
header('Content-Transfer-Encoding: binary');
header('Content-Disposition: attachment; filename="'.str_replace(" ", "", SITE_NAME)."-Download-".date("Ymd-His").".zip".'"');
$centralDirectory = "";
$offset = 0;
foreach($files as $file)
{
	$data = @file_get_contents($file[0]);
	echo "\x50\x4b\x03\x04";	// local file header signature
	echo "\x14\x00";		// version needed to extract
	echo "\x00\x00";		// general purpose bit flag
	echo "\x00\x00";		// compression method
	echo "\x00\x00";		// last mod file time
	echo "\x00\x00";		// last mod file date
	$crc32 = crc32($data);
	echo pack('V', $crc32);		// crc-32
	$size = strlen($data);
	echo pack('V', $size);		// compressed size
	echo pack('V', $size);		// uncompressed size
	$filenameLength = strlen($file[1]);
	echo pack('v', $filenameLength);// file name length
	echo pack('v', 0);		// extra field length
	echo $file[1];			// file name
	echo $data;			// file data
	$centralDirectory .= "\x50\x4b\x01\x02";
	$centralDirectory .= "\x00\x00";		// version made by
	$centralDirectory .= "\x14\x00";		// version needed to extract
	$centralDirectory .= "\x00\x00";		// gen purpose bit flag
	$centralDirectory .= "\x00\x00";		// compression method
	$centralDirectory .= "\x00\x00";		// last mod file time
	$centralDirectory .= "\x00\x00";		// last mod file date
	$centralDirectory .= pack('V', $crc32);		// crc-32
	$centralDirectory .= pack('V', $size);		// compressed filesize
	$centralDirectory .= pack('V', $size);		// uncompressed filesize
	$centralDirectory .= pack('v', $filenameLength);// length of filename
	$centralDirectory .= pack('v', 0);		// extra field length
	$centralDirectory .= pack('v', 0);		// file comment length
	$centralDirectory .= pack('v', 0);		// disk number start
	$centralDirectory .= pack('v', 0);		// internal file attributes
	$centralDirectory .= pack('V', 32);		// external file attributes - 'archive' bit set
	$centralDirectory .= pack('V', $offset);	// relative offset of local header
        $offset += 30 + $filenameLength + $size;
	$centralDirectory .= $file[1];
}
echo $centralDirectory;		// central directory
echo "\x50\x4b\x05\x06";	// end of central directory signature	
echo "\x00\x00";		// number of this disk
echo "\x00\x00";		// number of the disk with the start of the central directory
echo pack('v', sizeof($files));	// number of entries on disk
echo pack('v', sizeof($files));	// number of entries
echo pack('V', strlen($centralDirectory)); // size of central directory
echo pack('V', $offset);	// offset to start of central directory
echo "\x00\x00";		// zip comment size
?>
