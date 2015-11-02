<?php

	function callHook()
	{
		global $url;
		global $default;

		$urlArr = @explode("/", rtrim($url, "/"));
		$app = @array_shift($urlArr);
		$param = $urlArr;
		
		// 默认app名称，且参数为空
		if ($app == "")
		{
			$app = $default["app"];
		}
		
		// 引入app文件
		$appFile = ROOT . DS . "app" . DS . $app . DS . "app.php";
		if (file_exists($appFile))
		{
			require_once($appFile);
		}
		else
		{
			echo "文件缺失...";
			exit;
		}
		
		// 调用指定应用的run方法，启动应用
		// 应用存在文件夹app下，以appname命名的文件夹，如文件夹pub01
		// 应用文件夹根目录存在app.php文件，并实现了Appname类的run方法（类名首字母大写）
		// 参数将存入run方法
		$appClassName = ucfirst($app);
		$dispatch = new $appClassName();
		
		if (method_exists($dispatch, "run"))
		{
			call_user_func_array(array($dispatch, "run"), $param);
		}
		else
		{
			echo "方法缺失...";
			exit;
		}
	}
	
	callHook();