<?php

class Locationhandler
{
	private $app;		// 当前运行的应用
	
	// 通用属性
	private $openId;	// 用户的openId
	private $ownerId;	// 公众号Id
	private $time;		// 消息创建时间
	
	// 特定属性
	
	/*
	 * 构造函数
	 */
	public function __construct($app, $openId, $ownerId, $time, $postObj)
	{
		$this->app = $app;
		
		$this->openId = $openId;
		$this->ownerId = $ownerId;
		$this->time = $time;
	}
	
	/*
	 * wechat.class.php 调用
	 */
	public function run()
	{
		echo "";
		exit;
	}
}
