<?php
/**
 * Created by PhpStorm.
 * User: caipeichao
 * Date: 14-3-14
 * Time: AM10:59
 */

namespace Admin\Controller;

use Admin\Builder\AdminListBuilder;
use Admin\Builder\AdminConfigBuilder;
use Admin\Builder\AdminSortBuilder;
use Com\Wechat;
use Com\WechatAuth;

class ShopController extends AdminController
{
	//菜单列表
    public function index($page = 1, $r = 20)
    {
    	$goodsModel = D('Goods');  
		$map['status'] = 1;
        $lists = $goodsModel->where($map)->page($page, $r)->order('id desc')->select();
		foreach($lists as &$val){
			//竞拍信息
			if($val['goods_type'] == 1){
				//竞拍商品属性					
				$val['do'] = '<a href="' .U('edit',array('id'=>$val['id'])). '">编辑</a>&nbsp;<a href="' .U('auction',array('goods_id'=>$val['id'])). '">竞拍信息</a>';
			}else{
				$val['do'] ='<a href="' .U('edit',array('id'=>$val['id'])). '">编辑</a>';
			}
		}
		unset($val);

    	//显示页面
        $builder = new AdminListBuilder();
        $builder->title('商品列表')
            ->buttonNew(U('edit'))
			->setStatusUrl(U('setStatus'))
            ->buttonDelete()
            ->keyId()
			->keyText('goods_name', '商品名称')
            ->keyText('goods_type', '商品类型')            
			->keyCreateTime()
            ->keyStatus()
			->keyHtml('do','操作')
            ->data($lists)
            ->pagination($totalCount, $r)
            ->display();
    }
	public function edit($id = 0){
		$isEdit = $id?1:0;
		$goodsModel = D('Goods');   
		if(IS_POST){
			if($isEdit){
				$data = $goodsModel->create();
				$res = $goodsModel->where('id = '. $id)->save($data);
			}else{
				$data = $goodsModel->create();
				$data['type'] = I("post.type2");
				$res = $goodsModel->add($data);
				$id = $res;
			}
			if($res){
				//编辑时删除原来图片
				if($isEdit){
					D('GoodsGallery')->where('goods_id = '. $id)->delete();
					//修改商品内容
					$con['content'] = I("post.content");
					D('GoodsContent')->where('goods_id = '.$id)->save($con);
					//添加图片集合
					$gallery = I("post.gallery");
					$gallery_data = array();
					foreach($gallery as $val){
						$gallery_data['img_url'] = $val;
						$gallery_data['goods_id'] = $id;
						D('GoodsGallery')->add($gallery_data); 
					}
					
					//竞拍信息
					if($data['goods_type'] == 1){
						//竞拍商品属性					
						$this->success('提交成功',U('Shop/auction',array('goods_id'=>$id)));
					}else{
						$this->success('提交成功');
					}
				}else{
					//添加内容
					$con['goods_id'] = $id;
					$con['content'] = I("post.content");
					D('GoodsContent')->add($con);
					
					//添加图片集合
					$gallery = I("post.gallery");
					$gallery_data = array();
					foreach($gallery as $val){
						$gallery_data['img_url'] = $val;
						$gallery_data['goods_id'] = $id;
						D('GoodsGallery')->add($gallery_data); 
					}
					
					//竞拍信息
					if($data['goods_type'] == 1){
						//竞拍商品属性					
						$this->success('提交成功',U('Shop/auction',array('goods_id'=>$id)));
					}
					
					
				}
				
				
				
			}else{
				$this->error('遇到错误');
			}
		}else{
			
			if($isEdit){
				$data = $goodsModel->where('id = '. $id)->find();				
				$con = D('GoodsContent')->where('goods_id = '.$id)->find();
				$data['content'] = $con['content'];
			}else{
				$data['status'] = 1;
			}
			
			//商品分组
			$this->assign('group_list',array(
				1=>array('id','goods_name','shop_price','goods_type','cover','attr','images','status'),
				2=>array('content')
			));
			//显示页面
	        $builder = new AdminConfigBuilder();
	        $builder->title($isEdit ? '编辑商品'. $data['goods_name'] : '添加商品')
	            ->keyId()
	            ->keyText('goods_name', '商品名称')
				->keyText('shop_price', '价格')
				->keySelect('goods_type', '商品类型', '',C('GOODS_TYPE'))
				->keyImage('cover', '封面')
				->keyFree('attr','商品属性','goods-attr')
				->keyImageGallery('images', '商品图片')
				->keyEditor('content', '详情')
	            ->keyStatus()
	            ->data($data)
	            ->buttonSubmit(U('edit'))->buttonBack()
	            ->display('Shop/admin_config');
		}
		
	}
	public function auction($goods_id = 0){
		if(IS_POST){
			$info = D('GoodsAuction')->where('goods_id = '. $goods_id)->find();
			$data = D('GoodsAuction')->create();
			if(!$info){
				if($data){
					D('GoodsAuction')->add($data);
					$this->success("提交成功",U('Shop/index'));
				}else{
					$this->error("报错");
				}
			}else{
				if($data){
					D('GoodsAuction')->save($data);
					$this->success("提交成功",U('Shop/index'));
				}else{
					$this->error("报错");
				}
			}
			
			
			
		}else{
			$goodsModel = D('Goods');   
			$info = $goodsModel->where('id = '. $goods_id)->find();
			
			//读取信息
			$data = D('GoodsAuction')->where("goods_id = $goods_id")->find();
			$data['goods_id'] = $goods_id;
			//显示页面
	        $builder = new AdminConfigBuilder();
	        $builder->title($info['goods_name']."-竞拍信息")
	            ->keyId()
				->keyReadOnly('goods_id', '商品ID')
				->keySelect('auction_type', '竞拍类型', '',C('AUCTION_TYPE'))
				->keyText('consume', '消耗次数','竞拍一次消耗的米粒或金币')
				->keyText('total', '总需人次','满足该人次，即抽取1人获得该商品')
				->keyTime('start_time','开始时间')
				->keyTime('end_time','结束时间')
	            ->data($data)
	            ->buttonSubmit(U('auction'))->buttonBack()
	            ->display();
		}
		
	}
    public function setStatus($ids, $status)
    {
        $builder = new AdminListBuilder();
        $builder->doSetStatus('Goods', $ids, $status);
    }
}