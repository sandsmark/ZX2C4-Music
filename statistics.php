<?php require_once("authenticate.php"); ?>
<html>
<head>
<title><?php echo SITE_NAME; ?> - Statistics</title>
<style>
body
{
	font-family: Trebeuchet MS, Verdana, Arial;
}
table
{
	border: 1px solid black;
}
td
{
	border-width: 1px 0px 0px 0px;
	border-color: black;
	border-style: solid;
	font-size: 9pt;
}
.noborder
{
	border: 0px;
}
a
{
	color: black;
	text-decoration: none;
}
</style>
</head>
<body>
<h1 align="center" style="margin-bottom: 0px;">Logs and Statistics<h1>
<?php
require_once("logger.php");
echo '<h3 align="center" style="margin-bottom: 0px;">Quick Stats</h3><div align="center"><div align="left" style="display:inline-block; font-size: 10pt;">';
echo "Since ".date("F j, Y \\a\\t g:i:sa, T", mysql_result(mysql_query("SELECT MIN(time) FROM requestlog;"), 0, 0)).":";
echo "<li>".mysql_result(mysql_query("SELECT COUNT(*) FROM musictags;"), 0, 0)." songs available</li>";
echo "<li>".mysql_result(mysql_query("SELECT COUNT(*) FROM requestlog;"), 0, 0)." total songs served</li>";
echo "<li>".mysql_result(mysql_query("SELECT COUNT(DISTINCT sha1) FROM requestlog;"), 0, 0)." different songs served</li>";
echo "<li>".mysql_result(mysql_query("SELECT COUNT(*) FROM requestlog WHERE zip=0;"), 0, 0)." songs streamed in the web player</li>";
echo "<li>".mysql_result(mysql_query("SELECT COUNT(*) FROM requestlog WHERE zip=1;"), 0, 0)." downloaded in zip files</li>";
echo "<li>".mysql_result(mysql_query("SELECT COUNT(*) FROM requestlog WHERE zip=1 AND leaderid=-1;"), 0, 0)." total zip files downloaded</li>";
echo "<li>".mysql_result(mysql_query("SELECT COUNT(DISTINCT ip) FROM requestlog;"), 0, 0)." different IP addresses</li>";
echo "<li>".mysql_result(mysql_query("SELECT COUNT(DISTINCT useragent) FROM requestlog;"), 0, 0)." different user agents</li>";
echo "It is now ".date("F j, Y \\a\\t g:i:sa, T").".</div></div>";

echo '<h3 align="center" style="margin-bottom: 0px;">Top 10 Artists</h3><div align="center"><div align="left" style="display:inline-block; font-size: 10pt;">'; 
$query = mysql_query("SELECT COUNT(artist), artist FROM requestlog GROUP BY artist ORDER BY COUNT(artist) DESC LIMIT 10;");
while($result = mysql_fetch_assoc($query))
{
	echo "<li>".$result["COUNT(artist)"].": ".$result["artist"]."</li>";
}
echo "</div></div>";

echo '<h3 align="center" style="margin-bottom: 0px;">Top 10 User Agents</h3><div align="center"><div align="left" style="display:inline-block; font-size: 10pt;">'; 
$query = mysql_query("SELECT COUNT(DISTINCT ip), useragent FROM requestlog GROUP BY useragent ORDER BY COUNT(DISTINCT ip) DESC LIMIT 10;");
while($result = mysql_fetch_assoc($query))
{
	echo "<li>".$result["COUNT(DISTINCT ip)"].": ".$result["useragent"]."</li>";
}
echo "</div></div>";

echo '<h3 align="center" style="margin-bottom: 0px;">Requests by IP Address Ordered by Most Recent IP Request</h3>';
function linkTerm($term)
{
	if($term == "")
	{
		return "&nbsp;";
	}
	return "<a href=\"/?query=".urlencode(htmlspecialchars_decode($term))."\">".$term."</a>";
}
$ipsResult = mysql_query("SELECT ip, MAX(time), COUNT(*) FROM requestlog GROUP BY ip ORDER BY MAX(time) DESC;");
while($row = mysql_fetch_assoc($ipsResult))
{
	echo "<table width=\"100%\" cellspacing=\"0\">";
	echo "<tr><th colspan=\"6\"><a href=\"http://ws.arin.net/whois?queryinput=".$row["ip"]."\">".$row["ip"]." (".@gethostbyaddr($row["ip"]).")</a> ".$row["COUNT(*)"]." total downloads</th></tr>";
	$requestResult = mysql_query("SELECT * FROM requestlog WHERE ip = '".$row["ip"]."' AND leaderid = -1 ORDER BY time DESC");
	while($listen = mysql_fetch_assoc($requestResult))
	{
		if($listen["zip"])
		{
			echo "<tr><td><font style=\"font-size:6pt;\">".date("M j, Y g:i:sa T", $listen["time"])."</font></td><td colspan=\"4\" align=\"center\"><i>Zip File</i></td><td><font style=\"font-size: 4pt;\">".$listen["useragent"]."</font></td></tr>";
			$subRequestResult = mysql_query("SELECT * FROM requestlog WHERE leaderid = ".$listen["id"]." ORDER BY time DESC");
			do
			{
				echo "<tr><td class=\"noborder\"></td><td class=\"noborder\">".linkTerm($listen["artist"])."</td><td class=\"noborder\">".linkTerm($listen["album"])."</td><td class=\"noborder\">".linkTerm($listen["title"])."</td><td class=\"noborder\"><font style=\"font-size: 4pt;\">".linkTerm($listen["sha1"])."</font></td><td class=\"noborder\"></td></tr>";
			}
			while($listen = mysql_fetch_assoc($subRequestResult));
		}
		else
		{
			echo "<tr><td><font style=\"font-size:6pt;\">".date("M j, Y g:i:sa T", $listen["time"])."</font></td><td>".linkTerm($listen["artist"])."</td><td>".linkTerm($listen["album"])."</td><td>".linkTerm($listen["title"])."</td><td><font style=\"font-size: 4pt;\">".linkTerm($listen["sha1"])."</font></td><td><font style=\"font-size: 4pt;\">".$listen["useragent"]."</font></td></tr>";
		}
	}
	echo "</table>";
	echo "</p>";
}
?>
<p align="center" style="font-size:8pt">ZX2C4 Music is &copy; Copyright 2008-2009 Jason A. Donenfeld. All Rights Reserved.</p>
</body>
</html>
