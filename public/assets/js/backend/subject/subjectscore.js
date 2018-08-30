define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'subject/subjectscore/index',
                    add_url: 'subject/subjectscore/add',
                    edit_url: 'subject/subjectscore/edit',
                    del_url: 'subject/subjectscore/del',
                    multi_url: 'subject/subjectscore/multi',
                    table: 'subject_score',
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
                        // {field: 'id', title: __('Id')},
                        // {field: 'list_id', title: __('List_id')},
                        {field: 'list_name', title: __('科目名称')},
                        // {field: 'type_id', title: __('Type_id')},
                        {field: 'type_name', title: __('警种名称')},
                        {field: 'qualification', title: __('Qualification')},
                        {field: 'excellent', title: __('Excellent')},
                        {field: 'status', title: __('Status'), formatter: Table.api.formatter.status},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        //{field: 'desc', title: __('Desc')},
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

$('.refresh-p').on('click',function () {
    parent.location.reload();
});