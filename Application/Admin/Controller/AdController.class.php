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
use Com\IpLocation;

class AdController extends AdminController
{
	//菜单列表
    public function index($page = 1, $r = 20)
    {
$addip = get_client_ip();
$Ip = new IpLocation('UTFWry.dat'); // 实例化类 参数表示IP地址库文件
$area = $Ip->getlocation($addip); // 
dump($addip);
    	$adModel = D('Ad');  
		$map['status'] = 1;
        $lists = $adModel->where($map)->page($page, $r)->order('id desc')->select();


    	//显示页面
        $builder = new AdminListBuilder();
        $builder->title('商品列表')
            ->buttonNew(U('edit'))
			->setStatusUrl(U('setStatus'))
            ->buttonDelete()
            ->keyId()
			->keyText('name', '广告名称')
            ->keyText('noallow', '禁止城市')            
			->keyCreateTime()
            ->keyStatus()
			->keyDoActionEdit('edit?id=###')
            ->data($lists)
            ->pagination($totalCount, $r)
            ->display();
    }
	public function edit($id = 0){
		$isEdit = $id?1:0;
		$adModel = D('Ad');   
		if(IS_POST){
			if($isEdit){
				$data = $adModel->create();
				$res = $adModel->where('id = '. $id)->save($data);
			}else{
				$data = $adModel->create();
				$res = $adModel->add($data);
				$id = $res;
			}
			if($res){
				$this->success('操作成功',U('index'));
			}else{
				$this->error('遇到错误');
			}
		}else{
			
			if($isEdit){
				$data = $adModel->where('id = '. $id)->find();				

			}else{
				$data['status'] = 1;
			}
			

			//显示页面
	        $builder = new AdminConfigBuilder();
	        $builder->title($isEdit ? '编辑广告' : '添加广告')
	            ->keyId()
	            ->keyText('name', '广告名称')
				->keyTextArea('noallow', '静止城市','逗号隔开')
				->keyText('url', '广告链接')
				->keyImage('cover', '广告图片')
	            ->keyStatus()
	            ->data($data)
	            ->buttonSubmit(U('edit'))->buttonBack()
	            ->display();
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