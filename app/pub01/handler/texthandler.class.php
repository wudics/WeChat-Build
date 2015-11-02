<?php

class Texthandler
{
	private $app;		// ��ǰ���е�Ӧ��
	
	private $openId;	// �û���openId
	private $ownerId;	// ���ں�Id
	private $time;		// ��Ϣ����ʱ��
	private $content;	// �ı�����
	private $msgId;		// ��ϢId
	
	// ���캯��
	public function __construct($app, $openId, $ownerId, $time, $postObj)
	{
		$this->app = $app;
		
		$this->openId = $openId;
		$this->ownerId = $ownerId;
		$this->time = $time;
		$this->content = $postObj->Content;
		$this->msgId = $postObj->MsgId;
	}
	
	// wechat.class.php ����
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