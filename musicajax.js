var requestObj;
var timerId;
var lastValue;
var player = null;
var listings;
var loading;
var iframe;
var filter;
var downloads;
var downloadsBox;
var counter;
var songList = null;
var downloadBasket = new Array();
var tableComplete = true;
var requestInProgress = false;
var completeOffset;

if(navigator.appName == "Microsoft Internet Explorer")
{
	requestObj = new ActiveXObject("Microsoft.XMLHTTP");
}
else
{
	requestObj = new XMLHttpRequest();
}
function trimString(string)
{
	var start = 0;
	var end = string.length - 1;
	while(string.charAt(start) == " ")
	{
		start++;
	}
	while(string.charAt(end) == " ")
	{
		end--;
	}
	return string.substring(start, end + 1);
}
function filterResultsTimer()
{
	clearTimeout(timerId);
	timerId = setTimeout("filterResults()", 500);
}
function filterResults()
{
	query = trimString(filter.value);
	if(query == lastValue)
	{
		return;
	}
	listings.scrollTop = 0;
	lastValue = query;
	loading.style.visibility = "visible";
	requestInProgress = true;
	requestObj.open('GET', "getlisting.php?language=javascript&query=" + query + "&limit=50");
	requestObj.onreadystatechange = function() { displayResults(0, lastValue); }
	requestObj.send(null);
}
function displayResults(offset, requestedValue)
{
	if(requestObj.readyState == 4)
	{
		requestInProgress = false;
		if(requestedValue != lastValue)
		{
			return;
		}
		completeOffset = offset;
		var nextBatch = eval(requestObj.responseText);
		if(nextBatch == null || nextBatch == undefined)
		{
			nextBatch = [0,[]];
		}
		if(offset == 0)
		{
			songList = nextBatch[1];
		}
		else
		{
			songList = songList.concat(nextBatch[1]);
		}
		tableComplete = nextBatch[0] <= songList.length;
		if(songList.length == 0)
		{
			counter.innerHTML = "No songs found.";
		}
		else if(!tableComplete)
		{
			counter.innerHTML =  "Loaded " + songList.length.toString() + " of " + nextBatch[0].toString() + " songs. Scroll to the bottom for more.";
		}
		else
		{
			counter.innerHTML = "Loaded " + songList.length.toString() + " songs.";
		}
		var tableData = new Array();
		if(offset == 0)
		{
			tableData.push("<table id=\"listingsBox\">");
		}
		for(var i = offset; i < songList.length; i++)
		{
			tableData.push("<tr id=\"", songList[i][0], "\"", (i % 2 != 0 ? " bgcolor=\"#EEEEEE\"" : ""), ">");
			for(var j = 1; j < songList[i].length - 1; j++)
			{
				tableData.push("<td>");
				if(j == 2)
				{
					tableData.push("<a href=\"javascript:playSong('", songList[i][0], "',", (songList[i][5] == "mp3").toString(), ");\">");
				}
				tableData.push(songList[i][j]);
				if(j == 2)
				{
					tableData.push("</a>");
				}
				tableData.push("</td>");
			}
			tableData.push("<td>", getDownloadIcon(songList[i][0], (downloadBasket.indexOf(songList[i][0]) == -1)), "</td></tr>");
		}
		if(offset == 0)
		{
			tableData.push("</table>");
			listings.innerHTML = tableData.join("");
		}
		else
		{
			var box = document.getElementById("listingsBox");
			if(navigator.appName == "Microsoft Internet Explorer")
			{
				var outer = box.outerHTML;
				var tbody = outer.indexOf("</TBODY>");
				box.outerHTML = outer.substring(0, tbody) + tableData.join("") + outer.substring(tbody);
			}
			else
			{
				box.innerHTML += tableData.join("");
			}
		}
		loading.style.visibility = "hidden";
	}
}
function watchScroll()
{
	if(!tableComplete && !requestInProgress && listings.scrollHeight - listings.scrollTop - listings.offsetHeight < 250)
	{
		requestInProgress = true;
		loading.style.visibility = "visible";
		requestObj.open('GET', "getlisting.php?language=javascript&query=" + query + "&limit=50&offset=" + (completeOffset + 50).toString());
		requestObj.onreadystatechange = function() { displayResults(completeOffset + 50, trimString(filter.value)); }
		requestObj.send(null);
	}
	setTimeout(arguments.callee, 100);
}
function getDownloadIcon(hash, download)
{
	if(download)
	{
		return "<a href=\"javascript:addToBasket('" + hash + "');\"><img src=\"download.gif\" width=\"12\" height=\"12\" border=\"0\"></a>";
	}
	else
	{
		return "<a href=\"javascript:removeFromBasket('" + hash + "');\"><img src=\"remove.gif\" width=\"12\" height=\"12\" border=\"0\"></a>";
	}
}
function addToBasket(hash)
{
	if(downloadBasket.length == 0)
	{
		downloads.innerHTML = "<table align=\"center\" id=\"downloadsBox\"><tr><th align=\"center\" colspan=\"5\">Downloads Basket</th></tr><tr><th align=\"center\" colspan=\"5\"><i><a href=\"javascript:downloadBasketZip();\">Download ZIP of Basket</a> | <a href=\"javascript:emptyBasket();\">Empty Basket</a></i></th></tr></table>";	
		downloadsBox = document.getElementById("downloadsBox").childNodes[0];
	}
	if(downloadBasket.indexOf(hash) != -1)
	{
		return;
	}
	downloadBasket.push(hash);
	var row = document.getElementById(hash);
	row.childNodes[4].innerHTML = getDownloadIcon(hash, false);
	var clonedNode = row.cloneNode(true);
	clonedNode.style.background = "#FFFFFF";
	downloadsBox.appendChild(clonedNode);
}
function downloadBasketZip()
{
	if(downloadBasket.length == 0)
	{
		return;
	}
	var hashString = downloadBasket[0];
	for(var i = 1; i < downloadBasket.length; i++)
	{
		hashString += "|" + downloadBasket[i];
	}
	var postform = document.createElement("form");
	postform.action = "getzip.php";
	postform.method = "POST"
	
	var field = document.createElement("input");
	field.name = "count"
	field.value = downloadBasket.length;
	field.type = "hidden";
	postform.appendChild(field);
	for(var i = 0; i < downloadBasket.length; i++)
	{
		field = document.createElement("input");
		field.name = "hash" + i.toString();
		field.value = downloadBasket[i];
		field.type = "hidden";
		postform.appendChild(field);
	}
	
	document.body.appendChild(postform);
	postform.submit();
	document.body.removeChild(postform);
	emptyBasket();
}
function addEntireList()
{
	if(!tableComplete)
	{
		requestInProgress = true;
		loading.style.visibility = "visible";
		requestObj.open('GET', "getlisting.php?language=javascript&query=" + query + "&offset=" + (completeOffset + 50).toString());
		requestObj.onreadystatechange = function()
		{
			if(requestObj.readyState == 4)
			{
				displayResults(completeOffset + 50, trimString(filter.value));
				tableComplete = true;
				addEntireList();
			}
		}
		requestObj.send(null);
	}
	else if(songList != null && songList.length > 1)
	{
		if(songList.length > 99)
		{
			if(!confirm("You are about to add " + songList.length.toString() + " songs to the download basket. This may take a long time.\n\nAre you sure you want to continue?"))
			{
				return;
			}
		}
		for(var i = 0; i < songList.length; i++)
		{
			addToBasket(songList[i][0]);
		}
	}
}
function removeFromBasket(hash)
{
	var index = downloadBasket.indexOf(hash);
	downloadBasket.splice(index, 1);
	downloadsBox.removeChild(downloadsBox.childNodes[index + 2]);
	index = document.getElementById(hash);
	if(index != null)
	{
		index.childNodes[4].innerHTML = getDownloadIcon(hash, true);
	}
	if(downloadBasket.length == 0)
	{
		downloads.innerHTML = "";
	}
}
function emptyBasket()
{
	while(downloadBasket.length > 0)
	{
		removeFromBasket(downloadBasket[0]);
	}
}
function playSong(hash, mp3)
{
	if(player != null)
	{
		iframe.width = 1;
		iframe.height = 1;
		iframe.style.visibility = "hidden";
		iframe.src = "";
		player.width = 290;
		player.height = 24;
		player.style.visibility = "visible";
		player.SetVariable("soundFile", "getsong.php?hash=" + hash + (mp3 ? "" : "&transcode=true"));
		player.SetVariable("autostart", "yes");
		player.StopPlay();
		player.Rewind();
		player.Play();
	}
	else
	{
		if(player != null)
		{
			player.width = 1;
			player.height = 1;
			player.style.visibility = "hidden";
			player.SetVariable("closePlayer", 1);
		}
		iframe.src = "getsong.php?hash=" + hash;
		iframe.width = 290;
		iframe.height = 20;
		iframe.style.visibility = "visible";
	}
}
function initPlayers()
{
	if(swfobject.hasFlashPlayerVersion("7.0.0"))
	{
		swfobject.embedSWF("mp3player.swf", "flashplayer", "0", "0", "7.0.0", false, false, false, false);
		player = document.getElementById("flashplayer");
		player.style.visibility = "hidden";
	}
	iframe = document.getElementById("iframe");	
	downloads = document.getElementById("downloads");
	listings = document.getElementById("listings");
	loading = document.getElementById("loading");
	filter = document.getElementById("filter");
	counter = document.getElementById("counter");
	watchScroll();
	filterResults();
	filter.focus();
}
