<?php

	define("DB_HOST", "locahost");
	define("DB_PORT", "3306");
	define("DB_USER", "root");
	define("DB_PASS", "7758258");
	define("DB_BASE", "wx");
	define("DB_SET", "UTF-8");
	
	define("DOMAIN", "wudics.8800.org");
	define("WEBROOT", "http://" . DOMAIN . dirname($_SERVER["PHP_SELF"]));
	
	
	
	$url = $_GET["url"];
	
	$default["app"] = "homepage";
	
	