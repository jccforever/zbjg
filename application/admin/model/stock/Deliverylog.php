<?php

namespace app\admin\model\stock;

use think\Model;


class Deliverylog extends Model
{

    

    

    // 表名
    protected $name = 'delivery_log';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];
    

    







}
