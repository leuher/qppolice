define([], function () {
    require([], function () {
    //绑定data-toggle=addresspicker属性点击事件
    $(document).on('click', "[data-toggle='addresspicker']", function () {
        var that = this;
        var callback = $(that).data('callback');
        var input_id = $(that).data("input-id") ? $(that).data("input-id") : "";
        var lat_id = $(that).data("lat-id") ? $(that).data("lat-id") : "";
        var lng_id = $(that).data("lng-id") ? $(that).data("lng-id") : "";
        var lat = lat_id ? $("#" + lat_id).val() : '';
        var lng = lng_id ? $("#" + lng_id).val() : '';
        var url = "/addons/address/index/select";
        url += (lat && lng) ? '?lat=' + lat + '&lng=' + lng : '';
        Fast.api.open(url, '位置选择', {
            callback: function (res) {
                input_id && $("#" + input_id).val(res.address);
                lat_id && $("#" + lat_id).val(res.lat);
                lng_id && $("#" + lng_id).val(res.lng);
                try {
                    //执行回调函数
                    if (typeof callback === 'function') {
                        callback.call(that, res);
                    }
                } catch (e) {

                }
            }
        });
    });
});

require.config({
    paths: {
        'async': '../addons/example/js/async',
        'BMap': ['//api.map.baidu.com/api?v=2.0&ak=mXijumfojHnAaN2VxpBGoqHM'],
    },
    shim: {
        'BMap': {
            deps: ['jquery'],
            exports: 'BMap'
        }
    }
});

//修改验证码为检验验证
require.config({
    paths: {
        'geet': '../addons/geet/js/gt'
    }
});
require(['geet'], function (Geet) {
    var geetInit = false;
    $("input[name='captcha']").each(function () {
        var obj = $(this);
        var form = obj.closest('form');
        $("<input type='hidden' name='geeturl' value='" + (form.attr("action") ? form.attr("action") : location.pathname + location.search) + "' />").appendTo(form);
        $("<input type='hidden' name='geetmodule' value='" + Config.modulename + "' />").appendTo(form);
        $("<input type='hidden' name='geetmoduleurl' value='" + Config.moduleurl + "' />").appendTo(form);
        form.attr('action', Fast.api.fixurl('/addons/geet/index/check'));
        obj.parent().removeClass('input-group').addClass('form-group').html('<div id="embed-captcha"><input type="hidden" name="captcha" class="form-control" data-rule="请完成验证码,验证码:required;" /> </div> <p id="wait" class="show">正在加载验证码......</p>');
        var handlerEmbed = function (captchaObj) {
            // 将验证码加到id为captcha的元素里，同时会有三个input的值：geetest_challenge, geetest_validate, geetest_seccode
            geetInit = captchaObj;
            captchaObj.appendTo("#embed-captcha");
            captchaObj.onReady(function () {
                $("#wait")[0].className = "hide";
            });
            captchaObj.onSuccess(function () {
                var result = captchaObj.getValidate();
                if (result) {
                    $('#embed-captcha input[name="captcha"]').val('ok');
                }
            });
            // 更多接口参考：http://www.geetest.com/install/sections/idx-client-sdk.html
        };
        Fast.api.ajax("/addons/geet/index/start", function (data) {
            // 更多配置参数请参见：http://www.geetest.com/install/sections/idx-client-sdk.html#config
            // 使用initGeetest接口
            // 参数1：配置参数
            // 参数2：回调，回调的第一个参数验证码对象，之后可以使用它做appendTo之类的事件
            initGeetest({
                gt: data.gt,
                challenge: data.challenge,
                new_captcha: data.new_captcha,
                product: "embed", // 产品形式，包括：float，embed，popup。注意只对PC版验证码有效
                width: '100%',
                offline: !data.success // 表示用户后台检测极验服务器是否宕机，一般不需要关注
            }, handlerEmbed);
            form.on("error.form", function (e, data) {
                geetInit.reset();
            });
            return false;
        });
    });
});
require(['form', 'upload'], function (Form, Upload) {
    var _bindevent = Form.events.bindevent;
    Form.events.bindevent = function (form) {
        _bindevent.apply(this, [form]);
        try {
            //绑定自定义属性组件
            if ($("textarea[id$=Properties]", form).size() > 0) {
                require(['../addons/properties/Properties'], function (Properties){
                    var obj=$("textarea[id$=Properties]", form);

            　　　　var table=Properties.init(obj);

                    $(form).on("click",".property_add",function(event){
                        Properties.create(table);
                    })

                    $(form).on("click",".property_del",function(event){
                        Properties.remove(table,$(this).parents("tr").data("index"));
                    })

                    $(form).on("click",".property_drag",function(event){
                        Properties.sort(table,$(this).data("index"),$(this).data("direction"));
                    })

                    $(form).on("change","input",function(event){
                        Properties.set(table,$(this).parents("tr").data("index"));
                    })


                    
            　　});
            }
        } catch (e) {

        }

    };
});

require.config({
    paths: {
        'summernote': '../addons/summernote/lang/summernote-zh-CN.min'
    },
    shim:{
        'summernote': ['../addons/summernote/js/summernote.min', 'css!../addons/summernote/css/summernote.css'],
    }
});
require(['form', 'upload'], function (Form, Upload) {
    var _bindevent = Form.events.bindevent;
    Form.events.bindevent = function (form) {
        _bindevent.apply(this, [form]);
        try {
            //绑定summernote事件
            if ($(".summernote,.editor", form).size() > 0) {
                require(['summernote'], function () {
                    $(".summernote,.editor", form).summernote({
                        height: 250,
                        lang: 'zh-CN',
                        fontNames: [
                            'Arial', 'Arial Black', 'Serif', 'Sans', 'Courier',
                            'Courier New', 'Comic Sans MS', 'Helvetica', 'Impact', 'Lucida Grande',
                            "Open Sans", "Hiragino Sans GB", "Microsoft YaHei",
                            '微软雅黑', '宋体', '黑体', '仿宋', '楷体', '幼圆',
                        ],
                        fontNamesIgnoreCheck: [
                            "Open Sans", "Microsoft YaHei",
                            '微软雅黑', '宋体', '黑体', '仿宋', '楷体', '幼圆'
                        ],
                        toolbar: [
                            ['style', ['style', 'undo', 'redo']],
                            ['font', ['bold', 'underline', 'strikethrough', 'clear']],
                            ['fontname', ['color', 'fontname', 'fontsize']],
                            ['para', ['ul', 'ol', 'paragraph', 'height']],
                            ['table', ['table', 'hr']],
                            ['insert', ['link', 'picture', 'video']],
                            ['view', ['fullscreen', 'codeview', 'help']]
                        ],
                        dialogsInBody: true,
                        callbacks: {
                            onChange: function (contents) {
                                $(this).val(contents);
                                $(this).trigger('change');
                            },
                            onInit: function () {
                            },
                            onImageUpload: function (files) {
                                var that = this;
                                //依次上传图片
                                for (var i = 0; i < files.length; i++) {
                                    Upload.api.send(files[i], function (data) {
                                        var url = Fast.api.cdnurl(data.url);
                                        $(that).summernote("insertImage", url, 'filename');
                                    });
                                }
                            }
                        }
                    });
                });
            }
        } catch (e) {

        }

    };
});

});