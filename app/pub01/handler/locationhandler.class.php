<?php

class Locationhandler
{
	private $app;		// ��ǰ���е�Ӧ��
	
	// ͨ������
	private $openId;	// �û���openId
	private $ownerId;	// ���ں�Id
	private $time;		// ��Ϣ����ʱ��
	
	// �ض�����
	
	/*
	 * ���캯��
	 */
	public function __construct($app, $openId, $ownerId, $time, $postObj)
	{
		$this->app = $app;
		
		$this->openId = $openId;
		$this->ownerId = $ownerId;
		$this->time = $time;
	}
	
	/*
	 * wechat.class.php ����
	 */
	public function run()
	{
		echo "";
		exit;
	}
}
