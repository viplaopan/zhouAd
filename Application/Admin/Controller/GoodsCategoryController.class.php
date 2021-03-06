<?php
/**
 * Created by PhpStorm.
 * User: caipeichao
 * Date: 14-3-14
 * Time: AM10:59
 * category 分类处理
 */

namespace Admin\Controller;

use Admin\Builder\AdminListBuilder;
use Admin\Builder\AdminConfigBuilder;
use Admin\Builder\AdminSortBuilder;
use Com\Wechat;
use Com\WechatAuth;

class GoodsCategoryController extends AdminController
{
	//分类列表
    public function index($page = 1, $r = 20)
    {
    	$CategoryModel = D('GoodsCategory');  
		$map['status'] = 1;
        $lists = $CategoryModel->where($map)->page($page, $r)->order('id desc')->select();

    	//显示页面
        $builder = new AdminListBuilder();
        $builder->title('商品列表')
            ->buttonNew(U('edit'))
			->setStatusUrl(U('setStatus'))
            ->buttonDelete()
            ->keyId()
			->keyText('name', '分类名称')
			->keyDoActionEdit('edit?id=###')
			->keyDoActionEdit('attribute?cid=###','添加扩展属性')
            ->data($lists)
            ->pagination($totalCount, $r)
            ->display();
    }
	public function edit($id = 0){
		$isEdit = $id? 1: 0;
		$CategoryModel = D('GoodsCategory');
		if(IS_POST){
			
			if($data = $CategoryModel->create()){
				if($isEdit){
					$res = $CategoryModel -> save($data);
				}else{
					$id = $CategoryModel -> add($data);
					$res = $id;
				}
				
			}		
			if($res){
				$this->success('操作成功！');
			}
		}else{
			if($isEdit){
				$data = $CategoryModel->find($id);
			}
			//显示页面
	        $builder = new AdminConfigBuilder();
	        $builder->title($isEdit ? '编辑规则' : '添加规则')
	            ->keyId()->keyText('name', '分类名称')
	            ->data($data)
	            ->buttonSubmit(U('edit'))
	            ->buttonBack()
	            ->display();
		}
	}
	public function attribute($cid = 0){
		$CategoryModel = D('GoodsCategory');  
		$category = $CategoryModel->find($cid);
		
		$CategoryAttribute = D('CategoryAttribute');
		$map['category_id'] = $cid;
		$types = array(1=>'单选',2=>'复选',3=>'下拉',4=>'输入框');
		$lists = $CategoryAttribute -> where($map) ->select();
		foreach($lists as &$val){
			$val['type'] = $types[$val['type']];
			$val['search'] = $val['search']?'是':'否';
		}
		unset($val);
		//显示页面
        $builder = new AdminListBuilder();
        $builder->title('[' . $category['name'] . "]属性列表")
            ->buttonNew(U('editAttr',array('cid'=>$cid)))
			->setStatusUrl(U('setStatus'))
            ->buttonDelete()
            ->keyId()
			->keyText('name', '属性名')
			->keyText('type', '操作模式')
			->keyText('value', '选择项数据')
			->keyText('search', '是否作为商品的筛选项')
			->keyDoActionEdit('editAttr?id=###')
            ->data($lists)
            ->pagination($totalCount, $r)
            ->display();
	}
	public function editAttr($cid = 0, $id = 0){
		$isEdit = $id? 1: 0;
		$CategoryAttribute = D('CategoryAttribute');
		if(IS_POST){
			
			if($data = $CategoryAttribute->create()){
				//type 冲突前端读取错误 
				$data['type'] = I("post.type2");
				if($isEdit){
			
					$res = $CategoryAttribute -> save($data);
				}else{
					$id = $CategoryAttribute -> add($data);
					$res = $id;
				}
				
			}		
			if($res){
				$this->success('操作成功！',U('GoodsCategory/attribute',array('cid'=>$data['category_id'])));
			}
		}else{
			if($isEdit){
				$data = $CategoryAttribute->find($id);
				$data['type2'] = $data['type'];
			}else{
				$data['category_id'] = $cid;
				
			}
			
			$types = array(1=>'单选',2=>'复选',3=>'下拉',4=>'输入框');
			$searchs = array(1=>'');
			//显示页面
	        $builder = new AdminConfigBuilder();
	        $builder->title($isEdit ? '编辑属性' : '添加属性')
	            ->keyId()
				->keyId('category_id', '分类ID')
				->keyText('name', '属性名')
	            ->keySelect('type2', '操作模式', '', $types)
				->keyText('value', '属性值','【每项数据之间用逗号','做分割】')
				->keyCheckBox('search', '是否作为商品的筛选项', '', $searchs)
	            ->data($data)
	            ->buttonSubmit(U('editAttr'))
	            ->buttonBack()
	            ->display();
		}
	}
}