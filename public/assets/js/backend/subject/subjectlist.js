define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'subject/subjectlist/index',
                    add_url: 'subject/subjectlist/add',
                    edit_url: 'subject/subjectlist/edit',
                    del_url: 'subject/subjectlist/del',
                    multi_url: 'subject/subjectlist/multi',
                    table: 'subject_list',
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
                        // {field: 'cate_id', title: __('Cate_id')},
                        {field: 'cate_name', title: __('所属类别')},
                        {field: 'status', title: __('Status'), formatter: Table.api.formatter.status},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        //{field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                        {field: 'operate', title: __('Operate'), table: table,
                            
                            buttons: [{
                                    name: 'subjectscore',
                                    text: __('成绩管理'),
                                    icon: 'fa fa-list',
                                    classname: 'btn btn-xs btn-detail btn-primary',
                                    url: 'subject/subjectscore/index/id/{ids}'
                                }],
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
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});