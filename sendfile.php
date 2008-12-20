<?php
function sendFile($path, $inline, $name, $mime, $allowPartial)
{
	$handle = @fopen($path, 'rb');
	if($handle)
	{
		$filelength = @filesize($path);
		$length = $filelength;
		$posfrom = 0;
		$posto = $length - 1;
		if($allowPartial)
		{
			if(isset($_SERVER['HTTP_RANGE']))
			{
				$data = explode('=',$_SERVER['HTTP_RANGE']);
				$ppos = explode('-', trim($data[1]));
				$posfrom = (int)trim($ppos[0]);
				if(trim($ppos[1]) != "")
				{
					$posto = (int)trim($ppos[1]);
				}
				$length = $posto - $posfrom + 1;
				@fseek($handle, $posfrom, SEEK_SET);
				header('HTTP/1.1 206 Partial Content', true);
				header('Content-Range: bytes '.$posfrom.'-'.$posto.'/'.$filelength);
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
		header('Content-Length: '.$length);
		while(!feof($handle) && !connection_aborted() && $length > 0) 
		{	
			echo @fread($handle, min(16384, $length));
			$length -= 16384;
			ob_flush();	
			flush();
		}
		@fclose($handle);
	}
}
?>
