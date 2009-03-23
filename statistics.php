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
<h2 align="center" style="margin-bottom: 0px;">Requests by IP Address Ordered by Most Recent IP Request</h2>
<?php
echo "<h6 align=\"center\" style=\"margin-top: 0px;\">It is now ".date("F j, Y \\a\\t g:i:sa, T").".</h6>";
require_once("logger.php");
function linkTerm($term)
{
	if($term == "")
	{
		return "&nbsp;";
	}
	return "<a href=\"/?query=".urlencode(htmlspecialchars_decode($term))."\">".$term."</a>";
}
$ipsResult = mysql_query("SELECT ip, MAX(time) FROM requestlog GROUP BY ip ORDER BY MAX(time) DESC");
while($row = mysql_fetch_assoc($ipsResult))
{
	$ip = $row["ip"];
	echo "<table width=\"100%\" cellspacing=\"0\">";
	
	echo "<tr><th colspan=\"6\"><a href=\"http://ws.arin.net/whois?queryinput=".$ip."\">".$ip." (".@gethostbyaddr($ip).")</a></th></tr>";
	$requestResult = mysql_query("SELECT * FROM requestlog WHERE ip = '$ip' ORDER BY time DESC");
	while($listen = mysql_fetch_assoc($requestResult))
	{
		if($listen["zip"] && $listen["leaderid"] == -1)
		{
			echo "<tr><td><font style=\"font-size:6pt;\">".date("M j, Y g:i:sa T", $listen["time"])."</font></td><td colspan=\"4\" align=\"center\"><i>Zip File</i></td><td><font style=\"font-size: 4pt;\">".$listen["useragent"]."</font></td></tr>";
			$leader = $listen["id"];
		}
		elseif($listen["zip"] && $listen["leaderid"] == $leader)
		{
			echo "<tr><td class=\"noborder\"></td><td class=\"noborder\">".linkTerm($listen["artist"])."</td><td class=\"noborder\">".linkTerm($listen["album"])."</td><td class=\"noborder\">".linkTerm($listen["title"])."</td><td class=\"noborder\"><font style=\"font-size: 4pt;\">".linkTerm($listen["sha1"])."</font></td><td class=\"noborder\"></td></tr>";
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
