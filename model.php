<?php
	class Model
	{
		private static $pdo = null;
		private static $host = 'localhost';
		private static $db = 'p2011_tom';
		private static $user = 'p2011_root';
		private static $pass = 'BlR5eI35dF';
		private static $charset = 'utf8';
		
		public static function connectDB()
		{
			$dsn = "mysql:host=" . self::$host . ";dbname=" . self::$db . ";charset=" . self::$charset;
			$opt = array(
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
			);
			self::$pdo = new PDO($dsn, self::$user, self::$pass, $opt);
		}
		
		public static function closeDB()
		{
			self::$pdo = null;			
		}

		public static function tableExists($tablename)
		{	
			$db = self::$db;	    
			$result = self::run("SHOW TABLES FROM $db;");
			while($row = $result->fetch(PDO::FETCH_NUM))
				if ($tablename == $row[0])
					return true;
			return false;
		}

		public static function initBook()
		{
			self::run(
				"CREATE TABLE BOOK (
				id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				chapter VARCHAR(32),
				content TEXT);"
			);
		}
		
		public static function initCur($title, $chapter, $content)
		{
			self::run(
				"CREATE TABLE CUR_VAL (
				id INT(1) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				title VARCHAR(32),
				chapter VARCHAR(32),
				content TEXT);"
			);
			$params = array(
				':title' => $title,
				':chapter' => $chapter,
				':content' => $content
			);
			self::run(
				"INSERT INTO CUR_VAL (title, chapter, content)
				VALUES (:title, :chapter, :content);",
				$params
			);
		}

		public static function fillBook($chapter, $content)
		{
			$params = array(
				':chapter' => $chapter,
				':content' => $content
			);
			self::run(
				"INSERT INTO BOOK (chapter, content)
				VALUES (:chapter, :content);",
				$params
			);
		}
		
		public static function getRandRow()
		{
			$result = self::run(
				"SELECT chapter AS title, chapter, content FROM BOOK
				ORDER BY RAND()
				LIMIT 1;"
			);
			return $result->fetch();	
		}
		
		public static function getCurRow()
		{
			$result = self::run("SELECT title, chapter, content FROM CUR_VAL;");
			return $result->fetch();	
		}

		public static function setCurRow($title, $chapter, $content)
		{
			$params = array(
				':title' => $title,
				':chapter' => $chapter,
				':content' => $content
			);
			self::run(
				"UPDATE CUR_VAL 
				SET title = :title, chapter = :chapter, content = :content
				WHERE id = 1;",
				$params
			);
		}

		protected static function run($sql, $params = [])
		{
			$query = self::$pdo->prepare($sql);
			if (array_key_exists(0, $params))
			{
				$i = 1;
				foreach ($params as $value)
					$query->bindValue($i++, $value, self::type($value));
			}
			else
			{
				foreach ($params as $key => $value)
					$query->bindValue($key, $value, self::type($value));
			}
			$query->execute();
			return $query;
		}
 
		protected static function type($value)
		{
			if (is_int($value))
				$type = PDO::PARAM_INT;
			elseif (is_string($value) || is_float($value))
				$type = PDO::PARAM_STR;
			elseif (is_bool($value))
				$type = PDO::PARAM_BOOL;
			elseif (is_null($value))
				$type = PDO::PARAM_NULL;
			else
				$type = false;
			return $type;
		}
	}
?>

