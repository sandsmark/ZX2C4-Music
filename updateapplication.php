<html>
<head>
<title>ZX2C4 Music Updater</title>
<style>
pre
{
	margin: 0px 0px 0px 20px;
}
p
{
	margin: 0px;
}
</style>
</head>
<body>
<?php
require_once("authenticate.php");
require_once("databaseauthenticate.php");

$webSocket = fsockopen("git.zx2c4.com", 80);
$fileSocket = fopen("latest.tar.gz", "w");
fwrite($webSocket, "GET /?p=zx2c4music.git;a=snapshot;h=HEAD;sf=tgz HTTP/0.9\nHost: git.zx2c4.com\n\n");
$header = true;
echo "<h2>ZX2C4 Music Updater</h2>";
echo "<p>Downloading tarball";
ob_flush();
flush();
while($buffer = fread($webSocket, 1024))
{
	echo " .";
	ob_flush();
	flush();
	if($header)
	{
		$start = strpos($buffer, "\r\n\r\n");
		if($start !== false)
		{
			$header = false;
			$buffer = substr($buffer, $start + 4);
		}
	}
	fwrite($fileSocket, $buffer);
}
echo "</p>";
echo "<p>Unpacking tarball:<pre>";
ob_flush();
flush();
system("tar -xvzf latest.tar.gz");
echo "</pre></p>";
echo "<p>Removing binary blob in case of custom compilation:<pre>";
ob_flush();
flush();
system("rm -rv zx2c4music/tagreader");
echo "</pre></p>";
echo "<p>Merging new files:<pre>";
ob_flush();
flush();
system("mv -v zx2c4music/* .");
echo "</pre></p>";
echo "<p>Cleaning up:<pre>";
ob_flush();
flush();
system("rm -rv zx2c4music latest.tar.gz");
echo "</pre></p>";
echo "<h2>Finished!</h2>";
ob_flush();
flush();
?>
</body>
</html>
