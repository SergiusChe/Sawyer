<?php
	$path_parts = pathinfo($_SERVER['SCRIPT_FILENAME']);
	chdir($path_parts['dirname']);	
	require_once 'model.php';
	
	$iName = 'index.html';
	$bookName = 'tom.txt';
	
	try
	{
		Model::connectDB();
		if (Model::tableExists('BOOK') === false)
		{
			$text = file_get_contents($bookName);
			preg_match_all("/ГЛАВА\W+[IVXLM]+/su", $text, $chapters);
			$contents = preg_split("/((ГЛАВА\W+[IVXLM]+)|ЗАКЛЮЧЕНИЕ)\W+/su", $text);
			array_shift($contents);
			array_pop($contents);

			Model::initBook();
			foreach ($chapters[0] as $key => $chapter)
				Model::fillBook($chapter, $contents[$key]);
		}
		$row = Model::getRandRow();
		foreach ($row as $key => $val)
			if (rand(0, 1) === 1)
			{
				switch ($key)
				{
					case 'title':
						$patterns[] = "|<title>(.*?)</title>|su";
						$replacements[] = "<title>$val</title>";
						break;
					case 'chapter':
						$patterns[] = "|<h1>(.*?)</h1>|su";
						$replacements[] = "<h1>$val</h1>";
						break;
					case 'content':
						$val = iconv_substr ($val, 0, 2000, "UTF-8");
						$patterns[] = "|<p>(.*?)</p>|su";
						$replacements[] = "<p>$val</p>";
						break;
					default:
						break;
				}					
			}
		if (isset($patterns) && isset($replacements))
		{	
			$text = file_get_contents($iName);
			$text = preg_replace($patterns, $replacements, $text);
			file_put_contents($iName, $text, LOCK_EX);
		}
		Model::closeDB();
	}
	catch (Exception $e)
	{
		echo $e->getMessage();
	}
?>
