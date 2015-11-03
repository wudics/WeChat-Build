<?php

class Wechat
{	
	// 静态Wechat实例
	public static $wechat = null;

	// App
	private $app = null;
	
	// Token
	private $token = null;
	private $appid = null;
	private $appsecret = null;
	
	/*
		mapping数据结构，对应一个类型的处理方法，在app/appname/handler里
		mapping = array();
		mapping["text"] = "texthandler";	// texthandler.class.php
		mapping["voidc"] = "voicehandler";	// voice.class.php
	*/
	private $mapping = null;	// 消息处理映射表
	
	// 通过getWechat可获取静态实例
	public static function getWechat($app)
	{
		if (self::$wechat == null)
		{
			self::$wechat = new Wechat($app);
		}
		return self::$wechat;
	}
	
	// 构造函数
	public function __construct($app)
	{
		// AppName
		$this->app = $app;
		
		// 默认消息类型处理
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
	 * 设置token
	 */
	public function setToken($token)
	{
		$this->token = $token;
	}
	
	/*
	 * 设置appid
	 */
	public function setAppid($appid)
	{
		$this->appid = $appid;
	}
	
	/*
	 * 设置appsecret
	 */
	public function setAppsecret($appsecret)
	{
		$this->appsecret = $appsecret;
	}

	/*
	 * 设置mapping
	 */
	public function setMapping($mapping)
	{
		$this->mapping = $mapping;
	}
	
	// Wechat执行回调处理
	public function process()
	{
		// 通过判断echostr是否存在，确定是否需要valid
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
	 * 回应校验信息
	 */
	private function valid()
	{
		$echoStr = $_GET["echostr"];
		
		if ($this->checkSignature())
		{
			// 校验正确，原样返回echostr字符串
			echo $echoStr;
			exit;
		}
	}
	
	/*
	 * 校验算法
	 */
	private function checkSignature()
	{
		if ($this->token == null)
		{
			// 如果没有设置token则echo空字符串后退出，什么也不做
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
	 * 消息处理以及回应
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
			
			// 分发消息
			$this->dispatchMsg($fromUsername, $toUsername, $msgType, $time, $postObj);
			
        }else {
        	echo "";
        	exit;
        }
	}
	
	/*
	 * 消息分发
	 * 参数：用户openId、公众号Id、消息类型、消息创建时间、数据实体
	 */
	private function dispatchMsg($openId, $ownerId, $msgType, $time, $postObj)
	{
		// 通过MsgType的消息类型，选择相应的映射函数处理
		$msgHandler = $this->mapping[$msgType];
		
		// 找不到对应类型的处理方法时
		// 输出""空字符串，通知微信服务器什么也不做
		// 然后退出脚本，防止后边的代码执行
		if (!$msgHandler)
		{
			echo "";
			exit;
		}
		
		// 加载对应的handler并执行
		$file = ROOT . DS . "app" . DS . $this->app . DS . "handler" . DS . $msgHandler . ".class.php";

		if (file_exists($file))
		{
			// 加载文件
			require_once($file);
		}
		else
		{
			// 文件缺失，通知服务器什么也不做
			echo "";
			exit;
		}
		
		
		// 得到hanlder类实例，并执行run方法，传入$param参数
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
	 * 获取被动回复xml内容，类型text
	 * 参数：用户openId、公众号Id、消息创建时间、回复内容
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
	 * 获取被动回复xml内容，类型image
	 * 参数：用户openId、公众号Id、消息创建时间、图片媒体Id
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
	 * 获取被动回复xml内容，类型voice
	 * 参数：用户openId、公众号Id、消息创建时间、语音媒体Id
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
	 * 获取被动回复xml内容，类型video
	 * 参数：用户openId、公众号Id、消息创建时间、视频媒体Id、视频标题、视频描述
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
	 * 获取被动回复xml内容，类型music
	 * 参数：用户openId、公众号Id、消息创建时间、标题、描述、音乐url、高清音乐url、缩略图Id
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
	 * 获取被动回复xml内容，类型news
	 * 参数：用户openId、公众号Id、消息创建时间、图文条目
	 * 说明：$items = array();
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
		
		// 模板
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
		
		// item模板
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
