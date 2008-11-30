<?php 
include_once("databaseconnect.php");
include_once("logger.php");
include_once("sendfile.php");

set_time_limit(0);

$count = $_POST["count"];
if($count <= 0)
{
	echo "No hashs.";
	exit;
}
if(count($hashList) > 200)
{
	echo "You selected more than 200 songs.";
	exit;
}

connectToDatabase();
$query = "SELECT file,artist,album,title,sha1 FROM musictags WHERE ";
for($i = 0; $i < $_POST["count"]; $i++)
{
	if($i != 0)
	{
		$query .= " OR ";
	}
	$query .= "sha1 = ".nullString($_POST["hash".$i]);
}
$query = mysql_query($query);
$fileList = "";
while($row = mysql_fetch_assoc($query))
{
	$fileList .= " ".escapeshellarg($row["file"]);
	$rowList[] = $row;
}
if($fileList == "")
{
	exit;
}
logDownload($rowList);

$filename = "/tmp/sgs".rand().".tmp";
register_shutdown_function("unlink", $filename);
ignore_user_abort(true);
exec("zip -j -0 ".$filename.$fileList);
sendFile($filename, false, str_replace(" ", "", SITE_NAME)."-Download-".date("Ymd-His").".zip", "application/zip", false);
exit;
?>
