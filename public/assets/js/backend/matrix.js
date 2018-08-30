define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'matrix/index',
                    add_url: 'matrix/add',
                    edit_url: 'matrix/edit',
                    del_url: 'matrix/del',
                    multi_url: 'matrix/multi',
                    table: 'matrix',
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
                        {field: 'name', title: __('Name')},
                        {field: 'status', title: __('Status'), visible:false, searchList: {"1":__('Status 1'),"2":__('Status 2')}},
                        {field: 'status_text', title: __('Status'), operate:false},
                        // {field: 'category_id', title: __('Category_id')},
                        {field: 'category_id', title: __('Category_id'), align: 'left', searchList: $.getJSON('matrix/area'),formatter: Controller.api.Category_id},
                        {field: 'image', title: __('Image'), operate:false,formatter: Table.api.formatter.image},
                        {field: 'link', title: __('Link')},
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