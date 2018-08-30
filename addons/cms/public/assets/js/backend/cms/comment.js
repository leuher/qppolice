define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'cms/comment/index',
                    add_url: 'cms/comment/add',
                    edit_url: 'cms/comment/edit',
                    del_url: 'cms/comment/del',
                    multi_url: 'cms/comment/multi',
                    table: 'comment',
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
                        {field: 'type', title: __('Type'), visible: false, searchList: {"archives": __('archives'), "page": __('page')}},
                        {field: 'type_text', title: __('Type'), operate: false},
                        {field: 'aid', title: __('Aid'), formatter: Table.api.formatter.search},
                        {field: 'pid', title: __('Pid'), formatter: Table.api.formatter.search, visible: false},
                        {field: 'username', title: __('Username'), formatter: Table.api.formatter.search},
                        {field: 'email', title: __('Email'), formatter: Table.api.formatter.search},
                        {field: 'website', title: __('Website')},
                        {field: 'comments', title: __('Comments')},
                        {field: 'ip', title: __('Ip'), formatter: Table.api.formatter.search},
                        {field: 'useragent', title: __('Useragent'), visible: false},
                        {field: 'subscribe', title: __('Subscribe')},
                        {field: 'createtime', title: __('Createtime'), operate: 'RANGE', addclass: 'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate: 'RANGE', addclass: 'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'status', title: __('Status'), visible: false, searchList: {"normal": __('normal'), "hidden": __('hidden')}},
                        {field: 'status_text', title: __('Status'), operate: false},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});