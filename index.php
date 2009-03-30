<?php require_once("authenticate.php"); ?>
<?php if(!eregi("MSIE", $_SERVER['HTTP_USER_AGENT']) && !eregi("Internet Explorer", $_SERVER['HTTP_USER_AGENT'])) { ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<?php } ?>
<html>
<head>
<title><?php echo SITE_NAME; ?></title>
<link href="style.css" rel="stylesheet" type="text/css">
<script language="JavaScript" src="swfobject.js"></script>
<?php if(eregi("MSIE", $_SERVER['HTTP_USER_AGENT']) || eregi("Internet Explorer", $_SERVER['HTTP_USER_AGENT'])) { ?>
<script language="JavaScript" src="ieadditions.js"></script>
<?php } ?>
<script language="JavaScript" src="musicajax.js"></script>
</head>
<body onLoad="<?php if($_GET["playfirst"] == "true") { echo "playFirst = true;"; } ?>initPlayers();">
<div id="mainBox">

<div id="header">
<?php echo SITE_NAME.'<font id="subheader">'.SITE_BETA.'</font>'; ?>
</div>

<div id="filterBar">Filter: <input onFocus="filterResultsTimer()" onSelect="filterResultsTimer()" onChange="filterResultsTimer()" onKeyPress="filterResultsTimer()" id="filter" value="<?php if(isset($_GET["query"])) { echo $_GET["query"]; } else { echo SITE_DEFAULT_SEARCH; } ?>"> <select onChange="filterResults()" id="filtertype"><?php
$filtertypes = array("all"=>"All", "artist"=>"Artist", "album"=>"Album", "title"=>"Title", "sha1"=>"Hash");
foreach($filtertypes as $key=>$value)
{
	echo "<option value=\"$key\"";
	if($_GET["querytype"] == $key)
	{
		echo " selected";
	}
	echo ">$value</option>";
}
?></select> <img height="16" width="16" src="loading.gif" id="loading"></div>
<div id="counter"></div>
<div id="listings"></div>
<table id="instructions" cellspacing="0" cellpadding="0" border="0" width="100%"><tr>
<td align="left"><img src="download.gif" width="10" height="10">=Add to Download Basket<br><img src="remove.gif" width="10" height="10">=Remove from Download Basket</td>
<td align="center"><span id="flashplayer"></span><iframe id="iframe" frameborder="0" scrolling="no" width="0" height="0"></iframe></td>
<td align="right"><a href="javascript:addEntireList();">Add Entire List to Download Basket <img border="0" src="download.gif" width="10" height="10"></a></td>
</tr></table>
<div id="downloads"></div>
<span id="copyright">ZX2C4 Music is &copy; Copyright 2008-2009 Jason A. Donenfeld. All Rights Reserved.</span>
</div>
<div style="position: absolute; top: 0; left: 0; width: 100%; text-align: center; font-size: 7pt;">This web application is under active development. <a href="mailto:ZX2C4MusicSuggestions@zx2c4.com">Comments? Suggestions? Bugs?</a></div>
<p>
<a href="http://git.zx2c4.com/?p=zx2c4music.git;a=tree">Source Code</a><br>
<a href="statistics.php">Statistics</a><br>
<a href="updatedatabase.php">Update Database</a><br>
<a href="updateapplication.php">Upgrade Application</a><br>
<a href="http://www.zx2c4.com">ZX2C4.COM</a>
</p>
</body>
</html>
