<?php
require_once("databaseconnect.php");
function nullEq($str)
{
	if($str == "")
	{
		return "IS NULL";
	}
	else
	{
		return "= '".mysql_real_escape_string($str)."'";
	}
}
if($_GET["getlisting"] == true)
{
	if(isset($_GET["album"]) && isset($_GET["artist"]))
	{
		$artist = nullEq($_GET["artist"]);
		$album = nullEq($_GET["album"]);
		$result = @mysql_query("SELECT title, sha1 FROM musictags WHERE artist $artist AND album $album ORDER BY disc, track, title");
	}
	elseif(isset($_GET["artist"]))
	{
		$artist = nullEq($_GET["artist"]);
		$result = @mysql_query("SELECT DISTINCT album FROM musictags WHERE artist $artist ORDER BY year, album");
	}
	else
	{
		$result = @mysql_query("SELECT DISTINCT artist FROM musictags ORDER BY artist;");
	}
	header("Content-Type: text/javascript; charset=UTF-8");
	while($row = @mysql_fetch_row($result))
	{
		for ($i = 0; $i < count($row); ++$i)
			$row[$i] = utf8_encode($row[$i]);
		$rows[] = $row;
	}
	echo json_encode($rows);
	exit;
}
?>
<html>
<head>
<title>ZX2C4 Music Test</title>
<script language="JavaScript">
function addChildren(root, trail, level)
{
	var requestObj = new XMLHttpRequest();
	requestObj.open('GET', "testinterface.php?getlisting=true" + trail);
	requestObj.level = level;
	requestObj.trail = trail;
	requestObj.root = root;
	requestObj.onreadystatechange = function()
	{
		if(requestObj.readyState == 4 && requestObj.responseText != "")
		{
			var array = eval(requestObj.responseText);
			if(array == null)
			{
				return;
			}
			var verb;
			if(this.level == 0)
			{
				verb = "artist";
			}
			else if(this.level == 1)
			{
				verb = "album";
			}
			var list = document.createElement("ul");
			for(var i = 0; i < array.length; i++)
			{
				var nextNode = document.createElement("li");
				var nextLink = document.createElement("a");
				if(this.level < 2)
				{
					nextLink.setAttribute("href", "javascript:;");
					nextLink.list = nextNode;
					nextLink.arg = this.trail + "&" + verb + "=" + encodeURIComponent(array[i][0] == null ? "" : array[i][0]);
					nextLink.level = this.level + 1;
					var firstFunction = function()
					{
						this.onclick = function()
						{
							if(this.list != null && this.list.childNodes.length >= 2)
							{
								this.list.removeChild(this.list.childNodes[1]);
								this.onclick = firstFunction;
							}
						};
						addChildren(this.list, this.arg, this.level);
					};
					nextLink.onclick = firstFunction;
				}
				else
				{
					nextLink.setAttribute("href", "getsong.php?hash=" + array[i][1]);
				}
				var nextText = document.createTextNode(array[i][0] == null ? "Unnamed" : array[i][0]);
				nextLink.appendChild(nextText);
				nextNode.appendChild(nextLink);
				list.appendChild(nextNode);
			}
			this.root.appendChild(list);
		}
	}
	requestObj.send(null);
}
</script>
<style>
a { text-decoration: none; color: #777777; }
a:hover { color: black; }
</style>
</head>
<body onLoad="addChildren(document.body, '', 0);">
<h3>ZX2C4 Music</h3>
</body>
</html>
