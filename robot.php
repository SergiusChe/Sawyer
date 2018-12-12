<?php
	$path_parts = pathinfo($_SERVER['SCRIPT_FILENAME']);
	chdir($path_parts['dirname']);	
	require_once 'model.php';
	
	$iName = 'index.html';
	$logName = 'log.txt';	

	try
	{
		$text = file_get_contents($iName);
		preg_match("|<title>(.*?)</title>|su", $text, $matches);
		$title = $matches[1];
		preg_match("|<h1>(.*?)</h1>|su", $text, $matches);
		$chapter = $matches[1];
		preg_match("|<p>(.*?)</p>|su", $text, $matches);
		$content = $matches[1];

		Model::connectDB();
		if (Model::tableExists('CUR_VAL') === false)
		{
			Model::initCur($title, $chapter, $content);
		}
		else
		{
			$fields = "";
			$row = Model::getCurRow();
			if ($title !== $row['title']) $fields .= "Title ";
			if ($chapter !== $row['chapter']) $fields .= "Chapter ";
			if ($content !== $row['content']) $fields .= "Content ";
			if ($fields !== "")
			{
				date_default_timezone_set('Europe/Moscow');				
				$msg = date('Y-m-d H:i:s') . " Изменились поля: " . $fields . "\n";
				file_put_contents($logName, $msg, FILE_APPEND | LOCK_EX);
				Model::setCurRow($title, $chapter, $content);

				$subject="=?utf-8?B?". base64_encode("Документ изменен!"). "?=";
				$header="From: Chevdar"; 
				$header.="\nContent-type: text/plain; charset=\"utf-8\"";
				mail("antonvm@mail.ru", $subject, $msg, $header);
				mail("zerodoubler@gmail.com", $subject, $msg, $header);
			}
		}
		Model::closeDB();
	}
	catch (Exception $e)
	{
		echo $e->getMessage();
	}
?>
