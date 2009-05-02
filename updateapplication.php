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
set_time_limit(0);
require_once("authenticate.php");
require_once("databaseauthenticate.php");
echo "<h2>ZX2C4 Music Updater</h2>";
echo "<p>Downloading tarball...";
if(copy("http://git.zx2c4.com/?p=zx2c4music.git;a=snapshot;h=HEAD;sf=tgz", "latest.tar.gz"))
{
	echo "done.</p>";
}
else
{
	echo "error!</p>";
	exit;
}
echo "<p>Unpacking tarball:<pre>";
ob_flush();
flush();
system("tar -xvzf latest.tar.gz");
echo "</pre></p>";
if($_GET["updatetagreader"] != "true")
{
	echo "<p>Removing binary blob in case of custom compilation:<pre>";
	ob_flush();
	flush();
	system("rm -rv zx2c4music/tagreader");
	echo "</pre></p>";
}
echo "<p>Merging new files:<pre>";
ob_flush();
flush();
system("mv -v zx2c4music/* .");
echo "</pre></p>";
if($_GET["recompiletagreader"] == "true")
{
	echo "<p>Compiling tag reader:<pre>";
	ob_flush();
	flush();
	system("./compiletagreader.sh");
	echo "</pre></p>";
}
echo "<p>Cleaning up:<pre>";
ob_flush();
flush();
system("rm -rv zx2c4music latest.tar.gz");
echo "</pre></p>";
if($_GET["rescandatabase"] == "true")
{
	require_once("databaseconnect.php");
	echo "<p>Removing old database table and rescanning collection:<pre>";
	ob_flush();
	flush();
	mysql_query("DROP TABLE `musictags`;");
	require_once("updatedatabase.php");
	echo "</pre>";
}
echo "<h2>Finished!</h2>";
ob_flush();
flush();
?>
</body>
</html>
