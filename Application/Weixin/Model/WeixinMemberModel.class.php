<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Weixin\Model;
use Think\Model;
use Com\Wechat;
use Com\WechatAuth;

/**
 * 文档基础模型
 */
class WeixinMemberModel extends Model{

    /* 用户模型自动完成 */
    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
        array('status', 1, self::MODEL_INSERT),
    );
	//微信获取acecess_token
	public function get_access_token($token) {
		static $access_token;
		$access_token = S($token . 'weixin_access_token');
		if ($access_token) {//已缓存，直接使用
			return $access_token;
		} else {//获取access_token
			/*加载WechatAuth*/
			$WechatAuth = new WechatAuth(C('APP_ID'),C('APP_SECRET'));
			$S_accessToken=$WechatAuth->getAccessToken();
			// 缓存数据
			S($token . 'weixin_access_token', $S_accessToken['access_token'], 7200);
			return $access['access_token'];
		}
	}
	//微信用户默认登陆
	public function weixinAuth($url){
		$info = session('info');
		if(!$info){
			$code = I('get.code');
			//应用类
	    	$WechatAuth = new  WechatAuth(C('APP_ID'),C('APP_SECRET'),D("WeixinMember")->get_access_token(C('DATA_AUTH_KEY')));
	    	if(!$code){
			    $weixinurl = $WechatAuth->getRequestCodeURL($url,'STATE','snsapi_base');//静默授权
			    header("location: ".$weixinurl);
	        }else{
				$data = $WechatAuth->getAccessToken('code',$code);
				$openId = $data['openid'];
				$info = D("WeixinMember")->where(array('openid'=>$openId))->find();
				if($info){
					session('info',$info);
					header("location: " .$url);
				}else{
					return $openId;
				}
				
	        }
		}
	}
	//微信获取用户信息
	public function get_weixin_userinfo($url){
		$code = I('get.code');
		//应用类
    	$WechatAuth = new  WechatAuth(C('APP_ID'),C('APP_SECRET'),$this->get_access_token(C('DATA_AUTH_KEY')));
    	if(!$code){
		    $weixinurl = $WechatAuth->getRequestCodeURL($url);//静默授权
		    header("location: ".$weixinurl);
        }else{
			$info = $WechatAuth->getAccessToken('code',$code);
			$openId = $info['openid'];
			$userInfo = $this->where(array('openid'=>$openId))->field('id,openid,nickname,sex,language,city,province,country,headimgurl,privilege')->find();
			
			if(!$userInfo){
				//获取数据并写入
				$userInfo = $WechatAuth->getUserInfo($info['openid']);
				$data = $this->create($userInfo);
				$id = $this->add($data);
				$userInfo['id'] = $id;
				//资金状况
				if($id){
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
			}
        }

	}


}
