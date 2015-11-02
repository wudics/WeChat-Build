<?php

class Wechat
{	
	// ��̬Wechatʵ��
	public static $wechat = null;

	// App
	private $app = null;
	
	// Token
	private $token = null;
	private $appid = null;
	private $appsecret = null;
	
	/*
		mapping���ݽṹ����Ӧһ�����͵Ĵ���������app/appname/handler��
		mapping = array();
		mapping["text"] = "texthandler";	// texthandler.class.php
		mapping["voidc"] = "voicehandler";	// voice.class.php
	*/
	private $mapping = null;	// ��Ϣ����ӳ���
	
	// ͨ��getWechat�ɻ�ȡ��̬ʵ��
	public static function getWechat($app)
	{
		if (self::$wechat == null)
		{
			self::$wechat = new Wechat($app);
		}
		return self::$wechat;
	}
	
	// ���캯��
	public function __construct($app)
	{
		// AppName
		$this->app = $app;
		
		// Ĭ����Ϣ���ʹ���
		$this->mapping = array();
		$this->mapping["text"] = "texthandler";
		$this->mapping["image"] = "imagehandler";
		$this->mapping["voice"] = "voicehandler";
		$this->mapping["video"] = "videohandler";
		$this->mapping["shortvideo"] = "shortvideohandler";
		$this->mapping["location"] = "locationhandler";
		$this->mapping["link"] = "linkhandler";
		$this->mapping["event"] = "eventhandler";
	}
	
	// ����token
	public function setToken($token)
	{
		$this->token = $token;
	}
	
	// ����appid
	public function setAppid($appid)
	{
		$this->appid = $appid;
	}
	
	// ����appsecret
	public function setAppsecret($appsecret)
	{
		$this->appsecret = $appsecret;
	}

	// ����mapping
	public function setMapping($mapping)
	{
		$this->mapping = $mapping;
	}
	
	// Wechatִ�лص�����
	public function process()
	{
		// ͨ���ж�echostr�Ƿ���ڣ�ȷ���Ƿ���Ҫvalid
		if (isset($_GET["echostr"]))
		{
			$this->valid();
		}
		else
		{
			$this->responseMsg();
		}
	}
	
	// ��ӦУ����Ϣ
	private function valid()
	{
		$echoStr = $_GET["echostr"];
		
		if ($this->checkSignature())
		{
			// У����ȷ��ԭ������echostr�ַ���
			echo $echoStr;
			exit;
		}
	}
	
	// У���㷨
	private function checkSignature()
	{
		if ($this->token == null)
		{
			// ���û������token��echo���ַ������˳���ʲôҲ����
			echo "";
			exit;
		}
		
		$signature = $_GET["signature"];
		$timestamp = $_GET["timestamp"];
		$nonce = $_GET["nonce"];
		
		$token = $this->token;
		$tmpArr = array($token, $timestamp, $nonce);
		
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode($tmpArr);
		$tmpStr = sha1($tmpStr);
		
		if ($tmpStr == $signature)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	// ��Ϣ�����Լ���Ӧ
	private function responseMsg()
	{
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

		if (!empty($postStr)){
			// libxml_disable_entity_loader(true);
			$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
			$fromUsername = $postObj->FromUserName;
			$toUsername = $postObj->ToUserName;
			$msgType = (string)$postObj->MsgType;
			// $time = time();
			$time = $postObj->CreateTime;
			
			// �ַ���Ϣ
			$this->dispatchMsg($fromUsername, $toUsername, $msgType, $time, $postObj);
			
        }else {
        	echo "";
        	exit;
        }
	}
	
	// ��Ϣ�ַ�
	private function dispatchMsg($openId, $ownerId, $msgType, $time, $postObj)
	{
		// ͨ��MsgType����Ϣ���ͣ�ѡ����Ӧ��ӳ�亯������
		$msgHandler = $this->mapping[$msgType];
		
		// �Ҳ�����Ӧ���͵Ĵ�����ʱ
		// ���""���ַ�����֪ͨ΢�ŷ�����ʲôҲ����
		// Ȼ���˳��ű�����ֹ��ߵĴ���ִ��
		if (!$msgHandler)
		{
			echo "";
			exit;
		}
		
		// ���ض�Ӧ��handler��ִ��
		$file = ROOT . DS . "app" . DS . $this->app . DS . "handler" . DS . $msgHandler . ".class.php";

		if (file_exists($file))
		{
			// �����ļ�
			require_once($file);
		}
		else
		{
			// �ļ�ȱʧ��֪ͨ������ʲôҲ����
			echo "";
			exit;
		}
		
		
		// �õ�hanlder��ʵ������ִ��run����������$param����
		$param = array();
		$handlerClass = ucfirst($msgHandler);
		$dispatch = new $handlerClass($this->app, $openId, $ownerId, $time, $postObj);
		if (method_exists($dispatch, "run"))
		{
			call_user_func_array(array($dispatch, "run"), $param);
		}
		else
		{
			echo "";
			exit;
		}
	}
	
	
	
	
	
}
