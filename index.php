<?php

	/*
		��һ��ڣ�����appname���ز�ͬӦ��
		?url=[appname]/[param1]/[params2]/[params3]
		?url=pub01/wudics/7758258
	*/
	
	session_start();
	
	define("DS", DIRECTORY_SEPARATOR);
	define("ROOT", dirname(__FILE__));
	
	require_once(ROOT. DS . "core" . DS . "bootstrap.php");

	