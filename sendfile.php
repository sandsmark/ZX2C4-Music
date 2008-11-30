<?php
function sendFile($path, $inline, $name, $mime, $allowPartial)
{
	$handle = @fopen($path, 'rb');
	if($handle)
	{
		$filelength = @filesize($path);
		if($allowPartial)
		{
			$posfrom = 0;
			if(isset($_SERVER['HTTP_RANGE']))
			{
				$data = explode('=',$_SERVER['HTTP_RANGE']);
				$ppos = explode('-', trim($data[1]));
				$posfrom = (int)trim($ppos[0]);
			}
			header('Content-Range: bytes '.$posfrom.'-'); //.($filelength - 1).'/'.$filelength
			if(isset($_SERVER['HTTP_RANGE']))
			{
				header('HTTP/1.1 206 Partial Content', true);
				@fseek($handle, $posfrom, SEEK_SET);
			}
			header('Accept-Ranges: bytes');
		}
		if (!$inline)
		{		
			header('Content-Disposition: attachment; filename="'.$name.'"');
		}
		else
		{
			header('Content-Disposition: inline; filename="'.$name.'"');
		}
		header('Content-Type: '.$mime);
		header('Content-Transfer-Encoding: binary');
		header('Pragma: public');
		header('Content-Length: '.($filelength - $posfrom));
		while(!feof($handle) && !connection_aborted()) 
		{	
			echo fread($handle, 16384);
			ob_flush();	
			flush();
		}
		@fclose($handle);
	}
}
?>
