<?php

namespace Weixin\Controller;
use Com\Wechat;
use Com\WechatAuth;
/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class IndexController extends HomeController {
	/**
     * 首页
     * 
     * 
     */
    public function index(){
    	$WechatAuth = new WechatAuth(C('APP_ID'),C('APP_SECRET'));
    	//获取用户信息
        $WechatAuth->weixinAuth($url);
		
		$info = session('info');
		if($info != NULL){
			//获取用户账户信息
			$account = D('WeixinAccount')->where('wid = '.$info['id'])->find();
			$this->assign('account',$account);
			$this->assign('is_user',1);
		}else{
			//用户未授权
			cookie('auth_url',C('HOST') . U('Weixin/Index/index',array('id'=>$id)));
			$this->assign('is_user',0);
		}
		
	
		$this->display();
    }
	
	public function member(){
		//获取用户信息
		$userinfo = D('WeixinMember')->get_weixin_userinfo('http://wudi.1bea.com/weixin/Index/member.html');
		$this->assign('userinfo',$userinfo);
		$this->display();
	}

	public function views($id = 0){
		//微信用户检查 是否已经存在该页面
		$WechatAuth = new WechatAuth(C('APP_ID'),C('APP_SECRET'));
		$url = C('HOST') . U('Weixin/Index/views',array('id'=>$id));
		$WechatAuth->weixinAuth($url);
		
		//获取商品信息
		$goodsModel = D('Goods');
		$info = $goodsModel->find($id);
		$content = D('GoodsContent')->where('goods_id = '.$id)->find();
		$info['content'] = $content['content'];
		$this->assign('info',$info);
		
		//获取拍卖信息
		$auction = D('GoodsAuction')->where('goods_id = '.$id)->find();
		$this->assign('auction',$auction);
		
		$info = session('info');
		if($info != NULL){
			//获取用户账户信息
			$account = D('WeixinAccount')->where('wid = '.$info['id'])->find();
			$this->assign('account',$account);
			
			//读取当前用户出价此时
			$auctionDatas = D("AuctionData")->where(array('wid'=>$info['id'], 'goods_id'=>$id))->select();
			$this->assign('auctionDatas',$auctionDatas);
			
			$auctionCount = D("AuctionData")->where(array('wid'=>$info['id'], 'goods_id'=>$id))->count();
			$this->assign('auctionCount',$auctionCount);
	
			
			$this->assign('is_user',1);
		}else{
			//用户未授权
			$this->assign('is_user',0);
		}
		
		
		
		
		
		//获取图片集
		$gallerys = D('GoodsGallery')->where(array('goods_id = ' .$id))->getField('img_url',true);

		foreach($gallerys as &$val){
			$val = '/Uploads/Goods/Gallery/'.$val;
		}
		unset($val);
		$this->assign('gallerys',$gallerys);
		$imgList = array('imgList'=>$gallerys,'valueId'=>'1');
		$this->assign('propValueImgList',json_encode($imgList));
		//金币和米粒前端
		$templates = 'views_'.$auction['auction_type'];
		$this->display($templates);
	}
	public function doAuction(){
		if(IS_POST){
			$info = session('info');

			if($info){
				$wid = $info['id'];
				$number = I("post.number");
				$goods_id = I("post.goods_id");
				$consume = I("post.consume");
				$auction_type = I("post.auction_type");
				
				$nums = explode(',', $number);
				//判断用户 是否已经买过该数字
				$map['wid'] = $wid;
				$map['goods_id'] = $goods_id;
				$map['number'] = array('in', $nums);
				$areadyNum = D("AuctionData")->where($map)->getField('number',true);
				
				//获取拍卖信息
				$auction = D('GoodsAuction')->where('goods_id = '.$goods_id)->find();
				
				//获取用户账户信息
				$account = D('WeixinAccount')->where('wid = '.$info['id'])->find();
				
				$already = array();
				$yes = array();
			    foreach($nums as $val){
			    	if(in_array($val, $areadyNum)){
			    		array_push($already,$val);
			    	}else{
			    		array_push($yes,$val);
			    	}
			    }
			
				$data = array();
				foreach($yes as $key=>$val){
					$data[$key]['wid'] = $wid;
					$data[$key]['number'] = $val;
					$data[$key]['goods_id'] = $goods_id;
					$data[$key]['consume'] = $auction['consume'];
					$data[$key]['auction_type'] = $auction['auction_type'];
					$data[$key]['create_time'] = NOW_TIME;
				} 
				
				//计算消耗的米粒 判断账号米粒是否超过
				$mili = count($yes) * $auction['consume'];
				if($mili>$account['mili']){
					$result = array(
						'status'=>0,
						'info'=>'账户米粒不足'
					);
					$this->ajaxReturn($result,'json');
				}
				
				//计算购买米粒数量是否超过竞拍商品最大值
				$map2['goods_id'] = $goods_id;
				$areadyCount = D("AuctionData")->where($map2)->count;
				if((count($yes)+$areadyCount)>$auction['total']){
					$result = array(
						'status'=>0,
						'info'=>'您已经结束了',
					);
					$this->ajaxReturn($result,'json');
				}
				
				$res = D("AuctionData")->addAll($data);
				if($res){
					$result = array(
						'status'=>1,
						'already'=>$already,
						'yes'=>$yes,
					);
				    $this->ajaxReturn($result,'json');
				}
			}
		}
	}
}
