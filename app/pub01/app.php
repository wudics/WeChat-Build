<?php

require_once(ROOT . DS . "lib" . DS . "wx" . DS . "wechat.class.php");

class Pub01
{
	private $username = null;
	private $password = null;
	
	// ���캯��
	public function __construct()
	{
	}
	
	// route.php ����
	public function run()
	{
		$wechat = Wechat::getWechat(strtolower(__CLASS__));
		/*
		// ������Ϣ����ӳ��
		// Ĭ�����ã�����ʡ������
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
		
		// ����
		$wechat->setToken("wudics");
		$wechat->setAppid("wx2aaae40788f72d75");
		$wechat->setAppsecret("d4624c36b6795d1d99dcf0547af5443d");
		
		// ִ����Ϣ����
		$wechat->process();
	}
}
