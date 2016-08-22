<?php
function get_access_token($token) {
	static $access_token;
	$access_token = S($token . 'weixin_access_token');
	if ($access_token) {//已缓存，直接使用
		return $access_token;
	} else {//获取access_token
		/*加载WechatAuth*/
		$WechatAuth = new  \Com\WechatAuth(C('APP_ID'),C('APP_SECRET'));
		$S_accessToken=$WechatAuth->getAccessToken();
		// 缓存数据
		S($token . 'weixin_access_token', $S_accessToken['access_token'], 7200);
		return $access['access_token'];
	}
}


function get_weixin_userinfo($url){
	$code = I('get.code');
	//从cookie中读取用户信息
    $userInfo = cookie('userInfo');
	
	//用户信息不存在 用户授权登陆模块
    if($userInfo == NULL){
    	//应用类
    	$WechatAuth = new  \Com\WechatAuth(C('APP_ID'),C('APP_SECRET'),get_access_token(C('DATA_AUTH_KEY')));
    	if(!$code){
		    $weixinurl = $WechatAuth->getRequestCodeURL($url);
		    header("location: ".$weixinurl);
        }else{
			$info = $WechatAuth->getAccessToken('code',I('get.code'));
			$userInfo = $WechatAuth->getUserInfo($info['openid']);
			cookie('userInfo',$userInfo);
        }
    }
	dump($userInfo);
	return $userInfo;
}
/**
 * 模拟post进行url请求
 * @param string $url
 * @param string $param
 */
function request_post($url = '', $param = '') {
    if (empty($url) || empty($param)) {
        return false;
    }
    
    $postUrl = $url;
    $curlPost = $param;
    $ch = curl_init();//初始化curl
    curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
    curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
    curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
    curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
    $data = curl_exec($ch);//运行curl
    curl_close($ch);
    
    return $data;
}