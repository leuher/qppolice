define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {
    var selectVals = {
        type:0,
        group:0
    };


    function addSelect(id,data){


        var select = $('#'+id);
        var list = data;
        var options = '<option uid="">请选择</option>';
        console.log(list);

        $.each(list,function(index,item){
            options+='<option id="'+item.id+'">'+item.name+"</option>"
        })

        select.empty().append(options);

        console.log(selectVals)
        select.find("option[id="+selectVals[id]+"]").attr("selected",true);

    }

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'police/officer/index',
                    add_url: 'police/officer/add',
                    edit_url: 'police/officer/edit',
                    del_url: 'police/officer/del',
                    multi_url: 'police/officer/multi',
                    table: 'police_officer',
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
                        {field: 'name', title: __('Name')},
                        {field: 'sex', title: __('Sex'),formatter: Table.api.formatter.sex,operate:false},
                        {field: 'head_image', title: __('Head_image'), formatter: Table.api.formatter.image,operate: false},
                        {field: 'group_name', title: __('所属警组'), searchList:function(){

                            var html = '<select class="form-control" id="group"  name="police_group">' +

                                '</select>';

                            return html;

                        }},


                        {field: 'type_name', title: __('所属警种'), searchList:function(){

                            var html = '<select class="form-control" id="type"  name="police_type">' +

                                '</select>';

                            return html;

                        }},

                        {field: 'jointime', title: __('Jointime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'status', title: __('Status'),formatter: Table.api.formatter.status,operate: false},
                        // {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:false, addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'),
                            buttons: [{
                                name: 'subjectscore',
                                text: __('查看成绩'),
                                icon: 'fa fa-list',
                                classname: 'btn btn-xs btn-detail btn-primary',
                                url: 'officer/achievement/index/id/{ids}'
                            }],
                            table: table,
                            events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ],
                onLoadSuccess:function(group){  //加载成功时执行
                   // layer.msg("加载成功");

                    $.each(  [
                        {id:'group',data:group.group},
                        {id:'type',data:group.type}
                    ],function(index ,item){
                        addSelect(item.id,item.data)
                    })

                },
                queryParams:function(params){
                    var _filter = JSON.parse(params.filter);


                    _filter['police_type'] = $("#type").find("option:selected").attr('id');
                    _filter['police_group'] = $("#group").find("option:selected").attr('id');

                    selectVals['type'] = $("#type").find("option:selected").attr('id');
                    selectVals['group'] = $("#group").find("option:selected").attr('id');

                    params['newFilter'] = JSON.stringify(_filter);

                    return params;
                }

            });

            ;
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