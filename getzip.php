<?php 
require_once("databaseconnect.php");
require_once("logger.php");

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
	$rowList[] = $row;
}
@mysql_free_result($query);
if(count($rowList) == 0)
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
foreach($rowList as $file)
{
	$data = @file_get_contents($file["file"]);
	$filename = getPrettyFilename($file);
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
	$filenameLength = strlen($filename);
	echo pack('v', $filenameLength);// file name length
	echo "\x00\x00";		// extra field length
	echo $filename;			// file name
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
	$centralDirectory .= "\x00\x00";		// extra field length
	$centralDirectory .= "\x00\x00";		// file comment length
	$centralDirectory .= "\x00\x00";		// disk number start
	$centralDirectory .= "\x00\x00";		// internal file attributes
	$centralDirectory .= "\x20\x00\x00\x00";	// external file attributes - 'archive' bit set (32)
	$centralDirectory .= pack('V', $offset);	// relative offset of local header
        $offset += 30 + $filenameLength + $size;
	$centralDirectory .= $filename;
}
echo $centralDirectory;		// central directory
echo "\x50\x4b\x05\x06";	// end of central directory signature	
echo "\x00\x00";		// number of this disk
echo "\x00\x00";		// number of the disk with the start of the central directory
echo pack('v', sizeof($rowList)); // number of entries on disk
echo pack('v', sizeof($rowList)); // number of entries
echo pack('V', strlen($centralDirectory)); // size of central directory
echo pack('V', $offset);	// offset to start of central directory
echo "\x00\x00";		// zip comment size
?>
