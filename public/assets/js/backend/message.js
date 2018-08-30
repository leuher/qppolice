define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'message/index',
                    add_url: 'message/add',
                    edit_url: 'message/edit',
                    del_url: 'message/del',
                    multi_url: 'message/multi',
                    table: 'message',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'title', title: __('Title')},
                        {field: 'content', title: __('Content')},
                        {field: 'sender', title: __('Sender')},
                        {field: 'status', title: __('Status'), formatter: Table.api.formatter.status},
                        // {field: 'status_text', title: __('Status'), operate:false},
                        {field: 'createtime', title: __('Createtime'), sortable: true, operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), sortable: true, operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        // {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                        {field: 'operate', title: __('Operate'), table: table,
                            buttons: [
                                {
                                    name: 'detail',
                                    text: __('发送消息'), 
                                    icon: 'fa fa-send-o',
                                    classname: 'btn btn-success btn-xs btn-detail btn-dialog btn-message',
                                    url: 'sendofficer/index/id/{ids}'
                                },
                                {
                                    name: 'record',
                                    text: __('发送消息记录'),
                                    icon: 'fa fa-list-alt',
                                    classname: 'btn btn-primary btn-xs btn-detail btn-dialog',
                                    url: 'sendmessage/index/id/{ids}'
                                }
                                ], 
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
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
        api: {
            bindevent: function (t) {
              
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});