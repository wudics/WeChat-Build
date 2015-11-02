<?php

	/*
		单一入口，根据appname加载不同应用
		?url=[appname]/[param1]/[params2]/[params3]
		?url=pub01/wudics/7758258
	*/
	
	session_start();
	
	define("DS", DIRECTORY_SEPARATOR);
	define("ROOT", dirname(__FILE__));
	
	require_once(ROOT. DS . "core" . DS . "bootstrap.php");

	