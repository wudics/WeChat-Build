<?php

class Texthandler
{
	private $app;		// 当前运行的应用
	
	// 通用属性
	private $openId;	// 用户的openId
	private $ownerId;	// 公众号Id
	private $time;		// 消息创建时间
	
	// 特定属性
	private $content;	// 文本内容
	private $msgId;		// 消息Id
	
	/*
	 * 构造函数
	 */
	public function __construct($app, $openId, $ownerId, $time, $postObj)
	{
		$this->app = $app;
		
		$this->openId = $openId;
		$this->ownerId = $ownerId;
		$this->time = $time;

		$this->content = $postObj->Content;
		$this->msgId = $postObj->MsgId;
	}
	
	/*
	 * wechat.class.php 调用
	 */
	public function run()
	{
		// echo "running...\r\n";
		// echo $this->app . "\r\n";
		// echo $this->openId . "\r\n";
		// echo $this->ownerId . "\r\n";
		// echo $this->time . "\r\n";
		// echo $this->content . "\r\n";
		// echo $this->msgId . "\r\n";
		// $rspStr = Wechat::getRspText($this->openId, $this->ownerId, time(), $this->content);
		// echo $rspStr;
		// exit;
		$items = array();
		
		$item01 = array();
		$item01["title"] = "It's a title.";
		$item01["desc"] = "with a long description, lol.";
		$item01["picUrl"] = "https://ss0.bdstatic.com/5aV1bjqh_Q23odCf/static/superman/img/logo/logo_white_fe6da1ec.png";
		$item01["url"] = "http://www.baidu.com/";
		$items[] = $item01;
		
		$item02 = array();
		$item02["title"] = "Gochisa";
		$item02["desc"] = "This name means a lot.";
		$item02["picUrl"] = "https://ss0.bdstatic.com/5aV1bjqh_Q23odCf/static/superman/img/logo/logo_white_fe6da1ec.png";
		$item02["url"] = "http://www.baidu.com/";
		$items[] = $item02;
		
		$rspStr = Wechat::getRspNews($this->openId, $this->ownerId, time(), $items);
		echo $rspStr;
		exit;
	}
}