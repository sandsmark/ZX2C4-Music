<?php
$logfile = "./download.log";
function logDownload($songArray)
{
	$length = count($songArray);
	if($length == 0)
	{
		return;
	}
	
	$attributeString = "";
	foreach($songArray as $attributes)
	{
		$attributeString .= "song-";
		foreach($attributes as $key => $value)
		{
			$attributeString .= $key.">".base64_encode($value)."<";
		}
		$attributeString .= "|";
	}
	
	global $logfile;
	$file = fopen($logfile, "a");
	
	fwrite($file,
	"ip-".$_SERVER["REMOTE_ADDR"] ."|".
	"useragent-".base64_encode($_SERVER["HTTP_USER_AGENT"])."|".
	"time-".time()."|".
	"zip-".($length > 1 ? "true" : "false")."|".
	$attributeString."\n");
	
	fclose($file);
}
function parseLog()
{
	global $logfile;
	$rawlog = file_get_contents($logfile);
	$lines = explode("\n",$rawlog);
	foreach($lines as $line)
	{
		if($line == "")
		{
			continue;
		}
		$mainKeys = explode("|", $line);
		unset($requestInfo);
		unset($songInfo);
		foreach($mainKeys as $key)
		{
			$pair = explode("-", $key);
			if(count($pair) != 2)
			{
				continue;
			}
			if($pair[0] == "useragent")
			{
				$requestInfo[$pair[0]] = base64_decode($pair[1]);
			}
			elseif($pair[0] == "song")
			{
				$songKeys = explode("<", $pair[1]);
				foreach($songKeys as $songKey)
				{
					$songPair = explode(">", $songKey);
					if(count($songPair) != 2)
					{
						continue;
					}
					$songInfo[$songPair[0]] = base64_decode($songPair[1]);
				}
				$requestInfo["songs"][] = $songInfo;
			}
			elseif($pair[0] == "zip")
			{
				$requestInfo[$pair[0]] = ($pair[1] == "true" ? true : false);
			}
			else
			{
				$requestInfo[$pair[0]] = $pair[1];
			}
		}
		$log[] = $requestInfo;
	}
	return $log;
}
?>