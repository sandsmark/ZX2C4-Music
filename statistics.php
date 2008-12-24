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
$entries = parseLog();
foreach($entries as $entry)
{
	$byIP[$entry["ip"]][] = $entry;
}

function timeCompare($a, $b)
{
	return $a["time"] < $b["time"] ? 1 : -1;
}
foreach($byIP as $ip => $data)
{	
	usort($data,'timeCompare');
	$byIP[$ip] = $data;
}

function timeLastItemCompare($a, $b)
{
	return $a[0]["time"] < $b[0]["time"] ? 1 : -1;
}
uasort($byIP,'timeLastItemCompare');

function linkTerm($term)
{
	if($term == "")
	{
		return "&nbsp;";
	}
	return "<a href=\"/?query=".urlencode(htmlspecialchars_decode($term))."\">".$term."</a>";
}
foreach($byIP as $ip => $data)
{
	echo "<table width=\"100%\" cellspacing=\"0\">";
	
	echo "<tr><th colspan=\"6\"><a href=\"http://ws.arin.net/whois?queryinput=".$ip."\">".$ip." (".@gethostbyaddr($ip).")</a></th></tr>";
	foreach($data as $listen)
	{
		if($listen["zip"])
		{
			echo "<tr><td><font style=\"font-size:6pt;\">".date("M j, Y g:i:sa T", $listen["time"])."</font></td><td colspan=\"4\" align=\"center\"><i>Zip File</i></td><td><font style=\"font-size: 4pt;\">".$listen["useragent"]."</font></td></tr>";
			foreach($listen["songs"] as $song)
			{
				echo "<tr><td class=\"noborder\"></td><td class=\"noborder\">".linkTerm($song["artist"])."</td><td class=\"noborder\">".linkTerm($song["album"])."</td><td class=\"noborder\">".linkTerm($song["title"])."</td><td class=\"noborder\"><font style=\"font-size: 4pt;\">".linkTerm($song["sha1"])."</font></td><td class=\"noborder\"></td></tr>";
			}
		}
		else
		{
			echo "<tr><td><font style=\"font-size:6pt;\">".date("M j, Y g:i:sa T", $listen["time"])."</font></td><td>".linkTerm($listen["songs"][0]["artist"])."</td><td>".linkTerm($listen["songs"][0]["album"])."</td><td>".linkTerm($listen["songs"][0]["title"])."</td><td><font style=\"font-size: 4pt;\">".linkTerm($listen["songs"][0]["sha1"])."</font></td><td><font style=\"font-size: 4pt;\">".$listen["useragent"]."</font></td></tr>";
		}
	}
	echo "</table>";
	echo "</p>";
}
?>
<p align="center" style="font-size:8pt">ZX2C4 Music is &copy; Copyright 2008 Jason A. Donenfeld. All Rights Reserved.</p>
</body>
</html>
