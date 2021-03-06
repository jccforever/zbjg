<?php

namespace app\admin\controller\stock;

use app\admin\controller\supplier\Price;
use app\common\controller\Backend;
use think\Db;

/**
 * 领料商品管理
 *
 * @icon fa fa-circle-o
 */
class Deliverygoods extends Backend
{
    protected $noNeedRight = ['ajax_edit','delivery_add','delivery_add','next','ajax_add','ajax_del','ajax_edit','next_edit'];
    /**
     * Delivery_goods模型对象
     * @var \app\admin\model\stock\Deliverygoods
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\stock\Deliverygoods;

    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


    /**
     * 查看
     */
    public function index()
    {
        //当前是否为关联查询
        $this->relationSearch = false;
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        $params = $this->request->param();
        $delivery_id = $params['ids'];

        halt($delivery_id);
        $this->view->assign("supplier_id",$supplier_id);
        if ($this->request->isAjax())
        {

            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField'))
            {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $total = $this->model

                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model

                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

//            foreach ($list as $row) {
//                $row->visible(['id','goods_name','goods_sn','spec','unit','cate_id','scate_id','is_stock','status']);
//
//            }
            //此供应商编辑过的商品
            $supplier_goods_ids = DB::name('supplier_goods')
                ->where(['supplier_id'=>$supplier_id])
                ->column('goods_id');

            foreach($list as $key => &$value){
                $value['cate'] = DB::name('goodscategory')->where(['id'=>$value['cate_id']])->value('category_name');
                $value['scate'] = DB::name('goodscategory')->where(['id'=>$value['scate_id']])->value('category_name');
                //将被编辑过的商品价格修改
                if(in_array($value['id'],$supplier_goods_ids)){
                    $value['price'] = DB::name('supplier_goods')
                        ->where(['goods_id'=>$value['id'],'supplier_id'=>$supplier_id])
                        ->value('price');

                }
            }
            $list = collection($list)->toArray();

            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }


    /*
     * 领料出库 下一步->添加
     * */
    public function delivery_add()
    {

            //当前是否为关联查询
            $this->relationSearch = false;
            //设置过滤方法
            $this->request->filter(['strip_tags', 'trim']);
            $params = $this->request->param();

            $this->view->assign("apply_admin",$params['apply_admin']);
            $this->view->assign("delivery_id",0);
            $this->view->assign("department_id",$params['department_id']);
            if ($this->request->isAjax())
            {
                if(json_decode($params['filter'], true)['delivery_id'] != "0"){

                    $delivery_id = json_decode($params['filter'], true)['delivery_id'];
                }

                //如果发送的来源是Selectpage，则转发到Selectpage
                if ($this->request->request('keyField'))
                {
                    return $this->selectpage();
                }
                list($where, $sort, $order, $offset, $limit) = $this->buildparams();
                @$goods_name = json_decode($params['filter'], true)['fa_goods.goods_name'];
                $like = $goods_name ? ['fa_goods.goods_name' => ['like', '%' . $goods_name . '%']] : '1=1';

                $list = DB::name('stock')
                    ->field('fa_stock.id as stock_id,fa_stock.unit_price,fa_stock.stock_number,fa_goods.id as goods_id,fa_goods.goods_sn,fa_goods.goods_name,fa_goods.spec,fa_goods.unit,fa_goods.is_stock')
                    ->join('__GOODS__','fa_stock.goods_id=fa_goods.id','LEFT')
                    ->where(['fa_goods.is_stock'=>"1"])
                    ->where($like)
                    ->limit($offset, $limit)
                    ->select();
                $total = count($list);

                if(isset($delivery_id)){

                    foreach($list as $key => &$value){
                        $delivery_goods = DB::name('delivery_goods')
                            ->where(['delivery_id'=>$delivery_id,'goods_id'=>$value['goods_id']])
                            ->find();
                        $value['delivery_number'] = $delivery_goods['delivery_number'];
                        $value['remark'] = $delivery_goods['remark'];
                    }
                }
                $result = array("total" => $total, "rows" => $list);

                return json($result);
            }
            return $this->view->fetch();

    }

    /*
     * 领料出库 下一步->添加
     * */
//    public function delivery_edit()
//    {
//        {
//            //当前是否为关联查询
//            $this->relationSearch = false;
//            //设置过滤方法
//            $this->request->filter(['strip_tags', 'trim']);
//            $params = $this->request->param();
//
//            if ($this->request->isAjax())
//            {
//                $params = $this->request->param();
//                halt($params);
//                $delivery_id = json_decode($params['filter'], true)['delivery_id'];
//                //如果发送的来源是Selectpage，则转发到Selectpage
//                if ($this->request->request('keyField'))
//                {
//                    return $this->selectpage();
//                }
//                list($where, $sort, $order, $offset, $limit) = $this->buildparams();
//                @$goods_name = json_decode($params['filter'], true)['fa_goods.goods_name'];
//                $like = $goods_name ? ['fa_goods.goods_name' => ['like', '%' . $goods_name . '%']] : '1=1';
//
//                $list = DB::name('stock')
//                    ->field('fa_stock.id as stock_id,fa_stock.unit_price,fa_stock.stock_number,fa_goods.id as goods_id,fa_goods.goods_sn,fa_goods.goods_name,fa_goods.spec,fa_goods.unit,fa_goods.is_stock')
//                    ->join('__GOODS__','fa_stock.goods_id=fa_goods.id','LEFT')
//                    ->where(['fa_goods.is_stock'=>"1"])
//                    ->where($like)
//                    ->limit($offset, $limit)
//                    ->select();
//                $total = count($list);
//
//                if(isset($delivery_id)){
//
//                    foreach($list as $key => &$value){
//                        $delivery_goods = DB::name('delivery_goods')
//                            ->where(['delivery_id'=>$delivery_id,'goods_id'=>$value['goods_id']])
//                            ->find();
//                        $value['delivery_number'] = $delivery_goods['delivery_number'];
//                        $value['remark'] = $delivery_goods['remark'];
////                        $value['status'] = $delivery_goods['status'];
//                    }
//                }
//                $result = array("total" => $total, "rows" => $list);
//
//                return json($result);
//            }
//            $this->view->assign("delivery_id",$params['ids']);
//            return $this->view->fetch();
//        }
//    }

    public function delivery_edit()
    {
        {
            //当前是否为关联查询
            $this->relationSearch = false;
            //设置过滤方法
            $this->request->filter(['strip_tags', 'trim']);
            $params = $this->request->param();

            if ($this->request->isAjax())
            {
                $params = $this->request->param();

                $delivery_id = json_decode($params['filter'], true)['delivery_id'];
                //如果发送的来源是Selectpage，则转发到Selectpage
                if ($this->request->request('keyField'))
                {
                    return $this->selectpage();
                }
                list($where, $sort, $order, $offset, $limit) = $this->buildparams();
//                @$goods_name = json_decode($params['filter'], true)['fa_goods.goods_name'];
//                $like = $goods_name ? ['fa_goods.goods_name' => ['like', '%' . $goods_name . '%']] : '1=1';
                $list = DB::name('delivery_goods')
                    ->field('t1.id,t2.goods_sn,t2.goods_name,t2.spec,t2.unit,t3.unit_price,t1.delivery_number,t1.remark,t3.stock_number,t4.status')
                    ->alias('t1')
                    ->join('__GOODS__ t2','t1.goods_id=t2.id','LEFT')
                    ->join('__STOCK__ t3','t3.goods_id=t2.id','LEFT')
                    ->join('__DELIVERY__ t4','t4.id=t1.delivery_id','LEFT')
                    ->where(['t1.delivery_id'=>$delivery_id])
                    ->select();



                $total = count($list);
                $result = array("total" => $total, "rows" => $list);
                return json($result);
            }

            $delivery = DB::name('delivery')->where(['id'=>$params['ids']])->find();
            $department = DB::name('department')->where(['id'=>$delivery['department_id']])->find();
            $apply_name = DB::name('admin')->where(['id'=>$delivery['apply_admin']])->value('nickname');
            if($delivery['status'] == '0'){
                $status = "待确认";
            }elseif($delivery['status'] == '1'){
                $status = "已确认";
            }else{
                $status = "已取消";
            }
            $this->view->assign('status',$status);
            $this->view->assign('department_name',$department['name']);
            $this->view->assign('apply_name',$apply_name);
            $this->view->assign('createtime',date('Y-m-d H:i:s',$delivery['createtime']));
            $this->view->assign("delivery_id",$params['ids']);
            return $this->view->fetch();
        }
    }

    public function next()
    {
        //当前是否为关联查询
        $this->relationSearch = false;
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        $params = $this->request->param();
//halt($params);

        if ($this->request->isAjax())
        {


            $delivery_id = json_decode($params['filter'], true)['delivery_id'];

            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField'))
            {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            @$goods_name = json_decode($params['filter'], true)['fa_goods.goods_name'];
            $like = $goods_name ? ['fa_goods.goods_name' => ['like', '%' . $goods_name . '%']] : NULL;

            $list = DB::name('stock')
                ->field('fa_stock.id as stock_id,fa_stock.unit_price,fa_stock.stock_number,fa_goods.id as goods_id,fa_goods.goods_sn,fa_goods.goods_name,fa_goods.spec,fa_goods.unit,fa_goods.is_stock')
                ->join('__GOODS__','fa_stock.goods_id=fa_goods.id','LEFT')
                ->where(['fa_goods.is_stock'=>"1"])
                ->where($like)
                ->limit($offset, $limit)
                ->select();

            $total = count($list);

            if(isset($delivery_id)){

                foreach($list as $key => &$value){
                    $delivery_goods = DB::name('delivery_goods')
                        ->where(['delivery_id'=>$delivery_id,'goods_id'=>$value['goods_id']])
                        ->find();
                    $value['delivery_number'] = $delivery_goods['delivery_number'];
                    $value['remark'] = $delivery_goods['remark'];
                }
            }
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
//        $this->view->assign("apply_admin",$params['apply_admin']);
        $this->view->assign("delivery_id",$params['delivery_id']);
//        $this->view->assign("department_id",$params['department_id']);
        return $this->view->fetch();
    }



    /*
     * 新增出库申请明细
     * */

        public function ajax_add()
        {
            $params = $this->request->param();

            if(empty($params['delivery_number'])){
                $this->error('申请数量不能为空');
            }
            $stock = DB::name('stock')->find($params['stock_id']);
            if($params['delivery_number'] > $stock['stock_number']){
                $this->error('库存不足');
            }
            $delivery_id = $params['delivery_id']; //如果有申请单ID证明提交过数据 已经生成了申请单
            $delivery_amount = $stock['unit_price'] * $params['delivery_number'];
            if($delivery_id == '0'){

                $insert = [
                    'department_id' => $params['department_id'],
                    'apply_admin' => $params['apply_admin'],
                    'delivery_amount' => $delivery_amount,
                    'createtime' => time(),
                    'status' => "0",

                ];
                $delivery_id = DB::name('delivery')->insertGetId($insert);
                $delivery_goods = [
                    'delivery_id' => $delivery_id,
                    'goods_id' => $stock['goods_id'],
                    'stock_id' => $stock['id'],
                    'delivery_number' => $params['delivery_number'],
                    'remark' => $params['remark'],
                    'delivery_amount' => $delivery_amount
                ];
                $result = DB::name('delivery_goods')->insert($delivery_goods);
            }else{
                $is_isset = DB::name('delivery_goods')
                    ->where(['delivery_id'=>$params['delivery_id'],'stock_id'=>$params['stock_id']])
                    ->find();
                if($is_isset){
                    //领料商品表存在此商品(修改)
                    $result = DB::name('delivery_goods')
                        ->where(['id'=>$is_isset['id']])
                        ->update(['delivery_number'=>$params['delivery_number'],'remark'=>$params['remark'],'delivery_amount'=>$delivery_amount]);
                }else{
                    //领料商品表不存在此商品(新增)
                    $insert = [
                        'delivery_id' => $delivery_id,
                        'goods_id' => $stock['goods_id'],
                        'stock_id' => $stock['id'],
                        'delivery_number' => $params['delivery_number'],
                        'remark' => $params['remark'],
                        'delivery_amount' => $delivery_amount
                    ];
                    $result = DB::name('delivery_goods')->insert($insert);
                }
                $stock = DB::name('delivery_goods')
                    ->where(['delivery_id'=>$params['delivery_id']])
                    ->select();
                $delivery_amount = 0;
                foreach($stock as $key => $value){
                    $unit_price = DB::name('stock')
                        ->where(['goods_id'=>$value['goods_id']])
                        ->value('unit_price');
                    $delivery_amount += $unit_price * $value['delivery_number'];
                }
                DB::name('delivery')
                    ->where(['id'=>$params['delivery_id']])
                    ->update(['delivery_amount'=>$delivery_amount]);
            }

            if($result !== false){
                $this->success('操作成功','',['delivery_id'=>$delivery_id]);
            }else{
                $this->error('网络错误');
            }

        }

        public function ajax_edit()
        {
            $params = $this->request->param();

            $delivery_goods = DB::name('delivery_goods')->where(['id'=>$params['id']])->find();

            $stock = DB::name('stock')->where(['id'=>$delivery_goods['stock_id'],'goods_id'=>$delivery_goods['goods_id']])->find();

            if($stock['stock_number'] < $params['delivery_number']){
                $this->error('库存不足');
            }else{
                $update = [
                    'delivery_number' => $params['delivery_number'],
                    'remark' => $params['remark'],
                    'delivery_amount' => $params['delivery_number'] * $stock['unit_price'],
                ];
                $result = DB::name('delivery_goods')
                    ->where(['id'=>$params['id']])
                    ->update($update);
            }
            $delivery_orders = DB::name('delivery_goods')
                ->where(['delivery_id'=>$delivery_goods['delivery_id']])
                ->select();

            $delivery_amount = 0;
            foreach($delivery_orders as $key => $value){
                $delivery_amount += $value['delivery_amount'];

            }
            $result = DB::name('delivery')
                ->where(['id'=>$delivery_goods['delivery_id']])
                ->update(['delivery_amount'=>$delivery_amount]);
            if($result !== false){
                $this->success('操作成功','',['delivery_id'=>$delivery_goods['delivery_id']]);
            }else{
                $this->error('网络错误');
            }
        }

        public function ajax_del()
        {
            $params = $this->request->param();
            $delivery_goods = DB::name('delivery_goods')->where(['id'=>$params['id']])->find();
            $delivery= DB::name('delivery')->where(['id'=>$delivery_goods['delivery_id']])->find();
            $count = DB::name('delivery_goods')->where(['delivery_id'=>$delivery['id']])->count();

            if($delivery['status'] != "0"){
                $this->error('此状态不允许修改');
            }elseif($count<=1){
                $this->error('不得小于一条');
            }else{
                $result = DB::name('delivery_goods')->where(['id'=>$params['id']])->delete();

                $delivery_orders = DB::name('delivery_goods')
                    ->where(['delivery_id'=>$delivery_goods['delivery_id']])
                    ->select();

                $delivery_amount = 0;
                foreach($delivery_orders as $key => $value){
                    $delivery_amount += $value['delivery_amount'];

                }
                $result = DB::name('delivery')
                    ->where(['id'=>$delivery_goods['delivery_id']])
                    ->update(['delivery_amount'=>$delivery_amount]);
            }
            if($result !== false) {
                $this->success('操作成功','',['delivery_id'=>$delivery_goods['delivery_id']]);
            }else{
                $this->error('网路错误');
            }

        }


        /*
         * 编辑->添加领料商品->编辑
         * */
        public function next_edit()
        {
            $params = $this->request->param();
//            halt($params);
            $stock = DB::name('stock')->where(['id'=>$params['id']])->find();
            $delivery_goods = DB::name('delivery_goods')->where(['delivery_id'=>$params['delivery_id'],'goods_id'=>$stock['goods_id']])->find();
            $check_goods = DB::name('check_goods')
                ->alias('t1')
                ->join('__CHECK__ t2','t1.check_id=t2.id','LEFT')
                ->where(['t2.status'=>"0"])
                ->column('t1.goods_id');

            if(in_array($stock['goods_id'],$check_goods)){
                $this->error('盘点管理里存在该商品未确认，请确认后再领料出库');
            }
            if($stock['stock_number'] < $params['delivery_number']){
                $this->error('库存不足');
            }
            if($delivery_goods){
                //修改
                $update = [
                    'delivery_number' => $params['delivery_number'],
                    'remark' => $params['remark'],
                    'delivery_amount' => $params['delivery_number'] * $stock['unit_price'],
                ];
                $result = $result = DB::name('delivery_goods')
                    ->where(['id'=>$delivery_goods['id']])
                    ->update($update);
            }else{
                //新增
                $insert = [
                    'delivery_id' => $params['delivery_id'],
                    'goods_id' => $stock['goods_id'],
                    'stock_id' => $stock['id'],
                    'delivery_number' => $params['delivery_number'],
                    'remark' => $params['remark'],
                    'delivery_amount' => $params['delivery_number'] * $stock['unit_price']
                ];
                $result = DB::name('delivery_goods')->insert($insert);
            }
            $delivery_orders = DB::name('delivery_goods')
                ->where(['delivery_id'=>$delivery_goods['delivery_id']])
                ->select();

            $delivery_amount = 0;
            foreach($delivery_orders as $key => $value){
                $delivery_amount += $value['delivery_amount'];

            }
            $result = DB::name('delivery')
                ->where(['id'=>$delivery_goods['delivery_id']])
                ->update(['delivery_amount'=>$delivery_amount]);

            if($result !== false){
                $this->success('操作成功','',['delivery_id'=>$delivery_goods['delivery_id']]);
            }else{
                $this->error('网络错误');
            }
        }
}
