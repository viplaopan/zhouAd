<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Admin\Controller;
use User\Api\UserApi;

/**
 * 后台首页控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class PublicController extends \Think\Controller {
	public function getJs($id = 0){
		$info = D('Ad')->find($id); 
		echo 'document.writeln("<div class=\'\'><a href=\'' .$info['url']. '\'><img src=\'http://ad.1bea.com/Uploads/Ad/' . $info['image'] . '\'></a></div>")';
		die;
		$address = explode(',', $info['noallow']);
		$jsstr = '';
		foreach($address as $vo){
			$jsstr .='city != "' . $vo . '" && ';  
		}
		$jsstr .="city !=''";
		// 新浪根据ip获取地址  
	   	echo "var province = '' ;";  
	    echo "var city = '' ;  ";
	    echo 'jQuery.getScript("http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=js",function(){  ';
	    echo 'province = remote_ip_info["province"];';
	 	echo 'city = remote_ip_info["city"];';
			echo 'if(' .$jsstr. '){';
				echo 'document.writeln("<div class=\'\'><a href=\'' .$info['url']. '\'><img src=\'http://ad.1bea.com/Uploads/Ad/' . $info['image'] . '\'></a></div>")';
			echo '}';
	    echo '}) ;';
	}
    /**
     * 后台用户登录
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function login($username = null, $password = null, $verify = null){
        if(IS_POST){
//          /* 检测验证码 TODO: */
//          if(!check_verify($verify)){
//              $this->error('验证码输入错误！');
//          }

            /* 调用UC登录接口登录 */
            $User = new UserApi;
            $uid = $User->login($username, $password);
            if(0 < $uid){ //UC登录成功
                /* 登录用户 */
                $Member = D('Member');
                if($Member->login($uid)){ //登录用户
                    //TODO:跳转到登录前页面
                    $this->success('登录成功！', U('Index/index'));
                } else {
                    $this->error($Member->getError());
                }

            } else { //登录失败
                switch($uid) {
                    case -1: $error = '用户不存在或被禁用！'; break; //系统级别禁用
                    case -2: $error = '密码错误！'; break;
                    default: $error = '未知错误！'; break; // 0-接口参数错误（调试阶段使用）
                }
                $this->error($error);
            }
        } else {
            if(is_login()){
                $this->redirect('Index/index');
            }else{
                /* 读取数据库中的配置 */
                $config	=	S('DB_CONFIG_DATA');
                if(!$config){
                    $config	=	D('Config')->lists();
                    S('DB_CONFIG_DATA',$config);
                }
                C($config); //添加配置
                
                $this->display();
            }
        }
    }

    /* 退出登录 */
    public function logout(){
        if(is_login()){
            D('Member')->logout();
            session('[destroy]');
            $this->success('退出成功！', U('login'));
        } else {
            $this->redirect('login');
        }
    }

    public function verify(){
        $verify = new \Think\Verify();
        $verify->entry(1);
    }

}
