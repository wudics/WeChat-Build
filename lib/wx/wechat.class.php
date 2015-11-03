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
	
	/*
	 * ����token
	 */
	public function setToken($token)
	{
		$this->token = $token;
	}
	
	/*
	 * ����appid
	 */
	public function setAppid($appid)
	{
		$this->appid = $appid;
	}
	
	/*
	 * ����appsecret
	 */
	public function setAppsecret($appsecret)
	{
		$this->appsecret = $appsecret;
	}

	/*
	 * ����mapping
	 */
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
	
	/*
	 * ��ӦУ����Ϣ
	 */
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
	
	/*
	 * У���㷨
	 */
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
	
	/*
	 * ��Ϣ�����Լ���Ӧ
	 */
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
	
	/*
	 * ��Ϣ�ַ�
	 * �������û�openId�����ں�Id����Ϣ���͡���Ϣ����ʱ�䡢����ʵ��
	 */
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
	
	
	/*
	 * ��ȡ�����ظ�xml���ݣ�����text
	 * �������û�openId�����ں�Id����Ϣ����ʱ�䡢�ظ�����
	 */
	public static function getRspText($openId, $ownerId, $createat, $rspcontent)
	{  	
		$tpl = "<xml>
				<ToUserName><![CDATA[%s]]></ToUserName>
				<FromUserName><![CDATA[%s]]></FromUserName>
				<CreateTime>%s</CreateTime>
				<MsgType><![CDATA[text]]></MsgType>
				<Content><![CDATA[%s]]></Content>
				</xml>";
				
		return sprintf($tpl, $openId, $ownerId, $createat, $rspcontent);
	}
	
	/*
	 * ��ȡ�����ظ�xml���ݣ�����image
	 * �������û�openId�����ں�Id����Ϣ����ʱ�䡢ͼƬý��Id
	 */
	public static function getRspImage($openId, $ownerId, $createat, $mediaId)
	{
		$tpl = "<xml>
				<ToUserName><![CDATA[%s]]></ToUserName>
				<FromUserName><![CDATA[%s]]></FromUserName>
				<CreateTime>%s</CreateTime>
				<MsgType><![CDATA[image]]></MsgType>
				<Image>
				<MediaId><![CDATA[%s]]></MediaId>
				</Image>
				</xml>";
		return sprintf($tpl, $openId, $ownerId, $createat, $mediaId);
	}
	
	/*
	 * ��ȡ�����ظ�xml���ݣ�����voice
	 * �������û�openId�����ں�Id����Ϣ����ʱ�䡢����ý��Id
	 */
	public static function getRspVoice($openId, $ownerId, $createat, $mediaId)
	{
		$tpl = "<xml>
				<ToUserName><![CDATA[%s]]></ToUserName>
				<FromUserName><![CDATA[%s]]></FromUserName>
				<CreateTime>%s</CreateTime>
				<MsgType><![CDATA[voice]]></MsgType>
				<Voice>
				<MediaId><![CDATA[%s]]></MediaId>
				</Voice>
				</xml>";
		return sprintf($tpl, $openId, $ownerId, $createat, $mediaId);
	}
	
	/*
	 * ��ȡ�����ظ�xml���ݣ�����video
	 * �������û�openId�����ں�Id����Ϣ����ʱ�䡢��Ƶý��Id����Ƶ���⡢��Ƶ����
	 */
	public static function getRspVideo($openId, $ownerId, $createat, $mediaId, $title, $desc)
	{
		$tpl = "<xml>
				<ToUserName><![CDATA[%s]]></ToUserName>
				<FromUserName><![CDATA[%s]]></FromUserName>
				<CreateTime>%s</CreateTime>
				<MsgType><![CDATA[video]]></MsgType>
				<Video>
				<MediaId><![CDATA[%s]]></MediaId>
				<Title><![CDATA[%s]]></Title>
				<Description><![CDATA[%s]]></Description>
				</Video> 
				</xml>";
		return sprintf($tpl, $openId, $ownerId, $createat, $mediaId, $title, $desc);
	}
	
	/*
	 * ��ȡ�����ظ�xml���ݣ�����music
	 * �������û�openId�����ں�Id����Ϣ����ʱ�䡢���⡢����������url����������url������ͼId
	 */
	public static function getRspMusic($openId, $ownerId, $createat, $title, $desc, $musicUrl, $hdMusicUrl, $thumbId)
	{
		$tpl = "<xml>
				<ToUserName><![CDATA[%s]]></ToUserName>
				<FromUserName><![CDATA[%s]]></FromUserName>
				<CreateTime>%s</CreateTime>
				<MsgType><![CDATA[music]]></MsgType>
				<Music>
				<Title><![CDATA[%s]]></Title>
				<Description><![CDATA[%s]]></Description>
				<MusicUrl><![CDATA[%s]]></MusicUrl>
				<HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
				<ThumbMediaId><![CDATA[%s]]></ThumbMediaId>
				</Music>
				</xml>";
		return sprintf($tpl, $openId, $ownerId, $createat, $title, $desc, $musicUrl, $hdMusicUrl, $thumbId);
	}
	
	/*
	 * ��ȡ�����ظ�xml���ݣ�����news
	 * �������û�openId�����ں�Id����Ϣ����ʱ�䡢ͼ����Ŀ
	 * ˵����$items = array();
	 *       	$item = array();
	 *			$item["title"] = "Title";
	 *			$item["desc"] = "Description";
	 *			$item["picUrl"] = "http://wudics.8800.org/logo.png";
	 *			$item["url"] = "http://wudics.8800.org";
	 *		 $items[] = $item;
	 */
	public static function getRspNews($openId, $ownerId, $createat, $items)
	{
		// <xml>
		// <ToUserName><![CDATA[toUser]]></ToUserName>
		// <FromUserName><![CDATA[fromUser]]></FromUserName>
		// <CreateTime>12345678</CreateTime>
		// <MsgType><![CDATA[news]]></MsgType>
		// <ArticleCount>2</ArticleCount>
		// <Articles>
		// <item>
		// <Title><![CDATA[title1]]></Title> 
		// <Description><![CDATA[description1]]></Description>
		// <PicUrl><![CDATA[picurl]]></PicUrl>
		// <Url><![CDATA[url]]></Url>
		// </item>
		// <item>
		// <Title><![CDATA[title]]></Title>
		// <Description><![CDATA[description]]></Description>
		// <PicUrl><![CDATA[picurl]]></PicUrl>
		// <Url><![CDATA[url]]></Url>
		// </item>
		// </Articles>
		// </xml>
		$articlecount = count($items);
		if ($articlecount == 0)
		{
			return "";
		}
		
		// ģ��
		$tpl = "<xml>
				<ToUserName><![CDATA[%s]]></ToUserName>
				<FromUserName><![CDATA[%s]]></FromUserName>
				<CreateTime>%s</CreateTime>
				<MsgType><![CDATA[news]]></MsgType>
				<ArticleCount>%s</ArticleCount>
				<Articles>
				%s
				</Articles>
				</xml>";
		
		// itemģ��
		$tpl_item = "<item>
					 <Title><![CDATA[%s]]></Title> 
					 <Description><![CDATA[%s]]></Description>
					 <PicUrl><![CDATA[%s]]></PicUrl>
					 <Url><![CDATA[%s]]></Url>
					 </item>";
		
		$itemStr = "";
		for ($i = 0; $i < $articlecount; $i++)
		{
			$itemStr .= sprintf($tpl_item, $items[$i]["title"], $items[$i]["desc"], $items[$i]["picUrl"], $items[$i]["url"]);
		}
		
		return sprintf($tpl, $openId, $ownerId, $createat, $articlecount, $itemStr);
	}
	
	/*
	 * request
	 */
	public static function request($url, $data = null)
	{
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		if (!empty($data)){
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($curl);
		curl_close($curl);
		return $output;
	}
}
