<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: huajie <banhuajie@163.com>
// +----------------------------------------------------------------------

namespace Admin\Model;
use Think\Model;
use Admin\Model\AuthGroupModel;

/**
 * 文档基础模型
 */
class GoodsAuctionModel extends Model{

    /* 自动验证规则 */
    protected $_validate = array(
        array('model_id,pid,category_id', 'check_category_model', '该分类没有绑定当前模型', self::MUST_VALIDATE , 'function', self::MODEL_INSERT),
    );

    /* 自动完成规则 */
    protected $_auto = array(

    );

 
}