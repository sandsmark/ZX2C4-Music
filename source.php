<html>
<head>
<title>Source of ZX2C4 Music</title>
<style>
body { font-family: Trebeuchet MS, Verdana, Arial; }
a { color:  blue; text-decoration: none; }
a:hover { color: red; }
</style>
</head>
<body>
<h1>Source of ZX2C4 Music</h1>
<h4>&copy; Copyright 2008 Jason A. Donenfeld. All Rights Reserved.</h4>
<h6>You do not have the right to download, modify, distribute, install, copy, or use the following code for purposes other than educational reading.</h6>
<?php
$directory = opendir(".");
while($file = readdir($directory))
{
	$extension = strtolower(substr($file, strrpos($file, ".") + 1));
	if(($extension == "php" || $extension == "cgi" || $extension == "js" || $extension == "css") && $file != "settings.php")
	{
		echo "<h3>${file}</h3>";
		highlight_file($file);
	}
}
?>
<h3>taglib</h3><code>The update script uses a modified version of taglib, which uses components from amarok and custom components.<br><a href="taglib-1.5-modified.tar">Source</a> | <a href="tagreader">x86 Linux Binary</a></code>
</body>
</html>
