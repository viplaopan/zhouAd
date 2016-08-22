<?php

namespace Weixin\Controller;
use Com\Wechat;
use Com\WechatAuth;
/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class AuthController extends HomeController {
	/**
     * 首页
     * 
     * 
     */
    public function index(){
    	$authurl = cookie('auth_url');
    	//授权用户
    	$code = I('get.code');
		//应用类
    	$WechatAuth = new  WechatAuth(C('APP_ID'),C('APP_SECRET'),D("WeixinMember")->get_access_token(C('DATA_AUTH_KEY')));
    	if(!$code){
		    $weixinurl = $WechatAuth->getRequestCodeURL(C('HOST') . U('Weixin/Auth/index'));//静默授权
		    header("location: ".$weixinurl);
        }else{
			$info = $WechatAuth->getAccessToken('code',$code);
			
			$userinfo = D("WeixinMember")->where(array('openid'=>$info['openid']))->find();
			if($userinfo){
				session('info',$userinfo);
				header("location: ".$authurl);
				die;
			}

			//获取数据并写入
			$userinfo = $WechatAuth->getUserInfo($info['openid']);

			$data = D("WeixinMember")->create($userinfo);
			$id = D("WeixinMember")->add($data);

			//资金状况
			if($id){
				$userinfo['id'] = $id;
				$account['wid'] = $id;
				$account['mili'] = 5;//默认赠送5个金币
				$res = D('WeixinAccount')->add($account);
				
				//添增加米粒记录
				if($res){
					$log['wid'] = $id;
					$log['log_type'] = 1;//注册赠送金币
					$log['type'] = 1;// 1:米粒 2：金币
					$log['count'] = 5;
					$log['desc'] = '第一次进入赠送';
					D('WeixinAccountLog')->add($log);
				}
				
			}
			session('info',$userinfo);
			header("location: ".$authurl);
        }
		
    }

}
