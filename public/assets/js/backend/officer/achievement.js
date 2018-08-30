define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var selectVals = {
        cate:0,
        list:0
    };

    function addSelect(id,data){


        var select = $('#'+id);
        var list = data;
        var options = '<option uid="">请选择</option>';
        // console.log(list);

        $.each(list,function(index,item){
            options+='<option id="'+item.id+'">'+item.name+"</option>"
        })

        select.empty().append(options);
        select.find("option[id="+selectVals[id]+"]").attr("selected",true);

    }


    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'officer/achievement/index',
                    add_url: 'officer/achievement/add',
                    edit_url: 'officer/achievement/edit',
                    del_url: 'officer/achievement/del',
                    multi_url: 'officer/achievement/multi',
                    table: 'officer_achievement',
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
                        {field: 'officer_name', title: __('姓名')},
                        {field: 'cate_name', title: __('科目类'),searchList:function(){

                            var html = '<select class="form-control" id="cate"  name="cate_id">' +

                                '</select>';

                            return html;

                        }},
                        {field: 'list_name', title: __('科目'),searchList:function(){

                            var html = '<select class="form-control" id="list"  name="list_id">' +

                                '</select>';

                            return html;

                        }},
                        {field: 'achievement', title: __('Achievement'), operate:false},
                        {field: 'result', title: __('Result'),operate:false,formatter: Table.api.formatter.result},
                        {field: 'referee', title: __('Referee'),operate:false},
                        {field: 'status', title: __('Status'),formatter: Table.api.formatter.status,operate:false},
                        {field: 'testtime', title: __('Testtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        // {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate},

                    ]
                ],

                onLoadSuccess:function(cate){  //加载成功时执行
                    // layer.msg("加载成功");
                    console.log(cate);

                    $.each(  [
                        {id:'cate',data:cate.cate},
                        {id:'list',data:cate.s_list}
                    ],function(index ,item){
                        addSelect(item.id,item.data)
                    })

                },
                queryParams:function(params){
                    var _filter = JSON.parse(params.filter);

                    _filter['cate_id'] = $("#cate").find("option:selected").attr('id');
                    _filter['list_id'] = $("#list").find("option:selected").attr('id');

                    selectVals['cate'] = $("#cate").find("option:selected").attr('id');
                    selectVals['list'] = $("#list").find("option:selected").attr('id');

                    params['newFilter'] = JSON.stringify(_filter);

                    return params;
                }
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