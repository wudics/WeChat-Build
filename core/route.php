<?php

	function callHook()
	{
		global $url;
		global $default;

		$urlArr = @explode("/", rtrim($url, "/"));
		$app = @array_shift($urlArr);
		$param = $urlArr;
		
		// Ĭ��app���ƣ��Ҳ���Ϊ��
		if ($app == "")
		{
			$app = $default["app"];
		}
		
		// ����app�ļ�
		$appFile = ROOT . DS . "app" . DS . $app . DS . "app.php";
		if (file_exists($appFile))
		{
			require_once($appFile);
		}
		else
		{
			echo "�ļ�ȱʧ...";
			exit;
		}
		
		// ����ָ��Ӧ�õ�run����������Ӧ��
		// Ӧ�ô����ļ���app�£���appname�������ļ��У����ļ���pub01
		// Ӧ���ļ��и�Ŀ¼����app.php�ļ�����ʵ����Appname���run��������������ĸ��д��
		// ����������run����
		$appClassName = ucfirst($app);
		$dispatch = new $appClassName();
		
		if (method_exists($dispatch, "run"))
		{
			call_user_func_array(array($dispatch, "run"), $param);
		}
		else
		{
			echo "����ȱʧ...";
			exit;
		}
	}
	
	callHook();