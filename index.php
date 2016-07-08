<?php
/**
  * wechat babyshow
  */

//define your token
define("TOKEN", "cgkwll");
$wechatObj = new wechatCallbackApi();
if (isset($_GET["echostr"])) {
	$wechatObj->valid();
} else {
	$wechatObj->responseMsg();
}

class wechatCallbackApi
{
	public function valid()
    {
        $echoStr = $_GET["echostr"];

        //valid signature , option
        if($this->checkSignature()){
			// if no header , token check fail...
			header('content-type:text');
        	echo $echoStr;
        	exit;
        }
    }

    public function responseMsg()
    {
		//get post data, May be due to the different environments
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

      	//extract post data
		if (!empty($postStr)){
                /* libxml_disable_entity_loader is to prevent XML eXternal Entity Injection,
                   the best way is to check the validity of xml by yourself */
                libxml_disable_entity_loader(true);
              	$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                $fromUsername = $postObj->FromUserName;
                $toUsername = $postObj->ToUserName;
                $keyword = trim($postObj->Content);
                $time = time();
                $textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							<FuncFlag>0</FuncFlag>
							</xml>";             
				if(!empty( $keyword ))
                {
					//complex spaces change into one space
					$keyword = preg_replace("/[\s]+/"," ",$keyword);
					$searchPattern = explode(" ",$keyword);
					//delete all spaces
					$noSpaceKeyword = str_replace(' ', '', $keyword);
					if(!ereg("[^0-9]",$noSpaceKeyword) && count($searchPattern) == 3){
						$msgType = "text";
						$getType = $searchPattern[0];
						$getWeek = $searchPattern[1];
						$getDay = $searchPattern[2];
						$contentStr = "http://babyshownow.applinzi.com/test.html";
						$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
					} else {
						$msgType = "text";
						$inputStr = "若获取内容请按如下格式输入：\n【n w d】\nn:获取内容种类（1~3）\nm:孕周数（1~40）\nn:孕周数的第几天（1~6）\n\n";
						$inputSample = "例如：\n输入： 1 24 2\n即：获取孕24周+2天的胎教故事"; 
						$contentStr = "Welcome to 宝儿秀's world!\n\n您可以获取如下内容：\n1.胎教故事\n2.胎教音乐\n3.孕妈食谱\n\n".$inputStr.$inputSample;
						$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
					}
                	echo $resultStr;
                }else{
                	echo "Input something...";
                }

        }else {
        	echo "";
        	exit;
        }
    }
		
	private function checkSignature()
	{
        // you must define TOKEN by yourself
        if (!defined("TOKEN")) {
            throw new Exception('TOKEN is not defined!');
        }
        
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        		
		$token = TOKEN;
		$tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}
}

?>