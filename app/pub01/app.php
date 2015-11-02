<?php

require_once(ROOT . DS . "lib" . DS . "wx" . DS . "wechat.class.php");

class Pub01
{
	private $username = null;
	private $password = null;
	
	// 构造函数
	public function __construct()
	{
	}
	
	// route.php 调用
	public function run()
	{
		$wechat = Wechat::getWechat(strtolower(__CLASS__));
		/*
		// 设置消息处理映射
		// 默认配置，可以省略设置
		$mapping = array();
		$mapping["text"] = "texthandler";
		$mapping["image"] = "imagehandler";
		$mapping["voice"] = "voicehandler";
		$mapping["video"] = "videohandler";
		$mapping["shortvideo"] = "shortvideohandler";
		$mapping["location"] = "locationhandler";
		$mapping["link"] = "linkhandler";
		$mapping["event"] = "eventhandler";
		$wechat->setMapping($mapping);
		*/
		
		// 配置
		$wechat->setToken("wudics");
		$wechat->setAppid("wx2aaae40788f72d75");
		$wechat->setAppsecret("d4624c36b6795d1d99dcf0547af5443d");
		
		// 执行消息处理
		$wechat->process();
	}
}
