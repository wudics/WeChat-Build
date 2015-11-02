<?php

class Texthandler
{
	private $app;		// 当前运行的应用
	
	private $openId;	// 用户的openId
	private $ownerId;	// 公众号Id
	private $time;		// 消息创建时间
	private $content;	// 文本内容
	private $msgId;		// 消息Id
	
	// 构造函数
	public function __construct($app, $openId, $ownerId, $time, $postObj)
	{
		$this->app = $app;
		
		$this->openId = $openId;
		$this->ownerId = $ownerId;
		$this->time = $time;
		$this->content = $postObj->Content;
		$this->msgId = $postObj->MsgId;
	}
	
	// wechat.class.php 调用
	public function run()
	{
		echo "running...\r\n";
		echo $this->app . "\r\n";
		echo $this->openId . "\r\n";
		echo $this->ownerId . "\r\n";
		echo $this->time . "\r\n";
		echo $this->content . "\r\n";
		echo $this->msgId . "\r\n";
	}
}