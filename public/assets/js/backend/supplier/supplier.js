define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
      
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'supplier/supplier/index' + location.search,
                    add_url: 'supplier/supplier/add',
                    edit_url: 'supplier/supplier/edit',
                    // del_url: 'supplier/supplier/del',
                    multi_url: 'supplier/supplier/multi',
                    table: 'supplier',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'weigh',
                columns: [
                    [
                        // {checkbox: true},
                        {field: 'id', title: __('Id'),operate:false},
                        {field: 'supplier_sn', title: __('Supplier_sn'),operate: 'LIKE %...%'},
                        {field: 'supplier_name', title: __('Supplier_name'),operate: 'LIKE %...%'},
                        {field: 'address', title: __('Address'),operate:false},
                        {field: 'linkman', title: __('Linkman'),operate: 'LIKE %...%'},
                        {field: 'mobile', title: __('Mobile'),operate: 'LIKE %...%'},
                        {field: 'status', title: __('Status'), searchList: {"0":__('Status 0'),"1":__('Status 1'),"2":__('Status 2')}, formatter: Table.api.formatter.status},
                        {field: 'remark', title: __('Remark'),operate:false,visible:false},
                        {field: 'weigh', title: __('Weigh'),operate:false,visible:false},
                        // {
                        //     field: 'buttons',
                        //     width: "120px",
                        //     title: __('价格维护'),
                        //     operate:false,
                        //     table: table,
                        //     events: Table.api.events.operate,
                        //     buttons: [
                        //         {
                        //             name: 'addtabs',
                        //             text: __('价格维护'),
                        //             title: __('价格维护'),
                        //             classname: 'btn btn-xs btn-warning btn-addtabs',
                        //             icon: 'fa fa-folder-o',
                        //             //url: 'example/bootstraptable/detail'
                        //             url: "supplier/price/index?supplier_id="+Fast.api.query("")
                        //             //url: "supplier/price/index"
                        //         }
                        //     ],
                        //
                        // },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            buttons: [
                                {
                                    name: 'addtabs',
                                    text: __('价格维护'),
                                    title: __('价格维护'),
                                    extend: 'data-area= \'["100%", "100%"]\'',
                                    classname: 'btn btn-xs btn-warning btn-dialog',
                                    icon: 'fa fa-folder-o',
                                    //url: 'example/bootstraptable/detail'
                                    url: "supplier/price/index?supplier_id="+Fast.api.query("")
                                    //url: "supplier/price/index"
                                }
                            ],
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ],
                search:false,
                showToggle: false,
                showColumns: false,
                searchFormVisible: true
            });
            $(document).on("click", ".btn-daoru", function () { //监听刚刚的按钮btn-myexcel-export的动作

                //直接url访问，不能使用ajax，因为ajax要求返回数据，和PHPExcel一会浏览器输出冲突！将数据作为参数
                top.location.href="goods/daoru";
            });
            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        daoru: function () {

            Controller.api.bindevent();

        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});